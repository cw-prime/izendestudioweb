<?php
/**
 * AI Website Builder — Tenant Booking Endpoint
 *
 * Receives appointment requests from a customer's generated site (which may be
 * served from THEIR own domain), records them in iz_bookings tagged to the
 * tenant lead, and emails the business owner + the customer.
 *
 * Cross-domain by design: authenticated by a per-site HMAC token baked into the
 * form (not a session CSRF, which can't cross domains) + honeypot + rate limit.
 *
 * If the site hasn't paid for booking yet (preview), it returns an upsell nudge
 * so the booking form on the free preview doubles as a conversion driver.
 *
 * POST JSON: { lead_id, token, name, email, phone, service, preferred_date,
 *              preferred_time, message, website(honeypot) }
 */

require_once __DIR__ . '/../config/env-loader.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../admin/config/database.php';

// ---- CORS (forms live on customer domains) ----
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
header('Cache-Control: no-store');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit; }

function siteBookingToken($leadId) {
    return substr(hash_hmac('sha256', 'site:' . $leadId, (string)getEnv('SITE_BOOKING_SECRET', '')), 0, 40);
}
function ownerBookingToken($leadId) {
    return substr(hash_hmac('sha256', 'owner:' . $leadId, (string)getEnv('SITE_BOOKING_SECRET', '')), 0, 40);
}

// ---- rate limit ----
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (function_exists('checkRateLimit') && !checkRateLimit($ip . '_site_booking', 6, 600)) {
    http_response_code(429); echo json_encode(['success'=>false,'message'=>'Too many requests. Please try again shortly.']); exit;
}

$data = json_decode((string)file_get_contents('php://input'), true);
if (!is_array($data)) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid request.']); exit; }

// honeypot — bots fill hidden fields
if (trim((string)($data['website'] ?? '')) !== '') { echo json_encode(['success'=>true,'message'=>'Thank you!']); exit; }

$leadId = (string)($data['lead_id'] ?? '');
$token  = (string)($data['token'] ?? '');
if (!preg_match('/^[0-9a-f-]{36}$/i', $leadId) || !hash_equals(siteBookingToken($leadId), $token)) {
    http_response_code(403); echo json_encode(['success'=>false,'message'=>'This booking form is not valid. Please contact the business directly.']); exit;
}

// ---- validate the human inputs ----
$name    = trim((string)($data['name'] ?? ''));
$email   = trim((string)($data['email'] ?? ''));
$phone   = trim((string)($data['phone'] ?? ''));
$service = trim((string)($data['service'] ?? ''));
$message = trim((string)($data['message'] ?? ''));
$dateStr = trim((string)($data['preferred_date'] ?? ''));
$timeStr = trim((string)($data['preferred_time'] ?? ''));
if ($name === '' || ($email === '' && $phone === '') || $dateStr === '') {
    http_response_code(400); echo json_encode(['success'=>false,'message'=>'Please add your name, a way to reach you, and a preferred date.']); exit;
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400); echo json_encode(['success'=>false,'message'=>'Please enter a valid email address.']); exit;
}
$ts = strtotime(trim($dateStr . ' ' . $timeStr));
if ($ts === false) { $ts = strtotime($dateStr); }
$preferredDate = $ts ? date('Y-m-d H:i:s', $ts) : date('Y-m-d H:i:s');

// ---- look up the tenant lead (business owner to notify + enabled flag) ----
$base = rtrim((string)getEnv('SUPABASE_URL', ''), '/');
$key  = trim((string)getEnv('SUPABASE_SERVICE_ROLE_KEY', ''));
$lead = null;
if ($base !== '' && $key !== '') {
    $ch = curl_init($base . '/rest/v1/site_builder_leads?id=eq.' . rawurlencode($leadId) . '&select=business_name,contact_email,domain,preview_slug,booking_enabled&limit=1');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['apikey: ' . $key, 'Authorization: Bearer ' . $key]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_TIMEOUT, 12);
    $rows = json_decode((string)curl_exec($ch), true); curl_close($ch);
    $lead = is_array($rows) && count($rows) ? $rows[0] : null;
}
if (!$lead) { http_response_code(404); echo json_encode(['success'=>false,'message'=>'Site not found.']); exit; }

// ---- not paid yet -> the preview form becomes an upsell ----
if (empty($lead['booking_enabled'])) {
    $slug = (string)($lead['preview_slug'] ?? '');
    echo json_encode([
        'success'  => false,
        'upsell'   => true,
        'message'  => 'Online booking activates once you add it to your plan. Claim your site to turn it on.',
        'claim_url'=> 'https://izendestudioweb.com/claim-site.php' . ($slug !== '' ? '?slug=' . rawurlencode($slug) : ''),
    ]);
    exit;
}

// ---- record the booking (tagged to the tenant) ----
$business = (string)($lead['business_name'] ?? '');
$ownerEmail = (string)($lead['contact_email'] ?? '');
$duration = 60;
$stmt = $conn->prepare("INSERT INTO iz_bookings
    (client_name, client_email, client_phone, service_type, preferred_date, duration, message, status, tenant_lead_id, tenant_business)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)");
if (!$stmt) { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Could not save your request. Please try again.']); exit; }
$stmt->bind_param('sssssisss', $name, $email, $phone, $service, $preferredDate, $duration, $message, $leadId, $business);
if (!$stmt->execute()) { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Could not save your request. Please try again.']); exit; }

// ---- notify the business owner + confirm to the customer ----
$from = 'bookings@izendestudioweb.com';
$when = date('l, M j Y \a\t g:i A', $ts ?: time());
$manageUrl = 'https://izendestudioweb.com/my-bookings.php?lead=' . rawurlencode($leadId) . '&t=' . ownerBookingToken($leadId);
if ($ownerEmail !== '') {
    $sub = 'New booking request — ' . ($service !== '' ? $service . ' ' : '') . 'for ' . ($business ?: 'your site');
    $body = "You have a new appointment request from your website:\n\n"
        . "Name:    $name\n"
        . ($email !== '' ? "Email:   $email\n" : '')
        . ($phone !== '' ? "Phone:   $phone\n" : '')
        . ($service !== '' ? "Service: $service\n" : '')
        . "Requested: $when\n"
        . ($message !== '' ? "Notes:   $message\n" : '')
        . "\nView & confirm your bookings: $manageUrl\n\n— Izende Studio Web\n";
    @mail($ownerEmail, $sub, $body, "From: Izende Booking <$from>\r\nReply-To: " . ($email ?: $from) . "\r\nContent-Type: text/plain; charset=UTF-8");
}
if ($email !== '') {
    $body = "Hi $name,\n\nThanks — your request" . ($business ? " with $business" : '') . " has been received:\n\n"
        . ($service !== '' ? "Service: $service\n" : '')
        . "Requested: $when\n\n"
        . "You'll get a confirmation once it's approved. Need to change it? Just reply to this email.\n\n— "
        . ($business ?: 'The team') . "\n";
    @mail($email, 'We received your request' . ($business ? " — $business" : ''), $body, "From: " . ($business ?: 'Bookings') . " <$from>\r\nContent-Type: text/plain; charset=UTF-8");
}

echo json_encode(['success'=>true,'message'=>'Request received! You\'ll get a confirmation shortly.']);
