<?php
// Minimal handler for Data Subject Requests and Do Not Sell submissions
require_once __DIR__ . '/../config/env-loader.php';
require_once __DIR__ . '/../config/security.php';
initSecureSession();
setSecurityHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

// Enforce CSRF
$csrf = $_POST['csrf_token'] ?? '';
if (empty($csrf) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
    // Invalid CSRF - reject
    header('Location: /data-subject-request.php?status=error&msg=csrf');
    exit;
}

// Normalize and validate inputs
$type = strtolower(trim($_POST['type'] ?? $_POST['request_type'] ?? 'dsr'));
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$fullname = trim($_POST['fullname'] ?? '');
$details = trim($_POST['details'] ?? '');

$allowed_types = ['do_not_sell','access','delete','rectify','portability','dsr'];
if (!in_array($type, $allowed_types, true)) { $type = 'dsr'; }

if (!$email) {
    header('Location: /data-subject-request.php?status=error&msg=invalid_email');
    exit;
}

// Sanitize fields for logging
$fullname_s = substr(preg_replace('/\s+/', ' ', strip_tags($fullname)), 0, 200);
$details_s = substr(preg_replace('/\s+/', ' ', strip_tags($details)), 0, 2000);

// Generate request ID
$requestId = bin2hex(random_bytes(8));

// Ensure logs directory exists and is not web-accessible ideally
$logdir = __DIR__ . '/../logs';
if (!is_dir($logdir)) { @mkdir($logdir, 0750, true); }
$logfile = $logdir . '/dsr.log';

// Gather metadata
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

$entry = sprintf("%s\tREQID:%s\tTYPE:%s\tEMAIL:%s\tNAME:%s\tIP:%s\tUA:%s\tDETAILS:%s\n",
    date('c'), $requestId, $type, $email, $fullname_s, $ip, substr($ua,0,200), $details_s
);
@file_put_contents($logfile, $entry, FILE_APPEND | LOCK_EX);

// Send notification to internal support
$internalTo = 'support@izendestudioweb.com';
$subject = sprintf('[DSR] %s request received (ID: %s)', strtoupper($type), $requestId);
$message = "Request ID: $requestId\nType: $type\nEmail: $email\nName: $fullname_s\nIP: $ip\nUser-Agent: $ua\nDetails: $details_s\nReceived: " . date('c') . "\n";
@mail($internalTo, $subject, $message);

// Send confirmation to requester (best-effort)
$confirmSubj = 'Your Data Subject Request has been received';
$confirmMsg = "We have received your request (ID: $requestId). We will respond within the timeframe required by applicable law.\n\nRequest summary:\n$type\n\nIf you did not submit this request, contact us at support@izendestudioweb.com.\n";
@mail($email, $confirmSubj, $confirmMsg);

// Redirect back with success and request id
header('Location: /data-subject-request.php?status=success&request_id=' . urlencode($requestId));
exit;
