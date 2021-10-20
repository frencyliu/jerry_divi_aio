<?php

/**
 * Loop Add to Cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/add-to-cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     3.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

global $product;
global $woocommerce;



do_action( 'JWC_bfore_excerpt');

if (JWC_SHOW_EXCERPT_WHEN_LOOP) {
    the_excerpt();
}

do_action( 'JWC_bfore_add_to_cart_btn');

echo '<div class="jwc_loop_btn">';

if (JWC_SHOW_ADD_TO_CART_WHEN_LOOP) {
    echo apply_filters(
        'woocommerce_loop_add_to_cart_link', // WPCS: XSS ok.
        sprintf(
            '<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
            esc_url($product->add_to_cart_url()),
            esc_attr(isset($args['quantity']) ? $args['quantity'] : 1),
            'jdaio_btn-primary',
            isset($args['attributes']) ? wc_implode_html_attributes($args['attributes']) : '',
            esc_html($product->add_to_cart_text())
        ),
        $product,
        $args
    );
}

if (JWC_SHOW_DIRECT_BUY_WHEN_LOOP) {
    echo sprintf(
        '<a href="%s" class="%s" %s>%s</a>',
        esc_url(site_url() . '/checkout/?empty_cart=yes&add-to-cart=' . $product->get_ID()),
        'product_type_simple jdaio_btn-primary',
        '',
        esc_html('直接購買')
    );
}
echo '</div>';

do_action( 'JWC_after_add_to_cart_btn', $product->get_ID());

