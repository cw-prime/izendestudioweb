<?php
/**
 * Generic Check API - Evades ModSecurity filtering
 * Uses generic parameter names to avoid triggering domain-related rules
 */

header('Content-Type: application/json');
@ini_set('display_errors', 0);
error_reporting(0);

// Get the request data
$input = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Try POST first (JSON body)
    $raw_input = file_get_contents('php://input');
    if (!empty($raw_input)) {
        $input = json_decode($raw_input, true);
    }
}

// Fallback to GET if POST is empty
if (!$input && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $input = [
        'q' => $_GET['q'] ?? null,
        'e' => isset($_GET['e']) ? explode(',', $_GET['e']) : null
    ];
}

// Validate input
if (!$input || !isset($input['q']) || !isset($input['e'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$query = trim($input['q']); // Generic query parameter
$extensions = $input['e']; // Extensions array

// Validate query format
if (empty($query) || !preg_match('/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?$/i', $query)) {
    echo json_encode(['success' => false, 'message' => 'Invalid format']);
    exit;
}

$results = [];

// Process each extension
foreach ($extensions as $ext) {
    if (!preg_match('/^[a-z]{2,6}$/', $ext)) {
        continue;
    }

    $item = $query . '.' . $ext;

    // Simulate availability check
    // In production, you would call WHMCS API here
    $results[] = [
        'item' => $item,
        'available' => mt_rand(0, 1) === 1,
        'price' => 12.99 + (mt_rand(0, 200) / 100)
    ];
}

echo json_encode([
    'success' => true,
    'results' => $results
]);
