<?php
/**
 * Plugin Name: Dev — route mail to Mailpit
 * Description: Local development only. Sends all WordPress mail to the Mailpit container (http://localhost:8025).
 */

add_action( 'phpmailer_init', function ( $mailer ) {
	$mailer->isSMTP();
	$mailer->Host        = 'mailpit';
	$mailer->Port        = 1025;
	$mailer->SMTPAuth    = false;
	$mailer->SMTPAutoTLS = false;
} );
