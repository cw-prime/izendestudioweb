<?php
/**
 * AI Website Builder — Edit Worker (Stage 4)
 *
 * Polls site_builder_edits (status=pending), applies each with GLM to the lead's
 * current generated_html, re-renders the preview, and (for converted static
 * sites) redeploys live. Reuses the generator's helpers in library mode so the
 * preview is rendered with the exact same claim-bar / editor / booking wiring.
 *
 *   CLI cron:  php scripts/apply-pending-edits.php
 *   Web test:  GET /scripts/apply-pending-edits.php?token=<PREVIEW_DEPLOY_SECRET>
 */
set_time_limit(0);
ignore_user_abort(true);

define('IZ_GEN_LIB', 1);
require_once __DIR__ . '/generate-pending-previews.php'; // exposes helpers + globals ($glmKey,$deployRoot,$baseUrl,$supabaseUrl,$supabaseKey)
require_once __DIR__ . '/lib/wp-provision.php';          // izende_wp_reseed() for live WordPress edits

$isCli = (php_sapi_name() === 'cli');
if (!$isCli) {
    header('Content-Type: text/plain; charset=utf-8');
    $secret = trim((string) envOr('PREVIEW_DEPLOY_SECRET', ''));
    if ($secret === '' || !hash_equals($secret, (string)($_GET['token'] ?? ''))) { http_response_code(403); echo "Forbidden\n"; exit; }
    while (ob_get_level() > 0) { ob_end_flush(); }
    ob_implicit_flush(true);
    echo str_repeat(' ', 4096) . "starting\n"; flush();
}

$lock = fopen(sys_get_temp_dir() . '/site-builder-edits.lock', 'c');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) { echo "busy\n"; exit(0); }

function editLog($m, $c = []) { cronLog('[edit] ' . $m, $c); }
function out($s) { global $isCli; echo $s . "\n"; @flush(); }

/** GLM chat (streaming, accumulated) — returns [text|null, err]. */
function glmChat($system, $user) {
    global $glmKey;
    $payload = ['model' => (trim((string) envOr('GLM_MODEL', 'glm-5')) ?: 'glm-5'), 'max_tokens' => 16000, 'stream' => true,
        'messages' => [['role' => 'system', 'content' => $system], ['role' => 'user', 'content' => $user]]];
    $sse = '';
    $ch = curl_init('https://api.z.ai/api/paas/v4/chat/completions');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $glmKey, 'Content-Type: application/json', 'Accept: text/event-stream']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 900);
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $chunk) use (&$sse) { $sse .= $chunk; return strlen($chunk); });
    $ok = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); $err = curl_error($ch); curl_close($ch);
    if ($ok === false || $code !== 200) { return [null, "glm http=$code $err"]; }
    $out = '';
    foreach (explode("\n", $sse) as $line) {
        $line = trim($line);
        if (strpos($line, 'data: ') !== 0) { continue; }
        $d = substr($line, 6);
        if ($d === '[DONE]') { continue; }
        $j = json_decode($d, true);
        if (isset($j['choices'][0]['delta']['content'])) { $out .= $j['choices'][0]['delta']['content']; }
    }
    return [$out, null];
}

function editWithGlm($html, $request) {
    $sys = "You are editing an existing, complete single-page marketing website. Apply ONLY the change the owner requests and return the COMPLETE updated HTML document and NOTHING else — start at <!DOCTYPE html> and end at </html>, no markdown code fences, no commentary. Preserve everything else exactly: all other sections and copy, the CSS custom-property theming contract on :root (the --c-* colours and --font-* families), Google Font <link>s, inline scripts, and any <form id=\"izBookingForm\"> together with its input name attributes. Keep it one self-contained file that works with JS disabled.";
    $usr = "CURRENT HTML:\n" . $html . "\n\nREQUESTED CHANGE:\n" . $request . "\n\nReturn the full updated HTML now.";
    list($txt, $err) = glmChat($sys, $usr);
    if ($txt === null) { return [null, $err]; }
    $txt = preg_replace('/^\s*```[a-zA-Z]*\s*\n/', '', $txt);
    $txt = preg_replace('/\n```\s*$/', '', $txt);
    $txt = trim($txt);
    if (strlen($txt) < 800 || stripos($txt, '<html') === false || stripos(ltrim($txt), '<!doctype html') !== 0) {
        return [null, 'edit output was not a complete HTML document (len=' . strlen($txt) . ')'];
    }
    return [$txt, null];
}

/** Remove the injected booking <script> so we can re-add exactly one clean copy. */
function stripBookingScript($html) {
    return preg_replace('/<script>\(function\(\)\{var F=document\.getElementById\(\x27izBookingForm\x27\);.*?<\/script>/is', '', $html);
}

