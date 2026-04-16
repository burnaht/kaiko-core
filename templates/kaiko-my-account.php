<?php
/**
 * Template Name: KAIKO My Account
 * Description: Trade login, registration, and account management page.
 *
 * @package KaikoCore
 */

defined( 'ABSPATH' ) || exit;

get_header(); ?>

<?php do_action( 'kaiko_before_content' ); ?>

<div class="kaiko-myaccount">

<?php if ( ! is_user_logged_in() ) : ?>

	<!-- ── Guest View ── -->
	<section class="k-hero">
		<span class="k-hero-label"><?php esc_html_e( 'Trade Customers', 'kaiko-core' ); ?></span>
		<h1><?php esc_html_e( 'Trade Login & Registration', 'kaiko-core' ); ?></h1>
		<p class="k-hero-sub"><?php esc_html_e( 'Access wholesale pricing, priority service, and our full product range.', 'kaiko-core' ); ?></p>
	</section>

	<section class="k-auth-section">
		<?php do_action( 'woocommerce_before_customer_login_form' ); ?>

		<div class="k-auth-grid">

			<!-- Login -->
			<div class="k-auth-card">
				<h2><?php esc_html_e( 'Login', 'kaiko-core' ); ?></h2>
				<p class="k-auth-desc"><?php esc_html_e( 'Already have a trade account? Sign in below.', 'kaiko-core' ); ?></p>
				<?php woocommerce_login_form( [ 'redirect' => wc_get_page_permalink( 'myaccount' ) ] ); ?>
			</div>

			<!-- Register -->
			<div class="k-auth-card k-auth-register">
				<h2><?php esc_html_e( 'Register', 'kaiko-core' ); ?></h2>
				<p class="k-auth-desc"><?php esc_html_e( 'Apply for a trade account to unlock wholesale pricing.', 'kaiko-core' ); ?></p>

				<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>

					<form method="post" class="woocommerce-form woocommerce-form-register register"
					      <?php do_action( 'woocommerce_register_form_tag' ); ?>>

						<?php do_action( 'woocommerce_register_form_start' ); ?>

						<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="reg_username">
									<?php esc_html_e( 'Username', 'woocommerce' ); ?>
									<span class="required" aria-hidden="true">*</span>
								</label>
								<input type="text"
								       class="woocommerce-Input woocommerce-Input--text input-text"
								       name="username"
								       id="reg_username"
								       autocomplete="username"
								       value="<?php echo esc_attr( $_POST['username'] ?? '' ); ?>" />
							</p>
						<?php endif; ?>

						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="reg_email">
								<?php esc_html_e( 'Email address', 'woocommerce' ); ?>
								<span class="required" aria-hidden="true">*</span>
							</label>
							<input type="email"
							       class="woocommerce-Input woocommerce-Input--text input-text"
							       name="email"
							       id="reg_email"
							       autocomplete="email"
							       value="<?php echo esc_attr( $_POST['email'] ?? '' ); ?>" />
						</p>

						<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="reg_password">
									<?php esc_html_e( 'Password', 'woocommerce' ); ?>
									<span class="required" aria-hidden="true">*</span>
								</label>
								<input type="password"
								       class="woocommerce-Input woocommerce-Input--text input-text"
								       name="password"
								       id="reg_password"
								       autocomplete="new-password" />
							</p>
						<?php endif; ?>

						<?php do_action( 'woocommerce_register_form' ); ?>

						<div class="k-benefits-list">
							<h3><?php esc_html_e( 'Trade account benefits:', 'kaiko-core' ); ?></h3>
							<ul>
								<li><?php esc_html_e( 'Wholesale pricing on all products', 'kaiko-core' ); ?></li>
								<li><?php esc_html_e( 'Priority access to new product lines', 'kaiko-core' ); ?></li>
								<li><?php esc_html_e( 'Dedicated trade support', 'kaiko-core' ); ?></li>
								<li><?php esc_html_e( 'Free shipping on qualifying orders', 'kaiko-core' ); ?></li>
							</ul>
						</div>

						<p class="woocommerce-form-row form-row">
							<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
							<button type="submit"
							        class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit"
							        name="register"
							        value="<?php esc_attr_e( 'Apply for Trade Account', 'kaiko-core' ); ?>">
								<?php esc_html_e( 'Apply for Trade Account', 'kaiko-core' ); ?>
							</button>
						</p>

						<?php do_action( 'woocommerce_register_form_end' ); ?>
					</form>

				<?php else : ?>
					<p class="k-reg-closed">
						<?php esc_html_e( 'Registration is currently closed. Please contact us for trade enquiries.', 'kaiko-core' ); ?>
					</p>
				<?php endif; ?>
			</div>

		</div>

		<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
	</section>

