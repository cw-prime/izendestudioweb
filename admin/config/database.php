<?php
/**
 * Database Configuration for Admin Panel
 */

// Database credentials (same as WordPress database)
define('DB_HOST', 'localhost');
define('DB_USER', 'admin');
define('DB_PASS', 'mark');
define('DB_NAME', 'izendestudioweb_wp');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    error_log("Database Connection Error: " . $conn->connect_error);
    die("Database connection failed. Please check your configuration.");
}

// Set charset
$conn->set_charset("utf8mb4");

// Error reporting for development
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
