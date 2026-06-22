<?php
/**
 * Site Drafter — Preview Generator (cPanel cron)
 *
 * Polls Supabase for pending site_builder_leads, generates a single-file site
 * with GLM-5 (streaming), writes it to the previews dir, marks the lead
 * preview_live, and emails the prospect (BCC support@).
 *
 * Runs on the cPanel host (datacenter network) — replaces the n8n generation
 * path, which proved unreliable from the basement box (long HTTPS calls die).
 *
 * Invocation:
 *   CLI (cron, every 5 min):
 *     /usr/local/bin/php /home/izende6/public_html/scripts/generate-pending-previews.php
 *   Web (one-off test only, guarded):
 *     GET /scripts/generate-pending-previews.php?token=<PREVIEW_DEPLOY_SECRET>
 *
 * Env (config/.env): SUPABASE_URL, SUPABASE_SERVICE_ROLE_KEY, GLM_API_KEY,
 *   PREVIEW_DEPLOY_DIR, PREVIEW_BASE_URL, SMTP_* / MAIL_FROM.
 */

set_time_limit(0);
ignore_user_abort(true);

require_once __DIR__ . '/../config/env-loader.php';

$isCli = (php_sapi_name() === 'cli');

function cronLog($message, $context = []) {
    $entry = date('Y-m-d H:i:s') . ' ' . $message;
    if (!empty($context)) {
        $entry .= ' ' . json_encode($context);
    }
    $dir = dirname(__DIR__) . '/logs';
    if (!is_dir($dir)) { @mkdir($dir, 0750, true); }
    @file_put_contents($dir . '/site-builder-cron.log', $entry . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function envOr($key, $fallback) {
    $v = getEnv($key);
    return ($v === null || $v === false || $v === '') ? $fallback : $v;
}

// When included as a library (IZ_GEN_LIB), skip auth/lock/main and just expose
// the helper functions (used by scripts/apply-pending-edits.php).
if (!defined('IZ_GEN_LIB')) {
// ---- Auth (web invocations only) --------------------------------------------
$secret = trim((string) envOr('PREVIEW_DEPLOY_SECRET', ''));
if (!$isCli) {
    header('Content-Type: text/plain; charset=utf-8');
    if ($secret === '' || !hash_equals($secret, (string)($_GET['token'] ?? ''))) {
        http_response_code(403);
        echo "Forbidden.\n";
        exit;
    }
    // Defeat buffering so keepalive bytes reach the client during generation.
    while (ob_get_level() > 0) { ob_end_flush(); }
    ob_implicit_flush(true);
    echo str_repeat(' ', 8192) . "starting\n";
    flush();
}

// ---- Single-instance lock ----------------------------------------------------
$lockFile = sys_get_temp_dir() . '/site-builder-generate.lock';
if (is_file($lockFile) && (time() - (int) @filemtime($lockFile)) > 3600) {
    @unlink($lockFile);
    cronLog('Removed stale generator lock');
}
$lock = fopen($lockFile, 'c');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
    cronLog('Another instance is running — exiting');
    if (!$isCli) { echo "busy\n"; }
    exit(0);
}
} // end !IZ_GEN_LIB (auth + lock)

// ---- Config ------------------------------------------------------------------
$supabaseUrl = rtrim((string) envOr('SUPABASE_URL', ''), '/');
$supabaseKey = trim((string) envOr('SUPABASE_SERVICE_ROLE_KEY', ''));
$glmKey      = trim((string) envOr('GLM_API_KEY', ''));
$glmModel    = trim((string) envOr('GLM_MODEL', 'glm-5.2')) ?: 'glm-5.2'; // override via GLM_MODEL env (e.g. glm-5 to roll back)
$deployRoot  = rtrim((string) envOr('PREVIEW_DEPLOY_DIR', dirname(__DIR__) . '/previews'), '/');
$baseUrl     = rtrim((string) envOr('PREVIEW_BASE_URL', 'https://izendestudioweb.com/previews'), '/');

if ($supabaseUrl === '' || $supabaseKey === '' || (!defined('IZ_GEN_LIB') && $glmKey === '')) {
    cronLog('Missing config', ['supabase' => $supabaseUrl !== '', 'sb_key' => $supabaseKey !== '', 'glm_key' => $glmKey !== '']);
    if (!$isCli) { echo "misconfigured\n"; }
    exit(1);
}

// ---- Helpers -----------------------------------------------------------------
function supabaseRequest($method, $path, $body = null) {
    global $supabaseUrl, $supabaseKey;
    $ch = curl_init($supabaseUrl . $path);
    $headers = [
        'apikey: ' . $supabaseKey,
        'Authorization: Bearer ' . $supabaseKey,
        'Content-Type: application/json',
        'Prefer: return=representation',
    ];
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, $resp === false ? null : json_decode($resp, true)];
}

function buildPrompts($lead, $heroUrl = '', $logoUrl = '', $supportUrls = []) {
    $system = <<<'PROMPT'
You are a senior web designer at a professional web studio. You build a complete,
production-ready, single-page marketing website for a small business from a short
brief.

Output rules (CRITICAL):
- Return ONE complete HTML document and NOTHING else. Start at <!DOCTYPE html> and
  end at </html>. No markdown code fences, no explanation, no preamble.
- Keep the document compact and complete. Target 12k-22k characters. Do not overbuild
  huge decorative SVGs, app mockups, long animations, or excessive sections that risk
  truncation. A polished complete page is better than an ambitious broken page.
- Everything inline in that one file: a single <style> block in <head>. No external
  build step, no JS frameworks. A small amount of vanilla JS is fine (mobile menu,
  smooth scroll) but the site must look and work with JS disabled.
- You MAY use Google Fonts via <link>. For photography, use ONLY image URLs that are
  explicitly provided in this brief. NEVER invent, guess, or use stock/Unsplash/picsum
  placeholder image URLs — they break and look unprofessional. If no image URL is
  provided for a spot, use tasteful CSS gradients/backgrounds and inline SVG instead — but keep those
  fillers SMALL and clearly decorative (subtle section backgrounds, small icons inside content). NEVER
  render a large photo-sized rectangle whose only content is a centered icon on a gradient: that reads as
  a broken/missing image. If a section or card has no real photo, choose a layout that looks complete
  without one (icon-and-text cards with a small icon badge, bold type, columns) rather than an empty
  image placeholder.
- Do NOT add a film-grain or noise texture overlay over the page. Never place a fixed or
  absolutely-positioned full-screen layer of SVG feTurbulence/fractalNoise (or any repeating
  noise image) on top of the site — it makes the whole page look grainy and ruins legibility over
  photos and text. If you want subtle texture, confine it to a single section's own background and
  keep it nearly invisible (opacity <= 0.04); never layer noise above hero photos or body copy.
- Mobile-first and fully responsive. Must look great at 375px and 1280px.
- Accessible: semantic landmarks, alt text, sufficient color contrast, focus states.

Sections to include (YOU choose the order, layout, and visual treatment to fit the
DESIGN DIRECTION provided in the brief — do NOT always use the same top-to-bottom
arrangement):
- A header with nav + logo (sticky, ideally with a subtle shrink/shadow on scroll).
  Render the logo as the business name set as a clean typographic wordmark. If a LOGO
  MARK image URL is provided in the brief, place THAT image as a small icon (about
  40-48px tall) immediately before the wordmark; otherwise pair the wordmark with a
  simple inline-SVG monogram of the initials. NEVER render text/letters inside a
  raster image, and never attempt a photorealistic logo — keep it crisp and
  brand-appropriate using the theme colors.
- A striking hero (headline + one-line subhead + primary CTA) built per the Hero
  style named in the Design Direction. The first viewport must never feel empty:
  include visible headline copy, supporting copy, and a CTA above the fold on both
  desktop and mobile. Do not leave a blank 40-60% column beside an image. Avoid
  clipped diagonal photo panels unless the headline is visibly overlaid on the image.
- An about/story section in the business's voice (2-3 short paragraphs).
- The services/offerings, presented in the layout named in the Design Direction.
  Include prices ONLY if the brief implies them; otherwise describe without inventing.
- Social proof / trust (benefits, credentials, or clearly-labeled sample testimonials
  attributed to "— Sample Client").
- A contact or booking section (see ONLINE BOOKING below) with a click-to-call tel:
  link and a mailto: link.
- A footer with copyright + the business name.
You MAY add one extra section that fits (gallery, a "how it works"/process strip, an
FAQ, a stats band). Use distinctive section dividers/shapes — not plain stacked grey
blocks.

Theming contract (REQUIRED):
- Define ALL colors and fonts as CSS custom properties on :root, using EXACTLY
  these names, and reference them everywhere (no hardcoded hex or font-family in
  any other rule): --c-bg --c-surface --c-text --c-heading --c-muted --c-border
  --c-accent --c-accent-contrast --font-heading --font-body --radius
- You MAY add extra --c-accent-2 etc., but the names above MUST all be present.

Design rules (MAKE IT DISTINCT — do not produce a cookie-cutter template):
- COMMIT FULLY to the DESIGN DIRECTION in the brief; let it drive layout, composition,
  type scale, spacing rhythm, and section shapes. Two different businesses must yield
  visibly different LAYOUTS — not the same template recolored.
- Match the requested STYLE/VIBE in palette, type, and imagery mood.
- Avoid generic generated-site cliches: no Inter/Roboto/Arial-only stacks, no purple-on-white
  gradient cliché, no identical three-equal-cards-and-done. Use a confident type scale,
  strong visual hierarchy, asymmetry where it fits, and generous, intentional spacing.
- Real, specific copy derived from the brief — never lorem ipsum.

Motion & interaction (REQUIRED — the site must feel alive and modern, not static):
- Add tasteful, performant animation: on-scroll reveals (fade + slight slide-up or
  scale) with small STAGGERED delays between items; hover states that lift/scale/shift
  color on cards, buttons and links; a sticky header that shrinks or gains a shadow on
  scroll; smooth anchor scrolling.
- Give the hero a signature animated touch matching the Design Direction's motif (e.g.
  a slow animated gradient, gently floating accent shapes, a subtle parallax or slow
  zoom) — smooth and non-distracting.
