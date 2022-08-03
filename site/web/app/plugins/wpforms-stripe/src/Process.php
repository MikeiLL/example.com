<?php

namespace WPFormsStripe;

/**
 * Stripe payment processing.
 *
 * @since 2.0.0
 */
class Process {

	/**
	 * Payment amount.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $amount = '';

	/**
	 * Form ID.
	 *
	 * @since 2.0.0
	 *
	 * @var int
	 */
	public $form_id = 0;

	/**
	 * Form Stripe payment settings.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	public $settings = array();

	/**
	 * Sanitized submitted field values and data.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	public $fields = array();

	/**
	 * Form data and settings.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	public $form_data = array();

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

		add_action( 'wpforms_process', [ $this, 'process_entry' ], 10, 4 );
		add_action( 'wpforms_process_complete', [ $this, 'process_entry_meta' ], 10, 4 );
		add_action( 'wpformsstripe_api_common_set_error_from_exception', [ $this, 'process_card_error' ] );
		add_filter( 'wpforms_entry_email_process', [ $this, 'process_email' ], 70, 4 );
	}

	/**
	 * Check if a payment exists with an entry, if so validate and process.
	 *
	 * @since 2.0.0
	 *
	 * @param array $fields    Final/sanitized submitted field data.
	 * @param array $entry     Copy of original $_POST.
	 * @param array $form_data Form data and settings.
	 */
	public function process_entry( $fields, $entry, $form_data ) {

		// Check if payment method exists and is enabled.
		if ( empty( $form_data['payments']['stripe']['enable'] ) ) {
			return;
		}

		$this->form_id   = (int) $form_data['id'];
		$this->fields    = $fields;
		$this->form_data = $form_data;
		$this->settings  = $form_data['payments']['stripe'];
		$this->amount    = \wpforms_get_total_payment( $this->fields );

		// Check for processing errors.
		if ( ! empty( wpforms()->process->errors[ $this->form_id ] ) ) {
			return;
		}

		if ( ! $this->is_card_field_visibility_ok() ) {
			return;
		}

		// Check for conditional logic.
		if ( ! $this->is_conditional_logic_ok( $this->settings ) ) {

			$title = \esc_html__( 'Stripe payment stopped by conditional logic.', 'wpforms-stripe' );
			$this->log_error( $title, $this->fields, 'conditional_logic' );

			return;
		}

		// Check rate limit.
		if ( ! $this->is_rate_limit_ok() ) {
			\wpforms()->process->errors[ $this->form_id ]['footer'] = \esc_html__( 'Unable to process payment, please try again later.', 'wpforms-stripe' );
			return;
		}

		\wpforms_stripe()->api->set_payment_tokens( $entry );

		$error = $this->get_entry_errors();

		// Before proceeding, check if any basic errors were detected.
		if ( $error ) {
			$this->log_error( $error );
			$this->display_error( $error );
		} else {
			$this->process_payment();
		}
	}

	/**
	 * Update entry details and add meta for a successful payment.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $fields    Final/sanitized submitted field data.
	 * @param array  $entry     Copy of original $_POST.
	 * @param array  $form_data Form data and settings.
	 * @param string $entry_id  Entry ID.
	 */
	public function process_entry_meta( $fields, $entry, $form_data, $entry_id ) {

		$payment = \wpforms_stripe()->api->get_payment();

		if ( empty( $payment->id ) || empty( $entry_id ) ) {
			return;
		}

		$customer     = \wpforms_stripe()->api->get_customer();
		$subscription = \wpforms_stripe()->api->get_subscription();

		\wpforms()->entry->update(
			$entry_id,
			array(
				'status' => 'completed',
				'type'   => 'payment',
				'meta'   => \wp_json_encode(
					array(
						'payment_type'         => 'stripe',
						'payment_total'        => $this->amount,
						'payment_currency'     => wpforms_get_currency(),
						'payment_transaction'  => \sanitize_text_field( $payment->id ),
						'payment_mode'         => 'live' === Helpers::get_stripe_mode() ? 'production' : 'test',
						'payment_subscription' => ! empty( $subscription->id ) ? \sanitize_text_field( $subscription->id ) : '',
						'payment_customer'     => ! empty( $customer->id ) ? \sanitize_text_field( $customer->id ) : '',
						'payment_period'       => ! empty( $subscription->id ) ? \sanitize_text_field( $this->settings['recurring']['period'] ) : '',
					)
				),
			),
			'',
			'',
			array( 'cap' => false )
		);

		// Update the Stripe charge meta data to include the Entry ID.
		$payment->metadata['entry_id']  = $entry_id;
		$payment->metadata['entry_url'] = \esc_url_raw( \admin_url( 'admin.php?page=wpforms-entries&view=details&entry_id=' . $entry_id ) );
		$payment->save();

		// Update the Stripe subscription meta data to include the Entry ID.
		if ( ! empty( $subscription->id ) ) {
			$subscription->metadata['entry_id']  = $entry_id;
			$subscription->metadata['entry_url'] = \esc_url_raw( \admin_url( 'admin.php?page=wpforms-entries&view=details&entry_id=' . $entry_id ) );
			$subscription->save();
		}

		// Processing complete.
		\do_action( 'wpforms_stripe_process_complete', $fields, $form_data, $entry_id, $payment, $subscription, $customer );
	}

