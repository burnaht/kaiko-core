<?php
/**
 * WooCommerce Integration Module.
 *
 * Handles all WooCommerce-specific functionality:
 * - Cart fragment updates for the nav cart icon
 * - Product filter behaviour on the Products page
 * - Future: checkout customisations, subscription hooks, etc.
 *
 * Safe to load even if WooCommerce is deactivated — all methods
 * check for WC availability before executing.
 *
 * @package KaikoCore
 */

defined( 'ABSPATH' ) || exit;

class Kaiko_WooCommerce {

	/**
	 * Register hooks.
	 */
	public function init(): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		// Update cart icon via WC AJAX fragments
		add_filter( 'woocommerce_add_to_cart_fragments', [ $this, 'cart_fragment' ] );

		// Enqueue product page scripts
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_product_scripts' ] );

		// Fire KAIKO hooks on WooCommerce pages (nav + footer)
		// Shop, archives, single products use WC's own content hooks
		add_action( 'woocommerce_before_main_content', [ $this, 'fire_kaiko_before_content' ], 5 );
		add_action( 'woocommerce_after_main_content', [ $this, 'fire_kaiko_after_content' ], 99 );
		// Cart and checkout don't fire woocommerce_before/after_main_content,
		// so we use wp_body_open and wp_footer to inject nav/footer there
		add_action( 'wp_body_open', [ $this, 'maybe_fire_before_content_on_cart_checkout' ], 5 );
		add_action( 'wp_footer', [ $this, 'maybe_fire_after_content_on_cart_checkout' ], 1 );

		// Add kaiko-template body class on WC pages for CSS scoping
		add_filter( 'body_class', [ $this, 'add_wc_body_classes' ] );
	}

	/**
	 * Cart fragment for AJAX cart updates.
	 *
	 * When a product is added to cart, WooCommerce replaces HTML fragments.
	 * This keeps the nav cart icon in sync without a page reload.
	 *
	 * @param array $fragments Existing fragments.
	 * @return array
	 */
	public function cart_fragment( array $fragments ): array {
		if ( ! WC()->cart ) {
			return $fragments;
		}

		$count = WC()->cart->get_cart_contents_count();
		$class = $count > 0 ? 'has-items' : '';

		ob_start();
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
		$fragments['.kaiko-nav-cart'] = ob_get_clean();

		return $fragments;
	}

	/**
	 * Enqueue scripts for the Products page.
	 */
	public function enqueue_product_scripts(): void {
		if ( ! is_page() ) {
			return;
		}

		$template = get_page_template_slug();
		if ( 'kaiko-products.php' === $template ) {
			wp_enqueue_script(
				'kaiko-products',
				KAIKO_CORE_URL . 'assets/js/products.js',
				[],
				KAIKO_CORE_VERSION,
				[ 'in_footer' => true ]
			);
		}
	}

	/**
	 * Fire kaiko_before_content on WooCommerce pages.
	 *
	 * This renders the KAIKO nav bar on shop, product archive,
	 * and single product pages — same as on KAIKO template pages.
	 */
	public function fire_kaiko_before_content(): void {
		do_action( 'kaiko_before_content' );
	}

	/**
	 * Fire kaiko_after_content on WooCommerce pages.
	 */
	public function fire_kaiko_after_content(): void {
		do_action( 'kaiko_after_content' );
	}

	/**
	 * Fire kaiko_before_content on cart/checkout pages.
	 *
	 * Cart and checkout don't trigger woocommerce_before_main_content,
	 * so we hook into wp_body_open and conditionally fire our action.
	 */
	public function maybe_fire_before_content_on_cart_checkout(): void {
		if ( $this->is_cart_or_checkout() ) {
			do_action( 'kaiko_before_content' );
		}
	}

	/**
	 * Fire kaiko_after_content on cart/checkout pages.
	 *
	 * Hooked to wp_footer at priority 1 (before scripts) so the footer
	 * renders in the correct position.
	 */
	public function maybe_fire_after_content_on_cart_checkout(): void {
		if ( $this->is_cart_or_checkout() ) {
			do_action( 'kaiko_after_content' );
		}
	}

	/**
	 * Check if the current page is the WC cart or checkout.
	 */
	private function is_cart_or_checkout(): bool {
		return function_exists( 'is_cart' ) && ( is_cart() || is_checkout() );
	}

	/**
	 * Add kaiko-template body class on WooCommerce pages.
	 *
	 * This enables the same CSS scoping (WoodMart header hiding,
	 * nav padding offset) that KAIKO template pages get.
	 *
	 * @param array $classes Existing body classes.
	 * @return array
	 */
	public function add_wc_body_classes( array $classes ): array {
		$is_wc = function_exists( 'is_woocommerce' ) && is_woocommerce();
		$is_cart_checkout = $this->is_cart_or_checkout();

		if ( $is_wc || $is_cart_checkout ) {
			$classes[] = 'kaiko-template';
		}
		return $classes;
	}
}
