<?php
/**
 * Website Drafter — Claim / Plan Chooser
 *
 * Where "Claim this site" lands. Presents the three tiers and routes the
 * prospect to the matching WHMCS product cart:
 *   Static $39 (pid 14) | WordPress $49 (pid 15, popular) | Managed $149 (pid 16)
 * On payment, WHMCS creates the cPanel account and zeno_provision.php finishes
 * the site (static deploy or WordPress install+seed) matched to this lead by
 * the customer's email/domain.
 */

require_once __DIR__ . '/config/env-loader.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/admin/config/database.php';
require_once __DIR__ . '/includes/SEOHelper.php';

initSecureSession();
setSecurityHeaders();
$nonce = getCSPNonce();

// Optional context: ?slug= lets us greet them with their business name and show a
// truthful reservation countdown (previews auto-expire 7 days after created_at —
// enforced by expireOldPreviews() in scripts/generate-pending-previews.php).
$bizName = '';
$claimExpiryMs = 0;
$slug = preg_replace('/[^a-z0-9-]/', '', strtolower((string)($_GET['slug'] ?? '')));
if ($slug !== '') {
    $base = rtrim((string)getEnv('SUPABASE_URL', ''), '/');
    $key  = trim((string)getEnv('SUPABASE_SERVICE_ROLE_KEY', ''));
    if ($base !== '' && $key !== '') {
        $ch = curl_init($base . '/rest/v1/site_builder_leads?preview_slug=eq.' . rawurlencode($slug) . '&select=business_name,created_at&limit=1');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['apikey: ' . $key, 'Authorization: Bearer ' . $key]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        $rows = json_decode((string)curl_exec($ch), true); curl_close($ch);
        $bizName = trim((string)($rows[0]['business_name'] ?? ''));
        $created = trim((string)($rows[0]['created_at'] ?? ''));
        if ($created !== '' && ($ts = strtotime($created)) !== false) {
            $exp = $ts + 7 * 86400;
            if ($exp > time()) { $claimExpiryMs = $exp * 1000; } // only when still in the future
        }
    }
}
$cartBase = '/adminIzende/cart.php?a=add&pid=';
$slugQ = $slug !== '' ? ('&slug=' . rawurlencode($slug)) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
<?php
SEOHelper::outputMetaTags('claim-site', [
    'page_title' => 'Make It Yours — Choose Your Plan | Izende Studio Web',
    'meta_description' => 'Claim the website we built for you. Choose hosted static, editable WordPress, or fully managed WordPress — we set it all up automatically.',
    'canonical_url' => 'https://izendestudioweb.com/claim-site.php'
]);
?>
  <?php include './assets/includes/header-links.php'; ?>
  <style nonce="<?= htmlspecialchars($nonce, ENT_QUOTES) ?>">
    .claim-wrap{max-width:1080px;margin:0 auto;padding:48px 18px 72px}
    .claim-head{text-align:center;max-width:680px;margin:0 auto 36px}
    .claim-head h1{font-size:clamp(28px,4vw,40px);font-weight:800;color:#0f172a;margin:0 0 10px}
    .claim-head p{font-size:17px;color:#475569;margin:0}
    .claim-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;align-items:stretch}
    @media(max-width:880px){.claim-grid{grid-template-columns:1fr;max-width:420px;margin:0 auto}}
    .plan{position:relative;display:flex;flex-direction:column;background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px 24px;box-shadow:0 2px 12px rgba(15,23,42,.05)}
    .plan.pop{border:2px solid #2563eb;box-shadow:0 12px 32px rgba(37,99,235,.18);transform:translateY(-6px)}
    @media(max-width:880px){.plan.pop{transform:none}}
    .plan .tag{position:absolute;top:-13px;left:50%;transform:translateX(-50%);background:#2563eb;color:#fff;font-size:12px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;padding:5px 14px;border-radius:999px;white-space:nowrap}
    .plan h2{font-size:20px;font-weight:800;color:#0f172a;margin:0 0 2px}
    .plan .kind{display:inline-block;font-size:11.5px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#94a3b8;margin:0 0 6px}
    .plan .sub{font-size:14px;color:#64748b;margin:0 0 16px;min-height:38px}
    .plan li.feat{color:#0f172a;font-weight:700}
    .plan li.feat i{color:#2563eb}
    .plan .price{font-size:38px;font-weight:800;color:#0f172a;line-height:1}
    .plan .price span{font-size:15px;font-weight:600;color:#64748b}
    .plan ul{list-style:none;padding:0;margin:18px 0 24px;flex:1}
    .plan li{display:flex;gap:9px;align-items:flex-start;font-size:14.5px;color:#334155;padding:7px 0;border-bottom:1px solid #f1f5f9}
    .plan li i{color:#16a34a;margin-top:2px;flex:0 0 auto}
    .plan .btn{display:block;text-align:center;text-decoration:none;font-weight:700;padding:13px 18px;border-radius:10px;transition:transform .12s,box-shadow .12s,background .12s}
    .plan .btn-primary{background:#2563eb;color:#fff;box-shadow:0 6px 16px rgba(37,99,235,.3)}
    .plan .btn-primary:hover{background:#1d4ed8;transform:translateY(-2px)}
    .plan .btn-ghost{background:#f1f5f9;color:#1e293b}
    .plan .btn-ghost:hover{background:#e2e8f0}
    .claim-reassure{text-align:center;color:#64748b;font-size:14px;margin:30px auto 0;max-width:640px}
    .claim-reassure i{color:#16a34a}
    /* reservation countdown (truthful 7-day expiry) */
    .claim-countdown{max-width:660px;margin:0 auto 26px;text-align:center;background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;font-size:15px;font-weight:700;padding:11px 16px;border-radius:12px}
    .claim-countdown b{font-variant-numeric:tabular-nums;color:#c2410c}
    /* "why this beats the old way" 3-way value comparison */
    .claim-why{max-width:1000px;margin:0 auto 42px}
    .cw-head{text-align:center;font-size:clamp(20px,2.4vw,26px);font-weight:800;color:#0f172a;margin:0 0 8px}
    .cw-anchor{text-align:center;max-width:740px;margin:0 auto 24px;color:#475569;font-size:16px;line-height:1.5}
    .cw-anchor b{color:#0f172a}
    .cw-cols{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;align-items:stretch}
    @media(max-width:760px){.cw-cols{grid-template-columns:1fr;max-width:420px;margin:0 auto}}
    .cw-col{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px 18px}
    .cw-col.cw-win{border:2px solid #2563eb;box-shadow:0 12px 30px rgba(37,99,235,.16)}
    .cw-h{font-weight:800;font-size:16px;color:#0f172a;margin-bottom:4px}
    .cw-win .cw-h{color:#2563eb}
    .cw-price{font-size:22px;font-weight:800;color:#0f172a;margin-bottom:14px;line-height:1.1}
    .cw-price span{display:block;font-size:12.5px;font-weight:600;color:#64748b;margin-top:3px}
    .cw-col ul{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:9px}
    .cw-col li{font-size:14px;line-height:1.35;color:#334155;padding-left:24px;position:relative}
    .cw-win li::before{content:"\2713";color:#16a34a;position:absolute;left:0;font-weight:700}
    .cw-col li.no{color:#64748b}
    .cw-col li.no::before{content:"\2715";color:#cbd5e1;position:absolute;left:0;font-weight:700}
  </style>
</head>
<body>
  <?php include './assets/includes/header.php'; ?>

  <main class="claim-wrap">
    <div class="claim-head">
      <h1><?= $bizName !== '' ? 'Make ' . htmlspecialchars($bizName, ENT_QUOTES) . ' yours' : 'Make it yours' ?></h1>
      <p>Pick how you want to run your new site. Every plan includes hosting, SSL, real support, professional email setup, and first-year standard domain registration when you register a new domain during checkout.</p>
    </div>

<?php if ($claimExpiryMs > 0): ?>
    <div class="claim-countdown" id="claimCountdown" role="timer" aria-live="polite">
      <i class="bi bi-clock-history"></i> Your site is reserved — <b id="ccTime">7d 00:00:00</b> left before it expires
    </div>
<?php endif; ?>

    <section class="claim-why">
      <h2 class="cw-head">Why this beats the old way</h2>
      <p class="cw-anchor">A custom website used to mean <b>$2,000&ndash;$5,000 upfront</b> and <b>months of back-and-forth</b> — then you're on your own. We replaced that. Yours is already built, and we host, secure &amp; keep it online for <b>$39/mo</b>. No big upfront check, cancel anytime.</p>
      <div class="cw-cols">
        <div class="cw-col">
          <div class="cw-h">Hire a designer</div>
          <div class="cw-price">$2,000&ndash;$5,000<span>upfront + months</span></div>
          <ul>
            <li class="no">A one-time build, then they're gone</li>
            <li class="no">Hosting &amp; edits billed separately</li>
            <li class="no">Every change: email, wait, pay</li>
          </ul>
        </div>
        <div class="cw-col">
          <div class="cw-h">DIY builder</div>
          <div class="cw-price">$12&ndash;$17<span>/mo</span></div>
          <ul>
            <li class="no">A blank canvas — you build it</li>
            <li class="no">Hours of your time</li>
            <li class="no">Then left on your own</li>
          </ul>
        </div>
        <div class="cw-col cw-win">
          <div class="cw-h">Izende</div>
          <div class="cw-price">$39<span>/mo — all-in</span></div>
          <ul>
            <li>Built in 2 minutes — already done</li>
            <li>Hosting, email, SSL, backups &amp; support included</li>
            <li>Want changes? Edit it yourself ($49) or we do it ($149)</li>
            <li>Never left on your own</li>
          </ul>
        </div>
      </div>
    </section>

    <div class="claim-grid">
      <!-- Get Online (static) -->
      <div class="plan">
        <span class="kind">Static hosting</span>
        <h2>Get Online</h2>
        <p class="sub">The site you just saw — live, hosted, and done for you.</p>
        <div class="price">$39<span>/mo</span></div>
        <ul>
          <li><i class="bi bi-check-lg"></i> The exact site you previewed, published</li>
          <li class="feat"><i class="bi bi-envelope-fill"></i> Free professional email setup @ your domain</li>
          <li><i class="bi bi-check-lg"></i> Looks perfect on phones &amp; tablets</li>
          <li><i class="bi bi-check-lg"></i> Google-ready, fast hosting, SSL &amp; daily backups</li>
          <li><i class="bi bi-check-lg"></i> Done-for-you setup; we keep it secure, fast &amp; online</li>
        </ul>
        <a class="btn btn-ghost" href="<?= $cartBase . '14' . $slugQ ?>">Choose Get Online</a>
      </div>

      <!-- Grow It Yourself (WordPress, popular) -->
      <div class="plan pop">
        <span class="tag">Most Popular</span>
        <span class="kind">WordPress</span>
        <h2>Grow It Yourself</h2>
        <p class="sub">Same design — now yours to edit anytime.</p>
        <div class="price">$49<span>/mo</span></div>
        <ul>
          <li><i class="bi bi-check-lg"></i> Everything in Get Online</li>
          <li class="feat"><i class="bi bi-envelope-fill"></i> Free professional email setup @ your domain</li>
          <li><i class="bi bi-check-lg"></i> Your design, rebuilt in WordPress</li>
          <li><i class="bi bi-check-lg"></i> Edit text, images &amp; pages yourself</li>
          <li><i class="bi bi-check-lg"></i> Add pages, a blog or photos anytime</li>
          <li><i class="bi bi-check-lg"></i> Easy dashboard — no code needed</li>
        </ul>
        <a class="btn btn-primary" href="<?= $cartBase . '15' . $slugQ ?>">Choose Grow It Yourself</a>
      </div>

      <!-- We Run It For You (managed) -->
      <div class="plan">
        <span class="kind">Managed WordPress</span>
        <h2>We Run It For You</h2>
        <p class="sub">Hands-off — we handle the site so you can run your business.</p>
        <div class="price">$149<span>/mo</span></div>
        <ul>
          <li><i class="bi bi-check-lg"></i> Everything in Grow It Yourself</li>
          <li class="feat"><i class="bi bi-envelope-fill"></i> Free professional email setup @ your domain</li>
          <li class="feat"><i class="bi bi-calendar-check-fill"></i> Online booking included</li>
          <li><i class="bi bi-check-lg"></i> We make your everyday edits — text, photos, hours, new pages</li>
          <li><i class="bi bi-check-lg"></i> Automatic backups, updates, security &amp; uptime monitoring</li>
          <li><i class="bi bi-check-lg"></i> Priority same-day support</li>
        </ul>
        <a class="btn btn-ghost" href="<?= $cartBase . '16' . $slugQ ?>">Choose We Run It For You</a>
      </div>
    </div>

    <p class="claim-reassure"><i class="bi bi-shield-check"></i> Everything is set up automatically after checkout — just use the same email from your preview so we match your site. New standard domains include the first year; renewals bill separately each year at the registrar renewal rate. Already own a domain? Connect it or transfer it during checkout. Online booking is included with <em>We Run It For You</em> and can be added to any plan. Managed edits are fair-use each month; full redesigns or custom development are quoted separately. <br><strong>Cancel anytime · Built &amp; supported in St. Louis.</strong></p>
  </main>

  <?php include './assets/includes/footer.php'; ?>
<?php if ($claimExpiryMs > 0): ?>
  <script nonce="<?= htmlspecialchars($nonce, ENT_QUOTES) ?>">
  (function () {
    var el = document.getElementById('ccTime');
    if (!el) { return; }
    var exp = <?= (int)$claimExpiryMs ?>;
    function p(n) { return String(n).padStart(2, '0'); }
    function tick() {
      var left = Math.max(0, exp - Date.now());
      if (left <= 0) { var box = document.getElementById('claimCountdown'); if (box) { box.style.display = 'none'; } return; }
      var s = Math.floor(left / 1000);
      var d = Math.floor(s / 86400); s -= d * 86400;
      var h = Math.floor(s / 3600); s -= h * 3600;
      var m = Math.floor(s / 60); s -= m * 60;
      el.textContent = d + 'd ' + p(h) + ':' + p(m) + ':' + p(s);
    }
    tick();
    setInterval(tick, 1000);
  })();
  </script>
<?php endif; ?>
</body>
</html>
