<?php
/**
 * Inner page header: breadcrumb, title, intro and a photo fading in from the side.
 *
 * @package sees
 *
 * @var array $args {
 *     @type string $title      Page title.
 *     @type string $subtitle   Intro line.
 *     @type string $secondary  Optional extra line in brand blue.
 *     @type int    $image      Attachment ID (optional).
 *     @type string $fallback   Bundled image in assets/img.
 *     @type string $position   CSS object-position for the photo.
 *     @type array  $chips      Optional [ [url, label], … ] jump links.
 *     @type bool   $crumbs     Show breadcrumb (default true).
 * }
 */

$sees_args = wp_parse_args( $args, array(
	'title'     => get_the_title(),
	'subtitle'  => '',
	'secondary' => '',
	'image'     => 0,
	'fallback'  => 'sky.jpg',
	'position'  => 'center',
	'chips'     => array(),
	'crumbs'    => true,
) );
?>
<section class="page-header">
	<div class="page-header__media" aria-hidden="true">
		<?php
		echo sees_image( $sees_args['image'], $sees_args['fallback'], 'sees-hero', array( // phpcs:ignore WordPress.Security.EscapeOutput
			'class'   => 'page-header__img a-zoom',
			'alt'     => '',
			'loading' => 'eager',
			'sizes'   => '(max-width: 760px) 100vw, 62vw',
			'style'   => 'object-position:' . $sees_args['position'],
		) );
		?>
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
