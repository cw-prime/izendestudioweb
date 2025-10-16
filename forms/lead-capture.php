<?php
/**
 * Lead Capture Form Handler with CSRF Protection
 * Handles exit-intent lead magnet form submissions with email marketing integration
 */

// Load security infrastructure
require_once __DIR__ . '/../config/env-loader.php';
require_once __DIR__ . '/../config/security.php';

// Initialize secure session and set security headers
initSecureSession();
setSecurityHeaders();

// Set JSON content type
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    logSecurityEvent('invalid_request_method', ['method' => $_SERVER['REQUEST_METHOD'], 'form' => 'lead_capture'], 'WARNING');
    exit;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh and try again.']);
    logSecurityEvent('csrf_validation_failed', ['form' => 'lead_capture', 'ip' => getClientIP()], 'WARNING');
    exit;
}

// Rate limiting check using security helper (5 attempts per 5 minutes)
$identifier = getClientIP() . '_lead_capture';
if (!checkRateLimit($identifier, 5, 300)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many requests. Please try again in a few minutes.']);
    logSecurityEvent('rate_limit_exceeded', ['form' => 'lead_capture', 'ip' => getClientIP()], 'WARNING');
    exit;
}

// Sanitize and validate input using security helpers
$name = sanitizeInput($_POST['name'] ?? '', 'string');
$emailInput = sanitizeInput($_POST['email'] ?? '', 'string');
$email = validateEmail($emailInput);

// Validation
$errors = [];

if (empty($name) || !validateLength($name, 1, 100)) {
    $errors[] = 'Please enter a valid name (1-100 characters)';
}

if ($email === false) {
    $errors[] = 'Please enter a valid email address';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// =============================================================================
// EMAIL MARKETING INTEGRATION (Mailchimp/ConvertKit)
// =============================================================================

$emailMarketingSuccess = false;

// Mailchimp integration (if API key exists)
$mailchimpApiKey = getEnv('MAILCHIMP_API_KEY');
$mailchimpListId = getEnv('MAILCHIMP_LIST_ID');

if (!empty($mailchimpApiKey) && !empty($mailchimpListId)) {
    try {
        // Extract datacenter from API key (e.g., us1, us2, etc.)
        $datacenter = substr($mailchimpApiKey, strpos($mailchimpApiKey, '-') + 1);
        $url = "https://{$datacenter}.api.mailchimp.com/3.0/lists/{$mailchimpListId}/members";

        $data = [
            'email_address' => $email,
            'status' => 'subscribed',
            'merge_fields' => [
                'FNAME' => $name
            ],
            'tags' => ['Lead Magnet']
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $mailchimpApiKey);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $result = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode === 200) {
            $emailMarketingSuccess = true;
            logSecurityEvent('mailchimp_subscription_success', ['email' => $email, 'name' => $name], 'INFO');
        } else {
            logSecurityEvent('mailchimp_subscription_failed', ['email' => $email, 'status' => $statusCode], 'WARNING');
        }
    } catch (Exception $e) {
        logSecurityEvent('mailchimp_error', ['error' => $e->getMessage()], 'WARNING');
    }
}

// ConvertKit integration (if API key exists and Mailchimp not used)
$convertkitApiKey = getEnv('CONVERTKIT_API_KEY');
$convertkitFormId = getEnv('CONVERTKIT_FORM_ID');

if (!$emailMarketingSuccess && !empty($convertkitApiKey) && !empty($convertkitFormId)) {
    try {
        $url = "https://api.convertkit.com/v3/forms/{$convertkitFormId}/subscribe";

        $data = [
            'api_key' => $convertkitApiKey,
            'email' => $email,
            'first_name' => $name,
            'tags' => ['Lead Magnet']
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $result = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode === 200) {
            $emailMarketingSuccess = true;
            logSecurityEvent('convertkit_subscription_success', ['email' => $email, 'name' => $name], 'INFO');
        } else {
            logSecurityEvent('convertkit_subscription_failed', ['email' => $email, 'status' => $statusCode], 'WARNING');
        }
    } catch (Exception $e) {
        logSecurityEvent('convertkit_error', ['error' => $e->getMessage()], 'WARNING');
    }
}

