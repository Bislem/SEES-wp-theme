<?php
/**
 * Job applications: the standard application form every vacancy uses automatically.
 *
 * The automatic setup (inc/installer.php) creates one Contact Form 7 form per language
 * and stores their IDs in the `sees_job_forms` option. A vacancy uses its own form when
 * an editor pasted a custom shortcode, otherwise the standard form in its language.
 *
 * Each application is emailed to the careers address (Careers page → Applications tab,
 * falling back to the contact email) with the CV attached, and the applicant receives
 * a confirmation in their language. Applications to closed vacancies are refused.
 *
 * @package sees
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * IDs of the standard application forms, per language.
 *
 * @return array<string,int>
 */
function sees_job_forms(): array {
	return array_map( 'intval', (array) get_option( 'sees_job_forms', array() ) );
}

function sees_is_job_form( int $form_id ): bool {
	return in_array( $form_id, sees_job_forms(), true );
}

/**
 * Standard application form shortcode for a vacancy's language ('' when unavailable).
 */
function sees_job_default_form_shortcode( int $post_id ): string {
	if ( ! function_exists( 'wpcf7_contact_form' ) ) {
		return '';
	}
	$forms = sees_job_forms();
	$lang  = sees_job_post_language( $post_id );
	$id    = $forms[ $lang ] ?? ( $forms['en'] ?? 0 );
	return ( $id && wpcf7_contact_form( $id ) ) ? sprintf( '[contact-form-7 id="%d"]', $id ) : '';
}

/**
 * Address that receives applications.
 */
function sees_job_applications_email(): string {
	$pages = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => 5,
		'fields'         => 'ids',
		'lang'           => '',
		'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
		'meta_value'     => 'page-jobs.php',     // phpcs:ignore WordPress.DB.SlowDBQuery
		'no_found_rows'  => true,
	) );
	foreach ( $pages as $page_id ) {
		$email = sanitize_email( (string) get_post_meta( $page_id, 'applications_email', true ) );
		if ( is_email( $email ) ) {
			return $email;
		}
	}
	$contact = sees_contact()['email'];
	return is_email( $contact ) ? $contact : (string) get_option( 'admin_email' );
}

/**
 * The vacancy a submission was sent from.
 */
function sees_job_from_submission(): int {
	if ( ! class_exists( 'WPCF7_Submission' ) ) {
		return 0;
	}
	$submission = WPCF7_Submission::get_instance();
	$post_id    = $submission ? (int) $submission->get_meta( 'container_post_id' ) : 0;
	return ( $post_id && 'sees_job' === get_post_type( $post_id ) ) ? $post_id : 0;
}

// [_job_reference] mail tag.
add_filter( 'wpcf7_special_mail_tags', function ( $output, $name ) {
	if ( '_job_reference' === $name ) {
		$job = sees_job_from_submission();
		return $job ? (string) sees_job_field( $job, 'job_reference', '—', true ) : '—';
	}
	return $output;
}, 10, 2 );

// Send applications to the careers address.
add_filter( 'wpcf7_mail_components', function ( $components, $form, $mail ) {
	if ( $form instanceof WPCF7_ContactForm && sees_is_job_form( (int) $form->id() ) && 'mail' === $mail->name() ) {
		$components['recipient'] = sees_job_applications_email();
	}
	return $components;
}, 10, 3 );

// Refuse applications to vacancies that are closed, past their closing date, or not published.
add_filter( 'wpcf7_before_send_mail', function ( $form, &$abort, $submission ) {
	if ( ! $form instanceof WPCF7_ContactForm || ! sees_is_job_form( (int) $form->id() ) ) {
		return $form;
	}
	$job = sees_job_from_submission();
	if ( ! $job || 'publish' !== get_post_status( $job ) || ! sees_job_is_open( $job ) ) {
		$abort = true;
		$submission->set_status( 'aborted' );
		$submission->set_response( sees_jobs_text( 'closed_notice' ) );
	}
	return $form;
}, 10, 3 );
