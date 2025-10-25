<?php
/**
 * Blog Categories AJAX Endpoint
 * Returns blog categories as JSON
 */

// Load required files
require_once __DIR__ . '/../config/env-loader.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../services/blog-db.php'; // Changed to direct DB access

// Set security headers
setSecurityHeaders();

// Set JSON header
header('Content-Type: application/json');

// Initialize Blog DB (direct database access - much faster)
try {
    $blog_db = new BlogDB();
    $categories = $blog_db->getCategories();
} catch (Exception $e) {
    $categories = false;
}

if ($categories === false) {
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'message' => 'Failed to fetch categories'
    ));
    exit;
}

// Return success response
echo json_encode(array(
    'success' => true,
    'data' => $categories
));
