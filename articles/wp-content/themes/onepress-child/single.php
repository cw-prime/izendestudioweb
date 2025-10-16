<?php
/**
 * The template for displaying all single posts.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package OnePress_Child
 */

get_header();
$layout = onepress_get_layout();

/**
 * @since 2.0.0
 * @see onepress_display_page_title
 */
do_action( 'onepress_page_before_content' );
?>

	<div id="content" class="site-content">

		<?php onepress_breadcrumb(); ?>

		<div id="content-inside" class="container <?php echo esc_attr( $layout ); ?>">
			<div id="primary" class="content-area">
				<main id="main" class="site-main" role="main">

				<?php while ( have_posts() ) : the_post(); ?>

					<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-post' ); ?>>

						<!-- Post Categories -->
						<?php
						$categories = get_the_category();
						if ( ! empty( $categories ) ) :
						?>
						<div class="post-categories">
							<?php foreach ( $categories as $category ) : ?>
								<a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>">
									<?php echo esc_html( $category->name ); ?>
								</a>
							<?php endforeach; ?>
						</div>
						<?php endif; ?>

						<!-- Post Title -->
						<header class="entry-header">
							<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

							<!-- Post Meta -->
							<div class="entry-meta">
								<span class="author">
									<i class="fa fa-user"></i>
									<?php the_author_posts_link(); ?>
								</span>
								<span class="date">
									<i class="fa fa-calendar"></i>
									<?php echo get_the_date(); ?>
								</span>
								<span class="read-time">
									<i class="fa fa-clock-o"></i>
									<?php echo onepress_child_estimated_read_time(); ?>
								</span>
								<?php if ( comments_open() || get_comments_number() ) : ?>
								<span class="comments">
									<i class="fa fa-comments"></i>
									<a href="<?php comments_link(); ?>">
										<?php comments_number( '0 Comments', '1 Comment', '% Comments' ); ?>
									</a>
								</span>
								<?php endif; ?>
							</div>
						</header><!-- .entry-header -->

						<!-- Featured Image -->
						<?php if ( has_post_thumbnail() ) : ?>
						<div class="post-thumbnail">
							<?php the_post_thumbnail( 'full' ); ?>
						</div>
						<?php endif; ?>

						<!-- Post Content -->
						<div class="entry-content">
							<?php
							the_content();

							wp_link_pages( array(
								'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'onepress-child' ),
								'after'  => '</div>',
							) );
							?>
						</div><!-- .entry-content -->

						<!-- Post Tags -->
						<?php
						$tags = get_the_tags();
						if ( $tags ) :
						?>
						<div class="post-tags">
							<?php foreach ( $tags as $tag ) : ?>
								<a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>">
									#<?php echo esc_html( $tag->name ); ?>
								</a>
							<?php endforeach; ?>
						</div>
						<?php endif; ?>

						<!-- Social Sharing Buttons -->
						<?php onepress_child_social_sharing_buttons(); ?>

						<!-- Post Navigation -->
						<?php
						the_post_navigation( array(
							'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous:', 'onepress-child' ) . '</span> <span class="nav-title">%title</span>',
							'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next:', 'onepress-child' ) . '</span> <span class="nav-title">%title</span>',
						) );
						?>

						<!-- Author Bio -->
						<?php onepress_child_author_bio(); ?>

						<!-- Related Posts -->
						<?php
						$related_posts = onepress_child_get_related_posts( get_the_ID(), 3 );
						if ( $related_posts ) :
						?>
						<div class="related-posts">
							<h3><?php _e( 'Related Articles', 'onepress-child' ); ?></h3>
							<div class="row">
								<?php while ( $related_posts->have_posts() ) : $related_posts->the_post(); ?>
								<div class="col-md-4">
									<div class="related-post-card">
										<?php if ( has_post_thumbnail() ) : ?>
											<a href="<?php the_permalink(); ?>">
												<?php the_post_thumbnail( 'onepress-related-post' ); ?>
											</a>
										<?php endif; ?>
										<div class="card-content">
											<h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
											<p class="excerpt"><?php echo wp_trim_words( get_the_excerpt(), 15, '...' ); ?></p>
											<a href="<?php the_permalink(); ?>" class="read-more">
												<?php _e( 'Read More &rarr;', 'onepress-child' ); ?>
											</a>
										</div>
									</div>
								</div>
								<?php endwhile; ?>
							</div>
						</div>
						<?php
						wp_reset_postdata();
						endif;
						?>

					</article><!-- #post-<?php the_ID(); ?> -->

					<?php
					// If comments are open or we have at least one comment, load up the comment template.
					if ( comments_open() || get_comments_number() ) :
						comments_template();
					endif;
					?>

				<?php endwhile; // End of the loop. ?>

				</main><!-- #main -->
			</div><!-- #primary -->

            <?php if ( $layout != 'no-sidebar' ) { ?>
                <?php get_sidebar(); ?>
            <?php } ?>

		</div><!--#content-inside -->
	</div><!-- #content -->

<?php get_footer(); ?>
