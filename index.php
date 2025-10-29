<?php
/**
 * Izende Studio Web - Homepage
 * Gradually adding functionality back
 */

// Step 1: Error handling and security
error_reporting(0);
ini_set('display_errors', 0);

// Try to load basic config files
@require_once __DIR__ . '/config/env-loader.php';
@require_once __DIR__ . '/config/security.php';

// Initialize security if available
if (function_exists('initSecureSession')) {
    @initSecureSession();
}
if (function_exists('setSecurityHeaders')) {
    @setSecurityHeaders();
}

// Initialize empty content arrays
$heroSlides = [];
$featuredServices = [];
$stats = [];
$featuredPortfolio = [];
$portfolioVideos = [];
$testimonials = [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <meta name="google-site-verification" content="Wg2VOCCDPOm1l4Cof11F3kBTUqOSDR6yir-YKnoeHsM" />
  <title>St. Louis Web Design & Hosting | Izende Studio Web | Missouri & Illinois</title>
  <meta name="description" content="Professional web design, hosting, and digital marketing services in St. Louis, Missouri.">

  <?php @include_once './assets/includes/header-links.php'; ?>

</head>

<body id="home">

  <!-- Skip Links for Accessibility -->
  <a href="#main" class="skip-link">Skip to main content</a>

  <!-- ======= Header ======= -->
  <?php @include_once './assets/includes/header.php'; ?>
  <!-- End Header -->

  <main id="main" role="main">

  <!-- ======= Hero Section ======= -->
  <section id="hero" class="hero-modern">
    <div class="container">
      <div class="hero-content fade-in-new">
        <h1>Professional Web Design & Hosting Solutions</h1>
        <p>Fast, secure, and reliable services for your St. Louis business. Serving Missouri and Illinois.</p>
      </div>
    </div>
  </section><!-- End Hero -->

  <!-- ======= Services Section ======= -->
  <section id="services" class="services">
    <div class="container">
      <div class="section-title">
        <h2>Services</h2>
        <p>Serving St. Louis, Missouri, and Illinois businesses with professional web design, hosting, and digital marketing services.</p>
      </div>
      <p style="text-align: center; padding: 40px; color: #666;">Services content loading...</p>
    </div>
  </section>

  <!-- ======= Contact Section ======= -->
  <section id="contact" class="contact">
    <div class="container">
      <div class="section-title">
        <h2>Contact</h2>
        <p>Get in touch with us</p>
      </div>
      <div class="row">
        <div class="col-lg-5">
          <div class="info">
            <div class="email">
              <i class="bi bi-envelope"></i>
              <h4>Email:</h4>
              <p><a href="mailto:support@izendestudioweb.com">support@izendestudioweb.com</a></p>
            </div>
            <div class="phone">
              <i class="bi bi-phone"></i>
              <h4>Call:</h4>
              <p><a href="tel:314-312-6441">+1 314.312.6441</a></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  </main><!-- End #main -->

  <?php @include_once './assets/includes/footer.php'; ?>

</body>

</html>
