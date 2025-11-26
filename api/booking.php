<?php
/**
 * Simplified Booking API
 * Validates input, inserts into iz_bookings, and logs all errors.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/env-loader.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../includes/SpamProtection.php';
require_once __DIR__ . '/../admin/config/database.php';

/**
 * Write debug info to error_log and a temp file.
 */
function bookingLog($message, $context = []) {
    $entry = date('Y-m-d H:i:s') . ' ' . $message;
    if (!empty($context)) {
        $entry .= ' ' . json_encode($context);
    }

    error_log($entry);

    $tmp = sys_get_temp_dir() . '/booking-debug.log';
    @file_put_contents($tmp, $entry . PHP_EOL, FILE_APPEND | LOCK_EX);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Rate limiting: 3 attempts per 10 minutes per IP
$identifier = ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . '_booking_new';
if (function_exists('checkRateLimit') && !checkRateLimit($identifier, 3, 600)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many booking attempts. Please try again in 10 minutes.']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    bookingLog('Invalid JSON payload', ['raw_length' => strlen($raw)]);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request payload.']);
    exit;
}

// Extract and validate fields
$name = trim($data['client_name'] ?? '');
$email = filter_var($data['client_email'] ?? '', FILTER_SANITIZE_EMAIL);
$service = trim($data['service_type'] ?? '');
$preferredRaw = trim($data['preferred_date'] ?? '');
$phone = trim($data['client_phone'] ?? '');
$message = trim($data['message'] ?? '');
$duration = isset($data['duration']) ? (int)$data['duration'] : 30;

if ($name === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Full Name is required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Valid email is required.']);
    exit;
}

if ($service === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Service selection is required.']);
    exit;
}

// Spam checks (honeypot/timing/content/IP)
$spamCheck = SpamProtection::validateSubmission('booking_new', $data, [
    'check_honeypot' => isset($data['form_timestamp']),
    'check_timing' => isset($data['form_timestamp']),
    'check_ip' => true,
    'check_content' => true,
    'min_seconds' => 5,
    'max_seconds' => 1800
]);

if ($spamCheck['is_spam']) {
    bookingLog('Spam blocked booking', ['reason' => $spamCheck['reason'], 'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Your submission was flagged as spam. Please call us directly if this is a mistake.']);
    exit;
}

$preferredTs = strtotime($preferredRaw);
if ($preferredTs === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Preferred date/time is invalid.']);
    exit;
}

// Require booking time to be in the future (5 minutes buffer)
if ($preferredTs < (time() + 300)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please choose a future date/time.']);
    exit;
}

$preferredDate = date('Y-m-d H:i:s', $preferredTs);

// Insert booking
$stmt = $conn->prepare("INSERT INTO iz_bookings
    (client_name, client_email, client_phone, service_type, preferred_date, duration, message, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");

if (!$stmt) {
    $err = $conn->error;
    bookingLog('Prepare failed', ['error' => $err]);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to save booking right now: ' . $err]);
    exit;
}

$stmt->bind_param(
    'sssssis',
    $name,
    $email,
    $phone,
    $service,
    $preferredDate,
    $duration,
    $message
);

if ($stmt->execute()) {
    $bookingId = $conn->insert_id;
    // Fire admin notification (best-effort; errors are logged but won't block response)
    sendBookingNotification([
        'id' => $bookingId,
        'client_name' => $name,
        'client_email' => $email,
        'client_phone' => $phone,
        'service_type' => $service,
        'preferred_date' => $preferredDate,
        'duration' => $duration,
        'message' => $message
    ]);

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Consultation booked successfully! We will confirm shortly.',
        'booking_id' => $bookingId
    ]);
} else {
    $err = $stmt->error;
    bookingLog('Insert failed', ['error' => $err]);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to save booking right now: ' . $err]);
}

$stmt->close();

/**
 * Send admin notification email about a new booking (best-effort).
 */
function sendBookingNotification($booking) {
    $emailLib = __DIR__ . '/../assets/vendor/php-email-form/php-email-form.php';
    if (!file_exists($emailLib)) {
        bookingLog('Email library missing', []);
        return;
    }
    include $emailLib;

    $mailer = new PHP_Email_Form;
    $mailer->ajax = false; // internal call; no AJAX header available

    // SMTP configuration
    $mailer->smtp = array(
        'host' => getEnv('SMTP_HOST', 'mail.izendestudioweb.com'),
        'username' => getEnv('SMTP_USERNAME', 'info@izendestudioweb.com'),
        'password' => getEnv('SMTP_PASSWORD', ''),
        'port' => getEnv('SMTP_PORT', '465'),
        'encryption' => 'ssl'
    );

    $adminTo = getEnv('BOOKING_ADMIN_EMAIL', 'info@izendestudioweb.com');
    $fromEmail = getEnv('BOOKING_FROM_EMAIL', 'info@izendestudioweb.com');
    $fromName = getEnv('BOOKING_FROM_NAME', 'Izende Studio Web');

    $mailer->to = $adminTo;
    $mailer->from_name = $fromName;
    $mailer->from_email = $fromEmail;
    $mailer->subject = $booking['client_name'] . ' Has Booked a Consultation';

    $mailer->add_message($booking['client_name'], 'Client Name');
    $mailer->add_message($booking['client_email'], 'Client Email');
    $mailer->add_message($booking['client_phone'], 'Client Phone');
    $mailer->add_message($booking['service_type'], 'Service Type');
    $mailer->add_message($booking['preferred_date'], 'Preferred Date/Time');
    $mailer->add_message($booking['duration'] . ' minutes', 'Duration');
    $mailer->add_message($booking['message'], 'Project Details', 10);

    $result = $mailer->send();
    if ($result !== 'OK' && strpos($result, 'OK') === false) {
        bookingLog('Booking email failed', ['result' => $result]);
    } else {
        bookingLog('Booking email sent', ['to' => $adminTo]);
    }
}
