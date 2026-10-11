<?php
/**
 * Contact Form 7 tweaks.
 *
 * @package sees
 */

// Don't wrap form controls in <p> tags — the theme lays the form out with grid.
add_filter( 'wpcf7_autop_or_not', '__return_false' );
