<?php
/**
 * SEES sample content — run with `wp eval-file bin/seed.php` (see bin/seed.sh).
 *
 * Creates EN + AR languages (Polylang), media, pages with ACF fields, solutions,
 * projects, FAQs, menus and Contact Form 7 forms. Facts the client hasn't
 * supplied yet are left as [PLACEHOLDERS].
 *
 * Safe to re-run: it stops if content was already seeded (pass SEES_RESEED=1 to wipe and redo).
 *
 * @package sees
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

foreach ( array( 'pll_set_post_language' => 'Polylang', 'update_field' => 'Advanced Custom Fields', 'wpcf7_contact_form' => 'Contact Form 7' ) as $fn => $plugin ) {
	if ( ! function_exists( $fn ) ) {
		WP_CLI::error( "$plugin must be active." );
	}
}

/* ---------------------------------------------------------------------------
 * Reset (optional)
 * ------------------------------------------------------------------------ */
if ( get_option( 'sees_seeded' ) ) {
	if ( ! getenv( 'SEES_RESEED' ) ) {
		WP_CLI::success( 'Already seeded. Set SEES_RESEED=1 to delete the sample content and seed again.' );
		return;
	}
	WP_CLI::log( 'Removing previous sample content…' );
	foreach ( (array) get_option( 'sees_seeded_ids', array() ) as $type => $ids ) {
		foreach ( (array) $ids as $id ) {
			if ( 'term' === $type ) {
				wp_delete_term( $id, 'project_category' );
			} elseif ( 'menu' === $type ) {
				wp_delete_nav_menu( $id );
			} elseif ( 'attachment' === $type ) {
				wp_delete_attachment( $id, true );
			} else {
				wp_delete_post( $id, true );
			}
		}
	}
	delete_option( 'sees_seeded_ids' );
}

// wp eval-file runs this file inside a function, so keep the tracker in $GLOBALS explicitly.
$GLOBALS['sees_created'] = array();
function sees_track( string $type, int $id ): int {
	$GLOBALS['sees_created'][ $type ][] = $id;
	return $id;
}

/* ---------------------------------------------------------------------------
 * Languages
 * ------------------------------------------------------------------------ */
$model = PLL()->model;
$langs = array(
	'en' => array( 'name' => 'English', 'slug' => 'en', 'locale' => 'en_US', 'rtl' => false, 'term_group' => 0, 'flag' => 'us' ),
	'ar' => array( 'name' => 'عربي', 'slug' => 'ar', 'locale' => 'ar', 'rtl' => true, 'term_group' => 1, 'flag' => 'sa' ),
);
foreach ( $langs as $slug => $args ) {
	if ( ! $model->get_language( $slug ) ) {
		$result = $model->add_language( $args );
		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}
		WP_CLI::log( "Added language: $slug" );
	}
}
$model->clean_languages_cache();

/**
 * Write Polylang settings through its own options object (it saves on shutdown,
 * so a plain update_option() would be overwritten).
 */
function sees_pll_option( string $key, $value ): void {
	$opts = PLL()->options;
	if ( is_object( $opts ) && method_exists( $opts, 'set' ) ) {
		$errors = $opts->set( $key, $value );
		if ( is_wp_error( $errors ) && $errors->has_errors() ) {
			WP_CLI::warning( "Polylang option $key: " . $errors->get_error_message() );
		}
		if ( method_exists( $opts, 'save' ) ) {
			$opts->save();
		}
		return;
	}
	$all         = get_option( 'polylang' );
	$all[ $key ] = $value;
	update_option( 'polylang', $all );
	PLL()->options[ $key ] = $value;
}

sees_pll_option( 'default_lang', 'en' );
sees_pll_option( 'force_lang', 1 );          // Language from directory: /en/, /ar/.
sees_pll_option( 'hide_default', false );    // Keep /en/ in URLs.
sees_pll_option( 'rewrite', true );          // /en/ rather than /language/en/.
sees_pll_option( 'redirect_lang', true );    // Front pages at /en/ and /ar/.
sees_pll_option( 'browser', false );
sees_pll_option( 'media_support', false );
sees_pll_option( 'post_types', array( 'solution', 'project', 'faq' ) );
sees_pll_option( 'taxonomies', array( 'project_category' ) );
sees_pll_option( 'sync', array( '_wp_page_template', 'menu_order', '_thumbnail_id', 'taxonomies' ) );

// Remove WordPress default content.
foreach ( array( 'hello-world' => 'post', 'sample-page' => 'page', 'privacy-policy' => 'page' ) as $slug => $type ) {
	$p = get_page_by_path( $slug, OBJECT, $type );
	if ( $p ) {
		wp_delete_post( $p->ID, true );
	}
}

/* ---------------------------------------------------------------------------
 * Site settings
 * ------------------------------------------------------------------------ */
update_option( 'blogname', 'SEES' );
update_option( 'blogdescription', 'Smart Electrical Engineering Solutions' );
update_option( 'timezone_string', 'Asia/Riyadh' );
update_option( 'start_of_week', 0 );
update_option( 'date_format', 'j F Y' );

