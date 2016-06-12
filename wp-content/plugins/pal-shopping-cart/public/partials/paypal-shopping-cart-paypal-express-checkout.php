<?php

ob_start();

class Paypal_Shopping_Cart_Express_Checkout {

    public $obj_abstract_product;

    public function __construct() {
        $this->id = 'paypal_express';
        $this->method_title = __('PayPal Express Checkout ', 'pal-shopping-cart');
        $this->method_description = __('PayPal Express Checkout is designed to make the checkout experience for buyers using PayPal much more quick and easy than filling out billing and shipping forms.  Customers will be taken directly to PayPal to sign in and authorize the payment, and are then returned back to your store to choose a shipping method, review the final order total, and complete the payment.', 'pal-shopping-cart');
        $this->has_fields = false;
        $this->enabled = (get_option('psc_pec_enabled')) ? get_option('psc_pec_enabled') : 'no';
        $this->title = (get_option('psc_pec_title')) ? get_option('psc_pec_title') : '';
        $this->description = (get_option('psc_pec_description')) ? get_option('psc_pec_description') : '';
        $this->api_username = (get_option('psc_pec_sandbox_api_username')) ? get_option('psc_pec_sandbox_api_username') : '';
        $this->api_password = (get_option('psc_pec_sandbox_api_password')) ? get_option('psc_pec_sandbox_api_password') : '';
        $this->api_signature = (get_option('psc_pec_sandbox_api_signature')) ? get_option('psc_pec_sandbox_api_signature') : '';
        $this->testmode = (get_option('psc_pec_testmode')) ? get_option('psc_pec_testmode') : 'no';
        $this->debug = (get_option('psc_pec_debug')) ? get_option('psc_pec_debug') : 'no';
        $this->invoice_id_prefix = (get_option('psc_pec_invoice_id_prefix')) ? get_option('psc_pec_invoice_id_prefix') : '';
        $this->paypal_account_optional = (get_option('psc_pec_paypal_account_optional')) ? get_option('psc_pec_paypal_account_optional') : '';
        $this->landing_page = (get_option('psc_pec_landing_page')) ? get_option('psc_pec_landing_page') : '';
        $this->use_wp_locale_code = (get_option('psc_pec_use_wp_locale_code')) ? get_option('psc_pec_use_wp_locale_code') : '';
        $this->brand_name = (get_option('psc_pec_brand_name')) ? get_option('psc_pec_brand_name') : '';
        $this->checkout_logo = (get_option('psc_pec_checkout_logo')) ? get_option('psc_pec_checkout_logo') : '';
        $this->checkout_logo_hdrimg = (get_option('psc_pec_checkout_logo_hdrimg')) ? get_option('psc_pec_checkout_logo_hdrimg') : '';
        $this->customer_service_number = (get_option('psc_pec_customer_service_number')) ? get_option('psc_pec_customer_service_number') : '';
        $this->skip_final_review = (get_option('psc_pec_skip_final_review')) ? get_option('psc_pec_skip_final_review') : 'no';
        $this->payment_action = (get_option('psc_pec_payment_action')) ? get_option('psc_pec_payment_action') : '';
        $this->billing_address = (get_option('psc_pec_billing_address')) ? get_option('psc_pec_billing_address') : '';
        $this->send_items = (get_option('psc_pec_send_items')) ? get_option('psc_pec_send_items') : '';
        $this->psc_currency = (get_option('psc_currency_general_settings')) ? get_option('psc_currency_general_settings') : 'USD';
        $this->customer_id = get_current_user_id();
        if ($this->testmode == 'yes') {
            $this->API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
            $this->PAYPAL_URL = "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=";
            $this->api_username = (get_option('psc_pec_sandbox_api_username')) ? get_option('psc_pec_sandbox_api_username') : '';
            $this->api_password = (get_option('psc_pec_sandbox_api_password')) ? get_option('psc_pec_sandbox_api_password') : '';
            $this->api_signature = (get_option('psc_pec_sandbox_api_signature')) ? get_option('psc_pec_sandbox_api_signature') : '';
        } else {
            $this->API_Endpoint = "https://api-3t.paypal.com/nvp";
            $this->PAYPAL_URL = "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=";
        }
        $this->version = "64";
        $this->obj_abstract_product = new PSC_Common_Function();
        $this->coupon_cart_discount = $this->obj_abstract_product->get_cart_total_discount();
        $this->coupon_cart_discount_coupon_code = $this->obj_abstract_product->get_cart_total_coupon_code();
    }

    function psc_calculate_cart_totals() {
        $total_cart = 0;
        $total_cart = $this->obj_abstract_product->session_cart_contents_total('cart_total');
        return $total_cart;
    }