- Implement scroll reveals with a tiny IntersectionObserver script. CRITICAL: content
  MUST be fully visible if JavaScript is disabled — add a `js` class to <html> via an
  inline script at the very start of <body>, and only apply the hidden/pre-animation
  state under `html.js` selectors, so no-JS users still see everything.
- Respect `@media (prefers-reduced-motion: reduce)`: disable transforms/animations and
  show everything statically.
- Animate transform/opacity only (60fps); never sacrifice the mobile layout or
  readability for motion.

Accuracy & editability (CRITICAL — this is a preview the owner will personalize):
- Use ONLY facts the owner actually provided. NEVER invent specific business hours,
  prices, street addresses, years in business, staff names, license numbers, or
  statistics. Inventing a wrong fact is worse than omitting it.
- If the owner's description includes a structured "Services and pricing to preserve"
  section from an existing-site scan, treat it as authoritative source content:
  carry over the real service categories, named services/packages, durations, and
  prices that fit the page. Do not reduce a service-heavy business to three generic
  cards when real menu/pricing details were provided.
- For dense scanned content, preserve context by grouping details into concise sections
  such as Services, Plans, Shop/Categories, Offers, App/Tools, FAQs, and Disclosures.
  Summarize long category lists, but do not drop whole business-critical groups.
- When a section would normally show such a detail but it wasn't provided, use a
  clearly-editable placeholder the owner will obviously swap — e.g. "Hours: add your
  hours here", "[Your address]", "Call for pricing" — never a fabricated specific.
- Any testimonials must read as obvious sample placeholders (e.g. attributed to
  "— Sample Client") so no real review is faked.
- Use the provided email and phone for contact links; do not invent other contact info.
- Never render legal suffixes (LLC, Inc., Corp., Ltd., Co.) in the brand name shown on the site — use the plain business name.
PROMPT;

    $description = (string) ($lead['business_description'] ?? '');
    if (strlen($description) > 8000) {
        $description = substr($description, 0, 8000) . "\n\n[Input trimmed for draft speed. Preserve the main services, plans, offers, and disclosure notes above.]";
    }

    $user = 'Business name: ' . $lead['business_name'] . "\n"
        . 'What the business does: ' . $description . "\n"
        . 'Style / vibe: ' . (!empty($lead['style_vibe']) ? $lead['style_vibe'] : 'choose the most fitting style for this business') . "\n"
        . 'Contact email (for the mailto link): ' . $lead['contact_email'] . "\n"
        . (!empty($lead['contact_phone']) ? 'Contact phone (for the tel link): ' . $lead['contact_phone'] . "\n" : '');

    if (!empty($lead['_brief'])) {
        $user .= "\nDesigner brief (Zeno expanded the owner's words into this — use it to shape sections, copy, services, and tone; do not contradict the owner's own words):\n" . $lead['_brief'] . "\n";
    }

    // Per-site DESIGN DIRECTION — seeded off the business so different businesses
    // (and even re-rolls) diverge into genuinely different layouts, not one template.
    $heroArch = [
        'a full-bleed hero with the image behind a rich dark gradient and a large overlaid headline',
        'a compact editorial hero with oversized display type, a thin rule, and a visible CTA',
        'an asymmetric hero with an offset headline, floating accent shapes, and a clearly visible CTA',
        'a minimalist statement hero: one huge typographic line, a short subhead, a single CTA',
    ];
    $svcLayout = [
        'a bento grid of varied tile sizes',
        'alternating full-width image/text rows (zig-zag down the page)',
        'overlapping cards with depth and a pronounced hover lift',
        'a clean numbered list with oversized index numerals',
        'a snap-scrolling row of cards (horizontal on mobile, grid on desktop)',
    ];
    $persona = [
        'editorial & sophisticated', 'warm & boutique', 'bold & high-contrast',
        'sleek premium / luxe', 'energetic & playful (still professional)',
    ];
    $motif = [
        'a slow animated gradient backdrop', 'subtle floating geometric accent shapes',
        'a fine grain/noise texture against crisp type', 'layered diagonal colour bands',
        'expansive whitespace with one vivid accent colour',
    ];
    $seed = hexdec(substr(md5((string)($lead['id'] ?? '') . '|' . (string)($lead['business_name'] ?? '')), 0, 8));
    $user .= "\nDESIGN DIRECTION (commit fully — this is what makes the site distinct):\n"
        . '- Overall personality: ' . $persona[$seed % count($persona)] . "\n"
        . '- Hero style: ' . $heroArch[intdiv($seed, 7) % count($heroArch)] . "\n"
        . '- Present the services/offerings as: ' . $svcLayout[intdiv($seed, 13) % count($svcLayout)] . "\n"
        . '- Signature visual motif: ' . $motif[intdiv($seed, 17) % count($motif)] . "\n"
        . "- Pick a section order and composition that suits THIS direction — not a generic top-to-bottom stack.\n";

    if (!empty($heroUrl)) {
        $user .= "\nHERO IMAGE AVAILABLE (a real, on-brand photo was generated specifically for this business): " . $heroUrl . "\n"
            . "Use this photo as the hero by DEFAULT — a strong photographic hero makes the page far more compelling, and a bare CSS/gradient hero on a service or local business looks empty and unfinished. Make it a FULL-BLEED background with a dark gradient overlay so the headline stays legible, and give it a descriptive alt/aria-label. Only fall back to a clean typographic / CSS hero (omitting the photo) when a photo would genuinely look LESS premium for THIS specific brand — i.e. a strictly minimalist luxury or text-forward concept; that is the rare exception, not the default. Do NOT place this photo as a split-screen side panel, clipped diagonal panel, or oversized cropped rectangle beside empty space.\n";
    }

    if (!empty($supportUrls)) {
        $list = '';
        foreach (array_values($supportUrls) as $i => $u) { $list .= '  ' . ($i + 1) . '. ' . $u . "\n"; }
        $user .= "\nSUPPORTING PHOTOS AVAILABLE (real, on-brand photographs generated for THIS business — use them to make the page feel alive and credible instead of empty gradient panels):\n" . $list
            . "Place these REAL photos in the content sections where a photo genuinely helps a visitor understand the business — e.g. About, Services, a feature/'why us' band, or a small gallery — with descriptive alt text. Style them responsively (width:100%, object-fit:cover, a sensible aspect-ratio, border-radius) so they crop cleanly on any screen. Use each one where it adds meaning; do NOT force all of them in if the design is stronger without one, and never stretch, distort, or tile them.\n"
            . "CRITICAL — design the layout around EXACTLY the images you have (the hero plus the " . count($supportUrls) . " supporting photo(s) above), and no more. Do NOT build a section that needs one photo per item (e.g. a services or features list with a large image beside every row/card) and then leave the slots you can't fill as a big gradient box containing a single centered icon — that looks like a broken/missing image. For any item WITHOUT a real photo, use a compact icon-and-text card (a small icon badge next to the heading and copy) or a clean typographic/columned layout that looks complete WITHOUT imagery. Large photo-sized blocks are allowed ONLY where you place one of the real photos above.\n";
    }

    // Other-visuals policy: only the real URLs above are allowed; everything else must be CSS/SVG so nothing breaks.
    if (!empty($heroUrl) || !empty($supportUrls)) {
        $user .= "\nOTHER VISUALS: Besides the real image URL(s) provided above (hero / logo / supporting photos), do NOT use, invent, or hotlink ANY other photographic image URLs — they break and look unprofessional. For every other visual (backgrounds, accents, icons, decorative panels) use CSS gradients/backgrounds and small inline SVG only.\n";
    }

    if (!empty($logoUrl)) {
        if (strpos($logoUrl, '/genmedia/uploads/') !== false) {
            // Customer's OWN logo — feature it prominently, and size it defensively so any shape/resolution fits.
            $user .= "\nCUSTOMER LOGO (this is the customer's REAL brand logo — feature it prominently). Use THIS EXACT URL in the header as the primary logo, and also echo it in the footer. Display it FREE-STANDING (do NOT box it in a small tile, do NOT crop, stretch, or overlay text). Apply this exact CSS so any shape/resolution fits without breaking the layout: height auto, max-height:clamp(36px,5vw,52px), width:auto, object-fit:contain (a wide wordmark renders wide-but-short; a square icon stays square). Give the header enough padding that the logo has breathing room. URL: " . $logoUrl . "\n";
        } else {
            $user .= "\nLOGO MARK (a custom icon was generated for this business — use THIS EXACT URL as the small logo icon in the header, paired with the business name as a text wordmark beside it; the image is icon-only, do NOT overlay any letters on it). The icon sits on a solid WHITE background, so seat it inside a small rounded-corner tile/badge (about 40-44px, border-radius, object-fit:contain, optional subtle shadow or thin border) so it reads as an intentional logo on any header color: " . $logoUrl . "\n";
        }
    }

    // Brand colors from the customer's uploaded logo — only steer the palette when they choose automatic style direction.
    $autoVibe = empty($lead['style_vibe']);
    $bc = [];
    if (!empty($lead['brand_colors'])) { $decoded = json_decode((string) $lead['brand_colors'], true); if (is_array($decoded)) { $bc = $decoded; } }
    if ($autoVibe && $bc) {
        $user .= "\nBRAND COLORS (pulled from the customer's logo — build the palette around these; set --c-accent and --c-accent-2 from them and choose harmonious supporting colors so the whole site feels on-brand): " . implode(', ', $bc) . "\n";
    }

    // Embedded map placeholder — only when a Maps key is configured AND we have an address (location business).
    if (trim((string) envOr('GOOGLE_MAPS_EMBED_KEY', '')) !== '' && !empty($lead['business_address'])) {
        $user .= "\nMAP: This business has a physical address. In the contact section, place the exact placeholder comment <!--IZ_MAP--> on its own line where an embedded map should appear (we insert the real map there). Put it after the address text.\n";
    }

    $user .= "\nONLINE BOOKING: If this business takes appointments or reservations (salon, spa, massage, clinic, dentist, barber, trades/home-services, tutor, coach, consultant, photographer, restaurant, etc.), make the Contact section an 'Online Booking' section with id=\"book\" and point the hero's primary call-to-action button at #book. Inside it, build a styled form with EXACTLY this contract (match the site's theme): <form id=\"izBookingForm\"> containing inputs with these exact name attributes — name, email, phone, service (a <select> listing this business's actual services), preferred_date (input type=\"date\"), preferred_time (input type=\"time\"), message (textarea) — plus a visually-hidden honeypot <input name=\"website\"> (off-screen, tabindex=-1, autocomplete=off) and a submit button. Do NOT set a form action or method and do NOT write any submit JavaScript — leave the form as-is; it is wired up automatically. If this is NOT an appointment business, use a normal contact form instead (no izBookingForm id).\n";

    $user .= "\nBuild the complete single-file website now. Output only the HTML.";

    return [$system, $user];
}

