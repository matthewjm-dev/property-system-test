/*!
	AJAX Button v1.0
	Info: Processes forms through AJAX then calls relevant Success / Failure function
	Author: Matthew Murray ( admin@matdragon.com )

	Change Log:
	v1.0 - Created from ajaxForm v1.1.5
*/

(function ($) {
	'use strict';

	$.fn.ipsAjaxButton = function (args = {}) {

		return this.each(function () {

			var loader_message = '';
			var hide_loader = true;

			var methods = {
				'add_loader': function ($button) {
					var message = '';
					if (loader_message != '') {
						message = '<p class="loader-message">' + loader_message + '</p>';
					}
					$button.prepend('<div class="loader active"><div class="loader-icon"></div>' + message + '</div>');
				},

				'remove_loader': function ($button) {
					$button.find('.loader').remove();
				}
			};

			if (typeof(args.loader_message) != "undefined") {
				loader_message = args.loader_message;
			}

			$(this).on('click', function (e) {
				e.preventDefault();
				methods.add_loader($(this));

				if (typeof args.before === 'function') {
					args.before.call(this, methods);
				}

				var $button = $(this);
				var action = $button.attr('action') || '/';

				$.ajax({
					url: action,
					method: 'POST',
					type: 'POST',
					dataType: 'json',
					contentType: false,
					processData: false,
					//data: form_data,
					context: this,
					success: function (json) {

						if (json.fragments) { // Fragments
							$.each(json.fragments, function (key, value) {
								var content = '';
								var init_form = false;

								if (typeof value === 'object') {
									content = value.content;
									init_form = true;
								} else {
									content = value;
								}

								$(key).replaceWith(content);

								if (init_form) {
									var id = $(content).attr('id');
									$('#' + id).ipsAjaxForm({});
								}
							});
						}

						if (json.html) { // HTML
							if (typeof args.html === 'function') {
								args.html.call(this, json.html);
							}
						}

						if (json.reload) { // Reload
							if (typeof args.reload_override === 'function') {
								args.reload_override.call(this, json);
							} else {
								hide_loader = false;
								location.reload();
							}
						} else if (json.redirect) { // Redirect
							if (typeof args.redirect_override === 'function') {
								args.redirect_override.call(this, json);
							} else {
								hide_loader = false;
								window.location.replace(json.redirect);
							}
						} else {
							if (json.errors) { // Errors
								methods.remove_loader($button);

								// Call Failure function
								if (typeof args.failure_override === 'function') {
									args.failure_override.call(this, json.errors);
									args.failure.call(this, json.errors);
								} else {
									if (typeof args.failure === 'function') {
										args.failure.call(this, json.errors);
									}

									$.each(json.errors, function (message) {
										add_flash_error(message);
									});
								}

							} else if (json.success) { // Success

								// Call Success function
								if (typeof args.success_override === 'function') {
									args.success_override.call(this, json.success);
								} else {
									if (typeof args.success === 'function') {
										args.success.call(this, json.success);
									}

									if (json.success !== 'true') {
										add_flash_success(json.success);
									}
								}
							}
						}
					},
					complete: function () {
						if (typeof args.complete_override === 'function') {
							args.complete_override.call(this);
						} else {
							if (hide_loader) {
								methods.remove_loader($(this));
							}
						}
					}
				});
			});
		});
	};

})(jQuery);


/*!
	AJAX Form v1.1.5
	Info: Processes forms through AJAX then calls relevant Success / Failure function
	Author: Matthew Murray ( admin@matdragon.com )

	Change Log:
	v1.1.1 - Added "success_redirect" to remove loader from normal forms on complete, but keep them for redirect responses
	v1.1.2 - Fix for errors displayed on forms within forms
	v1.1.3 - Changed existing "success_redirect" to "hide_loader"
	v1.1.4 - Added "redirect_override" argument
	v1.1.5 - Updated how file uploads are handled to accommodate multiple files per input
*/

