<?php
/**
 * Single published vacancy.
 *
 * @package sees
 */

get_header();
the_post();

$sees_job_id      = get_the_ID();
$sees_is_open     = sees_job_is_open( $sees_job_id );
$sees_application = sees_job_application_form( $sees_job_id );
$sees_department  = trim( (string) sees_job_field( $sees_job_id, 'job_department' ) );
$sees_location    = trim( (string) sees_job_field( $sees_job_id, 'job_location' ) );
$sees_type        = trim( (string) sees_job_field( $sees_job_id, 'job_employment_type', '', true ) );
$sees_reference   = trim( (string) sees_job_field( $sees_job_id, 'job_reference', '', true ) );
$sees_closing     = preg_replace( '/[^0-9]/', '', (string) sees_job_field( $sees_job_id, 'closing_date', '', true ) );
$sees_closing_dt  = 8 === strlen( $sees_closing ) ? DateTimeImmutable::createFromFormat( '!Ymd', $sees_closing ) : false;
$sees_facts       = array_filter( array(
	sees_jobs_text( 'department' )      => $sees_department,
	sees_jobs_text( 'location' )        => $sees_location,
	sees_jobs_text( 'employment_type' ) => $sees_type ? sees_job_type_label( $sees_type ) : '',
	sees_jobs_text( 'published' )       => get_the_date(),
	sees_jobs_text( 'closing_date' )    => $sees_closing_dt ? wp_date( get_option( 'date_format' ), $sees_closing_dt->getTimestamp() ) : '',
) );
?>

<main id="main" class="job-detail-page">
	<?php
	get_template_part( 'template-parts/page-header', null, array(
		'subtitle' => has_excerpt() ? get_the_excerpt() : '',
		'parent'   => array( sees_page_url( 'page-jobs.php' ), sees_jobs_text( 'jobs_title' ) ),
	) );
	?>

	<section class="section container job-detail">
		<aside class="job-detail__aside reveal">
			<span class="job-detail__status <?php echo $sees_is_open ? 'is-open' : 'is-closed'; ?>"><?php echo esc_html( sees_jobs_text( $sees_is_open ? 'open' : 'closed' ) ); ?></span>
			<?php if ( $sees_reference ) : ?><p class="job-detail__reference"><?php echo esc_html( $sees_reference ); ?></p><?php endif; ?>
			<dl class="job-detail__facts">
				<?php foreach ( $sees_facts as $sees_label => $sees_value ) : ?>
					<div><dt><?php echo esc_html( $sees_label ); ?></dt><dd><?php echo esc_html( $sees_value ); ?></dd></div>
				<?php endforeach; ?>
			</dl>
			<a class="btn btn--outline arrow" href="<?php echo esc_url( sees_page_url( 'page-jobs.php' ) . '#open-positions' ); ?>"><?php echo esc_html( sees_jobs_text( 'back_to_jobs' ) ); ?> <?php echo sees_icon( 'arrow', 16, array( 'stroke' => 2 ) ); // phpcs:ignore ?></a>
		</aside>

		<div class="job-detail__content prose reveal">
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="media-frame job-detail__image"><?php the_post_thumbnail( 'sees-wide', array( 'sizes' => '(max-width: 900px) 100vw, 760px' ) ); ?></figure>
			<?php endif; ?>
			<?php the_content(); ?>

			<?php $sees_responsibilities = (string) sees_field( 'job_responsibilities', '', $sees_job_id ); ?>
			<?php if ( $sees_responsibilities ) : ?>
				<section><h2><?php echo esc_html( sees_jobs_text( 'responsibilities' ) ); ?></h2><?php echo wp_kses_post( $sees_responsibilities ); ?></section>
			<?php endif; ?>

			<?php $sees_requirements = (string) sees_field( 'job_requirements', '', $sees_job_id ); ?>
			<?php if ( $sees_requirements ) : ?>
				<section><h2><?php echo esc_html( sees_jobs_text( 'requirements' ) ); ?></h2><?php echo wp_kses_post( $sees_requirements ); ?></section>
			<?php endif; ?>

			<?php if ( ! $sees_is_open ) : ?>
				<div class="job-detail__closed"><?php echo sees_icon( 'clock', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><p><?php echo esc_html( sees_jobs_text( 'closed_notice' ) ); ?></p></div>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( $sees_application ) : ?>
		<section class="section section-band job-application" id="job-application">
			<div class="container job-application__inner">
				<div class="job-application__head">
					<?php sees_eyebrow( sees_jobs_text( 'apply_now' ) ); ?>
					<h2 class="h2"><?php echo esc_html( sees_jobs_text( 'application' ) ); ?></h2>
					<p><?php echo esc_html( sees_jobs_text( 'application_intro' ) ); ?></p>
				</div>
				<div class="sees-job-form"><?php echo $sees_application; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			</div>
		</section>
	<?php endif; ?>
</main>

<?php get_footer(); ?>
