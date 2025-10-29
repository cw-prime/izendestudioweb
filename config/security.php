<?php
/**
 * Security Configuration and Helper Functions
 *
 * Provides centralized security functionality including:
 * - CSRF token management
 * - Rate limiting
 * - Input validation and sanitization
 * - Session security
 * - Security headers
 * - Security event logging
 *
 * @version 1.0
 * @date 2025-10-14
 */

// Prevent direct access
if (!defined('SECURITY_INIT')) {
    define('SECURITY_INIT', true);
}

// =============================================================================
// CSRF TOKEN MANAGEMENT
// =============================================================================

/**
 * Generate a new CSRF token and store it in session
 *
 * @return string The generated CSRF token
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        initSecureSession();
    }

    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_token_time'] = time();

    return $token;
}

/**
 * Validate CSRF token with constant-time comparison
 *
 * @param string $token The token to validate
 * @return bool True if token is valid, false otherwise
 */
function validateCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        initSecureSession();
    }

    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }

    // Token expires after 1 hour
    if (time() - $_SESSION['csrf_token_time'] > 3600) {
        return false;
    }

    // Use hash_equals for constant-time comparison (prevents timing attacks)
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Regenerate CSRF token (call after successful form submission)
 */
function regenerateCSRFToken() {
    return generateCSRFToken();
}

// =============================================================================
// RATE LIMITING
// =============================================================================

/**
 * Check if rate limit has been exceeded
 *
 * @param string $identifier Unique identifier (typically IP address)
 * @param int $maxAttempts Maximum attempts allowed
 * @param int $timeWindow Time window in seconds
 * @return bool True if within rate limit, false if exceeded
 */
function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 300) {
    $rateLimitDir = '/tmp/rate_limit_izende';

    // Create rate limit directory if it doesn't exist
    if (!is_dir($rateLimitDir)) {
        mkdir($rateLimitDir, 0700, true);
    }

    // Create safe filename from identifier
    $filename = $rateLimitDir . '/' . md5($identifier);

    // Clean up old rate limit files (older than 1 hour)
    cleanupRateLimitFiles($rateLimitDir);

    $currentTime = time();
    $attempts = [];

    // Read existing attempts
    if (file_exists($filename)) {
        $data = file_get_contents($filename);
        if ($data !== false) {
            $attempts = json_decode($data, true) ?: [];
        }
    }

    // Remove attempts outside the time window
    $attempts = array_filter($attempts, function($timestamp) use ($currentTime, $timeWindow) {
        return ($currentTime - $timestamp) < $timeWindow;
    });

    // Check if limit exceeded
    if (count($attempts) >= $maxAttempts) {
        logSecurityEvent('rate_limit_exceeded', [
            'identifier' => $identifier,
            'attempts' => count($attempts),
            'max_attempts' => $maxAttempts,
            'time_window' => $timeWindow
        ], 'WARNING');
        return false;
    }

    // Add current attempt
    $attempts[] = $currentTime;

    // Save attempts
    file_put_contents($filename, json_encode($attempts), LOCK_EX);

    return true;
}

/**
 * Clean up old rate limit files
 *
 * @param string $directory Directory containing rate limit files
 */
function cleanupRateLimitFiles($directory) {
    $currentTime = time();
    $maxAge = 3600; // 1 hour

    if (!is_dir($directory)) {
        return;
    }

    $files = glob($directory . '/*');
    foreach ($files as $file) {
        if (is_file($file) && ($currentTime - filemtime($file)) > $maxAge) {
            @unlink($file);
        }
    }
}

/**
 * Get remaining attempts for rate limit
 *
 * @param string $identifier Unique identifier
 * @param int $maxAttempts Maximum attempts allowed
 * @param int $timeWindow Time window in seconds
 * @return int Remaining attempts
 */
function getRateLimitRemaining($identifier, $maxAttempts = 5, $timeWindow = 300) {
    $rateLimitDir = '/tmp/rate_limit_izende';
    $filename = $rateLimitDir . '/' . md5($identifier);

    if (!file_exists($filename)) {
        return $maxAttempts;
    }

    $currentTime = time();
    $data = file_get_contents($filename);
    $attempts = json_decode($data, true) ?: [];

    // Remove attempts outside the time window
    $attempts = array_filter($attempts, function($timestamp) use ($currentTime, $timeWindow) {
        return ($currentTime - $timestamp) < $timeWindow;
    });

    return max(0, $maxAttempts - count($attempts));
}

