<?php
/**
 * Solution card (Home "Our Solutions" grid).
 *
 * @package sees
 *
 * @var array $args { post: WP_Post }
 */

$sees_post = $args['post'];
$sees_icon = (string) sees_field( 'icon', 'bolt', $sees_post->ID );
?>
<a class="solution-card lift reveal arrow" href="<?php echo esc_url( sees_solution_url( $sees_post ) ); ?>">
	<span class="solution-card__bar" aria-hidden="true"></span>
	<span class="solution-card__icon"><?php echo sees_icon( $sees_icon, 28 ); // phpcs:ignore ?></span>
	<h3 class="solution-card__title"><?php echo esc_html( get_the_title( $sees_post ) ); ?></h3>
	<span class="solution-card__text"><?php echo esc_html( get_the_excerpt( $sees_post ) ); ?></span>
	<span class="solution-card__more" aria-hidden="true"><?php esc_html_e( 'Learn more', 'sees' ); ?> <?php echo sees_icon( 'arrow', 14, array( 'stroke' => 2 ) ); // phpcs:ignore ?></span>
</a>
