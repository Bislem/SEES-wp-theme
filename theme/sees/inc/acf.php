<?php
/**
 * Advanced Custom Fields (free) integration.
 * Field groups are stored as JSON in /acf-json and loaded automatically.
 *
 * @package sees
 */

add_filter( 'acf/settings/save_json', fn() => SEES_DIR . '/acf-json' );
add_filter( 'acf/settings/load_json', function ( $paths ) {
	$paths[] = SEES_DIR . '/acf-json';
	return array_unique( $paths );
} );

// Solution icon choices come from the theme's icon set.
add_filter( 'acf/load_field/name=icon', function ( $field ) {
	if ( 'select' === $field['type'] ) {
		$field['choices'] = sees_solution_icons();
	}
	return $field;
} );

// Tell admins when a required plugin is missing.
add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'activate_plugins' ) || get_option( 'sees_setup_check_pending' ) ) {
		return; // The setup checklist notice already covers missing plugins.
	}
	$missing = array();
	if ( ! class_exists( 'ACF' ) ) {
		$missing[] = 'Advanced Custom Fields';
	}
	if ( ! function_exists( 'pll_current_language' ) ) {
		$missing[] = 'Polylang';
	}
	if ( ! defined( 'WPCF7_VERSION' ) ) {
		$missing[] = 'Contact Form 7';
	}
	if ( $missing ) {
		printf(
			'<div class="notice notice-warning"><p>%s %s</p></div>',
			esc_html__( 'The SEES theme needs these free plugins:', 'sees' ),
			esc_html( implode( ', ', $missing ) )
		);
	}
} );
