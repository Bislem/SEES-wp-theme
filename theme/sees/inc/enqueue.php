<?php
/**
 * Styles, scripts and font preloads.
 *
 * @package sees
 */

add_action( 'wp_enqueue_scripts', function () {
	$css = SEES_DIR . '/assets/css/main.css';
	$js  = SEES_DIR . '/assets/js/main.js';

	wp_enqueue_style( 'sees-main', sees_asset( 'css/main.css' ), array(), (string) filemtime( $css ) );

	if ( is_rtl() ) {
		wp_enqueue_style( 'sees-rtl', sees_asset( 'css/rtl.css' ), array( 'sees-main' ), (string) filemtime( SEES_DIR . '/assets/css/rtl.css' ) );
	}

	wp_enqueue_script( 'sees-main', sees_asset( 'js/main.js' ), array(), (string) filemtime( $js ), array(
		'strategy'  => 'defer',
		'in_footer' => true,
	) );

	// Block library CSS isn't needed on templates that don't render post content.
	if ( is_front_page() || is_post_type_archive( 'project' ) || is_tax( 'project_category' ) ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'global-styles' );
	}
} );

/**
 * Preload the critical font files for the current language.
 * EN: Plus Jakarta Sans is one variable file (covers 400 and 800).
 * AR: IBM Plex Sans Arabic 700 (headings) + 400 (body).
 */
add_action( 'wp_head', function () {
	$lang  = function_exists( 'pll_current_language' ) ? pll_current_language() : ( is_rtl() ? 'ar' : 'en' );
	$fonts = 'ar' === $lang
		? array( 'fonts/ibm-plex-sans-arabic-arabic-700.woff2', 'fonts/ibm-plex-sans-arabic-arabic-400.woff2' )
		: array( 'fonts/plus-jakarta-sans-latin-wght.woff2' );
	foreach ( $fonts as $font ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( sees_asset( $font ) ) );
	}
}, 1 );

// Contact Form 7: only load its assets on the contact page.
add_filter( 'wpcf7_load_js', fn() => is_page_template( 'page-contact.php' ) );
add_filter( 'wpcf7_load_css', '__return_false' );
