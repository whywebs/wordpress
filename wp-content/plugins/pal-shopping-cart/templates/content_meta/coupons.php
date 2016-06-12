<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<?php
global $post;
$PSC_Common_Function = new PSC_Common_Function();
$get_current_coupons_details = $PSC_Common_Function->get_post_meta_all($post->ID);
?>
<div class="wrap">    
    <div class="wrap" id="psc_product_coupon_div">
        <ul>
            <li tab="tab1" class="first current"><?php echo esc_html('General'); ?></li>                                    
        </ul>
        <div class="tab-content" style="display: block;">
            <table class="widefat" cellspacing="0" style="clear: inherit; border:none">                       
                <tbody>
                    <tr class="">   
                        <th><?php echo esc_html('Discount type'); ?></th>
                        <td>
                            <select name="psc_coupon_discount_type">
                                <option value="fixed_cart"><?php echo esc_html('Cart Discount'); ?></option>                                                                
                            </select>
                        </td>
                    </tr>
                    <tr>   
                        <th><?php echo esc_html('Coupon amount ($)'); ?></th>
                        <td>
                            <input type="text" name="psc_coupon_amount" value="<?php echo esc_attr(($get_current_coupons_details['psc_coupon_amount']) ? $get_current_coupons_details['psc_coupon_amount'] : ''); ?>" id="psc_coupon_amount" placeholder="0">
                        </td>
                    </tr>                    
                    <tr>   
                        <th><?php echo esc_html('Coupon expiry date'); ?></th>
                        <td>
                            <input type="text" class="psc_coupon_expiry_date" name="psc_coupon_expiry_date" value="<?php echo esc_attr(($get_current_coupons_details['psc_coupon_expiry_date']) ? $get_current_coupons_details['psc_coupon_expiry_date'] : ''); ?>" id="psc_coupon_expiry_date" placeholder="YYYY-MM-DD">
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>                
    </div>    
</div>