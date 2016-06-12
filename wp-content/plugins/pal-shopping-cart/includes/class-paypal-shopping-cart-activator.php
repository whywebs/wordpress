<?php

/**
 * Fired during plugin activation
 *
 * @link       http://localleadminer.com/
 * @since      1.0.0
 *
 * @package    Paypal_Shopping_Cart
 * @subpackage Paypal_Shopping_Cart/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Paypal_Shopping_Cart
 * @subpackage Paypal_Shopping_Cart/includes
 * @author     wpgateways <wpgateways@gmail.com>
 */
class Paypal_Shopping_Cart_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        self::create_files();
        self::create_paypal_shopping_cart_page();
    }

    private static function create_files() {
        $upload_dir = wp_upload_dir();
        $files = array(
            array(
                'base' => PSC_WORDPRESS_LOG_DIR,
                'file' => '.htaccess',
                'content' => 'deny from all'
            ),
            array(
                'base' => PSC_WORDPRESS_LOG_DIR,
                'file' => 'index.html',
                'content' => ''
            )
        );
        foreach ($files as $file) {
            if (wp_mkdir_p($file['base']) && !file_exists(trailingslashit($file['base']) . $file['file'])) {
                if ($file_handle = @fopen(trailingslashit($file['base']) . $file['file'], 'w')) {
                    fwrite($file_handle, $file['content']);
                    fclose($file_handle);
                }
            }
        }
    }

    public static function create_paypal_shopping_cart_page() {

        $psc_shop_page_id = (get_option('psc_shoppage_product_settings')) ? get_option('psc_shoppage_product_settings') : '';
        $psc_cart_page_id = (get_option('psc_cartpage_product_settings')) ? get_option('psc_cartpage_product_settings') : '';
        $psc_checkout_page_id = (get_option('psc_checkoutpage_product_settings')) ? get_option('psc_checkoutpage_product_settings') : '';

        if (isset($psc_shop_page_id)) {
            $is_avilable_shop_page = self::get_page_status_by_postid($psc_shop_page_id);
            if ($is_avilable_shop_page == false) {
                $post = array(
                    'comment_status' => 'open',
                    'ping_status' => 'closed',
                    'post_date' => date('Y-m-d H:i:s'),
                    'post_name' => 'Shop',
                    'post_status' => 'publish',
                    'post_title' => 'Shop',
                    'post_type' => 'page',
                    'post_content' => '[shop]',
                );
                $newvalue = wp_insert_post($post, false);
                update_option('psc_shoppage_product_settings', $newvalue);
            }
        }
        if (isset($psc_cart_page_id)) {

            $is_avilable_cart_page = self::get_page_status_by_postid($psc_cart_page_id);
            if ($is_avilable_cart_page == false) {
                $post = array(
                    'comment_status' => 'open',
                    'ping_status' => 'closed',
                    'post_date' => date('Y-m-d H:i:s'),
                    'post_name' => 'Cart',
                    'post_status' => 'publish',
                    'post_title' => 'Cart',
                    'post_type' => 'page',
                    'post_content' => '[cart]',
                );
                $newvalue = wp_insert_post($post, false);
                update_option('psc_cartpage_product_settings', $newvalue);
            }
        }
        if (isset($psc_checkout_page_id)) {

            $is_avilable_checkout_page = self::get_page_status_by_postid($psc_checkout_page_id);
            if ($is_avilable_checkout_page == false) {
                $post = array(
                    'comment_status' => 'open',
                    'ping_status' => 'closed',
                    'post_date' => date('Y-m-d H:i:s'),
                    'post_name' => 'Checkout',
                    'post_status' => 'publish',
                    'post_title' => 'Checkout',
                    'post_type' => 'page',
                    'post_content' => '[checkout]',
                );
                $newvalue = wp_insert_post($post, false);
                update_option('psc_checkoutpage_product_settings', $newvalue);
            }
        }
    }

    public static function get_page_status_by_postid($id) {
        global $wpdb;
        $result = $wpdb->get_results("SELECT * FROM `" . $wpdb->posts . "` WHERE `ID` = " . $id . " and `post_status` = 'publish'");
        $is_avialble_page = false;

        if (is_array($result) && count($result) > 0) {
            if ($result[0]->post_status == 'publish') {
                $is_avialble_page = true;
            }
        }

        return $is_avialble_page;
    }

}