(function ($) {
    'use strict';

    $.fn.ipsAjaxForm = function (args = {}) {

        return this.each(function () {

            var loader_message = '';
            var submit_type = 'submit';
            var submit_delegate = '';
            var auto_submit = false;
            var hide_loader = true;

            var methods = {

                'add_error': function ($form, target, message) {
                    var $field = $form.find('input[name="' + target + '"], input[name="' + target + '[]"], select[name="' + target + '"], textarea[name="' + target + '"]');

                    if ($field.length) {
                        var $fieldset = $field.closest('fieldset');
                        var content = '<p class="message error">' + message + '</p>';

                        $fieldset.addClass('error').append(content);
                    } else {
                        var content = '<p class="message error">' + message + '</p>';

                        methods.add_message($form, content);
                    }
                },

                'add_message': function ($form, content) {
                    $form.prepend(content);
                },

                'remove_error': function ($target) {
                    $target.removeClass('error').find('.message').remove();
                },

                'clear_form': function ($form) {
                    $form.find('.message').remove();
                    $form.find('.error').removeClass('error');
                },

                'watch_errors': function ($form) {
                    $form.on('click', 'input, focus, select, textarea', function () {
                        var $fieldset = $(this).parents('fieldset');
                        if ($fieldset.hasClass('error')) {
                            methods.remove_error($fieldset);
                        }
                    });
                },

                'add_loader': function ($form) {
                    var message = '';
                    if (loader_message != '') {
                        message = '<p class="loader-message">' + loader_message + '</p>';
                    }
                    $form.prepend('<div class="loader active"><div class="loader-icon"></div>' + message + '</div>');
                },

                'remove_loader': function ($form) {
                    $form.find('.loader').remove();
                }
            };

            if (typeof(args.auto_submit) != "undefined" && args.auto_submit == true) {
                submit_type = 'change paste submit';
                submit_delegate = ':input';
                auto_submit = true;
            }

            if (typeof(args.loader_message) != "undefined") {
                loader_message = args.loader_message;
            }

            // auto submit forms can still be submitted as a normal form which is a problem
            $(this).on(submit_type, submit_delegate, function (e) {

                var keycode = e.which;

                if (!auto_submit || (e.type === 'paste' || e.type === 'change' || (
                    (keycode === 46 || keycode === 8) || // delete & backspace
                    (keycode > 47 && keycode < 58) || // number keys
                    keycode == 32 || keycode == 13 || // spacebar & return key(s) (if you want to allow carriage returns)
                    (keycode > 64 && keycode < 91) || // letter keys
                    (keycode > 95 && keycode < 112) || // numpad keys
                    (keycode > 185 && keycode < 193) || // ;=,-./` (in order)
                    (keycode > 218 && keycode < 223)))) { // [\]' (in order))

                    e.preventDefault();
                    methods.add_loader($(this));

                    if (typeof args.before === 'function') {
                        args.before.call(this);
                    }

                    if (auto_submit) {
                        var $form = $(this).parents('form');
                    } else {
                        var $form = $(this);
                    }
                    var form_data = new FormData();
                    var action = $form.attr('action') || '/';

                    var content = $form.serializeArray(); // + '&ajax=1';
                    $.each(content, function (key, input) {
                        form_data.append(input.name, input.value);
                    });

                    // File uploads
                    var file_inputs = $form.find('input[type="file"]');
                    $.each($(file_inputs), function (key, input) {
                        var input_name = $(input).attr('name');
                        $.each(input.files, function(file_key, file_item) {
                            form_data.append(input_name + '[' + file_key + ']', file_item);
                        });
                    });

                    $.ajax({
                        url: action,
                        method: 'POST',
                        type: 'POST',
                        dataType: 'json',
                        contentType: false,
                        processData: false,
                        data: form_data,
                        context: this,
                        success: function (json) {

                            if (json.fragments) { // Fragments
                                $.each(json.fragments, function (key, value) {
                                    var content = '';
                                    var init_form = false;

                                    if (typeof value === 'object') {
                                        content = value.content;
                                        init_form = true;
                                    } else {
                                        content = value;
                                    }

                                    $(key).replaceWith(content);

                                    if (init_form) {
                                        var id = $(content).attr('id');
                                        $('#' + id).ipsAjaxForm({});
                                    }
                                });
                            }

                            if (json.html) { // HTML
                                if (typeof args.html === 'function') {
                                    args.html.call(this, json.html);
                                }
                            }

                            if (json.reload) { // Reload
                                if (typeof args.reload_override === 'function') {
                                    args.reload_override.call(this, json);
                                } else {
                                    hide_loader = false;
                                    //window.location.replace(json.redirect);
                                    location.reload();
                                }
                            } else if (json.redirect) { // Redirect
                                if (typeof args.redirect_override === 'function') {
                                    args.redirect_override.call(this, json);
                                } else {
                                    hide_loader = false;
                                    window.location.replace(json.redirect);
                                }
                            } else {
                                if (json.errors) { // Errors

                                    methods.remove_loader($form);

                                    // Call Failure function
                                    if (typeof args.failure_override === 'function') {
                                        args.failure_override.call(this, json.errors);
                                        args.failure.call(this, json.errors);
                                    } else {
                                        if (typeof args.failure === 'function') {
                                            args.failure.call(this, json.errors);
                                        }

                                        methods.clear_form($form);

                                        $.each(json.errors, function (input, message) {
                                            methods.add_error($form, input, message);
                                        });

                                        methods.watch_errors($form);
                                    }

                                } else if (json.success) { // Success

                                    // Call Success function
                                    if (typeof args.success_override === 'function') {
                                        args.success_override.call(this, json.success);

                                        methods.clear_form($form);
                                    } else {
                                        if (typeof args.success === 'function') {
                                            args.success.call(this, json.success);
                                        }

                                        methods.clear_form($form);

                                        if (json.success !== 'true') {
                                            var content = '<p class="success message">' + json.success + '</p>';
                                            methods.add_message($form, content);
                                        }
                                    }
                                }
                            }
                        },
                        complete: function () {
                            if (typeof args.complete_override === 'function') {
                                args.complete_override.call(this);
                            } else {
                                if (hide_loader) {
                                    methods.remove_loader($(this));
                                }
                            }
                        }
                    });
                }
            });
        });
    };

})(jQuery);


