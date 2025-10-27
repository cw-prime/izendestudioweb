<?php
// Detect if we're in a subdirectory
$base_path = (basename(dirname($_SERVER['SCRIPT_FILENAME'])) !== 'izendestudioweb') ? '../' : '';
?>
<!-- Resource Hints for Performance -->
<link rel="dns-prefetch" href="https://www.google.com">
<link rel="dns-prefetch" href="https://www.gstatic.com">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<!-- Favicons -->
<link href="<?php echo $base_path; ?>assets/img/favicon.ico" rel="icon">
<link href="<?php echo $base_path; ?>assets/img/apple-touch-icon.png" rel="apple-touch-icon">

<!-- Main CSS File -->
<link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/../css/style.css'); ?>">

<!-- Blog CSS File -->
<link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/blog.css?v=<?php echo filemtime(__DIR__ . '/../css/blog.css'); ?>">

<!-- New Modern Design CSS -->
<link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style-new.css?v=<?php echo filemtime(__DIR__ . '/../css/style-new.css'); ?>">

<!-- Material Icons for new design -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<!-- Preload Critical Fonts -->
<link rel="preload" href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&family=Raleway:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&family=Cinzel:wght@600&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&family=Raleway:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&family=Cinzel:wght@600&display=swap" rel="stylesheet"></noscript>

<!-- Vendor CSS Files -->
<link href="<?php echo $base_path; ?>assets/vendor/animate.css/animate.min.css" rel="stylesheet">
<link href="<?php echo $base_path; ?>assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="<?php echo $base_path; ?>assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
<link href="<?php echo $base_path; ?>assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
<link href="<?php echo $base_path; ?>assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/swiper@11/swiper-bundle.min.css" />

<!-- WebP Support Detection -->
<script>
(function() {
  var webP = new Image();
  webP.onload = webP.onerror = function() {
    document.documentElement.classList.add(webP.height === 2 ? 'webp' : 'no-webp');
  };
  webP.src = 'data:image/webp;base64,UklGRjoAAABXRUJQVlA4IC4AAACyAgCdASoCAAIALmk0mk0iIiIiIgBoSygABc6WWgAA/veff/0PP8bA//LwYAAA';
})();
</script>

<?php
// Include Google Analytics tracking
if (file_exists(__DIR__ . '/analytics.php')) {
    include __DIR__ . '/analytics.php';
}
?>
