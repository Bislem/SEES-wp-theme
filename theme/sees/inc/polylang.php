<?php
/**
 * Polylang integration (all optional — the theme works without it).
 *
 * @package sees
 */

// Always make the theme's content types translatable.
add_filter( 'pll_get_post_types', function ( $types, $is_settings ) {
	foreach ( array( 'solution', 'project', 'faq', 'sees_job' ) as $type ) {
		$types[ $type ] = $type;
	}
	return $types;
}, 10, 2 );

add_filter( 'pll_get_taxonomies', function ( $taxonomies, $is_settings ) {
	$taxonomies['project_category'] = 'project_category';
	return $taxonomies;
}, 10, 2 );

// Keep ACF "detail" fields in sync across translations (numbers, flags). Header photos stay per language
// (the Arabic Home uses a mirrored hero photo).
add_filter( 'pll_copy_post_metas', function ( $metas ) {
	return array_values( array_unique( array_merge( $metas, array( 'number', 'icon', 'accent', 'year', 'is_featured' ), sees_job_shared_meta_keys() ) ) );
} );

/**
 * Header language switcher, e.g. "EN | عربي".
 */
function sees_language_switcher(): void {
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return;
	}
	$langs = pll_the_languages( array(
		'raw'           => 1,
		'hide_if_empty' => 0,
	) );
	if ( empty( $langs ) || count( $langs ) < 2 ) {
		return;
	}

	echo '<div class="lang-switch">';
	$i = 0;
	foreach ( $langs as $lang ) {
		if ( $i++ ) {
			echo '<span class="lang-sep" aria-hidden="true">|</span>';
		}
		$label = 'en' === $lang['slug'] ? 'EN' : $lang['name'];
		if ( $lang['current_lang'] ) {
			printf( '<span class="lang-current" lang="%s">%s</span>', esc_attr( $lang['locale'] ? str_replace( '_', '-', $lang['locale'] ) : $lang['slug'] ), esc_html( $label ) );
		} else {
			printf(
				'<a href="%1$s" lang="%2$s" hreflang="%2$s">%3$s</a>',
				esc_url( $lang['url'] ),
				esc_attr( $lang['slug'] ),
				esc_html( $label )
			);
		}
	}
	echo '</div>';
}
