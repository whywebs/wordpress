<?php
if (!defined('ABSPATH')) {
    exit;
}

global $psc_loop;

if (empty($psc_loop['loop'])) {
    $psc_loop['loop'] = 0;
}

if (empty($psc_loop['columns'])) {
    $psc_loop['columns'] = apply_filters('loop_shop_columns', 4);
}
$psc_loop['loop']++;
?>
<li <?php psc_product_cat_class(); ?>>
    <?php do_action('psc_before_subcategory', $category); ?>

    <a href="<?php echo esc_url(get_term_link($category->slug, 'product_cat')); ?>">

        <?php
        do_action('psc_before_subcategory_title', $category);
        ?>
        <h3>
            <?php
            echo esc_html($category->name);

            if ($category->count > 0)
                echo apply_filters('psc_subcategory_count_html', ' <mark class="count">(' . $category->count . ')</mark>', $category);
            ?>
        </h3>

        <?php
        do_action('psc_after_subcategory_title', $category);
        ?>

    </a>

    <?php do_action('psc_after_subcategory', $category); ?>
</li>