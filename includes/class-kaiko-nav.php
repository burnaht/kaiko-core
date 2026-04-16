<?php
/**
 * Navigation Module.
 *
 * Renders the shared KAIKO navigation bar on all KAIKO template pages.
 * Uses a WordPress nav menu if one is assigned (recommended), otherwise
 * falls back to a hardcoded default so the site always has navigation.
 *
 * To customise the nav:
 * - WP Admin → Appearance → Menus → create/assign to "KAIKO Primary"
 * - Or edit the fallback links in render_fallback_links()
 *
 * @package KaikoCore
 */

defined( 'ABSPATH' ) || exit;

class Kaiko_Nav {

	/** Menu location key. */
	const MENU_LOCATION = 'kaiko-primary';

	/**
	 * Register hooks.
	 */
	public function init(): void {
		// Register the menu location
		add_action( 'after_setup_theme', [ $this, 'register_menu' ] );

		// Render the nav early in the page content via a hook
		// Templates call: do_action('kaiko_before_content')
		add_action( 'kaiko_before_content', [ $this, 'render' ] );
	}

	/**
	 * Register the KAIKO Primary nav menu location.
	 */
	public function register_menu(): void {
		register_nav_menus( [
			self::MENU_LOCATION => __( 'KAIKO Primary Navigation', 'kaiko-core' ),
		] );
	}

	/**
	 * Render the navigation bar.
	 */
	public function render(): void {
		?>
		<nav class="kaiko-nav" role="navigation" aria-label="<?php esc_attr_e( 'Primary navigation', 'kaiko-core' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="kaiko-nav-logo">KAIKO</a>

			<button class="kaiko-hamburger" aria-label="<?php esc_attr_e( 'Menu', 'kaiko-core' ); ?>" aria-expanded="false">
				<span></span><span></span><span></span>
			</button>

			<div class="kaiko-nav-links">
				<?php $this->render_links(); ?>
				<?php $this->render_cart_icon(); ?>
				<a href="<?php echo esc_url( home_url( '/my-account/' ) ); ?>" class="kaiko-nav-cta">
					<?php esc_html_e( 'Trade Login', 'kaiko-core' ); ?>
				</a>
			</div>
		</nav>
		<?php
	}

	/**
	 * Render nav links — from WP menu if assigned, otherwise fallback.
	 */
	private function render_links(): void {
		if ( has_nav_menu( self::MENU_LOCATION ) ) {
			wp_nav_menu( [
				'theme_location' => self::MENU_LOCATION,
				'container'      => false,
				'items_wrap'     => '%3$s',
				'depth'          => 1,
				'fallback_cb'    => [ $this, 'render_fallback_links' ],
			] );
		} else {
			$this->render_fallback_links();
		}
	}

	/**
	 * Hardcoded fallback links — used until a WP menu is assigned.
	 */
	public function render_fallback_links(): void {
		$links = [
			'/products/' => __( 'Products', 'kaiko-core' ),
		];

		// Shop link only visible to logged-in users
		if ( is_user_logged_in() ) {
			$links['/shop/'] = __( 'Shop', 'kaiko-core' );
		}

		$links['/about/']   = __( 'About', 'kaiko-core' );
		$links['/contact/'] = __( 'Contact', 'kaiko-core' );

		foreach ( $links as $path => $label ) {
			printf(
				'<a href="%s">%s</a>',
				esc_url( home_url( $path ) ),
				esc_html( $label )
			);
		}
	}

	/**
	 * Render cart icon — only visible when cart has items.
	 */
	private function render_cart_icon(): void {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return;
		}

		$count = WC()->cart->get_cart_contents_count();
		$class = $count > 0 ? 'has-items' : '';
		?>
		<a href="<?php echo esc_url( wc_get_cart_url() ); ?>"
		   class="kaiko-nav-cart <?php echo esc_attr( $class ); ?>"
		   aria-label="<?php esc_attr_e( 'Shopping cart', 'kaiko-core' ); ?>">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
				<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
			</svg>
			<?php if ( $count > 0 ) : ?>
				<span class="kaiko-cart-count"><?php echo esc_html( $count ); ?></span>
			<?php endif; ?>
		</a>
		<?php
	}
}
