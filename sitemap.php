<?php
require_once __DIR__ . '/config/env-loader.php';
require_once __DIR__ . '/config/security.php';

initSecureSession();
setSecurityHeaders();

$pageTitle = 'Sitemap | Izende Studio Web';
$metaDescription = 'Sitemap for Izende Studio Web: services, blog, case studies, and legal pages.';

function sitemap_base_path_for_links(): string {
    return '/';
}

$base = sitemap_base_path_for_links();

$staticLinks = [
    ['label' => 'Home', 'href' => $base],
    ['label' => 'Services', 'href' => $base . 'services/'],
    ['label' => 'Blog', 'href' => $base . 'blog'],
    ['label' => 'Book Appointment', 'href' => $base . 'book-consultation'],
    ['label' => 'Free Quote', 'href' => $base . 'quote'],
    ['label' => 'Portfolio (Case Studies)', 'href' => $base . 'portfolio-details'],
    ['label' => 'Hosting', 'href' => $base . 'hosting'],
    ['label' => 'Service Areas', 'href' => $base . 'service-areas'],
    ['label' => 'St. Louis Web Design', 'href' => $base . 'st-louis-web-design'],
    ['label' => 'Missouri Web Hosting', 'href' => $base . 'missouri-web-hosting'],
    ['label' => 'Illinois SEO Services', 'href' => $base . 'illinois-seo-services'],
    ['label' => 'Domain Lookup', 'href' => $base . 'lookup'],
];

$legalLinks = [
    ['label' => 'Privacy Policy', 'href' => $base . 'privacy-policy'],
    ['label' => 'Terms of Service', 'href' => $base . 'terms-of-service'],
    ['label' => 'Cookie Policy', 'href' => $base . 'cookie-policy'],
    ['label' => 'Refund Policy', 'href' => $base . 'refund-policy'],
    ['label' => 'Service Level Agreement', 'href' => $base . 'service-level-agreement'],
    ['label' => 'Accessibility Statement', 'href' => $base . 'accessibility-statement'],
    ['label' => 'Do Not Sell or Share', 'href' => $base . 'do-not-sell'],
    ['label' => 'Data Subject Request', 'href' => $base . 'data-subject-request'],
];

$serviceLinks = [];
$servicesDir = __DIR__ . '/services';
if (is_dir($servicesDir)) {
    $exclude = ['index.php', 'blog-api.php', 'blog-db.php'];
    foreach (glob($servicesDir . '/*.php') ?: [] as $filePath) {
        $baseName = basename($filePath);
        if (in_array($baseName, $exclude, true)) {
            continue;
        }
        $slug = basename($baseName, '.php');
        $label = ucwords(str_replace('-', ' ', $slug));
        $serviceLinks[] = ['label' => $label, 'href' => $base . 'services/' . $slug];
    }
    usort($serviceLinks, fn($a, $b) => strcasecmp($a['label'], $b['label']));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  <meta content="<?php echo htmlspecialchars($metaDescription); ?>" name="description">
  <?php include './assets/includes/header-links.php'; ?>
</head>
<body>
  <?php include './assets/includes/topbar.php'; ?>
  <?php include './assets/includes/header.php'; ?>

  <main id="main" role="main">
    <section class="breadcrumbs">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center">
          <h2>Sitemap</h2>
          <ol>
            <li><a href="/">Home</a></li>
            <li>Sitemap</li>
          </ol>
        </div>
      </div>
    </section>

    <section class="inner-page">
      <div class="container" style="max-width: 920px;">
        <p class="mb-4">Quick links to key pages on Izende Studio Web.</p>

        <h3>Primary Pages</h3>
        <ul>
          <?php foreach ($staticLinks as $link): ?>
            <li><a href="<?php echo htmlspecialchars($link['href']); ?>"><?php echo htmlspecialchars($link['label']); ?></a></li>
          <?php endforeach; ?>
        </ul>

        <?php if (!empty($serviceLinks)): ?>
          <h3>Services</h3>
          <ul>
            <?php foreach ($serviceLinks as $link): ?>
              <li><a href="<?php echo htmlspecialchars($link['href']); ?>"><?php echo htmlspecialchars($link['label']); ?></a></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <h3>Legal</h3>
        <ul>
          <?php foreach ($legalLinks as $link): ?>
            <li><a href="<?php echo htmlspecialchars($link['href']); ?>"><?php echo htmlspecialchars($link['label']); ?></a></li>
          <?php endforeach; ?>
        </ul>

        <p class="mt-4">
          For search engines: <a href="/sitemap.xml">XML Sitemap</a>
        </p>
      </div>
    </section>
  </main>

  <?php include './assets/includes/footer.php'; ?>
</body>
</html>

