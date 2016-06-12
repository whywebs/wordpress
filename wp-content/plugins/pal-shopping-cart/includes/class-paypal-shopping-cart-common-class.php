<?php

class PSC_Common_Function {

    public $obj_session_heandler;
    public $obj_cart_heandler;

    public function __construct() {
        $this->obj_session_heandler = new PSC_Session_Handler();
        $this->obj_cart_heandler = new PSC_Cart_Handler();
    }

    public function psc_product_gallery_attachment_ids($post) {
        return apply_filters('psc_product_gallery_attachment_ids', array_filter((array) explode(',', $this->product_image_gallery)), $this);
    }

    public function psc_get_price_html($post) {

        $price = '';
        $get_array_result = $this->get_post_meta_all($post->ID);
        $currency_code = get_option('psc_currency_general_settings');
        if (empty($currency_code)) {
            $currency_code = 'USD';
        }
        $currency_symbol = Paypal_Shopping_Cart_General_Setting::get_paypal_shopping_cart_currency_symbol($currency_code);

        if (isset($get_array_result['_psc_regular_price']) && !empty($get_array_result['_psc_regular_price'])) {
            $price = '<del><span class="amount">' . $currency_symbol . number_format($get_array_result['_psc_regular_price'], 2) . '</span></del>&nbsp;&nbsp;&nbsp;';
        }
        if (isset($get_array_result['_psc_sale_price']) && !empty($get_array_result['_psc_sale_price'])) {
            $price .='<ins><span class="amount">' . $currency_symbol . number_format($get_array_result['_psc_sale_price'], 2) . '</span></ins>';
        }
        return $price;
    }

    public function psc_get_price_variable_and_simple($post) {

        $price = '';
        $price_del = '';
        $price_ins = '';
        $get_array_result = $this->get_post_meta_all($post->ID);
        $currency_code = get_option('psc_currency_general_settings');
        if (empty($currency_code)) {
            $currency_code = 'USD';
        }
        $currency_symbol = Paypal_Shopping_Cart_General_Setting::get_paypal_shopping_cart_currency_symbol($currency_code);

        $display_outofstock_product = get_option('psc_shop_display_outofstock_product') ? get_option('psc_shop_display_outofstock_product') : 'yes';
        $is_display_product = true;
        $is_display_product_found = true;
        if (isset($get_array_result['psc-product-type-dropdown']) && 'variable' == $get_array_result['psc-product-type-dropdown']) {

            $psc_variable_product_data = unserialize(trim($get_array_result['psc_variable_product_data']));
            $psc_variable_product_data = unserialize($psc_variable_product_data);
            $options = '';

            foreach ($psc_variable_product_data as $key => $value) {

                if ($display_outofstock_product == 'no') {

                    $is_display_product = $this->display_outof_stock_product($key, $value);
                }

                if ($is_display_product) {

                    $options .='<option value="' . $value['psc_variable_product_name' . $key] . '">' . $value['psc_variable_product_name' . $key] . '</option>';
                    if ($is_display_product_found) {
                        $stock_data = $this->load_time_product_stock_check($key, $value, $currency_symbol);
                        $is_display_product_found = false;
                    }
                }
            }

            $price = $stock_data . '<div class="psc_select_variable_product_div"><select id="psc_select_variable_product" product-id="' . $post->ID . '" style="padding: 5px; margin-bottom: 10px">' . $options . '</select></div>';
        } else {

            if (isset($get_array_result['_psc_regular_price']) && !empty($get_array_result['_psc_regular_price'])) {
                $price = '<del><span class="amount">' . $currency_symbol . number_format($get_array_result['_psc_regular_price'], 2) . '</span></del>&nbsp;&nbsp;&nbsp;';
            }
            if (isset($get_array_result['_psc_sale_price']) && !empty($get_array_result['_psc_sale_price'])) {
                $price .='<ins><span class="amount">' . $currency_symbol . number_format($get_array_result['_psc_sale_price'], 2) . '</span></ins>';
            }

            $price = '<p class="price">' . $price . '</p>';
        }


        return $price;
    }

