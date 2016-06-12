
function pfToggleMetaBox(toggleIcon) {
    jQuery(toggleIcon).parents('.postbox').children('.inside').toggle();
    
    if (jQuery(toggleIcon).parents('.postbox').hasClass('closed')) {
        jQuery(toggleIcon).parents('.postbox').removeClass("closed");
    } else {
        jQuery(toggleIcon).parents('.postbox').addClass("closed");
    }
}

function pfRemoveMetaBox(removeIcon) {
    if (confirm('Confirm to remove?')) {
        jQuery(removeIcon).parents('.postbox').parents('.meta-box-sortables').remove();
    }    
}

function umNewField(element) {
    newID = parseInt(jQuery("#last_id").val()) + 1;
    arg = 'id=' + newID + '&field_type=' + jQuery(element).attr('field_type');
    pfAjaxCall(element, 'um_add_field', arg, function(data) {
        jQuery("#um_fields_container").append(data);
        jQuery("#last_id").val(newID);
    });
}

function umUpdateField(element) { // TODO
    /*if (!jQuery(element).validationEngine("validate")) return;
    
    bindElement = jQuery(".pf_save_button");
    bindElement.parent().children(".pf_ajax_result").remove();
    arg = jQuery( element ).serialize();
    pfAjaxCall(bindElement, 'um_update_field', arg, function(data) {
        bindElement.after("<div class='pf_ajax_result'>"+data+"</div>");        
    });*/
}

function umChangeField(element, fieldID) {
    /*arg = jQuery( "#field_" + fieldID + " *" ).serialize();
    pfAjaxCall(element, "um_change_field", arg, function(data) {
        jQuery(element).parents(".meta-box-sortables").replaceWith(data);
    });*/
}

function umChangeFieldTitle(element) {
    title = jQuery(element).val();
    if (!title){ title = 'Untitled Field'; }
    jQuery(element).parents(".postbox").children("h3").children(".um_admin_field_title").text(title);
}

function umUpdateMetaKey(element) {
    if (jQuery(element).parents(".postbox").find(".um_meta_key_editor").length) {
        if (!jQuery(element).parents(".postbox").find(".um_meta_key_editor").val()) {
            title = jQuery(element).parents(".postbox").find(".um_field_title_editor").val();
            meta_key = title.trim().toLowerCase().replace(/[^a-z0-9 ]/g,'').replace(/\s+/g,'_');
            jQuery(element).parents(".postbox").find(".um_meta_key_editor").val(meta_key);
        }
    }
}

function umNewForm(element) {
    newID = parseInt(jQuery("#form_count").val()) + 1;
    pfAjaxCall(element, 'um_add_form', 'id='+newID, function(data) {
        jQuery("#um_fields_container").append(data);
        jQuery("#form_count").val(newID);
        
        jQuery('.um_dropme').sortable({
            connectWith: '.um_dropme',
            cursor: 'pointer'
        }).droppable({
            accept: '.postbox',
            activeClass: 'um_highlight'
        });
    });
}

function umUpdateForms(element) {
    if (!jQuery(element).validationEngine("validate")) return;
    
    jQuery(".um_selected_fields").each(function(index) {
        var length = jQuery(this).children(".postbox").size();
        n = index + 1;
        jQuery("#field_count_" + n).val(length); 
    });

    bindElement = jQuery(".pf_save_button");
    bindElement.parent().children(".pf_ajax_result").remove();
    arg = jQuery(element).serialize();
    pfAjaxCall(bindElement, 'um_update_forms', arg, function(data) {
        bindElement.after("<div class='pf_ajax_result'>"+data+"</div>");
    });
}

function umChangeFormTitle(element) {
    title = jQuery(element).val();
    if (!title){title = 'Untitled Form';}
    jQuery(element).parents(".postbox").children("h3").text(title);
}

