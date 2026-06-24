<?php
/**
 * AI Website Builder — logo upload (Step 2 helper).
 *
 * Accepts an optional logo image (multipart), validates it strictly, stores it under
 * /genmedia/uploads/, and extracts a small brand-color palette (GD, best-effort). The
 * returned URL + colors ride along in the lead submission so the generated site uses
 * THEIR logo and (when they let the AI choose the style) matches their colors.
 *
 * POST multipart: logo=<file>, csrf_token=<token>  ->  { success, url, brand_colors }
 */
require_once __DIR__ . '/../config/env-loader.php';
require_once __DIR__ . '/../config/security.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');
initSecureSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['success' => false, 'message' => 'Method not allowed']); exit;
}
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (function_exists('checkRateLimit') && !checkRateLimit($ip . '_logo_upload', 5, 600)) {
    http_response_code(429); echo json_encode(['success' => false, 'message' => 'Too many uploads — give it a minute.']); exit;
}
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403); echo json_encode(['success' => false, 'message' => 'Session expired — please refresh the page.']); exit;
}
if (empty($_FILES['logo']) || ($_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    http_response_code(400); echo json_encode(['success' => false, 'message' => 'No file received.']); exit;
}

$f = $_FILES['logo'];
if ($f['size'] > 5 * 1024 * 1024) {
    http_response_code(400); echo json_encode(['success' => false, 'message' => 'That image is over 5 MB — please upload a smaller one.']); exit;
}
if (!is_uploaded_file($f['tmp_name'])) {
    http_response_code(400); echo json_encode(['success' => false, 'message' => 'Upload failed.']); exit;
}

// Validate it's really an allowed raster image (no SVG — XSS risk).
$allowed = [IMAGETYPE_PNG => 'png', IMAGETYPE_JPEG => 'jpg', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];
$info = @getimagesize($f['tmp_name']);
$mimeOk = ['image/png' => 1, 'image/jpeg' => 1, 'image/gif' => 1, 'image/webp' => 1];
$finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : null;
$mime = $finfo ? finfo_file($finfo, $f['tmp_name']) : ($info['mime'] ?? '');
if ($finfo) { finfo_close($finfo); }
if ($info === false || !isset($allowed[$info[2]]) || empty($mimeOk[$mime])) {
    http_response_code(400); echo json_encode(['success' => false, 'message' => 'Please upload a PNG, JPG, WEBP, or GIF image.']); exit;
}
$ext = $allowed[$info[2]];

// Store under /genmedia/uploads/ with a random name.
$uploadsDir = dirname(__DIR__) . '/genmedia/uploads';
if (!is_dir($uploadsDir)) { @mkdir($uploadsDir, 0755, true); }
if (!is_dir($uploadsDir) || !is_writable($uploadsDir)) {
    http_response_code(500); echo json_encode(['success' => false, 'message' => 'Could not store the logo right now.']); exit;
}
$name = 'logo-' . bin2hex(random_bytes(8)) . '.' . $ext;
if (!move_uploaded_file($f['tmp_name'], $uploadsDir . '/' . $name)) {
    http_response_code(500); echo json_encode(['success' => false, 'message' => 'Could not save the logo.']); exit;
}
$url = 'https://izendestudioweb.com/genmedia/uploads/' . $name;

/** Best-effort dominant-color palette via GD; null if GD/extraction unavailable. */
function iz_logo_palette($path) {
    if (!function_exists('imagecreatefromstring')) { return null; }
    $raw = @file_get_contents($path);
    if ($raw === false) { return null; }
    $img = @imagecreatefromstring($raw);
    if (!$img) { return null; }
    $w = imagesx($img); $h = imagesy($img); $grid = 48; $buckets = [];
    for ($i = 0; $i < $grid; $i++) {
        for ($j = 0; $j < $grid; $j++) {
            $x = (int) ($i / $grid * $w); $y = (int) ($j / $grid * $h);
            $rgba = imagecolorat($img, $x, $y);
            if ((($rgba >> 24) & 0x7F) > 64) { continue; }       // mostly transparent
            $r = ($rgba >> 16) & 0xFF; $g = ($rgba >> 8) & 0xFF; $b = $rgba & 0xFF;
            $mx = max($r, $g, $b); $mn = min($r, $g, $b);
            if ($mx > 240 && $mn > 230) { continue; }            // near-white
            if ($mx < 30) { continue; }                          // near-black
            if (($mx - $mn) < 16 && $mx > 190) { continue; }     // light gray
            $key = (($r >> 4) << 8) | (($g >> 4) << 4) | ($b >> 4);
            if (!isset($buckets[$key])) { $buckets[$key] = ['n' => 0, 'r' => 0, 'g' => 0, 'b' => 0]; }
            $buckets[$key]['n']++; $buckets[$key]['r'] += $r; $buckets[$key]['g'] += $g; $buckets[$key]['b'] += $b;
        }
    }
    imagedestroy($img);
    if (!$buckets) { return null; }
    uasort($buckets, function ($a, $b) { return $b['n'] - $a['n']; });
    $out = [];
    foreach ($buckets as $c) {
        $r = (int) round($c['r'] / $c['n']); $g = (int) round($c['g'] / $c['n']); $b = (int) round($c['b'] / $c['n']);
        $dup = false;
        foreach ($out as $o) {
            if (abs(hexdec(substr($o, 1, 2)) - $r) + abs(hexdec(substr($o, 3, 2)) - $g) + abs(hexdec(substr($o, 5, 2)) - $b) < 48) { $dup = true; break; }
        }
        if (!$dup) { $out[] = sprintf('#%02x%02x%02x', $r, $g, $b); }
        if (count($out) >= 3) { break; }
    }
    return $out ?: null;
}

echo json_encode(['success' => true, 'url' => $url, 'brand_colors' => iz_logo_palette($uploadsDir . '/' . $name)]);
