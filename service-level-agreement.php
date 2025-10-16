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
  <title>Service Level Agreement (SLA) | Izende Studio Web</title>
  <?php include 'assets/includes/header-links.php'; ?>
</head>
<body>
  <?php include 'assets/includes/topbar.php'; ?>
  <?php include 'assets/includes/header.php'; ?>
  <main id="main">
    <section class="breadcrumbs">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center">
          <h2>Service Level Agreement</h2>
          <ol>
            <li><a href="/">Home</a></li>
            <li>Service Level Agreement</li>
          </ol>
        </div>
      </div>
    </section>

    <section class="legal-content section-bg">
      <div class="container">
        <div class="legal-last-updated">Last Updated: 2025-10-15 — Effective Date: 2025-10-15</div>

        <h2 id="sla-overview">Service Level Agreement (SLA)</h2>
        <p>This Service Level Agreement ("SLA") describes the availability, performance, and support commitments for Izende Studio Web hosting services and related managed services. This SLA is incorporated by reference into your service order or agreement.</p>

        <h2 id="uptime-commitment">1. Uptime Commitment</h2>
        <p>We commit to the following availability targets, measured monthly:</p>
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Plan</th>
                <th>Uptime Commitment (monthly)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Shared Hosting / Starter</td>
                <td>99.9%</td>
              </tr>
              <tr>
                <td>VPS / Business</td>
                <td>99.9%</td>
              </tr>
              <tr>
                <td>Dedicated / Enterprise</td>
                <td>99.95%</td>
              </tr>
            </tbody>
          </table>
        </div>

        <h3 id="uptime-calculation">Uptime Calculation &amp; Exclusions</h3>
        <p>Uptime is calculated as: (Total minutes in the month - Downtime minutes) / Total minutes in the month &times; 100. Downtime is measured from the time we confirm an incident affecting multiple customers or the specific service instance until service is restored.</p>
        <p>Downtime does not include outages or unavailability caused by:</p>
        <ul>
          <li>Scheduled maintenance (we will provide advance notice where practicable)</li>
          <li>Customer-caused issues, including misconfiguration or third-party integrations</li>
          <li>Force majeure events beyond our reasonable control (natural disasters, acts of government, cyberwarfare)</li>
          <li>Third-party services or networks beyond our control (upstream ISPs, public cloud provider incidents where we act as a reseller)</li>
          <li>Denial-of-service attacks where mitigation requires action by upstream providers</li>
        </ul>

        <h2 id="service-credits">2. Service Credit Table</h2>
        <p>If we fail to meet the uptime commitment for an affected service, you may be eligible for service credits calculated as a percentage of the monthly service fee for the impacted service during the measurement period. Service credits are the sole and exclusive remedy for SLA failures and are subject to the claim process below.</p>
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Monthly Uptime</th>
                <th>Service Credit</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>&ge; SLA (no credit)</td>
                <td>0%</td>
              </tr>
              <tr>
                <td>&lt; SLA and &ge; 99.0%</td>
                <td>5% credit</td>
              </tr>
              <tr>
                <td>&lt; 99.0% and &ge; 95.0%</td>
                <td>10% credit</td>
              </tr>
              <tr>
                <td>&lt; 95.0%</td>
                <td>25% credit (maximum one month's fee)</td>
              </tr>
            </tbody>
          </table>
        </div>

        <h2 id="maintenance">3. Scheduled Maintenance</h2>
        <p>We will provide reasonable notice for scheduled maintenance windows where possible. Maintenance windows may be performed during off-peak hours; we will use commercially reasonable efforts to notify affected customers at least 48 hours in advance for non-emergency maintenance.</p>

        <h2 id="support-response-times">4. Support Response Times</h2>
        <p>Response targets depend on incident priority and your plan:</p>
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Priority</th>
                <th>Impact</th>
                <th>Shared / VPS</th>
                <th>Dedicated / Enterprise</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Critical (P1)</td>
                <td>Complete outage or security incident</td>
                <td>Initial response within 1 hour; 24/7 handling</td>
                <td>Initial response within 30 minutes; 24/7 handling</td>
              </tr>
              <tr>
                <td>High (P2)</td>
                <td>Major functionality degraded</td>
                <td>Initial response within 4 hours</td>
                <td>Initial response within 2 hours</td>
              </tr>
              <tr>
                <td>Normal (P3)</td>
                <td>Partial impairment, non-critical</td>
                <td>Initial response within 1 business day</td>
                <td>Initial response within 4 business hours</td>
              </tr>
              <tr>
                <td>Low (P4)</td>
                <td>General questions or feature requests</td>
                <td>Response within 2 business days</td>
                <td>Response within 1 business day</td>
              </tr>
            </tbody>
          </table>
        </div>

        <h2 id="performance-security-backups">5. Performance, Security and Backups</h2>
        <p>We monitor service health, apply security patches, and maintain backups according to plan specifications. Backup retention and restore SLAs vary by plan; customers should review their order or contact support for specifics. We will use commercially reasonable measures to protect your data, including TLS, firewalls, and intrusion detection.</p>

        <h2 id="monitoring-reporting">6. Monitoring and Reporting</h2>
        <p>We continuously monitor platform health and maintain logs and dashboards for internal reporting. Customers may request incident reports for major incidents and root cause analyses following resolution.</p>

        <h2 id="claim-process">7. How to Claim Service Credits</h2>
        <ol>
          <li>Open a support ticket within 30 days of the incident detailing the affected service, dates/times, and impact.</li>
          <li>We will validate the claim against our monitoring and, if eligible, issue a credit to your account within the next billing cycle.</li>
          <li>Service credits are applied to future invoices and are non-transferable and non-refundable.</li>
        </ol>

        <h2 id="exclusions">8. Exclusions</h2>
        <p>The SLA does not apply to situations caused by:</p>
        <ul>
          <li>Customer configuration errors or misuse;</li>
          <li>Third-party software, plugins, or integrations not provided by us;</li>
          <li>Denial-of-service attacks beyond our mitigation capacity;</li>
          <li>Changes requested by the customer that cause service disruption.</li>
        </ul>

        <h2 id="change-management">9. Change Management and Versioning</h2>
        <p>Planned infrastructure changes and upgrades will be communicated in advance. For major platform changes we will provide migration guidance and timelines.</p>

        <h2 id="contact-escalation">10. Contact and Escalation</h2>
        <p>To report incidents, open a support ticket in your client area or email <a href="mailto:support@izendestudioweb.com">support@izendestudioweb.com</a>. For urgent incidents call +1 314-312-6441. For SLA disputes or escalation, include "SLA Escalation" in the subject line and provide incident references.</p>
      </div>
    </section>

  </main>
  <?php include 'assets/includes/footer.php'; ?>
</body>
</html>
