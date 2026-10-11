<?php
/**
 * Projects archive (also used for project category archives).
 * Filter pills work without JS (links), and filter in place with JS.
 *
 * @package sees
 */

get_header();

$sees_terms   = get_terms( array( 'taxonomy' => 'project_category', 'hide_empty' => true, 'orderby' => 'term_id' ) );
$sees_terms   = is_wp_error( $sees_terms ) ? array() : $sees_terms;
$sees_archive = (string) get_post_type_archive_link( 'project' );
$sees_active  = '';
if ( is_tax( 'project_category' ) ) {
	$sees_active = get_queried_object()->slug;
} elseif ( isset( $_GET['cat'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
	$sees_active = sanitize_title( wp_unslash( $_GET['cat'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
}
?>

<main id="main">
	<?php
	get_template_part( 'template-parts/page-header', null, array(
		'title'    => __( 'Our Projects', 'sees' ),
		'subtitle' => __( 'A selection of electrical systems we\'ve designed, built and keep running.', 'sees' ),
		'fallback' => 'pylons.jpg',
		'position' => 'center 40%',
	) );
	?>

	<section class="section section--tight-top container">
		<?php if ( $sees_terms ) : ?>
			<div class="filters" role="group" aria-label="<?php esc_attr_e( 'Filter projects', 'sees' ); ?>" data-filters>
				<a class="pill" href="<?php echo esc_url( $sees_archive ); ?>" data-filter=""<?php echo $sees_active ? '' : ' aria-current="true"'; ?>><?php esc_html_e( 'All', 'sees' ); ?></a>
				<?php foreach ( $sees_terms as $sees_term ) : ?>
					<a class="pill" href="<?php echo esc_url( add_query_arg( 'cat', $sees_term->slug, $sees_archive ) ); ?>" data-filter="<?php echo esc_attr( $sees_term->slug ); ?>"<?php echo $sees_active === $sees_term->slug ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $sees_term->name ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<div class="grid grid--projects" data-projects>
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/project-card', null, array( 'post' => get_post() ) );
				}
				?>
			</div>
			<p class="filters__empty" data-projects-empty hidden><?php esc_html_e( 'No projects in this category yet.', 'sees' ); ?></p>
			<?php
			the_posts_pagination( array(
				'prev_text' => __( 'Previous', 'sees' ),
				'next_text' => __( 'Next', 'sees' ),
			) );
			?>
		<?php else : ?>
			<p class="lead"><?php esc_html_e( 'No projects in this category yet.', 'sees' ); ?></p>
		<?php endif; ?>
	</section>

	<?php
	get_template_part( 'template-parts/cta-band', null, array(
		'style'  => 'photo',
		'title'  => __( 'Your project could be next.', 'sees' ),
		'text'   => __( 'Share your plans and we\'ll prepare a proposal.', 'sees' ),
		'button' => __( 'Start a project', 'sees' ),
	) );
	?>
</main>

<?php
get_footer();