    public function load_time_product_stock_check($key, $value, $currency_symbol) {

        $result = "";
        $stock_data = "";

        if (isset($value['_psc_manage_stock_variable' . $key]) && '0' != $value['_psc_manage_stock_variable' . $key]) {

            if ($value['psc_variable_product_stock' . $key] != '0') {
                $stock_data = '<p class="stock in-stock">' . $value['psc_variable_product_stock' . $key] . ' In stock </p>';
            } else {
                $stock_data = '<p class="stock out-of-stock">Out of stock</p>';
            }
        }

        $result = '<div class="psc-product-details-price">';
        $result .= '<p class="price"><del><span class="amount">' . $currency_symbol . number_format($value['psc_variable_product_regular_price' . $key], 2) . '</span></del>&nbsp;&nbsp;&nbsp;';
        $result .= '<ins><span class="amount">' . $currency_symbol . number_format($value['psc_variable_product_sale_price' . $key], 2) . '</span></ins></p>';
        $result .= $stock_data . '</div>';

        return $result;
    }

    public function display_outof_stock_product($key, $value) {

        $result = false;

        if ($value['_psc_manage_stock_variable' . $key] == '1') {

            if ($value['psc_variable_product_stock' . $key] > '0') {
                $result = true;
            }
            
        } else {

           // if ($value['psc_variable_product_stock_status' . $key] == '1') {
                $result = true;
           // }
        }

        return $result;
    }

    public function psc_get_price($post) {

        $result = '';
        $result_data = $this->get_post_meta_data_postid($post);
        $is_stock_enable = false;
        $product_qty = 0;
        $display_outofstock_product = get_option('psc_shop_display_outofstock_product') ? get_option('psc_shop_display_outofstock_product') : 'yes';
        if (isset($result_data['psc-product-type-dropdown'][0]) && $result_data['psc-product-type-dropdown'][0] == 'simple') {

            if ($result_data['_psc_manage_stock_simple'][0] == "yes") {
                $product_qty = $result_data['_psc_stock_qty_simple'][0];
                if ($product_qty > 0) {
                    $is_stock_enable = true;
                }
            } else if ($result_data['_psc_manage_stock_simple'][0] == "no") {
                $is_stock_enable = true;
            }
        } else if (isset($result_data['psc-product-type-dropdown'][0]) && $result_data['psc-product-type-dropdown'][0] == 'variable') {
            $result_product = $this->get_post_meta_all($post->ID);
            $result_unsirealize = $this->get_unserialize_data($result_product['psc_variable_product_data']);
            foreach ($result_unsirealize as $key => $value) {
                if ($value['_psc_manage_stock_variable' . $key] == 1) {

                    if ($display_outofstock_product == 'no' && $value['psc_variable_product_stock' . $key] > 0) {
                        return ($value['psc_variable_product_sale_price' . $key]) ? $value['psc_variable_product_sale_price' . $key] : $value['psc_variable_product_regular_price' . $key];
                    } else if ($display_outofstock_product == 'yes') {
                        if ($value['psc_variable_product_stock' . $key] != 0) {
                            return ($value['psc_variable_product_sale_price' . $key]) ? $value['psc_variable_product_sale_price' . $key] : $value['psc_variable_product_regular_price' . $key];
                        } else {
                            return;
                        }
                    }
                } else {
                    return ($value['psc_variable_product_sale_price' . $key]) ? $value['psc_variable_product_sale_price' . $key] : $value['psc_variable_product_regular_price' . $key];
                }
            }
        }
        if ($is_stock_enable == true) {
            if ((isset($result_data['_psc_sale_price'][0]) && !empty($result_data['_psc_sale_price'][0]))) {
                $result = isset($result_data['_psc_sale_price'][0]) ? $result_data['_psc_sale_price'][0] : $result_data['_psc_regular_price'][0];
            } else {
                $result = "";
            }
        } else {
            $result = "";
        }

        return $result;
    }

    public function get_psc_currency() {
        $result = '';
        $result = get_option('psc_currency_general_settings');
        if (empty($result)) {
            $result = 'USD';
        }
        return $result;
    }

    public function get_psc_currency_symbol($currency_code) {
        $result = '';
        $result = Paypal_Shopping_Cart_General_Setting::get_paypal_shopping_cart_currency_symbol($currency_code);
        return $result;
    }

    public function get_psc_currency_symbol_only() {
        $result = '';

        $code = get_option('psc_currency_general_settings');
        if (empty($code)) {
            $code = 'USD';
        }
        $result = Paypal_Shopping_Cart_General_Setting::get_paypal_shopping_cart_currency_symbol($code);

        return $result;
    }

    public function get_post_meta_data_postid($post) {
        $result = '';
        $result = get_post_meta($post->ID);
        return $result;
    }

