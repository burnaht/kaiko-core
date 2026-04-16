<?php
/**
 * SEO Module.
 *
 * Outputs meta tags (title, description, Open Graph, Twitter Card, canonical)
 * via wp_head, adds a meta box for custom SEO fields on pages and products,
 * generates an XML sitemap at /sitemap.xml, and filters robots.txt.
 *
 * @package KaikoCore
 */

defined( 'ABSPATH' ) || exit;

class Kaiko_SEO {

	/** @var string Site brand appended to titles. */
	private const BRAND = 'KAIKO — Wholesale Reptile Supplies';

	/** @var string Default OG image path relative to plugin URL. */
	private const DEFAULT_OG_IMAGE = 'assets/images/kaiko-og-default.jpg';

	/** @var int Max length for auto-generated descriptions. */
	private const DESC_MAX_LENGTH = 160;

	/**
	 * Register hooks.
	 */
	public function init(): void {
		// Tell WordPress we manage the <title> tag.
		add_theme_support( 'title-tag' );
		add_filter( 'pre_get_document_title', [ $this, 'document_title' ], 20 );

		// Meta tags in <head>.
		add_action( 'wp_head', [ $this, 'output_meta_tags' ], 1 );

		// Admin meta box.
		add_action( 'add_meta_boxes', [ $this, 'register_meta_box' ] );
		add_action( 'save_post', [ $this, 'save_meta_box' ], 10, 2 );

		// XML sitemap rewrite.
		add_action( 'init', [ $this, 'add_sitemap_rewrite' ] );
		add_action( 'template_redirect', [ $this, 'serve_sitemap' ] );

		// robots.txt.
		add_filter( 'robots_txt', [ $this, 'filter_robots_txt' ], 10, 2 );
	}

	/* =================================================================
	   Title Tag
	   ================================================================= */

	/**
	 * Build the <title> string.
	 *
	 * Format: "Page Name | KAIKO — Wholesale Reptile Supplies"
	 * Front page uses just the brand.
	 *
	 * @param string $title Existing title.
	 * @return string
	 */
	public function document_title( string $title ): string {
		if ( is_front_page() || is_home() ) {
			return self::BRAND;
		}

		$custom = $this->get_meta_title();
		if ( $custom ) {
			return $custom . ' | ' . self::BRAND;
		}

		$page_name = $this->get_page_name();
		if ( $page_name ) {
			return $page_name . ' | ' . self::BRAND;
		}

		return $title;
	}

	/* =================================================================
	   Meta Tags
	   ================================================================= */

	/**
	 * Output meta description, Open Graph, Twitter Card, and canonical.
	 */
	public function output_meta_tags(): void {
		$description = $this->get_description();
		$og_title    = $this->get_og_title();
		$og_image    = $this->get_og_image();
		$canonical   = $this->get_canonical();
		$og_type     = $this->get_og_type();

		// Meta description.
		if ( $description ) {
			printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
		}

		// Canonical URL.
		if ( $canonical ) {
			printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( $canonical ) );
		}

