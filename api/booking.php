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

/**
 * Best-effort: send booking details to a GoHighLevel Workflow Webhook trigger.
 *
 * Configure in `.env`:
 * - GHL_WORKFLOW_WEBHOOK_URL=https://... (from the workflow trigger)
 */
function sendGhlWorkflowWebhook(array $booking) {
    $webhookUrl = getEnv('GHL_WORKFLOW_WEBHOOK_URL');
    if (!$webhookUrl) {
        return;
    }

    $timezoneName = (string)getEnv('BOOKING_TIMEZONE', (string)getEnv('GOOGLE_CALENDAR_TIMEZONE', 'America/Chicago'));
    $bookingPreferred = (string)($booking['preferred_date'] ?? '');
    $bookingPreferredTime = (string)($booking['preferred_time'] ?? '');
    $preferredIso = $bookingPreferred;
    $preferredGhl = $bookingPreferred;
    try {
        if ($bookingPreferred !== '') {
            $tz = new DateTimeZone($timezoneName ?: 'America/Chicago');
            // Treat stored preferred_date as local time in $timezoneName
            $dt = new DateTime($bookingPreferred, $tz);
            $preferredIso = $dt->format(DateTimeInterface::ATOM); // ISO8601 w/ offset
            $preferredGhl = $dt->format('m-d-Y g:i A'); // GHL "Book Appointment" friendly
            if ($bookingPreferredTime === '') {
                $bookingPreferredTime = $dt->format('g:i A');
            }
        }
    } catch (Throwable $e) {
        // Fall back to raw string if parsing fails
    }

    $payload = [
        'event' => 'booking_created',
        'source' => 'izendestudioweb',
        'booking' => [
            'id' => (int)($booking['id'] ?? 0),
            'client_name' => (string)($booking['client_name'] ?? ''),
            'client_email' => (string)($booking['client_email'] ?? ''),
            'client_phone' => (string)($booking['client_phone'] ?? ''),
            'service_type' => (string)($booking['service_type'] ?? ''),
            'preferred_date' => $bookingPreferred,
            'preferred_time' => $bookingPreferredTime,
            // Use this for HighLevel "Book Appointment" action input
            'preferred_date_ghl' => $preferredGhl,
            // ISO8601 w/ offset (useful for logs, integrations, and debugging)
            'preferred_date_iso' => $preferredIso,
            'duration' => (int)($booking['duration'] ?? 0),
            'message' => (string)($booking['message'] ?? ''),
            'status' => (string)($booking['status'] ?? ''),
        ],
        'meta' => [
            'submitted_at' => gmdate('c'),
            'ip' => (string)($_SERVER['REMOTE_ADDR'] ?? ''),
            'user_agent' => (string)($_SERVER['HTTP_USER_AGENT'] ?? ''),
            'page_url' => (string)($_SERVER['HTTP_REFERER'] ?? ''),
            'timezone' => $timezoneName,
        ],
    ];

    $timeoutSeconds = (int)getEnv('GHL_WEBHOOK_TIMEOUT_SECONDS', 4);
    $timeoutSeconds = max(1, min(15, $timeoutSeconds));

    try {
        $json = json_encode($payload);
        if ($json === false) {
            bookingLog('GHL webhook payload json_encode failed', ['error' => json_last_error_msg()]);
            return;
        }

        // Prefer cURL when available.
        if (function_exists('curl_init')) {
            $ch = curl_init($webhookUrl);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_POSTFIELDS => $json,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => $timeoutSeconds,
                CURLOPT_TIMEOUT => $timeoutSeconds,
            ]);

            $responseBody = curl_exec($ch);
            $curlErr = curl_error($ch);
            $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);

            if ($responseBody === false) {
                bookingLog('GHL webhook cURL failed', ['error' => $curlErr ?: 'unknown']);
                return;
            }

            if ($status < 200 || $status >= 300) {
                bookingLog('GHL webhook non-2xx response', ['status' => $status, 'body' => substr((string)$responseBody, 0, 500)]);
                return;
            }

            bookingLog('GHL webhook delivered', ['status' => $status]);
            return;
        }

        // Fallback to stream context if cURL isn't installed.
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
                'content' => $json,
                'timeout' => $timeoutSeconds,
                'ignore_errors' => true,
            ],
        ]);

        $responseBody = @file_get_contents($webhookUrl, false, $context);
        $status = 0;
        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $headerLine) {
                if (preg_match('/^HTTP\\/\\S+\\s+(\\d{3})\\b/', $headerLine, $m)) {
                    $status = (int)$m[1];
                    break;
                }
            }
        }

        if ($responseBody === false) {
            bookingLog('GHL webhook HTTP request failed', ['status' => $status]);
            return;
        }

        if ($status && ($status < 200 || $status >= 300)) {
            bookingLog('GHL webhook non-2xx response', ['status' => $status, 'body' => substr((string)$responseBody, 0, 500)]);
            return;
        }

        bookingLog('GHL webhook delivered', ['status' => $status ?: null]);

    } catch (Throwable $e) {
        bookingLog('GHL webhook exception', ['error' => $e->getMessage()]);
    }
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
// Keep the user's time selection for downstream systems (HighLevel custom fields, etc.)
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
    sendGhlWorkflowWebhook($bookingPayload);

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
