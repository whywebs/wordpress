<?php
if (!defined('ABSPATH')) {
    exit;
}
get_header();
$PSC_Common_Function = new PSC_Common_Function();
$country_obj = new PSC_Countries();
$express_methods_obj = new Paypal_Shopping_Cart_Express_Checkout();

$total_checkout_price = 0;
$shiptocountry = '';

$pscorder = $PSC_Common_Function->session_cart_contents();
$country_array = $country_obj->Countries();
$currency_code = $PSC_Common_Function->get_psc_currency();
$currency_symbol = Paypal_Shopping_Cart_General_Setting::get_paypal_shopping_cart_currency_symbol($currency_code);
$coupon_cart_discount = $PSC_Common_Function->get_cart_total_discount();
$coupon_code = $PSC_Common_Function->get_cart_total_coupon_code();
do_action('psc_before_main_content');

$get_shiptoname = $PSC_Common_Function->session_get('shiptoname');
if(isset($get_shiptoname) && empty($get_shiptoname)){
    
    if (isset($_GET['token']) && !empty($_GET['token']) ) {
        $paypal_express_checkout = new Paypal_Shopping_Cart_Express_Checkout();
        $paypal_express_checkout->paypal_express_checkout($_GET);
    }    
    
}

?>

<header class="entry-header">
    <h1 class="entry-title"><?php echo esc_html(get_the_title()); ?></h1>
</header>
<div class="entry-content">
    <div class="pscpaypalexpress_order_review">
        <form class="paypal_express_checkout" method="POST" action="<?php echo add_query_arg(array('psc-api' => 'Paypal_Shopping_Cart_Express_Checkout', 'psc_action' => 'pscpayaction'), home_url()); ?>">
            <table class="psc-checkout-review-order-table">
                <thead>
                    <tr>
                        <th class="psc-product-name">Product</th>
                        <th class="psc-product-total">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pscorder as $key => $value) { ?>                
                        <tr class="psc_cart_item">
                            <td class="psc_product-name">
                                <strong class="psc_product-quantity"><?php echo esc_html($value['name'] . ' × ' . $value['qty']); ?></strong>	
                            </td>
                            <td class="psc_product-total">
                                <span class="amount"><?php echo esc_html($currency_symbol . '' . number_format($value['subtotal'], 2)); ?></span>
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
                            <span class="amount"><?php echo esc_html($currency_symbol . '' . number_format($total_checkout_price, 2)); ?></span>
                        </td>
                    </tr>
                    <?php if ($coupon_cart_discount != 0) { ?>
                        <tr class="cart-coupon">
                            <th>Coupons:<?php echo esc_html($coupon_code); ?></th>

                            <td><strong><span class="amount"><?php echo esc_html('-' . $currency_symbol . '' . number_format($coupon_cart_discount, 2)); ?></span></strong> </td>

                        </tr>

                    <?php }
                    ?>
                    <tr class="order-total">
                        <th>Total</th>
                        <td>
                            <strong><span class="amount"><?php echo esc_html($currency_symbol . '' . number_format($total_checkout_price - $coupon_cart_discount, 2)); ?></span></strong> 
                        </td>
                    </tr>
                </tfoot>
            </table>
            <?php
            if (isset($_GET['psc_action']) && 'pscrevieworder' == $_GET['psc_action']) {
                $express_methods_obj->paypal_express_checkout($_POST);
            }
            foreach ($country_array as $key => $value) {
                if ($key == $PSC_Common_Function->session_get('shiptocountrycode')) {
                    $shiptocountry = $value;
                }
            }
            if ($total_checkout_price != 0) {
                if ($PSC_Common_Function->session_get('shiptoname')) {
                    ?>
                    <div class="title">
                        <h2>Customer details</h2>
                    </div>
                    <div class="col2-set addresses">
                        <div class="col-1">
                            <div class="title">
                                <h3>Shipping Address</h3>
                            </div>
                            <div class="address">
                                <p>
                                    <?php
                                    $is_set_session = $PSC_Common_Function->session_get('shiptoname');
                                    if (isset($is_set_session) && !empty($is_set_session)) {
                                        echo $PSC_Common_Function->get_customer_address();
                                    }
                                    ?> 
                                </p>
                            </div>
                        </div>
                        <div class="col-2"></div>
                    </div>

                    <div class="clear"></div>
                    <p class="pac_button_action_click">             
                        <a class="button paypal_express_cancel pac_button_action_click_submit class_to_defualt_cursor" href="<?php echo esc_url(get_permalink($PSC_Common_Function->psc_cart_page())) ?>">Cancel Order</a> 
                        <input type="submit"  class="button pac_button_action_click_submit class_to_defualt_cursor" value="Place Order">
                    </p>
                <?php }
            } else { ?>
                <div class="title">
                    <h2 style="color:#b81c23;">Your Review Order is empty!.</h2>                    
                    <a  class="psc-button" href="<?php echo esc_url(get_permalink($PSC_Common_Function->psc_shop_page())); ?>">Return to Shop Page</a>
                </div>
            <?php } ?>
            </p>
        </form>
    </div>
</div>
<?php
do_action('psc_after_main_content');
get_footer();
?>