<?php
/**
 * Template Name: About
 *
 * @package sees
 */

get_header();
the_post();

$sees_values = array(
	array( 'bolt', 'navy', sees_field( 'value_1_title', __( 'Innovation', 'sees' ) ), sees_field( 'value_1_text', __( 'Modern technology applied where it makes systems safer and smarter.', 'sees' ) ) ),
	array( 'leaf', 'petrol', sees_field( 'value_2_title', __( 'Sustainability', 'sees' ) ), sees_field( 'value_2_text', __( 'Efficient designs and renewable energy that lower long-term impact.', 'sees' ) ) ),
	array( 'shield', 'teal', sees_field( 'value_3_title', __( 'Reliability', 'sees' ) ), sees_field( 'value_3_text', __( 'Work done to code, tested, documented and delivered on schedule.', 'sees' ) ) ),
	array( 'people', 'green', sees_field( 'value_4_title', __( 'People', 'sees' ) ), sees_field( 'value_4_text', __( 'Certified teams who listen, communicate clearly and put safety first.', 'sees' ) ) ),
);
$sees_story = sees_field( 'story_text', '<p>' . esc_html__( 'SEES — Smart Electrical Engineering Solutions — is based in Al Shahamah District, Makkah. We design, install and maintain electrical systems for commercial, residential and infrastructure projects, bringing careful engineering and modern technology to every installation.', 'sees' ) . '</p>' );
?>

<main id="main">
	<?php
	get_template_part( 'template-parts/page-header', null, array(
		'subtitle'  => sees_field( 'header_subtitle', __( 'An electrical engineering company built on precision, safety and smart technology.', 'sees' ) ),
		'secondary' => sees_field( 'header_arabic' ),
	) );
	?>

	<section class="section container split">
		<div class="split__media media-frame media-frame--5x4 zoomimg reveal">
			<?php echo sees_image( sees_field( 'story_image', 0 ), 'sign.jpg', 'sees-wide', array( 'alt' => __( 'SEES signage', 'sees' ), 'sizes' => '(max-width: 900px) 100vw, 50vw' ) ); // phpcs:ignore ?>
		</div>
		<div class="split__body reveal">
			<?php sees_eyebrow( sees_field( 'story_eyebrow', __( 'Who we are', 'sees' ) ), array( 'grow' => true ) ); ?>
			<h2 class="h2"><?php echo esc_html( sees_field( 'story_title', __( 'Smart engineering for the way the region builds today.', 'sees' ) ) ); ?></h2>
			<div class="prose"><?php echo wp_kses_post( $sees_story ); ?></div>
		</div>
	</section>

	<section class="section-band">
		<div class="section container mv">
			<article class="mv__card lift reveal">
				<h2 class="mv__label"><?php esc_html_e( 'Our Mission', 'sees' ); ?></h2>
				<p class="mv__text"><?php echo esc_html( sees_field( 'mission', __( 'To deliver integrated electrical solutions that are safe, efficient and built to last.', 'sees' ) ) ); ?></p>
			</article>
			<article class="mv__card mv__card--dark lift reveal">
				<h2 class="mv__label"><?php esc_html_e( 'Our Vision', 'sees' ); ?></h2>
				<p class="mv__text"><?php echo esc_html( sees_field( 'vision', __( 'To be the region\'s most trusted partner for smart electrical engineering.', 'sees' ) ) ); ?></p>
			</article>
		</div>
	</section>

	<section class="section container">
		<div class="section-head reveal">
			<?php sees_eyebrow( sees_field( 'values_eyebrow', __( 'Our values', 'sees' ) ) ); ?>
			<h2 class="h2"><?php echo esc_html( sees_field( 'values_title', __( 'What guides every project we take on.', 'sees' ) ) ); ?></h2>
		</div>
		<div class="grid grid--values">
			<?php foreach ( $sees_values as $sees_value ) : ?>
				<article class="value-card lift reveal">
					<span class="value-card__icon ic ic--<?php echo esc_attr( $sees_value[1] ); ?>"><?php echo sees_icon( $sees_value[0], 26 ); // phpcs:ignore ?></span>
					<h3 class="value-card__title"><?php echo esc_html( $sees_value[2] ); ?></h3>
					<p class="value-card__text"><?php echo esc_html( $sees_value[3] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="tagline">
		<?php echo sees_image( sees_field( 'tagline_image', 0 ), 'pylons.jpg', 'sees-hero', array( 'class' => 'tagline__img', 'alt' => '' ) ); // phpcs:ignore ?>
		<span class="tagline__shade" aria-hidden="true"></span>
		<svg class="tagline__ring ring" aria-hidden="true" viewBox="0 0 400 400" width="520" height="520"><circle class="tagline__ring-outer" cx="200" cy="200" r="190" fill="none" stroke-width="1" stroke-dasharray="4 10"/><circle class="tagline__ring-inner" cx="200" cy="200" r="150" fill="none" stroke-width="1" stroke-dasharray="2 14"/></svg>
		<div class="tagline__body reveal">
			<?php
			foreach ( array( sees_field( 'tagline_1', 'نصمّم الراحة بهندسة ذكية' ), sees_field( 'tagline_2', 'Engineering Comfort, Perfected.' ) ) as $sees_line ) {
				printf( '<p class="tagline__line"%s>%s</p>', sees_lang_attr( $sees_line ), esc_html( $sees_line ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			?>
		</div>
	</section>

	<?php
	get_template_part( 'template-parts/cta-band', null, array(
		'style' => 'plain',
		'title' => sees_field( 'cta_title', __( 'Ready to work with SEES?', 'sees' ) ),
		'text'  => sees_field( 'cta_text', __( 'Let\'s talk about your next electrical project.', 'sees' ) ),
	) );
	?>
</main>

<?php
get_footer();