/** Redeploy a converted STATIC site's index.html via WHM->cPanel. */
function deployStatic($user, $html) {
    if ($user === '') { return false; }
    $host = (string) envOr('WHM_HOST', ''); $u = (string) envOr('WHM_USER', ''); $t = (string) envOr('WHM_API_TOKEN', '');
    if ($host === '') { return false; }
    $fields = http_build_query([
        'cpanel_jsonapi_apiversion' => 3, 'cpanel_jsonapi_user' => $user,
        'cpanel_jsonapi_module' => 'Fileman', 'cpanel_jsonapi_func' => 'save_file_content',
        'dir' => '/public_html', 'file' => 'index.html', 'content' => $html,
    ]);
    $ch = curl_init("https://$host/json-api/cpanel");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: whm ' . $u . ':' . $t]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 90);
    $r = curl_exec($ch); curl_close($ch);
    $j = json_decode((string)$r, true);
    return ($j['result']['status'] ?? 0) ? true : false;
}

// ---- Poll + apply ----
list($code, $edits) = supabaseRequest('GET', '/rest/v1/site_builder_edits?status=eq.pending&order=created_at.asc&limit=5');
if ($code !== 200 || !is_array($edits)) { out("poll failed http=$code"); flock($lock, LOCK_UN); exit(1); }
if (count($edits) === 0) { out("no pending edits"); flock($lock, LOCK_UN); exit(0); }

foreach ($edits as $e) {
    $eid = $e['id']; $leadId = $e['lead_id']; $req = (string)$e['request_text'];
    out("\nedit $eid for lead $leadId");
    list($lc, $rows) = supabaseRequest('GET', '/rest/v1/site_builder_leads?id=eq.' . $leadId . '&select=generated_html,preview_slug,status,plan_kind,cpanel_username,cpanel_domain,business_name&limit=1');
    $lead = (is_array($rows) && count($rows)) ? $rows[0] : null;
    if (!$lead || empty($lead['generated_html'])) {
        supabaseRequest('PATCH', '/rest/v1/site_builder_edits?id=eq.' . $eid, ['status' => 'failed', 'result_note' => 'lead/html missing', 'applied_at' => gmdate('c')]);
        out("  skipped (no lead/html)"); continue;
    }
    list($newHtml, $err) = editWithGlm($lead['generated_html'], $req);
    if ($newHtml === null) {
        editLog('edit failed', ['edit' => $eid, 'err' => $err]);
        supabaseRequest('PATCH', '/rest/v1/site_builder_edits?id=eq.' . $eid, ['status' => 'failed', 'result_note' => $err, 'applied_at' => gmdate('c')]);
        out("  FAILED: $err"); continue;
    }
    // Normalize the booking wiring (GLM may have touched it).
    $newHtml = stripBookingScript($newHtml);
    $newHtml = injectBookingScript($newHtml, $leadId);

    $slug = $lead['preview_slug'] ?: slugForLead($lead);
    supabaseRequest('PATCH', '/rest/v1/site_builder_leads?id=eq.' . $leadId, ['generated_html' => $newHtml]);
    writePreview($slug, $newHtml, $leadId);

    $note = 'preview updated';
    if (($lead['status'] ?? '') === 'converted') {
        $kind = $lead['plan_kind'] ?? 'static';
        if ($kind === 'static') {
            $note = deployStatic((string)($lead['cpanel_username'] ?? ''), $newHtml) ? 'preview + live (static) updated' : 'preview updated; live static deploy FAILED';
        } else {
            $cfg = ['WHM_HOST'=>(string)envOr('WHM_HOST',''), 'WHM_USER'=>(string)envOr('WHM_USER',''), 'WHM_API_TOKEN'=>(string)envOr('WHM_API_TOKEN','')];
            $ok = izende_wp_reseed($cfg, (string)($lead['cpanel_username'] ?? ''), $newHtml, (string)($lead['business_name'] ?? ''), (string)($lead['cpanel_domain'] ?? ''), 'editLog');
            $note = $ok ? 'preview + live (WordPress re-seed) updated' : 'preview updated; live WP re-seed FAILED';
        }
    }
    supabaseRequest('PATCH', '/rest/v1/site_builder_edits?id=eq.' . $eid, ['status' => 'applied', 'result_note' => $note, 'applied_at' => gmdate('c')]);
    editLog('edit applied', ['edit' => $eid, 'lead' => $leadId, 'note' => $note]);
    out("  applied: $note");
}

flock($lock, LOCK_UN);
out("done");
