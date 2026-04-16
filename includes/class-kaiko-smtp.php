<?php
/**
 * SMTP Module.
 *
 * Configures WordPress to send all emails via SMTP and provides
 * an admin settings page under Settings → KAIKO SMTP.
 *
 * @package KaikoCore
 */

defined( 'ABSPATH' ) || exit;

class Kaiko_SMTP {

	/** Option keys. */
	const OPTION_HOST       = 'kaiko_smtp_host';
	const OPTION_PORT       = 'kaiko_smtp_port';
	const OPTION_ENCRYPTION = 'kaiko_smtp_encryption';
	const OPTION_AUTH       = 'kaiko_smtp_auth';
	const OPTION_USERNAME   = 'kaiko_smtp_username';
	const OPTION_PASSWORD   = 'kaiko_smtp_password';
	const OPTION_FROM_NAME  = 'kaiko_smtp_from_name';
	const OPTION_FROM_EMAIL = 'kaiko_smtp_from_email';

	/** Settings page slug. */
	const PAGE_SLUG = 'kaiko-smtp';

	/**
	 * Register hooks.
	 */
	public function init(): void {
		// SMTP configuration.
		add_action( 'phpmailer_init', [ $this, 'configure_phpmailer' ], 10, 1 );

		// Log mail failures.
		add_action( 'wp_mail_failed', [ $this, 'log_mail_error' ], 10, 1 );

		// WooCommerce from overrides.
		add_filter( 'woocommerce_email_from_name',    [ $this, 'from_name' ], 10, 1 );
		add_filter( 'woocommerce_email_from_address',  [ $this, 'from_address' ], 10, 1 );

		// Admin settings page.
		add_action( 'admin_menu',    [ $this, 'add_settings_page' ] );
		add_action( 'admin_init',    [ $this, 'register_settings' ] );

		// Handle test email AJAX.
		add_action( 'wp_ajax_kaiko_smtp_test', [ $this, 'send_test_email' ] );
	}

	/**
	 * Seed default options on plugin activation.
	 *
	 * Called from Kaiko_Core::activate(). Uses add_option so existing
	 * values are never overwritten.
	 */
	public static function on_activate(): void {
		add_option( self::OPTION_HOST,       'smtp.kaikoproducts.com' );
		add_option( self::OPTION_PORT,       '465' );
		add_option( self::OPTION_ENCRYPTION, 'ssl' );
		add_option( self::OPTION_AUTH,       '1' );
		add_option( self::OPTION_USERNAME,   'orders@kaikoproducts.com' );
		add_option( self::OPTION_PASSWORD,   'Jt7a503a2' );
		add_option( self::OPTION_FROM_NAME,  'KAIKO' );
		add_option( self::OPTION_FROM_EMAIL, 'orders@kaikoproducts.com' );
	}

	/**
	 * Configure PHPMailer to use SMTP.
	 *
	 * @param PHPMailer\PHPMailer\PHPMailer $phpmailer
	 */
	public function configure_phpmailer( $phpmailer ): void {
		$phpmailer->isSMTP();
		$phpmailer->Host       = get_option( self::OPTION_HOST, 'smtp.kaikoproducts.com' );
		$phpmailer->Port       = (int) get_option( self::OPTION_PORT, 465 );
		$phpmailer->SMTPSecure = get_option( self::OPTION_ENCRYPTION, 'ssl' );
		$phpmailer->SMTPAuth   = (bool) get_option( self::OPTION_AUTH, true );
		$phpmailer->Username   = get_option( self::OPTION_USERNAME, 'orders@kaikoproducts.com' );
		$phpmailer->Password   = get_option( self::OPTION_PASSWORD, '' );
		$phpmailer->From       = get_option( self::OPTION_FROM_EMAIL, 'orders@kaikoproducts.com' );
		$phpmailer->FromName   = get_option( self::OPTION_FROM_NAME, 'KAIKO' );
	}

	/**
	 * Log wp_mail failures to the PHP error log.
	 *
	 * @param WP_Error $wp_error
	 */
	public function log_mail_error( $wp_error ): void {
		error_log( 'KAIKO SMTP mail failed: ' . $wp_error->get_error_message() );
	}

	/**
	 * WooCommerce from name.
	 */
	public function from_name(): string {
		return get_option( self::OPTION_FROM_NAME, 'KAIKO' );
	}

	/**
	 * WooCommerce from address.
	 */
	public function from_address(): string {
		return get_option( self::OPTION_FROM_EMAIL, 'orders@kaikoproducts.com' );
	}

	// ------------------------------------------------------------------
	// Admin settings page
	// ------------------------------------------------------------------

