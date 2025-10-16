<?php
/**
 * Web Hosting Services Landing Page
 * Comprehensive hosting page with Shared, VPS, and Dedicated hosting options
 */

// Security initialization
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

  <title>Web Hosting Services - Shared, VPS & Dedicated Hosting | Izende Studio Web</title>
  <meta name="description" content="Professional web hosting in St. Louis with 99.9% uptime guarantee. Shared, VPS, and Dedicated hosting plans starting at $4.99/month. 24/7 support and free SSL included.">
  <meta name="keywords" content="web hosting, shared hosting, vps hosting, dedicated server, st louis hosting, managed hosting">

  <?php include 'assets/includes/header-links.php'; ?>
</head>

<body>

  <?php include 'assets/includes/topbar.php'; ?>
  <?php include 'assets/includes/header.php'; ?>

  <main id="main">

    <!-- ======= Breadcrumbs ======= -->
    <section class="breadcrumbs">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center">
          <h2>Web Hosting Services</h2>
          <ol>
            <li><a href="index.php">Home</a></li>
            <li><a href="services.php">Services</a></li>
            <li>Web Hosting</li>
          </ol>
        </div>
      </div>
    </section><!-- End Breadcrumbs -->

    <!-- ======= Hero Section ======= -->
    <section class="service-hero" data-aos="fade-up">
      <div class="container">
        <div class="row">
          <div class="col-lg-8 mx-auto text-center">
            <h1>Professional Web Hosting Solutions</h1>
            <p>Fast, secure, and reliable hosting with 99.9% uptime guarantee. Powered by enterprise-grade infrastructure.</p>
            <div class="mt-4">
              <a href="#pricing" class="btn btn-brand me-3">View Hosting Plans</a>
              <a href="quote.php" class="btn btn-outline-light">Get Free Quote</a>
            </div>
          </div>
        </div>
      </div>
    </section><!-- End Hero Section -->

    <!-- ======= Trust Badges Section ======= -->
    <section class="trust-badges section-bg" style="padding: 60px 0;">
      <div class="container">
        <div class="row">
          <div class="col-lg-3 col-md-6 mb-4 mb-lg-0" data-aos="zoom-in">
            <div class="icon-box iconbox-brand text-center">
              <i class="bx bx-check-shield"></i>
              <h4>99.9% Uptime Guarantee</h4>
              <p>Enterprise-grade infrastructure ensures your website is always accessible</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 mb-4 mb-lg-0" data-aos="zoom-in" data-aos-delay="100">
            <div class="icon-box iconbox-brand text-center">
              <i class="bx bx-lock-alt"></i>
              <h4>Free SSL Certificate</h4>
              <p>Secure your website and boost SEO with complimentary SSL encryption</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 mb-4 mb-lg-0" data-aos="zoom-in" data-aos-delay="200">
            <div class="icon-box iconbox-brand text-center">
              <i class="bx bx-support"></i>
              <h4>24/7 Expert Support</h4>
              <p>Our technical team is available round the clock to assist you</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="300">
            <div class="icon-box iconbox-brand text-center">
              <i class="bx bx-money"></i>
              <h4>30-Day Money-Back</h4>
              <p>Try our hosting risk-free with our satisfaction guarantee</p>
            </div>
          </div>
        </div>
      </div>
    </section><!-- End Trust Badges Section -->

    <!-- ======= Hosting Types Overview Section ======= -->
    <section style="padding: 60px 0;">
      <div class="container">
        <div class="section-title" data-aos="fade-up">
          <h2>Choose Your Hosting Solution</h2>
          <p>From small websites to enterprise applications, we have the perfect hosting plan for you</p>
        </div>

        <div class="row">
          <div class="col-lg-4 col-md-6 mb-4" data-aos="zoom-in" data-aos-delay="100">
            <div class="icon-box iconbox-blue">
              <div class="icon">
                <svg width="100" height="100" viewBox="0 0 600 600" xmlns="http://www.w3.org/2000/svg">
                  <path stroke="none" stroke-width="0" fill="#f5f5f5" d="M300,521.0016835830174C376.1290562159157,517.8887921683347,466.0731472004068,529.7835943286574,510.70327084640275,468.03025145048787C554.3714126377745,407.6079735673963,508.03601936045806,328.9844924480964,491.2728898941984,256.3432110539036C474.5976632858925,184.082847569629,479.9380746630129,96.60480741107993,416.23090153303,58.64404602377083C348.86323505073057,18.502131276798302,261.93793281208167,40.57373210992963,193.5410806939664,78.93577620505333C130.42746243093433,114.334589627462,98.30271207620316,179.96522072025542,76.75703585869454,249.04625023123273C51.97151888228291,328.5150500222984,13.704378332031375,421.85034740162234,66.52175969318436,486.19268352777647C119.04800174914682,550.1803526380478,217.28368757567262,524.383925680826,300,521.0016835830174"></path>
                </svg>
                <i class="bx bx-server"></i>
              </div>
              <h4>Shared Hosting</h4>
              <p>Perfect for personal websites, blogs, and small businesses. Easy to manage with cPanel control panel.</p>
              <p><strong>Best for:</strong></p>
              <ul>
                <li>Small business websites</li>
                <li>Personal blogs & portfolios</li>
                <li>Low to moderate traffic sites</li>
                <li>Budget-conscious startups</li>
              </ul>
              <a href="#pricing" class="learn-more">Learn More <i class="bx bx-chevron-right"></i></a>
            </div>
          </div>

          <div class="col-lg-4 col-md-6 mb-4" data-aos="zoom-in" data-aos-delay="200">
            <div class="icon-box iconbox-orange">
              <div class="icon">
                <svg width="100" height="100" viewBox="0 0 600 600" xmlns="http://www.w3.org/2000/svg">
                  <path stroke="none" stroke-width="0" fill="#f5f5f5" d="M300,532.3542879108572C369.38199826031484,532.3153073249985,429.10787420159085,491.63046689027357,474.5244479745417,439.17860296908856C522.8885846962883,383.3225815378663,545.1847828153581,305.66141126234886,548.6152519714028,221.20258689820073C551.9619716108029,139.34970728023082,528.3147379950141,55.56068034614516,477.39463779535886,8.673791636661084C427.19584236127287,-37.49420656636673,362.3964107886422,-61.05145396010125,294.9606032118167,-61.05145396010125C230.03197031893218,-61.05145396010125,165.4791323257695,-42.4682188034134,120.49452362880509,3.1982979845184757C78.13976147863097,46.29306237626263,40.95847159872537,113.87948447037037,32.98641556888213,182.6541444772088C24.924394742847267,252.42168791845254,57.98995904968874,325.8456082110679,107.31489500140164,378.7098255048421C159.75106630744575,434.54773881677544,228.84843023841746,532.3542879108572,300,532.3542879108572"></path>
                </svg>
                <i class="bx bxs-server"></i>
              </div>
              <h4>VPS Hosting</h4>
              <p>Scalable virtual private server with dedicated resources. Perfect for growing businesses and high-traffic sites.</p>
              <p><strong>Best for:</strong></p>
              <ul>
                <li>Growing e-commerce sites</li>
                <li>Multiple websites</li>
                <li>Resource-intensive applications</li>
                <li>Developers needing root access</li>
              </ul>
              <a href="#pricing" class="learn-more">Learn More <i class="bx bx-chevron-right"></i></a>
            </div>
          </div>

          <div class="col-lg-4 col-md-6 mb-4" data-aos="zoom-in" data-aos-delay="300">
            <div class="icon-box iconbox-red">
              <div class="icon">
                <svg width="100" height="100" viewBox="0 0 600 600" xmlns="http://www.w3.org/2000/svg">
                  <path stroke="none" stroke-width="0" fill="#f5f5f5" d="M300,566.797414625762C385.7384707136149,576.1784315230908,478.7894351017131,552.8928747891023,531.9192734346935,484.94944893311C584.6109503024035,417.5663521118492,582.489472248146,322.67544863468447,553.9536738515405,242.03673114598146C529.1557734026468,171.96086150256528,465.24506316201064,127.66468636344209,395.9583748389544,100.7403814666027C334.2173773831606,76.7482773500951,269.4350130405921,84.62216499799875,207.1952322260088,107.2889140133804C132.92018162631612,134.33871894543012,41.79353780512637,160.00259165414826,22.644507872594943,236.69541883565114C3.319112789854554,314.0945973066697,72.72355303640163,379.243833228382,124.04198916343866,440.3218312028393C172.9286146004772,498.5055451809895,224.45579914871206,558.5317968840102, 300,566.797414625762"></path>
                </svg>
                <i class="bx bx-data"></i>
              </div>
              <h4>Dedicated Server</h4>
              <p>Maximum power and control with your own dedicated server. Enterprise-grade performance for demanding applications.</p>
              <p><strong>Best for:</strong></p>
              <ul>
                <li>High-traffic e-commerce</li>
                <li>Enterprise applications</li>
                <li>Mission-critical websites</li>
                <li>Maximum security requirements</li>
              </ul>
              <a href="#pricing" class="learn-more">Learn More <i class="bx bx-chevron-right"></i></a>
            </div>
          </div>
        </div>
      </div>
    </section><!-- End Hosting Types Overview Section -->

    <!-- ======= Feature Comparison Matrix Section ======= -->
    <section class="feature-comparison section-bg" style="padding: 60px 0;">
      <div class="container">
        <div class="section-title" data-aos="fade-up">
          <h2>Compare Hosting Features</h2>
          <p>Find the perfect plan that meets your website's requirements</p>
        </div>

        <div class="table-responsive" data-aos="fade-up" data-aos-delay="100">
          <table class="table">
            <thead>
              <tr>
                <th>Feature</th>
                <th>Shared</th>
                <th class="recommended-col">VPS</th>
                <th>Dedicated</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="feature-name">Storage</td>
                <td>10GB SSD</td>
                <td class="recommended-col">50GB SSD</td>
                <td>500GB SSD</td>
              </tr>
              <tr>
                <td class="feature-name">Bandwidth</td>
                <td>Unlimited</td>
                <td class="recommended-col">Unlimited</td>
                <td>Unlimited</td>
              </tr>
              <tr>
                <td class="feature-name">Websites</td>
                <td>1</td>
                <td class="recommended-col">5</td>
                <td>Unlimited</td>
              </tr>
              <tr>
                <td class="feature-name">Email Accounts</td>
                <td>10</td>
                <td class="recommended-col">50</td>
                <td>Unlimited</td>
              </tr>
              <tr>
                <td class="feature-name">Free SSL</td>
                <td><i class="bx bx-check check-icon"></i></td>
                <td class="recommended-col"><i class="bx bx-check check-icon"></i></td>
                <td><i class="bx bx-check check-icon"></i></td>
              </tr>
              <tr>
                <td class="feature-name">Free Domain (1 year)</td>
                <td><i class="bx bx-check check-icon"></i></td>
                <td class="recommended-col"><i class="bx bx-check check-icon"></i></td>
                <td><i class="bx bx-check check-icon"></i></td>
              </tr>
              <tr>
                <td class="feature-name">cPanel Access</td>
                <td><i class="bx bx-check check-icon"></i></td>
                <td class="recommended-col"><i class="bx bx-check check-icon"></i></td>
                <td><i class="bx bx-check check-icon"></i></td>
              </tr>
              <tr>
                <td class="feature-name">MySQL Databases</td>
                <td>10</td>
                <td class="recommended-col">Unlimited</td>
                <td>Unlimited</td>
              </tr>
              <tr>
                <td class="feature-name">FTP Accounts</td>
                <td>5</td>
                <td class="recommended-col">Unlimited</td>
                <td>Unlimited</td>
              </tr>
              <tr>
                <td class="feature-name">Backup Frequency</td>
                <td>Weekly</td>
                <td class="recommended-col">Daily</td>
                <td>Daily</td>
              </tr>
              <tr>
                <td class="feature-name">CPU Cores</td>
                <td>Shared</td>
                <td class="recommended-col">2-4</td>
                <td>8-16</td>
              </tr>
              <tr>
                <td class="feature-name">RAM</td>
                <td>Shared</td>
                <td class="recommended-col">2-8GB</td>
                <td>16-64GB</td>
              </tr>
              <tr>
                <td class="feature-name">Root Access</td>
                <td><i class="bx bx-x x-icon"></i></td>
                <td class="recommended-col"><i class="bx bx-check check-icon"></i></td>
                <td><i class="bx bx-check check-icon"></i></td>
              </tr>
              <tr>
                <td class="feature-name">Dedicated IP</td>
                <td><i class="bx bx-x x-icon"></i></td>
                <td class="recommended-col"><i class="bx bx-check check-icon"></i></td>
                <td><i class="bx bx-check check-icon"></i></td>
              </tr>
              <tr>
                <td class="feature-name">Priority Support</td>
                <td><i class="bx bx-x x-icon"></i></td>
                <td class="recommended-col"><i class="bx bx-check check-icon"></i></td>
                <td><i class="bx bx-check check-icon"></i></td>
              </tr>
              <tr>
                <td class="feature-name">DDoS Protection</td>
                <td><i class="bx bx-check check-icon"></i></td>
                <td class="recommended-col"><i class="bx bx-check check-icon"></i></td>
                <td><i class="bx bx-check check-icon"></i></td>
              </tr>
              <tr>
                <td class="feature-name">Malware Scanning</td>
                <td><i class="bx bx-check check-icon"></i></td>
                <td class="recommended-col"><i class="bx bx-check check-icon"></i></td>
                <td><i class="bx bx-check check-icon"></i></td>
              </tr>
              <tr>
                <td class="feature-name">Uptime Guarantee</td>
                <td>99.9%</td>
                <td class="recommended-col">99.9%</td>
                <td>99.99%</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section><!-- End Feature Comparison Matrix Section -->

    <!-- ======= Pricing Tables Section ======= -->
    <section id="pricing" style="padding: 60px 0;">
      <div class="container">
        <div class="section-title" data-aos="fade-up">
          <h2>Hosting Plans & Pricing</h2>
          <p>Transparent pricing with no hidden fees. All plans include free SSL and 24/7 support.</p>
        </div>

        <div class="row">
          <div class="col-lg-4 col-md-6 mb-4" data-aos="zoom-in" data-aos-delay="100">
            <div class="pricing-table">
              <h3>Shared Hosting</h3>
              <div class="price">$4.99<span>/month</span></div>
              <ul>
                <li><i class="bx bx-check"></i> 10GB SSD Storage</li>
                <li><i class="bx bx-check"></i> Unlimited Bandwidth</li>
                <li><i class="bx bx-check"></i> 1 Website</li>
                <li><i class="bx bx-check"></i> 10 Email Accounts</li>
                <li><i class="bx bx-check"></i> Free SSL Certificate</li>
                <li><i class="bx bx-check"></i> Free Domain (1 year)</li>
                <li><i class="bx bx-check"></i> cPanel Control Panel</li>
                <li><i class="bx bx-check"></i> 99.9% Uptime Guarantee</li>
                <li><i class="bx bx-check"></i> 24/7 Support</li>
              </ul>
              <a href="/adminIzende/index.php?rp=/store/shared-hosting" class="btn btn-brand">Get Started</a>
            </div>
          </div>

          <div class="col-lg-4 col-md-6 mb-4" data-aos="zoom-in" data-aos-delay="200">
            <div class="pricing-table featured">
              <span class="badge-popular">Most Popular</span>
              <h3>VPS Hosting</h3>
              <div class="price">$29.99<span>/month</span></div>
              <ul>
                <li><i class="bx bx-check"></i> 50GB SSD Storage</li>
                <li><i class="bx bx-check"></i> Unlimited Bandwidth</li>
                <li><i class="bx bx-check"></i> 5 Websites</li>
                <li><i class="bx bx-check"></i> 50 Email Accounts</li>
                <li><i class="bx bx-check"></i> Free SSL Certificate</li>
                <li><i class="bx bx-check"></i> Free Domain (1 year)</li>
                <li><i class="bx bx-check"></i> cPanel/WHM Access</li>
                <li><i class="bx bx-check"></i> 2-4 CPU Cores</li>
                <li><i class="bx bx-check"></i> 2-8GB RAM</li>
                <li><i class="bx bx-check"></i> Root Access</li>
                <li><i class="bx bx-check"></i> Dedicated IP</li>
                <li><i class="bx bx-check"></i> Daily Backups</li>
                <li><i class="bx bx-check"></i> Priority Support</li>
              </ul>
              <a href="/adminIzende/index.php?rp=/store/vps-hosting" class="btn btn-brand">Get Started</a>
            </div>
          </div>

          <div class="col-lg-4 col-md-6 mb-4" data-aos="zoom-in" data-aos-delay="300">
            <div class="pricing-table">
              <h3>Dedicated Server</h3>
              <div class="price">$99.99<span>/month</span></div>
              <ul>
                <li><i class="bx bx-check"></i> 500GB SSD Storage</li>
                <li><i class="bx bx-check"></i> Unlimited Bandwidth</li>
                <li><i class="bx bx-check"></i> Unlimited Websites</li>
                <li><i class="bx bx-check"></i> Unlimited Email Accounts</li>
                <li><i class="bx bx-check"></i> Free SSL Certificate</li>
                <li><i class="bx bx-check"></i> Free Domain (1 year)</li>
                <li><i class="bx bx-check"></i> Full Root Access</li>
                <li><i class="bx bx-check"></i> 8-16 CPU Cores</li>
                <li><i class="bx bx-check"></i> 16-64GB RAM</li>
                <li><i class="bx bx-check"></i> Multiple Dedicated IPs</li>
                <li><i class="bx bx-check"></i> Daily Backups</li>
                <li><i class="bx bx-check"></i> Managed Services Available</li>
                <li><i class="bx bx-check"></i> Priority Support</li>
                <li><i class="bx bx-check"></i> 99.99% Uptime SLA</li>
              </ul>
              <a href="/adminIzende/index.php?rp=/store/dedicated-servers" class="btn btn-brand">Get Started</a>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12 text-center mt-4">
            <p class="text-muted"><em>All prices shown are for annual billing. Monthly billing available at higher rates.</em></p>
          </div>
        </div>
      </div>
    </section><!-- End Pricing Tables Section -->

    <!-- ======= Server Specifications Section ======= -->
    <section class="server-specs section-bg" style="padding: 60px 0;">
      <div class="container">
        <div class="section-title" data-aos="fade-up">
          <h2>Enterprise-Grade Infrastructure</h2>
          <p>Powered by cutting-edge technology for maximum performance and reliability</p>
        </div>

        <div class="row">
          <div class="col-lg-6 mb-4" data-aos="fade-right">
            <h3>World-Class Data Centers</h3>
            <p>Our hosting infrastructure is built on enterprise-grade hardware housed in state-of-the-art data centers. We ensure your website stays fast, secure, and online 24/7/365.</p>
            <ul class="list-unstyled">
              <li><i class="bx bx-check-circle" style="color: #5cb874;"></i> <strong>Tier III+ Data Centers</strong> - Redundant power and cooling</li>
              <li><i class="bx bx-check-circle" style="color: #5cb874;"></i> <strong>100% Network Uptime</strong> - Multiple carrier connections</li>
              <li><i class="bx bx-check-circle" style="color: #5cb874;"></i> <strong>DDoS Protection</strong> - Enterprise-grade security</li>
              <li><i class="bx bx-check-circle" style="color: #5cb874;"></i> <strong>Automated Backups</strong> - Daily backups with easy restore</li>
              <li><i class="bx bx-check-circle" style="color: #5cb874;"></i> <strong>Cloudflare CDN</strong> - Global content delivery</li>
            </ul>
          </div>

          <div class="col-lg-6" data-aos="fade-left">
            <div class="row">
              <div class="col-md-6 mb-4">
                <div class="icon-box iconbox-brand">
                  <i class="bx bx-hdd"></i>
                  <h4>SSD Storage</h4>
                  <p>Lightning-fast NVMe SSD drives for optimal performance and quick data access</p>
                </div>
              </div>
              <div class="col-md-6 mb-4">
                <div class="icon-box iconbox-brand">
                  <i class="bx bx-rocket"></i>
                  <h4>LiteSpeed Web Server</h4>
                  <p>Up to 40x faster than Apache with built-in caching and optimization</p>
                </div>
              </div>
              <div class="col-md-6 mb-4">
                <div class="icon-box iconbox-brand">
                  <i class="bx bx-code-alt"></i>
                  <h4>PHP 8.x</h4>
                  <p>Latest PHP versions with OPcache for maximum speed and security</p>
                </div>
              </div>
              <div class="col-md-6 mb-4">
                <div class="icon-box iconbox-brand">
                  <i class="bx bx-data"></i>
                  <h4>MySQL 8.0</h4>
                  <p>Optimized database performance with advanced security features</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section><!-- End Server Specifications Section -->

    <!-- ======= Testimonials Section ======= -->
    <section style="padding: 60px 0;">
      <div class="container">
        <div class="section-title" data-aos="fade-up">
          <h2>What Our Hosting Clients Say</h2>
          <p>Don't just take our word for it - hear from satisfied customers</p>
        </div>

        <div class="row">
          <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
            <div class="pricing-table" style="text-align: left;">
              <div style="margin-bottom: 20px;">
                <i class="bx bxs-quote-alt-left" style="font-size: 30px; color: #5cb874;"></i>
              </div>
              <p style="font-style: italic; margin-bottom: 20px;">We've been hosting with Izende Studio Web for 3 years. Zero downtime, lightning-fast speeds, and their support team is always there when we need them.</p>
              <div style="border-top: 2px solid #5cb874; padding-top: 15px;">
                <h4 style="margin-bottom: 5px;">David Martinez</h4>
                <p style="color: #777; margin: 0;">Owner, Martinez Consulting</p>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
            <div class="pricing-table" style="text-align: left;">
              <div style="margin-bottom: 20px;">
                <i class="bx bxs-quote-alt-left" style="font-size: 30px; color: #5cb874;"></i>
              </div>
              <p style="font-style: italic; margin-bottom: 20px;">Migrating our WordPress sites to their VPS hosting was seamless. Page load times improved by 60% and our SEO rankings went up!</p>
              <div style="border-top: 2px solid #5cb874; padding-top: 15px;">
                <h4 style="margin-bottom: 5px;">Jennifer Lee</h4>
                <p style="color: #777; margin: 0;">Marketing Director, TechFlow Solutions</p>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
            <div class="pricing-table" style="text-align: left;">
              <div style="margin-bottom: 20px;">
                <i class="bx bxs-quote-alt-left" style="font-size: 30px; color: #5cb874;"></i>
              </div>
              <p style="font-style: italic; margin-bottom: 20px;">Best hosting decision we ever made. The dedicated server handles our high-traffic e-commerce site flawlessly. Worth every penny.</p>
              <div style="border-top: 2px solid #5cb874; padding-top: 15px;">
                <h4 style="margin-bottom: 5px;">Robert Thompson</h4>
                <p style="color: #777; margin: 0;">CEO, ShopLocal STL</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section><!-- End Testimonials Section -->

    <!-- ======= FAQ Accordion Section ======= -->
    <section class="hosting-faq section-bg" style="padding: 60px 0;">
      <div class="container">
        <div class="section-title" data-aos="fade-up">
          <h2>Frequently Asked Questions</h2>
          <p>Find answers to common questions about our hosting services</p>
        </div>

        <div class="row">
          <div class="col-lg-10 mx-auto" data-aos="fade-up" data-aos-delay="100">
            <div class="accordion" id="hostingFAQ">

              <div class="accordion-item">
                <h2 class="accordion-header" id="faq1">
                  <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                    What is web hosting and why do I need it?
                  </button>
                </h2>
                <div id="collapse1" class="accordion-collapse collapse show" aria-labelledby="faq1" data-bs-parent="#hostingFAQ">
                  <div class="accordion-body">
                    Web hosting is a service that stores your website's files on a server connected to the internet, making your site accessible to visitors worldwide. Without hosting, your website wouldn't be visible online. Think of it like renting space for your business - instead of a physical storefront, you're renting digital space on a server. Our hosting plans include everything you need: storage space, bandwidth, email accounts, security features, and technical support.
                  </div>
                </div>
              </div>

              <div class="accordion-item">
                <h2 class="accordion-header" id="faq2">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                    What's the difference between Shared, VPS, and Dedicated hosting?
                  </button>
                </h2>
                <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="faq2" data-bs-parent="#hostingFAQ">
                  <div class="accordion-body">
                    <strong>Shared Hosting:</strong> Multiple websites share resources on one server. It's cost-effective and perfect for small websites with moderate traffic. Think of it like living in an apartment building.<br><br>
                    <strong>VPS Hosting:</strong> You get dedicated resources on a virtual private server. Better performance, more control, and ideal for growing businesses. Like owning a condo - you have your own space but share the building.<br><br>
                    <strong>Dedicated Server:</strong> An entire physical server just for your website(s). Maximum power, control, and security for high-traffic sites and applications. Like owning your own house - complete control and privacy.
                  </div>
                </div>
              </div>

              <div class="accordion-item">
                <h2 class="accordion-header" id="faq3">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                    Do you offer a money-back guarantee?
                  </button>
                </h2>
                <div id="collapse3" class="accordion-collapse collapse" aria-labelledby="faq3" data-bs-parent="#hostingFAQ">
                  <div class="accordion-body">
                    Yes! We offer a 30-day money-back guarantee on all hosting plans. If you're not completely satisfied with our service within the first 30 days, we'll refund your hosting fee - no questions asked. This gives you the opportunity to try our hosting risk-free and ensure it meets your needs.
                  </div>
                </div>
              </div>

              <div class="accordion-item">
                <h2 class="accordion-header" id="faq4">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                    Is an SSL certificate included?
                  </button>
                </h2>
                <div id="collapse4" class="accordion-collapse collapse" aria-labelledby="faq4" data-bs-parent="#hostingFAQ">
                  <div class="accordion-body">
                    Absolutely! A free SSL certificate is included with all hosting plans at no additional cost. SSL (Secure Sockets Layer) encrypts data between your website and visitors, protecting sensitive information and building trust. It's also essential for SEO - Google prioritizes secure websites in search rankings. We'll automatically install and configure your SSL certificate when you set up your hosting.
                  </div>
                </div>
              </div>

              <div class="accordion-item">
                <h2 class="accordion-header" id="faq5">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5" aria-expanded="false" aria-controls="collapse5">
                    Can I upgrade my hosting plan later?
                  </button>
                </h2>
                <div id="collapse5" class="accordion-collapse collapse" aria-labelledby="faq5" data-bs-parent="#hostingFAQ">
                  <div class="accordion-body">
                    Yes, you can upgrade your hosting plan at any time as your website grows. We make the transition seamless - whether you're moving from Shared to VPS hosting or from VPS to a Dedicated Server. We'll handle the migration process and ensure there's no downtime. You'll only pay the prorated difference for the remainder of your billing cycle when upgrading.
                  </div>
                </div>
              </div>

              <div class="accordion-item">
                <h2 class="accordion-header" id="faq6">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse6" aria-expanded="false" aria-controls="collapse6">
                    Do you provide website migration services?
                  </button>
                </h2>
                <div id="collapse6" class="accordion-collapse collapse" aria-labelledby="faq6" data-bs-parent="#hostingFAQ">
                  <div class="accordion-body">
                    Yes! We offer free migration assistance for VPS and Dedicated Server plans. Our technical team will help transfer your existing website from your current host to our servers with minimal downtime. For Shared Hosting plans, we provide detailed migration guides and support to help you move your site. If you need full-service migration for a Shared Hosting account, we can arrange that for a small fee.
                  </div>
                </div>
              </div>

              <div class="accordion-item">
                <h2 class="accordion-header" id="faq7">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse7" aria-expanded="false" aria-controls="collapse7">
                    What control panel do you use?
                  </button>
                </h2>
                <div id="collapse7" class="accordion-collapse collapse" aria-labelledby="faq7" data-bs-parent="#hostingFAQ">
                  <div class="accordion-body">
                    We use cPanel, the industry-standard control panel that makes managing your hosting easy. cPanel provides an intuitive interface for managing your files, databases, email accounts, domains, and more - no technical expertise required. Shared and VPS hosting plans include cPanel access. Dedicated Server plans include both cPanel and WHM (Web Host Manager) for advanced server management. If you're familiar with cPanel from another host, you'll feel right at home.
                  </div>
                </div>
              </div>

              <div class="accordion-item">
                <h2 class="accordion-header" id="faq8">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse8" aria-expanded="false" aria-controls="collapse8">
                    Where are your servers located?
                  </button>
                </h2>
                <div id="collapse8" class="accordion-collapse collapse" aria-labelledby="faq8" data-bs-parent="#hostingFAQ">
                  <div class="accordion-body">
                    Our primary data centers are located in the United States, ensuring fast performance for North American visitors. We use Tier III+ certified data centers with redundant power, cooling, and network connections to guarantee 99.9% uptime. For clients with global audiences or specific geographic requirements, we can arrange hosting in data centers across Europe, Asia, and other regions. Contact us to discuss international hosting options.
                  </div>
                </div>
              </div>

              <div class="accordion-item">
                <h2 class="accordion-header" id="faq9">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse9" aria-expanded="false" aria-controls="collapse9">
                    What kind of support do you offer?
                  </button>
                </h2>
                <div id="collapse9" class="accordion-collapse collapse" aria-labelledby="faq9" data-bs-parent="#hostingFAQ">
                  <div class="accordion-body">
                    We provide 24/7 technical support via multiple channels: email, phone, and live chat. Our US-based support team consists of experienced technicians who can help with everything from basic questions to complex technical issues. VPS and Dedicated Server customers receive priority support with faster response times. We also maintain an extensive knowledge base with tutorials, guides, and troubleshooting articles for self-service support whenever you need it.
                  </div>
                </div>
              </div>

              <div class="accordion-item">
                <h2 class="accordion-header" id="faq10">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse10" aria-expanded="false" aria-controls="collapse10">
                    Do you offer domain registration?
                  </button>
                </h2>
                <div id="collapse10" class="accordion-collapse collapse" aria-labelledby="faq10" data-bs-parent="#hostingFAQ">
                  <div class="accordion-body">
                    Yes! We offer domain registration services for all popular extensions (.com, .net, .org, and many more). All annual hosting plans include a free domain name for the first year - a $15 value. If you already own a domain, you can easily point it to our servers or transfer it to our management. We'll handle all the technical DNS configuration to ensure your domain works properly with your hosting account. <a href="/adminIzende/cart.php?a=add&domain=register">Register your domain today</a>.
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </section><!-- End FAQ Accordion Section -->

    <!-- ======= Security Features Section ======= -->
    <section class="security-features" style="padding: 60px 0;">
      <div class="container">
        <div class="section-title" data-aos="fade-up">
          <h2>Bank-Level Security</h2>
          <p>Your website's security is our top priority</p>
        </div>

        <div class="row">
          <div class="col-lg-3 col-md-6 mb-4" data-aos="zoom-in" data-aos-delay="100">
            <div class="icon-box iconbox-brand text-center">
              <i class="bx bx-shield-alt-2"></i>
              <h4>DDoS Protection</h4>
              <p>Advanced protection against distributed denial-of-service attacks keeps your site online</p>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 mb-4" data-aos="zoom-in" data-aos-delay="200">
            <div class="icon-box iconbox-brand text-center">
              <i class="bx bx-search-alt"></i>
              <h4>Malware Scanning</h4>
              <p>Daily automated malware scans detect and alert you to potential security threats</p>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 mb-4" data-aos="zoom-in" data-aos-delay="300">
            <div class="icon-box iconbox-brand text-center">
              <i class="bx bx-lock-open-alt"></i>
              <h4>Web Application Firewall</h4>
              <p>WAF filters malicious traffic and protects against common web vulnerabilities</p>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 mb-4" data-aos="zoom-in" data-aos-delay="400">
            <div class="icon-box iconbox-brand text-center">
              <i class="bx bx-cloud-upload"></i>
              <h4>Automated Backups</h4>
              <p>Daily backups with 30-day retention ensure you can always restore your site</p>
            </div>
          </div>
        </div>
      </div>
    </section><!-- End Security Features Section -->

    <!-- ======= Final CTA Section ======= -->
    <section class="cta">
      <div class="container" data-aos="zoom-in">
        <div class="row">
          <div class="col-lg-9 text-center text-lg-start">
            <h3>Ready to Get Started?</h3>
            <p>Choose your hosting plan and get your website online in minutes. 30-day money-back guarantee.</p>
          </div>
          <div class="col-lg-3 cta-btn-container text-center">
            <a class="cta-btn align-middle" href="#pricing">View Hosting Plans</a>
          </div>
        </div>
      </div>
    </section><!-- End Final CTA Section -->

  </main><!-- End #main -->

  <?php include 'assets/includes/footer.php'; ?>

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/waypoints/noframework.waypoints.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>
