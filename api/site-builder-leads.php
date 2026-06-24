<?php
/**
 * AI Website Builder — Lead Intake API
 *
 * Accepts the "Build Your Website with AI" intake form and writes the lead to
 * the Supabase `site_builder_leads` queue (status=pending). The n8n generation
 * workflow polls that table — the website never talks to n8n directly.
 *
 * Mirrors the validation/security flow of api/booking.php; the only difference
 * is the storage target (Supabase PostgREST via cURL instead of local mysqli).
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/env-loader.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../includes/SpamProtection.php';
require_once __DIR__ . '/../includes/gen-cap.php';

initSecureSession();

/**
 * Write debug info to error_log and a local log file (best-effort).
 */
function siteBuilderLog($message, $context = []) {
    $entry = date('Y-m-d H:i:s') . ' ' . $message;
    if (!empty($context)) {
        $entry .= ' ' . json_encode($context);
    }

    error_log($entry);

    $appLogDir = dirname(__DIR__) . '/logs';
    if (!is_dir($appLogDir)) {
        @mkdir($appLogDir, 0750, true);
    }
    @file_put_contents($appLogDir . '/site-builder.log', $entry . PHP_EOL, FILE_APPEND | LOCK_EX);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Rate limiting: 3 attempts per 10 minutes per IP
// Dev/admin bypass: requests from localhost or the server's own IP skip rate limiting.
$_devBypassIps = ['127.0.0.1', '::1', '192.168.1.253'];
$_remoteIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$_isDevBypass = in_array($_remoteIp, $_devBypassIps, true);

$identifier = $_remoteIp . '_site_builder';
if (!$_isDevBypass && function_exists('checkRateLimit') && !checkRateLimit($identifier, 3, 600)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many requests. Please try again in a few minutes.']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    siteBuilderLog('Invalid JSON payload', ['raw_length' => strlen($raw)]);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request payload.']);
    exit;
}

if (!validateCSRFToken($data['csrf_token'] ?? '')) {
    siteBuilderLog('Invalid CSRF token', ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Your session expired. Please refresh the page and try again.']);
    exit;
}

// Optional reCAPTCHA (only enforced when RECAPTCHA_SECRET_KEY is configured)
$recaptchaSecret = trim((string)getEnv('RECAPTCHA_SECRET_KEY', ''));
$recaptchaResponse = trim((string)($data['g-recaptcha-response'] ?? ''));

if ($recaptchaSecret !== '' && $recaptchaResponse !== '') {
    $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'secret' => $recaptchaSecret,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $verifyResponse = curl_exec($ch);
    $verifyHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $verifyResult = ($verifyResponse !== false && $verifyHttp === 200) ? json_decode($verifyResponse, true) : null;
    if (!isset($verifyResult['success']) || $verifyResult['success'] !== true) {
        siteBuilderLog('reCAPTCHA verification rejected submission', ['http_code' => $verifyHttp]);
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'reCAPTCHA verification failed. Please try again.']);
        exit;
    }
}

// Extract and validate fields
$businessName        = trim((string)($data['business_name'] ?? ''));
// Drop a trailing legal suffix (LLC, Inc., Corp., Ltd., etc.) so the brand shown on the site is clean.
$businessName        = trim(preg_replace('/[\s,]+(?:LLC|L\.L\.C\.?|Inc\.?|Incorporated|Corp\.?|Corporation|Co\.|Ltd\.?|LLP|PLLC)\.?\s*$/i', '', $businessName));
$businessDescription = trim((string)($data['business_description'] ?? ''));
$styleVibe           = trim((string)($data['style_vibe'] ?? ''));
$email               = filter_var($data['contact_email'] ?? '', FILTER_SANITIZE_EMAIL);
$phone               = trim((string)($data['contact_phone'] ?? ''));
$domain              = trim((string)($data['domain'] ?? ''));

