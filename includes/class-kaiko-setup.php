<?php
/**
 * One-Time Setup Module.
 *
 * Creates default legal pages on first activation. Pages are only
 * created once — the option flag prevents duplicates on re-activation.
 *
 * @package KaikoCore
 */

defined( 'ABSPATH' ) || exit;

class Kaiko_Setup {

	/** @var string Option key used to track whether setup has run. */
	private const SETUP_FLAG = 'kaiko_setup_pages_created';

	/**
	 * Register hooks.
	 */
	public function init(): void {
		// Run setup on admin_init so WordPress is fully loaded.
		// The activation hook sets a transient; we check it here.
		add_action( 'admin_init', [ $this, 'maybe_create_pages' ] );
	}

	/**
	 * Schedule page creation on next admin load.
	 *
	 * Called by the plugin activation hook in kaiko-core.php.
	 */
	public static function on_activate(): void {
		set_transient( 'kaiko_run_setup', true, 60 );
	}

	/**
	 * Create pages if the activation transient is present and setup
	 * hasn't already run.
	 */
	public function maybe_create_pages(): void {
		if ( ! get_transient( 'kaiko_run_setup' ) ) {
			return;
		}

		delete_transient( 'kaiko_run_setup' );

		if ( get_option( self::SETUP_FLAG ) ) {
			return;
		}

		$this->create_legal_pages();

		update_option( self::SETUP_FLAG, true, false );
	}

	// ─── Page Creation ───────────────────────────────────────────────

	/**
	 * Create all legal pages.
	 */
	private function create_legal_pages(): void {
		$pages = [
			[
				'title'   => 'Privacy Policy',
				'slug'    => 'privacy-policy',
				'content' => $this->privacy_policy_content(),
			],
			[
				'title'   => 'Terms & Conditions',
				'slug'    => 'terms-and-conditions',
				'content' => $this->terms_conditions_content(),
			],
			[
				'title'   => 'Returns Policy',
				'slug'    => 'returns-policy',
				'content' => $this->returns_policy_content(),
			],
		];

		foreach ( $pages as $page ) {
			$this->create_page( $page['title'], $page['slug'], $page['content'] );
		}
	}

	/**
	 * Insert a page if one with that slug doesn't already exist.
	 *
	 * @param string $title   Page title.
	 * @param string $slug    Page slug.
	 * @param string $content Page content (HTML).
	 */
	private function create_page( string $title, string $slug, string $content ): void {
		// Don't create duplicates
		$existing = get_page_by_path( $slug );
		if ( $existing ) {
			// Ensure template is assigned even if page already exists
			update_post_meta( $existing->ID, '_wp_page_template', 'kaiko-legal.php' );
			return;
		}

		$page_id = wp_insert_post( [
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_author'  => 1,
		] );

		if ( $page_id && ! is_wp_error( $page_id ) ) {
			update_post_meta( $page_id, '_wp_page_template', 'kaiko-legal.php' );
		}
	}

	// ─── Page Content ────────────────────────────────────────────────