    public function psc_short_dec($post) {
        $result = '';
        $result_data = $this->get_post_meta_data_postid($post);
        if (isset($result_data['_wp_editor_test_1'][0]) && !empty($result_data['_wp_editor_test_1'][0])) {
            $result = $result_data['_wp_editor_test_1'][0];
        }
        return $result;
    }

    public function psc_get_sku($post) {
        $result = '';
        $result_data = $this->get_post_meta_data_postid($post);
        if (isset($result_data['_psc_sku'][0]) && !empty($result_data['_psc_sku'][0])) {
            $result = $result_data['_psc_sku'][0];
        }
        return $result;
    }

    public function psc_readmore_text($post) {

        return get_permalink();
    }

    public function psc_add_to_cart_url($post) {
        $url = remove_query_arg('added-to-cart', add_query_arg('add-to-cart', $post->ID));
        return apply_filters('psc_product_add_to_cart_url', $url, $post);
    }

    public function psc_get_product_type($post) {
        $result = '';
        $result_data = $this->get_post_meta_data_postid($post);
        if (isset($result_data['psc-product-type-dropdown'][0]) && !empty($result_data['psc-product-type-dropdown'][0])) {
            $result = $result_data['psc-product-type-dropdown'][0];
        }
        return $result;
    }

    public function psc_add_to_cart_class($post) {

        $result = '';
        $result_data = $this->get_post_meta_data_postid($post);
        if (isset($result_data['_psc_regular_price'][0]) || isset($result_data['_psc_sale_price'][0])) {
            $result = "psc_add_to_cart_button";
        }
        return $result;
    }

    public function psc_add_to_cart_text($post) {

        $result = '';
        $result_data = $this->get_post_meta_all($post->ID);

        $product_qty = 0;
        if (isset($result_data['psc-product-type-dropdown']) && $result_data['psc-product-type-dropdown'] == 'simple') {

            if ($result_data['_psc_manage_stock_simple'] == "yes") {
                $product_qty = $result_data['_psc_stock_qty_simple'];
                if ($product_qty > 0) {
                    return "Add to cart";
                } else {
                    return "Read More";
                }
            } else if ((isset($result_data['_psc_regular_price']) && !empty($result_data['_psc_regular_price'])) || (isset($result_data['_psc_sale_price']) && !empty($result_data['_psc_regular_price']))) {
                return "Add to cart";
            }
        } else if (isset($result_data['psc-product-type-dropdown']) && $result_data['psc-product-type-dropdown'] == 'variable') {
            return "Select Option";
        }
    }

    public function get_post_meta_data_product_id($post_id) {
        $result = array();
        $result = get_post_meta($post_id);
        if (strlen($result['post_name'][0]) == 0) {
            $name = get_the_title($post_id);
            $result['post_name'][0] = $name;
        }
        return $result;
    }

    public function cart_get_image($id) {
        $size = 'psc_shop_thumbnail';
        $attr = array();
        if (has_post_thumbnail($id)) {
            $image = get_the_post_thumbnail($id, $size, $attr);
        } elseif (( $parent_id = wp_get_post_parent_id($id) ) && has_post_thumbnail($parent_id)) {
            $image = get_the_post_thumbnail($parent_id, $size, $attr);
        } else {
            $image = "<img src='" . apply_filters('psc_placeholder_img_src', plugins_url('/admin/images/placeholder.png', dirname(__FILE__))) . "'>";
        }
        return $image;
    }

    public function get_permalink($id) {
        return get_permalink($id);
    }

    public function is_enable_payment_methods() {
        $methos = array();
        $all_methods_array = array();
        $psc_pec_enable = get_option('psc_pec_enabled');

        if ($psc_pec_enable == 'yes') {

            $methos['enable'] = $psc_pec_enable;
            $methos['name'] = get_option('psc_pec_title');
            $methos['discription'] = get_option('psc_pec_description');
        }
        array_push($all_methods_array, $methos);

        return $all_methods_array;
    }

