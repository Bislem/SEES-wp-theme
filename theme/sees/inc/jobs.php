<?php
/**
 * Careers and job helpers.
 *
 * Jobs are deliberately never created, updated or deleted by the content seeder.
 * Administrators own every vacancy through the sees_job post type and its ACF fields.
 *
 * @package sees
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Current front-end language slug.
 */
function sees_jobs_language(): string {
	if ( function_exists( 'pll_current_language' ) ) {
		$lang = (string) pll_current_language( 'slug' );
		if ( in_array( $lang, array( 'en', 'ar' ), true ) ) {
			return $lang;
		}
	}
	return is_rtl() ? 'ar' : 'en';
}

/**
 * Fixed interface copy that must work before a translation catalogue is rebuilt.
 */
function sees_jobs_text( string $key ): string {
	$copy = array(
		'careers_nav'       => array( 'Careers', 'الوظائف' ),
		'hero_eyebrow'       => array( 'People power a smarter tomorrow', 'الناس يصنعون غدًا أذكى' ),
		'hero_title'         => array( 'Build Your Career With SEES', 'ابنِ مستقبلك المهني مع SEES' ),
		'hero_intro'         => array( 'Discover published opportunities to contribute to thoughtful electrical engineering work with SEES.', 'اكتشف الفرص المنشورة للمساهمة في أعمال هندسة كهربائية مدروسة مع سيز.' ),
		'view_roles'         => array( 'View open positions', 'استعرض الوظائف المتاحة' ),
		'values_label'       => array( 'Careers at SEES', 'العمل في سيز' ),
		'professional'       => array( 'Professional Growth', 'النمو المهني' ),
		'innovation'         => array( 'Innovation', 'الابتكار' ),
		'safety'             => array( 'Safety', 'السلامة' ),
		'learning'           => array( 'Continuous Learning', 'التعلم المستمر' ),
		'impact'             => array( 'Meaningful Impact', 'أثر هادف' ),
		'why_eyebrow'        => array( 'A career with purpose', 'مسيرة مهنية هادفة' ),
		'why_title'          => array( 'Why Work With SEES', 'لماذا تنضم إلى سيز؟' ),
		'why_text'           => array( 'We welcome people who care about sound engineering, responsible delivery and learning through real project work.', 'نرحّب بمن يهتمون بالهندسة المتقنة والتنفيذ المسؤول والتعلّم من خلال العمل في المشاريع الفعلية.' ),
		'why_point_1'        => array( 'Engineering development', 'تطوير الخبرات الهندسية' ),
		'why_point_2'        => array( 'Collaborative work', 'عمل تعاوني' ),
		'why_point_3'        => array( 'Practical challenges', 'تحديات عملية' ),
		'why_point_4'        => array( 'Responsible outcomes', 'نتائج مسؤولة' ),
		'jobs_eyebrow'       => array( 'Open positions', 'الوظائف المتاحة' ),
		'jobs_title'         => array( 'Join Our Team', 'انضم إلى فريقنا' ),
		'jobs_intro'         => array( 'Only currently published and open vacancies appear below.', 'تظهر أدناه الوظائف المنشورة والمفتوحة حالياً فقط.' ),
		'filter_department'  => array( 'All departments', 'جميع الأقسام' ),
		'filter_location'    => array( 'All locations', 'جميع المواقع' ),
		'filter_type'        => array( 'All employment types', 'جميع أنواع الدوام' ),
		'clear_filters'      => array( 'Clear filters', 'مسح عوامل التصفية' ),
		'no_jobs'            => array( 'There are currently no open positions. Please check back later for new opportunities.', 'لا توجد عروض عمل متاحة حالياً. يُرجى زيارة هذه الصفحة لاحقاً للاطلاع على الفرص الجديدة.' ),
		'no_matches'         => array( 'No open positions match these filters.', 'لا توجد وظائف مفتوحة مطابقة لعوامل التصفية.' ),
		'department'         => array( 'Department', 'القسم' ),
		'location'           => array( 'Location', 'الموقع' ),
		'employment_type'    => array( 'Employment type', 'نوع الدوام' ),
		'published'          => array( 'Published', 'تاريخ النشر' ),
		'closing_date'       => array( 'Closing date', 'آخر موعد للتقديم' ),
		'view_details'       => array( 'View details', 'عرض التفاصيل' ),
		'apply_now'          => array( 'Apply now', 'قدّم الآن' ),
		'open'               => array( 'Open', 'مفتوحة' ),
		'closed'             => array( 'Closed', 'مغلقة' ),
		'responsibilities'   => array( 'Responsibilities', 'المسؤوليات' ),
		'requirements'       => array( 'Requirements', 'المتطلبات' ),
		'back_to_jobs'       => array( 'Back to all vacancies', 'العودة إلى جميع الوظائف' ),
		'application'        => array( 'Apply for this position', 'التقديم على هذه الوظيفة' ),
		'application_intro'  => array( 'Complete the application form below. Required fields are marked clearly.', 'أكمل نموذج التقديم أدناه. الحقول المطلوبة موضحة بوضوح.' ),
		'application_sent'      => array( 'Application sent!', 'تم إرسال طلبك!' ),
		'application_sent_text' => array( 'Thank you for applying. We have emailed you a confirmation, and our team will contact you if your profile matches the role.', 'شكرًا لتقديمك. أرسلنا إليك رسالة تأكيد عبر البريد الإلكتروني، وسيتواصل معك فريقنا إذا كان ملفك مناسبًا للوظيفة.' ),
		'closed_notice'      => array( 'Applications for this position are closed.', 'أُغلق باب التقديم على هذه الوظيفة.' ),
		'process_eyebrow'    => array( 'Recruitment process', 'مراحل التوظيف' ),
		'process_title'      => array( 'A Clear and Simple Process', 'عملية واضحة وبسيطة' ),
		'process_intro'      => array( 'The steps below describe the intended workflow and may vary by role.', 'توضح الخطوات أدناه آلية العمل المقترحة، وقد تختلف بحسب الوظيفة.' ),
		'process_1'          => array( 'Apply', 'تقديم الطلب' ),
		'process_1_text'     => array( 'Submit the information requested for the published role.', 'أرسل المعلومات المطلوبة للوظيفة المنشورة.' ),
		'process_2'          => array( 'Application Review', 'مراجعة الطلبات' ),
		'process_2_text'     => array( 'The relevant team reviews complete applications.', 'يراجع الفريق المعني الطلبات المكتملة.' ),
		'process_3'          => array( 'Interview', 'المقابلة' ),
		'process_3_text'     => array( 'Selected candidates may be invited to an interview.', 'قد تتم دعوة المرشحين المختارين إلى مقابلة.' ),
		'process_4'          => array( 'Final Decision', 'القرار النهائي' ),
		'process_4_text'     => array( 'Candidates are informed when the review is complete.', 'يتم إبلاغ المرشحين عند اكتمال المراجعة.' ),
		'cta_title'          => array( 'Explore current opportunities.', 'استكشف الفرص الحالية.' ),
		'cta_text'           => array( 'Applications are accepted only through a published vacancy with an enabled application form.', 'تُقبل الطلبات فقط من خلال وظيفة منشورة ومفعّل لها نموذج التقديم.' ),
		'cta_button'         => array( 'See open positions', 'عرض الوظائف المتاحة' ),
	);

	if ( ! isset( $copy[ $key ] ) ) {
		return $key;
	}
	return 'ar' === sees_jobs_language() ? $copy[ $key ][1] : $copy[ $key ][0];
}

