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

// Step 2: Load database configuration
@require_once __DIR__ . '/admin/config/database.php';

// Step 3: Load CMS data layer
@require_once __DIR__ . '/config/cms-data.php';

// Initialize empty content arrays (fallback if CMS fails)
$heroSlides = [];
$featuredServices = [];
$stats = [];
$featuredPortfolio = [];
$portfolioVideos = [];
$testimonials = [];

// Try to load CMS content if available
if (class_exists('CMSData')) {
    $heroSlides = @CMSData::getHeroSlides() ?: [];
    $featuredServices = @CMSData::getFeaturedServices(6) ?: [];
    $stats = @CMSData::getStats() ?: [];
    $featuredPortfolio = @CMSData::getFeaturedPortfolio(6) ?: [];
    $portfolioVideos = @CMSData::getVideos('portfolio', 6) ?: [];
    $testimonials = @CMSData::getTestimonials(6) ?: [];
}
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
      <?php if (!empty($featuredServices)): ?>
        <div class="service-cards-grid">
          <?php foreach ($featuredServices as $service): ?>
            <div class="service-card-new">
              <div class="service-icon-new">
                <i class="material-icons"><?php echo htmlspecialchars($service['icon'] ?? 'build'); ?></i>
              </div>
              <h3 class="service-title-new"><?php echo htmlspecialchars($service['name'] ?? 'Service'); ?></h3>
              <p class="service-desc-new"><?php echo htmlspecialchars($service['description'] ?? ''); ?></p>
              <?php if (!empty($service['price'])): ?>
                <p class="service-price-new">Starting at <span><?php echo htmlspecialchars($service['price']); ?></span></p>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p style="text-align: center; padding: 40px; color: #666;">Loading services...</p>
      <?php endif; ?>
    </div>
  </section>

  <!-- ======= Portfolio Section ======= -->
  <section id="portfolio" class="portfolio">
    <div class="container">
      <div class="section-title">
        <h2>Portfolio</h2>
        <p>Showcase of our recent projects and client work</p>
      </div>
      <?php if (!empty($featuredPortfolio)): ?>
        <div class="service-cards-grid">
          <?php foreach ($featuredPortfolio as $project): ?>
            <div class="service-card-new">
              <?php if (!empty($project['image_url'])): ?>
                <img src="<?php echo htmlspecialchars($project['image_url']); ?>" alt="<?php echo htmlspecialchars($project['title'] ?? 'Project'); ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 15px;">
              <?php endif; ?>
              <h3 class="service-title-new"><?php echo htmlspecialchars($project['title'] ?? 'Project'); ?></h3>
              <?php if (!empty($project['category'])): ?>
                <p style="color: #666; font-size: 14px; margin: 8px 0;"><?php echo htmlspecialchars($project['category']); ?></p>
              <?php endif; ?>
              <p class="service-desc-new"><?php echo htmlspecialchars($project['description'] ?? ''); ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p style="text-align: center; padding: 40px; color: #666;">Loading portfolio...</p>
      <?php endif; ?>
    </div>
  </section>

  <!-- ======= Stats Section ======= -->
  <section id="stats" class="stats">
    <div class="container">
      <div class="section-title">
        <h2>Our Impact</h2>
        <p>Results that speak for themselves</p>
      </div>
      <?php if (!empty($stats)): ?>
        <div class="service-cards-grid">
          <?php foreach ($stats as $stat): ?>
            <div class="service-card-new" style="text-align: center;">
              <div style="font-size: 36px; font-weight: bold; color: #34a853; margin: 20px 0;">
                <?php echo htmlspecialchars($stat['value'] ?? '0'); ?>
              </div>
              <h3 class="service-title-new"><?php echo htmlspecialchars($stat['label'] ?? 'Stat'); ?></h3>
              <?php if (!empty($stat['description'])): ?>
                <p class="service-desc-new"><?php echo htmlspecialchars($stat['description']); ?></p>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p style="text-align: center; padding: 40px; color: #666;">Loading stats...</p>
      <?php endif; ?>
    </div>
  </section>

  <!-- ======= Testimonials Section ======= -->
  <section id="testimonials" class="testimonials">
    <div class="container">
      <div class="section-title">
        <h2>Client Testimonials</h2>
        <p>What our clients say about us</p>
      </div>
      <?php if (!empty($testimonials)): ?>
        <div class="testimonials-container-new">
          <?php foreach ($testimonials as $testimonial): ?>
            <div class="testimonial-card-new">
              <?php if (!empty($testimonial['thumbnail_url'])): ?>
                <div style="margin-bottom: 15px;">
                  <img src="<?php echo htmlspecialchars($testimonial['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($testimonial['title'] ?? 'Testimonial'); ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
                </div>
              <?php endif; ?>
              <p class="testimonial-text-new"><?php echo htmlspecialchars($testimonial['description'] ?? ''); ?></p>
              <?php if (!empty($testimonial['title'])): ?>
                <p class="testimonial-author-new">— <?php echo htmlspecialchars($testimonial['title']); ?></p>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p style="text-align: center; padding: 40px; color: #666;">Loading testimonials...</p>
      <?php endif; ?>
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