    public function is_empty_filed_found() {
        global $empty_filed;
        $empty_filed = '';
        if (isset($_POST['submit'])) {
            if (!empty($_POST['billing_first_name'])) {
                $empty_filed['first_name'] = sanitize_text_field($_POST['billing_first_name']);
            } else {
                $empty_filed['first_name'] = 'empty';
            }
            if (!empty($_POST['billing_last_name'])) {
                $empty_filed['last_name'] = sanitize_text_field($_POST['billing_last_name']);
            } else {
                $empty_filed['last_name'] = 'empty';
            }
            if (!empty($_POST['billing_email'])) {
                $empty_filed['email'] = sanitize_email($_POST['billing_email']);
            } else {
                $empty_filed['email'] = 'empty';
            }
            if (!empty($_POST['billing_phone'])) {
                $empty_filed['phone'] = intval($_POST['billing_phone']);
            } else {
                $empty_filed['phone'] = 'empty';
            }
            if (!empty($_POST['billing_country']) && $_POST['billing_country'] != 'select') {
                $empty_filed['country'] = sanitize_text_field($_POST['billing_country']);
            } else {
                $empty_filed['country'] = 'empty';
            }
            if (!empty($_POST['billing_address_1'])) {
                $empty_filed['address1'] = sanitize_text_field($_POST['billing_address_1']);
            } else {
                $empty_filed['address1'] = 'empty';
            }
            if (!empty($_POST['billing_city'])) {
                $empty_filed['city'] = sanitize_text_field($_POST['billing_city']);
            } else {
                $empty_filed['city'] = 'empty';
            }
            if (!empty($_POST['billing_state'])) {
                $empty_filed['state'] = sanitize_text_field($_POST['billing_state']);
            } else {
                $empty_filed['state'] = 'empty';
            }
            if (!empty($_POST['billing_postcode'])) {
                $empty_filed['postcode'] = intval($_POST['billing_postcode']);
            } else {
                $empty_filed['postcode'] = 'empty';
            }
            $this->redirect_payment_getway($empty_filed);
        }
        return;
    }

    public function redirect_payment_getway($empty_filed) {
        try {
            $result = true;
            foreach ($empty_filed as $key => $value) {
                if ($value == 'empty') {
                    $result = false;
                    break;
                }
            }
            if ($result == true) {
                if ($_POST['psc_payment_method'] == '1') {
                    $express_methods_obj = new Paypal_Shopping_Cart_Express_Checkout();
                    $express_methods_obj->paypal_express_checkout($_POST);
                }
            }
        } catch (Exception $ex) {
            
        }
    }

    public function get_result_of_order_received_data($orderid) {
        global $wpdb;
        $result_meta = "";
        $array_of_order_detail = array();
        $post_id = $wpdb->get_results("SELECT post_id FROM `" . $wpdb->postmeta . "` WHERE meta_key='" . $orderid . "'");
        if (isset($post_id[0]->post_id) && !empty($post_id[0]->post_id)) {
            $result_meta = $this->get_post_meta_all($post_id[0]->post_id);
            $array_of_order_detail['_orderid'] = $post_id[0]->post_id;
            $array_of_order_detail['_orderdate'] = get_the_date('d-F-Y', $post_id[0]->post_id);
            array_push($result_meta, $array_of_order_detail);
        }
        return $result_meta;
    }

    public function get_post_meta_all($post_id) {
        global $wpdb;
        $data = array();
        $wpdb->query(" SELECT `meta_key`, `meta_value` FROM $wpdb->postmeta WHERE `post_id` = $post_id");
        foreach ($wpdb->last_result as $k => $v) {
            $data[$v->meta_key] = $v->meta_value;
        };
        return $data;
    }

    public function two_digit_get_statecode_to_state($country_code, $statecode) {

        global $states;
        $state_name = '';

        if ((isset($country_code) && !empty($country_code)) && (isset($statecode) && !empty($statecode))) {
            if (file_exists(PSC_PLUGIN_DIR_PATH . '/templates/countries/states/' . $country_code . '.php')) {
                require PSC_PLUGIN_DIR_PATH . '/templates/countries/states/' . $country_code . '.php';
            }
            foreach ($states as $key => $value) {
                foreach ($value as $state_code => $state_name) {
                    if ($state_code == $statecode) {
                        return $state_name;
                    }
                }
            }
        }
        return $state_name;
    }

    public function two_digit_get_countrycode_to_country($country_code) {
        $country_obj = new PSC_Countries();
        $country_array = $country_obj->Countries();
        foreach ($country_array as $key => $value) {
            if ($key == $country_code) {
                return $value;
            }
        }
        return;
    }

