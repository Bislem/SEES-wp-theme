<?php
/**
 * News listing: category filters, featured latest post, card grid, pagination.
 * Used by home.php (the News page) and archive.php (categories, tags, dates).
 *
 * @package sees
 *
 * @var array $args { title, subtitle, current_cat (int) }
 */

$sees_args    = wp_parse_args( $args, array( 'title' => '', 'subtitle' => '', 'current_cat' => 0 ) );
$sees_news    = sees_news_url();
$sees_cats    = sees_news_categories();
$sees_paged   = max( 1, (int) get_query_var( 'paged' ) );
$sees_index   = 0;
?>
<main id="main" class="news-page">
	<?php
	get_template_part( 'template-parts/page-header', null, array(
		'title'    => $sees_args['title'],
		'subtitle' => $sees_args['subtitle'],
		'parent'   => $sees_args['current_cat'] && $sees_news ? array( $sees_news, sees_news_page() ? get_the_title( sees_news_page() ) : __( 'News', 'sees' ) ) : null,
	) );
	?>

	<section class="section section--tight-top container news">
		<?php if ( $sees_cats && $sees_news ) : ?>
			<nav class="filters news__cats" aria-label="<?php esc_attr_e( 'News categories', 'sees' ); ?>">
				<a class="pill" href="<?php echo esc_url( $sees_news ); ?>"<?php echo $sees_args['current_cat'] ? '' : ' aria-current="true"'; ?>><?php esc_html_e( 'All news', 'sees' ); ?></a>
				<?php foreach ( $sees_cats as $sees_cat ) : ?>
					<a class="pill" href="<?php echo esc_url( get_category_link( $sees_cat ) ); ?>"<?php echo (int) $sees_args['current_cat'] === (int) $sees_cat->term_id ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $sees_cat->name ); ?></a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<div class="news__grid">
				<?php
				while ( have_posts() ) :
					the_post();
					// The newest post on the first News page is shown large.
					$sees_featured = 1 === $sees_paged && 0 === $sees_index && ! $sees_args['current_cat'];
					get_template_part( 'template-parts/post-card', null, array( 'post' => get_post(), 'featured' => $sees_featured ) );
					++$sees_index;
				endwhile;
				?>
			</div>
			<?php
			the_posts_pagination( array(
				'mid_size'  => 1,
				'prev_text' => __( 'Previous', 'sees' ),
				'next_text' => __( 'Next', 'sees' ),
			) );
			?>
		<?php else : ?>
			<div class="downloads__empty">
				<?php echo sees_icon( 'mail', 34 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<p><?php esc_html_e( 'News and articles will be published here soon.', 'sees' ); ?></p>
			</div>
		<?php endif; ?>
	</section>

	<?php
	get_template_part( 'template-parts/cta-band', null, array(
		'style' => 'brand',
		'title' => __( 'Have a project in mind?', 'sees' ),
		'text'  => __( 'Talk to our engineers about your electrical project.', 'sees' ),
	) );
	?>
</main>
