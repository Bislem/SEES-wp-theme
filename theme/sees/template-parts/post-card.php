<?php
/**
 * News / blog post card.
 *
 * @package sees
 *
 * @var array $args { post: WP_Post, featured: bool }
 */

$sees_post     = $args['post'] ?? null;
$sees_featured = ! empty( $args['featured'] );
if ( ! $sees_post instanceof WP_Post ) {
	return;
}
$sees_cat   = sees_post_primary_category( $sees_post );
$sees_title = get_the_title( $sees_post );
?>
<article class="post-card<?php echo $sees_featured ? ' post-card--featured' : ''; ?> reveal">
	<div class="post-card__media">
		<?php if ( has_post_thumbnail( $sees_post ) ) : ?>
			<?php echo get_the_post_thumbnail( $sees_post, $sees_featured ? 'sees-wide' : 'sees-card', array( 'alt' => '', 'sizes' => $sees_featured ? '(max-width: 900px) 100vw, 60vw' : '(max-width: 760px) 100vw, (max-width: 1200px) 50vw, 420px' ) ); ?>
		<?php else : ?>
			<span class="post-card__placeholder" aria-hidden="true"><?php echo sees_image( 0, 'logo-mark.png', 'medium', array( 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		<?php endif; ?>
		<?php if ( $sees_cat ) : ?>
			<span class="post-card__tag"><?php echo esc_html( $sees_cat->name ); ?></span>
		<?php endif; ?>
	</div>
	<div class="post-card__body">
		<p class="post-card__meta">
			<time datetime="<?php echo esc_attr( get_the_date( 'c', $sees_post ) ); ?>"><?php echo esc_html( get_the_date( '', $sees_post ) ); ?></time>
			<span aria-hidden="true">·</span>
			<span><?php echo esc_html( sees_reading_time( $sees_post ) ); ?></span>
		</p>
		<h2 class="post-card__title"><a href="<?php echo esc_url( get_permalink( $sees_post ) ); ?>"><?php echo esc_html( $sees_title ); ?></a></h2>
		<p class="post-card__excerpt"><?php echo esc_html( get_the_excerpt( $sees_post ) ); ?></p>
		<span class="post-card__more" aria-hidden="true"><?php esc_html_e( 'Read more', 'sees' ); ?> <?php echo sees_icon( 'arrow', 16, array( 'stroke' => 1.7 ) ); // phpcs:ignore ?></span>
	</div>
</article>