// Optional logo (uploaded via api/upload-logo.php), brand colors (from that logo), and a
// captured street address. Validate strictly so we never store an attacker-supplied URL.
$logoUrl = trim((string)($data['logo_url'] ?? ''));
if ($logoUrl !== '' && !preg_match('~^https://izendestudioweb\.com/genmedia/uploads/[\w-]+\.(?:png|jpe?g|webp|gif)$~i', $logoUrl)) {
    $logoUrl = '';
}
$brandColors = null;
if (!empty($data['brand_colors']) && is_array($data['brand_colors'])) {
    $clean = [];
    foreach ($data['brand_colors'] as $c) {
        if (is_string($c) && preg_match('/^#[0-9a-fA-F]{6}$/', $c)) { $clean[] = strtolower($c); }
    }
    if ($clean) { $brandColors = json_encode(array_slice(array_values(array_unique($clean)), 0, 4)); }
}
$socialLinks = null;
if (!empty($data['social_links']) && is_array($data['social_links'])) {
    $allowedSocial = ['facebook', 'instagram', 'x', 'linkedin', 'tiktok', 'youtube', 'google'];
    $cleanSocial = [];
    foreach ($allowedSocial as $platform) {
        $url = trim((string)($data['social_links'][$platform] ?? ''));
        if ($url === '') { continue; }
        if (mb_strlen($url) > 300 || !filter_var($url, FILTER_VALIDATE_URL)) { continue; }
        $parts = parse_url($url);
        if (!is_array($parts) || strtolower((string)($parts['scheme'] ?? '')) !== 'https' || empty($parts['host'])) { continue; }
        $cleanSocial[$platform] = $url;
    }
    if ($cleanSocial) { $socialLinks = json_encode($cleanSocial); }
}
$uploadedPhotos = null;
if (!empty($data['uploaded_photos']) && is_array($data['uploaded_photos'])) {
    $cleanPhotos = [];
    foreach ($data['uploaded_photos'] as $url) {
        if (!is_string($url)) { continue; }
        $url = trim($url);
        if (preg_match('~^https://izendestudioweb\.com/genmedia/uploads/photo-[\w-]+\.(?:png|jpe?g|webp|gif)$~i', $url)) {
            $cleanPhotos[] = $url;
        }
    }
    $cleanPhotos = array_slice(array_values(array_unique($cleanPhotos)), 0, 7);
    if ($cleanPhotos) { $uploadedPhotos = json_encode($cleanPhotos); }
}
$businessAddress = trim((string)($data['business_address'] ?? ''));
$businessAddress = preg_replace('/[\x00-\x1f]+/', ' ', $businessAddress);
if (mb_strlen($businessAddress) > 200) { $businessAddress = ''; }

if ($businessName === '' || !validateLength($businessName, 2, 120)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please enter your business name.']);
    exit;
}

if ($businessDescription === '' || !validateLength($businessDescription, 10, 8000)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please describe what your business does (at least a sentence).']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'A valid email is required so we can send your preview.']);
    exit;
}

// Spam checks (honeypot/timing/content/IP)
$spamCheck = SpamProtection::validateSubmission('site_builder', $data, [
    'check_honeypot' => true,
    'check_timing' => true,
    'check_ip' => true,
    'check_content' => true,
    'min_seconds' => 5,
    'max_seconds' => 1800
]);

if ($spamCheck['is_spam']) {
    siteBuilderLog('Spam blocked submission', ['reason' => $spamCheck['reason'], 'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => "Hmm — that didn't go through. Please give it another try, or call us at (314) 312-6441 and we'll get your draft started."]);
    exit;
}

// Free-generation cap: signed cookie counts previews; gate once exhausted.
if (iz_gen_read()['remaining'] <= 0) {
    echo json_encode([
        'success' => false,
        'limit'   => true,
        'message' => "Claim your draft to keep going — hosting, your domain and email all set up for you.",
        'claim_url' => 'https://izendestudioweb.com/claim-site.php',
    ]);
    exit;
}

// Supabase configuration (cloud queue bridged between the cPanel site and the basement n8n box)
$supabaseUrl = rtrim((string)getEnv('SUPABASE_URL', ''), '/');
$supabaseKey = trim((string)getEnv('SUPABASE_SERVICE_ROLE_KEY', ''));

if ($supabaseUrl === '' || $supabaseKey === '') {
    siteBuilderLog('Supabase env vars missing', ['has_url' => $supabaseUrl !== '', 'has_key' => $supabaseKey !== '']);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'We could not start your build right now. Please try again later.']);
    exit;
}

