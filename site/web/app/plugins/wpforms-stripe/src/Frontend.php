<?php

namespace WPFormsStripe;

/**
 * Stripe form frontend related functionality.
 *
 * @since 2.0.0
 */
class Frontend {

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->init();
	}

	/**
	 * Initialize.
	 *
	 * @since 2.0.0
	 */
	public function init() {

		\add_action( 'wpforms_frontend_container_class', array( $this, 'form_container_class' ), 10, 2 );
		\add_action( 'wpforms_wp_footer', array( $this, 'enqueues' ) );
	}

	/**
	 * Add class to form container if Stripe is enabled.
	 *
	 * @since 2.0.0
	 *
	 * @param array $class     Array of form classes.
	 * @param array $form_data Form data of current form.
	 *
	 * @return array
	 */
	public function form_container_class( $class, $form_data ) {

		if ( ! Helpers::has_stripe_field( $form_data ) ) {
			return $class;
		}

		if ( ! Helpers::has_stripe_keys() ) {
			return $class;
		}

		if ( ! empty( $form_data['payments']['stripe']['enable'] ) ) {
			$class[] = 'wpforms-stripe';
		}

		return $class;
	}

	/**
	 * Enqueue assets in the frontend if Stripe is in use on the page.
	 *
	 * @since 2.0.0
	 *
	 * @param array $forms Form data of forms on current page.
	 */
	public function enqueues( $forms ) {

		if ( ! Helpers::has_stripe_field( $forms, true ) ) {
			return;
		}

		if ( ! Helpers::has_stripe_enabled( $forms ) ) {
			return;
		}

		if ( ! Helpers::has_stripe_keys() ) {
			return;
		}

		$config = \wpforms_stripe()->api->get_config();

		\wp_enqueue_script(
			'stripe-js',
			$config['remote_js_url'],
			array( 'jquery' )
		);

		\wp_enqueue_script(
			'wpforms-stripe',
			$config['local_js_url'],
			array( 'jquery', 'stripe-js' ),
			\WPFORMS_STRIPE_VERSION
		);

		\wp_localize_script(
			'wpforms-stripe',
			'wpforms_stripe',
			array(
				'publishable_key' => Helpers::get_stripe_key( 'publishable' ),
				'data'            => $config['localize_script'],
			)
		);
	}
}
