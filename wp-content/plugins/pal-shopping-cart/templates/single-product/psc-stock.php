<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<?php
global $post;
$PSC_Common_Function = new PSC_Common_Function();
$is_enable_stock_management = $PSC_Common_Function->is_enable_stock_management($post);
if ($is_enable_stock_management == true) {
    $product_stock = $PSC_Common_Function->psc_get_product_stock($post);
    $is_variation = $PSC_Common_Function->psc_get_product_type($post);
    if (isset($product_stock) && '0' != $product_stock && $is_variation == "simple") { ?>
        
       <p class="stock in-stock"><?php echo esc_html($product_stock . ' In stock'); ?></p>
        
    <?php } else if($is_variation == "simple") { ?>
        <p class="stock out-of-stock"><?php echo esc_html(' Out of Stock'); ?></p>
    <?php }
} else { ?>
    <p class="stock in-stock"><?php echo esc_html('In stock'); ?></p>
<?php }
?>