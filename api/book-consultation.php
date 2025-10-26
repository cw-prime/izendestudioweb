<?php
/**
 * Book Consultation API
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/env-loader.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../includes/SpamProtection.php';
require_once __DIR__ . '/../admin/config/database.php';

setSecurityHeaders();

// Initialize session for spam protection
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Rate limiting
$identifier = ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . '_booking';
if (function_exists('checkRateLimit') && !checkRateLimit($identifier, 3, 600)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many booking attempts. Please try again in 10 minutes.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Spam protection validation (honeypot + timing + IP + content)
if (is_array($data)) {
    $spamCheck = SpamProtection::validateSubmission('booking', $data, [
        'check_honeypot' => isset($data['form_timestamp']),
        'check_timing' => isset($data['form_timestamp']),
        'check_ip' => true,
        'check_content' => true,
        'min_seconds' => 5,
        'max_seconds' => 1800
    ]);

    if ($spamCheck['is_spam']) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Your submission was flagged as spam. If this is an error, please call us directly.'
        ]);
        exit;
    }
}

// Validate required fields
$required = ['client_name', 'client_email', 'service_type', 'preferred_date'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

// Sanitize inputs
$client_name = htmlspecialchars($data['client_name'], ENT_QUOTES, 'UTF-8');
$client_email = filter_var($data['client_email'], FILTER_SANITIZE_EMAIL);
$client_phone = htmlspecialchars($data['client_phone'] ?? '', ENT_QUOTES, 'UTF-8');
$service_type = htmlspecialchars($data['service_type'], ENT_QUOTES, 'UTF-8');
$preferred_date = $data['preferred_date'];
$message = htmlspecialchars($data['message'] ?? '', ENT_QUOTES, 'UTF-8');

// Validate email
if (!filter_var($client_email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Check if time slot is available
$check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM iz_bookings
    WHERE DATE(preferred_date) = DATE(?)
    AND TIME(preferred_date) = TIME(?)
    AND status IN ('pending', 'confirmed')");
$check_stmt->bind_param('ss', $preferred_date, $preferred_date);
$check_stmt->execute();
$result = $check_stmt->get_result()->fetch_assoc();

if ($result['count'] > 0) {
    http_response_code(409);
    echo json_encode([
        'success' => false,
        'message' => 'This time slot is already booked. Please choose another time.'
    ]);
    exit;
}

// Insert booking
$ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
$duration = 30; // 30 minute consultation

$stmt = $conn->prepare("INSERT INTO iz_bookings
    (client_name, client_email, client_phone, service_type, preferred_date, duration, message, status, ip_address)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?)");

$stmt->bind_param('sssssiss',
    $client_name,
    $client_email,
    $client_phone,
    $service_type,
    $preferred_date,
    $duration,
    $message,
    $ip_address
);

if ($stmt->execute()) {
    $booking_id = $conn->insert_id;

    // TODO: Send confirmation email to client
    // TODO: Send notification email to admin
    // TODO: Create Google Calendar event (we'll implement this next)

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Consultation booked successfully! You will receive a confirmation email shortly.',
        'booking_id' => $booking_id
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while booking. Please try again or call us directly.'
    ]);
}
