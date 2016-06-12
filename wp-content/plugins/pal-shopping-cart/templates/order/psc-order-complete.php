<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php get_header(); ?>

<?php do_action('psc_before_main_content'); ?>
<header class="entry-header">
    <h1 class="entry-title"><?php echo esc_html('Order Received', 'pal-shopping-cart'); ?></h1>
</header>

<div class="entry-content">
    <h1 style="color:#77a464"><?php echo esc_html('Thank you. Your order has been received.') ?></h1>
    <div class="psc-received">
        <?php
        $order = '';
        $PSC_Common_Function = new PSC_Common_Function();
        $currency_code = $PSC_Common_Function->get_psc_currency();
        $currency_symbol = Paypal_Shopping_Cart_General_Setting::get_paypal_shopping_cart_currency_symbol($currency_code);
        $coupon_cart_discount = $PSC_Common_Function->get_cart_total_discount();
        $coupon_code = $PSC_Common_Function->get_cart_total_coupon_code();
        if (isset($_GET['psc_action']) && $_GET['psc_action'] == 'pscordercomplete') {
            $order = sanitize_text_field($_GET['order']);
            $get_result_of_order_received_data = $PSC_Common_Function->get_result_of_order_received_data($order);
            if (is_array($get_result_of_order_received_data) && count($get_result_of_order_received_data) > 0) {
                ?> 
                <?php
                $psc_serialize = unserialize(trim($get_result_of_order_received_data['_psc_cart_serialize']));
                $psc_cart_serialize = unserialize($psc_serialize);
                ?>
                <ul class="psc-thankyou-order-details">
                    <li class="order">
                        Order Number:<strong><?php echo esc_html($get_result_of_order_received_data[0]['_orderid']); ?></strong>
                    </li>
                    <li class="date">
                        Date:<strong><?php echo esc_html($get_result_of_order_received_data[0]['_orderdate']); ?></strong>
                    </li>
                    <li class="total">
                        Total:<strong><span class="amount"><?php echo esc_html($currency_symbol) . '' . number_format(esc_html($get_result_of_order_received_data['_psc_cart_total']), 2, '.', ''); ?></span></strong>
                    </li>
                    <li class="method">
                        Payment Method:<strong><?php echo esc_html($get_result_of_order_received_data['_payment_method_title']); ?></strong>
                    </li>
                </ul>
                <div class="clear"></div>
                <h1>Order Details</h1>
                <table class="psc-checkout-review-order-table">
                    <thead>
                        <tr>
                            <th class="psc-product-name">Product</th>
                            <th class="psc-product-total">Total</th>
                        </tr>
                    </thead>
                    <tbody> 

                        <?php foreach ($psc_cart_serialize as $key => $value) { ?>  
                            <tr class="psc_cart_item">
                                <td class="psc_product-name">
                                    <strong class="psc_product-quantity"><?php echo esc_html($value['name']) . ' × ' . esc_html($value['qty']); ?></strong>	
                                </td>
                                <td class="psc_product-total">
                                    <span class="amount"><?php echo esc_html($currency_symbol) . '' . number_format(esc_html($value['subtotal']), 2); ?></span>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr class="cart-subtotal">
                            <th>SUBTOTAL:</th>
                            <td>
                                <span class="amount">
                                    <?php echo esc_html($currency_symbol) . '' . number_format(esc_html($get_result_of_order_received_data['_psc_cart_subtotal']), 2, '.', ''); ?>
                                </span>
                            </td>
                        </tr>
                        <?php if (isset($get_result_of_order_received_data['_order_cart_discount']) && '0' != $get_result_of_order_received_data['_order_cart_discount']) { ?>   
                            <tr class="cart-coupon">
                                <th>COUPONS:<?php echo esc_html($get_result_of_order_received_data['_order_cart_discount_coupon_code']); ?></th>
                                <td>
                                    <span class="amount">
                                        <?php echo esc_html('-') . esc_html($currency_symbol) . '' . number_format(esc_html($get_result_of_order_received_data['_order_cart_discount']), 2, '.', ''); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php } ?>

                        <tr class="cart-shipping">
                            <th>SHIPPING:</th>
                            <td>
                                <span class="shipping"><?php echo esc_html('Free Shipping'); ?></span>
                            </td>
                        </tr>
                        <tr class="cart-payment-method">
                            <th>PAYMENT METHOD:</th>
                            <td>
                                <span class="payment-method"><?php echo esc_html($get_result_of_order_received_data['_payment_method_title']); ?></span>
                            </td>
                        </tr>
                        <tr class="order-total">
                            <th>TOTAL:</th>
                            <td>
                                <strong><span class="amount"><?php echo esc_html($currency_symbol) . '' . number_format(esc_html($get_result_of_order_received_data['_psc_cart_total']), 2, '.', ''); ?></span></strong> 
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="clear"></div>
                <h1>Customer Details</h1>
                <table class="psc-checkout-review-order-table">                    
                    <tbody>                                    
                        <tr class="psc_cart_item">
                            <th class="psc-product-name">EMAIL:</th>
                            <td class="psc_product-total">
                                <span class="amount"><?php echo esc_html($get_result_of_order_received_data['_billing_email']); ?></span>
                            </td>
                        </tr>
                        <tr class="cart-subtotal">
                            <th class="psc-product-name">TELEPHONE:</th>
                            <td>
                                <span class="amount"><?php echo esc_html($get_result_of_order_received_data['_billing_phone']); ?></span>
                            </td>
                        </tr>                        
                    </tbody>
                </table>        
                <div class="psc-col2-set addresses">
                    <div class="psc-col-1">
                        <header class="title">
                            <h3>Billing Address</h3>
                        </header>
                        <address>
                            <?php echo esc_html($get_result_of_order_received_data['_shipping_company']); ?><br>
                            <?php echo esc_html($get_result_of_order_received_data['_shipping_full_name']); ?><br>
                            <?php echo esc_html($get_result_of_order_received_data['_shipping_city']); ?><br>
                            <?php echo esc_html($get_result_of_order_received_data['_shipping_address_1']) . '' . esc_html($get_result_of_order_received_data['_shipping_address_2']); ?><br>
                            <?php echo esc_html($get_result_of_order_received_data['_shipping_city']) . esc_html('-') . esc_html($get_result_of_order_received_data['_shipping_postcode']); ?><br>
                            <?php echo esc_html($get_result_of_order_received_data['_shipping_state']) . esc_html(', ') . esc_html($PSC_Common_Function->two_digit_get_countrycode_to_country($get_result_of_order_received_data['_shipping_country'])); ?>                           
                        </address>
                    </div><!-- /.col-1 -->
                    <div class="psc-col-2">
                        <header class="title">
                            <h3>Shipping Address</h3>
                        </header>
                        <address>
                            <?php echo esc_html($get_result_of_order_received_data['_shipping_company']); ?><br>
                            <?php echo esc_html($get_result_of_order_received_data['_shipping_full_name']); ?><br>
                            <?php echo esc_html($get_result_of_order_received_data['_shipping_city']); ?><br>
                            <?php echo esc_html($get_result_of_order_received_data['_shipping_address_1']) . '' . esc_html($get_result_of_order_received_data['_shipping_address_2']); ?><br>
                            <?php echo esc_html($get_result_of_order_received_data['_shipping_city']) . esc_html('-') . esc_html($get_result_of_order_received_data['_shipping_postcode']); ?><br>
                            <?php echo esc_html($get_result_of_order_received_data['_shipping_state']) . esc_html(', ') . esc_html($PSC_Common_Function->two_digit_get_countrycode_to_country($get_result_of_order_received_data['_shipping_country'])); ?>
                            <?php $PSC_Common_Function->session_cart_destroy(); ?>
                        </address>
                    </div><!-- /.col-2 -->
                </div>


                <?php
            }
        } else {
            wp_redirect(get_permalink($PSC_Common_Function->psc_cart_page()));
        }
        ?>
    </div>
</div>
<?php do_action('psc_after_main_content'); ?>
<?php get_footer(); ?>