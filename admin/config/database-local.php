<?php
/**
 * Local Development Database Configuration
 * Use this instead of database.php for local testing
 */

// Local development database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Usually empty for local root
define('DB_NAME', 'izendestudioweb_wp');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    error_log("Database Connection Error: " . $conn->connect_error);
    // For local dev, show the error
    die("Local Database connection failed: " . $conn->connect_error . "\n\nTry creating the database with:\nmysql -u root -e \"CREATE DATABASE IF NOT EXISTS izendestudioweb_wp;\"");
}

// Set charset
$conn->set_charset("utf8mb4");

// Error reporting for development
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
