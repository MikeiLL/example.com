/* global wpforms_admin_settings_stripe, wpforms_admin */

'use strict';

/**
 * WPForms Builder Form Pages function.
 *
 * @since 2.3.0
 * @package WPFormsStripe
 */
var WPFormsSettingsStripe = window.WPFormsSettingsStripe || ( function( document, window, $ ) {

	/**
	 * Elements.
	 *
	 * @since 2.3.0
	 *
	 * @type {Object}
	 */
	var $el = {
		liveConnectionBlock: $( '.wpforms-stripe-connection-status-live' ),
		testConnectionBlock: $( '.wpforms-stripe-connection-status-test' ),
		testModeCheckbox: $( '#wpforms-setting-stripe-test-mode' ),
		paymentCollectionTypeInputs: $( 'input[name=stripe-api-version]' ),
		apiKeyInputs: $( '#wpforms-setting-row-stripe-test-publishable-key, #wpforms-setting-row-stripe-test-secret-key, #wpforms-setting-row-stripe-live-publishable-key, #wpforms-setting-row-stripe-live-secret-key' ),
		apiKeyToggle: $( '#wpforms-setting-row-stripe-connection-status .desc a' ),
	};

	/**
	 * Public functions and properties.
	 *
	 * @since 2.3.0
	 *
	 * @type {Object}
	 */
	var app = {

		/**
		 * Start the engine.
		 *
		 * @since 2.3.0
		 */
		init: function() {

			$( app.ready );
		},

		/**
		 * Document ready.
		 *
		 * @since 2.3.0
		 */
		ready: function() {

			app.events();
		},

		/**
		 * Register JS events.
		 *
		 * @since 2.3.0
		 */
		events: function() {

			$( document ).on( 'change', 'input[name=stripe-api-version]', app.triggerPaymentCollectionAlert );

			$( document ).on( 'change', '#wpforms-setting-stripe-test-mode', app.triggerModeSwitchAlert );

			$el.apiKeyToggle.click( function( event ) {

				event.preventDefault();

				$el.apiKeyInputs.toggle();
			} );
		},

		/**
		 * Conditionally prevent showing the settings panel.
		 *
		 * @since 2.3.0
		 */
		triggerPaymentCollectionAlert: function() {

			var type = parseInt( $( 'input[name=stripe-api-version]:checked' ).val(), 10 );

			// User selected WPForms Credit Card field.
			if ( type === 2 && wpforms_admin_settings_stripe.has_payment_forms_elements ) {

				$.alert( {
					title: wpforms_admin.heads_up,
					content: '<div id="wpforms-stripe-payment-collection-update-modal">' + wpforms_admin_settings_stripe.payment_collection_type_modal_legacy + '</div>',
					backgroundDismiss: false,
					boxWidth: '425px',
					closeIcon: false,
					icon: 'fa fa-exclamation-circle',
					type: 'orange',
					buttons: {
						confirm: {
							text: wpforms_admin.ok,
							btnClass: 'btn-confirm',
							keys: [ 'enter' ],
						},
						cancel: {
							text: wpforms_admin.cancel,
							keys: [ 'esc' ],
							action: function() {
								$el.paymentCollectionTypeInputs.filter( '[value=3]' ).prop( 'checked', true );
							},
						},
					},
				} );
			}

			// User selected Stripe Credit Card.
			if ( type === 3 && wpforms_admin_settings_stripe.has_payment_forms_legacy ) {
				$.alert( {
					title: wpforms_admin.heads_up,
					content: '<div id="wpforms-stripe-payment-collection-update-modal">' + wpforms_admin_settings_stripe.payment_collection_type_modal_elements + '</div>',
					backgroundDismiss: false,
					boxWidth: '425px',
					closeIcon: false,
					icon: 'fa fa-exclamation-circle',
					type: 'orange',
					buttons: {
						confirm: {
							text: wpforms_admin_settings_stripe.payment_collection_type_modal_elements_ok,
							btnClass: 'btn-confirm btn-block btn-normal-case',
							keys: [ 'enter' ],
						},
						cancel: {
							text: wpforms_admin_settings_stripe.payment_collection_type_modal_elements_cancel,
							btnClass: 'btn-block btn-normal-case',
							keys: [ 'esc' ],
							action: function() {
								$el.paymentCollectionTypeInputs.filter( '[value=2]' ).prop( 'checked', true );
							},
						},
					},
				} );
			}
		},

		/**
		 * Conditionally show Stripe mode switch warning.
		 *
		 * @since 2.3.0
		 */
		triggerModeSwitchAlert: function() {

			if ( $el.testModeCheckbox.is( ':checked' ) ) {
				$el.liveConnectionBlock.hide();
				$el.testConnectionBlock.show();
			} else {
				$el.liveConnectionBlock.show();
				$el.testConnectionBlock.hide();
			}

			if ( $( '#wpforms-setting-row-stripe-connection-status .wpforms-connected' ).is( ':visible' ) ) {
				return;
			}

			$.alert( {
				title: wpforms_admin.heads_up,
				content: wpforms_admin_settings_stripe.mode_update,
				icon: 'fa fa-exclamation-circle',
				type: 'orange',
				buttons: {
					confirm: {
						text: wpforms_admin.ok,
						btnClass: 'btn-confirm',
						keys: [ 'enter' ],
					},
				},
			} );
		},
	};

	// Provide access to public functions/properties.
	return app;

}( document, window, jQuery ) );

// Initialize.
WPFormsSettingsStripe.init();
