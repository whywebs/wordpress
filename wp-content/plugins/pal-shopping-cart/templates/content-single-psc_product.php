<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<?php
do_action('psc_before_single_product');

if (post_password_required()) {
    echo get_the_password_form();
    return;
}
?>

<div id="product-<?php the_ID(); ?>" class="psc-single-image">

    <?php
    do_action('psc_before_single_product_summary');
    ?>

    <div class="psc-summary psc-entry-summary">

        <?php
        do_action('psc_single_product_summary');
        ?>

    </div>

    <?php
    do_action('psc_after_single_product_summary');
    ?>

    <meta itemprop="url" content="<?php the_permalink(); ?>" />

</div><!-- #product-<?php the_ID(); ?> -->

<?php do_action('psc_after_single_product'); ?>