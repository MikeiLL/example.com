<?php

namespace WPFormsStripe\API;

use WPFormsStripe\Helpers;

/**
 * Stripe PaymentIntents API.
 *
 * @since 2.3.0
 */
class PaymentIntents extends Common implements ApiInterface {

	/**
	 * Stripe PaymentMethod id received from Elements.
	 *
	 * @since 2.3.0
	 *
	 * @var string
	 */
	protected $payment_method_id;

	/**
	 * Stripe PaymentIntent id received from Elements.
	 *
	 * @since 2.3.0
	 *
	 * @var string
	 */
	protected $payment_intent_id;

	/**
	 * Stripe PaymentIntent object.
	 *
	 * @since 2.3.0
	 *
	 * @var \Stripe\PaymentIntent
	 */
	protected $intent;

	/**
	 * Initialize.
	 *
	 * @since 2.3.0
	 *
	 * @return \WPFormsStripe\API\PaymentIntents
	 */
	public function init() {

		$this->set_config();

		add_filter( 'wpforms_process_bypass_captcha', [ $this, 'bypass_captcha_on_3dsecure_submit' ], 10, 3 );

		new \WPFormsStripe\Fields\StripeCreditCard();

		return $this;
	}

	/**
	 * Set API configuration.
	 *
	 * @since 2.3.0
	 */
	public function set_config() {

		$min = \wpforms_get_min_suffix();

		/**
		 * This filter allows to overwrite a Style object, which consists of CSS properties nested under objects.
		 *
		 * @since 2.5.0
		 *
		 * @link https://stripe.com/docs/js/appendix/style
		 *
		 * @param array [] Style object.
		 */
		$element_style = \apply_filters( 'wpforms_stripe_api_payment_intents_set_config_element_style', [] );

		$localize_script = [
			'element_classes' => [
				'base'           => 'wpforms-stripe-element',
				'complete'       => 'wpforms-stripe-element-complete',
				'empty'          => 'wpforms-stripe-element-empty',
				'focus'          => 'wpforms-stripe-element-focus',
				'invalid'        => 'wpforms-stripe-element-invalid',
				'webkitAutofill' => 'wpforms-stripe-element-webkit-autofill',
			],
			'element_locale'  => $this->filter_config_element_locale(),
			'element_style'   => $element_style,
		];

		$this->config = [
			'remote_js_url'   => 'https://js.stripe.com/v3/',
			'local_js_url'    => \wpforms_stripe()->url . "assets/js/wpforms-stripe-elements{$min}.js",
			'field_slug'      => 'stripe-credit-card',
			'localize_script' => $localize_script,
		];
	}

	/**
	 * Get stripe locale.
	 *
	 * @since 2.4.0
	 *
	 * @return string
	 */
	public function filter_config_element_locale() {

		$locale = apply_filters( 'wpforms_stripe_api_payment_intents_filter_config_element_locale', '' );

		// Stripe Elements makes its own locale validation, but we add a general sanity check.
		return 2 === strlen( $locale ) ? esc_html( $locale ) : 'auto';
	}

	/**
	 * Initial Stripe app configuration.
	 *
	 * @since 2.3.0
	 */
	public function setup_stripe() {

		parent::setup_stripe();

		\Stripe\Stripe::setApiVersion( '2019-05-16' );
	}

	/**
	 * Set payment tokens from a submitted form data.
	 *
	 * @since 2.3.0
	 *
	 * @param array $entry Copy of original $_POST.
	 */
	public function set_payment_tokens( $entry ) {

		if ( ! empty( $entry['payment_method_id'] ) && empty( $entry['payment_intent_id'] ) ) {
			$this->payment_method_id = $entry['payment_method_id'];
		}

		if ( ! empty( $entry['payment_intent_id'] ) ) {
			$this->payment_intent_id = $entry['payment_intent_id'];
		}

		if ( empty( $this->payment_method_id ) && empty( $this->payment_intent_id ) ) {
			$this->error = \esc_html__( 'Stripe payment stopped, missing both PaymentMethod and PaymentIntent ids.', 'wpforms-stripe' );
		}
	}

	/**
	 * Retrieve PaymentIntent object from Stripe.
	 *
	 * @since 2.3.0
	 *
	 * @param string $id   PaymentIntent id.
	 * @param array  $args Additional arguments (e.g. 'expand').
	 *
	 * @return \Stripe\PaymentIntent
	 */
	protected function retrieve_payment_intent( $id, $args = [] ) {

		$defaults = [ 'id' => $id ];

		$args = \wp_parse_args( $args, $defaults );

		return \Stripe\PaymentIntent::retrieve( $args, Helpers::get_auth_opts() );
	}

