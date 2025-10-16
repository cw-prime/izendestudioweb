<?php
/**
 * Contact Form Handler
 * Secured with CSRF protection, rate limiting, and input validation
 * Returns JSON responses for AJAX consumption
 */

// Set JSON content type header
header('Content-Type: application/json');

// Load security infrastructure
require_once __DIR__ . '/../config/env-loader.php';
require_once __DIR__ . '/../config/security.php';

// Initialize secure session
initSecureSession();

// 1. CSRF Token Validation
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    logSecurityEvent('csrf_validation_failed', [
        'form' => 'contact',
        'ip' => getClientIP()
    ], 'WARNING');
    die(json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh the page and try again.']));
}

// 2. Rate Limiting
$identifier = getClientIP() . '_contact_form';
if (!checkRateLimit($identifier, 5, 300)) {
    logSecurityEvent('rate_limit_exceeded', [
        'form' => 'contact',
        'ip' => getClientIP()
    ], 'WARNING');
    die(json_encode(['success' => false, 'message' => 'Too many requests. Please try again in 5 minutes.']));
}

// 3. Input Validation & Sanitization

// Name validation
$name = sanitizeInput($_POST['name'] ?? '', 'string');
if (empty($name) || !validateLength($name, 1, 100)) {
    die(json_encode(['success' => false, 'message' => 'Please enter a valid name (1-100 characters).']));
}

// Marketing consent (optional) - record user's opt-in/out if provided
$marketing_consent = 0;
if (isset($_POST['marketing_consent'])) {
    $val = $_POST['marketing_consent'];
    // Accept common checkbox values
    if ($val === '1' || $val === 'on' || $val === 'true' || $val === 'yes') {
        $marketing_consent = 1;
    }
}

// Privacy / required consent enforcement
$privacy_consent = false;
if (isset($_POST['consent'])) {
    $pc = $_POST['consent'];
    if ($pc === '1' || $pc === 'on' || $pc === 'true' || $pc === 'yes') {
        $privacy_consent = true;
    }
}
if (!$privacy_consent) {
    // Return JSON error for AJAX consumer
    echo json_encode(['success' => false, 'message' => 'You must agree to the Privacy Policy to submit this form.']);
    exit;
}

// Email validation
$emailInput = sanitizeInput($_POST['email'] ?? '', 'string');
$email = validateEmail($emailInput);
if ($email === false) {
    die(json_encode(['success' => false, 'message' => 'Please enter a valid email address.']));
}

// Subject validation
$subject = sanitizeInput($_POST['subject'] ?? '', 'string');
if (empty($subject) || !validateLength($subject, 1, 200)) {
    die(json_encode(['success' => false, 'message' => 'Please enter a valid subject (1-200 characters).']));
}

// Message validation
$message = sanitizeInput($_POST['message'] ?? '', 'string');
if (empty($message) || !validateLength($message, 10, 5000)) {
    die(json_encode(['success' => false, 'message' => 'Please enter a valid message (10-5000 characters).']));
}

// 4. reCAPTCHA Verification (if present)
if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
    $recaptchaSecret = getEnv('RECAPTCHA_SECRET_KEY');
    $recaptchaResponse = $_POST['g-recaptcha-response'];

    if (!empty($recaptchaSecret)) {
        // Verify reCAPTCHA
        $verifyURL = 'https://www.google.com/recaptcha/api/siteverify';
        $postData = [
            'secret' => $recaptchaSecret,
            'response' => $recaptchaResponse,
            'remoteip' => getClientIP()
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $verifyURL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            logSecurityEvent('recaptcha_verification_failed', [
                'form' => 'contact',
                'http_code' => $httpCode
            ], 'WARNING');
            die(json_encode(['success' => false, 'message' => 'Failed to verify reCAPTCHA. Please try again.']));
        } else {
            $result = json_decode($response, true);

            if (!isset($result['success']) || $result['success'] !== true) {
                logSecurityEvent('recaptcha_failed', [
                    'form' => 'contact',
                    'result' => $result
                ], 'WARNING');
                die(json_encode(['success' => false, 'message' => 'reCAPTCHA verification failed. Please try again.']));
            }
        }
    }
}

// 5. Load and use PHP Email Form library
$receiving_email_address = getEnv('MAIL_TO', 'support@izendestudioweb.com');

if (file_exists($php_email_form = '../assets/vendor/php-email-form/php-email-form.php')) {
    include($php_email_form);
} else {
    logSecurityEvent('php_email_form_missing', ['form' => 'contact'], 'CRITICAL');
    die(json_encode(['success' => false, 'message' => 'Unable to load the email system. Please contact support.']));
}

// Create and configure contact form
$contact = new PHP_Email_Form;
$contact->ajax = true;

$contact->to = $receiving_email_address;
$contact->from_name = $name;  // Already sanitized
$contact->from_email = $email;  // Already validated
$contact->subject = $subject;  // Already sanitized

// Configure reCAPTCHA if enabled
$recaptchaSecret = getEnv('RECAPTCHA_SECRET_KEY');
if (!empty($recaptchaSecret)) {
    $contact->recaptcha_secret_key = $recaptchaSecret;
}

// Optional SMTP configuration (uncomment and configure if needed)
/*
$contact->smtp = array(
    'host' => getEnv('SMTP_HOST', 'smtp.example.com'),
    'username' => getEnv('SMTP_USERNAME', ''),
    'password' => getEnv('SMTP_PASSWORD', ''),
    'port' => getEnv('SMTP_PORT', '587')
);
*/

// Add message fields (the library will perform additional sanitization)
$contact->add_message($name, 'From');
$contact->add_message($email, 'Email');
$contact->add_message($message, 'Message', 10);

// Include consent details for audit in the outgoing email
$contact->add_message($privacy_consent ? 'Yes' : 'No', 'Privacy Policy Consent');
$contact->add_message($marketing_consent ? 'Yes' : 'No', 'Marketing Consent');
$contact->add_message(date('c'), 'Consent Timestamp');
$contact->add_message(getClientIP(), 'IP Address');
$contact->add_message($_SERVER['HTTP_USER_AGENT'] ?? '', 'User-Agent');

// Send email and return JSON response
$result = $contact->send();

if ($result === 'OK' || strpos($result, 'OK') !== false) {
    logSecurityEvent('contact_form_submitted', [
        'name' => $name,
        'email' => $email,
        'subject' => $subject
    ], 'INFO');
    // Append consent audit to logs/consent.log
    $consentEntry = json_encode([
        'ts' => date('c'),
        'form' => 'contact',
        'name' => $name,
        'email' => $email,
        'marketing_consent' => (bool)$marketing_consent,
        'privacy_consent' => (bool)$privacy_consent,
        'ip' => getClientIP(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]) . PHP_EOL;
    $consentDir = __DIR__ . '/../logs';
    if (!is_dir($consentDir)) { @mkdir($consentDir, 0750, true); }
    @file_put_contents($consentDir . '/consent.log', $consentEntry, FILE_APPEND | LOCK_EX);
    echo json_encode(['success' => true, 'message' => 'Your message has been sent. Thank you!']);
} else {
    echo json_encode(['success' => false, 'message' => $result]);
}
?>
