<?php
/**
 * Database Configuration for Admin Panel
 * Supports both local development and production
 */

// Check if we're in local development mode
$localEnvFile = __DIR__ . '/.env.local';
$useLocal = false;

if (file_exists($localEnvFile)) {
    $envContent = file_get_contents($localEnvFile);
    // Check if DB_ENV=local is set (not commented out)
    if (preg_match('/^\s*DB_ENV\s*=\s*local/m', $envContent)) {
        $useLocal = true;
    }
}

// Database credentials - local or production
if ($useLocal) {
    // Local development database
    define('DB_HOST', 'localhost');
    define('DB_USER', 'admin');
    define('DB_PASS', 'mark');
    define('DB_NAME', 'izendestudioweb_wp');
} else {
    // Production database
    define('DB_HOST', 'localhost');
    define('DB_USER', 'izende6_wp433');
    define('DB_PASS', 'Mw~;#vFTq.5D');
    define('DB_NAME', 'izende6_wp433');
}

// Create connection with fallback to socket if password auth fails (for local dev)
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// If connection fails and we're in local mode, try socket authentication
if ($conn->connect_error && $useLocal) {
    $conn = new mysqli('localhost:/var/run/mysqld/mysqld.sock', 'root', '', DB_NAME);
}

// Check connection
if ($conn->connect_error) {
    error_log("Database Connection Error: " . $conn->connect_error);
    die("Database connection failed. Please check your configuration.");
}

// Set charset
$conn->set_charset("utf8mb4");

// Error reporting - only in local development
if ($useLocal) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
}