/*!
	Form Errors v1.0
	Info: Received a set of form input key / values and displays the error messages
	Author: Matthew Murray ( matthew@exleysmith.com )
*/
//jQuery( function( $ ) {

var error_methods = {

    'add_error': function ($form, target, message) {
        var $field = $form.find('input[name="' + target + '"], input[name="' + target + '[]"], select[name="' + target + '"], textarea[name="' + target + '"]');

        if ($field.length) {
            var $fieldset = $field.closest('fieldset');
            var content = '<p class="message error">' + message + '</p>';

            $fieldset.addClass('error').append(content);
        } else {
            var content = '<p class="message error">' + message + '</p>';

            methods.add_message($form, content);
        }
    },

    'add_message': function ($form, content) {
        $form.prepend(content);
    },

    'remove_error': function ($target) {
        $target.removeClass('error').find('.message').remove();
    },

    'clear_form': function ($form) {
        $form.find('.message').remove();
        $form.find('.error').removeClass('error');
    },

    'watch_errors': function ($form) {
        $form.on('click', 'input, focus, select, textarea', function () {
            var $fieldset = $(this).parents('fieldset');
            if ($fieldset.hasClass('error')) {
                error_methods.remove_error($fieldset);
            }
        });
    }
}

function show_errors($form, errors) {
    error_methods.clear_form($form);

    $.each(errors, function (input, message) {
        error_methods.add_error($form, input, message);
    });

    error_methods.watch_errors($form);
}

function show_success($form, message) {
    error_methods.clear_form($form);

    var content = '<p class="success message">' + message + '</p>';

    error_methods.add_message($form, content);
}

//});

// Add a Success flash message
function add_flash_success( message ) {
    add_flash(message, 'success');
}

// Add an Error flash message
function add_flash_error( message ) {
    add_flash(message, 'error');
}

// Add a flash message
function add_flash( message, type ) {
    if ($('#flash-messages').length) {
        var $content = $('<p class="message ' + type + '">' + message + '<span>Dismiss<i class="far fa-times-circle"></i></span></p>');

        $('#flash-messages .container').prepend($content);

        // Remove the created flash message after 3 seconds
        setTimeout(function() {
            $content.fadeOut(400, function() {
                $content.remove();
            });
        }, 3000);
    }
}

// Remove flash messages after 3 seconds that exist on page load
$( document ).ready( function() {
    setTimeout(function() {
        var $flash = $('section#flash-messages .container p.message');
        $flash.fadeOut(400, function() {
            $flash.remove();
        });
    }, 3000);
});

jQuery(function ($) {
    'use strict';

    $('.repeater-group .repeater-group-add').on('click', function(e) {
        e.preventDefault();

        var field_group = $(this).siblings('.repeater-group-fields').find('.repeater-group-item:first-child');
        var new_field_group = field_group.clone();

        $.each(new_field_group.find('input, select, textarea'), function(key, field) {
            $(field).val(function() {
                this.defaultValue;
            })
        });

        new_field_group.appendTo('.repeater-group-fields');
    });

    $('.repeater-group').on('click', '.repeater-group-item .repeater-group-item-remove', function() {
       $(this).parents('.repeater-group-item').remove();
    });
});

// Add Loader
function addLoader($element, message = '') {
    if (message) {
        message = '<div class="loader-message">' + message + '</div>';
    }
    $element.prepend('<div class="loader active"><div class="loader-icon"></div>' + message + '</div>');
}

// Remove Loader
function removeLoader($element) {
    $element.find('.loader').remove();
}

