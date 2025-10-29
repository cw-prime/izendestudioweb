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
    CMSData::init();
    $heroSlides = @CMSData::getHeroSlides() ?: [];
    $featuredServices = @CMSData::getFeaturedServices(6) ?: [];
    $stats = @CMSData::getStats() ?: [];
    $featuredPortfolio = @CMSData::getFeaturedPortfolio(6) ?: [];
    $portfolioVideos = @CMSData::getVideos('portfolio', 6) ?: [];
    $testimonials = @CMSData::getTestimonials(6) ?: [];
}

// Fallback hero slides if CMS table is empty
if (empty($heroSlides)) {
    $fallbackImages = glob(__DIR__ . '/assets/img/slide/*.{jpg,jpeg,png,webp}', GLOB_BRACE);
    if (!empty($fallbackImages)) {
        $heroSlides = [];
        $fallbackTexts = [
            ['title' => 'Peace of Mind for Your Digital Presence', 'subtitle' => 'Calm, reliable web solutions for St. Louis businesses', 'description' => 'We blend design, hosting, and ongoing support to keep your online presence serene and effective.'],
            ['title' => 'Design in Harmony', 'subtitle' => 'Balanced visuals that resonate with your audience', 'description' => 'Custom sites built to reflect your brand, optimized for conversions and performance.'],
            ['title' => 'Hosting with Confidence', 'subtitle' => 'Secure, optimized, and monitored around the clock', 'description' => 'Rest easy knowing your site is fast, protected, and cared for by experts.']
        ];

        foreach ($fallbackImages as $index => $imagePath) {
            $normalizedPath = str_replace(__DIR__, '', $imagePath);
            $text = $fallbackTexts[$index % count($fallbackTexts)];
            $heroSlides[] = array_merge($text, [
                'background_image' => $normalizedPath,
                'button_text' => 'Start a Project',
                'button_url' => '/quote.php',
            ]);
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
  <?php if (!empty($heroSlides)): ?>
  <section id="hero" class="hero-modern">
    <div class="hero-carousel swiper" id="heroCarousel">
      <div class="swiper-wrapper">
        <?php foreach ($heroSlides as $index => $slide): ?>
        <div class="swiper-slide">
          <?php if (!empty($slide['background_image'])): ?>
            <img
              src="<?php echo htmlspecialchars($slide['background_image']); ?>"
              alt="<?php echo htmlspecialchars($slide['title'] ?: 'Hero slide'); ?>"
              class="hero-slide-img"
              loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>"
              width="1920"
              height="1080">
          <?php endif; ?>
          <div class="carousel-container">
            <div class="container slide-content text-center">
              <?php if (!empty($slide['title'])): ?>
                <<?php echo $index === 0 ? 'h1' : 'h2'; ?> class="animate__animated animate__fadeInDown">
                  <?php echo htmlspecialchars($slide['title']); ?>
                </<?php echo $index === 0 ? 'h1' : 'h2'; ?>>
              <?php endif; ?>

              <?php if (!empty($slide['subtitle'])): ?>
                <p class="animate__animated animate__fadeInUp">
                  <?php echo htmlspecialchars($slide['subtitle']); ?>
                </p>
              <?php endif; ?>

              <?php if (!empty($slide['description'])): ?>
                <p class="hero-description animate__animated animate__fadeInUp">
                  <?php echo htmlspecialchars($slide['description']); ?>
                </p>
              <?php endif; ?>

              <?php if (!empty($slide['button_text']) && !empty($slide['button_url'])): ?>
                <div class="cta-buttons-new animate__animated animate__fadeInUp">
                  <a href="<?php echo htmlspecialchars($slide['button_url']); ?>"
                     class="btn-modern">
                    <?php echo htmlspecialchars($slide['button_text']); ?>
                  </a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="swiper-button-prev" aria-label="Previous slide"></div>
      <div class="swiper-button-next" aria-label="Next slide"></div>
      <div class="swiper-pagination" aria-label="Hero slide pagination"></div>
    </div>
  </section>
  <?php else: ?>
  <section id="hero" class="hero-modern">
    <div class="container">
      <div class="hero-content fade-in-new text-center">
        <h1>Peace of Mind for Your Digital Presence</h1>
        <p>Harmonious web design and hosting that works seamlessly, so you can focus on what matters. Trusted by St. Louis, Missouri, and Illinois businesses.</p>
      </div>
    </div>
  </section>
  <?php endif; ?><!-- End Hero -->

  <!-- ======= Services Section ======= -->
  <section id="services" class="services">
    <div class="container">
      <div class="section-title">
        <h2>Our Approach</h2>
        <p>Everything in harmony. We craft digital solutions that work effortlessly, giving you peace of mind while you focus on your business.</p>
      </div>
      <?php if (!empty($featuredServices)): ?>
        <div class="service-cards-grid">
          <?php foreach ($featuredServices as $service): ?>
            <div class="service-card-new">
              <div class="service-icon-new">
                <i class="material-icons"><?php echo htmlspecialchars($service['icon_class'] ?? 'build'); ?></i>
              </div>
              <h3 class="service-title-new"><?php echo htmlspecialchars($service['title'] ?? 'Service'); ?></h3>
              <p class="service-desc-new"><?php echo htmlspecialchars($service['description'] ?? ''); ?></p>
              <?php if (!empty($service['link_url'])): ?>
                <a href="<?php echo htmlspecialchars($service['link_url']); ?>" class="service-link-new"><?php echo htmlspecialchars($service['link_text'] ?? 'Learn more'); ?></a>
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
        <h2>Our Work</h2>
        <p>Purposeful projects that balance beauty with function. Each solution crafted with intention.</p>
      </div>
      <?php if (!empty($featuredPortfolio)): ?>
        <div class="service-cards-grid">
          <?php foreach ($featuredPortfolio as $project): ?>
            <div class="service-card-new">
              <?php if (!empty($project['featured_image'])): ?>
                <img src="<?php echo htmlspecialchars($project['featured_image']); ?>" alt="<?php echo htmlspecialchars($project['title'] ?? 'Project'); ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 15px;">
              <?php elseif (!empty($project['thumbnail_image'])): ?>
                <img src="<?php echo htmlspecialchars($project['thumbnail_image']); ?>" alt="<?php echo htmlspecialchars($project['title'] ?? 'Project'); ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 15px;">
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
        <h2>Proven Trust</h2>
        <p>Measured success through lasting relationships and satisfied partners</p>
      </div>
      <?php if (!empty($stats)): ?>
        <div class="service-cards-grid">
          <?php foreach ($stats as $stat): ?>
            <div class="service-card-new" style="text-align: center;">
              <div style="font-size: 36px; font-weight: bold; color: #34a853; margin: 20px 0;">
                <?php echo htmlspecialchars($stat['stat_value'] ?? '0'); ?><?php if (!empty($stat['stat_suffix'])): ?><span><?php echo htmlspecialchars($stat['stat_suffix']); ?></span><?php endif; ?>
              </div>
              <h3 class="service-title-new"><?php echo htmlspecialchars($stat['stat_label'] ?? 'Stat'); ?></h3>
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
        <h2>Stories of Success</h2>
        <p>Hear how harmony and peace of mind transformed their digital journey</p>
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
              <div>
                <?php if (!empty($testimonial['title'])): ?>
                  <p class="testimonial-author-new">— <?php echo htmlspecialchars($testimonial['title']); ?></p>
                <?php endif; ?>
              </div>
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
      <div class="row gy-4">
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
            <div class="service-area-summary mt-4">
              <h4><i class="bi bi-geo-alt"></i> Greater St. Louis Service Area</h4>
              <p class="mb-0">
                We partner with businesses across the St. Louis metro region, within a 12-mile service radius covering Missouri and Illinois communities.
              </p>
            </div>
          </div>
        </div>
        <div class="col-lg-7">
          <div class="info map-info">
            <div class="map-card">
              <div class="map-card-header">
                <h4 class="mb-0"><i class="bi bi-map"></i> 12-Mile Service Radius</h4>
                <p class="small text-muted mb-0">Covering St. Louis metro area and nearby communities.</p>
              </div>
              <div id="service-area-map" class="service-area-map" role="img" aria-label="Map illustrating Izende Studio Web's service radius around the St. Louis metro area."></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  </main><!-- End #main -->

  <?php @include_once './assets/includes/footer.php'; ?>

  <!-- Analytics -->
  <?php @include_once './assets/includes/analytics.php'; ?>
  <!-- End Analytics -->

</body>

</html>