// =============================================================================
// INPUT VALIDATION & SANITIZATION
// =============================================================================

/**
 * Validate email address
 *
 * @param string $email Email to validate
 * @return string|false Validated email or false on failure
 */
function validateEmail($email) {
    $email = trim($email);

    if (empty($email)) {
        return false;
    }

    $validated = filter_var($email, FILTER_VALIDATE_EMAIL);

    if ($validated === false) {
        return false;
    }

    // Additional checks for common email issues
    if (strlen($email) > 254) { // RFC 5321
        return false;
    }

    return $validated;
}

/**
 * Validate phone number (US format)
 *
 * @param string $phone Phone number to validate
 * @return string|false Validated phone or false on failure
 */
function validatePhone($phone) {
    $phone = trim($phone);

    if (empty($phone)) {
        return false;
    }

    // Remove all non-digit characters
    $digitsOnly = preg_replace('/\D/', '', $phone);

    // Check if it's a valid US phone number (10 digits)
    if (strlen($digitsOnly) === 10 || strlen($digitsOnly) === 11) {
        return $phone; // Return original format
    }

    return false;
}

/**
 * Sanitize input based on type
 *
 * @param mixed $input Input to sanitize
 * @param string $type Type of sanitization (string, email, int, float, url)
 * @return mixed Sanitized input
 */
function sanitizeInput($input, $type = 'string') {
    if (is_null($input)) {
        return '';
    }

    switch ($type) {
        case 'email':
            return filter_var($input, FILTER_SANITIZE_EMAIL);

        case 'int':
        case 'integer':
            return filter_var($input, FILTER_SANITIZE_NUMBER_INT);

        case 'float':
            return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        case 'url':
            return filter_var($input, FILTER_SANITIZE_URL);

        case 'string':
        default:
            // Remove null bytes, trim whitespace
            $input = str_replace("\0", '', $input);
            $input = trim($input);
            return $input;
    }
}

/**
 * Sanitize HTML to prevent XSS attacks
 *
 * @param string $html HTML to sanitize
 * @return string Sanitized HTML
 */
function sanitizeHTML($html) {
    return htmlspecialchars($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Validate string length
 *
 * @param string $string String to validate
 * @param int $minLength Minimum length
 * @param int $maxLength Maximum length
 * @return bool True if valid, false otherwise
 */
function validateLength($string, $minLength = 0, $maxLength = PHP_INT_MAX) {
    $length = mb_strlen($string, 'UTF-8');
    return $length >= $minLength && $length <= $maxLength;
}

// =============================================================================
// SESSION SECURITY
// =============================================================================

/**
 * Initialize secure session with best practices
 */
function initSecureSession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return; // Session already started
    }

    // Configure session security settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 1); // HTTPS only
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_trans_sid', 0);

    // Set session name (change from default PHPSESSID)
    session_name('IZENDE_SESSION');

    // Set session cookie parameters
    session_set_cookie_params([
        'lifetime' => 3600, // 1 hour
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => true, // HTTPS only
        'httponly' => true, // No JavaScript access
        'samesite' => 'Strict' // CSRF protection
    ]);

    session_start();

    // Regenerate session ID periodically to prevent session fixation
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }

    // Validate session to prevent session hijacking
    validateSession();
}

/**
 * Validate session integrity
 */
