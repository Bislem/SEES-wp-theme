<?php
/**
 * Template Name: Support & Downloads
 *
 * Document library clients can search and download from, plus support contacts.
 * Search and filters work in place with JavaScript and through the URL without it
 * (?q=…&type=…&doc_lang=…).
 *
 * @package sees
 */

get_header();
the_post();

$sees_page_id = get_the_ID();
$sees_docs    = sees_get_downloads();
$sees_types   = sees_get_download_types();
$sees_contact = sees_contact();
$sees_email   = (string) sees_field( 'support_email', $sees_contact['email'], $sees_page_id );
$sees_phone   = (string) sees_field( 'support_phone', $sees_contact['phone'], $sees_page_id );

// No-JS filtering from the URL.
$sees_q    = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';               // phpcs:ignore WordPress.Security.NonceVerification
$sees_type = isset( $_GET['type'] ) ? sanitize_title( wp_unslash( $_GET['type'] ) ) : '';            // phpcs:ignore WordPress.Security.NonceVerification
$sees_lang = isset( $_GET['doc_lang'] ) ? sanitize_key( wp_unslash( $_GET['doc_lang'] ) ) : '';      // phpcs:ignore WordPress.Security.NonceVerification
$sees_lang = in_array( $sees_lang, array( 'en', 'ar' ), true ) ? $sees_lang : '';
$sees_shown = 0;
$sees_base  = get_permalink();
?>

