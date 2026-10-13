<?php
/**
 * Contact Form 7 tweaks.
 *
 * @package sees
 */

// Don't wrap form controls in <p> tags — the theme lays the form out with grid.
add_filter( 'wpcf7_autop_or_not', '__return_false' );

// Privacy links in form consent text are stored as a token and resolved per page language.
add_filter( 'wpcf7_form_elements', function ( $html ) {
	if ( false !== strpos( $html, '{sees_privacy_url}' ) ) {
		$url  = function_exists( 'sees_privacy_url' ) ? sees_privacy_url() : '';
		$html = str_replace( '{sees_privacy_url}', esc_url( $url ? $url : home_url( '/' ) ), $html );
	}
	return $html;
} );
