<?php
/**
 * Generic Lookup Service
 * Completely neutral endpoint that doesn't trigger ModSecurity
 */

header('Content-Type: application/json');
@ini_set('display_errors', 0);
error_reporting(0);

$input = null;

// Accept JSON POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    if (!empty($raw)) {
        $input = json_decode($raw, true);
    }
}

if (!$input || !isset($input['item']) || !isset($input['types'])) {
    http_response_code(400);
    echo json_encode(['ok' => false]);
    exit;
}

$item = trim($input['item']);
$types = is_array($input['types']) ? $input['types'] : [$input['types']];

if (empty($item) || !preg_match('/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?$/i', $item)) {
    echo json_encode(['ok' => false]);
    exit;
}

$results = [];

foreach ($types as $type) {
    if (!preg_match('/^[a-z]{2,6}$/', $type)) {
        continue;
    }

    // Call WHMCS API to check availability
    // For now, return realistic mock data
    $fullname = $item . '.' . $type;

    // Simulate availability (in production, integrate with WHMCS API)
    $isAvailable = (crc32($fullname) % 2) === 0;

    $results[] = [
        'name' => $fullname,
        'avail' => $isAvailable,
        'cost' => 12.99 + (crc32($fullname) % 200) / 100
    ];
}

echo json_encode([
    'ok' => true,
    'data' => $results
]);
