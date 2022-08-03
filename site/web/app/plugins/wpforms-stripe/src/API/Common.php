<?php

namespace WPFormsStripe\API;

use WPFormsStripe\Helpers;

/**
 * Common methods for every Stripe API implementation.
 *
 * @since 2.3.0
 */
abstract class Common {

	/**
	 * API configuration.
	 *
	 * @since 2.3.0
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * Stripe customer object.
	 *
	 * @since 2.3.0
	 *
	 * @var \Stripe\Customer
	 */
	protected $customer;

	/**
	 * Stripe subscription object.
	 *
	 * @since 2.3.0
	 *
	 * @var \Stripe\Subscription
	 */
	protected $subscription;

	/**
	 * API error message.
	 *
	 * @since 2.3.0
	 *
	 * @var string
	 */
	protected $error;

	/**
	 * API exception.
	 *
	 * @since 2.5.0
	 *
	 * @var \Exception
	 */
	protected $exception;

	/**
	 * Get class variable value or its key.
	 *
	 * @since 2.3.0
	 *
	 * @param string $field Name of the variable to retrieve.
	 * @param string $key   Name of the key to retrieve.
	 *
	 * @return mixed
	 */
	protected function get_var( $field, $key = '' ) {

		$var = isset( $this->{$field} ) ? $this->{$field} : null;

		if ( $key && \is_object( $var ) ) {
			return isset( $var->{$key} ) ? $var->{$key} : null;
		}

		if ( $key && \is_array( $var ) ) {
			return isset( $var[ $key ] ) ? $var[ $key ] : null;
		}

		return $var;
	}

	/**
	 * Get API configuration array or its key.
	 *
	 * @since 2.3.0
	 *
	 * @param string $key Name of the key to retrieve.
	 *
	 * @return mixed
	 */
	public function get_config( $key = '' ) {

		return $this->get_var( 'config', $key );
	}

	/**
	 * Get saved Stripe customer object or its key.
	 *
	 * @since 2.3.0
	 *
	 * @param string $key Name of the key to retrieve.
	 *
	 * @return mixed
	 */
	public function get_customer( $key = '' ) {

		return $this->get_var( 'customer', $key );
	}

	/**
	 * Get saved Stripe subscription object or its key.
	 *
	 * @since 2.3.0
	 *
	 * @param string $key Name of the key to retrieve.
	 *
	 * @return mixed
	 */
	public function get_subscription( $key = '' ) {

		return $this->get_var( 'subscription', $key );
	}

	/**
	 * Get API error message.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	public function get_error() {

		return $this->get_var( 'error' );
	}

	/**
	 * Get API exception.
	 *
	 * @since 2.5.0
	 *
	 * @return \Exception
	 */
	public function get_exception() {

		return $this->get_var( 'exception' );
	}

	/**
	 * Initial Stripe app configuration.
	 *
	 * @since 2.3.0
	 */
	public function setup_stripe() {

		\Stripe\Stripe::setAppInfo(
			'WPForms acct_17Xt6qIdtRxnENqV',
			\WPFORMS_STRIPE_VERSION,
			'https://wpforms.com/addons/stripe-addon/',
			'pp_partner_Dw7IkUZbIlCrtq'
		);
	}

	/**
	 * Set a customer object.
	 * Check if a customer exists in Stripe, if not creates one.
	 *
	 * @since 2.3.0
	 *
	 * @param string $email Email to fetch an existing customer.
	 */
	protected function set_customer( $email ) {

		try {
			$customers = \Stripe\Customer::all(
				array( 'email' => $email ),
				Helpers::get_auth_opts()
			);
		} catch ( \Exception $e ) {
			$customers = null;
		}

		if ( isset( $customers->data[0]->id ) ) {
			$this->customer = $customers->data[0];
			return;
		}

		try {
			$customer = \Stripe\Customer::create(
				array( 'email' => $email ),
				Helpers::get_auth_opts()
			);
		} catch ( \Exception $e ) {
			$customer = null;
		}

		if ( isset( $customer->id ) ) {
			$this->customer = $customer;
		}
	}

	/**
	 * Set an error message from a Stripe API exception.
	 *
	 * @since 2.3.0
	 *
	 * @param \Exception|\Stripe\Exception\ApiErrorException $e Stripe API exception to process.
	 */
	protected function set_error_from_exception( $e ) {

		\do_action( 'wpformsstripe_api_common_set_error_from_exception', $e );

		if ( \is_a( $e, '\Stripe\Exception\CardException' ) ) {
			$body        = $e->getJsonBody();
			$this->error = $body['error']['message'];
			return;
		}

		$errors = array(
			'\Stripe\Exception\RateLimitException'      => \esc_html__( 'Too many requests made to the API too quickly.', 'wpforms-stripe' ),
			'\Stripe\Exception\InvalidRequestException' => \esc_html__( 'Invalid parameters were supplied to Stripe API.', 'wpforms-stripe' ),
			'\Stripe\Exception\AuthenticationException' => \esc_html__( 'Authentication with Stripe API failed.', 'wpforms-stripe' ),
			'\Stripe\Exception\ApiConnectionException'  => \esc_html__( 'Network communication with Stripe failed.', 'wpforms-stripe' ),
			'\Stripe\Exception\ApiErrorException'       => \esc_html__( 'Unable to process Stripe payment.', 'wpforms-stripe' ),
			'\Exception'                                => \esc_html__( 'Unable to process payment.', 'wpforms-stripe' ),
		);

		foreach ( $errors as $error_type => $error_message ) {

			if ( \is_a( $e, $error_type ) ) {
				$this->error = $error_message;

				return;
			}
		}
	}

