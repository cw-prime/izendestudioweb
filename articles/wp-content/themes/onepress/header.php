<?php

/**
 * The header for the OnePress theme.
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package OnePress
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

	<div class="container">

		<div class="row">
			<section id="topbar">
				<div class="col contact-info">
					<a href="mailto:support@izendestudioweb.com"><i class="bi bi-envelope-fill"></i>support@izendestudioweb.com</a> <i class="bi bi-phone-fill phone-icon"></i><a href="tel:314-312-6441"><span class="phone-num">+1 314.312.6441 </span></a>
				</div>
			</section>
			<div class="col-4">

			</div>
			<div class="col">
				<section>
					<div class="social-links ">
						<a href="https://twitter.com/IzendeWeb" target="_blank" class="twitter"><i class="bi bi-twitter"></i></a>
						<a href="https://www.facebook.com/Izende-Studio-Web-109880234906868" target="_blank" class="facebook"><i class="bi bi-facebook"></i></a>
						<!-- <a href="#" class="instagram"><i class="bi bi-instagram"></i></a> -->
						<a href="https://www.linkedin.com/company/izende-studio-web" target="_blank" class="linkedin"><i class="bi bi-linkedin"></i></a>
					</div>
				</section>
			</div>
		</div>
	</div>



	<?php
	if (function_exists('wp_body_open')) {
		wp_body_open();
	}
	?>
	<?php do_action('onepress_before_site_start'); ?>
	<div id="page" class="hfeed site">
		<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e('Skip to content', 'onepress'); ?></a>
		<?php
		/**
		 * @since 2.0.0
		 */
		onepress_header();
		?>