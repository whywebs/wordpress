<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://localleadminer.com/
 * @since      1.0.0
 *
 * @package    Paypal_Shopping_Cart
 * @subpackage Paypal_Shopping_Cart/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Paypal_Shopping_Cart
 * @subpackage Paypal_Shopping_Cart/public
 * @author     wpgateways <wpgateways@gmail.com>
 */
class Paypal_Shopping_Cart_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->psc_add_image_sizes();
        add_filter('wp_nav_menu_items', 'do_shortcode');
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/paypal-shopping-cart-public.css', array(), $this->version, 'all');
        wp_enqueue_style($this->plugin_name . 'customselect_css', PSC_FOR_WORDPRESS_LOG_DIR . 'admin/css/jquery-customselect.css', array(), $this->version, 'all');
        wp_enqueue_style($this->plugin_name . 'colorbox_css', plugin_dir_url(__FILE__) . 'css/colorbox.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/paypal-shopping-cart-public.js', array('jquery'), $this->version, false);
        if (wp_script_is($this->plugin_name)) {
            wp_localize_script($this->plugin_name, 'paypal_shopping_cart_url_params', apply_filters('paypal_shopping_cart_url_params', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'paypal_shopping_cart_url' => wp_create_nonce("paypal_shopping_cart_url"),
            )));
        }
        wp_enqueue_script($this->plugin_name . 'blockui', plugin_dir_url(__FILE__) . 'js/paypal-shopping-cart-public-blockUI.js', array('jquery'), $this->version, false);
        wp_enqueue_script($this->plugin_name . 'customselect_js', PSC_FOR_WORDPRESS_LOG_DIR . 'admin/js/jquery-customselect.js', array('jquery'), $this->version, false);
        wp_enqueue_script($this->plugin_name . 'colorbox_js', plugin_dir_url(__FILE__) . 'js/jquery.colorbox.js', array('jquery'), $this->version, false);
    }

    public function psc_add_image_sizes() {
        $shop_thumbnail = psc_get_image_size('psc_shop_thumbnail');
        $shop_catalog = psc_get_image_size('psc_shop_catalog');
        $shop_single = psc_get_image_size('psc_shop_single');

        add_image_size('psc_shop_thumbnail', $shop_thumbnail['width'], $shop_thumbnail['height'], $shop_thumbnail['crop']);
        add_image_size('psc_shop_catalog', $shop_catalog['width'], $shop_catalog['height'], $shop_catalog['crop']);
        add_image_size('psc_shop_single', $shop_single['width'], $shop_single['height'], $shop_single['crop']);
    }

    public function paypal_shopping_cart_get_shoppage($template) {
        global $post;
        $PSC_Common_Function = new PSC_Common_Function();

        $psc_shoppage = (get_option('psc_shoppage_product_settings')) ? get_option('psc_shoppage_product_settings') : '';
        $psc_cartpage = (get_option('psc_cartpage_product_settings')) ? get_option('psc_cartpage_product_settings') : '';
        $psc_checkoutpage = (get_option('psc_checkoutpage_product_settings')) ? get_option('psc_checkoutpage_product_settings') : '';

        if (empty($psc_shoppage)) {
            $psc_shoppage = $PSC_Common_Function->psc_shop_page();
        }
        if (empty($psc_cartpage)) {
            $psc_cartpage = $PSC_Common_Function->psc_cart_page();
        }
        if (empty($psc_checkoutpage)) {
            $psc_checkoutpage = $PSC_Common_Function->psc_checkout_page();
        }

        if (isset($post) && !empty($post)) {
            $file = '';
            if ($psc_shoppage == $post->ID) {
                if (is_post_type_archive('psc_product') || is_page($psc_shoppage)) {
                    $file = 'archive-psc_product.php';
                    $find[] = $file;
                    $find[] = $this->template_path() . $file;
                }
            } elseif (is_single() && get_post_type() == 'psc_product') {
                $file = 'single-psc_product.php';
                $find[] = $file;
                $find[] = $this->template_path() . $file;
            } elseif ($post->ID == $psc_cartpage) {
                $file = 'cart/psc-cart.php';
                $find[] = $file;
                $find[] = $this->template_path() . $file;
            } elseif ($post->ID == $psc_checkoutpage) {
                $file = 'checkout/psc-form-checkout.php';
                $find[] = $file;
                $find[] = $this->template_path() . $file;
            } elseif ($post->post_name == 'pscrevieworder') {
                $file = 'order/psc-order-details.php';
                $find[] = $file;
                $find[] = $this->template_path() . $file;
            } elseif ($post->post_name == 'pscordercomplete') {
                $file = 'order/psc-order-complete.php';
                $find[] = $file;
                $find[] = $this->template_path() . $file;
            }
            if ($file) {
                $template = locate_template(array_unique($find));
                if (!$template) {
                    $template = PSC_PLUGIN_DIR_PATH . '/templates/' . $file;
                }
            }
            return $template;
        }
    }

    public function template_path() {
        return apply_filters('psc_template_path', 'pal-shopping-cart/');
    }

}