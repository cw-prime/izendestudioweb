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
  <title>Accessibility Statement | Izende Studio Web</title>
  <?php include 'assets/includes/header-links.php'; ?>
</head>
<body>
  <?php include 'assets/includes/topbar.php'; ?>
  <?php include 'assets/includes/header.php'; ?>
  <main id="main">
    <section class="breadcrumbs">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center">
          <h2>Accessibility Statement</h2>
          <ol>
            <li><a href="/">Home</a></li>
            <li>Accessibility Statement</li>
          </ol>
        </div>
      </div>
    </section>

    <section class="legal-content section-bg">
      <div class="container">
        <div class="legal-last-updated">Last Updated: 2025-10-15 — Effective Date: 2025-10-15</div>

        <h2 id="commitment">Commitment to Accessibility</h2>
        <p>Izende Studio Web is committed to ensuring that our digital services are accessible to all users, including people with disabilities. We strive to conform to the Web Content Accessibility Guidelines (WCAG) 2.1 AA and to follow industry best practices.</p>

        <h2 id="conformance-status">Conformance Status</h2>
        <p>Current conformance target: WCAG 2.1 Level AA. We perform ongoing work to reduce barriers and remediate issues as they are identified. Some third-party content and embedded tools may not fully conform.</p>

        <h2 id="features">Accessibility Features</h2>
        <ul>
          <li>Semantic HTML and landmark regions (header, nav, main, footer)</li>
          <li>Use of ARIA attributes where necessary and appropriate</li>
          <li>Keyboard-accessible navigation and focus management</li>
          <li>Text alternatives for meaningful images and icons where possible</li>
          <li>Responsive design and scalable text for users who resize content</li>
          <li>Contrast-aware color choices and accessible forms with labels and instructions</li>
        </ul>

        <h2 id="known-limitations">Known Limitations</h2>
        <p>Some limitations may exist due to third-party embeds or legacy content. Notable known limitations include:</p>
        <ul>
          <li>Third‑party map and widget embeds that rely on their own controls and do not expose accessible APIs.</li>
          <li>Some older media assets may lack full captions or transcripts; transcripts will be provided on request where practicable.</li>
          <li>PDFs or documents uploaded by third parties may not meet accessibility standards; we will provide alternative formats on request.</li>
        </ul>

        <h2 id="assistive-tech">Assistive Technologies</h2>
        <p>Our site is tested for basic compatibility with common assistive technologies including screen readers (NVDA, VoiceOver, JAWS), browser zoom and magnifiers, and keyboard-only navigation. If you experience specific compatibility issues, please provide details and we will investigate.</p>

        <h2 id="reporting">Feedback, Reporting and Response</h2>
        <p>If you encounter accessibility barriers, please contact us with the following information so we can investigate and respond:</p>
        <ul>
          <li>Web page address (URL)</li>
          <li>Description of the issue encountered</li>
          <li>Assistive technology used (if any)</li>
          <li>Preferred method of contact and contact details</li>
        </ul>
        <p>Contact: <a href="mailto:support@izendestudioweb.com">support@izendestudioweb.com</a> | Phone: +1 314-312-6441</p>
        <p>We aim to respond to accessibility inquiries within 5 business days and to provide a resolution timeline or workaround when possible.</p>

        <h2 id="assessment-testing">Assessment and Testing</h2>
        <p>We perform a combination of automated scans and manual testing on primary user flows. Our most recent internal assessment was conducted on 2025-09-20. We plan periodic assessments at least annually; the next scheduled assessment is 2026-09-01.</p>

        <h2 id="formal-complaints">Formal Complaints</h2>
        <p>If you are unable to resolve an accessibility issue with us, you may contact the U.S. Department of Justice or your local civil rights agency. U.S. Department of Justice (DOJ) information on accessibility and the ADA is available at: <a href="https://www.ada.gov/">https://www.ada.gov/</a>.</p>

        <h2 id="third-party">Third‑Party Content and Services</h2>
        <p>Some content or services on this site are provided by third parties (for example, embedded maps, external forms, or videos). We do not control the accessibility of third-party resources but we will work with vendors to address issues and provide alternatives where feasible. If a third‑party vendor is responsible for a critical accessibility failure, we will assist affected users in obtaining the content in an alternative format.</p>

        <h2 id="updates">Updates to this Statement</h2>
        <p>We will update this accessibility statement as we make improvements. Any material changes will be reflected with a new "Last Updated" date at the top of this page.</p>
      </div>
    </section>

  </main>
  <?php include 'assets/includes/footer.php'; ?>
</body>
</html>
