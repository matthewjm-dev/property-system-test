// Admin Forms
jQuery(function ($) {
    'use strict';

    // Init Ajax Forms
    $('#login_form').ipsAjaxForm({});
    $('#forgotten_form').ipsAjaxForm({});
    $('#reset_form').ipsAjaxForm({});

    $('form.ajax_form.create').ipsAjaxForm({
        before: function () {
            $(this).find('fieldset.editor textarea').each(function (key, value) {
                var editor_id = $(this).attr('id');
                CKEDITOR.instances[editor_id].updateElement();
            });
        },
        /*success: function (message) {
            show_success($(this), message);
        },*/
        failure: function (errors) {
            show_errors($(this), errors);
        }
    });

    $('form.ajax_form.edit').ipsAjaxForm({
        before: function () {
            $(this).find('fieldset.editor textarea').each(function (key, value) {
                var editor_id = $(this).attr('id');
                CKEDITOR.instances[editor_id].updateElement();
            });
        },
        success: function (message) {
            var totop = 0;
            if ($('#page-head').length) {
                totop = $('#page-head').offset().top - '10';
            }
            $('html, body').animate({scrollTop: totop});
            show_success($(this), message);
        },
        failure: function (errors) {
            show_errors($(this), errors);
        }
    });

    $('form.ajax_form.remove').ipsAjaxForm({
        html: function (html) {
            var id = $(this).attr('id');
            var remove_popup = show_popup(html, id + '_popup');
            remove_popup.find('form').ipsAjaxForm({
                success: function (redirect) {
                    window.location.replace(redirect);
                },
                failure: function (errors) {
                    show_errors($(this), errors);
                }
            });
        }
    });

    $('form.ajax_form.restore').ipsAjaxForm({
        html: function (html) {
            var id = $(this).attr('id');
            var restore_popup = show_popup(html, id + '_popup');
            restore_popup.find('form').ipsAjaxForm({
                success: function (redirect) {
                    window.location.replace(redirect);
                },
                failure: function (errors) {
                    show_errors($(this), errors);
                }
            });
        }
    });

    // Modules form TYPE
    $('form.ajax_form select#type').on('change', function () {
        var field_type = $(this).val();
        var action = '/admin/modules/do_field_type_change/' + field_type;
        $.ajax({
            url: action,
            type: 'POST',
            dataType: 'json',
            success: function (json) {
                $.each(json.show, function (key, value) {
                    $(value).removeClass('hidden');
                });
                $.each(json.hide, function (key, value) {
                    $(value).addClass('hidden');
                });
            }
        });
    });

    $('form.ajax_form select#link').on('change', function () {
        var link_module = $(this).val();
        var action = '/admin/modules/do_field_link_module_change/' + link_module;
        $('#field-link_field').prepend('<div class="loader active"><div class="loader-icon"></div></div>');

        $.ajax({
            url: action,
            type: 'POST',
            dataType: 'json',
            success: function (json) {
                $('#link_field').find('option').not(':first').remove();

                $.each(json.link_module_fields, function (key, value) {
                    var selected = '';
                    console.log('Adding Link Field item - key:' + key + ' value.text: ' + value.text + ' value.value: ' + value.value);
                    $('#link_field').append('<option value="' + value.value + '">' + value.text + '</option>');
                });

                $('#field-link_field').find('.loader').remove();
            }
        });
    });

    $('form.ajax_form.toggle_form').ipsAjaxForm({ // Toggle Live / Lock
        success_override: function (update) {
            $(this).parents('tr').addClass(update.add).removeClass(update.remove);
        },
        failure: function (errors) {
            show_errors($(this), errors);
        }
    });

    // Filterbars
    $('form.ajax_form#filterbar').ipsAjaxForm({
        auto_submit: true
    });
});
