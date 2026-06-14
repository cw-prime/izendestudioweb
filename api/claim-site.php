<?php
/**
 * AI Website Builder — Claim Site
 *
 * Called when a prospect clicks "Claim this site" on their live preview.
 * Flips the lead to `claimed` so it surfaces in the owner's queue for
 * provisioning (Stage 3). No payment yet — this captures intent.
 *
 * POST JSON: { "lead_id": "<uuid>" }   ->  { success, status }
 */

header('Content-Type: application/json');
header('Cache-Control: no-store');

require_once __DIR__ . '/../config/env-loader.php';
require_once __DIR__ . '/../config/security.php';

initSecureSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$identifier = ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . '_claim_site';
if (function_exists('checkRateLimit') && !checkRateLimit($identifier, 20, 600)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many requests.']);
    exit;
}

$data = json_decode((string)file_get_contents('php://input'), true);
$id = is_array($data) ? (string)($data['lead_id'] ?? '') : '';
if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid id.']);
    exit;
}

$supabaseUrl = rtrim((string)getEnv('SUPABASE_URL', ''), '/');
$supabaseKey = trim((string)getEnv('SUPABASE_SERVICE_ROLE_KEY', ''));
if ($supabaseUrl === '' || $supabaseKey === '') {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Not configured.']);
    exit;
}

// Only promote a live preview to claimed (don't clobber converted/expired).
$url = $supabaseUrl . '/rest/v1/site_builder_leads?id=eq.' . rawurlencode($id) . '&status=eq.preview_live';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . $supabaseKey,
    'Authorization: Bearer ' . $supabaseKey,
    'Content-Type: application/json',
    'Prefer: return=representation',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['status' => 'claimed', 'claimed_at' => gmdate('c')]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$rows = json_decode((string)$resp, true);
$ok = ($code >= 200 && $code < 300);
// If nothing matched preview_live, treat an already-claimed lead as success (idempotent).
$status = (is_array($rows) && isset($rows[0]['status'])) ? $rows[0]['status'] : 'claimed';

if (!$ok) {
    http_response_code(502);
    echo json_encode(['success' => false, 'message' => 'Could not record your claim. Please call us at (314) 886-6356.']);
    exit;
}

echo json_encode(['success' => true, 'status' => $status]);
