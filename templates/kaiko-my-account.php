<?php
/**
 * Template Name: KAIKO My Account
 * Description: Trade login, registration, and account management page.
 *
 * @package KaikoCore
 */

defined( 'ABSPATH' ) || exit;

get_header(); ?>

<style>
/* ── KAIKO My Account Styles ── */
:root {
  --k-teal: #1a5c52;
  --k-deep-teal: #134840;
  --k-light-teal: #2d8073;
  --k-lime: #b8d435;
  --k-gold: #c89b3c;
  --k-dark: #1a1a1a;
  --k-stone-600: #57534E;
  --k-stone-500: #78716C;
  --k-stone-400: #A8A29E;
  --k-stone-300: #D6D3D1;
  --k-stone-200: #E7E5E4;
  --k-stone-100: #F5F5F4;
  --k-stone-50: #FAFAF9;
  --k-cream: #f5f1ea;
  --k-white: #ffffff;
  --k-font: 'Inter', -apple-system, system-ui, 'Segoe UI', sans-serif;
  --k-r-sm: 6px;
  --k-r-md: 10px;
  --k-r-lg: 16px;
  --k-r-pill: 999px;
  --k-ease: cubic-bezier(0.25, 0.46, 0.45, 0.94);
  --k-dur: 0.3s;
}

/* Reset within our template */
.kaiko-myaccount * { box-sizing: border-box; }
.kaiko-myaccount img { max-width: 100%; display: block; }
.kaiko-myaccount a { text-decoration: none; color: inherit; }
.kaiko-myaccount { font-family: var(--k-font); color: var(--k-dark); line-height: 1.65; -webkit-font-smoothing: antialiased; }

/* ── Hero ── */
.kaiko-myaccount .k-hero {
  background: linear-gradient(180deg, var(--k-cream) 0%, var(--k-white) 100%);
  padding: 70px clamp(1.5rem, 4vw, 4rem) 60px;
  text-align: center;
}
.kaiko-myaccount .k-hero-label {
  display: inline-block;
  font-size: 0.68rem;
  font-weight: 600;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--k-teal);
  background: rgba(26,92,82,0.06);
  border: 1px solid rgba(26,92,82,0.1);
  padding: 7px 16px;
  border-radius: var(--k-r-pill);
  margin-bottom: 20px;
}
.kaiko-myaccount .k-hero h1 {
  font-family: var(--k-font);
  font-size: clamp(1.8rem, 3.5vw, 2.8rem);
  font-weight: 700;
  line-height: 1.15;
  letter-spacing: -0.02em;
  color: var(--k-dark);
  margin-bottom: 12px;
}
.kaiko-myaccount .k-hero-sub {
  font-size: 0.95rem;
  color: var(--k-stone-500);
  max-width: 500px;
  margin: 0 auto;
  line-height: 1.7;
}

/* ── Status Badge ── */
.kaiko-myaccount .k-status-badge {
  display: inline-block;
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  padding: 6px 16px;
  border-radius: var(--k-r-pill);
  margin-top: 12px;
}
.kaiko-myaccount .k-status-pending {
  background: rgba(200,155,60,0.1);
  color: var(--k-gold);
  border: 1px solid rgba(200,155,60,0.2);
}
.kaiko-myaccount .k-status-approved {
  background: rgba(26,92,82,0.08);
  color: var(--k-teal);
  border: 1px solid rgba(26,92,82,0.15);
}

/* ── Auth Section (Guest) ── */
.kaiko-myaccount .k-auth-section {
  padding: 50px clamp(1.5rem, 4vw, 4rem) 80px;
  max-width: 1100px;
  margin: 0 auto;
}
.kaiko-myaccount .k-auth-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 32px;
  align-items: start;
}
.kaiko-myaccount .k-auth-card {
  background: var(--k-white);
  border: 1px solid var(--k-stone-200);
  border-radius: var(--k-r-md);
  padding: 36px 32px;
}
.kaiko-myaccount .k-auth-card h2 {
  font-family: var(--k-font);
  font-size: 1.3rem;
  font-weight: 700;
  color: var(--k-dark);
  margin-bottom: 6px;
}
.kaiko-myaccount .k-auth-desc {
  font-size: 0.88rem;
  color: var(--k-stone-500);
  margin-bottom: 28px;
  line-height: 1.6;
}