		// Open Graph.
		printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $og_title ) );
		printf( '<meta property="og:type" content="%s" />' . "\n", esc_attr( $og_type ) );
		if ( $description ) {
			printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $description ) );
		}
		if ( $canonical ) {
			printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( $canonical ) );
		}
		if ( $og_image ) {
			printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $og_image ) );
		}
		echo '<meta property="og:site_name" content="KAIKO Products" />' . "\n";

		// Twitter Card.
		echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
		printf( '<meta name="twitter:title" content="%s" />' . "\n", esc_attr( $og_title ) );
		if ( $description ) {
			printf( '<meta name="twitter:description" content="%s" />' . "\n", esc_attr( $description ) );
		}
		if ( $og_image ) {
			printf( '<meta name="twitter:image" content="%s" />' . "\n", esc_url( $og_image ) );
		}
	}

	/* =================================================================
	   Data Helpers
	   ================================================================= */

	/**
	 * Get custom meta title for the current post.
	 */
	private function get_meta_title(): string {
		if ( ! is_singular() ) {
			return '';
		}
		return (string) get_post_meta( get_the_ID(), 'kaiko_meta_title', true );
	}

	/**
	 * Get the page name for the title tag.
	 */
	private function get_page_name(): string {
		if ( is_singular() ) {
			return get_the_title();
		}
		if ( is_post_type_archive( 'product' ) || ( function_exists( 'is_shop' ) && is_shop() ) ) {
			return 'Products';
		}
		if ( is_category() || is_tag() || is_tax() ) {
			return single_term_title( '', false ) ?: '';
		}
		if ( is_search() ) {
			return 'Search Results';
		}
		if ( is_404() ) {
			return 'Page Not Found';
		}
		return '';
	}

	/**
	 * Get meta description — custom field first, then auto-generated.
	 */
	private function get_description(): string {
		if ( is_singular() ) {
			$custom = (string) get_post_meta( get_the_ID(), 'kaiko_meta_description', true );
			if ( $custom ) {
				return $custom;
			}

			$post = get_post();
			if ( $post && $post->post_content ) {
				$text = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
				$text = preg_replace( '/\s+/', ' ', trim( $text ) );
				if ( mb_strlen( $text ) > self::DESC_MAX_LENGTH ) {
					$text = mb_substr( $text, 0, self::DESC_MAX_LENGTH - 1 ) . "\u{2026}";
				}
				return $text;
			}
		}

		if ( is_post_type_archive( 'product' ) || ( function_exists( 'is_shop' ) && is_shop() ) ) {
			return 'Browse wholesale reptile supplies from KAIKO — premium products for trade and retail customers.';
		}

		return '';
	}

	/**
	 * Get title for OG/Twitter tags.
	 */
	private function get_og_title(): string {
		$custom = $this->get_meta_title();
		if ( $custom ) {
			return $custom;
		}

		$page_name = $this->get_page_name();
		return $page_name ?: 'KAIKO Products';
	}

	/**
	 * Get OG image URL.
	 *
	 * Priority: product featured image → post featured image → default.
	 */
	private function get_og_image(): string {
		if ( is_singular() ) {
			$thumb_id = get_post_thumbnail_id();
			if ( $thumb_id ) {
				$url = wp_get_attachment_image_url( $thumb_id, 'large' );
				if ( $url ) {
					return $url;
				}
			}
		}

		// Default fallback image.
		$default = KAIKO_CORE_URL . self::DEFAULT_OG_IMAGE;
		return $default;
	}

	/**
	 * Get canonical URL.
	 */
	private function get_canonical(): string {
		if ( is_singular() ) {
			return (string) wp_get_canonical_url();
		}
		if ( is_post_type_archive() ) {
			return (string) get_post_type_archive_link( get_queried_object()->name ?? 'product' );
		}
		if ( is_tax() || is_category() || is_tag() ) {
			$term = get_queried_object();
			return $term ? (string) get_term_link( $term ) : '';
		}
		if ( is_home() || is_front_page() ) {
			return home_url( '/' );
		}
		return '';
	}

	/**
	 * Get OG type.
	 */
	private function get_og_type(): string {
		if ( is_singular( 'product' ) ) {
			return 'product';
		}
		if ( is_singular() ) {
			return 'article';
		}
		return 'website';
	}

	/* =================================================================
	   Admin Meta Box
	   ================================================================= */

	/**
	 * Register the SEO meta box on page and product edit screens.
	 */
	public function register_meta_box(): void {
		add_meta_box(
			'kaiko_seo_meta',
			'KAIKO SEO',
			[ $this, 'render_meta_box' ],
			[ 'page', 'product' ],
			'normal',
			'low'
		);
	}

	/**
	 * Render the meta box fields.
	 *
	 * @param \WP_Post $post Current post.
	 */
	public function render_meta_box( \WP_Post $post ): void {
		$meta_title = get_post_meta( $post->ID, 'kaiko_meta_title', true );
		$meta_desc  = get_post_meta( $post->ID, 'kaiko_meta_description', true );

		wp_nonce_field( 'kaiko_seo_save', 'kaiko_seo_nonce' );
		?>
		<p>
			<label for="kaiko_meta_title"><strong>Meta Title</strong></label><br>
			<input type="text" id="kaiko_meta_title" name="kaiko_meta_title"
				   value="<?php echo esc_attr( $meta_title ); ?>"
				   style="width:100%;" maxlength="70"
				   placeholder="Custom title (defaults to page title)">
			<span class="description">Appended with " | <?php echo esc_html( self::BRAND ); ?>"</span>
		</p>
		<p>
			<label for="kaiko_meta_description"><strong>Meta Description</strong></label><br>
			<textarea id="kaiko_meta_description" name="kaiko_meta_description"
					  rows="3" style="width:100%;" maxlength="160"
					  placeholder="Custom description (auto-generated from content if empty)"
			><?php echo esc_textarea( $meta_desc ); ?></textarea>
			<span class="description">Max 160 characters. Used for search results and social sharing.</span>
		</p>
		<?php
	}

	/**
	 * Save meta box values.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	public function save_meta_box( int $post_id, \WP_Post $post ): void {
		if ( ! isset( $_POST['kaiko_seo_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( $_POST['kaiko_seo_nonce'], 'kaiko_seo_save' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = [ 'kaiko_meta_title', 'kaiko_meta_description' ];
		foreach ( $fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				$value = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
				if ( $value ) {
					update_post_meta( $post_id, $field, $value );
				} else {
					delete_post_meta( $post_id, $field );
				}
			}
		}
	}

	/* =================================================================
	   XML Sitemap
	   ================================================================= */

	/**
	 * Add rewrite rule for /sitemap.xml.
	 */
	public function add_sitemap_rewrite(): void {
		add_rewrite_rule( 'sitemap\.xml$', 'index.php?kaiko_sitemap=1', 'top' );
		add_filter( 'query_vars', function ( array $vars ): array {
			$vars[] = 'kaiko_sitemap';
			return $vars;
		} );
	}

	/**
	 * Serve the XML sitemap when the query var is present.
	 */
	public function serve_sitemap(): void {
		if ( ! get_query_var( 'kaiko_sitemap' ) ) {
			return;
		}

		header( 'Content-Type: application/xml; charset=UTF-8' );
		header( 'X-Robots-Tag: noindex' );

		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

		// Published pages (public KAIKO pages + legal pages).
		$pages = get_posts( [
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		] );

		foreach ( $pages as $page ) {
			// Skip the My Account page — it's behind login.
			$template = get_post_meta( $page->ID, '_wp_page_template', true );
			if ( 'kaiko-my-account.php' === $template ) {
				continue;
			}
			$this->sitemap_url_entry(
				get_permalink( $page ),
				get_post_modified_time( 'Y-m-d', true, $page ),
				$this->sitemap_page_priority( $template ),
				'monthly'
			);
		}

		// Published WooCommerce products.
		if ( post_type_exists( 'product' ) ) {
			// Shop page.
			$shop_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : 0;
			if ( $shop_id && $shop_id > 0 ) {
				$this->sitemap_url_entry(
					get_permalink( $shop_id ),
					get_post_modified_time( 'Y-m-d', true, $shop_id ),
					'0.8',
					'weekly'
				);
			}

			$products = get_posts( [
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			] );

			foreach ( $products as $product ) {
				$this->sitemap_url_entry(
					get_permalink( $product ),
					get_post_modified_time( 'Y-m-d', true, $product ),
					'0.6',
					'weekly'
				);
			}
		}

		echo '</urlset>' . "\n";
		exit;
	}

	/**
	 * Output a single <url> entry.
	 *
	 * @param string $loc        URL.
	 * @param string $lastmod    Last modified date (Y-m-d).
	 * @param string $priority   Priority (0.0–1.0).
	 * @param string $changefreq Change frequency.
	 */
	private function sitemap_url_entry( string $loc, string $lastmod, string $priority, string $changefreq ): void {
		echo '  <url>' . "\n";
		echo '    <loc>' . esc_url( $loc ) . '</loc>' . "\n";
		if ( $lastmod ) {
			echo '    <lastmod>' . esc_html( $lastmod ) . '</lastmod>' . "\n";
		}
		echo '    <changefreq>' . esc_html( $changefreq ) . '</changefreq>' . "\n";
		echo '    <priority>' . esc_html( $priority ) . '</priority>' . "\n";
		echo '  </url>' . "\n";
	}

	/**
	 * Determine sitemap priority based on template.
	 *
	 * @param string $template Template slug.
	 * @return string
	 */
	private function sitemap_page_priority( string $template ): string {
		return match ( $template ) {
			'kaiko-homepage.php' => '1.0',
			'kaiko-products.php' => '0.9',
			'kaiko-about.php'    => '0.7',
			'kaiko-contact.php'  => '0.7',
			default              => '0.5',
		};
	}

	/* =================================================================
	   robots.txt
	   ================================================================= */

	/**
	 * Filter the virtual robots.txt output.
	 *
	 * @param string $output  Existing robots.txt content.
	 * @param bool   $public  Whether the site is public.
	 * @return string
	 */
	public function filter_robots_txt( string $output, bool $public ): string {
		if ( ! $public ) {
			return $output;
		}

		$sitemap_url = home_url( '/sitemap.xml' );
		$additions   = "\n";
		$additions  .= "# KAIKO SEO\n";
		$additions  .= "Disallow: /wp-admin/\n";
		$additions  .= "Disallow: /my-account/\n";
		$additions  .= "Allow: /wp-admin/admin-ajax.php\n";
		$additions  .= "\n";
		$additions  .= "Sitemap: {$sitemap_url}\n";

		return $output . $additions;
	}
}