    public function get_customer_address() {
        $result = '';
        $shiptoname = explode(' ', $this->session_get('shiptoname'));
        $firstname = esc_html($shiptoname[0]);
        $lastname = esc_html($shiptoname[1]);
        $result .= esc_html($firstname) . ' ' . esc_html($lastname) . '<br>';
        $result .= esc_html($this->session_get('shiptostreet')) . '<br>';
        $result .= ($this->session_get('shiptostreet2')) ? esc_html($this->session_get('shiptostreet2')) . '<br> ' : '';
        $result .= esc_html($this->session_get('shiptocity') . ', ' . $this->session_get('shiptostate') . '(' . $this->two_digit_get_statecode_to_state($this->session_get('shiptocountrycode'), $this->session_get('shiptostate')) . ') ' . $this->session_get('shiptozip')) . '<br>';
        $result .= esc_html($this->two_digit_get_countrycode_to_country($this->session_get('shiptocountrycode')));

        return $result;
    }

    public function get_unserialize_data($data) {
        $result = '';
        $result = unserialize(trim($data));
        return unserialize($result);
    }

    public function is_enable_stock_management($post) {

        $result_data = $this->get_post_meta_all($post->ID);
        $is_stock_enable = false;
        if (isset($result_data['psc-product-type-dropdown']) && $result_data['psc-product-type-dropdown'] == 'simple') {
            if ($result_data['_psc_manage_stock_simple'] == "yes") {
                $is_stock_enable = true;
            }
        } else if (isset($result_data['psc-product-type-dropdown']) && $result_data['psc-product-type-dropdown'] == 'variable') {

            $result_unsirealize = $this->get_unserialize_data($result_data['psc_variable_product_data']);
            
            foreach ($result_unsirealize as $key => $value) {
                
                if ($value['_psc_manage_stock_variable' . $key] == 1) {
                    $is_stock_enable = true;
                    break;
                }
                break;
            }
        }
        return $is_stock_enable;
    }
    
    public function is_enable_stock_management_old($post) {

        $result_data = $this->get_post_meta_data_postid($post);
        $is_stock_enable = false;
        if (isset($result_data['psc-product-type-dropdown'][0]) && $result_data['psc-product-type-dropdown'][0] == 'simple') {
            if ($result_data['_psc_manage_stock_simple'][0] == "yes") {
                $is_stock_enable = true;
            }
        } else if (isset($result_data['psc-product-type-dropdown'][0]) && $result_data['psc-product-type-dropdown'][0] == 'variable') {
            if ($result_data['_psc_manage_stock_variable'][0] == "yes") {
                $is_stock_enable = true;
            }
        }

        return $is_stock_enable;
    }

    public function psc_get_product_stock($post) {

        $result = '';
        $result_data = $this->get_post_meta_all($post->ID);
        $is_stock_enable = false;
        $product_qty = 0;
        if (isset($result_data['psc-product-type-dropdown']) && $result_data['psc-product-type-dropdown'] == 'simple') {

            if ($result_data['_psc_manage_stock_simple'] == "yes") {
                $product_qty = ($result_data['_psc_stock_qty_simple'])?$result_data['_psc_stock_qty_simple']:0;
                $is_stock_enable = true;
            }
        } else if (isset($result_data['psc-product-type-dropdown']) && $result_data['psc-product-type-dropdown'] == 'variable') {

            $result_unsirealize = $this->get_unserialize_data($result_data['psc_variable_product_data']);

            foreach ($result_unsirealize as $key => $value) {

                if ($value['_psc_manage_stock_variable' . $key] == 1) {
                    $product_qty = ($value['psc_variable_product_stock' . $key])?$value['psc_variable_product_stock' . $key]:0;
                    $is_stock_enable = true;
                    break;
                }
                break;
            }

        }
        if ($is_stock_enable == true) {
            if ((isset($product_qty) && '0' != $product_qty)) {
                $result = $product_qty;
            } else {
                $result = $product_qty;
            }
        } else {
            $result = 0;
        }
        return $result;
    }

    public function get_order_all_details($post_id) {
        $result_array = $this->get_post_meta_all($post_id);
        $order_responce = '';
        $psc_cart_serialize = '';
        $final_merge_array = array();
        if (is_array($result_array) && count($result_array) > 0) {
            if (isset($result_array['_shipping_state']) && !empty($result_array['_shipping_state'])) {
                $state_name = $this->two_digit_get_statecode_to_state($result_array['_shipping_country'], $result_array['_shipping_state']);
                unset($result_array['_shipping_state']);
                $result_array['_shipping_state'] = $state_name;
            }
            if (isset($result_array['_shipping_country']) && !empty($result_array['_shipping_country'])) {
                $country_name = $this->two_digit_get_countrycode_to_country($result_array['_shipping_country']);
                unset($result_array['_shipping_country']);
                $result_array['_shipping_country'] = $country_name;
            }
            if (isset($result_array['_order_responce'])) {
                $order_responce = $this->get_unserialize_data($result_array['_order_responce']);
                unset($result_array['_order_responce']);
            }
            if (isset($result_array['_psc_cart_serialize'])) {
                $psc_cart_serialize = $this->get_unserialize_data($result_array['_psc_cart_serialize']);
                unset($result_array['_psc_cart_serialize']);
            }
            array_push($final_merge_array, $result_array);
            array_push($final_merge_array, $order_responce);
            array_push($final_merge_array, $psc_cart_serialize);
        }


        return $final_merge_array;
    }

