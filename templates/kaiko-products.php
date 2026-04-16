<?php
/**
 * Template Name: KAIKO Products
 * Description: Products showcase page for KAIKO Products.
 *
 * @package KaikoCore
 */

defined( 'ABSPATH' ) || exit;

get_header(); ?>

<style>
/* ── KAIKO Products Page Styles ── */
:root {
  --k-teal: #1a5c52;
  --k-deep-teal: #134840;
  --k-light-teal: #2d8073;
  --k-lime: #b8d435;
  --k-lime-soft: #c8df5c;
  --k-gold: #c89b3c;
  --k-dark: #1a1a1a;
  --k-charcoal: #292524;
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

/* Reset within template */
.kaiko-products * { box-sizing: border-box; }
.kaiko-products img { max-width: 100%; display: block; }
.kaiko-products a { text-decoration: none; color: inherit; }
.kaiko-products {
  font-family: var(--k-font);
  color: var(--k-dark);
  line-height: 1.65;
  -webkit-font-smoothing: antialiased;
}

/* Scroll reveal */
.kaiko-products .fade-in {
  opacity: 0;
  transform: translateY(24px);
  transition: opacity 0.7s var(--k-ease), transform 0.7s var(--k-ease);
}
.kaiko-products .fade-in.visible {
  opacity: 1;
  transform: translateY(0);
}

/* ── Hero ── */
.kaiko-products .k-hero {
  background-color: var(--k-cream);
  padding: 6rem 2rem;
  text-align: center;
}
.kaiko-products .k-hero-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--k-teal);
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-bottom: 1rem;
}
.kaiko-products .k-hero h1 {
  font-family: var(--k-font);
  font-size: clamp(2rem, 4vw, 3rem);
  font-weight: 700;
  color: var(--k-deep-teal);
  margin-bottom: 1rem;
  line-height: 1.2;
}
.kaiko-products .k-hero p {
  font-size: 1.1rem;
  color: var(--k-stone-500);
  max-width: 600px;
  margin: 0 auto;
}

/* ── Filters ── */
.kaiko-products .k-filters {
  padding: 40px clamp(1.5rem, 4vw, 4rem) 0;
}
.kaiko-products .k-filters-inner {
  max-width: 1200px;
  margin: 0 auto;
}
.kaiko-products .k-filters-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  justify-content: center;
}
.kaiko-products .k-filter-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 10px 20px;
  font-size: 0.82rem;
  font-weight: 500;
  font-family: var(--k-font);
  color: var(--k-stone-600);
  background: var(--k-white);
  border: 1px solid var(--k-stone-200);
  border-radius: var(--k-r-pill);
  cursor: pointer;
  transition: all var(--k-dur) var(--k-ease);
  text-decoration: none;
}
.kaiko-products .k-filter-btn:hover {
  border-color: var(--k-stone-300);
  background: var(--k-stone-50);
  color: var(--k-dark);
}
.kaiko-products .k-filter-btn.active {
  background: var(--k-teal);
  color: var(--k-white);
  border-color: var(--k-teal);
}
.kaiko-products .k-filter-btn.active:hover {
  background: var(--k-deep-teal);
  border-color: var(--k-deep-teal);
}
.kaiko-products .k-filter-count {
  font-size: 0.72rem;
  font-weight: 600;
  opacity: 0.7;
}
.kaiko-products .k-filter-btn.active .k-filter-count {
  opacity: 0.85;
}
.kaiko-products .k-results-summary {
  text-align: center;
  font-size: 0.88rem;
  color: var(--k-stone-500);
  margin-top: 20px;
}

/* ── Product Grid ── */
.kaiko-products .k-products-section {
  padding: 40px clamp(1.5rem, 4vw, 4rem) 90px;
}
.kaiko-products .k-products-inner {
  max-width: 1200px;
  margin: 0 auto;
}
.kaiko-products .k-products-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

/* ── Product Card ── */
.kaiko-products .k-product-card {
  display: block;
  background: var(--k-white);
  border: 1px solid var(--k-stone-200);
  border-radius: var(--k-r-md);
  overflow: hidden;
  transition: box-shadow var(--k-dur), transform var(--k-dur);
  cursor: pointer;
}
.kaiko-products .k-product-card:hover {
  box-shadow: 0 6px 24px rgba(0,0,0,0.06);
  transform: translateY(-3px);
}
.kaiko-products .k-product-img {
  aspect-ratio: 1;
  background: var(--k-stone-100);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}
