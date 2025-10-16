<?php
// Simple consent logger for sendBeacon/fetch from front-end
header('Content-Type: application/json');
require_once __DIR__ . '/../config/env-loader.php';
require_once __DIR__ . '/../config/security.php';
initSecureSession();
setSecurityHeaders();

$raw = file_get_contents('php://input');
if (empty($raw)) {
    echo json_encode(['success' => false, 'message' => 'No payload']);
    exit;
}

// Try to parse JSON
$data = json_decode($raw, true);
if (!is_array($data)) {
    // Accept form-encoded as fallback
    parse_str($raw, $data);
}

$logdir = __DIR__ . '/../logs';
if (!is_dir($logdir)) { @mkdir($logdir, 0750, true); }
$file = $logdir . '/consent.log';
$entry = sprintf("%s\t%s\n", date('c'), json_encode($data));
@file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);

echo json_encode(['success' => true]);
exit;
