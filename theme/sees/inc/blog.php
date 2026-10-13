<?php
/**
 * News / blog helpers.
 *
 * Uses WordPress's own posts and categories (translated by Polylang). The News page is
 * the "Posts page" (Settings → Reading), created per language by the automatic setup.
 *
 * @package sees
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * News page in the current language (null when not set up).
 */
function sees_news_page(): ?WP_Post {
	$id = (int) get_option( 'page_for_posts' );
	if ( $id && function_exists( 'pll_get_post' ) ) {
		$id = (int) ( pll_get_post( $id ) ?: $id );
	}
	$page = $id ? get_post( $id ) : null;
	return ( $page && 'publish' === $page->post_status ) ? $page : null;
}

function sees_news_url(): string {
	$page = sees_news_page();
	return $page ? (string) get_permalink( $page ) : '';
}

function sees_is_news_section(): bool {
	return is_home() || is_singular( 'post' ) || is_category() || is_tag() || ( is_archive() && 'post' === get_post_type() );
}

/**
 * The first category of a post, skipping "Uncategorized".
 */
function sees_post_primary_category( WP_Post $post ): ?WP_Term {
	foreach ( get_the_category( $post->ID ) as $cat ) {
		if ( 'uncategorized' !== $cat->slug ) {
			return $cat;
		}
	}
	return null;
}

/**
 * "4 min read" estimate (about 200 words a minute).
 */
function sees_reading_time( WP_Post $post ): string {
	$words   = count( preg_split( '/\s+/u', trim( wp_strip_all_tags( $post->post_content ) ), -1, PREG_SPLIT_NO_EMPTY ) );
	$minutes = max( 1, (int) ceil( $words / 200 ) );
	/* translators: %d: minutes */
	return sprintf( _n( '%d min read', '%d min read', $minutes, 'sees' ), $minutes );
}

/**
 * Categories that have posts in the current language.
 *
 * @return WP_Term[]
 */
function sees_news_categories(): array {
	$terms = get_terms( array(
		'taxonomy'   => 'category',
		'hide_empty' => true,
		'orderby'    => 'count',
		'order'      => 'DESC',
	) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	return array_values( array_filter( $terms, fn( $t ) => 'uncategorized' !== $t->slug ) );
}

/**
 * Posts related to a post (same category, else latest).
 *
 * @return WP_Post[]
 */
function sees_related_posts( WP_Post $post, int $limit = 3 ): array {
	$cat     = sees_post_primary_category( $post );
	$related = get_posts( array(
		'post_type'      => 'post',
		'posts_per_page' => $limit,
		'post__not_in'   => array( $post->ID ),
		'category__in'   => $cat ? array( $cat->term_id ) : array(),
		'no_found_rows'  => true,
	) );
	if ( count( $related ) < $limit ) {
		$related = array_merge( $related, get_posts( array(
			'post_type'      => 'post',
			'posts_per_page' => $limit - count( $related ),
			'post__not_in'   => array_merge( array( $post->ID ), wp_list_pluck( $related, 'ID' ) ),
			'no_found_rows'  => true,
		) ) );
	}
	return $related;
}

// A corporate news section: no comments or pings (no moderation, no spam).
add_filter( 'comments_open', fn( $open, $post_id ) => 'post' === get_post_type( $post_id ) ? false : $open, 10, 2 );
add_filter( 'pings_open', fn( $open, $post_id ) => 'post' === get_post_type( $post_id ) ? false : $open, 10, 2 );

// 9 posts per News page (featured + 8), regardless of the Reading setting.
add_action( 'pre_get_posts', function ( WP_Query $q ) {
	if ( ! is_admin() && $q->is_main_query() && ( $q->is_home() || $q->is_category() || $q->is_tag() ) ) {
		$q->set( 'posts_per_page', 9 );
	}
} );
