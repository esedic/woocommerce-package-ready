<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>

= <?php esc_html_e( 'Your Package Is Ready For Shipment', 'woocommerce-package-ready' ); ?> =

<?php 
printf(
    esc_html__( 'Your order #%d is ready for shipment!', 'woocommerce-package-ready' ), 
    esc_html( $order->get_order_number() )
); 
?>

== <?php esc_html_e( 'Order Details', 'woocommerce-package-ready' ); ?> ==

<?php foreach ( $order->get_items() as $item ) : ?>
- <?php echo esc_html( $item->get_name() ); ?> (<?php esc_html_e( 'Quantity:', 'woocommerce-package-ready' ); ?> <?php echo esc_html( $item->get_quantity() ); ?>)
  <?php esc_html_e( 'Price:', 'woocommerce-package-ready' ); ?> <?php echo wc_price( $order->get_item_total( $item, true ) ); ?>

<?php endforeach; ?>

== <?php esc_html_e( 'Total:', 'woocommerce-package-ready' ); ?> == 
<?php echo wc_price( $order->get_total() ); ?>


<?php esc_html_e( 'Thank you for shopping with us!', 'woocommerce-package-ready' ); ?>
