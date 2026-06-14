<?php
/**
 * AI Website Builder — WordPress provisioning (Stage 5).
 *
 * Turns a claimed WordPress-tier lead into a real, hosted WordPress site that
 * MATCHES the claimed preview 1:1 and is editable in wp-admin:
 *   WHM createacct -> Softaculous WordPress install -> drop the Izende canvas
 *   mu-plugin + seed.json (transform of generated_html) -> trigger seed ->
 *   verify the rendered front page -> (real) email login + mark converted.
 *
 * DRY-RUN (no Supabase writes, auto-teardown of the throwaway account):
 *   GET /scripts/provision-wp.php?token=<PREVIEW_DEPLOY_SECRET>&dryrun=1[&lead_id=<uuid>]
 *   CLI: php scripts/provision-wp.php --dryrun [lead_id]
 *
 * Env: SUPABASE_URL, SUPABASE_SERVICE_ROLE_KEY, WHM_HOST, WHM_USER,
 *      WHM_API_TOKEN, WHM_DEFAULT_PLAN, SERVER_IP, MAIL_FROM.
 */

set_time_limit(0);
ignore_user_abort(true);
require_once __DIR__ . '/../config/env-loader.php';
require_once __DIR__ . '/lib/wp-provision.php';

$isCli = (php_sapi_name() === 'cli');

function pw_env($k, $d = '') { $v = getEnv($k); return ($v === null || $v === false || $v === '') ? $d : $v; }
function pw_out($s) { echo $s . "\n"; @flush(); }
function pw_log($msg, $ctx = []) {
    $dir = dirname(__DIR__) . '/logs';
    if (!is_dir($dir)) { @mkdir($dir, 0750, true); }
    @file_put_contents($dir . '/provision-wp.log', gmdate('c') . ' ' . $msg . (empty($ctx) ? '' : ' ' . json_encode($ctx)) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

// ---- inputs / auth ----------------------------------------------------------
$dryrun = false; $leadId = '';
if ($isCli) {
    $args = array_slice($argv, 1);
    $dryrun = in_array('--dryrun', $args, true);
    foreach ($args as $a) { if ($a !== '--dryrun') { $leadId = $a; } }
} else {
    header('Content-Type: text/plain; charset=utf-8');
    while (ob_get_level() > 0) { ob_end_flush(); }
    ob_implicit_flush(true);
    echo str_repeat(' ', 4096) . "starting\n"; flush();
    $secret = trim((string) pw_env('PREVIEW_DEPLOY_SECRET', ''));
    if ($secret === '' || !hash_equals($secret, (string) ($_GET['token'] ?? ''))) { http_response_code(403); echo "Forbidden\n"; exit; }
    $dryrun = !empty($_GET['dryrun']);
    $leadId = (string) ($_GET['lead_id'] ?? '');
}
if (!$dryrun) { pw_out('Only --dryrun is implemented in this build. Aborting.'); exit(1); }

// ---- config -----------------------------------------------------------------
$whmHost   = trim((string) pw_env('WHM_HOST', ''));
$whmUser   = trim((string) pw_env('WHM_USER', ''));
$whmToken  = trim((string) pw_env('WHM_API_TOKEN', ''));
$plan      = (string) pw_env('WHM_DEFAULT_PLAN', '');
$serverIp  = (string) pw_env('SERVER_IP', '');
$cpHost    = preg_replace('/:\d+$/', '', $whmHost) . ':2083';
foreach (['WHM_HOST'=>$whmHost,'WHM_USER'=>$whmUser,'WHM_API_TOKEN'=>$whmToken,'WHM_DEFAULT_PLAN'=>$plan,'SERVER_IP'=>$serverIp] as $k=>$v) {
    if ($v === '') { pw_out("Missing config: $k"); exit(1); }
}

// ---- helpers ----------------------------------------------------------------
function whm($func, $params, $timeout = 120) {
    global $whmHost, $whmUser, $whmToken;
    $ch = curl_init("https://$whmHost/json-api/$func");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: whm ' . $whmUser . ':' . $whmToken]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $r = curl_exec($ch); $e = curl_error($ch); curl_close($ch);
    return [$r === false ? null : json_decode($r, true), $e, $r];
}
// cPanel UAPI via the WHM reseller proxy (used for file writes into the account).
function cpanel_uapi($user, $module, $func, $params, $timeout = 90) {
    global $whmHost, $whmUser, $whmToken;
    $fields = array_merge([
        'cpanel_jsonapi_apiversion' => 3, 'cpanel_jsonapi_user' => $user,
        'cpanel_jsonapi_module' => $module, 'cpanel_jsonapi_func' => $func,
    ], $params);
    $ch = curl_init("https://$whmHost/json-api/cpanel");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: whm ' . $whmUser . ':' . $whmToken]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $r = curl_exec($ch); $e = curl_error($ch); curl_close($ch);
    return [$r === false ? null : json_decode($r, true), $e, $r];
}
// cPanel API2 via the WHM reseller proxy (UAPI v3 Fileman has no mkdir; API2 does).
function cpanel_api2($user, $module, $func, $params, $timeout = 60) {
    global $whmHost, $whmUser, $whmToken;
    $fields = array_merge([
        'cpanel_jsonapi_apiversion' => 2, 'cpanel_jsonapi_user' => $user,
        'cpanel_jsonapi_module' => $module, 'cpanel_jsonapi_func' => $func,
    ], $params);
    $ch = curl_init("https://$whmHost/json-api/cpanel");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: whm ' . $whmUser . ':' . $whmToken]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $r = curl_exec($ch); $e = curl_error($ch); curl_close($ch);
    return [$r === false ? null : json_decode($r, true), $e, $r];
}
// Softaculous enduser API (direct to cPanel :2083 with the account's Basic auth).
function softac($cpHost, $cpUser, $cpPass, $query, $post = null, $timeout = 180) {
    $themes = ['jupiter', 'paper_lantern']; // modern first, then legacy
    foreach ($themes as $theme) {
        $url = "https://$cpHost/frontend/$theme/softaculous/index.live.php?" . $query . '&api=json';
        $ch = curl_init($url);
        if ($post !== null) { curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post)); }
        curl_setopt($ch, CURLOPT_USERPWD, $cpUser . ':' . $cpPass);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $r = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); $e = curl_error($ch); curl_close($ch);
        if ($r !== false && $code !== 404 && stripos((string)$r, '<title>404') === false) {
            return [$theme, $code, $r, $e];
        }
    }
    return [null, 0, null, 'softaculous endpoint not found on any theme'];
}