    function paypal_express_checkout($posted = null) {
        
        if ((( isset($_POST['psc_action']) && !empty($_POST['psc_action']) ) || ( isset($posted) && !empty($posted) ) ) && ( $posted['psc_action'] != 'pscpayaction' && $_POST['psc_payment_method'] == '1') || ( $posted['psc_action'] == 'PayPalExpressCheckout' && $posted['psc_payment_method'] == '1' )) {

            if (sizeof($this->obj_abstract_product->session_cart_contents()) > 0) {
                if (isset($_GET['use_paypal_credit']) && 'true' == $_GET['use_paypal_credit']) {
                    $usePayPalCredit = true;
                } else {
                    $usePayPalCredit = false;
                }
                $this->psc_calculate_cart_totals();
                $paymentAmount = number_format($this->psc_calculate_cart_totals(), 2, '.', '');

                //Check if review order page is exist, otherwise re-create it on the fly
                $psc_review_order_id = $this->obj_abstract_product->psc_pscrevieworder_page('pscrevieworder');
                if (isset($psc_review_order_id) && $psc_review_order_id != null) {
                    $review_order_page_url = get_permalink($psc_review_order_id);
                } else {
                    $page_id = $this->psc_create_page('pscrevieworder', 'pscrevieworder', '[pscrevieworder]');
                    $review_order_page_url = get_permalink($page_id);
                }

                $returnURL = urlencode(add_query_arg('psc_action', 'pscrevieworder', $review_order_page_url));
                $cancelURL = urlencode(get_permalink(get_option('psc_pec_cancel_page')));
                $resArray = $this->CallSetExpressCheckout($paymentAmount, $returnURL, $cancelURL, $usePayPalCredit, $posted);

                if ($this->debug == 'yes') {
                    $this->obj_abstract_product->psc_write_log_activity_array('paypal_shopping_cart_log', 'CallSetExpressCheckout', $resArray);
                }

                $ack = strtoupper($resArray["ACK"]);
                if ($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING") {
                    wp_redirect($resArray['REDIRECTURL']);
                    exit;
                } else {

                    $PSC_ERROR_DISP = array();
                    if (isset($resArray['ERRORS']) && count($resArray['ERRORS']) > 0) {

                        foreach ($resArray['ERRORS'] as $key => $value) {

                            $PSC_ERROR_DISP[$key] = $value;
                        }
                    } else if (isset($resArray['CURL_ERROR']) && strlen($resArray['CURL_ERROR']) > 0) {

                        $PSC_ERROR_DISP[0] = array('L_LONGMESSAGE' => $resArray['CURL_ERROR']);
                    }
                    $this->obj_abstract_product->session_set('PSC_PAYMENT_ERROR', $PSC_ERROR_DISP);
                    $page_redirect_error = "";
                    if (isset($posted['page']) && !empty($posted['page'])) {
                        if ($posted['page'] == 'single') {
                            $page_redirect_error = PSC_FOR_WORDPRESS_SITE_URL . 'psc_product/' . sanitize_text_field($posted['product_name']);
                        } else {
                            $page_current_id = $this->obj_abstract_product->psc_get_page_id_by_title(sanitize_text_field($posted['page']));
                            $page_redirect_error = get_permalink($page_current_id->ID);
                        }
                    } else {
                        $page_redirect_error = get_permalink($this->obj_abstract_product->psc_checkout_page());
                    }
                    wp_redirect($page_redirect_error);
                    exit;
                }
            }
        } elseif (isset($_GET['psc_action']) && $_GET['psc_action'] == 'pscrevieworder') {
            $country_array = new PSC_Countries();
            if (isset($_GET['token'])) {
                $token = sanitize_text_field($_GET['token']);
                $this->obj_abstract_product->session_set('TOKEN', $token);
            }
            if (isset($_GET['PayerID'])) {
                $payerID = sanitize_text_field($_GET['PayerID']);
                $this->obj_abstract_product->session_set('PayerID', $payerID);
            }
            $result = $this->CallGetShippingDetails($this->obj_abstract_product->session_get('TOKEN'));
            if ($this->debug == 'yes') {
                $this->obj_abstract_product->psc_write_log_activity_array('paypal_shopping_cart_log', 'CallGetShippingDetails', $result);
            }
            if (!empty($result)) {
                if (isset($result['SHIPTOCOUNTRYCODE'])) {
                    if (!array_key_exists($result['SHIPTOCOUNTRYCODE'], $country_array->Countries())) {
                        wp_redirect(get_permalink($this->obj_abstract_product->psc_cart_page()));
                        exit;
                    };
                }
                $this->obj_abstract_product->session_set('company', isset($result['BUSINESS']) ? $result['BUSINESS'] : '');
                $this->obj_abstract_product->session_set('firstname', isset($result['FIRSTNAME']) ? $result['FIRSTNAME'] : '');
                $this->obj_abstract_product->session_set('lastname', isset($result['LASTNAME']) ? $result['LASTNAME'] : '');
                $this->obj_abstract_product->session_set('shiptoname', isset($result['SHIPTONAME']) ? $result['SHIPTONAME'] : '');
                $this->obj_abstract_product->session_set('shiptostreet', isset($result['SHIPTOSTREET']) ? $result['SHIPTOSTREET'] : '');
                $this->obj_abstract_product->session_set('shiptostreet2', isset($result['SHIPTOSTREET2']) ? $result['SHIPTOSTREET2'] : '');
                $this->obj_abstract_product->session_set('shiptocity', isset($result['SHIPTOCITY']) ? $result['SHIPTOCITY'] : '');
                $this->obj_abstract_product->session_set('shiptocountrycode', isset($result['SHIPTOCOUNTRYCODE']) ? $result['SHIPTOCOUNTRYCODE'] : '');
                $this->obj_abstract_product->session_set('shiptostate', isset($result['SHIPTOSTATE']) ? $result['SHIPTOSTATE'] : '');
                $this->obj_abstract_product->session_set('shiptozip', isset($result['SHIPTOZIP']) ? $result['SHIPTOZIP'] : '');
                $this->obj_abstract_product->session_set('payeremail', isset($result['EMAIL']) ? $result['EMAIL'] : '');
                $this->obj_abstract_product->session_set('customer_notes', isset($result['PAYMENTREQUEST_0_NOTETEXT']) ? $result['PAYMENTREQUEST_0_NOTETEXT'] : '');
                $this->obj_abstract_product->session_set('phonenum', isset($result['SHIPTOPHONENUM']) ? $result['SHIPTOPHONENUM'] : '');
                $this->psc_calculate_cart_totals();

                if ($this->skip_final_review == 'yes') {
                    $this->Do_Payment_Confirm();
                } else {
                    $psc_order_view_page_id = $this->obj_abstract_product->psc_pscrevieworder_page('pscrevieworder');
                    wp_redirect(get_permalink($psc_order_view_page_id));
                }
            }
        } elseif (isset($_GET['psc_action']) && $_GET['psc_action'] == 'pscpayaction') {
            $this->Do_Payment_Confirm();
        }
    }

