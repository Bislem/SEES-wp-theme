<?php
/**
 * Single news article.
 *
 * @package sees
 */

get_header();
the_post();

$sees_post    = get_post();
$sees_cat     = sees_post_primary_category( $sees_post );
$sees_news    = sees_news_page();
$sees_url     = rawurlencode( get_permalink() );
$sees_title   = rawurlencode( get_the_title() );
$sees_related = sees_related_posts( $sees_post );
?>

<main id="main" class="article-page">
	<?php
	get_template_part( 'template-parts/page-header', null, array(
		'subtitle' => has_excerpt() ? get_the_excerpt() : '',
		'parent'   => $sees_news ? array( get_permalink( $sees_news ), get_the_title( $sees_news ) ) : null,
	) );
	?>

	<article <?php post_class( 'section section--tight-top container container--narrow article' ); ?>>
		<p class="article__meta">
			<?php if ( $sees_cat ) : ?>
				<a class="article__cat" href="<?php echo esc_url( get_category_link( $sees_cat ) ); ?>"><?php echo esc_html( $sees_cat->name ); ?></a>
			<?php endif; ?>
			<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			<span aria-hidden="true">·</span>
			<span><?php echo esc_html( sees_reading_time( $sees_post ) ); ?></span>
		</p>

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="article__image media-frame media-frame--16x11">
				<?php the_post_thumbnail( 'sees-wide', array( 'alt' => '', 'sizes' => '(max-width: 1000px) 100vw, 920px', 'loading' => 'eager' ) ); ?>
			</figure>
		<?php endif; ?>

		<div class="prose entry-content article__body">
			<?php
			the_content();
			wp_link_pages();
			?>
		</div>

		<div class="article__share">
			<span><?php esc_html_e( 'Share', 'sees' ); ?></span>
			<a href="<?php echo esc_url( 'https://www.linkedin.com/sharing/share-offsite/?url=' . $sees_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( 'LinkedIn' ); ?></a>
			<a href="<?php echo esc_url( 'https://x.com/intent/post?url=' . $sees_url . '&text=' . $sees_title ); ?>" target="_blank" rel="noopener"><?php echo esc_html( 'X' ); ?></a>
			<a href="<?php echo esc_url( 'https://wa.me/?text=' . $sees_title . '%20' . $sees_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( 'WhatsApp' ); ?></a>
			<a href="<?php echo esc_url( 'mailto:?subject=' . $sees_title . '&body=' . $sees_url ); ?>"><?php esc_html_e( 'Email', 'sees' ); ?></a>
		</div>
	</article>

	<?php if ( $sees_related ) : ?>
		<section class="section-band">
			<div class="section container">
				<div class="section-head section-head--split reveal">
					<div class="section-head__text">
						<?php sees_eyebrow( __( 'Keep reading', 'sees' ) ); ?>
						<h2 class="h2"><?php esc_html_e( 'More from SEES', 'sees' ); ?></h2>
					</div>
					<?php if ( $sees_news ) : ?>
						<a class="link-arrow arrow" href="<?php echo esc_url( get_permalink( $sees_news ) ); ?>"><?php esc_html_e( 'All news', 'sees' ); ?> <?php echo sees_icon( 'arrow', 16, array( 'stroke' => 1.7 ) ); // phpcs:ignore ?></a>
					<?php endif; ?>
				</div>
				<div class="news__grid">
					<?php foreach ( $sees_related as $sees_item ) : ?>
						<?php get_template_part( 'template-parts/post-card', null, array( 'post' => $sees_item ) ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	get_template_part( 'template-parts/cta-band', null, array(
		'style' => 'brand',
		'title' => __( 'Have a project in mind?', 'sees' ),
		'text'  => __( 'Talk to our engineers about your electrical project.', 'sees' ),
	) );
	?>
</main>

<?php
get_footer();
