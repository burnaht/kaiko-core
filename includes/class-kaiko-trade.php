<?php
/**
 * Trade Account Management Module.
 *
 * Manages the full trade customer lifecycle:
 * - Custom roles (kaiko_pending, kaiko_trade)
 * - Registration with Business Name field
 * - Admin approval workflow with one-click approve
 * - Price hiding for non-trade users
 * - Purchase restrictions
 *
 * Safe to load even if WooCommerce is deactivated — init() checks
 * for WC availability before registering any hooks.
 *
 * @package KaikoCore
 */

defined( 'ABSPATH' ) || exit;

class Kaiko_Trade {

	/**
	 * Register hooks.
	 */
	public function init(): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		// Ensure custom roles exist (idempotent, version-gated)
		$this->maybe_create_roles();

		// ── Registration ──
		add_action( 'woocommerce_register_form', [ $this, 'render_business_name_field' ] );
		add_action( 'woocommerce_register_post', [ $this, 'validate_business_name' ], 10, 3 );
		add_action( 'woocommerce_created_customer', [ $this, 'save_business_name' ], 10, 3 );
		add_action( 'woocommerce_created_customer', [ $this, 'assign_pending_role' ], 20, 1 );
		add_action( 'woocommerce_created_customer', [ $this, 'notify_admin_new_registration' ], 30, 1 );

		// ── Shop access: redirect guests away from WC pages ──
		add_action( 'template_redirect', [ $this, 'redirect_guests_from_shop' ] );

		// ── Checkout/cart access: bounce pending users back to my-account ──
		add_action( 'template_redirect', [ $this, 'redirect_pending_from_checkout' ] );

		// ── Price display ──
		add_filter( 'woocommerce_get_price_html', [ $this, 'filter_price_html' ], 9999, 2 );

		// ── Purchase restriction ──
		add_filter( 'woocommerce_is_purchasable', [ $this, 'restrict_purchase' ], 9999, 2 );
		add_filter( 'woocommerce_loop_add_to_cart_link', [ $this, 'filter_loop_add_to_cart' ], 9999, 2 );
		add_action( 'woocommerce_single_product_summary', [ $this, 'maybe_replace_single_add_to_cart' ], 1 );

		// ── Admin: Users table ──
		add_filter( 'manage_users_columns', [ $this, 'add_user_columns' ] );
		add_filter( 'manage_users_custom_column', [ $this, 'render_user_column' ], 10, 3 );
		add_filter( 'user_row_actions', [ $this, 'add_approve_action' ], 10, 2 );

		// ── Admin: Approval workflow ──
		add_action( 'admin_init', [ $this, 'handle_approve_action' ] );
		add_action( 'admin_notices', [ $this, 'approval_admin_notice' ] );

		// ── Admin: Pending count bubble ──
		add_action( 'admin_menu', [ $this, 'add_pending_count_bubble' ] );