/**
 * Zeno's brief enrichment: expand the owner's short description into a fuller
 * creative brief so thin input still yields a rich site. Best-effort — on any
 * failure we silently fall back to the raw description.
 */
function enrichBrief($lead) {
    global $glmKey, $glmModel;
    $sys = "You are a brand strategist briefing a web designer. From a short business description, write a tight, concrete creative brief. Infer and include: core services/offerings (short list), the ideal customer, the brand tone/personality in a few adjectives, 2-3 realistic selling points or differentiators, and obvious trust signals (e.g. licensed, family-owned, years in business, local) ONLY if implied. Be specific and believable. Do NOT invent prices, street addresses, phone numbers, awards, or statistics. Keep it under 170 words, plain text.";
    $usr = "Business name: " . $lead['business_name'] . "\n"
         . "Their words: \"" . $lead['business_description'] . "\"\n"
         . "Desired vibe: " . (!empty($lead['style_vibe']) ? $lead['style_vibe'] : 'designer\'s choice');

    $payload = json_encode([
        'model' => $glmModel,
        'max_tokens' => 3000, // GLM-5 spends budget on reasoning first; leave room or content comes back empty
        'messages' => [
            ['role' => 'system', 'content' => $sys],
            ['role' => 'user', 'content' => $usr],
        ],
    ]);
    $ch = curl_init('https://api.z.ai/api/paas/v4/chat/completions');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $glmKey, 'Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($resp === false || $code !== 200) { return null; }
    $j = json_decode($resp, true);
    $text = $j['choices'][0]['message']['content'] ?? '';
    $text = trim($text);
    return ($text !== '' && strlen($text) > 40) ? $text : null;
}

function generateWithGlm($lead, $isCli, $heroUrl = '', $logoUrl = '', $supportUrls = []) {
    global $glmKey, $glmModel;
    // Zeno expands a thin description into a real brief first (best-effort).
    $rawDescription = trim((string) ($lead['business_description'] ?? ''));
    $brief = strlen($rawDescription) > 900 ? null : enrichBrief($lead);
    if ($brief !== null) {
        $lead['_brief'] = $brief;
        cronLog('Brief enriched', ['id' => $lead['id'] ?? '', 'chars' => strlen($brief)]);
    }
    list($system, $user) = buildPrompts($lead, $heroUrl, $logoUrl, $supportUrls);
    $payload = [
        'model' => $glmModel,
        'max_tokens' => 20000, // enough for rich scanned context, while still discouraging giant broken pages
        'stream' => true,
        'messages' => [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ],
    ];

    $sse = '';
    $ch = curl_init('https://api.z.ai/api/paas/v4/chat/completions');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $glmKey,
        'Content-Type: application/json',
        'Accept: text/event-stream',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 900);
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $chunk) use (&$sse, $isCli) {
        $sse .= $chunk;
        if (!$isCli) { echo '.'; flush(); } // keepalive bytes for web invocations
        return strlen($chunk);
    });
    $ok = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($ok === false || $code !== 200) {
        return [null, "curl failed (http=$code): $err — first bytes: " . substr($sse, 0, 200)];
    }

    $html = '';
    foreach (explode("\n", $sse) as $line) {
        $line = trim($line);
        if (strpos($line, 'data: ') !== 0) { continue; }
        $data = substr($line, 6);
        if ($data === '[DONE]') { continue; }
        $j = json_decode($data, true);
        if (isset($j['choices'][0]['delta']['content'])) {
            $html .= $j['choices'][0]['delta']['content'];
        }
    }
    $html = preg_replace('/^\s*```[a-zA-Z]*\s*\n/', '', $html);
    $html = preg_replace('/\n```\s*$/', '', $html);
    $html = trim($html);
    if (preg_match('/<\/html\s*>/i', $html, $m, PREG_OFFSET_CAPTURE)) {
        $html = substr($html, 0, $m[0][1] + strlen($m[0][0]));
    }

    if (strlen($html) < 1000 || stripos($html, '<html') === false || stripos(ltrim($html), '<!doctype html') !== 0) {
        return [null, 'output did not look like a complete HTML document (len=' . strlen($html) . ')'];
    }
    if (stripos($html, '</body>') === false || stripos($html, '</html>') === false) {
        return [null, 'output was truncated before closing body/html (len=' . strlen($html) . ')'];
    }
    if (preg_match('/<script\b/i', $html) && stripos($html, '</script>') === false) {
        return [null, 'output contained an unclosed script tag'];
    }
    return [$html, null];
}

/**
 * Generate one image with Gemini (Nano Banana family) and host it under
 * /genmedia/<slug>/<basename>.<ext>. Returns an absolute URL, or '' on any
 * failure. Runs on the cPanel host; the basement box can't hold the long HTTPS.
 */