/* ── WooCommerce Form Overrides ── */
.kaiko-myaccount .woocommerce-form .form-row {
  margin-bottom: 18px;
}
.kaiko-myaccount .woocommerce-form label {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--k-dark);
  margin-bottom: 6px;
  font-family: var(--k-font);
}
.kaiko-myaccount .woocommerce-form input[type="text"],
.kaiko-myaccount .woocommerce-form input[type="email"],
.kaiko-myaccount .woocommerce-form input[type="password"],
.kaiko-myaccount .woocommerce-form .input-text {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid var(--k-stone-300);
  border-radius: var(--k-r-sm);
  font-size: 0.88rem;
  font-family: var(--k-font);
  outline: none;
  transition: border-color var(--k-dur);
  background: var(--k-white);
  color: var(--k-dark);
}
.kaiko-myaccount .woocommerce-form input:focus,
.kaiko-myaccount .woocommerce-form .input-text:focus {
  border-color: var(--k-teal);
  box-shadow: 0 0 0 3px rgba(26,92,82,0.08);
}
.kaiko-myaccount .woocommerce-form .woocommerce-form-login__rememberme {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.82rem;
  color: var(--k-stone-500);
  margin-bottom: 20px;
}
.kaiko-myaccount .woocommerce-form button[type="submit"],
.kaiko-myaccount .woocommerce-form .woocommerce-form-register__submit,
.kaiko-myaccount .woocommerce-form .woocommerce-form-login__submit {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  background: var(--k-teal);
  color: var(--k-white);
  padding: 14px 28px;
  border: none;
  border-radius: var(--k-r-sm);
  font-size: 0.85rem;
  font-weight: 600;
  font-family: var(--k-font);
  letter-spacing: 0.02em;
  cursor: pointer;
  transition: background var(--k-dur), transform var(--k-dur);
}
.kaiko-myaccount .woocommerce-form button[type="submit"]:hover,
.kaiko-myaccount .woocommerce-form .woocommerce-form-register__submit:hover,
.kaiko-myaccount .woocommerce-form .woocommerce-form-login__submit:hover {
  background: var(--k-deep-teal);
  transform: translateY(-1px);
  color: var(--k-white);
}
.kaiko-myaccount .woocommerce-form .lost_password {
  margin-top: 16px;
  text-align: center;
}
.kaiko-myaccount .woocommerce-form .lost_password a {
  font-size: 0.82rem;
  color: var(--k-teal);
  font-weight: 500;
}
.kaiko-myaccount .woocommerce-form .lost_password a:hover {
  color: var(--k-deep-teal);
}
.kaiko-myaccount .woocommerce-form .required {
  color: var(--k-gold);
}

/* ── Benefits List ── */
.kaiko-myaccount .k-benefits-list {
  margin-top: 28px;
  padding-top: 24px;
  border-top: 1px solid var(--k-stone-200);
}
.kaiko-myaccount .k-benefits-list h3 {
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--k-dark);
  margin-bottom: 14px;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}
