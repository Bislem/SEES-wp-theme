<?php
/**
 * Automatic setup.
 *
 * Once Polylang, ACF and Contact Form 7 are active, this configures everything else the
 * bilingual site needs: languages, URL mode, the Arabic language pack, permalinks, the
 * Home/About/Solutions/Contact pages in English and Arabic (linked, with templates and
 * content), solutions, FAQs, project categories, placeholder projects (as drafts),
 * contact forms, menus and the front page.
 *
 * It only creates what is missing — existing pages, fields, menus and forms are never
 * overwritten — so it is safe to run again.
 *
 * Runs automatically after the theme is activated (on the next admin page load once the
 * plugins are active), and on demand from Appearance → SEES setup.
 *
 * @package sees
 */

require_once __DIR__ . '/installer-content.php';

const SEES_AUTOSETUP_PENDING = 'sees_autosetup_pending';
const SEES_AUTOSETUP_RESULT  = 'sees_autosetup_result';

/**
 * Are the required plugins active?
 */
function sees_setup_plugins_ready(): bool {
	return function_exists( 'pll_set_post_language' ) && function_exists( 'update_field' ) && function_exists( 'wpcf7_contact_form' );
}

/**
 * Run the setup.
 *
 * @param array $args {
 *     @type string $project_status Status for placeholder projects ('draft' on real sites).
 *     @type bool   $sample_images  Import the bundled photos into the Media Library and use them (local dev).
 * }
 * @return array{done:string[],skipped:string[],errors:string[]}
 */
function sees_run_setup( array $args = array() ): array {
	$args = wp_parse_args( $args, array(
		'project_status' => 'draft',
		'sample_images'  => false,
	) );
	if ( ! sees_projects_public_enabled() ) {
		$args['project_status'] = 'draft';
	}
	$log = array( 'done' => array(), 'skipped' => array(), 'errors' => array() );

	if ( ! sees_setup_plugins_ready() ) {
		$log['errors'][] = __( 'Polylang, Advanced Custom Fields and Contact Form 7 must be active first.', 'sees' );
		return $log;
	}

	$content = sees_installer_content();

	sees_setup_permalinks( $log );
	sees_setup_languages( $log );
	sees_setup_language_pack( $log );
	$media = $args['sample_images'] ? sees_setup_media( $log ) : array();
	$pages = sees_setup_pages( $content, $log );
	sees_setup_front_page( $pages, $log );
	sees_setup_migrate_domain( $log );
	$pages['privacy'] = sees_setup_privacy( $content, $log );
	sees_setup_forms( $content, $pages, $log );
	sees_setup_contact_subject( $content, $pages, $log );
	sees_setup_form_consent( $content, $pages, $log );
	sees_setup_job_forms( $content, $pages, $log );
	sees_setup_download_types( $content, $log );
	sees_setup_blog( $content, $pages, $log );
	sees_setup_site_icon( $log );
	sees_setup_solutions( $content, $media, $log );
	sees_setup_faqs( $content, $log );
	$terms = sees_setup_categories( $content, $log );
	sees_setup_projects( $content, $terms, $media, $args['project_status'], $log );
	sees_setup_menus( $pages, $log );
	sees_setup_strings( $content, $log );
	sees_setup_acf_sync( $log );

	// Polylang caches each language's home page; refresh it now that pages exist.
	PLL()->model->clean_languages_cache();

	// Rebuild URL rules on the next request: Polylang only adds the /en/ and /ar/ rules
	// for languages that existed when the request started.
	update_option( 'sees_flush_rewrite', 1, false );
	update_option( 'sees_setup_version', SEES_VERSION, false );

	return $log;
}

/* ---------------------------------------------------------------------------
 * Steps
 * ------------------------------------------------------------------------ */

function sees_setup_permalinks( array &$log ): void {
	if ( '' === (string) get_option( 'permalink_structure' ) ) {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
		$log['done'][] = __( 'Permalinks set to "Post name".', 'sees' );
	}
}

/**
 * Set a Polylang option through its own options object (it saves on shutdown).
 */
function sees_setup_pll_option( string $key, $value ): void {
	$opts = PLL()->options;
	if ( is_object( $opts ) && method_exists( $opts, 'set' ) ) {
		$opts->set( $key, $value );
		if ( method_exists( $opts, 'save' ) ) {
			$opts->save();
		}
		return;
	}
	$all         = (array) get_option( 'polylang' );
	$all[ $key ] = $value;
	update_option( 'polylang', $all );
}

function sees_setup_languages( array &$log ): void {
	$model = PLL()->model;
	$add   = isset( $model->languages ) && is_object( $model->languages ) && method_exists( $model->languages, 'add' )
		? array( $model->languages, 'add' )
		: array( $model, 'add_language' );

	$wanted = array(
		'en' => array( 'name' => 'English', 'slug' => 'en', 'locale' => 'en_US', 'rtl' => false, 'term_group' => 0, 'flag' => 'us' ),
		'ar' => array( 'name' => 'عربي', 'slug' => 'ar', 'locale' => 'ar', 'rtl' => true, 'term_group' => 1, 'flag' => 'sa' ),
	);
	$had_languages = (bool) pll_languages_list();
	foreach ( $wanted as $slug => $lang ) {
		if ( $model->get_language( $slug ) ) {
			continue;
		}
		$result = call_user_func( $add, $lang );
		if ( is_wp_error( $result ) ) {
			$log['errors'][] = $result->get_error_message();
		} else {
			/* translators: %s: language name */
			$log['done'][] = sprintf( __( 'Language added: %s.', 'sees' ), $lang['name'] );
		}
	}
	$model->clean_languages_cache();

	if ( ! $had_languages ) {
		sees_setup_pll_option( 'default_lang', 'en' );
	}
	// URL mode and translatable types (only touch what differs).
	$opts = PLL()->options;
	$set  = array(
		'force_lang'    => 1,      // /en/, /ar/ directories.
		'hide_default'  => false,  // Keep /en/ visible.
		'rewrite'       => true,   // /en/ rather than /language/en/.
		'redirect_lang' => true,   // Home pages at /en/ and /ar/.
		'browser'       => false,
	);
	$changed = false;
	foreach ( $set as $key => $value ) {
		if ( $opts[ $key ] != $value ) { // phpcs:ignore Universal.Operators.StrictComparisons
			sees_setup_pll_option( $key, $value );
			$changed = true;
		}
	}
	$sync = array_values( array_unique( array_merge( (array) $opts['sync'], array( '_wp_page_template', 'menu_order', '_thumbnail_id', 'taxonomies' ) ) ) );
	if ( $sync !== array_values( (array) $opts['sync'] ) ) {
		sees_setup_pll_option( 'sync', $sync );
		$changed = true;
	}
	if ( $changed ) {
		$log['done'][] = __( 'Polylang URLs set to /en/ and /ar/.', 'sees' );
	}

	// Content created before Polylang gets the default language.
	if ( method_exists( $model, 'set_language_in_mass' ) && $model->get_objects_with_no_lang( 1 ) ) {
		$model->set_language_in_mass();
		$log['done'][] = __( 'Existing content assigned to English.', 'sees' );
	}
}

