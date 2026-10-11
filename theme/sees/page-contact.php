<?php
/**
 * Template Name: Contact
 *
 * @package sees
 */

get_header();
the_post();

$sees_contact   = sees_contact();
$sees_map       = sees_map_src( (string) sees_field( 'map_url' ) );
$sees_shortcode = trim( (string) sees_field( 'form_shortcode' ) );
?>

<main id="main">
	<?php
	get_template_part( 'template-parts/page-header', null, array(
		'subtitle' => sees_field( 'header_subtitle', __( 'Tell us about your project — an engineer will get back to you with next steps.', 'sees' ) ),
		'image'    => sees_field( 'header_image', 0 ),
		'fallback' => 'sign.jpg',
		'position' => 'center 45%',
	) );
	?>

	<section class="section section--tight-top container contact">
		<div class="contact__info">
			<a class="info-card a-up d2" href="<?php echo esc_url( sees_tel( $sees_contact['phone'] ) ); ?>">
				<span class="info-card__icon ic ic--navy"><?php echo sees_icon( 'phone', 24, array( 'stroke' => 1.7 ) ); // phpcs:ignore ?></span>
				<span class="info-card__text"><span class="info-card__label"><?php esc_html_e( 'Call us', 'sees' ); ?></span><strong dir="ltr"><?php echo esc_html( $sees_contact['phone'] ); ?></strong></span>
			</a>
			<a class="info-card a-up d3" href="<?php echo esc_url( 'mailto:' . $sees_contact['email'] ); ?>">
				<span class="info-card__icon ic ic--petrol"><?php echo sees_icon( 'mail', 24, array( 'stroke' => 1.7 ) ); // phpcs:ignore ?></span>
				<span class="info-card__text"><span class="info-card__label"><?php esc_html_e( 'Email', 'sees' ); ?></span><strong><?php echo esc_html( $sees_contact['email'] ); ?></strong></span>
			</a>
			<div class="info-card a-up d4">
				<span class="info-card__icon ic ic--teal"><?php echo sees_icon( 'pin', 24, array( 'stroke' => 1.7 ) ); // phpcs:ignore ?></span>
				<span class="info-card__text"><span class="info-card__label"><?php esc_html_e( 'Visit', 'sees' ); ?></span><strong><?php echo esc_html( $sees_contact['address'] ); ?></strong></span>
			</div>
			<div class="info-card a-up d5">
				<span class="info-card__icon ic ic--green"><?php echo sees_icon( 'clock', 24, array( 'stroke' => 1.7 ) ); // phpcs:ignore ?></span>
				<span class="info-card__text"><span class="info-card__label"><?php esc_html_e( 'Working hours', 'sees' ); ?></span><strong><?php echo esc_html( $sees_contact['hours'] ); ?></strong></span>
			</div>

			<div class="map reveal">
				<?php if ( $sees_map ) : ?>
					<iframe src="<?php echo esc_url( $sees_map ); ?>" title="<?php esc_attr_e( 'Map: SEES office location', 'sees' ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
				<?php else : ?>
					<svg class="map__roads" aria-hidden="true" viewBox="0 0 400 250" preserveAspectRatio="none"><path d="M0 70 L400 40 M0 160 L400 200 M120 0 L160 250 M280 0 L250 250 M0 230 Q200 120 400 120" stroke-width="10" fill="none"/></svg>
					<span class="map__pin" aria-hidden="true"><span class="map__ping ping"></span></span>
					<span class="map__label"><?php echo esc_html( '[' . __( 'MAP', 'sees' ) . ' — ' . $sees_contact['address'] . ']' ); ?></span>
				<?php endif; ?>
			</div>
		</div>

		<div class="contact__form a-up d3" data-contact-form>
			<div class="contact__form-inner" data-form-panel>
				<h2 class="contact__title"><?php echo esc_html( sees_field( 'form_title', __( 'Request a quote', 'sees' ) ) ); ?></h2>
				<p class="contact__note"><?php echo esc_html( sees_field( 'form_text', __( 'Fields marked * are required.', 'sees' ) ) ); ?></p>
				<?php
				if ( $sees_shortcode && shortcode_exists( 'contact-form-7' ) ) {
					echo do_shortcode( $sees_shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput -- CF7 output.
				} elseif ( current_user_can( 'edit_pages' ) ) {
					echo '<p class="notice">' . esc_html__( 'Add a Contact Form 7 shortcode in this page\'s "Form" settings.', 'sees' ) . '</p>';
				}
				?>
			</div>
			<div class="contact__success" data-form-success role="status" hidden tabindex="-1">
				<span class="contact__success-icon pop" aria-hidden="true"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path class="draw" d="M5 12l5 5 9-10"/></svg></span>
				<h2 class="contact__title"><?php echo esc_html( sees_field( 'success_title', __( 'Thank you!', 'sees' ) ) ); ?></h2>
				<p class="contact__note"><?php echo esc_html( sees_field( 'success_text', __( 'Your request has been sent. A SEES engineer will contact you shortly.', 'sees' ) ) ); ?></p>
				<button type="button" class="btn btn--outline" data-form-reset><?php esc_html_e( 'Send another request', 'sees' ); ?></button>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
