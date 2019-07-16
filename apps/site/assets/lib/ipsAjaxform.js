/*!
	AJAX Form v1.0
	Info: Processes forms through AJAX then calls relevant Success / Failure function
	Author: Matthew Murray ( admin@matdragon.com )
*/

(function ($) {
	'use strict';

	$.fn.ipsAjaxForm = function (args) {

		return this.each(function () {

			var methods = {

				'add_error': function ($form, target, message) {
					var $field = $('#' + target);

					if ($field.length) {
						var $fieldset = $field.parents('fieldset');
						var content = '<p class="message">' + message + '</p>';

						$fieldset.addClass('error').append(content);
					} else {
						var content = '<p class="error message">' + message + '</p>';

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
					$form.prepend('<div class="loader active"><div class="loader-icon"></div></div>');
				},

				'remove_loader': function ($form) {
					$form.find('.loader').remove();
				}
			};

			var submit_type = 'submit';
			var submit_delegate = '';
			var auto_submit = false;

			if (args.auto_submit) {
				submit_type = 'change keyup paste';
				submit_delegate = ':input';
				auto_submit = true;
			}

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

					var $form = $(this);
					var form_data = new FormData();
					var action = $(this).attr('action') || '/';

					var content = $(this).serializeArray(); // + '&ajax=1';
					$.each(content, function (key, input) {
						form_data.append(input.name, input.value);
					});

					// File uploads
					var file = $form.find('input[type="file"]');
					$.each($(file), function (key, input) {
						form_data.append('files[' + $(this).attr('name') + ']', input.files[0]);
						//$.each( input.files, function( key_detail, file ) {
						//    console.log( ' - found file detail ' + key_detail );
						//    console.log( file );
						//    form_data.append( 'files[' + key_detail + ']', file );
						//} )
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
									$(key).replaceWith(value);
								});
							}

							if (json.html) { // HTML

								if (typeof args.html === 'function') {
									args.html.call(this, json.html);
								}
							}

							if (json.redirect) { // Redirect
								window.location.replace(json.redirect);
							} else {
								if (json.errors) { // Errors

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
							//$( 'html' ).removeClass( 'wait' );
							methods.remove_loader($(this));
						}
					});
				}
			});
		});
	};

})(jQuery);
