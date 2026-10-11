<?php
/**
 * Call-to-action band. Three looks from the design:
 *  - photo: dark photo card with gradient (Home, Projects)
 *  - brand: petrol-blue card (Solutions)
 *  - plain: title + button on white (About)
 *
 * @package sees
 *
 * @var array $args { style, kicker, title, text, button, url, image, phone (bool) }
 */

$sees_args = wp_parse_args( $args, array(
	'style'  => 'photo',
	'kicker' => '',
	'title'  => '',
	'text'   => '',
	'button' => __( 'Get in Touch', 'sees' ),
	'url'    => sees_page_url( 'page-contact.php' ),
	'image'  => 0,
	'phone'  => false,
) );
$sees_style = in_array( $sees_args['style'], array( 'photo', 'brand', 'plain' ), true ) ? $sees_args['style'] : 'photo';
// On dark/brand backgrounds the primary action is green with ink text; on white it is navy.
$sees_btn   = 'plain' === $sees_style ? 'btn--primary' : 'btn--green';
?>
<section class="cta cta--<?php echo esc_attr( $sees_style ); ?>">
	<div class="container">
		<div class="cta__box reveal">
			<?php if ( 'photo' === $sees_style ) : ?>
				<div class="cta__media" aria-hidden="true">
					<?php echo sees_image( $sees_args['image'], 'facade.jpg', 'sees-hero', array( 'alt' => '', 'sizes' => '(max-width: 1360px) 100vw, 1280px' ) ); // phpcs:ignore ?>
				</div>
			<?php endif; ?>
			<div class="cta__body">
				<?php if ( $sees_args['kicker'] ) : ?>
					<p class="cta__kicker"<?php echo sees_lang_attr( $sees_args['kicker'] ); // phpcs:ignore ?>><?php echo esc_html( $sees_args['kicker'] ); ?></p>
				<?php endif; ?>
				<h2 class="cta__title"><?php echo esc_html( $sees_args['title'] ); ?></h2>
				<?php if ( $sees_args['text'] ) : ?>
					<p class="cta__text"><?php echo esc_html( $sees_args['text'] ); ?></p>
				<?php endif; ?>
				<?php if ( 'plain' !== $sees_style ) : ?>
					<div class="btn-row">
						<a class="btn <?php echo esc_attr( $sees_btn ); ?> arrow" href="<?php echo esc_url( $sees_args['url'] ); ?>"><?php echo esc_html( $sees_args['button'] ); ?> <?php echo sees_icon( 'arrow', 16, array( 'stroke' => 2 ) ); // phpcs:ignore ?></a>
						<?php if ( $sees_args['phone'] ) : ?>
							<?php $sees_phone = sees_contact()['phone']; ?>
							<a class="btn btn--ghost-light" href="<?php echo esc_url( sees_tel( $sees_phone ) ); ?>" dir="ltr"><?php echo esc_html( $sees_phone ); ?></a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
			<?php if ( 'plain' === $sees_style ) : ?>
				<a class="btn btn--primary btn--lg arrow" href="<?php echo esc_url( $sees_args['url'] ); ?>"><?php echo esc_html( $sees_args['button'] ); ?> <?php echo sees_icon( 'arrow', 18, array( 'stroke' => 2 ) ); // phpcs:ignore ?></a>
			<?php endif; ?>
		</div>
	</div>
</section>
