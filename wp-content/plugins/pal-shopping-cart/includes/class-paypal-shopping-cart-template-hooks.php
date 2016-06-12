<?php

add_action('psc_before_main_content', 'psc_output_content_wrapper', 10);
add_action('psc_after_main_content', 'psc_output_content_wrapper_end', 10);
add_action('psc_sidebar', 'psc_get_sidebar', 10);

add_action('psc_archive_description', 'psc_taxonomy_archive_description', 10);
add_action('psc_archive_description', 'psc_product_archive_description', 10);

add_action('psc_before_shop_loop', 'psc_result_count', 20);
add_action('psc_before_subcategory_title', 'psc_subcategory_thumbnail', 10);

add_action('psc_after_shop_loop', 'psc_pagination', 10);

add_action('psc_after_shop_loop_item', 'psc_template_loop_add_to_cart', 10);
add_action('psc_before_shop_loop_item_title', 'psc_template_loop_product_thumbnail', 10);
add_action('psc_shop_loop_item_title', 'psc_template_loop_product_title', 10);
add_action('psc_after_shop_loop_item_title', 'psc_template_loop_price', 10);

add_action('psc_before_single_product_summary', 'psc_show_product_images', 20);
add_action('psc_product_thumbnails', 'psc_show_product_thumbnails', 20);


add_action('psc_single_product_summary', 'psc_template_single_title', 5);
add_action('psc_single_product_summary', 'psc_template_single_price', 10);
add_action('psc_single_product_summary', 'psc_template_single_stock', 20);
add_action('psc_single_product_summary', 'psc_template_single_excerpt', 25);
add_action('psc_single_product_summary', 'psc_template_single_meta', 30);


add_action('psc_after_single_product_summary', 'psc_output_product_data_tabs', 10);

add_action('psc_simple_add_to_cart', 'psc_simple_add_to_cart', 30);

add_action('psc_checkout_form_billing', 'psc_checkout_form_billing');
add_action('psc_checkout_before_order_review', 'psc_checkout_before_order_review');

add_action('psc_simple_product_meta', 'psc_simple_product_meta_content', 10);
add_action('psc_add_new_product_coupon', 'psc_add_new_product_coupon', 10);
add_action('psc_order_view_custom_meta', 'psc_order_view_custom_meta', 10, 1);
add_action('psc_order_item_view_custom_meta', 'psc_order_item_view_custom_meta', 10);
add_action('psc_get_all_enable_payment_methods', 'psc_get_all_enable_payment_methods');
add_action('psc_display_notice', 'psc_display_notice', 10);
add_action('psc_display_notice_coupons', 'psc_display_notice_coupons', 10);

add_action('enable_checkout_button', 'enable_checkout_button_cart', 10, 2);