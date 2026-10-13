<?php
/**
 * Template Name: Contact
 *
 * @package sees
 */

get_header();
the_post();

$sees_contact   = sees_contact();
// Map: the pasted embed (exact office pin) if set, otherwise the city.
$sees_map       = sees_map_src( (string) sees_field( 'map_url' ) ) ?: sees_map_embed_for( __( 'Makkah, Saudi Arabia', 'sees' ) );
$sees_map_link  = sees_map_link( (string) sees_field( 'map_link' ) );
$sees_shortcode = trim( (string) sees_field( 'form_shortcode' ) );
?>

<main id="main">
	<?php
	get_template_part( 'template-parts/page-header', null, array(
		'subtitle' => sees_field( 'header_subtitle', __( 'Tell us about your project — an engineer will get back to you with next steps.', 'sees' ) ),
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

			<figure class="map reveal">
				<iframe src="<?php echo esc_url( $sees_map ); ?>" title="<?php esc_attr_e( 'Map: SEES office location', 'sees' ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
				<figcaption class="map__bar">
					<span class="map__address"><?php echo sees_icon( 'pin', 18, array( 'stroke' => 1.7 ) ); // phpcs:ignore ?><?php echo esc_html( $sees_contact['address'] ); ?></span>
					<?php if ( $sees_map_link ) : ?>
						<a class="btn btn--primary map__btn arrow" href="<?php echo esc_url( $sees_map_link ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Get directions', 'sees' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(opens Google Maps)', 'sees' ); ?></span> <?php echo sees_icon( 'arrow', 16, array( 'stroke' => 1.7 ) ); // phpcs:ignore ?></a>
					<?php endif; ?>
				</figcaption>
			</figure>
		</div>

		<div class="contact__form a-up d3" data-contact-form data-form-host>
			<div class="contact__form-inner" data-form-panel>
				<h2 class="contact__title"><?php echo esc_html( sees_field( 'form_title', __( 'Send us a message', 'sees' ) ) ); ?></h2>
				<p class="contact__note"><?php echo esc_html( sees_field( 'form_text', __( 'Fields marked * are required.', 'sees' ) ) ); ?></p>
				<?php
				if ( $sees_shortcode && shortcode_exists( 'contact-form-7' ) ) {
					echo do_shortcode( $sees_shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput -- CF7 output.
				} elseif ( current_user_can( 'edit_pages' ) ) {
					echo '<p class="notice">' . esc_html__( 'Add a Contact Form 7 shortcode in this page\'s "Form" settings.', 'sees' ) . '</p>';
				}
				?>
			</div>
			<?php
			get_template_part( 'template-parts/form-success', null, array(
				'title' => sees_field( 'success_title', __( 'Thank you!', 'sees' ) ),
				'text'  => sees_field( 'success_text', __( 'Your message has been sent. Our team will get back to you shortly.', 'sees' ) ),
				'reset' => __( 'Send another message', 'sees' ),
			) );
			?>
		</div>
	</section>
</main>

<?php
get_footer();
