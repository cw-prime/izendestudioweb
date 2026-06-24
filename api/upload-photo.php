<?php
/**
 * Site Drafter — customer photo upload (Step 2 helper).
 *
 * Accepts optional content photos, validates them strictly, stores them under
 * /genmedia/uploads/, and returns a URL that rides along with the lead submission.
 *
 * POST multipart: photo=<file>, csrf_token=<token> -> { success, url, width, height }
 */
require_once __DIR__ . '/../config/env-loader.php';
require_once __DIR__ . '/../config/security.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');
initSecureSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (function_exists('checkRateLimit') && !checkRateLimit($ip . '_photo_upload', 15, 600)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many photo uploads — give it a minute.']);
    exit;
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Session expired — please refresh the page.']);
    exit;
}

if (empty($_FILES['photo']) || ($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No photo received.']);
    exit;
}

$f = $_FILES['photo'];
if ($f['size'] > 10 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'That photo is over 10 MB — please upload a smaller one.']);
    exit;
}

if (!is_uploaded_file($f['tmp_name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Upload failed.']);
    exit;
}

$allowed = [IMAGETYPE_PNG => 'png', IMAGETYPE_JPEG => 'jpg', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];
$info = @getimagesize($f['tmp_name']);
$mimeOk = ['image/png' => 1, 'image/jpeg' => 1, 'image/gif' => 1, 'image/webp' => 1];
$finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : null;
$mime = $finfo ? finfo_file($finfo, $f['tmp_name']) : ($info['mime'] ?? '');
if ($finfo) { finfo_close($finfo); }
if ($info === false || !isset($allowed[$info[2]]) || empty($mimeOk[$mime])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please upload a PNG, JPG, WEBP, or GIF photo.']);
    exit;
}

$width = (int) ($info[0] ?? 0);
$height = (int) ($info[1] ?? 0);
if (max($width, $height) < 1000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please upload a larger photo — at least 1000px on the longest side.']);
    exit;
}

$uploadsDir = dirname(__DIR__) . '/genmedia/uploads';
if (!is_dir($uploadsDir)) { @mkdir($uploadsDir, 0755, true); }
if (!is_dir($uploadsDir) || !is_writable($uploadsDir)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not store the photo right now.']);
    exit;
}

$ext = $allowed[$info[2]];
$name = 'photo-' . bin2hex(random_bytes(8)) . '.' . $ext;
if (!move_uploaded_file($f['tmp_name'], $uploadsDir . '/' . $name)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not save the photo.']);
    exit;
}

echo json_encode([
    'success' => true,
    'url' => 'https://izendestudioweb.com/genmedia/uploads/' . $name,
    'width' => $width,
    'height' => $height,
]);