	/**
	 * Set an exception from a Stripe API exception.
	 *
	 * @since 2.5.0
	 *
	 * @param \Exception $e Stripe API exception to process.
	 */
	protected function set_exception( $e ) {

		$this->exception = $e;
	}

	/**
	 * Handle Stripe API exception.
	 *
	 * @since 2.5.0
	 *
	 * @param \Exception $e Stripe API exception to process.
	 */
	protected function handle_exception( $e ) {

		$this->set_exception( $e );
		$this->set_error_from_exception( $e );
	}

	/**
	 * Get data for every subscription period.
	 *
	 * @since 2.3.0
	 *
	 * @return array
	 */
	protected function get_subscription_period_data() {

		return array(
			'daily'      => array(
				'name'     => 'daily',
				'interval' => 'day',
				'count'    => 1,
				'desc'     => \esc_html__( 'Daily', 'wpforms-stripe' ),
			),
			'weekly'     => array(
				'name'     => 'weekly',
				'interval' => 'week',
				'count'    => 1,
				'desc'     => \esc_html__( 'Weekly', 'wpforms-stripe' ),
			),
			'monthly'    => array(
				'name'     => 'monthly',
				'interval' => 'month',
				'count'    => 1,
				'desc'     => \esc_html__( 'Monthly', 'wpforms-stripe' ),
			),
			'quarterly'  => array(
				'name'     => 'quarterly',
				'interval' => 'month',
				'count'    => 3,
				'desc'     => \esc_html__( 'Quarterly', 'wpforms-stripe' ),
			),
			'semiyearly' => array(
				'name'     => 'semiyearly',
				'interval' => 'month',
				'count'    => 6,
				'desc'     => \esc_html__( 'Semi-Yearly', 'wpforms-stripe' ),
			),
			'yearly'     => array(
				'name'     => 'yearly',
				'interval' => 'year',
				'count'    => 1,
				'desc'     => \esc_html__( 'Yearly', 'wpforms-stripe' ),
			),
		);
	}

	/**
	 * Create Stripe plan.
	 *
	 * @since 2.3.0
	 *
	 * @param string $id     ID of a plan to create.
	 * @param array  $period Subscription period data.
	 * @param array  $args   Additional arguments.
	 *
	 * @return \Stripe\Plan|null
	 */
	protected function create_plan( $id, $period, $args ) {

		$name = \sprintf(
			'%s (%s %s)',
			! empty( $args['settings']['name'] ) ? $args['settings']['name'] : $args['form_title'],
			$args['amount'],
			$period['desc']
		);

		$plan_args = array(
			'amount'         => $args['amount'] * 100,
			'interval'       => $period['interval'],
			'interval_count' => $period['count'],
			'product'        => array(
				'name' => \sanitize_text_field( $name ),
			),
			'nickname'       => \sanitize_text_field( $name ),
			'currency'       => strtolower( wpforms_get_currency() ),
			'id'             => $id,
			'metadata'       => array(
				'form_name' => \sanitize_text_field( $args['form_title'] ),
				'form_id'   => $args['form_id'],
			),
		);

		try {
			$plan = \Stripe\Plan::create( $plan_args, Helpers::get_auth_opts() );
		} catch ( \Exception $e ) {
			$plan = null;
		}

		return $plan;
	}

	/**
	 * Get Stripe plan ID.
	 * Check if a plan exists in Stripe, if not creates one.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Arguments needed for getting a valid plan ID.
	 *
	 * @return string
	 */
	protected function get_plan_id( $args ) {

		$period_data = $this->get_subscription_period_data();

		$period = \array_key_exists( $args['settings']['period'], $period_data ) ? $period_data[ $args['settings']['period'] ] : $period_data['yearly'];

		if ( ! empty( $args['settings']['name'] ) ) {
			$slug = \preg_replace( '/[^a-z0-9\-]/', '', \strtolower( \str_replace( ' ', '-', $args['settings']['name'] ) ) );
		} else {
			$slug = 'form' . $args['form_id'];
		}

		$plan_id = \sprintf(
			'%s_%s_%s',
			$slug,
			$args['amount'] * 100,
			$period['name']
		);

		try {
			$plan = \Stripe\Plan::retrieve( $plan_id, Helpers::get_auth_opts() );
		} catch ( \Exception $e ) {
			$plan = $this->create_plan( $plan_id, $period, $args );
		}

		return isset( $plan->id ) ? $plan->id : '';
	}
}
