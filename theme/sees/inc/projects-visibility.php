<?php
/**
 * Temporary public visibility controls for Projects.
 *
 * Projects stay registered in wp-admin so their content, media and translations
 * remain editable. Change the default below to true (or use the filter) when the
 * Projects area is ready to be public again.
 *
 * @package sees
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether Projects should be visible on the public website.
 */
function sees_projects_public_enabled(): bool {
	return (bool) apply_filters( 'sees_projects_public_enabled', false );
}

/**
 * Recognise Projects links in any registered WordPress menu.
 */
function sees_is_projects_menu_item( $item ): bool {
	if ( 'project' === ( $item->object ?? '' ) || 'project_category' === ( $item->object ?? '' ) ) {
		return true;
	}

	$path = (string) wp_parse_url( (string) ( $item->url ?? '' ), PHP_URL_PATH );
	return (bool) preg_match( '#/(?:[a-z]{2}/)?(?:projects|project-category)(?:/|$)#i', $path );
}

// Hide Projects links from header, mobile, footer and any other public wp_nav_menu().
add_filter( 'wp_nav_menu_objects', function ( $items ) {
	if ( sees_projects_public_enabled() ) {
		return $items;
	}

	return array_values( array_filter( $items, fn( $item ) => ! sees_is_projects_menu_item( $item ) ) );
} );

// Keep Projects out of WordPress core XML sitemaps while hidden.
add_filter( 'wp_sitemaps_post_types', function ( $post_types ) {
	if ( ! sees_projects_public_enabled() ) {
		unset( $post_types['project'] );
	}
	return $post_types;
} );

add_filter( 'wp_sitemaps_taxonomies', function ( $taxonomies ) {
	if ( ! sees_projects_public_enabled() ) {
		unset( $taxonomies['project_category'] );
	}
	return $taxonomies;
} );

/**
 * Prevent a project from being published while the public Projects area is off.
 * This also protects against the local/demo content seeder publishing projects.
 */
add_filter( 'wp_insert_post_data', function ( $data ) {
	if (
		! sees_projects_public_enabled()
		&& 'project' === ( $data['post_type'] ?? '' )
		&& in_array( $data['post_status'] ?? '', array( 'publish', 'future' ), true )
	) {
		$data['post_status'] = 'draft';
	}

	return $data;
} );

/**
 * Draft currently public Projects once, remembering their prior states so a
 * future reactivation can restore only content that was already public.
 */
function sees_sync_projects_visibility(): void {
	$enabled = sees_projects_public_enabled();
	$state   = (string) get_option( 'sees_projects_visibility_state', '' );

	if ( ! $enabled && 'hidden' !== $state ) {
		$statuses = (array) get_option( 'sees_projects_previous_statuses', array() );
		$projects = get_posts( array(
			'post_type'      => 'project',
			'post_status'    => array( 'publish', 'future' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'lang'           => '',
			'no_found_rows'  => true,
		) );

		foreach ( $projects as $project_id ) {
			$statuses[ (int) $project_id ] = get_post_status( $project_id );
			wp_update_post( array( 'ID' => (int) $project_id, 'post_status' => 'draft' ) );
		}

		update_option( 'sees_projects_previous_statuses', $statuses, false );
		update_option( 'sees_projects_visibility_state', 'hidden', false );
		flush_rewrite_rules( false );
		return;
	}

	if ( $enabled && 'hidden' === $state ) {
		foreach ( (array) get_option( 'sees_projects_previous_statuses', array() ) as $project_id => $status ) {
			if ( get_post( (int) $project_id ) && 'draft' === get_post_status( (int) $project_id ) ) {
				wp_update_post( array( 'ID' => (int) $project_id, 'post_status' => $status ) );
			}
		}

		delete_option( 'sees_projects_previous_statuses' );
		update_option( 'sees_projects_visibility_state', 'enabled', false );
		flush_rewrite_rules( false );
	}
}
add_action( 'init', 'sees_sync_projects_visibility', 99 );

/**
 * Return a real 404 for legacy archive, taxonomy or single URLs that may still
 * exist in cached rewrite rules.
 */
add_action( 'template_redirect', function () {
	if ( sees_projects_public_enabled() ) {
		return;
	}

	$is_project_request = is_singular( 'project' )
		|| is_post_type_archive( 'project' )
		|| is_tax( 'project_category' )
		|| 'project' === get_query_var( 'post_type' )
		|| (bool) get_query_var( 'project' );

	if ( ! $is_project_request ) {
		return;
	}

	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();
	include get_query_template( '404' );
	exit;
}, 0 );