    function Do_Payment_Confirm() {

        $chosen_shipping_methods = 'Express Checkout';
        $order_id = $this->create_order();
        $this->obj_abstract_product->session_set('chosen_shipping_methods', $chosen_shipping_methods);
        $this->obj_abstract_product->session_set('order_id', $order_id);
        $this->psc_calculate_cart_totals();
        $shiptoname = explode(' ', $this->obj_abstract_product->session_get('shiptoname'));
        $firstname = $shiptoname[0];
        $lastname = $shiptoname[1];

        $shipping_first_name = $firstname;
        $shipping_last_name = $lastname;
        $full_name = $shipping_first_name . ' ' . $shipping_last_name;

        $this->obj_abstract_product->session_set('firstname', isset($result['FIRSTNAME']) ? $result['FIRSTNAME'] : $shipping_first_name);
        $this->obj_abstract_product->session_set('lastname', isset($result['LASTNAME']) ? $result['LASTNAME'] : $shipping_last_name);
        update_post_meta($order_id, '_payment_method', $this->id);
        update_post_meta($order_id, '_payment_method_title', $this->title);

        if (is_user_logged_in()) {
            $userLogined = wp_get_current_user();
            update_post_meta($order_id, '_billing_email', $userLogined->user_email);
        } else {
            update_post_meta($order_id, '_billing_email', $this->obj_abstract_product->session_get('payeremail'));
        }
        update_post_meta($order_id, '_shipping_first_name', $this->obj_abstract_product->session_get('firstname'));
        update_post_meta($order_id, '_shipping_last_name', $this->obj_abstract_product->session_get('lastname'));
        update_post_meta($order_id, '_shipping_full_name', $full_name);
        update_post_meta($order_id, '_shipping_company', $this->obj_abstract_product->session_get('company'));
        update_post_meta($order_id, '_billing_phone', $this->obj_abstract_product->session_get('phonenum'));
        update_post_meta($order_id, '_shipping_address_1', $this->obj_abstract_product->session_get('shiptostreet'));
        update_post_meta($order_id, '_shipping_address_2', $this->obj_abstract_product->session_get('shiptostreet2'));
        update_post_meta($order_id, '_shipping_city', $this->obj_abstract_product->session_get('shiptocity'));
        update_post_meta($order_id, '_shipping_postcode', $this->obj_abstract_product->session_get('shiptozip'));
        update_post_meta($order_id, '_shipping_country', $this->obj_abstract_product->session_get('shiptocountrycode'));
        update_post_meta($order_id, '_shipping_state', $this->obj_abstract_product->session_get('shiptostate'));
        update_post_meta($order_id, '_customer_user', get_current_user_id());
        if ($this->billing_address == 'yes') {
            update_post_meta($order_id, '_billing_first_name', $this->obj_abstract_product->session_get('firstname'));
            update_post_meta($order_id, '_billing_last_name', $this->obj_abstract_product->session_get('lastname'));
            update_post_meta($order_id, '_billing_full_name', $full_name);
            update_post_meta($order_id, '_billing_company', $this->obj_abstract_product->session_get('company'));
            update_post_meta($order_id, '_billing_address_1', $this->obj_abstract_product->session_get('shiptostreet'));
            update_post_meta($order_id, '_billing_address_2', $this->obj_abstract_product->session_get('shiptostreet2'));
            update_post_meta($order_id, '_billing_city', $this->obj_abstract_product->session_get('shiptocity'));
            update_post_meta($order_id, '_billing_postcode', $this->obj_abstract_product->session_get('shiptozip'));
            update_post_meta($order_id, '_billing_country', $this->obj_abstract_product->session_get('shiptocountrycode'));
            update_post_meta($order_id, '_billing_state', $this->obj_abstract_product->session_get('shiptostate'));
        }
        $result = $this->ConfirmPayment($this->psc_calculate_cart_totals());
        if (!get_current_user_id()) {
            update_post_meta($order_id, '_billing_first_name', $shipping_first_name);
            update_post_meta($order_id, '_billing_last_name', $shipping_last_name);
        }

        if ($result['ACK'] == 'Success' || $result['ACK'] == 'SuccessWithWarning') {

            $psc_order_id = $this->obj_abstract_product->psc_get_page_id_by_title('pscordercomplete');

            $order_page_url = '';
            if (isset($psc_order_id) && $psc_order_id != null) {
                $order_page_url = get_permalink($psc_order_id->ID);
            } else {
                $page_id = $this->psc_create_page('pscordercomplete', 'pscordercomplete', '[psc_complete_order]');
                $order_page_url = get_permalink($page_id);
            }
            $uniq_id_generatore = $order_id . '' . substr(microtime(), -10);
            $psc_cart_serialize = serialize($this->obj_abstract_product->session_cart_contents());
            update_post_meta($order_id, $uniq_id_generatore, $uniq_id_generatore);
            update_post_meta($order_id, '_psc_cart_subtotal', $this->psc_calculate_cart_totals());
            update_post_meta($order_id, '_psc_cart_total', $result['PAYMENTINFO_0_AMT']);
            update_post_meta($order_id, '_psc_cart_paymentstatus', $result['PAYMENTINFO_0_PAYMENTSTATUS']);
            update_post_meta($order_id, '_psc_cart_serialize', $psc_cart_serialize);
            $url = add_query_arg(array('psc_action' => 'pscordercomplete', 'order' => $uniq_id_generatore), $order_page_url);
            wp_redirect($url);
            exit();
        } else {
            wp_redirect(get_permalink($this->obj_abstract_product->psc_cart_page()));
            exit();
        }
    }

