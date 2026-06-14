<?php
/**
 * AI Website Builder — Stage 3 Provisioning
 *
 * Turns a claimed/paid lead into a real, hosted website:
 *   WHM createacct (cPanel account on assigned package)
 *   -> write the lead's generated_html into the account's public_html
 *   -> mark lead converted (+ store cpanel username/domain)
 *   -> email the customer their login + DNS pointing instructions
 *
 * Deliberately per-lead and guarded — provisioning creates real accounts and
 * must only run once payment is confirmed (or manually for the pilot).
 *
 * CLI:  php scripts/provision-site.php <lead_id>
 * Web:  POST /scripts/provision-site.php   {token, lead_id}   (token = PROVISION_SECRET or PREVIEW_DEPLOY_SECRET)
 *
 * Env: SUPABASE_URL, SUPABASE_SERVICE_ROLE_KEY, WHM_HOST, WHM_USER,
 *      WHM_API_TOKEN, WHM_DEFAULT_PLAN, SERVER_IP, MAIL_FROM / SMTP_*.
 */

set_time_limit(0);
require_once __DIR__ . '/../config/env-loader.php';

$isCli = (php_sapi_name() === 'cli');

function pLog($msg, $ctx = []) {
    $e = date('Y-m-d H:i:s') . ' ' . $msg . (!empty($ctx) ? ' ' . json_encode($ctx) : '');
    $dir = dirname(__DIR__) . '/logs';
    if (!is_dir($dir)) { @mkdir($dir, 0750, true); }
    @file_put_contents($dir . '/provision.log', $e . PHP_EOL, FILE_APPEND | LOCK_EX);
}
function envOr($k, $d) { $v = getEnv($k); return ($v === null || $v === false || $v === '') ? $d : $v; }
function out($s) { global $isCli; echo $s . ($isCli ? "\n" : "<br>\n"); @flush(); }
function fail($msg, $http = 400) {
    global $isCli;
    if (!$isCli) { http_response_code($http); }
    out('ERROR: ' . $msg);
    exit(1);
}

// ---- Inputs / auth ----------------------------------------------------------
if ($isCli) {
    $leadId = $argv[1] ?? '';
} else {
    header('Content-Type: text/plain');
    $secret = trim((string)(envOr('PROVISION_SECRET', '') ?: envOr('PREVIEW_DEPLOY_SECRET', '')));
    $body = json_decode((string)file_get_contents('php://input'), true) ?: [];
    $token = $_POST['token'] ?? ($body['token'] ?? ($_GET['token'] ?? ''));
    if ($secret === '' || !hash_equals($secret, (string)$token)) { fail('Forbidden', 403); }
    $leadId = $_POST['lead_id'] ?? ($body['lead_id'] ?? ($_GET['lead_id'] ?? ''));
}
if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', (string)$leadId)) {
    fail('Invalid or missing lead_id');
}

// ---- Config -----------------------------------------------------------------
$supabaseUrl = rtrim((string)envOr('SUPABASE_URL', ''), '/');
$supabaseKey = trim((string)envOr('SUPABASE_SERVICE_ROLE_KEY', ''));
$whmHost = trim((string)envOr('WHM_HOST', ''));
$whmUser = trim((string)envOr('WHM_USER', ''));
$whmToken = trim((string)envOr('WHM_API_TOKEN', ''));
$plan = (string)envOr('WHM_DEFAULT_PLAN', '');
$serverIp = (string)envOr('SERVER_IP', '');
foreach (['SUPABASE_URL'=>$supabaseUrl,'SUPABASE_SERVICE_ROLE_KEY'=>$supabaseKey,'WHM_HOST'=>$whmHost,'WHM_USER'=>$whmUser,'WHM_API_TOKEN'=>$whmToken,'WHM_DEFAULT_PLAN'=>$plan] as $k=>$v) {
    if ($v === '') { fail("Missing config: $k", 500); }
}

// ---- Supabase helpers -------------------------------------------------------
function sb($method, $path, $body = null) {
    global $supabaseUrl, $supabaseKey;
    $ch = curl_init($supabaseUrl . $path);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['apikey: '.$supabaseKey,'Authorization: Bearer '.$supabaseKey,'Content-Type: application/json','Prefer: return=representation']);
    if ($body !== null) { curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body)); }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $r = curl_exec($ch); $c = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    return [$c, json_decode((string)$r, true)];
}

// ---- WHM helper -------------------------------------------------------------
function whm($func, $params = [], $cpanelUser = null) {
    global $whmHost, $whmUser, $whmToken;
    $url = "https://$whmHost/json-api/$func";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    $fields = [];
    foreach ($params as $k => $v) { $fields[] = rawurlencode($k) . '=' . rawurlencode($v); }
    curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $fields));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: whm ' . $whmUser . ':' . $whmToken]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // WHM commonly uses a hostname-mismatched cert
    curl_setopt($ch, CURLOPT_TIMEOUT, 90);
    $r = curl_exec($ch); $err = curl_error($ch); curl_close($ch);
    if ($r === false) { return [null, 'curl: ' . $err]; }
    return [json_decode($r, true), null];
}

