<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

get_header();
?>

<?php
do_action('psc_before_main_content');
?>

<header class="entry-header">
    <h1 class="entry-title"><?php echo esc_html($post->post_title); ?></h1>
</header>

<?php
$PSC_Common_Function = new PSC_Common_Function();
$psc_shop_page_id = $PSC_Common_Function->psc_shop_page();
$currency_symbol = $PSC_Common_Function->get_psc_currency_symbol_only();
$cart_item_array = '';
$psc_card_handler_obj = new PSC_Cart_Handler();


//customer session remove
$is_customer_session = $PSC_Common_Function->session_get('TOKEN');
if (isset($is_customer_session) && strlen($is_customer_session) > 0) {
    $PSC_Common_Function->customer_session_empty();
}

$coupon_cart_discount = $PSC_Common_Function->get_cart_total_discount();
$coupon_code = $PSC_Common_Function->get_cart_total_coupon_code();
$cart_item_array = $psc_card_handler_obj->contents();

$psc_coupons_general_settings = (get_option('psc_coupons_general_settings')) ? get_option('psc_coupons_general_settings') : 'yes';
$psc_coupons_general_settings = ($psc_coupons_general_settings) == 'yes' ? true : false;

if (is_array($cart_item_array) && count($cart_item_array) > 0) {
    ?>
    <div class="entry-content">
        <div class="psc_display_notice"><?php do_action('psc_display_notice'); ?></div> 
        <div class="psc_shop_table_div">    
            <div class="psc_display_notice">
                <?php do_action('psc_display_notice_coupons'); ?>
            </div>
            <form action="<?php echo esc_url(get_permalink($PSC_Common_Function->psc_cart_page())); ?>" method="post">
                <table class="psc_shop_table cart" cellspacing="0">
                    <thead>
                        <tr class="main_header_tr">
                            <th class="psc-product-remove"><?php _e('Action', 'pal-shopping-cart'); ?></th>
                            <th class="psc-product-thumbnail"><?php _e('Image', 'pal-shopping-cart'); ?></th>
                            <th class="psc-product-name"><?php _e('Product', 'pal-shopping-cart'); ?></th>
                            <th class="psc-product-price"><?php _e('Price', 'pal-shopping-cart'); ?></th>
                            <th class="psc-product-quantity"><?php _e('Quantity', 'pal-shopping-cart'); ?></th>
                            <th class="psc-product-subtotal"><?php _e('Total', 'pal-shopping-cart'); ?></th>
                        </tr>
                        <?php
                        $count = 1;
                        $total_price = 0;
                        foreach ($cart_item_array as $key => $value) {
                            $expload_data = explode(':', $value['name']);
                            $expload_product_id = explode('_', $value['id']);
                            $is_stock_is = $PSC_Common_Function->get_update_stock_by_post_id($expload_product_id[0], $expload_data[1]);
                            if (isset($is_stock_is) && $is_stock_is > 0) {
                                $product_stock = $is_stock_is;
                            } else if (isset($is_stock_is) && $is_stock_is == 'instock') {
                                $product_stock = "";
                            }
                            ?>

                            <tr class="psc-product-tr-<?php echo esc_html($count); ?>">
                                <td class="psc-product-remove"><?php echo "<span class='psc-product-remove-icon' data-product-id='" . esc_attr($expload_product_id[0]) . "' data-row-id='" . esc_attr($value['rowid']) . "'><img src='" . esc_url(apply_filters('psc_placeholder_img_src', plugins_url('../public/images/remove_item.png', dirname(__FILE__)))) . "'></span>" ?></td>
                                <td class="psc-product-thumbnail">
                                    <?php
                                    $thumbnail = $PSC_Common_Function->cart_get_image($value['id']);

                                    if ($thumbnail) {
                                        echo $thumbnail;
                                    } else {
                                        printf('<a href="%s">%s</a>', esc_url($PSC_Common_Function->get_permalink($value['id'])), $thumbnail);
                                    }
                                    ?>
                                </td>
                                <td class="psc-product-name"><?php echo esc_html($expload_data[0]) . '<br />'; ?><strong data-variation-name="<?php echo esc_html($expload_data[1]); ?>"><?php echo esc_html($expload_data[1]); ?></strong></td>
                                <td class="psc-product-price"><?php echo esc_html($currency_symbol) . '' . number_format(esc_html($value['price']), 2); ?></td>
                                <td class="psc-product-quantity"><input type="number" id="psc_update_item_qty" name="psc_update_item_qty" value="<?php echo esc_html($value['qty']); ?>" min="1" max="<?php echo esc_html($product_stock); ?>"></td>
                                <td class="psc-product-subtotal"><?php echo esc_html($currency_symbol) . '' . number_format(esc_html($value['subtotal']), 2); ?></td>
                            </tr> 
                            <?php
                            $total_price = $total_price + $value['subtotal'];
                            $count++;
                        }
                        ?>
                        <tr>
                            <td colspan="6"> 
                                <?php if ($psc_coupons_general_settings) { ?>                            
                                    <input type="text" name="psc_applay_coupons_text" id="psc_applay_coupons_text" value="">                            
                                    <span id="psc_applay_coupons" class="psc-button" style="background-color: #eee; color:#555"><?php _e('Applay Coupons'); ?></span>
                                <?php } ?>
                                <span id="psc_update_cart" class="psc-button"><?php _e('Update Cart'); ?></span>
                            </td>
                        </tr>    
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <div class="psc-totals-checkout">   
                    <div class="psc-total-carts">
                        <h2><?php _e('Cart Totals') ?></h2>
                        <table cellspacing="0" class="psc-chackout-table">
                            <tbody><tr class="psc-cart-subtotal">
                                    <th>Subtotal</th>
                                    <td><span class="amount"><?php echo esc_html($currency_symbol) . '' . number_format(esc_html($total_price), 2); ?></span></td>
                                </tr>
                                <?php if ($coupon_cart_discount != 0) { ?>
                                    <tr class="psc-cart-coupon">
                                        <th>Coupons:<?php echo esc_html($coupon_code); ?></th>
                                        <td><strong><span class="amount"><?php echo '-' . esc_html($currency_symbol) . '' . number_format(esc_html($coupon_cart_discount), 2); ?></span></strong> </td>
                                    </tr>
                                <?php }
                                ?>

                                <tr class="psc-order-total">
                                    <th>Total</th>
                                    <td><strong><span class="amount"><?php echo esc_html($currency_symbol) . '' . number_format(esc_html($psc_card_handler_obj->_cart_contents['cart_total'] - $coupon_cart_discount), 2); ?></span></strong> </td>
                                </tr>                   
                            </tbody>
                        </table>
                        <div class="psc-proceed-to-checkout">
                            <?php
                            $enable_checkout_button = (get_option('psc_pec_cart_page_enabled_button')) ? get_option('psc_pec_cart_page_enabled_button') : 'yes';
                            $psc_pec_standaed_checkout_button = (get_option('psc_pec_standaed_checkout_button')) ? get_option('psc_pec_standaed_checkout_button') : 'no';
                            if ($enable_checkout_button == 'yes') {

                                do_action('enable_checkout_button', 'cart', '');
                            }

                            if ($psc_pec_standaed_checkout_button == 'no') {
                                ?>

                                <a id="" href="<?php echo esc_url(get_permalink($PSC_Common_Function->psc_checkout_page())); ?>" class="psc-button">Process to Checkout</a>

                            <?php }
                            ?>

                        </div>
                    </div>
                </div>
            </form>
        </div>   
    </div>
<?php } else { ?>
    <div class="psc-return-shop-page">
        <p><?php echo esc_html('Your cart is currently empty.'); ?></p>
        <a  class="psc-button" href="<?php echo esc_url(get_permalink($psc_shop_page_id)); ?>">Return to Shop Page</a>
    </div> 
<?php }
?>
<?php
do_action('psc_after_main_content');
?>
<?php get_footer(); ?>