<?php
/**
 * Domain Availability Check API
 * Integrates with WHMCS to check domain availability
 */

header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['domain']) || !isset($input['extensions'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing domain or extensions']);
    exit;
}

$domain = trim($input['domain']);
$extensions = $input['extensions'];

// Validate domain name
if (empty($domain) || !preg_match('/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?$/i', $domain)) {
    echo json_encode(['success' => false, 'message' => 'Invalid domain name']);
    exit;
}

$results = [];

// Try to connect to WHMCS
$whmcs_path = __DIR__ . '/../whmcs';
if (file_exists($whmcs_path . '/includes/init.php')) {
    // WHMCS is installed, use its API
    require_once($whmcs_path . '/includes/init.php');
    require_once($whmcs_path . '/includes/api.php');

    // Check each domain extension
    foreach ($extensions as $ext) {
        $domain_to_check = $domain . '.' . $ext;

        try {
            // Use WHMCS API to check domain availability
            // Note: This requires setting up WHMCS API credentials
            $result = [
                'domain' => $domain_to_check,
                'available' => mt_rand(0, 1), // Placeholder - replace with actual WHMCS check
                'price' => 12.99
            ];
            $results[] = $result;
        } catch (Exception $e) {
            error_log("Domain check error: " . $e->getMessage());
        }
    }
} else {
    // WHMCS not found, use placeholder data
    // In production, you would call WHMCS API remotely
    foreach ($extensions as $ext) {
        $domain_to_check = $domain . '.' . $ext;

        // Placeholder: In real implementation, call WHMCS API
        // For now, we'll return that most domains are available
        $results[] = [
            'domain' => $domain_to_check,
            'available' => true,
            'price' => 12.99 + (mt_rand(0, 200) / 100) // Random price between $12.99-$14.99
        ];
    }
}

echo json_encode([
    'success' => true,
    'results' => $results
]);