function sees_setup_language_pack( array &$log ): void {
	if ( in_array( 'ar', get_available_languages(), true ) ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/translation-install.php';
	if ( wp_can_install_language_pack() && wp_download_language_pack( 'ar' ) ) {
		$log['done'][] = __( 'Arabic WordPress language pack installed.', 'sees' );
	} else {
		$log['errors'][] = __( 'Could not download the Arabic language pack automatically — add Arabic under Settings → General → Site Language.', 'sees' );
	}
}

/**
 * Import the bundled photos once (local/demo sites only).
 *
 * @return array<string,int> file name => attachment ID
 */
function sees_setup_media( array &$log ): array {
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$ids = (array) get_option( 'sees_setup_media', array() );
	foreach ( array( 'engineer.jpg', 'pylons.jpg', 'facade.jpg', 'concrete.jpg', 'sitebanner.jpg', 'sign.jpg', 'hero-building.jpg', 'hero-building-rtl.jpg' ) as $file ) {
		if ( ! empty( $ids[ $file ] ) && get_post( $ids[ $file ] ) ) {
			continue;
		}
		$tmp = wp_tempnam( $file );
		copy( SEES_DIR . '/assets/img/' . $file, $tmp );
		$id = media_handle_sideload( array( 'name' => 'sees-' . $file, 'tmp_name' => $tmp ), 0 );
		if ( ! is_wp_error( $id ) ) {
			$ids[ $file ] = $id;
		}
	}
	update_option( 'sees_setup_media', $ids, false );
	return $ids;
}

/**
 * Find the page for a template in a language.
 */
function sees_setup_find_page( string $key, string $template, string $lang ): int {
	if ( 'news' === $key ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		if ( $posts_page && get_post_status( $posts_page ) ) {
			$id = (int) pll_get_post( $posts_page, $lang );
			return $id ? $id : ( pll_get_post_language( $posts_page ) === $lang ? $posts_page : 0 );
		}
		return 0;
	}
	if ( 'home' === $key ) {
		$front = (int) get_option( 'page_on_front' );
		if ( $front && 'page' === get_option( 'show_on_front' ) && get_post_status( $front ) ) {
			$id = (int) pll_get_post( $front, $lang );
			return $id ? $id : ( pll_get_post_language( $front ) === $lang ? $front : 0 );
		}
		return 0;
	}
	$found = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => array( 'publish', 'draft', 'private' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'lang'           => $lang,
		'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
		'meta_value'     => $template,           // phpcs:ignore WordPress.DB.SlowDBQuery
		'no_found_rows'  => true,
	) );
	return $found ? (int) $found[0] : 0;
}

/**
 * Fill ACF fields that are still empty (never overwrites the client's edits).
 */
function sees_setup_fill_fields( int $post_id, array $fields ): void {
	foreach ( $fields as $name => $value ) {
		$current = get_field( $name, $post_id, false );
		if ( null === $current || '' === $current || false === $current ) {
			if ( ! update_field( $name, $value, $post_id ) ) {
				update_post_meta( $post_id, $name, $value );
			}
		}
	}
}

/**
 * @return array<string,array<string,int>> page key => [ lang => ID ]
 */
function sees_setup_pages( array $content, array &$log ): array {
	$pages = array();
	foreach ( $content['pages'] as $key => $def ) {
		$ids = array();
		foreach ( array( 'en', 'ar' ) as $lang ) {
			$id = sees_setup_find_page( $key, $def['template'], $lang );
			if ( ! $id ) {
				$id = wp_insert_post( wp_slash( array(
					'post_type'   => 'page',
					'post_status' => 'publish',
					'post_title'  => $def[ $lang ]['title'],
					'post_name'   => $def[ $lang ]['slug'],
					'menu_order'  => $def['order'],
				) ), true );
				if ( is_wp_error( $id ) ) {
					$log['errors'][] = $id->get_error_message();
					continue;
				}
				pll_set_post_language( $id, $lang );
				if ( $def['template'] ) {
					update_post_meta( $id, '_wp_page_template', $def['template'] );
				}
				/* translators: 1: page title, 2: language code */
				$log['done'][] = sprintf( __( 'Page created: %1$s (%2$s).', 'sees' ), $def[ $lang ]['title'], strtoupper( $lang ) );
			}
			sees_setup_fill_fields( (int) $id, $def[ $lang ]['fields'] );
			$ids[ $lang ] = (int) $id;
		}
		if ( count( $ids ) === 2 ) {
			pll_save_post_translations( array_merge( (array) pll_get_post_translations( $ids['en'] ), $ids ) );
		}
		$pages[ $key ] = $ids;
	}
	return $pages;
}

/**
 * Move contact details from the old domain (seesengineering.com) to the final one
 * (sees-engineering.com). Only values still holding an old address are changed.
 */