/* ---------------------------------------------------------------------------
 * Media — import the bundled brand photos once
 * ------------------------------------------------------------------------ */
$photo_alts = array(
	'engineer'   => array( 'SEES engineer beside a solar array and distribution panels', 'مهندس من سيز بجانب ألواح شمسية ولوحات توزيع' ),
	'pylons'     => array( 'Transmission towers along a road', 'أبراج نقل الكهرباء على امتداد طريق' ),
	'facade'     => array( 'Modern building facade with illuminated signage', 'واجهة مبنى حديث بلافتة مضيئة' ),
	'concrete'   => array( 'Modern concrete building', 'مبنى خرساني حديث' ),
	'sitebanner' => array( 'SEES branded site hoarding', 'سياج موقع بعلامة سيز' ),
	'sign'       => array( 'SEES signage', 'لافتة سيز' ),
	'sky'        => array( 'Concrete architecture against the sky', 'عمارة خرسانية أمام السماء' ),
);
$media = array();
foreach ( $photo_alts as $name => $alts ) {
	$src = get_template_directory() . "/assets/img/$name.jpg";
	$tmp = wp_tempnam( "$name.jpg" );
	copy( $src, $tmp );
	$id = media_handle_sideload( array( 'name' => "sees-$name.jpg", 'tmp_name' => $tmp ), 0, $alts[0] );
	if ( is_wp_error( $id ) ) {
		WP_CLI::warning( "Media $name: " . $id->get_error_message() );
		continue;
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $alts[0] );
	$media[ $name ] = sees_track( 'attachment', $id );
}
WP_CLI::log( 'Imported ' . count( $media ) . ' photos.' );

/* ---------------------------------------------------------------------------
 * Helpers
 * ------------------------------------------------------------------------ */
function sees_make_post( array $args, string $lang, array $fields = array() ): int {
	$args = array_merge( array( 'post_status' => 'publish', 'post_author' => 1 ), $args );
	$id   = wp_insert_post( wp_slash( $args ), true );
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}
	pll_set_post_language( $id, $lang );
	foreach ( $fields as $name => $value ) {
		if ( '_wp_page_template' === $name || '_thumbnail_id' === $name ) {
			update_post_meta( $id, $name, $value );
		} elseif ( ! update_field( $name, $value, $id ) ) {
			// ACF couldn't resolve the field: store raw so get_field() still finds it.
			update_post_meta( $id, $name, $value );
		}
	}
	return sees_track( 'post', $id );
}

/** Create an EN/AR pair and link them as translations. */
function sees_pair( array $en, array $ar, array $shared = array() ): array {
	$ids = array(
		'en' => sees_make_post( $en['post'], 'en', array_merge( $shared, $en['fields'] ?? array() ) ),
		'ar' => sees_make_post( $ar['post'], 'ar', array_merge( $shared, $ar['fields'] ?? array() ) ),
	);
	pll_save_post_translations( $ids );
	return $ids;
}

/* ---------------------------------------------------------------------------
 * Pages
 * ------------------------------------------------------------------------ */
