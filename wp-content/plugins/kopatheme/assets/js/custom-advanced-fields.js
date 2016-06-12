/* datetime */
var KF_Datetime_Js;

/* gallery */
var FK_Gallery, kf_gallery_iframe, kf_gallery_button;
kf_gallery_iframe = '';
kf_gallery_button = '';

/* icon picker */
var KF_Icon_Picker;
var kf_lighbox_icons_id = '#kf_advanced_field_lighbox_icons';


jQuery(document).ready(function() {
    KF_Datetime_Js.init_field_datetime();

    FK_Gallery.init();

    KF_Icon_Picker.init();
});

jQuery(document).ajaxSuccess(function() {
    KF_Datetime_Js.init_field_datetime();

    FK_Gallery.init();

    KF_Icon_Picker.init();
});

KF_Datetime_Js = {
    init_field_datetime: function() {
        if (jQuery('.kopa-framework-datetime').length > 0) {
            jQuery('.kopa-framework-datetime').each(function(index, element) {
                var kf_timepicker = jQuery(element).attr('data-timepicker');
                var kf_datepicker = jQuery(element).attr('data-datepicker');
                if ( 1 == kf_timepicker ) {
                    kf_timepicker = true;
                } else {
                    kf_timepicker = false;
                }
                if ( 1 == kf_datepicker ) {
                    kf_datepicker = true;
                } else {
                    kf_datepicker = false;
                }
                jQuery(element).datetimepicker({
                    lang: 'en',
                    timepicker: kf_timepicker,
                    datepicker: kf_datepicker,
                    format: jQuery(element).attr('data-format'),
                    i18n: {
                        en: {
                            months: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                            dayOfWeek: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"]
                        }
                    }
                });
            });
        }
    }
};

FK_Gallery = {
    init: function() {
        jQuery('.kopa-framework-gallery-box').on('click', '.kopa-framework-gallery-config', function(event) {
            event.preventDefault();
            kf_gallery_button = jQuery(this);
            if (kf_gallery_iframe) {
                kf_gallery_iframe.open();
                return;
            }
            kf_gallery_iframe = wp.media.frames.kf_gallery_iframe = wp.media({
                title: 'Gallery config',
                button: {
                    text: 'Use'
                },
                library: {
                    type: 'image'
                },
                multiple: true
            });
            kf_gallery_iframe.on('open', function() {
                var ids, selection;
                ids = kf_gallery_button.parents('.kopa-framework-gallery-box').find('input.kopa-framework-gallery').val();
                if ('' !== ids) {
                    selection = kf_gallery_iframe.state().get('selection');
                    ids = ids.split(',');
                    jQuery(ids).each(function(index, element) {
                        var attachment;
                        attachment = wp.media.attachment(element);
                        attachment.fetch();
                        selection.add(attachment ? [attachment] : []);
                    });
                }
            });
            kf_gallery_iframe.on('select', function() {
                var result, selection;
                result = [];
                selection = kf_gallery_iframe.state().get('selection');
                selection.map(function(attachment) {
                    attachment = attachment.toJSON();
                    return result.push(attachment.id);
                });
                if (result.length > 0) {
                    result = result.join(',');
                    kf_gallery_button.parents('.kopa-framework-gallery-box').find('input.kopa-framework-gallery').val(result);
                }
            });
            kf_gallery_iframe.open();
        });
    }
};

KF_Icon_Picker = {
    init: function() {
        /*if (jQuery('.upside-icon-picker-select').length > 0) {
         jQuery('.upside-icon-picker-select').change(function(event){
         var btn = jQuery(this);
         var icon = btn.val();
         console.log(icon);
         btn.parent().find('.upside-icon-picker-preview i').attr('class', icon);
         });
         }*/

        if (jQuery('.kf-icon-picker').length > 0) {
            jQuery('.kf-icon-picker').click(function(event) {
                var btn;
                event.preventDefault();
                btn = jQuery(this);
                if (jQuery(kf_lighbox_icons_id).length !== 1) {
                    jQuery('body').append('<div id="kf_advanced_field_lighbox_icons" class="upside-hide"></div>');
                    jQuery.ajax({
                        beforeSend: function(jqXHR) {},
                        success: function(data, textStatus, jqXHR) {
                            jQuery(kf_lighbox_icons_id).html(data);
                        },
                        complete: function() {
                            KF_Icon_Picker.open_lighbox(btn);
                        },
                        url: kopa_advanced_field.ajax_url,
                        dataType: "html",
                        type: 'GET',
                        async: false,
                        data: {
                            action: 'get_lighbox_icons'
                        }
                    });
                } else {
                    KF_Icon_Picker.open_lighbox(btn);
                }
            });
        }
    },
    open_lighbox: function(btn) {
        jQuery(kf_lighbox_icons_id).dialog({
            width: 360,
            height: 480,
            modal: true,
            title: kopa_advanced_field.i18n.icon_picker,
            buttons: {
                "OK": function() {
                    var icon;
                    icon = KF_Icon_Picker.click_ok();
                    btn.parent().find('.kf-icon-picker-value').val(icon);
                    btn.parent().find('.kf-icon-picker-preview i').attr('class', icon);
                },
                "cancel": function() {
                    jQuery(kf_lighbox_icons_id).dialog('close');
                }
            }
        });
    },
    click_ok: function() {
        var icon;
        icon = jQuery(kf_lighbox_icons_id).find('.kf-icon-item.upside-active i').attr('class');
        jQuery(kf_lighbox_icons_id).dialog('close');
        return icon;
    },
    select_a_icon: function(event, obj) {
        event.preventDefault();
        obj.parents('.kf-wrap').find('.kf-icon-item').removeClass('upside-active');
        obj.addClass('upside-active');
    },
    filter_icons: function(event, obj) {
        var filter, regex, wrap;
        event.preventDefault();
        wrap = obj.parents('.kf-list-of-icon');
        filter = obj.val();
        if (!filter) {
            wrap.find('.kf-icon-item').show();
            return false;
        }
        regex = new RegExp(filter, "i");
        wrap.find('.kf-icon-item i').each(function(index, element) {
            if (jQuery(this).data('title').search(regex) < 0) {
                jQuery(this).parent().hide();
            } else {
                jQuery(this).parent().show();
            }
        });
    }
};