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

const THEME_ORIGINAL_MARKER = 'iz-theme-original:';

function allowedThemeFonts() {
    return [
        'Modern' => ['h' => 'Poppins', 'b' => 'Inter'],
        'Classic' => ['h' => 'Playfair Display', 'b' => 'Lora'],
        'Friendly' => ['h' => 'Fredoka', 'b' => 'Nunito'],
        'Clean' => ['h' => 'Montserrat', 'b' => 'Work Sans'],
    ];
}

function extractCssVar($css, $name) {
    return preg_match('/--' . preg_quote($name, '/') . '\s*:\s*([^;]+);/i', $css, $m) ? trim($m[1]) : null;
}

function setCssVar($css, $name, $value) {
    $pattern = '/--' . preg_quote($name, '/') . '\s*:\s*[^;]+;/i';
    if ($value === null) {
        return preg_replace($pattern, '', $css);
    }
    $line = '--' . $name . ': ' . $value . ';';
    if (preg_match($pattern, $css)) {
        return preg_replace($pattern, $line, $css, 1);
    }
    return rtrim($css) . "\n  " . $line;
}

function themeFontLink($family) {
    $href = 'https://fonts.googleapis.com/css2?family=' . rawurlencode($family) . ':wght@400;600;700;800&display=swap';
    $href = str_replace('%20', '+', $href);
    return '<link data-iz-theme-link rel="stylesheet" href="' . htmlspecialchars($href, ENT_QUOTES) . '">';
}

function getOriginalTheme($html, $rootCss) {
    if (preg_match('/<!--' . preg_quote(THEME_ORIGINAL_MARKER, '/') . '([A-Za-z0-9+\/=]+)-->/i', $html, $m)) {
        $decoded = json_decode((string)base64_decode($m[1], true), true);
        if (is_array($decoded)) { return $decoded; }
    }
    $vars = [];
    foreach (['c-accent', 'c-accent-2', 'c-accent-contrast', 'font-heading', 'font-body'] as $name) {
        $vars[$name] = extractCssVar($rootCss, $name);
    }
    return ['vars' => $vars];
}

function applyThemeToHtml($html, $theme) {
    if (stripos($html, '<html') === false || !preg_match('/(:root\s*\{)(.*?)(\})/is', $html, $m)) {
        return [null, 'Theme contract missing.'];
    }
    $reset = !empty($theme['reset']);
    $fonts = allowedThemeFonts();
    $color = trim((string)($theme['color'] ?? ''));
    $fontKey = trim((string)($theme['font'] ?? ''));
    if (!$reset) {
        if ($color !== '' && !preg_match('/^#[0-9a-f]{6}$/i', $color)) {
            return [null, 'Invalid theme color.'];
        }
        if ($fontKey !== '' && !isset($fonts[$fontKey])) {
            return [null, 'Invalid theme font.'];
        }
    }

    $original = getOriginalTheme($html, $m[2]);
    $css = $m[2];
    if ($reset) {
        foreach (($original['vars'] ?? []) as $name => $value) {
            $css = setCssVar($css, $name, $value);
        }
        $html = preg_replace('/<!--' . preg_quote(THEME_ORIGINAL_MARKER, '/') . '[A-Za-z0-9+\/=]+-->/i', '', $html);
        $html = preg_replace('/\s*<link\b[^>]*data-iz-theme-link[^>]*>\s*/i', "\n", $html);
    } else {
        if ($color !== '') {
            $css = setCssVar($css, 'c-accent', strtolower($color));
            $css = setCssVar($css, 'c-accent-2', strtolower($color));
            $css = setCssVar($css, 'c-accent-contrast', '#ffffff');
        }
        if ($fontKey !== '') {
            $font = $fonts[$fontKey];
            $css = setCssVar($css, 'font-heading', "'" . $font['h'] . "', serif");
            $css = setCssVar($css, 'font-body', "'" . $font['b'] . "', sans-serif");
            $html = preg_replace('/\s*<link\b[^>]*data-iz-theme-link[^>]*>\s*/i', "\n", $html);
            $links = "\n" . themeFontLink($font['h']) . "\n" . themeFontLink($font['b']) . "\n";
            $html = preg_replace('/<\/head>/i', $links . '</head>', $html, 1);
        }
        if (!preg_match('/<!--' . preg_quote(THEME_ORIGINAL_MARKER, '/') . '[A-Za-z0-9+\/=]+-->/i', $html)) {
            $marker = '<!--' . THEME_ORIGINAL_MARKER . base64_encode(json_encode($original)) . "-->\n";
            $html = preg_replace('/<head([^>]*)>/i', '<head$1>' . "\n" . $marker, $html, 1);
        }
    }

    $newRoot = $m[1] . $css . $m[3];
    $html = preg_replace('/(:root\s*\{)(.*?)(\})/is', $newRoot, $html, 1);
    return [$html, null];
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
if (!preg_match('/^[0-9a-f-]{36}$/i', $leadId) || !hash_equals(siteToken($leadId), $token)) {
    http_response_code(403); echo json_encode(['success'=>false,'message'=>'This editor is not valid here.']); exit;
}

list($lc, $rows) = sb('GET', '/rest/v1/site_builder_leads?id=eq.' . rawurlencode($leadId) . '&select=status,free_edits_used,preview_slug,generated_html,created_at&limit=1');
$lead = (is_array($rows) && count($rows)) ? $rows[0] : null;
if (!$lead) { http_response_code(404); echo json_encode(['success'=>false,'message'=>'Site not found.']); exit; }

$action = (string)($data['action'] ?? 'edit');
if ($action === 'theme') {
    $theme = is_array($data['theme'] ?? null) ? $data['theme'] : [];
    list($newHtml, $themeErr) = applyThemeToHtml((string)($lead['generated_html'] ?? ''), $theme);
    if ($newHtml === null) {
        http_response_code(400); echo json_encode(['success'=>false,'message'=>$themeErr ?: 'Could not save theme.']); exit;
    }
    list($pc) = sb('PATCH', '/rest/v1/site_builder_leads?id=eq.' . rawurlencode($leadId), ['generated_html'=>$newHtml]);
    if ($pc < 200 || $pc >= 300) {
        http_response_code(502); echo json_encode(['success'=>false,'message'=>'Could not save theme.']); exit;
    }
    define('IZ_GEN_LIB', 1);
    require_once __DIR__ . '/../scripts/generate-pending-previews.php';
    $slug = (string)($lead['preview_slug'] ?? '');
    if ($slug !== '') { writePreview($slug, $newHtml, $leadId, (string)($lead['created_at'] ?? '')); }
    echo json_encode(['success'=>true,'saved'=>true]);
    exit;
}

$req = trim((string)($data['request_text'] ?? ''));
if (mb_strlen($req) < 3 || mb_strlen($req) > 500) {
    http_response_code(400); echo json_encode(['success'=>false,'message'=>'Tell us the change in a sentence or two.']); exit;
}

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
