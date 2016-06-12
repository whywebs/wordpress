<?php
if (!defined('ABSPATH')) {
    exit;
}
global $post;
if (!empty($post->post_content)) :
    ?>
    <div class="psc-content psc-content-wrapper">
        <ul class="psc-single-product-tabs-menu">
            <li class="current">
                <a>
                    <?php echo esc_html('Product Description'); ?>
                </a>
            </li>        
        </ul>
        <div class="psc-single-product-tabs">
            <div id="psc-single-product-tabs-1" class="psc-single-product-tabs-content">
                <?php echo $post->post_content; ?>
            </div>
        </div>
    </div>

<?php endif; ?>