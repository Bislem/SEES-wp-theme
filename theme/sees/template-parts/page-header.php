<?php
/**
 * Inner page header: breadcrumb, title and intro on a light brand gradient
 * with decorative circuit lines and a faint SEES mark (no photo).
 *
 * @package sees
 *
 * @var array $args {
 *     @type string $title      Page title.
 *     @type string $subtitle   Intro line.
 *     @type string $secondary  Optional extra line in brand blue.
 *     @type array  $chips      Optional [ [url, label], … ] jump links.
 *     @type array  $parent     Optional [ url, label ] breadcrumb parent.
 *     @type bool   $crumbs     Show breadcrumb (default true).
 * }
 */

$sees_args = wp_parse_args( $args, array(
	'title'     => get_the_title(),
	'subtitle'  => '',
	'secondary' => '',
	'chips'     => array(),
	'crumbs'    => true,
) );
?>
<section class="page-header">
	<div class="page-header__decor" aria-hidden="true">
		<svg class="page-header__circuit" viewBox="0 0 640 400" preserveAspectRatio="xMaxYMid slice" focusable="false">
			<defs>
				<linearGradient id="sees-ph-grad" x1="0" y1="0" x2="1" y2="0">
					<stop offset="0" class="page-header__stop-1"/>
					<stop offset=".5" class="page-header__stop-2"/>
					<stop offset="1" class="page-header__stop-3"/>
				</linearGradient>
			</defs>
			<g class="page-header__lines">
				<path d="M640 64H486a16 16 0 0 0-16 16v62a16 16 0 0 1-16 16H330"/>
				<path d="M640 124H556a16 16 0 0 0-16 16v104a16 16 0 0 0 16 16h84"/>
				<path d="M640 206h-22v114H430a16 16 0 0 1-16-16v-40"/>
				<path d="M372 400v-50a16 16 0 0 1 16-16h96"/>
				<path d="M520 0v22a16 16 0 0 1-16 16H400"/>
			</g>
			<path class="page-header__line-accent" d="M640 64H486a16 16 0 0 0-16 16v62a16 16 0 0 1-16 16H330"/>
			<g class="page-header__nodes">
				<circle cx="330" cy="158" r="5"/>
				<circle cx="414" cy="264" r="5"/>
				<circle cx="484" cy="334" r="5"/>
				<circle cx="400" cy="38" r="5"/>
				<circle cx="618" cy="206" r="4"/>
			</g>
		</svg>
		<?php echo sees_image( 0, 'logo-mark.png', 'medium', array( 'class' => 'page-header__mark', 'alt' => '', 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>

	<div class="container page-header__body">
		<?php if ( $sees_args['crumbs'] ) : ?>
			<nav class="breadcrumb a-up d1" aria-label="<?php esc_attr_e( 'Breadcrumb', 'sees' ); ?>">
				<a href="<?php echo esc_url( function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'sees' ); ?></a>
				<span aria-hidden="true">/</span>
				<?php if ( ! empty( $sees_args['parent'] ) ) : ?>
					<a href="<?php echo esc_url( $sees_args['parent'][0] ); ?>"><?php echo esc_html( $sees_args['parent'][1] ); ?></a>
					<span aria-hidden="true">/</span>
				<?php endif; ?>
				<span aria-current="page"><?php echo esc_html( wp_strip_all_tags( $sees_args['title'] ) ); ?></span>
			</nav>
		<?php endif; ?>

		<h1 class="page-header__title a-up d2"><?php echo esc_html( wp_strip_all_tags( $sees_args['title'] ) ); ?></h1>

		<?php if ( $sees_args['subtitle'] ) : ?>
			<p class="page-header__lead a-up d3"><?php echo esc_html( $sees_args['subtitle'] ); ?></p>
		<?php endif; ?>

		<?php if ( $sees_args['secondary'] ) : ?>
			<p class="page-header__secondary a-up d4"<?php echo sees_lang_attr( $sees_args['secondary'] ); // phpcs:ignore ?>><?php echo esc_html( $sees_args['secondary'] ); ?></p>
		<?php endif; ?>

		<?php if ( $sees_args['chips'] ) : ?>
			<ul class="chips a-up d4">
				<?php foreach ( $sees_args['chips'] as $chip ) : ?>
					<li><a class="chip" href="<?php echo esc_url( $chip[0] ); ?>"><?php echo esc_html( $chip[1] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