	/**
	 * Privacy Policy — UK e-commerce GDPR compliant placeholder.
	 */
	private function privacy_policy_content(): string {
		return <<<'HTML'
<!-- PLACEHOLDER — Tom: review and customise all content below for KAIKO Products / Silkworm Store Limited -->

<p class="k-legal-meta">Last updated: [DATE] <!-- PLACEHOLDER: insert date --></p>

<h2>1. Who We Are</h2>
<!-- PLACEHOLDER: confirm company details, registration number, and ICO registration -->
<p>KAIKO Products is a trading name of Silkworm Store Limited, a company registered in England and Wales (company number [NUMBER]). Our registered address is [ADDRESS].</p>
<p>We are the data controller for personal data collected through this website. If you have questions about how we handle your data, contact us at <a href="mailto:info@kaikoproducts.com">info@kaikoproducts.com</a>.</p>

<h2>2. What Data We Collect</h2>
<p>We collect the following categories of personal data:</p>
<ul>
  <li><strong>Identity data:</strong> name, business name (for trade accounts).</li>
  <li><strong>Contact data:</strong> email address, telephone number, delivery and billing addresses.</li>
  <li><strong>Account data:</strong> username, password (encrypted), trade account status.</li>
  <li><strong>Transaction data:</strong> order history, payment references, delivery details.</li>
  <li><strong>Technical data:</strong> IP address, browser type, device information, pages visited.</li>
  <li><strong>Communication data:</strong> messages sent via our contact form or email.</li>
</ul>

<h2>3. How We Use Your Data</h2>
<p>We process your personal data on the following lawful bases under the UK GDPR:</p>
<ul>
  <li><strong>Contract performance:</strong> to fulfil orders, manage your account, and process returns.</li>
  <li><strong>Legitimate interests:</strong> to improve our website, prevent fraud, and administer our business.</li>
  <li><strong>Legal obligation:</strong> to comply with tax, accounting, and regulatory requirements.</li>
  <li><strong>Consent:</strong> to send marketing communications (you can withdraw consent at any time).</li>
</ul>

<h2>4. Sharing Your Data</h2>
<!-- PLACEHOLDER: confirm all third-party processors -->
<p>We share personal data only where necessary:</p>
<ul>
  <li><strong>Payment processors:</strong> to process transactions securely (e.g. Stripe, PayPal). <!-- PLACEHOLDER: confirm providers --></li>
  <li><strong>Delivery partners:</strong> to ship your orders (e.g. Royal Mail, DPD). <!-- PLACEHOLDER: confirm carriers --></li>
  <li><strong>Hosting provider:</strong> our website is hosted on secure UK/EEA servers. <!-- PLACEHOLDER: confirm host --></li>
  <li><strong>Analytics:</strong> we use anonymised analytics to improve our website. <!-- PLACEHOLDER: confirm analytics tool --></li>
</ul>
<p>We do not sell your personal data to third parties.</p>

<h2>5. Data Retention</h2>
<p>We retain your personal data for as long as necessary to fulfil the purposes outlined above:</p>
<ul>
  <li><strong>Account data:</strong> for the duration of your account, plus 12 months after closure.</li>
  <li><strong>Transaction data:</strong> 7 years to meet HMRC requirements.</li>
  <li><strong>Marketing data:</strong> until you unsubscribe or withdraw consent.</li>
  <li><strong>Technical data:</strong> up to 26 months.</li>
</ul>

<h2>6. Your Rights</h2>
<p>Under the UK GDPR, you have the right to:</p>
<ul>
  <li>Access the personal data we hold about you.</li>
  <li>Rectify inaccurate or incomplete data.</li>
  <li>Erase your data (subject to legal obligations).</li>
  <li>Restrict or object to processing.</li>
  <li>Data portability — receive your data in a structured, machine-readable format.</li>
  <li>Withdraw consent at any time where processing is based on consent.</li>
</ul>
<p>To exercise any of these rights, email us at <a href="mailto:info@kaikoproducts.com">info@kaikoproducts.com</a>. We will respond within 30 days.</p>

<h2>7. Cookies</h2>
<!-- PLACEHOLDER: update cookie list to match actual cookies used -->
<p>Our website uses cookies to provide essential functionality and improve your experience:</p>
<ul>
  <li><strong>Essential cookies:</strong> required for the website and checkout to function.</li>
  <li><strong>Analytics cookies:</strong> help us understand how visitors use our site.</li>
</ul>
<p>You can manage cookie preferences through your browser settings.</p>

<h2>8. Security</h2>
<p>We implement appropriate technical and organisational measures to protect your personal data, including encrypted connections (SSL/TLS), secure payment processing, and access controls.</p>

<h2>9. Changes to This Policy</h2>
<p>We may update this policy from time to time. The "Last updated" date at the top will reflect any changes. We encourage you to review this page periodically.</p>

<div class="k-legal-contact">
  <h3>Contact Us</h3>
  <!-- PLACEHOLDER: confirm contact details -->
  <p>If you have questions about this Privacy Policy or wish to exercise your rights, please contact us:</p>
  <p>Email: <a href="mailto:info@kaikoproducts.com">info@kaikoproducts.com</a><br>
  Post: Silkworm Store Limited, [ADDRESS] <!-- PLACEHOLDER: insert address --></p>
  <p>You also have the right to lodge a complaint with the Information Commissioner's Office (ICO) at <a href="https://ico.org.uk">ico.org.uk</a>.</p>
</div>
HTML;
	}