		// ── Admin: Profile fields ──
		add_action( 'show_user_profile', [ $this, 'render_profile_fields' ] );
		add_action( 'edit_user_profile', [ $this, 'render_profile_fields' ] );
	}

	// ─────────────────────────────────────────────
	//  Roles
	// ─────────────────────────────────────────────

	/**
	 * Create custom trade roles if they don't exist or plugin version changed.
	 */
	private function maybe_create_roles(): void {
		$db_version = get_option( 'kaiko_trade_roles_version', '' );
		if ( $db_version === KAIKO_CORE_VERSION ) {
			return;
		}

		$customer = get_role( 'customer' );
		$caps     = $customer ? $customer->capabilities : [ 'read' => true ];

		remove_role( 'kaiko_pending' );
		remove_role( 'kaiko_trade' );

		add_role( 'kaiko_pending', __( 'KAIKO Pending Trade', 'kaiko-core' ), $caps );
		add_role( 'kaiko_trade', __( 'KAIKO Trade', 'kaiko-core' ), $caps );

		update_option( 'kaiko_trade_roles_version', KAIKO_CORE_VERSION );
	}

	/**
	 * Remove custom roles — called on plugin deactivation.
	 */
	public static function remove_roles(): void {
		remove_role( 'kaiko_pending' );
		remove_role( 'kaiko_trade' );
		delete_option( 'kaiko_trade_roles_version' );
	}

	// ─────────────────────────────────────────────
	//  Registration
	// ─────────────────────────────────────────────

	/**
	 * Render Business Name field on WooCommerce register form.
	 */
	public function render_business_name_field(): void {
		?>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="kaiko_business_name">
				<?php esc_html_e( 'Business Name', 'kaiko-core' ); ?>
				<span class="required" aria-hidden="true">*</span>
			</label>
			<input type="text"
			       class="woocommerce-Input woocommerce-Input--text input-text"
			       name="kaiko_business_name"
			       id="kaiko_business_name"
			       autocomplete="organization"
			       value="<?php echo esc_attr( $_POST['kaiko_business_name'] ?? '' ); ?>"
			       required />
		</p>
		<?php
	}

	/**
	 * Validate Business Name on registration.
	 *
	 * @param string    $username Username.
	 * @param string    $email    Email address.
	 * @param \WP_Error $errors   Validation errors (passed by reference).
	 */
	public function validate_business_name( string $username, string $email, \WP_Error $errors ): void {
		if ( empty( $_POST['kaiko_business_name'] ) || '' === trim( $_POST['kaiko_business_name'] ) ) {
			$errors->add(
				'kaiko_business_name_error',
				__( '<strong>Error</strong>: Please enter your business name.', 'kaiko-core' )
			);
		}
	}

	/**
	 * Save Business Name as user meta after registration.
	 *
	 * @param int    $customer_id       New customer ID.
	 * @param array  $new_customer_data Customer data.
	 * @param string $password_generated Generated password.
	 */
	public function save_business_name( int $customer_id, array $new_customer_data, string $password_generated ): void {
		if ( ! empty( $_POST['kaiko_business_name'] ) ) {
			update_user_meta(
				$customer_id,
				'kaiko_business_name',
				sanitize_text_field( wp_unslash( $_POST['kaiko_business_name'] ) )
			);
		}
	}

	/**
	 * Assign kaiko_pending role to new registrations.
	 *
	 * Priority 20 — runs after WooCommerce assigns the default customer role.
	 *
	 * @param int $customer_id New customer ID.
	 */
	public function assign_pending_role( int $customer_id ): void {
		$user = get_userdata( $customer_id );
		if ( $user ) {
			$user->set_role( 'kaiko_pending' );
		}
	}

	/**
	 * Notify site admin of a new trade registration.
	 *
	 * @param int $customer_id New customer ID.
	 */
	public function notify_admin_new_registration( int $customer_id ): void {
		$user = get_userdata( $customer_id );
		if ( ! $user ) {
			return;
		}

		$business_name = get_user_meta( $customer_id, 'kaiko_business_name', true );
		$admin_email   = get_option( 'admin_email' );
		$site_name     = get_bloginfo( 'name' );

		$subject = sprintf(
			/* translators: %s: site name */
			__( '[%s] New Trade Account Registration', 'kaiko-core' ),
			$site_name
		);

		$message = sprintf(
			"A new trade account registration is awaiting approval.\n\n" .
			"Business Name: %s\n" .
			"Username: %s\n" .
			"Email: %s\n" .
			"Date: %s\n\n" .
			"Review and approve: %s",
			$business_name,
			$user->user_login,
			$user->user_email,
			current_time( 'Y-m-d H:i' ),
			admin_url( 'users.php?role=kaiko_pending' )
		);

		wp_mail( $admin_email, $subject, $message );
	}

	// ─────────────────────────────────────────────
	//  Shop Access
	// ─────────────────────────────────────────────

	/**
	 * Redirect non-logged-in users from WooCommerce pages to /products/.
	 *
	 * Covers the shop page, product category/tag archives, and single
	 * product pages. Logged-in users (any role) can access the shop.
	 */
	public function redirect_guests_from_shop(): void {
		if ( is_user_logged_in() ) {
			return;
		}

		if ( ! function_exists( 'is_woocommerce' ) ) {
			return;
		}

		if ( is_woocommerce() ) {
			wp_safe_redirect( home_url( '/products/' ) );
			exit;
		}
	}

	/**
	 * Belt-and-braces guard: redirect kaiko_pending users away from
	 * the cart and checkout pages back to their account dashboard.
	 *
	 * The is_purchasable filter already prevents add-to-cart, but if a
	 * pending user lands on /checkout/ via a stale link, kick them back.
	 */
	public function redirect_pending_from_checkout(): void {
		if ( ! is_user_logged_in() ) {
			return;
		}
		if ( ! function_exists( 'is_checkout' ) ) {
			return;
		}
		if ( ! is_checkout() && ! is_cart() ) {
			return;
		}

		$user = wp_get_current_user();
		if ( ! in_array( 'kaiko_pending', (array) $user->roles, true ) ) {
			return;
		}
		if ( current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		wc_add_notice(
			__( 'Your trade application is under review. You will be able to order as soon as you’re approved.', 'kaiko-core' ),
			'notice'
		);
		wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
		exit;
	}

	// ─────────────────────────────────────────────
	//  Price Display
	// ─────────────────────────────────────────────

	/**
	 * Filter product price HTML based on user's trade status.
	 *
	 * @param string      $price_html Price HTML.
	 * @param \WC_Product $product    Product object.
	 * @return string
	 */
	public function filter_price_html( string $price_html, \WC_Product $product ): string {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $price_html;
		}

		if ( ! is_user_logged_in() ) {
			return '<span class="kaiko-trade-price-notice">'
				. esc_html__( 'Login for trade pricing', 'kaiko-core' )
				. '</span>';
		}

		$user = wp_get_current_user();

		if ( in_array( 'kaiko_pending', (array) $user->roles, true ) ) {
			return '<span class="kaiko-trade-price-notice">'
				. esc_html__( 'Pricing available once approved', 'kaiko-core' )
				. '</span>';
		}

		return $price_html;
	}

	// ─────────────────────────────────────────────
	//  Purchase Restriction
	// ─────────────────────────────────────────────

	/**
	 * Restrict purchases to approved trade users and admins.
	 *
	 * @param bool        $purchasable Whether the product is purchasable.
	 * @param \WC_Product $product     Product object.
	 * @return bool
	 */
	public function restrict_purchase( bool $purchasable, \WC_Product $product ): bool {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $purchasable;
		}

		if ( ! is_user_logged_in() ) {
			return false;
		}

		$user = wp_get_current_user();

		if ( in_array( 'kaiko_trade', (array) $user->roles, true ) || current_user_can( 'manage_woocommerce' ) ) {
			return $purchasable;
		}

		return false;
	}

	/**
	 * Replace add-to-cart button on product loops for non-trade users.
	 *
	 * @param string      $html    Button HTML.
	 * @param \WC_Product $product Product object.
	 * @return string
	 */
	public function filter_loop_add_to_cart( string $html, \WC_Product $product ): string {
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			if ( in_array( 'kaiko_trade', (array) $user->roles, true ) || current_user_can( 'manage_woocommerce' ) ) {
				return $html;
			}

			return '<a href="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '" class="button kaiko-trade-btn">'
				. esc_html__( 'Awaiting Approval', 'kaiko-core' ) . '</a>';
		}

		return '<a href="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '" class="button kaiko-trade-btn">'
			. esc_html__( 'Login for Trade Pricing', 'kaiko-core' ) . '</a>';
	}

	/**
	 * Remove default add-to-cart on single product pages for non-trade users.
	 */
	public function maybe_replace_single_add_to_cart(): void {
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			if ( in_array( 'kaiko_trade', (array) $user->roles, true ) || current_user_can( 'manage_woocommerce' ) ) {
				return;
			}
		}

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		add_action( 'woocommerce_single_product_summary', [ $this, 'render_trade_cta' ], 30 );
	}

	/**
	 * Render trade CTA in place of add-to-cart on single product pages.
	 */
	public function render_trade_cta(): void {
		$account_url = wc_get_page_permalink( 'myaccount' );

		if ( ! is_user_logged_in() ) {
			echo '<div class="kaiko-trade-cta">';
			echo '<p>' . esc_html__( 'Trade accounts only. Login or register for wholesale pricing.', 'kaiko-core' ) . '</p>';
			echo '<a href="' . esc_url( $account_url ) . '" class="button alt">'
				. esc_html__( 'Login / Register', 'kaiko-core' ) . '</a>';
			echo '</div>';
			return;
		}

		echo '<div class="kaiko-trade-cta">';
		echo '<p>' . esc_html__( 'Your trade account is pending approval. You will be able to purchase once approved.', 'kaiko-core' ) . '</p>';
		echo '</div>';
	}

	// ─────────────────────────────────────────────
	//  Admin: Users Table
	// ─────────────────────────────────────────────

	/**
	 * Add Business Name and Trade Status columns to the Users table.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function add_user_columns( array $columns ): array {
		$new = [];
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'username' === $key ) {
				$new['kaiko_business_name'] = __( 'Business Name', 'kaiko-core' );
				$new['kaiko_trade_status']  = __( 'Trade Status', 'kaiko-core' );
			}
		}
		return $new;
	}

	/**
	 * Render custom column content in the Users table.
	 *
	 * @param string $output      Column output.
	 * @param string $column_name Column key.
	 * @param int    $user_id     User ID.
	 * @return string
	 */
	public function render_user_column( string $output, string $column_name, int $user_id ): string {
		if ( 'kaiko_business_name' === $column_name ) {
			return esc_html( get_user_meta( $user_id, 'kaiko_business_name', true ) );
		}

		if ( 'kaiko_trade_status' === $column_name ) {
			$user = get_userdata( $user_id );
			if ( in_array( 'kaiko_pending', (array) $user->roles, true ) ) {
				return '<span style="color:#c89b3c;font-weight:600;">' . esc_html__( 'Pending', 'kaiko-core' ) . '</span>';
			}
			if ( in_array( 'kaiko_trade', (array) $user->roles, true ) ) {
				return '<span style="color:#1a5c52;font-weight:600;">' . esc_html__( 'Approved', 'kaiko-core' ) . '</span>';
			}
			return '&mdash;';
		}

		return $output;
	}

	/**
	 * Add "Approve Trade" action link for pending users in the Users table.
	 *
	 * @param array    $actions Existing row actions.
	 * @param \WP_User $user    User object.
	 * @return array
	 */
	public function add_approve_action( array $actions, \WP_User $user ): array {
		if ( ! current_user_can( 'promote_users' ) ) {
			return $actions;
		}

		if ( ! in_array( 'kaiko_pending', (array) $user->roles, true ) ) {
			return $actions;
		}

		$url = wp_nonce_url(
			add_query_arg( [
				'action'  => 'kaiko_approve_trade',
				'user_id' => $user->ID,
			], admin_url( 'users.php' ) ),
			'kaiko_approve_trade_' . $user->ID
		);

		$actions['kaiko_approve'] = sprintf(
			'<a href="%s" style="color:#1a5c52;font-weight:600;">%s</a>',
			esc_url( $url ),
			esc_html__( 'Approve Trade', 'kaiko-core' )
		);

		return $actions;
	}

	// ─────────────────────────────────────────────
	//  Admin: Approval Workflow
	// ─────────────────────────────────────────────

	/**
	 * Handle the one-click trade approval action.
	 */
	public function handle_approve_action(): void {
		if ( ! isset( $_GET['action'] ) || 'kaiko_approve_trade' !== $_GET['action'] ) {
			return;
		}

		$user_id = absint( $_GET['user_id'] ?? 0 );
		if ( ! $user_id ) {
			return;
		}

		if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'kaiko_approve_trade_' . $user_id ) ) {
			wp_die( esc_html__( 'Security check failed.', 'kaiko-core' ) );
		}

		if ( ! current_user_can( 'promote_users' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'kaiko-core' ) );
		}

		$user = get_userdata( $user_id );
		if ( ! $user || ! in_array( 'kaiko_pending', (array) $user->roles, true ) ) {
			wp_die( esc_html__( 'Invalid user or user is not pending.', 'kaiko-core' ) );
		}

		$user->set_role( 'kaiko_trade' );
		$this->send_approval_email( $user );

		wp_safe_redirect( add_query_arg( 'kaiko_approved', $user_id, admin_url( 'users.php' ) ) );
		exit;
	}

	/**
	 * Send approval notification email to the customer.
	 *
	 * @param \WP_User $user Approved user.
	 */
	private function send_approval_email( \WP_User $user ): void {
		$site_name = get_bloginfo( 'name' );
		$login_url = wc_get_page_permalink( 'myaccount' );

		$subject = sprintf(
			/* translators: %s: site name */
			__( '[%s] Your Trade Account Has Been Approved', 'kaiko-core' ),
			$site_name
		);

		$message = sprintf(
			"Hi %s,\n\n" .
			"Great news — your trade account has been approved!\n\n" .
			"You now have access to wholesale pricing and can place orders.\n\n" .
			"Log in here: %s\n\n" .
			"Thanks,\n%s",
			$user->first_name ?: $user->user_login,
			$login_url,
			$site_name
		);

		wp_mail( $user->user_email, $subject, $message );
	}

	/**
	 * Show admin notice after successful approval.
	 */
	public function approval_admin_notice(): void {
		if ( ! isset( $_GET['kaiko_approved'] ) ) {
			return;
		}

		$user = get_userdata( absint( $_GET['kaiko_approved'] ) );
		if ( ! $user ) {
			return;
		}

		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			sprintf(
				/* translators: %s: username */
				esc_html__( 'Trade account approved for %s. Notification email sent.', 'kaiko-core' ),
				'<strong>' . esc_html( $user->user_login ) . '</strong>'
			)
		);
	}

	// ─────────────────────────────────────────────
	//  Admin: Pending Count Bubble
	// ─────────────────────────────────────────────

	/**
	 * Add pending trade count bubble to the Users menu item.
	 */
	public function add_pending_count_bubble(): void {
		$count = $this->get_pending_count();
		if ( $count < 1 ) {
			return;
		}

		global $menu;
		foreach ( $menu as $key => $item ) {
			if ( 'users.php' === ( $item[2] ?? '' ) ) {
				$menu[ $key ][0] .= sprintf(
					' <span class="awaiting-mod update-plugins count-%d"><span class="pending-count">%d</span></span>',
					$count,
					$count
				);
				break;
			}
		}
	}

	/**
	 * Get the number of pending trade users.
	 *
	 * @return int
	 */
	private function get_pending_count(): int {
		$users = get_users( [
			'role'   => 'kaiko_pending',
			'fields' => 'ID',
		] );
		return count( $users );
	}

	// ─────────────────────────────────────────────
	//  Admin: Profile Fields
	// ─────────────────────────────────────────────

	/**
	 * Render KAIKO Trade fields on user profile screens.
	 *
	 * @param \WP_User $user User being viewed.
	 */
	public function render_profile_fields( \WP_User $user ): void {
		$business_name = get_user_meta( $user->ID, 'kaiko_business_name', true );
		$is_pending    = in_array( 'kaiko_pending', (array) $user->roles, true );
		$is_trade      = in_array( 'kaiko_trade', (array) $user->roles, true );

		if ( ! $business_name && ! $is_pending && ! $is_trade ) {
			return;
		}
		?>
		<h2><?php esc_html_e( 'KAIKO Trade Account', 'kaiko-core' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><label><?php esc_html_e( 'Business Name', 'kaiko-core' ); ?></label></th>
				<td><p><?php echo esc_html( $business_name ?: '—' ); ?></p></td>
			</tr>
			<tr>
				<th><label><?php esc_html_e( 'Trade Status', 'kaiko-core' ); ?></label></th>
				<td>
					<?php if ( $is_pending ) : ?>
						<span style="color:#c89b3c;font-weight:600;">
							<?php esc_html_e( 'Pending Approval', 'kaiko-core' ); ?>
						</span>
						<?php if ( current_user_can( 'promote_users' ) ) : ?>
							<?php
							$approve_url = wp_nonce_url(
								add_query_arg( [
									'action'  => 'kaiko_approve_trade',
									'user_id' => $user->ID,
								], admin_url( 'users.php' ) ),
								'kaiko_approve_trade_' . $user->ID
							);
							?>
							<a href="<?php echo esc_url( $approve_url ); ?>"
							   class="button button-primary"
							   style="margin-left:10px;">
								<?php esc_html_e( 'Approve Trade Account', 'kaiko-core' ); ?>
							</a>
						<?php endif; ?>
					<?php elseif ( $is_trade ) : ?>
						<span style="color:#1a5c52;font-weight:600;">
							<?php esc_html_e( 'Approved', 'kaiko-core' ); ?>
						</span>
					<?php endif; ?>
				</td>
			</tr>
		</table>
		<?php
	}
}
