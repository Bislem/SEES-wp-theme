<?php
/**
 * Small template helpers.
 *
 * @package sees
 */

/**
 * URL of a file in assets/.
 */
function sees_asset( string $path ): string {
	return SEES_URI . '/assets/' . ltrim( $path, '/' );
}

/**
 * ACF field with a fallback, safe when ACF is inactive.
 *
 * @param string   $name     Field name.
 * @param mixed    $fallback Returned when the field is empty.
 * @param int|null $post_id  Defaults to the current post.
 */
function sees_field( string $name, $fallback = '', $post_id = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $fallback;
	}
	$value = get_field( $name, $post_id ?? false );
	return ( null === $value || '' === $value || false === $value || array() === $value ) ? $fallback : $value;
}

/**
 * Split a textarea into trimmed, non-empty lines.
 *
 * @return string[]
 */
function sees_lines( $text ): array {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\R/u', (string) $text ) ) ) );
}

/**
 * Render an image: a media-library attachment (with srcset) or a bundled theme image.
 *
 * @param int|array|string $image    Attachment ID / ACF image array, or empty.
 * @param string           $fallback File in assets/img/ used when $image is empty.
 * @param string           $size     Registered image size.
 * @param array            $attr     Extra attributes (class, alt, loading, sizes…).
 */
function sees_image( $image, string $fallback = '', string $size = 'large', array $attr = array() ): string {
	if ( is_array( $image ) && isset( $image['ID'] ) ) {
		$image = $image['ID'];
	}
	$attr = array_merge( array( 'loading' => 'lazy', 'decoding' => 'async' ), $attr );

	if ( $image && is_numeric( $image ) ) {
		if ( isset( $attr['alt'] ) && '' === $attr['alt'] ) {
			// Decorative: keep alt empty even if the attachment has one.
			$attr['alt'] = '';
		} elseif ( ! isset( $attr['alt'] ) ) {
			unset( $attr['alt'] );
		}
		if ( 'eager' === $attr['loading'] ) {
			$attr['fetchpriority'] = 'high';
		}
		return wp_get_attachment_image( (int) $image, $size, false, $attr );
	}

	if ( ! $fallback ) {
		return '';
	}

	// Prefer a pre-built WebP copy of bundled photos.
	$webp = preg_replace( '/\.jpe?g$/i', '.webp', $fallback );
	if ( $webp !== $fallback && is_readable( SEES_DIR . '/assets/img/' . $webp ) ) {
		$fallback = $webp;
	}
	$file = SEES_DIR . '/assets/img/' . $fallback;
	$dims = is_readable( $file ) ? wp_getimagesize( $file ) : false;
	$out  = '<img src="' . esc_url( sees_asset( 'img/' . $fallback ) ) . '"';
	if ( $dims ) {
		$out .= ' width="' . (int) $dims[0] . '" height="' . (int) $dims[1] . '"';
	}
	if ( 'eager' === $attr['loading'] ) {
		$attr['fetchpriority'] = 'high';
	}
	$attr['alt'] = $attr['alt'] ?? '';
	foreach ( $attr as $key => $val ) {
		$out .= ' ' . esc_attr( $key ) . '="' . esc_attr( $val ) . '"';
	}
	return $out . '>';
}

/**
 * Find the page using a given template (in the current language when Polylang is active).
 */
function sees_page_by_template( string $template ): ?WP_Post {
	static $cache = array();
	$lang = function_exists( 'pll_current_language' ) ? (string) pll_current_language() : '';
	$key  = $template . '|' . $lang;

	if ( ! array_key_exists( $key, $cache ) ) {
		$args = array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $template,           // phpcs:ignore WordPress.DB.SlowDBQuery
			'no_found_rows'  => true,
		);
		if ( $lang ) {
			$args['lang'] = $lang;
		}
		$pages         = get_posts( $args );
		$cache[ $key ] = $pages[0] ?? null;
	}
	return $cache[ $key ];
}

/**
 * Permalink of the page using a template, with a fallback URL.
 */