/*! matchMedia() polyfill - Test a CSS media type/query in JS. Authors & copyright (coffee) 2012: Scott Jehl, Paul Irish, Nicholas Zakas, David Knight. Dual MIT/BSD license */
window.matchMedia || (window.matchMedia = function () {
    var b = (window.styleMedia || window.media);
    if (!b) {
        var c = document.createElement("style"), a = document.getElementsByTagName("script")[0], d = null;
        c.type = "text/css";
        c.id = "matchmediajs-test";
        a.parentNode.insertBefore(c, a);
        d = ("getComputedStyle" in window) && window.getComputedStyle(c, null) || c.currentStyle;
        b = {
            matchMedium: function (e) {
                var f = "@media " + e + "{ #matchmediajs-test { width: 1px; } }";
                if (c.styleSheet) {
                    c.styleSheet.cssText = f
                } else {
                    c.textContent = f
                }
                return d.width === "1px"
            }
        }
    }
    return function (e) {
        return {matches: b.matchMedium(e || "all"), media: e || "all"}
    }
}());

var media_sizes = {xl: 1200, lg: 992, md: 768, sm: 545, xs: 1};

var media_queries = {xl: 0, lg: 0, md: 0, sm: 0, xs: 0};

function refreshMediaQueries() {
    media_queries.xl = window.matchMedia('(min-width:1200px)');
    media_queries.lg = window.matchMedia('(min-width:992px) and (max-width: 1199px)');
    media_queries.md = window.matchMedia('(min-width:768px) and (max-width: 991px)');
    media_queries.sm = window.matchMedia('(min-width:545px) and (max-width: 767px)');
    media_queries.xs = window.matchMedia('(max-width:544px)');
}

function isBreakpoint(size) {
    return media_queries[size].matches;
}

function getBreakpoint(size) {
    return media_sizes[size];
}

function isMobile() {
    if (isBreakpoint('xs') || isBreakpoint('sm')) {
        return true;
    }
    return false;
}

function isTablet() {
    if (isBreakpoint('md')) {
        return true;
    }
    return false;
}

function isLaptop() {
    if (isBreakpoint('lg')) {
        return true;
    }
    return false;
}

function isDesktop() {
    if (isBreakpoint('xl')) {
        return true;
    }
    return false;
}

function isBreakpointUp(size) {
    if (size == 'xs') {
        return true;
    } else if (size == 'sm') {
        if (isBreakpoint('sm') || isBreakpoint('md') || isBreakpoint('lg') || isBreakpoint('xl')) {
            return true;
        }
    } else if (size == 'md') {
        if (isBreakpoint('md') || isBreakpoint('lg') || isBreakpoint('xl')) {
            return true;
        }
    } else if (size == 'lg') {
        if (isBreakpoint('lg') || isBreakpoint('xl')) {
            return true;
        }
    } else if (size == 'xl') {
        if (isBreakpoint('xl')) {
            return true;
        }
    }

    return false;
}

function isBreakpointDown(size) {
    if (size == 'xs') {
        if (isBreakpoint('xs')) {
            return true;
        }
    } else if (size == 'sm') {
        if (isBreakpoint('xs') || isBreakpoint('sm')) {
            return true;
        }
    } else if (size == 'md') {
        if (isBreakpoint('xs') || isBreakpoint('sm') || isBreakpoint('md')) {
            return true;
        }
    } else if (size == 'lg') {
        if (isBreakpoint('xs') || isBreakpoint('sm') || isBreakpoint('md') || isBreakpoint('lg')) {
            return true;
        }
    } else if (size == 'xl') {
        return true;
    }

    return false;
}

refreshMediaQueries();
jQuery(function ($) {
    $(window).on('resize', refreshMediaQueries);
});


/*!
	Popup plugin v1.0
	Info: This plugin is designed for simple, functional popup dialogs.
	Author: Matthew Murray ( admin@matdragon.com )

	--| Update Log |--

	- v1.0

*/

function show_popup( $content, popup_class ) {
	if ( !$( 'body' ).find( '#popup' ).length ) {
		var popup_close = '<div id="popup-close">x</div>';
		var popup = '<div id="popup" class="' + popup_class + '">' + popup_close + $content + '</div>';
		var cover = '<div id="popup-cover" class="' + popup_class + '"></div>';

		$( 'body' ).prepend( cover );
		$( 'body' ).prepend( popup );

		return $( 'body' ).find( '#popup.' + popup_class.replace(' ', '.') );
	}
}

function remove_popup( popup ) {
	var popup_class = popup.attr('class').replace(' ', '.');
	var toclose = '#popup.' + popup_class + ', #popup-cover.' + popup_class;

	$( 'body' ).find( toclose ).fadeOut( function() {
		$( 'body' ).find( toclose ).remove();
	} );
}

$( document ).ready( function() {
	var closes = '#popup-close, .popup-close';

	$( 'body' ).on( 'click', closes, function() {
		var popup = $( this ).parents( '#popup' );
		remove_popup( popup );
	});

	var covercloses = '#popup-cover';
	$( 'body' ).on( 'click', covercloses, function() {
		var popup_cover_class = $(this).attr('class').replace(' ', '.');
		var popup = $( '#popup.' + popup_cover_class );
		remove_popup( popup );
	});

} );
