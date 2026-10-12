<?php
/**
 * Template Name: Jobs
 * Careers landing page and published vacancy directory.
 *
 * @package sees
 */

get_header();
the_post();

$sees_page_id = get_the_ID();
$sees_jobs     = sees_get_open_jobs();
$sees_filters  = sees_job_filter_options( $sees_jobs );
$sees_values   = array(
	array( 'people', (string) sees_field( 'career_value_1', sees_jobs_text( 'professional' ), $sees_page_id ) ),
	array( 'bolt', (string) sees_field( 'career_value_2', sees_jobs_text( 'innovation' ), $sees_page_id ) ),
	array( 'shield', (string) sees_field( 'career_value_3', sees_jobs_text( 'safety' ), $sees_page_id ) ),
	array( 'leaf', (string) sees_field( 'career_value_4', sees_jobs_text( 'learning' ), $sees_page_id ) ),
	array( 'target', (string) sees_field( 'career_value_5', sees_jobs_text( 'impact' ), $sees_page_id ) ),
);
$sees_why_points = array(
	array( 'pylon', (string) sees_field( 'why_point_1', sees_jobs_text( 'why_point_1' ), $sees_page_id ) ),
	array( 'people', (string) sees_field( 'why_point_2', sees_jobs_text( 'why_point_2' ), $sees_page_id ) ),
	array( 'wrench', (string) sees_field( 'why_point_3', sees_jobs_text( 'why_point_3' ), $sees_page_id ) ),
	array( 'leaf', (string) sees_field( 'why_point_4', sees_jobs_text( 'why_point_4' ), $sees_page_id ) ),
);
$sees_process = array(
	array( '01', 'check', (string) sees_field( 'process_step_1', sees_jobs_text( 'process_1' ), $sees_page_id ), sees_jobs_text( 'process_1_text' ) ),
	array( '02', 'people', (string) sees_field( 'process_step_2', sees_jobs_text( 'process_2' ), $sees_page_id ), sees_jobs_text( 'process_2_text' ) ),
	array( '03', 'mail', (string) sees_field( 'process_step_3', sees_jobs_text( 'process_3' ), $sees_page_id ), sees_jobs_text( 'process_3_text' ) ),
	array( '04', 'target', (string) sees_field( 'process_step_4', sees_jobs_text( 'process_4' ), $sees_page_id ), sees_jobs_text( 'process_4_text' ) ),
);
?>

