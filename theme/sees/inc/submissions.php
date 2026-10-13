<?php
/**
 * Submissions: a copy of every quote request and job application, kept in wp-admin.
 *
 * - Saved before the email is sent, so nothing is lost if mail delivery fails.
 * - CVs are copied to a private folder (wp-content/uploads/sees-private/, blocked by
 *   .htaccess) and can only be downloaded by logged-in editors/administrators.
 * - Deleted automatically after a retention period (Saudi PDPL: keep no longer than
 *   needed). Covered by Tools → Export / Erase Personal Data.
 *
 * @package sees
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const SEES_SUB_CAP = 'edit_others_pages'; // Editors and administrators.

/* ---------------------------------------------------------------------------
 * Post type
 * ------------------------------------------------------------------------ */
add_action( 'init', function () {
	register_post_type( 'sees_submission', array(
		'labels'          => array(
			'name'          => __( 'Submissions', 'sees' ),
			'singular_name' => __( 'Submission', 'sees' ),
			'edit_item'     => __( 'Submission', 'sees' ),
			'all_items'     => __( 'All submissions', 'sees' ),
			'not_found'     => __( 'No submissions yet.', 'sees' ),
			'search_items'  => __( 'Search submissions', 'sees' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'show_in_rest'    => false,
		'menu_icon'       => 'dashicons-email-alt',
		'menu_position'   => 25,
		'supports'        => array( 'title' ),
		'capability_type' => 'page',
		'map_meta_cap'    => true,
		'capabilities'    => array( 'create_posts' => 'do_not_allow' ), // Only forms create submissions.
	) );
} );

/**
 * Human labels for the theme's form fields.
 */
function sees_submission_labels(): array {
	return array(
		'your-name'     => __( 'Name', 'sees' ),
		'your-company'  => __( 'Company', 'sees' ),
		'your-email'    => __( 'Email', 'sees' ),
		'your-phone'    => __( 'Phone', 'sees' ),
		'your-solution' => __( 'Solution', 'sees' ),
		'your-link'     => __( 'LinkedIn / portfolio', 'sees' ),
		'your-message'  => __( 'Message', 'sees' ),
		'consent'       => __( 'Privacy consent', 'sees' ),
	);
}

/* ---------------------------------------------------------------------------
 * Private storage for CVs
 * ------------------------------------------------------------------------ */
function sees_private_dir(): string {
	$dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'sees-private';
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
	}
	if ( ! file_exists( $dir . '/.htaccess' ) ) {
		// Apache 2.4 and 2.2 syntax: nothing in this folder is served to browsers.
		file_put_contents( $dir . '/.htaccess', "Require all denied\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		file_put_contents( $dir . '/index.php', "<?php // Silence.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	return $dir;
}

/* ---------------------------------------------------------------------------
 * Save a copy of each submission (theme forms only)
 * ------------------------------------------------------------------------ */
add_filter( 'wpcf7_before_send_mail', function ( $form, &$abort, $submission ) {
	if ( $abort || ! $form instanceof WPCF7_ContactForm || ! $submission ) {
		return $form; // e.g. an application to a closed vacancy (refused earlier).
	}
	$is_job  = function_exists( 'sees_is_job_form' ) && sees_is_job_form( (int) $form->id() );
	$is_ours = $is_job || false !== strpos( (string) $form->prop( 'form' ), 'sees-form' );
	if ( ! $is_ours ) {
		return $form;
	}

	$data   = array();
	$labels = sees_submission_labels();
	foreach ( (array) $submission->get_posted_data() as $key => $value ) {
		if ( 0 === strpos( $key, '_' ) || 'your-cv' === $key ) {
			continue;
		}
		$value        = is_array( $value ) ? implode( ', ', array_map( 'sanitize_text_field', $value ) ) : sanitize_textarea_field( (string) $value );
		$data[ $key ] = 'consent' === $key ? ( $value ? __( 'Given', 'sees' ) : '' ) : $value;
	}

	$job     = $is_job ? (int) $submission->get_meta( 'container_post_id' ) : 0;
	$name    = $data['your-name'] ?? '';
	$subject = $is_job ? get_the_title( $job ) : ( $data['your-company'] ?? '' );

	$id = wp_insert_post( array(
		'post_type'   => 'sees_submission',
		'post_status' => 'publish',
		'post_title'  => trim( $name . ' — ' . $subject, ' —' ),
	), true );
	if ( is_wp_error( $id ) ) {
		return $form;
	}

	update_post_meta( $id, '_sees_type', $is_job ? 'application' : 'quote' );
	update_post_meta( $id, '_sees_fields', $data );
	update_post_meta( $id, '_sees_email', strtolower( (string) ( $data['your-email'] ?? '' ) ) );
	update_post_meta( $id, '_sees_form', (int) $form->id() );
	update_post_meta( $id, '_sees_lang', function_exists( 'pll_current_language' ) && pll_current_language() ? pll_current_language() : ( 'ar' === substr( (string) $form->locale(), 0, 2 ) ? 'ar' : 'en' ) );
	update_post_meta( $id, '_sees_unread', 1 );
	if ( $job ) {
		update_post_meta( $id, '_sees_job', $job );
		update_post_meta( $id, '_sees_job_reference', (string) sees_job_field( $job, 'job_reference', '', true ) );
	}

	// Keep a private copy of the CV (CF7 deletes its temporary upload after sending).
	foreach ( (array) $submission->uploaded_files() as $field => $paths ) {
		foreach ( (array) $paths as $path ) {
			if ( $path && is_readable( $path ) ) {
				$dir  = sees_private_dir() . '/' . gmdate( 'Y/m' );
				wp_mkdir_p( $dir );
				$name_on_disk = wp_generate_password( 16, false ) . '-' . sanitize_file_name( basename( $path ) );
				if ( copy( $path, $dir . '/' . $name_on_disk ) ) {
					update_post_meta( $id, '_sees_file', gmdate( 'Y/m' ) . '/' . $name_on_disk );
					update_post_meta( $id, '_sees_file_name', sanitize_file_name( basename( $path ) ) );
				}
				break 2; // One CV per application.
			}
		}
	}
	return $form;
}, 20, 3 );

// Delete the stored CV with its submission.
add_action( 'before_delete_post', function ( $post_id ) {
	if ( 'sees_submission' !== get_post_type( $post_id ) ) {
		return;
	}
	$file = (string) get_post_meta( $post_id, '_sees_file', true );
	if ( $file && false === strpos( $file, '..' ) ) {
		$path = sees_private_dir() . '/' . $file;
		if ( is_file( $path ) ) {
			wp_delete_file( $path );
		}
	}
} );

/* ---------------------------------------------------------------------------
 * CV download (logged-in editors/administrators only)
 * ------------------------------------------------------------------------ */
function sees_submission_file_url( int $id ): string {
	return wp_nonce_url( admin_url( 'admin-post.php?action=sees_cv&id=' . $id ), 'sees_cv_' . $id );
}

add_action( 'admin_post_sees_cv', function () {
	$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
	check_admin_referer( 'sees_cv_' . $id );
	if ( ! current_user_can( SEES_SUB_CAP ) || 'sees_submission' !== get_post_type( $id ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'sees' ), 403 );
	}
	$file = (string) get_post_meta( $id, '_sees_file', true );
	$path = $file && false === strpos( $file, '..' ) ? sees_private_dir() . '/' . $file : '';
	if ( ! $path || ! is_file( $path ) ) {
		wp_die( esc_html__( 'The file is no longer available.', 'sees' ), 404 );
	}
	$type = wp_check_filetype( $path );
	nocache_headers();
	header( 'Content-Type: ' . ( $type['type'] ?: 'application/octet-stream' ) );
	header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( (string) get_post_meta( $id, '_sees_file_name', true ) ?: basename( $path ) ) . '"' );
	header( 'Content-Length: ' . filesize( $path ) );
	header( 'X-Content-Type-Options: nosniff' );
	readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	exit;
} );

/* ---------------------------------------------------------------------------
 * Admin: menu, views, columns, detail box
 * ------------------------------------------------------------------------ */
add_action( 'admin_menu', function () {
	$base = 'edit.php?post_type=sees_submission';
	add_submenu_page( $base, __( 'Quote requests', 'sees' ), __( 'Quote requests', 'sees' ), SEES_SUB_CAP, $base . '&sees_type=quote' );
	add_submenu_page( $base, __( 'Job applications', 'sees' ), __( 'Job applications', 'sees' ), SEES_SUB_CAP, $base . '&sees_type=application' );
	add_submenu_page( $base, __( 'Retention', 'sees' ), __( 'Retention', 'sees' ), 'manage_options', 'sees-submissions-retention', 'sees_submissions_retention_page' );

	// Unread count bubble on the menu.
	global $menu;
	$unread = sees_submissions_unread_count();
	if ( $unread ) {
		foreach ( (array) $menu as $i => $item ) {
			if ( $base === ( $item[2] ?? '' ) ) {
				$menu[ $i ][0] .= ' <span class="awaiting-mod"><span class="pending-count">' . (int) $unread . '</span></span>'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			}
		}
	}
}, 20 );

function sees_submissions_unread_count(): int {
	$q = new WP_Query( array(
		'post_type'      => 'sees_submission',
		'post_status'    => 'publish',
		'meta_key'       => '_sees_unread', // phpcs:ignore WordPress.DB.SlowDBQuery
		'meta_value'     => '1',            // phpcs:ignore WordPress.DB.SlowDBQuery
		'fields'         => 'ids',
		'posts_per_page' => 1,
	) );
	return (int) $q->found_posts;
}

// Restrict the list to editors/admins and filter by type.
add_action( 'pre_get_posts', function ( WP_Query $q ) {
	if ( ! is_admin() || ! $q->is_main_query() || 'sees_submission' !== $q->get( 'post_type' ) ) {
		return;
	}
	$type = isset( $_GET['sees_type'] ) ? sanitize_key( wp_unslash( $_GET['sees_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$job  = isset( $_GET['sees_job'] ) ? absint( $_GET['sees_job'] ) : 0;                          // phpcs:ignore WordPress.Security.NonceVerification
	$meta = array();
	if ( in_array( $type, array( 'quote', 'application' ), true ) ) {
		$meta[] = array( 'key' => '_sees_type', 'value' => $type );
	}
	if ( $job ) {
		$ids    = function_exists( 'sees_job_translation_ids' ) ? sees_job_translation_ids( $job ) : array( $job );
		$meta[] = array( 'key' => '_sees_job', 'value' => $ids, 'compare' => 'IN' );
	}
	if ( $meta ) {
		$q->set( 'meta_query', $meta );
	}
} );

add_filter( 'manage_sees_submission_posts_columns', function () {
	return array(
		'cb'        => '<input type="checkbox" />',
		'title'     => __( 'Name', 'sees' ),
		'sees_type' => __( 'Type', 'sees' ),
		'sees_about'=> __( 'About', 'sees' ),
		'sees_email'=> __( 'Email', 'sees' ),
		'sees_phone'=> __( 'Phone', 'sees' ),
		'sees_cv'   => __( 'CV', 'sees' ),
		'date'      => __( 'Received', 'sees' ),
	);
} );

add_action( 'manage_sees_submission_posts_custom_column', function ( $column, $id ) {
	$f = (array) get_post_meta( $id, '_sees_fields', true );
	switch ( $column ) {
		case 'sees_type':
			echo esc_html( 'application' === get_post_meta( $id, '_sees_type', true ) ? __( 'Job application', 'sees' ) : __( 'Quote request', 'sees' ) );
			echo get_post_meta( $id, '_sees_unread', true ) ? ' <strong>• ' . esc_html__( 'New', 'sees' ) . '</strong>' : '';
			break;
		case 'sees_about':
			$job = (int) get_post_meta( $id, '_sees_job', true );
			echo esc_html( $job ? get_the_title( $job ) . ( get_post_meta( $id, '_sees_job_reference', true ) ? ' (' . get_post_meta( $id, '_sees_job_reference', true ) . ')' : '' ) : ( $f['your-solution'] ?? '—' ) );
			break;
		case 'sees_email':
			$email = (string) ( $f['your-email'] ?? '' );
			echo $email ? '<a href="' . esc_url( 'mailto:' . $email ) . '">' . esc_html( $email ) . '</a>' : '—';
			break;
		case 'sees_phone':
			echo esc_html( $f['your-phone'] ?? '—' );
			break;
		case 'sees_cv':
			echo get_post_meta( $id, '_sees_file', true ) ? '<a class="button button-small" href="' . esc_url( sees_submission_file_url( (int) $id ) ) . '">' . esc_html__( 'Download CV', 'sees' ) . '</a>' : '—';
			break;
	}
}, 10, 2 );

// Read-only detail box instead of an editor; opening a submission marks it as read.
add_action( 'add_meta_boxes_sees_submission', function ( $post ) {
	remove_meta_box( 'submitdiv', 'sees_submission', 'side' );
	add_meta_box( 'sees_submission_details', __( 'Details', 'sees' ), 'sees_submission_details_box', 'sees_submission', 'normal', 'high' );
	add_meta_box( 'sees_submission_actions', __( 'Actions', 'sees' ), 'sees_submission_actions_box', 'sees_submission', 'side', 'high' );
	delete_post_meta( $post->ID, '_sees_unread' );
} );

function sees_submission_details_box( WP_Post $post ): void {
	$f      = (array) get_post_meta( $post->ID, '_sees_fields', true );
	$labels = sees_submission_labels();
	$job    = (int) get_post_meta( $post->ID, '_sees_job', true );
	echo '<table class="widefat striped" role="presentation"><tbody>';
	printf( '<tr><th style="width:200px">%s</th><td>%s</td></tr>', esc_html__( 'Received', 'sees' ), esc_html( get_the_date( 'j F Y, H:i', $post ) ) );
	printf( '<tr><th>%s</th><td>%s</td></tr>', esc_html__( 'Language', 'sees' ), esc_html( strtoupper( (string) get_post_meta( $post->ID, '_sees_lang', true ) ) ) );
	if ( $job ) {
		printf( '<tr><th>%s</th><td><a href="%s">%s</a> %s</td></tr>', esc_html__( 'Vacancy', 'sees' ), esc_url( (string) get_edit_post_link( $job ) ), esc_html( get_the_title( $job ) ), esc_html( (string) get_post_meta( $post->ID, '_sees_job_reference', true ) ) );
	}
	foreach ( $f as $key => $value ) {
		printf( '<tr><th>%s</th><td>%s</td></tr>', esc_html( $labels[ $key ] ?? $key ), nl2br( esc_html( (string) $value ) ) );
	}
	if ( get_post_meta( $post->ID, '_sees_file', true ) ) {
		printf( '<tr><th>%s</th><td><a class="button" href="%s">%s</a> %s</td></tr>', esc_html__( 'CV', 'sees' ), esc_url( sees_submission_file_url( $post->ID ) ), esc_html__( 'Download CV', 'sees' ), esc_html( (string) get_post_meta( $post->ID, '_sees_file_name', true ) ) );
	}
	echo '</tbody></table>';
}

function sees_submission_actions_box( WP_Post $post ): void {
	$f     = (array) get_post_meta( $post->ID, '_sees_fields', true );
	$email = (string) ( $f['your-email'] ?? '' );
	if ( $email ) {
		printf( '<p><a class="button button-primary" href="%s">%s</a></p>', esc_url( 'mailto:' . $email ), esc_html__( 'Reply by email', 'sees' ) );
	}
	printf( '<p><a class="submitdelete" style="color:#b32d2e" href="%s">%s</a></p>', esc_url( (string) get_delete_post_link( $post->ID, '', true ) ), esc_html__( 'Delete permanently', 'sees' ) );
	$months = sees_submissions_retention_months();
	/* translators: %d: months */
	printf( '<p class="description">%s</p>', esc_html( sprintf( __( 'Deleted automatically %d months after it was received.', 'sees' ), $months ) ) );
}

// Hide "Add New" and the title field editing (submissions are read-only).
add_action( 'admin_head', function () {
	$screen = get_current_screen();
	if ( $screen && 'sees_submission' === $screen->post_type ) {
		echo '<style>.page-title-action{display:none}#titlediv #title{pointer-events:none;background:transparent;border-color:transparent;box-shadow:none;font-weight:600}</style>';
	}
} );

/* ---------------------------------------------------------------------------
 * Applications on the Jobs screens
 * ------------------------------------------------------------------------ */
function sees_job_application_count( int $job ): int {
	$ids = function_exists( 'sees_job_translation_ids' ) ? sees_job_translation_ids( $job ) : array( $job );
	$q   = new WP_Query( array(
		'post_type'      => 'sees_submission',
		'post_status'    => 'publish',
		'meta_query'     => array( array( 'key' => '_sees_job', 'value' => $ids, 'compare' => 'IN' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
		'fields'         => 'ids',
		'posts_per_page' => 1,
	) );
	return (int) $q->found_posts;
}

add_filter( 'manage_sees_job_posts_columns', function ( array $columns ): array {
	$columns['sees_applications'] = __( 'Applications', 'sees' );
	return $columns;
}, 20 );
add_action( 'manage_sees_job_posts_custom_column', function ( $column, $job ) {
	if ( 'sees_applications' === $column && current_user_can( SEES_SUB_CAP ) ) {
		$count = sees_job_application_count( (int) $job );
		echo $count ? '<a href="' . esc_url( admin_url( 'edit.php?post_type=sees_submission&sees_job=' . (int) $job ) ) . '">' . (int) $count . '</a>' : '0';
	}
}, 10, 2 );
add_action( 'add_meta_boxes_sees_job', function ( $post ) {
	if ( ! current_user_can( SEES_SUB_CAP ) ) {
		return;
	}
	add_meta_box( 'sees_job_applications', __( 'Applications', 'sees' ), function ( $post ) {
		$count = sees_job_application_count( $post->ID );
		/* translators: %d: number of applications */
		printf( '<p><strong>%s</strong></p>', esc_html( sprintf( _n( '%d application received', '%d applications received', $count, 'sees' ), $count ) ) );
		if ( $count ) {
			printf( '<p><a class="button" href="%s">%s</a></p>', esc_url( admin_url( 'edit.php?post_type=sees_submission&sees_job=' . $post->ID ) ), esc_html__( 'View applications', 'sees' ) );
		}
	}, 'sees_job', 'side', 'high' );
} );

/* ---------------------------------------------------------------------------
 * Retention: automatic deletion (default 12 months)
 * ------------------------------------------------------------------------ */
function sees_submissions_retention_months(): int {
	return max( 1, (int) get_option( 'sees_submission_retention_months', 12 ) );
}

add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'sees_submissions_cleanup' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'sees_submissions_cleanup' );
	}
} );
add_action( 'switch_theme', function () {
	wp_clear_scheduled_hook( 'sees_submissions_cleanup' );
} );

function sees_submissions_cleanup(): int {
	$old = get_posts( array(
		'post_type'      => 'sees_submission',
		'post_status'    => 'any',
		'posts_per_page' => 200,
		'fields'         => 'ids',
		'date_query'     => array( array( 'before' => sees_submissions_retention_months() . ' months ago' ) ),
	) );
	foreach ( $old as $id ) {
		wp_delete_post( $id, true );
	}
	return count( $old );
}
add_action( 'sees_submissions_cleanup', 'sees_submissions_cleanup' );

function sees_submissions_retention_page(): void {
	if ( isset( $_POST['sees_retention'] ) && check_admin_referer( 'sees_retention' ) && current_user_can( 'manage_options' ) ) {
		update_option( 'sees_submission_retention_months', max( 1, min( 120, absint( $_POST['sees_retention'] ) ) ), false );
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'sees' ) . '</p></div>';
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Submission retention', 'sees' ); ?></h1>
		<p><?php esc_html_e( 'Quote requests and job applications (including CVs) are deleted automatically after this period. The Saudi Personal Data Protection Law expects personal data to be kept no longer than needed — make sure this matches the period stated in your privacy policy.', 'sees' ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 'sees_retention' ); ?>
			<label><?php esc_html_e( 'Keep submissions for', 'sees' ); ?>
				<input type="number" name="sees_retention" min="1" max="120" value="<?php echo esc_attr( (string) sees_submissions_retention_months() ); ?>" class="small-text"> <?php esc_html_e( 'months', 'sees' ); ?>
			</label>
			<?php submit_button( __( 'Save', 'sees' ), 'primary', 'submit', false ); ?>
		</form>
	</div>
	<?php
}

/* ---------------------------------------------------------------------------
 * WordPress privacy tools: Export / Erase Personal Data
 * ------------------------------------------------------------------------ */
function sees_submissions_by_email( string $email ): array {
	return get_posts( array(
		'post_type'      => 'sees_submission',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'meta_key'       => '_sees_email',            // phpcs:ignore WordPress.DB.SlowDBQuery
		'meta_value'     => strtolower( $email ),     // phpcs:ignore WordPress.DB.SlowDBQuery
	) );
}

add_filter( 'wp_privacy_personal_data_exporters', function ( $exporters ) {
	$exporters['sees-submissions'] = array(
		'exporter_friendly_name' => __( 'SEES form submissions', 'sees' ),
		'callback'               => function ( $email ) {
			$items  = array();
			$labels = sees_submission_labels();
			foreach ( sees_submissions_by_email( $email ) as $post ) {
				$data = array( array( 'name' => __( 'Received', 'sees' ), 'value' => get_the_date( 'c', $post ) ) );
				foreach ( (array) get_post_meta( $post->ID, '_sees_fields', true ) as $k => $v ) {
					$data[] = array( 'name' => $labels[ $k ] ?? $k, 'value' => $v );
				}
				$items[] = array( 'group_id' => 'sees-submissions', 'group_label' => __( 'Form submissions', 'sees' ), 'item_id' => 'sees-submission-' . $post->ID, 'data' => $data );
			}
			return array( 'data' => $items, 'done' => true );
		},
	);
	return $exporters;
} );

add_filter( 'wp_privacy_personal_data_erasers', function ( $erasers ) {
	$erasers['sees-submissions'] = array(
		'eraser_friendly_name' => __( 'SEES form submissions', 'sees' ),
		'callback'             => function ( $email ) {
			$posts = sees_submissions_by_email( $email );
			foreach ( $posts as $post ) {
				wp_delete_post( $post->ID, true ); // Also deletes the stored CV.
			}
			return array( 'items_removed' => count( $posts ) > 0, 'items_retained' => false, 'messages' => array(), 'done' => true );
		},
	);
	return $erasers;
} );