	/**
	 * Logic that helps decide if we should send completed payments notifications.
	 *
	 * @since 2.5.0
	 *
	 * @param bool  $process         Whether to process or not.
	 * @param array $fields          Form fields.
	 * @param array $form_data       Form data.
	 * @param int   $notification_id Notification ID.
	 *
	 * @return bool
	 */
	public function process_email( $process, $fields, $form_data, $notification_id ) {

		if ( ! $process ) {
			return false;
		}

		if ( empty( $form_data['payments']['stripe']['enable'] ) ) {
			return $process;
		}

		if ( empty( $form_data['settings']['notifications'][ $notification_id ]['stripe'] ) ) {
			return $process;
		}

		if ( ! $this->is_conditional_logic_ok( $this->settings ) ) {
			return false;
		}

		return empty( wpforms_stripe()->api->get_error() );
	}

	/**
	 * Get general errors before payment processing.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	protected function get_entry_errors() {

		// Check for Stripe payment tokens (card token or payment id).
		$error = \wpforms_stripe()->api->get_error();

		// Check for Stripe keys.
		if ( ! Helpers::has_stripe_keys() ) {
			$error = \esc_html__( 'Stripe payment stopped, missing keys.', 'wpforms-stripe' );
		}

		// Check that, despite how the form is configured, the form and
		// entry actually contain payment fields, otherwise no need to proceed.
		if ( ! \wpforms_has_payment( 'form', $this->form_data ) || ! \wpforms_has_payment( 'entry', $this->fields ) ) {
			$error = \esc_html__( 'Stripe payment stopped, missing payment fields.', 'wpforms-stripe' );
		}

		// Check total charge amount.
		if ( empty( $this->amount ) || \wpforms_sanitize_amount( 0 ) == $this->amount ) {
			$error = \esc_html__( 'Stripe payment stopped, invalid/empty amount.', 'wpforms-stripe' );
		} elseif ( 50 > ( $this->amount * 100 ) ) {
			$error = \esc_html__( 'Stripe payment stopped, amount less than minimum charge required.', 'wpforms-stripe' );
		}

		return $error;
	}

	/**
	 * Process a payment.
	 *
	 * @since 2.3.0
	 */
	public function process_payment() {

		\wpforms_stripe()->api->setup_stripe();

		$error = \wpforms_stripe()->api->get_error();

		if ( $error ) {
			$this->process_api_error( 'general' );
			return;
		}

		// Proceed to executing the purchase.
		if ( empty( $this->settings['recurring']['enable'] ) ) {
			$this->process_payment_single();
		} else {
			$this->process_payment_subscription();
		}
	}

