<?php
/**
 * Setup checklist.
 *
 * Runs whenever the theme is activated: an admin notice lists what is still missing
 * for the bilingual site to work (plugins, languages, pages, menus, forms…), each with
 * a link to the screen that fixes it. The full list stays available under
 * Appearance → SEES setup.
 *
 * @package sees
 */

const SEES_SETUP_OPTION  = 'sees_setup_check_pending';
const SEES_SETUP_DISMISS = 'sees_setup_check_dismissed';

/**
 * Every activation re-arms the notice for all admins.
 */
add_action( 'after_switch_theme', function () {
	update_option( SEES_SETUP_OPTION, time(), false );
	delete_metadata( 'user', 0, SEES_SETUP_DISMISS, '', true );
} );

/**
 * Run all checks.
 *
 * @return array<int,array{id:string,label:string,ok:bool,required:bool,hint:string,url:string,action:string}>
 */
function sees_setup_checks(): array {
	$checks = array();
	$add    = function ( string $id, string $label, bool $ok, bool $required, string $hint, string $url, string $action = '' ) use ( &$checks ) {
		$checks[] = compact( 'id', 'label', 'ok', 'required', 'hint', 'url', 'action' );
	};

	$has_pll  = function_exists( 'pll_languages_list' );
	$has_acf  = class_exists( 'ACF' );
	$has_cf7  = defined( 'WPCF7_VERSION' );
	$plugins  = admin_url( 'plugin-install.php?s=%s&tab=search&type=term' );

	// 1. Plugins.
	$add( 'polylang', __( 'Polylang plugin', 'sees' ), $has_pll, true,
		__( 'Provides English / Arabic content, URLs and the language switcher.', 'sees' ),
		sprintf( $plugins, 'polylang' ), __( 'Install', 'sees' ) );
	$add( 'acf', __( 'Advanced Custom Fields plugin', 'sees' ), $has_acf, true,
		__( 'All editable page fields (texts, photos, contact details) use ACF.', 'sees' ),
		sprintf( $plugins, 'advanced-custom-fields' ), __( 'Install', 'sees' ) );
	$add( 'cf7', __( 'Contact Form 7 plugin', 'sees' ), $has_cf7, true,
		__( 'Powers the quote request form on the Contact page.', 'sees' ),
		sprintf( $plugins, 'contact-form-7' ), __( 'Install', 'sees' ) );

	// 2. Languages.
	$langs = $has_pll ? (array) pll_languages_list( array( 'fields' => 'slug' ) ) : array();
	$add( 'languages', __( 'English and Arabic languages in Polylang', 'sees' ),
		in_array( 'en', $langs, true ) && in_array( 'ar', $langs, true ), true,
		__( 'Add English (en_US) and Arabic (ar). The theme switches to right-to-left automatically on Arabic pages.', 'sees' ),
		admin_url( 'admin.php?page=mlang' ), __( 'Add languages', 'sees' ) );

	if ( $has_pll && function_exists( 'PLL' ) && isset( PLL()->options ) ) {
		$opts = PLL()->options;
		$add( 'pll_urls', __( 'Language URLs (/en/, /ar/)', 'sees' ),
			1 === (int) $opts['force_lang'] && empty( $opts['hide_default'] ), false,
			__( 'Recommended: "The language is set from the directory name" and keep /en/ visible.', 'sees' ),
			admin_url( 'admin.php?page=mlang_settings' ), __( 'URL settings', 'sees' ) );
	}

	// 3. WordPress Arabic language pack (dates, form messages, admin).
	$add( 'langpack', __( 'Arabic WordPress language pack', 'sees' ),
		in_array( 'ar', get_available_languages(), true ), true,
		__( 'Needed for Arabic dates and system messages. Polylang offers to download it when you add Arabic.', 'sees' ),
		admin_url( 'options-general.php' ), __( 'Site language', 'sees' ) );

	// 4. Permalinks.
	$add( 'permalinks', __( 'Pretty permalinks (Post name)', 'sees' ),
		'' !== (string) get_option( 'permalink_structure' ), true,
		__( 'Choose "Post name" so URLs look like /en/about/.', 'sees' ),
		admin_url( 'options-permalink.php' ), __( 'Permalinks', 'sees' ) );

	// 5. Front page.
	$front = (int) get_option( 'page_on_front' );
	$front_ok = 'page' === get_option( 'show_on_front' ) && $front > 0;
	if ( $front_ok && $has_pll && in_array( 'ar', $langs, true ) ) {
		$front_ok = (bool) pll_get_post( $front, 'ar' ) && (bool) pll_get_post( $front, 'en' );
	}
	$add( 'front', __( 'Static Home page in English and Arabic', 'sees' ), $front_ok, true,
		__( 'Settings → Reading → "A static page", then translate that page into Arabic.', 'sees' ),
		admin_url( 'options-reading.php' ), __( 'Reading settings', 'sees' ) );

	// 6. Template pages, per language.
	$templates = array(
		'page-about.php'     => __( 'About', 'sees' ),
		'page-solutions.php' => __( 'Our Solutions', 'sees' ),
		'page-contact.php'   => __( 'Contact', 'sees' ),
	);
	$check_langs = $langs ? array_intersect( array( 'en', 'ar' ), $langs ) : array( '' );
	foreach ( $templates as $template => $name ) {
		$missing = array();
		foreach ( $check_langs as $lang ) {
			$args = array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => $template,           // phpcs:ignore WordPress.DB.SlowDBQuery
				'no_found_rows'  => true,
			);
			if ( $lang ) {
				$args['lang'] = $lang;
			}
			if ( ! get_posts( $args ) ) {
				$missing[] = $lang ? strtoupper( $lang ) : __( 'page', 'sees' );
			}
		}
		$add( 'tpl-' . $template,
			/* translators: %s: page name, e.g. About */
			sprintf( __( '"%s" page in each language, with its template', 'sees' ), $name ),
			! $missing, true,
			$missing
				/* translators: 1: page name, 2: missing languages, 3: template name */
				? sprintf( __( 'Missing: %2$s. Create the page, choose the "%1$s" template (Page → Template), and link the translations.', 'sees' ), $name, implode( ', ', $missing ) )
				/* translators: %s: template name */
				: sprintf( __( 'Uses the "%s" template.', 'sees' ), $name ),
			admin_url( 'post-new.php?post_type=page' ), __( 'Add page', 'sees' ) );
	}

	// 7. Menus (the theme falls back to an automatic menu, so this is a recommendation).
	$menu_ok = false;
	if ( $has_pll && function_exists( 'PLL' ) && isset( PLL()->options ) ) {
		$assigned = PLL()->options['nav_menus'][ get_stylesheet() ]['primary'] ?? array();
		$menu_ok  = $langs && ! array_diff( array_intersect( array( 'en', 'ar' ), $langs ), array_keys( array_filter( (array) $assigned ) ) );
	} else {
		$menu_ok = has_nav_menu( 'primary' );
	}
	$add( 'menus', __( 'Primary menu for each language', 'sees' ), $menu_ok, false,
		__( 'Optional: without it the header shows an automatic menu. Assign one menu per language to "Primary menu".', 'sees' ),
		admin_url( 'nav-menus.php?action=locations' ), __( 'Menus', 'sees' ) );

	// 8. Contact form on each Contact page.
	$form_missing = array();
	if ( $has_cf7 && $has_acf ) {
		foreach ( $check_langs as $lang ) {
			$args = array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => 'page-contact.php',  // phpcs:ignore WordPress.DB.SlowDBQuery
				'no_found_rows'  => true,
			);
			if ( $lang ) {
				$args['lang'] = $lang;
			}
			$page = get_posts( $args );
			if ( ! $page ) {
				continue; // Reported by the template check above.
			}
			$code = trim( (string) get_field( 'form_shortcode', $page[0]->ID ) );
			$id   = preg_match( '/id=["\']?([\w-]+)/', $code, $m ) ? $m[1] : '';
			$form = null;
			if ( $id ) {
				$form = is_numeric( $id ) ? wpcf7_contact_form( (int) $id ) : ( function_exists( 'wpcf7_get_contact_form_by_hash' ) ? wpcf7_get_contact_form_by_hash( $id ) : null );
			}
			$ok   = $code && $form;
			if ( ! $ok ) {
				$form_missing[] = $lang ? strtoupper( $lang ) : __( 'page', 'sees' );
			}
		}
	}
	$add( 'forms', __( 'Contact form on each Contact page', 'sees' ),
		$has_cf7 && $has_acf && ! $form_missing, true,
		$form_missing
			/* translators: %s: languages */
			? sprintf( __( 'Missing or invalid on: %s. Create a form per language and paste its shortcode in the Contact page "Form" tab.', 'sees' ), implode( ', ', $form_missing ) )
			: __( 'Paste the form shortcode in the Contact page "Form" tab.', 'sees' ),
		admin_url( 'admin.php?page=wpcf7' ), __( 'Contact forms', 'sees' ) );

	// 9. ACF field groups pending sync (JSON in the theme newer than the database copy).
	if ( $has_acf && function_exists( 'acf_get_local_json_files' ) && function_exists( 'acf_get_field_group_post' ) ) {
		$pending = 0;
		foreach ( acf_get_local_json_files( 'acf-field-group' ) as $key => $file ) {
			if ( 0 !== strpos( $key, 'group_sees_' ) ) {
				continue;
			}
			$post = acf_get_field_group_post( $key );
			if ( $post ) {
				$json = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
				if ( ! empty( $json['modified'] ) && (int) $json['modified'] > (int) get_post_modified_time( 'U', true, $post ) ) {
					++$pending;
				}
			}
		}
		$add( 'acf_sync', __( 'Theme field groups synced', 'sees' ), 0 === $pending, false,
			$pending
				/* translators: %d: number of field groups */
				? sprintf( _n( '%d field group was updated with the theme. Click "Sync" to apply it.', '%d field groups were updated with the theme. Click "Sync" to apply them.', $pending, 'sees' ), $pending )
				: __( 'Field groups load from the theme’s acf-json folder.', 'sees' ),
			admin_url( 'edit.php?post_type=acf-field-group&post_status=sync' ), __( 'Sync fields', 'sees' ) );
	}

	// 10. Outgoing mail (recommendation: shared hosts often drop PHP mail()).
	$smtp = false;
	foreach ( array( 'wp-mail-smtp/wp_mail_smtp.php', 'fluent-smtp/fluent-smtp.php', 'post-smtp/postman-smtp.php', 'easy-wp-smtp/easy-wp-smtp.php' ) as $plugin ) {
		if ( is_plugin_active( $plugin ) ) {
			$smtp = true;
		}
	}
	$smtp = $smtp || has_action( 'phpmailer_init' );
	$add( 'smtp', __( 'SMTP email delivery', 'sees' ), $smtp, false,
		__( 'Recommended so contact form emails are delivered: install WP Mail SMTP and connect a cPanel email account.', 'sees' ),
		sprintf( $plugins, 'wp-mail-smtp' ), __( 'Install', 'sees' ) );

	return $checks;
}

