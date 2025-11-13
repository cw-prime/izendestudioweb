<?php
/**
 * Blog Sidebar AJAX Endpoint
 * Returns rendered WordPress sidebar markup so the custom blog
 * template can stay in sync with the active theme/widget config.
 */

require_once __DIR__ . '/../config/env-loader.php';
require_once __DIR__ . '/../config/security.php';

setSecurityHeaders();

header('Content-Type: application/json');

$default_sidebar = 'sidebar-1';
$requested_sidebar = isset($_GET['sidebar']) ? $_GET['sidebar'] : $default_sidebar;
$sidebar_id = sanitize_sidebar_id($requested_sidebar, $default_sidebar);

$wp_loader = realpath(__DIR__ . '/../articles/wp-load.php');
if ($wp_loader === false || !file_exists($wp_loader)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'WordPress bootstrap file not found.'
    ]);
    exit;
}

require_once $wp_loader;

if (!function_exists('is_active_sidebar') || !function_exists('dynamic_sidebar')) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'WordPress sidebar functions are unavailable.'
    ]);
    exit;
}

if (!is_active_sidebar($sidebar_id)) {
    echo json_encode([
        'success' => true,
        'sidebar' => $sidebar_id,
        'html' => '',
        'is_active' => false,
        'message' => 'Sidebar has no active widgets.'
    ]);
    exit;
}

ob_start();
dynamic_sidebar($sidebar_id);
$sidebar_html = trim(ob_get_clean());

echo json_encode([
    'success' => true,
    'sidebar' => $sidebar_id,
    'html' => $sidebar_html,
    'is_active' => true
]);

/**
 * Sanitize sidebar id to safe characters and fall back when necessary.
 */
function sanitize_sidebar_id($value, $fallback) {
    $value = strtolower(trim((string) $value));
    $value = preg_replace('/[^a-z0-9_-]/', '', $value);

    return $value !== '' ? $value : $fallback;
}
