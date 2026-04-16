<?php
/**
 * WooCommerce Store Configuration Module.
 *
 * One-time setup for shipping zones, tax rates, currency, and base
 * location.  Runs on plugin activation via the same transient pattern
 * used by Kaiko_Setup.  Checks existing configuration first to avoid
 * duplicates.
 *
 * @package KaikoCore
 */

defined( 'ABSPATH' ) || exit;

class Kaiko_Store_Config {

	/** @var string Option key to track whether store config has run. */
	private const CONFIG_FLAG = 'kaiko_store_config_done';

	/** @var string Option key to track whether payment config has run. */
	private const PAYMENT_FLAG = 'kaiko_payment_config_done';

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action( 'admin_init', [ $this, 'maybe_configure' ] );
		add_action( 'admin_init', [ $this, 'maybe_configure_payments' ] );
	}

	/**
	 * Schedule configuration on next admin load.
	 *
	 * Called by the plugin activation hook in kaiko-core.php.
	 */
	public static function on_activate(): void {
		set_transient( 'kaiko_run_store_config', true, 60 );
	}

	/**
	 * Run configuration if the activation transient is present
	 * and setup hasn't already completed.
	 */
	public function maybe_configure(): void {
		if ( ! get_transient( 'kaiko_run_store_config' ) ) {
			return;
		}

		delete_transient( 'kaiko_run_store_config' );

		if ( get_option( self::CONFIG_FLAG ) ) {
			return;
		}

		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$this->configure_base_location();
		$this->configure_currency();
		$this->configure_tax_options();
		$this->configure_tax_rates();
		$this->configure_shipping_zones();

		update_option( self::CONFIG_FLAG, true, false );
	}

	// ─── Base Location ──────────────────────────────────────────────

	/**
	 * Set WooCommerce base country to United Kingdom.
	 */
	private function configure_base_location(): void {
		update_option( 'woocommerce_default_country', 'GB' );
	}

	// ─── Currency ───────────────────────────────────────────────────

	/**
	 * Set shop currency to GBP.
	 */
	private function configure_currency(): void {
		update_option( 'woocommerce_currency', 'GBP' );
	}

	// ─── Tax Options ────────────────────────────────────────────────

	/**
	 * Enable taxes and configure display settings.
	 *
	 * - Prices entered exclusive of tax (trade pricing is ex-VAT)
	 * - Display exclusive of tax throughout (shop + cart)
	 * - VAT shown as a separate line item at checkout
	 */
	private function configure_tax_options(): void {
		update_option( 'woocommerce_calc_taxes', 'yes' );
		update_option( 'woocommerce_prices_include_tax', 'no' );
		update_option( 'woocommerce_tax_display_shop', 'excl' );
		update_option( 'woocommerce_tax_display_cart', 'excl' );
		update_option( 'woocommerce_tax_total_display', 'itemized' );
		update_option( 'woocommerce_tax_based_on', 'shipping' );
	}

	// ─── Tax Rates ──────────────────────────────────────────────────

	/**
	 * Add the UK standard 20% VAT rate if it doesn't already exist.
	 */
	private function configure_tax_rates(): void {
		global $wpdb;

		$table = $wpdb->prefix . 'woocommerce_tax_rates';

		// Check if a GB 20% standard rate already exists.
		$exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT tax_rate_id FROM {$table} WHERE tax_rate_country = %s AND tax_rate = %s AND tax_rate_class = %s LIMIT 1",
			'GB',
			'20.0000',
			''
		) );

		if ( $exists ) {
			return;
		}

		$wpdb->insert( $table, [
			'tax_rate_country'  => 'GB',
			'tax_rate_state'    => '',
			'tax_rate'          => '20.0000',
			'tax_rate_name'     => 'VAT',
			'tax_rate_priority' => 1,
			'tax_rate_compound' => 0,
			'tax_rate_shipping' => 1,
			'tax_rate_order'    => 0,
			'tax_rate_class'    => '',
		] );
	}

	// ─── Shipping Zones ─────────────────────────────────────────────

	/**
	 * Create shipping zones:
	 *
	 * 1. United Kingdom — Flat Rate £4.99 + Free Shipping over £50
	 * 2. Everywhere Else — Flat Rate £12.99
	 *
	 * Checks for existing zones by name to avoid duplicates.
	 */
	private function configure_shipping_zones(): void {
		if ( ! class_exists( 'WC_Shipping_Zone' ) ) {
			return;
		}

		$this->create_uk_zone();
		$this->create_rest_of_world_zone();
	}

	/**
	 * Create the United Kingdom shipping zone.
	 */
	private function create_uk_zone(): void {
		if ( $this->zone_exists( 'United Kingdom' ) ) {
			return;
		}

		$zone = new WC_Shipping_Zone();
		$zone->set_zone_name( 'United Kingdom' );
		$zone->set_zone_order( 0 );
		$zone->save();

		// Add GB as the zone location.
		$zone->add_location( 'GB', 'country' );
		$zone->save();

		// Flat Rate — £4.99.
		$flat_rate_id = $zone->add_shipping_method( 'flat_rate' );
		$this->configure_flat_rate( $flat_rate_id, '4.99' );

		// Free Shipping — orders over £50.
		$free_shipping_id = $zone->add_shipping_method( 'free_shipping' );
		$this->configure_free_shipping( $free_shipping_id, '50' );
	}

	/**
	 * Create the Everywhere Else (Rest of World) shipping zone.
	 *
	 * Uses WooCommerce's built-in zone 0 (locations not covered by
	 * other zones) to avoid creating a redundant catch-all zone.
	 */
	private function create_rest_of_world_zone(): void {
		// Zone 0 is WooCommerce's default "Locations not covered by your other zones".
		$zone = WC_Shipping_Zones::get_zone( 0 );

		// Skip if zone 0 already has shipping methods configured.
		if ( count( $zone->get_shipping_methods() ) > 0 ) {
			return;
		}

		// Flat Rate — £12.99.
		$flat_rate_id = $zone->add_shipping_method( 'flat_rate' );
		$this->configure_flat_rate( $flat_rate_id, '12.99' );
	}

	/**
	 * Configure a flat rate shipping method instance.
	 *
	 * @param int    $instance_id The shipping method instance ID.
	 * @param string $cost        The flat rate cost.
	 */
	private function configure_flat_rate( int $instance_id, string $cost ): void {
		update_option( "woocommerce_flat_rate_{$instance_id}_settings", [
			'title'      => 'Flat Rate',
			'tax_status' => 'taxable',
			'cost'       => $cost,
		] );
	}

	/**
	 * Configure a free shipping method instance.
	 *
	 * @param int    $instance_id The shipping method instance ID.
	 * @param string $min_amount  Minimum order amount for free shipping.
	 */
	private function configure_free_shipping( int $instance_id, string $min_amount ): void {
		update_option( "woocommerce_free_shipping_{$instance_id}_settings", [
			'title'      => 'Free Shipping',
			'requires'   => 'min_amount',
			'min_amount' => $min_amount,
		] );
	}

	// ─── Payment Configuration (Phase 2) ────────────────────────

	/**
	 * Run payment gateway configuration if it hasn't completed yet.
	 *
	 * Separate from the initial store config so it runs even if the
	 * original CONFIG_FLAG was already set on the live site.
	 */
	public function maybe_configure_payments(): void {
		if ( get_option( self::PAYMENT_FLAG ) ) {
			return;
		}

		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$this->configure_bacs_payment();
		$this->disable_other_gateways();
		$this->configure_tax_display_suffix();

		update_option( self::PAYMENT_FLAG, true, false );
	}

	/**
	 * Enable and configure BACS (Bank Transfer) payment gateway.
	 */
	private function configure_bacs_payment(): void {
		update_option( 'woocommerce_bacs_settings', [
			'enabled'      => 'yes',
			'title'        => 'Pay by Bank Transfer',
			'description'  => 'Place your order and pay directly from your bank account. Your order will be processed once payment is received.',
			'instructions' => 'Please transfer the total amount to the account below, using your order number as the payment reference. Orders are dispatched once payment is confirmed.',
		] );

		// BACS account details stored as a separate option.
		update_option( 'woocommerce_bacs_accounts', [
			[
				'account_name' => 'KAIKO Products',
				'bank_name'    => '', // <!-- PLACEHOLDER: add bank name -->
				'sort_code'    => '', // <!-- PLACEHOLDER: add sort code -->
				'account_number' => '', // <!-- PLACEHOLDER: add account number -->
				'iban'         => '',
				'bic'          => '',
			],
		] );
	}

	/**
	 * Disable all payment gateways except BACS.
	 *
	 * Targets the built-in gateways that WooCommerce ships with:
	 * COD (cash on delivery), cheque, and PayPal standard.
	 */
	private function disable_other_gateways(): void {
		$gateways_to_disable = [
			'woocommerce_cod_settings',
			'woocommerce_cheque_settings',
			'woocommerce_paypal_settings',
		];

		foreach ( $gateways_to_disable as $option_key ) {
			$settings = get_option( $option_key, [] );
			if ( is_array( $settings ) ) {
				$settings['enabled'] = 'no';
				update_option( $option_key, $settings );
			}
		}
	}

	/**
	 * Set the price display suffix to show "ex. VAT" on shop pages.
	 */
	private function configure_tax_display_suffix(): void {
		update_option( 'woocommerce_price_display_suffix', 'ex. VAT' );
	}

	// ─── Helpers ────────────────────────────────────────────────────

	/**
	 * Check if a shipping zone with the given name already exists.
	 *
	 * @param string $name Zone name to search for.
	 * @return bool
	 */
	private function zone_exists( string $name ): bool {
		if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
			return false;
		}

		$zones = WC_Shipping_Zones::get_zones();
		foreach ( $zones as $zone_data ) {
			if ( $zone_data['zone_name'] === $name ) {
				return true;
			}
		}
		return false;
	}
}
