<?php
/**
 * Izende Studio Web - Homepage
 * Secured with session management and security headers
 */

// Load security infrastructure
require_once __DIR__ . '/config/env-loader.php';
require_once __DIR__ . '/config/security.php';

// Initialize secure session and set security headers
initSecureSession();
setSecurityHeaders();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>St. Louis Web Design &amp; Hosting | Izende Studio Web | Missouri &amp; Illinois</title>
  <meta content="Professional web design, hosting, and digital marketing services in St. Louis, Missouri. Serving businesses throughout Missouri and Illinois with custom websites, SEO, and web development. 15+ years experience." name="description">
  <meta content="st louis web design, missouri web hosting, illinois seo, st louis web developer, missouri website design, web design st louis, hosting missouri" name="keywords">
  <meta name="google-site-verification" content="Wg2VOCCDPOm1l4Cof11F3kBTUqOSDR6yir-YKnoeHsM" />

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
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "PO Box 23456",
      "addressLocality": "St. Louis",
      "addressRegion": "MO",
      "postalCode": "63156",
      "addressCountry": "US"
    },
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

<body id="home">
  <!-- Google Tag Manager (noscript) -->
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-XXXXXXX"
  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->

  <!-- ======= Top Bar ======= -->
  <?php include './assets/includes/topbar.php'; ?>
  <!-- ======= Header ======= -->
  <?php include './assets/includes/header.php'; ?>
  <!-- End Header -->

  <!-- ======= Hero Section ======= -->
  <section id="hero">

    <div id="heroCarousel" data-bs-interval="5000" class="carousel slide carousel-fade" data-bs-ride="carousel">

      <ol class="carousel-indicators" id="hero-carousel-indicators"></ol>

      <div class="carousel-inner" role="listbox">

        <!-- Slide 1 -->
        <div class="carousel-item active" style="background-image: url(assets/img/slide/3ZYUW.jpg)">
          <div class="carousel-container">
            <div class="container">
              <h2 class="animate__animated animate__fadeInDown">St. Louis Web Design &amp; Hosting</h2>
              <p class="animate__animated animate__fadeInUp"><span style="text-transform:capitalize">Serving Missouri &amp; Illinois businesses with professional web design and hosting. Get your business online quick &amp; easy with local St. Louis expertise.</span>
              <!-- Inline local link for quick access to St. Louis services -->
              <a href="/st-louis-web-design.php" style="color: #5cb874; font-weight:600; margin-left:8px; display:inline-block;" class="animate__animated animate__fadeInUp" aria-label="St. Louis Web Design — learn about our local St. Louis web design services">St. Louis Web Design</a>
              </p>
              <a href="http://izendestudioweb.com/adminIzende/index.php?rp=/store/shared-hosting" class="btn-get-started animate__animated animate__fadeInUp scrollto">Choose A Plan</a>
            </div>
          </div>
        </div>

        <!-- Slide 2 -->
        <div class="carousel-item" style="background-image: url(assets/img/slide/55768.jpg)">
          <div class="carousel-container">
            <div class="container">
              <h2 class="animate__animated animate__fadeInDown">Custom Web Development</h2>
              <p class="animate__animated animate__fadeInUp"><span style="text-transform:capitalize">Using The Lastest Web Technologies Such As NodeJs, PHP and Javascript Let US Help You Plan Your Next Project.</span></p>
              <a href="./quote.php" class="btn-get-started animate__animated animate__fadeInUp scrollto">Start Now</a>
            </div>
          </div>
        </div>

        <!-- Slide 3 -->
        <div class="carousel-item" style="background-image: url(assets/img/slide/545FZX.jpg)">
          <div class="carousel-container">
            <div class="container">
              <h2 class="animate__animated animate__fadeInDown">Discover A Huge Variety of Domains</h2>
              <p class="animate__animated animate__fadeInUp "><span style="text-transform:capitalize">Check domain name availability and secure yours now.</span></p>
                <a href="http://izendestudioweb.com/adminIzende/cart.php?a=add&amp;domain=register" class="btn-get-started animate__animated animate__fadeInUp scrollto">Search Now</a>
            </div>
          </div>
        </div>

      </div>

      <a class="carousel-control-prev" href="#heroCarousel" role="button" data-bs-slide="prev">
        <span class="carousel-control-prev-icon bi bi-chevron-left" aria-hidden="true"></span>
      </a>

      <a class="carousel-control-next" href="#heroCarousel" role="button" data-bs-slide="next">
        <span class="carousel-control-next-icon bi bi-chevron-right" aria-hidden="true"></span>
      </a>

    </div>
  </section><!-- End Hero -->

  <main id="main">

    <!-- ======= Featured Services Section ======= -->


    <!-- ======= Services Section ======= -->
    <section id="services" class="services">
      <div class="container">

        <div class="section-title">
          <h2>Services</h2>
          <p>Serving St. Louis, Missouri, and Illinois businesses with professional web design, hosting, and digital marketing services. Our products help clients promote themselves online, get noticed on search engines, and generate leads.</p>
        </div>

        <div class="row">
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
              <p class="mt-2"><a href="/st-louis-web-design.php" class="btn btn-link" style="padding:0;" aria-label="St. Louis Web Design — learn about our local St. Louis web design services">St. Louis Web Design</a></p>
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
              <p>What good is having a nice site if customers can’t find it? We have the tool to make your site SEO friendly &amp; Visible Throughout The Web.</p>
                <h4><a href="http://izendestudioweb.com/adminIzende/cart.php?a=add&amp;domain=register">Domain Lookup</a></h4>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 d-flex align-items-stretch mt-4 mt-lg-0" data-aos="zoom-in" data-aos-delay="300">
            <div class="icon-box iconbox-yellow">
              <div class="icon">
                <svg width="100" height="100" viewBox="0 0 600 600" xmlns="http://www.w3.org/2000/svg">
                  <path stroke="none" stroke-width="0" fill="#f5f5f5" d="M426.667 874.667c-188.203 0-341.333-153.131-341.333-341.333s153.131-341.333 341.333-341.333c79.148 0.017 151.959 27.132 209.664 72.572l-0.725-0.55 187.563-187.563 60.331 60.331-187.563 187.563c44.913 56.987 72.044 129.814 72.064 208.977v0.005c0 188.203-153.131 341.333-341.333 341.333z"></path>
                </svg>
                <i class="bx bxs-search"></i>
              </div>
              <h4><a href="http://izendestudioweb.com/adminIzende/cart.php?a=add&domain=register">Domain Lookup</a></h4>
              <p>Why do you need a domain name? Your domain gives you an exclusive piece of digital real estate that cannot be used by anyone else as long as it’s registered to you.</p>
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
              <h4><a href="/hosting.php">Web Hosting</a></h4>
              <p>Fast, secure, and reliable web hosting with 99.9% uptime guarantee. From shared hosting to dedicated servers, we have the perfect solution for your website. Starting at $4.99/month with free SSL and 24/7 support.</p>
              <p class="mt-2"><a href="/missouri-web-hosting.php" class="btn btn-link" style="padding:0;" aria-label="Missouri Web Hosting — learn about web hosting in Missouri">Missouri Web Hosting</a></p>
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
              <h4><a href="/services/video-editing.php">Video Editing Services</a></h4>
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
              <h4><a href="/services/security-maintenance.php">Website Security &amp; Maintenance</a></h4>
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
              <h4><a href="/services/ecommerce.php">E-Commerce Solutions</a></h4>
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
              <h4><a href="/services/social-media.php">Social Media Management</a></h4>
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
              <h4><a href="/services/email-marketing.php">Email Marketing Automation</a></h4>
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
              <h4><a href="/services/speed-optimization.php">Website Speed Optimization</a></h4>
              <p>Make your website lightning fast. Improve Core Web Vitals, boost SEO rankings, and increase conversions. Professional speed optimization to reduce load times by up to 80%.</p>
            </div>
          </div>
        </div>

      </div>
    </section><!-- End Services Section -->

    <!-- ======= Stats Section ======= -->
    <section id="stats" class="stats section-bg">
      <div class="container">
        <div class="row">
          <div class="col-lg-3 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="100">
            <div class="icon-box text-center w-100">
              <i class="bx bx-trophy" style="font-size: 48px; color: #5cb874;"></i>
              <h3 class="counter" data-target="15">0</h3>
              <p><strong>Years Experience</strong></p>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="200">
            <div class="icon-box text-center w-100">
              <i class="bx bx-check-shield" style="font-size: 48px; color: #5cb874;"></i>
              <h3><span class="counter" data-target="500">0</span>+</h3>
              <p><strong>Projects Completed</strong></p>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="300">
            <div class="icon-box text-center w-100">
              <i class="bx bx-happy" style="font-size: 48px; color: #5cb874;"></i>
              <h3><span class="counter" data-target="99">0</span>%</h3>
              <p><strong>Client Satisfaction</strong></p>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="400">
            <div class="icon-box text-center w-100">
              <i class="bx bx-time-five" style="font-size: 48px; color: #5cb874;"></i>
              <h3><span class="counter" data-target="24">0</span>/7</h3>
              <p><strong>Support Available</strong></p>
            </div>
          </div>
        </div>
      </div>
    </section><!-- End Stats Section -->

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
                <div class="swiper-slide"><div class="client-logo" style="padding: 20px; text-align: center; font-size: 24px; font-weight: 600; color: #ccc;">Client Logo 1</div></div>
                <div class="swiper-slide"><div class="client-logo" style="padding: 20px; text-align: center; font-size: 24px; font-weight: 600; color: #ccc;">Client Logo 2</div></div>
                <div class="swiper-slide"><div class="client-logo" style="padding: 20px; text-align: center; font-size: 24px; font-weight: 600; color: #ccc;">Client Logo 3</div></div>
                <div class="swiper-slide"><div class="client-logo" style="padding: 20px; text-align: center; font-size: 24px; font-weight: 600; color: #ccc;">Client Logo 4</div></div>
                <div class="swiper-slide"><div class="client-logo" style="padding: 20px; text-align: center; font-size: 24px; font-weight: 600; color: #ccc;">Client Logo 5</div></div>
                <div class="swiper-slide"><div class="client-logo" style="padding: 20px; text-align: center; font-size: 24px; font-weight: 600; color: #ccc;">Client Logo 6</div></div>
              </div>
              <div class="swiper-pagination"></div>
            </div>
          </div>
        </div>
      </div>
    </section><!-- End Clients Section -->

    <!-- ======= Portfolio Section ======= -->
    <section id="portfolio" class="portfolio">
      <div class="container">

        <div class="section-title">
          <h2>Portfolio</h2>
          <p>Below are some of the latest web design and development projects we have done for our clients in St. Louis and surrounding areas.</p>
        </div>

        <div class="row">
          <div class="col-lg-12 d-flex justify-content-center">
            <ul id="portfolio-flters">
              <!-- <li data-filter="*" class="filter-active">All</li>
              <li data-filter=".filter-app">App</li>
              <li data-filter=".filter-card">Card</li>
              <li data-filter=".filter-web">Web</li> -->
            </ul>
          </div>
        </div>

        <div class="row portfolio-container">

          <div class="col-lg-4 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="assets/img/portfolio/Irie-Blog.png" class="img-fluid" alt="">
              <div class="portfolio-info">
                <h4>App 1</h4>
                <p>App</p>
                <div class="portfolio-links">
                  <a href="assets/img/portfolio/Irie-Blog.png" data-gallery="portfolioGallery" class="portfolio-lightbox" title="App 1"><i class="bx bx-plus"></i></a>
                  <a href="irie-blog.php" title="More Details"><i class="bx bx-link"></i></a>
                </div>
              </div>
            </div>
          </div>


        </div>

      </div>
    </section><!-- End Portfolio Section -->

    <!-- ======= Video Portfolio Section ======= -->
    <section id="video-portfolio" class="video-portfolio section-bg">
      <div class="container">
        <div class="section-title">
          <h2>Video Portfolio</h2>
          <p>Check out some of our recent video editing projects</p>
        </div>

        <div class="row">
          <div class="col-lg-4 col-md-6 video-item" data-aos="fade-up" data-aos-delay="100">
            <a href="https://www.youtube.com/watch?v=jDDaplaOz7Q" class="video-lightbox" data-glightbox="type: video">
              <img src="https://img.youtube.com/vi/jDDaplaOz7Q/maxresdefault.jpg" alt="Social Media Video Sample" class="img-fluid" loading="lazy">
              <div class="video-overlay">
                <i class="bx bx-play-circle"></i>
              </div>
            </a>
            <div class="video-info">
              <h4>Social Media Content</h4>
              <p>Instagram Reel for local business</p>
            </div>
          </div>

          <div class="col-lg-4 col-md-6 video-item" data-aos="fade-up" data-aos-delay="200">
            <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" class="video-lightbox" data-glightbox="type: video">
              <img src="https://img.youtube.com/vi/dQw4w9WgXcQ/maxresdefault.jpg" alt="YouTube Tutorial Sample" class="img-fluid" loading="lazy">
              <div class="video-overlay">
                <i class="bx bx-play-circle"></i>
              </div>
            </a>
            <div class="video-info">
              <h4>YouTube Tutorial</h4>
              <p>Educational content with graphics</p>
            </div>
          </div>

          <div class="col-lg-4 col-md-6 video-item" data-aos="fade-up" data-aos-delay="300">
            <a href="https://www.youtube.com/watch?v=9bZkp7q19f0" class="video-lightbox" data-glightbox="type: video">
              <img src="https://img.youtube.com/vi/9bZkp7q19f0/maxresdefault.jpg" alt="Promotional Video Sample" class="img-fluid" loading="lazy">
              <div class="video-overlay">
                <i class="bx bx-play-circle"></i>
              </div>
            </a>
            <div class="video-info">
              <h4>Promotional Video</h4>
              <p>Product launch campaign</p>
            </div>
          </div>
        </div>
      </div>
    </section><!-- End Video Portfolio Section -->

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
          <a href="/blog.php" class="btn btn-brand">View All Articles</a>
        </div>
      </div>
    </section><!-- End Featured Blog Section -->

    <!-- ======= About Us Section ======= -->
    <section id="about" class="about">
      <div class="container">

        <div class="section-title">
          <h2>About</h2>
          <p>We are a small, but creative and passionate team of designers and developers specializing in web seo, web development, branding &amp; digital marketing.</p>
        </div>

        <div class="row">
          <div class="col-lg-6 order-1 order-lg-2">

            <img src="assets/img/team/mark_a.jpg" class="img-fluid" alt="">
          </div>
          <div class="col-lg-6 pt-4 pt-lg-0 order-2 order-lg-1 content">
            <h3>Hey! Thanks for stopping by. We would like to share with you a little bit about our Izende Web Design Company.</h3>
            <p class="fst-italic">
              A little about our founder
            </p>

              <i class="bi bi-check-circled"></i><p>Mark founded Izende Studio Web in 2021, based in St. Louis, Missouri. It's a rebrand of Tikohosting founded in 2013.</p>
              <i class="bi bi-check-circled"></i><p>He has over 15 years of experience with web technologies, serving clients throughout Missouri and Illinois.</p>
              <i class="bi bi-check-circled"></i><p>Mark has been a DevOps engineer for major corporations such as WebMD, AT&amp;T Mobility, State Farm Insurance, Wells Fargo Bank, and more.</p>


            <p>
              <h4>In his spare time…</h4>
              <p>Mark is a digital nomad thats enjoys traveling with his wife. One of his favorites countries is Mexico. He is fluent in Spanish and is learning
                French as well.  Being a Florida native, He is a fan of The Tampa Bay Bucaneers and enjoys spending winters in Florida.
              </p>
            </p>
          </div>
        </div>

      </div>
    </section><!-- End About Us Section -->

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

    <!-- ======= Team Section ======= -->

    <!-- ======= Contact Section ======= -->
    <section id="contact" class="contact">
      <div class="container" >

        <div class="section-title">
          <h2>Contact</h2>
          <p>You can get in touch with us directly by e-mail, or by using this contact form. Leave me a message, and I will get back to you shortly. For a quick response you can start a chat with us on WhatsApp</p>
        </div>

        <div class="row">

          <div class="col-lg-5 d-flex align-items-stretch">
            <div class="info">
              <div class="address">
                <i class="bi bi-geo-alt"></i>
                <h4>Location:</h4>
                <p>PO Box 23456, St.Louis MO, 63156</p>
              </div>

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
              <p style="margin-top: 20px; margin-bottom: 10px; font-weight: 600; color: #5cb874;"><i class="bx bx-map" style="margin-right: 5px;"></i> Based in St. Louis, Missouri</p>
              <div id="map-placeholder">
                <iframe data-src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d12464.924189415528!2d-90.24116621297298!3d38.64356743325632!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x87d8b4af7beb781f%3A0x954ff3601377fd54!2sGrand%20Center%2C%20St.%20Louis%2C%20MO%2C%20USA!5e0!3m2!1sen!2sca!4v1637347839047!5m2!1sen!2sca" width="100%" height="290px" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
              </div>

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
                  <label class="form-check-label" for="consent">I agree to the <a href="/privacy-policy.php" target="_blank" rel="noopener">Privacy Policy</a>.</label>
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
          <a href="/service-areas.php" class="btn btn-primary btn-brand" style="margin-top: 20px;">View Full Service Area Coverage</a>
          <!-- Deep links for popular location pages to improve internal linking -->
          <div class="mt-2"><small>Popular: <a href="/st-louis-web-design.php" aria-label="St. Louis Web Design — popular location">St. Louis Web Design</a>, <a href="/missouri-web-hosting.php" aria-label="Missouri Web Hosting — popular location">Missouri Web Hosting</a>, <a href="/illinois-seo-services.php" aria-label="Illinois SEO Services — popular location">Illinois SEO</a></small></div>
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
