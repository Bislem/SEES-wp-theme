<?php
/**
 * Default page template (e.g. privacy policy).
 *
 * @package sees
 */

get_header();
the_post();
?>

<main id="main">
	<?php
	get_template_part( 'template-parts/page-header', null, array(
		'subtitle' => sees_field( 'header_subtitle' ),
		'image'    => sees_field( 'header_image', 0 ) ?: get_post_thumbnail_id(),
	) );
	?>
	<section class="section container container--narrow">
		<div class="prose entry-content">
			<?php
			the_content();
			wp_link_pages();
			?>
		</div>
	</section>
</main>

<?php
get_footer();
