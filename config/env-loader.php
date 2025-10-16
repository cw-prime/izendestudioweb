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
    $defaults = [
        'APP_ENV' => 'production',
        'APP_DEBUG' => 'false',
        'SESSION_LIFETIME' => '3600',
        'RATE_LIMIT_MAX_ATTEMPTS' => '5',
        'RATE_LIMIT_TIME_WINDOW' => '300',
        'MAIL_TO' => 'support@izendestudioweb.com',
        'MAIL_FROM' => 'noreply@izendestudioweb.com'
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

// Auto-load .env file when this file is included
loadEnvFile();

// Verify critical variables are set
$criticalVars = ['RECAPTCHA_SECRET_KEY', 'RECAPTCHA_SITE_KEY'];
$missingVars = checkRequiredEnvVars($criticalVars);

if (!empty($missingVars) && getEnvBool('APP_DEBUG', false)) {
    // Only show warning in debug mode
    error_log('Warning: Missing environment variables: ' . implode(', ', $missingVars));
}
