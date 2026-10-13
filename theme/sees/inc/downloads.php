<?php
/**
 * Support & Downloads: documents clients can download (brochures, datasheets,
 * manuals, certificates, forms).
 *
 * One `sees_download` record serves both languages: English title in the post title,
 * Arabic title/descriptions in ACF fields, one attached file, and a "document language"
 * (English, Arabic or both). Types use the `sees_download_type` taxonomy with an Arabic
 * name stored as term meta. Neither is translated by Polylang on purpose — editors upload
 * a file once and it appears on both language versions of the Support page.
 *
 * @package sees
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	register_post_type( 'sees_download', array(
		'labels'              => array(
			'name'          => __( 'Downloads', 'sees' ),
			'singular_name' => __( 'Download', 'sees' ),
			'add_new_item'  => __( 'Add new document', 'sees' ),
			'edit_item'     => __( 'Edit document', 'sees' ),
			'all_items'     => __( 'All documents', 'sees' ),
			'not_found'     => __( 'No documents found.', 'sees' ),
		),
		'public'              => false,
		'show_ui'             => true,
		'show_in_rest'        => true,
		'exclude_from_search' => true,
		'menu_icon'           => 'dashicons-download',
		'menu_position'       => 24,
		'supports'            => array( 'title', 'page-attributes' ),
	) );

	register_taxonomy( 'sees_download_type', 'sees_download', array(
		'labels'            => array(
			'name'          => __( 'Document types', 'sees' ),
			'singular_name' => __( 'Document type', 'sees' ),
		),
		'public'            => false,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'hierarchical'      => true,
		'rewrite'           => false,
	) );
} );

/**
 * Text in the current language: Arabic value when on an Arabic page and it exists.
 */
function sees_download_text( string $en, string $ar ): string {
	return ( is_rtl() && '' !== trim( $ar ) ) ? $ar : $en;
}

/**
 * Everything the Support page needs about one document.
 *
 * @return array|null Null when the document has no usable file.
 */
function sees_download_data( WP_Post $post ): ?array {
	$file_id = (int) get_post_meta( $post->ID, 'download_file', true );
	$path    = $file_id ? get_attached_file( $file_id ) : '';
	if ( ! $file_id || ! $path || ! file_exists( $path ) ) {
		return null;
	}
	$ext   = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
	$terms = get_the_terms( $post, 'sees_download_type' );
	$terms = ( $terms && ! is_wp_error( $terms ) ) ? $terms : array();
	$date  = (string) get_post_meta( $post->ID, 'document_date', true );
	$date  = preg_match( '/^\d{8}$/', $date ) ? strtotime( $date ) : strtotime( $post->post_modified_gmt );

	return array(
		'id'          => $post->ID,
		'title'       => sees_download_text( get_the_title( $post ), (string) get_post_meta( $post->ID, 'title_ar', true ) ),
		'description' => sees_download_text( (string) get_post_meta( $post->ID, 'description_en', true ), (string) get_post_meta( $post->ID, 'description_ar', true ) ),
		'url'         => (string) wp_get_attachment_url( $file_id ),
		'ext'         => $ext,
		'size'        => size_format( (int) filesize( $path ), 1 ),
		'language'    => (string) ( get_post_meta( $post->ID, 'doc_language', true ) ?: 'both' ),
		'version'     => (string) get_post_meta( $post->ID, 'version', true ),
		'date'        => wp_date( get_option( 'date_format' ), $date ),
		'types'       => array_map( fn( $t ) => array( 'slug' => $t->slug, 'name' => sees_download_type_name( $t ) ), $terms ),
		'viewable'    => in_array( $ext, array( 'pdf', 'jpg', 'jpeg', 'png', 'webp' ), true ),
	);
}

function sees_download_type_name( WP_Term $term ): string {
	return sees_download_text( $term->name, (string) get_term_meta( $term->term_id, 'name_ar', true ) );
}

function sees_download_language_label( string $lang ): string {
	$labels = array(
		'en'   => __( 'English', 'sees' ),
		'ar'   => __( 'Arabic', 'sees' ),
		'both' => __( 'English & Arabic', 'sees' ),
	);
	return $labels[ $lang ] ?? $labels['both'];
}

/**
 * Published documents with a file, in display order.
 *
 * @return array[]
 */
function sees_get_downloads(): array {
	$posts = get_posts( array(
		'post_type'      => 'sees_download',
		'post_status'    => 'publish',
		'posts_per_page' => 500,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		'no_found_rows'  => true,
	) );
	return array_values( array_filter( array_map( 'sees_download_data', $posts ) ) );
}

/**
 * Document types that have at least one published document.
 *
 * @return WP_Term[]
 */
function sees_get_download_types(): array {
	$terms = get_terms( array(
		'taxonomy'   => 'sees_download_type',
		'hide_empty' => true,
		'orderby'    => 'term_id', // creation order (set by the setup)
	) );
	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Does a document match the (no-JS) search/filter parameters?
 */
function sees_download_matches( array $doc, string $q, string $type, string $lang ): bool {
	if ( $type && ! in_array( $type, wp_list_pluck( $doc['types'], 'slug' ), true ) ) {
		return false;
	}
	if ( $lang && 'both' !== $doc['language'] && $lang !== $doc['language'] ) {
		return false;
	}
	if ( '' !== $q ) {
		$haystack = mb_strtolower( $doc['title'] . ' ' . $doc['description'] . ' ' . implode( ' ', wp_list_pluck( $doc['types'], 'name' ) ) );
		foreach ( preg_split( '/\s+/u', mb_strtolower( $q ) ) as $word ) {
			if ( '' !== $word && false === mb_strpos( $haystack, $word ) ) {
				return false;
			}
		}
	}
	return true;
}

// Admin list: file type and language columns.
add_filter( 'manage_sees_download_posts_columns', function ( array $columns ): array {
	$columns['download_file'] = __( 'File', 'sees' );
	$columns['doc_language']  = __( 'Language', 'sees' );
	return $columns;
} );
add_action( 'manage_sees_download_posts_custom_column', function ( string $column, int $post_id ): void {
	if ( 'download_file' === $column ) {
		$doc = sees_download_data( get_post( $post_id ) );
		echo $doc ? esc_html( strtoupper( $doc['ext'] ) . ' · ' . $doc['size'] ) : '<strong>' . esc_html__( 'No file', 'sees' ) . '</strong>';
	} elseif ( 'doc_language' === $column ) {
		echo esc_html( sees_download_language_label( (string) get_post_meta( $post_id, 'doc_language', true ) ) );
	}
}, 10, 2 );