	/**
	 * Process a single payment.
	 *
	 * @since 2.0.0
	 */
	public function process_payment_single() {

		$currency = strtolower( wpforms_get_currency() );

		$amount_decimals = (int) str_pad( 1, wpforms_get_currency_decimals( $currency ) + 1, 0, STR_PAD_RIGHT );

		// Define the basic payment details.
		$args = [
			'amount'   => $this->amount * $amount_decimals,
			'currency' => $currency,
			'metadata' => [
				'form_name' => \sanitize_text_field( $this->form_data['settings']['form_title'] ),
				'form_id'   => $this->form_id,
			],
		];

		// Payment description.
		if ( ! empty( $this->settings['payment_description'] ) ) {
			$args['description'] = \html_entity_decode( $this->settings['payment_description'], ENT_COMPAT, 'UTF-8' );
		}

		// Receipt email.
		if ( ! empty( $this->settings['receipt_email'] ) && ! empty( $this->fields[ $this->settings['receipt_email'] ]['value'] ) ) {
			$args['receipt_email'] = \sanitize_email( $this->fields[ $this->settings['receipt_email'] ]['value'] );
		}

		\wpforms_stripe()->api->process_single( $args );

		$this->update_credit_card_field_value();

		$this->process_api_error( 'single' );
	}

	/**
	 * Process a subscription payment.
	 *
	 * @since 2.0.0
	 */
	public function process_payment_subscription() {

		$error = '';

		// Check for conditional logic.
		if ( ! $this->is_conditional_logic_ok( $this->settings['recurring'] ) ) {
			$this->process_payment_single();
			return;
		}

		// Check subscription settings are provided.
		if ( empty( $this->settings['recurring']['period'] ) || empty( $this->settings['recurring']['email'] ) ) {
			$error = \esc_html__( 'Stripe subscription payment stopped, missing form settings.', 'wpforms-stripe' );
		}

		// Check for required customer email.
		if ( empty( $this->fields[ $this->settings['recurring']['email'] ]['value'] ) ) {
			$error = \esc_html__( 'Stripe subscription payment stopped, customer email not found.', 'wpforms-stripe' );
		}

		// Before proceeding, check if any basic errors were detected.
		if ( $error ) {
			$this->log_error( $error );
			$this->display_error( $error );
			return;
		}

		$args = [
			'form_id'    => $this->form_id,
			'form_title' => \sanitize_text_field( $this->form_data['settings']['form_title'] ),
			'amount'     => $this->amount,
			'email'      => \sanitize_email( $this->fields[ $this->settings['recurring']['email'] ]['value'] ),
			'settings'   => $this->settings['recurring'],
		];

		\wpforms_stripe()->api->process_subscription( $args );

		// Update the credit card field value to contain basic details.
		$this->update_credit_card_field_value();

		$this->process_api_error( 'subscription' );
	}

	/**
	 * Update the credit card field value to contain basic details.
	 *
	 * @since 2.0.0
	 */
	public function update_credit_card_field_value() {

		foreach ( $this->fields as $field_id => $field ) {

			if ( \wpforms_stripe()->api->get_config( 'field_slug' ) !== $field['type'] ) {
				continue;
			}

			$details = \wpforms_stripe()->api->get_charge_details( array( 'name', 'last4', 'brand' ) );

			if ( ! empty( $details['last4'] ) ) {
				$details['last4'] = 'XXXXXXXXXXXX' . $details['last4'];
			}

			if ( ! empty( $details['brand'] ) ) {
				$details['brand'] = \ucfirst( $details['brand'] );
			}

			$details = \is_array( $details ) && ! empty( $details ) ? \implode( "\n", \array_filter( $details ) ) : '';

			\wpforms()->process->fields[ $field_id ]['value'] = \apply_filters(
				'wpforms_stripe_creditcard_value',
				$details,
				\wpforms_stripe()->api->get_payment()
			);
		}
	}

