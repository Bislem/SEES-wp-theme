<?php
/**
 * SEES theme bootstrap.
 *
 * @package sees
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SEES_VERSION', '0.1.0' );
define( 'SEES_DIR', get_template_directory() );
define( 'SEES_URI', get_template_directory_uri() );

require SEES_DIR . '/inc/helpers.php';
require SEES_DIR . '/inc/icons.php';
require SEES_DIR . '/inc/setup.php';
require SEES_DIR . '/inc/enqueue.php';
require SEES_DIR . '/inc/post-types.php';
require SEES_DIR . '/inc/acf.php';
require SEES_DIR . '/inc/menus.php';
require SEES_DIR . '/inc/polylang.php';
require SEES_DIR . '/inc/contact-form.php';
