<?php
/**
 * Not found.
 *
 * @package sees
 */

get_header();
?>

<main id="main">
	<?php
	get_template_part( 'template-parts/page-header', null, array(
		'title'    => __( 'Page not found', 'sees' ),
		'subtitle' => __( 'The page you are looking for has moved or no longer exists.', 'sees' ),
		'crumbs'   => false,
	) );
	?>
	<section class="section container container--narrow">
		<?php get_search_form(); ?>
		<p><a class="btn btn--primary arrow" href="<?php echo esc_url( function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'sees' ); ?> <?php echo sees_icon( 'arrow', 16, array( 'stroke' => 2 ) ); // phpcs:ignore ?></a></p>
	</section>
</main>

<?php
get_footer();
