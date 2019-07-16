// Admin File Picker
jQuery(function ($) {
    'use strict';

    /*$('.image-picker-button').ipsAjaxButton({
        success: function (json) {

        }
    });*/

    $('.file-picker-button').on('click', function (e) {
        e.preventDefault();
        var file_picker = $(this);
        var name = file_picker.data('name');
        var action = file_picker.data('action') + name + '/';
        var items = file_picker.parents('.file-picker').find('.file-picker-preview-item input');

        $.ajax({
            url: action,
            type: 'POST',
            dataType: 'json',
            data: items,
            success: function (json) {

                if (json.html) {
                    var file_selector_popup = show_popup(json.html, 'file-picker ' + name + '_popup');

                    file_selector_popup.find('form').ipsAjaxForm({
                        html: function (html) {
                            file_picker.siblings('.file-picker-selection').empty().prepend(html);
                            file_picker.siblings('input').val();
                            remove_popup(file_selector_popup);
                        },
                        failure: function (errors) {
                            show_errors($(this), errors);
                        }
                    });
                }
            }
        });
    });

    $('body').on('click', '.file-picker-preview-item-remove', function(e) {
        e.preventDefault();
        $(this).parents('.file-picker-preview-item').remove();
    } );
});