$home = sees_pair(
	array(
		'post'   => array( 'post_type' => 'page', 'post_title' => 'Home', 'post_name' => 'home' ),
		'fields' => array(
			'hero_eyebrow'      => 'Engineering a smarter tomorrow',
			'hero_title'        => 'Smart Electrical Engineering Solutions',
			'hero_text'         => 'Delivering intelligent, reliable and sustainable electrical solutions for a brighter future.',
			'feature_1'         => 'Innovative Solutions',
			'feature_2'         => 'Sustainable Growth',
			'feature_3'         => 'Reliable Execution',
			'feature_4'         => 'People Focused',
			'about_eyebrow'     => 'About SEES',
			'about_title'       => 'Engineering Intelligence for Real-World Impact',
			'about_text'        => 'SEES delivers integrated electrical engineering solutions that combine innovation, quality and sustainability. We power progress across diverse sectors with a focus on reliability, efficiency and long-term value.',
			'story_title'       => 'Our Story',
			'story_text'        => 'Building smarter infrastructure for a better tomorrow.',
			'stat_1_value'      => '[10+]',
			'stat_1_label'      => 'Years of Experience',
			'stat_2_value'      => '[100+]',
			'stat_2_label'      => 'Projects Delivered',
			'stat_3_label'      => 'Trusted by Leading Partners',
			'stat_4_label'      => 'Commitment to Sustainable Solutions',
			'solutions_eyebrow' => 'Our Solutions',
			'solutions_title'   => 'Integrated electrical solutions, from design to operation.',
			'marquee_items'     => "Power Distribution\nSolar PV\nSubstations\nSmart Buildings\nنهندس بذكاء ونُشغّل بشكل أفضل\nOperations & Maintenance",
			'featured_eyebrow'  => 'Featured Project',
			'cta_kicker'        => 'نهندس بذكاء ونُشغّل بشكل أفضل',
			'cta_title'         => 'Let\'s power your next project.',
			'cta_text'          => 'Tell us what you\'re building — an engineer will get back to you with next steps.',
		),
	),
	array(
		'post'   => array( 'post_type' => 'page', 'post_title' => 'الرئيسية', 'post_name' => 'الرئيسية' ),
		'fields' => array(
			'hero_eyebrow'      => 'نهندس غدًا أذكى',
			'hero_title'        => 'الحلول الهندسية الكهربائية الذكية',
			'hero_text'         => 'نقدّم حلولًا كهربائية ذكية وموثوقة ومستدامة من أجل مستقبل أكثر إشراقًا.',
			'feature_1'         => 'حلول مبتكرة',
			'feature_2'         => 'نمو مستدام',
			'feature_3'         => 'تنفيذ موثوق',
			'feature_4'         => 'الإنسان أولًا',
			'about_eyebrow'     => 'عن سيز',
			'about_title'       => 'ذكاء هندسي بأثر ملموس',
			'about_text'        => 'تقدّم سيز حلولًا هندسية كهربائية متكاملة تجمع بين الابتكار والجودة والاستدامة. نُسهم في دفع عجلة التقدّم في مختلف القطاعات مع تركيز على الموثوقية والكفاءة والقيمة على المدى الطويل.',
			'story_title'       => 'قصتنا',
			'story_text'        => 'نبني بنية تحتية أذكى من أجل غدٍ أفضل.',
			'stat_1_value'      => '[+10]',
			'stat_1_label'      => 'سنوات من الخبرة',
			'stat_2_value'      => '[+100]',
			'stat_2_label'      => 'مشروع منجز',
			'stat_3_label'      => 'شريك موثوق لكبرى الجهات',
			'stat_4_label'      => 'التزام بالحلول المستدامة',
			'solutions_eyebrow' => 'حلولنا',
			'solutions_title'   => 'حلول كهربائية متكاملة، من التصميم إلى التشغيل.',
			'marquee_items'     => "توزيع الطاقة\nالطاقة الشمسية\nالمحطات الفرعية\nالمباني الذكية\nEngineering Smarter, Powering Better\nالتشغيل والصيانة",
			'featured_eyebrow'  => 'مشروع مميّز',
			'cta_kicker'        => 'Engineering Smarter, Powering Better',
			'cta_title'         => 'لنُشغّل مشروعك القادم.',
			'cta_text'          => 'أخبرنا بما تبنيه، وسيتواصل معك أحد مهندسينا بالخطوات التالية.',
		),
	),
	array( 'header_image' => $media['sky'] ?? 0, 'about_image' => $media['concrete'] ?? 0, 'cta_image' => $media['facade'] ?? 0 )
);