function umAuthorizePro(element) {
    if (!jQuery(element).validationEngine("validate")) return;
    
    arg = jQuery(element).serialize();
    bindElement = jQuery("#authorize_pro");
    pfAjaxCall(bindElement, 'um_update_settings', arg, function(data) {
        bindElement.parent().children(".pf_ajax_result").remove();
        bindElement.after("<div class='pf_ajax_result'>"+data+"</div>");
    });    
}

function umWithdrawLicense(element) {
    bindElement = jQuery(element);
    arg = "method_name=withdrawLicense";
    bindElement.parent().children(".pf_ajax_result").remove();
    pfAjaxCall(bindElement, 'pf_ajax_request', arg, function(data) {
        bindElement.after("<div class='pf_ajax_result'>"+data+"</div>");
    });     
}

function umUpdateSettings(element) {
    bindElement = jQuery("#update_settings");
    
    jQuery(".um_selected_fields").each(function(index){
        var length = jQuery(this).children(".postbox").size();
        n = index + 1;
        jQuery("#field_count_" + n).val( length ); 
        
    });    
    
    arg = jQuery( element ).serialize();
    pfAjaxCall(bindElement, 'um_update_settings', arg, function(data) {
        bindElement.parent().children(".pf_ajax_result").remove();
        bindElement.after("<div class='pf_ajax_result'>"+data+"</div>");
    });
}

// Get Pro Message in admin section
function umGetProMessage( element ){
    alert(user_meta.get_pro_link);
}

// Toggle custom field in Admin Import Page
function umToggleCustomField(element) {
    if (jQuery(element).val() == 'custom_field' )
        jQuery(element).parent().children(".um_custom_field").fadeIn();
    else
        jQuery(element).parent().children(".um_custom_field").fadeOut();
}

/**
 * Export and Import
 */

var umAjaxRequest;

function umUserImportDialog(element) {
    jQuery("#import_user_dialog").html( '<center>' + user_meta.please_wait + '</center>' );
    jQuery("#dialog:ui-dialog").dialog("destroy");
	jQuery("#import_user_dialog").dialog({
		modal: true,
        beforeClose: function(event, ui) {
            umAjaxRequest.abort();
            jQuery(".pf_loading").remove();
        },
		buttons: {
			Cancel: function() {
				jQuery( this ).dialog( "close" );
			}
		}
	});   
    umUserImport( element, 0, 1 );  
}

function umUserImport(element, file_pointer, init) {
    arg = jQuery( element ).serialize();    
    arg = arg + '&step=import&file_pointer=' + file_pointer;
    if ( init ) arg = arg + '&init=1';
    pfAjaxCall( element, 'um_user_import', arg, function(data){
        jQuery( "#import_user_dialog" ).html( data );
        if ( jQuery(data).attr('do_loop') == 'do_loop' ){
            umUserImport( element, jQuery(data).attr('file_pointer') );
        } 
    });
}

function umUserExport(element, type) {
    var arg = jQuery( element ).parent("form").serialize();
    arg = arg.replace(/\(/g, "%28").replace(/\)/g, "%29");//Replace "()"
    var field_count = jQuery( element ).parent("form").children(".um_selected_fields").children(".postbox").size();
        
    arg = arg + "&action_type=" + type + "&field_count=" + field_count;
       
    if ( type == 'export' || type == 'save_export' ) {
        document.location.href = ajaxurl + "?action=pf_ajax_request&" + arg;
    }else if( type == 'save' ){
        pfAjaxCall( element, 'pf_ajax_request', arg, function(data){
            alert('Form saved');
        });          
    }
}

function umNewUserExportForm(element) {
    var formID = jQuery("#new_user_export_form_id").val();
    incID = formID + 1;
    jQuery("#new_user_export_form_id").val( parseInt(formID) + 1 );  
    
    arg = 'method_name=userExportForm&form_id=' + formID;
    
    pfAjaxCall( element, 'pf_ajax_request', arg, function(data){
        jQuery(element).before(data);        
        
        jQuery('.um_dropme').sortable({
            connectWith: '.um_dropme',
            cursor: 'pointer'
        }).droppable({
            accept: '.postbox',
            activeClass: 'um_highlight'
        });  
        jQuery(".um_date").datepicker({ dateFormat: 'yy-mm-dd', changeYear: true });
    });    
}

