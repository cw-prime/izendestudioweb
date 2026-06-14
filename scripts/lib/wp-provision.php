<?php
/**
 * Shared WordPress provisioning core (proven by scripts/provision-wp.php dry-run).
 *
 * izende_wp_provision() turns an already-created cPanel account into a hosted
 * WordPress site that matches the claimed preview 1:1 and is editable in
 * wp-admin. Called by BOTH the dry-run harness and the live WHMCS hook so the
 * tested path and the production path are the exact same code.
 *
 * It does NOT create or remove the cPanel account, and does NOT touch Supabase —
 * the caller owns those. Best-effort and defensive: returns a result array,
 * never throws.
 */

require_once __DIR__ . '/html-to-wp.php';

function iwp_whm($cfg, $func, $params, $timeout = 120) {
    $ch = curl_init('https://' . $cfg['WHM_HOST'] . '/json-api/' . $func);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: whm ' . ($cfg['WHM_USER'] ?? '') . ':' . ($cfg['WHM_API_TOKEN'] ?? '')]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $r = curl_exec($ch); curl_close($ch);
    return $r === false ? null : json_decode($r, true);
}
function iwp_cpanel($cfg, $apiversion, $user, $module, $func, $params, $timeout = 90) {
    $fields = array_merge([
        'cpanel_jsonapi_apiversion' => $apiversion, 'cpanel_jsonapi_user' => $user,
        'cpanel_jsonapi_module' => $module, 'cpanel_jsonapi_func' => $func,
    ], $params);
    $ch = curl_init('https://' . $cfg['WHM_HOST'] . '/json-api/cpanel');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: whm ' . ($cfg['WHM_USER'] ?? '') . ':' . ($cfg['WHM_API_TOKEN'] ?? '')]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $r = curl_exec($ch); curl_close($ch);
    return $r === false ? null : json_decode($r, true);
}
// Softaculous enduser API (direct to cPanel :2083 with the account's Basic auth).
function iwp_softac($cpHost, $cpUser, $cpPass, $query, $post = null, $timeout = 240) {
    foreach (['jupiter', 'paper_lantern'] as $theme) {
        $url = "https://$cpHost/frontend/$theme/softaculous/index.live.php?" . $query . '&api=json';
        $ch = curl_init($url);
        if ($post !== null) { curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post)); }
        curl_setopt($ch, CURLOPT_USERPWD, $cpUser . ':' . $cpPass);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $r = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        if ($r !== false && $code !== 404 && stripos((string)$r, '<title>404') === false) {
            return [$theme, $code, $r];
        }
    }
    return [null, 0, null];
}

/**
 * @return array{ok:bool, admin_user:string, admin_pass:string, wp_url:string, login_url:string, error:string}
 */
