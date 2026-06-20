<?php
/**
 * AI Website Builder — free-generation cap.
 *
 * A tamper-proof signed cookie (`iz_genc` = "count.ts.hmac") tracks how many free
 * previews a visitor has generated. The page reads it to show "N of LIMIT left";
 * the leads API enforces it and bumps it on each successful generation. Signed with
 * SITE_BOOKING_SECRET so the value can't be edited. Resets after the window so a
 * returning visitor isn't permanently blocked. (A per-IP Supabase backstop in the
 * leads API catches cookie-clearers doing it at scale.)
 */

if (!defined('IZ_GEN_LIMIT'))  { define('IZ_GEN_LIMIT', 3); }
if (!defined('IZ_GEN_WINDOW')) { define('IZ_GEN_WINDOW', 2592000); } // 30 days

function iz_gen_secret() {
    $s = function_exists('getEnv') ? (string) getEnv('SITE_BOOKING_SECRET', '') : (string) getenv('SITE_BOOKING_SECRET');
    return $s !== '' ? $s : 'iz-gen-cap-fallback';
}

/** Read the signed cookie. Returns ['used'=>int, 'remaining'=>int]. Stale windows reset to 0. */
function iz_gen_read() {
    $raw   = (string) ($_COOKIE['iz_genc'] ?? '');
    $used  = 0;
    $parts = explode('.', $raw);
    if (count($parts) === 3) {
        list($c, $t, $sig) = $parts;
        if (ctype_digit($c) && ctype_digit($t)) {
            $expect = hash_hmac('sha256', $c . '.' . $t, iz_gen_secret());
            if (hash_equals($expect, (string) $sig) && (time() - (int) $t) <= IZ_GEN_WINDOW) {
                $used = (int) $c;
            }
        }
    }
    return ['used' => $used, 'remaining' => max(0, IZ_GEN_LIMIT - $used)];
}

/** Increment + (re)issue the signed cookie. Call once per successful generation. Returns new count. */
function iz_gen_bump() {
    $used = iz_gen_read()['used'] + 1;
    $ts   = time();
    $val  = $used . '.' . $ts . '.' . hash_hmac('sha256', $used . '.' . $ts, iz_gen_secret());
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    if (!headers_sent()) {
        setcookie('iz_genc', $val, [
            'expires'  => $ts + IZ_GEN_WINDOW,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
    $_COOKIE['iz_genc'] = $val;
    return $used;
}

/** Clear the visitor's signed generation-count cookie. */
function iz_gen_clear() {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    if (!headers_sent()) {
        setcookie('iz_genc', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
    unset($_COOKIE['iz_genc']);
}
