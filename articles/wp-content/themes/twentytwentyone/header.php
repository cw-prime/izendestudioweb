<?php

/**
 * The header.
 *
 * This is the template that displays all of the <head> section and everything up until main.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

?>
<!doctype html>
<html <?php language_attributes(); ?> <?php twentytwentyone_the_html_classes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">

  <?php wp_head(); ?>
</head>

<section id="topbar" class="d-flex align-items-center">
  <div class="contact-info d-flex align-items-center">
    <a href="mailto:support@izendestudioweb.com"><i class="bi bi-envelope-fill"></i>support@izendestudioweb.com</a> <i class="bi bi-phone-fill phone-icon"></i><a href="tel:314-312-6441"><span class="phone-num">+1 314.312.6441 </span></a>  </div>
</section>  
<section>
  <div class="social-links d-none d-md-block">
  <a href="https://twitter.com/IzendeWeb" target="_blank" class="twitter"><i class="bi bi-twitter"></i></a>
  <a href="https://www.facebook.com/Izende-Studio-Web-109880234906868" target="_blank" class="facebook"><i class="bi bi-facebook"></i></a>
  <!-- <a href="#" class="instagram"><i class="bi bi-instagram"></i></a> -->
  <a href="https://www.linkedin.com/company/izende-studio-web" target="_blank" class="linkedin"><i class="bi bi-linkedin"></i></a>
</div>
</section>



<body <?php body_class(); ?>>
  <?php wp_body_open(); ?>
  <div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#content"><?php esc_html_e('Skip to content', 'twentytwentyone'); ?></a>

    <?php get_template_part('template-parts/header/site-header'); ?>

    <div id="content" class="site-content">
      <div id="primary" class="content-area">
        <main id="main" class="site-main">