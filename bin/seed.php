<?php
/**
 * Local/demo seed — run with `wp eval-file bin/seed.php` (see bin/seed.sh).
 *
 * Uses the theme's automatic setup (theme/sees/inc/installer.php) and adds what only
 * makes sense on a local or demo site: the bundled photos in the Media Library and
 * published (rather than draft) sample projects.
 *
 * SEES_RESEED=1 deletes the theme's content (pages, solutions, projects, FAQs, forms,
 * menus, project categories, imported photos) first and builds it again.
 *
 * @package sees
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

if ( ! function_exists( 'sees_run_setup' ) ) {
	WP_CLI::error( 'Activate the SEES theme first.' );
}
if ( ! sees_setup_plugins_ready() ) {
	WP_CLI::error( 'Polylang, Advanced Custom Fields and Contact Form 7 must be active.' );
}

/* ---------------------------------------------------------------------------
 * Optional reset
 * ------------------------------------------------------------------------ */
if ( getenv( 'SEES_RESEED' ) ) {
	WP_CLI::log( 'Removing the theme content…' );
	$types = array( 'page', 'solution', 'project', 'faq', 'wpcf7_contact_form', 'sees_download' );
	foreach ( get_posts( array( 'post_type' => $types, 'post_status' => 'any', 'posts_per_page' => -1, 'lang' => '', 'fields' => 'ids' ) ) as $id ) {
		wp_delete_post( $id, true );
	}
	foreach ( get_terms( array( 'taxonomy' => 'project_category', 'hide_empty' => false, 'lang' => '', 'fields' => 'ids' ) ) as $term_id ) {
		wp_delete_term( $term_id, 'project_category' );
	}
	foreach ( array( 'Primary (EN)', 'Primary (AR)' ) as $menu ) {
		if ( wp_get_nav_menu_object( $menu ) ) {
			wp_delete_nav_menu( $menu );
		}
	}
	foreach ( (array) get_option( 'sees_setup_media', array() ) as $attachment ) {
		wp_delete_attachment( (int) $attachment, true );
	}
	delete_option( 'sees_setup_media' );
	delete_option( 'sees_seeded_projects' );
	delete_option( 'sees_job_forms' );
	delete_option( 'sees_seeded_download_types' );
	foreach ( get_terms( array( 'taxonomy' => 'sees_download_type', 'hide_empty' => false, 'fields' => 'ids' ) ) as $term_id ) {
		wp_delete_term( $term_id, 'sees_download_type' );
	}
	foreach ( (array) get_option( 'sees_sample_downloads', array() ) as $attachment ) {
		wp_delete_attachment( (int) $attachment, true );
	}
	delete_option( 'sees_sample_downloads' );
	foreach ( (array) get_option( 'sees_sample_posts', array() ) as $post_id ) {
		wp_delete_post( (int) $post_id, true );
	}
	delete_option( 'sees_sample_posts' );
	delete_option( 'sees_seeded_news_categories' );
	delete_option( 'sees_menu_additions' );
	update_option( 'show_on_front', 'posts' );
	update_option( 'page_on_front', 0 );
	$nav_menus = (array) PLL()->options['nav_menus'];
	unset( $nav_menus[ get_stylesheet() ] );
	sees_setup_pll_option( 'nav_menus', $nav_menus );
}

/* ---------------------------------------------------------------------------
 * Site settings for the local copy
 * ------------------------------------------------------------------------ */
update_option( 'blogname', 'SEES' );
update_option( 'blogdescription', 'Smart Electrical Engineering Solutions' );
update_option( 'timezone_string', 'Asia/Riyadh' );
update_option( 'start_of_week', 0 );
update_option( 'date_format', 'j F Y' );

/* ---------------------------------------------------------------------------
 * Theme setup + local extras
 * ------------------------------------------------------------------------ */
$log = sees_run_setup( array(
	'project_status' => 'publish',
	'sample_images'  => true,
) );

// Use the imported photos on the pages (only where no image is set yet).
$media = (array) get_option( 'sees_setup_media', array() );
$pick  = fn( $file ) => (int) ( $media[ $file ] ?? 0 );
$front = (int) get_option( 'page_on_front' );
$about = sees_setup_find_page( 'about', 'page-about.php', 'en' );
$images = array(
	array( $front, array( 'header_image' => $pick( 'hero-building.jpg' ), 'about_image' => $pick( 'concrete.jpg' ), 'cta_image' => $pick( 'facade.jpg' ) ) ),
	array( (int) pll_get_post( $front, 'ar' ), array( 'header_image' => $pick( 'hero-building-rtl.jpg' ), 'about_image' => $pick( 'concrete.jpg' ), 'cta_image' => $pick( 'facade.jpg' ) ) ),
	array( $about, array( 'story_image' => $pick( 'sign.jpg' ), 'tagline_image' => $pick( 'pylons.jpg' ) ) ),
	array( (int) pll_get_post( $about, 'ar' ), array( 'story_image' => $pick( 'sign.jpg' ), 'tagline_image' => $pick( 'pylons.jpg' ) ) ),
);
foreach ( $images as list( $post_id, $fields ) ) {
	if ( $post_id ) {
		sees_setup_fill_fields( $post_id, array_filter( $fields ) );
	}
}

/* ---------------------------------------------------------------------------
 * Sample support documents (local only — clearly labelled placeholder PDFs)
 * ------------------------------------------------------------------------ */
