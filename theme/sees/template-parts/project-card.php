<?php
/**
 * Project card (Projects grid).
 *
 * @package sees
 *
 * @var array $args { post: WP_Post }
 */

$sees_post  = $args['post'];
$sees_terms = get_the_terms( $sees_post, 'project_category' );
$sees_term  = ( $sees_terms && ! is_wp_error( $sees_terms ) ) ? $sees_terms[0] : null;
$sees_slugs = $sees_terms && ! is_wp_error( $sees_terms ) ? wp_list_pluck( $sees_terms, 'slug' ) : array();
$sees_meta  = array_filter( array(
	sees_field( 'client', '', $sees_post->ID ),
	sees_field( 'city', '', $sees_post->ID ),
	sees_field( 'year', '', $sees_post->ID ),
) );
?>
<article class="project-card" data-cats="<?php echo esc_attr( implode( ' ', $sees_slugs ) ); ?>">
	<div class="project-card__media">
		<?php
		echo sees_image( get_post_thumbnail_id( $sees_post ), 'engineer.jpg', 'sees-card', array( // phpcs:ignore WordPress.Security.EscapeOutput
			'alt'   => '',
			'sizes' => '(max-width: 760px) 100vw, (max-width: 1200px) 50vw, 420px',
		) );
		?>
		<?php if ( $sees_term ) : ?>
			<span class="project-card__tag"><?php echo esc_html( $sees_term->name ); ?></span>
		<?php endif; ?>
	</div>
	<div class="project-card__body">
		<h2 class="project-card__title"><a href="<?php echo esc_url( get_permalink( $sees_post ) ); ?>"><?php echo esc_html( get_the_title( $sees_post ) ); ?></a></h2>
		<?php if ( $sees_meta ) : ?>
			<p class="project-card__meta"><?php echo esc_html( implode( ' · ', $sees_meta ) ); ?></p>
		<?php endif; ?>
		<span class="project-card__more" aria-hidden="true"><?php esc_html_e( 'View case study', 'sees' ); ?> <?php echo sees_icon( 'arrow', 14, array( 'stroke' => 2 ) ); // phpcs:ignore ?></span>
	</div>
</article>
