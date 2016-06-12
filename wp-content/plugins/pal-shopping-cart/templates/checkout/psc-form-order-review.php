<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<?php
$psc_card_handler_obj = new PSC_Cart_Handler();
$PSC_Common_Function = new PSC_Common_Function();
$cart_item_array = $psc_card_handler_obj->contents();
$currency_code = $PSC_Common_Function->get_psc_currency();
$currency_symbol = Paypal_Shopping_Cart_General_Setting::get_paypal_shopping_cart_currency_symbol($currency_code);
$coupon_cart_discount = $PSC_Common_Function->get_cart_total_discount();
$coupon_code = $PSC_Common_Function->get_cart_total_coupon_code();
$total_checkout_price = 0;
?>
<?php if (is_array($cart_item_array)) { ?>
    <table class="psc-checkout-review-order-table">
        <thead>
            <tr>
                <th class="psc-product-name">Product</th>
                <th class="psc-product-total">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cart_item_array as $key => $value) { ?>                
                <tr class="psc_cart_item">
                    <td class="psc_product-name">
                        <strong class="psc_product-quantity"><?php echo esc_html($value['name']) . ' × ' . esc_html($value['qty']); ?></strong>	
                    </td>
                    <td class="psc_product-total">
                        <span class="amount"><?php echo esc_html($currency_symbol) . '' . number_format(esc_html($value['subtotal']), 2); ?></span>
                    </td>
                </tr>
                <?php
                $total_checkout_price = $total_checkout_price + $value['subtotal'];
            }
            ?>

        </tbody>
        <tfoot>
            <tr class="cart-subtotal">
                <th>Subtotal</th>
                <td>
                    <span class="amount"><?php echo esc_html($currency_symbol) . '' . number_format(esc_html($total_checkout_price), 2); ?></span>
                </td>
            </tr>
            <?php if ($coupon_cart_discount != 0) { ?>

                <tr class="cart-coupons">
                    <th>Coupon:<?php echo esc_html($coupon_code); ?></th>
                    <td>
                        <span class="amount"><?php echo '-' . esc_html($currency_symbol) . '' . number_format(esc_html($coupon_cart_discount), 2); ?></span>
                    </td>
                </tr>


            <?php }
            ?>
            <tr class="order-total">
                <th>Total</th>
                <td>
                    <strong><span class="amount"><?php echo esc_html($currency_symbol) . '' . number_format(esc_html($total_checkout_price - $coupon_cart_discount), 2); ?></span></strong> 
                </td>
            </tr>
        </tfoot>
    </table>
    <div id="psc-payment" class="psc-checkout-payment">        
        <?php do_action('psc_get_all_enable_payment_methods'); ?>
    </div>

<?php } ?>