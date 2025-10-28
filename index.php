<?php
/**
 * Izende Studio Web - Homepage
 * Minimal version - just load the HTML
 */

// Suppress all errors
error_reporting(0);
ini_set('display_errors', 0);

// Try to set basic security headers if possible
if (!headers_sent()) {
    @header('X-Frame-Options: SAMEORIGIN');
    @header('X-Content-Type-Options: nosniff');
    @header('X-XSS-Protection: 1; mode=block');
}

// Initialize empty arrays for content
$heroSlides = [];
$featuredServices = [];
$stats = [];
$featuredPortfolio = [];
$portfolioVideos = [];
$testimonials = [];

// Try to load CMS data - but don't fail if it errors
$cmsLoaded = false;
if (function_exists('get_included_files') && file_exists(__DIR__ . '/config/cms-data.php')) {
    if (@include_once __DIR__ . '/config/cms-data.php') {
        $cmsLoaded = class_exists('CMSData');
        if ($cmsLoaded) {
            $heroSlides = @CMSData::getHeroSlides() ?: [];
            $featuredServices = @CMSData::getFeaturedServices(6) ?: [];
            $stats = @CMSData::getStats() ?: [];
            $featuredPortfolio = @CMSData::getFeaturedPortfolio(6) ?: [];
            $portfolioVideos = @CMSData::getVideos('portfolio', 6) ?: [];
        }
    }
}

// Try to load testimonials
$testimonials = [];
if (file_exists(__DIR__ . '/admin/config/database.php')) {
    @include_once __DIR__ . '/admin/config/database.php';
    if (isset($conn) && $conn && function_exists('mysqli_query')) {
        $result = @mysqli_query($conn, "SELECT * FROM iz_testimonials WHERE is_active = 1 ORDER BY is_featured DESC LIMIT 6");
        if ($result) {
            while ($row = @mysqli_fetch_assoc($result)) {
                $testimonials[] = $row;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <meta name="google-site-verification" content="Wg2VOCCDPOm1l4Cof11F3kBTUqOSDR6yir-YKnoeHsM" />

  <title>St. Louis Web Design & Hosting | Izende Studio Web | Missouri & Illinois</title>
  <meta name="description" content="Professional web design, hosting, and digital marketing services in St. Louis, Missouri. Serving businesses throughout Missouri and Illinois with custom websites, SEO, and web development. 15+ years experience.">

  <?php include_once './assets/includes/header-links.php'; ?>

</head>

<body id="home">

  <!-- Skip Links for Accessibility -->
  <a href="#main" class="skip-link">Skip to main content</a>

  <!-- ======= Header ======= -->
  <?php include_once './assets/includes/header.php'; ?>
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

  <?php include_once './assets/includes/footer.php'; ?>

</body>

</html>
