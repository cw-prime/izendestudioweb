<?php
/**
 * OnePress Child Theme Functions
 *
 * @package OnePress_Child
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue parent and child theme stylesheets
 */
function onepress_child_enqueue_styles() {
	// Enqueue parent theme stylesheet
	wp_enqueue_style(
		'onepress-style',
		get_template_directory_uri() . '/style.css',
		array(),
		wp_get_theme()->parent()->get('Version')
	);

	// Enqueue child theme stylesheet
	wp_enqueue_style(
		'onepress-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		array( 'onepress-style' ),
		wp_get_theme()->get('Version')
	);
}
add_action( 'wp_enqueue_scripts', 'onepress_child_enqueue_styles', 15 );

/**
 * Add Customizer settings for "Back to Main Site" link
 */
function onepress_child_customize_register( $wp_customize ) {
	// Add section for child theme settings
	$wp_customize->add_section( 'onepress_child_settings', array(
		'title'    => __( 'Izende Child Theme Settings', 'onepress-child' ),
		'priority' => 30,
	) );

	// Main Site URL setting
	$wp_customize->add_setting( 'onepress_child_main_site_url', array(
		'default'           => 'https://izendestudioweb.com/',
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( 'onepress_child_main_site_url', array(
		'label'    => __( 'Main Site URL', 'onepress-child' ),
		'section'  => 'onepress_child_settings',
		'type'     => 'url',
		'priority' => 10,
	) );

	// Main Site Link Text setting
	$wp_customize->add_setting( 'onepress_child_main_site_text', array(
		'default'           => 'Main Site',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( 'onepress_child_main_site_text', array(
		'label'    => __( 'Main Site Link Text', 'onepress-child' ),
		'section'  => 'onepress_child_settings',
		'type'     => 'text',
		'priority' => 20,
	) );

	// Show Service Area setting
	$wp_customize->add_setting( 'onepress_child_show_service_area', array(
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( 'onepress_child_show_service_area', array(
		'label'    => __( 'Show Service Area in Footer', 'onepress-child' ),
		'section'  => 'onepress_child_settings',
		'type'     => 'checkbox',
		'priority' => 30,
	) );
}
add_action( 'customize_register', 'onepress_child_customize_register' );

/**
 * Customize excerpt length
 */
function onepress_child_excerpt_length( $length ) {
	return 25; // 25 words for blog cards
}
add_filter( 'excerpt_length', 'onepress_child_excerpt_length', 999 );

/**
 * Customize excerpt more text
 */
function onepress_child_excerpt_more( $more ) {
	return '... <a class="read-more" href="' . get_permalink() . '">' . __( 'Read More &rarr;', 'onepress-child' ) . '</a>';
}
add_filter( 'excerpt_more', 'onepress_child_excerpt_more' );

/**
 * Add custom image sizes for blog cards
 */
function onepress_child_image_sizes() {
	add_image_size( 'onepress-blog-card', 400, 250, true ); // For blog card thumbnails
	add_image_size( 'onepress-related-post', 360, 200, true ); // For related posts
}
add_action( 'after_setup_theme', 'onepress_child_image_sizes' );

/**
 * Calculate estimated read time for a post
 *
 * @param int $post_id Post ID
 * @return string Formatted read time string
 */
function onepress_child_estimated_read_time( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$post = get_post( $post_id );
	$word_count = str_word_count( strip_tags( $post->post_content ) );
	$read_time = ceil( $word_count / 200 ); // Average reading speed: 200 words per minute

	if ( $read_time < 1 ) {
		$read_time = 1;
	}

	return sprintf( __( '%d min read', 'onepress-child' ), $read_time );
}

/**
 * Display estimated read time in post meta
 *
 * @return string HTML for read time display
 */
function onepress_child_display_read_time() {
	$read_time = onepress_child_estimated_read_time();
	return '<span class="read-time"><i class="fa fa-clock-o"></i> ' . esc_html( $read_time ) . '</span>';
}

/**
 * Get related posts by category
 *
 * @param int $post_id Current post ID
 * @param int $limit Number of related posts to retrieve
 * @return WP_Query|null Query object or null
 */
function onepress_child_get_related_posts( $post_id = null, $limit = 3 ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$categories = wp_get_post_categories( $post_id );

	if ( empty( $categories ) ) {
		return null;
	}

	$args = array(
		'category__in'   => $categories,
		'post__not_in'   => array( $post_id ),
		'posts_per_page' => $limit,
		'orderby'        => 'rand',
		'post_status'    => 'publish',
	);

	$related_posts = new WP_Query( $args );

	return $related_posts->have_posts() ? $related_posts : null;
}

/**
 * Display social sharing buttons
 *
 * @param int $post_id Post ID
 */
function onepress_child_social_sharing_buttons( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$post_url   = urlencode( get_permalink( $post_id ) );
	$post_title = urlencode( get_the_title( $post_id ) );

	?>
	<div class="post-share">
		<h4><?php _e( 'Share This Article', 'onepress-child' ); ?></h4>
		<div class="share-buttons">
			<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $post_url; ?>"
			   target="_blank"
			   rel="noopener noreferrer"
			   class="share-button share-facebook"
			   aria-label="<?php _e( 'Share on Facebook', 'onepress-child' ); ?>">
				<i class="fa fa-facebook"></i> Facebook
			</a>
			<a href="https://twitter.com/intent/tweet?url=<?php echo $post_url; ?>&text=<?php echo $post_title; ?>"
			   target="_blank"
			   rel="noopener noreferrer"
			   class="share-button share-twitter"
			   aria-label="<?php _e( 'Share on Twitter', 'onepress-child' ); ?>">
				<i class="fa fa-twitter"></i> Twitter
			</a>
			<a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo $post_url; ?>&title=<?php echo $post_title; ?>"
			   target="_blank"
			   rel="noopener noreferrer"
			   class="share-button share-linkedin"
			   aria-label="<?php _e( 'Share on LinkedIn', 'onepress-child' ); ?>">
				<i class="fa fa-linkedin"></i> LinkedIn
			</a>
		</div>
	</div>
	<?php
}

/**
 * Display author bio box
 *
 * @param int $author_id Author ID
 */
function onepress_child_author_bio( $author_id = null ) {
	if ( ! $author_id ) {
		$author_id = get_the_author_meta( 'ID' );
	}

	$author_description = get_the_author_meta( 'description', $author_id );

	// Only display if author has a bio
	if ( empty( $author_description ) ) {
		return;
	}

	$author_name = get_the_author_meta( 'display_name', $author_id );
	$author_url  = get_author_posts_url( $author_id );

	?>
	<div class="author-bio">
		<div class="author-avatar">
			<?php echo get_avatar( $author_id, 80 ); ?>
		</div>
		<div class="author-info">
			<h4><?php echo esc_html( $author_name ); ?></h4>
			<p><?php echo wp_kses_post( $author_description ); ?></p>
			<a href="<?php echo esc_url( $author_url ); ?>" class="author-posts-link">
				<?php printf( __( 'View all posts by %s &rarr;', 'onepress-child' ), esc_html( $author_name ) ); ?>
			</a>
		</div>
	</div>
	<?php
}

/**
 * Customize comment form fields
 */
function onepress_child_comment_form_fields( $fields ) {
	// Add placeholders to comment form fields
	$fields['author'] = str_replace(
		'<input',
		'<input placeholder="' . __( 'Your Name *', 'onepress-child' ) . '"',
		$fields['author']
	);

	$fields['email'] = str_replace(
		'<input',
		'<input placeholder="' . __( 'Your Email *', 'onepress-child' ) . '"',
		$fields['email']
	);

	$fields['url'] = str_replace(
		'<input',
		'<input placeholder="' . __( 'Your Website', 'onepress-child' ) . '"',
		$fields['url']
	);

	return $fields;
}
add_filter( 'comment_form_default_fields', 'onepress_child_comment_form_fields' );

/**
 * Customize comment form submit button
 */
function onepress_child_comment_form_submit_button( $submit_button ) {
	$submit_button = str_replace(
		'class="submit"',
		'class="submit btn-primary"',
		$submit_button
	);

	return $submit_button;
}
add_filter( 'comment_form_submit_button', 'onepress_child_comment_form_submit_button' );

/**
 * Add theme support for additional features
 */
function onepress_child_theme_support() {
	// Add support for responsive embeds
	add_theme_support( 'responsive-embeds' );

	// Add support for editor color palette (ensure brand colors are available)
	add_theme_support( 'editor-color-palette', array(
		array(
			'name'  => __( 'Brand Green', 'onepress-child' ),
			'slug'  => 'brand-green',
			'color' => '#5cb874',
		),
		array(
			'name'  => __( 'Brand Green Hover', 'onepress-child' ),
			'slug'  => 'brand-green-hover',
			'color' => '#6ec083',
		),
		array(
			'name'  => __( 'Brand Green Dark', 'onepress-child' ),
			'slug'  => 'brand-green-dark',
			'color' => '#449d5b',
		),
		array(
			'name'  => __( 'White', 'onepress-child' ),
			'slug'  => 'white',
			'color' => '#ffffff',
		),
		array(
			'name'  => __( 'Black', 'onepress-child' ),
			'slug'  => 'black',
			'color' => '#000000',
		),
		array(
			'name'  => __( 'Gray', 'onepress-child' ),
			'slug'  => 'gray',
			'color' => '#777777',
		),
	) );
}
add_action( 'after_setup_theme', 'onepress_child_theme_support' );