$about = sees_pair(
	array(
		'post'   => array( 'post_type' => 'page', 'post_title' => 'About', 'post_name' => 'about', 'menu_order' => 1 ),
		'fields' => array(
			'header_subtitle' => 'An electrical engineering company built on precision, safety and smart technology.',
			'header_arabic'   => 'سيز للحلول الهندسية الكهربائية الذكية',
			'story_eyebrow'   => 'Who we are',
			'story_title'     => 'Smart engineering for the way the region builds today.',
			'story_text'      => '<p>SEES — Smart Electrical Engineering Solutions — is based in Al Shahamah District, Makkah. We design, install and maintain electrical systems for commercial, residential and infrastructure projects, bringing careful engineering and modern technology to every installation.</p><p>[Add a short paragraph on the company\'s founding, leadership and key certifications.]</p>',
			'mission'         => 'To deliver integrated electrical solutions that are safe, efficient and built to last.',
			'vision'          => 'To be the region\'s most trusted partner for smart electrical engineering.',
			'values_eyebrow'  => 'Our values',
			'values_title'    => 'What guides every project we take on.',
			'value_1_title'   => 'Innovation',
			'value_1_text'    => 'Modern technology applied where it makes systems safer and smarter.',
			'value_2_title'   => 'Sustainability',
			'value_2_text'    => 'Efficient designs and renewable energy that lower long-term impact.',
			'value_3_title'   => 'Reliability',
			'value_3_text'    => 'Work done to code, tested, documented and delivered on schedule.',
			'value_4_title'   => 'People',
			'value_4_text'    => 'Certified teams who listen, communicate clearly and put safety first.',
			'tagline_1'       => 'نصمّم الراحة بهندسة ذكية',
			'tagline_2'       => 'Engineering Comfort, Perfected.',
			'cta_title'       => 'Ready to work with SEES?',
			'cta_text'        => 'Let\'s talk about your next electrical project.',
		),
	),
	array(
		'post'   => array( 'post_type' => 'page', 'post_title' => 'من نحن', 'post_name' => 'من-نحن', 'menu_order' => 1 ),
		'fields' => array(
			'header_subtitle' => 'شركة هندسة كهربائية قائمة على الدقة والسلامة والتقنية الذكية.',
			'header_arabic'   => 'Smart Electrical Engineering Solutions',
			'story_eyebrow'   => 'من نحن',
			'story_title'     => 'هندسة ذكية تواكب طريقة البناء في المنطقة اليوم.',
			'story_text'      => '<p>سيز للحلول الهندسية الكهربائية الذكية شركة مقرّها حي الشهامة في مكة المكرمة. نصمّم الأنظمة الكهربائية ونركّبها ونصونها للمشاريع التجارية والسكنية ومشاريع البنية التحتية، ونجمع في كل تركيب بين الهندسة الدقيقة والتقنيات الحديثة.</p><p>[أضف فقرة قصيرة عن تأسيس الشركة وقيادتها وأبرز شهاداتها.]</p>',
			'mission'         => 'تقديم حلول كهربائية متكاملة آمنة وفعّالة ومصمّمة لتدوم.',
			'vision'          => 'أن نكون الشريك الأكثر ثقة في المنطقة في مجال الهندسة الكهربائية الذكية.',
			'values_eyebrow'  => 'قيمنا',
			'values_title'    => 'ما يوجّه كل مشروع نتولّاه.',
			'value_1_title'   => 'الابتكار',
			'value_1_text'    => 'تقنيات حديثة نوظّفها حيث تجعل الأنظمة أكثر أمانًا وذكاءً.',
			'value_2_title'   => 'الاستدامة',
			'value_2_text'    => 'تصاميم عالية الكفاءة وطاقة متجددة تقلّل الأثر على المدى الطويل.',
			'value_3_title'   => 'الموثوقية',
			'value_3_text'    => 'عمل مطابق للأنظمة، مُختبَر وموثّق ويُسلَّم في موعده.',
			'value_4_title'   => 'الإنسان',
			'value_4_text'    => 'فرق معتمدة تُصغي وتتواصل بوضوح وتضع السلامة أولًا.',
			'tagline_1'       => 'نصمّم الراحة بهندسة ذكية',
			'tagline_2'       => 'Engineering Comfort, Perfected.',
			'cta_title'       => 'مستعد للعمل مع سيز؟',
			'cta_text'        => 'لنتحدث عن مشروعك الكهربائي القادم.',
		),
	),
	array( '_wp_page_template' => 'page-about.php', 'header_image' => $media['sky'] ?? 0, 'story_image' => $media['sign'] ?? 0, 'tagline_image' => $media['pylons'] ?? 0 )
);

$solutions_page = sees_pair(
	array(
		'post'   => array( 'post_type' => 'page', 'post_title' => 'Our Solutions', 'post_name' => 'solutions', 'menu_order' => 2 ),
		'fields' => array(
			'header_subtitle' => 'Complete electrical engineering — designed, installed and maintained by one accountable team.',
			'process_eyebrow' => 'How we work',
			'process_title'   => 'Four steps from survey to switch-on.',
			'step_1_title'    => 'Assess',
			'step_1_text'     => 'Site survey, load analysis and a clear scope before design begins.',
			'step_2_title'    => 'Engineer',
			'step_2_text'     => 'Drawings, calculations and equipment selection, checked against code and budget.',
			'step_3_title'    => 'Install',
			'step_3_text'     => 'Certified crews build, test and commission, with full documentation at handover.',
			'step_4_title'    => 'Operate',
			'step_4_text'     => 'Maintenance plans and monitoring that keep systems efficient for years.',
			'faq_eyebrow'     => 'FAQ',
			'faq_title'       => 'Common questions',
			'cta_title'       => 'Not sure which solution fits?',
			'cta_text'        => 'Describe your project and our engineers will recommend the right approach.',
		),
	),
	array(
		'post'   => array( 'post_type' => 'page', 'post_title' => 'حلولنا', 'post_name' => 'حلولنا', 'menu_order' => 2 ),
		'fields' => array(
			'header_subtitle' => 'هندسة كهربائية متكاملة — تصميم وتركيب وصيانة من فريق واحد مسؤول.',
			'process_eyebrow' => 'كيف نعمل',
			'process_title'   => 'أربع خطوات من المسح الميداني حتى التشغيل.',
			'step_1_title'    => 'التقييم',
			'step_1_text'     => 'مسح الموقع وتحليل الأحمال وتحديد نطاق واضح قبل بدء التصميم.',
			'step_2_title'    => 'الهندسة',
			'step_2_text'     => 'المخططات والحسابات واختيار المعدّات، مع التحقق من مطابقتها للأنظمة والميزانية.',
			'step_3_title'    => 'التركيب',
			'step_3_text'     => 'فرق معتمدة تنفّذ وتختبر وتشغّل، مع توثيق كامل عند التسليم.',
			'step_4_title'    => 'التشغيل',
			'step_4_text'     => 'خطط صيانة ومراقبة تحافظ على كفاءة الأنظمة لسنوات.',
			'faq_eyebrow'     => 'الأسئلة الشائعة',
			'faq_title'       => 'أسئلة متكررة',
			'cta_title'       => 'لست متأكدًا من الحل المناسب؟',
			'cta_text'        => 'صف لنا مشروعك وسيوصي مهندسونا بالنهج الأنسب.',
		),
	),
	array( '_wp_page_template' => 'page-solutions.php', 'header_image' => $media['engineer'] ?? 0 )
);