    function CallSetExpressCheckout($paymentAmount, $returnURL, $cancelURL, $usePayPalCredit = false, $posted) {

        if (sizeof($this->obj_abstract_product->session_cart_contents()) == 0) {
            wp_redirect(get_permalink($this->obj_abstract_product->psc_shop_page()));
        }

        if (!class_exists('PayPal_Express_PayPal')) {
            require_once plugin_dir_path(__FILE__) . 'lib/paypal.class.php';
        }

        $PayPalConfig = array(
            'Sandbox' => $this->testmode == 'yes' ? TRUE : FALSE,
            'APIUsername' => $this->api_username,
            'APIPassword' => $this->api_password,
            'APISignature' => $this->api_signature
        );
        $PayPal = new PayPal_Express_PayPal($PayPalConfig);

        //prefill email
        if (isset($posted['billing_email'])) {
            $customer_email = sanitize_email($posted['billing_email']);
        } elseif (is_user_logged_in()) {
            global $current_user;
            get_currentuserinfo();
            $customer_email = $current_user->user_email;
        } else {
            $customer_email = '';
        }
        $SECFields = array(
            'token' => '', // A timestamped token, the value of which was returned by a previous SetExpressCheckout call.
            'maxamt' => '', // The expected maximum total amount the order will be, including S&H and sales tax.
            'returnurl' => urldecode($returnURL), // Required.  URL to which the customer will be returned after returning from PayPal.  2048 char max.
            'cancelurl' => urldecode($cancelURL), // Required.  URL to which the customer will be returned if they cancel payment on PayPal's site.
            'callback' => '', // URL to which the callback request from PayPal is sent.  Must start with https:// for production.
            'callbacktimeout' => '', // An override for you to request more or less time to be able to process the callback request and response.  Acceptable range for override is 1-6 seconds.  If you specify greater than 6 PayPal will use default value of 3 seconds.
            'callbackversion' => '', // The version of the Instant Update API you're using.  The default is the current version.
            'reqconfirmshipping' => '', // The value 1 indicates that you require that the customer's shipping address is Confirmed with PayPal.  This overrides anything in the account profile.  Possible values are 1 or 0.
            'noshipping' => '', // The value 1 indiciates that on the PayPal pages, no shipping address fields should be displayed.  Maybe 1 or 0.
            'allownote' => 1, // The value 1 indiciates that the customer may enter a note to the merchant on the PayPal page during checkout.  The note is returned in the GetExpresscheckoutDetails response and the DoExpressCheckoutPayment response.  Must be 1 or 0.
            'addroverride' => '', // The value 1 indiciates that the PayPal pages should display the shipping address set by you in the SetExpressCheckout request, not the shipping address on file with PayPal.  This does not allow the customer to edit the address here.  Must be 1 or 0.
            'localecode' => ($this->use_wp_locale_code == 'yes' && get_locale() != '') ? get_locale() : '', // Locale of pages displayed by PayPal during checkout.  Should be a 2 character country code.  You can retrive the country code by passing the country name into the class' GetCountryCode() function.
            'pagestyle' => '', // Sets the Custom Payment Page Style for payment pages associated with this button/link.
            'hdrimg' => $this->checkout_logo_hdrimg, // URL for the image displayed as the header during checkout.  Max size of 750x90.  Should be stored on an https:// server or you'll get a warning message in the browser.
            'logoimg' => $this->checkout_logo,
            'hdrbordercolor' => '', // Sets the border color around the header of the payment page.  The border is a 2-pixel permiter around the header space.  Default is black.
            'hdrbackcolor' => '', // Sets the background color for the header of the payment page.  Default is white.
            'payflowcolor' => '', // Sets the background color for the payment page.  Default is white.
            'skipdetails' => $this->skip_final_review == 'yes' ? '1' : '0', // This is a custom field not included in the PayPal documentation.  It's used to specify whether you want to skip the GetExpressCheckoutDetails part of checkout or not.  See PayPal docs for more info.
            'email' => $customer_email, // Email address of the buyer as entered during checkout.  PayPal uses this value to pre-fill the PayPal sign-in page.  127 char max.
            'channeltype' => '', // Type of channel.  Must be Merchant (non-auction seller) or eBayItem (eBay auction)
            'giropaysuccessurl' => '', // The URL on the merchant site to redirect to after a successful giropay payment.  Only use this field if you are using giropay or bank transfer payment methods in Germany.
            'giropaycancelurl' => '', // The URL on the merchant site to redirect to after a canceled giropay payment.  Only use this field if you are using giropay or bank transfer methods in Germany.
            'banktxnpendingurl' => '', // The URL on the merchant site to transfer to after a bank transfter payment.  Use this field only if you are using giropay or bank transfer methods in Germany.
            'brandname' => $this->brand_name, // A label that overrides the business name in the PayPal account on the PayPal hosted checkout pages.  127 char max.
            'customerservicenumber' => $this->customer_service_number, // Merchant Customer Service number displayed on the PayPal Review page. 16 char max.
            'buyeremailoptionenable' => '', // Enable buyer email opt-in on the PayPal Review page. Allowable values are 0 and 1
            'surveyquestion' => '', // Text for the survey question on the PayPal Review page. If the survey question is present, at least 2 survey answer options need to be present.  50 char max.
            'surveyenable' => '', // Enable survey functionality. Allowable values are 0 and 1
            'totaltype' => '', // Enables display of "estimated total" instead of "total" in the cart review area.  Values are:  Total, EstimatedTotal
            'notetobuyer' => '', // Displays a note to buyers in the cart review area below the total amount.  Use the note to tell buyers about items in the cart, such as your return policy or that the total excludes shipping and handling.  127 char max.
            'buyerid' => '', // The unique identifier provided by eBay for this buyer. The value may or may not be the same as the username. In the case of eBay, it is different. 255 char max.
            'buyerusername' => '', // The user name of the user at the marketplaces site.
            'buyerregistrationdate' => '', // Date when the user registered with the marketplace.
            'allowpushfunding' => '', // Whether the merchant can accept push funding.  0 = Merchant can accept push funding : 1 = Merchant cannot accept push funding.
            'taxidtype' => '', // The buyer's tax ID type.  This field is required for Brazil and used for Brazil only.  Values:  BR_CPF for individuals and BR_CNPJ for businesses.
            'taxid' => ''        // The buyer's tax ID.  This field is required for Brazil and used for Brazil only.  The tax ID is 11 single-byte characters for individutals and 14 single-byte characters for businesses.
        );
        /**
         * If PayPal Credit is being used, override the necessary parameters
         */
        if ($usePayPalCredit) {
            $SECFields['solutiontype'] = 'Sole';
            $SECFields['landingpage'] = 'Billing';
            $SECFields['userselectedfundingsource'] = 'BML';
        } elseif (strtolower($this->paypal_account_optional) == 'yes' && strtolower($this->landing_page) == 'billing') {
            $SECFields['solutiontype'] = 'Sole';
            $SECFields['landingpage'] = 'Billing';
            $SECFields['userselectedfundingsource'] = 'CreditCard';
        } elseif (strtolower($this->paypal_account_optional) == 'yes' && strtolower($this->landing_page) == 'login') {
            $SECFields['solutiontype'] = 'Sole';
            $SECFields['landingpage'] = 'Login';
        }

        // Basic array of survey choices.  Nothing but the values should go in here.
        $SurveyChoices = array('Choice 1', 'Choice2', 'Choice3', 'etc');
        $Payments = array();
        $Payment = array(
            'amt' => number_format($this->psc_calculate_cart_totals() - $this->obj_abstract_product->get_cart_total_discount(), 2, '.', ''), // Required.  The total cost of the transaction to the customer.  If shipping cost and tax charges are known, include them in this value.  If not, this value should be the current sub-total of the order.
            'currencycode' => $this->psc_currency, // A three-character currency code.  Default is USD.
            'shippingamt' => '', // Total shipping costs for this order.  If you specify SHIPPINGAMT you mut also specify a value for ITEMAMT.
            'shippingdiscamt' => '', // Shipping discount for this order, specified as a negative number.
            'insuranceamt' => '', // Total shipping insurance costs for this order.
            'insuranceoptionoffered' => '', // If true, the insurance drop-down on the PayPal review page displays the string 'Yes' and the insurance amount.  If true, the total shipping insurance for this order must be a positive number.
            'handlingamt' => '', // Total handling costs for this order.  If you specify HANDLINGAMT you mut also specify a value for ITEMAMT.
            'taxamt' => '', // Required if you specify itemized L_TAXAMT fields.  Sum of all tax items in this order.
            'desc' => '', // Description of items on the order.  127 char max.
            'custom' => '', // Free-form field for your own use.  256 char max.
            'invnum' => '', // Your own invoice or tracking number.  127 char max.
            'notifyurl' => '', // URL for receiving Instant Payment Notifications
            'shiptoname' => '', // Required if shipping is included.  Person's name associated with this address.  32 char max.
            'shiptostreet' => '', // Required if shipping is included.  First street address.  100 char max.
            'shiptostreet2' => '', // Second street address.  100 char max.
            'shiptocity' => '', // Required if shipping is included.  Name of city.  40 char max.
            'shiptostate' => '', // Required if shipping is included.  Name of state or province.  40 char max.
            'shiptozip' => '', // Required if shipping is included.  Postal code of shipping address.  20 char max.
            'shiptocountrycode' => '', // Required if shipping is included.  Country code of shipping address.  2 char max.
            'shiptophonenum' => '', // Phone number for shipping address.  20 char max.
            'notetext' => '', // Note to the merchant.  255 char max.
            'allowedpaymentmethod' => '', // The payment method type.  Specify the value InstantPaymentOnly.
            'paymentaction' => $this->payment_action == 'Authorization' ? 'Authorization' : 'Sale', // How you want to obtain the payment.  When implementing parallel payments, this field is required and must be set to Order.
            'paymentrequestid' => '', // A unique identifier of the specific payment request, which is required for parallel payments.
            'sellerpaypalaccountid' => ''   // A unique identifier for the merchant.  For parallel payments, this field is required and must contain the Payer ID or the email address of the merchant.
        );


        $SECFields['addroverride'] = 1;
        $Payment['shiptoname'] = sanitize_text_field($posted['billing_first_name'] . ' ' . $posted['billing_last_name']);
        $Payment['shiptostreet'] = sanitize_text_field($posted['billing_address_1']);
        $Payment['shiptostreet2'] = sanitize_text_field(@$posted['billing_address_2']);
        $Payment['shiptocity'] = sanitize_text_field(@$posted['billing_city']);
        $Payment['shiptostate'] = sanitize_text_field(@$posted['billing_state']);
        $Payment['shiptozip'] = intval(@$posted['billing_postcode']);
        $Payment['shiptocountrycode'] = sanitize_text_field(@$posted['billing_country']);
        $Payment['shiptophonenum'] = sanitize_text_field(@$posted['billing_phone']);
        $PaymentOrderItems = array();
        $ctr = $total_items = $total_discount = $total_tax = $order_total = 0;
        if ($this->send_items) {

            $bag_item_array = $this->obj_abstract_product->get_cart_array();

            //$bag_item_array = $this->obj_abstract_product->session_cart_contents();
            foreach ($bag_item_array['cart_item'] as $item) {
                $Item = array(
                    'name' => $item['name'], // Item name. 127 char max.
                    'desc' => '', // Item description. 127 char max.
                    'amt' => $item['amt'], // Cost of item.
                    'number' => $item['number'], // Item number.  127 char max.
                    'qty' => $item['qty'], // Item qty on order.  Any positive integer.
                    'taxamt' => '', // Item sales tax
                    'itemurl' => '', // URL for the item.
                    'itemcategory' => '', // One of the following values:  Digital, Physical
                    'itemweightvalue' => '', // The weight value of the item.
                    'itemweightunit' => '', // The weight unit of the item.
                    'itemheightvalue' => '', // The height value of the item.
                    'itemheightunit' => '', // The height unit of the item.
                    'itemwidthvalue' => '', // The width value of the item.
                    'itemwidthunit' => '', // The width unit of the item.
                    'itemlengthvalue' => '', // The length value of the item.
                    'itemlengthunit' => '', // The length unit of the item.
                    'ebayitemnumber' => '', // Auction item number.
                    'ebayitemauctiontxnid' => '', // Auction transaction ID number.
                    'ebayitemorderid' => '', // Auction order ID number.
                    'ebayitemcartid' => ''      // The unique identifier provided by eBay for this order from the buyer. These parameters must be ordered sequentially beginning with 0 (for example L_EBAYITEMCARTID0, L_EBAYITEMCARTID1). Character length: 255 single-byte characters
                );
                array_push($PaymentOrderItems, $Item);
            }

            $Payment['order_items'] = $PaymentOrderItems;
        } else {
            $Payment['order_items'] = array();
        }
        $Payment['itemamt'] = $bag_item_array['itemamt'];
        /*
         * Then we load the payment into the $Payments array
         */
        array_push($Payments, $Payment);

        $BuyerDetails = array(
            'buyerid' => '', // The unique identifier provided by eBay for this buyer.  The value may or may not be the same as the username.  In the case of eBay, it is different.  Char max 255.
            'buyerusername' => '', // The username of the marketplace site.
            'buyerregistrationdate' => '' // The registration of the buyer with the marketplace.
        );

        // For shipping options we create an array of all shipping choices similar to how order items works.
        $ShippingOptions = array();
        $Option = array(
            'l_shippingoptionisdefault' => '', // Shipping option.  Required if specifying the Callback URL.  true or false.  Must be only 1 default!
            'l_shippingoptionname' => '', // Shipping option name.  Required if specifying the Callback URL.  50 character max.
            'l_shippingoptionlabel' => '', // Shipping option label.  Required if specifying the Callback URL.  50 character max.
            'l_shippingoptionamount' => ''      // Shipping option amount.  Required if specifying the Callback URL.
        );
        array_push($ShippingOptions, $Option);
        $BillingAgreements = array();

        $Item = array(
            'l_billingtype' => 'MerchantInitiatedBilling', // Required.  Type of billing agreement.  For recurring payments it must be RecurringPayments.  You can specify up to ten billing agreements.  For reference transactions, this field must be either:  MerchantInitiatedBilling, or MerchantInitiatedBillingSingleSource
            'l_billingagreementdescription' => '', // Required for recurring payments.  Description of goods or services associated with the billing agreement.
            'l_paymenttype' => 'Any', // Specifies the type of PayPal payment you require for the billing agreement.  Any or IntantOnly
            'l_billingagreementcustom' => ''     // Custom annotation field for your own use.  256 char max.
        );
        array_push($BillingAgreements, $Item);
        $PayPalRequestData = array(
            'SECFields' => $SECFields,
            'SurveyChoices' => $SurveyChoices,
            'Payments' => $Payments
        );

        // Pass data into class for processing with PayPal and load the response array into $PayPalResult
        $PayPalResult = $PayPal->SetExpressCheckout($PayPalRequestData);
        /*
         * Error handling
         */
        if ($PayPal->APICallSuccessful($PayPalResult['ACK'])) {
            $token = urldecode($PayPalResult["TOKEN"]);
            $this->obj_abstract_product->session_set('TOKEN', $token);
        }
        /*
         * Return the class library result array.
         */
        return $PayPalResult;
    }