<main id="main" class="jobs-page">
	<section class="careers-hero">
		<div class="container careers-hero__grid">
			<div class="careers-hero__body">
				<?php sees_eyebrow( (string) sees_field( 'hero_eyebrow', sees_jobs_text( 'hero_eyebrow' ), $sees_page_id ) ); ?>
				<h1 class="careers-hero__title"><?php echo esc_html( (string) sees_field( 'hero_title', sees_jobs_text( 'hero_title' ), $sees_page_id ) ); ?></h1>
				<p class="careers-hero__lead"><?php echo esc_html( (string) sees_field( 'hero_intro', sees_jobs_text( 'hero_intro' ), $sees_page_id ) ); ?></p>
				<a class="btn btn--primary btn--lg arrow" href="#open-positions"><?php echo esc_html( sees_jobs_text( 'view_roles' ) ); ?> <?php echo sees_icon( 'arrow', 18, array( 'stroke' => 2 ) ); // phpcs:ignore ?></a>
			</div>
			<figure class="careers-hero__media">
				<?php echo sees_image( sees_field( 'hero_image', 0, $sees_page_id ), 'engineer.jpg', 'sees-hero', array( 'alt' => '', 'loading' => 'eager', 'sizes' => '(max-width: 900px) 100vw, 54vw' ) ); // phpcs:ignore ?>
			</figure>
		</div>
	</section>

	<section class="careers-values" aria-label="<?php echo esc_attr( sees_jobs_text( 'values_label' ) ); ?>">
		<div class="container careers-values__grid">
			<?php foreach ( $sees_values as $sees_value ) : ?>
				<div class="careers-value">
					<span class="careers-value__icon"><?php echo sees_icon( $sees_value[0], 32 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<span><?php echo esc_html( $sees_value[1] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="section container careers-why">
		<figure class="careers-why__media media-frame reveal">
			<?php echo sees_image( sees_field( 'why_image', 0, $sees_page_id ), 'hero-building.jpg', 'sees-wide', array( 'alt' => '', 'sizes' => '(max-width: 900px) 100vw, 50vw' ) ); // phpcs:ignore ?>
		</figure>
		<div class="careers-why__body reveal">
			<?php sees_eyebrow( (string) sees_field( 'why_eyebrow', sees_jobs_text( 'why_eyebrow' ), $sees_page_id ) ); ?>
			<h2 class="h2"><?php echo esc_html( (string) sees_field( 'why_title', sees_jobs_text( 'why_title' ), $sees_page_id ) ); ?></h2>
			<p class="prose"><?php echo esc_html( (string) sees_field( 'why_text', sees_jobs_text( 'why_text' ), $sees_page_id ) ); ?></p>
			<ul class="careers-why__points">
				<?php foreach ( $sees_why_points as $sees_point ) : ?>
					<li><?php echo sees_icon( $sees_point[0], 27 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $sees_point[1] ); ?></span></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<section class="section section-band jobs-listing" id="open-positions">
		<div class="container">
			<div class="jobs-listing__head">
				<div>
					<?php sees_eyebrow( sees_jobs_text( 'jobs_eyebrow' ) ); ?>
					<h2 class="h2"><?php echo esc_html( sees_jobs_text( 'jobs_title' ) ); ?></h2>
					<p><?php echo esc_html( sees_jobs_text( 'jobs_intro' ) ); ?></p>
				</div>

				<?php if ( $sees_jobs && array_filter( $sees_filters, fn( array $values ) => count( $values ) > 1 ) ) : ?>
					<form class="job-filters" data-job-filters>
						<?php foreach ( array( 'department', 'location', 'employment_type' ) as $sees_filter ) : ?>
							<?php if ( count( $sees_filters[ $sees_filter ] ) > 1 ) : ?>
								<label>
									<span class="screen-reader-text"><?php echo esc_html( sees_jobs_text( 'filter_' . ( 'employment_type' === $sees_filter ? 'type' : $sees_filter ) ) ); ?></span>
									<select data-job-filter="<?php echo esc_attr( $sees_filter ); ?>">
										<option value=""><?php echo esc_html( sees_jobs_text( 'filter_' . ( 'employment_type' === $sees_filter ? 'type' : $sees_filter ) ) ); ?></option>
										<?php foreach ( $sees_filters[ $sees_filter ] as $sees_value => $sees_label ) : ?>
											<option value="<?php echo esc_attr( $sees_value ); ?>"><?php echo esc_html( $sees_label ); ?></option>
										<?php endforeach; ?>
									</select>
								</label>
							<?php endif; ?>
						<?php endforeach; ?>
						<button class="job-filters__clear" type="reset"><?php echo esc_html( sees_jobs_text( 'clear_filters' ) ); ?></button>
					</form>
				<?php endif; ?>
			</div>

			<?php if ( $sees_jobs ) : ?>
				<div class="jobs-grid" data-job-grid data-job-total="<?php echo esc_attr( (string) count( $sees_jobs ) ); ?>">
					<?php foreach ( $sees_jobs as $sees_job ) : ?>
						<?php get_template_part( 'template-parts/job-card', null, array( 'job' => $sees_job ) ); ?>
					<?php endforeach; ?>
				</div>
				<div class="jobs-empty" data-job-filter-empty hidden><?php echo sees_icon( 'search', 34 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><p><?php echo esc_html( sees_jobs_text( 'no_matches' ) ); ?></p></div>
			<?php else : ?>
				<div class="jobs-empty reveal"><?php echo sees_icon( 'building', 40 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><p><?php echo esc_html( sees_jobs_text( 'no_jobs' ) ); ?></p></div>
			<?php endif; ?>
		</div>
	</section>

	<section class="section container recruitment-process">
		<div class="recruitment-process__head">
			<?php sees_eyebrow( sees_jobs_text( 'process_eyebrow' ) ); ?>
			<h2 class="h2"><?php echo esc_html( (string) sees_field( 'process_title', sees_jobs_text( 'process_title' ), $sees_page_id ) ); ?></h2>
			<p><?php echo esc_html( (string) sees_field( 'process_intro', sees_jobs_text( 'process_intro' ), $sees_page_id ) ); ?></p>
		</div>
		<ol class="recruitment-process__steps">
			<?php foreach ( $sees_process as $sees_step ) : ?>
				<li class="recruitment-step reveal">
					<div class="recruitment-step__icon"><?php echo sees_icon( $sees_step[1], 28 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
					<span class="recruitment-step__number"><?php echo esc_html( $sees_step[0] ); ?></span>
					<h3><?php echo esc_html( $sees_step[2] ); ?></h3>
					<p><?php echo esc_html( $sees_step[3] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</section>

	<?php
	get_template_part( 'template-parts/cta-band', null, array(
		'style'  => 'photo',
		'title'  => (string) sees_field( 'cta_title', sees_jobs_text( 'cta_title' ), $sees_page_id ),
		'text'   => (string) sees_field( 'cta_text', sees_jobs_text( 'cta_text' ), $sees_page_id ),
		'button' => sees_jobs_text( 'cta_button' ),
		'url'    => '#open-positions',
	) );
	?>
</main>

<?php get_footer(); ?>
