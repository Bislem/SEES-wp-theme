<?php
/**
 * Inline stroke icons (from the approved design).
 *
 * @package sees
 */

/**
 * Icon choices offered in the Solution "icon" field.
 *
 * @return array<string,string>
 */
function sees_solution_icons(): array {
	return array(
		'bolt'     => __( 'Lightning bolt', 'sees' ),
		'sun'      => __( 'Sun', 'sees' ),
		'pylon'    => __( 'Pylon', 'sees' ),
		'home'     => __( 'Building', 'sees' ),
		'wrench'   => __( 'Wrench', 'sees' ),
		'leaf'     => __( 'Leaf', 'sees' ),
		'target'   => __( 'Target', 'sees' ),
		'shield'   => __( 'Shield', 'sees' ),
		'people'   => __( 'People', 'sees' ),
		'globe'    => __( 'Globe', 'sees' ),
		'building' => __( 'Office', 'sees' ),
	);
}

/**
 * Return an inline SVG icon. Decorative (aria-hidden) unless a label is given.
 *
 * @param string $name   Icon name.
 * @param int    $size   Pixel size.
 * @param array  $args   { @type float $stroke Stroke width. @type string $class Class. @type string $label Accessible name. }
 */
function sees_icon( string $name, int $size = 24, array $args = array() ): string {
	$paths = array(
		'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'search'   => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>',
		'bolt'     => '<path d="M13 2L4 14h7l-1 8 9-12h-7l1-8z"/>',
		'leaf'     => '<path d="M5 21c0-9 6-15 15-16-1 9-7 15-15 16z"/><path d="M5 21l7-7"/>',
		'target'   => '<circle cx="12" cy="12" r="3"/><circle cx="12" cy="12" r="7"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M4.9 19.1L7 17M17 7l2.1-2.1"/>',
		'people'   => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6"/><circle cx="17" cy="9" r="2.5"/><path d="M16 14c3 0 5.5 2 5.5 5"/>',
		'sun'      => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
		'pylon'    => '<path d="M12 2L7 22M12 2l5 20M8.5 15h7M10 8h4M5 6h14"/>',
		'home'     => '<path d="M3 11l9-7 9 7v9a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/><path d="M9 21v-6h6v6"/>',
		'building' => '<path d="M4 21V4h10v17M14 9h6v12M2 21h20M7 8h2M7 12h2M7 16h2M17 13h1M17 17h1"/>',
		'globe'    => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3 3 15 0 18M12 3c-3 3-3 15 0 18"/>',
		'wrench'   => '<path d="M14.7 6.3a4 4 0 0 0 5 5L21 13l-8 8-3-3 6.3-6.3M14.7 6.3L13 4.6a4 4 0 0 0-5.6 5.6L3 14.6 6.4 18l4.4-4.4"/>',
		'shield'   => '<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/><path d="M8.5 12l2.5 2.5 4.5-5"/>',
		'check'    => '<path d="M5 12l5 5 9-10"/>',
		'phone'    => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>',
		'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
		'pin'      => '<path d="M12 21s-7-6.5-7-12a7 7 0 0 1 14 0c0 5.5-7 12-7 12z"/><circle cx="12" cy="9" r="2.5"/>',
		'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'menu'     => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'moon'     => '<path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5z"/>',
		'close'    => '<path d="M6 6l12 12M18 6L6 18"/>',
		'play'     => '<path d="M8 5l12 7-12 7z" fill="currentColor" stroke="none"/>',
	);

	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}

	// Design system: line icons with a 1.4–1.7px stroke.
	$stroke = min( 1.7, max( 1.4, (float) ( $args['stroke'] ?? 1.6 ) ) );
	$class  = 'icon icon-' . $name . ( empty( $args['class'] ) ? '' : ' ' . $args['class'] );
	$a11y   = empty( $args['label'] )
		? 'aria-hidden="true" focusable="false"'
		: 'role="img" aria-label="' . esc_attr( $args['label'] ) . '"';

	return sprintf(
		'<svg class="%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="%3$s" stroke-linecap="round" stroke-linejoin="round" %4$s>%5$s</svg>',
		esc_attr( $class ),
		$size,
		esc_attr( (string) $stroke ),
		$a11y,
		$paths[ $name ] // Static markup defined above.
	);
}
