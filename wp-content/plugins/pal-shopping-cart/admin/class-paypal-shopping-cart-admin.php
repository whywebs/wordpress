<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://localleadminer.com/
 * @since      1.0.0
 *
 * @package    Paypal_Shopping_Cart
 * @subpackage Paypal_Shopping_Cart/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Paypal_Shopping_Cart
 * @subpackage Paypal_Shopping_Cart/admin
 * @author     wpgateways <wpgateways@gmail.com>
 */
class Paypal_Shopping_Cart_Admin {

    private $plugin_name;
    private $version;
    public $PSC_Common_Function;
    public $PSC_Custom_Post_type;
    public $PSC_Product_Save_Data;

    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->load_dependencies();
        $this->PSC_Common_Function = new PSC_Common_Function();
        $this->PSC_Custom_Post_type = new PSC_Custom_PostType();
        $this->PSC_Product_Save_Data = new PSC_Product_Save_Data();
    }

    private function load_dependencies() {

        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/paypal-shopping-cart-admin-display.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/paypal-shopping-cart-general-setting.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/paypal-shopping-cart-html-output.php';
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/paypal-shopping-cart-admin.css', array(), $this->version, 'all');
        wp_enqueue_style($this->plugin_name . 'jquery-ui-datepicker', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/themes/smoothness/jquery-ui.css');
        wp_enqueue_style($this->plugin_name . 'customselect_css', plugin_dir_url(__FILE__) . 'css/jquery-customselect.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/paypal-shopping-cart-admin.js', array('jquery'), $this->version, false);
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script($this->plugin_name . 'customselect_js', plugin_dir_url(__FILE__) . 'js/jquery-customselect.js', array('jquery'), $this->version, false);
    }

    public function paypal_shopping_cart_custom_post() {


        /**
         * Add order Custom Post 
         */
        $labels_order = array(
            'name' => _x('Orders', 'post type general name', 'pal-shopping-cart'),
            'singular_name' => _x('Order', 'post type singular name', 'pal-shopping-cart'),
            'menu_name' => _x('PayPal Shopping Carts', 'admin menu', 'pal-shopping-cart'),
            'name_admin_bar' => _x('Order', 'add new on admin bar', 'pal-shopping-cart'),
            'new_item' => __('New Order', 'pal-shopping-cart'),
            'edit_item' => __('View Order', 'pal-shopping-cart'),
            'view_item' => __('View Order', 'pal-shopping-cart'),
            'all_items' => __('Orders', 'pal-shopping-cart'),
            'search_items' => __('Search Orders', 'pal-shopping-cart'),
            'parent_item_colon' => __('Parent Orders:', 'pal-shopping-cart'),
            'not_found' => __('No Orders found.', 'pal-shopping-cart'),
            'not_found_in_trash' => __('No found Orders in Trash.', 'pal-shopping-cart')
        );

        $args_order = array(
            'labels' => $labels_order,
            'description' => __('Description.', 'pal-shopping-cart'),
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => false,
            'rewrite' => array('slug' => 'psc_order', 'with_front' => FALSE),
            'capability_type' => 'post',
            'capabilities' => array(
                'create_posts' => 'do_not_allow', // Removes support for the "Add New" function, including Super Admin's
            ),
            'has_archive' => true,
            'hierarchical' => false,
            'map_meta_cap' => true,
            'menu_icon' => PSC_FOR_WORDPRESS_LOG_DIR . 'admin/images/cart_add.png',
            'supports' => array('')
        );

        register_post_type('psc_order', $args_order);


        /**
         * Add Coupon Custom Post 
         */
        $labels_coupon = array(
            'name' => _x('Coupons', 'post type general name', 'pal-shopping-cart'),
            'singular_name' => _x('Coupon', 'post type singular name', 'pal-shopping-cart'),
            'menu_name' => _x('Coupons', 'admin menu', 'pal-shopping-cart'),
            'name_admin_bar' => _x('Coupons', 'add new on admin bar', 'pal-shopping-cart'),
            'add_new' => _x('Add New Coupons', 'Coupon', 'pal-shopping-cart'),
            'add_new_item' => __('Add New Coupon', 'pal-shopping-cart'),
            'new_item' => __('New Coupon', 'pal-shopping-cart'),
            'edit_item' => __('Edit Coupon', 'pal-shopping-cart'),
            'view_item' => __('View Coupon', 'pal-shopping-cart'),
            'search_items' => __('Search Coupons', 'pal-shopping-cart'),
            'parent_item_colon' => __('Parent Coupons:', 'pal-shopping-cart'),
            'not_found' => __('No Coupons found.', 'pal-shopping-cart'),
            'not_found_in_trash' => __('No found Coupons in Trash.', 'pal-shopping-cart')
        );

        $args_coupon = array(
            'labels' => $labels_coupon,
            'description' => __('Description.', 'pal-shopping-cart'),
            'public' => true,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'query_var' => false,
            'rewrite' => array('slug' => 'psc_coupon', 'with_front' => FALSE),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'excerpt')
        );

        register_post_type('psc_coupon', $args_coupon);

        $labels_product = array(
            'name' => _x('Products', 'post type general name', 'pal-shopping-cart'),
            'singular_name' => _x('Product', 'post type singular name', 'pal-shopping-cart'),
            'menu_name' => _x('Products', 'admin menu', 'pal-shopping-cart'),
            'name_admin_bar' => _x('Product', 'add new on admin bar', 'pal-shopping-cart'),
            'add_new' => _x('Add Product', 'Product', 'pal-shopping-cart'),
            'add_new_item' => __('Add New Product', 'pal-shopping-cart'),
            'new_item' => __('New Product', 'pal-shopping-cart'),
            'edit_item' => __('Edit Product', 'pal-shopping-cart'),
            'view_item' => __('View Product', 'pal-shopping-cart'),
            'all_items' => __('Products', 'pal-shopping-cart'),
            'search_items' => __('Search Products', 'pal-shopping-cart'),
            'parent_item_colon' => __('Parent Products:', 'pal-shopping-cart'),
            'not_found' => __('No Products found.', 'pal-shopping-cart'),
            'not_found_in_trash' => __('No found Products in Trash.', 'pal-shopping-cart'),
            'parent' => __('Parent Product', 'pal-shopping-cart'),
            'featured_image' => __('Product Image', 'pal-shopping-cart'),
            'set_featured_image' => __('Set product image', 'pal-shopping-cart'),
            'remove_featured_image' => __('Remove product image', 'pal-shopping-cart'),
            'use_featured_image' => __('Use as product image', 'pal-shopping-cart'),
        );

        $args_product = array(
            'labels' => $labels_product,
            'description' => __('Description.', 'pal-shopping-cart'),
            'public' => true,
            'map_meta_cap' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'psc_product', 'with_front' => true),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'map_meta_cap' => true,
            'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'comments', 'custom-fields', 'page-attributes', 'publicize', 'wpcom-markdown'),
        );

        register_post_type('psc_product', $args_product);


        $labels = array(
            'name' => _x('Product Categories', 'taxonomy general name'),
            'singular_name' => _x('Product Categories', 'taxonomy singular name'),
            'search_items' => __('Search Product Categories'),
            'all_items' => __('All Product Categories'),
            'parent_item' => __('Parent Product Category'),
            'parent_item_colon' => __('Parent Product Category:'),
            'edit_item' => __('Edit Product Category'),
            'update_item' => __('Update Product Category'),
            'add_new_item' => __('Add New Product Category'),
            'new_item_name' => __('New Product Category'),
            'menu_name' => __('Categories'),
        );
        $args = array(
            'labels' => $labels,
            'hierarchical' => true,
            'public' => true,
            'query_var' => 'categories',
            'rewrite' => array('slug' => 'psc_category'),
            '_builtin' => false,
        );
        register_taxonomy('psc_category', 'psc_product', $args);
    }

    public function paypal_shopping_cart_custom_post_menu() {
        add_submenu_page('edit.php?post_type=psc_order', 'Coupons', 'Coupons', 'manage_options', 'edit.php?post_type=psc_coupon');
        add_submenu_page('edit.php?post_type=psc_order', __('Settings', 'pal-shopping-cart'), __('Settings', 'pal-shopping-cart'), 'manage_options', 'psc_settings', array($this, 'paypal_shopping_cart_submenu_settings'));
        remove_submenu_page('edit.php?post_type=psc_order', 'post-new.php?post_type=psc_order');
        remove_submenu_page('edit.php?post_type=psc_order', 'post-new.php?post_type=psc_coupon');
    }

    public function paypal_shopping_cart_product_coupon() {
        add_meta_box('psc_coupon_view', __('Coupons Data'), array($this, 'paypal_shopping_cart_add_new_product_coupon'), 'psc_coupon', 'normal', 'low');
    }

    public function paypal_shopping_cart_add_new_product_coupon() {
        $this->PSC_Custom_Post_type->paypal_shopping_cart_add_new_product_coupon($post);
    }

    public function paypal_shopping_cart_save_coupons_fields($post_id, $post, $update) {
        $this->PSC_Product_Save_Data->save_new_coupons_data_postmeta($post_id);
    }

    public function paypal_shopping_cart_product_coupon_view_place_holder($title, $post) {
        if ($post->post_type == 'psc_coupon') {
            $my_title = "Coupon Code";
            return $my_title;
        }
        return $title;
    }

    public function paypal_shopping_cart_submenu_settings() {
        $report_tab = new PayPal_Shopping_Cart_Admin_Display();
        $report_tab->init_settings();
    }

    public function paypal_shopping_cart_add_new_product() {
        add_meta_box('psc_product_item', __('Product Data'), array($this, 'paypal_shopping_cart_product_metabox'), 'psc_product', 'normal', 'high');
        add_meta_box('psc_product_short_item', __('Product Short Description'), array($this, 'paypal_shopping_cart_product_short_metabox'), 'psc_product', 'normal', 'high');
    }

    public function paypal_shopping_cart_product_metabox($post) {
        $this->PSC_Custom_Post_type->create_product_detail_custom_posttype($post);
    }

    public function paypal_shopping_cart_product_short_metabox($post) {
        $this->PSC_Custom_Post_type->create_product_detail_custom_posttype_with_editor($post);
    }

    public function paypal_shopping_cart_save_postdata($post_id, $post, $update) {
        $this->PSC_Product_Save_Data->save_new_product_data_postmeta($post_id);
    }

    public function paypal_shopping_cart_add_new_order() {
        add_meta_box('psc_order_view', __('Order View'), array($this, 'paypal_shopping_cart_order_metabox'), 'psc_order', 'normal', 'high');
        add_meta_box('psc_order_item', __('Order Items'), array($this, 'paypal_shopping_cart_order_item_metabox'), 'psc_order', 'normal', 'high');
    }

    public function paypal_shopping_cart_order_metabox() {
        $this->PSC_Custom_Post_type->psc_order_detail_view($post);
    }

    public function paypal_shopping_cart_order_item_metabox() {
        $this->PSC_Custom_Post_type->psc_order_item_view($post);
    }

    public function psc_create_page($slug, $option = '', $page_title = '', $page_content = '', $post_parent = 0) {
        $page_data = array(
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => 1,
            'post_name' => $slug,
            'post_title' => $page_title,
            'post_content' => $page_content,
            'post_parent' => $post_parent,
            'comment_status' => 'closed'
        );
        $page_id = wp_insert_post($page_data);
    }

    public function set_custom_edit_psc_order_columns($columns) {

        unset($columns['date']);
        unset($columns['title']);
        $columns['pscstatus'] = __('<span class="psc_status_head"></span>', 'pal-shopping-cart');
        $columns['pscorder'] = __('Order', 'pal-shopping-cart');
        $columns['pscqty'] = __('Qty', 'pal-shopping-cart');
        $columns['pscship'] = __('Ship', 'pal-shopping-cart');
        $columns['pscorderdate'] = __('Date', 'pal-shopping-cart');
        $columns['psctotal'] = __('Total', 'pal-shopping-cart');
        $columns['pscaction'] = __('Action', 'pal-shopping-cart');
        return $columns;
    }

    public function custom_psc_order_columns($column, $post_id) {
        global $post;

        $result_array = $this->get_all_result_column_set($post);

        switch ($column) {
            case 'pscstatus' :
                echo $this->psc_get_order_status($result_array);
                break;
            case 'pscorder' :
                echo $this->psc_get_order_details($post->ID, $result_array[0]);
                break;
            case 'pscqty' :
                echo esc_html($this->psc_get_order_qty($result_array[2]));
                break;
            case 'pscship' :
                echo esc_html($this->psc_get_order_ship_to($result_array[0]));
                break;
            case 'pscorderdate' :
                echo esc_html(get_the_date('Y-m-d', $post->ID));
                break;
            case 'psctotal' :
                echo $this->psc_get_order_total($result_array[0]['_payment_method_title'], $result_array[1]);
                break;
            case 'pscaction' :
                echo $this->psc_get_order_status_action($result_array, $post_id);
                break;
        }
    }

    public function get_all_result_column_set($post) {

        $result_final_array = array();
        $all_result_array = $this->PSC_Common_Function->get_post_meta_all($post->ID);
        $order_responce = '';
        $psc_cart_serialize = '';
        if (isset($all_result_array['_order_responce'])) {
            $order_responce = $all_result_array['_order_responce'];
            unset($all_result_array['_order_responce']);
        }
        if (isset($all_result_array['_psc_cart_serialize'])) {
            $psc_cart_serialize = $all_result_array['_psc_cart_serialize'];
            unset($all_result_array['_psc_cart_serialize']);
        }
        $order_responce_unserialize = $this->PSC_Common_Function->get_unserialize_data($order_responce);
        $psc_cart_unserialize = $this->PSC_Common_Function->get_unserialize_data($psc_cart_serialize);
        array_push($result_final_array, $all_result_array);
        array_push($result_final_array, $order_responce_unserialize);
        array_push($result_final_array, $psc_cart_unserialize);
        return $result_final_array;
    }

    public function psc_get_order_status($result_array) {
        $result = '';
        if (isset($result_array[0]['_order_action_status']) && !empty($result_array[0]['_order_action_status'])) {
            $result = '<abbr class="' . $result_array[0]['_order_action_status'] . ' psctips" title="' . ucfirst($result_array[0]['_order_action_status']) . '"><img src="' . PSC_FOR_WORDPRESS_LOG_DIR . 'admin/images/order/' . $result_array[0]['_order_action_status'] . '.png"></abbr>';
        }
        return $result;
    }

    public function psc_get_order_details($id, $result_array) {
        $result = '';

        $result .= esc_html('#' . $id . ' By ') . esc_html($result_array['_shipping_full_name']) . '<br>' . esc_html($result_array['_billing_email']);

        return $result;
    }

    public function psc_get_order_qty($result_array) {
        $result = 0;
        if (is_array($result_array)) {
            foreach ($result_array as $key => $value) {
                $qty = $value['qty'];
                $result = $result + $qty;
            }
        }
        return $result . ' Item';
    }

    public function psc_get_order_ship_to($result_array) {

        $result = '';

        if (isset($result_array['_shipping_company']) && !empty($result_array['_shipping_company'])) {
            $result = $result_array['_shipping_company'] . ', ';
        }
        if (isset($result_array['_shipping_full_name']) && !empty($result_array['_shipping_full_name'])) {
            $result .= $result_array['_shipping_full_name'] . ', ';
        }
        if (isset($result_array['_shipping_address_1']) && !empty($result_array['_shipping_address_1'])) {
            $result .= $result_array['_shipping_address_1'] . ', ';
        }
        if (isset($result_array['_shipping_address_2']) && !empty($result_array['_shipping_address_2'])) {
            $result .= $result_array['_shipping_address_2'] . ', ';
        }
        if (isset($result_array['_shipping_city']) && !empty($result_array['_shipping_city'])) {
            $result .= $result_array['_shipping_city'] . ' - ';
        }
        if (isset($result_array['_shipping_postcode']) && !empty($result_array['_shipping_postcode'])) {
            $result .= $result_array['_shipping_postcode'] . ', ';
        }
        if (isset($result_array['_shipping_state']) && !empty($result_array['_shipping_state'])) {
            $result .= $this->PSC_Common_Function->two_digit_get_statecode_to_state($result_array['_shipping_country'], $result_array['_shipping_state']) . ', ';
        }
        if (isset($result_array['_shipping_country']) && !empty($result_array['_shipping_country'])) {
            $result .= $this->PSC_Common_Function->two_digit_get_countrycode_to_country($result_array['_shipping_country']);
        }

        return $result;
    }

    public function psc_get_order_date($post_id) {
        $date_formate = get_the_date('Y-m-d', $post_id);
        return $date_formate;
    }

    public function psc_get_order_total($methods, $result_array) {
        $result = '';
        $currency_symbole = $this->PSC_Common_Function->get_psc_currency_symbol_only();
        $AMT = 0;

        if (isset($result_array[0]['CURRENCYCODE']) && !empty($result_array[0]['CURRENCYCODE'])) {
            $currency_symbole = Paypal_Shopping_Cart_General_Setting::get_paypal_shopping_cart_currency_symbol($result_array[0]['CURRENCYCODE']);
        }

        if (isset($result_array[0]['AMT']) && !empty($result_array[0]['AMT'])) {
            $AMT = $result_array[0]['AMT'];
        }

        $result = esc_html($currency_symbole . '' . number_format($AMT, 2)) . '<br>' . esc_html($methods);
        return $result;
    }

    public function psc_get_order_status_action($result_array, $order_id) {
        $result = '';


        if (isset($result_array[0]['_order_action_status']) && 'on-hold' == $result_array[0]['_order_action_status']) {
            $result = '<p>
                            <a class="button tips cancelled psc-order-action-now" href="' . add_query_arg(array("psc-api" => "Paypal_Shopping_Cart_Order_Action_Now", "psc_action" => "cancelled", "psc_order" => $order_id), home_url()) . '"><img src="' . PSC_FOR_WORDPRESS_LOG_DIR . 'admin/images/order/cancelled.png"></a>
                            <a class="button tips processing psc-order-action-now" href="' . add_query_arg(array("psc-api" => "Paypal_Shopping_Cart_Order_Action_Now", "psc_action" => "processing", "psc_order" => $order_id), home_url()) . '"><img src="' . PSC_FOR_WORDPRESS_LOG_DIR . 'admin/images/order/processing.png"></a>
                            <a class="button tips complete psc-order-action-now" href="' . add_query_arg(array("psc-api" => "Paypal_Shopping_Cart_Order_Action_Now", "psc_action" => "completed", "psc_order" => $order_id), home_url()) . '"><img src="' . PSC_FOR_WORDPRESS_LOG_DIR . 'admin/images/order/completed.png"></a>
                            <a class="button tips view psc-order-action-now" href="' . add_query_arg(array("psc-api" => "Paypal_Shopping_Cart_Order_Action_Now", "psc_action" => "view", "psc_order" => $order_id), home_url()) . '"><img src="' . PSC_FOR_WORDPRESS_LOG_DIR . 'admin/images/order/view.png"></a>
                      </p>';
        } else if (isset($result_array[0]['_order_action_status']) && 'processing' == $result_array[0]['_order_action_status']) {
            $result = '<p>    
                            <a class="button tips processing psc-order-action-now" href="' . add_query_arg(array("psc-api" => "Paypal_Shopping_Cart_Order_Action_Now", "psc_action" => "cancelled", "psc_order" => $order_id), home_url()) . '"><img src="' . PSC_FOR_WORDPRESS_LOG_DIR . 'admin/images/order/cancelled.png"></a>                            
                            <a class="button tips complete psc-order-action-now" href="' . add_query_arg(array("psc-api" => "Paypal_Shopping_Cart_Order_Action_Now", "psc_action" => "completed", "psc_order" => $order_id), home_url()) . '"><img src="' . PSC_FOR_WORDPRESS_LOG_DIR . 'admin/images/order/completed.png"></a>
                            <a class="button tips view psc-order-action-now" href="' . add_query_arg(array("psc-api" => "Paypal_Shopping_Cart_Order_Action_Now", "psc_action" => "view", "psc_order" => $order_id), home_url()) . '"><img src="' . PSC_FOR_WORDPRESS_LOG_DIR . 'admin/images/order/view.png"></a>
                      </p>';
        } else if (isset($result_array[0]['_order_action_status']) && 'cancelled' == $result_array[0]['_order_action_status']) {
            $result = '<p> 
                            <a class="button tips view psc-order-action-now" href="' . add_query_arg(array("psc-api" => "Paypal_Shopping_Cart_Order_Action_Now", "psc_action" => "view", "psc_order" => $order_id), home_url()) . '"><img src="' . PSC_FOR_WORDPRESS_LOG_DIR . 'admin/images/order/view.png"></a>
                      </p>';
        } else if (isset($result_array[0]['_order_action_status']) && 'completed' == $result_array[0]['_order_action_status']) {
            $result = '<p> 
                            <a class="button tips view psc-order-action-now" href="' . add_query_arg(array("psc-api" => "Paypal_Shopping_Cart_Order_Action_Now", "psc_action" => "view", "psc_order" => $order_id), home_url()) . '"><img src="' . PSC_FOR_WORDPRESS_LOG_DIR . 'admin/images/order/view.png"></a>
                      </p>';
        }
        return $result;
    }

    public function psc_order_table_sorting($columns) {

        $columns['order'] = 'order';
        $columns['date'] = 'date';
        $columns['total'] = 'total';
        return $columns;
    }

    public function set_custom_edit_psc_product_columns($columns) {
        unset($columns['date']);
        unset($columns['title']);
        unset($columns['comments']);
        unset($columns['taxonomy-psc_category']);
        $columns['pscimage'] = __('<img src="' . PSC_FOR_WORDPRESS_LOG_DIR . 'admin/images/gallery.png">', 'pal-shopping-cart');
        $columns['title'] = __('Name', 'pal-shopping-cart');
        $columns['pscsku'] = __('SKU', 'pal-shopping-cart');
        $columns['pscstock'] = __('Stock', 'pal-shopping-cart');
        $columns['pscprice'] = __('Price', 'pal-shopping-cart');
        $columns['taxonomy-psc_category'] = __('Category', 'pal-shopping-cart');
        $columns['psctype'] = __('Type', 'pal-shopping-cart');
        $columns['date'] = __('Date', 'pal-shopping-cart');
        return $columns;
    }

    public function custom_psc_product_columns($column, $post_id) {
        global $post;

        $currency_code = $this->PSC_Common_Function->get_psc_currency();
        $result_array = $this->PSC_Common_Function->get_post_meta_all($post->ID);
        $img_url = $this->PSC_Common_Function->make_product_image_by_id($post->ID);
        if (isset($img_url) && empty($img_url)) {
            $img_url = PSC_FOR_WORDPRESS_LOG_DIR . 'admin/images/placeholder.png';
        }
        $post_name = (isset($post->post_name) ? $post->post_name : '');
        $psc_sku = (isset($result_array['_psc_sku']) ? $result_array['_psc_sku'] : '');
        $psc_product_type_dropdown = (isset($result_array['psc-product-type-dropdown']) ? $result_array['psc-product-type-dropdown'] : '');
        
        switch ($column) {
            case 'pscimage' :
                echo '<img src="' . esc_url($img_url) . '" heigth="50" width="50">';
                break;
            case 'title' :
                echo esc_html($post_name);
                break;
            case 'pscsku' :
                echo esc_html($psc_sku);
                break;
            case 'pscstock' :
                echo $this->psc_get_product_stock($result_array);
                break;
            case 'pscprice' :
                echo $this->psc_get_product_price($currency_code, $result_array);
                break;
            case 'taxonomy-psc_category' :
                break;
            case 'psctype' :
                echo esc_html($psc_product_type_dropdown);
                break;
            case 'date' :
                echo esc_html('date');
                break;
        }
    }

    public function psc_get_product_price($currency_code, $result_array) {

        $result = '';
        $currency_symbol = Paypal_Shopping_Cart_General_Setting::get_paypal_shopping_cart_currency_symbol($currency_code);

        if ($result_array['psc-product-type-dropdown'] == 'simple') {

            $psc_regular_price = (isset($result_array['_psc_regular_price'])) ? $result_array['_psc_regular_price'] : '';
            $psc_sale_price = (isset($result_array['_psc_sale_price'])) ? $result_array['_psc_sale_price'] : '';

            if (!empty($psc_regular_price)) {
                $result .= '<del><span class="amount">' . $currency_symbol . '' . number_format($psc_regular_price, 2) . '</span></del>';
            }
            if (!empty($psc_sale_price)) {
                $result .= '<ins><span class="amount">' . $currency_symbol . '' . number_format($psc_sale_price, 2) . '</span></ins>';
            }
        } else if ($result_array['psc-product-type-dropdown'] == 'variable') {

            $result_variable_array = $this->PSC_Common_Function->get_unserialize_data($result_array['psc_variable_product_data']);

            foreach ($result_variable_array as $key => $value) {

                $regular = $value['psc_variable_product_regular_price' . $key];
                $sale = $value['psc_variable_product_sale_price' . $key];

                if (!empty($regular)) {
                    $result .= '<del><span class="amount">' . $currency_symbol . '' . number_format($regular, 2) . '</span></del> ';
                }
                if (!empty($sale)) {
                    $result .= '<ins><span class="amount">' . $currency_symbol . '' . number_format($sale, 2) . '</span></ins>';
                }
                $result .= '<br>';
            }
        }

        return $result;
    }

    public function psc_get_product_stock($result_array) {
        $result = '';
        $Qty = 0;
        $Stock = '';
        $Name = '';
        $variable_product = '';

        if (isset($result_array['psc-product-type-dropdown']) && !empty($result_array['psc-product-type-dropdown'])) {
            if ($result_array['psc-product-type-dropdown'] == 'simple') {

                $Qty = $result_array['_psc_stock_qty_' . $result_array['psc-product-type-dropdown']];
                $Stock = $result_array['_psc_stock_status_' . $result_array['psc-product-type-dropdown']];

                if (isset($result_array['_psc_manage_stock_simple']) && $result_array['_psc_manage_stock_simple'] == 'yes') {
                    if ($result_array['_psc_stock_qty_simple'] != 0) {
                        $result = '<span>' . $result_array['post_title'] . ': </span><span style="color:#77a464;">' . ucfirst('instock') . '</span> × ' . $result_array['_psc_stock_qty_simple'];
                    } else {
                        $result = '<span>' . $result_array['post_title'] . ': </span><span style="color:#a44;">' . ucfirst('outofstock') . '</span>';
                    }
                } else {
                    $result = '<span>' . $result_array['post_title'] . ': </span><span style="color:#77a464;">' . ucfirst('instock') . '</span>';
                }
            } else if ($result_array['psc-product-type-dropdown'] = 'variable') {

                if (isset($result_array['psc_variable_product_data']) && !empty($result_array['psc_variable_product_data'])) {

                    $result_variable_array = $this->PSC_Common_Function->get_unserialize_data($result_array['psc_variable_product_data']);

                    foreach ($result_variable_array as $key => $value) {

                        $Name = $value['psc_variable_product_name' . $key];
                        $Qty = $value['psc_variable_product_stock' . $key];
                        $Stock = $value['psc_variable_product_status' . $key];

                        if (isset($value['_psc_manage_stock_variable' . $key]) && $value['_psc_manage_stock_variable' . $key] == '1') {
                            if ($Qty != 0) {

                                $result .= '<span>' . $Name . ': </span><span style="color:#77a464;">' . ucfirst('instock') . '</span> × <span>' . $Qty . '</span><br />';
                            } else {

                                $result .= '<span>' . $Name . ': </span><span style="color:#a44;">' . ucfirst('outofstock') . '</span> × <span>' . $Qty . '</span><br />';
                            }
                        } else {
                            $result .= '<span>' . $Name . ': </span><span style="color:#77a464;">' . ucfirst('instock') . '</span><br />';
                        }
                    }
                }
            }
        }
        return $result;
    }

    public function psc_product_table_sorting($columns) {
        $columns['name'] = 'name';
        $columns['sku'] = 'sku';
        $columns['price'] = 'price';
        $columns['date'] = 'date';
        return $columns;
    }

    public function set_custom_edit_psc_coupon_columns($columns) {
        unset($columns['date']);
        unset($columns['title']);
        $columns['title'] = __('Code', 'pal-shopping-cart');
        $columns['coupon_type'] = __('Coupon type', 'pal-shopping-cart');
        $columns['coupon_amount'] = __('Coupon amount', 'pal-shopping-cart');
        $columns['discription'] = __('Description', 'pal-shopping-cart');
        $columns['expiry_date'] = __('Expiry date', 'pal-shopping-cart');
        return $columns;
    }

    public function custom_psc_coupon_columns($column, $post_id) {
        global $post;

        $get_current_coupons_details = $this->PSC_Common_Function->get_post_meta_all($post->ID);

        switch ($column) {
            case 'title' :
                echo esc_html($post->post_title);
                break;
            case 'coupon_type' :
                echo esc_html($this->get_full_name_coupon_type($get_current_coupons_details['psc_coupon_discount_type']));
                break;
            case 'coupon_amount' :
                echo esc_html(($get_current_coupons_details['psc_coupon_amount']) ? $this->PSC_Common_Function->get_psc_currency_symbol_only().$get_current_coupons_details['psc_coupon_amount'] : 0);
                break;
            case 'discription' :
                echo esc_html(($get_current_coupons_details['excerpt']) ? $get_current_coupons_details['excerpt'] : '');
                break;
            case 'expiry_date' :
                echo esc_html(($get_current_coupons_details['psc_coupon_expiry_date']) ? $get_current_coupons_details['psc_coupon_expiry_date'] : '');
                break;
        }
    }

    public function get_full_name_coupon_type($psc_coupon_discount_type) {
        $result = '';

        if ($psc_coupon_discount_type == 'fixed_cart') {
            $result = 'Cart Discount';
        }

        return $result;
    }

}