function validateSession() {
    // Store user agent and IP on first access
    if (!isset($_SESSION['user_agent'])) {
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
    }

    // Validate user agent hasn't changed
    if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
        logSecurityEvent('session_hijack_attempt', [
            'expected_user_agent' => $_SESSION['user_agent'],
            'actual_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ], 'CRITICAL');
        session_destroy();
        session_start();
    }

    // Optionally validate IP address (commented out as it can cause issues with mobile users)
    // if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
    //     session_destroy();
    //     session_start();
    // }
}

// =============================================================================
// SECURITY HEADERS
// =============================================================================

/**
 * Set comprehensive security headers
 */
function setSecurityHeaders() {
    // Prevent sending if headers already sent
    if (headers_sent()) {
        return;
    }

    // Generate nonce for CSP
    $nonce = base64_encode(random_bytes(16));
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['csp_nonce'] = $nonce;
    }

    // Content Security Policy (CSP)
    // Note: Relaxed policy for development - tighten for production
    $csp = "default-src 'self'; ";
    $csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.google.com https://www.gstatic.com https://maps.googleapis.com https://cdn.jsdelivr.net https://code.jquery.com https://unpkg.com https://fonts.googleapis.com https://www.googletagmanager.com https://pagead2.googlesyndication.com; ";
    $csp .= "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://unpkg.com; ";
    $csp .= "img-src 'self' data: https: http:; ";
    $csp .= "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net data:; ";
    $csp .= "connect-src 'self' http://localhost:8081 http://127.0.0.1:8081 https://www.google-analytics.com https://maps.googleapis.com https://unpkg.com; ";
    $csp .= "frame-src 'self' https://www.google.com https://maps.googleapis.com; ";
    $csp .= "frame-ancestors 'self'; ";
    $csp .= "form-action 'self'; ";
    $csp .= "base-uri 'self'; ";

    header("Content-Security-Policy: " . $csp);

    // Strict Transport Security (HSTS)
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");

    // Prevent clickjacking
    header("X-Frame-Options: SAMEORIGIN");

    // Prevent MIME type sniffing
    header("X-Content-Type-Options: nosniff");

    // XSS Protection (legacy, but still useful for older browsers)
    header("X-XSS-Protection: 1; mode=block");

    // Referrer Policy
    header("Referrer-Policy: strict-origin-when-cross-origin");

    // Permissions Policy (formerly Feature Policy)
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
}

/**
 * Get CSP nonce for inline scripts
 *
 * @return string CSP nonce value
 */
function getCSPNonce() {
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['csp_nonce'])) {
        return $_SESSION['csp_nonce'];
    }
    return '';
}

// =============================================================================
// SECURITY EVENT LOGGING
// =============================================================================

/**
 * Log security events to file
 *
 * @param string $event Event type
 * @param array $details Event details
 * @param string $severity Severity level (INFO, WARNING, CRITICAL)
 */
function logSecurityEvent($event, $details = [], $severity = 'INFO') {
    $logDir = '/var/www/html/izendestudioweb/logs';

    // Create log directory if it doesn't exist
    if (!is_dir($logDir)) {
        mkdir($logDir, 0750, true);
    }

    $logFile = $logDir . '/security.log';

    // Prepare log entry
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'severity' => $severity,
        'event' => $event,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
        'details' => $details
    ];

    // Format log entry
    $logLine = sprintf(
        "[%s] [%s] %s - IP: %s - %s\n",
        $logEntry['timestamp'],
        $logEntry['severity'],
        $logEntry['event'],
        $logEntry['ip_address'],
        json_encode($logEntry['details'])
    );

    // Write to log file
    error_log($logLine, 3, $logFile);

    // If critical event, consider sending email alert
    if ($severity === 'CRITICAL') {
        // TODO: Implement email alerting for critical events
        // mail(getEnv('ADMIN_EMAIL'), 'Security Alert: ' . $event, $logLine);
    }
}

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

/**
 * Get client IP address (handles proxies)
 *
 * @return string Client IP address
 */
function getClientIP() {
    $ip = '';

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Handle multiple IPs in X-Forwarded-For
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ips[0]);
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    }

    // Validate IP
    if (filter_var($ip, FILTER_VALIDATE_IP)) {
        return $ip;
    }

    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * Check if request is HTTPS
 *
 * @return bool True if HTTPS, false otherwise
 */
function isHTTPS() {
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }

    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        return true;
    }

    if (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') {
        return true;
    }

    return false;
}

/**
 * Redirect to HTTPS if not already
 */
function forceHTTPS() {
    if (!isHTTPS()) {
        $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $redirect);
        exit();
    }
}

// Initialize security on include
// Note: Session and headers are NOT started here automatically
// They must be called explicitly in each page to ensure proper order
