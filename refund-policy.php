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
  <title>Refund Policy | Izende Studio Web</title>
  <?php include 'assets/includes/header-links.php'; ?>
</head>
<body>
  <?php include 'assets/includes/topbar.php'; ?>
  <?php include 'assets/includes/header.php'; ?>
  <main id="main">
    <section class="breadcrumbs">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center">
          <h2>Refund Policy</h2>
          <ol>
            <li><a href="/">Home</a></li>
            <li>Refund Policy</li>
          </ol>
        </div>
      </div>
    </section>

    <section class="legal-content section-bg">
      <div class="container">
  <div class="legal-last-updated">Last Updated: 2025-10-15 — Effective Date: 2025-10-15</div>

        <h2 id="overview">1. Overview</h2>
        <p>This Refund Policy describes how refunds and cancellations are handled for our services. We stand behind the quality of our work and offer fair refund terms that balance customer satisfaction with the costs of providing services.</p>

        <h2 id="money-back-guarantee">2. 30-Day Money-Back Guarantee</h2>
        <p>We offer a 30-day money-back guarantee on new website projects and initial hosting purchases (first billing cycle only). If you are not satisfied with our services within the first 30 days, you may request a full refund. This guarantee applies to:</p>
        <ul>
          <li>New website design and development projects (initial deposit or first milestone)</li>
          <li>New shared hosting, VPS hosting, or dedicated server purchases (first month or billing cycle)</li>
          <li>WordPress starter packages</li>
        </ul>
        <p>The 30-day period begins on the date of purchase or project commencement as documented in your service agreement.</p>

        <h2 id="hosting-refunds">3. Hosting Service Refunds</h2>
        <h3>Shared and VPS Hosting</h3>
        <ul>
          <li><strong>First billing cycle:</strong> Eligible for full refund within 30 days of purchase under the money-back guarantee.</li>
          <li><strong>Renewals:</strong> Hosting renewals are non-refundable. You may cancel at any time, but no prorated refunds will be issued for unused time in the current billing period.</li>
          <li><strong>Monthly plans:</strong> May be canceled at any time; no refund for the current month.</li>
          <li><strong>Annual plans:</strong> Eligible for refund only within the first 30 days. After 30 days, annual hosting is non-refundable.</li>
        </ul>
        <h3>Dedicated Servers</h3>
        <ul>
          <li>30-day money-back guarantee applies to the first month.</li>
          <li>Setup fees are non-refundable.</li>
          <li>After 30 days, dedicated server fees are non-refundable. You may cancel with 30 days' written notice.</li>
        </ul>

        <h2 id="project-refunds">4. Website Design and Development Project Refunds</h2>
        <p>Refunds for custom website design, development, SEO, and consulting projects are handled based on project stage and work completed:</p>
        <ul>
          <li><strong>Before work begins:</strong> Full refund of deposit, less any third-party costs already incurred (domain registration, licenses, etc.).</li>
          <li><strong>During design/development:</strong> Refunds will be calculated on a pro-rata basis based on milestones completed and documented work performed. Completed milestones and deliverables already provided are non-refundable.</li>
          <li><strong>After project completion and delivery:</strong> No refunds. Any issues with completed work will be addressed under our warranty and support terms.</li>
          <li><strong>Cancellation by customer:</strong> Customer is responsible for payment for all work completed to date, plus any non-refundable third-party costs.</li>
        </ul>

        <h2 id="monthly-services">5. Monthly Recurring Services</h2>
        <p>Monthly recurring services (SEO, maintenance plans, social media management, email marketing) may be canceled at any time with at least 5 business days' notice prior to the next billing date. No refunds will be issued for the current billing month. Services will continue through the end of the paid period.</p>

        <h2 id="non-refundable">6. Non-Refundable Items</h2>
        <p>The following items and fees are non-refundable under all circumstances:</p>
        <ul>
          <li>Domain name registration and renewal fees (due to registrar policies)</li>
          <li>Third-party software licenses purchased on your behalf (e.g., premium themes, plugins, SSL certificates)</li>
          <li>Setup fees and migration fees</li>
          <li>Custom development work that has been completed and delivered</li>
          <li>Hosting renewals after the first 30 days</li>
          <li>SMS, email marketing, or advertising credits purchased through third-party platforms</li>
          <li>Video editing services after files have been delivered</li>
        </ul>

        <h2 id="refund-process">7. How to Request a Refund</h2>
        <h3>Refund Request Process</h3>
        <ol>
          <li>Submit your refund request via email to <a href="mailto:billing@izendestudioweb.com">billing@izendestudioweb.com</a> (or <a href="mailto:support@izendestudioweb.com">support@izendestudioweb.com</a>) with your account details, invoice number, and reason for the request.</li>
          <li>We will acknowledge receipt of your request within 2-5 business days.</li>
          <li>We will review your request and determine eligibility based on this policy, your service agreement, and any applicable third-party vendor terms.</li>
          <li>If approved, refunds will be issued within 5-10 business days and returned via the original payment method. The time it appears on your account depends on the payment provider.</li>
          <li>You will receive an email confirmation once the refund has been issued. Please allow an additional 5-7 business days for your financial institution to post the refund.</li>
        </ol>

        <h2 id="chargebacks">8. Chargebacks and Payment Disputes</h2>
        <p>If you dispute a charge with your payment provider or credit card company, please contact us immediately at <a href="mailto:support@izendestudioweb.com">support@izendestudioweb.com</a> so we can work to resolve the issue. We are committed to addressing any billing concerns fairly and promptly.</p>
        <p>Unwarranted chargebacks (chargebacks filed without first contacting us or after a refund has already been issued) may result in immediate account suspension and termination of services. We reserve the right to dispute illegitimate chargebacks and may charge a $50 administrative fee for processing chargeback disputes.</p>

        <h2 id="cancellation">9. Cancellation and Data Retention</h2>
        <p>To cancel services, provide written notice to <a href="mailto:support@izendestudioweb.com">support@izendestudioweb.com</a> with at least 5 business days' notice (or as specified in your service agreement). Upon cancellation:</p>
        <ul>
          <li>Services will continue through the end of the current billing period.</li>
          <li>You will have 30 days to download and back up your data from our servers.</li>
          <li>After 30 days, all data, files, and backups will be permanently deleted and cannot be recovered.</li>
          <li>You are solely responsible for backing up and downloading your data before cancellation.</li>
        </ul>

        <h2 id="exceptions">10. Exceptions, Force Majeure, and Contact</h2>
        <h3>Exceptions</h3>
        <p>We may make exceptions to this policy on a case-by-case basis at our sole discretion for extraordinary circumstances. Contact us to discuss your situation.</p>
        <h3>Force Majeure</h3>
        <p>Refunds will not be provided for service interruptions or delays caused by events beyond our reasonable control, including natural disasters, acts of war or terrorism, government restrictions, network outages, or third-party failures.</p>
        <h3>Contact and Questions</h3>
        <p>If you have questions about this Refund Policy or need assistance with a refund or cancellation, please contact us:</p>
        <p>Email: <a href="mailto:billing@izendestudioweb.com">billing@izendestudioweb.com</a> or <a href="mailto:support@izendestudioweb.com">support@izendestudioweb.com</a><br>
        Phone: +1 314-312-6441</p>
        <h3>Frequently Asked Questions</h3>
        <p><strong>Q: Can I get a refund if I don't like my website?</strong><br>
        A: Yes, if you request a refund within the first 30 days and before substantial work has been completed. Otherwise, refunds are based on completed milestones.</p>
        <p><strong>Q: What if I cancel my hosting after 6 months?</strong><br>
        A: You may cancel at any time, but no refund will be issued for unused time. The 30-day money-back guarantee only applies to new purchases.</p>
        <p><strong>Q: Are domain registrations refundable?</strong><br>
        A: No, domain registration fees are non-refundable due to registrar policies, even if requested within 30 days.</p>
      </div>
    </section>

  </main>
  <?php include 'assets/includes/footer.php'; ?>
</body>
</html>
