<?php
/**
 * AI Website Builder — Analyze an existing site (Step 1 helper).
 *
 * Takes a URL the prospect already has, fetches it SSRF-safely, extracts the
 * visible text, and asks GLM to draft a business description that pre-fills the
 * builder form. Optional convenience — failures are friendly, never fatal.
 *
 * POST JSON: { url, csrf_token }  ->  { success, business_name, description }
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
if (function_exists('checkRateLimit') && !checkRateLimit($ip . '_analyze_site', 5, 600)) {
    http_response_code(429); echo json_encode(['success' => false, 'message' => 'Too many tries — give it a minute.']); exit;
}

$data = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($data)) { http_response_code(400); echo json_encode(['success' => false, 'message' => 'Invalid request.']); exit; }
if (!validateCSRFToken($data['csrf_token'] ?? '')) {  // validate only — do NOT rotate (the form submit needs this token)
    http_response_code(403); echo json_encode(['success' => false, 'message' => 'Session expired — please refresh the page.']); exit;
}

$url = trim((string) ($data['url'] ?? ''));
if ($url === '') { echo json_encode(['success' => false, 'message' => 'Enter your website address first.']); exit; }
if (!preg_match('~^https?://~i', $url)) { $url = 'https://' . $url; } // be forgiving about a bare domain

/* ---------- SSRF-safe fetch ---------- */
function iz_ip_is_public($ip) {
    return (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
}
/** Resolve a host to IPs and require EVERY one to be public; false if any private/reserved or unresolvable. */
function iz_host_public_ips($host) {
    if (filter_var($host, FILTER_VALIDATE_IP)) { return iz_ip_is_public($host) ? [$host] : false; }
    $ips = [];
    foreach ([DNS_A, DNS_AAAA] as $type) {
        $recs = @dns_get_record($host, $type);
        if (is_array($recs)) { foreach ($recs as $r) { $v = $r['ip'] ?? ($r['ipv6'] ?? ''); if ($v !== '') { $ips[] = $v; } } }
    }
    if (!$ips) { $g = @gethostbyname($host); if ($g && $g !== $host) { $ips[] = $g; } }
    if (!$ips) { return false; }
    foreach ($ips as $ip) { if (!iz_ip_is_public($ip)) { return false; } }
    return $ips;
}
function iz_safe_fetch($url, $depth = 0) {
    if ($depth > 2) { return null; }
    $p = parse_url($url);
    if (!$p || empty($p['scheme']) || empty($p['host'])) { return null; }
    if (!in_array(strtolower($p['scheme']), ['http', 'https'], true)) { return null; }
    if (!filter_var($url, FILTER_VALIDATE_URL)) { return null; }
    if (iz_host_public_ips($p['host']) === false) { return null; }

    $size = 0; $body = '';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,                 // handle redirects manually + re-validate
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_ENCODING       => '',                    // accept + auto-decode gzip/deflate/br
        CURLOPT_CONNECTTIMEOUT => 6,
        CURLOPT_TIMEOUT        => 12,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; IzendeSiteAnalyzer/1.0; +https://izendestudioweb.com)',
        CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        CURLOPT_WRITEFUNCTION  => function ($ch, $chunk) use (&$size, &$body) {
            $size += strlen($chunk);
            if ($size > 1500000) { return 0; }           // abort once past ~1.5 MB
            $body .= $chunk; return strlen($chunk);
        },
    ]);
    $ok        = curl_exec($ch);
    $code      = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $primaryIp = (string) curl_getinfo($ch, CURLINFO_PRIMARY_IP);
    $redirect  = (string) curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);

    // DNS-rebinding defense: the IP we actually connected to must be public too.
    if ($primaryIp !== '' && !iz_ip_is_public($primaryIp)) { return null; }

    if ($code >= 300 && $code < 400 && $redirect !== '') { return iz_safe_fetch($redirect, $depth + 1); }
    if ($body === '') { return null; }
    return $body;
}

$html = iz_safe_fetch($url);
if ($html === null || strlen(trim($html)) < 80) {
    echo json_encode(['success' => false, 'message' => "We couldn't read that site. Just tell us about your business below."]); exit;
}