	/**
	 * Terms & Conditions — UK wholesale/trade terms placeholder.
	 */
	private function terms_conditions_content(): string {
		return <<<'HTML'
<!-- PLACEHOLDER — Tom: review and customise all content below for KAIKO Products / Silkworm Store Limited -->

<p class="k-legal-meta">Last updated: [DATE] <!-- PLACEHOLDER: insert date --></p>

<h2>1. About These Terms</h2>
<p>These Terms and Conditions govern your use of the KAIKO Products website and the purchase of goods from us. By placing an order, you agree to be bound by these terms.</p>
<p>KAIKO Products is a trading name of Silkworm Store Limited, registered in England and Wales (company number [NUMBER]). <!-- PLACEHOLDER: confirm company number --></p>

<h2>2. Trade Accounts</h2>
<!-- PLACEHOLDER: confirm trade account terms, minimum order values, and approval process -->
<p>Trade pricing is available exclusively to approved trade account holders. To apply for a trade account, register on our website and provide your business details. Accounts are subject to approval at our discretion.</p>
<ul>
  <li>Trade accounts are for businesses purchasing for resale or professional use only.</li>
  <li>All trade prices are displayed exclusive of VAT unless otherwise stated.</li>
  <li>We reserve the right to withdraw trade status at any time if terms are breached.</li>
  <li>Minimum order values may apply. <!-- PLACEHOLDER: confirm minimum order value --></li>
</ul>

<h2>3. Orders and Pricing</h2>
<ul>
  <li>All orders are subject to availability and acceptance.</li>
  <li>Prices are in GBP (£) and include VAT for retail customers. Trade prices exclude VAT.</li>
  <li>We reserve the right to correct pricing errors. If an error affects your order, we will contact you before processing.</li>
  <li>An order confirmation email does not constitute acceptance. Acceptance occurs when we dispatch your goods.</li>
</ul>

<h2>4. Payment</h2>
<!-- PLACEHOLDER: confirm accepted payment methods -->
<p>We accept payment by credit/debit card and other methods shown at checkout. Payment is taken at the time of order. All transactions are processed securely.</p>

<h2>5. Delivery</h2>
<!-- PLACEHOLDER: confirm delivery timeframes, carriers, and shipping costs -->
<ul>
  <li>We aim to dispatch orders within [X] working days. <!-- PLACEHOLDER: confirm dispatch timeframe --></li>
  <li>Delivery times are estimates and not guaranteed.</li>
  <li>Risk of loss passes to you upon delivery.</li>
  <li>If your order arrives damaged, please contact us within 48 hours with photographs.</li>
</ul>

<h2>6. Returns and Refunds</h2>
<p>Please see our <a href="/returns-policy/">Returns Policy</a> for full details. In summary:</p>
<ul>
  <li>You have 14 days from receipt to return unused items in original packaging.</li>
  <li>Items must be unused, in original condition, and in original packaging.</li>
  <li>Refunds are processed within 14 days of receiving the returned goods.</li>
  <li>Return shipping costs are the responsibility of the buyer unless the item is faulty or incorrect.</li>
</ul>

<h2>7. Intellectual Property</h2>
<p>All content on this website — including text, images, logos, product designs, and branding — is owned by Silkworm Store Limited or its licensors. You may not reproduce, distribute, or use any content without our written permission.</p>

<h2>8. Limitation of Liability</h2>
<p>To the fullest extent permitted by law:</p>
<ul>
  <li>Our total liability for any claim arising from the supply of goods shall not exceed the price paid for those goods.</li>
  <li>We are not liable for indirect, consequential, or incidental losses.</li>
  <li>Nothing in these terms excludes or limits liability for death or personal injury caused by negligence, fraud, or any other liability that cannot be excluded by law.</li>
</ul>

<h2>9. Consumer Rights</h2>
<p>These terms do not affect your statutory rights as a consumer under the Consumer Rights Act 2015. If goods are faulty, not as described, or not fit for purpose, you are entitled to a repair, replacement, or refund.</p>

<h2>10. Governing Law</h2>
<p>These terms are governed by the laws of England and Wales. Any disputes will be subject to the exclusive jurisdiction of the courts of England and Wales.</p>

<h2>11. Changes to These Terms</h2>
<p>We may update these terms from time to time. Changes will be posted on this page with an updated date. Continued use of the website after changes constitutes acceptance of the revised terms.</p>

<div class="k-legal-contact">
  <h3>Contact Us</h3>
  <!-- PLACEHOLDER: confirm contact details -->
  <p>If you have questions about these Terms and Conditions:</p>
  <p>Email: <a href="mailto:info@kaikoproducts.com">info@kaikoproducts.com</a><br>
  Post: Silkworm Store Limited, [ADDRESS] <!-- PLACEHOLDER: insert address --></p>
</div>
HTML;
	}