	/**
	 * Process single payment.
	 *
	 * @since 2.3.0
	 *
	 * @param array $args Single payment arguments.
	 */
	public function process_single( $args ) {

		if ( $this->payment_method_id ) {

			// Normal flow.
			$this->charge_single( $args );

		} elseif ( $this->payment_intent_id ) {

			// 3D Secure flow.
			$this->finalize_single();
		}
	}

	/**
	 * Request a single payment charge to be made by Stripe.
	 *
	 * @since 2.3.0
	 *
	 * @param array $args Single payment arguments.
	 */
	protected function charge_single( $args ) {

		if ( empty( $this->payment_method_id ) ) {
			$this->error = \esc_html__( 'Stripe payment stopped, missing PaymentMethod id.', 'wpforms-stripe' );
			return;
		}

		$defaults = array(
			'payment_method' => $this->payment_method_id,
			'confirm'        => true,
		);

		$args = \wp_parse_args( $args, $defaults );

		try {

			$this->intent = \Stripe\PaymentIntent::create( $args, Helpers::get_auth_opts() );

			if ( ! \in_array( $this->intent->status, array( 'succeeded', 'requires_action' ), true ) ) {
				$this->error = \esc_html__( 'Stripe payment stopped. invalid PaymentIntent status.', 'wpforms-stripe' );
				return;
			}

			if ( 'requires_action' === $this->intent->status ) {
				$this->set_bypass_captcha_3dsecure_token();
				$this->request_3dsecure_ajax( $this->intent );
			}
		} catch ( \Exception $e ) {

			$this->handle_exception( $e );
		}
	}