// =============================================================================
// SEND TRANSACTIONAL EMAIL WITH DOWNLOAD LINKS
// =============================================================================

$clientIP = getClientIP();
$downloadBaseUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'izendestudioweb.com');

// Define download asset URLs (centralized for easy updates)
$websiteLaunchChecklistUrl = $downloadBaseUrl . '/downloads/website-launch-checklist.pdf';
$seoAuditTemplateUrl = $downloadBaseUrl . '/downloads/seo-audit-template.xlsx';
$hostingComparisonGuideUrl = $downloadBaseUrl . '/downloads/hosting-comparison-guide.pdf';

// Send notification email to admin
$adminEmail = getEnv('MAIL_TO', 'support@izendestudioweb.com');
$adminSubject = 'New Lead Magnet Download Request';

$adminMessage = "New lead magnet download request:\n\n";
$adminMessage .= "Name: {$name}\n";
$adminMessage .= "Email: {$email}\n";
$adminMessage .= "IP Address: {$clientIP}\n";
$adminMessage .= "User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') . "\n";
$adminMessage .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
$adminMessage .= "Email Marketing: " . ($emailMarketingSuccess ? 'Subscribed' : 'Not subscribed (check API keys)') . "\n";

$adminHeaders = [
    'From: Izende Studio Web <noreply@izendestudioweb.com>',
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . phpversion(),
    'Content-Type: text/plain; charset=UTF-8'
];

mail($adminEmail, $adminSubject, $adminMessage, implode("\r\n", $adminHeaders));

// Send welcome email to subscriber with download links
$welcomeSubject = 'Your Free Resources from Izende Studio Web';
$welcomeMessage = "Hi {$name},\n\n";
$welcomeMessage .= "Thank you for downloading our free resources! Here are your download links:\n\n";
$welcomeMessage .= "1. Website Launch Checklist (PDF)\n";
$welcomeMessage .= "   {$websiteLaunchChecklistUrl}\n\n";
$welcomeMessage .= "2. SEO Audit Template (Excel)\n";
$welcomeMessage .= "   {$seoAuditTemplateUrl}\n\n";
$welcomeMessage .= "3. Hosting Comparison Guide (PDF)\n";
$welcomeMessage .= "   {$hostingComparisonGuideUrl}\n\n";
$welcomeMessage .= "These resources will help you plan and launch a successful website.\n\n";
$welcomeMessage .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
$welcomeMessage .= "Need help with your website project? We're here for you!\n\n";
$welcomeMessage .= "📞 Call us: 314-312-6441\n";
$welcomeMessage .= "📧 Email: support@izendestudioweb.com\n";
$welcomeMessage .= "🌐 Website: https://izendestudioweb.com\n\n";
$welcomeMessage .= "Schedule a FREE consultation: https://izendestudioweb.com/quote.php\n\n";
$welcomeMessage .= "Best regards,\n";
$welcomeMessage .= "The Izende Studio Web Team\n";
$welcomeMessage .= "St. Louis Web Design & Hosting\n";

$welcomeHeaders = [
    'From: Izende Studio Web <support@izendestudioweb.com>',
    'Reply-To: support@izendestudioweb.com',
    'X-Mailer: PHP/' . phpversion(),
    'Content-Type: text/plain; charset=UTF-8'
];

$emailSent = mail($email, $welcomeSubject, $welcomeMessage, implode("\r\n", $welcomeHeaders));

// Log the submission
logSecurityEvent('lead_capture_submitted', [
    'name' => $name,
    'email' => $email,
    'ip' => $clientIP,
    'email_sent' => $emailSent,
    'email_marketing' => $emailMarketingSuccess
], 'INFO');

// Return success response
if ($emailSent) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Check your email for the download links.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'There was an error sending the email. Please contact support@izendestudioweb.com.'
    ]);
}

// Regenerate CSRF token for next submission
regenerateCSRFToken();
exit;
