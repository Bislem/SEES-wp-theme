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

// Public website address (shown in the footer) and default contact email.
define( 'SEES_PUBLIC_DOMAIN', 'www.sees-engineering.com' );
define( 'SEES_CONTACT_EMAIL', 'contact@sees-engineering.com' );

require SEES_DIR . '/inc/helpers.php';
require SEES_DIR . '/inc/icons.php';
require SEES_DIR . '/inc/setup.php';
require SEES_DIR . '/inc/enqueue.php';
require SEES_DIR . '/inc/projects-visibility.php';
require SEES_DIR . '/inc/post-types.php';
require SEES_DIR . '/inc/jobs.php';
require SEES_DIR . '/inc/job-applications.php';
require SEES_DIR . '/inc/submissions.php';
require SEES_DIR . '/inc/downloads.php';
require SEES_DIR . '/inc/blog.php';
require SEES_DIR . '/inc/acf.php';
require SEES_DIR . '/inc/menus.php';
require SEES_DIR . '/inc/polylang.php';
require SEES_DIR . '/inc/contact-form.php';
require SEES_DIR . '/inc/setup-check.php';
require SEES_DIR . '/inc/installer.php';
