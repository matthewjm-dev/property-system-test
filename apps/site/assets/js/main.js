// Admin Main
jQuery(function ($) {
    'use strict';

    $('body').removeClass('no-js');

    // Header buttons
    $('div.head-mob-button').on('click', function () {
        var target = $(this).data('target');

        var doopen = true;
        if ($(this).hasClass('active')) {
            doopen = false;
        }

        var already_open = false;
        $('.head-mob-button.active').each(function () {
            already_open = true;
            var thistarget = $(this).data('target');
            if (target != thistarget) {
                $(this).removeClass('active');
                $(thistarget).hide();
            }
        });

        if (doopen) {
            $('html, body').animate({scrollTop: 0}, "fast").css('overflow', 'hidden');
            $(this).addClass('active');
            if (already_open) {
                $(target).show();
            } else {
                $(target).fadeIn(200);
            }
        } else {
            $('html, body').css('overflow', 'auto');
            $(this).removeClass('active');
            $(target).fadeOut(200);
        }
    });
});
