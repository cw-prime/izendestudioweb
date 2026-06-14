<?php
/**
 * Izende AI Website — post-provision hook.
 *
 * When WHMCS finishes creating the cPanel account, this fires and finishes the
 * site for the customer based on which product they bought:
 *   - pid 14 (AI Website, static):  write the lead's generated_html into
 *     public_html/index.html.
 *   - pid 15/16 (AI Website WordPress / Managed): install WordPress via
 *     Softaculous and seed it from the same generated_html so the live site
 *     MATCHES the claimed preview 1:1 and is editable in wp-admin
 *     (see scripts/lib/wp-provision.php — proven by the dry-run harness).
 * Then it marks the matching site_builder_lead 'converted' in Supabase.
 *
 * Defensive by design: gated strictly to products 14/15/16, fully wrapped in
 * try/catch, never throws — a failure here can never block WHMCS provisioning.
 *
 * Install path: <whmcs>/includes/hooks/zeno_provision.php
 */

if (!defined('WHMCS')) { die('Access denied'); }

if (!function_exists('zeno_log')) {
    function zeno_log($msg, $ctx = []) {
        $f = dirname(__DIR__, 3) . '/logs/zeno-hook.log'; // <public_html>/logs/
        @file_put_contents($f, gmdate('c') . ' ' . $msg . (empty($ctx) ? '' : ' ' . json_encode($ctx)) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
if (!function_exists('zeno_env')) {
    function zeno_env() {
        $o = [];
        $raw = @file_get_contents(dirname(__DIR__, 3) . '/config/.env');
        if ($raw === false) { return $o; }
        foreach (explode("\n", $raw) as $l) {
            $l = trim($l);
            if ($l === '' || $l[0] === '#' || strpos($l, '=') === false) { continue; }
            list($k, $v) = explode('=', $l, 2);
            $o[trim($k)] = trim($v);
        }
        return $o;
    }
}
if (!function_exists('zeno_http')) {
    function zeno_http($url, $headers, $post = null, $timeout = 30) {
        $ch = curl_init($url);
        if ($post !== null) { curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, $post); }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $r = curl_exec($ch); curl_close($ch);
        return $r;
    }
}
if (!function_exists('zeno_find_lead')) {
    function zeno_find_lead($cfg, $email, $domain) {
        $base = rtrim($cfg['SUPABASE_URL'] ?? '', '/');
        $key = $cfg['SUPABASE_SERVICE_ROLE_KEY'] ?? '';
        if ($base === '' || $key === '') { return null; }
        $h = ['apikey: ' . $key, 'Authorization: Bearer ' . $key, 'Accept: application/json'];
        $sel = 'select=id,generated_html,business_name,contact_email';
        foreach ([['contact_email', $email], ['domain', $domain]] as $pair) {
            $val = trim((string)$pair[1]);
            if ($val === '') { continue; }
            $url = $base . '/rest/v1/site_builder_leads?' . $pair[0] . '=eq.' . rawurlencode($val)
                 . '&status=in.(preview_live,claimed)&' . $sel . '&order=created_at.desc&limit=1';
            $a = json_decode((string)zeno_http($url, $h), true);
            if (is_array($a) && count($a) && !empty($a[0]['generated_html'])) { return $a[0]; }
        }
        return null;
    }
}
if (!function_exists('zeno_wp_email')) {
    function zeno_wp_email($to, $domain, $prov, $bizName) {
        if (!$to) { return; }
        $from = 'support@izendestudioweb.com';
        $subject = 'Your WordPress site is live: ' . $bizName;
        $body = "Your new WordPress website is installed and matches the design you claimed.\n\n"
            . "Site:        " . $prov['wp_url'] . "\n"
            . "WP admin:    " . $prov['login_url'] . "\n"
            . "  Username:  " . $prov['admin_user'] . "\n"
            . "  Password:  " . $prov['admin_pass'] . "\n\n"
            . "Your homepage is fully editable under Pages > Home in the WordPress dashboard.\n"
            . "Secure https:// activates automatically once your domain's DNS points to our server.\n\n"
            . "Questions? Reply to this email or call (314) 312-6441.\n\n— Izende Studio Web\n";
        $headers = "From: Izende Studio Web <$from>\r\nReply-To: $from\r\nContent-Type: text/plain; charset=UTF-8";
        foreach (array_unique([$to, $from]) as $rcpt) { @mail($rcpt, $subject, $body, $headers); }
    }
}

if (!function_exists('zeno_has_booking_addon')) {
    // Did this service include the "Online Booking" product add-on at order time?
    function zeno_has_booking_addon($serviceid) {
        if (!$serviceid) { return false; }
        try {
            return \WHMCS\Database\Capsule::table('tblhostingaddons')
                ->join('tbladdons', 'tbladdons.id', '=', 'tblhostingaddons.addonid')
                ->where('tblhostingaddons.hostingid', (int)$serviceid)
                ->where('tbladdons.name', 'Online Booking')
                ->whereIn('tblhostingaddons.status', ['Active', 'Pending'])
                ->exists();
        } catch (\Throwable $e) { zeno_log('addon check failed', ['e' => $e->getMessage()]); return false; }
    }
}

add_hook('AfterModuleCreate', 1, function ($vars) {
    try {
        $pid = (int)($vars['pid'] ?? 0);
        if (!in_array($pid, [14, 15, 16], true)) { return; } // AI Website products only
        $cfg = zeno_env();
        $domain   = (string)($vars['domain'] ?? '');
        $username = (string)($vars['username'] ?? '');
        $cpPass   = (string)($vars['password'] ?? '');
        $email    = (string)($vars['clientsdetails']['email'] ?? '');
        $serviceid = (int)($vars['serviceid'] ?? ($vars['params']['serviceid'] ?? 0));
        if ($username === '') { zeno_log('no username in vars'); return; }

        $lead = zeno_find_lead($cfg, $email, $domain);
        if (!$lead) { zeno_log('no matching lead', ['email' => $email, 'domain' => $domain, 'user' => $username, 'pid' => $pid]); return; }
        $html = (string)$lead['generated_html'];

        $patch = ['status' => 'converted', 'cpanel_username' => $username, 'cpanel_domain' => $domain, 'provisioned_at' => gmdate('c')];

        if ($pid === 14) {
            // ---- Static tier: drop the generated HTML into public_html ----
            if (stripos($html, '<html') !== false && !empty($cfg['WHM_HOST'])) {
                $wh = ['Authorization: whm ' . ($cfg['WHM_USER'] ?? '') . ':' . ($cfg['WHM_API_TOKEN'] ?? '')];
                $fields = http_build_query([
                    'cpanel_jsonapi_apiversion' => 3, 'cpanel_jsonapi_user' => $username,
                    'cpanel_jsonapi_module' => 'Fileman', 'cpanel_jsonapi_func' => 'save_file_content',
                    'dir' => '/public_html', 'file' => 'index.html', 'content' => $html,
                ]);
                zeno_http('https://' . $cfg['WHM_HOST'] . '/json-api/cpanel', $wh, $fields, 90);
                zeno_log('static site deployed', ['lead' => $lead['id'], 'user' => $username]);
            }
            $patch['plan_kind'] = 'static';
        } else {
            // ---- WordPress tiers (15 self-edit, 16 managed) ----
            $lib = dirname(__DIR__, 3) . '/scripts/lib/wp-provision.php';
            if (!is_readable($lib)) { zeno_log('wp lib missing', ['lib' => $lib]); }
            else {
                require_once $lib;
                $prov = izende_wp_provision($cfg, $username, $cpPass, $domain, $email, $html, ($lead['business_name'] ?? $domain), 'zeno_log');
                if (!empty($prov['ok'])) {
                    zeno_wp_email($email, $domain, $prov, $lead['business_name'] ?? $domain);
                    $patch['plan_kind']   = ($pid === 16) ? 'wordpress_managed' : 'wordpress';
                    $patch['wp_admin_url'] = $prov['login_url'];
                    zeno_log('wp provisioned', ['lead' => $lead['id'], 'domain' => $domain, 'admin' => $prov['admin_user']]);
                } else {
                    zeno_log('wp provision FAILED', ['lead' => $lead['id'], 'err' => $prov['error'] ?? 'unknown']);
                    // Leave the account up; do not mark converted on failure so it can be retried.
                    return;
                }
            }
        }

        // ---- Online booking: Managed bundles it; any tier can buy the add-on ----
        if ($pid === 16 || zeno_has_booking_addon($serviceid)) {
            $patch['booking_enabled'] = true;
            zeno_log('booking enabled', ['lead' => $lead['id'], 'pid' => $pid, 'service' => $serviceid]);
        }

        // ---- Mark the lead converted in Supabase ----
        $base = rtrim($cfg['SUPABASE_URL'], '/'); $key = $cfg['SUPABASE_SERVICE_ROLE_KEY'];
        $h = ['apikey: ' . $key, 'Authorization: Bearer ' . $key, 'Content-Type: application/json'];
        $req = curl_init($base . '/rest/v1/site_builder_leads?id=eq.' . rawurlencode($lead['id']));
        curl_setopt($req, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($req, CURLOPT_HTTPHEADER, $h);
        curl_setopt($req, CURLOPT_POSTFIELDS, json_encode($patch));
        curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($req, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($req, CURLOPT_TIMEOUT, 20);
        curl_exec($req); curl_close($req);
        zeno_log('lead converted', ['lead' => $lead['id'], 'domain' => $domain, 'pid' => $pid]);
    } catch (\Throwable $e) {
        zeno_log('hook exception', ['e' => $e->getMessage()]);
    }
});