<?php else : ?>

	<?php
	$current_user = wp_get_current_user();
	$first_name   = $current_user->first_name ?: $current_user->user_login;
	$is_pending   = in_array( 'kaiko_pending', (array) $current_user->roles, true );
	$is_trade     = in_array( 'kaiko_trade', (array) $current_user->roles, true );
	$is_dashboard = function_exists( 'is_wc_endpoint_url' ) ? ! is_wc_endpoint_url() : true;
	$account_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : '';
	?>

	<!-- ── Logged-In View ── -->
	<section class="k-hero">
		<h1><?php printf( esc_html__( 'Welcome back, %s', 'kaiko-core' ), esc_html( $first_name ) ); ?></h1>
		<?php if ( $is_pending ) : ?>
			<span class="k-status-badge k-status-pending"><?php esc_html_e( 'Pending Approval', 'kaiko-core' ); ?></span>
		<?php elseif ( $is_trade ) : ?>
			<span class="k-status-badge k-status-approved"><?php esc_html_e( 'Trade Account', 'kaiko-core' ); ?></span>
		<?php endif; ?>
	</section>

	<?php if ( $is_pending ) : ?>
		<div class="k-pending-notice">
			<p><strong><?php esc_html_e( 'Your trade account is under review.', 'kaiko-core' ); ?></strong></p>
			<p><?php esc_html_e( 'Our team is reviewing your application. You will receive an email once your account is approved and you can access wholesale pricing.', 'kaiko-core' ); ?></p>
		</div>
	<?php endif; ?>

	<div class="k-account-wrap">

		<!-- Account Navigation -->
		<nav class="k-account-nav">
			<?php
			$menu_items = function_exists( 'wc_get_account_menu_items' ) ? wc_get_account_menu_items() : [];
			$logout_url = function_exists( 'wc_logout_url' ) ? wc_logout_url( $account_url ) : wp_logout_url( home_url() );

			foreach ( $menu_items as $endpoint => $label ) :
				// Logout gets special treatment (pushed right, rendered last)
				if ( 'customer-logout' === $endpoint ) {
					continue;
				}

				if ( 'dashboard' === $endpoint ) {
					$url    = $account_url;
					$active = $is_dashboard;
				} else {
					$url    = wc_get_endpoint_url( $endpoint, '', $account_url );
					$active = is_wc_endpoint_url( $endpoint );
					// view-order is a sub-endpoint of orders
					if ( 'orders' === $endpoint && is_wc_endpoint_url( 'view-order' ) ) {
						$active = true;
					}
				}
			?>
				<a href="<?php echo esc_url( $url ); ?>"<?php echo $active ? ' class="active"' : ''; ?>>
					<?php echo esc_html( $label ); ?>
				</a>
			<?php endforeach; ?>
			<a href="<?php echo esc_url( $logout_url ); ?>" class="k-nav-logout">
				<?php esc_html_e( 'Log out', 'kaiko-core' ); ?>
			</a>
		</nav>

		<?php
		// Print WC notices (login success, form confirmations, etc.)
		if ( function_exists( 'wc_print_notices' ) ) {
			wc_print_notices();
		}
		?>

		<?php if ( $is_dashboard ) : ?>

			<!-- ── Trade Dashboard ── -->
			<div class="k-dashboard-grid">

				<!-- Recent Orders -->
				<div class="k-dash-card k-dash-orders">
					<div class="k-dash-card-header">
						<h2><?php esc_html_e( 'Recent Orders', 'kaiko-core' ); ?></h2>
						<a href="<?php echo esc_url( wc_get_endpoint_url( 'orders', '', $account_url ) ); ?>" class="k-card-link">
							<?php esc_html_e( 'View All', 'kaiko-core' ); ?> &rarr;
						</a>
					</div>
					<div class="k-dash-card-body">
						<?php
						$orders = wc_get_orders( [
							'customer' => $current_user->ID,
							'limit'    => 5,
							'orderby'  => 'date',
							'order'    => 'DESC',
						] );

						if ( $orders ) : ?>
							<table class="k-orders-table">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Order', 'kaiko-core' ); ?></th>
										<th><?php esc_html_e( 'Date', 'kaiko-core' ); ?></th>
										<th><?php esc_html_e( 'Status', 'kaiko-core' ); ?></th>
										<th><?php esc_html_e( 'Total', 'kaiko-core' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $orders as $order ) : ?>
										<tr>
											<td>
												<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
													#<?php echo esc_html( $order->get_order_number() ); ?>
												</a>
											</td>
											<td><?php echo esc_html( $order->get_date_created()->date_i18n( get_option( 'date_format' ) ) ); ?></td>
											<td>
												<span class="k-order-status k-status-<?php echo esc_attr( $order->get_status() ); ?>">
													<?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
												</span>
											</td>
											<td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						<?php else : ?>
							<p class="k-dash-empty"><?php esc_html_e( 'No orders placed yet.', 'kaiko-core' ); ?></p>
						<?php endif; ?>
					</div>
				</div>

				<!-- Subscription Status -->
				<div class="k-dash-card k-dash-subscriptions">
					<div class="k-dash-card-header">
						<h2><?php esc_html_e( 'Subscriptions', 'kaiko-core' ); ?></h2>
						<?php if ( class_exists( 'WC_Subscriptions' ) ) : ?>
							<a href="<?php echo esc_url( wc_get_endpoint_url( 'subscriptions', '', $account_url ) ); ?>" class="k-card-link">
								<?php esc_html_e( 'Manage', 'kaiko-core' ); ?> &rarr;
							</a>
						<?php endif; ?>
					</div>
					<div class="k-dash-card-body">
						<?php if ( class_exists( 'WC_Subscriptions' ) && function_exists( 'wcs_get_subscriptions' ) ) :
							$subscriptions = wcs_get_subscriptions( [
								'customer_id'            => $current_user->ID,
								'subscriptions_per_page' => 5,
								'subscription_status'    => [ 'active', 'on-hold', 'pending' ],
							] );

							if ( $subscriptions ) : ?>
								<div class="k-subs-list">
									<?php foreach ( $subscriptions as $subscription ) : ?>
										<div class="k-sub-item">
											<div class="k-sub-info">
												<span class="k-sub-id">#<?php echo esc_html( $subscription->get_order_number() ); ?></span>
												<span class="k-sub-status k-status-<?php echo esc_attr( $subscription->get_status() ); ?>">
													<?php echo esc_html( wcs_get_subscription_status_name( $subscription->get_status() ) ); ?>
												</span>
											</div>
											<div class="k-sub-meta">
												<?php
												$next_payment = $subscription->get_date( 'next_payment' );
												if ( $next_payment ) :
												?>
													<span class="k-sub-next">
														<?php printf(
															esc_html__( 'Next: %s', 'kaiko-core' ),
															esc_html( date_i18n( get_option( 'date_format' ), strtotime( $next_payment ) ) )
														); ?>
													</span>
												<?php endif; ?>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							<?php else : ?>
								<p class="k-dash-empty"><?php esc_html_e( 'No active subscriptions.', 'kaiko-core' ); ?></p>
							<?php endif;
						else : ?>
							<p class="k-dash-empty"><?php esc_html_e( 'No subscriptions available.', 'kaiko-core' ); ?></p>
						<?php endif; ?>
					</div>
				</div>

				<!-- Account Details -->
				<div class="k-dash-card k-dash-details">
					<div class="k-dash-card-header">
						<h2><?php esc_html_e( 'Account Details', 'kaiko-core' ); ?></h2>
						<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-account', '', $account_url ) ); ?>" class="k-card-link">
							<?php esc_html_e( 'Edit', 'kaiko-core' ); ?> &rarr;
						</a>
					</div>
					<div class="k-dash-card-body">
						<div class="k-details-list">
							<div class="k-detail-row">
								<span class="k-detail-label"><?php esc_html_e( 'Name', 'kaiko-core' ); ?></span>
								<span class="k-detail-value">
									<?php echo esc_html( trim( $current_user->first_name . ' ' . $current_user->last_name ) ?: $current_user->user_login ); ?>
								</span>
							</div>
							<div class="k-detail-row">
								<span class="k-detail-label"><?php esc_html_e( 'Email', 'kaiko-core' ); ?></span>
								<span class="k-detail-value"><?php echo esc_html( $current_user->user_email ); ?></span>
							</div>
							<?php $business_name = get_user_meta( $current_user->ID, 'kaiko_business_name', true ); ?>
							<?php if ( $business_name ) : ?>
								<div class="k-detail-row">
									<span class="k-detail-label"><?php esc_html_e( 'Business', 'kaiko-core' ); ?></span>
									<span class="k-detail-value"><?php echo esc_html( $business_name ); ?></span>
								</div>
							<?php endif; ?>
						</div>
						<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', '', $account_url ) ); ?>" class="k-dash-action">
							<?php esc_html_e( 'Manage Addresses', 'kaiko-core' ); ?> &rarr;
						</a>
					</div>
				</div>

			</div><!-- .k-dashboard-grid -->

		<?php else : ?>

			<!-- ── WooCommerce Endpoint Content ── -->
			<div class="k-wc-content">
				<?php echo do_shortcode( '[woocommerce_my_account]' ); ?>
			</div>

		<?php endif; ?>

	</div><!-- .k-account-wrap -->

<?php endif; ?>

</div><!-- .kaiko-myaccount -->

<script>
(function() {
	/* Add focus class to parent for enhanced styling */
	var inputs = document.querySelectorAll('.kaiko-myaccount .woocommerce-form input');
	inputs.forEach(function(input) {
		input.addEventListener('focus', function() {
			if (this.parentElement) this.parentElement.classList.add('focused');
		});
		input.addEventListener('blur', function() {
			if (this.parentElement) this.parentElement.classList.remove('focused');
		});
	});
})();
</script>

<?php do_action( 'kaiko_after_content' ); ?>

<?php get_footer(); ?>
