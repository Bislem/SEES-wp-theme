<?php
/**
 * Single blog post.
 *
 * @package sees
 */

get_header();
the_post();
?>

<main id="main">
	<?php
	get_template_part( 'template-parts/page-header', null, array(
		'subtitle' => get_the_date(),
	) );
	?>
	<article <?php post_class( 'section container container--narrow' ); ?>>
		<div class="prose entry-content">
			<?php
			the_content();
			wp_link_pages();
			?>
		</div>
	</article>
</main>

<?php
get_footer();
