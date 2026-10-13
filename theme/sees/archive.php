<?php
/**
 * News archives: categories, tags and dates.
 *
 * @package sees
 */

get_header();

get_template_part( 'template-parts/news-list', null, array(
	'title'       => wp_strip_all_tags( is_category() || is_tag() ? single_term_title( '', false ) : get_the_archive_title() ),
	'subtitle'    => wp_strip_all_tags( (string) get_the_archive_description() ),
	'current_cat' => is_category() ? (int) get_queried_object_id() : 0,
) );

get_footer();
