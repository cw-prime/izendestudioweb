<?php
// Detect if we're in a subdirectory
$base_path = (basename(dirname($_SERVER['SCRIPT_FILENAME'])) !== 'izendestudioweb') ? '../' : '';
?>
<!-- Skip link for accessibility -->
<a class="skip-link" href="#main">Skip to main content</a>
<header id="header" class="d-flex align-items-center">
    <div class="container d-flex align-items-center">

      <!-- Uncomment below if you prefer to use an image logo -->
       <a href="<?php echo $base_path; ?>index.php" class="logo me-auto"><img src="<?php echo $base_path; ?>assets/img/izende-T.png" alt="" class="img-fluid"><br><div class="logo-font">Studio Web</div></a>

      <nav id="navbar" class="navbar">
        <ul>
          <li><a class="nav-link scrollto active" href="<?php echo $base_path; ?>index.php#home">Home</a></li>
          <li class="dropdown"><a href="#"><span>Services</span> <i class="bi bi-chevron-down"></i></a>
            <ul>
              <li><a href="<?php echo $base_path; ?>services/chatbot.php">24/7 Chatbot Service</a></li>
              <li><a href="<?php echo $base_path; ?>services/video-editing.php">Video Editing</a></li>
              <li><a href="<?php echo $base_path; ?>services/web-development.php">Web Development</a></li>
              <li><a href="<?php echo $base_path; ?>services/wordpress.php">WordPress Design</a></li>
              <li><a href="<?php echo $base_path; ?>services/seo.php">SEO Services</a></li>
              <li><a href="<?php echo $base_path; ?>hosting.php">Web Hosting</a></li>
              <li><a href="<?php echo $base_path; ?>services/domain-lookup.php">Domain Lookup</a></li>
              <li><a href="<?php echo $base_path; ?>services/security-maintenance.php">Security & Maintenance</a></li>
              <li><a href="<?php echo $base_path; ?>services/ecommerce.php">E-Commerce Solutions</a></li>
              <li><a href="<?php echo $base_path; ?>services/social-media.php">Social Media Management</a></li>
              <li><a href="<?php echo $base_path; ?>services/email-marketing.php">Email Marketing</a></li>
              <li><a href="<?php echo $base_path; ?>services/speed-optimization.php">Speed Optimization</a></li>
              <li><a href="<?php echo $base_path; ?>index.php#services">View All Services</a></li>
            </ul>
          </li>
          <li><a class="nav-link scrollto " href="<?php echo $base_path; ?>index.php#portfolio">Portfolio</a></li>
          <li><a class="nav-link scrollto" href="<?php echo $base_path; ?>index.php#contact">Contact</a></li>
          <li><a class="nav-link scrollto" href="<?php echo $base_path; ?>blog.php">Blog</a></li>
          <li><a class="nav-link" href="<?php echo $base_path; ?>book-consultation.php"><i class="bi bi-calendar-check"></i> Book Consultation</a></li>
          <li><a class="getstarted scrollto" href="<?php echo $base_path; ?>quote.php" id="quote">Free Quote</a></li>
        </ul>
        <button id="dark-mode-toggle" class="dark-mode-toggle" aria-label="Toggle dark mode" title="Toggle dark mode">
          <i class="bx bx-moon"></i>
        </button>
        <i class="bi bi-list mobile-nav-toggle"></i>
      </nav><!-- .navbar -->

    </div>
  </header>
