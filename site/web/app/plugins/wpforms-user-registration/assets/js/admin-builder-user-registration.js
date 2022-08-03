;(function($) {

	var WPFormsUserRegistration = {

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		init: function() {

			WPFormsUserRegistration.bindUIActions();

			$(document).ready(WPFormsUserRegistration.ready);
		},

		/**
		 * Document ready.
		 * 
		 * @since 1.0.0
		 */
		ready: function() {

			// User Activation setting
			WPFormsUserRegistration.activationToggle();

			// User Activation method setting
			WPFormsUserRegistration.activationMethodToggle();
		},
	
		/**
		 * Element bindings.
		 *
		 * @since 1.0.0
		 */
		bindUIActions: function() {
			
			// Toggle user activation setting fields
			$(document).on('change', '#wpforms-panel-field-settings-registration_activation', function(e) {
				WPFormsUserRegistration.activationToggle();
			});

			// Toggle user activation setting fields
			$(document).on('change', '#wpforms-panel-field-settings-registration_activation_method', function(e) {
				WPFormsUserRegistration.activationMethodToggle();
			});
		},

		/**
		 * Toggle the displaying activation method settings depending on if user
		 * activation is enabled.
		 *
		 * @since 1.0.0
		 */
		activationToggle: function() {

			var $activation = $('#wpforms-panel-field-settings-registration_activation'),
				$method     = $('#wpforms-panel-field-settings-registration_activation_method-wrap');

			if ($activation.is(':checked')){
				$method.show();
			} else {
				$method.hide();
			}
		},

		/**
		 * Toggle the displaying activation confirmation page settings depending
		 * on if activation method configured.
		 *
		 * @since 1.0.0
		 */
		activationMethodToggle: function() {

			var $method       = $('#wpforms-panel-field-settings-registration_activation_method'),
				$confirmation = $('#wpforms-panel-field-settings-registration_activation_confirmation-wrap');

			if ($method.find('option:selected').val() == 'user'){
				$confirmation.show();
			} else {
				$confirmation.hide();
			}
		}
	}

	WPFormsUserRegistration.init();
})(jQuery);