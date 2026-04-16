<?php
/**
 * GDPR Cookie Consent Module.
 *
 * Displays a cookie consent banner on all frontend pages and manages
 * consent preferences via a lightweight popup. Stores consent state
 * in a first-party cookie (kaiko_consent) for 365 days.
 *
 * Categories: essential (always on), analytics, marketing.
 *
 * @package KaikoCore
 */

defined( 'ABSPATH' ) || exit;

class Kaiko_GDPR {

	/** Cookie name used to store consent choices. */
	private const COOKIE_NAME = 'kaiko_consent';

	/** Cookie lifetime in days. */
	private const COOKIE_DAYS = 365;

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action( 'wp_footer', [ $this, 'render_banner' ] );
		add_action( 'wp_footer', [ $this, 'render_script' ], 99 );
	}

	/**
	 * Check whether the visitor has granted consent for a given category.
	 *
	 * Usage: Kaiko_GDPR::has_consent( 'analytics' )
	 *        or the global helper kaiko_has_consent( 'analytics' )
	 *
	 * @param string $category One of: essential, analytics, marketing.
	 * @return bool
	 */
	public static function has_consent( string $category ): bool {
		if ( 'essential' === $category ) {
			return true; // Essential cookies are always allowed.
		}

		$value = $_COOKIE[ self::COOKIE_NAME ] ?? '';

		if ( '' === $value ) {
			return false;
		}

		if ( 'all' === $value ) {
			return true;
		}

		$categories = array_map( 'trim', explode( ',', $value ) );
		return in_array( $category, $categories, true );
	}

	/**
	 * Render the consent banner and preferences modal HTML.
	 *
	 * Hidden by default via CSS when the consent cookie is already set;
	 * JavaScript handles show/hide and cookie writing.
	 */
	public function render_banner(): void {
		if ( is_admin() ) {
			return;
		}
		?>
		<!-- KAIKO Cookie Consent Banner -->
		<div id="kaiko-consent-banner" class="kaiko-consent-banner" role="dialog" aria-label="<?php esc_attr_e( 'Cookie consent', 'kaiko-core' ); ?>" style="display:none;">
			<div class="kaiko-consent-banner-inner">
				<p class="kaiko-consent-text">We use cookies to improve your experience. Essential cookies are required for the site to function. You may also enable analytics and marketing cookies.</p>
				<div class="kaiko-consent-actions">
					<button type="button" id="kaiko-consent-accept" class="kaiko-consent-btn"><?php esc_html_e( 'Accept All', 'kaiko-core' ); ?></button>
					<button type="button" id="kaiko-consent-manage" class="kaiko-consent-link"><?php esc_html_e( 'Manage preferences', 'kaiko-core' ); ?></button>
				</div>
			</div>
		</div>

		<!-- KAIKO Cookie Preferences Modal -->
		<div id="kaiko-consent-modal" class="kaiko-consent-modal" role="dialog" aria-label="<?php esc_attr_e( 'Cookie preferences', 'kaiko-core' ); ?>" style="display:none;">
			<div class="kaiko-consent-modal-overlay" id="kaiko-consent-overlay"></div>
			<div class="kaiko-consent-modal-content">
				<h3 class="kaiko-consent-modal-title"><?php esc_html_e( 'Cookie Preferences', 'kaiko-core' ); ?></h3>
				<p class="kaiko-consent-modal-desc"><?php esc_html_e( 'Choose which cookies you would like to allow. Essential cookies cannot be disabled as they are required for the site to function.', 'kaiko-core' ); ?></p>

				<div class="kaiko-consent-categories">
					<!-- Essential -->
					<label class="kaiko-consent-category">
						<span class="kaiko-consent-category-info">
							<strong><?php esc_html_e( 'Essential', 'kaiko-core' ); ?></strong>
							<span><?php esc_html_e( 'Required for the site to work properly.', 'kaiko-core' ); ?></span>
						</span>
						<span class="kaiko-consent-toggle kaiko-consent-toggle--disabled">
							<input type="checkbox" checked disabled>
							<span class="kaiko-consent-toggle-track"></span>
						</span>
					</label>

					<!-- Analytics -->
					<label class="kaiko-consent-category">
						<span class="kaiko-consent-category-info">
							<strong><?php esc_html_e( 'Analytics', 'kaiko-core' ); ?></strong>
							<span><?php esc_html_e( 'Help us understand how visitors use our site.', 'kaiko-core' ); ?></span>
						</span>
						<span class="kaiko-consent-toggle">
							<input type="checkbox" id="kaiko-consent-analytics" value="analytics">
							<span class="kaiko-consent-toggle-track"></span>
						</span>
					</label>

					<!-- Marketing -->
					<label class="kaiko-consent-category">
						<span class="kaiko-consent-category-info">
							<strong><?php esc_html_e( 'Marketing', 'kaiko-core' ); ?></strong>
							<span><?php esc_html_e( 'Used to deliver relevant ads and track campaigns.', 'kaiko-core' ); ?></span>
						</span>
						<span class="kaiko-consent-toggle">
							<input type="checkbox" id="kaiko-consent-marketing" value="marketing">
							<span class="kaiko-consent-toggle-track"></span>
						</span>
					</label>
				</div>

				<div class="kaiko-consent-modal-actions">
					<button type="button" id="kaiko-consent-save" class="kaiko-consent-btn"><?php esc_html_e( 'Save preferences', 'kaiko-core' ); ?></button>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render inline JavaScript for consent logic.
	 *
	 * Kept minimal and inline to avoid an extra HTTP request for a
	 * script that must run before any tracking fires.
	 */
	public function render_script(): void {
		if ( is_admin() ) {
			return;
		}
		?>
		<script>
		(function(){
			var COOKIE = '<?php echo esc_js( self::COOKIE_NAME ); ?>';
			var DAYS   = <?php echo (int) self::COOKIE_DAYS; ?>;

			function getCookie(name){
				var m = document.cookie.match('(^|;)\\s*'+name+'=([^;]*)');
				return m ? decodeURIComponent(m[2]) : '';
			}

			function setCookie(name,val,days){
				var d = new Date();
				d.setTime(d.getTime()+(days*86400000));
				document.cookie = name+'='+encodeURIComponent(val)+';expires='+d.toUTCString()+';path=/;SameSite=Lax';
			}

			var banner = document.getElementById('kaiko-consent-banner');
			var modal  = document.getElementById('kaiko-consent-modal');

			if(!banner||!modal) return;

			// Show banner if no consent cookie
			if(!getCookie(COOKIE)){
				banner.style.display='';
			}

			// Accept All
			document.getElementById('kaiko-consent-accept').addEventListener('click',function(){
				setCookie(COOKIE,'all',DAYS);
				banner.style.display='none';
			});

			// Manage preferences — open modal
			document.getElementById('kaiko-consent-manage').addEventListener('click',function(){
				banner.style.display='none';
				openModal();
			});

			// Close modal on overlay click
			document.getElementById('kaiko-consent-overlay').addEventListener('click',function(){
				modal.style.display='none';
			});

			// Save preferences
			document.getElementById('kaiko-consent-save').addEventListener('click',function(){
				var cats = ['essential'];
				var a = document.getElementById('kaiko-consent-analytics');
				var m = document.getElementById('kaiko-consent-marketing');
				if(a&&a.checked) cats.push('analytics');
				if(m&&m.checked) cats.push('marketing');
				setCookie(COOKIE,cats.join(','),DAYS);
				modal.style.display='none';
			});

			function openModal(){
				// Pre-check toggles based on current consent
				var val = getCookie(COOKIE);
				var a = document.getElementById('kaiko-consent-analytics');
				var m = document.getElementById('kaiko-consent-marketing');
				if(a) a.checked = (val==='all'||val.indexOf('analytics')!==-1);
				if(m) m.checked = (val==='all'||val.indexOf('marketing')!==-1);
				modal.style.display='';
			}

			// Expose for footer "Cookie Settings" link
			window.kaikoOpenConsentModal = openModal;
		})();
		</script>
		<?php
	}
}

/**
 * Global helper — check consent for a given category.
 *
 * @param string $category One of: essential, analytics, marketing.
 * @return bool
 */
function kaiko_has_consent( string $category ): bool {
	return Kaiko_GDPR::has_consent( $category );
}