/* Contact forms (one per language) */
function sees_make_form( string $title, array $t ): int {
	$form = WPCF7_ContactForm::get_template( array( 'title' => $title ) );
	$markup = <<<FORM
<div class="sees-form">
<div class="sees-form__grid">
<label>{$t['name']} *
[text* your-name autocomplete:name placeholder "{$t['name_ph']}"]</label>
<label>{$t['company']}
[text your-company autocomplete:organization placeholder "{$t['company_ph']}"]</label>
<label>{$t['email']} *
[email* your-email autocomplete:email placeholder "you@company.com"]</label>
<label>{$t['phone']}
[tel your-phone autocomplete:tel placeholder "+966"]</label>
</div>
<label>{$t['solution']}
[select your-solution {$t['options']}]</label>
<label>{$t['details']} *
[textarea* your-message x5 placeholder "{$t['details_ph']}"]</label>
[submit "{$t['submit']}"]
</div>
FORM;
	$mail            = $form->prop( 'mail' );
	$mail['subject'] = '[_site_title] ' . $t['subject'] . ': [your-name]';
	$mail['recipient'] = 'info@seesengineering.com';
	$mail['additional_headers'] = 'Reply-To: [your-email]';
	$mail['body']    = "Name: [your-name]\nCompany: [your-company]\nEmail: [your-email]\nPhone: [your-phone]\nSolution: [your-solution]\n\n[your-message]\n\n-- \nSent from [_url]";
	$form->set_properties( array( 'form' => $markup, 'mail' => $mail, 'mail_2' => array_merge( $form->prop( 'mail_2' ), array( 'active' => false ) ) ) );
	$id = $form->save();
	return sees_track( 'post', (int) $id );
}
$form_en = sees_make_form( 'Quote request (EN)', array(
	'name' => 'Full name', 'name_ph' => 'Your name', 'company' => 'Company', 'company_ph' => 'Company name',
	'email' => 'Email', 'phone' => 'Phone', 'solution' => 'Solution',
	'options' => '"Power Distribution" "Solar & Renewables" "Power Infrastructure" "Smart Buildings" "Operations & Maintenance"',
	'details' => 'Project details', 'details_ph' => 'Location, size, timeline…', 'submit' => 'Send request', 'subject' => 'Quote request',
) );
$form_ar = sees_make_form( 'طلب عرض سعر (AR)', array(
	'name' => 'الاسم الكامل', 'name_ph' => 'اسمك', 'company' => 'الشركة', 'company_ph' => 'اسم الشركة',
	'email' => 'البريد الإلكتروني', 'phone' => 'رقم الجوال', 'solution' => 'الحل المطلوب',
	'options' => '"توزيع الطاقة" "الطاقة الشمسية والمتجددة" "البنية التحتية للطاقة" "المباني الذكية" "التشغيل والصيانة"',
	'details' => 'تفاصيل المشروع', 'details_ph' => 'الموقع، الحجم، الجدول الزمني…', 'submit' => 'إرسال الطلب', 'subject' => 'طلب عرض سعر',
) );
foreach ( array( $form_en => 'en', $form_ar => 'ar' ) as $fid => $l ) {
	$f = wpcf7_contact_form( $fid );
	$f->set_locale( 'en' === $l ? 'en_US' : 'ar' );
	$f->save();
}

$contact = sees_pair(
	array(
		'post'   => array( 'post_type' => 'page', 'post_title' => 'Contact', 'post_name' => 'contact', 'menu_order' => 4 ),
		'fields' => array(
			'header_subtitle' => 'Tell us about your project — an engineer will get back to you with next steps.',
			'phone'           => '+966 50 326 4000',
			'email'           => 'info@seesengineering.com',
			'address'         => 'Al Shahamah District, Makkah, Saudi Arabia',
			'hours'           => '[Sun – Thu, 00:00 – 00:00]',
			'form_title'      => 'Request a quote',
			'form_text'       => 'Fields marked * are required.',
			'form_shortcode'  => '[contact-form-7 id="' . $form_en . '" title="Quote request (EN)"]',
			'success_title'   => 'Thank you!',
			'success_text'    => 'Your request has been sent. A SEES engineer will contact you shortly.',
		),
	),
	array(
		'post'   => array( 'post_type' => 'page', 'post_title' => 'تواصل معنا', 'post_name' => 'تواصل-معنا', 'menu_order' => 4 ),
		'fields' => array(
			'header_subtitle' => 'أخبرنا عن مشروعك، وسيتواصل معك أحد مهندسينا بالخطوات التالية.',
			'phone'           => '+966 50 326 4000',
			'email'           => 'info@seesengineering.com',
			'address'         => 'حي الشهامة، مكة المكرمة، المملكة العربية السعودية',
			'hours'           => '[الأحد – الخميس، 00:00 – 00:00]',
			'form_title'      => 'اطلب عرض سعر',
			'form_text'       => 'الحقول المعلَّمة بـ * إلزامية.',
			'form_shortcode'  => '[contact-form-7 id="' . $form_ar . '" title="طلب عرض سعر (AR)"]',
			'success_title'   => 'شكرًا لك!',
			'success_text'    => 'تم إرسال طلبك. سيتواصل معك أحد مهندسي سيز قريبًا.',
		),
	),
	array( '_wp_page_template' => 'page-contact.php', 'header_image' => $media['sign'] ?? 0 )
);

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home['en'] );