	/**
	 * Finalize single payment after 3D Secure authorization is finished successfully.
	 *
	 * @since 2.3.0
	 *
	 * @return mixed
	 */
	protected function finalize_single() {

		// Saving payment info is important for a future form entry meta update.
		$this->intent = $this->retrieve_payment_intent( $this->payment_intent_id );

		if ( 'succeeded' !== $this->intent->status ) {

			// This error is unlikely to happen because the same check is done on a frontend.
			$this->error = \esc_html__( 'Stripe payment was not confirmed. Please check your Stripe dashboard.', 'wpforms-stripe' );
			return;
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

		if ( $this->payment_method_id ) {

			// Normal flow.
			$this->charge_subscription( $args );

		} elseif ( $this->payment_intent_id ) {

			// 3D Secure flow.
			$this->finalize_subscription();
		}
	}

	/**
	 * Request a subscription charge to be made by Stripe.
	 *
	 * @since 2.3.0
	 *
	 * @param array $args Single payment arguments.
	 */
	protected function charge_subscription( $args ) {

		if ( empty( $this->payment_method_id ) ) {
			$this->error = \esc_html__( 'Stripe subscription stopped, missing PaymentMethod id.', 'wpforms-stripe' );
			return;
		}

		$sub_args = [
			'items'    => [
				[
					'plan' => $this->get_plan_id( $args ),
				],
			],
			'metadata' => [
				'form_name' => $args['form_title'],
				'form_id'   => $args['form_id'],
			],
			'expand'   => [ 'latest_invoice.payment_intent' ],
		];

		try {

			$this->set_customer( $args['email'] );
			$sub_args['customer'] = $this->get_customer( 'id' );

			$new_payment_method = \Stripe\PaymentMethod::retrieve(
				$this->payment_method_id,
				Helpers::get_auth_opts()
			);

			// Attaching a PaymentMethod to a Customer validates CVC and throws an exception if PaymentMethod is invalid.
			$new_payment_method->attach( array( 'customer' => $this->customer->id ) );

			// Check whether a default PaymentMethod needs to be explicitly set.
			$selected_payment_method_id = $this->select_subscription_default_payment_method( $new_payment_method );

			if ( $selected_payment_method_id ) {
				// Explicitly set a PaymentMethod for this Subscription because default Customer's PaymentMethod cannot be used.
				$sub_args['default_payment_method'] = $selected_payment_method_id;
			}

			// Create the subscription.
			$this->subscription = \Stripe\Subscription::create( $sub_args, Helpers::get_auth_opts() );

			$this->intent = $this->subscription->latest_invoice->payment_intent;

			if ( ! \in_array( $this->intent->status, array( 'succeeded', 'requires_action' ), true ) ) {
				$this->error = \esc_html__( 'Stripe subscription stopped. invalid PaymentIntent status.', 'wpforms-stripe' );
				return;
			}

			if ( 'requires_action' === $this->intent->status ) {
				$this->set_bypass_captcha_3dsecure_token();
				$this->request_3dsecure_ajax( $this->intent );
			}
		} catch ( \Exception $e ) {

			$this->handle_exception( $e );
		}
	}

	/**
	 * Finalize a subscription after 3D Secure authorization is finished successfully.
	 *
	 * @since 2.3.0
	 *
	 * @return mixed
	 */
	protected function finalize_subscription() {

		// Saving payment info is important for a future form entry meta update.
		$this->intent = $this->retrieve_payment_intent( $this->payment_intent_id, array( 'expand' => array( 'invoice.subscription', 'customer' ) ) );

		if ( 'succeeded' !== $this->intent->status ) {

			// This error is unlikely to happen because the same check is done on a frontend.
			$this->error = \esc_html__( 'Stripe subscription was not confirmed. Please check your Stripe dashboard.', 'wpforms-stripe' );
			return;
		}

		// Saving customer and subscription info is important for a future form meta update.
		$this->customer     = $this->intent->customer;
		$this->subscription = $this->intent->invoice->subscription;
	}

	/**
	 * Get saved Stripe PaymentIntent object or its key.
	 *
	 * @since 2.3.0
	 *
	 * @param string $key Name of the key to retrieve.
	 *
	 * @return mixed
	 */
	public function get_payment( $key = '' ) {

		return $this->get_var( 'intent', $key );
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

		$charge = isset( $this->intent->charges->data[0] ) ? $this->intent->charges->data[0] : null;

		if ( empty( $charge ) || empty( $keys ) ) {
			return array();
		}

		if ( \is_string( $keys ) ) {
			$keys = array( $keys );
		}

		$result = array();

		foreach ( $keys as $key ) {
			if ( isset( $charge->payment_method_details->card->{$key} ) ) {
				$result[ $key ] = \sanitize_text_field( $charge->payment_method_details->card->{$key} );
			} elseif ( isset( $charge->billing_details->{$key} ) ) {
				$result[ $key ] = \sanitize_text_field( $charge->billing_details->{$key} );
			}
		}

		return $result;
	}

	/**
	 * Request a frontend 3D Secure authorization from a user.
	 *
	 * @since 2.3.0
	 *
	 * @param \Stripe\PaymentIntent $intent PaymentIntent to authorize.
	 */
	protected function request_3dsecure_ajax( $intent ) {

		if ( ! isset( $intent->status ) || ! isset( $intent->next_action->type ) ) {
			return;
		}

		if ( 'requires_action' !== $intent->status || 'use_stripe_sdk' !== $intent->next_action->type ) {
			return;
		}

		\wp_send_json_success(
			array(
				'action_required'              => true,
				'payment_intent_client_secret' => $intent->client_secret,
			)
		);
	}

	/**
	 * Select 'default_payment_method' for Subscription if it needs to be explicitly set
	 * and cleanup remote PaymentMethods in the process.
	 *
	 * @since 2.3.0
	 *
	 * @param \Stripe\PaymentMethod $new_payment_method PaymentMethod object.
	 *
	 * @return string
	 *
	 * @throws \Exception In case of Stripe API error.
	 */
	protected function select_subscription_default_payment_method( $new_payment_method ) {

		// Stripe does not set the first PaymentMethod attached to a Customer as Customer's 'default_payment_method'.
		// Setting it manually if Customer's 'default_payment_method' is empty.
		if ( empty( $this->customer->invoice_settings->default_payment_method ) ) {
			$this->update_remote_customer_default_payment_method( $new_payment_method->id );
			// In this case Subscription's 'default_payment_method' doesn't have to be explicitly set and defaults to Customer's 'default_payment_method'.
			return '';
		}

		$default_payment_method = \Stripe\PaymentMethod::retrieve(
			$this->customer->invoice_settings->default_payment_method,
			Helpers::get_auth_opts()
		);

		// Update Customer's 'default_payment_method' with a new PaymentMethod if it has the same fingerprint.
		if ( $new_payment_method->card->fingerprint === $default_payment_method->card->fingerprint ) {
			$this->update_remote_customer_default_payment_method( $new_payment_method->id );
			$default_payment_method->detach();
			// In this case Subscription's 'default_payment_method' doesn't have to be explicitly set and defaults to Customer's 'default_payment_method'.
			return '';
		}

		// In case Customer's 'default_payment_method' is set and its fingerprint doesn't match with a new PaymentMethod, several things need to be done:
		// - Scan all active subscriptions for 'default_payment_method' with a same fingerprint as a new PaymentMethod.
		// - Change all matching subscriptions 'default_payment_method' to a new PaymentMethod.
		// - Delete all PaymentMethods previously set as 'default_payment_method' for matching subscriptions.
		$this->detach_remote_subscriptions_duplicated_payment_methods( $new_payment_method );

		// In this case Subscription's 'default_payment_method' has to be explicitly set
		// because Customer's 'default_payment_method' contains a different PaymentMethod and cannot be defaulted to.
		return $new_payment_method->id;
	}

	/**
	 * Update 'default_payment_method' for a Customer stored on a Stripe side.
	 *
	 * @since 2.3.0
	 *
	 * @param string $payment_method_id PaymentMethod id.
	 *
	 * @throws \Exception If a Customer fails to update.
	 */
	protected function update_remote_customer_default_payment_method( $payment_method_id ) {

		\Stripe\Customer::update(
			$this->get_customer( 'id' ),
			array(
				'invoice_settings' => array(
					'default_payment_method' => $payment_method_id,
				),
			),
			Helpers::get_auth_opts()
		);
	}

	/**
	 * Detach all active Subscriptions PaymentMethods having the same fingerprint as a given PaymentMethod.
	 *
	 * @since 2.3.0
	 *
	 * @param \Stripe\PaymentMethod $new_payment_method PaymentMethod object.
	 *
	 * @throws \Exception In case of Stripe API error.
	 */
	protected function detach_remote_subscriptions_duplicated_payment_methods( $new_payment_method ) {

		$subscriptions = \Stripe\Subscription::all(
			array(
				'customer' => $this->get_customer( 'id' ),
				'status'   => 'active',
				'limit'    => 100, // Maximum limit allowed by Stripe (https://stripe.com/docs/api/subscriptions/list#list_subscriptions-limit).
				'expand'   => array( 'data.default_payment_method' ),
			),
			Helpers::get_auth_opts()
		);

		$detach_methods = array();

		foreach ( $subscriptions as $subscription ) {

			if ( empty( $subscription->default_payment_method ) ) {
				continue;
			}

			if ( $new_payment_method->card->fingerprint === $subscription->default_payment_method->card->fingerprint ) {

				\Stripe\Subscription::update(
					$subscription->id,
					array( 'default_payment_method' => $new_payment_method->id ),
					Helpers::get_auth_opts()
				);
				$detach_methods[ $subscription->default_payment_method->id ] = $subscription->default_payment_method;
			}
		}

		foreach ( $detach_methods as $detach_method ) {
			$detach_method->detach();
		}
	}

	/**
	 * Set an encrypted token as a PaymentIntent metadata item.
	 *
	 * @since 2.5.0
	 *
	 * @throws \Stripe\Exception\ApiErrorException In case payment intent save wasn't successful.
	 */
	private function set_bypass_captcha_3dsecure_token() {

		$form_data = wpforms()->process->form_data;

		// Set token only if captcha is enabled for the form.
		if ( empty( $form_data['settings']['recaptcha'] ) ) {
			return;
		}

		$this->intent->metadata['captcha_3dsecure_token'] = \WPForms\Helpers\Crypto::encrypt( $this->intent->id );

		$this->intent->save();
	}

	/**
	 * Bypass CAPTCHA check on successful 3dSecure check.
	 *
	 * @since 2.5.0
	 *
	 * @param bool  $is_bypassed True if CAPTCHA is bypassed.
	 * @param array $entry       Form entry data.
	 * @param array $form_data   Form data and settings.
	 *
	 * @return bool
	 *
	 * @throws \Stripe\Exception\ApiErrorException In case payment intent save wasn't successful.
	 */
	public function bypass_captcha_on_3dsecure_submit( $is_bypassed, $entry, $form_data ) {

		// Sanity check to prevent possible tinkering with captcha on non-payment forms.
		if ( empty( $form_data['payments']['stripe']['enable'] ) ) {
			return $is_bypassed;
		}

		// Both reCAPTCHA and hCaptcha are enabled by the same setting.
		if ( empty( $form_data['settings']['recaptcha'] ) ) {
			return $is_bypassed;
		}

		if ( empty( $entry['payment_intent_id'] ) ) {
			return $is_bypassed;
		}

		// This is executed before payment processing kicks in and fills `$this->intent`.
		// PaymentIntent intent has to be retrieved from Stripe instead of getting it from `$this->intent`.
		$intent = $this->retrieve_payment_intent( $entry['payment_intent_id'] );

		if ( empty( $intent->status ) || $intent->status !== 'succeeded' ) {
			return $is_bypassed;
		}

		$token = ! empty( $intent->metadata['captcha_3dsecure_token'] ) ? $intent->metadata['captcha_3dsecure_token'] : '';

		if ( \WPForms\Helpers\Crypto::decrypt( $token ) !== $intent->id ) {
			return $is_bypassed;
		}

		// Cleanup the token to prevent its repeated usage and declutter the metadata.
		$intent->metadata['captcha_3dsecure_token'] = null;

		$intent->save();

		return true;
	}
}
