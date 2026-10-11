<?php
/**
 * Local/demo seed — run with `wp eval-file bin/seed.php` (see bin/seed.sh).
 *
 * Uses the theme's automatic setup (theme/sees/inc/installer.php) and adds what only
 * makes sense on a local or demo site: the bundled photos in the Media Library and
 * published (rather than draft) sample projects.
 *
 * SEES_RESEED=1 deletes the theme's content (pages, solutions, projects, FAQs, forms,
 * menus, project categories, imported photos) first and builds it again.
 *
 * @package sees
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

if ( ! function_exists( 'sees_run_setup' ) ) {
	WP_CLI::error( 'Activate the SEES theme first.' );
}
if ( ! sees_setup_plugins_ready() ) {
	WP_CLI::error( 'Polylang, Advanced Custom Fields and Contact Form 7 must be active.' );
}

/* ---------------------------------------------------------------------------
 * Optional reset
 * ------------------------------------------------------------------------ */
if ( getenv( 'SEES_RESEED' ) ) {
	WP_CLI::log( 'Removing the theme content…' );
	$types = array( 'page', 'solution', 'project', 'faq', 'wpcf7_contact_form' );
	foreach ( get_posts( array( 'post_type' => $types, 'post_status' => 'any', 'posts_per_page' => -1, 'lang' => '', 'fields' => 'ids' ) ) as $id ) {
		wp_delete_post( $id, true );
	}
	foreach ( get_terms( array( 'taxonomy' => 'project_category', 'hide_empty' => false, 'lang' => '', 'fields' => 'ids' ) ) as $term_id ) {
		wp_delete_term( $term_id, 'project_category' );
	}
	foreach ( array( 'Primary (EN)', 'Primary (AR)' ) as $menu ) {
		if ( wp_get_nav_menu_object( $menu ) ) {
			wp_delete_nav_menu( $menu );
		}
	}
	foreach ( (array) get_option( 'sees_setup_media', array() ) as $attachment ) {
		wp_delete_attachment( (int) $attachment, true );
	}
	delete_option( 'sees_setup_media' );
	delete_option( 'sees_seeded_projects' );
	update_option( 'show_on_front', 'posts' );
	update_option( 'page_on_front', 0 );
	$nav_menus = (array) PLL()->options['nav_menus'];
	unset( $nav_menus[ get_stylesheet() ] );
	sees_setup_pll_option( 'nav_menus', $nav_menus );
}

/* ---------------------------------------------------------------------------
 * Site settings for the local copy
 * ------------------------------------------------------------------------ */
update_option( 'blogname', 'SEES' );
update_option( 'blogdescription', 'Smart Electrical Engineering Solutions' );
update_option( 'timezone_string', 'Asia/Riyadh' );
update_option( 'start_of_week', 0 );
update_option( 'date_format', 'j F Y' );

/* ---------------------------------------------------------------------------
 * Theme setup + local extras
 * ------------------------------------------------------------------------ */
$log = sees_run_setup( array(
	'project_status' => 'publish',
	'sample_images'  => true,
) );

// Use the imported photos on the pages (only where no image is set yet).
$media = (array) get_option( 'sees_setup_media', array() );
$pick  = fn( $file ) => (int) ( $media[ $file ] ?? 0 );
$front = (int) get_option( 'page_on_front' );
$about = sees_setup_find_page( 'about', 'page-about.php', 'en' );
$images = array(
	array( $front, array( 'header_image' => $pick( 'hero-building.jpg' ), 'about_image' => $pick( 'concrete.jpg' ), 'cta_image' => $pick( 'facade.jpg' ) ) ),
	array( (int) pll_get_post( $front, 'ar' ), array( 'header_image' => $pick( 'hero-building-rtl.jpg' ), 'about_image' => $pick( 'concrete.jpg' ), 'cta_image' => $pick( 'facade.jpg' ) ) ),
	array( $about, array( 'story_image' => $pick( 'sign.jpg' ), 'tagline_image' => $pick( 'pylons.jpg' ) ) ),
	array( (int) pll_get_post( $about, 'ar' ), array( 'story_image' => $pick( 'sign.jpg' ), 'tagline_image' => $pick( 'pylons.jpg' ) ) ),
);
foreach ( $images as list( $post_id, $fields ) ) {
	if ( $post_id ) {
		sees_setup_fill_fields( $post_id, array_filter( $fields ) );
	}
}

PLL()->model->clean_languages_cache();
flush_rewrite_rules( false );

foreach ( $log['done'] as $line ) {
	WP_CLI::log( '  ✓ ' . $line );
}
foreach ( $log['errors'] as $line ) {
	WP_CLI::warning( $line );
}
WP_CLI::success( $log['done'] ? 'SEES content is set up (EN + AR).' : 'Everything was already in place.' );