	/**
	 * Returns Policy — 14-day returns for unused items.
	 */
	private function returns_policy_content(): string {
		return <<<'HTML'
<!-- PLACEHOLDER — Tom: review and customise all content below for KAIKO Products / Silkworm Store Limited -->

<p class="k-legal-meta">Last updated: [DATE] <!-- PLACEHOLDER: insert date --></p>

<h2>1. Our Returns Promise</h2>
<p>We want you to be completely happy with your purchase. If for any reason you are not satisfied, you may return items within 14 days of delivery, in accordance with the Consumer Contracts (Information, Cancellation and Additional Charges) Regulations 2013.</p>

<h2>2. Eligibility</h2>
<p>To be eligible for a return, items must be:</p>
<ul>
  <li>Unused and in the same condition as received.</li>
  <li>In the original, undamaged packaging.</li>
  <li>Returned within 14 days of the delivery date.</li>
</ul>
<p>The following items cannot be returned unless faulty:</p>
<ul>
  <li>Items that have been used or assembled. <!-- PLACEHOLDER: confirm non-returnable categories --></li>
  <li>Items without original packaging.</li>
  <li>Custom or bespoke orders (if applicable). <!-- PLACEHOLDER: confirm if custom orders exist --></li>
</ul>

<h2>3. How to Return an Item</h2>
<ol>
  <li>Email us at <a href="mailto:info@kaikoproducts.com">info@kaikoproducts.com</a> with your order number and reason for return.</li>
  <li>We will provide a returns authorisation and delivery instructions.</li>
  <li>Pack the item securely in its original packaging and post it to the address provided.</li>
</ol>
<!-- PLACEHOLDER: confirm if you want to offer a returns portal or printable label -->

<h2>4. Return Shipping</h2>
<!-- PLACEHOLDER: confirm who pays return shipping -->
<ul>
  <li><strong>Change of mind:</strong> return shipping costs are the responsibility of the buyer.</li>
  <li><strong>Faulty or incorrect items:</strong> we will cover the return shipping costs or arrange collection.</li>
</ul>
<p>We recommend using a tracked delivery service, as we cannot be responsible for items lost in transit.</p>

<h2>5. Refunds</h2>
<ul>
  <li>Once we receive and inspect your return, we will notify you by email.</li>
  <li>Approved refunds are processed within 14 days to your original payment method.</li>
  <li>Original delivery charges are refunded only if the return is due to our error or a faulty item.</li>
</ul>

<h2>6. Faulty or Damaged Goods</h2>
<p>Under the Consumer Rights Act 2015, you are entitled to a refund, repair, or replacement if goods are:</p>
<ul>
  <li>Faulty or defective.</li>
  <li>Not as described on the website.</li>
  <li>Not fit for purpose.</li>
</ul>
<p>If your item arrives damaged, please contact us within 48 hours of delivery with:</p>
<ul>
  <li>Your order number.</li>
  <li>A description of the issue.</li>
  <li>Photographs of the damage (including packaging).</li>
</ul>

<h2>7. Exchanges</h2>
<!-- PLACEHOLDER: confirm exchange policy -->
<p>We do not offer direct exchanges. To receive a different item, please return the original for a refund and place a new order.</p>

<h2>8. Trade Account Returns</h2>
<!-- PLACEHOLDER: confirm trade-specific return terms -->
<p>Trade account holders are subject to the same 14-day return policy. Bulk orders may have additional conditions — please contact us before returning trade orders.</p>

<div class="k-legal-contact">
  <h3>Contact Us About a Return</h3>
  <!-- PLACEHOLDER: confirm contact details -->
  <p>For all returns enquiries:</p>
  <p>Email: <a href="mailto:info@kaikoproducts.com">info@kaikoproducts.com</a><br>
  Post: Silkworm Store Limited, [ADDRESS] <!-- PLACEHOLDER: insert address --></p>
</div>
HTML;
	}
}
