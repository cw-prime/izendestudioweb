<?php
/**
 * Services Hub Page
 */
$page_title = 'Digital Services | Izende Studio Web';
$page_description = 'Explore Izende Studio Web\'s full range of digital services, from web design and hosting to SEO, chatbots, and security.';

$services = [
  [
    'title' => '24/7 Chatbot Service',
    'description' => 'Automate lead capture and customer support with AI-powered chat experiences that never clock out.',
    'icon' => 'bx bx-message-square-dots',
    'link' => './chatbot'
  ],
  [
    'title' => 'Video Editing',
    'description' => 'Transform raw footage into polished, on-brand storytelling that keeps viewers engaged.',
    'icon' => 'bx bx-film',
    'link' => './video-editing'
  ],
  [
    'title' => 'Web Development',
    'description' => 'Custom websites and applications built for speed, accessibility, and long-term growth.',
    'icon' => 'bx bx-code-alt',
    'link' => './web-development'
  ],
  [
    'title' => 'WordPress Design',
    'description' => 'Pixel-perfect WordPress builds with intuitive editing, modern themes, and expert support.',
    'icon' => 'bx bxl-wordpress',
    'link' => './wordpress'
  ],
  [
    'title' => 'SEO Services',
    'description' => 'Increase visibility with technical SEO, content optimization, and local search strategies.',
    'icon' => 'bx bx-trending-up',
    'link' => './seo'
  ],
  [
    'title' => 'Web Hosting',
    'description' => 'Secure, managed hosting with proactive monitoring, nightly backups, and SSL as standard.',
    'icon' => 'bx bx-server',
    'link' => '../hosting'
  ],
  [
    'title' => 'Domain Lookup',
    'description' => 'Find, register, and manage the perfect domain for your brand in minutes.',
    'icon' => 'bx bx-search-alt',
    'link' => './domain-lookup'
  ],
  [
    'title' => 'Security & Maintenance',
    'description' => 'Stay protected with continuous updates, malware scanning, and site-hardening best practices.',
    'icon' => 'bx bx-shield-quarter',
    'link' => './security-maintenance'
  ],
  [
    'title' => 'E-Commerce Solutions',
    'description' => 'Launch or scale your store with frictionless checkout, inventory integrations, and analytics.',
    'icon' => 'bx bx-cart',
    'link' => './ecommerce'
  ],
  [
    'title' => 'Social Media Management',
    'description' => 'Strategic content planning, scheduling, and reporting that keeps your feeds active and on-brand.',
    'icon' => 'bx bx-share-alt',
    'link' => './social-media'
  ],
  [
    'title' => 'Email Marketing',
    'description' => 'Build automations and campaigns that nurture leads and keep customers coming back.',
    'icon' => 'bx bx-mail-send',
    'link' => './email-marketing'
  ],
  [
    'title' => 'Speed Optimization',
    'description' => 'Boost performance with caching, image optimization, and Core Web Vitals improvements.',
    'icon' => 'bx bx-tachometer',
    'link' => './speed-optimization'
  ],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title><?= $page_title; ?></title>
  <meta name="description" content="<?= $page_description; ?>">

  <?php include '../assets/includes/header-links.php'; ?>
</head>

<body>
  <?php include '../assets/includes/topbar.php'; ?>
  <?php include '../assets/includes/header.php'; ?>

  <main id="main">
    <section class="services-hero" style="padding: 120px 0 70px;">
      <div class="container" data-aos="fade-up">
        <div class="row align-items-center gy-4">
          <div class="col-lg-7">
            <h1 class="mb-3">Services Designed to Grow With You</h1>
            <p class="lead mb-4">From first impression to long-term retention, Izende Studio Web brings development, marketing, and technology together for a seamless digital experience.</p>
            <div class="d-flex flex-column flex-md-row gap-3">
              <a class="btn btn-primary btn-brand" href="../book-consultation.php">
                <i class="bi bi-calendar-check me-1"></i> Book a Consultation
              </a>
              <a class="btn btn-outline-primary" href="../quote.php">
                <i class="bi bi-rocket-takeoff me-1"></i> Start a Project
              </a>
            </div>
          </div>
          <div class="col-lg-5">
            <div class="services-hero-card shadow-lg p-4 rounded-3">
              <h3 class="h5 text-uppercase text-muted mb-3">How We Help</h3>
              <ul class="list-unstyled mb-4">
                <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i>Launch or modernize your website</li>
                <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i>Automate outreach and support</li>
                <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i>Drive leads with targeted campaigns</li>
                <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i>Keep everything secure and fast</li>
              </ul>
              <a class="btn btn-link p-0" href="../portfolio-details.php">See recent client wins →</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="services-grid section-bg" style="padding: 70px 0;">
      <div class="container" data-aos="fade-up">
        <div class="section-title">
          <h2>Explore Our Services</h2>
          <p>Select a capability below to learn more about how we deliver it.</p>
        </div>
        <div class="service-cards-grid">
          <?php foreach ($services as $service): ?>
            <article class="service-card-new" data-aos="fade-up">
              <div class="service-icon-new">
                <i class="<?= $service['icon']; ?>" aria-hidden="true"></i>
              </div>
              <h3 class="service-title-new"><?= $service['title']; ?></h3>
              <p class="service-desc-new"><?= $service['description']; ?></p>
              <a class="service-link-new" href="<?= $service['link']; ?>">Learn more</a>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section class="services-cta" style="padding: 70px 0;">
      <div class="container" data-aos="zoom-in">
        <div class="row justify-content-center">
          <div class="col-lg-10">
            <div class="cta-box d-flex flex-column flex-lg-row align-items-center justify-content-between p-4 p-lg-5 rounded-4 shadow-lg">
              <div class="cta-content text-center text-lg-start mb-3 mb-lg-0">
                <h2 class="h3 mb-2">Need help choosing the right solution?</h2>
                <p class="mb-0">Schedule a discovery call and we’ll map out the services that fit your timeline, budget, and objectives.</p>
              </div>
              <a class="btn btn-primary btn-brand btn-lg" href="../book-consultation.php">
                <i class="bi bi-telephone-outbound me-2"></i> Talk With Our Team
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <?php include '../assets/includes/footer.php'; ?>
</body>

</html>