// ---- sample / lead HTML -----------------------------------------------------
$generatedHtml = '';
if ($leadId !== '' && preg_match('/^[0-9a-f-]{36}$/i', $leadId)) {
    $base = rtrim((string) pw_env('SUPABASE_URL', ''), '/');
    $key  = trim((string) pw_env('SUPABASE_SERVICE_ROLE_KEY', ''));
    $ch = curl_init($base . '/rest/v1/site_builder_leads?id=eq.' . rawurlencode($leadId) . '&select=generated_html,business_name');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['apikey: '.$key, 'Authorization: Bearer '.$key]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $rows = json_decode((string) curl_exec($ch), true); curl_close($ch);
    $generatedHtml = (string) ($rows[0]['generated_html'] ?? '');
    pw_out('Loaded lead generated_html: ' . strlen($generatedHtml) . ' bytes');
}
if ($generatedHtml === '') {
    $generatedHtml = '<!DOCTYPE html><html><head><link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">'
        . '<style>:root{--c-bg:#0f172a;--c-accent:#38bdf8}body{margin:0;background:var(--c-bg);color:#fff;font-family:Poppins,sans-serif}'
        . 'header{padding:20px}h1{color:var(--c-accent)}</style></head>'
        . '<body><header>SAMPLE</header><h1 id="izwp-marker">Izende WP Dry-Run Works</h1><footer>footer</footer></body></html>';
    pw_out('Using built-in sample HTML (no lead).');
}

// ---- 1) create a throwaway account ------------------------------------------
$rand = substr(bin2hex(random_bytes(4)), 0, 6);
$domain = "izwptest{$rand}.com";
$user = 'izwp' . substr($rand, 0, 4);
$pass = bin2hex(random_bytes(10)) . 'Zx9!';
$adminUser = 'izadmin';
$adminPass = bin2hex(random_bytes(10)) . 'Wp7!';

pw_out("== DRY-RUN ==  throwaway: $domain (user $user)");
pw_log('dryrun start', ['domain'=>$domain,'user'=>$user]);

$teardown = function () use ($user) {
    pw_out("-- teardown: removeacct $user");
    list($r) = whm('removeacct', ['api.version'=>1, 'username'=>$user, 'keepdns'=>0]);
    pw_out('   removeacct result=' . (($r['metadata']['result'] ?? 0) == 1 ? 'ok' : 'FAILED ' . json_encode($r['metadata'] ?? [])));
};

list($res, $ce, $craw) = whm('createacct', ['api.version'=>1,'username'=>$user,'domain'=>$domain,'password'=>$pass,'plan'=>$plan,'contactemail'=>'dev@izendestudioweb.com']);
if (($res['metadata']['result'] ?? 0) != 1) { pw_out('createacct FAILED: ' . ($res['metadata']['reason'] ?? 'unknown')); exit(1); }
$acctIp = $res['data']['ip'] ?? ($res['metadata']['ip'] ?? $serverIp);
pw_out('  [1] account created (ip=' . $acctIp . ')');
pw_log('createacct', ['raw'=>substr((string)$craw,0,600)]);
sleep(2);

