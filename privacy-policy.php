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
  <title>Privacy Policy | Izende Studio Web</title>
  <meta name="description" content="Privacy policy for Izende Studio Web. Learn how we collect, use, and protect your personal information. GDPR and CCPA compliant.">
  <meta name="keywords" content="privacy policy, data protection, GDPR, CCPA, privacy rights">
  <?php include 'assets/includes/header-links.php'; ?>
</head>
<body>
  <?php include 'assets/includes/topbar.php'; ?>
  <?php include 'assets/includes/header.php'; ?>

  <main id="main">

    <section class="breadcrumbs">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center">
          <h2>Privacy Policy</h2>
          <ol>
            <li><a href="/">Home</a></li>
            <li>Privacy Policy</li>
          </ol>
        </div>
      </div>
    </section>

    <section class="legal-content section-bg">
      <div class="container">
        <div class="legal-last-updated">Last Updated: October 15, 2025 — Effective Date: October 15, 2025</div>
        <div class="legal-toc">
          <h3>Contents</h3>
          <ul>
            <li><a href="#who-we-are">1. Who We Are</a></li>
            <li><a href="#services">2. Our Services</a></li>
            <li><a href="#information-we-collect">3. Information We Collect</a></li>
            <li><a href="#how-we-use">4. Purposes and Legal Bases</a></li>
            <li><a href="#cookies">5. Cookies and Tracking</a></li>
            <li><a href="#sharing">6. How We Share Your Information</a></li>
            <li><a href="#international">7. International Transfers</a></li>
            <li><a href="#retention">8. Data Retention</a></li>
            <li><a href="#rights">9. Your Rights</a></li>
            <li><a href="#california">10. California Privacy Rights</a></li>
            <li><a href="#security">11. How We Protect Your Information</a></li>
            <li><a href="#children">12. Children's Privacy</a></li>
            <li><a href="#changes">13. Updates to This Policy</a></li>
            <li><a href="#contact">14. Contact Information</a></li>
          </ul>
        </div>

        <h2 id="who-we-are">1. Who We Are</h2>
        <p>Izende Studio Web is a Professional Service based in St. Louis, Missouri. PO Box 23456, St. Louis, MO 63156. Email: support@izendestudioweb.com. Phone: +1 314-312-6441. We are the data controller for personal information collected through our website and services.</p>

        <h2 id="services">2. Our Services</h2>
        <p>We provide web design, web hosting, domain registration, SEO, e-commerce development, video editing, social media management, email marketing, website maintenance and speed optimization. We act as controller for our website and marketing operations and as a processor for some hosting customers under a separate DPA.</p>

        <h2 id="information-we-collect">3. Information We Collect</h2>
        <h3>3.1 Information You Provide</h3>
        <ul>
          <li>Account signup: name, email, phone, address, company name</li>
          <li>Payment/billing details (processed by third-party payment processors)</li>
          <li>Support tickets and attachments</li>
          <li>Quote and contact form submissions</li>
          <li>Domain registration data (WHOIS)</li>
        </ul>

        <h3>3.2 Information Automatically Collected</h3>
        <ul>
          <li>Server logs: IP address, timestamps, user agent, requested URLs</li>
          <li>Cookies: session cookies (essential), reCAPTCHA cookies, Google Fonts cookies</li>
          <li>Hosting metrics and security logs</li>
        </ul>

        <h3>3.3 Information from Third Parties</h3>
        <p>Registrars, payment processors, fraud prevention services, and public WHOIS data.</p>

                <h2 id="how-we-use">4. Purposes and Legal Bases</h2>
                <p>We process personal data for the purposes listed in the table below. Where required by law, we document the legal basis for each processing activity.</p>

                <div class="table-responsive">
                  <table class="table table-bordered">
                    <thead>
                      <tr>
                        <th>Purpose</th>
                        <th>Data Categories</th>
                        <th>Legal Basis</th>
                        <th>Business Purpose</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>Account creation and management</td>
                        <td>Name, email, phone, billing address</td>
                        <td>Contract performance; legitimate interests</td>
                        <td>Provide and manage customer accounts</td>
                      </tr>
                      <tr>
                        <td>Billing and payments</td>
                        <td>Billing details, transaction records</td>
                        <td>Contract performance; legal obligation</td>
                        <td>Process payments, invoicing, tax compliance</td>
                      </tr>
                      <tr>
                        <td>Support and ticketing</td>
                        <td>Support correspondence, attachments</td>
                        <td>Contract performance; legitimate interests</td>
                        <td>Provide technical and account support</td>
                      </tr>
                      <tr>
                        <td>Security and fraud prevention</td>
                        <td>Server logs, IP address, user-agent</td>
                        <td>Legitimate interests; legal obligation</td>
                        <td>Protect systems and users</td>
                      </tr>
                      <tr>
                        <td>Marketing and analytics</td>
                        <td>Browsing behaviour, contact details (where consented)</td>
                        <td>Consent (where required) or legitimate interests</td>
                        <td>Measure and improve services; marketing communications</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

        <h2 id="cookies">5. Cookies and Tracking</h2>
        <p>We use essential cookies for session management. We use reCAPTCHA and Google Fonts which may set cookies; these are treated as functional cookies and require consent in the EU/UK. See our <a href="/cookie-policy.php">Cookie Policy</a> for details and preference controls.</p>

        <h2 id="sharing">6. How We Share Your Information</h2>
        <p>We share data with service providers such as hosting infrastructure, CDNs, payment processors and email delivery providers under contract. We do not sell personal information.</p>

        <h2 id="international">7. International Data Transfers</h2>
        <p>We operate US-based servers. For EU/UK customers we rely on Standard Contractual Clauses (SCCs) for transfers where applicable.</p>

        <h2 id="retention">8. How Long We Keep Your Information</h2>
        <p>Retention periods vary by data type. Account and billing data are retained for the duration of the contract plus 7 years for tax/legal purposes. Server logs: 90 days. Backups: 30 days.</p>

        <h2 id="rights">9. Your Privacy Rights (EU/UK Residents)</h2>
        <p>You have rights including access, rectification, erasure, restriction of processing, data portability, objection, and withdrawal of consent. To exercise rights, email <a href="mailto:support@izendestudioweb.com">support@izendestudioweb.com</a> or submit a request at <a href="/data-subject-request.php">Data Subject Request</a>.</p>

        <h2 id="california">10. Your Privacy Rights (California Residents)</h2>
        <p>California residents have rights under the CCPA/CPRA. We do not sell or share personal information. To opt-out, visit our <a href="/do-not-sell.php">Do Not Sell or Share</a> page or enable Global Privacy Control (GPC) in your browser.</p>

        <h2 id="security">11. How We Protect Your Information</h2>
        <p>We implement technical and organizational measures such as TLS encryption, access controls, monitoring, backups and patching. No transmission over the internet is perfectly secure.</p>

        <h2 id="children">12. Children's Privacy</h2>
        <p>We do not knowingly collect personal information from children under 13. Contact us if you believe we have mistakenly collected data from a child.</p>

        <h2 id="changes">13. Updates to This Privacy Policy</h2>
        <p>We may update this policy when necessary. We will post the revised policy with a new effective date.</p>

        <h2 id="contact">14. Contact Information</h2>
        <p>For privacy requests: <a href="mailto:support@izendestudioweb.com">support@izendestudioweb.com</a> | Phone: +1 314-312-6441 | Mail: PO Box 23456, St. Louis, MO 63156</p>

      </div>
    </section>

  </main>

  <?php include 'assets/includes/footer.php'; ?>
</body>
</html>
