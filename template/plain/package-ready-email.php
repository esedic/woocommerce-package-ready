<?php
/**
 * Package Ready for shipment email (plain text)
 *
 * @package WooCommerce Ready for shipment
 */

if (!defined('ABSPATH')) {
    exit;
}

echo "= " . esc_html($email_heading) . " =\n\n";

echo sprintf(
    esc_html( pll__( 'Hi %s,' ) ),
    esc_html( $order->get_billing_first_name() )
) . "\n\n";

echo esc_html( pll__( 'Your order #%d is ready for shipment!' ) ) . "\n\n";

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo sprintf(
    esc_html( pll__( 'Order #%s' ) ),
    esc_html( $order->get_order_number() )
) . "\n\n";

foreach ($order->get_items() as $item_id => $item) {
    echo wp_kses_post($item->get_name()) . ' × ' . wp_kses_post($item->get_quantity()) . "\n";
}

echo "\n";

$totals = $order->get_order_item_totals();
if ($totals) {
    foreach ($totals as $total) {
        echo wp_kses_post($total['label'] . ': ' . $total['value']) . "\n";
    }
}

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo esc_html( pll__( 'Thank you for shopping with us!' ) ) . "\n\n";


/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ($additional_content) {
    echo esc_html(wp_strip_all_tags(wptexturize($additional_content)));
    echo "\n\n";
}

echo apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text'));