// Anti-abuse backstop: cap leads per IP per 24h (catches cookie-clearers at scale).
$ipAddr = $_SERVER['REMOTE_ADDR'] ?? '';
if (!$_isDevBypass && $ipAddr !== '') {
    $since = gmdate('Y-m-d\TH:i:s\Z', time() - 86400);
    $cch = curl_init($supabaseUrl . '/rest/v1/site_builder_leads?select=id&ip_address=eq.' . rawurlencode($ipAddr) . '&created_at=gte.' . rawurlencode($since));
    curl_setopt($cch, CURLOPT_HTTPHEADER, ['apikey: ' . $supabaseKey, 'Authorization: Bearer ' . $supabaseKey, 'Prefer: count=exact', 'Range: 0-0']);
    curl_setopt($cch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($cch, CURLOPT_HEADER, true);
    curl_setopt($cch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($cch, CURLOPT_TIMEOUT, 8);
    $cres = (string) curl_exec($cch);
    curl_close($cch);
    if (preg_match('~Content-Range:\s*\d+-\d+/(\d+)~i', $cres, $m) && (int)$m[1] > 8) {
        siteBuilderLog('IP daily generation backstop hit', ['ip' => $ipAddr, 'count' => $m[1]]);
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => 'Too many previews from your network today. Please try again tomorrow or claim a site.']);
        exit;
    }
}

// Insert lead into the Supabase queue via PostgREST
$lead = [
    'business_name'        => $businessName,
    'business_description' => $businessDescription,
    'style_vibe'           => $styleVibe !== '' ? $styleVibe : null,
    'contact_email'        => $email,
    'contact_phone'        => $phone !== '' ? $phone : null,
    'domain'               => $domain !== '' ? $domain : null,
    'status'               => 'pending',
    'ip_address'           => $_SERVER['REMOTE_ADDR'] ?? null,
    'logo_url'             => $logoUrl !== '' ? $logoUrl : null,
    'brand_colors'         => $brandColors,
    'business_address'     => $businessAddress !== '' ? $businessAddress : null,
];
if ($socialLinks !== null) { $lead['social_links'] = $socialLinks; }
if ($uploadedPhotos !== null) { $lead['uploaded_photos'] = $uploadedPhotos; }

$ch = curl_init($supabaseUrl . '/rest/v1/site_builder_leads');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . $supabaseKey,
    'Authorization: Bearer ' . $supabaseKey,
    'Content-Type: application/json',
    'Prefer: return=representation',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($lead));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($response === false || $httpCode < 200 || $httpCode >= 300) {
    siteBuilderLog('Supabase insert failed', ['http_code' => $httpCode, 'curl_error' => $curlErr, 'body' => substr((string)$response, 0, 500)]);
    http_response_code(502);
    echo json_encode(['success' => false, 'message' => 'We could not save your request right now. Please try again shortly.']);
    exit;
}

// Extract the new lead id so the browser can poll for its preview as it builds.
$leadId = '';
$decoded = json_decode((string)$response, true);
if (is_array($decoded) && isset($decoded[0]['id'])) {
    $leadId = (string)$decoded[0]['id'];
}

siteBuilderLog('Lead queued', ['email' => $email, 'business' => $businessName, 'id' => $leadId]);

// Count this successful generation against the visitor's free cap.
iz_gen_bump();

// Kick the generator NOW so the prospect can watch their site build in real time,
// rather than waiting for the next cron tick. Fire-and-forget: the generator runs
// with ignore_user_abort, so it keeps working after this short-timeout call drops.
$deploySecret = trim((string)getEnv('PREVIEW_DEPLOY_SECRET', ''));
if ($deploySecret !== '') {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'izendestudioweb.com';
    $triggerUrl = $scheme . '://' . $host . '/scripts/generate-pending-previews.php?token=' . rawurlencode($deploySecret);
    $kick = curl_init($triggerUrl);
    curl_setopt($kick, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($kick, CURLOPT_NOSIGNAL, true);
    curl_setopt($kick, CURLOPT_TIMEOUT_MS, 1200);        // return fast; generator continues on its own
    curl_setopt($kick, CURLOPT_CONNECTTIMEOUT_MS, 1000);
    curl_setopt($kick, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($kick, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64; rv:127.0) Gecko/20100101 Firefox/127.0'); // prod ModSecurity 406s non-browser UAs
    curl_exec($kick);
    curl_close($kick);
}

// Rotate CSRF token for the next submission
if (function_exists('regenerateCSRFToken')) {
    regenerateCSRFToken();
}

http_response_code(201);
echo json_encode([
    'success' => true,
    'lead_id' => $leadId,
    'message' => "You're in! We're building your free preview now."
]);
