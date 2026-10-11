<?php
/**
 * Search form.
 *
 * @package sees
 */

$sees_search_id = wp_unique_id( 'search-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $sees_search_id ); ?>"><?php esc_html_e( 'Search for:', 'sees' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $sees_search_id ); ?>" class="field search-form__input" placeholder="<?php esc_attr_e( 'Search the site…', 'sees' ); ?>" value="<?php echo get_search_query(); ?>" name="s">
	<button type="submit" class="btn btn--primary"><?php esc_html_e( 'Search', 'sees' ); ?></button>
</form>
