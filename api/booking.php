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

initSecureSession();

/**
 * Write debug info to error_log and local log files.
 */
function bookingLog($message, $context = []) {
    $entry = date('Y-m-d H:i:s') . ' ' . $message;
    if (!empty($context)) {
        $entry .= ' ' . json_encode($context);
    }

    error_log($entry);

    $appLogDir = dirname(__DIR__) . '/logs';
    if (!is_dir($appLogDir)) {
        @mkdir($appLogDir, 0750, true);
    }
    $appLogFile = $appLogDir . '/booking.log';
    @file_put_contents($appLogFile, $entry . PHP_EOL, FILE_APPEND | LOCK_EX);

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

if (!validateCSRFToken($data['csrf_token'] ?? '')) {
    bookingLog('Invalid CSRF token on booking submission', ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Your session expired. Please refresh the page and try again.']);
    exit;
}

$recaptchaSecret = trim((string)getEnv('RECAPTCHA_SECRET_KEY', ''));
$recaptchaResponse = trim((string)($data['g-recaptcha-response'] ?? ''));

if ($recaptchaSecret !== '') {
    if ($recaptchaResponse === '') {
        bookingLog('Missing reCAPTCHA token on booking submission', ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Please complete the reCAPTCHA challenge.']);
        exit;
    }

    if (!function_exists('curl_init')) {
        bookingLog('Cannot verify reCAPTCHA: cURL missing', []);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Unable to verify reCAPTCHA right now. Please try again later.']);
        exit;
    }

    $verifyURL = 'https://www.google.com/recaptcha/api/siteverify';
    $postData = [
        'secret' => $recaptchaSecret,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $verifyURL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $verifyResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($verifyResponse === false || $httpCode !== 200) {
        bookingLog('reCAPTCHA verification request failed', ['http_code' => $httpCode]);
        http_response_code(502);
        echo json_encode(['success' => false, 'message' => 'Failed to verify reCAPTCHA. Please try again.']);
        exit;
    }

    $verifyResult = json_decode($verifyResponse, true);
    if (!isset($verifyResult['success']) || $verifyResult['success'] !== true) {
        bookingLog('reCAPTCHA verification rejected booking', ['result' => $verifyResult]);
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'reCAPTCHA verification failed. Please try again.']);
        exit;
    }
}

// Extract and validate fields
$name = trim($data['client_name'] ?? '');
$email = filter_var($data['client_email'] ?? '', FILTER_SANITIZE_EMAIL);
$service = trim($data['service_type'] ?? '');
$preferredDateRaw = trim($data['preferred_date'] ?? '');
$preferredTimeRaw = trim($data['preferred_time'] ?? '');
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
    'check_honeypot' => true,
    'check_timing' => true,
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

// Allow either:
// - preferred_date = "YYYY-MM-DD HH:MM:SS" (legacy)
// - preferred_date = "YYYY-MM-DD" and preferred_time = "HH:MM" (new)
$preferredCombined = $preferredDateRaw;
if ($preferredTimeRaw !== '' && preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $preferredDateRaw)) {
    // Normalize "HH:MM" to "HH:MM:SS"
    $timePart = preg_match('/^\\d{2}:\\d{2}$/', $preferredTimeRaw) ? ($preferredTimeRaw . ':00') : $preferredTimeRaw;
    $preferredCombined = $preferredDateRaw . ' ' . $timePart;
}

$preferredTs = strtotime($preferredCombined);
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
// Keep a readable local-time slot string for internal notifications.
$preferredTime = '';
try {
    $preferredTime = date('g:i A', $preferredTs);
} catch (Throwable $e) {
    $preferredTime = $preferredTimeRaw;
}

// Insert booking
$stmt = $conn->prepare("INSERT INTO iz_bookings
    (client_name, client_email, client_phone, service_type, preferred_date, duration, message, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");

if (!$stmt) {
    $err = $conn->error;
    bookingLog('Prepare failed', ['error' => $err]);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to save booking right now. Please try again later.']);
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
    $bookingPayload = [
        'id' => $bookingId,
        'client_name' => $name,
        'client_email' => $email,
        'client_phone' => $phone,
        'service_type' => $service,
        'preferred_date' => $preferredDate,
        'preferred_time' => $preferredTime,
        'duration' => $duration,
        'message' => $message,
        'status' => 'pending'
    ];

    sendBookingNotification($bookingPayload);

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
    echo json_encode(['success' => false, 'message' => 'Unable to save booking right now. Please try again later.']);
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
    include_once $emailLib;

    $mailer = new PHP_Email_Form;
    $mailer->ajax = false; // internal call; no AJAX header available

    // All email configuration must come from environment variables — no hardcoded defaults.
    // Required: BOOKING_ADMIN_EMAIL (or MAIL_TO), BOOKING_FROM_EMAIL (or MAIL_FROM).
    // Optional SMTP: all four vars (SMTP_HOST, SMTP_USERNAME, SMTP_PASSWORD, SMTP_PORT)
    //   must be set together — partial config is treated as misconfiguration, not a
    //   silent fallback, so operators are aware of the issue at startup.
    $adminTo = trim((string)(getenv('BOOKING_ADMIN_EMAIL') ?: getenv('MAIL_TO') ?: ''));
    $fromEmail = trim((string)(getenv('BOOKING_FROM_EMAIL') ?: getenv('MAIL_FROM') ?: ''));
    $fromName = getenv('BOOKING_FROM_NAME') ?: 'Izende Studio Web';
    $smtpHost     = trim((string)(getenv('SMTP_HOST')     ?: ''));
    $smtpUsername = trim((string)(getenv('SMTP_USERNAME') ?: ''));
    $smtpPassword = trim((string)(getenv('SMTP_PASSWORD') ?: ''));
    $smtpPortRaw  = getenv('SMTP_PORT');
    $smtpPort     = ($smtpPortRaw !== false && $smtpPortRaw !== '') ? (int)$smtpPortRaw : 0;

    // SMTP is all-or-nothing: any subset of vars present without the full set is logged.
    $smtpPresent = array_filter([
        'SMTP_HOST'     => $smtpHost,
        'SMTP_USERNAME' => $smtpUsername,
        'SMTP_PASSWORD' => $smtpPassword,
        'SMTP_PORT'     => $smtpPort > 0 ? (string)$smtpPort : '',
    ]);
    $hasSmtp = (count($smtpPresent) === 4);
    if (!$hasSmtp && !empty($smtpPresent)) {
        // Some SMTP vars are set but not all — warn to surface misconfiguration.
        $missing = array_keys(array_diff_key(
            ['SMTP_HOST' => '', 'SMTP_USERNAME' => '', 'SMTP_PASSWORD' => '', 'SMTP_PORT' => ''],
            $smtpPresent
        ));
        bookingLog('SMTP config incomplete — falling back to php mail(). Missing: ' . implode(', ', $missing), [
            'present' => array_keys($smtpPresent),
            'missing' => $missing
        ]);
    }

    if ($adminTo === '' || !filter_var($adminTo, FILTER_VALIDATE_EMAIL)) {
        bookingLog('Booking email skipped: BOOKING_ADMIN_EMAIL / MAIL_TO env var missing or invalid', ['admin_to' => $adminTo]);
        return;
    }

    if ($fromEmail === '' || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
        bookingLog('Booking email skipped: BOOKING_FROM_EMAIL / MAIL_FROM env var missing or invalid', ['from_email' => $fromEmail]);
        return;
    }

    if ($hasSmtp) {
        $mailer->smtp = array(
            'host' => $smtpHost,
            'username' => $smtpUsername,
            'password' => $smtpPassword,
            'port' => (string)$smtpPort,
            'encryption' => ($smtpPort === 465 ? 'ssl' : 'tls')
        );
    }

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
    $ok = ($result === 'OK' || strpos((string)$result, 'OK') !== false);

    if (!$ok && $hasSmtp) {
        // Retry once without SMTP using server mail transport.
        $fallback = new PHP_Email_Form;
        $fallback->ajax = false;
        $fallback->to = $adminTo;
        $fallback->from_name = $fromName;
        $fallback->from_email = $fromEmail;
        $fallback->subject = $booking['client_name'] . ' Has Booked a Consultation';
        $fallback->add_message($booking['client_name'], 'Client Name');
        $fallback->add_message($booking['client_email'], 'Client Email');
        $fallback->add_message($booking['client_phone'], 'Client Phone');
        $fallback->add_message($booking['service_type'], 'Service Type');
        $fallback->add_message($booking['preferred_date'], 'Preferred Date/Time');
        $fallback->add_message($booking['duration'] . ' minutes', 'Duration');
        $fallback->add_message($booking['message'], 'Project Details', 10);

        $fallbackResult = $fallback->send();
        $fallbackOk = ($fallbackResult === 'OK' || strpos((string)$fallbackResult, 'OK') !== false);

        if ($fallbackOk) {
            bookingLog('Booking email sent', ['to' => $adminTo, 'transport' => 'php-mail-fallback']);
            return;
        }

        bookingLog('Booking email failed', [
            'transport' => 'smtp+fallback',
            'smtp_result' => (string)$result,
            'fallback_result' => (string)$fallbackResult,
            'to' => $adminTo
        ]);
        return;
    }

    if ($ok) {
        bookingLog('Booking email sent', ['to' => $adminTo, 'transport' => $hasSmtp ? 'smtp' : 'php-mail']);
    } else {
        bookingLog('Booking email failed', ['transport' => $hasSmtp ? 'smtp' : 'php-mail', 'result' => (string)$result, 'to' => $adminTo]);
    }
}
