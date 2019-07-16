// Admin Fields
jQuery(function ($) {
    'use strict';

    // Generate Slug on the fly, and single title on blur
    $('form #title').on('keyup', function () {
        var title = $(this).val();
        if ($(this).parents('form').find('#slug')) {
            $(this).parents('form').find('#slug').val(to_slug(title));
        }
        if ($(this).parents('form').find('#dbslug')) {
            $(this).parents('form').find('#dbslug').val(to_dbslug(title));
        }
    }).on('blur', function () {
        var title = $(this).val();
        var single_title = title;
        var find_ies = single_title.slice(-3);
        if (find_ies == 'ies') {
            single_title = single_title.slice(0, -3) + 'y';
        } else {
            var find_s = single_title[single_title.length - 1];
            if (find_s == 's') {
                single_title = single_title.slice(0, -1);
            }
        }
        $(this).parents('form').find('#title_single').val(single_title);
    });

    // Restrict slug input entry
    $('form #slug').on('keyup', function () {
        $(this).val(to_slug($(this).val()));
    });

    // Restrict dbslug input entry
    $('form #dbslug').on('keyup', function () {
        $(this).val(to_dbslug($(this).val()));
    });

    function to_slug(string, replacement = '-') {
        return string.replace(/[^\w\s-_]/gi, '').replace(/[\s]/g, replacement).toLowerCase();
    }

    function to_dbslug(string) {
        return to_slug(string, '_');
    }
});
