<?php
/**
 * News page (the Posts page set in Settings → Reading).
 *
 * @package sees
 */

get_header();

$sees_page = sees_news_page();
get_template_part( 'template-parts/news-list', null, array(
	'title'    => $sees_page ? get_the_title( $sees_page ) : __( 'News', 'sees' ),
	'subtitle' => $sees_page ? (string) sees_field( 'header_subtitle', __( 'Company news, project updates and insights from the SEES team.', 'sees' ), $sees_page->ID ) : __( 'Company news, project updates and insights from the SEES team.', 'sees' ),
) );

get_footer();
