<?php
// Detect if we're in a subdirectory
$base_path = (basename(dirname($_SERVER['SCRIPT_FILENAME'])) !== 'izendestudioweb') ? '../' : '';
?>
<!-- Skip link for accessibility -->
<a class="skip-link" href="#main">Skip to main content</a>
<header id="header" class="d-flex align-items-center">
    <div class="container d-flex align-items-center">

      <!-- Original Logo with Image -->
       <a href="<?php echo $base_path === '' ? '/' : $base_path; ?>" class="logo me-auto"><img src="<?php echo $base_path; ?>assets/img/izende-T.png" alt="Izende Studio Web" class="img-fluid"><br><div class="logo-font">Studio Web</div></a>

      <nav id="navbar" class="navbar">
        <ul>
          <li><a class="nav-link scrollto active" href="<?php echo $base_path; ?>#home">Home</a></li>
          <li class="desktop-services-link"><a class="nav-link" href="<?php echo $base_path; ?>services/">Services</a></li>
          <li class="mobile-services-cta"><a class="nav-link services-cta-link" href="<?php echo $base_path; ?>services/"><i class="bi bi-kanban me-1"></i> Services</a></li>
          <li><a class="nav-link scrollto " href="<?php echo $base_path; ?>#portfolio">Portfolio</a></li>
          <li><a class="nav-link scrollto" href="<?php echo $base_path; ?>#contact">Contact</a></li>
          <li><a class="nav-link scrollto" href="<?php echo $base_path; ?>blog.php">Blog</a></li>
          <li><a class="nav-link book-appointment" href="<?php echo $base_path; ?>book-consultation"><i class="bi bi-calendar-check"></i> Book Appointment</a></li>
          <li><a class="getstarted scrollto" href="<?php echo $base_path; ?>quote" id="quote">Free Quote</a></li>
        </ul>
        <button id="dark-mode-toggle" class="dark-mode-toggle" aria-label="Toggle dark mode" title="Toggle dark mode">
          <i class="bx bx-moon"></i>
        </button>
        <i class="bi bi-list mobile-nav-toggle"></i>
      </nav><!-- .navbar -->

    </div>
  </header>