function makeUsername($slug) {
    $base = strtolower(preg_replace('/[^a-z0-9]/i', '', $slug));
    if ($base === '' || !ctype_alpha($base[0])) { $base = 'z' . $base; }
    $base = substr($base, 0, 5);
    return $base . substr(bin2hex(random_bytes(3)), 0, 3); // <= 8 chars, starts with letter
}

// ---- Load the lead ----------------------------------------------------------
list($code, $rows) = sb('GET', '/rest/v1/site_builder_leads?id=eq.' . rawurlencode($leadId) . '&select=*');
if ($code !== 200 || !is_array($rows) || !count($rows)) { fail('Lead not found', 404); }
$lead = $rows[0];

if (in_array($lead['status'], ['converted'], true) && !empty($lead['cpanel_username'])) {
    out('Already provisioned: ' . $lead['cpanel_username']); exit(0);
}
$domain = trim((string)($lead['domain'] ?? ''));
$domain = preg_replace('#^https?://#', '', $domain);
$domain = preg_replace('#/.*$#', '', $domain);
if ($domain === '' || !preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/i', $domain)) {
    fail('Lead has no valid domain to provision. Collect a domain before provisioning.');
}
$html = (string)($lead['generated_html'] ?? '');
if (stripos($html, '<html') === false) { fail('Lead has no generated_html to deploy.'); }
$email = (string)($lead['contact_email'] ?? '');

$username = makeUsername($lead['preview_slug'] ?? $lead['business_name'] ?? 'site');
$password = bin2hex(random_bytes(9)) . 'Zx9!';

out("Provisioning {$lead['business_name']} -> $domain (user $username)");
pLog('Provision start', ['lead'=>$leadId,'domain'=>$domain,'user'=>$username]);

// ---- 1) createacct ----------------------------------------------------------
list($res, $e) = whm('createacct', [
    'api.version' => '1', 'username' => $username, 'domain' => $domain,
    'password' => $password, 'plan' => $plan, 'contactemail' => $email,
]);
if ($e || ($res['metadata']['result'] ?? 0) != 1) {
    $reason = $res['metadata']['reason'] ?? $e ?? 'unknown';
    pLog('createacct failed', ['reason'=>$reason]); fail('createacct failed: ' . $reason, 502);
}
out('  account created');

// ---- 2) deploy the generated site into public_html --------------------------
list($wres, $we) = whm('cpanel', [
    'cpanel_jsonapi_apiversion' => '3', 'cpanel_jsonapi_user' => $username,
    'cpanel_jsonapi_module' => 'Fileman', 'cpanel_jsonapi_func' => 'save_file_content',
    'dir' => '/public_html', 'file' => 'index.html', 'content' => $html,
]);
$wok = ($wres['result']['status'] ?? ($wres['result']['data']['path'] ?? null)) ? true : false;
if ($we || !$wok) {
    pLog('file deploy failed', ['err'=>$we, 'resp'=>substr(json_encode($wres),0,300)]);
    out('  WARNING: site file deploy failed — account exists, will need manual upload');
} else {
    out('  site deployed to public_html/index.html');
}

// ---- 3) mark converted ------------------------------------------------------
sb('PATCH', '/rest/v1/site_builder_leads?id=eq.' . rawurlencode($leadId), [
    'status' => 'converted', 'cpanel_username' => $username, 'cpanel_domain' => $domain,
    'plan' => $plan, 'provisioned_at' => gmdate('c'),
]);
out('  lead marked converted');

// ---- 4) email the customer --------------------------------------------------
$from = trim((string)(getenv('MAIL_FROM') ?: 'support@izendestudioweb.com'));
$cpUrl = 'https://' . preg_replace('/:\d+$/', '', $whmHost) . ':2083';
$subject = 'Your website is live: ' . $lead['business_name'];
$text = "Congratulations — your website is hosted and ready!\n\n"
    . "Site: https://$domain\n"
    . "cPanel login: $cpUrl\n  Username: $username\n  Password: $password\n\n"
    . ($serverIp ? "To point your domain here, set an A record for $domain to $serverIp (we can do this for you — just reply).\n\n" : '')
    . "Questions? Reply to this email or call (314) 312-6441.\n\n— Izende Studio Web\n";
$headers = "From: Izende Studio Web <$from>\r\nReply-To: support@izendestudioweb.com\r\nContent-Type: text/plain; charset=UTF-8";
foreach (array_unique([$email, 'support@izendestudioweb.com']) as $to) {
    if ($to) { @mail($to, $subject, $text, $headers); }
}
pLog('Provision complete', ['lead'=>$leadId,'user'=>$username,'domain'=>$domain]);
out('DONE — provisioned, converted, and emailed. cPanel user: ' . $username);