    public function get_order_item_details($post_id) {
        $result_array = $this->get_post_meta_all($post_id);
        $order_item_array = array();
        $currency_code = $this->get_psc_currency();
        $currency_symbol = Paypal_Shopping_Cart_General_Setting::get_paypal_shopping_cart_currency_symbol($currency_code);

        $total = 0;
        if (isset($result_array['_psc_cart_serialize'])) {
            $psc_cart_serialize = $this->get_unserialize_data($result_array['_psc_cart_serialize']);
            foreach ($psc_cart_serialize as $key => $value) {

                $array_Item = array();
                $array_Item['id'] = $value['id'];
                $array_Item['name'] = $value['name'];
                $array_Item['price'] = $value['price'];
                $array_Item['qty'] = $value['qty'];
                $array_Item['subtotal'] = $value['subtotal'];
                $array_Item['url'] = $this->make_product_image_by_id($value['id']);
                $total = $total + $value['subtotal'];
                array_push($order_item_array, $array_Item);
            }
        }
        $order_item_array['order_cart_discount'] = $result_array['_order_cart_discount'];
        $order_item_array['order_cart_discount_coupon_code'] = $result_array['_order_cart_discount_coupon_code'];
        $order_item_array['final_total'] = $total;
        $order_item_array['currency_symbol'] = $currency_symbol;

        return $order_item_array;
    }

    public function make_product_image_by_id($id) {
        $url = "";

        $url_data = $this->cart_get_image($id);
        preg_match('/src=(\'|")+[^(\'|")]+/', $url_data, $image_url);

        if (isset($image_url[0])) {
            $url = preg_replace('/src=(\'|")/', "", $image_url[0]);
        }
        if (isset($url) && empty($url)) {
            $url = PSC_FOR_WORDPRESS_LOG_DIR . 'admin/images/placeholder.png';
        }
        return $url;
    }

    public function session_set($key, $value) {
        $this->obj_session_heandler->set_userdata($key, $value);
    }

    public function session_get($key) {
        return $this->obj_session_heandler->userdata($key);
    }

    public function session_remove($key) {
        $this->obj_session_heandler->unset_userdata($key);
    }

    public function session_cart_contents() {
        return $this->obj_cart_heandler->contents();
    }

    public function session_cart_contents_total($key) {
        return $this->obj_cart_heandler->_cart_contents[$key];
    }

    public function session_cart_destroy() {
        $this->obj_cart_heandler->destroy();
    }

    public function customer_session_empty() {

        $this->session_remove('TOKEN');
        $this->session_remove('PayerID');
        $this->session_remove('chosen_shipping_methods');
        $this->session_remove('company');
        $this->session_remove('firstname');
        $this->session_remove('lastname');
        $this->session_remove('shiptoname');
        $this->session_remove('shiptostreet');
        $this->session_remove('shiptostreet2');
        $this->session_remove('shiptocity');
        $this->session_remove('shiptocountrycode');
        $this->session_remove('shiptostate');
        $this->session_remove('shiptozip');
        $this->session_remove('payeremail');
        $this->session_remove('customer_notes');
        $this->session_remove('phonenum');
        $this->session_remove('order_id');
        $this->session_remove('coupon_cart_discount');
        $this->session_remove('coupon_code');
        $this->session_remove('coupon_cart_discount_msg');
    }

    public function psc_write_log_activity($handle, $message) {
        $psclog = new Paypal_Shopping_Cart_Logger();
        $psclog->add($handle, $message);
    }

    public function psc_write_log_activity_array($handle, $title, $message) {
        $psclog = new Paypal_Shopping_Cart_Logger();

        $error_key = $message['ERRORS'];
        unset($message['RAWREQUEST']);
        $message['REQUESTDATA'] = $this->pattern_to_star($message['REQUESTDATA']);
        $psclog->add($handle, $title . print_r($message, true));
        return;
    }