$fixtures = '/var/www/fixtures';
if ( is_dir( $fixtures ) && ! get_posts( array( 'post_type' => 'sees_download', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids' ) ) ) {
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	$samples = array(
		array( 'company-profile', 'brochures', 'both', '1.0', 'SAMPLE — Company profile', 'نموذج — الملف التعريفي للشركة', 'Placeholder brochure for testing the download centre.', 'ملف تجريبي لاختبار مركز التنزيلات.' ),
		array( 'panel-datasheet', 'datasheets', 'en', '2.1', 'SAMPLE — LV panel datasheet', 'نموذج — ورقة بيانات لوحة جهد منخفض', 'Placeholder datasheet.', 'ورقة بيانات تجريبية.' ),
		array( 'maintenance-guide', 'manuals', 'ar', '1.3', 'SAMPLE — Maintenance guide', 'نموذج — دليل الصيانة', 'Placeholder guide.', 'دليل تجريبي.' ),
		array( 'iso-certificate', 'certificates', 'both', '', 'SAMPLE — Quality certificate', 'نموذج — شهادة الجودة', 'Placeholder certificate.', 'شهادة تجريبية.' ),
	);
	$attachments = array();
	foreach ( $samples as $i => $d ) {
		$tmp = wp_tempnam( 'sample.pdf' );
		copy( "$fixtures/sample-{$d[0]}.pdf", $tmp );
		$file = media_handle_sideload( array( 'name' => "sees-sample-{$d[0]}.pdf", 'tmp_name' => $tmp ), 0 );
		if ( is_wp_error( $file ) ) {
			WP_CLI::warning( $file->get_error_message() );
			continue;
		}
		$attachments[] = $file;
		$id = wp_insert_post( array( 'post_type' => 'sees_download', 'post_status' => 'publish', 'post_title' => $d[4], 'menu_order' => $i ) );
		foreach ( array( 'download_file' => $file, 'doc_language' => $d[2], 'version' => $d[3], 'title_ar' => $d[5], 'description_en' => $d[6], 'description_ar' => $d[7] ) as $k => $v ) {
			update_field( $k, $v, $id );
		}
		wp_set_object_terms( $id, $d[1], 'sees_download_type' );
	}
	update_option( 'sees_sample_downloads', $attachments, false );
	$log['done'][] = 'Sample support documents created (local only).';
}

/* ---------------------------------------------------------------------------
 * Sample news posts (local only — clearly labelled placeholders)
 * ------------------------------------------------------------------------ */
if ( ! get_option( 'sees_sample_posts' ) ) {
	$media = (array) get_option( 'sees_setup_media', array() );
	$cat   = fn( $slug ) => ( $t = get_term_by( 'slug', $slug, 'category' ) ) ? (int) $t->term_id : 0;
	$body  = fn( $p ) => '<!-- wp:paragraph --><p>' . esc_html( $p ) . '</p><!-- /wp:paragraph -->';
	$samples = array(
		array( 'news', 'engineer.jpg', '[Sample] SEES completes a solar and distribution upgrade', '[نموذج] سيز تُنجز تحديث أنظمة الطاقة الشمسية والتوزيع', 3 ),
		array( 'insights', 'pylons.jpg', '[Sample] Five checks before upgrading an electrical panel', '[نموذج] خمسة فحوصات قبل تحديث اللوحة الكهربائية', 10 ),
		array( 'news', 'facade.jpg', '[Sample] Our new office signage goes up in Makkah', '[نموذج] تركيب اللافتة الجديدة لمكتبنا في مكة المكرمة', 20 ),
	);
	$ids = array();
	foreach ( $samples as $d ) {
		$pair = array();
		foreach ( array( 'en' => $d[2], 'ar' => $d[3] ) as $lang => $title ) {
			$text = 'en' === $lang
				? 'This is a sample post for testing the News section. Replace it with real company news. ' . str_repeat( 'Placeholder text for layout testing. ', 40 )
				: 'هذا منشور تجريبي لاختبار قسم الأخبار. استبدله بأخبار حقيقية للشركة. ' . str_repeat( 'نص مؤقت لاختبار التصميم. ', 40 );
			$id = wp_insert_post( array(
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_excerpt' => 'en' === $lang ? 'Sample post for testing the News section.' : 'منشور تجريبي لاختبار قسم الأخبار.',
				'post_content' => $body( $text ) . $body( $text ),
				'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( "-{$d[4]} days" ) ),
			) );
			pll_set_post_language( $id, $lang );
			wp_set_post_categories( $id, array_filter( array( $cat( 'en' === $lang ? $d[0] : $d[0] . '-ar' ) ) ) );
			if ( ! empty( $media[ $d[1] ] ) ) {
				set_post_thumbnail( $id, $media[ $d[1] ] );
			}
			$pair[ $lang ] = $id;
			$ids[]         = $id;
		}
		pll_save_post_translations( $pair );
	}
	update_option( 'sees_sample_posts', $ids, false );
	$log['done'][] = 'Sample news posts created (local only).';
}

PLL()->model->clean_languages_cache();
flush_rewrite_rules( false );

foreach ( $log['done'] as $line ) {
	WP_CLI::log( '  ✓ ' . $line );
}
foreach ( $log['errors'] as $line ) {
	WP_CLI::warning( $line );
}
WP_CLI::success( $log['done'] ? 'SEES content is set up (EN + AR).' : 'Everything was already in place.' );