// ---- 2) install WP + seed the canvas, via the SHARED provisioning core -------
$cfg = ['WHM_HOST'=>$whmHost, 'WHM_USER'=>$whmUser, 'WHM_API_TOKEN'=>$whmToken];
$prov = izende_wp_provision($cfg, $user, $pass, $domain, 'dev@izendestudioweb.com', $generatedHtml, ($rows[0]['business_name'] ?? 'Izende WP Dry-Run'), 'pw_out');
if (!$prov['ok']) { pw_out('  PROVISION FAILED: ' . $prov['error']); $teardown(); exit(1); }
pw_out('  [2-5] provisioned via shared core; admin=' . $prov['admin_user']);

// ---- 4) trigger seed + verify render ----------------------------------------
sleep(1);
$fetch = function ($path, $label) use ($domain, $acctIp) {
    $ch = curl_init("http://$domain$path");
    curl_setopt($ch, CURLOPT_RESOLVE, ["$domain:80:$acctIp", "$domain:443:$acctIp"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 45);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120 Safari/537.36');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: text/html,application/xhtml+xml,*/*']);
    $r = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); $eff = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); curl_close($ch);
    pw_out("      [$label] http=$code bytes=" . strlen((string)$r) . " url=$eff");
    return (string) $r;
};
// Diagnostics: is WP even serving at the docroot? list docroot + probe wp-login.
list($ls) = cpanel_uapi($user, 'Fileman', 'list_files', ['dir'=>'/public_html', 'types'=>'file']);
$names = array_map(function($f){ return $f['file'] ?? ''; }, $ls['result']['data'] ?? []);
pw_out('      docroot files: ' . implode(', ', array_slice($names, 0, 25)));
// Wait out the cPanel httpd-rebuild lag: poll wp-login until the vhost is live.
$serving = false;
for ($i = 1; $i <= 12; $i++) {
    $wplogin = $fetch('/wp-login.php', "wp-login try$i");
    if (stripos($wplogin, 'wordpress') !== false || stripos($wplogin, 'user_login') !== false) { $serving = true; break; }
    sleep(8);
}
pw_out('      WP serving at domain: ' . ($serving ? 'YES' : 'NO (vhost still not live)'));

$fetch('/', 'seed-trigger');       // first hit runs init -> seeds
$home = $fetch('/', 'render');     // second hit -> canvas render

$hasCss   = stripos($home, '--c-') !== false || stripos($home, '<style') !== false;
$hasBody  = stripos($home, '<header') !== false || stripos($home, 'izwp-marker') !== false;
$noTheme  = stripos($home, 'wp-content/themes') === false; // canvas should NOT load a theme stylesheet
pw_out('  [6] VERIFY: css=' . ($hasCss?'Y':'N') . ' bodyhtml=' . ($hasBody?'Y':'N') . ' no-theme-css=' . ($noTheme?'Y':'N'));
pw_out('      home (first 300b): ' . substr(preg_replace('/\s+/',' ', $home), 0, 300));

// ---- 6b) RESEED test: live WordPress edit re-apply --------------------------
$reseedOk = false;
if ($serving) {
    $marker = 'RESEED_OK_' . substr(bin2hex(random_bytes(3)), 0, 6);
    $edited = preg_replace('/<\/body>/i', '<div id="izReseedMarker">' . $marker . '</div></body>', $generatedHtml, 1);
    $cfg = ['WHM_HOST'=>$whmHost, 'WHM_USER'=>$whmUser, 'WHM_API_TOKEN'=>$whmToken];
    $rok = izende_wp_reseed($cfg, $user, $edited, ($rows[0]['business_name'] ?? 'Test'), $domain, 'pw_out');
    sleep(2);
    $fetch('/', 'reseed-trigger');
    $h2 = $fetch('/', 'reseed-render');
    $reseedOk = (stripos($h2, $marker) !== false);
    pw_out('  [7] RESEED (live WP edit): wrote=' . ($rok ? 'Y' : 'N') . ' marker-on-live=' . ($reseedOk ? 'Y' : 'N'));
}

// ---- 7) teardown ------------------------------------------------------------
$teardown();
$ok = $hasCss && $hasBody;
pw_out("\n" . ($ok ? '== DRY-RUN PASS ==' : '== DRY-RUN INCOMPLETE (see above) ==') . ($serving ? ('  | reseed: ' . ($reseedOk ? 'PASS' : 'FAIL')) : ''));
pw_log('dryrun done', ['ok'=>$ok, 'reseed'=>$reseedOk]);