/* ---------------------------------------------------------------------------
 * Solutions
 * ------------------------------------------------------------------------ */
$solutions = array(
	array(
		'icon' => 'bolt', 'accent' => 'teal', 'img' => 'engineer',
		'en'   => array( 'Power Distribution', 'Switchgear, distribution boards and LV/MV panels — designed, built and commissioned to code.', 'Safe, well-sized distribution is the backbone of every building. We design and build the boards, panels and cabling that carry power where it\'s needed.', "LV / MV switchgear\nDistribution boards\nLoad studies\nTesting & commissioning" ),
		'ar'   => array( 'توزيع الطاقة', 'لوحات المفاتيح ولوحات التوزيع ولوحات الجهد المنخفض والمتوسط — تصميم وتصنيع وتشغيل وفق الأنظمة.', 'التوزيع الآمن والمحسوب بدقة هو العمود الفقري لكل مبنى. نصمّم وننفّذ اللوحات والكابلات التي توصل الطاقة حيث تحتاجها.', "لوحات مفاتيح الجهد المنخفض والمتوسط\nلوحات التوزيع\nدراسات الأحمال\nالاختبار والتشغيل" ),
	),
	array(
		'icon' => 'sun', 'accent' => 'green', 'img' => 'engineer',
		'en'   => array( 'Solar & Renewables', 'Rooftop and ground-mount PV systems, from yield study and design to grid connection.', 'Clean energy that pays back. From rooftop arrays to ground-mount fields, we handle the full PV lifecycle — yield study, design, installation and grid connection.', "Rooftop PV\nGround-mount arrays\nInverters & storage\nGrid connection" ),
		'ar'   => array( 'الطاقة الشمسية والمتجددة', 'أنظمة كهروضوئية على الأسطح وعلى الأرض، من دراسة الإنتاجية والتصميم حتى الربط بالشبكة.', 'طاقة نظيفة تعود عليك بالفائدة. من المصفوفات على الأسطح إلى المحطات الأرضية، نتولّى دورة حياة النظام الكهروضوئي كاملة — دراسة الإنتاجية والتصميم والتركيب والربط بالشبكة.', "أنظمة الأسطح الشمسية\nالمصفوفات الأرضية\nالعاكسات والتخزين\nالربط بالشبكة" ),
	),
	array(
		'icon' => 'pylon', 'accent' => 'petrol', 'img' => 'pylons',
		'en'   => array( 'Power Infrastructure', 'Substations, cabling and overhead lines that bring reliable power to sites and communities.', 'Substations, underground cabling and overhead lines that bring reliable power to developments, sites and communities.', "Substations\nUnderground cabling\nOverhead lines\nStreet lighting" ),
		'ar'   => array( 'البنية التحتية للطاقة', 'محطات فرعية وكابلات وخطوط هوائية توصل طاقة موثوقة إلى المواقع والمجتمعات.', 'محطات فرعية وكابلات أرضية وخطوط هوائية توصل طاقة موثوقة إلى المشاريع والمواقع والمجتمعات.', "المحطات الفرعية\nالكابلات الأرضية\nالخطوط الهوائية\nإنارة الطرق" ),
	),
	array(
		'icon' => 'home', 'accent' => 'teal', 'img' => 'facade',
		'en'   => array( 'Smart Buildings', 'Lighting control, automation and energy monitoring that cut consumption and simplify operations.', 'Buildings that think. We integrate lighting control, automation and energy monitoring so your facility uses less and runs smoother.', "Lighting control\nBuilding automation\nEnergy monitoring\nLow-current systems" ),
		'ar'   => array( 'المباني الذكية', 'التحكم في الإنارة والأتمتة ومراقبة الطاقة لخفض الاستهلاك وتبسيط التشغيل.', 'مبانٍ تفكّر. ندمج أنظمة التحكم في الإنارة والأتمتة ومراقبة الطاقة لتستهلك منشأتك أقل وتعمل بسلاسة أكبر.', "التحكم في الإنارة\nأتمتة المباني\nمراقبة الطاقة\nأنظمة التيار الخفيف" ),
	),
	array(
		'icon' => 'wrench', 'accent' => 'green', 'img' => 'sitebanner',
		'en'   => array( 'Operations & Maintenance', 'Planned maintenance, inspection and fast response that keep systems efficient year after year.', 'Planned maintenance, inspection and fast response that keep your systems efficient — and your operation running — year after year.', "Preventive maintenance\nThermal inspection\nEmergency response\nUpgrades & retrofits" ),
		'ar'   => array( 'التشغيل والصيانة', 'صيانة مخططة وفحص واستجابة سريعة تحافظ على كفاءة الأنظمة عامًا بعد عام.', 'صيانة مخططة وفحص واستجابة سريعة تحافظ على كفاءة أنظمتك واستمرارية أعمالك عامًا بعد عام.', "الصيانة الوقائية\nالفحص الحراري\nالاستجابة للطوارئ\nالتحديث والتطوير" ),
	),
);
foreach ( $solutions as $i => $s ) {
	$shared = array( 'number' => sprintf( '%02d', $i + 1 ), 'icon' => $s['icon'], 'accent' => $s['accent'], '_thumbnail_id' => $media[ $s['img'] ] ?? 0 );
	$make   = fn( $c ) => array(
		'post'   => array( 'post_type' => 'solution', 'post_title' => $c[0], 'post_excerpt' => $c[1], 'menu_order' => $i + 1 ),
		'fields' => array( 'intro' => $c[2], 'bullets' => $c[3] ),
	);
	$en = $make( $s['en'] );
	$en['post']['post_name'] = sanitize_title( $s['en'][0] );
	$ar = $make( $s['ar'] );
	$ar['post']['post_name'] = sanitize_title( $s['en'][0] ) . '-ar';
	sees_pair( $en, $ar, $shared );
}
WP_CLI::log( 'Created solutions.' );

