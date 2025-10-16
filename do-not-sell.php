<?php
require_once __DIR__ . '/config/env-loader.php';
require_once __DIR__ . '/config/security.php';
initSecureSession();
setSecurityHeaders();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Do Not Sell or Share My Personal Information | Izende Studio Web</title>
  <?php include 'assets/includes/header-links.php'; ?>
</head>
<body>
  <?php include 'assets/includes/topbar.php'; ?>
  <?php include 'assets/includes/header.php'; ?>
  <main id="main">
    <section class="breadcrumbs">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center">
          <h2>Do Not Sell or Share My Personal Information</h2>
          <ol>
            <li><a href="/">Home</a></li>
            <li>Do Not Sell</li>
          </ol>
        </div>
      </div>
    </section>

    <section class="legal-content section-bg">
      <div class="container">
        <div class="legal-last-updated">Last Updated: October 15, 2025 — Effective Date: October 15, 2025</div>

        <h2>Your Right to Opt-Out (California Residents)</h2>
        <p>Under the California Consumer Privacy Act (CCPA) and the California Privacy Rights Act (CPRA), California residents have the right to opt out of the sale or sharing of their personal information. We do not sell personal information; however, we provide this mechanism to record and honor opt-out choices.</p>

        <h2>Our Data Practices</h2>
        <p>We use service providers for hosting, analytics, and payment processing. We do not sell personal information. We may share data with service providers acting on our behalf under contract and only for limited purposes.</p>

        <h2>Global Privacy Control (GPC)</h2>
        <p>If your browser supports Global Privacy Control (GPC), enable it and our site will honor the signal as an opt-out of sale/sharing and non-essential cookie placement. For assistance enabling GPC, see your browser settings or privacy extensions.</p>

        <h2>How to Exercise Your Opt-Out Right</h2>
        <p>You may exercise your opt-out by any of the following methods:</p>
        <ul>
          <li>Enabling GPC in your browser (we will honor the signal)</li>
          <li>Submitting the form below (we will verify identity as necessary)</li>
          <li>Emailing <a href="mailto:support@izendestudioweb.com">support@izendestudioweb.com</a></li>
          <li>Calling +1 314-312-6441</li>
        </ul>
        <p>We may require reasonable identity verification to process opt-out requests. We will acknowledge receipt within 15 business days and will process the request consistent with applicable law.</p>

        <h2>Other California Rights</h2>
        <p>For a full description of California resident rights (access, deletion, correction, nondiscrimination), see our <a href="/privacy-policy.php">Privacy Policy</a>.</p>

        <h2>Contact</h2>
        <p>Contact us at <a href="mailto:support@izendestudioweb.com">support@izendestudioweb.com</a> or call +1 314-312-6441 for assistance.</p>

        <h3>Submit an Opt-Out Request</h3>
        <form method="post" action="/forms/data-subject-request.php">
          <input type="hidden" name="type" value="do_not_sell">
          <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" class="form-control" required aria-required="true">
          </div>
          <div class="mb-3">
            <label for="fullname" class="form-label">Full name (optional)</label>
            <input id="fullname" name="fullname" type="text" class="form-control">
          </div>
          <div class="mb-3">
            <button type="submit" class="btn btn-primary">Submit Opt-Out Request</button>
          </div>
        </form>
      </div>
    </section>

  </main>
  <?php include 'assets/includes/footer.php'; ?>
</body>
</html>
