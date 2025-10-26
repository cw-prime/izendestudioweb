<?php
/**
 * Newsletter Signup API
 * Handles email newsletter subscriptions
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/env-loader.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../includes/SpamProtection.php';
require_once __DIR__ . '/../admin/config/database.php';

// Set security headers
setSecurityHeaders();

// Initialize session for spam protection
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Rate limiting
$identifier = ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . '_newsletter';
if (function_exists('checkRateLimit') && !checkRateLimit($identifier, 3, 300)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many requests. Please try again in 5 minutes.']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    // Fallback to form data
    $data = $_POST;
}

$email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
$firstName = htmlspecialchars($data['first_name'] ?? '', ENT_QUOTES, 'UTF-8');
$lastName = htmlspecialchars($data['last_name'] ?? '', ENT_QUOTES, 'UTF-8');
$source = htmlspecialchars($data['source'] ?? 'website', ENT_QUOTES, 'UTF-8');

// Validate email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Please provide a valid email address'
    ]);
    exit;
}

// Get IP and user agent
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

// Check if email already exists
$stmt = $conn->prepare("SELECT id, status FROM iz_newsletter_subscribers WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($existing = $result->fetch_assoc()) {
    if ($existing['status'] === 'active') {
        echo json_encode([
            'success' => true,
            'message' => 'You are already subscribed to our newsletter!'
        ]);
        exit;
    } elseif ($existing['status'] === 'unsubscribed') {
        // Resubscribe
        $stmt = $conn->prepare("UPDATE iz_newsletter_subscribers
            SET status = 'active', unsubscribed_at = NULL, subscribe_date = NOW()
            WHERE email = ?");
        $stmt->bind_param('s', $email);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Welcome back! You have been resubscribed to our newsletter.'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ]);
        }
        exit;
    }
}

// Insert new subscriber
$stmt = $conn->prepare("INSERT INTO iz_newsletter_subscribers
    (email, first_name, last_name, status, source, ip_address, user_agent, confirmed_at)
    VALUES (?, ?, ?, 'active', ?, ?, ?, NOW())");

$stmt->bind_param('ssssss', $email, $firstName, $lastName, $source, $ipAddress, $userAgent);

if ($stmt->execute()) {
    // Log the subscription
    $subscriberId = $conn->insert_id;

    // You could send a welcome email here (future enhancement)
    // sendWelcomeEmail($email, $firstName);

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for subscribing! You will receive our latest updates and tips.',
        'subscriber_id' => $subscriberId
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while subscribing. Please try again.'
    ]);
}