	/**
	 * Add the settings page.
	 */
	public function add_settings_page(): void {
		add_options_page(
			'KAIKO SMTP',
			'KAIKO SMTP',
			'manage_options',
			self::PAGE_SLUG,
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Register settings and fields.
	 */
	public function register_settings(): void {
		$fields = [
			self::OPTION_HOST       => 'SMTP Host',
			self::OPTION_PORT       => 'Port',
			self::OPTION_ENCRYPTION => 'Encryption',
			self::OPTION_USERNAME   => 'Username',
			self::OPTION_PASSWORD   => 'Password',
			self::OPTION_FROM_NAME  => 'From Name',
			self::OPTION_FROM_EMAIL => 'From Address',
		];

		add_settings_section(
			'kaiko_smtp_section',
			'SMTP Configuration',
			'__return_null',
			self::PAGE_SLUG
		);

		foreach ( $fields as $option => $label ) {
			register_setting( 'kaiko_smtp_settings', $option, [
				'sanitize_callback' => 'sanitize_text_field',
			] );

			add_settings_field(
				$option,
				$label,
				[ $this, 'render_field' ],
				self::PAGE_SLUG,
				'kaiko_smtp_section',
				[ 'option' => $option, 'label' => $label ]
			);
		}
	}

	/**
	 * Render a single settings field.
	 *
	 * @param array $args
	 */
	public function render_field( array $args ): void {
		$option = $args['option'];
		$value  = get_option( $option, '' );

		if ( self::OPTION_ENCRYPTION === $option ) {
			printf(
				'<select name="%1$s" id="%1$s">
					<option value="ssl" %2$s>SSL</option>
					<option value="tls" %3$s>TLS</option>
					<option value="" %4$s>None</option>
				</select>',
				esc_attr( $option ),
				selected( $value, 'ssl', false ),
				selected( $value, 'tls', false ),
				selected( $value, '', false )
			);
			return;
		}

		$type = ( self::OPTION_PASSWORD === $option ) ? 'password' : 'text';
		printf(
			'<input type="%1$s" name="%2$s" id="%2$s" value="%3$s" class="regular-text" />',
			esc_attr( $type ),
			esc_attr( $option ),
			esc_attr( $value )
		);
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1>KAIKO SMTP Settings</h1>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'kaiko_smtp_settings' );
				do_settings_sections( self::PAGE_SLUG );
				submit_button( 'Save Settings' );
				?>
			</form>

			<hr />
			<h2>Send Test Email</h2>
			<p>Sends a test email to <strong><?php echo esc_html( get_option( 'admin_email' ) ); ?></strong>.</p>
			<button type="button" id="kaiko-smtp-test" class="button button-secondary">Send Test Email</button>
			<span id="kaiko-smtp-test-result" style="margin-left:10px;"></span>

			<script>
			(function(){
				var btn    = document.getElementById('kaiko-smtp-test');
				var result = document.getElementById('kaiko-smtp-test-result');
				if ( ! btn ) return;
				btn.addEventListener('click', function(){
					btn.disabled = true;
					result.textContent = 'Sending…';
					var xhr = new XMLHttpRequest();
					xhr.open('POST', ajaxurl);
					xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
					xhr.onload = function(){
						var data = JSON.parse(xhr.responseText);
						result.textContent = data.data ? data.data.message : 'Unknown response';
						result.style.color = data.success ? 'green' : 'red';
						btn.disabled = false;
					};
					xhr.onerror = function(){
						result.textContent = 'Request failed.';
						result.style.color = 'red';
						btn.disabled = false;
					};
					xhr.send('action=kaiko_smtp_test&_wpnonce=<?php echo esc_js( wp_create_nonce( 'kaiko_smtp_test' ) ); ?>');
				});
			})();
			</script>
		</div>
		<?php
	}

	/**
	 * AJAX handler — send a test email to the admin.
	 */
	public function send_test_email(): void {
		check_ajax_referer( 'kaiko_smtp_test' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Permission denied.' ], 403 );
		}

		$to      = get_option( 'admin_email' );
		$subject = 'KAIKO SMTP Test';
		$body    = 'This is a test email sent from the KAIKO SMTP settings page at ' . gmdate( 'Y-m-d H:i:s' ) . ' UTC.';
		$sent    = wp_mail( $to, $subject, $body );

		if ( $sent ) {
			wp_send_json_success( [ 'message' => 'Test email sent successfully.' ] );
		} else {
			wp_send_json_error( [ 'message' => 'Failed to send test email. Check the PHP error log for details.' ] );
		}
	}
}
