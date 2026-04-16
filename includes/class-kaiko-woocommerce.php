<?php
/**
 * WooCommerce Integration Module.
 *
 * Handles all WooCommerce-specific functionality:
 * - Cart fragment updates for the nav cart icon
 * - Product filter behaviour on the Products page
 * - Branded email templates (header, footer, styles)
 * - Email sender name and address
 *
 * Safe to load even if WooCommerce is deactivated — all methods
 * check for WC availability before executing.
 *
 * @package KaikoCore
 */

defined( 'ABSPATH' ) || exit;

class Kaiko_WooCommerce {

	/**
	 * Email templates we override from kaiko-core's templates/emails/ directory.
	 *
	 * @var string[]
	 */
	private const EMAIL_TEMPLATES = [
		'emails/email-header.php',
		'emails/email-footer.php',
		'emails/email-styles.php',
	];

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

		// Enforce "ex. VAT" price suffix (prevents theme overrides)
		add_filter( 'woocommerce_get_price_suffix', [ $this, 'enforce_price_suffix' ], 99, 2 );

		// Branded email templates — redirect WC to load from kaiko-core
		add_filter( 'woocommerce_locate_template', [ $this, 'locate_email_template' ], 10, 2 );

		// Email sender identity
		add_filter( 'woocommerce_email_from_name', [ $this, 'email_from_name' ] );
		add_filter( 'woocommerce_email_from_address', [ $this, 'email_from_address' ] );
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

	/**
	 * Enforce "ex. VAT" price suffix.
	 *
	 * Runs at priority 99 to override any theme (e.g. WoodMart) that
	 * may filter or clear the price suffix.
	 *
	 * @param string      $suffix  Current suffix HTML.
	 * @param \WC_Product $product Product instance.
	 * @return string
	 */
	public function enforce_price_suffix( string $suffix, $product ): string {
		$configured = get_option( 'woocommerce_price_display_suffix', '' );
		if ( '' !== $configured && '' === trim( strip_tags( $suffix ) ) ) {
			return ' <small class="woocommerce-price-suffix">' . wp_kses_post( $configured ) . '</small>';
		}
		return $suffix;
	}

	/* =================================================================
	   Email Branding
	   ================================================================= */

	/**
	 * Redirect WooCommerce to load email templates from kaiko-core.
	 *
	 * Only overrides the header, footer, and styles templates — all other
	 * email templates (e.g. customer-processing-order.php) continue to
	 * use WooCommerce defaults, which inherit our header/footer/styles.
	 *
	 * @param string $template      Full path to the located template.
	 * @param string $template_name Template name (e.g. "emails/email-header.php").
	 * @return string
	 */
	public function locate_email_template( string $template, string $template_name ): string {
		if ( ! in_array( $template_name, self::EMAIL_TEMPLATES, true ) ) {
			return $template;
		}

		$plugin_template = KAIKO_CORE_PATH . 'templates/' . $template_name;

		if ( file_exists( $plugin_template ) ) {
			return $plugin_template;
		}

		return $template;
	}

	/**
	 * Set the "From" name on WooCommerce emails.
	 *
	 * @return string
	 */
	public function email_from_name(): string {
		return 'KAIKO';
	}

	/**
	 * Set the "From" address on WooCommerce emails.
	 *
	 * @return string
	 */
	public function email_from_address(): string {
		return 'orders@kaikoproducts.com';
	}
}
