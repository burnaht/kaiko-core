<?php
/**
 * Assets Module.
 *
 * Enqueues shared CSS and JavaScript for KAIKO pages.
 * Per-template styles are loaded inline within each template file;
 * this module handles global assets (nav, fonts, shared utilities).
 *
 * @package KaikoCore
 */

defined( 'ABSPATH' ) || exit;

class Kaiko_Assets {

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_global' ] );
		add_action( 'wp_head', [ $this, 'preload_fonts' ], 5 );
	}

	/**
	 * Enqueue stylesheets and scripts on KAIKO pages and WooCommerce pages.
	 */
	public function enqueue(): void {
		if ( ! Kaiko_Core::is_kaiko_frontend() ) {
			return;
		}

		// Nav styles
		wp_enqueue_style(
			'kaiko-nav',
			KAIKO_CORE_URL . 'assets/css/nav.css',
			[],
			KAIKO_CORE_VERSION
		);

		// Footer styles
		wp_enqueue_style(
			'kaiko-footer',
			KAIKO_CORE_URL . 'assets/css/footer.css',
			[],
			KAIKO_CORE_VERSION
		);

		// Nav JS (hamburger, mobile menu)
		wp_enqueue_script(
			'kaiko-nav',
			KAIKO_CORE_URL . 'assets/js/nav.js',
			[],
			KAIKO_CORE_VERSION,
			[ 'in_footer' => true ]
		);

		// Inter font (check if already loaded by theme to avoid double-load)
		if ( ! wp_style_is( 'google-fonts-inter' ) ) {
			wp_enqueue_style(
				'kaiko-font-inter',
				'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
				[],
				null
			);
		}

		// My Account styles (only on the My Account template)
		if ( is_page() && 'kaiko-my-account.php' === get_page_template_slug() ) {
			wp_enqueue_style(
				'kaiko-my-account',
				KAIKO_CORE_URL . 'assets/css/my-account.css',
				[ 'kaiko-nav' ],
				KAIKO_CORE_VERSION
			);
		}

		// Mobile responsive fixes
		wp_enqueue_style(
			'kaiko-mobile',
			KAIKO_CORE_URL . 'assets/css/mobile.css',
			[ 'kaiko-nav', 'kaiko-footer' ],
			KAIKO_CORE_VERSION
		);

	}

	/**
	 * Enqueue assets that load on ALL frontend pages (not just KAIKO pages).
	 */
	public function enqueue_global(): void {
		// GDPR consent banner — must load on all pages since the
		// banner renders via wp_footer on every frontend page.
		wp_enqueue_style(
			'kaiko-gdpr',
			KAIKO_CORE_URL . 'assets/css/gdpr.css',
			[],
			KAIKO_CORE_VERSION
		);
	}

	/**
	 * Preload critical fonts for performance.
	 */
	public function preload_fonts(): void {
		if ( ! Kaiko_Core::is_kaiko_frontend() ) {
			return;
		}
		?>
		<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<?php
	}
}
