<?php
/**
 * Website Drafter — visitor generation count.
 *
 * Static preview pages cannot read the signed `iz_genc` cookie directly because
 * it is HttpOnly. This endpoint lets same-origin preview JavaScript display the
 * remaining free-draft count without exposing or trusting the cookie value.
 */

header('Content-Type: application/json');
header('Cache-Control: no-store');

require_once __DIR__ . '/../config/env-loader.php';
require_once __DIR__ . '/../includes/gen-cap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$cap = iz_gen_read();
echo json_encode([
    'success' => true,
    'used' => (int) $cap['used'],
    'remaining' => (int) $cap['remaining'],
    'limit' => (int) IZ_GEN_LIMIT,
]);
