<?php
/**
 * Site footer.
 *
 * @package sees
 */

$contact = sees_contact();
?>
<footer class="site-footer">
	<div class="container site-footer__inner">
		<div class="site-footer__top">
			<div class="site-footer__brand">
				<a href="<?php echo esc_url( function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' ) ); ?>" class="brand">
					<?php
					echo sees_image( 0, 'logo-dark.png', 'medium', array( // phpcs:ignore WordPress.Security.EscapeOutput
						'class' => 'brand__logo brand__logo--footer',
						'alt'   => __( 'SEES — Smart Electrical Engineering Solutions', 'sees' ),
					) );
					?>
				</a>
				<p><?php esc_html_e( 'Engineering smarter, powering better — integrated electrical solutions from Makkah, Saudi Arabia.', 'sees' ); ?></p>
			</div>

			<div class="site-footer__cols">
				<div class="footer-col">
					<h2 class="footer-col__title"><?php esc_html_e( 'Company', 'sees' ); ?></h2>
					<?php
					wp_nav_menu( array(
						'theme_location' => 'footer_company',
						'container'      => false,
						'menu_class'     => 'footer-col__list',
						'depth'          => 1,
						'fallback_cb'    => 'sees_footer_company_fallback',
					) );
					?>
				</div>
				<div class="footer-col">
					<h2 class="footer-col__title"><?php esc_html_e( 'Solutions', 'sees' ); ?></h2>
					<?php
					wp_nav_menu( array(
						'theme_location' => 'footer_solutions',
						'container'      => false,
						'menu_class'     => 'footer-col__list',
						'depth'          => 1,
						'fallback_cb'    => 'sees_footer_solutions_fallback',
					) );
					?>
				</div>
				<div class="footer-col">
					<h2 class="footer-col__title"><?php esc_html_e( 'Contact', 'sees' ); ?></h2>
					<ul class="footer-col__list">
						<li><a href="<?php echo esc_url( sees_tel( $contact['phone'] ) ); ?>" dir="ltr"><?php echo esc_html( $contact['phone'] ); ?></a></li>
						<li><a href="<?php echo esc_url( 'mailto:' . $contact['email'] ); ?>"><?php echo esc_html( $contact['email'] ); ?></a></li>
						<li><?php echo esc_html( $contact['address'] ); ?></li>
					</ul>
				</div>
			</div>
		</div>

		<div class="site-footer__bottom">
			<span>
				<?php
				/* translators: %s: year */
				printf( esc_html__( '© %s SEES ENG Company. All rights reserved.', 'sees' ), esc_html( wp_date( 'Y' ) ) );
				?>
			</span>
			<span class="site-footer__legal">
				<?php $sees_privacy = sees_privacy_url(); ?>
				<?php if ( $sees_privacy ) : ?>
					<a href="<?php echo esc_url( $sees_privacy ); ?>"><?php esc_html_e( 'Privacy Policy', 'sees' ); ?></a>
				<?php endif; ?>
				<span dir="ltr">www.seesengineering.com</span>
			</span>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
