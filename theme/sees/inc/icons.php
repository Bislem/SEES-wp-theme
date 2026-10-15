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
		'download' => '<path d="M12 4v11M7 10l5 5 5-5"/><path d="M5 20h14"/>',
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

/**
 * Filled brand icon for a social network (simplified glyphs, currentColor).
 */
function sees_social_icon( string $network, int $size = 18 ): string {
	$s     = 'fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"';
	$paths = array(
		'linkedin'  => '<path d="M20.45 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43a2.06 2.06 0 1 1 0-4.13 2.06 2.06 0 0 1 0 4.13zM7.12 20.45H3.56V9h3.56v11.45z"/>',
		'x'         => '<path d="M18.9 1.15h3.68l-8.04 9.19L24 22.85h-7.41l-5.8-7.58-6.64 7.58H.47l8.6-9.83L0 1.15h7.59l5.24 6.93zM17.61 20.64h2.04L6.49 3.24H4.3z"/>',
		'instagram' => '<rect x="2.5" y="2.5" width="19" height="19" rx="5.5" ' . $s . '/><circle cx="12" cy="12" r="4.3" ' . $s . '/><circle cx="17.6" cy="6.4" r="1.3"/>',
		'facebook'  => '<path d="M13.6 22v-8.1h2.7l.4-3.2h-3.1V8.7c0-.9.3-1.6 1.6-1.6h1.7V4.3c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2.3H7.4v3.2h2.8V22z"/>',
		'youtube'   => '<path fill-rule="evenodd" d="M23 7.2a3 3 0 0 0-2.1-2.1C19 4.6 12 4.6 12 4.6s-7 0-8.9.5A3 3 0 0 0 1 7.2C.5 9.1.5 12 .5 12s0 2.9.5 4.8a3 3 0 0 0 2.1 2.1c1.9.5 8.9.5 8.9.5s7 0 8.9-.5a3 3 0 0 0 2.1-2.1c.5-1.9.5-4.8.5-4.8s0-2.9-.5-4.8zM9.75 15.3V8.7L15.5 12z"/>',
		'tiktok'    => '<path d="M12.53.02C13.84 0 15.14.01 16.44 0c.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>',
		'snapchat'  => '<path d="M12 2.2c-3.1 0-5.2 2.3-5.2 5.4v2.5l-1.5-.4c-.7-.2-1.1.7-.5 1.1l1.8 1.2c-.6 1.6-1.9 3-3.6 3.6-.4.2-.4.8 0 1l1.7.5.3 1.2c.1.3.4.5.7.4l1.4-.2c.9.9 2.3 2.3 4.9 2.3s4-1.4 4.9-2.3l1.4.2c.3.1.6-.1.7-.4l.3-1.2 1.7-.5c.4-.2.4-.8 0-1-1.7-.6-3-2-3.6-3.6l1.8-1.2c.6-.4.2-1.3-.5-1.1l-1.5.4V7.6c0-3.1-2.1-5.4-5.2-5.4z"/>',
		'whatsapp'  => '<path d="M3.6 20.4l1.3-4.3a8.6 8.6 0 1 1 3.1 3z" ' . $s . '/><path d="M9.1 7.9c.3-.3.8-.3 1 .1l.8 1.7c.1.3 0 .6-.2.8l-.6.6c.6 1.3 1.6 2.3 2.9 2.9l.6-.6c.2-.2.5-.3.8-.2l1.7.8c.4.2.4.7.1 1l-.7.8c-.5.5-1.3.6-2 .3a9.4 9.4 0 0 1-4.6-4.6c-.3-.7-.2-1.5.3-2z"/>',
	);
	if ( ! isset( $paths[ $network ] ) ) {
		return '';
	}
	return sprintf(
		'<svg class="icon icon-social icon-%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">%3$s</svg>',
		esc_attr( $network ),
		$size,
		$paths[ $network ] // Static markup defined above.
	);
}
