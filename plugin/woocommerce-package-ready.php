<?php
/**
 * Plugin Name: WooCommerce Package ready for shipment
 * Plugin URI:  https://spletodrom.si
 * Description: Sets a custom WooCommerce order status "Package Ready" and sends a custom email when a WooCommerce order status changes to this status
 * Version:     1.0.0
 * Author:      Elvis Sedić
 * Author URI:  https://spletodrom.si
 * License:     GPL2
 * Text Domain: woocommerce-package-ready
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Register the custom order status
function woo_register_package_ready_status() {
    register_post_status( 'wc-package-ready', array(
        'label'                     => _x( 'Package ready for shipment', 'Order status', 'woocommerce-package-ready' ),
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'Package ready for shipment <span class="count">(%s)</span>', 'Package ready for shipment <span class="count">(%s)</span>', 'woocommerce-package-ready' ),
    ) );
}
add_action( 'init', 'woo_register_package_ready_status' );

// Add custom status to WooCommerce list
function woo_add_package_ready_status( $order_statuses ) {
    $order_statuses['wc-package-ready'] = __( 'Package ready for shipment', 'woocommerce-package-ready' );
    return $order_statuses;
}
add_filter( 'wc_order_statuses', 'woo_add_package_ready_status' );

// Add custom email class
function woo_add_package_ready_email_class($email_classes) {
    require_once('class-wc-email-package-ready.php');
    $email_classes['WC_Email_Package_ready'] = new WC_Email_Package_ready();
    return $email_classes;
}
add_filter('woocommerce_email_classes', 'woo_add_package_ready_email_class');

// Send email when order status changes to package-ready
function woo_send_package_ready_email_notification($order_id) {
    $order = wc_get_order($order_id);
    
    // Check if order exists and has the right status
    if (!$order || $order->get_status() !== 'package-ready') {
        return;
    }
    
    // Get all WC emails
    $emails = WC()->mailer()->get_emails();
    
    // Loop through email classes
    foreach ($emails as $email) {
        if ($email->id === 'WC_Email_Package_ready') {
            // Send the email
            $email->trigger($order_id, $order);
            break;
        }
    }
}
// Hook the function to status transition
add_action('woocommerce_order_status_changed', 'woo_send_package_ready_email_notification', 10, 1);