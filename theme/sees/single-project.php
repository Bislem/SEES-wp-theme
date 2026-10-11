<?php
/**
 * Single project (case study).
 *
 * @package sees
 */

get_header();
the_post();

$sees_id    = get_the_ID();
$sees_terms = get_the_terms( $sees_id, 'project_category' );
$sees_facts = array(
	__( 'Client', 'sees' )   => sees_field( 'client', '[Client]' ),
	__( 'Location', 'sees' ) => sees_field( 'city', '[City]' ),
	__( 'Year', 'sees' )     => sees_field( 'year', '[Year]' ),
);
if ( $sees_terms && ! is_wp_error( $sees_terms ) ) {
	$sees_facts[ __( 'Category', 'sees' ) ] = implode( ', ', wp_list_pluck( $sees_terms, 'name' ) );
}
?>

<main id="main">
	<?php
	get_template_part( 'template-parts/page-header', null, array(
		'subtitle' => sees_field( 'summary', get_the_excerpt() ),
		'parent'   => array( (string) get_post_type_archive_link( 'project' ), __( 'Projects', 'sees' ) ),
	) );
	?>

	<section class="section container project-detail">
		<aside class="project-facts reveal">
			<dl>
				<?php foreach ( $sees_facts as $sees_label => $sees_value ) : ?>
					<div><dt><?php echo esc_html( $sees_label ); ?></dt><dd><?php echo esc_html( $sees_value ); ?></dd></div>
				<?php endforeach; ?>
			</dl>
		</aside>
		<div class="project-detail__content prose reveal">
			<?php
			if ( has_post_thumbnail() ) {
				echo '<figure class="media-frame media-frame--16x11">';
				the_post_thumbnail( 'sees-wide', array( 'sizes' => '(max-width: 900px) 100vw, 760px' ) );
				echo '</figure>';
			}
			if ( get_the_content() ) {
				the_content();
			} else {
				echo '<p>' . esc_html__( '[Describe the project: scope, challenge, what SEES delivered and the result.]', 'sees' ) . '</p>';
			}
			?>
		</div>
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