/**
 * Admin notice after activation (until everything required passes, or the user dismisses it).
 */
add_action( 'admin_notices', function () {
	if ( ! get_option( SEES_SETUP_OPTION ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( get_user_meta( get_current_user_id(), SEES_SETUP_DISMISS, true ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( $screen && 'appearance_page_sees-setup' === $screen->id ) {
		return;
	}

	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$checks   = sees_setup_checks();
	$failing  = array_filter( $checks, fn( $c ) => ! $c['ok'] );
	$required = array_filter( $failing, fn( $c ) => $c['required'] );

	$page_url    = admin_url( 'themes.php?page=sees-setup' );
	$dismiss_url = wp_nonce_url( admin_url( 'admin-post.php?action=sees_setup_dismiss' ), 'sees_setup_dismiss' );

	if ( ! $failing ) {
		delete_option( SEES_SETUP_OPTION );
		printf(
			'<div class="notice notice-success is-dismissible"><p><strong>%s</strong> %s</p></div>',
			esc_html__( 'SEES theme:', 'sees' ),
			esc_html__( 'setup check passed — languages, pages, menus and forms are all in place.', 'sees' )
		);
		return;
	}

	$class = $required ? 'notice-warning' : 'notice-info';
	echo '<div class="notice ' . esc_attr( $class ) . '"><p><strong>' . esc_html__( 'SEES theme setup check', 'sees' ) . '</strong> — ';
	echo esc_html(
		$required
			/* translators: %d: number of items */
			? sprintf( _n( '%d required step is missing:', '%d required steps are missing:', count( $required ), 'sees' ), count( $required ) )
			: __( 'everything required is done. Recommended:', 'sees' )
	);
	echo '</p><ul style="list-style:disc;margin-inline-start:20px">';
	foreach ( $failing as $check ) {
		printf(
			'<li>%1$s%2$s — <a href="%3$s">%4$s</a></li>',
			esc_html( $check['label'] ),
			$check['required'] ? '' : ' <em>(' . esc_html__( 'recommended', 'sees' ) . ')</em>',
			esc_url( $check['url'] ),
			esc_html( $check['action'] ? $check['action'] : __( 'Fix', 'sees' ) )
		);
	}
	echo '</ul><p>';
	printf( '<a class="button button-primary" href="%s">%s</a> ', esc_url( $page_url ), esc_html__( 'Open the setup checklist', 'sees' ) );
	printf( '<a class="button" href="%s">%s</a>', esc_url( $dismiss_url ), esc_html__( 'Hide until next activation', 'sees' ) );
	echo '</p></div>';
} );

/**
 * Dismiss handler.
 */
add_action( 'admin_post_sees_setup_dismiss', function () {
	check_admin_referer( 'sees_setup_dismiss' );
	if ( current_user_can( 'manage_options' ) ) {
		update_user_meta( get_current_user_id(), SEES_SETUP_DISMISS, 1 );
	}
	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
	exit;
} );

/**
 * Appearance → SEES setup: the full checklist, always available.
 */
add_action( 'admin_menu', function () {
	add_theme_page( __( 'SEES setup', 'sees' ), __( 'SEES setup', 'sees' ), 'manage_options', 'sees-setup', 'sees_setup_page' );
} );

function sees_setup_page(): void {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$checks = sees_setup_checks();
	$done   = count( array_filter( $checks, fn( $c ) => $c['ok'] ) );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'SEES setup checklist', 'sees' ); ?></h1>
		<p>
			<?php
			/* translators: 1: completed steps, 2: total steps */
			printf( esc_html__( '%1$d of %2$d steps complete. This check also runs every time the theme is activated.', 'sees' ), (int) $done, count( $checks ) );
			?>
		</p>
		<table class="widefat striped" role="presentation">
			<tbody>
			<?php foreach ( $checks as $check ) : ?>
				<tr>
					<td style="width:28px;font-size:18px" aria-hidden="true"><?php echo $check['ok'] ? '<span style="color:#008a20">✔</span>' : ( $check['required'] ? '<span style="color:#b32d2e">✖</span>' : '<span style="color:#996800">!</span>' ); ?></td>
					<td>
						<strong><?php echo esc_html( $check['label'] ); ?></strong>
						<span class="screen-reader-text"><?php echo esc_html( $check['ok'] ? __( 'Done', 'sees' ) : ( $check['required'] ? __( 'Missing', 'sees' ) : __( 'Recommended', 'sees' ) ) ); ?></span>
						<?php if ( ! $check['required'] ) : ?>
							<em>(<?php esc_html_e( 'recommended', 'sees' ); ?>)</em>
						<?php endif; ?>
						<br><span class="description"><?php echo esc_html( $check['hint'] ); ?></span>
					</td>
					<td style="width:160px;text-align:end">
						<?php if ( ! $check['ok'] ) : ?>
							<a class="button" href="<?php echo esc_url( $check['url'] ); ?>"><?php echo esc_html( $check['action'] ? $check['action'] : __( 'Fix', 'sees' ) ); ?></a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<p class="description" style="margin-top:16px">
			<?php esc_html_e( 'Fastest route for a new server: migrate the prepared site with All-in-One WP Migration — it brings languages, pages, menus and forms in one step.', 'sees' ); ?>
		</p>
	</div>
	<?php
}
