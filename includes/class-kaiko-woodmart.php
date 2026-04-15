<?php
/**
 * WoodMart Compatibility Module.
 *
 * Handles all interactions with the WoodMart theme:
 * - Hides WoodMart's header on KAIKO pages (we use our own nav)
 * - Removes page title banners
 * - Breaks content out of WoodMart's container for full-width layouts
 *
 * If WoodMart is ever replaced, this module can be deactivated or removed
 * without affecting the rest of the plugin.
 *
 * @package KaikoCore
 */

defined( 'ABSPATH' ) || exit;

class Kaiko_WoodMart_Compat {

	/**
	 * Register hooks.
	 */
	public function init(): void {
		// Only load if WoodMart is the active parent theme
		if ( ! $this->is_woodmart_active() ) {
			return;
		}

		// Inline critical CSS for WoodMart overrides (must be in <head>)
		add_action( 'wp_head', [ $this, 'output_override_css' ], 99 );

		// Remove WoodMart page title on KAIKO pages
		add_action( 'wp', [ $this, 'remove_page_title' ] );
	}

	/**
	 * Check if WoodMart is the active theme.
	 */
	private function is_woodmart_active(): bool {
		$theme = wp_get_theme();
		$parent = $theme->parent();
		$name = $parent ? $parent->get( 'Name' ) : $theme->get( 'Name' );
		return stripos( $name, 'woodmart' ) !== false;
	}

	/**
	 * Output CSS overrides for WoodMart on KAIKO template pages.
	 */
	public function output_override_css(): void {
		if ( ! Kaiko_Core::is_kaiko_page() ) {
			return;
		}
		?>
		<style id="kaiko-woodmart-overrides">
			/* Hide WoodMart's built-in header — KAIKO uses its own nav */
			body.kaiko-template .whb-header,
			body.kaiko-template .whb-header-clone { display: none !important; }

			/* Hide page title banner */
			body.kaiko-template .page-title,
			body.kaiko-template .wd-page-title,
			body.kaiko-template .page-title-default { display: none !important; }

			/* Full-width: break out of WoodMart container */
			body.kaiko-template .main-page-wrapper { padding-top: 0 !important; }
			body.kaiko-template .site-content { padding: 0 !important; max-width: 100% !important; width: 100% !important; }
			body.kaiko-template .site-content > .container,
			body.kaiko-template .main-page-wrapper .container,
			body.kaiko-template .content-area,
			body.kaiko-template .site-main { max-width: 100% !important; width: 100% !important; padding: 0 !important; margin: 0 !important; box-sizing: border-box !important; }
			body.kaiko-template .entry-content { max-width: 100% !important; width: 100% !important; padding: 0 !important; margin: 0 !important; }
			body.kaiko-template .website-wrapper { overflow-x: hidden; }
		</style>
		<?php
	}

	/**
	 * Remove WoodMart page title action on KAIKO pages.
	 *
	 * This is a belt-and-braces approach alongside CSS.
	 * Using priority 1 on `wp` so it runs after WoodMart registers its hooks.
	 */
	public function remove_page_title(): void {
		if ( ! Kaiko_Core::is_kaiko_page() ) {
			return;
		}

		// WoodMart registers this at various priorities — try common ones
		$priorities = [ 10, 15, 20, 25, 30 ];
		foreach ( $priorities as $p ) {
			remove_action( 'woodmart_before_main_content', 'woodmart_page_title', $p );
		}
	}
}