    function psc_create_page($page_title, $slug, $page_content) {
        $page_data = array(
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => 1,
            'post_name' => $slug,
            'post_title' => $page_title,
            'post_content' => $page_content,
            'comment_status' => 'closed'
        );
        $page_id = wp_insert_post($page_data);
        return $page_id;
    }

    function CallGetShippingDetails($token) {

        if (sizeof($this->obj_abstract_product->session_cart_contents()) == 0) {
            wp_redirect(get_permalink($this->obj_abstract_product->psc_cart_page()));
        }
        if (!class_exists('PayPal_Express_PayPal')) {
            require_once plugin_dir_path(__FILE__) . 'lib/paypal.class.php';
        }
        $PayPalConfig = array(
            'Sandbox' => $this->testmode == 'yes' ? TRUE : FALSE,
            'APIUsername' => $this->api_username,
            'APIPassword' => $this->api_password,
            'APISignature' => $this->api_signature
        );
        $PayPal = new PayPal_Express_PayPal($PayPalConfig);
        $PayPalResult = $PayPal->GetExpressCheckoutDetails($token);
        $PayPalRequest = isset($PayPalResult['RAWREQUEST']) ? $PayPalResult['RAWREQUEST'] : '';
        $PayPalResponse = isset($PayPalResult['RAWRESPONSE']) ? $PayPalResult['RAWRESPONSE'] : '';
        if ($PayPal->APICallSuccessful($PayPalResult['ACK'])) {
            $this->obj_abstract_product->session_set('payer_id', $PayPalResult['PAYERID']);
        }
        return $PayPalResult;
    }

