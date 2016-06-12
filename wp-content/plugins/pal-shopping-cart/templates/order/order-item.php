<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $post;

$PSC_Common_Function = new PSC_Common_Function();
$get_order_item_details = $PSC_Common_Function->get_order_item_details($post->ID);
$final_total = $get_order_item_details['final_total'];
$currency_symbol = $get_order_item_details['currency_symbol'];
$order_cart_discount = $get_order_item_details['order_cart_discount'];
$order_cart_discount_coupon_code = $get_order_item_details['order_cart_discount_coupon_code'];

unset($get_order_item_details['final_total']);
unset($get_order_item_details['currency_symbol']);
unset($get_order_item_details['order_cart_discount']);
unset($get_order_item_details['order_cart_discount_coupon_code']);
?>
<div class="psc_order_items_wrapper psc-order-items-editable">
    <div class='wrap' >
        <table class="widefat" cellspacing="0" id="psc_order_items">
            <thead>
                <tr>
                    <th class="order_item">Item</th>
                    <th class="order_cost">Cost</th>
                    <th class="order_qty">Qty</th>
                    <th class="order_total">Total</th>
                </tr>
            </thead>
            <tbody>                    
                <?php
                foreach ($get_order_item_details as $key => $value) {
                    $order_item_output .='<tr>';
                    $order_item_output .='<td class="thumb"><img src="' . esc_url($value['url']) . '" height="25" width="25"><br>' . esc_html($value['name']) . '</td>';
                    $order_item_output .='<td>' . esc_html($currency_symbol) . '' . number_format(esc_html($value['price']), 2) . '</td>';
                    $order_item_output .='<td>' . esc_html($value['qty']) . '</td>';
                    $order_item_output .='<td>' . esc_html($currency_symbol) . '' . number_format(esc_html($value['subtotal']), 2) . '</td>';
                    $order_item_output .='</tr>';
                }
                echo $order_item_output;
                ?>                   
            </tbody>            
        </table>
        <table class="widefat" cellspacing="0" id="psc_order_total_detail">
            <tfoot class="">
                <tr class="psc_discount">
                    <td class="lable">Subtotal:</td>
                    <td class="amount"><?php echo esc_html($currency_symbol) . '' . number_format(esc_html($final_total), 2); ?></td>                    
                </tr>
                <tr class="psc_discount">
                    <td class="lable">Discount: <strong><?php echo ($order_cart_discount_coupon_code) ? esc_html($order_cart_discount_coupon_code) : '' ?></strong></td>
                    <td class="amount"><?php echo esc_html($currency_symbol) . '' . number_format(($order_cart_discount) ? esc_html($order_cart_discount) : esc_html('0'), 2); ?></td>                    
                </tr>
                <tr class="psc_shipping">
                    <td class="lable">Shipping:</td>
                    <td class="amount"><?php echo esc_html($currency_symbol . '0.00'); ?></td>                    
                </tr>
                <tr class="psc_order_total">
                    <td class="lable">Order Total:</td>
                    <td class="amount"><?php echo esc_html($currency_symbol) . '' . number_format(esc_html($final_total - $order_cart_discount), 2); ?></td>                    
                </tr>                            
            </tfoot>
        </table>
    </div>
</div>