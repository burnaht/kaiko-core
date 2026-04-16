<?php
/**
 * Template Name: KAIKO Legal Page
 * Description: Clean legal content template for policy and terms pages.
 *
 * @package KaikoCore
 */

defined( 'ABSPATH' ) || exit;

get_header(); ?>

<style>
/* ── KAIKO Legal Page Styles ── */
.kaiko-legal * { box-sizing: border-box; }
.kaiko-legal img { max-width: 100%; display: block; }
.kaiko-legal a { color: var(--teal); text-decoration: underline; }
.kaiko-legal a:hover { color: var(--deep-teal); }
.kaiko-legal {
  font-family: 'Inter', -apple-system, system-ui, 'Segoe UI', sans-serif;
  color: #1a1a1a;
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
  --teal: #1a5c52;
  --deep-teal: #134840;
  --lime: #b8d435;
  --gold: #c89b3c;
  --cream: #faf8f3;
  --white: #ffffff;
  --dark: #1a1a1a;
  --light-gray: #f5f5f5;
  --medium-gray: #888888;
  --border: #e5e5e5;
}

.kaiko-legal .k-hero {
  background-color: var(--cream);
  padding: 5rem 2rem 3rem;
  text-align: center;
}
.kaiko-legal .k-hero-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--teal);
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-bottom: 1rem;
}
.kaiko-legal .k-hero h1 {
  font-size: 2.5rem;
  font-weight: 700;
  color: var(--deep-teal);
  margin-bottom: 1rem;
  line-height: 1.2;
}
.kaiko-legal .k-hero p {
  font-size: 1rem;
  color: var(--medium-gray);
  max-width: 600px;
  margin: 0 auto;
}

.kaiko-legal .k-legal-body {
  max-width: 820px;
  margin: 0 auto;
  padding: 3rem 2rem 5rem;
}
.kaiko-legal .k-legal-body h2 {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--deep-teal);
  margin: 2.5rem 0 1rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border);
}
.kaiko-legal .k-legal-body h2:first-child {
  margin-top: 0;
  padding-top: 0;
  border-top: none;
}
.kaiko-legal .k-legal-body h3 {
  font-size: 1.15rem;
  font-weight: 600;
  color: var(--dark);
  margin: 1.5rem 0 0.75rem;
}
.kaiko-legal .k-legal-body p {
  font-size: 1rem;
  color: var(--dark);
  line-height: 1.8;
  margin-bottom: 1rem;
}
.kaiko-legal .k-legal-body ul,
.kaiko-legal .k-legal-body ol {
  padding-left: 1.5rem;
  margin-bottom: 1rem;
}
.kaiko-legal .k-legal-body li {
  font-size: 1rem;
  color: var(--dark);
  line-height: 1.8;
  margin-bottom: 0.5rem;
}
.kaiko-legal .k-legal-body strong {
  font-weight: 600;
  color: var(--dark);
}
.kaiko-legal .k-legal-meta {
  font-size: 0.875rem;
  color: var(--medium-gray);
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid var(--border);
}
.kaiko-legal .k-legal-contact {
  margin-top: 2rem;
  padding: 2rem;
  background-color: var(--cream);
  border-radius: 8px;
}
.kaiko-legal .k-legal-contact h3 {
  margin-top: 0;
}

@media (max-width: 1024px) {
  .kaiko-legal .k-hero h1 { font-size: 2rem; }
}
@media (max-width: 640px) {
  .kaiko-legal .k-hero { padding: 3rem 1.5rem 2rem; }
  .kaiko-legal .k-hero h1 { font-size: 1.5rem; }
  .kaiko-legal .k-legal-body { padding: 2rem 1.5rem 3rem; }
  .kaiko-legal .k-legal-body h2 { font-size: 1.25rem; }
}
</style>

<?php do_action( 'kaiko_before_content' ); ?>

<div class="kaiko-legal">

<section class="k-hero">
  <div class="k-hero-label"><?php esc_html_e( 'Legal', 'kaiko-core' ); ?></div>
  <h1><?php the_title(); ?></h1>
</section>

<div class="k-legal-body">
  <?php
  while ( have_posts() ) :
    the_post();
    the_content();
  endwhile;
  ?>
</div>

</div><!-- .kaiko-legal -->

<?php do_action( 'kaiko_after_content' ); ?>

<?php get_footer(); ?>
