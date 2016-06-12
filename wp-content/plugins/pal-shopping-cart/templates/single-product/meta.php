<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $post;
$PSC_Common_Function = new PSC_Common_Function();
$psc_sku = $PSC_Common_Function->psc_get_sku($post);
$psc_price = $PSC_Common_Function->psc_get_price($post);
$product_stock = $PSC_Common_Function->get_stock_by_post_id($post->ID);
?>
<div class="psc-single-product-add-to-cart">
    <?php if (isset($psc_price) && $psc_price > 0) { ?>
        <input type="number" name="psc_quantity" id="psc_quantity" value="1" min="1" max="<?php echo esc_attr($product_stock); ?>"/>
        <input type="hidden" name="psc_available_stock" id="psc_available_stock" value="<?php echo esc_attr($product_stock); ?>" />
        <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($post->ID); ?>" />
        <a id="psc_add_to_cart_button<?php echo esc_attr($post->ID); ?>" rel="nofollow" psc-product-id="<?php echo esc_attr($post->ID); ?>" class="psc-button psc_add_to_cart_button product_type_simple">Add to cart <span class="pac_add_to_cart_process process_now<?php echo esc_attr($post->ID); ?>" hidden=""></span> </a>
        <a href="<?php echo esc_url(get_permalink($PSC_Common_Function->psc_addtocart_after_redirect_page())); ?>" class="view_cart<?php echo esc_attr($post->ID); ?> view_cart" title="View Cart" hidden>View Cart</a>
    <?php } else { ?>
        <input type="number" name="psc_quantity" id="psc_quantity" value="0" disabled/>
        <input type="hidden" name="psc_available_stock" id="psc_available_stock" value="<?php echo esc_url($product_stock); ?>" />
        <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($post->ID); ?>" />
        <a id="psc_add_to_cart_button<?php echo esc_attr($post->ID); ?>" rel="nofollow" psc-product-id="<?php echo esc_attr($post->ID); ?>" class="psc-button psc_add_to_cart_button product_type_simple" style="pointer-events: none;cursor: default; background-color:#E8E8E8">Add to cart <span class="pac_add_to_cart_process process_now<?php echo esc_attr($post->ID); ?>" hidden=""></span> </a>        
        <a href="<?php echo esc_url(get_permalink($PSC_Common_Function->psc_addtocart_after_redirect_page())); ?>" class="view_cart<?php echo esc_attr($post->ID); ?> view_cart" title="View Cart" hidden>View Cart</a>
    <?php } ?>
</div>
<?php if (isset($psc_price) && $psc_price > 0) { ?>
    <div class="psc-enable_express-checkout">
        <?php
        $enable_checkout_button = (get_option('psc_pec_single_product_enabled_button')) ? get_option('psc_pec_single_product_enabled_button') : 'yes';
        if ($enable_checkout_button == 'yes') {

            $p_name_title = ($post->post_name) ? $post->post_name : $post->post_title;
            do_action('enable_checkout_button', 'single', $p_name_title);
        }
        ?> 
    </div>
<?php } ?>
<div class="product_meta">

    <?php do_action('psc_product_meta_start'); ?>

    <?php if ($psc_sku) : ?>

        <span class="sku_wrapper"><?php echo esc_html('SKU:', 'pal-shopping-cart'); ?> <span class="sku" itemprop="sku"><?php echo ( $psc_sku ) ? esc_html($psc_sku) : esc_html('N/A', 'pal-shopping-cart'); ?></span></span>

    <?php endif; ?>

    <?php do_action('psc_product_meta_end'); ?>
</div>