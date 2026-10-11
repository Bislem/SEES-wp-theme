<?php
/**
 * Eyebrow label with a short teal line.
 *
 * @package sees
 *
 * @var array $args { text, dark (bool), grow (bool), tag (string) }
 */

$sees_tag = $args['tag'] ?? 'p';
$sees_cls = 'eyebrow' . ( empty( $args['dark'] ) ? '' : ' eyebrow--dark' ) . ( empty( $args['grow'] ) ? '' : ' eyebrow--grow' );
printf(
	'<%1$s class="%2$s">%3$s <span class="eyebrow__line" aria-hidden="true"></span></%1$s>',
	tag_escape( $sees_tag ),
	esc_attr( $sees_cls ),
	esc_html( $args['text'] ?? '' )
);
