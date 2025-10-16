<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

?>
			</main><!-- #main -->
		</div><!-- #primary -->
	</div><!-- #content -->

	<?php get_template_part( 'template-parts/footer/footer-widgets' ); ?>

	<!-- Service Area Section -->
	<div style="background: #0a0a0a; color: #fff; text-align: center; padding: 30px 15px; border-top: 1px solid rgba(255,255,255,0.1);">
		<div style="max-width: 1200px; margin: 0 auto;">
			<i class="bi bi-map" style="font-size: 24px; color: #5cb874; margin-bottom: 10px; display: inline-block;"></i>
			<h4 style="font-size: 18px; font-weight: 600; margin: 10px 0; color: #fff;">Serving St. Louis Metro, Missouri & Illinois</h4>
			<p style="font-size: 14px; color: rgba(255,255,255,0.7); max-width: 800px; margin: 0 auto; line-height: 1.6;">Professional web design, hosting, and digital marketing services for businesses throughout the St. Louis region and beyond.</p>
		</div>
	</div>
	<!-- End Service Area Section -->

	<!-- Legal Links Section -->
	<div style="background: #090909; padding: 20px 15px; border-top: 1px solid rgba(255,255,255,0.05);">
		<div style="max-width: 1200px; margin: 0 auto; text-align: center;">
			<div style="display: flex; justify-content: center; align-items: center; flex-wrap: wrap; gap: 15px; font-size: 13px;">
				<a href="https://izendestudioweb.com/privacy-policy.php" style="color: rgba(255,255,255,0.7); text-decoration: none;">Privacy Policy</a>
				<span style="color: rgba(255,255,255,0.3);">|</span>
				<a href="https://izendestudioweb.com/terms-of-service.php" style="color: rgba(255,255,255,0.7); text-decoration: none;">Terms of Service</a>
				<span style="color: rgba(255,255,255,0.3);">|</span>
				<a href="https://izendestudioweb.com/cookie-policy.php" style="color: rgba(255,255,255,0.7); text-decoration: none;">Cookie Policy</a>
				<span style="color: rgba(255,255,255,0.3);">|</span>
				<a href="https://izendestudioweb.com/refund-policy.php" style="color: rgba(255,255,255,0.7); text-decoration: none;">Refund Policy</a>
				<span style="color: rgba(255,255,255,0.3);">|</span>
				<a href="https://izendestudioweb.com/service-level-agreement.php" style="color: rgba(255,255,255,0.7); text-decoration: none;">SLA</a>
				<span style="color: rgba(255,255,255,0.3);">|</span>
				<a href="https://izendestudioweb.com/accessibility-statement.php" style="color: rgba(255,255,255,0.7); text-decoration: none;">Accessibility</a>
				<span style="color: rgba(255,255,255,0.3);">|</span>
				<a href="https://izendestudioweb.com/do-not-sell.php" style="color: rgba(255,255,255,0.7); text-decoration: none;">Do Not Sell or Share</a>
				<span style="color: rgba(255,255,255,0.3);">|</span>
				<a href="javascript:void(0);" onclick="if(typeof window.showCookieSettings === 'function') window.showCookieSettings();" style="color: rgba(255,255,255,0.7); text-decoration: none;">Cookie Settings</a>
			</div>
		</div>
	</div>
	<!-- End Legal Links Section -->

	<footer id="colophon" class="site-footer">

		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav aria-label="<?php esc_attr_e( 'Secondary menu', 'twentytwentyone' ); ?>" class="footer-navigation">
				<ul class="footer-navigation-wrapper">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'items_wrap'     => '%3$s',
							'container'      => false,
							'depth'          => 1,
							'link_before'    => '<span>',
							'link_after'     => '</span>',
							'fallback_cb'    => false,
						)
					);
					?>
				</ul><!-- .footer-navigation-wrapper -->
			</nav><!-- .footer-navigation -->
		<?php endif; ?>
		<div class="site-info">
			<div class="site-name">
				<?php if ( has_custom_logo() ) : ?>
					<div class="site-logo"><?php the_custom_logo(); ?></div>
				<?php else : ?>
					<?php if ( get_bloginfo( 'name' ) && get_theme_mod( 'display_title_and_tagline', true ) ) : ?>
						<?php if ( is_front_page() && ! is_paged() ) : ?>
							<?php bloginfo( 'name' ); ?>
						<?php else : ?>
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
						<?php endif; ?>
					<?php endif; ?>
				<?php endif; ?>
			</div><!-- .site-name -->

			<?php
			if ( function_exists( 'the_privacy_policy_link' ) ) {
				the_privacy_policy_link( '<div class="privacy-policy">', '</div>' );
			}
			?>

			<div class="powered-by">
				<?php
				printf(
					/* translators: %s: WordPress. */
					esc_html__( 'Proudly powered by %s.', 'twentytwentyone' ),
					'<a href="' . esc_url( __( 'https://wordpress.org/', 'twentytwentyone' ) ) . '">WordPress</a>'
				);
				?>
			</div><!-- .powered-by -->

		</div><!-- .site-info -->
	</footer><!-- #colophon -->

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