    function create_order() {

        $newid = "";
        $post = array(
            'comment_status' => 'open',
            'ping_status' => 'closed',
            'post_date' => date('Y-m-d H:i:s'),
            'post_name' => 'psc_order',
            'post_status' => 'publish',
            'post_title' => 'psc_order',
            'post_type' => 'psc_order',
            'post_content' => '',
        );
        $newid = wp_insert_post($post, false);
        return $newid;
    }

    function ConfirmPayment($FinalPaymentAmt) {

        if (sizeof($this->obj_abstract_product->session_cart_contents()) == 0) {
            wp_redirect(get_permalink($this->obj_abstract_product->psc_cart_page()));
        }
        if (!class_exists('PayPal_Express_PayPal')) {
            require_once plugin_dir_path(__FILE__) . 'lib/paypal.class.php';
        }
        $PayPalConfig = array(
            'Sandbox' => $this->testmode == 'yes' ? TRUE : FALSE,
            'APIUsername' => $this->api_username,
            'APIPassword' => $this->api_password,
            'APISignature' => $this->api_signature
        );
        $shipping_first_name = $this->obj_abstract_product->session_get('firstname');
        $shipping_last_name = $this->obj_abstract_product->session_get('lastname');
        $shipping_address_1 = $this->obj_abstract_product->session_get('shiptostreet');
        $shipping_address_2 = $this->obj_abstract_product->session_get('shiptostreet2');
        $shipping_city = $this->obj_abstract_product->session_get('shiptocity');
        $shipping_state = $this->obj_abstract_product->session_get('shiptostate');
        $shipping_postcode = $this->obj_abstract_product->session_get('shiptozip');
        $shipping_country = $this->obj_abstract_product->session_get('shiptocountrycode');

        $DECPFields = array(
            'token' => urlencode($this->obj_abstract_product->session_get('TOKEN')),
            'payerid' => urlencode($this->obj_abstract_product->session_get('payer_id')),
            'returnfmfdetails' => '',
            'buyermarketingemail' => '',
            'surveyquestion' => '',
            'surveychoiceselected' => '',
            'allowedpaymentmethod' => ''
        );
        $Payments = array();
        $Payment = array(
            'amt' => number_format($this->psc_calculate_cart_totals() - $this->obj_abstract_product->get_cart_total_discount(), 2, '.', ''),
            'currencycode' => $this->obj_abstract_product->get_psc_currency(),
            'shippingdiscamt' => '',
            'insuranceoptionoffered' => '',
            'handlingamt' => '',
            'desc' => '',
            'custom' => '',
            'invnum' => $this->invoice_id_prefix . substr(microtime(), -5),
            'notifyurl' => '',
            'shiptoname' => $shipping_first_name . ' ' . $shipping_last_name,
            'shiptostreet' => $shipping_address_1,
            'shiptostreet2' => $shipping_address_2,
            'shiptocity' => $shipping_city,
            'shiptostate' => $shipping_state,
            'shiptozip' => $shipping_postcode,
            'shiptocountrycode' => $shipping_country,
            'shiptophonenum' => '',
            'notetext' => '',
            'allowedpaymentmethod' => '',
            'paymentaction' => $this->payment_action == 'Authorization' ? 'Authorization' : 'Sale',
            'paymentrequestid' => '',
            'sellerpaypalaccountid' => '',
            'sellerid' => '',
            'sellerusername' => '',
            'sellerregistrationdate' => '',
            'softdescriptor' => ''
        );
        $PaymentOrderItems = array();
        $ctr = $total_items = $total_discount = $total_tax = $shipping = 0;
        if (sizeof($this->obj_abstract_product->session_cart_contents()) != 0) {
            // $bag_item_array = $this->obj_abstract_product->session_cart_contents();
            $bag_item_array = $this->obj_abstract_product->get_cart_array();
            foreach ($bag_item_array['cart_item'] as $item) {
                $Item = array(
                    'name' => $item['name'], // Item name. 127 char max.
                    'desc' => '', // Item description. 127 char max.
                    'amt' => $item['amt'], // Cost of item.
                    'number' => $item['number'], // Item number.  127 char max.
                    'qty' => $item['qty'], // Item qty on order.  Any positive integer.
                    'taxamt' => '', // Item sales tax
                    'itemurl' => '', // URL for the item.
                    'itemcategory' => '', // One of the following values:  Digital, Physical
                    'itemweightvalue' => '', // The weight value of the item.
                    'itemweightunit' => '', // The weight unit of the item.
                    'itemheightvalue' => '', // The height value of the item.
                    'itemheightunit' => '', // The height unit of the item.
                    'itemwidthvalue' => '', // The width value of the item.
                    'itemwidthunit' => '', // The width unit of the item.
                    'itemlengthvalue' => '', // The length value of the item.
                    'itemlengthunit' => '', // The length unit of the item.
                    'ebayitemnumber' => '', // Auction item number.
                    'ebayitemauctiontxnid' => '', // Auction transaction ID number.
                    'ebayitemorderid' => '', // Auction order ID number.
                    'ebayitemcartid' => ''      // The unique identifier provided by eBay for this order from the buyer. These parameters must be ordered sequentially beginning with 0 (for example L_EBAYITEMCARTID0, L_EBAYITEMCARTID1). Character length: 255 single-byte characters
                );
                array_push($PaymentOrderItems, $Item);
            }
        }
        $Payment['order_items'] = $PaymentOrderItems;
        $Payment['itemamt'] = $bag_item_array['itemamt'];
        array_push($Payments, $Payment);
        $UserSelectedOptions = array(
            'shippingcalculationmode' => '',
            'insuranceoptionselected' => '',
            'shippingoptionisdefault' => '',
            'shippingoptionamount' => '',
            'shippingoptionname' => '',
        );
        $PayPalRequestData = array(
            'DECPFields' => $DECPFields,
            'Payments' => $Payments,
        );
        if (number_format($this->psc_calculate_cart_totals(), 2, '.', '')) {
            $PayPalRequestData['Payments'][0]['shippingamt'] = 0; //number_format($this->psc_calculate_cart_totals(), 2, '.', '');            
        }

        $PayPal = new PayPal_Express_PayPal($PayPalConfig);
        $PayPalResult = $PayPal->DoExpressCheckoutPayment($PayPalRequestData);
        if ($this->debug == 'yes') {
            $this->obj_abstract_product->psc_write_log_activity_array('paypal_shopping_cart_log', 'DoExpressCheckoutPayment', $PayPalResult);
        }
        $PayPalRequest = isset($PayPalResult['RAWREQUEST']) ? $PayPalResult['RAWREQUEST'] : '';
        $PayPalResponse = isset($PayPalResult['RAWRESPONSE']) ? $PayPalResult['RAWRESPONSE'] : '';

        if ($PayPal->APICallSuccessful($PayPalResult['ACK'])) {
            $PayPalResponseDetails = $PayPalResult['PAYMENTS'];
            $this->pac_stock_management_store();
            update_post_meta($this->obj_abstract_product->session_get('order_id'), '_order_responce', serialize($PayPalResponseDetails));
            update_post_meta($this->obj_abstract_product->session_get('order_id'), '_order_action_status', 'on-hold');
            update_post_meta($this->obj_abstract_product->session_get('order_id'), '_order_cart_discount', $this->coupon_cart_discount);
            update_post_meta($this->obj_abstract_product->session_get('order_id'), '_order_cart_discount_coupon_code', $this->coupon_cart_discount_coupon_code);
            $this->send_email_notification_user_and_admin($PayPalResult);
            $this->obj_abstract_product->customer_session_empty();
        }
        return $PayPalResult;
    }

