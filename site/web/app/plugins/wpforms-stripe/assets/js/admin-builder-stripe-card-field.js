/* global wpforms_builder, wpforms_builder_stripe_card_field */

/**
 * WPForms Stripe Card Field function.
 *
 * @since 2.3.0
*/
'use strict';

var WPFormsStripeCardField = window.WPFormsStripeCardField || ( function( document, window, $ ) {

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

			app.bindUIActions();
		},

		/**
		 * Process various events as a response to UI interactions.
		 *
		 * @since 2.3.0
		 */
		bindUIActions: function() {

			$( document ).on( 'wpformsSaved', app.ajaxRequiredCheck );
			$( document ).on( 'wpformsSaved', app.paymentsEnabledCheck );

			$( document ).on( 'click', '#wpforms-add-fields-' + wpforms_builder_stripe_card_field.field_slug, app.stripeKeysCheck );

			$( document ).on( 'wpformsFieldAdd', app.disableAddCardButton );
			$( document ).on( 'wpformsFieldDelete', app.enableAddCardButton );
		},

		/**
		 * On form save notify users if AJAX submission is required.
		 *
		 * @since 2.3.0
		 */
		ajaxRequiredCheck: function() {

			if ( ! $( '.wpforms-field.wpforms-field-' + wpforms_builder_stripe_card_field.field_slug ).length ||
				$( '#wpforms-panel-field-settings-ajax_submit' ).is( ':checked' ) ) {
				return;
			}

			$.alert( {
				title: wpforms_builder.heads_up,
				content: wpforms_builder.stripe_ajax_required,
				icon: 'fa fa-exclamation-circle',
				type: 'orange',
				buttons: {
					confirm: {
						text: wpforms_builder.ok,
						btnClass: 'btn-confirm',
						keys: [ 'enter' ],
					},
				},
			} );
		},

		/**
		 * On form save notify users if Stripe payments are not enabled.
		 *
		 * @since 2.3.0
		 */
		paymentsEnabledCheck: function() {

			if ( ! $( '.wpforms-field.wpforms-field-' + wpforms_builder_stripe_card_field.field_slug ).length ||
				$( '#wpforms-panel-field-stripe-enable' ).is( ':checked' ) ) {
				return;
			}

			$.alert( {
				title: wpforms_builder.heads_up,
				content: wpforms_builder.payments_enabled_required,
				icon: 'fa fa-exclamation-circle',
				type: 'orange',
				buttons: {
					confirm: {
						text: wpforms_builder.ok,
						btnClass: 'btn-confirm',
						keys: [ 'enter' ],
					},
				},
			} );
		},

		/**
		 * On adding Stripe Credit Card field notify users if Stripe keys are missing.
		 *
		 * @since 2.3.0
		 */
		stripeKeysCheck: function() {

			if ( ! $( this ).hasClass( 'stripe-keys-required' ) ) {
				return;
			}

			$.alert( {
				title: wpforms_builder.heads_up,
				content: wpforms_builder.stripe_keys_required,
				icon: 'fa fa-exclamation-circle',
				type: 'orange',
				buttons: {
					confirm: {
						text: wpforms_builder.ok,
						btnClass: 'btn-confirm',
						keys: [ 'enter' ],
					},
				},
			} );
		},

		/**
		 * Disable "Add Card" button in the fields list.
		 *
		 * @since 2.3.0
		 *
		 * @param {object} e Event object.
		 * @param {number} id Field ID.
		 * @param {string} type Field type.
		 */
		disableAddCardButton: function( e, id, type ) {

			if ( wpforms_builder_stripe_card_field.field_slug === type ) {
				$( '#wpforms-add-fields-' + wpforms_builder_stripe_card_field.field_slug )
					.prop( 'disabled', true );
			}
		},

		/**
		 * Enable "Add Card" button in the fields list.
		 *
		 * @since 2.3.0
		 *
		 * @param {object} e Event object.
		 * @param {number} id Field ID.
		 * @param {string} type Field type.
		 */
		enableAddCardButton: function( e, id, type ) {

			if ( wpforms_builder_stripe_card_field.field_slug === type ) {
				$( '#wpforms-add-fields-' + wpforms_builder_stripe_card_field.field_slug )
					.prop( 'disabled', false );
			}
		},
	};

	// Provide access to public functions/properties.
	return app;

}( document, window, jQuery ) );

// Initialize.
WPFormsStripeCardField.init();