	/**
	 * Check if there is at least one visible (not hidden by conditional logic) card field in the form.
	 *
	 * @since 2.4.1
	 */
	protected function is_card_field_visibility_ok() {

		// If the form contains no fields with conditional logic the card field is visible by default.
		if ( empty( $this->form_data['conditional_fields'] ) ) {
			return true;
		}

		foreach ( $this->fields as $field ) {

			if ( wpforms_stripe()->api->get_config( 'field_slug' ) !== $field['type'] ) {
				continue;
			}

			// if the field is NOT in array of conditional fields, it's visible.
			if ( ! in_array( $field['id'], $this->form_data['conditional_fields'], true ) ) {
				return true;
			}

			// if the field IS in array of conditional fields and marked as visible, it's visible.
			if ( ! empty( $field['visible'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if conditional logic check passes for the given settings.
	 *
	 * @since 2.3.0
	 *
	 * @param array $settings Conditional logic settings to process.
	 *
	 * @return bool
	 */
	protected function is_conditional_logic_ok( $settings ) {

		// Check conditional logic settings.
		if (
			empty( $settings['conditional_logic'] ) ||
			empty( $settings['conditional_type'] ) ||
			empty( $settings['conditionals'] )
		) {
			return true;
		}

		// All conditional logic checks passed, continue with processing.
		$process = \wpforms_conditional_logic()->process( $this->fields, $this->form_data, $settings['conditionals'] );

		if ( 'stop' === $settings['conditional_type'] ) {
			$process = ! $process;
		}

		return $process;
	}

	/**
	 * Log payment error.
	 *
	 * @since 2.3.0
	 *
	 * @param string $title   Error title.
	 * @param string $message Error message.
	 * @param string $level   Error level to add to 'payment' error level.
	 */
	protected function log_error( $title, $message = '', $level = 'error' ) {

		if ( $message instanceof \Stripe\Exception\ApiErrorException ) {
			$body    = $message->getJsonBody();
			$message = isset( $body['error']['message'] ) ? $body['error'] : $message->getMessage();
		}

		\wpforms_log(
			$title,
			$message,
			array(
				'type'    => array( 'payment', $level ),
				'form_id' => $this->form_id,
			)
		);
	}

	/**
	 * Collect errors from API and turn it into form errors.
	 *
	 * @since 2.3.0
	 *
	 * @param string $type Payment time (e.g. 'single' or 'subscription').
	 */
	protected function process_api_error( $type ) {

		$message = \wpforms_stripe()->api->get_error();

		if ( empty( $message ) ) {
			return;
		}

		$message = \sprintf(
			/* translators: %s - error message. */
			\esc_html__( 'Credit Card Payment Error: %s', 'wpforms-stripe' ),
			$message
		);

		$this->display_error( $message );

		if ( 'subscription' === $type ) {
			$title = \esc_html__( 'Stripe subscription payment stopped by error', 'wpforms-stripe' );
		} else {
			$title = \esc_html__( 'Stripe payment stopped by error', 'wpforms-stripe' );
		}

		$this->log_error( $title, \wpforms_stripe()->api->get_exception() );
	}

	/**
	 * Display form error.
	 *
	 * @since 2.4.0
	 *
	 * @param string $error Error to display.
	 */
	private function display_error( $error ) {

		if ( ! $error ) {
			return;
		}

		$field_slug = \wpforms_stripe()->api->get_config( 'field_slug' );

		// Check if the form contains a required credit card. If it does
		// and there was an error, return the error to the user and prevent
		// the form from being submitted. This should not occur under normal
		// circumstances.
		foreach ( $this->form_data['fields'] as $field ) {

			if ( empty( $field['type'] ) || $field_slug !== $field['type'] ) {
				continue;
			}

			if ( ! empty( $field['required'] ) ) {
				\wpforms()->process->errors[ $this->form_id ]['footer'] = $error;
				return;
			}
		}
	}

	/**
	 * Process card error from Stripe API exception and adds rate limit tracking.
	 *
	 * @since 2.3.0
	 *
	 * @param \Exception|\Stripe\Exception\ApiErrorException $e Stripe API exception to process.
	 */
	public function process_card_error( $e ) {

		if ( Helpers::get_stripe_mode() === 'test' ) {
			return;
		}

		if ( ! \is_a( $e, '\Stripe\Exception\CardException' ) ) {
			return;
		}

		if ( ! \apply_filters( 'wpforms_stripe_process_process_card_error', true ) ) {
			return;
		}

		( new RateLimit() )->increment_attempts();
	}

	/**
	 * Check if rate limit is under threshold and passes.
	 *
	 * @since 2.3.0
	 */
	protected function is_rate_limit_ok() {

		return ( new RateLimit() )->is_ok();
	}
}
