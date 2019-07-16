// Site Forms
jQuery(function ($) {
    'use strict';

    // Init Ajax Forms
    $('#contact_form_submissions').ipsAjaxForm({
        success: function (message) {
            $(this).addClass('success').empty();
        }
    });

    /*$('form.ajax_form.create').ipsAjaxForm({
        before: function () {
            $(this).find('fieldset.editor textarea').each(function (key, value) {
                var editor_id = $(this).attr('id');
                CKEDITOR.instances[editor_id].updateElement();
            });
        },
        failure: function (errors) {
            show_errors($(this), errors);
        }
    });*/
});