/* ---------------------------------------------------------------------------
 * FAQs
 * ------------------------------------------------------------------------ */
$faqs = array(
	array( 'Which areas do you serve?', 'We are based in Makkah and work on projects across [SERVICE AREA]. Get in touch to confirm coverage for your site.',
		'ما المناطق التي تخدمونها؟', 'مقرّنا في مكة المكرمة ونعمل على مشاريع في [منطقة الخدمة]. تواصل معنا للتأكد من تغطية موقعك.' ),
	array( 'Do you handle design and installation together?', 'Yes. One team takes your project from survey and engineering through installation, commissioning and handover, so there is a single point of accountability.',
		'هل تتولّون التصميم والتركيب معًا؟', 'نعم. يتولّى فريق واحد مشروعك من المسح والهندسة إلى التركيب والتشغيل والتسليم، لتكون هناك جهة واحدة مسؤولة.' ),
	array( 'Can you work on existing buildings?', 'Yes — upgrades, retrofits and expansions of existing electrical systems are a core part of our work, including solar added to occupied buildings.',
		'هل يمكنكم العمل على مبانٍ قائمة؟', 'نعم، فتحديث الأنظمة الكهربائية القائمة وتطويرها وتوسعتها جزء أساسي من عملنا، بما في ذلك إضافة الطاقة الشمسية إلى مبانٍ مأهولة.' ),
	array( 'Do you offer maintenance contracts?', 'We offer planned maintenance and inspection programmes tailored to your facility. [Add contract terms or response times here.]',
		'هل تقدّمون عقود صيانة؟', 'نقدّم برامج صيانة وفحص مخططة مصمّمة وفق احتياجات منشأتك. [أضف شروط العقد أو أوقات الاستجابة هنا.]' ),
);
foreach ( $faqs as $i => $f ) {
	sees_pair(
		array( 'post' => array( 'post_type' => 'faq', 'post_title' => $f[0], 'post_content' => $f[1], 'menu_order' => $i + 1 ) ),
		array( 'post' => array( 'post_type' => 'faq', 'post_title' => $f[2], 'post_content' => $f[3], 'menu_order' => $i + 1, 'post_name' => 'faq-' . ( $i + 1 ) . '-ar' ) )
	);
}
WP_CLI::log( 'Created FAQs.' );

/* ---------------------------------------------------------------------------
 * Project categories + projects
 * ------------------------------------------------------------------------ */
$cats = array(
	'power-distribution' => array( 'Power Distribution', 'توزيع الطاقة' ),
	'solar'              => array( 'Solar', 'الطاقة الشمسية' ),
	'infrastructure'     => array( 'Infrastructure', 'البنية التحتية' ),
	'smart-buildings'    => array( 'Smart Buildings', 'المباني الذكية' ),
	'maintenance'        => array( 'Maintenance', 'الصيانة' ),
);
$terms = array();
foreach ( $cats as $slug => $names ) {
	$pair = array();
	foreach ( array( 'en' => array( $names[0], $slug ), 'ar' => array( $names[1], $slug . '-ar' ) ) as $l => $t ) {
		$existing = get_term_by( 'slug', $t[1], 'project_category' );
		$tid      = $existing ? $existing->term_id : wp_insert_term( $t[0], 'project_category', array( 'slug' => $t[1] ) )['term_id'];
		pll_set_term_language( $tid, $l );
		$pair[ $l ] = sees_track( 'term', (int) $tid );
	}
	pll_save_term_translations( $pair );
	$terms[ $slug ] = $pair;
}

