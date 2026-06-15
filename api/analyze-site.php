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
@set_time_limit(120); // multi-page fetch + GLM can exceed the default 30s
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

/** Pull readable text (headings, paragraphs, list items) out of a page. */
function iz_page_text($html) {
    $clean = preg_replace('~<(script|style|noscript)\b[^>]*>.*?</\1>~is', ' ', (string) $html);
    $out = [];
    if (preg_match_all('~<(h1|h2|h3|h4|p|li|address)\b[^>]*>(.*?)</\1>~is', (string) $clean, $mm)) {
        foreach ($mm[2] as $frag) {
            $t = trim(preg_replace('~\s+~', ' ', html_entity_decode(strip_tags($frag), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            if (mb_strlen($t) >= 3) { $out[] = $t; }
        }
    }
    return implode("\n", $out);
}

/** Pull real contact details (phone + address) from a page: tel: links, JSON-LD, <address>. */
function iz_contact_details($html, &$firstAddress = null) {
    $html = (string) $html;
    $phones = []; $addresses = []; $emails = [];
    // tel: links — most reliable phone source
    if (preg_match_all('~href=["\']tel:([^"\']+)["\']~i', $html, $m)) {
        foreach ($m[1] as $p) { $p = trim($p); if ($p !== '') { $phones[] = $p; } }
    }
    // mailto: links — most reliable email source
    if (preg_match_all('~href=["\']mailto:([^"\'?]+)~i', $html, $m)) {
        foreach ($m[1] as $e) { $e = trim(rawurldecode($e)); if (strpos($e, '@') !== false) { $emails[] = $e; } }
    }
    // visible email addresses (guarded against asset/3rd-party noise)
    if (preg_match_all('~[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}~', $html, $m)) {
        foreach ($m[0] as $e) {
            $el = strtolower($e);
            if (preg_match('~\.(png|jpe?g|gif|svg|webp)$~', $el)) { continue; }
            if (preg_match('~(sentry|wixpress|example\.|\.local|@2x|sentry\.io)~', $el)) { continue; }
            $emails[] = $e;
        }
    }
    // JSON-LD structured data (telephone + postalAddress)
    if (preg_match_all('~<script[^>]+type=["\']application/ld\+json["\'][^>]*>(.*?)</script>~is', $html, $mm)) {
        foreach ($mm[1] as $json) {
            $d = json_decode(trim($json), true);
            if (!is_array($d)) { continue; }
            $nodes = (isset($d['@graph']) && is_array($d['@graph'])) ? $d['@graph'] : [$d];
            foreach ($nodes as $node) {
                if (!is_array($node)) { continue; }
                if (!empty($node['telephone']) && is_string($node['telephone'])) { $phones[] = trim($node['telephone']); }
                if (!empty($node['email']) && is_string($node['email'])) { $emails[] = trim(preg_replace('~^mailto:~i', '', $node['email'])); }
                if (!empty($node['address'])) {
                    $a = $node['address'];
                    if (is_array($a)) {
                        $parts = array_filter([$a['streetAddress'] ?? '', $a['addressLocality'] ?? '', $a['addressRegion'] ?? '', $a['postalCode'] ?? '']);
                        if ($parts) { $addresses[] = implode(', ', $parts); }
                    } elseif (is_string($a)) { $addresses[] = trim($a); }
                }
            }
        }
    }
    // <address> tag
    if (preg_match('~<address\b[^>]*>(.*?)</address>~is', $html, $m)) {
        $a = trim(preg_replace('~\s+~', ' ', html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        if ($a !== '' && mb_strlen($a) < 220) { $addresses[] = $a; }
    }
    $phones    = array_slice(array_values(array_unique(array_filter($phones))), 0, 3);
    $addresses = array_slice(array_values(array_unique(array_filter($addresses))), 0, 2);
    $emails    = array_slice(array_values(array_unique(array_map('strtolower', array_filter($emails)))), 0, 3);
    $lines = [];
    if ($phones)    { $lines[] = 'Phone: ' . implode(', ', $phones); }
    if ($emails)    { $lines[] = 'Email: ' . implode(', ', $emails); }
    if ($addresses) { $lines[] = 'Address: ' . implode(' | ', $addresses); }
    $firstAddress = $addresses[0] ?? '';
    return $lines ? "\nCONTACT DETAILS FOUND ON SITE:\n" . implode("\n", $lines) : '';
}

/** Same-host internal links from a page, ranked toward services/about pages. Returns up to $max absolute URLs. */
function iz_internal_links($html, $baseUrl, $max = 3) {
    $pb = parse_url($baseUrl);
    $bhost = strtolower($pb['host'] ?? '');
    $scheme = $pb['scheme'] ?? 'https';
    if ($bhost === '') { return []; }
    $scored = [];
    if (preg_match_all('~<a\b[^>]*href=["\']([^"\'#]+)["\'][^>]*>(.*?)</a>~is', (string) $html, $mm)) {
        foreach ($mm[1] as $i => $href) {
            $href = trim($href);
            if ($href === '' || preg_match('~^(mailto:|tel:|javascript:)~i', $href)) { continue; }
            if (preg_match('~^https?://~i', $href))      { $abs = $href; }
            elseif (strpos($href, '//') === 0)           { $abs = $scheme . ':' . $href; }
            elseif ($href[0] === '/')                    { $abs = $scheme . '://' . $bhost . $href; }
            else                                          { $abs = $scheme . '://' . $bhost . '/' . ltrim($href, './'); }
            $pa = parse_url($abs);
            if (!$pa || strtolower($pa['host'] ?? '') !== $bhost) { continue; } // same host only
            $path = strtolower($pa['path'] ?? '/');
            if ($path === '' || $path === '/') { continue; }
            if (preg_match('~\.(jpg|jpeg|png|gif|svg|webp|pdf|zip|mp4|css|js|ico|xml|docx?)$~', $path)) { continue; }
            $norm = $scheme . '://' . $bhost . $path;
            $hay = $path . ' ' . strtolower(trim(strip_tags($mm[2][$i])));
            $score = 0;
            foreach (['service','what-we','offer','program','treatment','solution','care','menu','pricing','plans','specialt','about','our-','product'] as $kw) {
                if (strpos($hay, $kw) !== false) { $score += 2; }
            }
            if (preg_match('~(contact|privacy|terms|login|signin|cart|account|career|faq|blog|news)~', $path)) { $score -= 1; }
            if (!isset($scored[$norm]) || $score > $scored[$norm]) { $scored[$norm] = $score; }
        }
    }
    arsort($scored);
    $picked = [];
    foreach ($scored as $u => $s) { if ($s > 0) { $picked[] = $u; } if (count($picked) >= $max) { break; } }
    return $picked;
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

// Home page text, then crawl a few key inner pages (services/about/...) on the SAME host so
// the AI sees the actual offerings, not just the marketing home page.
$blocks = [$title . "\n" . $metaDesc . "\n" . $ogDesc . "\n" . iz_page_text($html)];
$crawled = [$url];
foreach (iz_internal_links($html, $url, 3) as $link) {
    if (in_array($link, $crawled, true)) { continue; }
    $sub = iz_safe_fetch($link);
    if ($sub !== null && strlen($sub) > 80) {
        $blocks[] = "\n--- " . $link . " ---\n" . iz_page_text($sub);
        $crawled[] = $link;
    }
}
$textBlob = trim(implode("\n", $blocks));
$textBlob = preg_replace('~\n{3,}~', "\n\n", $textBlob);
if (mb_strlen($textBlob) > 11000) { $textBlob = mb_substr($textBlob, 0, 11000); }
$foundAddress = '';
$textBlob .= iz_contact_details($html, $foundAddress); // real phone/address from the home page (footer/JSON-LD/tel:)
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

$sys = "You read text gathered from a small business's CURRENT website (home page plus a few inner pages such as services/about) and write a clear description to brief building them a brand-new site. Write 3-6 sentences as the business owner. You MUST include: what the business does; a concrete list of their main services or products by name (pull the actual service names found in the text — e.g. \"We offer X, Y, and Z\"); who they serve; their city/service area if stated; and the overall tone. If the text shows the business's real phone number, email address, or street address (e.g. a 'CONTACT DETAILS FOUND' section, footer, or contact page), include them accurately so the new site can reuse the real contact info. Use ONLY facts present in the text — never invent services, prices, addresses, phone numbers, awards, or statistics; omit anything not present. Refer to the business by its plain name without legal suffixes like LLC, Inc., or Corp. Plain text only, no preamble, no markdown, no bullet characters.";
$usr = "Website: " . $url . "\n" . ($bizName !== '' ? "Business name: $bizName\n" : '') . "\nPAGE TEXT (multiple pages, separated by '--- url ---'):\n" . $textBlob;
$payload = json_encode(['model' => 'glm-5', 'max_tokens' => 900, 'messages' => [
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

echo json_encode(['success' => true, 'business_name' => $bizName, 'description' => $desc, 'business_address' => $foundAddress]);