.kaiko-products .k-product-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.kaiko-products .k-product-placeholder {
  font-size: 0.8rem;
  color: var(--k-stone-400);
}
.kaiko-products .k-product-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  font-size: 0.65rem;
  font-weight: 600;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  padding: 4px 10px;
  border-radius: var(--k-r-pill);
}
.kaiko-products .badge-new {
  background: var(--k-lime);
  color: var(--k-dark);
}
.kaiko-products .badge-sale {
  background: var(--k-gold);
  color: #fff;
}
.kaiko-products .k-product-info {
  padding: 16px 18px 18px;
}
.kaiko-products .k-product-info h3 {
  font-size: 0.88rem;
  font-weight: 600;
  color: var(--k-dark);
  margin-bottom: 6px;
  line-height: 1.3;
}
.kaiko-products .k-product-info .price {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--k-teal);
}
.kaiko-products .k-product-info .price del {
  text-decoration: line-through;
  color: var(--k-stone-400);
  font-weight: 400;
  margin-left: 6px;
  font-size: 0.78rem;
}

/* ── Empty State ── */
.kaiko-products .k-empty-state {
  text-align: center;
  padding: 80px 2rem;
}
.kaiko-products .k-empty-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 24px;
  color: var(--k-stone-300);
}
.kaiko-products .k-empty-icon svg {
  width: 100%;
  height: 100%;
}
.kaiko-products .k-empty-state h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--k-dark);
  margin-bottom: 8px;
}
.kaiko-products .k-empty-state p {
  font-size: 0.95rem;
  color: var(--k-stone-500);
  max-width: 480px;
  margin: 0 auto 24px;
  line-height: 1.7;
}
.kaiko-products .k-btn-primary {
  display: inline-block;
  padding: 12px 28px;
  background: var(--k-teal);
  color: var(--k-white);
  border-radius: var(--k-r-sm);
  font-size: 0.85rem;
  font-weight: 600;
  transition: background var(--k-dur), transform var(--k-dur);
}
.kaiko-products .k-btn-primary:hover {
  background: var(--k-deep-teal);
  transform: translateY(-1px);
  color: var(--k-white);
}

/* ── Responsive ── */
@media (max-width: 1024px) {
  .kaiko-products .k-hero h1 { font-size: 2.25rem; }
  .kaiko-products .k-products-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
  .kaiko-products .k-hero { padding: 3rem 1.5rem; }
  .kaiko-products .k-hero h1 { font-size: 1.75rem; }
  .kaiko-products .k-hero p { font-size: 1rem; }
  .kaiko-products .k-filters { padding: 24px 1.5rem 0; }
  .kaiko-products .k-filters-bar {
    justify-content: flex-start;
    overflow-x: auto;
    flex-wrap: nowrap;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 8px;
  }
  .kaiko-products .k-filter-btn { flex-shrink: 0; }
  .kaiko-products .k-products-section { padding: 24px 1.5rem 60px; }
  .kaiko-products .k-products-grid { grid-template-columns: 1fr; }
}
</style>

<?php do_action( 'kaiko_before_content' ); ?>

<div class="kaiko-products">

<?php
// ── Get active category filter ──
$active_cat = isset( $_GET['category'] ) ? sanitize_title( wp_unslash( $_GET['category'] ) ) : '';
$page_url   = get_permalink();

// ── Fetch product categories ──
$categories = get_terms( [
	'taxonomy'   => 'product_cat',
	'hide_empty' => true,
	'orderby'    => 'name',
	'order'      => 'ASC',
] );
if ( is_wp_error( $categories ) ) {
	$categories = [];
}

// ── Total product count for "All Products" button ──
$all_query = new WC_Product_Query( [
	'status' => 'publish',
	'limit'  => -1,
	'return' => 'ids',
] );
$total_all = count( $all_query->get_products() );

// ── Build filtered product query ──
$query_args = [
	'status'  => 'publish',
	'limit'   => 24,
	'orderby' => 'date',
	'order'   => 'DESC',
	'return'  => 'objects',
];

if ( $active_cat ) {
	$query_args['tax_query'] = [ [
		'taxonomy' => 'product_cat',
		'field'    => 'slug',
		'terms'    => $active_cat,
	] ];
}

$query    = new WC_Product_Query( $query_args );
$products = $query->get_products();
$count    = count( $products );

// ── Active category name for display ──
$active_cat_name = '';
if ( $active_cat ) {
	$term = get_term_by( 'slug', $active_cat, 'product_cat' );
	if ( $term && ! is_wp_error( $term ) ) {
		$active_cat_name = $term->name;
	}
}
?>

