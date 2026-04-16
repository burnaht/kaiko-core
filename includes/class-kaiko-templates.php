<?php
/**
 * Template Registration Module.
 *
 * Registers page templates from the plugin's /templates directory,
 * making them available in the WP Page Attributes dropdown without
 * needing files in the active theme.
 *
 * @package KaikoCore
 */

defined( 'ABSPATH' ) || exit;

class Kaiko_Templates {

	/**
	 * Template slug => display name.
	 *
	 * To add a new page template:
	 * 1. Create the PHP file in /templates
	 * 2. Add the mapping here
	 * 3. Push — done.
	 *
	 * @var array<string, string>
	 */
	private array $templates = [
		'kaiko-homepage.php' => 'KAIKO Homepage',
		'kaiko-about.php'    => 'KAIKO About',
		'kaiko-contact.php'  => 'KAIKO Contact',
		'kaiko-products.php'    => 'KAIKO Products',
		'kaiko-my-account.php'  => 'KAIKO My Account',
	];

	/**
	 * Register hooks.
	 */
	public function init(): void {
		// Inject templates into the page-template dropdown
		add_filter( 'theme_page_templates', [ $this, 'register_templates' ], 20 );

		// Resolve template file path when WordPress loads the page
		add_filter( 'template_include', [ $this, 'resolve_template' ], 99 );

		// Also add body classes for CSS scoping
		add_filter( 'body_class', [ $this, 'add_body_classes' ] );
	}

	/**
	 * Add our templates to the Page Attributes → Template dropdown.
	 *
	 * @param array $templates Existing templates.
	 * @return array
	 */
	public function register_templates( array $templates ): array {
		return array_merge( $templates, $this->templates );
	}

	/**
	 * If the current page uses one of our templates, serve it from the plugin.
	 *
	 * Falls back to the theme's copy if the plugin file doesn't exist,
	 * ensuring backwards compatibility during migration.
	 *
	 * @param string $template Current resolved template path.
	 * @return string
	 */
	public function resolve_template( string $template ): string {
		if ( ! is_singular( 'page' ) ) {
			return $template;
		}

		$slug = get_page_template_slug();

		if ( ! $slug || ! array_key_exists( $slug, $this->templates ) ) {
			return $template;
		}

		$plugin_template = Kaiko_Core::template_path() . $slug;

		if ( file_exists( $plugin_template ) ) {
			return $plugin_template;
		}

		// Fallback: check theme directory (migration period)
		$theme_template = get_stylesheet_directory() . '/' . $slug;
		if ( file_exists( $theme_template ) ) {
			return $theme_template;
		}

		return $template;
	}

	/**
	 * Add kaiko-specific body classes for CSS scoping.
	 *
	 * @param array $classes Existing body classes.
	 * @return array
	 */
	public function add_body_classes( array $classes ): array {
		if ( ! is_singular( 'page' ) ) {
			return $classes;
		}

		$slug = get_page_template_slug();
		if ( $slug && array_key_exists( $slug, $this->templates ) ) {
			$classes[] = 'kaiko-template';
			$classes[] = 'kaiko-' . str_replace( [ 'kaiko-', '.php' ], '', $slug );
		}

		return $classes;
	}

	/**
	 * Get all registered template slugs.
	 *
	 * @return array<string, string>
	 */
	public function get_templates(): array {
		return $this->templates;
	}
}