function geminiImageToFile($prompt, $slug, $basename, $modelOverride = '') {
    global $deployRoot, $baseUrl;
    $key = trim((string) envOr('GEMINI_API_KEY', ''));
    if ($key === '') { return ''; }
    $model = $modelOverride !== '' ? $modelOverride : trim((string) envOr('GEMINI_IMAGE_MODEL', 'gemini-3.1-flash-image'));

    $payload = json_encode([
        'contents' => [['parts' => [['text' => $prompt]]]],
        'generationConfig' => ['responseModalities' => ['IMAGE']],
    ]);
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent?key=' . rawurlencode($key);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $cerr = curl_error($ch);
    curl_close($ch);
    if ($resp === false || $code !== 200) {
        cronLog('Gemini image gen failed', ['slug' => $slug, 'kind' => $basename, 'http' => $code, 'err' => $cerr, 'body' => substr((string) $resp, 0, 200)]);
        return '';
    }

    $j = json_decode($resp, true);
    $b64 = ''; $mime = 'image/png';
    foreach (($j['candidates'][0]['content']['parts'] ?? []) as $part) {
        if (!empty($part['inlineData']['data'])) {
            $b64 = $part['inlineData']['data'];
            $mime = $part['inlineData']['mimeType'] ?? $mime;
            break;
        }
    }
    if ($b64 === '') {
        cronLog('Gemini image: no inline data', ['slug' => $slug, 'kind' => $basename, 'body' => substr($resp, 0, 200)]);
        return '';
    }
    $bytes = base64_decode($b64, true);
    if ($bytes === false || strlen($bytes) < 500) { return ''; }
    $ext = (stripos($mime, 'jpeg') !== false || stripos($mime, 'jpg') !== false) ? 'jpg'
         : ((stripos($mime, 'webp') !== false) ? 'webp' : 'png');

    $genDir = dirname($deployRoot) . '/genmedia/' . $slug;
    if (!is_dir($genDir) && !@mkdir($genDir, 0755, true) && !is_dir($genDir)) {
        cronLog('Gemini image: mkdir failed', ['dir' => $genDir]);
        return '';
    }
    $file = $genDir . '/' . $basename . '.' . $ext;
    if (@file_put_contents($file, $bytes, LOCK_EX) === false) {
        cronLog('Gemini image: write failed', ['file' => $file]);
        return '';
    }
    $genBase = preg_replace('#/[^/]+/?$#', '/genmedia', $baseUrl);
    $imgUrl = $genBase . '/' . $slug . '/' . $basename . '.' . $ext;
    cronLog('Gemini image generated', ['slug' => $slug, 'kind' => $basename, 'bytes' => strlen($bytes), 'url' => $imgUrl]);
    return $imgUrl;
}

/** Real on-brand hero photo (best-effort; '' falls back to CSS/SVG visuals). */
function generateHeroImage($lead, $slug, $isCli) {
    $desc = trim((string) ($lead['business_description'] ?? ''));
    $vibe = trim((string) ($lead['style_vibe'] ?? ''));
    $prompt = 'Create a wide, photorealistic hero/banner photograph for the website of "'
        . $lead['business_name'] . '". The business: ' . $desc . '.'
        . ($vibe ? ' Visual mood and style: ' . $vibe . '.' : '')
        . ' Landscape 16:9 composition designed to work as a FULL-BLEED website background behind overlaid white text:'
        . ' keep it visually calm with reasonably even tones, avoid a single hard-edged focal point dead-center,'
        . ' and do NOT leave a large flat empty panel or blank wall on one side (the layout adds its own dark gradient scrim for legibility).'
        . ' Natural, flattering light; authentic, editorial, professional quality.'
        . ' Depict authentic, respectful people consistent with the clientele and community this business describes serving —'
        . ' reflect any cues in the description about the people served (e.g. ethnicity, age group, families, profession).'
        . ' If the description gives no such cues, show people naturally relevant to the service. Real and candid, never stocky.'
        . ' Absolutely NO text, NO words, NO logos, NO watermarks, and NO user-interface elements in the image.';
    $url = geminiImageToFile($prompt, $slug, 'hero');
    if ($url !== '' && !$isCli) { echo " [hero image ok] "; flush(); }
    return $url;
}

/** Two real, on-brand supporting photos (people/service + space/detail) for the content
 *  sections, so non-hero spots show a real image instead of an empty gradient+icon panel.
 *  Best-effort; any '' simply isn't offered to GLM (it falls back to CSS/SVG for that spot). */
function generateSupportImages($lead, $slug, $isCli) {
    $name = (string) $lead['business_name'];
    $desc = trim((string) ($lead['business_description'] ?? ''));
    $vibe = trim((string) ($lead['style_vibe'] ?? ''));
    $common = ($vibe ? ' Visual mood and style: ' . $vibe . '.' : '')
        . ' Natural, flattering light; authentic, candid, editorial and professional quality — real, never stocky.'
        . ' Depict authentic, respectful people consistent with the clientele and community this business describes serving —'
        . ' reflect any cues in the description about the people served (e.g. ethnicity, age group, families, profession);'
        . ' if the description gives no such cues, show people naturally relevant to the service.'
        . ' Photorealistic, versatile 4:3 composition that still reads well when cropped into a card or column.'
        . ' Absolutely NO text, NO words, NO logos, NO watermarks, and NO user-interface elements in the image.';
    // Content-aware count: a service-heavy business needs more real photos than a simple one.
    // Estimate distinct offerings from the description, map to [2..IZ_SUPPORT_MAX]. The layout
    // rule ("design around the photos you have") is the safety net for any leftover slots.
    $supportMax = (int) envOr('IZ_SUPPORT_MAX', 7);
    if ($supportMax < 2) { $supportMax = 2; }
    if ($supportMax > 7) { $supportMax = 7; }
    $chunks = preg_split('/[,;\n\x{2022}]+|\band\b/iu', $desc) ?: [];
    $items  = 0;
    foreach ($chunks as $c) { if (strlen(trim((string) $c)) >= 3) { $items++; } }
    $photoCount = (int) max(2, min($supportMax, (int) ceil($items / 2)));

    // Pool of distinct shot concepts (varied so multiple photos never look repetitive); take the first N.
    $concepts = [
        'Show this business\'s work actually happening — the service being delivered or a customer/client being helped — so a visitor instantly understands what they get.',
        'Focus on the space or environment (interior, setting, or welcoming entrance), conveying quality, cleanliness and care; people optional and secondary.',
        'Show the team / staff at work — warm, competent professionals engaged in their craft.',
        'A close, editorial detail of a signature service, product, tool, or result that conveys expertise and attention to detail.',
        'A genuine, happy customer / client enjoying the outcome or result of this business\'s work — candid and natural, not posed.',
        'A second distinct service or offering this business provides, depicted authentically in context.',
        'A lifestyle / context shot that captures the feeling and value of choosing this business — calm, trustworthy, aspirational.',
    ];
    $model    = trim((string) envOr('GEMINI_LOGO_MODEL', 'nano-banana-pro-preview'));      // higher-fidelity model
    $fallback = trim((string) envOr('GEMINI_IMAGE_MODEL', 'gemini-3.1-flash-image'));
    $urls = [];
    for ($i = 0; $i < $photoCount; $i++) {
        $base   = 'support-' . ($i + 1);
        $prompt = 'Create a warm, authentic, editorial photograph for the website of "' . $name . '". The business: ' . $desc . '. '
            . $concepts[$i] . $common;
        $u = geminiImageToFile($prompt, $slug, $base, $model);
        if ($u === '') { $u = geminiImageToFile($prompt, $slug, $base, $fallback); }
        if ($u !== '') { $urls[] = $u; }
    }
    if (!empty($urls) && !$isCli) { echo ' [' . count($urls) . ' support image' . (count($urls) === 1 ? '' : 's') . " of $photoCount] "; flush(); }
    return $urls;
}

