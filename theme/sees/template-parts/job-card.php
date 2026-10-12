<?php
/**
 * Open vacancy card.
 *
 * @package sees
 * @var array $args { @type WP_Post $job }
 */

$sees_job = $args['job'] ?? null;
if ( ! $sees_job instanceof WP_Post ) {
	return;
}

$sees_job_id         = $sees_job->ID;
$sees_department     = trim( (string) sees_job_field( $sees_job_id, 'job_department' ) );
$sees_location       = trim( (string) sees_job_field( $sees_job_id, 'job_location' ) );
$sees_employment     = trim( (string) sees_job_field( $sees_job_id, 'job_employment_type', '', true ) );
$sees_has_application = sees_job_has_application_form( $sees_job_id );
$sees_summary        = has_excerpt( $sees_job_id ) ? get_the_excerpt( $sees_job_id ) : wp_trim_words( wp_strip_all_tags( $sees_job->post_content ), 24, '…' );
?>
<article
	class="job-card reveal"
	data-job-card
	data-department="<?php echo esc_attr( $sees_department ); ?>"
	data-location="<?php echo esc_attr( $sees_location ); ?>"
	data-employment-type="<?php echo esc_attr( $sees_employment ); ?>"
>
	<div class="job-card__top">
		<h3 class="job-card__title"><a href="<?php echo esc_url( get_permalink( $sees_job ) ); ?>"<?php echo sees_lang_attr( get_the_title( $sees_job ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( get_the_title( $sees_job ) ); ?></a></h3>
		<span class="job-card__status"><?php echo esc_html( sees_jobs_text( 'open' ) ); ?></span>
	</div>

	<ul class="job-card__meta" aria-label="<?php echo esc_attr( sees_jobs_text( 'jobs_eyebrow' ) ); ?>">
		<?php if ( $sees_location ) : ?>
			<li><?php echo sees_icon( 'pin', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span<?php echo sees_lang_attr( $sees_location ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $sees_location ); ?></span></li>
		<?php endif; ?>
		<?php if ( $sees_department ) : ?>
			<li><?php echo sees_icon( 'building', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span<?php echo sees_lang_attr( $sees_department ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $sees_department ); ?></span></li>
		<?php endif; ?>
		<?php if ( $sees_employment ) : ?>
			<li><?php echo sees_icon( 'clock', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( sees_job_type_label( $sees_employment ) ); ?></span></li>
		<?php endif; ?>
		<li><?php echo sees_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( sees_jobs_text( 'published' ) . ': ' . get_the_date( '', $sees_job ) ); ?></span></li>
	</ul>

	<?php if ( $sees_summary ) : ?>
		<p class="job-card__summary"<?php echo sees_lang_attr( $sees_summary ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $sees_summary ); ?></p>
	<?php endif; ?>

	<div class="job-card__actions">
		<a class="btn btn--outline" href="<?php echo esc_url( get_permalink( $sees_job ) ); ?>"><?php echo esc_html( sees_jobs_text( 'view_details' ) ); ?></a>
		<?php if ( $sees_has_application ) : ?>
			<a class="btn btn--primary arrow" href="<?php echo esc_url( get_permalink( $sees_job ) . '#job-application' ); ?>"><?php echo esc_html( sees_jobs_text( 'apply_now' ) ); ?> <?php echo sees_icon( 'arrow', 16, array( 'stroke' => 2 ) ); // phpcs:ignore ?></a>
		<?php endif; ?>
	</div>
</article>
