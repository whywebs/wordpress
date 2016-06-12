<?php

if (!defined('ABSPATH')) {
    exit;
}

class PSC_AJAX_Handler {

    public static function init() {
        add_action('wp_ajax_psc_add_to_cart_item', array(__CLASS__, 'psc_add_to_cart_item'));
        add_action('wp_ajax_nopriv_psc_add_to_cart_item', array(__CLASS__, 'psc_add_to_cart_item'));
        add_action('wp_ajax_psc_update_cart_item', array(__CLASS__, 'psc_update_cart_item'));
        add_action('wp_ajax_nopriv_psc_update_cart_item', array(__CLASS__, 'psc_update_cart_item'));
        add_action('wp_ajax_psc_update_country_state', array(__CLASS__, 'psc_update_country_state'));
        add_action('wp_ajax_nopriv_psc_update_country_state', array(__CLASS__, 'psc_update_country_state'));
        add_action('wp_ajax_psc_update_cart_with_coupon', array(__CLASS__, 'psc_update_cart_with_coupon'));
        add_action('wp_ajax_nopriv_psc_update_cart_with_coupon', array(__CLASS__, 'psc_update_cart_with_coupon'));
        add_action('wp_ajax_psc_select_variable_product', array(__CLASS__, 'psc_select_variable_product'));
        add_action('wp_ajax_nopriv_psc_select_variable_product', array(__CLASS__, 'psc_select_variable_product'));
        add_action('wp_ajax_psc_add_class_shop_page', array(__CLASS__, 'psc_add_class_shop_page'));
        add_action('wp_ajax_nopriv_psc_add_class_shop_page', array(__CLASS__, 'psc_add_class_shop_page'));
        add_action('wp_ajax_set_update_cart_session_result', array(__CLASS__, 'set_update_cart_session_result'));
        add_action('wp_ajax_nopriv_set_update_cart_session_result', array(__CLASS__, 'set_update_cart_session_result'));
    }

    public static function psc_add_to_cart_item() {

        check_ajax_referer('paypal_shopping_cart_url', 'security');
        $psc_product_id = !empty($_POST['value']) ? $_POST['value'] : false;
        $PSC_Common_Function = new PSC_Common_Function();
        $cart_item_obj = new PSC_Cart_Handler();

        $PID = (sanitize_text_field($psc_product_id[0])) ? sanitize_text_field($psc_product_id[0]) : '';
        $PACTION = (sanitize_text_field($psc_product_id[1])) ? sanitize_text_field($psc_product_id[1]) : '';
        $PQTY = (intval($psc_product_id[2])) ? intval($psc_product_id[2]) : '';
        $PNAME = (sanitize_text_field($psc_product_id[3])) ? sanitize_text_field($psc_product_id[3]) : '';

        $is_stock_is = '';

        if (is_array($psc_product_id) && "insert" == $PACTION) {

            $is_stock_is = self::is_stock_available_current_product_id($PID, $PNAME);

            if (isset($PNAME) && !empty($PNAME)) {
                self::is_variations_product_update_OR_insert($PSC_Common_Function, $cart_item_obj, $is_stock_is, $PID, $PQTY, $PNAME);
            } else {
                self::is_simple_product_update_OR_insert($PSC_Common_Function, $cart_item_obj, $is_stock_is, $PID, $PQTY, $PNAME);
            }
        } elseif (is_array($psc_product_id) && "delete" == $PACTION) {
            self::is_product_cart_item_delete($cart_item_obj, $PID);
        }
        echo esc_html('success');
        die();
    }

    public static function is_variations_product_update_OR_insert($PSC_Common_Function, $cart_item_obj, $is_stock_is, $PID, $PQTY, $PNAME) {

        try {

            $result_product = $PSC_Common_Function->get_post_meta_all($PID);
            $result_unsirealize = $PSC_Common_Function->get_unserialize_data($result_product['psc_variable_product_data']);

            foreach ($result_unsirealize as $key => $value) {

                if ($PNAME == $value['psc_variable_product_name' . $key]) {

                    $md5_id = md5($PID . '_' . $PNAME);
                    $is_exist = self::is_product_rowid_exist($PSC_Common_Function, $md5_id);
                    $is_md5_id_exist = $is_exist[0];
                    $is_qty = $is_exist[1];


                    if ((isset($is_stock_is) && $is_stock_is > 0) || (isset($is_stock_is) && $is_stock_is == 'instock')) {

                        if ($is_md5_id_exist == true) {

                            self::is_update_product_cart($cart_item_obj, $md5_id, $is_qty);
                        } else {

                            self::is_insert_variations_product_cart($cart_item_obj, $PID, $PNAME, $PQTY, $key, $value, $result_product);
                        }
                    }
                }
            }

            return;
        } catch (Exception $ex) {
            
        }
    }

