<?php
/**
 * "Message sent" panel shown in place of a Contact Form 7 form after a successful submission
 * (see the form section in assets/js/main.js). Put it next to a [data-form-panel] inside a
 * [data-form-host] element.
 *
 * @package sees
 *
 * @var array $args {
 *     title, text,
 *     use_response (bool) replace the text with the form's own success message (CF7 → Messages),
 *     reset (string) label of a "send another" button, link_url + link_label for a link instead/as well.
 * }
 */

$sees_args = wp_parse_args( $args, array(
	'title'        => __( 'Thank you!', 'sees' ),
	'text'         => '',
	'use_response' => false,
	'reset'        => '',
	'link_url'     => '',
	'link_label'   => '',
) );
?>
<div class="form-success" data-form-success role="status" aria-live="polite" hidden tabindex="-1">
	<span class="form-success__icon pop" aria-hidden="true"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path class="draw" d="M5 12l5 5 9-10"/></svg></span>
	<h2 class="form-success__title"><?php echo esc_html( $sees_args['title'] ); ?></h2>
	<p class="form-success__text"<?php echo $sees_args['use_response'] ? ' data-form-message' : ''; ?>><?php echo esc_html( $sees_args['text'] ); ?></p>
	<?php if ( $sees_args['reset'] || $sees_args['link_url'] ) : ?>
		<div class="form-success__actions">
			<?php if ( $sees_args['link_url'] ) : ?>
				<a class="btn btn--primary" href="<?php echo esc_url( $sees_args['link_url'] ); ?>"><?php echo esc_html( $sees_args['link_label'] ); ?></a>
			<?php endif; ?>
			<?php if ( $sees_args['reset'] ) : ?>
				<button type="button" class="btn btn--outline" data-form-reset><?php echo esc_html( $sees_args['reset'] ); ?></button>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
