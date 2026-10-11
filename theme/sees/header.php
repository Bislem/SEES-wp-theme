<?php
/**
 * Site header: document head, skip link and the main navigation bar.
 * The navigation floats over each page's header (template-parts/page-header.php).
 *
 * @package sees
 */

$contact_url = sees_page_url( 'page-contact.php' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script>document.documentElement.classList.add('js');</script>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'sees' ); ?></a>

<header class="site-header" id="site-header">
	<div class="container site-header__bar a-up">
		<a class="brand" href="<?php echo esc_url( function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' ) ); ?>" rel="home">
			<?php
			if ( has_custom_logo() ) {
				$logo_id = (int) get_theme_mod( 'custom_logo' );
				echo wp_get_attachment_image( $logo_id, 'medium', false, array( 'class' => 'brand__logo', 'alt' => get_bloginfo( 'name' ), 'loading' => 'eager' ) );
			} else {
				echo sees_image( 0, 'logo-light.png', 'medium', array(
					'class'   => 'brand__logo',
					'alt'     => __( 'SEES — Smart Electrical Engineering Solutions', 'sees' ),
					'loading' => 'eager',
				) ); // phpcs:ignore WordPress.Security.EscapeOutput -- built with esc_* in sees_image().
			}
			?>
		</a>

		<nav class="primary-nav" id="primary-nav" aria-label="<?php esc_attr_e( 'Main', 'sees' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'primary-nav__list',
				'depth'          => 1,
				'fallback_cb'    => 'sees_menu_fallback',
			) );
			?>
			<div class="primary-nav__search"><?php get_search_form(); ?></div>
			<a class="btn btn--primary arrow primary-nav__cta" href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Get in Touch', 'sees' ); ?> <?php echo sees_icon( 'arrow', 18, array( 'stroke' => 2 ) ); // phpcs:ignore ?></a>
		</nav>

		<div class="header-tools">
			<button type="button" class="icon-btn search-toggle" aria-expanded="false" aria-controls="site-search" aria-label="<?php esc_attr_e( 'Search', 'sees' ); ?>">
				<?php echo sees_icon( 'search', 20, array( 'stroke' => 2 ) ); // phpcs:ignore ?>
			</button>
			<?php sees_language_switcher(); ?>
			<a class="btn btn--primary arrow header-tools__cta" href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Get in Touch', 'sees' ); ?> <?php echo sees_icon( 'arrow', 18, array( 'stroke' => 2 ) ); // phpcs:ignore ?></a>
			<button type="button" class="icon-btn nav-toggle" aria-expanded="false" aria-controls="primary-nav" aria-label="<?php esc_attr_e( 'Menu', 'sees' ); ?>">
				<?php echo sees_icon( 'menu', 24, array( 'stroke' => 2, 'class' => 'nav-toggle__open' ) ); // phpcs:ignore ?>
				<?php echo sees_icon( 'close', 24, array( 'stroke' => 2, 'class' => 'nav-toggle__close' ) ); // phpcs:ignore ?>
			</button>
		</div>
	</div>

	<div class="site-search" id="site-search" hidden>
		<div class="container">
			<?php get_search_form(); ?>
		</div>
	</div>
</header>
