<?php
/**
 * Izende Studio Web - Single Blog Post
 * Displays individual blog post from WordPress
 */

// Load security infrastructure
require_once __DIR__ . '/config/env-loader.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/services/blog-db.php'; // Changed to direct DB access

// Initialize secure session and set security headers
initSecureSession();
setSecurityHeaders();

// Get post slug from URL
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    header('Location: /blog.php');
    exit;
}

// Initialize Blog DB (direct database access - much faster)
try {
    $blog_db = new BlogDB();
    $post = $blog_db->getPostBySlug($slug);
} catch (Exception $e) {
    $post = false;
}

if (!$post) {
    header('HTTP/1.1 404 Not Found');
    include '404.php';
    exit;
}

// SEO meta data
$page_title = htmlspecialchars($post['title']) . ' | Izende Studio Web Blog';
$meta_description = htmlspecialchars(substr($post['excerpt'], 0, 160));
$meta_keywords = implode(', ', array_map(function($tag) { return $tag['name']; }, $post['tags']));
$og_image = $post['featured_image']['url'];
$canonical_url = 'https://izendestudioweb.com' . $post['link'];

// Prepare sidebar markup directly from WordPress widget area
$sidebar_id = 'sidebar-1';
$sidebar_html = '';
$wp_loader = __DIR__ . '/articles/wp-load.php';

