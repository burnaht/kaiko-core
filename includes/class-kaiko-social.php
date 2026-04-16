<?php
/**
 * Social Links Module.
 *
 * Adds a Settings → KAIKO Social admin page for managing social media URLs.
 * The footer module reads these options to render social icons.
 *
 * Options: kaiko_social_instagram, kaiko_social_facebook, kaiko_social_tiktok
 *
 * @package KaikoCore
 */

defined( 'ABSPATH' ) || exit;

class Kaiko_Social {

	/** @var string Settings page slug. */
	private const PAGE_SLUG = 'kaiko-social';

	/** @var string Option group for Settings API. */
	private const OPTION_GROUP = 'kaiko_social_options';

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	/**
	 * Add settings page under Settings menu.
	 */
	public function add_menu_page(): void {
		add_options_page(
			__( 'KAIKO Social Links', 'kaiko-core' ),
			__( 'KAIKO Social', 'kaiko-core' ),
			'manage_options',
			self::PAGE_SLUG,
			[ $this, 'render_page' ]
		);
	}

	/**
	 * Register settings fields.
	 */
	public function register_settings(): void {
		$fields = [
			'kaiko_social_instagram' => __( 'Instagram URL', 'kaiko-core' ),
			'kaiko_social_facebook'  => __( 'Facebook URL', 'kaiko-core' ),
			'kaiko_social_tiktok'    => __( 'TikTok URL', 'kaiko-core' ),
		];

		add_settings_section(
			'kaiko_social_section',
			__( 'Social Media Links', 'kaiko-core' ),
			function () {
				echo '<p>' . esc_html__( 'Enter the full URL for each social profile. Leave blank to hide the icon in the footer.', 'kaiko-core' ) . '</p>';
			},
			self::PAGE_SLUG
		);

		foreach ( $fields as $option => $label ) {
			register_setting( self::OPTION_GROUP, $option, [
				'type'              => 'string',
				'sanitize_callback' => 'esc_url_raw',
				'default'           => '',
			] );

			add_settings_field(
				$option,
				$label,
				function () use ( $option ) {
					$value = get_option( $option, '' );
					echo '<input type="url" name="' . esc_attr( $option ) . '" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="https://">';
				},
				self::PAGE_SLUG,
				'kaiko_social_section'
			);
		}
	}

	/**
	 * Render the settings page.
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'KAIKO Social Links', 'kaiko-core' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( self::OPTION_GROUP );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
