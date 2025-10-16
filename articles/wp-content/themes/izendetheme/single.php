<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package WordPress
 * @subpackage izendetheme
 * @since izendetheme 1.0
 */

get_header();

/* Start the Loop */
while ( have_posts() ) :
	the_post();
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'izende-single-post' ); ?>>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="post-featured-image">
				<?php the_post_thumbnail( 'large', array( 'class' => 'featured-image' ) ); ?>
			</div>
		<?php endif; ?>

		<header class="entry-header">
			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

			<div class="entry-meta">
				<div class="meta-item author-meta">
					<?php echo get_avatar( get_the_author_meta( 'ID' ), 40, '', get_the_author(), array( 'class' => 'author-avatar' ) ); ?>
					<span class="author-name">
						<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
							<?php the_author(); ?>
						</a>
					</span>
				</div>

				<div class="meta-item publish-date">
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
						<?php echo esc_html( get_the_date() ); ?>
					</time>
				</div>

				<?php
				$categories = get_the_category();
				if ( ! empty( $categories ) ) :
					?>
					<div class="meta-item categories">
						<?php
						foreach ( $categories as $category ) {
							echo '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '" class="category-pill">' . esc_html( $category->name ) . '</a>';
						}
						?>
					</div>
				<?php endif; ?>

				<div class="meta-item reading-time">
					<?php
					$reading_time = izendetheme_get_reading_time();
					/* translators: %d: Reading time in minutes */
					printf( esc_html__( '%d min read', 'izendetheme' ), $reading_time );
					?>
				</div>
			</div>
		</header>

		<div class="entry-content">
			<?php
			the_content();

			wp_link_pages(
				array(
					'before'   => '<nav class="page-links" aria-label="' . esc_attr__( 'Page', 'izendetheme' ) . '">',
					'after'    => '</nav>',
					/* translators: %: Page number. */
					'pagelink' => esc_html__( 'Page %', 'izendetheme' ),
				)
			);
			?>
		</div>

		<?php
		$tags = get_the_tags();
		if ( $tags ) :
			?>
			<footer class="entry-footer">
				<div class="tags-section">
					<span class="tags-label"><?php esc_html_e( 'Tags:', 'izendetheme' ); ?></span>
					<div class="tags-list">
						<?php
						foreach ( $tags as $tag ) {
							echo '<a href="' . esc_url( get_tag_link( $tag->term_id ) ) . '" class="tag-pill">' . esc_html( $tag->name ) . '</a>';
						}
						?>
					</div>
				</div>

				<div class="social-share">
					<span class="share-label"><?php esc_html_e( 'Share:', 'izendetheme' ); ?></span>
					<div class="share-links">
						<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_url( get_permalink() ); ?>"
						   target="_blank"
						   rel="noopener noreferrer"
						   class="share-link facebook"
						   aria-label="<?php esc_attr_e( 'Share on Facebook', 'izendetheme' ); ?>">
							<span class="share-icon">f</span>
						</a>
						<a href="https://twitter.com/intent/tweet?url=<?php echo esc_url( get_permalink() ); ?>&text=<?php echo esc_attr( get_the_title() ); ?>"
						   target="_blank"
						   rel="noopener noreferrer"
						   class="share-link twitter"
						   aria-label="<?php esc_attr_e( 'Share on Twitter', 'izendetheme' ); ?>">
							<span class="share-icon">𝕏</span>
						</a>
						<a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo esc_url( get_permalink() ); ?>&title=<?php echo esc_attr( get_the_title() ); ?>"
						   target="_blank"
						   rel="noopener noreferrer"
						   class="share-link linkedin"
						   aria-label="<?php esc_attr_e( 'Share on LinkedIn', 'izendetheme' ); ?>">
							<span class="share-icon">in</span>
						</a>
					</div>
				</div>
			</footer>
		<?php endif; ?>

	</article>

	<?php
	// Author bio section
	$author_description = get_the_author_meta( 'description' );
	if ( ! empty( $author_description ) ) :
		?>
		<div class="author-bio-box">
			<div class="author-bio-avatar">
				<?php echo get_avatar( get_the_author_meta( 'ID' ), 80, '', get_the_author() ); ?>
			</div>
			<div class="author-bio-content">
				<h3 class="author-bio-name">
					<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
						<?php the_author(); ?>
					</a>
				</h3>
				<p class="author-bio-description"><?php echo wp_kses_post( $author_description ); ?></p>
				<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" class="author-bio-link">
					<?php
					/* translators: %s: Author name */
					printf( esc_html__( 'More by %s', 'izendetheme' ), get_the_author() );
					?>
				</a>
			</div>
		</div>
	<?php endif; ?>

	<?php
	// Related posts section
	$categories = get_the_category();
	if ( ! empty( $categories ) ) {
		$category_ids = array();
		foreach ( $categories as $category ) {
			$category_ids[] = $category->term_id;
		}

		$related_args = array(
			'category__in'        => $category_ids,
			'post__not_in'        => array( get_the_ID() ),
			'posts_per_page'      => 3,
			'ignore_sticky_posts' => 1,
			'orderby'             => 'rand',
		);

		$related_query = new WP_Query( $related_args );

		if ( $related_query->have_posts() ) :
			?>
			<div class="related-posts-section">
				<h2 class="related-posts-title"><?php esc_html_e( 'Related Posts', 'izendetheme' ); ?></h2>
				<div class="related-posts-grid">
					<?php
					while ( $related_query->have_posts() ) :
						$related_query->the_post();
						?>
						<article class="related-post-card">
							<?php if ( has_post_thumbnail() ) : ?>
								<a href="<?php the_permalink(); ?>" class="related-post-thumbnail">
									<?php the_post_thumbnail( 'medium', array( 'class' => 'related-post-image' ) ); ?>
								</a>
							<?php endif; ?>
							<div class="related-post-content">
								<h3 class="related-post-title">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h3>
								<div class="related-post-meta">
									<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
										<?php echo esc_html( get_the_date() ); ?>
									</time>
								</div>
							</div>
						</article>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</div>
			<?php
		endif;
	}
	?>

	<?php
	// If comments are open or there is at least one comment, load up the comment template.
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}

	// Previous/next post navigation.
	$twentytwentyone_next = is_rtl() ? twenty_twenty_one_get_icon_svg( 'ui', 'arrow_left' ) : twenty_twenty_one_get_icon_svg( 'ui', 'arrow_right' );
	$twentytwentyone_prev = is_rtl() ? twenty_twenty_one_get_icon_svg( 'ui', 'arrow_right' ) : twenty_twenty_one_get_icon_svg( 'ui', 'arrow_left' );

	$twentytwentyone_next_label     = esc_html__( 'Next post', 'twentytwentyone' );
	$twentytwentyone_previous_label = esc_html__( 'Previous post', 'twentytwentyone' );

	the_post_navigation(
		array(
			'next_text' => '<p class="meta-nav">' . $twentytwentyone_next_label . $twentytwentyone_next . '</p><p class="post-title">%title</p>',
			'prev_text' => '<p class="meta-nav">' . $twentytwentyone_prev . $twentytwentyone_previous_label . '</p><p class="post-title">%title</p>',
		)
	);
	?>

<?php
endwhile; // End of the loop.

get_footer();