<!-- HERO -->
<section class="k-hero">
  <div class="k-hero-label fade-in">Our Range</div>
  <h1 class="fade-in">Products</h1>
  <p class="fade-in">Species-specific reptile supplies, designed in Britain and built to last.</p>
</section>

<!-- FILTERS -->
<section class="k-filters">
  <div class="k-filters-inner">
    <div class="k-filters-bar fade-in">
      <a href="<?php echo esc_url( $page_url ); ?>"
         class="k-filter-btn<?php echo '' === $active_cat ? ' active' : ''; ?>">
        All Products
        <span class="k-filter-count"><?php echo esc_html( $total_all ); ?></span>
      </a>
      <?php foreach ( $categories as $cat ) : ?>
        <a href="<?php echo esc_url( add_query_arg( 'category', $cat->slug, $page_url ) ); ?>"
           class="k-filter-btn<?php echo $active_cat === $cat->slug ? ' active' : ''; ?>">
          <?php echo esc_html( $cat->name ); ?>
          <span class="k-filter-count"><?php echo esc_html( $cat->count ); ?></span>
        </a>
      <?php endforeach; ?>
    </div>
    <?php if ( $active_cat_name ) : ?>
      <p class="k-results-summary fade-in">
        Showing <?php echo esc_html( $count ); ?> product<?php echo 1 !== $count ? 's' : ''; ?>
        in <strong><?php echo esc_html( $active_cat_name ); ?></strong>
      </p>
    <?php else : ?>
      <p class="k-results-summary fade-in">
        Showing <?php echo esc_html( $count ); ?> product<?php echo 1 !== $count ? 's' : ''; ?>
      </p>
    <?php endif; ?>
  </div>
</section>

<!-- PRODUCTS -->
<section class="k-products-section">
  <div class="k-products-inner">
    <?php if ( $products ) : ?>
      <div class="k-products-grid">
        <?php foreach ( $products as $product ) :
          $permalink = $product->get_permalink();
          $image_id  = $product->get_image_id();
          $image     = $image_id
              ? wp_get_attachment_image( $image_id, 'woocommerce_thumbnail', false, [ 'class' => 'k-product-thumb', 'loading' => 'lazy' ] )
              : '';
          $on_sale   = $product->is_on_sale();
          $is_new    = ( ( time() - get_post_time( 'U', true, $product->get_id() ) ) < 30 * DAY_IN_SECONDS );
        ?>
          <a href="<?php echo esc_url( $permalink ); ?>" class="k-product-card fade-in">
            <div class="k-product-img">
              <?php if ( $image ) : ?>
                <?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image is pre-escaped ?>
              <?php else : ?>
                <span class="k-product-placeholder">No Image</span>
              <?php endif; ?>
              <?php if ( $on_sale ) : ?>
                <span class="k-product-badge badge-sale">Sale</span>
              <?php elseif ( $is_new ) : ?>
                <span class="k-product-badge badge-new">New</span>
              <?php endif; ?>
            </div>
            <div class="k-product-info">
              <h3><?php echo esc_html( $product->get_name() ); ?></h3>
              <div class="price"><?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce pre-escapes price HTML ?></div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else : ?>
      <div class="k-empty-state fade-in">
        <div class="k-empty-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="11" cy="11" r="8"/>
            <path d="M21 21l-4.35-4.35"/>
          </svg>
        </div>
        <h3>No products found</h3>
        <p>We couldn't find any products<?php echo $active_cat_name ? ' in <strong>' . esc_html( $active_cat_name ) . '</strong>' : ''; ?>. Try a different category or browse all products.</p>
        <a href="<?php echo esc_url( $page_url ); ?>" class="k-btn-primary">View All Products</a>
      </div>
    <?php endif; ?>
  </div>
</section>

</div><!-- .kaiko-products -->

<script>
(function() {
  var elements = document.querySelectorAll('.kaiko-products .fade-in');
  var observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) {
        var siblings = entry.target.parentElement ? entry.target.parentElement.querySelectorAll('.fade-in') : [];
        var index = Array.from(siblings).indexOf(entry.target);
        var delay = index >= 0 ? index * 0.06 : 0;
        setTimeout(function() { entry.target.classList.add('visible'); }, delay * 1000);
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });
  elements.forEach(function(el) { observer.observe(el); });
})();
</script>

<?php get_footer(); ?>
