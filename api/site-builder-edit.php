<?php
/**
 * AI Website Builder — Edit Request Endpoint (Stage 4)
 *
 * Queues an AI edit ("make the hero greener", "shorten the about text") from the
 * preview's Customize chat box. Cross-domain + per-site HMAC token (same as the
 * booking endpoint) + honeypot + rate limit. A cron worker
 * (scripts/apply-pending-edits.php) applies queued edits with GLM and re-renders.
 *
 * Gating: N free edits before claiming; after that -> claim nudge. Converted
 * (paid) sites edit without limit.
 *
 * POST JSON: { lead_id, token, request_text, website(honeypot) }
 */
require_once __DIR__ . '/../config/env-loader.php';
require_once __DIR__ . '/../config/security.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
header('Cache-Control: no-store');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

const FREE_EDIT_LIMIT = 3;

function siteToken($leadId) {
    return substr(hash_hmac('sha256', 'site:' . $leadId, (string)getEnv('SITE_BOOKING_SECRET', '')), 0, 40);
}

$base = rtrim((string)getEnv('SUPABASE_URL', ''), '/');
$key  = trim((string)getEnv('SUPABASE_SERVICE_ROLE_KEY', ''));
function sb($method, $path, $body = null) {
    global $base, $key;
    $ch = curl_init($base . $path);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    $h = ['apikey: '.$key, 'Authorization: Bearer '.$key, 'Accept: application/json'];
    if ($body !== null) { $h[] = 'Content-Type: application/json'; $h[] = 'Prefer: return=representation'; curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body)); }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $h);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $r = curl_exec($ch); $c = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    return [$c, json_decode((string)$r, true)];
}

// ---- GET: edit status (powers the widget's progress indicator) ----
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $leadId = (string)($_GET['lead_id'] ?? '');
    $token  = (string)($_GET['token'] ?? '');
    $eid    = (string)($_GET['eid'] ?? '');
    if (!preg_match('/^[0-9a-f-]{36}$/i', $leadId) || !hash_equals(siteToken($leadId), $token) || !preg_match('/^[0-9a-f-]{36}$/i', $eid)) {
        http_response_code(403); echo json_encode(['ok'=>false]); exit;
    }
    list($c, $rows) = sb('GET', '/rest/v1/site_builder_edits?id=eq.' . rawurlencode($eid) . '&lead_id=eq.' . rawurlencode($leadId) . '&select=status&limit=1');
    $row = (is_array($rows) && count($rows)) ? $rows[0] : null;
    echo json_encode(['ok'=>true, 'status'=>$row['status'] ?? 'unknown']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit; }

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (function_exists('checkRateLimit') && !checkRateLimit($ip . '_site_edit', 10, 600)) {
    http_response_code(429); echo json_encode(['success'=>false,'message'=>'Too many edits at once — give it a minute.']); exit;
}

$data = json_decode((string)file_get_contents('php://input'), true);
if (!is_array($data)) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid request.']); exit; }
if (trim((string)($data['website'] ?? '')) !== '') { echo json_encode(['success'=>true,'message'=>'Thanks!']); exit; } // honeypot

$leadId = (string)($data['lead_id'] ?? '');
$token  = (string)($data['token'] ?? '');
$req    = trim((string)($data['request_text'] ?? ''));
if (!preg_match('/^[0-9a-f-]{36}$/i', $leadId) || !hash_equals(siteToken($leadId), $token)) {
    http_response_code(403); echo json_encode(['success'=>false,'message'=>'This editor is not valid here.']); exit;
}
if (mb_strlen($req) < 3 || mb_strlen($req) > 500) {
    http_response_code(400); echo json_encode(['success'=>false,'message'=>'Tell us the change in a sentence or two.']); exit;
}

list($lc, $rows) = sb('GET', '/rest/v1/site_builder_leads?id=eq.' . rawurlencode($leadId) . '&select=status,free_edits_used,preview_slug&limit=1');
$lead = (is_array($rows) && count($rows)) ? $rows[0] : null;
if (!$lead) { http_response_code(404); echo json_encode(['success'=>false,'message'=>'Site not found.']); exit; }

$converted = ($lead['status'] ?? '') === 'converted';
$used = (int)($lead['free_edits_used'] ?? 0);
if (!$converted && $used >= FREE_EDIT_LIMIT) {
    $slug = (string)($lead['preview_slug'] ?? '');
    echo json_encode([
        'success'=>false, 'gated'=>true,
        'message'=>'You\'ve used your free edits — claim your site to keep editing as much as you like.',
        'claim_url'=>'https://izendestudioweb.com/claim-site.php' . ($slug !== '' ? '?slug='.rawurlencode($slug) : ''),
    ]);
    exit;
}

list($ic, $ins) = sb('POST', '/rest/v1/site_builder_edits', ['lead_id'=>$leadId, 'request_text'=>$req, 'status'=>'pending']);
if ($ic < 200 || $ic >= 300) { http_response_code(502); echo json_encode(['success'=>false,'message'=>'Could not queue your edit. Please try again.']); exit; }
$editId = (is_array($ins) && isset($ins[0]['id'])) ? $ins[0]['id'] : '';
if (!$converted) { sb('PATCH', '/rest/v1/site_builder_leads?id=eq.' . rawurlencode($leadId), ['free_edits_used'=>$used + 1]); }

$left = $converted ? null : max(0, FREE_EDIT_LIMIT - ($used + 1));
echo json_encode([
    'success'=>true, 'queued'=>true, 'edit_id'=>$editId,
    'message'=>'Working on your change…',
    'free_left'=>$left,
]);