    public function pattern_to_star($result) {

        foreach ($result as $key => $value) {
            if ("USER" == $key || "PWD" == $key || "BUTTONSOURCE" == $key || "SIGNATURE" == $key || "EMAIL" == $key) {

                $str_length = strlen($value);
                $ponter_data = "";
                for ($i = 0; $i <= $str_length; $i++) {
                    $ponter_data .= '*';
                }
                $result[$key] = $ponter_data;
            }
        }

        return $result;
    }

    public function psc_get_page_id_by_title($title) {
        $result = '';
        $result = get_page_by_title($title);
        return $result;
    }

    public function psc_pscrevieworder_page($title) {
        $result = '';
        $result = get_page_by_title($title);
        $result = $result->ID;
        return $result;
    }

    public function psc_checkout_page() {
        $result = '';
        $result = get_option('psc_checkoutpage_product_settings');
        if (empty($result)) {
            $checkoutpage = get_page_by_title('Checkout');
            $result = $checkoutpage->ID;
        }
        return $result;
    }

    public function psc_cart_page() {
        $result = '';
        $result = get_option('psc_cartpage_product_settings');
        if (empty($result)) {
            $cartpage = get_page_by_title('Cart');
            $result = $cartpage->ID;
        }
        return $result;
    }

    public function psc_shop_page() {
        $result = '';
        $result = get_option('psc_shoppage_product_settings');
        if (empty($result)) {
            $shoppage = get_page_by_title('Shop');
            $result = $shoppage->ID;
        }
        return $result;
    }

    public function psc_addtocart_after_redirect_page() {
        $result = '';
        $result = (get_option('psc_addtocart_after_general_settings')) ? get_option('psc_addtocart_after_general_settings') : '';
        if (empty($result)) {
            $cartpage = get_page_by_title('Cart');
            $result = $cartpage->ID;
        }
        return $result;
    }

    public function get_all_coupons_with_match($coupon_code) {
        global $wpdb;
        $result = array();
        $result['code_match'] = false;
        $result['coupon_code'] = $coupon_code;
        $psc_coupons = $wpdb->get_results("SELECT * FROM `" . $wpdb->posts . "` WHERE `post_type` = 'psc_coupon' and `post_status` = 'publish'");

        if (is_array($psc_coupons) && count($psc_coupons) > 0) {
            foreach ($psc_coupons as $key => $value) {

                if (trim($value->post_title) == trim($coupon_code)) {

                    $postmeta_data = $this->get_post_meta_all($value->ID);
                    if (date("Y-m-d") <= $postmeta_data['psc_coupon_expiry_date']) {
                        $result['code_match'] = true;
                        $result['psc_coupon_amount'] = $postmeta_data['psc_coupon_amount'];
                        $result['psc_coupon_expiry_date'] = $postmeta_data['psc_coupon_expiry_date'];
                        $result['psc_coupon_status'] = 'success';
                    } else {

                        $result['psc_coupon_status'] = 'This coupon has expired.';
                    }
                } else {
                    $result['psc_coupon_status'] = 'Coupon "' . $coupon_code . '" does not exist!';
                }
            }
        } else {
            $result['psc_coupon_status'] = 'Coupon "' . $coupon_code . '" does not exist!';
        }

        return $result;
    }

    public function psc_is_enable_coupons() {
        $result = '';
        $result = (get_option('psc_coupons_general_settings')) == 'yes' ? true : false;
        return $result;
    }

    public function get_cart_total_discount() {
        $result = 0;
        $discount_amount = $this->session_get('coupon_cart_discount');
        if (isset($discount_amount) && '0' != $discount_amount) {
            $result = $discount_amount;
        }
        return $result;
    }

    public function get_cart_total_coupon_code() {
        $result = '';
        $coupon_code = $this->session_get('coupon_code');
        if (isset($coupon_code) && !empty($coupon_code)) {
            $result = $coupon_code;
        }
        return $result;
    }

