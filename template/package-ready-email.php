<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<?php do_action( 'woocommerce_email_header', esc_html__( 'Your Package Is Ready For Shipment', 'woocommerce-package-ready' ), $email ); ?>

<p>
    <?php 
    printf(
        esc_html__( 'Your order #%d is ready for shipment!', 'woocommerce-package-ready' ), 
        esc_html( $order->get_order_number() )
    ); 
    ?>
</p>

<h2><?php esc_html_e( 'Order Details', 'woocommerce-package-ready' ); ?></h2>

<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="text-align: left; border-bottom: 1px solid #ddd;"><?php esc_html_e( 'Product', 'woocommerce-package-ready' ); ?></th>
            <th style="text-align: center; border-bottom: 1px solid #ddd;"><?php esc_html_e( 'Quantity', 'woocommerce-package-ready' ); ?></th>
            <th style="text-align: right; border-bottom: 1px solid #ddd;"><?php esc_html_e( 'Price', 'woocommerce-package-ready' ); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ( $order->get_items() as $item_id => $item ) : ?>
            <?php
            $product = $item->get_product();
            ?>
            <tr>
                <td><?php echo esc_html( $item->get_name() ); ?></td>
                <td style="text-align: center;"><?php echo esc_html( $item->get_quantity() ); ?></td>
                <td style="text-align: right;"><?php echo wc_price( $order->get_item_total( $item, true ) ); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p>
    <strong><?php esc_html_e( 'Total:', 'woocommerce-package-ready' ); ?></strong> 
    <?php echo wc_price( $order->get_total() ); ?>
</p>

<p><?php esc_html_e( 'Thank you for shopping with us!', 'woocommerce-package-ready' ); ?></p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
