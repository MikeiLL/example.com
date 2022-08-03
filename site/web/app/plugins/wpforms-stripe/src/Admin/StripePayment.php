<?php

namespace WPFormsStripe\Admin;

/**
 * Stripe Form Builder related functionality.
 *
 * @since 2.0.0
 */
class StripePayment extends \WPForms_Payment {

	/**
	 * Initialize.
	 *
	 * @since 2.0.0
	 */
	public function init() {

		$this->version  = \WPFORMS_STRIPE_VERSION;
		$this->name     = 'Stripe';
		$this->slug     = 'stripe';
		$this->priority = 10;
		$this->icon     = \wpforms_stripe()->url . 'assets/images/addon-icon-stripe.png';
	}

	/**
	 * Display content inside the panel content area.
	 *
	 * @since 2.0.0
	 */
	public function builder_content() {

		Builder::content( $this->form_data );
	}
}
