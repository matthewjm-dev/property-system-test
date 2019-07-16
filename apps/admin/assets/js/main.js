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
            //$('html, body').animate({scrollTop: 0}, "fast").css('overflow', 'hidden');
            $('html, body').css('overflow', 'hidden');
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

    $('.menu-sub-item').on('click', function () {
        if ($(this).hasClass('open')) {
            $(this).removeClass('open').addClass('close');
            $(this).siblings('ul').slideDown(200);
        } else {
            $(this).removeClass('close').addClass('open');
            $(this).siblings('ul').hide();
        }

    });

    // Footer Up
    $('#footer-up').on('click', function () {
        $('html,body').animate({scrollTop: 0}, 'slow');
    });

    // Remove success messages when clicked
    $('body').on('click', '.success.message', function () {
        $(this).slideUp(200, function () {
            $(this).remove();
        });
    });

    // Add content editors
    if ($('fieldset.editor textarea').length) {
        $('fieldset.editor textarea').each(function (key, value) {
            CKEDITOR.replace(value);
        });
    }

    // Sort table
    $('table[data-sort] tbody').sortable({
        handle: '.sort-handle',
        axis: 'y',
        update: function (event, ui) {
            var sort_order = $(this).sortable('serialize');
            var sort_url = $(this).parents('table').data('sort');
            console.log('SORTABLE');
            console.log(sort_url);
            console.log(sort_order);
            $.ajax({
                url: sort_url,
                data: sort_order,
                method: 'POST',
                type: 'POST'
            });

        }
    }).disableSelection();

    // Anchor Link Scroll
    $('a.jump').on('click', function(e) {
        var anchor = $( $(this).attr('href') );
        var speed = 'fast';
        if (anchor.length) {
            e.preventDefault();
            var offset = anchor.offset().top - 40;
            $('html,body').animate({scrollTop: offset}, speed);
        }
    });

});
