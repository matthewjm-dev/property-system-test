/*!
	AJAX Button v1.0
	Info: Processes buttons through AJAX then calls relevant Success / Failure function
	Author: Matthew Murray ( admin@matdragon.com )
*/

( function( $ ) {
	'use strict';

	$.fn.ipsAjaxButton = function( args ) {

		return this.each( function() {

			var methods = {

				'add_error': function( $form, target, message ) {
					var $field = $( '#' + target );

					if ( $field.length ) {
						var $fieldset = $field.parents( 'fieldset' );
						var content = '<p class="message">' + message + '</p>';

						$fieldset.addClass( 'error' ).append( content );
					} else {
						var content = '<p class="error message">' + message + '</p>';

						methods.add_message( $form, content );
					}
				},

				'add_message': function( $form, content ) {
					$form.prepend( content );
				},

				'remove_error': function( $target ) {
					$target.removeClass( 'error' ).find( '.message' ).remove();
				},

				'clear_form': function( $form ) {
					$form.find( '.message' ).remove();
					$form.find( '.error' ).removeClass( 'error' );
				},

				'watch_errors': function( $form ) {
					$form.on( 'click', 'input, focus, select, textarea', function() {
						var $fieldset = $( this ).parents( 'fieldset' );
						if ( $fieldset.hasClass( 'error') ) {
							methods.remove_error( $fieldset );
						}
					} );
				},

				'add_loader': function( $form ) {
					$form.prepend( '<div class="loader active"><div class="loader-icon"></div></div>' );
				},

				'remove_loader': function( $form ) {
					$form.find( '.loader' ).remove();
				}
			};

			$( this ).on( 'submit', function( e ) {

				e.preventDefault();
				methods.add_loader( $( this ) );

				if ( typeof args.before === 'function' ) {
					args.before.call( this );
				}

				var action = $( this ).data( 'action' ) || '/';
				var data = $( this ).data( 'data' ) || '/';

				$.ajax( {
					url: action,
					method: 'POST',
					type: 'POST',
					dataType: 'json',
					contentType: false,
					processData: false,
					data: data,
					context: this,
					success: function( json ) {

						if ( json.fragments ) { // Fragments

							$.each( json.fragments, function( key, value ) {
								$( key ).replaceWith( value );
							} );
						}

						if ( json.html ) { // HTML

							if ( typeof args.html === 'function' ) {
								args.html.call( this, json.html );
							}
						}

						if ( json.redirect ) { // Redirect
							window.location.replace( json.redirect );
						} else {
							if ( json.errors ) { // Errors

								// Call Failure function
								if ( typeof args.failure_override === 'function' ) {
									args.failure_override.call( this, json.errors );
									args.failure.call( this, json.errors );
								} else {
									if ( typeof args.failure === 'function' ) {
										args.failure.call( this, json.errors );
									}

									methods.clear_form( $form );

									$.each( json.errors, function( input, message ) {
										methods.add_error( $form, input, message );
									} );

									methods.watch_errors( $form );
								}

							} else if ( json.success ) { // Success

								// Call Success function
								if ( typeof args.success_override === 'function' ) {
									args.success_override.call( this, json.success );

									methods.clear_form( $form );
								} else {
									if ( typeof args.success === 'function' ) {
										args.success.call( this, json.success );
									}

									methods.clear_form( $form );

									if ( json.success !== 'true' ) {
										var content = '<p class="success message">' + json.success + '</p>';
										methods.add_message( $form, content );
									}
								}
							}
						}
					},
					complete: function() {
						methods.remove_loader( $( this ) );
					}
				} );
			} );
		} );
	};

} )( jQuery );