function umAddFieldToExport(element) {
    var metaKey = jQuery(element).parent().children(".um_add_export_meta_key").val();
    if(metaKey){
        var button  = '<div class="postbox">Title:<input type="text" style="width:50%" name="fields['+metaKey+']" value="'+metaKey+'" /> ('+metaKey+')</div>';
        jQuery(element).parents("form").children(".um_selected_fields").append(button);
    }else{
        alert( 'Please provide Meta Key.' );
    }
}

function umDragAllFieldToExport(element) {
    jQuery(element).parents("form").children(".um_selected_fields").append(
        jQuery(element).parents("form").children(".um_availabele_fields").html()
    );
    jQuery(element).parents("form").children(".um_availabele_fields").empty()
}

function umRemoveFieldToExport(element, formID) {
    if( confirm( "This form will removed permanantly. Confirm to Remove?" ) ){ 
        var arg = 'method_name=RemoveExportForm&form_id=' + formID;
        pfAjaxCall( element, 'pf_ajax_request', arg, function(data){

        });  
        jQuery( element ).parents(".meta-box-sortables").hide('slow').empty();
    }
}

function umToggleVisibility(condition, result, reverse) {
    reverse = typeof reverse == 'undefined' ? true : false;
    val = jQuery(condition).val();
    val = reverse ? !val : val;
    val ? jQuery(result).fadeIn() : jQuery(result).fadeOut();
}

function umSettingsRegistratioUserActivationChange() {
    var userActivationType = jQuery('.um_registration_user_activation:checked').val();
    if( userActivationType == 'auto_active' ){
        jQuery('#um_settings_registration_block_2').hide();
        jQuery('#um_settings_registration_block_1').fadeIn();
    }else if( userActivationType == 'email_verification' ){
        jQuery('#um_settings_registration_block_1').hide();
        jQuery('#um_settings_registration_block_2').fadeIn();
    }else if( userActivationType == 'admin_approval' ){
        jQuery('#um_settings_registration_block_1').hide();
        jQuery('#um_settings_registration_block_2').hide();
    }else if( userActivationType == 'both_email_admin' ){
        jQuery('#um_settings_registration_block_1').hide();
        jQuery('#um_settings_registration_block_2').fadeIn();
    }
}

function umSettingsToggleCreatePage() {
    umToggleVisibility('#um_login_login_page', '#um_login_login_page_create');
    umToggleVisibility('#um_login_login_page', '#um_login_disable_wp_login_php_block', false);
    
    umToggleVisibility('#um_registration_email_verification_page', '#um_registration_email_verification_page_create');
    umToggleVisibility('#um_login_resetpass_page', '#um_login_resetpass_page_create');
}

function umSettingsToggleError() {
   umToggleVisibility('#um_registration_email_verification_page', '.um_required_email_verification_page');
   
    showError = false;
    if( jQuery('#um_login_disable_wp_login_php:checked').val() ){
        if( ! jQuery('#um_login_resetpass_page').val() )
            showError = true;
    }
    if( showError )
        jQuery('.um_required_resetpass_page_page').fadeIn();
    else
        jQuery('.um_required_resetpass_page_page').fadeOut();
}



