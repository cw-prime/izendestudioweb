<?php
/**
 * Izende Studio Web - Blog Landing Page
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
  <title>Blog | Izende Studio Web | Web Design & Development Insights</title>
  <meta content="Expert insights on web design, development, SEO, and digital marketing. Tips and tutorials for St. Louis businesses and beyond." name="description">
  <meta content="web design blog, wordpress tips, seo blog, web development tutorials, st louis digital marketing" name="keywords">

  <?php include './assets/includes/header-links.php'; ?>
</head>

<body>
  <!-- ======= Top Bar ======= -->
  <?php include './assets/includes/topbar.php'; ?>

  <!-- ======= Header ======= -->
  <?php include './assets/includes/header.php'; ?>
  <!-- End Header -->

  <main id="main">
    <!-- ======= Breadcrumbs ======= -->
    <section class="breadcrumbs">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center">
          <h2>Blog</h2>
          <ol>
            <li><a href="/index.php">Home</a></li>
            <li>Blog</li>
          </ol>
        </div>
      </div>
    </section><!-- End Breadcrumbs -->

    <!-- ======= Blog Hero Header ======= -->
    <section class="blog-hero">
      <div class="container">
        <div class="section-title">
          <h1>Web Design & Development Insights</h1>
          <p>Expert tips, tutorials, and industry insights to help your business thrive online</p>
        </div>
      </div>
    </section><!-- End Blog Hero -->

    <!-- ======= Blog Filters Section ======= -->
    <section class="blog-filters">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-6 mb-3 mb-md-0">
            <div class="form-floating">
              <input type="text" class="form-control" id="blog-search" placeholder="Search articles...">
              <label for="blog-search">Search articles...</label>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-floating">
              <select class="form-select" id="category-filter">
                <option value="">All Categories</option>
                <!-- Categories will be populated dynamically -->
              </select>
              <label for="category-filter">Filter by Category</label>
            </div>
          </div>
        </div>
      </div>
    </section><!-- End Blog Filters -->

    <!-- ======= Blog Posts Section ======= -->
    <section class="blog-posts section-bg">
      <div class="container">
        <div id="blog-posts-container" class="row" aria-live="polite">
          <!-- Loading skeletons will appear here initially -->
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

        <!-- Pagination -->
        <div id="blog-pagination" class="row mt-4">
          <div class="col-12">
            <!-- Pagination will be populated dynamically -->
          </div>
        </div>
      </div>
    </section><!-- End Blog Posts -->

    <!-- ======= Blog Categories Section ======= -->
    <section class="blog-categories">
      <div class="container">
        <div class="section-title">
          <h2>Popular Categories</h2>
        </div>
        <div id="blog-categories-container" class="row">
          <!-- Categories will be populated dynamically -->
        </div>
      </div>
    </section><!-- End Blog Categories -->

    <!-- ======= Newsletter Signup Section ======= -->
    <section class="newsletter-signup section-bg">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8 text-center">
            <h3>Stay Updated</h3>
            <p>Subscribe to our newsletter for the latest web design tips, tutorials, and industry insights delivered to your inbox.</p>
            <form id="newsletter-form" class="newsletter-form">
              <div class="row g-2 justify-content-center">
                <div class="col-md-8">
                  <input type="email" class="form-control" id="newsletter-email" placeholder="Enter your email" required>
                </div>
                <div class="col-md-4">
                  <button type="submit" class="btn btn-brand w-100">Subscribe</button>
                </div>
              </div>
              <p class="mt-2 small text-muted">We respect your privacy. Unsubscribe anytime.</p>
            </form>
          </div>
        </div>
      </div>
    </section><!-- End Newsletter Signup -->

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <?php include './assets/includes/footer.php'; ?>
  <!-- End Footer -->

</body>
</html>
