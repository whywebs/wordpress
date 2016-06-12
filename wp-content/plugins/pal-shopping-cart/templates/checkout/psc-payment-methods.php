<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php
$PSC_Common_Function = new PSC_Common_Function();
$result_array = $PSC_Common_Function->is_enable_payment_methods();
if (is_array($result_array) && count($result_array[0]) > 0) {
    $count = 1;
    ?>
    <ul class="psc_payment_methods">
        <?php
        foreach ($result_array as $value) {
            ?>
            <li class="psc_payment_method_<?php echo esc_html(str_replace(' ', '_', $value['name'])); ?>">
                <input id="psc_payment_method_<?php echo esc_html(str_replace(' ', '_', $value['name'])); ?>" type="radio" class="input-radio-button" name="psc_payment_method" value="<?php echo esc_html($count); ?>" checked="checked" data-order_button_text="">

                <label for="psc_payment_method_<?php echo esc_html(str_replace(' ', '_', $value['name'])); ?>">
                    <span style="float: left;margin-left: -1.5em;"><?php echo esc_html($value['name']); ?> </span>
                    <img style="margin-left: -2em;width: 40px;height: 25px;" src="<?php echo esc_url(plugins_url() . '/pal-shopping-cart/public/images/paypal-credit.png'); ?>">
                </label>
                <div class="psc_payment_box psc_payment_method_<?php echo esc_html(str_replace(' ', '_', $value['name'])); ?>">
                    <p><?php echo esc_html($value['discription']); ?></p>
                </div>
            </li>

            <?php
            $count++;
        }
        ?>
    </ul>
    <div class="psc-row psc-place-order">                    
        <input type="submit" class="psc-button" name="submit" id="psc_place_order" value="submit" data-value="">
    </div>

<?php } else { ?>
    <ul class="psc_payment_methods">
        <li class="">
            <label>
                <p><strong>Enable Payment Methods.</strong></p>
            </label>            
        </li>
    </ul>
<?php }
?>