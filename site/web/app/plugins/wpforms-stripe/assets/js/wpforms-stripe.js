/* global Stripe, wpforms_stripe */

/**
 * WPForms Stripe function.
 *
 * @since 1.2.0
*/
'use strict';

var WPFormsStripe = window.WPFormsStripe || ( function( document, window, $ ) {

	/**
	 * Public functions and properties.
	 *
	 * @since 1.2.0
	 *
	 * @type {Object}
	 */
	var app = {

		/**
		 * Start the engine.
		 *
		 * @since 1.2.0
		 */
		init: function() {

			$( document ).on( 'wpformsReady', app.submitHandler );
		},

		/**
		 * Update submitHandler for forms containing Stripe.
		 *
		 * @since 1.2.0
		 */
		submitHandler: function() {

			$( '.wpforms-stripe form' ).each( function() {

				var formSettings      = $( this ).validate().settings,
					formSubmitHandler = formSettings.submitHandler;

				// Replace the default submit handler.
				formSettings.submitHandler = function( form ) {

					var $form     = $( form ),
						$ccName   = $form.find( '.wpforms-field-credit-card-cardname' ),
						$ccCVC    = $form.find( '.wpforms-field-credit-card-cardcvc' ),
						$ccNumber = $form.find( '.wpforms-field-credit-card-cardnumber' ),
						$ccMonth  = $form.find( '.wpforms-field-credit-card-cardmonth' ),
						$ccYear   = $form.find( '.wpforms-field-credit-card-cardyear' ),
						valid     = $form.validate().form();

					if ( valid && $ccNumber.val().length > 0 ) {

						// Only charge if there is a credit card number provided.
						$form.find( '.wpforms-submit' ).prop( 'disabled', true );

						// Form is valid and there is credit card number, so
						// proceed with Stripe API processing.
						Stripe.setPublishableKey( wpforms_stripe.publishable_key );

						Stripe.card.createToken( {
							number:    $ccNumber.val(),
							name:      $ccName.val(),
							cvc:       $ccCVC.val(),
							exp_month: $ccMonth.val(),
							exp_year:  $ccYear.val(),
						}, function( status, response ) {
							if ( response.error ) {
								$form.find( '.wpforms-submit' ).prop( 'disabled', false );
								var $cont = $form.find( '.wpforms-submit-container' );
								$cont.siblings( '.wpforms-error-stripe' ).remove();
								$cont.before( '<div class="wpforms-error-alert wpforms-error-stripe">' + response.error.message + '</div>' );
								$form.validate().cancelSubmit = true;
							} else {
								$form.append( '<input type="hidden" name="wpforms[stripeToken]" value="' + response.id + '">' );
								return formSubmitHandler( form );
							}
						} );

					} else if ( valid ) {

						// Form is valid, however no credit card to process.
						$form.find( '.wpforms-submit' ).prop( 'disabled', false );
						return formSubmitHandler( form );

					} else {

						// Form is not valid.
						$form.find( '.wpforms-submit' ).prop( 'disabled', false );
						$form.validate().cancelSubmit = true;

					}
				};
			} );
		},
	};

	// Provide access to public functions/properties.
	return app;

}( document, window, jQuery ) );

// Initialize.
WPFormsStripe.init();
