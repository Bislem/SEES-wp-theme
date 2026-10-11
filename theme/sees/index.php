<?php
/**
 * Fallback listing (blog, search results, archives).
 *
 * @package sees
 */

get_header();

if ( is_search() ) {
	/* translators: %s: search query */
	$sees_title = sprintf( __( 'Search: %s', 'sees' ), get_search_query() );
} elseif ( is_home() && ! is_front_page() ) {
	$sees_title = single_post_title( '', false );
} else {
	$sees_title = wp_strip_all_tags( get_the_archive_title() );
}
?>

<main id="main">
	<?php get_template_part( 'template-parts/page-header', null, array( 'title' => $sees_title ) ); ?>

	<section class="section container container--narrow">
		<?php if ( have_posts() ) : ?>
			<ul class="post-list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<li class="post-list__item">
						<h2 class="post-list__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<?php if ( has_excerpt() || 'post' === get_post_type() ) : ?>
							<p><?php echo esc_html( get_the_excerpt() ); ?></p>
						<?php endif; ?>
					</li>
				<?php endwhile; ?>
			</ul>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p class="lead"><?php esc_html_e( 'Nothing found. Try another search.', 'sees' ); ?></p>
			<?php get_search_form(); ?>
		<?php endif; ?>
	</section>
</main>

<?php
get_footer();