    public static function is_simple_product_update_OR_insert($PSC_Common_Function, $cart_item_obj, $is_stock_is, $PID, $PQTY, $PNAME) {

        try {


            $result_product = $PSC_Common_Function->get_post_meta_all($PID);
            $md5_id = md5($PID);

            $is_exist = self::is_product_rowid_exist($PSC_Common_Function, $md5_id);

            $is_md5_id_exist = $is_exist[0];
            $is_qty = $is_exist[1];

            if ((isset($is_stock_is) && $is_stock_is > 0) || (isset($is_stock_is) && $is_stock_is == 'instock')) {

                if ($is_md5_id_exist == true) {

                    self::is_update_product_cart($cart_item_obj, $md5_id, $is_qty);
                } else {

                    self::is_insert_simple_product_cart($cart_item_obj, $PID, $PQTY, $result_product);
                }
            }
        } catch (Exception $ex) {
            
        }
    }

    public static function is_product_rowid_exist($PSC_Common_Function, $md5_id) {

        try {
            $result = array();
            $result[0] = false;

            $Get_Cart_Result = $PSC_Common_Function->session_cart_contents();

            foreach ($Get_Cart_Result as $key => $value) {

                if ($md5_id == $key) {
                    $result[0] = true;
                    $result[1] = $value['qty'];
                }
            }

            return $result;
        } catch (Exception $ex) {
            
        }
    }

    public static function is_stock_available_current_product_id($post_id, $productname) {

        try {

            $result = 0;
            $PSC_Common_Function = new PSC_Common_Function();
            $result = $PSC_Common_Function->get_update_stock_by_post_id($post_id, $productname);

            return $result;
        } catch (Exception $ex) {
            
        }
    }

    public static function is_update_product_cart($cart_item_obj, $md5_id, $is_qty) {

        try {
            $data = array('rowid' => $md5_id, 'qty' => $is_qty + 1);
            $cart_item_obj->update($data);
            return;
        } catch (Exception $ex) {
            
        }
    }

    public static function is_insert_variations_product_cart($cart_item_obj, $PID, $PNAME, $PQTY, $key, $value, $result_product) {

        try {

            $insert_data = array(
                'id' => $PID . '_' . $PNAME,
                'name' => ($result_product['post_name']) ? $result_product['post_name'] . ':' . $PNAME : $result_product['post_title'] . ':' . $PNAME,
                'price' => $value['psc_variable_product_sale_price' . $key],
                'qty' => $PQTY
            );

            $cart_item_obj->insert($insert_data);

            return;
        } catch (Exception $ex) {
            
        }
    }

    public static function is_insert_simple_product_cart($cart_item_obj, $PID, $PQTY, $result_product) {

        try {

            $insert_data = array(
                'id' => $PID,
                'name' => ($result_product['post_name']) ? $result_product['post_name'] : $result_product['post_title'],
                'price' => $result_product['_psc_sale_price'],
                'qty' => $PQTY
            );

            $cart_item_obj->insert($insert_data);

            return;
        } catch (Exception $ex) {
            
        }
    }

    public static function is_product_cart_item_delete($cart_item_obj, $PID) {

        try {

            $data = array(
                'rowid' => $PID,
                'qty' => 0
            );
            $cart_item_obj->update($data);
            return;
        } catch (Exception $ex) {
            
        }
    }

    public static function psc_update_cart_item() {
        check_ajax_referer('paypal_shopping_cart_url', 'security');
        $psc_product_id = !empty($_POST['value']) ? $_POST['value'] : false;

        $update_cart_result = false;

        if (is_array($psc_product_id)) {
            foreach ($psc_product_id as $value) {

                $is_stock_is = self::is_stock_available_current_product_id(intval($value['id']), sanitize_text_field($value['name']));

                if ((isset($is_stock_is) && $is_stock_is > 0) || (isset($is_stock_is) && $is_stock_is == 'instock')) {

                    $remove_to_cart_item = new PSC_Cart_Handler();
                    $remove_to_cart_item->update($value);
                }
            }
        }

        echo esc_html($update_cart_result);
        die();
    }