function sees_page_url( string $template, string $fallback = '' ): string {
	$page = sees_page_by_template( $template );
	return $page ? get_permalink( $page ) : ( $fallback ? $fallback : home_url( '/' ) );
}

/**
 * Site-wide contact details, edited on the Contact page.
 *
 * @return array{phone:string,email:string,address:string,hours:string}
 */
function sees_contact(): array {
	$page = sees_page_by_template( 'page-contact.php' );
	$id   = $page ? $page->ID : 0;
	return array(
		'phone'   => (string) sees_field( 'phone', '+966 50 326 4000', $id ),
		'email'   => (string) sees_field( 'email', 'info@seesengineering.com', $id ),
		'address' => (string) sees_field( 'address', __( 'Al Shahamah District, Makkah, Saudi Arabia', 'sees' ), $id ),
		'hours'   => (string) sees_field( 'hours', '[Sun – Thu, 00:00 – 00:00]', $id ),
	);
}

/**
 * Privacy policy URL in the current language ('' when there is none).
 */
function sees_privacy_url(): string {
	$id = (int) get_option( 'wp_page_for_privacy_policy' );
	if ( $id && function_exists( 'pll_get_post' ) ) {
		$id = (int) ( pll_get_post( $id ) ?: $id );
	}
	return ( $id && 'publish' === get_post_status( $id ) ) ? (string) get_permalink( $id ) : '';
}

/**
 * tel: link from a display phone number.
 */
function sees_tel( string $phone ): string {
	return 'tel:' . preg_replace( '/[^0-9+]/', '', $phone );
}

/**
 * Eyebrow label: uppercase text followed by a short line.
 *
 * @param string $text Label.
 * @param array  $args { @type bool $dark On dark background. @type bool $grow Animate the line. }
 */
function sees_eyebrow( string $text, array $args = array() ): void {
	get_template_part( 'template-parts/eyebrow', null, array_merge( array( 'text' => $text ), $args ) );
}

/**
 * Google Maps embed URL (no API key needed), in the page language.
 * Used when no exact pin is configured: a free-text district search can land on the
 * wrong business, so this shows the city, which is always correct.
 */
function sees_map_embed_for( string $place, int $zoom = 12 ): string {
	return add_query_arg( array(
		'q'      => rawurlencode( $place ),
		'hl'     => is_rtl() ? 'ar' : 'en',
		'z'      => $zoom,
		'output' => 'embed',
	), 'https://maps.google.com/maps' );
}

/**
 * Validated Google Maps share/place link (used for "Get directions").
 */
function sees_map_link( string $value ): string {
	$value = trim( $value );
	$host  = (string) wp_parse_url( $value, PHP_URL_HOST );
	$ok    = 'https' === wp_parse_url( $value, PHP_URL_SCHEME )
		&& preg_match( '/(^|\.)(google\.[a-z.]+|goo\.gl|maps\.app\.goo\.gl)$/', $host );
	return $ok ? $value : '';
}

/**
 * Allowed HTML for the map embed field.
 */
function sees_map_src( string $value ): string {
	// Accept either a URL or a full <iframe> embed code; keep only https Google Maps / OpenStreetMap sources.
	if ( preg_match( '/src=["\']([^"\']+)["\']/', $value, $m ) ) {
		$value = html_entity_decode( $m[1] );
	}
	$value = trim( $value );
	$host  = (string) wp_parse_url( $value, PHP_URL_HOST );
	$ok    = 'https' === wp_parse_url( $value, PHP_URL_SCHEME )
		&& preg_match( '/(^|\.)(google\.[a-z.]+|openstreetmap\.org)$/', $host );
	return $ok ? $value : '';
}

/**
 * lang="…" attribute for text written in the other script than the page (mixed EN/AR copy).
 */
function sees_lang_attr( string $text ): string {
	$is_arabic = (bool) preg_match( '/\p{Arabic}/u', $text );
	if ( $is_arabic && ! is_rtl() ) {
		return ' lang="ar" dir="rtl"';
	}
	if ( ! $is_arabic && is_rtl() && preg_match( '/[A-Za-z]/', $text ) ) {
		return ' lang="en" dir="ltr"';
	}
	return '';
}