if (file_exists($wp_loader)) {
    require_once $wp_loader;

    if (function_exists('is_active_sidebar') && function_exists('dynamic_sidebar') && is_active_sidebar($sidebar_id)) {
        ob_start();
        dynamic_sidebar($sidebar_id);
        $sidebar_html = ob_get_clean();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title><?php echo $page_title; ?></title>
  <meta content="<?php echo $meta_description; ?>" name="description">
  <meta content="<?php echo $meta_keywords; ?>" name="keywords">

  <!-- Canonical URL -->
  <link rel="canonical" href="<?php echo $canonical_url; ?>">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="article">
  <meta property="og:url" content="<?php echo $canonical_url; ?>">
  <meta property="og:title" content="<?php echo htmlspecialchars($post['title']); ?>">
  <meta property="og:description" content="<?php echo $meta_description; ?>">
  <meta property="og:image" content="<?php echo $og_image; ?>">
  <meta property="article:published_time" content="<?php echo $post['date']; ?>">
  <meta property="article:modified_time" content="<?php echo $post['modified']; ?>">
  <meta property="article:author" content="<?php echo htmlspecialchars($post['author']); ?>">

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="<?php echo $canonical_url; ?>">
  <meta property="twitter:title" content="<?php echo htmlspecialchars($post['title']); ?>">
  <meta property="twitter:description" content="<?php echo $meta_description; ?>">
  <meta property="twitter:image" content="<?php echo $og_image; ?>">

  <!-- Article Schema Markup -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BlogPosting",
    "headline": "<?php echo addslashes($post['title']); ?>",
    "image": "<?php echo $og_image; ?>",
    "datePublished": "<?php echo $post['date']; ?>",
    "dateModified": "<?php echo $post['modified']; ?>",
    "author": {
      "@type": "Organization",
      "name": "<?php echo htmlspecialchars($post['author']); ?>",
      "url": "https://izendestudioweb.com"
    },
    "publisher": {
      "@type": "Organization",
      "name": "Izende Studio Web",
      "logo": {
        "@type": "ImageObject",
        "url": "https://izendestudioweb.com/assets/img/izende-T.png"
      }
    },
    "description": "<?php echo addslashes($meta_description); ?>",
    "mainEntityOfPage": {
      "@type": "WebPage",
      "@id": "<?php echo $canonical_url; ?>"
    }
  }
  </script>

  <?php include './assets/includes/header-links.php'; ?>
</head>

<body class="blog-post-page">
  <!-- ======= Top Bar ======= -->
  <?php include './assets/includes/topbar.php'; ?>

  <!-- ======= Header ======= -->
  <?php include './assets/includes/header.php'; ?>
  <!-- End Header -->

  <main id="main" role="main">
    <!-- ======= Breadcrumbs ======= -->
    <section class="breadcrumbs">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center">
          <h2>Blog</h2>
          <ol>
            <li><a href="/index.php">Home</a></li>
            <li><a href="/blog.php">Blog</a></li>
            <li><?php echo htmlspecialchars($post['title']); ?></li>
          </ol>
        </div>
      </div>
    </section><!-- End Breadcrumbs -->

    <!-- ======= Blog Post Content ======= -->
    <section class="blog-post-content">
      <div class="container">
        <div class="row">
          <!-- Main Content -->
          <div class="col-lg-8">
            <article class="blog-post">
              <!-- Featured Image -->
              <?php if (!empty($post['featured_image']['url'])): ?>
              <div class="blog-post-image">
                <img src="<?php echo $post['featured_image']['url']; ?>"
                     alt="<?php echo htmlspecialchars($post['featured_image']['alt']); ?>"
                     class="img-fluid">
              </div>
              <?php endif; ?>

              <!-- Post Header -->
              <div class="blog-post-header">
                <h1 class="blog-post-title"><?php echo $post['title']; ?></h1>

                <div class="blog-post-meta">
                  <span class="post-author">
                    <i class="bx bx-user"></i> <?php echo htmlspecialchars($post['author']); ?>
                  </span>
                  <span class="post-date">
                    <i class="bx bx-calendar"></i>
                    <?php echo date('F j, Y', strtotime($post['date'])); ?>
                  </span>
                  <span class="post-reading-time">
                    <i class="bx bx-time-five"></i> <?php echo $post['reading_time']; ?> min read
                  </span>
                </div>

                <!-- Categories -->
                <?php if (!empty($post['categories'])): ?>
                <div class="blog-post-categories">
                  <?php foreach ($post['categories'] as $category): ?>
                    <a href="/blog.php?category=<?php echo urlencode($category['slug']); ?>" class="category-tag">
                      <?php echo htmlspecialchars($category['name']); ?>
                    </a>
                  <?php endforeach; ?>
                </div>
                <?php endif; ?>
              </div>

              <!-- Post Content -->
              <div class="blog-post-body">
                <?php echo $post['content']; ?>
              </div>

              <!-- Tags -->
              <?php if (!empty($post['tags'])): ?>
              <div class="blog-post-tags">
                <h4>Tags:</h4>
                <div class="tag-list">
                  <?php foreach ($post['tags'] as $tag): ?>
                    <span class="tag"><?php echo htmlspecialchars($tag['name']); ?></span>
                  <?php endforeach; ?>
                </div>
              </div>
              <?php endif; ?>

              <!-- Social Sharing -->
              <div class="blog-post-share">
                <h4>Share this article:</h4>
                <div class="share-buttons">
                  <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($canonical_url); ?>"
                     target="_blank"
                     rel="noopener"
                     class="share-btn facebook"
                     aria-label="Share on Facebook">
                    <i class="bx bxl-facebook"></i> Facebook
                  </a>
                  <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($canonical_url); ?>&text=<?php echo urlencode($post['title']); ?>"
                     target="_blank"
                     rel="noopener"
                     class="share-btn twitter"
                     aria-label="Share on Twitter">
                    <i class="bx bxl-twitter"></i> Twitter
                  </a>
                  <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode($canonical_url); ?>&title=<?php echo urlencode($post['title']); ?>"
                     target="_blank"
                     rel="noopener"
                     class="share-btn linkedin"
                     aria-label="Share on LinkedIn">
                    <i class="bx bxl-linkedin"></i> LinkedIn
                  </a>
                  <a href="mailto:?subject=<?php echo urlencode($post['title']); ?>&body=<?php echo urlencode('Check out this article: ' . $canonical_url); ?>"
                     class="share-btn email"
                     aria-label="Share via Email">
                    <i class="bx bx-envelope"></i> Email
                  </a>
                </div>
              </div>

              <!-- Author Bio -->
              <div class="blog-author-bio">
                <div class="author-image">
                  <img src="/assets/img/izende-T.png" alt="Izende Studio Web" class="img-fluid">
                </div>
                <div class="author-info">
                  <h4>About <?php echo htmlspecialchars($post['author']); ?></h4>
                  <p>Izende Studio Web has been serving St. Louis, Missouri, and Illinois businesses since 2013. We specialize in web design, hosting, SEO, and digital marketing solutions that help local businesses grow online.</p>
                  <div class="author-social">
                    <a href="https://www.facebook.com/Izende-Studio-Web-109880234906868" target="_blank"><i class="bx bxl-facebook"></i></a>
                    <a href="https://twitter.com/IzendeWeb" target="_blank"><i class="bx bxl-twitter"></i></a>
                    <a href="https://www.linkedin.com/company/izende-studio-web" target="_blank"><i class="bx bxl-linkedin"></i></a>
                  </div>
                </div>
              </div>

              <!-- Call to Action -->
              <div class="blog-post-cta">
                <div class="cta-content">
                  <h3>Need Help With Your Website?</h3>
                  <p>Whether you need web design, hosting, SEO, or digital marketing services, we're here to help your St. Louis business succeed online.</p>
                  <a href="/quote.php" class="btn btn-brand">Get a Free Quote</a>
                </div>
              </div>

            </article>
          </div>

          <!-- Sidebar -->
          <div class="col-lg-4">
            <aside class="blog-sidebar" aria-label="Blog sidebar widgets">
              <?php
                if (!empty($sidebar_html)) {
                    echo $sidebar_html;
                } else {
                    echo '<!-- Sidebar has no active widgets -->';
                }
              ?>
            </aside>
          </div>

        </div>
      </div>
    </section><!-- End Blog Post Content -->

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <?php include './assets/includes/footer.php'; ?>
  <!-- End Footer -->

</body>
</html>