/**
 * Localised display value for employment type keys stored by ACF.
 */
function sees_job_type_label( string $value ): string {
	$labels = array(
		'full_time'  => array( 'Full time', 'دوام كامل' ),
		'part_time'  => array( 'Part time', 'دوام جزئي' ),
		'contract'   => array( 'Contract', 'عقد' ),
		'internship' => array( 'Internship', 'تدريب' ),
		'temporary'  => array( 'Temporary', 'مؤقت' ),
	);
	if ( ! isset( $labels[ $value ] ) ) {
		return $value;
	}
	return 'ar' === sees_jobs_language() ? $labels[ $value ][1] : $labels[ $value ][0];
}

/**
 * Language assigned to a vacancy. An empty string means that Polylang is not
 * active or that the post has not been assigned a language yet.
 */
function sees_job_post_language( int $post_id ): string {
	if ( ! function_exists( 'pll_get_post_language' ) ) {
		return '';
	}

	return (string) pll_get_post_language( $post_id, 'slug' );
}

/**
 * All posts linked to the same Polylang vacancy, including non-published
 * translations. The requested post is always present in the returned list.
 *
 * @return int[]
 */
function sees_job_translation_ids( int $post_id ): array {
	$ids = array( $post_id );
	if ( function_exists( 'pll_get_post_translations' ) ) {
		$translations = pll_get_post_translations( $post_id );
		if ( is_array( $translations ) ) {
			$ids = array_merge( $ids, array_map( 'absint', array_values( $translations ) ) );
		}
	}

	$ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
	sort( $ids, SORT_NUMERIC );
	return $ids;
}

/**
 * Stable key used to group translated posts into one logical vacancy.
 */
