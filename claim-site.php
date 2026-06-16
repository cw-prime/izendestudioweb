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

// Optional context: ?slug= lets us greet them with their business name.
$bizName = '';
$slug = preg_replace('/[^a-z0-9-]/', '', strtolower((string)($_GET['slug'] ?? '')));
if ($slug !== '') {
    $base = rtrim((string)getEnv('SUPABASE_URL', ''), '/');
    $key  = trim((string)getEnv('SUPABASE_SERVICE_ROLE_KEY', ''));
    if ($base !== '' && $key !== '') {
        $ch = curl_init($base . '/rest/v1/site_builder_leads?preview_slug=eq.' . rawurlencode($slug) . '&select=business_name&limit=1');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['apikey: ' . $key, 'Authorization: Bearer ' . $key]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        $rows = json_decode((string)curl_exec($ch), true); curl_close($ch);
        $bizName = trim((string)($rows[0]['business_name'] ?? ''));
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
  </style>
</head>
<body>
  <?php include './assets/includes/header.php'; ?>

  <main class="claim-wrap">
    <div class="claim-head">
      <h1><?= $bizName !== '' ? 'Make ' . htmlspecialchars($bizName, ENT_QUOTES) . ' yours' : 'Make it yours' ?></h1>
      <p>Pick how you want to run your new site. Every plan includes hosting, your own domain set up for you, a free professional email, SSL, and real support — no Wix or GoDaddy upsells.</p>
    </div>

    <div class="claim-grid">
      <!-- Get Online (static) -->
      <div class="plan">
        <span class="kind">Static hosting</span>
        <h2>Get Online</h2>
        <p class="sub">The site you just saw — live, hosted, and done for you.</p>
        <div class="price">$39<span>/mo</span></div>
        <ul>
          <li><i class="bi bi-check-lg"></i> The exact site you previewed, published</li>
          <li class="feat"><i class="bi bi-envelope-fill"></i> Free professional email @ your domain</li>
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
          <li class="feat"><i class="bi bi-envelope-fill"></i> Free professional email @ your domain</li>
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
          <li class="feat"><i class="bi bi-envelope-fill"></i> Free professional email @ your domain</li>
          <li class="feat"><i class="bi bi-calendar-check-fill"></i> Online booking included</li>
          <li><i class="bi bi-check-lg"></i> We make your everyday edits — text, photos, hours, new pages</li>
          <li><i class="bi bi-check-lg"></i> Automatic backups, updates, security &amp; uptime monitoring</li>
          <li><i class="bi bi-check-lg"></i> Priority same-day support</li>
        </ul>
        <a class="btn btn-ghost" href="<?= $cartBase . '16' . $slugQ ?>">Choose We Run It For You</a>
      </div>
    </div>

    <p class="claim-reassure"><i class="bi bi-shield-check"></i> Everything is set up automatically after checkout — just use the same email from your preview so we match your site. Online booking is included with <em>We Run It For You</em> and can be added to any plan. Managed edits are fair-use each month; full redesigns or custom development are quoted separately. <br><strong>Cancel anytime · Built &amp; supported in St. Louis.</strong></p>
  </main>

  <?php include './assets/includes/footer.php'; ?>
</body>
</html>
