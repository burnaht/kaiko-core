<?php
/**
 * Plugin Name: KAIKO Core
 * Plugin URI: https://www.kaikoproducts.com
 * Description: Core frontend plugin for KAIKO Products — manages page templates, navigation, styling, and WooCommerce integration.
 * Version: 1.0.0
 * Author: KAIKO Products
 * Author URI: https://www.kaikoproducts.com
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Text Domain: kaiko-core
 * WC requires at least: 8.0
 * WC tested up to: 10.6
 *
 * @package KaikoCore
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants
define( 'KAIKO_CORE_VERSION', '1.0.0' );
define( 'KAIKO_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'KAIKO_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'KAIKO_CORE_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class — singleton.
 *
 * Uses a modular architecture: each feature is a self-contained class
 * loaded from /includes. Adding or removing features is a single line change.
 */
final class Kaiko_Core {

	/** @var self|null */
	private static ?self $instance = null;

	/** @var array<string, object> Loaded module instances */
	private array $modules = [];

	/**
	 * Singleton accessor.
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Boot the plugin.
	 */
	private function __construct() {
		$this->load_modules();
		$this->register_hooks();
	}

	/**
	 * Load feature modules.
	 *
	 * Each module is a class in /includes that implements an `init()` method.
	 * Modules are loaded in dependency order. To add a feature, create the
	 * class file and add it here.
	 */
	private function load_modules(): void {

		$module_files = [
			'class-kaiko-templates'    => 'Kaiko_Templates',
			'class-kaiko-nav'          => 'Kaiko_Nav',
			'class-kaiko-woodmart'     => 'Kaiko_WoodMart_Compat',
			'class-kaiko-assets'       => 'Kaiko_Assets',
			'class-kaiko-woocommerce'  => 'Kaiko_WooCommerce',
		];

		foreach ( $module_files as $file => $class ) {
			$path = KAIKO_CORE_PATH . "includes/{$file}.php";
			if ( file_exists( $path ) ) {
				require_once $path;
				if ( class_exists( $class ) ) {
					$this->modules[ $class ] = new $class();
					if ( method_exists( $this->modules[ $class ], 'init' ) ) {
						$this->modules[ $class ]->init();
					}
				}
			}
		}
	}

	/**
	 * Plugin-level hooks.
	 */
	private function register_hooks(): void {
		// Declare WooCommerce HPOS compatibility
		add_action( 'before_woocommerce_init', [ $this, 'declare_wc_compat' ] );

		// Activation / deactivation
		register_activation_hook( __FILE__, [ $this, 'activate' ] );
		register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );
	}

	/**
	 * Declare compatibility with WooCommerce HPOS and Blocks.
	 */
	public function declare_wc_compat(): void {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'custom_order_tables',
				__FILE__,
				true
			);
		}
	}

	/**
	 * Plugin activation.
	 */
	public function activate(): void {
		// Flush rewrite rules so template slugs register
		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivation.
	 */
	public function deactivate(): void {
		flush_rewrite_rules();
	}

	/**
	 * Get a loaded module by class name.
	 *
	 * @param string $class Fully qualified class name.
	 * @return object|null
	 */
	public function module( string $class ): ?object {
		return $this->modules[ $class ] ?? null;
	}

	/**
	 * Helper: Check if current page uses a KAIKO template.
	 */
	public static function is_kaiko_page(): bool {
		if ( ! is_singular( 'page' ) ) {
			return false;
		}
		$template = get_page_template_slug();
		return $template && str_starts_with( $template, 'kaiko-' );
	}

	/**
	 * Helper: Get the plugin's template directory path.
	 */
	public static function template_path(): string {
		return KAIKO_CORE_PATH . 'templates/';
	}
}

/**
 * Boot on plugins_loaded so WooCommerce classes are available.
 */
add_action( 'plugins_loaded', function () {
	Kaiko_Core::instance();
}, 10 );