function sees_job_group_key( int $post_id ): string {
	return implode( ':', sees_job_translation_ids( $post_id ) );
}

/**
 * Deterministic source for operational vacancy fields shared by translations.
 * Prefer the published default-language post, then the first published member.
 */
function sees_job_canonical_id( int $post_id ): int {
	$ids          = sees_job_translation_ids( $post_id );
	$published    = array_values( array_filter( $ids, fn( int $id ): bool => 'publish' === get_post_status( $id ) ) );
	$candidates   = $published ?: $ids;
	$default_lang = function_exists( 'pll_default_language' ) ? (string) pll_default_language( 'slug' ) : '';

	if ( $default_lang ) {
		foreach ( $candidates as $candidate ) {
			if ( $default_lang === sees_job_post_language( $candidate ) ) {
				return $candidate;
			}
		}
	}

	return $candidates[0] ?? $post_id;
}

/**
 * Resolve a vacancy field and remember which translation supplied it.
 *
 * Shared fields start at the canonical vacancy. Localisable display fields
 * start at the selected language post and fall back only when they are empty.
 * A string "0" is a real value and is not treated as missing.
 *
 * @return array{value:mixed,post_id:int}
 */
function sees_job_resolved_field( int $post_id, string $field, $fallback = '', bool $shared = false ): array {
	$canonical = sees_job_canonical_id( $post_id );
	$ids       = sees_job_translation_ids( $post_id );
	$order     = $shared ? array_merge( array( $canonical, $post_id ), $ids ) : array_merge( array( $post_id, $canonical ), $ids );
	$order     = array_values( array_unique( array_filter( array_map( 'absint', $order ) ) ) );

	foreach ( $order as $source_id ) {
		if ( ! metadata_exists( 'post', $source_id, $field ) ) {
			continue;
		}
		$value = get_post_meta( $source_id, $field, true );
		if ( null !== $value && '' !== $value ) {
			return array( 'value' => $value, 'post_id' => $source_id );
		}
	}

	return array( 'value' => $fallback, 'post_id' => $post_id );
}

/**
 * Convenience wrapper around sees_job_resolved_field().
 */
function sees_job_field( int $post_id, string $field, $fallback = '', bool $shared = false ) {
	return sees_job_resolved_field( $post_id, $field, $fallback, $shared )['value'];
}

/**
 * Whether a vacancy is administratively open and has not passed its closing date.
 */
function sees_job_is_open( int $post_id ): bool {
	$status = (string) sees_job_field( $post_id, 'job_status', 'open', true );
	if ( 'closed' === $status ) {
		return false;
	}

	$closing = preg_replace( '/[^0-9]/', '', (string) sees_job_field( $post_id, 'closing_date', '', true ) );
	return ! $closing || $closing >= current_time( 'Ymd' );
}

/**
 * Published, open logical vacancies for the current language.
 *
 * Polylang normally restricts front-end queries to the current language. Jobs
 * are different: a vacancy may deliberately be authored only once. Query all
 * languages, group linked translations, prefer the current-language post and
 * fall back to any published member when that translation does not exist.
 *
 * @return WP_Post[]
 */
function sees_get_open_jobs( ?string $language = null ): array {
	$args = array(
		'post_type'      => 'sees_job',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		'no_found_rows'  => true,
		'suppress_filters' => false,
	);
	if ( function_exists( 'pll_current_language' ) ) {
		// An empty Polylang language explicitly requests posts in every language.
		$args['lang'] = '';
	}

	$groups       = array();
	$current_lang = in_array( $language, array( 'en', 'ar' ), true ) ? $language : sees_jobs_language();
	foreach ( get_posts( $args ) as $job ) {
		$key = sees_job_group_key( $job->ID );
		if ( ! isset( $groups[ $key ] ) ) {
			$groups[ $key ] = array();
		}
		$groups[ $key ][] = $job;
	}

	$jobs = array();
	foreach ( $groups as $members ) {
		$selected = $members[0];
		foreach ( $members as $member ) {
			if ( $current_lang === sees_job_post_language( $member->ID ) ) {
				$selected = $member;
				break;
			}
		}
		if ( sees_job_is_open( $selected->ID ) ) {
			$jobs[] = $selected;
		}
	}

	return $jobs;
}

/**
 * Options used by the client-side vacancy filters.
 *
 * @param WP_Post[] $jobs Open jobs.
 * @return array<string,string[]>
 */
