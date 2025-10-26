<?php
/**
 * Izende Studio Web - Homepage
 * Secured with session management and security headers
 */

// Load security infrastructure
require_once __DIR__ . '/config/env-loader.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/cms-data.php';
require_once __DIR__ . '/includes/SEOHelper.php';
require_once __DIR__ . '/includes/BannerHelper.php';

// Initialize secure session and set security headers
initSecureSession();
setSecurityHeaders();

// Load CMS content
$heroSlides = CMSData::getHeroSlides();
$featuredServices = CMSData::getFeaturedServices(6);
$stats = CMSData::getStats();
$featuredPortfolio = CMSData::getFeaturedPortfolio(6);
$portfolioVideos = CMSData::getVideos('portfolio', 6);

// Get featured testimonials
require_once __DIR__ . '/admin/config/database.php';
global $conn;
$testimonialsResult = mysqli_query($conn, "SELECT * FROM iz_testimonials WHERE is_active = 1 ORDER BY is_featured DESC, display_order ASC LIMIT 6");
$testimonials = [];
while ($row = mysqli_fetch_assoc($testimonialsResult)) {
    $testimonials[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <meta name="google-site-verification" content="Wg2VOCCDPOm1l4Cof11F3kBTUqOSDR6yir-YKnoeHsM" />

<?php
// Output SEO meta tags from SEO Manager (with fallback defaults)
SEOHelper::outputMetaTags('homepage', [
    'page_title' => 'St. Louis Web Design & Hosting | Izende Studio Web | Missouri & Illinois',
    'meta_description' => 'Professional web design, hosting, and digital marketing services in St. Louis, Missouri. Serving businesses throughout Missouri and Illinois with custom websites, SEO, and web development. 15+ years experience.',
    'meta_keywords' => 'st louis web design, missouri web hosting, illinois seo, st louis web developer, missouri website design, web design st louis, hosting missouri',
    'og_image' => 'https://izendestudioweb.com/assets/img/izende-T.png',
    'canonical_url' => 'https://izendestudioweb.com'
]);
?>

  <!-- LocalBusiness Schema Markup -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "ProfessionalService",
    "@id": "https://izendestudioweb.com/#organization",
    "name": "Izende Studio Web",
    "url": "https://izendestudioweb.com",
    "logo": "https://izendestudioweb.com/assets/img/izende-T.png",
    "image": "https://izendestudioweb.com/assets/img/izende-T.png",
    "description": "Professional web design, hosting, and digital marketing services in St. Louis, Missouri. Serving businesses throughout Missouri and Illinois.",
    "telephone": "+1-314-312-6441",
    "email": "support@izendestudioweb.com",
    "geo": {
      "@type": "GeoCoordinates",
      "latitude": 38.64357,
      "longitude": -90.24117
    },
    "areaServed": [
      {
        "@type": "State",
        "name": "Missouri",
        "@id": "https://en.wikipedia.org/wiki/Missouri"
      },
      {
        "@type": "State",
        "name": "Illinois",
        "@id": "https://en.wikipedia.org/wiki/Illinois"
      },
      {
        "@type": "City",
        "name": "St. Louis",
        "@id": "https://en.wikipedia.org/wiki/St._Louis"
      },
      {
        "@type": "City",
        "name": "Clayton"
      },
      {
        "@type": "City",
        "name": "Chesterfield"
      },
      {
        "@type": "City",
        "name": "Belleville"
      },
      {
        "@type": "City",
        "name": "O'Fallon"
      }
    ],
    "openingHoursSpecification": [
      {
        "@type": "OpeningHoursSpecification",
        "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
        "opens": "09:00:00",
        "closes": "17:00:00"
      }
    ],
    "priceRange": "$",
    "sameAs": [
      "https://www.facebook.com/Izende-Studio-Web-109880234906868",
      "https://twitter.com/IzendeWeb",
      "https://www.linkedin.com/company/izende-studio-web"
    ],
    "founder": {
      "@type": "Person",
      "name": "Mark",
    "jobTitle": "Founder &amp; Lead Developer"
    },
    "foundingDate": "2013",
    "slogan": "Professional Web Solutions for St. Louis Businesses"
  }
  </script>

  <?php include './assets/includes/header-links.php'; ?>
<?php /* Set Google Consent Mode defaults before loading any Google libraries. This ensures ad/analytics storage are denied until user grants consent. */ ?>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  // Set conservative defaults — deny non-essential storage until user consents
  try {
    gtag('consent', 'default', {
      'ad_storage': 'denied',
      'ad_user_data': 'denied',
      'ad_personalization': 'denied',
      'analytics_storage': 'denied'
    });
  } catch (e) { /* no-op if gtag not yet available */ }
</script>

<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-XXXXXXX');</script>
<!-- End Google Tag Manager -->

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-JJ5VJ6SS5X"></script>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-0574640776335150"
     crossorigin="anonymous"></script>
<script>
  // Keep shim and push JS initialization; config should only activate cookies when analytics consent is granted.
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  // DO NOT call gtag('config',...) here — config should be triggered after consent is applied by CookieConsent.applyConsent
</script>
</head>

<body id="home"
      data-track-forms="<?php echo (CMSData::getSetting('track_form_submissions') ?? '1') == '1' ? 'true' : 'false'; ?>"
      data-track-videos="<?php echo (CMSData::getSetting('track_video_plays') ?? '1') == '1' ? 'true' : 'false'; ?>"
      data-track-external="<?php echo (CMSData::getSetting('track_external_links') ?? '1') == '1' ? 'true' : 'false'; ?>"
      data-track-phone="<?php echo (CMSData::getSetting('track_phone_clicks') ?? '1') == '1' ? 'true' : 'false'; ?>">

  <?php
  // Display promotional banners at top
  BannerHelper::displayBanners('top');
  ?>

  <!-- Skip Links for Accessibility (provided by header include) -->

  <!-- Google Tag Manager (noscript) -->
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-XXXXXXX"
  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->

  <!-- ======= Top Bar ======= -->
  <?php include './assets/includes/topbar.php'; ?>
  <!-- ======= Header ======= -->
  <?php include './assets/includes/header.php'; ?>
  <!-- End Header -->

  <main id="main" role="main">

  <!-- ======= Hero Section ======= -->
  <section id="hero">
    <div class="hero-carousel swiper" id="heroCarousel">
      <div class="swiper-wrapper">

        <?php foreach ($heroSlides as $index => $slide): ?>
        <!-- Slide <?php echo $index + 1; ?> -->
        <div class="swiper-slide">
          <?php if ($slide['background_image']): ?>
            <img src="<?php echo htmlspecialchars($slide['background_image']); ?>"
                 alt="<?php echo htmlspecialchars($slide['title']); ?>"
                 class="hero-slide-img"
                 loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>"
                 width="1920" height="1080">
          <?php endif; ?>
          <div class="carousel-container">
            <div class="container">
              <?php if ($slide['title']): ?>
                <<?php echo $index === 0 ? 'h1' : 'h2'; ?> class="animate__animated animate__fadeInDown">
                  <?php echo htmlspecialchars($slide['title']); ?>
                </<?php echo $index === 0 ? 'h1' : 'h2'; ?>>
              <?php endif; ?>

              <?php if ($slide['subtitle']): ?>
                <p class="animate__animated animate__fadeInUp">
                  <span style="text-transform:capitalize">
                    <?php echo htmlspecialchars($slide['subtitle']); ?>
                  </span>
                </p>
              <?php endif; ?>

              <?php if ($slide['button_text'] && $slide['button_url']): ?>
                <a href="<?php echo htmlspecialchars($slide['button_url']); ?>"
                   class="btn-get-started animate__animated animate__fadeInUp scrollto">
                  <?php echo htmlspecialchars($slide['button_text']); ?>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>

      </div>

      <!-- Navigation arrows -->
      <div class="swiper-button-prev"></div>
      <div class="swiper-button-next"></div>

      <!-- Pagination -->
      <div class="swiper-pagination"></div>
    </div>
  </section><!-- End Hero -->

    <!-- ======= Featured Services Section ======= -->

    <hr class="section-separator" aria-hidden="true">

    <!-- ======= Services Section ======= -->
    <section id="services" class="services">
      <div class="container">

        <div class="section-title">
          <h2>Services</h2>
          <p>Serving St. Louis, Missouri, and Illinois businesses with professional web design, hosting, and digital marketing services. Our products help clients promote themselves online, get noticed on search engines, and generate leads.</p>
        </div>

        <div class="row">
          <?php
          $iconboxColors = ['blue', 'orange', 'pink', 'red', 'brand', 'yellow', 'teal', 'purple'];
          foreach ($featuredServices as $index => $service):
            $colorClass = 'iconbox-' . $iconboxColors[$index % count($iconboxColors)];
            $delay = ($index + 1) * 100;
            $mtClass = $index >= 4 ? 'mt-4' : ($index >= 1 ? 'mt-4 mt-lg-0' : '');
            if ($index == 2) $mtClass = 'mt-4 mt-md-0';
          ?>
          <div class="col-lg-4 col-md-6 d-flex align-items-stretch <?php echo $mtClass; ?>" data-aos="zoom-in" data-aos-delay="<?php echo $delay; ?>">
            <div class="icon-box <?php echo $colorClass; ?>">
              <div class="icon">
                <svg width="100" height="100" viewBox="0 0 600 600" xmlns="http://www.w3.org/2000/svg">
                  <path stroke="none" stroke-width="0" fill="#f5f5f5" d="M300,521.0016835830174C376.1290562159157,517.8887921683347,466.0731472004068,529.7835943286574,510.70327084640275,468.03025145048787C554.3714126377745,407.6079735673963,508.03601936045806,328.9844924480964,491.2728898941984,256.3432110539036C474.5976632858925,184.082847569629,479.9380746630129,96.60480741107993,416.23090153303,58.64404602377083C348.86323505073057,18.502131276798302,261.93793281208167,40.57373210992963,193.5410806939664,78.93577620505333C130.42746243093433,114.334589627462,98.30271207620316,179.96522072025542,76.75703585869454,249.04625023123273C51.97151888228291,328.5150500222984,13.704378332031375,421.85034740162234,66.52175969318436,486.19268352777647C119.04800174914682,550.1803526380478,217.28368757567262,524.383925680826,300,521.0016835830174"></path>
                </svg>
                <i class="<?php echo htmlspecialchars($service['icon_class']); ?>"></i>
              </div>
              <h4><a href="<?php echo htmlspecialchars($service['link_url']); ?>"><?php echo htmlspecialchars($service['title']); ?></a></h4>
              <p><?php echo htmlspecialchars($service['description']); ?></p>
              <?php if ($service['link_text']): ?>
                <p class="mt-2"><a href="<?php echo htmlspecialchars($service['link_url']); ?>" class="btn btn-link" style="padding:0;"><?php echo htmlspecialchars($service['link_text']); ?></a></p>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="row" style="display: none;">
          <div class="col-lg-4 col-md-6 d-flex align-items-stretch" data-aos="zoom-in" data-aos-delay="100">
            <div class="icon-box iconbox-blue">
              <div class="icon">
                <svg width="100" height="100" viewBox="0 0 600 600" xmlns="http://www.w3.org/2000/svg">
                  <path stroke="none" stroke-width="0" fill="#f5f5f5" d="M300,521.0016835830174C376.1290562159157,517.8887921683347,466.0731472004068,529.7835943286574,510.70327084640275,468.03025145048787C554.3714126377745,407.6079735673963,508.03601936045806,328.9844924480964,491.2728898941984,256.3432110539036C474.5976632858925,184.082847569629,479.9380746630129,96.60480741107993,416.23090153303,58.64404602377083C348.86323505073057,18.502131276798302,261.93793281208167,40.57373210992963,193.5410806939664,78.93577620505333C130.42746243093433,114.334589627462,98.30271207620316,179.96522072025542,76.75703585869454,249.04625023123273C51.97151888228291,328.5150500222984,13.704378332031375,421.85034740162234,66.52175969318436,486.19268352777647C119.04800174914682,550.1803526380478,217.28368757567262,524.383925680826,300,521.0016835830174"></path>
                </svg>
                <i class="bx bxl-wordpress"></i>
              </div>
              <h4><a href="https://izendestudioweb.com/adminIzende/index.php/store/wordpress-starters">WordPress</a></h4>
              <p>WordPress is one of the most popular content management systems in use today. Affordable website packages for small business and professionals starting at $499.</p>
              <p class="mt-2"><a href="st-louis-web-design.php" class="btn btn-link" style="padding:0;" aria-label="St. Louis Web Design — learn about our local St. Louis web design services">St. Louis Web Design</a></p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 d-flex align-items-stretch mt-4 mt-lg-0" data-aos="zoom-in" data-aos-delay="300">
            <div class="icon-box iconbox-pink">
              <div class="icon">
                <svg width="100" height="100" viewBox="0 0 600 600" xmlns="http://www.w3.org/2000/svg">
                  <path stroke="none" stroke-width="0" fill="#f5f5f5" d="M300,541.5067337569781C382.14930387511276,545.0595476570109,479.8736841581634,548.3450877840088,526.4010558755058,480.5488172755941C571.5218469581645,414.80211281144784,517.5187510058486,332.0715597781072,496.52539010469104,255.14436215662573C477.37192572678356,184.95920475031193,473.57363656557914,105.61284051026155,413.0603344069578,65.22779650032875C343.27470386102294,18.654635553484475,251.2091493199835,5.337323636656869,175.0934190732945,40.62881213300186C97.87086631185822,76.43348514350839,51.98124368387456,156.15599469081315,36.44837278890362,239.84606092416172C21.716077023791087,319.22268207091537,43.775223500013084,401.1760424656574,96.891909868211,461.97329694683043C147.22146801428983,519.5804099606455,223.5754009179313,538.201503339737,300,541.5067337569781"></path>
                </svg>
                <i class="bx bxl-nodejs"></i>
              </div>
              <h4><a href="./quote.php">Custom Web App Development</a></h4>
              <p>Professional, creative and responsive, our Izende Studio Web Design services are just what your site needs to succeed.</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 d-flex align-items-stretch mt-4 mt-md-0" data-aos="zoom-in" data-aos-delay="200">
            <div class="icon-box iconbox-orange ">
              <div class="icon">
                <svg width="100" height="100" viewBox="0 0 600 600" xmlns="http://www.w3.org/2000/svg">
                  <path stroke="none" stroke-width="0" fill="#f5f5f5" d="M300,582.0697525312426C382.5290701553225,586.8405444964366,449.9789794690241,525.3245884688669,502.5850820975895,461.55621195738473C556.606425686781,396.0723002908107,615.8543463187945,314.28637112970534,586.6730223649479,234.56875336149918C558.9533121215079,158.8439757836574,454.9685369536778,164.00468322053177,381.49747125262974,130.76875717737553C312.15926192815925,99.40240125094834,248.97055460311594,18.661163978235184,179.8680185752513,50.54337015887873C110.5421016452524,82.52863877960104,119.82277516462835,180.83849132639028,109.12597500060166,256.43424936330496C100.08760227029461,320.3096726198365,92.17705696193138,384.0621239912766,124.79988738764834,439.7174275375508C164.83382741302287,508.01625554203684,220.96474134820875,577.5009287672846,300,582.0697525312426"></path>
                </svg>
                <i class="bx bxs-map"></i>
              </div>
              <h4><a href="./quote.php">Local SEO</a></h4>
              <p>What good is having a nice site if customers can't find it? We have the tool to make your site SEO friendly &amp; Visible Throughout The Web.</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 d-flex align-items-stretch mt-4 mt-lg-0" data-aos="zoom-in" data-aos-delay="300">
            <div class="icon-box iconbox-red">
              <div class="icon">
                <svg width="100" height="100" viewBox="0 0 600 380" xmlns="http://www.w3.org/2000/svg">
                  <path stroke="none" stroke-width="0" fill="#f5f5f5" d="M853.333 832h-682.667c-47.128 0-85.333-38.205-85.333-85.333v0-170.667c0-47.128 38.205-85.333 85.333-85.333v0h682.667c47.128 0 85.333 38.205 85.333 85.333v0 170.667c0 47.128-38.205 85.333-85.333 85.333v0zM640 618.667h-85.333v85.333h85.333zM810.667 618.667h-85.333v85.333h85.333zM853.333 405.333h-682.667c-47.128 0-85.333-38.205-85.333-85.333v0-170.667c0-47.128 38.205-85.333 85.333-85.333v0h682.667c47.128 0 85.333 38.205 85.333 85.333v0 170.667c0 47.128-38.205 85.333-85.333 85.333v0zM640 192h-85.333v85.333h85.333zM810.667 192h-85.333v85.333h85.333z" ></path>
                </svg>
                <i class="bx bxs-server"></i>
              </div>
              <h4><a href="hosting.php">Web Hosting</a></h4>
              <p>Fast, secure, and reliable web hosting with 99.9% uptime guarantee. From shared hosting to dedicated servers, we have the perfect solution for your website. Starting at $4.99/month with free SSL and 24/7 support.</p>
              <p class="mt-2"><a href="missouri-web-hosting.php" class="btn btn-link" style="padding:0;" aria-label="Missouri Web Hosting — learn about web hosting in Missouri">Missouri Web Hosting</a></p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 d-flex align-items-stretch mt-4" data-aos="zoom-in" data-aos-delay="400">
            <div class="icon-box iconbox-brand">
              <div class="icon">
                <svg width="100" height="100" viewBox="0 0 600 600" xmlns="http://www.w3.org/2000/svg">
                  <path stroke="none" stroke-width="0" fill="#f5f5f5" d="M300,503.46388370962813C374.79870501325706,506.71871716319447,464.8034551963731,527.1746412648533,510.4981551193396,467.86667711651364C555.9287308511215,408.9015244558933,512.6030010748507,327.5744911775523,490.211057578863,256.5855673507754C471.097692560561,195.9906835881958,447.69079081568157,138.11976852964426,395.19560036434837,102.3242989838813C329.3053358748298,57.3949838291264,248.02791733380457,8.279543830951368,175.87071277845988,42.242879143198664C103.41431057327972,76.34704239035025,93.79494320519305,170.9812938413882,81.28167332365135,250.07896920659033C70.17666984294237,320.27484674793965,64.84698225790005,396.69656628748305,111.28512138212992,450.4950937839243C156.20124167950087,502.5303643271138,231.32542653798444,500.4755392045468,300,503.46388370962813"></path>
                </svg>
                <i class="bx bxs-movie-play"></i>
              </div>
              <h4><a href="services/video-editing.php">Video Editing Services</a></h4>
              <p>Professional video editing for social media, marketing, and promotional content. From short-form Reels to long-form YouTube videos, we bring your vision to life.</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 d-flex align-items-stretch mt-4" data-aos="zoom-in" data-aos-delay="100">
            <div class="icon-box iconbox-red">
              <div class="icon">
                <svg width="100" height="100" viewBox="0 0 600 600" xmlns="http://www.w3.org/2000/svg">
                  <path stroke="none" stroke-width="0" fill="#f5f5f5" d="M300,521.0016835830174C376.1290562159157,517.8887921683347,466.0731472004068,529.7835943286574,510.70327084640275,468.03025145048787C554.3714126377745,407.6079735673963,508.03601936045806,328.9844924480964,491.2728898941984,256.3432110539036C474.5976632858925,184.082847569629,479.9380746630129,96.60480741107993,416.23090153303,58.64404602377083C348.86323505073057,18.502131276798302,261.93793281208167,40.57373210992963,193.5410806939664,78.93577620505333C130.42746243093433,114.334589627462,98.30271207620316,179.96522072025542,76.75703585869454,249.04625023123273C51.97151888228291,328.5150500222984,13.704378332031375,421.85034740162234,66.52175969318436,486.19268352777647C119.04800174914682,550.1803526380478,217.28368757567262,524.383925680826,300,521.0016835830174"></path>
                </svg>
                <i class="bx bx-shield-alt-2"></i>
              </div>
              <h4><a href="services/security-maintenance.php">Website Security &amp; Maintenance</a></h4>
              <p>Protect your website with 24/7 monitoring, daily backups, malware scanning, and automatic updates. Keep your site secure and running smoothly with our comprehensive maintenance plans starting at $99/month.</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 d-flex align-items-stretch mt-4" data-aos="zoom-in" data-aos-delay="200">
            <div class="icon-box iconbox-blue">
              <div class="icon">
                <svg width="100" height="100" viewBox="0 0 600 600" xmlns="http://www.w3.org/2000/svg">
                  <path stroke="none" stroke-width="0" fill="#f5f5f5" d="M300,521.0016835830174C376.1290562159157,517.8887921683347,466.0731472004068,529.7835943286574,510.70327084640275,468.03025145048787C554.3714126377745,407.6079735673963,508.03601936045806,328.9844924480964,491.2728898941984,256.3432110539036C474.5976632858925,184.082847569629,479.9380746630129,96.60480741107993,416.23090153303,58.64404602377083C348.86323505073057,18.502131276798302,261.93793281208167,40.57373210992963,193.5410806939664,78.93577620505333C130.42746243093433,114.334589627462,98.30271207620316,179.96522072025542,76.75703585869454,249.04625023123273C51.97151888228291,328.5150500222984,13.704378332031375,421.85034740162234,66.52175969318436,486.19268352777647C119.04800174914682,550.1803526380478,217.28368757567262,524.383925680826,300,521.0016835830174"></path>
                </svg>
                <i class="bx bx-cart"></i>
              </div>
              <h4><a href="services/ecommerce.php">E-Commerce Solutions</a></h4>
              <p>Launch your online store with WooCommerce or Shopify. Complete e-commerce solutions with payment integration, inventory management, and secure checkout. Start selling online today.</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 d-flex align-items-stretch mt-4" data-aos="zoom-in" data-aos-delay="300">
            <div class="icon-box iconbox-pink">
              <div class="icon">
                <svg width="100" height="100" viewBox="0 0 600 600" xmlns="http://www.w3.org/2000/svg">
                  <path stroke="none" stroke-width="0" fill="#f5f5f5" d="M300,521.0016835830174C376.1290562159157,517.8887921683347,466.0731472004068,529.7835943286574,510.70327084640275,468.03025145048787C554.3714126377745,407.6079735673963,508.03601936045806,328.9844924480964,491.2728898941984,256.3432110539036C474.5976632858925,184.082847569629,479.9380746630129,96.60480741107993,416.23090153303,58.64404602377083C348.86323505073057,18.502131276798302,261.93793281208167,40.57373210992963,193.5410806939664,78.93577620505333C130.42746243093433,114.334589627462,98.30271207620316,179.96522072025542,76.75703585869454,249.04625023123273C51.97151888228291,328.5150500222984,13.704378332031375,421.85034740162234,66.52175969318436,486.19268352777647C119.04800174914682,550.1803526380478,217.28368757567262,524.383925680826,300,521.0016835830174"></path>
                </svg>
                <i class="bx bx-share-alt"></i>
              </div>
              <h4><a href="services/social-media.php">Social Media Management</a></h4>
              <p>Grow your brand with professional social media management. Content creation, daily posting, community engagement, and analytics for Facebook, Instagram, LinkedIn, and more. Plans starting at $499/month.</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 d-flex align-items-stretch mt-4" data-aos="zoom-in" data-aos-delay="400">
            <div class="icon-box iconbox-orange">
              <div class="icon">
                <svg width="100" height="100" viewBox="0 0 600 600" xmlns="http://www.w3.org/2000/svg">
                  <path stroke="none" stroke-width="0" fill="#f5f5f5" d="M300,521.0016835830174C376.1290562159157,517.8887921683347,466.0731472004068,529.7835943286574,510.70327084640275,468.03025145048787C554.3714126377745,407.6079735673963,508.03601936045806,328.9844924480964,491.2728898941984,256.3432110539036C474.5976632858925,184.082847569629,479.9380746630129,96.60480741107993,416.23090153303,58.64404602377083C348.86323505073057,18.502131276798302,261.93793281208167,40.57373210992963,193.5410806939664,78.93577620505333C130.42746243093433,114.334589627462,98.30271207620316,179.96522072025542,76.75703585869454,249.04625023123273C51.97151888228291,328.5150500222984,13.704378332031375,421.85034740162234,66.52175969318436,486.19268352777647C119.04800174914682,550.1803526380478,217.28368757567262,524.383925680826,300,521.0016835830174"></path>
                </svg>
                <i class="bx bx-envelope"></i>
              </div>
              <h4><a href="services/email-marketing.php">Email Marketing Automation</a></h4>
              <p>Turn subscribers into customers with strategic email marketing. Automated campaigns, beautiful templates, list building, and analytics. Get $42 ROI for every $1 spent with professional email marketing.</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 d-flex align-items-stretch mt-4" data-aos="zoom-in" data-aos-delay="500">
            <div class="icon-box iconbox-yellow">
              <div class="icon">
                <svg width="100" height="100" viewBox="0 0 600 600" xmlns="http://www.w3.org/2000/svg">
                  <path stroke="none" stroke-width="0" fill="#f5f5f5" d="M300,521.0016835830174C376.1290562159157,517.8887921683347,466.0731472004068,529.7835943286574,510.70327084640275,468.03025145048787C554.3714126377745,407.6079735673963,508.03601936045806,328.9844924480964,491.2728898941984,256.3432110539036C474.5976632858925,184.082847569629,479.9380746630129,96.60480741107993,416.23090153303,58.64404602377083C348.86323505073057,18.502131276798302,261.93793281208167,40.57373210992963,193.5410806939664,78.93577620505333C130.42746243093433,114.334589627462,98.30271207620316,179.96522072025542,76.75703585869454,249.04625023123273C51.97151888228291,328.5150500222984,13.704378332031375,421.85034740162234,66.52175969318436,486.19268352777647C119.04800174914682,550.1803526380478,217.28368757567262,524.383925680826,300,521.0016835830174"></path>
                </svg>
                <i class="bx bx-rocket"></i>
              </div>
              <h4><a href="services/speed-optimization.php">Website Speed Optimization</a></h4>
              <p>Make your website lightning fast. Improve Core Web Vitals, boost SEO rankings, and increase conversions. Professional speed optimization to reduce load times by up to 80%.</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 d-flex align-items-stretch mt-4" data-aos="zoom-in" data-aos-delay="600">
            <div class="icon-box iconbox-teal">
              <div class="icon">
                <svg width="100" height="100" viewBox="0 0 600 600" xmlns="http://www.w3.org/2000/svg">
                  <path stroke="none" stroke-width="0" fill="#f5f5f5" d="M300,521.0016835830174C376.1290562159157,517.8887921683347,466.0731472004068,529.7835943286574,510.70327084640275,468.03025145048787C554.3714126377745,407.6079735673963,508.03601936045806,328.9844924480964,491.2728898941984,256.3432110539036C474.5976632858925,184.082847569629,479.9380746630129,96.60480741107993,416.23090153303,58.64404602377083C348.86323505073057,18.502131276798302,261.93793281208167,40.57373210992963,193.5410806939664,78.93577620505333C130.42746243093433,114.334589627462,98.30271207620316,179.96522072025542,76.75703585869454,249.04625023123273C51.97151888228291,328.5150500222984,13.704378332031375,421.85034740162234,66.52175969318436,486.19268352777647C119.04800174914682,550.1803526380478,217.28368757567262,524.383925680826,300,521.0016835830174"></path>
                </svg>
                <i class="bx bx-globe"></i>
              </div>
              <h4><a href="services/domain-lookup.php">Domain Lookup</a></h4>
              <p>Search and register your perfect domain name. Secure your brand online with the ideal web address for your business or project.</p>
            </div>
          </div>
        </div><!-- End hidden hardcoded services -->

      </div>
    </section><!-- End Services Section -->

    <hr class="section-separator" aria-hidden="true">

    <!-- ======= Stats Section ======= -->
    <section id="stats" class="stats section-bg">
      <div class="container">
        <div class="row">
          <?php foreach ($stats as $index => $stat): ?>
          <div class="col-lg-3 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
            <div class="icon-box text-center w-100">
              <?php if ($stat['icon_class']): ?>
                <i class="<?php echo htmlspecialchars($stat['icon_class']); ?>" style="font-size: 48px; color: #5cb874;"></i>
              <?php endif; ?>
              <h3>
                <span class="counter" data-target="<?php echo htmlspecialchars($stat['stat_value']); ?>">0</span><?php echo htmlspecialchars($stat['stat_suffix'] ?? ''); ?>
              </h3>
              <p><strong><?php echo htmlspecialchars($stat['stat_label']); ?></strong></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section><!-- End Stats Section -->

    <hr class="section-separator" aria-hidden="true">

    <!-- ======= Book Consultation CTA ======= -->
    <section class="cta-section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 60px 0; color: white;">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-8" data-aos="fade-right">
            <h2 style="color: white; margin-bottom: 15px;">Ready to Transform Your Online Presence?</h2>
            <p style="font-size: 18px; margin-bottom: 0; opacity: 0.95;">Schedule a free 30-minute consultation to discuss your project. No obligation, just expert advice tailored to your business needs.</p>
          </div>
          <div class="col-lg-4 text-lg-end mt-4 mt-lg-0" data-aos="fade-left">
            <a href="book-consultation.php" class="btn btn-light btn-lg" style="padding: 15px 40px; font-size: 18px; font-weight: 600; border-radius: 50px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
              <i class="bi bi-calendar-check"></i> Book Free Consultation
            </a>
          </div>
        </div>
      </div>
    </section><!-- End CTA Section -->

    <hr class="section-separator" aria-hidden="true">

    <!-- ======= Trust Badges Section ======= -->
    <section id="trust-badges" class="trust-badges">
      <div class="container">
        <div class="section-title">
          <h2>Why Choose Izende Studio Web</h2>
          <p>Professional, reliable, and results-driven web solutions for St. Louis businesses</p>
        </div>
        <div class="row">
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="icon-box">
              <i class="bx bx-shield-alt-2"></i>
              <h4>SSL Security Included</h4>
              <p>Free SSL certificates with all hosting plans to keep your site secure and boost SEO</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
            <div class="icon-box">
              <i class="bx bx-bar-chart-alt-2"></i>
              <h4>99.9% Uptime SLA</h4>
              <p>Industry-leading uptime guarantee ensures your website is always accessible</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
            <div class="icon-box">
              <i class="bx bx-money"></i>
              <h4>Money-Back Guarantee</h4>
              <p>30-day satisfaction guarantee on all new projects and hosting services</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="icon-box">
              <i class="bx bx-support"></i>
              <h4>Local St. Louis Support</h4>
              <p>Real people, real help. Based in St. Louis, serving Missouri and Illinois</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
            <div class="icon-box">
              <i class="bx bx-rocket"></i>
              <h4>Fast Page Loads</h4>
              <p>Optimized servers and CDN integration for lightning-fast website performance</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
            <div class="icon-box">
              <i class="bx bx-cog"></i>
              <h4>Custom Solutions</h4>
              <p>Tailored web solutions built specifically for your business needs and goals</p>
            </div>
          </div>
        </div>
      </div>
    </section><!-- End Trust Badges Section -->

    <hr class="section-separator" aria-hidden="true">

    <!-- ======= Cta Section ======= -->
    <section id="cta" class="cta">
      <div class="container">

        <div class="row">
          <div class="col-lg-9 text-center text-lg-start">
            <h3>Take control</h3>
            <p> With your own domain and website, you can control what customers, friends and prospects see about your business online – whether that’s a website, blog, social page or storefront.</p>
          </div>
          <div class="col-lg-3 cta-btn-container text-center">
            <a class="cta-btn align-middle" href="#">Start a Project With Us</a>
          </div>
        </div>

      </div>
    </section><!-- End Cta Section -->

    <hr class="section-separator" aria-hidden="true">

    <!-- ======= Clients Section ======= -->
    <section id="clients" class="clients section-bg">
      <div class="container">
        <div class="section-title">
          <h2>Trusted By Businesses Across St. Louis</h2>
          <p>We're proud to work with amazing clients throughout Missouri and Illinois</p>
        </div>
        <div class="row">
          <div class="col-lg-12">
            <div class="clients-slider swiper">
              <div class="swiper-wrapper align-items-center">
                <div class="swiper-slide"><img src="assets/img/clients/client-1.png" class="img-fluid" alt="Technology Company Client" loading="lazy"></div>
                <div class="swiper-slide"><img src="assets/img/clients/client-2.png" class="img-fluid" alt="Retail Business Client" loading="lazy"></div>
                <div class="swiper-slide"><img src="assets/img/clients/client-3.png" class="img-fluid" alt="Professional Services Client" loading="lazy"></div>
                <div class="swiper-slide"><img src="assets/img/clients/client-4.png" class="img-fluid" alt="Restaurant Hospitality Client" loading="lazy"></div>
                <div class="swiper-slide"><img src="assets/img/clients/client-5.png" class="img-fluid" alt="Healthcare Business Client" loading="lazy"></div>
                <div class="swiper-slide"><img src="assets/img/clients/client-6.png" class="img-fluid" alt="Real Estate Company Client" loading="lazy"></div>
              </div>
              <div class="swiper-pagination"></div>
            </div>
          </div>
        </div>
      </div>
    </section><!-- End Clients Section -->

    <hr class="section-separator" aria-hidden="true">

    <!-- ======= Portfolio Section ======= -->
    <section id="portfolio" class="portfolio">
      <div class="container">

        <div class="section-title">
          <h2>Portfolio</h2>
          <p>Explore our recent web design, hosting, and video editing projects. Each case study showcases real results for St. Louis businesses - from performance improvements to increased conversions.</p>
        </div>

        <div class="row">
          <div class="col-lg-12 d-flex justify-content-center" data-aos="fade-up">
            <ul id="portfolio-flters" role="group" aria-label="Portfolio filters">
              <li data-filter="*" class="filter-active" role="button" aria-pressed="true" tabindex="0">All</li>
              <li data-filter=".filter-hosting" role="button" aria-pressed="false" tabindex="0">Web Hosting</li>
              <li data-filter=".filter-video" role="button" aria-pressed="false" tabindex="0">Video Editing</li>
              <li data-filter=".filter-web" role="button" aria-pressed="false" tabindex="0">Web Development</li>
              <li data-filter=".filter-wordpress" role="button" aria-pressed="false" tabindex="0">WordPress</li>
              <li data-filter=".filter-seo" role="button" aria-pressed="false" tabindex="0">SEO</li>
            </ul>
          </div>
        </div>

        <div class="row portfolio-container">

          <?php if (!empty($featuredPortfolio)): ?>
            <?php
            $delay = 100;
            foreach ($featuredPortfolio as $index => $item):
              // Create filter class from category
              $filterClass = 'filter-' . strtolower(str_replace([' ', '&'], ['-', ''], $item['category']));

              // Get image paths
              $imagePath = $item['thumbnail_image'] ?? $item['featured_image'] ?? 'assets/img/placeholder.jpg';

              // Check if image has webp version
              $pathInfo = pathinfo($imagePath);
              $webpPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';
            ?>
          <div class="col-lg-4 col-md-6 portfolio-item <?php echo htmlspecialchars($filterClass); ?>" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
            <div class="portfolio-wrap">
              <picture>
                <?php if (file_exists($webpPath)): ?>
                <source srcset="<?php echo htmlspecialchars($webpPath); ?>" type="image/webp">
                <?php endif; ?>
                <img src="<?php echo htmlspecialchars($imagePath); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($item['title']); ?>" loading="lazy" decoding="async" width="600" height="400">
              </picture>
              <span class="portfolio-category-badge"><?php echo htmlspecialchars($item['category']); ?></span>
              <div class="portfolio-info">
                <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                <p><?php echo htmlspecialchars($item['category']); ?></p>
                <?php if (!empty($item['tags'])): ?>
                <span class="portfolio-metric"><i class="bx bx-trending-up"></i> <?php echo htmlspecialchars($item['tags']); ?></span>
                <?php endif; ?>
                <div class="portfolio-links">
                  <a href="<?php echo htmlspecialchars($imagePath); ?>" data-gallery="portfolioGallery" class="portfolio-lightbox" title="<?php echo htmlspecialchars($item['title']); ?>"><i class="bx bx-plus"></i></a>
                  <a href="portfolio-details.php?project=<?php echo htmlspecialchars($item['slug']); ?>" title="View Case Study"><i class="bx bx-link"></i></a>
                </div>
              </div>
            </div>
          </div>
            <?php
              $delay = ($delay == 300) ? 100 : $delay + 100;
            endforeach; ?>
          <?php else: ?>
          <!-- No portfolio items found - show placeholder message -->
          <div class="col-12">
            <div class="alert alert-info text-center">
              <i class="bx bx-info-circle"></i> No portfolio items available yet. Add some from the admin panel!
            </div>
          </div>
          <?php endif; ?>

        </div>

      </div>
    </section><!-- End Portfolio Section -->

    <hr class="section-separator" aria-hidden="true">

    <!-- ======= Video Portfolio Section ======= -->
    <section id="video-portfolio" class="video-portfolio section-bg">
      <div class="container">
        <div class="section-title">
          <h2>Video Portfolio</h2>
          <p>Check out some of our recent video editing projects</p>
        </div>

        <div class="row">
          <?php if (!empty($portfolioVideos)): ?>
            <?php
            $videoDelay = 100;
            foreach ($portfolioVideos as $video):
              // Get thumbnail - use custom or YouTube thumbnail
              $thumbnail = $video['custom_thumbnail'] ?? $video['thumbnail_url'] ?? 'https://img.youtube.com/vi/' . $video['youtube_id'] . '/maxresdefault.jpg';
            ?>
          <div class="col-lg-4 col-md-6 video-item" data-aos="fade-up" data-aos-delay="<?php echo $videoDelay; ?>">
            <a href="<?php echo htmlspecialchars($video['youtube_url']); ?>" class="video-lightbox" data-glightbox="type: video">
              <img src="<?php echo htmlspecialchars($thumbnail); ?>" alt="<?php echo htmlspecialchars($video['title']); ?>" class="img-fluid" loading="lazy">
              <div class="video-overlay">
                <i class="bx bx-play-circle"></i>
              </div>
            </a>
            <div class="video-info">
              <h4><?php echo htmlspecialchars($video['title']); ?></h4>
              <p><?php echo htmlspecialchars($video['description'] ?? 'Video Portfolio'); ?></p>
            </div>
          </div>
            <?php
              $videoDelay = ($videoDelay == 300) ? 100 : $videoDelay + 100;
            endforeach; ?>
          <?php else: ?>
          <!-- No videos found - show placeholder message -->
          <div class="col-12">
            <div class="alert alert-info text-center">
              <i class="bx bx-info-circle"></i> No videos available yet. Add some from the admin panel!
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </section><!-- End Video Portfolio Section -->

    <hr class="section-separator" aria-hidden="true">

    <!-- ======= Testimonials Section ======= -->
    <section id="testimonials" class="testimonials">
      <div class="container">
        <div class="section-title">
          <h2>Client Testimonials</h2>
          <p>See what our clients say about working with us</p>
        </div>

        <?php if (!empty($testimonials)): ?>
        <div class="row">
          <?php
          $testDelay = 100;
          foreach ($testimonials as $testimonial):
          ?>
          <div class="col-lg-4 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="<?php echo $testDelay; ?>">
            <div class="testimonial-item w-100">
              <div class="stars mb-2">
                <?php for ($i = 0; $i < 5; $i++): ?>
                  <i class="bi bi-star<?php echo $i < $testimonial['rating'] ? '-fill' : ''; ?>" style="color: #ffc107;"></i>
                <?php endfor; ?>
              </div>
              <p class="testimonial-text">
                <i class="bx bxs-quote-alt-left quote-icon-left"></i>
                <?php echo htmlspecialchars($testimonial['testimonial_text']); ?>
                <i class="bx bxs-quote-alt-right quote-icon-right"></i>
              </p>
              <div class="testimonial-author mt-3">
                <?php if (!empty($testimonial['client_photo'])): ?>
                <img src="<?php echo htmlspecialchars($testimonial['client_photo']); ?>" class="testimonial-img" alt="<?php echo htmlspecialchars($testimonial['client_name']); ?>">
                <?php endif; ?>
                <h4><?php echo htmlspecialchars($testimonial['client_name']); ?></h4>
                <?php if (!empty($testimonial['client_position']) && !empty($testimonial['client_company'])): ?>
                <p class="mb-0"><?php echo htmlspecialchars($testimonial['client_position']); ?></p>
                <p class="text-muted"><?php echo htmlspecialchars($testimonial['client_company']); ?></p>
                <?php elseif (!empty($testimonial['client_company'])): ?>
                <p class="text-muted"><?php echo htmlspecialchars($testimonial['client_company']); ?></p>
                <?php endif; ?>
                <?php if (!empty($testimonial['project_type'])): ?>
                <span class="badge bg-primary mt-2"><?php echo htmlspecialchars($testimonial['project_type']); ?></span>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php
          $testDelay += 100;
          endforeach;
          ?>
        </div>
        <?php else: ?>
        <div class="alert alert-info text-center">
          <i class="bi bi-info-circle"></i> No testimonials available yet.
        </div>
        <?php endif; ?>
      </div>
    </section><!-- End Testimonials Section -->

    <hr class="section-separator" aria-hidden="true">

    <!-- ======= Featured Blog Section ======= -->
    <section id="featured-blog" class="featured-blog">
      <div class="container">
        <div class="section-title">
          <h2>Latest from Our Blog</h2>
          <p>Expert insights on web design, development, and digital marketing</p>
        </div>

        <div id="homepage-blog-posts" class="row">
          <!-- Loading skeletons -->
          <div class="col-lg-4 col-md-6 mb-4">
            <div class="blog-skeleton">
              <div class="blog-skeleton-image"></div>
              <div class="blog-skeleton-content">
                <div class="blog-skeleton-category"></div>
                <div class="blog-skeleton-title"></div>
                <div class="blog-skeleton-excerpt"></div>
                <div class="blog-skeleton-meta"></div>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 mb-4">
            <div class="blog-skeleton">
              <div class="blog-skeleton-image"></div>
              <div class="blog-skeleton-content">
                <div class="blog-skeleton-category"></div>
                <div class="blog-skeleton-title"></div>
                <div class="blog-skeleton-excerpt"></div>
                <div class="blog-skeleton-meta"></div>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 mb-4">
            <div class="blog-skeleton">
              <div class="blog-skeleton-image"></div>
              <div class="blog-skeleton-content">
                <div class="blog-skeleton-category"></div>
                <div class="blog-skeleton-title"></div>
                <div class="blog-skeleton-excerpt"></div>
                <div class="blog-skeleton-meta"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="text-center mt-4">
          <a href="blog.php" class="btn btn-brand">View All Articles</a>
        </div>
      </div>
    </section><!-- End Featured Blog Section -->

    <hr class="section-separator" aria-hidden="true">

    <!-- ======= Guarantees Section ======= -->
    <section id="guarantees" class="guarantees section-bg">
      <div class="container">
        <div class="section-title">
          <h2>Our Guarantees</h2>
          <p>Your success is our priority. That's why we back our services with solid guarantees</p>
        </div>
        <div class="row">
          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
            <div class="icon-box" style="padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); margin-bottom: 30px;">
              <i class="bx bx-money" style="font-size: 48px; color: #5cb874; margin-bottom: 15px;"></i>
              <h4>30-Day Money-Back Guarantee</h4>
              <p>Not satisfied with your new website or hosting service? We'll refund your investment within the first 30 days, no questions asked.</p>
            </div>
          </div>
          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
            <div class="icon-box" style="padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); margin-bottom: 30px;">
              <i class="bx bx-bar-chart-alt-2" style="font-size: 48px; color: #5cb874; margin-bottom: 15px;"></i>
              <h4>99.9% Uptime Guarantee</h4>
              <p>Your website will be online and accessible 99.9% of the time. If we don't meet this standard, we'll credit your account.</p>
            </div>
          </div>
          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="300">
            <div class="icon-box" style="padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); margin-bottom: 30px;">
              <i class="bx bx-time-five" style="font-size: 48px; color: #5cb874; margin-bottom: 15px;"></i>
              <h4>On-Time Project Delivery</h4>
              <p>We commit to delivering your website on the agreed timeline. If we're late, you'll receive a discount on your project fee.</p>
            </div>
          </div>
          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="400">
            <div class="icon-box" style="padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); margin-bottom: 30px;">
              <i class="bx bx-support" style="font-size: 48px; color: #5cb874; margin-bottom: 15px;"></i>
              <h4>Free Post-Launch Support</h4>
              <p>Every new website includes 30 days of free post-launch support to ensure everything runs smoothly and you're comfortable managing your site.</p>
            </div>
          </div>
        </div>
      </div>
</section><!-- End Guarantees Section -->
<hr class="section-separator" aria-hidden="true">

    <!-- ======= Team Section ======= -->

    <!-- ======= Contact Section ======= -->
    <section id="contact" class="contact">
      <div class="container" >

        <div class="section-title">
          <h2>Contact</h2>
          <p style="text-align: center; max-width: 100%; padding: 0 15px;">You can get in touch with us directly by e-mail, or by using this contact form. Leave me a message, and I will get back to you shortly. For a quick response you can start a chat with us on WhatsApp</p>
        </div>

        <div class="row">

          <div class="col-lg-5 d-flex align-items-stretch">
            <div class="info">
              <div class="email">
                <i class="bi bi-envelope"></i>
                <h4>Email:</h4>
                <p><a href="mailto:support@izendewebstudio.com">support@izendewebstudio.com</a></p>
              </div>
              <div class="phone">
                <i class="bi bi-phone"></i>
                <h4>Call:</h4>
                <p> <a href="tel:314-312-6441">+1 314.312.6441 </a></p>
              </div>
              <p style="margin-top: 20px; margin-bottom: 10px; font-weight: 600; color: #5cb874;"><i class="bx bx-map" style="margin-right: 5px;"></i> Serving Greater St. Louis Area (20-Mile Radius)</p>
              <div id="service-area-map" style="height: 450px; width: 120%; margin-left: -10%; border-radius: 10px; overflow: hidden; box-shadow: 0 0 20px rgba(0,0,0,0.1);"></div>

             </div>

          </div>

          <div class="col-lg-7 mt-5 mt-lg-0 d-flex align-items-stretch">
            <form action="forms/contact.php" method="post" role="form" class="php-email-form" id="contact-form">
              <!-- CSRF Token -->
              <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

              <div class="row">
                <div class="form-group col-md-6">
                  <div class="form-floating">
                    <input type="text" name="name" class="form-control" id="name" placeholder=" " required aria-describedby="name-error">
                    <label for="name">Your Name</label>
                  </div>
                  <div class="invalid-feedback" id="name-error" role="alert"></div>
                  <div class="valid-feedback">Looks good!</div>
                </div>
                <div class="form-group col-md-6 mt-3 mt-md-0">
                  <div class="form-floating">
                    <input type="email" class="form-control" name="email" id="email" placeholder=" " required aria-describedby="email-error">
                    <label for="email">Your Email</label>
                  </div>
                  <div class="invalid-feedback" id="email-error" role="alert"></div>
                  <div class="valid-feedback">Looks good!</div>
                </div>
              </div>

              <div class="form-group mt-3">
                <div class="form-floating">
                  <input type="text" class="form-control" name="subject" id="subject" placeholder=" " required aria-describedby="subject-error">
                  <label for="subject">Subject</label>
                </div>
                <div class="invalid-feedback" id="subject-error" role="alert"></div>
                <div class="valid-feedback">Looks good!</div>
              </div>

              <div class="form-group mt-3">
                <div class="form-floating">
                  <textarea class="form-control" name="message" id="message" placeholder=" " rows="10" style="height: 150px;" required aria-describedby="message-error"></textarea>
                  <label for="message">Message</label>
                </div>
                <div class="invalid-feedback" id="message-error" role="alert"></div>
                <div class="valid-feedback">Looks good!</div>
              </div>

              <!-- Consent checkboxes (required consent + optional marketing) -->
              <div class="form-group mb-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="consent" name="consent" required>
                  <label class="form-check-label" for="consent">I agree to the <a href="privacy-policy.php" target="_blank" rel="noopener">Privacy Policy</a>.</label>
                </div>
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" id="marketing_consent" name="marketing_consent" value="1">
                  <label class="form-check-label" for="marketing_consent">I agree to receive marketing emails.</label>
                </div>
              </div>

              <div class="my-3">
                <div class="loading" id="contact-loading" role="status" style="display: none;">
                  <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                  Sending message...
                </div>
                <div class="error-message" id="contact-error" role="alert" style="display: none;"></div>
                <div class="sent-message" id="contact-success" role="status" style="display: none;">Your message has been sent. Thank you!</div>
              </div>

              <div class="text-center">
                <button type="submit" class="btn-brand">
                  <span class="btn-text">Send Message</span>
                  <span class="btn-spinner" style="display: none;">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Sending...
                  </span>
                </button>
              </div>
            </form>
          </div>

        </div>

      </div>
    </section><!-- End Contact Section -->
<hr class="section-separator" aria-hidden="true">

    <!-- ======= Service Areas Section ======= -->
    <section id="service-areas" class="services section-bg">
      <div class="container">
        <div class="section-title">
          <h2>Service Areas</h2>
          <p>Proudly serving businesses throughout the St. Louis Metro Area, Missouri, and Illinois</p>
        </div>

        <div class="row">
          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
            <h3>Missouri</h3>
            <ul style="list-style: none; padding: 0; columns: 2; -webkit-columns: 2; -moz-columns: 2;">
              <li style="margin-bottom: 10px;"><i class="bx bx-check" style="color: #5cb874; margin-right: 8px;"></i> St. Louis City &amp; County</li>
              <li style="margin-bottom: 10px;"><i class="bx bx-check" style="color: #5cb874; margin-right: 8px;"></i> Clayton</li>
              <li style="margin-bottom: 10px;"><i class="bx bx-check" style="color: #5cb874; margin-right: 8px;"></i> Chesterfield</li>
              <li style="margin-bottom: 10px;"><i class="bx bx-check" style="color: #5cb874; margin-right: 8px;"></i> O'Fallon</li>
              <li style="margin-bottom: 10px;"><i class="bx bx-check" style="color: #5cb874; margin-right: 8px;"></i> St. Charles</li>
              <li style="margin-bottom: 10px;"><i class="bx bx-check" style="color: #5cb874; margin-right: 8px;"></i> Florissant</li>
              <li style="margin-bottom: 10px;"><i class="bx bx-check" style="color: #5cb874; margin-right: 8px;"></i> University City</li>
              <li style="margin-bottom: 10px;"><i class="bx bx-check" style="color: #5cb874; margin-right: 8px;"></i> Webster Groves</li>
              <li style="margin-bottom: 10px;"><i class="bx bx-check" style="color: #5cb874; margin-right: 8px;"></i> Kirkwood</li>
              <li style="margin-bottom: 10px;"><i class="bx bx-check" style="color: #5cb874; margin-right: 8px;"></i> + All Missouri Statewide</li>
            </ul>
          </div>

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
            <h3>Illinois</h3>
            <ul style="list-style: none; padding: 0; columns: 2; -webkit-columns: 2; -moz-columns: 2;">
              <li style="margin-bottom: 10px;"><i class="bx bx-check" style="color: #5cb874; margin-right: 8px;"></i> Belleville</li>
              <li style="margin-bottom: 10px;"><i class="bx bx-check" style="color: #5cb874; margin-right: 8px;"></i> Collinsville</li>
              <li style="margin-bottom: 10px;"><i class="bx bx-check" style="color: #5cb874; margin-right: 8px;"></i> Edwardsville</li>
              <li style="margin-bottom: 10px;"><i class="bx bx-check" style="color: #5cb874; margin-right: 8px;"></i> Alton</li>
              <li style="margin-bottom: 10px;"><i class="bx bx-check" style="color: #5cb874; margin-right: 8px;"></i> Granite City</li>
              <li style="margin-bottom: 10px;"><i class="bx bx-check" style="color: #5cb874; margin-right: 8px;"></i> O'Fallon, IL</li>
              <li style="margin-bottom: 10px;"><i class="bx bx-check" style="color: #5cb874; margin-right: 8px;"></i> Fairview Heights</li>
              <li style="margin-bottom: 10px;"><i class="bx bx-check" style="color: #5cb874; margin-right: 8px;"></i> Swansea</li>
              <li style="margin-bottom: 10px;"><i class="bx bx-check" style="color: #5cb874; margin-right: 8px;"></i> Glen Carbon</li>
              <li style="margin-bottom: 10px;"><i class="bx bx-check" style="color: #5cb874; margin-right: 8px;"></i> + All Illinois Statewide</li>
            </ul>
          </div>
        </div>

        <div class="text-center mt-4" data-aos="fade-up" data-aos-delay="300">
          <p style="font-size: 16px; margin-bottom: 10px;"><strong>Remote Services Available Nationwide</strong></p>
          <p style="color: #777;">While we're based in St. Louis, we work with clients across the United States through virtual consultations and remote project management.</p>
          <a href="service-areas.php" class="btn btn-primary btn-brand" style="margin-top: 20px;">View Full Service Area Coverage</a>
          <!-- Deep links for popular location pages to improve internal linking -->
          <div class="mt-2"><small>Popular: <a href="st-louis-web-design.php" aria-label="St. Louis Web Design — popular location">St. Louis Web Design</a>, <a href="missouri-web-hosting.php" aria-label="Missouri Web Hosting — popular location">Missouri Web Hosting</a>, <a href="illinois-seo-services.php" aria-label="Illinois SEO Services — popular location">Illinois SEO</a></small></div>
        </div>
      </div>
    </section><!-- End Service Areas Section -->

  </main><!-- End #main -->
  <!-- ======= Footer ======= -->

<!-- End Footer -->
<?php include './assets/includes/footer.php'; ?>

<!-- ======= Exit-Intent Lead Magnet Modal ======= -->
<div class="modal-overlay" id="leadMagnetModal" style="display: none;" role="dialog" aria-labelledby="leadMagnetTitle" aria-modal="true">
  <div class="modal-content">
    <button class="modal-close" id="closeLeadModal" aria-label="Close modal">&times;</button>
    <div class="modal-header">
      <h2 id="leadMagnetTitle">Wait! Before You Go...</h2>
      <p>Get our FREE Website Launch Checklist + Bonus Resources</p>
    </div>
    <div class="modal-body">
      <ul style="text-align: left; margin: 20px 0; padding-left: 20px;">
        <li><i class="bx bx-check-circle" style="color: #5cb874;"></i> Website Launch Checklist (PDF)</li>
        <li><i class="bx bx-check-circle" style="color: #5cb874;"></i> SEO Audit Template (Excel)</li>
        <li><i class="bx bx-check-circle" style="color: #5cb874;"></i> Hosting Comparison Guide (PDF)</li>
      </ul>
      <form id="leadCaptureForm" action="forms/lead-capture.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <div class="form-floating mb-3">
          <input type="text" class="form-control" id="lead_name" name="name" placeholder=" " required>
          <label for="lead_name">Your Name</label>
        </div>
        <div class="form-floating mb-3">
          <input type="email" class="form-control" id="lead_email" name="email" placeholder=" " required>
          <label for="lead_email">Your Email</label>
        </div>
        <div class="invalid-feedback" id="lead-error" role="alert" style="display: none;"></div>
        <button type="submit" class="btn btn-brand w-100" id="leadSubmitBtn">
          <span class="btn-text">Get My Free Resources</span>
          <span class="btn-spinner" style="display: none;">
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Sending...
          </span>
        </button>
        <p style="font-size: 12px; color: #777; margin-top: 10px;">We respect your privacy. Unsubscribe anytime.</p>
      </form>
    </div>
  </div>
</div><!-- End Exit-Intent Modal -->

<!-- Tawk.to Live Chat: gated by CookieConsent. Set the source and let CookieConsent._loadFunctionalFeatures() inject the script when functional consent is granted. -->
<script>
  window.__tawk_to_src = 'https://embed.tawk.to/YOUR_PROPERTY_ID/YOUR_WIDGET_ID';
</script>

</body>

</html>

<!-- Leaflet Map CSS and JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Service Area Map Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // St. Louis coordinates (downtown)
    const stlLat = 38.6270;
    const stlLng = -90.1994;
    
    // Initialize map
    const map = L.map('service-area-map').setView([stlLat, stlLng], 10);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);
    
    // Add 20-mile radius circle
    const radiusCircle = L.circle([stlLat, stlLng], {
        color: '#5cb874',
        fillColor: '#5cb874',
        fillOpacity: 0.15,
        radius: 32186.9  // 20 miles in meters
    }).addTo(map);
    
    // Add marker for St. Louis
    const marker = L.marker([stlLat, stlLng]).addTo(map);
    marker.bindPopup('<div style="text-align: center;"><strong>Izende Studio Web</strong><br>St. Louis, MO<br><small>Serving 20-Mile Radius</small></div>');
    
    // Add major cities within range as markers
    const cities = [
        {name: 'Clayton', lat: 38.6425, lng: -90.3237},
        {name: 'Chesterfield', lat: 38.6631, lng: -90.5771},
        {name: 'Florissant', lat: 38.7892, lng: -90.3229},
        {name: 'University City', lat: 38.6567, lng: -90.3148},
        {name: 'Belleville, IL', lat: 38.5201, lng: -89.9840}
    ];
    
    cities.forEach(city => {
        L.circleMarker([city.lat, city.lng], {
            radius: 6,
            fillColor: "#667eea",
            color: "#fff",
            weight: 2,
            opacity: 1,
            fillOpacity: 0.8
        }).addTo(map).bindPopup(`<strong>${city.name}</strong><br><small>In Service Area</small>`);
    });
});
</script>
