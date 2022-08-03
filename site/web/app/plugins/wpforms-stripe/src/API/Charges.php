<?php

namespace WPFormsStripe\API;

use WPFormsStripe\Helpers;

/**
 * Stripe Charges API.
 *
 * @since 2.3.0
 */
class Charges extends Common implements ApiInterface {

	/**
	 * Stripe token received from Stripe.js.
	 *
	 * @since 2.3.0
	 *
	 * @var string
	 */
	protected $token;

	/**
	 * Stripe Charge object.
	 *
	 * @since 2.3.0
	 *
	 * @var \Stripe\Charge
	 */
	protected $charge;

	/**
	 * Initialize.
	 *
	 * @since 2.3.0
	 *
	 * @return \WPFormsStripe\API\Charges
	 */
	public function init() {

		$this->set_config();

		\add_filter( 'wpforms_field_credit_card_enable', '__return_true' );

		return $this;
	}

	/**
	 * Set API configuration.
	 *
	 * @since 2.3.0
	 */
	public function set_config() {

		$min = \wpforms_get_min_suffix();

		$this->config = array(
			'remote_js_url'   => 'https://js.stripe.com/v2/',
			'local_js_url'    => \wpforms_stripe()->url . "assets/js/wpforms-stripe{$min}.js",
			'field_slug'      => 'credit-card',
			'localize_script' => array(),
		);
	}

	/**
	 * Set payment tokens from a submitted form data.
	 *
	 * @since 2.3.0
	 *
	 * @param array $entry Copy of original $_POST.
	 */
	public function set_payment_tokens( $entry ) {

		if ( empty( $entry['stripeToken'] ) ) {
			$this->error = \esc_html__( 'Stripe payment stopped, missing token.', 'wpforms-stripe' );
		} else {
			$this->token = $entry['stripeToken'];
		}
	}

	/**
	 * Process single payment.
	 *
	 * @since 2.3.0
	 *
	 * @param array $args Single payment arguments.
	 */
	public function process_single( $args ) {

		if ( empty( $this->token ) ) {
			$this->error = \esc_html__( 'Stripe payment stopped, missing token.', 'wpforms-stripe' );
			return;
		}

		$defaults = array(
			'source' => $this->token,
		);

		$args = \wp_parse_args( $args, $defaults );

		try {
			$this->charge = \Stripe\Charge::create( $args, Helpers::get_auth_opts() );
		} catch ( \Exception $e ) {
			$this->handle_exception( $e );
		}
	}

	/**
	 * Process subscription.
	 *
	 * @since 2.3.0
	 *
	 * @param array $args Subscription arguments.
	 */
	public function process_subscription( $args ) {

		if ( empty( $this->token ) ) {
			$this->error = \esc_html__( 'Stripe payment stopped, missing token.', 'wpforms-stripe' );
			return;
		}

		$sub_args = array(
			'items'    => array(
				array(
					'plan' => $this->get_plan_id( $args ),
				),
			),
			'metadata' => array(
				'form_name' => $args['form_title'],
				'form_id'   => $args['form_id'],
			),
		);

		try {

			$this->set_customer( $args['email'] );
			$sub_args['customer'] = $this->get_customer( 'id' );

			// Attaching a Source to a Customer validates CVC and throws an exception if Source is invalid.
			$new_source = \Stripe\Customer::createSource(
				$this->get_customer( 'id' ),
				array( 'source' => $this->token ),
				Helpers::get_auth_opts()
			);

			// Check whether a default Source needs to be explicitly set.
			$selected_source_id = $this->select_subscription_default_source( $new_source );

			if ( $selected_source_id ) {
				// Explicitly set a Source for this Subscription because default Customer's Source cannot be used.
				$sub_args['default_source'] = $selected_source_id;
			}

			// Create the subscription.
			$this->subscription = \Stripe\Subscription::create( $sub_args, Helpers::get_auth_opts() );

			// Reference invoice to get the charge object.
			$invoice = \Stripe\Invoice::all(
				array(
					'limit'        => 1,
					'subscription' => $this->subscription->id,
					'expand'       => array( 'data.charge' ),
				),
				Helpers::get_auth_opts()
			);

			$this->charge = $invoice->data[0]->charge;

		} catch ( \Exception $e ) {

			if ( \is_a( $e, '\Stripe\Exception\CardException' ) ) {
				$body = $e->getJsonBody();
				// Cleanup if the card was added but requires user action unsupported by legacy integration.
				if ( 'subscription_payment_intent_requires_action' === $body['error']['code'] ) {
					\Stripe\Customer::deleteSource(
						$this->get_customer( 'id' ),
						$new_source->id,
						Helpers::get_auth_opts()
					);
				}
			}

			$this->handle_exception( $e );
		}

	}