    public function get_cart_array() {
        $result = array();
        $cart_total = $this->session_cart_contents_total('cart_total');
        $cart = $this->session_cart_contents();
        $discount = $this->get_cart_total_discount();
        $result['itemamt'] = number_format($cart_total, '2');
        $cart_array = array();

        if (is_array($cart) && count($cart)) {
            foreach ($cart as $key => $value) {
                $result_data = '';
                $result_data['name'] = $value['name'];
                $result_data['amt'] = $value['price'];
                $result_data['number'] = $value['rowid'];
                $result_data['qty'] = $value['qty'];

                array_push($cart_array, $result_data);
            }
        }

        if (isset($discount) && '0' != $discount) {

            $coupons = $this->get_cart_total_coupon_code();

            $result_data = '';
            $result_data['name'] = 'Cart Discount';
            $result_data['amt'] = '-' . number_format(round($discount), '2');
            $result_data['number'] = $coupons;
            $result_data['qty'] = '1';

            array_push($cart_array, $result_data);

            $result['itemamt'] = number_format($cart_total - $discount, '2');
        }
        $result['cart_item'] = $cart_array;
        return $result;
    }

    public function get_stock_by_post_id($post_id) {
        $result = 0;
        $meta_array = $this->get_post_meta_all($post_id);

        if ($meta_array['psc-product-type-dropdown'] == 'simple') {
            $result = $meta_array['_psc_stock_qty_simple'];
        } else if ($meta_array['psc-product-type-dropdown'] == 'variable') {
            $psc_variable_product_data = unserialize(trim($meta_array['psc_variable_product_data']));
            $psc_variable_product_data = unserialize($psc_variable_product_data);
            $result = $psc_variable_product_data[0]['psc_variable_product_stock0'];
        }

        return $result;
    }

    public function get_stock_status_by_post_id($post_id) {

        $result = false;

        $meta_array = $this->get_post_meta_all($post_id);

        if ($meta_array['psc-product-type-dropdown'] == 'simple') {

            if ($meta_array['_psc_manage_stock_simple'] == 'yes') {

                if ($meta_array['_psc_stock_qty_simple'] > 0) {
                    $result = true;
                }
            } else {
                if ($meta_array['_psc_stock_status_simple'] == 'instock') {
                    $result = true;
                }
            }
        } else if ($meta_array['psc-product-type-dropdown'] == 'variable') {

            $psc_variable_product_data = unserialize(trim($meta_array['psc_variable_product_data']));
            $psc_variable_product_data = unserialize($psc_variable_product_data);

            foreach ($psc_variable_product_data as $key => $value) {

                if ($value['_psc_manage_stock_variable' . $key] == '1') {

                    if ($value['psc_variable_product_stock' . $key] > 0) {
                        $result = true;
                        break;
                    }
                } else {

                    if ($value['psc_variable_product_stock_status'] == '1') {
                        $result = true;
                        break;
                    }
                }
            }
        }

        return $result;
    }

    public function get_update_stock_by_post_id($post_id, $pname = "") {

        $result = '';

        $meta_array = $this->get_post_meta_all($post_id);
        if ($meta_array['psc-product-type-dropdown'] == 'simple') {

            if ($meta_array['_psc_manage_stock_simple'] == 'yes') {
                $result = $meta_array['_psc_stock_qty_simple'];
            } else {
                $result = 'instock';
            }
        } else if ($meta_array['psc-product-type-dropdown'] == 'variable') {

            $psc_variable_product_data = unserialize(trim($meta_array['psc_variable_product_data']));
            $psc_variable_product_data = unserialize($psc_variable_product_data);

            if (is_array($psc_variable_product_data) && count($psc_variable_product_data) > 0) {
                foreach ($psc_variable_product_data as $key => $value) {

                    if ($pname == $value['psc_variable_product_name' . $key]) {
                        if ('1' == $value['_psc_manage_stock_variable' . $key]) {
                            $result = $value['psc_variable_product_stock' . $key];
                            return $result;
                        } else {
                            return 'instock';
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function psc_product_price_empty($post_id) {

        $result = false;

        $meta_array = $this->get_post_meta_all($post_id);
        if ($meta_array['psc-product-type-dropdown'] == 'simple') {

            if (isset($meta_array['_psc_sale_price']) && $meta_array['_psc_sale_price'] != 0 && $meta_array['_psc_sale_price'] > 0) {
                $result = true;
            }
        } else if ($meta_array['psc-product-type-dropdown'] == 'variable') {

            $psc_variable_product_data = unserialize(trim($meta_array['psc_variable_product_data']));
            $psc_variable_product_data = unserialize($psc_variable_product_data);

            if (is_array($psc_variable_product_data) && count($psc_variable_product_data) > 0) {

                foreach ($psc_variable_product_data as $key => $value) {

                    if ($value['psc_variable_product_sale_price' . $key] != 0 && $value['psc_variable_product_sale_price' . $key] > 0) {
                        $result = true;
                        break;
                    }
                }
            }
        }
        return $result;
    }

}