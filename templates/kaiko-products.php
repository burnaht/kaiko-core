<?php
/**
 * Template Name: KAIKO Products
 * Description: Products showcase page for KAIKO Products.
 *
 * @package KaikoCore
 */

defined( 'ABSPATH' ) || exit;

get_header();
do_action( 'kaiko_before_content' );

// For now, fall through to the existing page content.
// The Products page currently uses its own inline template.
// This file will be built out in a future iteration.

the_content();

get_footer();
