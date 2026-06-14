<?php
/**
 * Flip booking_enabled for a lead (turns the booking widget live on their site).
 * Used as the safety-valve / manual enable until the WHMCS "Online Booking"
 * add-on hook is wired; also handy for testing.
 *
 *   GET /scripts/enable-booking.php?token=<PREVIEW_DEPLOY_SECRET>&lead=<uuid>[&off=1]
 */
require_once __DIR__ . '/../config/env-loader.php';
header('Content-Type: text/plain; charset=utf-8');

$secret = trim((string)getEnv('PREVIEW_DEPLOY_SECRET', ''));
if ($secret === '' || !hash_equals($secret, (string)($_GET['token'] ?? ''))) { http_response_code(403); echo "Forbidden\n"; exit; }

$lead = (string)($_GET['lead'] ?? '');
if (!preg_match('/^[0-9a-f-]{36}$/i', $lead)) { echo "Invalid lead\n"; exit(1); }
$enable = empty($_GET['off']);

$base = rtrim((string)getEnv('SUPABASE_URL', ''), '/');
$key  = trim((string)getEnv('SUPABASE_SERVICE_ROLE_KEY', ''));
$ch = curl_init($base . '/rest/v1/site_builder_leads?id=eq.' . rawurlencode($lead));
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['apikey: '.$key, 'Authorization: Bearer '.$key, 'Content-Type: application/json', 'Prefer: return=representation']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['booking_enabled' => $enable]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$resp = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
echo "booking_enabled=" . ($enable ? 'true' : 'false') . " http=$code\n$resp\n";
