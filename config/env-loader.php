<?php
/**
 * Environment Variable Loader
 *
 * Simple .env file parser for loading environment variables
 * Provides secure configuration management without external dependencies
 *
 * @version 1.0
 * @date 2025-10-14
 */

// Prevent direct access
if (!defined('ENV_LOADER_INIT')) {
    define('ENV_LOADER_INIT', true);
}

/**
 * Load environment variables from .env file
 *
 * @param string|null $envPath Optional custom path to .env file
 * @return bool True if file was loaded, false otherwise
 */
if (!function_exists('loadEnvFile')) {
function loadEnvFile($envPath = null) {
    // Try multiple possible locations for .env file
    $possiblePaths = [];

    if ($envPath !== null) {
        $possiblePaths[] = $envPath;
    }

    // Try config directory
    $possiblePaths[] = __DIR__ . '/.env';

    // Try one level up from config
    $possiblePaths[] = dirname(__DIR__) . '/.env';

    // Try two levels up (outside web root if possible)
    $possiblePaths[] = dirname(dirname(__DIR__)) . '/.env';

    $envFile = null;
    foreach ($possiblePaths as $path) {
        if (file_exists($path) && is_readable($path)) {
            $envFile = $path;
            break;
        }
    }

    // If no .env file found, use defaults (fail gracefully)
    if ($envFile === null) {
        setDefaultEnvVariables();
        return false;
    }

    // Read and parse .env file
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        setDefaultEnvVariables();
        return false;
    }

    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse key=value pairs
        if (strpos($line, '=') === false) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);

        // Trim whitespace
        $key = trim($key);
        $value = trim($value);

        // Remove quotes from value if present
        if (preg_match('/^(["\'])(.*)\1$/', $value, $matches)) {
            $value = $matches[2];
        }

        // Set environment variable
        if (!empty($key)) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    return true;
}
}

/**
 * Set default environment variables (fallback)
 */
if (!function_exists('setDefaultEnvVariables')) {
function setDefaultEnvVariables() {
    // IMPORTANT: Do NOT add email address defaults here.
    // MAIL_TO and MAIL_FROM must be set explicitly in environment to prevent
    // silently sending sensitive form submissions to wrong addresses.
    $defaults = [
        'APP_ENV'                  => 'production',
        'APP_DEBUG'                => 'false',
        'SESSION_LIFETIME'         => '3600',
        'SESSION_IP_VALIDATION'    => 'false',   // off by default; enable in staging/production after testing
        'CSP_REPORT_ONLY'          => 'false',   // set 'true' in dev/staging to monitor CSP violations
        'BOOKING_FROM_NAME'        => 'Izende Studio Web',  // display name for booking notification emails
        'RATE_LIMIT_MAX_ATTEMPTS'  => '5',
        'RATE_LIMIT_TIME_WINDOW'   => '300',
    ];

    foreach ($defaults as $key => $value) {
        if (getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}
}

/**
 * Get environment variable with fallback
 *
 * @param string $key Variable name
 * @param mixed $default Default value if not found
 * @return mixed Variable value or default
 */
if (!function_exists('getEnv')) {
function getEnv($key, $default = null) {
    // Try $_ENV first
    if (isset($_ENV[$key])) {
        return $_ENV[$key];
    }

    // Try getenv()
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }

    // Try $_SERVER
    if (isset($_SERVER[$key])) {
        return $_SERVER[$key];
    }

    // Return default
    return $default;
}
}

/**
 * Check if required environment variables are set
 *
 * @param array $required Array of required variable names
 * @return array Array of missing variables (empty if all present)
 */
if (!function_exists('checkRequiredEnvVars')) {
function checkRequiredEnvVars($required = []) {
    $missing = [];

    foreach ($required as $key) {
        if (getEnv($key) === null) {
            $missing[] = $key;
        }
    }

    return $missing;
}
}

/**
 * Get boolean environment variable
 *
 * @param string $key Variable name
 * @param bool $default Default value
 * @return bool
 */
if (!function_exists('getEnvBool')) {
function getEnvBool($key, $default = false) {
    $value = getEnv($key);

    if ($value === null) {
        return $default;
    }

    $value = strtolower(trim($value));

    return in_array($value, ['true', '1', 'yes', 'on'], true);
}
}

/**
 * Get integer environment variable
 *
 * @param string $key Variable name
 * @param int $default Default value
 * @return int
 */
if (!function_exists('getEnvInt')) {
function getEnvInt($key, $default = 0) {
    $value = getEnv($key);

    if ($value === null) {
        return $default;
    }

    return (int) $value;
}
}

/**
 * Validate environment variables at startup.
 *
 * Required vars trigger an error_log entry regardless of APP_DEBUG — missing
 * required vars indicate a deployment misconfiguration that must be surfaced.
 * Recommended vars are logged only when APP_DEBUG is true (they may be absent
 * in minimal environments).
 *
 * Groups:
 *   required    — application will be broken or insecure without these
 *   recommended — degrade functionality when absent (email, SMTP, etc.)
 *
 * @return array{required: string[], recommended: string[]} Missing var names by group
 */
if (!function_exists('validateEnvVars')) {
function validateEnvVars() {
    $groups = [
        'required' => [
            'RECAPTCHA_SECRET_KEY',   // reCAPTCHA server-side verification
            'RECAPTCHA_SITE_KEY',     // reCAPTCHA client-side widget
            'MAIL_TO',                // Recipient for quote/contact form emails (required — no safe default)
            'MAIL_FROM',              // Sender address for outbound emails (required — no safe default)
        ],
        'recommended' => [
            'BOOKING_ADMIN_EMAIL',    // Admin notification email for bookings
            'BOOKING_FROM_EMAIL',     // From address for booking notification emails
            'SMTP_HOST',              // SMTP relay host
            'SMTP_USERNAME',          // SMTP credentials
            'SMTP_PASSWORD',          // SMTP credentials
            'SMTP_PORT',              // SMTP port (465 or 587)
        ],
    ];

    $missing = ['required' => [], 'recommended' => []];

    foreach ($groups['required'] as $key) {
        if (getEnv($key) === null) {
            $missing['required'][] = $key;
        }
    }

    foreach ($groups['recommended'] as $key) {
        if (getEnv($key) === null) {
            $missing['recommended'][] = $key;
        }
    }

    // Required missing vars are always logged — deployment misconfiguration must be surfaced.
    if (!empty($missing['required'])) {
        error_log('[ENV] CRITICAL — missing required env vars: ' . implode(', ', $missing['required']));
    }

    // Recommended missing vars are logged only in debug mode (may be absent legitimately).
    if (!empty($missing['recommended']) && getEnvBool('APP_DEBUG', false)) {
        error_log('[ENV] WARNING — missing recommended env vars: ' . implode(', ', $missing['recommended']));
    }

    return $missing;
}
}

// Auto-load .env file when this file is included
loadEnvFile();

// Optionally load local overrides (useful for local dev / per-environment secrets).
// This merges on top of the already-loaded env vars.
$localOverride = dirname(__DIR__) . '/.env.local';
if (file_exists($localOverride) && is_readable($localOverride)) {
    loadEnvFile($localOverride);
}

// Centralized environment variable validation at startup.
// Missing required vars are always logged; recommended vars only in APP_DEBUG mode.
validateEnvVars();