    function send_email_notification_user_and_admin($PayPalResult) {
        $psc_donor_notification = get_option('psc_donor_notification');
        $psc_admin_notification = get_option('psc_admin_notification');
        if (( isset($psc_donor_notification) && 'yes' == $psc_donor_notification ) || (isset($psc_admin_notification) && 'yes' == $psc_admin_notification)) {
            $posted_result_email = $PayPalResult['REQUESTDATA'];
            $posted_result_email['payeremail'] = $this->obj_abstract_product->session_get('payeremail');
            $posted_result_email['payment_date'] = $PayPalResult['TIMESTAMP'];
            $posted_result_email['txn_id'] = $PayPalResult['PAYMENTINFO_0_TRANSACTIONID'];
            do_action('psc_send_notification_mail', $posted_result_email);
        }
    }

    function pac_stock_management_store() {

        $item_array_name = $this->obj_abstract_product->session_cart_contents();

        foreach ($item_array_name as $value) {
            $product_name = $value['name'];
            $product_qty = $value['qty'];
            $product_id = preg_replace("/_[^*]+/", '', $value['id']);

            if ((isset($product_name) && !empty($product_name)) && (isset($product_qty) && !empty($product_qty)) && (isset($product_id) && !empty($product_id))) {
                $result_array = $this->obj_abstract_product->get_post_meta_all($product_id);
                if (is_array($result_array)) {
                    if (isset($result_array['psc-product-type-dropdown']) && $result_array['psc-product-type-dropdown'] == "simple") {

                        if ($result_array['_psc_stock_qty_simple'] != '0') {
                            $qty_simple = $result_array['_psc_stock_qty_simple'] - $product_qty;
                        } else {
                            $qty_simple = '0';
                        }

                        update_post_meta($product_id, '_psc_stock_qty_simple', $qty_simple);
                    } else if (isset($result_array['psc-product-type-dropdown']) && $result_array['psc-product-type-dropdown'] == "variable") {
                        $expload_data = explode(':', $product_name);
                        $result_unsirealize = $this->obj_abstract_product->get_unserialize_data($result_array['psc_variable_product_data']);
                        foreach ($result_unsirealize as $key => $value) {
                            if ($expload_data[1] == $value['psc_variable_product_name' . $key]) {

                                if ($value['psc_variable_product_stock' . $key] != '0') {
                                    $qty_variable = $value['psc_variable_product_stock' . $key] - $product_qty;
                                } else {
                                    $qty_variable = '0';
                                }

                                $result_unsirealize[$key]['psc_variable_product_stock' . $key] = $qty_variable;
                            }
                        }
                        update_post_meta($product_id, 'psc_variable_product_data', serialize($result_unsirealize));
                    }
                }
            }
        }
    }

}
ob_flush();
?>