.kaiko-myaccount .k-benefits-list ul {
  list-style: none;
  padding: 0;
  margin: 0 0 24px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.kaiko-myaccount .k-benefits-list li {
  font-size: 0.88rem;
  color: var(--k-stone-600);
  padding-left: 24px;
  position: relative;
  line-height: 1.5;
}
.kaiko-myaccount .k-benefits-list li::before {
  content: '';
  position: absolute;
  left: 0;
  top: 4px;
  width: 14px;
  height: 14px;
  background: rgba(26,92,82,0.1);
  border-radius: 50%;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 12 12' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M3 6l2 2 4-4' stroke='%231a5c52' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: center;
  background-size: 12px;
}

/* ── Registration Disabled ── */
.kaiko-myaccount .k-reg-closed {
  font-size: 0.9rem;
  color: var(--k-stone-500);
  text-align: center;
  padding: 24px 0;
}

/* ── Pending Notice ── */
.kaiko-myaccount .k-pending-notice {
  max-width: 800px;
  margin: 0 auto 32px;
  padding: 20px 28px;
  background: rgba(200,155,60,0.06);
  border-left: 4px solid var(--k-gold);
  border-radius: 0 var(--k-r-sm) var(--k-r-sm) 0;
}
.kaiko-myaccount .k-pending-notice p {
  font-size: 0.9rem;
  color: var(--k-stone-600);
  margin: 0 0 6px;
  line-height: 1.6;
}
.kaiko-myaccount .k-pending-notice p:last-child { margin-bottom: 0; }

/* ── Account Dashboard (Logged In) ── */
.kaiko-myaccount .k-account-wrap {
  max-width: 1100px;
  margin: 0 auto;
  padding: 0 clamp(1.5rem, 4vw, 4rem) 80px;
}

/* WooCommerce My Account Tab Navigation */
.kaiko-myaccount .woocommerce-MyAccount-navigation {
  margin-bottom: 32px;
}
.kaiko-myaccount .woocommerce-MyAccount-navigation ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  gap: 4px;
  border-bottom: 2px solid var(--k-stone-200);
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
.kaiko-myaccount .woocommerce-MyAccount-navigation ul li {
  flex-shrink: 0;
}
.kaiko-myaccount .woocommerce-MyAccount-navigation ul li a {
  display: block;
  padding: 12px 20px;
  font-size: 0.85rem;
  font-weight: 500;
  color: var(--k-stone-500);
  border-bottom: 2px solid transparent;
  margin-bottom: -2px;
  transition: color var(--k-dur), border-color var(--k-dur);
  white-space: nowrap;
}
.kaiko-myaccount .woocommerce-MyAccount-navigation ul li a:hover {
  color: var(--k-dark);
}
.kaiko-myaccount .woocommerce-MyAccount-navigation ul li.is-active a {
  color: var(--k-teal);
  border-bottom-color: var(--k-teal);
  font-weight: 600;
}

/* WooCommerce My Account Content */
.kaiko-myaccount .woocommerce-MyAccount-content {
  font-size: 0.92rem;
  line-height: 1.7;
  color: var(--k-stone-600);
}
.kaiko-myaccount .woocommerce-MyAccount-content a {
  color: var(--k-teal);
  font-weight: 500;
}
.kaiko-myaccount .woocommerce-MyAccount-content a:hover {
  color: var(--k-deep-teal);
}

/* WooCommerce tables within account */
.kaiko-myaccount .woocommerce-orders-table,
.kaiko-myaccount .woocommerce-table--order-details {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.88rem;
}
.kaiko-myaccount .woocommerce-orders-table th,
.kaiko-myaccount .woocommerce-table--order-details th {
  text-align: left;
  font-weight: 600;
  color: var(--k-dark);
  padding: 12px 16px;
  border-bottom: 2px solid var(--k-stone-200);
  font-size: 0.82rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.kaiko-myaccount .woocommerce-orders-table td,
.kaiko-myaccount .woocommerce-table--order-details td {
  padding: 14px 16px;
  border-bottom: 1px solid var(--k-stone-100);
  color: var(--k-stone-600);
}
.kaiko-myaccount .woocommerce-orders-table .button,
.kaiko-myaccount .woocommerce-MyAccount-content .button {
  display: inline-block;
  background: var(--k-teal);
  color: var(--k-white) !important;
  padding: 8px 18px;
  border-radius: var(--k-r-sm);
  font-size: 0.78rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: background var(--k-dur);
}
.kaiko-myaccount .woocommerce-orders-table .button:hover,
.kaiko-myaccount .woocommerce-MyAccount-content .button:hover {
  background: var(--k-deep-teal);
  color: var(--k-white) !important;
}

/* WooCommerce address cards */
.kaiko-myaccount .woocommerce-Addresses {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}
.kaiko-myaccount .woocommerce-Address {
  border: 1px solid var(--k-stone-200);
  border-radius: var(--k-r-md);
  padding: 24px;
}
.kaiko-myaccount .woocommerce-Address-title h3 {
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--k-dark);
  margin-bottom: 12px;
}

/* WooCommerce edit account form */
.kaiko-myaccount .woocommerce-EditAccountForm .form-row {
  margin-bottom: 18px;
}
.kaiko-myaccount .woocommerce-EditAccountForm input[type="text"],
.kaiko-myaccount .woocommerce-EditAccountForm input[type="email"],
.kaiko-myaccount .woocommerce-EditAccountForm input[type="password"] {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid var(--k-stone-300);
  border-radius: var(--k-r-sm);
  font-size: 0.88rem;
  font-family: var(--k-font);
  outline: none;
  transition: border-color var(--k-dur);
}
.kaiko-myaccount .woocommerce-EditAccountForm input:focus {
  border-color: var(--k-teal);
  box-shadow: 0 0 0 3px rgba(26,92,82,0.08);
}

/* WooCommerce notices */
.kaiko-myaccount .woocommerce-message,
.kaiko-myaccount .woocommerce-info {
  background: rgba(26,92,82,0.06);
  border-left: 4px solid var(--k-teal);
  padding: 14px 20px;
  border-radius: 0 var(--k-r-sm) var(--k-r-sm) 0;
  margin-bottom: 24px;
  font-size: 0.88rem;
  color: var(--k-dark);
}
.kaiko-myaccount .woocommerce-error {
  background: rgba(220,38,38,0.06);
  border-left: 4px solid #dc2626;
  padding: 14px 20px;
  border-radius: 0 var(--k-r-sm) var(--k-r-sm) 0;
  margin-bottom: 24px;
  font-size: 0.88rem;
  color: var(--k-dark);
}
.kaiko-myaccount .woocommerce-error li { list-style: none; }

/* ── Responsive ── */
@media (max-width: 768px) {
  .kaiko-myaccount .k-hero { padding: 50px 1.5rem 40px; }
  .kaiko-myaccount .k-hero h1 { font-size: 1.6rem; }
  .kaiko-myaccount .k-auth-section { padding: 30px 1.5rem 60px; }
  .kaiko-myaccount .k-auth-grid { grid-template-columns: 1fr; gap: 24px; }
  .kaiko-myaccount .k-auth-card { padding: 28px 24px; }
  .kaiko-myaccount .k-account-wrap { padding: 0 1.5rem 60px; }
  .kaiko-myaccount .woocommerce-MyAccount-navigation ul {
    gap: 0;
    flex-wrap: nowrap;
  }
  .kaiko-myaccount .woocommerce-MyAccount-navigation ul li a {
    padding: 10px 14px;
    font-size: 0.8rem;
  }
  .kaiko-myaccount .woocommerce-Addresses { grid-template-columns: 1fr; }
  .kaiko-myaccount .k-pending-notice { margin-left: 1.5rem; margin-right: 1.5rem; }
}
</style>

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
		<?php echo do_shortcode( '[woocommerce_my_account]' ); ?>
	</div>

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

<?php get_footer(); ?>