/** Premium icon-only logo mark (no text — letters in raster logos misrender). */
function generateLogoMark($lead, $slug, $isCli) {
    // Customer uploaded their own logo? Use it as-is.
    $uploaded = trim((string) ($lead['logo_url'] ?? ''));
    if ($uploaded !== '' && preg_match('~^https://izendestudioweb\.com/genmedia/uploads/[\w-]+\.(?:png|jpe?g|webp|gif)$~i', $uploaded)) {
        if (!$isCli) { echo " [using uploaded logo] "; flush(); }
        return $uploaded;
    }
    $desc = trim((string) ($lead['business_description'] ?? ''));
    $vibe = trim((string) ($lead['style_vibe'] ?? ''));
    $prompt = 'Design a polished, PREMIUM logo ICON (emblem/symbol only) for "'
        . $lead['business_name'] . '", a business that does: ' . $desc . '.'
        . ($vibe ? ' Palette and mood: ' . $vibe . '.' : '')
        . ' Make it look like a real brand mark crafted by a top design studio — modern, refined and memorable, NOT flat clipart.'
        . ' You MAY use tasteful gradients, subtle depth/dimension, overlap and negative space, and elegant detailing. Cohesive 2-4 colour palette that fits the vibe.'
        . ' Centered with generous even margin on a SOLID PURE WHITE (#FFFFFF) background. Do NOT draw a transparency checkerboard, grid, or any background pattern.'
        . ' ABSOLUTELY NO text, NO letters, NO numbers, NO words, NO typography — the symbol ONLY.'
        . ' Balanced and iconic, with crisp edges that stay clear and legible even at a small ~48px size.';
    $logoModel = trim((string) envOr('GEMINI_LOGO_MODEL', 'nano-banana-pro-preview'));
    $url = geminiImageToFile($prompt, $slug, 'logo', $logoModel);
    if ($url === '') { // fall back to the standard image model if the pro model is unavailable
        $url = geminiImageToFile($prompt, $slug, 'logo', trim((string) envOr('GEMINI_IMAGE_MODEL', 'gemini-3.1-flash-image')));
    }
    if ($url !== '' && !$isCli) { echo " [logo mark ok] "; flush(); }
    return $url;
}

function slugForLead($lead) {
    global $deployRoot;
    // Reuse a previously assigned slug (e.g. failed run being retried)
    if (!empty($lead['preview_slug'])) {
        return $lead['preview_slug'];
    }
    $slug = strtolower(trim($lead['business_name']));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim(preg_replace('/-+/', '-', $slug), '-');
    $slug = substr($slug, 0, 60) ?: 'preview';
    // Avoid collisions with other businesses already on disk
    $candidate = $slug;
    $i = 2;
    while (is_dir($deployRoot . '/' . $candidate)) {
        $candidate = $slug . '-' . $i;
        $i++;
        if ($i > 50) { return $slug . '-' . substr(bin2hex(random_bytes(3)), 0, 6); }
    }
    return $candidate;
}

/**
 * Izende "Claim this site" bar — injected into the DEPLOYED preview only (so the
 * prospect can convert from the emailed link). NOT stored in generated_html, so
 * the customer's real provisioned site never carries it.
 */
/**
 * Wire any booking form (#izBookingForm) in the generated draft to the tenant booking
 * endpoint. Baked into generated_html so it persists onto the live static/WP
 * site. No-op if the site has no booking form. The per-site token is public by
 * design (the endpoint is honeypot + rate-limit + enable-gated).
 */
function injectBookingScript($html, $leadId) {
    $secret = (string) envOr('SITE_BOOKING_SECRET', '');
    if ($secret === '' || $leadId === '') { return $html; }
    $token = substr(hash_hmac('sha256', 'site:' . $leadId, $secret), 0, 40);
    $js = <<<'JS'
<script>(function(){var F=document.getElementById('izBookingForm');if(!F)return;
var CFG={endpoint:'https://izendestudioweb.com/api/site-booking.php',leadId:'__LEAD__',token:'__TOKEN__'};
F.addEventListener('submit',function(e){e.preventDefault();
var hp=F.querySelector('[name=website]');if(hp&&hp.value)return;
var d={lead_id:CFG.leadId,token:CFG.token};
['name','email','phone','service','preferred_date','preferred_time','message','website'].forEach(function(k){var el=F.querySelector('[name='+k+']');if(el)d[k]=el.value;});
var btn=F.querySelector('button[type=submit],input[type=submit],button');var lbl=btn?btn.textContent:'';if(btn){btn.disabled=true;btn.textContent='Sending…';}
function note(bg,fg,html){var m=document.createElement('div');m.style.cssText='padding:14px 16px;border-radius:10px;margin-top:14px;font-weight:600;background:'+bg+';color:'+fg;m.innerHTML=html;F.appendChild(m);}
fetch(CFG.endpoint,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(d)}).then(function(r){return r.json();}).then(function(j){
if(j.success){F.reset();note('#dcfce7','#15803d',j.message||'Request received!');}
else if(j.upsell){note('#dbeafe','#1d4ed8',(j.message||'')+' <a href="'+(j.claim_url||'#')+'" style="color:#1d4ed8;font-weight:700;text-decoration:underline">Claim now &rarr;</a>');}
else{note('#fee2e2','#b91c1c',j.message||'Could not send your request. Please call us.');}
if(btn){btn.disabled=false;btn.textContent=lbl;}
}).catch(function(){if(btn){btn.disabled=false;btn.textContent=lbl;}note('#fee2e2','#b91c1c','Network error — please call us.');});
});})();</script>
JS;
    $js = str_replace(['__LEAD__', '__TOKEN__'], [$leadId, $token], $js);
    if (stripos($html, '</body>') !== false) {
        return preg_replace('/<\/body>/i', $js . '</body>', $html, 1);
    }
    return $html . $js;
}

/**
 * Belt-and-suspenders: GLM sometimes adds a heavy full-screen film-grain overlay (an SVG
 * feTurbulence/fractalNoise texture used as a CSS background) that makes the whole page look
 * grainy and hurts legibility over photos and text. Neutralize any such noise data-URI
 * background so it can never ship, regardless of what the model generated. Quoted data URIs
 * only (GLM always quotes them; matching to the quote is safe because the encoded SVG itself
 * contains literal ")" characters that would break a naive [^)] match).
 */
function stripGrainOverlay($html) {
    if ($html === '' || stripos($html, 'feTurbulence') === false) { return $html; }
    $html = preg_replace('~url\(\s*"data:image/svg\+xml,[^"]*(?:feTurbulence|fractalNoise)[^"]*"\s*\)~i', 'none', $html);
    $html = preg_replace("~url\(\s*'data:image/svg\\+xml,[^']*(?:feTurbulence|fractalNoise)[^']*'\s*\)~i", 'none', $html);
    return $html;
}

function injectMap($html, $lead) {
    $placeholder = '<!--IZ_MAP-->';
    $key = trim((string) envOr('GOOGLE_MAPS_EMBED_KEY', ''));
    $address = trim((string) ($lead['business_address'] ?? ''));
    if ($key === '' || $address === '') {
        return str_replace($placeholder, '', $html);
    }

    $src = 'https://www.google.com/maps/embed/v1/place?key=' . rawurlencode($key) . '&q=' . rawurlencode($address);
    $map = '<div class="iz-map-embed" style="position:relative;aspect-ratio:16/7;max-width:100%;overflow:hidden;border-radius:16px;background:#e2e8f0">'
        . '<iframe src="' . htmlspecialchars($src, ENT_QUOTES) . '" loading="lazy" title="Map" referrerpolicy="no-referrer-when-downgrade" '
        . 'style="position:absolute;inset:0;width:100%;height:100%;border:0"></iframe></div>';
    return str_replace($placeholder, $map, $html);
}

function injectPreviewRobotsMeta($html) {
    $meta = '<meta name="robots" content="noindex,nofollow">';
    if (stripos($html, 'name="robots"') !== false || stripos($html, "name='robots'") !== false) {
        return $html;
    }
    if (preg_match('/<head\b[^>]*>/i', $html, $m, PREG_OFFSET_CAPTURE)) {
        $pos = $m[0][1] + strlen($m[0][0]);
        return substr($html, 0, $pos) . "\n  " . $meta . substr($html, $pos);
    }
    return $meta . "\n" . $html;
}