(function($){

    var userMeta = userMeta || {};
    
    userMeta.admin = {
        
        init: function() {
            this.events();
            this.initMultiselect(); 
        },
        
        events: function() {
            $(document).on('click', '.panel .panel-heading', this.togglePanel);
        },
        
        togglePanel: function() {
            $(this).closest(".panel").find(".collapse").slideToggle();
        },
        
        initMultiselect: function() {
            if ( $.isFunction($.fn.multiselect) ) {
                $('.um_multiselect').multiselect({
                    includeSelectAllOption: true
                });
            }
        },
        
        saveButton: function( arg ) {
            $.ajax({
            type: "post",
            url: ajaxurl,
            data: arg,
                beforeSend: function(){ $(".um_save_button").html('<i class="fa fa-spin fa-circle-o-notch"></i> Saving') },
                success: function( data ){
                    
                    try {
                        var config = JSON.parse(data);
                        if ( config.redirect_to ) {
                            window.location.replace(config.redirect_to);
                        }
                    } catch (err) {}
                    
                    
                    if ( data == '1' || ( config && typeof config == 'object' ) ) {
                        $(".um_save_button").removeClass('btn-primary').addClass('btn-success');
                        $(".um_save_button").html('<i class="fa fa-check"></i> Saved');
                        $(".um_error_msg").html("");
                    }
                    else { 
                        $(".um_save_button").removeClass('btn-primary').addClass('btn-danger');
                        $(".um_save_button").html('Not Saved <i class="fa fa-exclamation-triangle"></i>');
                        $(".um_error_msg").html('<span class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> ' + data + '</span>');
                    }

                    setTimeout(function(){
                        $(".um_save_button").removeClass('btn-success').removeClass('btn-danger').addClass('btn-primary');
                        $(".um_save_button").html('Save Changes');
                    }, 3000);

                }
            });
        }
    };
     
    userMeta.formEditor = {

        init: function() {
            if ( $('#um_fields_editor').length ) {
                this.name = 'fields_editor';
                this.editor = $('#um_fields_editor');
                this.fieldsLoad();
                this.fieldsEvents();
                
            } else if ( $('#um_form_editor').length ) {
                this.name = 'form_editor';
                this.editor = $('#um_form_editor');
                this.formLoad();
                this.formEvents();
            }
        },
        
        fieldsLoad: function() {
            this.expandFirstField();

            this.load();
        },

        formLoad: function() {    
            this.collapseAll();
            this.sanitizeSelectors();
            
            this.load();
        },
        
        load: function() {
            $('#um_fields_container').sortable();
            
            $(window).scroll(this.steadySidebar);
            $(window).resize(this.steadySidebar);
            $(window).load(this.steadySidebar);
            
            this.loadConditionalConfig();
        },
        
        fieldsEvents: function() {
            this.editor.on('click', '.um_save_button', this.updateFields);
            this.editor.on('click', '#um_fields_selectors .panel-heading', this.toggleSelectorPanel);
            
            this.editor.on('click', '.um_field_selecor', this.addNewField);

            this.events();
        },
         
        formEvents: function() {
            this.editor.on('click', '.um_field_selecor', this.addNewFormField);

            this.editor.on('click', '#um_fields_selectors .panel-heading', this.toggleSelectorPanel);
            this.editor.on('change', '.um_enable_conditional_logic', this.toggleConditionalPanel);
            
            this.editor.on('click', '.um_conditional_plus', this.conditionalPlus);
            this.editor.on('click', '.um_conditional_minus', this.conditionalMinus);

            this.editor.on('click', '.um_save_button', this.updateForm);
            
            this.events();
        },
        
        events: function() {
            this.editor.on('change', 'select[name=field_type]', this.changeField);
            
            this.editor.on('keyup', 'input[name=field_title]', this.changeTitle);
            this.editor.on('blur', 'input[name=field_title]', this.updateMetaKey);
            this.editor.on('blur', 'input[name=meta_key]', this.updateMetaKey);

            this.editor.on('click', '.panel .panel-heading .um_trash', this.removePanel);
            
            this.editor.on('change', '.um_parent', this.toggleConditionalConfig);
        },
        
        expandFirstField: function() {
            $('#um_fields_container .panel-collapse').removeClass('in');
            $('#um_fields_container .panel-collapse').first().addClass('in');
            
            $('#um_fields_selectors .panel-collapse').first().addClass('in');
        },
        
        collapseAll: function() {
            $('#um_fields_container .panel-collapse').removeClass('in');
        },
        
        sanitizeSelectors: function() {
            var first = $('#um_fields_selectors .panel-collapse').first();
            first.addClass('in');
            first.css('max-height', '300px');
            first.css('overflow', 'auto');
        },
              
        toggleSelectorPanel: function() {
            self = $(this).closest(".panel").find(".collapse");
            $(this).closest(".panel-group").find(".collapse").not(self).slideUp();
        },
        
        removePanel: function() {
            if (confirm('Confirm to remove?')) {
                if ( userMeta.formEditor.name == 'form_editor' ) {
                    var fieldID = $(this).closest(".panel").find('.um_field_id').text();
                    $('#um_fields_selectors button[data-field-id="'+ fieldID +'"]').show();
                    
                    userMeta.formEditor.removeOptionFromConditions( fieldID );
                }
                
                $(this).closest(".panel").remove();
            }
        },
        
        addNewField: function() {
            var self = $(this);
            var label = self.text();
            var newID = parseInt($('#um_max_id').val()) + 1;

            var arg = 'id=' + newID + '&field_type=' + $(this).attr('data-field-type');
            arg = arg + '&action=um_add_field&_wpnonce=' + $(this).attr('data-nonce');
            
            $.ajax({
            type: 'post',
            url: ajaxurl,
            data: arg,
                beforeSend: function(){ self.html('<i class="fa fa-spin fa-circle-o-notch"></i> ' + label) },
                success: function( data ){
                    self.html(label);
                    $('#um_fields_container').append(data);
                    $('#um_max_id').val(newID);
                    
                    $('html, body').animate({
                        scrollTop: $('#um_admin_field_' + newID).offset().top
                    });

                    userMeta.formEditor.loadConditionalConfig();
                    userMeta.admin.initMultiselect();
                }
            }); 
        },
        
        addNewFormField: function() {
            var self = $(this);
            var label = self.text();
            var newID = parseInt($("#um_max_id").val()) + 1;
            
            var isShared = 0;
            
            if ( $(this).attr('data-is-shared') && parseInt($(this).attr('data-field-id')) > 0 ) {
                isShared = 1;
                newID = parseInt($(this).attr('data-field-id'));
            }

            var arg = 'id=' + newID + '&field_type=' + $(this).attr('data-field-type')
            arg = arg + '&action=um_add_form_field&is_shared=' + isShared + '&_wpnonce=' + $(this).attr('data-nonce');
            
            $.ajax({
            type: "post",
            url: ajaxurl,
            data: arg,
                beforeSend: function(){ self.html('<i class="fa fa-spin fa-circle-o-notch"></i> ' + label) },
                success: function( data ){
                    if ( isShared ) {
                        self.hide();
                    } else {
                        $('#um_max_id').val(newID);
                    }

                    self.html(label);
                    $('#um_fields_container').append(data);

                    $('html, body').animate({
                        scrollTop: $('#um_admin_field_' + newID).offset().top
                    });  

                    userMeta.formEditor.loadConditionalConfig();
                    userMeta.admin.initMultiselect();
                    
                    userMeta.formEditor.addOptionToConditions(newID);
                }
            }); 
        },
        
        changeField: function() {
            var field = $(this).closest('.panel');
            var id = $(field).find('.um_field_id').text();

            var arg = $(this).closest('.panel-body').find('input, textarea, select').serialize();
            arg = arg + '&id=' + id + '&editor=' + $('#um_editor').val();

            pfAjaxCall(this, "um_change_field", arg, function(data) {
                field.replaceWith(data);
                userMeta.formEditor.loadConditionalConfig();
                userMeta.admin.initMultiselect();
            });
        },
        
        changeTitle: function() {
            title = $(this).val();
            //if (!title){ title = 'Untitled'; }
            $(this).closest(".panel").find("h3 .um_field_label").text(title);
        },
        
        updateMetaKey: function() {
            self = $(this).closest('.panel');
            if( self.find('input[name=meta_key]').length && ! self.find('input[name=meta_key]').val() ) {
                title = self.find('input[name=field_title]').val()
                meta_key = title.trim().toLowerCase().replace(/[^a-z0-9 ]/g,'').replace(/\s+/g,'_');
                self.find('input[name=meta_key]').val(meta_key);
            }
        },
                
        steadySidebar: function() {
            var windowWidth = $(window).width();
            var windowHeight = $(window).height();
            var windowTop = $(window).scrollTop();
            
            var containerTop = $("#wpbody").offset().top + 5;
            //var containerTop = $(".wrap").offset().top;
            var FieldsContainerTop = $("#um_fields_container").offset().top;
            var FieldsContainerHeight = parseInt($("#um_fields_container").css("height"));
            var holderTop = $("#um_steady_sidebar_holder").offset().top;
            var sidebarTop = $("#um_steady_sidebar").offset().top;
            var sidebarHeight = parseInt($("#um_steady_sidebar").css("height"));
            
            if ( FieldsContainerHeight < windowHeight /*|| sidebarHeight > windowHeight*/ ) {
                $('#um_steady_sidebar').css({ position: 'relative', top: 0, width:'100%' });
                return;
            }
            
            //var footerTop = $("#wpfooter").offset().top;
            //var footerHeight = parseInt($("#wpfooter").css("height")) ;
            //var sidebarHeight = windowHeight - containerTop;
            
            var adminbarHeight = parseInt($("#wpadminbar").css("height"));
            //var sidebarHeight = parseInt($("#um_steady_sidebar").css("height")) ;
            var frameTop = windowTop + containerTop;
            var footerScrollTop = $("#wpfooter").offset().top - windowHeight;
            
            if ( windowWidth >= 790 ) { //Standard: 767
                if ( frameTop >= sidebarTop ) {
                    if ( windowTop >= footerScrollTop ) {
                        $('#um_steady_sidebar').css({ position: 'relative', top: (footerScrollTop - holderTop + containerTop), width:'100%' });
                    } else if ( FieldsContainerTop > sidebarTop ) {
                        $('#um_steady_sidebar').css({ position: 'relative', top: 0, width:'100%' });
                    } else {
                        $('#um_steady_sidebar').css({ position: 'fixed', top: containerTop, width:'26.5%' });
                    }
                    
                } else {
                    $('#um_steady_sidebar').css({ position: 'relative', top: 0, width:'100%' });
                }
                
            } else {
                $('#um_steady_sidebar').css({ position: 'relative', top: 0, width:'100%' });
            }
            
            //if ( sidebarHeight > windowHeight ) {
                $('#um_steady_sidebar').css({height: windowHeight - adminbarHeight, overflow:'hidden'});
            //}
            //console.log( FieldsContainerTop + ', ' + frameTop + ', ' + sidebarTop + ', ' + windowWidth  );
        },
        
        loadConditionalConfig: function() {
            this.editor.find('.panel-body .um_parent').each(function(){
                userMeta.formEditor.toggleConditionalConfig(this, $(this)); // First argument is for default event
            });
        },
        
        /**
         * Implemented for select and checkbox
         */
        toggleConditionalConfig: function(event, input) {
            if ( ! input ) {
                input = $(this);
            }
            
            var panel = input.closest('.panel-body');
            var tagName = input.prop("tagName").toLowerCase();
            
            if ( tagName == 'select' ) {
                var allChild = [];
                input.find('option').each(function(){
                    if ( $(this).data('child') ) {
                        child = $(this).data('child').split(',');
                        $.merge( allChild, child );
                    }
                });
                allChild = $.unique( allChild );//console.log(allChild);
                
                // Hide all child first
                $(allChild).each(function(){//console.log(panel.find( 'input[name='+ this +']' ).closest('p'));
                    panel.find( 'input[name='+ this +']' ).closest('.um_fb_field').slideUp();
                });
                
                // Show relevent child
                if ( input.find(':selected').data('child') ) {
                    targetChild = input.find(':selected').data('child').split(',');
                    $(targetChild).each(function(){
                        panel.find( 'input[name='+ this +']' ).closest('.um_fb_field').slideDown();
                    });
                }

            } else if ( tagName == 'input' && input.attr('type') == 'checkbox' ) {
                targetChild = input.data('child').split(',');
                $(targetChild).each(function(){//panel.find( 'input[name='+ this +']' ).hide();
                    if ( input.is(":checked") ) {
                        panel.find( 'input[name='+ this +']' ).closest('.um_fb_field').slideDown();
                    } else {
                        panel.find( 'input[name='+ this +']' ).closest('.um_fb_field').slideUp();
                    }
                });
            }
        },
        
        toggleConditionalPanel: function() {
            var panel = $(this).closest('.panel-body').find('.um_conditional_details');
            if ( $(this).is(":checked") ) {
                panel.slideDown();
            } else {
                panel.slideUp();
            }
            
            userMeta.formEditor.conditionsCountsEvent( panel );
        },
        
        conditionalPlus: function() {
            var panel = $(this).closest('.um_conditional_details');
            
            var row = $(this).closest('.form-group');      
            var clone = row.clone();  
            
            clone.find('.um_conditional_value').val('');
            
            clone.insertAfter(row);
            
            userMeta.formEditor.conditionsCountsEvent( panel );
            return;
            
            var fields = umFieldsEditor.currentFieldList;         
            var row = $(this).closest('tr');      
            var clone = row.clone();
            
            clone.find('.um_conditional_field_id').empty();
            
            $.each(fields, function(key, field) {   
                 clone.find('.um_conditional_field_id')
                    .append($("<option></option>")
                    .attr("value",field.id)
                    .text(field.title)); 
            });
            
            clone.find('input').val('');
            clone.insertAfter(row);
        },
        
        conditionalMinus: function(e) {
            e.preventDefault();
            
            var panel = $(this).closest('.um_conditional_details');

            rows = $(this).closest('.um_conditions').find('.form-group').length;   
            if ( rows > 1 ) {
                $(this).closest('.form-group').remove();
            }
            
            userMeta.formEditor.conditionsCountsEvent( panel );
        },
        
        conditionsCountsEvent: function( panel ) {
            var rows = panel.find('.um_conditions .form-group').length;  
            if ( rows > 1 ) {
                panel.find('.um_conditional_relation_div').slideDown();
                panel.find('.um_conditional_minus').show();
            } else {
                panel.find('.um_conditional_relation_div').slideUp();
                panel.find('.um_conditional_minus').hide();
            }
        },
        
        addOptionToConditions: function( id ) {
            var field = $('#um_admin_field_' + id + ' .panel-title');
            
            var optionLabel = $('#um_admin_field_' + id + ' .panel-title .um_field_panel_title').text();
            //optionLabel = 'ID:' + id + ' (' + field.find('.um_field_type').text() + ') ' + field.find('.um_field_label').text();
            
            this.editor.find('.um_conditional_field_id').each(function(){
                $(this).append('<option value="' + id + '">' + optionLabel + '</option>');
            });
            
            // Copy populated select
            first = this.editor.find('.um_conditional_field_id').first().html();
            $('#um_admin_field_' + id + ' .um_conditional_field_id').html(first);
        },
        
        removeOptionFromConditions: function( id ) {
            this.editor.find('.um_conditional_field_id option[value="'+ id + '"]').remove();
        },
        
        getConditionalLogic: function( element ) {
            var condition = {}, rules = []; 
            
            if (element.find('.um_enable_conditional_logic').is(':checked')) {
                condition.visibility = element.find('.um_conditional_visibility').val();
                condition.relation = element.find('.um_conditional_relation').val();

                $(element).find('.um_conditional_details .um_conditions .form-group').each(function(){
                    var rule = {};
                    rule.field_id = $(this).find('.um_conditional_field_id').val();
                    rule.condition = $(this).find('.um_conditional_condition').val();
                    rule.value = $(this).find('.um_conditional_value').val();
                    rules.push(rule);
                });

                condition.rules = rules;
            }
            
            return condition;
        },

        updateFields: function() {
            var fields = [];
            $(".um_field_single").each(function(index){
                fieldID = $(this).find(".um_field_id").text();
                field = {'id':fieldID};
                fieldObj = $(this).find('input, textarea, select').serializeArray(); 
                for (var i=0; i < fieldObj.length; i++) {
                    if ( fieldObj[i].value ) {
                        field[fieldObj[i].name] = fieldObj[i].value;
                    }
                }
                
                // Multiselect
                $(this).find('.um_multiselect').each(function(){
                    name = $(this).attr('name');
                    if ( name == 'undefined' ) return;

                    delete field[ name ];
                    name = name.replace('[]', '');
                    multiselectVal = [];
                    $(this).parent().find('.multiselect-container li.active input').each(function(){
                        var val = $(this).val();
                        if ( val && val != 'multiselect-all' ) {
                            multiselectVal.push( $(this).val() );
                        }
                    });
                    field[ name ] = multiselectVal;
                });
                
                fields[index] = field;
            });
            
            var arg = { "action": "um_update_field", "fields": fields };
            
            var input = $('#um_additional_input').find('input').serializeArray();
            for (var i=0; i < input.length; i++) {
                arg[input[i].name] = input[i].value;  
            }
            
            userMeta.admin.saveButton(arg);
        },

        updateForm: function() {
            var fields = [];
            $(".um_field_single").each(function(index){
                fieldID = $(this).find(".um_field_id").text();
                field = {'id':fieldID};
                fieldObj = $(this).find('input, textarea, select').serializeArray(); 
                for (var i=0; i < fieldObj.length; i++) {
                    field[fieldObj[i].name] = fieldObj[i].value;
                }
                
                // Multiselect
                $(this).find('.um_multiselect').each(function(){
                    name = $(this).attr('name');
                    if ( name == 'undefined' ) return;

                    delete field[ name ];
                    name = name.replace('[]', '');
                    multiselectVal = [];
                    $(this).parent().find('.multiselect-container li.active input').each(function(){
                        var val = $(this).val();
                        if ( val && val != 'multiselect-all' ) {
                            multiselectVal.push( $(this).val() );
                        }
                    });
                    field[ name ] = multiselectVal;
                });
                
                $(this).find('input[type="checkbox"]').each(function(){
                    name = $(this).attr('name');
                    if ( name && name != 'undefined' ) {
                        if ( $(this).is(':checked') ) {
                            field[ name ] = 1;
                        } else {
                            field[ name ] = 0;
                        }
                    }
                });
                
                condition = userMeta.formEditor.getConditionalLogic($(this));
                if (condition) {
                    field.condition = condition;
                }
                
                fields[index] = field;
            });
            
            var arg = { "action": "um_update_forms" };
            
            arg.form_key = $('input[name="form_key"]').val();
            arg.fields = fields;
            
            input = $('#um_form_settings_tab').find('input, textarea, select').serializeArray();
            for (var i=0; i < input.length; i++) {
                arg[input[i].name] = input[i].value;  
            }
            
            input = $('#um_additional_input').find('input').serializeArray();
            for (var i=0; i < input.length; i++) {
                arg[input[i].name] = input[i].value;  
            }
            
            //console.log(arg);
            
            userMeta.admin.saveButton(arg);
        }
        
    };
    
    userMeta.advanced = {

        init: function() {
            if ( $('#um_advanced_settings').length ) {
                this.editor = $('#um_advanced_settings');
                this.events();   
            }
        },
        
        events: function() {
            this.editor.on('click', '.um_generate_wpml_config', this.wpmlConfig);
        },
        
        wpmlConfig: function() {
            bindElement = $(this);
            pfAjaxCall(bindElement, 'um_generate_wpml_config', '', function(data) {
                bindElement.after("<div class='pf_ajax_result'>"+data+"</div>");  
            });
        }
        
    };

    $(function() {
        userMeta.admin.init();
        userMeta.formEditor.init();
        userMeta.advanced.init();
    });
    
})(jQuery);