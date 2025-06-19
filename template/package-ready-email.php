<?php
/**
 * Package Ready for shipment email
 *
 * @package WooCommerce Package Ready for shipment
 */

if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_email_header', $email_heading, $email);
?>

<p><?php printf( esc_html( pll__( 'Hi %s,' ) ), esc_html( $order->get_billing_first_name() ) ); ?></p>

<p>
<?php 
    printf(
        esc_html( pll__( 'Your order #%d is ready for shipment!' ) ),
        esc_html( $order->get_order_number() )
    ); 
?>
</p>

<h2><?php echo esc_html( pll__( 'Order Details' ) ); ?></h2>

<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
    <thead>
        <tr>
            <th style="text-align: left; border-bottom: 1px solid #ddd;"><?php echo esc_html( pll__( 'Product' ) ); ?></th>
            <th style="text-align: center; border-bottom: 1px solid #ddd;"><?php echo esc_html( pll__( 'Quantity' ) ); ?></th>
            <th style="text-align: right; border-bottom: 1px solid #ddd;"><?php echo esc_html( pll__( 'Price' ) ); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            ?>
            <tr class="<?php echo esc_attr(apply_filters('woocommerce_order_item_class', 'order_item', $item, $order)); ?>">
                <td class="td" style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
                    <?php echo esc_html($item->get_name()); ?>
                </td>
                <td class="td" style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                    <?php echo esc_html($item->get_quantity()); ?>
                </td>
                <td class="td" style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                    <?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?>
                </td>
            </tr>
            <?php
        }
        ?>
    </tbody>
    <tfoot>
        <?php
        $totals = $order->get_order_item_totals();
        if ($totals) {
            foreach ($totals as $total) {
                ?>
                <tr>
                    <th class="td" scope="row" colspan="2" style="text-align:right; border: 1px solid #eee;"><?php echo esc_html($total['label']); ?></th>
                    <td class="td" style="text-align:left; border: 1px solid #eee;"><?php echo wp_kses_post($total['value']); ?></td>
                </tr>
                <?php
            }
        }
        ?>
    </tfoot>
</table>
<br/>
<p><?php echo esc_html( pll__( 'Thank you for shopping with us!' ) ); ?></p>

<?php
/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ($additional_content) {
    echo wp_kses_post(wpautop(wptexturize($additional_content)));
}
do_action('woocommerce_email_footer', $email);