    public static function set_update_cart_session_result() {
        check_ajax_referer('paypal_shopping_cart_url', 'security');
        $psc_product_id = !empty($_POST['value']) ? $_POST['value'] : false;
        $psc_product_id = sanitize_text_field($psc_product_id);
        $PSC_Common_Function = new PSC_Common_Function();
        $PSC_Common_Function->session_set('update_cart_message', 'success');
        echo esc_html('success');
        die();
    }

    public static function psc_update_country_state() {
        global $states;
        check_ajax_referer('paypal_shopping_cart_url', 'security');
        $code = !empty($_POST['value']) ? $_POST['value'] : false;
        if (file_exists(plugin_dir_path(dirname(__FILE__)) . 'templates/countries/states/' . sanitize_text_field($code[1]) . '.php')) {
            require plugin_dir_path(dirname(__FILE__)) . 'templates/countries/states/' . sanitize_text_field($code[1]) . '.php';
            $result = self::countries_state_option($states, sanitize_text_field($code[0]));
            echo json_encode($result);
            die();
        } else {
            echo esc_html('nofound');
            die();
        }
    }

    public static function countries_state_option($states, $address_state) {

        try {
            $result = '<select name="' . $address_state . '" id="' . $address_state . '" class="psc-custom-select ' . $address_state . '">';
            foreach ($states as $data) {
                foreach ($data as $key => $value) {
                    $result .= '<option value="' . $key . '" >' . $value . '</option>';
                }
            }
            return $result . '</select>';
        } catch (Exception $ex) {
            
        }
    }

    public static function psc_update_cart_with_coupon() {
        check_ajax_referer('paypal_shopping_cart_url', 'security');
        $coupon_code = !empty($_POST['value']) ? $_POST['value'] : false;
        $coupon_code = sanitize_text_field($coupon_code);
        $PSC_Common_Function = new PSC_Common_Function();
        $result = $PSC_Common_Function->get_all_coupons_with_match($coupon_code);

        if ($result['code_match'] == 'true') {
            $PSC_Common_Function->session_set('coupon_cart_discount', $result['psc_coupon_amount']);
            $PSC_Common_Function->session_set('coupon_cart_discount_msg', $result['psc_coupon_status']);
            $PSC_Common_Function->session_set('coupon_code', $result['coupon_code']);
            echo esc_html($result['psc_coupon_status']);
            die();
        } else {
            $PSC_Common_Function->session_set('coupon_cart_discount_msg', $result['psc_coupon_status']);
            echo esc_html($result['psc_coupon_status']);
            die();
        }
    }

    public static function psc_select_variable_product() {
        check_ajax_referer('paypal_shopping_cart_url', 'security');
        $select_array = !empty($_POST['value']) ? $_POST['value'] : false;
        $PSC_Common_Function = new PSC_Common_Function();
        $get_array_result = $PSC_Common_Function->get_post_meta_all(intval($select_array[1]));
        $symbol = $PSC_Common_Function->get_psc_currency_symbol_only();
        $psc_variable_product_data = unserialize(trim($get_array_result['psc_variable_product_data']));
        $psc_variable_product_data = unserialize($psc_variable_product_data);
        $data = "";
        foreach ($psc_variable_product_data as $key => $value) {
            if (sanitize_text_field($select_array[0]) == $value['psc_variable_product_name' . $key]) {

                $data['product_details'] = self::get_product_store_details($symbol, $key, $value);
                $data['product_addcart'] = self::get_product_store_status_and_storege($key, $value, intval($select_array[1]), $PSC_Common_Function);
                $data['product_stock'] = self::get_product_store_storege($key, $value, intval($select_array[1]), $PSC_Common_Function);
            }
        }
        echo json_encode($data);
        die();
    }

    public static function get_product_store_details($symbol, $key, $value) {

        $result = "";
        $del_not_empty = '';

        if (isset($value['psc_variable_product_regular_price' . $key]) && strlen($value['psc_variable_product_regular_price' . $key]) > 0) {
            $del_not_empty = '<del><span class="amount">' . $symbol . number_format($value['psc_variable_product_regular_price' . $key], 2) . '</span></del>&nbsp;&nbsp;&nbsp;';
        }

        if ($value['_psc_manage_stock_variable' . $key] == '1') {

            $status_class = 'out-of-stock';
            $status = 'Out of stock';

            if ($value['psc_variable_product_stock' . $key] != 0) {
                $status_class = 'in-stock';
                $status = $value['psc_variable_product_stock' . $key] . ' In stock';
            }

            $result = '<p class="price">' . $del_not_empty . '<ins><span class="amount">' . $symbol . number_format($value['psc_variable_product_sale_price' . $key], 2) . '</span></ins></p><p class="stock ' . $status_class . '">' . $status . '</p>';
        } else {

            $status_class = 'in-stock';
            $status = 'In stock';
            $result = '<p class="price">' . $del_not_empty . '<ins><span class="amount">' . $symbol . number_format($value['psc_variable_product_sale_price' . $key], 2) . '</span></ins></p><p class="stock ' . $status_class . '">' . $status . '</p>';
        }
        return $result;
    }

