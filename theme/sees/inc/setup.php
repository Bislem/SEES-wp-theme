<?php
/**
 * Theme supports, menus, image sizes.
 *
 * @package sees
 */

add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'sees', SEES_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-logo', array(
		'height'      => 128,
		'width'       => 445,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	register_nav_menus( array(
		'primary'          => __( 'Primary menu', 'sees' ),
		'footer_company'   => __( 'Footer: Company', 'sees' ),
		'footer_solutions' => __( 'Footer: Solutions', 'sees' ),
	) );

	add_image_size( 'sees-card', 680, 510, true );   // 4:3 project cards.
	add_image_size( 'sees-wide', 1200, 825, true );  // 16:11 solution rows.
	add_image_size( 'sees-hero', 1600, 1100, false ); // Page header photos.
} );

add_filter( 'image_size_names_choose', function ( $sizes ) {
	return array_merge( $sizes, array(
		'sees-card' => __( 'Card (4:3)', 'sees' ),
		'sees-wide' => __( 'Wide (16:11)', 'sees' ),
	) );
} );

// Content width for embeds.
add_action( 'after_setup_theme', function () {
	$GLOBALS['content_width'] = 760;
}, 0 );

// Fallback favicon when no Site Icon is set in the Customizer.
add_action( 'wp_head', function () {
	if ( ! has_site_icon() ) {
		printf( '<link rel="icon" href="%s" type="image/png">' . "\n", esc_url( sees_asset( 'img/favicon.png' ) ) );
	}
} );

// Serve generated image sizes as WebP (originals are kept).
add_filter( 'image_editor_output_format', function ( $formats ) {
	$formats['image/jpeg'] = 'image/webp';
	$formats['image/png']  = 'image/webp';
	return $formats;
} );

/**
 * Basic meta description (skipped when an SEO plugin is active).
 */
add_action( 'wp_head', function () {
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || class_exists( 'The_SEO_Framework\\Load' ) ) {
		return;
	}
	$desc = '';
	if ( is_singular() ) {
		$post = get_queried_object();
		$desc = (string) sees_field( 'header_subtitle', '', $post->ID );
		if ( ! $desc && is_front_page() ) {
			$desc = (string) sees_field( 'hero_text', '', $post->ID );
		}
		if ( ! $desc ) {
			$desc = (string) sees_field( 'summary', '', $post->ID );
		}
		if ( ! $desc && has_excerpt( $post ) ) {
			$desc = get_the_excerpt( $post );
		}
	} elseif ( is_post_type_archive( 'project' ) || is_tax( 'project_category' ) ) {
		$desc = __( 'A selection of electrical systems we\'ve designed, built and keep running.', 'sees' );
	}
	if ( ! $desc ) {
		$desc = get_bloginfo( 'description' );
	}
	printf( '<meta name="description" content="%s">' . "\n", esc_attr( wp_trim_words( wp_strip_all_tags( $desc ), 30, '…' ) ) );
}, 2 );

// Tidy up head output.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );

// Shorter excerpts for cards.
add_filter( 'excerpt_length', fn() => 24 );
add_filter( 'excerpt_more', fn() => '…' );

/**
 * Projects archive: no pagination limit surprises, honour ?cat= fallback filter.
 */
add_action( 'pre_get_posts', function ( WP_Query $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( $q->is_post_type_archive( 'project' ) || $q->is_tax( 'project_category' ) ) {
		$q->set( 'posts_per_page', 24 );
		$q->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );

		$cat = isset( $_GET['cat'] ) ? sanitize_title( wp_unslash( $_GET['cat'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if ( $cat && $q->is_post_type_archive( 'project' ) ) {
			$q->set( 'cat', '' );
			$q->set( 'tax_query', array( array(
				'taxonomy' => 'project_category',
				'field'    => 'slug',
				'terms'    => $cat,
			) ) );
		}
	}
} );
