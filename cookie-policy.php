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
  <title>Cookie Policy | Izende Studio Web</title>
  <meta name="description" content="Detailed cookie policy describing essential, functional, analytics and marketing cookies and how to manage them.">
  <?php include 'assets/includes/header-links.php'; ?>
</head>
<body>
  <?php include 'assets/includes/topbar.php'; ?>
  <?php include 'assets/includes/header.php'; ?>
  <main id="main">
    <section class="breadcrumbs">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center">
          <h2>Cookie Policy</h2>
          <ol>
            <li><a href="/">Home</a></li>
            <li>Cookie Policy</li>
          </ol>
        </div>
      </div>
    </section>

    <section class="legal-content section-bg">
      <div class="container">
        <div class="legal-last-updated">Last Updated: October 15, 2025 — Effective Date: October 15, 2025</div>

        <h2>Our Use of Cookies</h2>
        <p>We use cookies and similar technologies to operate the site, provide services, secure accounts, and analyze usage. Cookies fall into the following categories:</p>

        <h3>Cookie Inventory</h3>
        <div class="table-responsive">
          <table class="table table-striped table-sm">
            <thead>
              <tr>
                <th>Name</th>
                <th>Purpose</th>
                <th>Category</th>
                <th>Retention</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>PHPSESSID</td>
                <td>Session identifier used to maintain login and session state</td>
                <td>Essential / Functional</td>
                <td>Session</td>
              </tr>
              <tr>
                <td>csrf_token</td>
                <td>CSRF protection token for form submissions</td>
                <td>Essential</td>
                <td>Session / short-lived</td>
              </tr>
              <tr>
                <td>izende_cookie_consent_v1</td>
                <td>Saves your cookie preferences for this site</td>
                <td>Preferences</td>
                <td>180 days</td>
              </tr>
              <tr>
                <td>_GRECAPTCHA</td>
                <td>reCAPTCHA token used for spam protection (Google)</td>
                <td>Functional / Security</td>
                <td>Short-lived (token)</td>
              </tr>
              <tr>
                <td>_ga, _gid (Google Analytics)</td>
                <td>Analytics: distinguish users and measure site usage</td>
                <td>Analytics (consent required in EU/UK)</td>
                <td>2 years / 24 hours</td>
              </tr>
              <!-- Add marketing cookies if/when used -->
            </tbody>
          </table>
        </div>

        <h3>Managing Cookies</h3>
        <p>Use the <a id="cookie-settings-link" href="#">Cookie Settings</a> to change preferences for analytics and marketing cookies. To manage cookies in your browser, follow the vendor instructions below (links open in new tabs):</p>
        <ul>
          <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener">Chrome: Manage cookies</a></li>
          <li><a href="https://support.mozilla.org/en-US/kb/enable-and-disable-cookies-website-preferences" target="_blank" rel="noopener">Firefox: Manage cookies</a></li>
          <li><a href="https://support.apple.com/guide/safari/manage-cookies-and-website-data-sfri11471/mac" target="_blank" rel="noopener">Safari: Manage cookies</a></li>
          <li><a href="https://support.microsoft.com/microsoft-edge/delete-cookies-in-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" rel="noopener">Edge: Manage cookies</a></li>
        </ul>

        <h3>Third Party Cookies</h3>
        <p>We may use third-party providers such as Google for analytics and reCAPTCHA. These providers have their own privacy policies:</p>
        <ul>
          <li><a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Google Privacy Policy</a></li>
          <li><a href="https://developers.google.com/recaptcha/intro" target="_blank" rel="noopener">Google reCAPTCHA</a></li>
        </ul>

        <h3>Global Privacy Control (GPC) & Do Not Track (DNT)</h3>
        <p>We honor Global Privacy Control (GPC) signals which indicate a user's choice to opt-out of the sale or sharing of personal information. If your browser sends a GPC signal, we will treat it as an opt-out of analytics and marketing cookies. Do Not Track (DNT) is an older mechanism that is not consistently implemented; we encourage the use of GPC for clarity.</p>

        <h3>Contact</h3>
        <p>For questions about this Cookie Policy or to make specific requests, see our <a href="/privacy-policy.php">Privacy Policy</a> or contact <a href="mailto:support@izendestudioweb.com">support@izendestudioweb.com</a>.</p>
      </div>
    </section>

  </main>
  <?php include 'assets/includes/footer.php'; ?>
</body>
</html>
