<?php
/**
 * Menu fallbacks and nav link classes.
 *
 * @package sees
 */

/**
 * Pages linked when no menu is assigned yet.
 *
 * @return array<int,array{url:string,label:string,current:bool}>
 */
function sees_default_nav_items(): array {
	$items = array(
		array( 'url' => function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' ), 'label' => __( 'Home', 'sees' ), 'current' => is_front_page() ),
		array( 'url' => sees_page_url( 'page-about.php' ), 'label' => __( 'About', 'sees' ), 'current' => is_page_template( 'page-about.php' ) ),
		array( 'url' => sees_page_url( 'page-solutions.php' ), 'label' => __( 'Our Solutions', 'sees' ), 'current' => is_page_template( 'page-solutions.php' ) ),
		array( 'url' => (string) get_post_type_archive_link( 'project' ), 'label' => __( 'Projects', 'sees' ), 'current' => sees_is_projects_section() ),
		array( 'url' => sees_page_url( 'page-contact.php' ), 'label' => __( 'Contact', 'sees' ), 'current' => is_page_template( 'page-contact.php' ) ),
	);
	return $items;
}

function sees_is_projects_section(): bool {
	return is_post_type_archive( 'project' ) || is_singular( 'project' ) || is_tax( 'project_category' );
}

function sees_render_list( array $items, string $class, string $link_class = '' ): void {
	echo '<ul class="' . esc_attr( $class ) . '">';
	foreach ( $items as $item ) {
		printf(
			'<li%1$s><a href="%2$s"%3$s%4$s>%5$s</a></li>',
			! empty( $item['current'] ) ? ' class="current-menu-item"' : '',
			esc_url( $item['url'] ),
			$link_class ? ' class="' . esc_attr( $link_class ) . '"' : '',
			! empty( $item['current'] ) ? ' aria-current="page"' : '',
			esc_html( $item['label'] )
		);
	}
	echo '</ul>';
}

function sees_menu_fallback(): void {
	sees_render_list( sees_default_nav_items(), 'primary-nav__list' );
}

function sees_footer_company_fallback(): void {
	$items = sees_default_nav_items();
	sees_render_list( array( $items[1], $items[3], $items[4] ), 'footer-col__list' );
}

function sees_footer_solutions_fallback(): void {
	$items = array();
	foreach ( sees_get_solutions( 3 ) as $solution ) {
		$items[] = array( 'url' => sees_solution_url( $solution ), 'label' => get_the_title( $solution ) );
	}
	sees_render_list( $items, 'footer-col__list' );
}

// Highlight "Projects" in the menu on single projects and category archives.
add_filter( 'nav_menu_css_class', function ( $classes, $item ) {
	if ( sees_is_projects_section() && untrailingslashit( $item->url ) === untrailingslashit( (string) get_post_type_archive_link( 'project' ) ) ) {
		$classes[] = 'current-menu-item';
	}
	return array_unique( $classes );
}, 10, 2 );
