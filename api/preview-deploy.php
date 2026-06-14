<?php
/**
 * AI Website Builder — Preview Deploy Receiver
 *
 * Server-to-server endpoint called by the n8n generation workflow. n8n POSTs the
 * AI-generated HTML for a lead; this writes it to the preview directory on the
 * (cPanel) host and returns the public preview URL. No browser session/CSRF —
 * auth is a shared secret (n8n stores it as a credential).
 *
 * Request (JSON):  { "lead_id": "<uuid>", "html": "<!DOCTYPE html>..." }
 * Auth:            header  X-Deploy-Token: <PREVIEW_DEPLOY_SECRET>   (or body "secret")
 * Response (JSON): { "success": true, "preview_url": "https://preview…/<lead_id>/" }
 *
 * Env:
 *   PREVIEW_DEPLOY_SECRET  required — shared secret
 *   PREVIEW_DEPLOY_DIR     filesystem root for previews (default: <repo>/previews)
 *   PREVIEW_BASE_URL       public base URL (default: http://localhost/izendestudioweb/previews)
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/env-loader.php';

function deployLog($message, $context = []) {
    $entry = date('Y-m-d H:i:s') . ' ' . $message;
    if (!empty($context)) {
        $entry .= ' ' . json_encode($context);
    }
    error_log($entry);
    $dir = dirname(__DIR__) . '/logs';
    if (!is_dir($dir)) { @mkdir($dir, 0750, true); }
    @file_put_contents($dir . '/preview-deploy.log', $entry . PHP_EOL, FILE_APPEND | LOCK_EX);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$secret = trim((string)getEnv('PREVIEW_DEPLOY_SECRET', ''));
if ($secret === '') {
    deployLog('PREVIEW_DEPLOY_SECRET not configured');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Deploy endpoint not configured.']);
    exit;
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON payload.']);
    exit;
}

// Auth — shared secret via header or body, constant-time compare
$provided = $_SERVER['HTTP_X_DEPLOY_TOKEN'] ?? ($data['secret'] ?? '');
if (!is_string($provided) || !hash_equals($secret, $provided)) {
    deployLog('Bad deploy token', ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden.']);
    exit;
}

// Validate lead_id strictly (UUID) — this becomes a directory name, so no traversal
$leadId = (string)($data['lead_id'] ?? '');
if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $leadId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid lead_id.']);
    exit;
}

$html = (string)($data['html'] ?? '');

// Defensive: strip stray markdown code fences the model may have wrapped around the HTML
$html = preg_replace('/^\s*```[a-zA-Z]*\s*\n/', '', $html);
$html = preg_replace('/\n```\s*$/', '', $html);
$html = trim($html);

if (strlen($html) < 60 || stripos($html, '<html') === false) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Payload does not look like a complete HTML document.']);
    exit;
}
if (strlen($html) > 3_000_000) { // 3 MB ceiling
    http_response_code(413);
    echo json_encode(['success' => false, 'message' => 'Generated site is too large.']);
    exit;
}

// Resolve targets
// Do not rely on getEnv() default arguments here: PHP function names are case-insensitive,
// so calls to getEnv() may resolve to PHP's built-in getenv(), whose second parameter is
// local_only (bool), not a fallback default.
$deployRoot = getEnv('PREVIEW_DEPLOY_DIR');
if (!$deployRoot) {
    $deployRoot = dirname(__DIR__) . '/previews';
}
$deployRoot = rtrim((string)$deployRoot, '/');

$baseUrl = getEnv('PREVIEW_BASE_URL');
if (!$baseUrl) {
    $baseUrl = 'http://localhost/izendestudioweb/previews';
}
$baseUrl = rtrim((string)$baseUrl, '/');

$targetDir = $deployRoot . '/' . $leadId;
if (!is_dir($targetDir) && !@mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
    deployLog('mkdir failed', ['dir' => $targetDir]);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not create preview directory.']);
    exit;
}

// Write atomically: temp file + rename
$finalPath = $targetDir . '/index.html';
$tmpPath   = $targetDir . '/.index.' . bin2hex(random_bytes(4)) . '.tmp';
if (@file_put_contents($tmpPath, $html, LOCK_EX) === false || !@rename($tmpPath, $finalPath)) {
    @unlink($tmpPath);
    deployLog('write failed', ['path' => $finalPath]);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not write preview file.']);
    exit;
}

$previewUrl = $baseUrl . '/' . $leadId . '/';
deployLog('Preview deployed', ['lead_id' => $leadId, 'bytes' => strlen($html), 'url' => $previewUrl]);

http_response_code(200);
echo json_encode(['success' => true, 'preview_url' => $previewUrl]);