	/**
	 * Get saved Stripe payment object or its key.
	 *
	 * @since 2.3.0
	 *
	 * @param string $key Name of the key to retrieve.
	 *
	 * @return mixed
	 */
	public function get_payment( $key = '' ) {

		return $this->get_var( 'charge', $key );
	}

	/**
	 * Get details from a saved Charge object.
	 *
	 * @since 2.3.0
	 *
	 * @param string|array $keys Key or an array of keys to retrieve.
	 *
	 * @return array
	 */
	public function get_charge_details( $keys ) {

		$source = $this->get_payment( 'source' );

		if ( empty( $source ) || empty( $keys ) ) {
			return array();
		}

		if ( \is_string( $keys ) ) {
			$keys = array( $keys );
		}

		$result = array();

		foreach ( $keys as $key ) {
			if ( isset( $source->{$key} ) ) {
				$result[ $key ] = \sanitize_text_field( $source->{$key} );
			}
		}

		return $result;
	}

	/**
	 * Select 'default_source' for Subscription if it needs to be explicitly set
	 * and cleanup remote Sources in the process.
	 *
	 * @since 2.3.0
	 *
	 * @param \Stripe\Source $new_source Source object.
	 *
	 * @return string
	 *
	 * @throws \Exception In case of Stripe API error.
	 */
	protected function select_subscription_default_source( $new_source ) {

		if ( empty( $this->customer->default_source ) ) {
			return '';
		}

		$default_source = \Stripe\Customer::retrieveSource(
			$this->get_customer( 'id' ),
			$this->customer->default_source,
			Helpers::get_auth_opts()
		);

		// Update Customer's 'default_source' with a new Source if it has the same fingerprint.
		if ( $new_source->fingerprint === $default_source->fingerprint ) {
			$this->update_remote_customer_default_source( $new_source->id );
			$this->delete_remote_customer_source( $default_source->id );
			// In this case Subscription's 'default_source' doesn't have to be explicitly set and defaults to Customer's 'default_source'.
			return '';
		}

		// In case Customer's 'default_source' is set and its fingerprint doesn't match with a new Source, several things need to be done:
		// - Scan all active subscriptions for 'default_source' with a same fingerprint as a new Source.
		// - Change all matching subscriptions 'default_source' to a new Source.
		// - Delete all Sources previously set as 'default_source' for matching subscriptions.
		$this->delete_remote_subscriptions_duplicated_sources( $new_source );

		// In this case Subscription's 'default_source' has to be explicitly set
		// because Customer's 'default_source' contains a different Source and cannot be defaulted to.
		return $new_source->id;
	}

	/**
	 * Update 'default_source' for a Customer stored on a Stripe side.
	 *
	 * @since 2.3.0
	 *
	 * @param string $source_id Source id.
	 *
	 * @throws \Exception If a Customer fails to update.
	 */
	protected function update_remote_customer_default_source( $source_id ) {

		\Stripe\Customer::update(
			$this->get_customer( 'id' ),
			array(
				'default_source' => $source_id,
			),
			Helpers::get_auth_opts()
		);
	}

	/**
	 * Delete 'default_source' for a Customer stored on a Stripe side.
	 *
	 * @since 2.3.0
	 *
	 * @param string $source_id Source id.
	 *
	 * @throws \Exception If a Source fails to delete.
	 */
	protected function delete_remote_customer_source( $source_id ) {

		\Stripe\Customer::deleteSource(
			$this->get_customer( 'id' ),
			$source_id,
			Helpers::get_auth_opts()
		);
	}

	/**
	 * Delete all active Subscriptions Sources having the same fingerprint as a given Source.
	 *
	 * @since 2.3.0
	 *
	 * @param \Stripe\Source $new_source Source object.
	 *
	 * @throws \Exception In case of Stripe API error.
	 */
	protected function delete_remote_subscriptions_duplicated_sources( $new_source ) {

		$subscriptions = \Stripe\Subscription::all(
			array(
				'customer' => $this->get_customer( 'id' ),
				'status'   => 'active',
				'limit'    => 100, // Maximum limit allowed by Stripe (https://stripe.com/docs/api/subscriptions/list#list_subscriptions-limit).
				'expand'   => array( 'data.default_source' ),
			),
			Helpers::get_auth_opts()
		);

		$delete_sources = array();

		foreach ( $subscriptions as $subscription ) {

			if ( empty( $subscription->default_source ) ) {
				continue;
			}

			if ( $new_source->fingerprint === $subscription->default_source->fingerprint ) {

				\Stripe\Subscription::update(
					$subscription->id,
					array( 'default_source' => $new_source->id ),
					Helpers::get_auth_opts()
				);

				$delete_sources[ $subscription->default_source->id ] = $subscription->default_source;
			}
		}

		foreach ( $delete_sources as $delete_source ) {
			$this->delete_remote_customer_source( $delete_source->id );
		}
	}
}
