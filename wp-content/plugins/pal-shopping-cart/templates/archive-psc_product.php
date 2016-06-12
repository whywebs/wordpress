<?php get_header(); ?>

<?php do_action('psc_before_main_content'); ?>

<?php if (apply_filters('psc_show_page_title', true)) : ?>
    <h1 class="page-title"><?php psc_show_page_title(); ?></h1>
<?php endif; ?>

<?php do_action('psc_archive_description'); ?>    
<?php
$posts_per_page = (get_option('posts_per_page')) ? get_option('posts_per_page') : 10;
$wp_query = new WP_Query();
$wp_query->query('showposts=' . $posts_per_page . '&post_type=psc_product' . '&paged=' . $paged);
?>    
<?php if (have_posts()) : ?>    
    <?php
    do_action('psc_before_shop_loop');
    ?>
    <?php psc_product_loop_start(); ?>

    <?php psc_product_subcategories(); ?>

    <?php while (have_posts()) : the_post(); ?>
        <?php psc_get_template_part('content', 'psc_product'); ?>
    <?php endwhile; ?>

    <?php psc_product_loop_end(); ?>

    <?php
    do_action('psc_after_shop_loop');
    ?>
<?php elseif (!psc_product_subcategories(array('before' => psc_product_loop_start(false), 'after' => psc_product_loop_end(false)))) : ?>
    <?php psc_get_template('loop/psc-no-products-found.php'); ?>
<?php endif; ?> 
<?php do_action('psc_after_main_content'); ?> 
<?php do_action('psc_sidebar'); ?> 
<?php get_footer(); ?>