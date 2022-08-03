<?php

namespace WPFormsStripe;

/**
 * Stripe related helper methods.
 *
 * @since 2.0.0
 */
class Helpers {

	/**
	 * Check if Stripe keys have been configured in the plugin settings.
	 *
	 * @since 2.0.0
	 *
	 * @param string $mode Stripe mode to check the keys for.
	 *
	 * @return bool
	 */
	public static function has_stripe_keys( $mode = '' ) {

		$mode = self::validate_stripe_mode( $mode );

		return \wpforms_setting( "stripe-{$mode}-secret-key", false ) && \wpforms_setting( "stripe-{$mode}-publishable-key", false );
	}

	/**
	 * Check if Stripe is in use on the page.
	 *
	 * @since 2.3.0
	 *
	 * @param array $forms Form data (e.g. forms on a current page).
	 */
	public static function has_stripe_enabled( $forms ) {

		$stripe_enabled = false;

		foreach ( $forms as $form ) {
			if ( ! empty( $form['payments']['stripe']['enable'] ) ) {
				$stripe_enabled = true;
				break;
			}
		}

		return $stripe_enabled;
	}

	/**
	 * Check if Stripe field is in the form.
	 *
	 * @since 2.3.0
	 *
	 * @param array $forms    Form data (e.g. forms on a current page).
	 * @param bool  $multiple Must be 'true' if $forms contain multiple forms.
	 *
	 * @return bool
	 */
	public static function has_stripe_field( $forms, $multiple = false ) {

		$slug = \wpforms_stripe()->api->get_config( 'field_slug' );

		if ( empty( $slug ) ) {
			return false;
		}

		return ( false !== \wpforms_has_field_type( $slug, $forms, $multiple ) );
	}

	/**
	 * Get API version.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	public static function get_api_version() {

		return \apply_filters( 'wpforms_stripe_helpers_get_api_class_api_version', \wpforms_setting( 'stripe-api-version' ) );
	}

	/**
	 * Get API classes list.
	 *
	 * @since 2.3.0
	 *
	 * @return array
	 */
	public static function get_api_classes() {

		$classes = array(
			2 => '\WPFormsStripe\API\Charges',
			3 => '\WPFormsStripe\API\PaymentIntents',
		);

		return \apply_filters( 'wpforms_stripe_helpers_get_api_classes', $classes );
	}

	/**
	 * Get API classes configuration arrays or just the specific keys.
	 *
	 * @since 2.3.0
	 *
	 * @param string $key Name of the key to retrieve.
	 *
	 * @return array
	 */
	public static function get_api_classes_config( $key = '' ) {

		$api_classes = self::get_api_classes();
		$configs     = array();

		foreach ( $api_classes as $api_class ) {

			/**
			 * Instance of API class.
			 *
			 * @var \WPFormsStripe\API\ApiInterface $instance
			 */
			$instance = new $api_class();
			$instance->set_config();

			$configs[ $api_class ] = $instance->get_config( $key );
		}

		return $configs;
	}

	/**
	 * Get API class object.
	 *
	 * @since 2.3.0
	 *
	 * @return API\ApiInterface
	 */
	public static function get_api_class() {

		$api_version = self::get_api_version();
		$api_classes = self::get_api_classes();

		if ( \array_key_exists( \absint( $api_version ), $api_classes ) ) {
			$class = new $api_classes[ $api_version ]();
		}

		if ( empty( $class ) ) {
			$class = new API\PaymentIntents();
		}

		return \apply_filters( 'wpforms_stripe_helpers_get_api_class', $class );
	}

	/**
	 * Get Stripe mode from WPForms settings.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	public static function get_stripe_mode() {

		return \wpforms_setting( 'stripe-test-mode' ) ? 'test' : 'live';
	}

	/**
	 * Validate Stripe mode name to ensure it's either 'live' or 'test'.
	 * If given mode is invalid, fetches current Stripe mode.
	 *
	 * @since 2.3.0
	 *
	 * @param string $mode Stripe mode to validate.
	 *
	 * @return string
	 */
	public static function validate_stripe_mode( $mode ) {

		if ( empty( $mode ) || ! \in_array( $mode, array( 'live', 'test' ), true ) ) {
			$mode = self::get_stripe_mode();
		}

		return $mode;
	}

	/**
	 * Get Stripe key from WPForms settings.
	 *
	 * @since 2.3.0
	 *
	 * @param string $type Key type (e.g. 'publishable' or 'secret').
	 * @param string $mode Stripe mode (e.g. 'live' or 'test').
	 *
	 * @return string
	 */
	public static function get_stripe_key( $type, $mode = '' ) {

		$mode = self::validate_stripe_mode( $mode );

		if ( \in_array( $type, array( 'publishable', 'secret' ), true ) ) {
			$key = \wpforms_setting( "stripe-{$mode}-{$type}-key" );
			return ( ! empty( $key ) && \is_string( $key ) ) ? \sanitize_text_field( $key ) : '';
		}

		return '';
	}

	/**
	 * Set Stripe key from WPForms settings.
	 *
	 * @since 2.3.0
	 *
	 * @param string $value Key string to set.
	 * @param string $type  Key type (e.g. 'publishable' or 'secret').
	 * @param string $mode  Stripe mode (e.g. 'live' or 'test').
	 *
	 * @return bool
	 */
	public static function set_stripe_key( $value, $type, $mode = '' ) {

		$mode = self::validate_stripe_mode( $mode );

		if ( ! \in_array( $type, array( 'publishable', 'secret' ), true ) ) {
			return false;
		}

		$key              = "stripe-{$mode}-{$type}-key";
		$settings         = \get_option( 'wpforms_settings', array() );
		$settings[ $key ] = \sanitize_text_field( $value );

		return wpforms_update_settings( $settings );
	}

	/**
	 * Get authorization options used for every Stripe transaction as recommended in Stripe official docs.
	 * https://stripe.com/docs/connect/authentication#api-keys
	 *
	 * @since 2.3.0
	 *
	 * @return array
	 */
	public static function get_auth_opts() {

		return array( 'api_key' => self::get_stripe_key( 'secret' ) );
	}

	/**
	 * Get forms using Stripe with a specific payment collection type.
	 *
	 * @param string $type Payment collection type, legacy or elements.
	 *
	 * @return array
	 */
	public static function get_forms_by_payment_collection_type( $type = 'legacy' ) {

		$field_type = 'credit-card';
		$forms      = \wpforms()->form->get();

		if ( 'elements' === $type || 3 === \absint( $type ) ) {
			$field_type = 'stripe-credit-card';
		}

		if ( empty( $forms ) ) {
			return array();
		}

		$payment_forms = array();

		foreach ( $forms as $form ) {

			$form_data = \wpforms_decode( $form->post_content );

			if ( empty( $form_data['payments']['stripe']['enable'] ) ) {
				continue;
			}

			if ( \wpforms_has_field_type( $field_type, $form_data ) ) {
				$payment_forms[] = $form;
			}
		}

		return $payment_forms;
	}
}
