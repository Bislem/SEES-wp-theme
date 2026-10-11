<?php
/**
 * Home page.
 *
 * @package sees
 */

get_header();

$sees_features = array(
	array( 'bolt', sees_field( 'feature_1', __( 'Innovative Solutions', 'sees' ) ) ),
	array( 'leaf', sees_field( 'feature_2', __( 'Sustainable Growth', 'sees' ) ) ),
	array( 'target', sees_field( 'feature_3', __( 'Reliable Execution', 'sees' ) ) ),
	array( 'people', sees_field( 'feature_4', __( 'People Focused', 'sees' ) ) ),
);
$sees_solutions = sees_get_solutions( 4 );
$sees_featured  = sees_get_featured_project();
$sees_marquee   = sees_lines( sees_field( 'marquee_items', implode( "\n", array(
	__( 'Power Distribution', 'sees' ),
	__( 'Solar PV', 'sees' ),
	__( 'Substations', 'sees' ),
	__( 'Smart Buildings', 'sees' ),
	'نهندس بذكاء ونُشغّل بشكل أفضل',
	__( 'Operations & Maintenance', 'sees' ),
) ) ) );
$sees_video     = (string) sees_field( 'story_video' );
?>

<main id="main">

	<!-- Hero -->
	<section class="hero">
		<div class="hero__media" aria-hidden="true">
			<?php
			echo sees_image( sees_field( 'header_image', 0 ), 'sky.jpg', 'sees-hero', array( // phpcs:ignore WordPress.Security.EscapeOutput
				'class'   => 'hero__img a-zoom',
				'alt'     => '',
				'loading' => 'eager',
				'sizes'   => '100vw',
			) );
			?>
		</div>
		<?php if ( sees_field( 'hero_mark', false ) ) : ?>
			<div class="hero__mark a-float" aria-hidden="true">
				<?php echo sees_image( 0, 'logo-mark.png', 'medium', array( 'alt' => '', 'loading' => 'eager' ) ); // phpcs:ignore ?>
			</div>
		<?php endif; ?>

		<div class="container hero__body">
			<p class="hero__eyebrow a-up d2"><?php echo esc_html( sees_field( 'hero_eyebrow', __( 'Engineering a smarter tomorrow', 'sees' ) ) ); ?></p>
			<h1 class="hero__title a-up d3"><?php echo esc_html( sees_field( 'hero_title', __( 'Smart Electrical Engineering Solutions', 'sees' ) ) ); ?></h1>
			<p class="hero__lead a-up d4"><?php echo esc_html( sees_field( 'hero_text', __( 'Delivering intelligent, reliable and sustainable electrical solutions for a brighter future.', 'sees' ) ) ); ?></p>
			<div class="btn-row a-up d5">
				<a class="btn btn--primary btn--lg arrow" href="<?php echo esc_url( sees_page_url( 'page-solutions.php' ) ); ?>"><?php esc_html_e( 'Our Solutions', 'sees' ); ?> <?php echo sees_icon( 'arrow', 18, array( 'stroke' => 2 ) ); // phpcs:ignore ?></a>
				<a class="btn btn--outline btn--lg" href="<?php echo esc_url( (string) get_post_type_archive_link( 'project' ) ); ?>"><?php esc_html_e( 'View Projects', 'sees' ); ?></a>
			</div>
		</div>

		<div class="features">
			<ul class="features__list">
				<?php foreach ( $sees_features as $i => $feature ) : ?>
					<li class="features__item a-up d<?php echo (int) $i + 5; ?>">
						<?php echo sees_icon( $feature[0], 40, array( 'stroke' => 1.4 ) ); // phpcs:ignore ?>
						<span<?php echo sees_lang_attr( $feature[1] ); // phpcs:ignore ?>><?php echo esc_html( $feature[1] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<!-- About preview -->
	<section class="section container about-preview">
		<div class="about-preview__intro reveal">
			<?php sees_eyebrow( sees_field( 'about_eyebrow', __( 'About SEES', 'sees' ) ), array( 'grow' => true ) ); ?>
			<h2 class="h2"><?php echo esc_html( sees_field( 'about_title', __( 'Engineering Intelligence for Real-World Impact', 'sees' ) ) ); ?></h2>
			<p class="lead"><?php echo esc_html( sees_field( 'about_text', __( 'SEES delivers integrated electrical engineering solutions that combine innovation, quality and sustainability. We power progress across diverse sectors with a focus on reliability, efficiency and long-term value.', 'sees' ) ) ); ?></p>
			<div><a class="btn btn--outline arrow" href="<?php echo esc_url( sees_page_url( 'page-about.php' ) ); ?>"><?php esc_html_e( 'Learn More', 'sees' ); ?><span class="screen-reader-text"> <?php esc_html_e( 'about SEES', 'sees' ); ?></span> <?php echo sees_icon( 'arrow', 16, array( 'stroke' => 2 ) ); // phpcs:ignore ?></a></div>
		</div>

		<div class="story-card zoomimg reveal">
			<?php echo sees_image( sees_field( 'about_image', 0 ), 'concrete.jpg', 'sees-wide', array( 'alt' => '', 'sizes' => '(max-width: 760px) 100vw, 40vw' ) ); // phpcs:ignore ?>
			<span class="story-card__shade" aria-hidden="true"></span>
			<?php if ( $sees_video ) : ?>
				<a class="story-card__play pulse" href="<?php echo esc_url( $sees_video ); ?>" target="_blank" rel="noopener">
					<?php echo sees_icon( 'play', 22, array( 'label' => __( 'Play our story video', 'sees' ) ) ); // phpcs:ignore ?>
				</a>
			<?php endif; ?>
			<div class="story-card__text">
				<strong><?php echo esc_html( sees_field( 'story_title', __( 'Our Story', 'sees' ) ) ); ?></strong>
				<span><?php echo esc_html( sees_field( 'story_text', __( 'Building smarter infrastructure for a better tomorrow.', 'sees' ) ) ); ?></span>
			</div>
		</div>

		<ul class="stats reveal">
			<li class="stats__item">
				<?php echo sees_icon( 'building', 38, array( 'stroke' => 1.4 ) ); // phpcs:ignore ?>
				<span><strong><?php echo esc_html( sees_field( 'stat_1_value', '[10+]' ) ); ?></strong><?php echo esc_html( sees_field( 'stat_1_label', __( 'Years of Experience', 'sees' ) ) ); ?></span>
			</li>
			<li class="stats__item">
				<?php echo sees_icon( 'target', 38, array( 'stroke' => 1.4 ) ); // phpcs:ignore ?>
				<span><strong><?php echo esc_html( sees_field( 'stat_2_value', '[100+]' ) ); ?></strong><?php echo esc_html( sees_field( 'stat_2_label', __( 'Projects Delivered', 'sees' ) ) ); ?></span>
			</li>
			<li class="stats__item">
				<?php echo sees_icon( 'people', 38, array( 'stroke' => 1.4 ) ); // phpcs:ignore ?>
				<span><?php echo esc_html( sees_field( 'stat_3_label', __( 'Trusted by Leading Partners', 'sees' ) ) ); ?></span>
			</li>
			<li class="stats__item">
				<?php echo sees_icon( 'globe', 38, array( 'stroke' => 1.4 ) ); // phpcs:ignore ?>
				<span><?php echo esc_html( sees_field( 'stat_4_label', __( 'Commitment to Sustainable Solutions', 'sees' ) ) ); ?></span>
			</li>
		</ul>
	</section>

	<!-- Solutions preview -->
	<?php if ( $sees_solutions ) : ?>
	<section class="section-band">
		<div class="section container">
			<div class="section-head section-head--split reveal">
				<div class="section-head__text">
					<?php sees_eyebrow( sees_field( 'solutions_eyebrow', __( 'Our Solutions', 'sees' ) ) ); ?>
					<h2 class="h2"><?php echo esc_html( sees_field( 'solutions_title', __( 'Integrated electrical solutions, from design to operation.', 'sees' ) ) ); ?></h2>
				</div>
				<a class="link-arrow arrow" href="<?php echo esc_url( sees_page_url( 'page-solutions.php' ) ); ?>"><?php esc_html_e( 'All solutions', 'sees' ); ?> <?php echo sees_icon( 'arrow', 16, array( 'stroke' => 2 ) ); // phpcs:ignore ?></a>
			</div>
			<div class="grid grid--cards">
				<?php
				foreach ( $sees_solutions as $sees_solution ) {
					get_template_part( 'template-parts/solution-card', null, array( 'post' => $sees_solution ) );
				}
				?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<!-- Marquee -->
	<?php if ( $sees_marquee ) : ?>
	<div class="marquee" aria-hidden="true">
		<div class="marquee__track">
			<?php for ( $sees_pass = 0; $sees_pass < 2; $sees_pass++ ) : ?>
				<?php foreach ( $sees_marquee as $i => $sees_word ) : ?>
					<span class="marquee__word"<?php echo sees_lang_attr( $sees_word ); // phpcs:ignore ?>><?php echo esc_html( $sees_word ); ?></span>
					<span class="marquee__dot marquee__dot--<?php echo 0 === $i % 2 ? 'green' : 'teal'; ?>">●</span>
				<?php endforeach; ?>
			<?php endfor; ?>
		</div>
	</div>
	<?php endif; ?>

	<!-- Featured project -->
	<?php if ( $sees_featured ) : ?>
		<?php
		$sees_fid     = $sees_featured->ID;
		$sees_summary = sees_field( 'summary', '[' . __( 'One or two sentences on the client\'s challenge and what SEES delivered.', 'sees' ) . ']', $sees_fid );
		?>
	<section class="section container">
		<article class="featured reveal">
			<div class="featured__media zoomimg">
				<?php echo sees_image( get_post_thumbnail_id( $sees_featured ), 'engineer.jpg', 'sees-wide', array( 'alt' => '', 'sizes' => '(max-width: 900px) 100vw, 55vw' ) ); // phpcs:ignore ?>
			</div>
			<div class="featured__body">
				<?php sees_eyebrow( sees_field( 'featured_eyebrow', __( 'Featured Project', 'sees' ) ), array( 'dark' => true ) ); ?>
				<h2 class="featured__title"><?php echo esc_html( get_the_title( $sees_featured ) ); ?></h2>
				<p class="featured__text"><?php echo esc_html( $sees_summary ); ?></p>
				<dl class="featured__meta">
					<div><dt><?php esc_html_e( 'Client', 'sees' ); ?></dt><dd><?php echo esc_html( sees_field( 'client', '[Client]', $sees_fid ) ); ?></dd></div>
					<div><dt><?php esc_html_e( 'Location', 'sees' ); ?></dt><dd><?php echo esc_html( sees_field( 'city', '[City]', $sees_fid ) ); ?></dd></div>
				</dl>
				<div class="btn-row">
					<a class="btn btn--green arrow" href="<?php echo esc_url( (string) get_post_type_archive_link( 'project' ) ); ?>"><?php esc_html_e( 'View all projects', 'sees' ); ?> <?php echo sees_icon( 'arrow', 16, array( 'stroke' => 2 ) ); // phpcs:ignore ?></a>
				</div>
			</div>
		</article>
	</section>
	<?php endif; ?>

	<?php
	get_template_part( 'template-parts/cta-band', null, array(
		'style'  => 'photo',
		'kicker' => sees_field( 'cta_kicker', 'نهندس بذكاء ونُشغّل بشكل أفضل' ),
		'title'  => sees_field( 'cta_title', __( 'Let\'s power your next project.', 'sees' ) ),
		'text'   => sees_field( 'cta_text', __( 'Tell us what you\'re building — an engineer will get back to you with next steps.', 'sees' ) ),
		'image'  => sees_field( 'cta_image', 0 ),
		'phone'  => true,
	) );
	?>

</main>

<?php
get_footer();