function injectClaimBar($html, $slug = '', $createdAt = '') {
    $createdTs = $createdAt !== '' ? strtotime((string) $createdAt) : false;
    if (!$createdTs) { $createdTs = time(); }
    $expiryMs = (int) (($createdTs + 7 * 86400) * 1000);
    $bar = '<div id="izende-claim-bar" style="position:fixed;top:0;left:0;right:0;z-index:2147483647;'
        . 'background:#0f172a;color:#fff;display:flex;align-items:center;justify-content:space-between;'
        . 'gap:10px;flex-wrap:wrap;padding:9px 16px;font-family:system-ui,-apple-system,\'Segoe UI\',sans-serif;'
        . 'font-size:14px;line-height:1.3;box-shadow:0 2px 14px rgba(0,0,0,.28)">'
        . '<span style="display:flex;align-items:center;gap:8px">✨ <strong>Built for you by Izende Studio Web</strong>'
        . '<span style="opacity:.8"> — love it? Make it yours.</span></span>'
        . '<span id="izende-preview-status" style="margin-left:auto;display:flex;align-items:center;gap:14px;flex-wrap:wrap;opacity:.92;font-weight:700;white-space:nowrap">'
        . '<span id="izende-claim-countdown">Reserved for you — 7 days left</span>'
        . '<span id="izende-draft-count" style="opacity:.86">Checking drafts left…</span></span>'
        . '<a href="https://izendestudioweb.com/claim-site.php?slug=' . rawurlencode($slug) . '" class="izende-claim-btn" '
        . 'style="background:#2563eb;color:#fff;text-decoration:none;font-weight:700;padding:9px 22px;'
        . 'border-radius:999px;white-space:nowrap">Claim this site →</a></div>'
        . '<style>html{scroll-padding-top:120px}body{padding-top:54px!important}'
        . 'header,.header,[role=banner]{top:54px!important}' // push the site\'s own fixed/sticky header below the claim bar so the nav stays visible
        . '.izende-claim-btn{animation:izClaimPulse 1.8s ease-in-out infinite}'
        . '.izende-claim-btn:hover{background:#1d4ed8!important;transform:scale(1.04)}'
        . '@keyframes izClaimPulse{0%,100%{box-shadow:0 0 0 0 rgba(37,99,235,.7),0 0 12px rgba(56,189,248,.5)}'
        . '50%{box-shadow:0 0 0 10px rgba(37,99,235,0),0 0 26px rgba(56,189,248,.95)}}'
        . '@media(max-width:680px){#izende-preview-status{margin-left:0;font-size:13px;flex-basis:100%}}'
        . '@media(prefers-reduced-motion:reduce){.izende-claim-btn{animation:none}}</style>'
        . '<script>(function(){var el=document.getElementById("izende-claim-countdown");if(!el)return;var exp=' . $expiryMs . ';'
        . 'function tick(){var left=Math.max(0,exp-Date.now());var days=Math.max(0,Math.ceil(left/86400000));'
        . 'el.textContent="Reserved for you — "+days+" day"+(days===1?"":"s")+" left";}tick();'
        . 'var dc=document.getElementById("izende-draft-count");if(dc&&window.fetch){fetch("/api/generation-count.php",{cache:"no-store",credentials:"same-origin"}).then(function(r){return r.json();}).then(function(j){if(j&&j.success){var rem=Math.max(0,parseInt(j.remaining,10)||0);var limit=Math.max(1,parseInt(j.limit,10)||3);dc.textContent=rem>0?rem+" of "+limit+" free drafts left — add more detail for better results":"All "+limit+" free drafts used — claim this site to keep editing";}}).catch(function(){dc.textContent="Draft count available after preview";});}})();</script>';
    if (stripos($html, '</body>') !== false) {
        return preg_replace('/<\/body>/i', $bar . '</body>', $html, 1);
    }
    return $html . $bar;
}

/**
 * Stage 4 editor (presets) — a "Customize" widget injected into the PREVIEW only.
 * Re-themes the visible page instantly, then saves the selected CSS-variable
 * preset back to generated_html so a later claim/provision uses the same look.
 * Browser localStorage keeps the preview responsive while the server save finishes.
 */