function sees_setup_migrate_domain( array &$log ): void {
	$map = array(
		'info@seesengineering.com'    => SEES_CONTACT_EMAIL,
		'contact@seesengineering.com' => SEES_CONTACT_EMAIL,
		'careers@seesengineering.com' => 'careers@sees-engineering.com',
	);
	$changed = 0;

	// Page fields: Contact email, Careers applications email, Support email.
	$fields = array( 'page-contact.php' => 'email', 'page-jobs.php' => 'applications_email', 'page-support.php' => 'support_email' );
	foreach ( $fields as $template => $field ) {
		$ids = get_posts( array(
			'post_type'      => 'page',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'lang'           => '',
			'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $template,           // phpcs:ignore WordPress.DB.SlowDBQuery
		) );
		foreach ( $ids as $id ) {
			$value = strtolower( trim( (string) get_post_meta( $id, $field, true ) ) );
			if ( isset( $map[ $value ] ) ) {
				update_field( $field, $map[ $value ], $id );
				++$changed;
			}
		}
	}

	// Contact Form 7 recipients.
	foreach ( get_posts( array( 'post_type' => 'wpcf7_contact_form', 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $form_id ) {
		$form = wpcf7_contact_form( $form_id );
		$mail = $form ? (array) $form->prop( 'mail' ) : array();
		$to   = strtolower( trim( (string) ( $mail['recipient'] ?? '' ) ) );
		if ( isset( $map[ $to ] ) ) {
			$mail['recipient'] = $map[ $to ];
			$form->set_properties( array( 'mail' => $mail ) );
			$form->save();
			++$changed;
		}
	}

	if ( $changed ) {
		/* translators: %d: number of updated settings */
		$log['done'][] = sprintf( __( 'Contact email moved to the sees-engineering.com domain (%d settings updated).', 'sees' ), $changed );
	}
}

/**
 * Block markup for policy-style content: [ heading, paragraphs[], list items[]? ].
 */
function sees_setup_blocks( array $sections ): string {
	$out = '';
	foreach ( $sections as $section ) {
		list( $heading, $paras ) = $section;
		$items = $section[2] ?? array();
		if ( $heading ) {
			$out .= '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( $heading ) . '</h2><!-- /wp:heading -->' . "\n\n";
		}
		foreach ( $paras as $para ) {
			$out .= '<!-- wp:paragraph --><p>' . esc_html( $para ) . '</p><!-- /wp:paragraph -->' . "\n\n";
		}
		if ( $items ) {
			$out .= '<!-- wp:list --><ul class="wp-block-list">';
			foreach ( $items as $item ) {
				$out .= '<!-- wp:list-item --><li>' . esc_html( $item ) . '</li><!-- /wp:list-item -->';
			}
			$out .= '</ul><!-- /wp:list -->' . "\n\n";
		}
	}
	return $out;
}

/**
 * Privacy Policy page (Saudi PDPL template) in English and Arabic.
 * Replaces WordPress's own untouched draft privacy page; never touches a page someone wrote.
 *
 * @return array<string,int> lang => page ID
 */
function sees_setup_privacy( array $content, array &$log ): array {
	$def = $content['privacy'];
	$ids = array();

	$en   = (int) get_option( 'wp_page_for_privacy_policy' );
	$post = $en ? get_post( $en ) : null;
	if ( $post && 'trash' === $post->post_status ) {
		$post = null;
	}
	// WordPress's default privacy page: a draft still holding the suggested text.
	$is_wp_default = $post && 'draft' === $post->post_status
		&& ( false !== strpos( $post->post_content, 'Suggested text:' ) || false !== strpos( $post->post_content, 'Our website address is' ) );

	foreach ( array( 'en', 'ar' ) as $lang ) {
		$page = $def[ $lang ];
		$id   = 0;
		if ( 'en' === $lang && $post && ! $is_wp_default ) {
			$id = (int) $post->ID;
		} elseif ( 'ar' === $lang && ! empty( $ids['en'] ) ) {
			$id = (int) pll_get_post( $ids['en'], 'ar' );
		}

		if ( ! $id ) {
			$data = array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $page['title'],
				'post_name'    => $page['slug'],
				'post_content' => sees_setup_blocks( $page['sections'] ),
				'menu_order'   => 9,
			);
			if ( 'en' === $lang && $is_wp_default ) {
				$data['ID'] = $post->ID;
				$id         = wp_update_post( wp_slash( $data ), true );
			} else {
				$id = wp_insert_post( wp_slash( $data ), true );
			}
			if ( is_wp_error( $id ) ) {
				$log['errors'][] = $id->get_error_message();
				continue;
			}
			pll_set_post_language( $id, $lang );
			/* translators: 1: page title, 2: language code */
			$log['done'][] = sprintf( __( 'Page created: %1$s (%2$s).', 'sees' ), $page['title'], strtoupper( $lang ) );
		}
		sees_setup_fill_fields( (int) $id, array( 'header_subtitle' => $page['subtitle'] ) );
		$ids[ $lang ] = (int) $id;
	}

	if ( ! empty( $ids['en'] ) ) {
		update_option( 'wp_page_for_privacy_policy', $ids['en'] );
	}
	if ( count( $ids ) === 2 ) {
		pll_save_post_translations( array_merge( (array) pll_get_post_translations( $ids['en'] ), $ids ) );
	}
	return $ids;
}

/**
 * Add a required consent checkbox (linking to the privacy policy) to the theme's contact forms.
 */
function sees_setup_form_consent( array $content, array $pages, array &$log ): void {
	foreach ( array( 'en', 'ar' ) as $lang ) {
		$page    = $pages['contact'][ $lang ] ?? 0;
		$privacy = $pages['privacy'][ $lang ] ?? 0;
		if ( ! $page || ! $privacy ) {
			continue;
		}
		$code = (string) get_field( 'form_shortcode', $page, false );
		$id   = preg_match( '/id=["\']?(\d+)/', $code, $m ) ? (int) $m[1] : 0;
		$form = $id ? wpcf7_contact_form( $id ) : null;
		if ( ! $form ) {
			continue;
		}
		$markup = (string) $form->prop( 'form' );
		// Only forms built by the theme.
		if ( false === strpos( $markup, 'sees-form' ) ) {
			continue;
		}
		$before = wp_json_encode( $form->prop( 'messages' ) );
		sees_setup_form_messages( $form, $content['forms'][ $lang ]['messages'] );
		if ( false !== strpos( $markup, '[acceptance' ) ) {
			// Consent box already there: swap a fixed privacy link for the per-language token.
			$fixed = sees_setup_consent_token( $markup );
			if ( $fixed !== $markup ) {
				$form->set_properties( array( 'form' => $fixed ) );
			}
			if ( $fixed !== $markup || wp_json_encode( $form->prop( 'messages' ) ) !== $before ) {
				$form->save();
			}
			continue;
		}
		$text    = sprintf( $content['privacy']['consent'][ $lang ], '{sees_privacy_url}' );
		$consent = '<div class="sees-form__consent">[acceptance consent] ' . $text . " [/acceptance]</div>\n";
		$markup  = preg_replace( '/\[submit/', $consent . '[submit', $markup, 1 );
		$form->set_properties( array( 'form' => $markup ) );
		$form->save();
		/* translators: %s: language code */
		$log['done'][] = sprintf( __( 'Privacy consent checkbox added to the contact form (%s).', 'sees' ), strtoupper( $lang ) );
	}
}

/**
 * Replace a fixed link inside the consent box with the {sees_privacy_url} token.
 */
function sees_setup_consent_token( string $markup ): string {
	return (string) preg_replace_callback( '#(<div class="sees-form__consent">.*?</div>)#s', function ( $m ) {
		return preg_replace( '#href="[^"]*"#', 'href="{sees_privacy_url}"', $m[1] );
	}, $markup );
}

/**
 * Standard job application forms (one per language), used by every vacancy that has no
 * custom form. IDs are stored in the `sees_job_forms` option (see inc/job-applications.php).
 */
function sees_setup_job_forms( array $content, array $pages, array &$log ): void {
	$forms = (array) get_option( 'sees_job_forms', array() );
	foreach ( array( 'en', 'ar' ) as $lang ) {
		$existing = ! empty( $forms[ $lang ] ) ? wpcf7_contact_form( (int) $forms[ $lang ] ) : null;
		if ( $existing ) {
			$markup = (string) $existing->prop( 'form' );
			$fixed  = sees_setup_consent_token( $markup );
			if ( $fixed !== $markup ) {
				$existing->set_properties( array( 'form' => $fixed ) );
				$existing->save();
			}
			continue;
		}
		$def     = $content['job_forms'][ $lang ];
		$t       = $def['labels'];
		$privacy = $pages['privacy'][ $lang ] ?? 0;
		$consent = $privacy
			? '<div class="sees-form__consent">[acceptance consent] ' . sprintf( $content['privacy']['consent'][ $lang ], '{sees_privacy_url}' ) . " [/acceptance]</div>\n"
			: '';

		$markup = "<div class=\"sees-form sees-form--job\">\n<div class=\"sees-form__grid\">\n"
			. "<label>{$t['name']} *\n[text* your-name autocomplete:name]</label>\n"
			. "<label>{$t['email']} *\n[email* your-email autocomplete:email placeholder \"you@example.com\"]</label>\n"
			. "<label>{$t['phone']} *\n[tel* your-phone autocomplete:tel placeholder \"+966\"]</label>\n"
			. "<label>{$t['link']}\n[url your-link placeholder \"https://\"]</label>\n"
			. "</div>\n"
			. "<label>{$t['cv']} * <span class=\"sees-form__hint\">{$t['cv_hint']}</span>\n[file* your-cv limit:5mb filetypes:pdf|doc|docx]</label>\n"
			. "<label>{$t['letter']}\n[textarea your-message x5 placeholder \"{$t['letter_ph']}\"]</label>\n"
			. $consent
			. "[submit \"{$t['submit']}\"]\n</div>";

		$form = WPCF7_ContactForm::get_template( array( 'title' => $def['title'], 'locale' => $def['locale'] ) );
		$mail = $form->prop( 'mail' );
		$mail['subject']            = $def['mail_subject'];
		$mail['recipient']          = sees_job_applications_email(); // Also set per submission (filter).
		$mail['additional_headers'] = 'Reply-To: [your-email]';
		$mail['body']               = $def['mail_body'];
		$mail['attachments']        = '[your-cv]';

		$reply = array_merge( (array) $form->prop( 'mail_2' ), array(
			'active'             => true,
			'recipient'          => '[your-email]',
			'subject'            => $def['reply_subject'],
			'body'               => $def['reply_body'],
			'additional_headers' => '',
			'attachments'        => '',
		) );

		// A brand-new form: our messages win over Contact Form 7's (possibly translated) defaults.
		$form->set_properties( array(
			'form'     => $markup,
			'mail'     => $mail,
			'mail_2'   => $reply,
			'messages' => array_merge( (array) $form->prop( 'messages' ), $def['messages'] ),
		) );
		$id = (int) $form->save();
		if ( $id ) {
			$forms[ $lang ] = $id;
			/* translators: %s: form title */
			$log['done'][] = sprintf( __( 'Job application form created: %s.', 'sees' ), $def['title'] );
		}
	}
	update_option( 'sees_job_forms', array_map( 'intval', $forms ), false );
}

/**
 * Support document types with their Arabic names (created once; editors may rename or delete them).
 */
function sees_setup_download_types( array $content, array &$log ): void {
	if ( get_option( 'sees_seeded_download_types' ) ) {
		return;
	}
	$existing = get_terms( array( 'taxonomy' => 'sees_download_type', 'hide_empty' => false, 'fields' => 'ids' ) );
	if ( ! is_wp_error( $existing ) && ! $existing ) {
		foreach ( $content['download_types'] as $slug => $names ) {
			$term = wp_insert_term( $names[0], 'sees_download_type', array( 'slug' => $slug ) );
			if ( ! is_wp_error( $term ) ) {
				update_term_meta( $term['term_id'], 'name_ar', $names[1] );
			}
		}
		$log['done'][] = __( 'Support document types created (English + Arabic names).', 'sees' );
	}
	update_option( 'sees_seeded_download_types', 1, false );
}

/**
 * News: the News page becomes the Posts page, News/Insights categories in both languages,
 * comments off by default, and WordPress's untouched "Hello world!" post removed.
 */
function sees_setup_blog( array $content, array $pages, array &$log ): void {
	if ( ! empty( $pages['news']['en'] ) && ! (int) get_option( 'page_for_posts' ) ) {
		update_option( 'page_for_posts', (int) $pages['news']['en'] );
		$log['done'][] = __( 'News page set as the posts page.', 'sees' );
	}

	if ( ! get_option( 'sees_seeded_news_categories' ) ) {
		$first = 0;
		foreach ( $content['news_categories'] as $slug => $names ) {
			$pair = array();
			foreach ( array( 'en' => array( $names[0], $slug ), 'ar' => array( $names[1], $slug . '-ar' ) ) as $lang => $t ) {
				$term = get_term_by( 'slug', $t[1], 'category' );
				$id   = $term ? (int) $term->term_id : 0;
				if ( ! $id ) {
					$new = wp_insert_term( $t[0], 'category', array( 'slug' => $t[1] ) );
					$id  = is_wp_error( $new ) ? 0 : (int) $new['term_id'];
				}
				if ( $id ) {
					pll_set_term_language( $id, $lang );
					$pair[ $lang ] = $id;
				}
			}
			if ( count( $pair ) === 2 ) {
				pll_save_term_translations( $pair );
				$first = $first ?: $pair['en'];
			}
		}
		if ( $first ) {
			update_option( 'default_category', $first ); // New posts land in "News" rather than "Uncategorized".
		}
		update_option( 'sees_seeded_news_categories', 1, false );
		$log['done'][] = __( 'News categories created (English + Arabic).', 'sees' );
	}

	if ( 'closed' !== get_option( 'default_comment_status' ) ) {
		update_option( 'default_comment_status', 'closed' );
		update_option( 'default_ping_status', 'closed' );
	}

	$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
	if ( $hello && 'publish' === $hello->post_status && false !== strpos( $hello->post_content, 'Welcome to WordPress' ) && $hello->post_date === $hello->post_modified ) {
		wp_delete_post( $hello->ID, true );
		$log['done'][] = __( 'WordPress sample post "Hello world!" removed.', 'sees' );
	}
}

/**
 * Add a page to a language's menu once, before another page (or at the end).
 * Remembers what it added, so an item an editor removes on purpose stays removed.
 */
function sees_setup_menu_add_once( int $menu_id, string $key, string $lang, int $page_id, int $before_page_id, array &$log ): void {
	$done = (array) get_option( 'sees_menu_additions', array() );
	if ( ! $page_id || ! empty( $done[ $key ][ $lang ] ) ) {
		return;
	}
	$items = wp_get_nav_menu_items( $menu_id ) ?: array();
	$has   = array_filter( $items, fn( $item ) => 'page' === $item->object && $page_id === (int) $item->object_id );
	if ( ! $has ) {
		$insert_at = count( $items ) + 1;
		foreach ( $items as $existing ) {
			if ( $before_page_id && 'page' === $existing->object && $before_page_id === (int) $existing->object_id ) {
				$insert_at = (int) $existing->menu_order;
				break;
			}
		}
		foreach ( array_reverse( $items ) as $existing ) {
			if ( (int) $existing->menu_order >= $insert_at ) {
				wp_update_post( array( 'ID' => $existing->ID, 'menu_order' => (int) $existing->menu_order + 1 ) );
			}
		}
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-status'    => 'publish',
			'menu-item-position'  => $insert_at,
			'menu-item-type'      => 'post_type',
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $page_id,
		) );
		/* translators: 1: page title, 2: menu name */
		$log['done'][] = sprintf( __( '%1$s added to menu: %2$s.', 'sees' ), get_the_title( $page_id ), 'Primary (' . strtoupper( $lang ) . ')' );
	}
	$done[ $key ][ $lang ] = 1;
	update_option( 'sees_menu_additions', $done, false );
}