$projects = array(
	array( 'solar', 'engineer', true ),
	array( 'power-distribution', 'engineer', false ),
	array( 'infrastructure', 'pylons', false ),
	array( 'smart-buildings', 'facade', false ),
	array( 'smart-buildings', 'concrete', false ),
	array( 'maintenance', 'sitebanner', false ),
);
foreach ( $projects as $i => $p ) {
	$featured = $p[2];
	$ids      = sees_pair(
		array(
			'post'   => array(
				'post_type'    => 'project',
				'post_title'   => $featured ? '[PROJECT NAME] — Solar & distribution upgrade' : '[PROJECT NAME]',
				'post_name'    => 'project-' . ( $i + 1 ),
				'post_content' => '<!-- wp:paragraph --><p>[Describe the project: scope, challenge, what SEES delivered and the result.]</p><!-- /wp:paragraph -->',
				'menu_order'   => $i + 1,
			),
			'fields' => array( 'client' => '[Client]', 'city' => '[City]', 'year' => '[Year]', 'summary' => '[One or two sentences on the client\'s challenge and what SEES delivered.]' ),
		),
		array(
			'post'   => array(
				'post_type'    => 'project',
				'post_title'   => $featured ? '[اسم المشروع] — تحديث أنظمة الطاقة الشمسية والتوزيع' : '[اسم المشروع]',
				'post_name'    => 'project-' . ( $i + 1 ) . '-ar',
				'post_content' => '<!-- wp:paragraph --><p>[صف المشروع: النطاق والتحدي وما قدّمته سيز والنتيجة.]</p><!-- /wp:paragraph -->',
				'menu_order'   => $i + 1,
			),
			'fields' => array( 'client' => '[العميل]', 'city' => '[المدينة]', 'year' => '[السنة]', 'summary' => '[جملة أو جملتان عن تحدي العميل وما قدّمته سيز.]' ),
		),
		array( 'is_featured' => $featured ? 1 : 0, '_thumbnail_id' => $media[ $p[1] ] ?? 0 )
	);
	wp_set_object_terms( $ids['en'], array( $terms[ $p[0] ]['en'] ), 'project_category' );
	wp_set_object_terms( $ids['ar'], array( $terms[ $p[0] ]['ar'] ), 'project_category' );
}
WP_CLI::log( 'Created projects.' );

/* ---------------------------------------------------------------------------
 * Menus (one per language; Polylang assigns them to the location)
 * ------------------------------------------------------------------------ */
$menu_ids = array();
foreach ( array( 'en' => 'Primary (EN)', 'ar' => 'Primary (AR)' ) as $l => $name ) {
	$existing = wp_get_nav_menu_object( $name );
	if ( $existing ) {
		wp_delete_nav_menu( $existing->term_id );
	}
	$menu_id = sees_track( 'menu', (int) wp_create_nav_menu( $name ) );
	$items   = array(
		array( 'post_type', 'page', $home[ $l ] ),
		array( 'post_type', 'page', $about[ $l ] ),
		array( 'post_type', 'page', $solutions_page[ $l ] ),
		array( 'post_type_archive', 'project', 0 ),
		array( 'post_type', 'page', $contact[ $l ] ),
	);
	foreach ( $items as $pos => $item ) {
		$args = array(
			'menu-item-type'     => $item[0],
			'menu-item-object'   => $item[1],
			'menu-item-status'   => 'publish',
			'menu-item-position' => $pos + 1,
		);
		if ( 'post_type' === $item[0] ) {
			$args['menu-item-object-id'] = $item[2];
		} else {
			$args['menu-item-title'] = 'en' === $l ? 'Projects' : 'المشاريع';
		}
		wp_update_nav_menu_item( $menu_id, 0, $args );
	}
	$menu_ids[ $l ] = $menu_id;
}
$nav_menus = (array) PLL()->options['nav_menus'];
$nav_menus[ get_stylesheet() ]['primary'] = $menu_ids;
sees_pll_option( 'nav_menus', $nav_menus );
set_theme_mod( 'nav_menu_locations', array_merge( (array) get_theme_mod( 'nav_menu_locations', array() ), array( 'primary' => $menu_ids['en'] ) ) );

/* ---------------------------------------------------------------------------
 * Translated site tagline (Polylang strings)
 * ------------------------------------------------------------------------ */
if ( class_exists( 'PLL_MO' ) ) {
	$lang = $model->get_language( 'ar' );
	$mo   = new PLL_MO();
	$mo->import_from_db( $lang );
	$mo->add_entry( $mo->make_entry( 'Smart Electrical Engineering Solutions', 'سيز للحلول الهندسية الكهربائية الذكية' ) );
	$mo->add_entry( $mo->make_entry( 'SEES', 'سيز' ) );
	$mo->export_to_db( $lang );
}

update_option( 'sees_seeded', time() );
update_option( 'sees_seeded_ids', $GLOBALS['sees_created'], false );
flush_rewrite_rules( false );

WP_CLI::success( 'Seeded EN + AR sample content.' );
