jQuery(function($) {

    var product_type = jQuery('select#psc-product-type-dropdown option:selected').val();

    if (typeof(product_type) != "undefined") {
        selected_product_type(product_type);
    }

    //set_paypal_sendbox_or_paypal_mode(jQuery('#psc_pec_testmode').is(':checked'));

    var items = $('#psc_simpale_product_div>ul>li').each(function() {
        jQuery(this).click(function() {
            items.removeClass('current');
            jQuery(this).addClass('current');
            jQuery('#psc_simpale_product_div>div.tab-content').hide().eq(items.index(jQuery(this))).show('slow');
            window.location.hash = jQuery(this).attr('tab');
        });
    });

    var items1 = $('#psc_variable_product_div>ul>li').each(function() {
        jQuery(this).click(function() {
            items1.removeClass('current');
            jQuery(this).addClass('current');
            jQuery('#psc_variable_product_div>div.tab-content').hide().eq(items1.index(jQuery(this))).show('slow');
            window.location.hash = jQuery(this).attr('tab');
        });
    });

    var items2 = $('#psc_product_coupon_div>ul>li').each(function() {
        jQuery(this).click(function() {
            items2.removeClass('current');
            jQuery(this).addClass('current');
            jQuery('#psc_product_coupon_div>div.tab-content').hide().eq(items2.index(jQuery(this))).show('slow');
            window.location.hash = jQuery(this).attr('tab');
        });
    });

    jQuery(document).on('keydown', '#_psc_sale_price,#_psc_regular_price,.psc_input_regulare_price,.psc_input_sale_price', function(e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                // Allow: Ctrl+A, Command+A
                        (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                        // Allow: home, end, left, right, down, up
                                (e.keyCode >= 35 && e.keyCode <= 40)) {


                    return;
                }
                // Ensure that it is a number and stop the keypress
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    jQuery(this).next('span').remove();
                    jQuery(this).after('<span style="color:red">Add Number Only</span>');
                    e.preventDefault();
                } else {
                    jQuery(this).next('span').remove();
                }

            });

    jQuery(document).on('focusout', '#_psc_sale_price', function() {
        var price = jQuery(this).val();
        var regular = jQuery('#_psc_regular_price').val();
        if (parseFloat(regular) <= parseFloat(price)) {
            jQuery(this).next('span').remove();
            jQuery(this).val(parseFloat(regular) - parseFloat(1));
            jQuery(this).after('<span style="color:red">Price Less Then Regular Price</span>');
        } else {
            jQuery(this).next('span').remove();
        }
    });

    jQuery(document).on('focusout', '.psc_input_sale_price', function() {
        var sale_id = jQuery(this).attr('data-id');
        var price = jQuery(this).val();
        var regular = jQuery('#psc_product_regular_price' + sale_id).val();

        if (parseFloat(regular) <= parseFloat(price)) {
            jQuery(this).next('span').remove();
            jQuery(this).val(parseFloat(regular) - parseFloat(1));
            jQuery(this).after('<span style="color:red">Price Less Then Regular Price</span>');
        } else {
            jQuery(this).next('span').remove();
        }


    });

    jQuery(document).on('click', '#_psc_manage_stock_simple', function() {

        var current_id = jQuery(this).attr('id');
        var current_value = jQuery(this).attr('value');

        psc_enable_manage_stock(current_id, current_value);

    });

    jQuery(document).on('click', '#_psc_manage_stock_variable', function() {

        var current_id = jQuery(this).attr('id');
        var current_value = jQuery(this).attr('value');

        psc_enable_manage_stock(current_id, current_value);

    });

    jQuery(document).on('change', '#psc-product-type-dropdown', function() {
        var product_type = jQuery('select#psc-product-type-dropdown option:selected').val();
        selected_product_type(product_type);

    });

    jQuery(document).on('click', '#psc_add_new_variable_product', function(e) {
        e.preventDefault();
        var last_tr = jQuery('#psc_variable_product tr:last').attr('data-tr');
        var last_tr_after = parseInt(last_tr) + parseInt(1);
        var str_variabale_product = '<tr id="psc_table_option_' + last_tr_after + '" data-tr="' + last_tr_after + '"><td><input type ="text" class="psc_input input_box" name ="psc_variable_product_name' + last_tr_after + '" id = "psc_product_name' + last_tr_after + '" placeholder = "Variation Name"></td><td><input type = "text" class="psc_input_regulare_price psc_input input_box" name = "psc_variable_product_regular_price' + last_tr_after + '" id = "psc_product_regular_price' + last_tr_after + '" placeholder = "Regular Price"></td><td><input type = "text" class="psc_input_sale_price psc_input input_box" name = "psc_variable_product_sale_price' + last_tr_after + '" id = "psc_product_sale_price' + last_tr_after + '" data-id="' + last_tr_after + '" placeholder = "Sale Price"></td><td><input type = "checkbox" class="psc_input_checkbox input_box" name = "_psc_manage_stock_variable' + last_tr_after + '" id = "psc_product_manage' + last_tr_after + '" value="0"></td><td><input type = "checkbox" class="psc_input_checkbox input_box" name = "psc_variable_product_stock_status' + last_tr_after + '" id = "psc_variable_product_stock_status' + last_tr_after + '" value="0"></td><td class="_psc_manage_stock_variable_display' + last_tr_after + '"><input type = "text" class="psc_input input_box" name = "psc_variable_product_stock' + last_tr_after + '" id = "psc_product_stock' + last_tr_after + '" placeholder = "QTY"></td><td><button class="btn button psc_cancel" id="psc_remove_variable_product">X</button></td></tr>';
        jQuery('#psc_table_option_' + last_tr).after(str_variabale_product);
        variable_product_plus_count();


    });

    jQuery(document).on('click', '#psc_remove_variable_product', function(e) {
        e.preventDefault();
        var id = jQuery(this).closest('tr').attr('data-tr');
        jQuery("#psc_variable_product #psc_table_option_" + id).remove();
        remove_tr_reset_id(id);
        variable_product_minus_count();
    });

    jQuery(document).on('click', '.psc_input_checkbox', function() {
        var id = $(this).attr('id');
        var isChecked = $('#' + id + ':checked').val() ? true : false;
        var data_tr = jQuery(this).closest('tr').attr('data-tr');

        if (isChecked) {
            jQuery('#' + id).val('1');
        } else {
            jQuery('#' + id).val('0');
        }


    });

    jQuery(document).on('click', '#psc_pec_testmode', function() {
      //  set_paypal_sendbox_or_paypal_mode(jQuery(this).is(':checked'));
    });

    jQuery('#paypal_shopping_cart_setting_form').on('change', '#psc_pec_checkout_botton_type', function() {
        var name = jQuery('#psc_pec_checkout_botton_type').val();
        if (name == 'textbutton') {
            jQuery('#psc_pec_my_custom_image').closest('tr').hide();
            jQuery('#psc_pec_my_text_button').closest('tr').show();
        } else if (name == 'customimage') {
            jQuery('#psc_pec_my_text_button').closest('tr').hide();
            jQuery('#psc_pec_my_custom_image').closest('tr').show();
        } else {
            jQuery('#psc_pec_my_custom_image').closest('tr').hide();
            jQuery('#psc_pec_my_text_button').closest('tr').hide();
        }

    });

    jQuery('#psc_pec_my_text_button').closest('tr').hide();

    jQuery('#psc_pec_my_custom_image').closest('tr').hide();

    jQuery("#psc_pec_my_custom_image").css({float: "left"});

    jQuery("#psc_pec_my_custom_image").after('<a href="#" id="upload" class="button_upload button">Upload</a>');

    var custom_uploader;
    $('.button_upload').click(function(e) {
        var BTthis = jQuery(this);
        e.preventDefault();
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        });
        custom_uploader.on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            var pre_input = BTthis.prev();
            var url = attachment.url;
            if (BTthis.attr('id') != 'upload') {
                if (attachment.url.indexOf('http:') > -1) {
                    url = url.replace('http', 'https');
                }
            }
            pre_input.val(url);
        });
        custom_uploader.open();
    });

    var psc_manage_stock_simple = jQuery('#_psc_manage_stock_simple').val();

    if (typeof(psc_manage_stock_simple) != "undefined" && psc_manage_stock_simple !== null) {
        if (psc_manage_stock_simple.toString().length > 0) {
            defualt_show_and_hide_stock_table('_psc_manage_stock_simple', psc_manage_stock_simple);
        }
    }

    var psc_manage_stock_variable = jQuery('#_psc_manage_stock_variable').val();

    if (typeof(psc_manage_stock_variable) != "undefined" && psc_manage_stock_variable !== null) {
        if (psc_manage_stock_variable.toString().length > 0) {
            defualt_show_and_hide_stock_table('_psc_manage_stock_variable', psc_manage_stock_variable);
        }
    }

    jQuery('.post-type-psc_order #psc_order_view h2').remove();

    jQuery('.post-type-psc_order #psc_order_view button').remove();

    jQuery('.post-type-psc_coupon #postexcerpt h2').remove();

    jQuery('.post-type-psc_coupon #postexcerpt button').remove();

    jQuery('.post-type-psc_coupon #postexcerpt .inside p').remove();

    jQuery('.post-type-psc_coupon #postexcerpt .inside textarea').attr("placeholder", "Description (optional)");

    jQuery('.psc_coupon_expiry_date').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    jQuery(".post-type-psc_coupon #psc_coupon_view #psc_product_coupon_div #psc_coupon_products").customselect();

    jQuery(".post-type-psc_coupon #psc_coupon_view #psc_product_coupon_div #psc_coupon_exclude_products").customselect();



    function set_paypal_sendbox_or_paypal_mode(is_check) {
        var sandbox = jQuery('#psc_pec_sandbox_api_username, #psc_pec_sandbox_api_password, #psc_pec_sandbox_api_signature').closest('tr'),
                production = jQuery('#psc_pec_api_username, #psc_pec_api_password, #psc_pec_api_signature').closest('tr');
        if (is_check) {
            sandbox.show();
            production.hide();
        } else {
            sandbox.hide();
            production.show();
        }
    }

    function remove_tr_reset_id(id) {
        var new_id = parseInt(id) + 1;
        var last_tr_id = jQuery('#psc_variable_product tr:last').attr('data-tr');
        for (var i = new_id; i <= last_tr_id; i++) {
            var cla_data = parseInt(i) - 1;

            jQuery('#psc_table_option_' + i).attr('data-tr', cla_data);
            jQuery('#psc_table_option_' + i).attr('id', 'psc_table_option_' + cla_data);

            jQuery('#psc_product_name' + i).attr('name', 'psc_variable_product_name' + cla_data);
            jQuery('#psc_product_name' + i).attr('id', 'psc_product_name' + cla_data);

            jQuery('#psc_product_regular_price' + i).attr('name', 'psc_variable_product_regular_price' + cla_data);
            jQuery('#psc_product_regular_price' + i).attr('id', 'psc_product_regular_price' + cla_data);

            jQuery('#psc_product_sale_price' + i).attr('data-id', cla_data);
            jQuery('#psc_product_sale_price' + i).attr('name', 'psc_variable_product_sale_price' + cla_data);
            jQuery('#psc_product_sale_price' + i).attr('id', 'psc_product_sale_price' + cla_data);

            jQuery('#psc_product_manage' + i).attr('name', '_psc_manage_stock_variable' + cla_data);
            jQuery('#psc_product_manage' + i).attr('id', 'psc_product_manage' + cla_data);

            jQuery('#psc_variable_product_stock_status' + i).attr('name', 'psc_variable_product_stock_status' + cla_data);
            jQuery('#psc_variable_product_stock_status' + i).attr('id', 'psc_variable_product_stock_status' + cla_data);

            jQuery('._psc_manage_stock_variable_display' + i).attr('class', '_psc_manage_stock_variable_display' + cla_data);


            jQuery('#psc_product_stock' + i).attr('name', 'psc_variable_product_stock' + cla_data);
            jQuery('#psc_product_stock' + i).attr('id', 'psc_product_stock' + cla_data);

        }
    }

    function variable_product_plus_count() {
        var p_count = jQuery('#psc_product_count').val();
        p_count = parseInt(p_count) + parseInt(1);
        jQuery('#psc_product_count').val(p_count);
    }

    function variable_product_minus_count() {
        var p_count = jQuery('#psc_product_count').val();
        p_count = parseInt(p_count) - parseInt(1);
        jQuery('#psc_product_count').val(p_count);
    }

    function selected_product_type(product_type) {
        var product_sku = jQuery("#product_sku").val();
        if ('simple' == product_type) {
            jQuery("#psc_variable_product_div").hide();
            jQuery("#psc_simpale_product_div").show();
            jQuery("#psc_variable_product_div .product_sku").html('');
            jQuery("#psc_simpale_product_div .product_sku").html('');
            jQuery("#psc_simpale_product_div .product_sku").append('<th class="product_sku">SKU</th><td><input type="text" class="" name="_psc_sku" id="_psc_sku" value="' + product_sku + '" placeholder=""></td>');
        } else if ('variable' == product_type) {
            jQuery("#psc_simpale_product_div").hide();
            jQuery("#psc_variable_product_div").show();
            jQuery("#psc_simpale_product_div .product_sku").html('');
            jQuery("#psc_variable_product_div .product_sku").html('');
            jQuery("#psc_variable_product_div .product_sku").append('<th class="product_sku">SKU</th><td style="padding-top: 25px;"><input type="text" class="" name="_psc_sku" id="_psc_sku" value="' + product_sku + '" placeholder=""></td>');
        }
    }

    function defualt_show_and_hide_stock_table(current_id, current_value) {

        if (current_value == 'no') {
            if (current_id == '_psc_manage_stock_simple') {
                jQuery('#_psc_simple_product_stock_table').css("display", "none");
            } else if (current_id == '_psc_manage_stock_variable') {
                jQuery('#_psc_variable_product_stock_table').css("display", "none");
            }
        } else if (current_value == 'yes') {
            if (current_id == '_psc_manage_stock_simple') {
                jQuery('#_psc_simple_product_stock_table').show();
            } else if (current_id == '_psc_manage_stock_variable') {
                jQuery('#_psc_variable_product_stock_table').show();
            }
        }

        return;
    }

    function psc_enable_manage_stock(current_id, current_value) {

        if (current_value == 'no') {
            jQuery('#' + current_id).val("yes");
            show_and_hide_stock_table(current_id, current_value);

        } else if (current_value == 'yes') {
            jQuery('#' + current_id).val("no");
            show_and_hide_stock_table(current_id, current_value);
        }
        return;

    }

    function show_and_hide_stock_table(current_id, current_value) {

        if (current_value == 'no') {
            if (current_id == '_psc_manage_stock_simple') {
                jQuery('#_psc_simple_product_stock_table').show();
            } else if (current_id == '_psc_manage_stock_variable') {
                jQuery('#_psc_variable_product_stock_table').show();
            }
        } else if (current_value == 'yes') {
            if (current_id == '_psc_manage_stock_simple') {
                jQuery('#_psc_simple_product_stock_table').hide();
            } else if (current_id == '_psc_manage_stock_variable') {
                jQuery('#_psc_variable_product_stock_table').hide();
            }
        }

        return;
    }
});