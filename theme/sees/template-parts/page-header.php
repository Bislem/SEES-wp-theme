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
		<?php // Generated PCB-style wires: each has a dim trace + a moving current pulse (timings vary per wire). ?>
		<svg class="page-header__circuit" viewBox="0 0 640 400" preserveAspectRatio="xMaxYMid slice" focusable="false" aria-hidden="true">
			<defs>
				<linearGradient id="sees-current" gradientUnits="userSpaceOnUse" x1="640" y1="0" x2="220" y2="0">
					<stop offset="0" class="circuit__stop-1"/>
					<stop offset=".55" class="circuit__stop-2"/>
					<stop offset="1" class="circuit__stop-3"/>
				</linearGradient>
			</defs>
			<g class="circuit__wires">
				<path d="M640 16H493l-14 14H415l-14 -14H350"/>
				<path class="circuit__opt" d="M640 44H551l-14 14H437"/>
				<path d="M640 72H565l-14 14H472"/>
				<path d="M640 100H595l-14 14H313l-14 -14H259"/>
				<path class="circuit__opt" d="M640 128H499l-14 14H453"/>
				<path d="M640 156H545l-14 14H398"/>
				<path d="M640 212H408l-14 14H342"/>
				<path d="M640 240H352l-14 14H282"/>
				<path class="circuit__opt" d="M640 268H569l-14 14H410l-14 -14H333"/>
				<path d="M640 296H560l-14 14H342"/>
				<path d="M640 324H599l-14 14H503"/>
				<path class="circuit__opt" d="M640 352H387l-14 14H287l-14 -14H215"/>
				<path d="M640 380H566l-14 14H500"/>
			</g>
			<g class="circuit__currents">
				<path class="circuit__current" d="M640 16H493l-14 14H415l-14 -14H350" pathLength="100" style="--dur:4.42s;--delay:-0.32s"/>
				<path class="circuit__current circuit__opt" d="M640 44H551l-14 14H437" pathLength="100" style="--dur:2.76s;--delay:-1.4s"/>
				<path class="circuit__current" d="M640 72H565l-14 14H472" pathLength="100" style="--dur:2.8s;--delay:-0.25s"/>
				<path class="circuit__current" d="M640 100H595l-14 14H313l-14 -14H259" pathLength="100" style="--dur:4.37s;--delay:-2.55s"/>
				<path class="circuit__current circuit__opt" d="M640 128H499l-14 14H453" pathLength="100" style="--dur:5.33s;--delay:-0.25s"/>
				<path class="circuit__current" d="M640 156H545l-14 14H398" pathLength="100" style="--dur:3.0s;--delay:-0.35s"/>
				<path class="circuit__current" d="M640 212H408l-14 14H342" pathLength="100" style="--dur:4.23s;--delay:-2.7s"/>
				<path class="circuit__current" d="M640 240H352l-14 14H282" pathLength="100" style="--dur:4.33s;--delay:-2.15s"/>
				<path class="circuit__current circuit__opt" d="M640 268H569l-14 14H410l-14 -14H333" pathLength="100" style="--dur:5.19s;--delay:-1.88s"/>
				<path class="circuit__current" d="M640 296H560l-14 14H342" pathLength="100" style="--dur:4.78s;--delay:-0.39s"/>
				<path class="circuit__current" d="M640 324H599l-14 14H503" pathLength="100" style="--dur:3.56s;--delay:-1.6s"/>
				<path class="circuit__current circuit__opt" d="M640 352H387l-14 14H287l-14 -14H215" pathLength="100" style="--dur:3.77s;--delay:-2.85s"/>
				<path class="circuit__current" d="M640 380H566l-14 14H500" pathLength="100" style="--dur:2.71s;--delay:-1.81s"/>
			</g>
			<g class="circuit__vias">
				<circle cx="600" cy="16" r="3"/>
				<circle cx="603" cy="72" r="3"/>
				<circle cx="604" cy="128" r="3"/>
				<circle cx="623" cy="184" r="3"/>
				<circle cx="618" cy="240" r="3"/>
				<circle cx="568" cy="296" r="3"/>
				<circle cx="571" cy="352" r="3"/>
			</g>
			<g class="circuit__nodes">
				<circle class="circuit__node" cx="350" cy="16" r="4.5" style="--delay:-1.61s"/>
				<circle class="circuit__node circuit__opt" cx="437" cy="58" r="4.5" style="--delay:-0.11s"/>
				<circle class="circuit__node" cx="472" cy="86" r="4.5" style="--delay:-1.27s"/>
				<circle class="circuit__node" cx="259" cy="100" r="4.5" style="--delay:-0.19s"/>
				<circle class="circuit__node circuit__opt" cx="453" cy="142" r="4.5" style="--delay:-2.58s"/>
				<circle class="circuit__node" cx="398" cy="170" r="4.5" style="--delay:-0.93s"/>
				<circle class="circuit__node" cx="342" cy="226" r="4.5" style="--delay:-1.12s"/>
				<circle class="circuit__node" cx="282" cy="254" r="4.5" style="--delay:-1.6s"/>
				<circle class="circuit__node circuit__opt" cx="333" cy="268" r="4.5" style="--delay:-0.75s"/>
				<circle class="circuit__node" cx="342" cy="310" r="4.5" style="--delay:-0.9s"/>
				<circle class="circuit__node" cx="503" cy="338" r="4.5" style="--delay:-1.83s"/>
				<circle class="circuit__node circuit__opt" cx="215" cy="352" r="4.5" style="--delay:-0.46s"/>
				<circle class="circuit__node" cx="500" cy="394" r="4.5" style="--delay:-2.29s"/>
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
