<?php
/**
 * Blog Posts AJAX Endpoint
 * Returns blog posts as JSON for dynamic loading
 */

// Load required files
require_once __DIR__ . '/../config/env-loader.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../services/blog-db.php'; // Changed to direct DB access

// Set security headers
setSecurityHeaders();

// Set JSON header
header('Content-Type: application/json');

// Get parameters
$per_page = isset($_GET['per_page']) ? min((int)$_GET['per_page'], 50) : 9;
$page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
$category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
$search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Initialize Blog DB (direct database access - much faster)
try {
    $blog_db = new BlogDB();
    $result = $blog_db->getPosts($per_page, $page, $category, $search);
} catch (Exception $e) {
    error_log('Blog posts API error: ' . $e->getMessage());
    $result = false;
}

if ($result === false) {
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'message' => 'Failed to fetch blog posts'
    ));
    exit;
}

// Return success response
echo json_encode(array(
    'success' => true,
    'data' => $result
));

/**
 * Sanitize text field
 *
 * @param string $text
 * @return string
 */
function sanitize_text_field($text) {
    $text = strip_tags($text);
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    return trim($text);
}
