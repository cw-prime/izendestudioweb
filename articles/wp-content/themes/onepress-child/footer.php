<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package OnePress_Child
 */

$hide_footer = false;
$page_id     = get_the_ID();

if ( is_page() ) {
	$hide_footer = get_post_meta( $page_id, '_hide_footer', true );
}

if ( onepress_is_wc_active() ) {
	if ( is_shop() ) {
		$page_id     = wc_get_page_id( 'shop' );
		$hide_footer = get_post_meta( $page_id, '_hide_footer', true );
	}
}

if ( ! $hide_footer ) {
	?>
	<!-- Service Area Section -->
	<?php if ( get_theme_mod( 'onepress_child_show_service_area', true ) ) : ?>
	<div class="footer-service-area">
		<div class="container">
			<i class="fa fa-map-marker"></i>
			<h4><?php _e( 'Serving St. Louis Metro, Missouri & Illinois', 'onepress-child' ); ?></h4>
			<p><?php _e( 'Professional web design, hosting, and digital marketing services for businesses throughout the St. Louis region and beyond.', 'onepress-child' ); ?></p>
		</div>
	</div>
	<?php endif; ?>
	<!-- End Service Area Section -->

	<!-- Legal Links Section -->
	<div class="footer-legal-links">
		<div class="container">
			<a href="https://izendestudioweb.com/privacy-policy.php"><?php _e( 'Privacy Policy', 'onepress-child' ); ?></a>
			<span class="separator">|</span>
			<a href="https://izendestudioweb.com/terms-of-service.php"><?php _e( 'Terms of Service', 'onepress-child' ); ?></a>
			<span class="separator">|</span>
			<a href="https://izendestudioweb.com/cookie-policy.php"><?php _e( 'Cookie Policy', 'onepress-child' ); ?></a>
			<span class="separator">|</span>
			<a href="https://izendestudioweb.com/refund-policy.php"><?php _e( 'Refund Policy', 'onepress-child' ); ?></a>
			<span class="separator">|</span>
			<a href="https://izendestudioweb.com/service-level-agreement.php"><?php _e( 'SLA', 'onepress-child' ); ?></a>
			<span class="separator">|</span>
			<a href="https://izendestudioweb.com/accessibility-statement.php"><?php _e( 'Accessibility', 'onepress-child' ); ?></a>
			<span class="separator">|</span>
			<a href="https://izendestudioweb.com/do-not-sell.php"><?php _e( 'Do Not Sell or Share', 'onepress-child' ); ?></a>
			<span class="separator">|</span>
			<a href="javascript:void(0);" onclick="if(typeof window.showCookieSettings === 'function') window.showCookieSettings();">
				<?php _e( 'Cookie Settings', 'onepress-child' ); ?>
			</a>
		</div>
	</div>
	<!-- End Legal Links Section -->

	<footer id="colophon" class="site-footer" role="contentinfo">
		<?php
		/**
		 * @since 2.0.0
		 * @see onepress_footer_widgets
		 * @see onepress_footer_connect
		 */
		do_action( 'onepress_before_site_info' );
		$onepress_btt_disable = sanitize_text_field( get_theme_mod( 'onepress_btt_disable' ) );

		?>

		<div class="site-info">
			<div class="container">
				<?php if ( $onepress_btt_disable != '1' ) : ?>
					<div class="btt">
						<a class="back-to-top" href="#page" title="<?php echo esc_attr__( 'Back To Top', 'onepress' ); ?>"><i class="fa fa-angle-double-up wow flash" data-wow-duration="2s"></i></a>
					</div>
				<?php endif; ?>
				<?php
				/**
				 * hooked onepress_footer_site_info
				 *
				 * @see onepress_footer_site_info
				 */
				do_action( 'onepress_footer_site_info' );
				?>
			</div>
		</div>
		<!-- .site-info -->

	</footer><!-- #colophon -->
	<?php
}
/**
 * Hooked: onepress_site_footer
 *
 * @see onepress_site_footer
 */
do_action( 'onepress_site_end' );
?>
</div><!-- #page -->

<?php do_action( 'onepress_after_site_end' ); ?>

<?php wp_footer(); ?>

</body>
</html>