function sees_job_filter_options( array $jobs ): array {
	$options = array( 'department' => array(), 'location' => array(), 'employment_type' => array() );
	foreach ( $jobs as $job ) {
		foreach ( array_keys( $options ) as $field ) {
			$value = trim( (string) sees_job_field( $job->ID, 'job_' . $field ) );
			if ( '' !== $value ) {
				$options[ $field ][ $value ] = 'employment_type' === $field ? sees_job_type_label( $value ) : $value;
			}
		}
	}
	foreach ( $options as &$values ) {
		asort( $values, SORT_NATURAL | SORT_FLAG_CASE );
	}
	return $options;
}

/**
 * Validate and return a configured Contact Form 7 shortcode.
 * Arbitrary shortcodes are rejected.
 */
function sees_job_application_shortcode( int $post_id ): string {
	// Applications are on unless an editor switched them off for this vacancy.
	if ( ! sees_job_is_open( $post_id ) || ! sees_job_field( $post_id, 'applications_enabled', true, true ) || ! function_exists( 'wpcf7_contact_form' ) ) {
		return '';
	}
	$shortcode = trim( (string) sees_job_field( $post_id, 'application_form_shortcode', '', true ) );
	if ( preg_match( '/^\[contact-form-7\b[^\]]*\]$/', $shortcode ) && preg_match( '/\bid=["\']?(\d+)/', $shortcode, $match ) && wpcf7_contact_form( (int) $match[1] ) ) {
		return $shortcode;
	}
	// No (valid) custom form: use the standard application form created by the theme.
	return function_exists( 'sees_job_default_form_shortcode' ) ? sees_job_default_form_shortcode( $post_id ) : '';
}

function sees_job_has_application_form( int $post_id ): bool {
	return '' !== sees_job_application_shortcode( $post_id );
}

function sees_job_application_form( int $post_id ): string {
	$shortcode = sees_job_application_shortcode( $post_id );
	return $shortcode ? do_shortcode( $shortcode ) : '';
}

/**
 * Operational fields are one vacancy-level record, not translated content.
 */
function sees_job_shared_meta_keys(): array {
	return array( 'job_status', 'closing_date', 'job_reference', 'job_employment_type', 'applications_enabled', 'application_form_shortcode' );
}

/**
 * Keep shared vacancy fields identical when an editor changes any translation.
 */
function sees_sync_job_shared_meta( $meta_id, int $post_id, string $meta_key, $meta_value ): void {
	static $syncing = false;
	if ( $syncing || 'sees_job' !== get_post_type( $post_id ) || ! in_array( $meta_key, sees_job_shared_meta_keys(), true ) ) {
		return;
	}

	$syncing = true;
	foreach ( sees_job_translation_ids( $post_id ) as $translation_id ) {
		if ( $translation_id === $post_id || 'sees_job' !== get_post_type( $translation_id ) ) {
			continue;
		}
		if ( 'deleted_post_meta' === current_action() ) {
			delete_post_meta( $translation_id, $meta_key );
		} else {
			update_post_meta( $translation_id, $meta_key, $meta_value );
		}
	}
	$syncing = false;
}
add_action( 'added_post_meta', 'sees_sync_job_shared_meta', 10, 4 );
add_action( 'updated_post_meta', 'sees_sync_job_shared_meta', 10, 4 );
add_action( 'deleted_post_meta', 'sees_sync_job_shared_meta', 10, 4 );

/**
 * Jobs admin overview columns.
 */
add_filter( 'manage_sees_job_posts_columns', function ( array $columns ): array {
	$columns['job_department'] = __( 'Department', 'sees' );
	$columns['job_location']   = __( 'Location', 'sees' );
	$columns['job_state']      = __( 'Vacancy status', 'sees' );
	return $columns;
} );

add_action( 'manage_sees_job_posts_custom_column', function ( string $column, int $post_id ): void {
	if ( 'job_department' === $column ) {
		echo esc_html( (string) sees_field( 'job_department', '—', $post_id ) );
	} elseif ( 'job_location' === $column ) {
		echo esc_html( (string) sees_field( 'job_location', '—', $post_id ) );
	} elseif ( 'job_state' === $column ) {
		echo esc_html( sees_job_is_open( $post_id ) ? __( 'Open', 'sees' ) : __( 'Closed', 'sees' ) );
	}
}, 10, 2 );

/**
 * Warn editors when applications are enabled without a usable CF7 form.
 */
add_action( 'admin_notices', function (): void {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'sees_job' !== $screen->post_type || 'post' !== $screen->base ) {
		return;
	}
	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $post_id && sees_job_field( $post_id, 'applications_enabled', true, true ) && sees_job_is_open( $post_id ) && ! sees_job_has_application_form( $post_id ) ) {
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'Applications are enabled, but no application form is available. Run Appearance → SEES setup to recreate the standard form.', 'sees' ) . '</p></div>';
	}
} );
