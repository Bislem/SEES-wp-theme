<?php
/**
 * Custom post types and taxonomy.
 *
 * @package sees
 */

add_action( 'init', function () {
	$projects_public = sees_projects_public_enabled();

	register_post_type( 'solution', array(
		'labels'              => array(
			'name'          => __( 'Solutions', 'sees' ),
			'singular_name' => __( 'Solution', 'sees' ),
			'add_new_item'  => __( 'Add new solution', 'sees' ),
			'edit_item'     => __( 'Edit solution', 'sees' ),
			'all_items'     => __( 'All solutions', 'sees' ),
		),
		'public'              => true,
		'publicly_queryable'  => false, // Shown on the Our Solutions page, no single view.
		'exclude_from_search' => true,
		'has_archive'         => false,
		'rewrite'             => false,
		'show_in_rest'        => true,
		'menu_icon'           => 'dashicons-lightbulb',
		'menu_position'       => 21,
		'supports'            => array( 'title', 'excerpt', 'thumbnail', 'page-attributes' ),
	) );

	register_post_type( 'project', array(
		'labels'              => array(
			'name'          => __( 'Projects', 'sees' ),
			'singular_name' => __( 'Project', 'sees' ),
			'add_new_item'  => __( 'Add new project', 'sees' ),
			'edit_item'     => __( 'Edit project', 'sees' ),
			'all_items'     => __( 'All projects', 'sees' ),
		),
		'public'              => true,
		'publicly_queryable'  => $projects_public,
		'exclude_from_search' => ! $projects_public,
		'has_archive'         => $projects_public ? 'projects' : false,
		'rewrite'             => $projects_public ? array( 'slug' => 'projects', 'with_front' => false ) : false,
		'query_var'           => $projects_public,
		'show_ui'             => true,
		'show_in_nav_menus'   => $projects_public,
		'show_in_rest'        => true,
		'menu_icon'           => 'dashicons-portfolio',
		'menu_position'       => 22,
		'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
	) );

	register_post_type( 'faq', array(
		'labels'              => array(
			'name'          => __( 'FAQs', 'sees' ),
			'singular_name' => __( 'FAQ', 'sees' ),
			'add_new_item'  => __( 'Add new question', 'sees' ),
			'edit_item'     => __( 'Edit question', 'sees' ),
			'all_items'     => __( 'All FAQs', 'sees' ),
		),
		'public'              => true,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'rewrite'             => false,
		'show_in_rest'        => true,
		'menu_icon'           => 'dashicons-editor-help',
		'menu_position'       => 23,
		'supports'            => array( 'title', 'editor', 'page-attributes' ),
	) );

	register_post_type( 'sees_job', array(
		'labels'              => array(
			'name'               => __( 'Jobs', 'sees' ),
			'singular_name'      => __( 'Job', 'sees' ),
			'add_new_item'       => __( 'Add new job', 'sees' ),
			'edit_item'          => __( 'Edit job', 'sees' ),
			'all_items'          => __( 'All jobs', 'sees' ),
			'not_found'          => __( 'No jobs found.', 'sees' ),
			'not_found_in_trash' => __( 'No jobs found in Trash.', 'sees' ),
		),
		'public'              => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => false,
		'has_archive'         => false,
		'rewrite'             => array( 'slug' => 'jobs', 'with_front' => false ),
		'show_in_rest'        => true,
		'menu_icon'           => 'dashicons-businessperson',
		'menu_position'       => 23,
		'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
	) );

	register_taxonomy( 'project_category', 'project', array(
		'labels'             => array(
			'name'          => __( 'Project categories', 'sees' ),
			'singular_name' => __( 'Project category', 'sees' ),
		),
		'public'             => $projects_public,
		'publicly_queryable' => $projects_public,
		'hierarchical'       => true,
		'show_ui'            => true,
		'show_in_nav_menus'  => $projects_public,
		'show_in_rest'       => true,
		'show_admin_column'  => true,
		'query_var'          => $projects_public,
		'rewrite'            => $projects_public ? array( 'slug' => 'project-category', 'with_front' => false ) : false,
	) );
} );

// Show order and featured columns in admin lists.
add_filter( 'manage_solution_posts_columns', fn( $c ) => array_merge( $c, array( 'menu_order' => __( 'Order', 'sees' ) ) ) );
add_filter( 'manage_faq_posts_columns', fn( $c ) => array_merge( $c, array( 'menu_order' => __( 'Order', 'sees' ) ) ) );
add_action( 'manage_solution_posts_custom_column', 'sees_order_column', 10, 2 );
add_action( 'manage_faq_posts_custom_column', 'sees_order_column', 10, 2 );
function sees_order_column( $column, $post_id ) {
	if ( 'menu_order' === $column ) {
		echo (int) get_post_field( 'menu_order', $post_id );
	}
}

/**
 * Query helpers.
 */
function sees_get_solutions( int $limit = -1 ): array {
	return get_posts( array(
		'post_type'      => 'solution',
		'posts_per_page' => $limit,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
		'no_found_rows'  => true,
	) );
}

function sees_get_faqs(): array {
	return get_posts( array(
		'post_type'      => 'faq',
		'posts_per_page' => 50,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
		'no_found_rows'  => true,
	) );
}

function sees_get_featured_project(): ?WP_Post {
	$args = array(
		'post_type'      => 'project',
		'posts_per_page' => 1,
		'no_found_rows'  => true,
	);
	$featured = get_posts( array_merge( $args, array(
		'meta_key'   => 'is_featured', // phpcs:ignore WordPress.DB.SlowDBQuery
		'meta_value' => '1',           // phpcs:ignore WordPress.DB.SlowDBQuery
	) ) );
	if ( ! $featured ) {
		$featured = get_posts( $args );
	}
	return $featured[0] ?? null;
}

/**
 * Anchor id for a solution on the Our Solutions page.
 */
function sees_solution_anchor( WP_Post $post ): string {
	return 'solution-' . $post->post_name;
}

function sees_solution_url( WP_Post $post ): string {
	return sees_page_url( 'page-solutions.php' ) . '#' . sees_solution_anchor( $post );
}
