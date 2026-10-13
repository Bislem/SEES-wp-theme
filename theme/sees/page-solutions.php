<?php
/**
 * Template Name: Our Solutions
 *
 * @package sees
 */

get_header();
the_post();

$sees_solutions = sees_get_solutions();
$sees_faqs      = sees_get_faqs();
$sees_fallbacks = array( 'engineer.jpg', 'engineer.jpg', 'pylons.jpg', 'facade.jpg', 'sitebanner.jpg' );
$sees_steps     = array(
	array( 'petrol', sees_field( 'step_1_title', __( 'Assess', 'sees' ) ), sees_field( 'step_1_text', __( 'Site survey, load analysis and a clear scope before design begins.', 'sees' ) ) ),
	array( 'teal', sees_field( 'step_2_title', __( 'Engineer', 'sees' ) ), sees_field( 'step_2_text', __( 'Drawings, calculations and equipment selection, checked against code and budget.', 'sees' ) ) ),
	array( 'green', sees_field( 'step_3_title', __( 'Install', 'sees' ) ), sees_field( 'step_3_text', __( 'Certified crews build, test and commission, with full documentation at handover.', 'sees' ) ) ),
	array( 'teal', sees_field( 'step_4_title', __( 'Operate', 'sees' ) ), sees_field( 'step_4_text', __( 'Maintenance plans and monitoring that keep systems efficient for years.', 'sees' ) ) ),
);
$sees_chips = array();
foreach ( $sees_solutions as $sees_solution ) {
	$sees_chips[] = array( '#' . sees_solution_anchor( $sees_solution ), get_the_title( $sees_solution ) );
}
?>

<main id="main">
	<?php
	get_template_part( 'template-parts/page-header', null, array(
		'subtitle' => sees_field( 'header_subtitle', __( 'Complete electrical engineering — designed, installed and maintained by one accountable team.', 'sees' ) ),
		'chips'    => $sees_chips,
	) );
	?>

	<?php if ( $sees_solutions ) : ?>
	<section class="section container solutions">
		<?php foreach ( $sees_solutions as $i => $sees_solution ) : ?>
			<?php
			$sees_id      = $sees_solution->ID;
			$sees_accent  = (string) sees_field( 'accent', array( 'teal', 'green', 'petrol' )[ $i % 3 ], $sees_id );
			$sees_bullets = sees_lines( sees_field( 'bullets', '', $sees_id ) );
			?>
			<article class="solution-row<?php echo $i % 2 ? ' solution-row--flip' : ''; ?> accent--<?php echo esc_attr( $sees_accent ); ?>" id="<?php echo esc_attr( sees_solution_anchor( $sees_solution ) ); ?>">
				<div class="solution-row__media media-frame media-frame--16x11 zoomimg reveal">
					<?php echo sees_image( get_post_thumbnail_id( $sees_solution ), $sees_fallbacks[ $i % 5 ], 'sees-wide', array( 'alt' => '', 'sizes' => '(max-width: 900px) 100vw, 50vw' ) ); // phpcs:ignore ?>
				</div>
				<div class="solution-row__body reveal">
					<span class="solution-row__num" aria-hidden="true" data-num="<?php echo esc_attr( sees_field( 'number', sprintf( '%02d', $i + 1 ), $sees_id ) ); ?>"></span>
					<h2 class="solution-row__title"><?php echo esc_html( get_the_title( $sees_solution ) ); ?></h2>
					<span class="solution-row__line growv" aria-hidden="true"></span>
					<p class="lead"><?php echo esc_html( sees_field( 'intro', get_the_excerpt( $sees_solution ), $sees_id ) ); ?></p>
					<?php if ( $sees_bullets ) : ?>
						<ul class="checklist">
							<?php foreach ( $sees_bullets as $sees_bullet ) : ?>
								<li><?php echo sees_icon( 'check', 18, array( 'stroke' => 2.4 ) ); // phpcs:ignore ?><?php echo esc_html( $sees_bullet ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			</article>
		<?php endforeach; ?>
	</section>
	<?php endif; ?>

	<section class="section-dark">
		<div class="section container">
			<div class="section-head reveal">
				<?php sees_eyebrow( sees_field( 'process_eyebrow', __( 'How we work', 'sees' ) ), array( 'dark' => true ) ); ?>
				<h2 class="h2"><?php echo esc_html( sees_field( 'process_title', __( 'Four steps from survey to switch-on.', 'sees' ) ) ); ?></h2>
			</div>
			<div class="process">
				<div class="process__flow flow" aria-hidden="true"></div>
				<ol class="process__steps">
					<?php foreach ( $sees_steps as $i => $sees_step ) : ?>
						<li class="step reveal">
							<span class="step__dot step__dot--<?php echo esc_attr( $sees_step[0] ); ?>" aria-hidden="true"></span>
							<span class="step__num">
								<?php
								/* translators: %s: step number, e.g. 01 */
								printf( esc_html__( 'Step %s', 'sees' ), esc_html( sprintf( '%02d', $i + 1 ) ) );
								?>
							</span>
							<h3 class="step__title"><?php echo esc_html( $sees_step[1] ); ?></h3>
							<p class="step__text"><?php echo esc_html( $sees_step[2] ); ?></p>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</div>
	</section>

	<?php if ( $sees_faqs ) : ?>
	<section class="section container container--narrow">
		<div class="section-head reveal">
			<?php sees_eyebrow( sees_field( 'faq_eyebrow', __( 'FAQ', 'sees' ) ) ); ?>
			<h2 class="h2"><?php echo esc_html( sees_field( 'faq_title', __( 'Common questions', 'sees' ) ) ); ?></h2>
		</div>
		<div class="faq">
			<?php foreach ( $sees_faqs as $i => $sees_faq ) : ?>
				<details class="faq__item" name="faq"<?php echo 0 === $i ? ' open' : ''; ?>>
					<summary class="faq__q">
						<span><?php echo esc_html( get_the_title( $sees_faq ) ); ?></span>
						<span class="faq__sign" aria-hidden="true"></span>
					</summary>
					<div class="faq__a prose"><?php echo wp_kses_post( apply_filters( 'the_content', $sees_faq->post_content ) ); ?></div>
				</details>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php
	get_template_part( 'template-parts/cta-band', null, array(
		'style'  => 'brand',
		'title'  => sees_field( 'cta_title', __( 'Not sure which solution fits?', 'sees' ) ),
		'text'   => sees_field( 'cta_text', __( 'Describe your project and our engineers will recommend the right approach.', 'sees' ) ),
		'button' => __( 'Talk to an engineer', 'sees' ),
		'url'    => sees_contact_url( 'project' ),
	) );
	?>
</main>

<?php
get_footer();
