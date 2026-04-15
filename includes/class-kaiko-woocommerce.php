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
}