function izende_wp_provision($cfg, $username, $cpPass, $domain, $email, $generatedHtml, $businessName, $log = null) {
    $say = is_callable($log) ? $log : function () {};
    $out = ['ok'=>false, 'admin_user'=>'', 'admin_pass'=>'', 'wp_url'=>'', 'login_url'=>'', 'error'=>''];

    $cpHost = preg_replace('/:\d+$/', '', (string)($cfg['WHM_HOST'] ?? '')) . ':2083';
    if (empty($cfg['WHM_HOST']) || $username === '' || $cpPass === '' || $domain === '') {
        $out['error'] = 'missing inputs'; return $out;
    }

    $adminUser = 'owner' . substr(bin2hex(random_bytes(3)), 0, 4);
    $adminPass = bin2hex(random_bytes(10)) . 'Wp7!';
    $proto = 1; // http:// — AutoSSL upgrades to https once DNS/cert settle

    // 1) Install WordPress via Softaculous.
    $install = [
        'softsubmit'=>1, 'soft'=>26, 'softproto'=>$proto, 'softdomain'=>$domain, 'softdirectory'=>'',
        'admin_username'=>$adminUser, 'admin_pass'=>$adminPass, 'admin_email'=>$email ?: ('support@' . $domain),
        'language'=>'en', 'site_name'=>($businessName ?: $domain), 'site_desc'=>'',
    ];
    list($theme, $icode, $iraw) = iwp_softac($cpHost, $username, $cpPass, 'act=software&soft=26', $install, 300);
    $ij = json_decode((string)$iraw, true);
    $installed = is_array($ij) && (!empty($ij['done']) || stripos((string)$iraw, 'successfully installed') !== false);
    $say('wp install: theme=' . $theme . ' http=' . $icode . ' installed=' . ($installed ? 'yes' : 'no'));
    if (!$installed) { $out['error'] = 'softaculous install failed (http=' . $icode . ')'; return $out; }

    // 2) Transform the claimed preview into the canvas seed.
    $t = htmlToWp($generatedHtml);
    $seed = json_encode([
        'head'=>$t['head'], 'body'=>$t['body'], 'body_class'=>$t['body_class'],
        'site_name'=>($businessName ?: $domain),
        'ver'=>substr(sha1($t['body'] . $t['head']), 0, 12),
    ]);

    // 3) Drop the canvas mu-plugin + seed.json (mkdir via API2; writes via UAPI v3).
    iwp_cpanel($cfg, 2, $username, 'Fileman', 'mkdir', ['path'=>'public_html/wp-content', 'name'=>'mu-plugins']);
    $plugin = @file_get_contents(__DIR__ . '/izende-canvas.php');
    $w1 = iwp_cpanel($cfg, 3, $username, 'Fileman', 'save_file_content', ['dir'=>'/public_html/wp-content/mu-plugins', 'file'=>'izende-canvas.php', 'content'=>(string)$plugin]);
    $w2 = iwp_cpanel($cfg, 3, $username, 'Fileman', 'save_file_content', ['dir'=>'/public_html/wp-content', 'file'=>'izende-seed.json', 'content'=>$seed]);
    $wok = (($w1['result']['status'] ?? 0) && ($w2['result']['status'] ?? 0));
    $say('dropped canvas + seed: ' . ($wok ? 'ok' : 'FAILED'));
    if (!$wok) { $out['error'] = 'failed to write canvas/seed files'; return $out; }

    // 4) Best-effort: trigger the one-time seed so the site is ready on first visit.
    $ch = curl_init('http://' . $domain . '/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; IzendeProvision/1.0)');
    @curl_exec($ch); curl_close($ch);

    $out['ok'] = true;
    $out['admin_user'] = $adminUser;
    $out['admin_pass'] = $adminPass;
    $out['wp_url']     = 'http://' . $domain . '/';
    $out['login_url']  = 'http://' . $domain . '/wp-login.php';
    $say('wp provision complete for ' . $domain);
    return $out;
}

/**
 * Re-apply an edit to a LIVE WordPress site: drop a new versioned seed.json (+
 * refresh the canvas mu-plugin so older installs gain re-seed logic), then hit
 * the homepage so the mu-plugin updates the Home page content + CSS in place.
 * Returns true on a successful seed write.
 */
function izende_wp_reseed($cfg, $user, $generatedHtml, $businessName, $domain, $log = null) {
    $say = is_callable($log) ? $log : function () {};
    if (empty($cfg['WHM_HOST']) || $user === '') { $say('reseed: missing cfg/user'); return false; }

    $t = htmlToWp($generatedHtml);
    $seed = json_encode([
        'head'=>$t['head'], 'body'=>$t['body'], 'body_class'=>$t['body_class'],
        'site_name'=>($businessName ?: $domain),
        'ver'=>substr(sha1($t['body'] . $t['head']), 0, 12),
    ]);
    $w = iwp_cpanel($cfg, 3, $user, 'Fileman', 'save_file_content', ['dir'=>'/public_html/wp-content', 'file'=>'izende-seed.json', 'content'=>$seed]);
    $ok = ($w['result']['status'] ?? 0) ? true : false;
    $say('reseed seed.json write: ' . ($ok ? 'ok' : 'FAILED'));
    if (!$ok) { return false; }

    // Keep the mu-plugin current (re-seed logic) for sites provisioned earlier.
    $plugin = @file_get_contents(__DIR__ . '/izende-canvas.php');
    if ($plugin !== false) {
        iwp_cpanel($cfg, 3, $user, 'Fileman', 'save_file_content', ['dir'=>'/public_html/wp-content/mu-plugins', 'file'=>'izende-canvas.php', 'content'=>$plugin]);
    }

    // Trigger the mu-plugin to pick up the new version.
    if ($domain !== '') {
        $ch = curl_init('http://' . $domain . '/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; IzendeProvision/1.0)');
        @curl_exec($ch); curl_close($ch);
    }
    return true;
}
