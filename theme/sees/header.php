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
<meta name="color-scheme" content="light dark">
<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#0a1520" media="(prefers-color-scheme: dark)">
<script>
(function (d) {
	d.classList.add('js');
	// Theme before first paint: saved choice, otherwise the system setting.
	var saved = null;
	try { saved = localStorage.getItem('sees-theme'); } catch (e) {}
	var dark = saved ? saved === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
	d.setAttribute('data-theme', dark ? 'dark' : 'light');
}(document.documentElement));
</script>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'sees' ); ?></a>

<header class="site-header" id="site-header">
	<?php
	$sees_info    = sees_contact();
	$sees_support = sees_page_by_template( 'page-support.php' );
	$sees_careers = sees_page_by_template( 'page-jobs.php' );
	?>
	<div class="topbar">
		<div class="container topbar__inner">
			<ul class="topbar__contact" aria-label="<?php esc_attr_e( 'Contact information', 'sees' ); ?>">
				<li><a href="<?php echo esc_url( sees_tel( $sees_info['phone'] ) ); ?>"><?php echo sees_icon( 'phone', 15, array( 'stroke' => 1.7 ) ); // phpcs:ignore ?><span dir="ltr"><?php echo esc_html( $sees_info['phone'] ); ?></span></a></li>
				<li><a href="<?php echo esc_url( 'mailto:' . $sees_info['email'] ); ?>"><?php echo sees_icon( 'mail', 15, array( 'stroke' => 1.7 ) ); // phpcs:ignore ?><span class="topbar__label"><?php echo esc_html( $sees_info['email'] ); ?></span></a></li>
				<li class="topbar__wide"><?php echo sees_icon( 'pin', 15, array( 'stroke' => 1.7 ) ); // phpcs:ignore ?><span><?php echo esc_html( $sees_info['address'] ); ?></span></li>
				<?php if ( false === strpos( $sees_info['hours'], '[' ) ) : // Hide until real hours are set. ?>
					<li class="topbar__wide"><?php echo sees_icon( 'clock', 15, array( 'stroke' => 1.7 ) ); // phpcs:ignore ?><span><?php echo esc_html( $sees_info['hours'] ); ?></span></li>
				<?php endif; ?>
			</ul>
			<div class="topbar__end">
			<ul class="topbar__links">
				<?php if ( $sees_support ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( $sees_support ) ); ?>"><?php echo sees_icon( 'download', 15, array( 'stroke' => 1.7 ) ); // phpcs:ignore ?><span><?php echo esc_html( get_the_title( $sees_support ) ); ?></span></a></li>
				<?php endif; ?>
				<?php if ( $sees_careers ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( $sees_careers ) ); ?>"><?php echo sees_icon( 'people', 15, array( 'stroke' => 1.7 ) ); // phpcs:ignore ?><span><?php echo esc_html( get_the_title( $sees_careers ) ); ?></span></a></li>
				<?php endif; ?>
			</ul>
			<?php sees_social_list( 'topbar__social', 15 ); ?>
			</div>
		</div>
	</div>
	<div class="container site-header__bar a-up">
		<a class="brand" href="<?php echo esc_url( function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' ) ); ?>" rel="home">
			<?php
			if ( has_custom_logo() ) {
				$logo_id = (int) get_theme_mod( 'custom_logo' );
				echo wp_get_attachment_image( $logo_id, 'medium', false, array( 'class' => 'brand__logo', 'alt' => get_bloginfo( 'name' ), 'loading' => 'eager' ) );
			} else {
				// Navy-text logo for light mode, white-text logo for dark mode (CSS shows one).
				echo sees_image( 0, 'logo-light.png', 'medium', array( // phpcs:ignore WordPress.Security.EscapeOutput -- built with esc_* in sees_image().
					'class'   => 'brand__logo brand__logo--on-light',
					'alt'     => __( 'SEES — Smart Electrical Engineering Solutions', 'sees' ),
					'loading' => 'eager',
				) );
				echo sees_image( 0, 'logo-dark.png', 'medium', array( // phpcs:ignore WordPress.Security.EscapeOutput
					'class'   => 'brand__logo brand__logo--on-dark',
					'alt'     => __( 'SEES — Smart Electrical Engineering Solutions', 'sees' ),
					'loading' => 'eager',
				) );
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
			<?php if ( $sees_support ) : // The top-bar links are hidden on phones, so the menu carries Support. ?>
				<ul class="primary-nav__extra">
					<li<?php echo is_page( $sees_support->ID ) ? ' class="current-menu-item"' : ''; ?>>
						<a href="<?php echo esc_url( get_permalink( $sees_support ) ); ?>"<?php echo is_page( $sees_support->ID ) ? ' aria-current="page"' : ''; ?>><?php echo sees_icon( 'download', 18, array( 'stroke' => 1.7 ) ); // phpcs:ignore ?><span><?php echo esc_html( get_the_title( $sees_support ) ); ?></span></a>
					</li>
				</ul>
			<?php endif; ?>
			<?php sees_social_list( 'primary-nav__social', 18 ); // Phones and tablets: the top bar hides its social icons. ?>
			<div class="primary-nav__search"><?php get_search_form(); ?></div>
			<button type="button" class="btn btn--outline theme-toggle primary-nav__theme" aria-pressed="false">
				<?php echo sees_icon( 'moon', 18, array( 'stroke' => 1.7 ) ); // phpcs:ignore ?>
				<?php echo sees_icon( 'sun', 18, array( 'stroke' => 1.7 ) ); // phpcs:ignore ?>
				<span><?php esc_html_e( 'Dark mode', 'sees' ); ?></span>
			</button>
			<a class="btn btn--primary arrow primary-nav__cta" href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Get in Touch', 'sees' ); ?> <?php echo sees_icon( 'arrow', 18, array( 'stroke' => 2 ) ); // phpcs:ignore ?></a>
		</nav>

		<div class="header-tools">
			<button type="button" class="icon-btn search-toggle" aria-expanded="false" aria-controls="site-search" aria-label="<?php esc_attr_e( 'Search', 'sees' ); ?>">
				<?php echo sees_icon( 'search', 20, array( 'stroke' => 2 ) ); // phpcs:ignore ?>
			</button>
			<button type="button" class="icon-btn theme-toggle header-tools__theme" aria-pressed="false" aria-label="<?php esc_attr_e( 'Dark mode', 'sees' ); ?>">
				<?php echo sees_icon( 'moon', 20, array( 'stroke' => 1.7 ) ); // phpcs:ignore ?>
				<?php echo sees_icon( 'sun', 20, array( 'stroke' => 1.7 ) ); // phpcs:ignore ?>
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