/**
 * Apply the theme's form messages where the form still has Contact Form 7's default text.
 */
function sees_setup_form_messages( WPCF7_ContactForm $form, array $messages ): void {
	if ( ! $messages || ! function_exists( 'wpcf7_messages' ) ) {
		return;
	}
	$defaults = wpcf7_messages();
	$current  = (array) $form->prop( 'messages' );
	$changed  = false;
	foreach ( $messages as $key => $text ) {
		$default = $defaults[ $key ]['default'] ?? null;
		if ( ! isset( $current[ $key ] ) || '' === $current[ $key ] || $current[ $key ] === $default ) {
			$current[ $key ] = $text;
			$changed         = true;
		}
	}
	if ( $changed ) {
		$form->set_properties( array( 'messages' => $current ) );
	}
}

/**
 * Site Icon (favicon / app icon) from the SEES logo mark, if none is set.
 */
function sees_setup_site_icon( array &$log ): void {
	if ( get_option( 'site_icon' ) ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	$tmp = wp_tempnam( 'site-icon.png' );
	copy( SEES_DIR . '/assets/img/site-icon.png', $tmp );
	$id = media_handle_sideload( array( 'name' => 'sees-site-icon.png', 'tmp_name' => $tmp ), 0, 'SEES' );
	if ( is_wp_error( $id ) ) {
		$log['errors'][] = $id->get_error_message();
		return;
	}
	update_post_meta( $id, '_wp_attachment_context', 'site-icon' );
	update_option( 'site_icon', $id );
	$log['done'][] = __( 'Site icon set from the SEES logo mark.', 'sees' );
}

function sees_setup_front_page( array $pages, array &$log ): void {
	$home = $pages['home']['en'] ?? 0;
	if ( $home && ( 'page' !== get_option( 'show_on_front' ) || ! get_option( 'page_on_front' ) ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home );
		$log['done'][] = __( 'Home set as the front page.', 'sees' );
	}
}

/**
 * Contact Form 7 forms per language, with the theme's markup; shortcode stored on each Contact page.
 */
function sees_setup_forms( array $content, array $pages, array &$log ): void {
	foreach ( array( 'en', 'ar' ) as $lang ) {
		$page = $pages['contact'][ $lang ] ?? 0;
		if ( ! $page || trim( (string) get_field( 'form_shortcode', $page, false ) ) ) {
			continue;
		}
		$def  = $content['forms'][ $lang ];
		$form = WPCF7_ContactForm::get_template( array( 'title' => $def['title'], 'locale' => $def['locale'] ) );

		$contact_email = (string) get_field( 'email', $page ) ?: (string) get_option( 'admin_email' );
		$mail          = sees_setup_contact_mail( (array) $form->prop( 'mail' ), $def );
		$mail['recipient']          = $contact_email;
		$mail['additional_headers'] = 'Reply-To: [your-email]';

		$props = array(
			'form'   => sees_setup_contact_markup( $content, $lang ),
			'mail'   => $mail,
			'mail_2' => array_merge( (array) $form->prop( 'mail_2' ), array( 'active' => false ) ),
		);
		if ( $def['messages'] ) {
			$props['messages'] = array_merge( (array) $form->prop( 'messages' ), $def['messages'] );
		}
		$form->set_properties( $props );
		$form_id = $form->save();

		if ( $form_id ) {
			update_field( 'form_shortcode', sprintf( '[contact-form-7 id="%d" title="%s"]', $form_id, $def['title'] ), $page );
			/* translators: %s: form title */
			$log['done'][] = sprintf( __( 'Contact form created: %s.', 'sees' ), $def['title'] );
		}
	}
}

/**
 * Contact form markup: name, company, email, phone, subject (required select) and message.
 * The subject keys in data-topics let ?topic=quote links preselect a subject.
 */
function sees_setup_contact_markup( array $content, string $lang ): string {
	$def    = $content['forms'][ $lang ];
	$t      = $def['labels'];
	$topics = '"' . implode( '" "', array_map( fn( $o ) => str_replace( '"', '', $o ), array_merge( array( $t['topic_ph'] ), $def['topics'] ) ) ) . '"';
	return "<div class=\"sees-form\" data-topics=\"" . esc_attr( implode( ' ', $content['forms']['topic_keys'] ) ) . "\">\n<div class=\"sees-form__grid\">\n"
		. "<label>{$t['name']} *\n[text* your-name autocomplete:name placeholder \"{$t['name_ph']}\"]</label>\n"
		. "<label>{$t['company']}\n[text your-company autocomplete:organization placeholder \"{$t['company_ph']}\"]</label>\n"
		. "<label>{$t['email']} *\n[email* your-email autocomplete:email placeholder \"you@company.com\"]</label>\n"
		. "<label>{$t['phone']}\n[tel your-phone autocomplete:tel placeholder \"+966\"]</label>\n"
		. "</div>\n"
		. "<label>{$t['topic']} *\n[select* your-subject first_as_label {$topics}]</label>\n"
		. "<label>{$t['message']} *\n[textarea* your-message x5 placeholder \"{$t['message_ph']}\"]</label>\n"
		. "[submit \"{$t['submit']}\"]\n</div>";
}

function sees_setup_contact_mail( array $mail, array $def ): array {
	$t               = $def['labels'];
	$mail['subject'] = '[_site_title] [your-subject]: [your-name]';
	$mail['body']    = "{$t['mail_topic']}: [your-subject]\n\n{$t['name']}: [your-name]\n{$t['company']}: [your-company]\n{$t['email']}: [your-email]\n{$t['phone']}: [your-phone]\n\n{$t['mail_message']}:\n[your-message]\n\n-- \nSent from [_post_url]";
	return $mail;
}

/**
 * Upgrade the theme's original quote forms (with a "Solution" select) to the general contact form
 * with a "Subject" select. Only touches theme-built forms; the consent box is re-added afterwards.
 */
function sees_setup_contact_subject( array $content, array $pages, array &$log ): void {
	$old_texts = array(
		'form_title'      => array( 'Request a quote', 'اطلب عرض سعر' ),
		'success_text'    => array( 'Your request has been sent. A SEES engineer will contact you shortly.', 'تم إرسال طلبك. سيتواصل معك أحد مهندسي سيز قريبًا.' ),
		'header_subtitle' => array( 'Tell us about your project — an engineer will get back to you with next steps.', 'أخبرنا عن مشروعك، وسيتواصل معك أحد مهندسينا بالخطوات التالية.' ),
	);
	foreach ( array( 'en', 'ar' ) as $lang ) {
		$page = $pages['contact'][ $lang ] ?? 0;
		if ( ! $page ) {
			continue;
		}
		// Page texts still holding the old quote wording.
		foreach ( $old_texts as $field => $olds ) {
			if ( in_array( trim( (string) get_field( $field, $page, false ) ), $olds, true ) ) {
				update_field( $field, $content['pages']['contact'][ $lang ]['fields'][ $field ], $page );
			}
		}

		$code = (string) get_field( 'form_shortcode', $page, false );
		$id   = preg_match( '/id=["\']?(\d+)/', $code, $m ) ? (int) $m[1] : 0;
		$form = $id ? wpcf7_contact_form( $id ) : null;
		if ( ! $form || false === strpos( (string) $form->prop( 'form' ), 'sees-form' ) || false === strpos( (string) $form->prop( 'form' ), '[select your-solution' ) ) {
			continue;
		}
		$def   = $content['forms'][ $lang ];
		$props = array(
			'form' => sees_setup_contact_markup( $content, $lang ),
			'mail' => sees_setup_contact_mail( (array) $form->prop( 'mail' ), $def ),
		);
		$form->set_properties( $props );
		$form->set_title( $def['title'] );
		$form->save();
		update_field( 'form_shortcode', sprintf( '[contact-form-7 id="%d" title="%s"]', $form->id(), $def['title'] ), $page );
		/* translators: %s: form title */
		$log['done'][] = sprintf( __( 'Contact form updated with a Subject field: %s.', 'sees' ), $def['title'] );
	}
}

function sees_setup_solutions( array $content, array $media, array &$log ): void {
	if ( get_posts( array( 'post_type' => 'solution', 'post_status' => 'any', 'posts_per_page' => 1, 'lang' => '', 'fields' => 'ids' ) ) ) {
		return;
	}
	foreach ( $content['solutions'] as $i => $s ) {
		$ids = array();
		foreach ( array( 'en', 'ar' ) as $lang ) {
			$id = wp_insert_post( wp_slash( array(
				'post_type'    => 'solution',
				'post_status'  => 'publish',
				'post_title'   => $s[ $lang ][0],
				'post_excerpt' => $s[ $lang ][1],
				'post_name'    => 'ar' === $lang ? $s['slug'] . '-ar' : $s['slug'],
				'menu_order'   => $i + 1,
			) ) );
			pll_set_post_language( $id, $lang );
			sees_setup_fill_fields( $id, array(
				'number'  => sprintf( '%02d', $i + 1 ),
				'icon'    => $s['icon'],
				'accent'  => $s['accent'],
				'intro'   => $s[ $lang ][2],
				'bullets' => $s[ $lang ][3],
			) );
			if ( ! empty( $media[ $s['image'] ] ) ) {
				set_post_thumbnail( $id, $media[ $s['image'] ] );
			}
			$ids[ $lang ] = $id;
		}
		pll_save_post_translations( $ids );
	}
	$log['done'][] = __( 'Solutions created (5, English + Arabic).', 'sees' );
}

function sees_setup_faqs( array $content, array &$log ): void {
	if ( get_posts( array( 'post_type' => 'faq', 'post_status' => 'any', 'posts_per_page' => 1, 'lang' => '', 'fields' => 'ids' ) ) ) {
		return;
	}
	foreach ( $content['faqs'] as $i => $f ) {
		$ids = array();
		foreach ( array( 'en' => array( $f[0], $f[1] ), 'ar' => array( $f[2], $f[3] ) ) as $lang => $qa ) {
			$id = wp_insert_post( wp_slash( array(
				'post_type'    => 'faq',
				'post_status'  => 'publish',
				'post_title'   => $qa[0],
				'post_content' => $qa[1],
				'post_name'    => 'faq-' . ( $i + 1 ) . ( 'ar' === $lang ? '-ar' : '' ),
				'menu_order'   => $i + 1,
			) ) );
			pll_set_post_language( $id, $lang );
			$ids[ $lang ] = $id;
		}
		pll_save_post_translations( $ids );
	}
	$log['done'][] = __( 'FAQs created (4, English + Arabic).', 'sees' );
}

/**
 * @return array<string,array<string,int>> slug => [ lang => term ID ]
 */
function sees_setup_categories( array $content, array &$log ): array {
	$terms   = array();
	$created = 0;
	foreach ( $content['categories'] as $slug => $names ) {
		$pair = array();
		foreach ( array( 'en' => array( $names[0], $slug ), 'ar' => array( $names[1], $slug . '-ar' ) ) as $lang => $t ) {
			$existing = get_term_by( 'slug', $t[1], 'project_category' );
			if ( $existing ) {
				$pair[ $lang ] = (int) $existing->term_id;
				continue;
			}
			$new = wp_insert_term( $t[0], 'project_category', array( 'slug' => $t[1] ) );
			if ( is_wp_error( $new ) ) {
				$log['errors'][] = $new->get_error_message();
				continue;
			}
			pll_set_term_language( $new['term_id'], $lang );
			$pair[ $lang ] = (int) $new['term_id'];
			++$created;
		}
		if ( count( $pair ) === 2 ) {
			pll_save_term_translations( $pair );
		}
		$terms[ $slug ] = $pair;
	}
	if ( $created ) {
		$log['done'][] = __( 'Project categories created (English + Arabic).', 'sees' );
	}
	return $terms;
}

function sees_setup_projects( array $content, array $terms, array $media, string $status, array &$log ): void {
	// Remember which seed projects were created, so new ones get added on existing sites
	// but a placeholder the client deleted is never re-created.
	$created = get_option( 'sees_seeded_projects', null );
	if ( null === $created ) {
		$has_projects = (bool) get_posts( array( 'post_type' => 'project', 'post_status' => 'any', 'posts_per_page' => 1, 'lang' => '', 'fields' => 'ids' ) );
		$created      = $has_projects ? range( 0, 5 ) : array(); // Sites seeded before this option existed got the first six.
	}
	$created = array_map( 'intval', (array) $created );
	$added   = 0;

	foreach ( $content['projects'] as $i => $p ) {
		if ( in_array( $i, $created, true ) ) {
			continue;
		}
		list( $cat, $image, $featured ) = $p;
		$ids = array();
		foreach ( array( 'en', 'ar' ) as $lang ) {
			$text = $content['project_text'][ $lang ];
			$id   = wp_insert_post( wp_slash( array(
				'post_type'    => 'project',
				'post_status'  => $status,
				'post_title'   => $featured ? $text['featured_title'] : $text['title'],
				'post_name'    => 'project-' . ( $i + 1 ) . ( 'ar' === $lang ? '-ar' : '' ),
				'post_content' => $text['content'],
				'menu_order'   => $i + 1,
			) ) );
			pll_set_post_language( $id, $lang );
			sees_setup_fill_fields( $id, array_merge( $text['fields'], array( 'is_featured' => $featured ? 1 : 0 ) ) );
			if ( ! empty( $terms[ $cat ][ $lang ] ) ) {
				wp_set_object_terms( $id, array( $terms[ $cat ][ $lang ] ), 'project_category' );
			}
			if ( ! empty( $media[ $image ] ) ) {
				set_post_thumbnail( $id, $media[ $image ] );
			}
			$ids[ $lang ] = $id;
		}
		pll_save_post_translations( $ids );
		$created[] = $i;
		++$added;
	}
	update_option( 'sees_seeded_projects', array_values( array_unique( $created ) ), false );

	if ( $added ) {
		$log['done'][] = 'publish' === $status
			/* translators: %d: number of projects */
			? sprintf( _n( '%d sample project created (English + Arabic).', '%d sample projects created (English + Arabic).', $added, 'sees' ), $added )
			/* translators: %d: number of projects */
			: sprintf( _n( '%d placeholder project created as a draft (English + Arabic) — fill it in and publish.', '%d placeholder projects created as drafts (English + Arabic) — fill them in and publish.', $added, 'sees' ), $added );
	}
}

function sees_setup_menus( array $pages, array &$log ): void {
	$theme     = get_stylesheet();
	$nav_menus = (array) PLL()->options['nav_menus'];
	$assigned  = (array) ( $nav_menus[ $theme ]['primary'] ?? array() );
	$labels    = array( 'en' => 'Projects', 'ar' => 'المشاريع' );
	$changed   = false;

	foreach ( array( 'en', 'ar' ) as $lang ) {
		$name    = 'Primary (' . strtoupper( $lang ) . ')';
		$menu    = ! empty( $assigned[ $lang ] ) ? wp_get_nav_menu_object( $assigned[ $lang ] ) : null;
		$menu    = $menu ?: wp_get_nav_menu_object( $name );
		$menu_id = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu( $name );
		if ( ! $menu ) {
			$order = array( 'home', 'about', 'solutions', 'news', 'jobs' );
			if ( sees_projects_public_enabled() ) {
				$order[] = 'projects';
			}
			$order[] = 'contact';
			foreach ( $order as $pos => $key ) {
				$item = array( 'menu-item-status' => 'publish', 'menu-item-position' => $pos + 1 );
				if ( 'projects' === $key ) {
					$item += array( 'menu-item-type' => 'post_type_archive', 'menu-item-object' => 'project', 'menu-item-title' => $labels[ $lang ] );
				} elseif ( ! empty( $pages[ $key ][ $lang ] ) ) {
					$item += array( 'menu-item-type' => 'post_type', 'menu-item-object' => 'page', 'menu-item-object-id' => $pages[ $key ][ $lang ] );
				} else {
					continue;
				}
				wp_update_nav_menu_item( $menu_id, 0, $item );
			}
		} else {
			// Existing menus: add pages introduced after the menu was created (once each).
			$contact = (int) ( $pages['contact'][ $lang ] ?? 0 );
			$jobs    = (int) ( $pages['jobs'][ $lang ] ?? 0 );
			sees_setup_menu_add_once( $menu_id, 'news', $lang, (int) ( $pages['news'][ $lang ] ?? 0 ), $jobs ?: $contact, $log );
			sees_setup_menu_add_once( $menu_id, 'jobs', $lang, $jobs, $contact, $log );
		}
		if ( ! $menu ) {
			// A brand-new menu already contains everything: record that.
			$done = (array) get_option( 'sees_menu_additions', array() );
			$done['news'][ $lang ] = 1;
			$done['jobs'][ $lang ] = 1;
			update_option( 'sees_menu_additions', $done, false );
		}
		if ( empty( $assigned[ $lang ] ) || (int) $assigned[ $lang ] !== $menu_id ) {
			$assigned[ $lang ] = $menu_id;
			$changed           = true;
			/* translators: %s: menu name */
			$log['done'][] = sprintf( __( 'Menu created and assigned: %s.', 'sees' ), $name );
		}
	}

	if ( $changed ) {
		$nav_menus[ $theme ]['primary'] = $assigned;
		sees_setup_pll_option( 'nav_menus', $nav_menus );
		$locations            = (array) get_theme_mod( 'nav_menu_locations', array() );
		$locations['primary'] = $assigned['en'];
		set_theme_mod( 'nav_menu_locations', $locations );
	}
}

/**
 * Arabic translations of the site title/tagline (Polylang strings).
 */
function sees_setup_strings( array $content, array &$log ): void {
	if ( ! class_exists( 'PLL_MO' ) ) {
		return;
	}
	$lang = PLL()->model->get_language( 'ar' );
	if ( ! $lang ) {
		return;
	}
	$mo = new PLL_MO();
	$mo->import_from_db( $lang );
	$added = false;
	foreach ( $content['strings'] as $source => $translation ) {
		if ( $mo->translate( $source ) === $source ) { // Not translated yet.
			$mo->add_entry( $mo->make_entry( $source, $translation ) );
			$added = true;
		}
	}
	if ( $added ) {
		$mo->export_to_db( $lang );
	}
}

/**
 * Apply theme field-group updates (acf-json newer than the database copy).
 */
function sees_setup_acf_sync( array &$log ): void {
	if ( ! function_exists( 'acf_get_local_json_files' ) || ! function_exists( 'acf_import_field_group' ) ) {
		return;
	}
	$synced = 0;
	foreach ( acf_get_local_json_files( 'acf-field-group' ) as $key => $file ) {
		if ( 0 !== strpos( $key, 'group_sees_' ) ) {
			continue;
		}
		$post = acf_get_field_group_post( $key );
		if ( ! $post ) {
			continue; // Not in the database: ACF loads it straight from JSON.
		}
		$json = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( ! empty( $json['modified'] ) && (int) $json['modified'] > (int) get_post_modified_time( 'U', true, $post ) ) {
			$json['ID'] = $post->ID;
			acf_import_field_group( $json );
			++$synced;
		}
	}
	if ( $synced ) {
		/* translators: %d: number of field groups */
		$log['done'][] = sprintf( _n( '%d field group synced from the theme.', '%d field groups synced from the theme.', $synced, 'sees' ), $synced );
	}
}

/* ---------------------------------------------------------------------------
 * Triggers
 * ------------------------------------------------------------------------ */

// Deferred finish (see sees_run_setup()): Polylang's language cache and the rewrite rules
// are rebuilt on the request after setup, when Polylang loads the new languages and pages fresh.
add_action( 'init', function () {
	if ( get_option( 'sees_flush_rewrite' ) ) {
		delete_option( 'sees_flush_rewrite' );
		if ( function_exists( 'PLL' ) && isset( PLL()->model ) ) {
			PLL()->model->clean_languages_cache();
		}
		flush_rewrite_rules( false );
	}
}, 1 );

// Every activation queues the automatic setup.
add_action( 'after_switch_theme', function () {
	update_option( SEES_AUTOSETUP_PENDING, 1, false );
} );

// Runs on the next admin page load where the plugins are active (right after activation, or
// later, once the client has installed the plugins).
add_action( 'admin_init', function () {
	if ( ! get_option( SEES_AUTOSETUP_PENDING ) || ! current_user_can( 'manage_options' ) || wp_doing_ajax() ) {
		return;
	}
	if ( ! sees_setup_plugins_ready() ) {
		return; // The setup checklist explains which plugins are missing.
	}
	delete_option( SEES_AUTOSETUP_PENDING );
	$log = sees_run_setup();
	update_option( SEES_AUTOSETUP_RESULT, array( 'time' => time(), 'log' => $log ), false );
	sees_setup_skip_polylang_wizard();
}, 20 ); // Before Polylang's wizard redirect (priority 40).

/**
 * The theme already did what Polylang's setup wizard does, so don't send the admin there.
 */
function sees_setup_skip_polylang_wizard(): void {
	if ( function_exists( 'pll_languages_list' ) && pll_languages_list() ) {
		delete_transient( 'pll_activation_redirect' );
		if ( class_exists( 'PLL_Admin_Notices' ) && method_exists( 'PLL_Admin_Notices', 'dismiss' ) ) {
			PLL_Admin_Notices::dismiss( 'wizard' );
		}
	}
}

// "Run automatic setup" button on Appearance → SEES setup.
add_action( 'admin_post_sees_run_setup', function () {
	check_admin_referer( 'sees_run_setup' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'sees' ) );
	}
	$log = sees_run_setup();
	update_option( SEES_AUTOSETUP_RESULT, array( 'time' => time(), 'log' => $log ), false );
	sees_setup_skip_polylang_wizard();
	wp_safe_redirect( admin_url( 'themes.php?page=sees-setup' ) );
	exit;
} );

// Report what the automatic setup did (shown once).
add_action( 'admin_notices', function () {
	$result = get_option( SEES_AUTOSETUP_RESULT );
	if ( ! $result || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	delete_option( SEES_AUTOSETUP_RESULT );
	$log   = $result['log'];
	$class = $log['errors'] ? 'notice-warning' : 'notice-success';
	echo '<div class="notice ' . esc_attr( $class ) . ' is-dismissible"><p><strong>' . esc_html__( 'SEES automatic setup', 'sees' ) . '</strong> — ';
	echo esc_html( $log['done'] ? __( 'here is what was set up:', 'sees' ) : __( 'everything was already in place.', 'sees' ) );
	echo '</p>';
	if ( $log['done'] || $log['errors'] ) {
		echo '<ul style="list-style:disc;margin-inline-start:20px">';
		foreach ( $log['done'] as $line ) {
			echo '<li>' . esc_html( $line ) . '</li>';
		}
		foreach ( $log['errors'] as $line ) {
			echo '<li><strong>' . esc_html( $line ) . '</strong></li>';
		}
		echo '</ul>';
	}
	echo '</div>';
}, 5 );
