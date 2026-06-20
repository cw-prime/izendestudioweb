<?php
/**
 * AI Website Builder — Preview Status (browser poll endpoint)
 *
 * The intake page polls this while the prospect watches their site build.
 * GET ?id=<uuid>  ->  { status, preview_url }
 *
 * Read-only. Uses the Supabase service-role key server-side (never exposed to
 * the browser) and returns only non-sensitive status fields for the one row.
 */

header('Content-Type: application/json');
header('Cache-Control: no-store');

require_once __DIR__ . '/../config/env-loader.php';
require_once __DIR__ . '/../config/security.php';

initSecureSession();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Light rate limit — polling is frequent but bounded per client.
$identifier = ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . '_preview_status';
if (function_exists('checkRateLimit') && !checkRateLimit($identifier, 240, 300)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Slow down.']);
    exit;
}

$id = (string)($_GET['id'] ?? '');
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

$url = $supabaseUrl . '/rest/v1/site_builder_leads?id=eq.' . rawurlencode($id)
     . '&select=status,preview_url,preview_slug';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . $supabaseKey,
    'Authorization: Bearer ' . $supabaseKey,
    'Accept: application/json',
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($resp === false || $code < 200 || $code >= 300) {
    http_response_code(502);
    echo json_encode(['success' => false, 'message' => 'Lookup failed.']);
    exit;
}

$rows = json_decode($resp, true);
if (!is_array($rows) || count($rows) === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Not found.']);
    exit;
}

echo json_encode([
    'success'     => true,
    'status'      => $rows[0]['status'] ?? 'pending',
    'preview_url' => $rows[0]['preview_url'] ?? null,
    'preview_slug' => $rows[0]['preview_slug'] ?? null,
]);