<main id="main" class="support-page">
	<?php
	get_template_part( 'template-parts/page-header', null, array(
		'subtitle' => sees_field( 'header_subtitle', __( 'Brochures, datasheets, manuals and certificates — find and download the documents you need.', 'sees' ), $sees_page_id ),
	) );
	?>

	<section class="section section--tight-top container downloads" data-downloads>
		<form class="downloads__search" role="search" method="get" action="<?php echo esc_url( $sees_base ); ?>" data-downloads-form>
			<label class="downloads__query">
				<span class="screen-reader-text"><?php esc_html_e( 'Search documents', 'sees' ); ?></span>
				<?php echo sees_icon( 'search', 22, array( 'stroke' => 1.7 ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<input type="search" name="q" value="<?php echo esc_attr( $sees_q ); ?>" placeholder="<?php esc_attr_e( 'Search by document name, product or keyword…', 'sees' ); ?>" autocomplete="off" data-downloads-q>
			</label>
			<label class="downloads__lang">
				<span class="screen-reader-text"><?php esc_html_e( 'Document language', 'sees' ); ?></span>
				<select name="doc_lang" data-downloads-lang>
					<option value=""><?php esc_html_e( 'All languages', 'sees' ); ?></option>
					<option value="en" <?php selected( $sees_lang, 'en' ); ?>><?php esc_html_e( 'English', 'sees' ); ?></option>
					<option value="ar" <?php selected( $sees_lang, 'ar' ); ?>><?php esc_html_e( 'Arabic', 'sees' ); ?></option>
				</select>
			</label>
			<?php if ( $sees_type ) : ?>
				<input type="hidden" name="type" value="<?php echo esc_attr( $sees_type ); ?>" data-downloads-type-input>
			<?php endif; ?>
			<button type="submit" class="btn btn--primary downloads__submit"><?php esc_html_e( 'Search', 'sees' ); ?></button>
		</form>

		<?php if ( $sees_types ) : ?>
			<div class="filters downloads__types" role="group" aria-label="<?php esc_attr_e( 'Document type', 'sees' ); ?>">
				<a class="pill" href="<?php echo esc_url( remove_query_arg( 'type', add_query_arg( array( 'q' => $sees_q ?: null, 'doc_lang' => $sees_lang ?: null ), $sees_base ) ) ); ?>" data-downloads-type=""<?php echo $sees_type ? '' : ' aria-current="true"'; ?>><?php esc_html_e( 'All documents', 'sees' ); ?></a>
				<?php foreach ( $sees_types as $sees_term ) : ?>
					<a class="pill" href="<?php echo esc_url( add_query_arg( array( 'type' => $sees_term->slug, 'q' => $sees_q ?: null, 'doc_lang' => $sees_lang ?: null ), $sees_base ) ); ?>" data-downloads-type="<?php echo esc_attr( $sees_term->slug ); ?>"<?php echo $sees_type === $sees_term->slug ? ' aria-current="true"' : ''; ?>><?php echo esc_html( sees_download_type_name( $sees_term ) ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( $sees_docs ) : ?>
			<p class="downloads__count" aria-live="polite" data-downloads-count
				data-one="<?php esc_attr_e( '1 document', 'sees' ); ?>"
				<?php /* translators: %s: number of documents */ ?>
				data-many="<?php esc_attr_e( '%s documents', 'sees' ); ?>"></p>

			<ul class="downloads__list" data-downloads-list>
				<?php foreach ( $sees_docs as $sees_doc ) : ?>
					<?php
					$sees_match = sees_download_matches( $sees_doc, $sees_q, $sees_type, $sees_lang );
					$sees_shown += $sees_match ? 1 : 0;
					$sees_meta   = array_filter( array(
						implode( ', ', wp_list_pluck( $sees_doc['types'], 'name' ) ),
						sees_download_language_label( $sees_doc['language'] ),
						$sees_doc['size'],
						/* translators: %s: document version */
						$sees_doc['version'] ? sprintf( __( 'Version %s', 'sees' ), $sees_doc['version'] ) : '',
						$sees_doc['date'],
					) );
					?>
					<li class="download"<?php echo $sees_match ? '' : ' hidden'; ?>
						data-download
						data-types="<?php echo esc_attr( implode( ' ', wp_list_pluck( $sees_doc['types'], 'slug' ) ) ); ?>"
						data-lang="<?php echo esc_attr( $sees_doc['language'] ); ?>"
						data-search="<?php echo esc_attr( mb_strtolower( $sees_doc['title'] . ' ' . $sees_doc['description'] . ' ' . implode( ' ', wp_list_pluck( $sees_doc['types'], 'name' ) ) ) ); ?>">
						<span class="download__badge download__badge--<?php echo esc_attr( $sees_doc['ext'] ); ?>" aria-hidden="true"><?php echo esc_html( strtoupper( $sees_doc['ext'] ) ); ?></span>
						<div class="download__body">
							<h2 class="download__title"<?php echo sees_lang_attr( $sees_doc['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $sees_doc['title'] ); ?></h2>
							<?php if ( $sees_doc['description'] ) : ?>
								<p class="download__desc"><?php echo esc_html( $sees_doc['description'] ); ?></p>
							<?php endif; ?>
							<p class="download__meta"><?php echo esc_html( implode( ' · ', $sees_meta ) ); ?></p>
						</div>
						<div class="download__actions">
							<?php if ( $sees_doc['viewable'] ) : ?>
								<a class="btn btn--outline download__view" href="<?php echo esc_url( $sees_doc['url'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View', 'sees' ); ?><span class="screen-reader-text"> <?php echo esc_html( $sees_doc['title'] ); ?> <?php esc_html_e( '(opens in a new tab)', 'sees' ); ?></span></a>
							<?php endif; ?>
							<a class="btn btn--primary download__get" href="<?php echo esc_url( $sees_doc['url'] ); ?>" download>
								<?php echo sees_icon( 'download', 18, array( 'stroke' => 1.7 ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<?php esc_html_e( 'Download', 'sees' ); ?><span class="screen-reader-text"> <?php echo esc_html( $sees_doc['title'] . ' (' . strtoupper( $sees_doc['ext'] ) . ', ' . $sees_doc['size'] . ')' ); ?></span>
							</a>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
			<div class="downloads__empty" data-downloads-empty<?php echo $sees_shown ? ' hidden' : ''; ?>>
				<?php echo sees_icon( 'search', 34 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<p><?php esc_html_e( 'No documents match your search. Try other keywords or another document type.', 'sees' ); ?></p>
			</div>
		<?php else : ?>
			<div class="downloads__empty">
				<?php echo sees_icon( 'download', 34 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<p><?php esc_html_e( 'Documents will be published here soon. Contact us if you need a document now.', 'sees' ); ?></p>
			</div>
		<?php endif; ?>
	</section>

	<section class="section-band">
		<div class="section container support-help">
			<div class="section-head reveal">
				<?php sees_eyebrow( __( 'Need help?', 'sees' ) ); ?>
				<h2 class="h2"><?php esc_html_e( 'Our engineers are here to support you.', 'sees' ); ?></h2>
			</div>
			<div class="support-help__grid">
				<a class="info-card reveal" href="<?php echo esc_url( sees_tel( $sees_phone ) ); ?>">
					<span class="info-card__icon ic ic--navy"><?php echo sees_icon( 'phone', 24, array( 'stroke' => 1.7 ) ); // phpcs:ignore ?></span>
					<span class="info-card__text"><span class="info-card__label"><?php esc_html_e( 'Call support', 'sees' ); ?></span><strong dir="ltr"><?php echo esc_html( $sees_phone ); ?></strong></span>
				</a>
				<a class="info-card reveal" href="<?php echo esc_url( 'mailto:' . $sees_email ); ?>">
					<span class="info-card__icon ic ic--petrol"><?php echo sees_icon( 'mail', 24, array( 'stroke' => 1.7 ) ); // phpcs:ignore ?></span>
					<span class="info-card__text"><span class="info-card__label"><?php esc_html_e( 'Email support', 'sees' ); ?></span><strong><?php echo esc_html( $sees_email ); ?></strong></span>
				</a>
				<a class="info-card reveal" href="<?php echo esc_url( sees_contact_url( 'documents' ) ); ?>">
					<span class="info-card__icon ic ic--teal"><?php echo sees_icon( 'search', 24, array( 'stroke' => 1.7 ) ); // phpcs:ignore ?></span>
					<span class="info-card__text"><span class="info-card__label"><?php esc_html_e( 'Can’t find a document?', 'sees' ); ?></span><strong><?php esc_html_e( 'Request it from our team', 'sees' ); ?></strong></span>
				</a>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