/* ---------- Extract text + a candidate business name ---------- */
$title = ''; if (preg_match('~<title[^>]*>(.*?)</title>~is', $html, $m)) { $title = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8')); }
$metaDesc = ''; if (preg_match('~<meta[^>]+name=["\']description["\'][^>]*content=["\']([^"\']*)~i', $html, $m)) { $metaDesc = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8')); }
$ogSite = ''; if (preg_match('~<meta[^>]+property=["\']og:site_name["\'][^>]*content=["\']([^"\']*)~i', $html, $m)) { $ogSite = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8')); }
$ogDesc = ''; if (preg_match('~<meta[^>]+property=["\']og:description["\'][^>]*content=["\']([^"\']*)~i', $html, $m)) { $ogDesc = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8')); }

$clean = preg_replace('~<(script|style|noscript)\b[^>]*>.*?</\1>~is', ' ', $html);
$chunks = [];
if (preg_match_all('~<(h1|h2|h3|p|li)\b[^>]*>(.*?)</\1>~is', (string) $clean, $mm)) {
    foreach ($mm[2] as $frag) {
        $t = trim(preg_replace('~\s+~', ' ', html_entity_decode(strip_tags($frag), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        if (mb_strlen($t) >= 3) { $chunks[] = $t; }
    }
}
$textBlob = trim($title . "\n" . $metaDesc . "\n" . $ogDesc . "\n" . implode("\n", $chunks));
$textBlob = preg_replace('~\n{2,}~', "\n", $textBlob);
if (mb_strlen($textBlob) > 6000) { $textBlob = mb_substr($textBlob, 0, 6000); }
if (mb_strlen(trim($textBlob)) < 40) {
    echo json_encode(['success' => false, 'message' => "That site didn't have enough readable text. Tell us about your business below."]); exit;
}

$bizName = $ogSite !== '' ? $ogSite : $title;
$bizName = preg_replace('~\s*[\|\x{2013}\x{2014}\-:·»].*$~u', '', (string) $bizName); // drop tagline after a separator
$bizName = trim(preg_replace('~\s+~', ' ', (string) $bizName));
$bizName = trim(preg_replace('/[\s,]+(?:LLC|L\.L\.C\.?|Inc\.?|Incorporated|Corp\.?|Corporation|Co\.|Ltd\.?|LLP|PLLC)\.?\s*$/i', '', $bizName));
if (mb_strlen($bizName) > 80) { $bizName = ''; }

/* ---------- GLM summary ---------- */
$glmKey = trim((string) getEnv('GLM_API_KEY', ''));
if ($glmKey === '') { echo json_encode(['success' => false, 'message' => 'Analyzer is unavailable right now — please type your description.']); exit; }

$sys = "You read the text of a small business's CURRENT website and write a short, clear description to brief building them a brand-new site. Output 2-4 sentences, written as the business owner describing their business: what they do, their main services or products, who they serve, their city/area if stated, and the overall tone. Use ONLY facts present in the text — do NOT invent prices, addresses, phone numbers, awards, or statistics. Plain text only, no preamble or markdown.";
$usr = "Website: " . $url . "\n" . ($bizName !== '' ? "Business name: $bizName\n" : '') . "\nPAGE TEXT:\n" . $textBlob;
$payload = json_encode(['model' => 'glm-5', 'max_tokens' => 700, 'messages' => [
    ['role' => 'system', 'content' => $sys], ['role' => 'user', 'content' => $usr],
]]);

$ch = curl_init('https://api.z.ai/api/paas/v4/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $glmKey, 'Content-Type: application/json'],
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT => 60,
]);
$resp = curl_exec($ch); $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
if ($resp === false || $code !== 200) {
    echo json_encode(['success' => false, 'message' => "Couldn't analyze that site just now — please type your description."]); exit;
}
$j = json_decode((string) $resp, true);
$desc = trim((string) ($j['choices'][0]['message']['content'] ?? ''));
if (mb_strlen($desc) < 40) {
    echo json_encode(['success' => false, 'message' => "We couldn't summarize that site — please type your description."]); exit;
}

echo json_encode(['success' => true, 'business_name' => $bizName, 'description' => $desc]);
