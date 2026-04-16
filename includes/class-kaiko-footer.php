<?php
/**
 * Footer Module.
 *
 * Renders the shared KAIKO footer on all KAIKO template pages.
 * Mirrors the nav header's teal/cream aesthetic and provides
 * site links, contact info, social links, and copyright notice.
 *
 * Templates call: do_action('kaiko_after_content')
 *
 * @package KaikoCore
 */

defined( 'ABSPATH' ) || exit;

class Kaiko_Footer {

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action( 'kaiko_after_content', [ $this, 'render' ] );
	}

	/**
	 * Render the footer.
	 */
	public function render(): void {
		$year = gmdate( 'Y' );

		// Social links from options (Settings > KAIKO Social).
		$social_links = [
			'instagram' => [
				'url'   => get_option( 'kaiko_social_instagram', '' ),
				'label' => __( 'Instagram', 'kaiko-core' ),
				'icon'  => '<svg viewBox="0 0 24 24" width="20" height="20"><rect x="2" y="2" width="20" height="20" rx="4.5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3.5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg>',
			],
			'facebook' => [
				'url'   => get_option( 'kaiko_social_facebook', '' ),
				'label' => __( 'Facebook', 'kaiko-core' ),
				'icon'  => '<svg viewBox="0 0 24 24" width="20" height="20"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" fill="currentColor"/></svg>',
			],
			'tiktok' => [
				'url'   => get_option( 'kaiko_social_tiktok', '' ),
				'label' => __( 'TikTok', 'kaiko-core' ),
				'icon'  => '<svg viewBox="0 0 24 24" width="20" height="20"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1 0-5.78c.27 0 .54.04.79.1v-3.5a6.37 6.37 0 0 0-.79-.05A6.34 6.34 0 0 0 3.15 15.2a6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.34-6.34V8.73a8.19 8.19 0 0 0 4.76 1.52v-3.4a4.85 4.85 0 0 1-1-.16z" fill="currentColor"/></svg>',
			],
		];

		$active_links = array_filter( $social_links, fn( $link ) => ! empty( $link['url'] ) );
		?>
		<footer class="kaiko-footer" role="contentinfo">
			<div class="kaiko-footer-inner">

				<!-- Brand Column -->
				<div class="kaiko-footer-brand">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="kaiko-footer-logo">KAIKO</a>
					<p class="kaiko-footer-tagline">Premium reptile supplies, designed and manufactured in the UK. Trusted by keepers and trade partners nationwide.</p>
					<?php if ( $active_links ) : ?>
					<div class="kaiko-footer-social">
						<?php foreach ( $active_links as $link ) : ?>
						<a href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $link['label'] ); ?>" class="kaiko-footer-social-link">
							<?php echo $link['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG markup ?>
						</a>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
				</div>

				<!-- Navigation Column -->
				<div class="kaiko-footer-col">
					<h4 class="kaiko-footer-heading">Navigation</h4>
					<ul class="kaiko-footer-links">
						<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'kaiko-core' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/products/' ) ); ?>"><?php esc_html_e( 'Products', 'kaiko-core' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About', 'kaiko-core' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact', 'kaiko-core' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/my-account/' ) ); ?>"><?php esc_html_e( 'Trade Login', 'kaiko-core' ); ?></a></li>
					</ul>
				</div>

				<!-- Contact Column -->
				<div class="kaiko-footer-col">
					<h4 class="kaiko-footer-heading">Contact</h4>
					<ul class="kaiko-footer-links">
						<li>
							<a href="mailto:hello@kaikoproducts.com">hello@kaikoproducts.com</a>
						</li>
						<li>
							<a href="mailto:trade@kaikoproducts.com">trade@kaikoproducts.com</a>
						</li>
						<li class="kaiko-footer-location">United Kingdom</li>
					</ul>
				</div>

			</div>

			<!-- Copyright Bar -->
			<div class="kaiko-footer-bar">
				<p>
					&copy; <?php echo esc_html( $year ); ?> KAIKO Products. All rights reserved.
					<button type="button" class="kaiko-footer-cookie-link" onclick="if(window.kaikoOpenConsentModal)window.kaikoOpenConsentModal();"><?php esc_html_e( 'Cookie Settings', 'kaiko-core' ); ?></button>
				</p>
			</div>
		</footer>
		<?php
	}
}
