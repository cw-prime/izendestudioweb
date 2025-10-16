<?php
/**
 * Custom Error Handler for Production Environment
 *
 * Provides centralized error handling with:
 * - Error logging to file
 * - Security event logging
 * - Generic error messages to users
 * - Fatal error handling
 *
 * @version 1.0
 * @date 2025-10-14
 */

// Prevent direct access
if (!defined('ERROR_HANDLER_INIT')) {
    define('ERROR_HANDLER_INIT', true);
}

// =============================================================================
// CONFIGURATION
// =============================================================================

$logDirectory = '/var/www/html/izendestudioweb/logs';
$phpErrorLog = $logDirectory . '/php_errors.log';
$securityLog = $logDirectory . '/security.log';

// Create log directory if it doesn't exist
if (!is_dir($logDirectory)) {
    @mkdir($logDirectory, 0750, true);
}

// =============================================================================
// PHP ERROR SETTINGS
// =============================================================================

// Production error settings
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', $phpErrorLog);

// =============================================================================
// CUSTOM ERROR HANDLER
// =============================================================================

/**
 * Custom error handler function
 *
 * @param int $errno Error level
 * @param string $errstr Error message
 * @param string $errfile File where error occurred
 * @param int $errline Line number where error occurred
 * @return bool
 */
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    global $phpErrorLog;

    // Don't process errors that are suppressed with @
    if (!(error_reporting() & $errno)) {
        return false;
    }

    // Map error types to readable names
    $errorTypes = [
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_PARSE => 'PARSE ERROR',
        E_NOTICE => 'NOTICE',
        E_CORE_ERROR => 'CORE ERROR',
        E_CORE_WARNING => 'CORE WARNING',
        E_COMPILE_ERROR => 'COMPILE ERROR',
        E_COMPILE_WARNING => 'COMPILE WARNING',
        E_USER_ERROR => 'USER ERROR',
        E_USER_WARNING => 'USER WARNING',
        E_USER_NOTICE => 'USER NOTICE',
        E_STRICT => 'STRICT',
        E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR',
        E_DEPRECATED => 'DEPRECATED',
        E_USER_DEPRECATED => 'USER DEPRECATED',
    ];

    $errorType = $errorTypes[$errno] ?? 'UNKNOWN';

    // Format log entry
    $logEntry = sprintf(
        "[%s] [%s] %s in %s on line %d\n",
        date('Y-m-d H:i:s'),
        $errorType,
        $errstr,
        $errfile,
        $errline
    );

    // Write to error log
    error_log($logEntry, 3, $phpErrorLog);

    // For fatal errors, display generic error page
    if ($errno === E_ERROR || $errno === E_USER_ERROR || $errno === E_CORE_ERROR || $errno === E_COMPILE_ERROR) {
        displayErrorPage('An error occurred while processing your request.');
        exit(1);
    }

    // Don't execute PHP internal error handler
    return true;
}

// =============================================================================
// CUSTOM EXCEPTION HANDLER
// =============================================================================

/**
 * Custom exception handler function
 *
 * @param Exception $exception The uncaught exception
 */
function customExceptionHandler($exception) {
    global $phpErrorLog;

    // Format log entry
    $logEntry = sprintf(
        "[%s] [EXCEPTION] %s in %s on line %d\nStack trace:\n%s\n",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );

    // Write to error log
    error_log($logEntry, 3, $phpErrorLog);

    // Display generic error page
    displayErrorPage('An unexpected error occurred. Please try again later.');
}

// =============================================================================
// SHUTDOWN HANDLER (CATCH FATAL ERRORS)
// =============================================================================

/**
 * Shutdown handler to catch fatal errors
 */
function shutdownHandler() {
    $error = error_get_last();

    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        global $phpErrorLog;

        // Format log entry
        $logEntry = sprintf(
            "[%s] [FATAL ERROR] %s in %s on line %d\n",
            date('Y-m-d H:i:s'),
            $error['message'],
            $error['file'],
            $error['line']
        );

        // Write to error log
        error_log($logEntry, 3, $phpErrorLog);

        // Display maintenance page for fatal errors
        if (!headers_sent()) {
            displayErrorPage('The site is temporarily unavailable. Please try again in a few minutes.');
        }
    }
}

// =============================================================================
// DISPLAY ERROR PAGE
// =============================================================================

/**
 * Display generic error page to users
 *
 * @param string $message User-friendly error message
 */
function displayErrorPage($message = 'An error occurred') {
    // Only send headers if not already sent
    if (!headers_sent()) {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: text/html; charset=UTF-8');
    }

    // Display simple error page
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Izende Studio Web</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .error-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 600px;
            text-align: center;
        }
        h1 {
            color: #d9534f;
            margin-bottom: 20px;
        }
        p {
            color: #666;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #5cb85c;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .btn:hover {
            background-color: #4cae4c;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>Oops! Something went wrong</h1>
        <p>' . htmlspecialchars($message) . '</p>
        <p>If the problem persists, please contact us at <a href="mailto:support@izendestudioweb.com">support@izendestudioweb.com</a></p>
        <a href="/" class="btn">Return to Homepage</a>
    </div>
</body>
</html>';
}

// =============================================================================
// LOG ROTATION INSTRUCTIONS
// =============================================================================

/**
 * LOG ROTATION SETUP
 *
 * To prevent log files from growing too large, set up log rotation:
 *
 * 1. Create logrotate configuration file:
 *    sudo nano /etc/logrotate.d/izendestudio
 *
 * 2. Add the following configuration:
 *
 *    /var/www/html/izendestudioweb/logs/*.log {
 *        weekly
 *        rotate 4
 *        compress
 *        delaycompress
 *        missingok
 *        notifempty
 *        create 640 www-data www-data
 *    }
 *
 * 3. Test the configuration:
 *    sudo logrotate -d /etc/logrotate.d/izendestudio
 *
 * Alternatively, set up a cron job to archive old logs:
 *    0 0 * * 0 find /var/www/html/izendestudioweb/logs -name "*.log" -mtime +30 -exec gzip {} \;
 */

// =============================================================================
// REGISTER HANDLERS
// =============================================================================

// Register error handler
set_error_handler('customErrorHandler');

// Register exception handler
set_exception_handler('customExceptionHandler');

// Register shutdown handler
register_shutdown_function('shutdownHandler');

// =============================================================================
// USAGE NOTES
// =============================================================================

/**
 * To use this error handler, include it at the top of your PHP files:
 *
 *   require_once __DIR__ . '/config/error-handler.php';
 *
 * Or add it to your main configuration file that's included on every page.
 *
 * IMPORTANT:
 * - Ensure the logs directory has proper permissions (750)
 * - Monitor log file size and set up log rotation
 * - Review error logs regularly
 * - Never expose sensitive information in error messages
 * - Test error handling in development environment first
 */
