<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

get_header();
?>

<?php
do_action('psc_before_main_content');
?>
<div class="psc_display_notice"><?php do_action('psc_display_notice'); ?></div> 
<?php while (have_posts()) : the_post(); ?>

    <?php psc_get_template_part('content', 'single-psc_product'); ?>

<?php endwhile; // end of the loop. ?>

<?php
do_action('psc_after_main_content');
?>

<?php
do_action('psc_sidebar');
?>

<?php get_footer(); ?>