    public static function get_product_store_storege($key, $value, $postid, $PSC_Common_Function) {
        $result = true;

        if ($value['_psc_manage_stock_variable' . $key] == 1) {
            if ($value['psc_variable_product_stock' . $key] != 0) {
                $result = true;
            } else {
                $result = false;
            }
        } else {
            $result = true;
        }


        return $result;
    }

    public static function get_product_store_status_and_storege($key, $value, $postid, $PSC_Common_Function) {
        $result = "";

        if ($value['_psc_manage_stock_variable' . $key] == 1) {
            if ($value['psc_variable_product_stock' . $key] != 0) {
                $result = '<input type="number" name="psc_quantity" id="psc_quantity" value="1" min="1" max="' . $value['psc_variable_product_stock' . $key] . '"><input type="hidden" name="add-to-cart" value="' . $postid . '"><input type="hidden" name="psc_available_stock" id="psc_available_stock" value="' . $value['psc_variable_product_stock' . $key] . '" />';
                $result .= '<a id="psc_add_to_cart_button' . $postid . '" rel="nofollow" psc-product-id="' . $postid . '" class="psc-button psc_add_to_cart_button product_type_simple">Add to cart <span class="pac_add_to_cart_process process_now' . $postid . '" hidden=""></span></a>';
                $result .= '<a href="' . get_permalink($PSC_Common_Function->psc_addtocart_after_redirect_page()) . '" class="view_cart' . $postid . ' view_cart" title="View Cart" hidden="">View Cart</a>';
            } else {
                $result = '<input type="number" name="psc_quantity" id="psc_quantity" value="0" min="" max="' . $value['psc_variable_product_stock' . $key] . '" disabled><input type="hidden" name="add-to-cart" value="' . $postid . '"><input type="hidden" name="psc_available_stock" id="psc_available_stock" value="' . $value['psc_variable_product_stock' . $key] . '" />';
                $result .= '<a style="pointer-events: none;cursor: default; background-color:#E8E8E8" id="psc_add_to_cart_button' . $postid . '" rel="nofollow" psc-product-id="' . $postid . '" class="psc-button psc_add_to_cart_button product_type_simple">Add to cart <span class="pac_add_to_cart_process process_now' . $postid . '" hidden=""></span></a>';
                $result .= '<a href="' . get_permalink($PSC_Common_Function->psc_addtocart_after_redirect_page()) . '" class="view_cart' . $postid . ' view_cart" title="View Cart" hidden="">View Cart</a>';
            }
        } else {
            $result = '<input type="number" name="psc_quantity" id="psc_quantity" min="1" max="" value="1"><input type="hidden" name="add-to-cart" value="' . $postid . '"><input type="hidden" name="psc_available_stock" id="psc_available_stock" value="nolimit" />';
            $result .= '<a id="psc_add_to_cart_button' . $postid . '" rel="nofollow" psc-product-id="' . $postid . '" class="psc-button psc_add_to_cart_button product_type_simple">Add to cart <span class="pac_add_to_cart_process process_now' . $postid . '" hidden=""></span></a>';
            $result .= '<a href="' . get_permalink($PSC_Common_Function->psc_addtocart_after_redirect_page()) . '" class="view_cart' . $postid . ' view_cart" title="View Cart" hidden="">View Cart</a>';
        }


        return $result;
    }

    public static function psc_add_class_shop_page() {
        check_ajax_referer('paypal_shopping_cart_url', 'security');
        $id = !empty($_POST['value']) ? $_POST['value'] : false;
        $id[0] = intval($id[0]);
        $PSC_Common_Function = new PSC_Common_Function();
        $get_result = $PSC_Common_Function->psc_shop_page();

        if (isset($get_result) && !empty($get_result)) {
            if ($get_result == $id[0]) {
                echo esc_html('true');
                die();
            } else {
                echo esc_html('false');
                die();
            }
        } else {
            echo esc_html('false');
            die();
        }
    }

}

PSC_AJAX_Handler::init();