function injectEditorWidget($html, $slug, $leadId = '') {
    $token = ($leadId !== '') ? substr(hash_hmac('sha256', 'site:' . $leadId, (string) envOr('SITE_BOOKING_SECRET', '')), 0, 40) : '';
    $js = <<<'JS'
<style>#izEditPanel{font-size:14px;line-height:1.45;color:#1e293b}
#izEditPanel .iz-h{font-weight:700;color:#0f172a;margin-bottom:6px}
#izEditPanel .iz-sub{color:#475569}
#izEditPanel #izAsk{background:#fff !important;color:#0f172a !important;-webkit-text-fill-color:#0f172a !important;caret-color:#0f172a;border:1px solid #cbd5e1 !important;opacity:1 !important;filter:none !important}
#izEditPanel #izAsk::placeholder{color:#94a3b8 !important;-webkit-text-fill-color:#94a3b8 !important;opacity:1}
.izspin{display:inline-block;width:14px;height:14px;border:2px solid #c7d2fe;border-top-color:#2563eb;border-radius:50%;animation:izspin .7s linear infinite;vertical-align:-2px;margin-right:7px}
.izwaitbar{height:7px;background:#e2e8f0;border-radius:999px;overflow:hidden;margin:9px 0 7px}
.izwaitbar span{display:block;height:100%;width:100%;background:linear-gradient(90deg,#2563eb,#38bdf8);transition:width 1s linear}
@keyframes izspin{to{transform:rotate(360deg)}}
@media(prefers-reduced-motion:reduce){.izspin{animation:none}.izwaitbar span{transition:none}}</style>
<div id="izEdit" style="position:fixed;right:16px;bottom:16px;z-index:2147483646;font-family:system-ui,-apple-system,'Segoe UI',sans-serif">
<button id="izEditBtn" aria-label="Customize this site" style="display:flex;align-items:center;gap:7px;background:#0f172a;color:#fff;border:0;border-radius:999px;padding:11px 18px;font-size:14px;font-weight:700;cursor:pointer;box-shadow:0 6px 20px rgba(0,0,0,.28)">🎨 Customize</button>
<div id="izEditPanel" style="display:none;position:absolute;right:0;bottom:54px;width:290px;max-height:80vh;overflow:auto;background:#fff;border-radius:14px;box-shadow:0 12px 40px rgba(0,0,0,.25);padding:18px">
<div style="font-weight:800;font-size:16px;color:#0f172a;margin-bottom:3px">Make it yours</div>
<div class="iz-sub" style="margin-bottom:14px">Try a look — changes are instant.</div>
<div class="iz-h">Accent colour</div>
<div id="izColors" style="display:flex;gap:9px;flex-wrap:wrap;margin:6px 0 16px"></div>
<div class="iz-h">Font</div>
<div id="izFonts" style="display:flex;flex-direction:column;gap:7px;margin:6px 0 16px"></div>
<button id="izReset" style="width:100%;background:#f1f5f9;border:0;border-radius:8px;padding:10px;font-weight:600;font-size:14px;color:#334155;cursor:pointer;margin-bottom:16px">Reset look</button>
<div id="izAskWrap" style="border-top:1px solid #e2e8f0;padding-top:14px">
	<div class="iz-h">Ask Site Drafter to change anything</div>
<div class="iz-sub" style="margin-bottom:9px">e.g. "shorten the hero", "use a warmer tone", "add an FAQ"</div>
<textarea id="izAsk" rows="4" placeholder="Describe a change…" style="width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:8px;padding:10px;font-family:inherit;font-size:14px;line-height:1.45;color:#0f172a;min-height:88px;resize:vertical"></textarea>
	<button id="izSend" style="width:100%;background:#2563eb;color:#fff;border:0;border-radius:8px;padding:12px;font-weight:700;font-size:14px;cursor:pointer;margin-top:9px">Send to Site Drafter</button>
<div id="izMsg" style="margin-top:12px;font-size:13.5px;line-height:1.5;color:#334155"></div>
</div>
<div style="text-align:center;margin-top:14px;color:#475569;font-size:12.5px">Like it? <strong>Claim your site</strong> to keep editing.</div>
</div></div>
<script>(function(){
var K='izTheme_'+'__SLUG__',R=document.documentElement,LEAD='__LEAD__',TOKEN='__TOKEN__',
EP='https://izendestudioweb.com/api/site-builder-edit.php',
COLORS=['#2563eb','#c2683f','#0f766e','#7c3aed','#db2777','#b45309','#0ea5e9','#16a34a'],
FONTS=[{n:'Modern',h:'Poppins',b:'Inter'},{n:'Classic',h:'Playfair Display',b:'Lora'},{n:'Friendly',h:'Fredoka',b:'Nunito'},{n:'Clean',h:'Montserrat',b:'Work Sans'}];
function gf(f){var id='izFont_'+f.replace(/\W/g,'');if(document.getElementById(id))return;var l=document.createElement('link');l.id=id;l.rel='stylesheet';l.href='https://fonts.googleapis.com/css2?family='+f.replace(/ /g,'+')+':wght@400;600;700;800&display=swap';document.head.appendChild(l);}
function applyColor(c){R.style.setProperty('--c-accent',c);R.style.setProperty('--c-accent-2',c);R.style.setProperty('--c-accent-contrast','#ffffff');}
function applyFont(f){gf(f.h);gf(f.b);R.style.setProperty('--font-heading',"'"+f.h+"',serif");R.style.setProperty('--font-body',"'"+f.b+"',sans-serif");}
function save(s){try{localStorage.setItem(K,JSON.stringify(s))}catch(e){}}
function load(){try{return JSON.parse(localStorage.getItem(K)||'{}')}catch(e){return{}}}
var send=document.getElementById('izSend'),ask=document.getElementById('izAsk'),msg=document.getElementById('izMsg'),lbl='Send to Site Drafter',waitTimer=null,waitEnd=0,themeTimer=null,themeDirty=false,themeResetPending=false;
function setMsg(t,c){if(!msg)return;msg.style.color=c||'#334155';msg.innerHTML=t;}
function persistTheme(reset,done){
 if(!LEAD){if(done)done(true);return;}
 var ok=false;
 fetch(EP,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({lead_id:LEAD,token:TOKEN,action:'theme',theme:reset?{reset:true}:{color:st.color||'',font:st.font||''}})})
  .then(function(r){return r.json();})
  .then(function(j){ok=!!(j&&j.success);if(ok){themeDirty=false;themeResetPending=false;if(send&&!send.disabled)setMsg('✓ Look saved for claim.','#15803d');}else if(send&&!send.disabled){setMsg((j&&j.message)||'Look changed here, but could not save yet.','#b45309');}})
  .catch(function(){if(send&&!send.disabled)setMsg('Look changed here, but could not save yet.','#b45309');})
  .finally(function(){if(done)done(ok);});
}
function queueTheme(reset){themeDirty=true;themeResetPending=!!reset;clearTimeout(themeTimer);themeTimer=setTimeout(function(){persistTheme(themeResetPending);},350);}
var st=load();if(st.color)applyColor(st.color);if(st.font){var ff=FONTS.filter(function(x){return x.n==st.font})[0];if(ff)applyFont(ff);}
var cw=document.getElementById('izColors');COLORS.forEach(function(c){var b=document.createElement('button');b.style.cssText='width:28px;height:28px;border-radius:50%;border:2px solid #fff;box-shadow:0 0 0 1px #cbd5e1;cursor:pointer;background:'+c;b.onclick=function(){applyColor(c);st.color=c;save(st);queueTheme(false);};cw.appendChild(b);});
var fw=document.getElementById('izFonts');FONTS.forEach(function(f){var b=document.createElement('button');b.textContent=f.n;b.style.cssText='text-align:left;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:9px 11px;cursor:pointer;font-weight:600;font-size:14px;color:#334155';b.onclick=function(){applyFont(f);st.font=f.n;save(st);queueTheme(false);};fw.appendChild(b);});
document.getElementById('izEditBtn').onclick=function(){var p=document.getElementById('izEditPanel');p.style.display=p.style.display=='none'?'block':'none';};
document.getElementById('izReset').onclick=function(){['--c-accent','--c-accent-2','--c-accent-contrast','--font-heading','--font-body'].forEach(function(v){R.style.removeProperty(v)});try{localStorage.removeItem(K)}catch(e){}st={};queueTheme(true);};
Array.prototype.forEach.call(document.querySelectorAll('.izende-claim-btn'),function(a){a.addEventListener('click',function(e){if(!LEAD||!themeDirty)return;e.preventDefault();var href=a.href;clearTimeout(themeTimer);setMsg('<span class="izspin"></span>Saving your look before claim…','#334155');persistTheme(themeResetPending,function(ok){if(ok){location.href=href;}else{setMsg('Could not save your look yet. Try Claim again in a moment.','#b91c1c');}});});});
if(!LEAD){document.getElementById('izAskWrap').style.display='none';}
function stopWait(){if(waitTimer){clearInterval(waitTimer);waitTimer=null;}}
function reset(){stopWait();send.disabled=false;send.textContent=lbl;}
function startWait(freeLeft){stopWait();waitEnd=Date.now()+120000;function draw(){var left=Math.max(0,Math.ceil((waitEnd-Date.now())/1000));var pct=Math.max(0,Math.min(100,(left/120)*100));var mm=Math.floor(left/60),ss=String(left%60).padStart(2,'0');var label=left>0?('Estimated update in <strong style="color:#2563eb">'+mm+':'+ss+'</strong>'):('<strong style="color:#2563eb">Finalizing your change…</strong>');setMsg('<span class="izspin"></span>'+label+(freeLeft!=null?' <span style="color:#64748b">('+freeLeft+' free left)</span>':'')+'<div class="izwaitbar" aria-hidden="true"><span style="width:'+pct+'%"></span></div><div style="color:#64748b">Keep this panel open — we will refresh the preview when it is done.</div>','#334155');}draw();waitTimer=setInterval(draw,1000);}
function poll(eid,tries){
 if(tries<=0){setMsg('Still working — refresh the page in a moment to see your change.','#475569');reset();return;}
 fetch(EP+'?eid='+encodeURIComponent(eid)+'&lead_id='+encodeURIComponent(LEAD)+'&token='+encodeURIComponent(TOKEN)).then(function(r){return r.json();}).then(function(s){
  if(s&&s.status==='applied'){stopWait();setMsg('✓ Done! Refreshing your preview…','#15803d');setTimeout(function(){location.reload();},900);}
  else if(s&&s.status==='failed'){setMsg('That one didn’t take — try rewording the change.','#b91c1c');reset();}
  else{setTimeout(function(){poll(eid,tries-1);},7000);}
 }).catch(function(){setTimeout(function(){poll(eid,tries-1);},7000);});
}
send&&(send.onclick=function(){var q=(ask.value||'').trim();if(q.length<3){setMsg('Tell us the change in a sentence.','#b91c1c');return;}
send.disabled=true;send.textContent='Sending…';setMsg('<span class="izspin"></span>Sending your request…','#334155');
fetch(EP,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({lead_id:LEAD,token:TOKEN,request_text:q})}).then(function(r){return r.json();}).then(function(j){
if(j.success&&j.edit_id){ask.value='';startWait(j.free_left);poll(j.edit_id,24);}
else if(j.success){ask.value='';setMsg('✓ Queued — refresh in about a minute.','#15803d');reset();}
else if(j.gated){setMsg((j.message||'')+' <a href="'+(j.claim_url||'#')+'" style="color:#2563eb;font-weight:700">Claim now &rarr;</a>','#1d4ed8');reset();}
else{setMsg(j.message||'Could not queue that.','#b91c1c');reset();}
}).catch(function(){setMsg('Network error — try again.','#b91c1c');reset();});});
})();</script>
JS;
    $js = str_replace(['__SLUG__', '__LEAD__', '__TOKEN__'], [$slug, $leadId, $token], $js);
    if (stripos($html, '</body>') !== false) {
        return preg_replace('/<\/body>/i', $js . '</body>', $html, 1);
    }
    return $html . $js;
}

function writePreview($slug, $html, $leadId = '', $createdAt = '') {
    global $deployRoot, $baseUrl;
    if (!preg_match('/^[a-z0-9][a-z0-9-]{1,70}$/', $slug)) {
        return [null, 'invalid slug'];
    }
    $dir = $deployRoot . '/' . $slug;
    if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
        return [null, 'mkdir failed: ' . $dir];
    }
    $fileHtml = injectPreviewRobotsMeta($html);                  // preview only; stored generated_html stays indexable
    $fileHtml = injectClaimBar($fileHtml, $slug, $createdAt);    // preview gets the claim bar; stored generated_html stays clean
    $fileHtml = injectEditorWidget($fileHtml, $slug, $leadId);   // + the "Customize" widget (presets + text change requests), preview only
    $tmp = $dir . '/.index.' . bin2hex(random_bytes(4)) . '.tmp';
    if (@file_put_contents($tmp, $fileHtml, LOCK_EX) === false || !@rename($tmp, $dir . '/index.html')) {
        @unlink($tmp);
        return [null, 'write failed'];
    }
    return [$baseUrl . '/' . $slug . '/', null];
}

function sendPreviewEmail($lead, $previewUrl) {
    $from = trim((string) (getenv('MAIL_FROM') ?: 'support@izendestudioweb.com'));
    $subject = 'Your free website preview is ready: ' . $lead['business_name'];
    $biz = htmlspecialchars($lead['business_name'], ENT_QUOTES);
    $url = htmlspecialchars($previewUrl, ENT_QUOTES);

    $html = <<<HTML
<!DOCTYPE html>
<html><body style="margin:0;padding:0;background:#f5f1ea;font-family:Georgia,'Times New Roman',serif;">
<div style="max-width:560px;margin:0 auto;padding:32px 20px;">
  <div style="background:#ffffff;border-radius:14px;padding:36px 32px;box-shadow:0 4px 18px rgba(0,0,0,.07);">
    <p style="margin:0 0 6px;font-size:13px;letter-spacing:.14em;text-transform:uppercase;color:#a0724a;font-family:Arial,sans-serif;">Izende Studio Web</p>
    <h1 style="margin:0 0 14px;font-size:26px;line-height:1.25;color:#241813;">Your website preview is ready 🎉</h1>
    <p style="margin:0 0 22px;font-size:16px;line-height:1.6;color:#4a443d;">We built a website for <strong>{$biz}</strong> — see it live right now:</p>
    <p style="text-align:center;margin:0 0 24px;">
      <a href="{$url}" style="display:inline-block;background:#a0724a;color:#ffffff;text-decoration:none;font-family:Arial,sans-serif;font-size:17px;font-weight:bold;padding:15px 34px;border-radius:999px;">View Your Free Preview</a>
    </p>
    <p style="margin:0 0 22px;font-size:13px;color:#8a857d;text-align:center;font-family:Arial,sans-serif;">Or copy this link: <a href="{$url}" style="color:#a0724a;">{$url}</a></p>
    <hr style="border:none;border-top:1px solid #eadccb;margin:0 0 22px;">
    <p style="margin:0 0 8px;font-size:15px;line-height:1.6;color:#4a443d;"><strong>Like what you see?</strong> Reply to this email or call <a href="tel:+13143126441" style="color:#a0724a;">(314) 312-6441</a> and we'll put it live on your own domain with managed hosting.</p>
  </div>
  <p style="text-align:center;font-size:12px;color:#8a857d;margin:18px 0 0;font-family:Arial,sans-serif;">Izende Studio Web &middot; St. Louis, MO &middot; izendestudioweb.com</p>
</div>
</body></html>
HTML;

    $text = "Your website draft for {$lead['business_name']} is ready!\n\n"
        . "View it here: $previewUrl\n\n"
        . "Like what you see? Reply to this email or call (314) 312-6441 and Izende Studio Web will put it live on your own domain with managed hosting.\n\n"
        . "— Izende Studio Web\n";

    // Multipart MIME: text + HTML alternative
    $boundary = 'b' . bin2hex(random_bytes(12));
    $headers = "From: Izende Studio Web <$from>\r\n"
        . "Reply-To: support@izendestudioweb.com\r\n"
        . "MIME-Version: 1.0\r\n"
        . "Content-Type: multipart/alternative; boundary=\"$boundary\"";
    $body = "--$boundary\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n$text\r\n"
        . "--$boundary\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n$html\r\n"
        . "--$boundary--";

    $targets = [$lead['contact_email'], 'support@izendestudioweb.com'];
    $sentAny = false;
    foreach (array_unique($targets) as $to) {
        $ok = @mail($to, $subject, $body, $headers);
        cronLog('Email ' . ($ok ? 'sent' : 'FAILED'), ['to' => $to]);
        $sentAny = $sentAny || $ok;
    }
    return $sentAny;
}

function expireOldPreviews($isCli) {
    global $deployRoot;
    $maxAge = 7 * 86400;
    $now = time();
    foreach (@scandir($deployRoot) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') { continue; }
        if ($entry === 'samples') { continue; } // permanent showcase sites — never expire
        $dir = $deployRoot . '/' . $entry;
        if (!is_dir($dir)) { continue; }
        if (($now - @filemtime($dir)) < $maxAge) { continue; }
        // Never expire previews belonging to claimed/converted clients
        list($code, $rows) = supabaseRequest('GET', '/rest/v1/site_builder_leads?preview_slug=eq.' . rawurlencode($entry) . '&select=id,status');
        $lead = ($code === 200 && is_array($rows) && count($rows)) ? $rows[0] : null;
        if ($lead && in_array($lead['status'], ['claimed', 'converted'], true)) { continue; }
        foreach (@scandir($dir) ?: [] as $f) {
            if ($f !== '.' && $f !== '..') { @unlink($dir . '/' . $f); }
        }
        @rmdir($dir);
        // Also remove the matching generated hero image dir so /genmedia doesn't accumulate
        $genDir = dirname($deployRoot) . '/genmedia/' . $entry;
        if (is_dir($genDir)) {
            foreach (@scandir($genDir) ?: [] as $f) {
                if ($f !== '.' && $f !== '..') { @unlink($genDir . '/' . $f); }
            }
            @rmdir($genDir);
        }
        if ($lead) {
            supabaseRequest('PATCH', '/rest/v1/site_builder_leads?id=eq.' . $lead['id'], ['status' => 'expired']);
        }
        cronLog('Preview expired + removed', ['slug' => $entry, 'lead' => $lead['id'] ?? null]);
        if (!$isCli) { echo "expired: $entry\n"; }
    }
}

// ---- Main --------------------------------------------------------------------
if (!defined('IZ_GEN_LIB')) {
expireOldPreviews($isCli);
list($code, $leads) = supabaseRequest('GET', '/rest/v1/site_builder_leads?select=*&status=eq.pending&order=created_at.asc&limit=3');
if ($code !== 200 || !is_array($leads)) {
    cronLog('Poll failed', ['http' => $code]);
    if (!$isCli) { echo "poll failed http=$code\n"; }
    exit(1);
}
if (count($leads) === 0) {
    if (!$isCli) { echo "no pending leads\n"; }
    exit(0);
}

foreach ($leads as $lead) {
    $id = $lead['id'];
    cronLog('Processing lead', ['id' => $id, 'business' => $lead['business_name']]);
    if (!$isCli) { echo "\nlead $id ({$lead['business_name']}) generating"; flush(); }

    $slug = slugForLead($lead);
    $heroUrl = generateHeroImage($lead, $slug, $isCli); // best-effort; '' falls back to CSS/SVG
    $logoUrl = generateLogoMark($lead, $slug, $isCli);  // best-effort; '' falls back to SVG monogram
    $supportUrls = generateSupportImages($lead, $slug, $isCli); // best-effort real content photos; missing spots fall back to CSS/SVG

    list($html, $err) = generateWithGlm($lead, $isCli, $heroUrl, $logoUrl, $supportUrls);
    if ($html === null) {
        cronLog('Generation FAILED', ['id' => $id, 'err' => $err]);
        supabaseRequest('PATCH', '/rest/v1/site_builder_leads?id=eq.' . $id, ['status' => 'failed']);
        if (!$isCli) { echo "\nFAILED: $err\n"; }
        continue;
    }

    $html = stripGrainOverlay($html); // kill any heavy full-screen film-grain/noise overlay GLM may add
    $html = injectMap($html, $lead); // replace the placeholder server-side so the Maps key never goes to GLM
    $html = injectBookingScript($html, $id); // wire any booking form to the endpoint (persisted into generated_html)

    list($previewUrl, $werr) = writePreview($slug, $html, $id, $lead['created_at'] ?? '');
    if ($previewUrl === null) {
        cronLog('Write FAILED', ['id' => $id, 'err' => $werr]);
        supabaseRequest('PATCH', '/rest/v1/site_builder_leads?id=eq.' . $id, ['status' => 'failed']);
        if (!$isCli) { echo "\nWRITE FAILED: $werr\n"; }
        continue;
    }

    list($ucode) = supabaseRequest('PATCH', '/rest/v1/site_builder_leads?id=eq.' . $id, [
        'status' => 'preview_live',
        'preview_url' => $previewUrl,
        'preview_slug' => $slug,
    ]);
    if ($ucode < 200 || $ucode >= 300) {
        cronLog('Status PATCH failed after preview write', ['id' => $id, 'url' => $previewUrl, 'patch_http' => $ucode]);
        if (!$isCli) { echo "\nSTATUS PATCH FAILED: $ucode (preview exists at $previewUrl)\n"; }
        continue;
    }
    list($hcode) = supabaseRequest('PATCH', '/rest/v1/site_builder_leads?id=eq.' . $id, [
        'generated_html' => $html,
    ]);
    if ($hcode < 200 || $hcode >= 300) {
        cronLog('generated_html PATCH failed', ['id' => $id, 'patch_http' => $hcode, 'bytes' => strlen($html)]);
    }
    $emailed = sendPreviewEmail($lead, $previewUrl);
    cronLog('Lead complete', ['id' => $id, 'url' => $previewUrl, 'bytes' => strlen($html), 'patch_http' => $ucode, 'html_patch_http' => $hcode, 'emailed' => $emailed]);
    if (!$isCli) { echo "\nDONE: $previewUrl (emailed: " . ($emailed ? 'yes' : 'NO') . ")\n"; flush(); }
}

flock($lock, LOCK_UN);
if (!$isCli) { echo "all done\n"; }
} // end !IZ_GEN_LIB (main)
