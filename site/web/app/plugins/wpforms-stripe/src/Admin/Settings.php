<?php

namespace WPFormsStripe\Admin;

use WPFormsStripe\Helpers;

/**
 * Stripe addon settings.
 *
 * @since 2.0.0
 */
class Settings {

	/**
	 * Current payment forms.
	 *
	 * @since 2.3.0
	 * @var array
	 */
	private $payment_forms = array();

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

		$this->get_payment_forms();

		\add_action( 'wpforms_settings_enqueue', array( $this, 'enqueue_scripts' ) );
		\add_action( 'wpforms_settings_init', array( $this, 'stripe_is_not_connected_admin_notice' ) );

		\add_filter( 'wpforms_settings_defaults', array( $this, 'register' ) );
	}

	/**
	 * Get current payment forms
	 *
	 * @since 2.3.0
	 */
	protected function get_payment_forms() {

		$this->payment_forms = array(
			'legacy'   => Helpers::get_forms_by_payment_collection_type( 'legacy' ),
			'elements' => Helpers::get_forms_by_payment_collection_type( 'elements' ),
		);
	}

	/**
	 * Enqueue Settings scripts and styles.
	 *
	 * @since 2.3.0
	 */
	public function enqueue_scripts() {

		$min = \wpforms_get_min_suffix();

		\wp_enqueue_script(
			'wpforms-admin-settings-stripe',
			\plugin_dir_url( WPFORMS_STRIPE_FILE ) . "assets/js/admin-settings-stripe{$min}.js",
			array( 'jquery' ),
			\WPFORMS_STRIPE_VERSION,
			true
		);

		$admin_settings_stripe_l10n = array(
			'has_payment_forms_legacy'                  => ! empty( $this->payment_forms['legacy'] ) ? true : false,
			'has_payment_forms_elements'                => ! empty( $this->payment_forms['elements'] ) ? true : false,
			'payment_collection_type_modal_elements_ok' => \esc_html__( 'Yes, use the Stripe Credit Card field', 'wpforms-stripe' ),
			'mode_update_ok'                            => \esc_html__( 'Yes, switch modes', 'wpforms-stripe' ),
			'mode_update_cancel'                        => \esc_html__( 'No, continue with a current mode', 'wpforms-stripe' ),
		);

		// PHPCS doesn't work well with long array key names. Using different syntax to work around that.
		$admin_settings_stripe_l10n['payment_collection_type_modal_elements_cancel'] = \esc_html__( 'No, continue with WPForms Credit Card field', 'wpforms-stripe' );

		$admin_settings_stripe_l10n['payment_collection_type_modal_elements'] = \sprintf(
			\wp_kses(
				/* translators: %s - WPForms.com Stripe documentation article URL. */
				\__(
					'<p>To use the Stripe Credit Card field, any previous Stripe payment forms must be <em>manually updated</em> after the settings are saved.</p>' .
					'<p><strong>Stripe payments will not be processed until the form updates have been completed if you continue.</strong></p>' .
					'<p>Before proceeding, please <a href="%s" target="_blank" rel="noopener noreferrer">read our documentation</a> on updating and the steps involved.</p>',
					'wpforms-stripe'
				),
				array(
					'p'      => array(),
					'a'      => array(
						'href'   => array(),
						'target' => array(),
						'rel'    => array(),
					),
					'em'     => array(),
					'strong' => array(),
				)
			),
			'https://wpforms.com/docs/how-to-update-to-the-stripe-credit-card-field'
		);

		$admin_settings_stripe_l10n['payment_collection_type_modal_legacy'] = \wp_kses(
			\__(
				'<p>To use the legacy WPForms Credit Card field, any previous Stripe payment forms containing the Stripe Credit Card field must be <em>manually updated</em> after the settings are saved.</p>' .
				'<p><strong>Stripe payments will not be processed until the form updates have been completed if you continue.</strong></p>',
				'wpforms-stripe'
			),
			array(
				'p'      => array(),
				'em'     => array(),
				'strong' => array(),
			)
		);

		$admin_settings_stripe_l10n['mode_update'] = \wp_kses(
			\__(
				'<p>Switching test/live modes requires Stripe account reconnection.</p>' .
				'<p>Press the <em>"Connect with Stripe"</em> button after saving the settings to reconnect.</p>',
				'wpforms-stripe'
			),
			array(
				'p'  => array(),
				'em' => array(),
			)
		);

		\wp_localize_script(
			'wpforms-admin-settings-stripe',
			'wpforms_admin_settings_stripe',
			$admin_settings_stripe_l10n
		);

		\wp_enqueue_style(
			'wpforms-admin-settings-stripe',
			\plugin_dir_url( WPFORMS_STRIPE_FILE ) . "assets/css/admin-settings-stripe{$min}.css",
			array(),
			\WPFORMS_STRIPE_VERSION
		);
	}

	/**
	 * Register Settings fields.
	 *
	 * @since 2.0.0
	 *
	 * @param array $settings Array of current form settings.
	 *
	 * @return array
	 */
	public function register( $settings ) {

		$settings['payments']['stripe-heading']           = array(
			'id'       => 'stripe-heading',
			'content'  => $this->get_heading_content(),
			'type'     => 'content',
			'no_label' => true,
			'class'    => array( 'section-heading' ),
		);
		$settings['payments']['stripe-connection-status'] = array(
			'id'      => 'stripe-connection-status',
			'name'    => \esc_html__( 'Connection Status', 'wpforms-stripe' ),
			'content' => $this->get_connection_status_content(),
			'type'    => 'content',
		);
		$settings['payments']['stripe-test-mode']         = array(
			'id'   => 'stripe-test-mode',
			'name' => \esc_html__( 'Test Mode', 'wpforms-stripe' ),
			'desc' => \sprintf(
				/* translators: %s - WPForms.com URL for Stripe paymen with more details. */
				\esc_html__( 'Check this option to prevent Stripe from processing live transactions. Please see %1$sour documentation on Stripe test payments for full details%2$s.', 'wpforms-stripe' ),
				'<a href="https://wpforms.com/docs/how-to-test-stripe-payments-on-your-site/" target="_blank" rel="noopener noreferrer">',
				'</a>'
			),
			'type' => 'checkbox',
		);
		if ( \absint( Helpers::get_api_version() ) === 2 || \get_option( 'wpforms_stripe_v230_upgrade', false ) ) {
			$settings['payments']['stripe-api-version'] = array(
				'id'         => 'stripe-api-version',
				'name'       => \esc_html__( 'Payment Collection Type', 'wpforms-stripe' ),
				'type'       => 'radio',
				'default'    => Helpers::has_stripe_keys() ? 2 : 3,
				'desc_after' => $this->get_payment_collection_type_desc_after(),
				'options'    => array(
					3 => \esc_html__( 'Stripe Credit Card Field (Recommended)', 'wpforms-stripe' ),
					2 => \esc_html__( 'WPForms Credit Card Field (Legacy)', 'wpforms-stripe' ),
				),
			);
		}
		$settings['payments']['stripe-test-publishable-key'] = array(
			'id'   => 'stripe-test-publishable-key',
			'name' => \esc_html__( 'Test Publishable Key', 'wpforms-stripe' ),
			'type' => 'text',
		);
		$settings['payments']['stripe-test-secret-key']      = array(
			'id'   => 'stripe-test-secret-key',
			'name' => \esc_html__( 'Test Secret Key', 'wpforms-stripe' ),
			'type' => 'text',
		);
		$settings['payments']['stripe-live-publishable-key'] = array(
			'id'   => 'stripe-live-publishable-key',
			'name' => \esc_html__( 'Live Publishable Key', 'wpforms-stripe' ),
			'type' => 'text',
		);
		$settings['payments']['stripe-live-secret-key']      = array(
			'id'   => 'stripe-live-secret-key',
			'name' => \esc_html__( 'Live Secret Key', 'wpforms-stripe' ),
			'type' => 'text',
		);

		return $settings;
	}

	/**
	 * Section header content.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	public function get_heading_content() {

		$output =
			'<h4>' . \esc_html__( 'Stripe', 'wpforms-stripe' ) . '</h4>' .
			'<p>' .
			\sprintf(
				\wp_kses(
					/* translators: %s - WPForms.com Stripe documentation article URL. */
					\__( 'Easily collect credit card payments with Stripe. For getting started and more information, see our <a href="%s" target="_blank" rel="noopener noreferrer">Stripe documentation</a>.', 'wpforms-stripe' ),
					array(
						'a' => array(
							'href'   => array(),
							'target' => array(),
							'rel'    => array(),
						),
					)
				),
				'https://wpforms.com/docs/how-to-install-and-use-the-stripe-addon-with-wpforms/'
			) .
			'</p>';

		return $output;
	}

	/**
	 * Connection Status setting content.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	public function get_connection_status_content() {

		$output = '';

		foreach ( array( 'live', 'test' ) as $mode ) {

			$account = \wpforms_stripe()->connect->get_connected_account( $mode );

			$output .= '<div class="wpforms-stripe-connection-status wpforms-stripe-connection-status-' . \esc_html( $mode ) . '"' . ( Helpers::get_stripe_mode() !== $mode ? '  style="display: none;"' : '' ) . '>';

			if ( empty( $account->id ) ) {
				$output .= $this->get_disconnected_status_content( $mode );
			} else {
				$output .= $this->get_connected_status_content( $mode );
			}

			$output .= '</div>';
		}

		if ( ! empty( $_GET['stripe_api_keys'] ) ) {
		$output .=
			'<p class="desc">' .
			\wp_kses(
				\__( 'Alternatively, you can <a href="#">manage your API keys manually</a>.', 'wpforms-stripe' ),
				array(
					'a' => array(
						'href'  => array(),
						'class' => array(),
					),
				)
			) .
			'</p>';
		}

		return $output;
	}

	/**
	 * Connected Status setting content.
	 *
	 * @since 2.3.0
	 *
	 * @param string $mode Stripe mode (e.g. 'live' or 'test').
	 *
	 * @return string
	 */
	public function get_connected_status_content( $mode = '' ) {

		$account_name = \wpforms_stripe()->connect->get_connected_account_name( $mode );
		$output       = '';

		$output .=
			'<div class="wpforms-connected">' .
			'<p>✅ ' .
			\sprintf(
				\wp_kses(
					/* translators: %1$s - Stripe account name connected; %2$s - Stripe mode connected (live or test). */
					__( 'Connected to Stripe as <em>%1$s</em> in <strong>%2$s</strong> mode.', 'wpforms-stripe' ),
					array(
						'strong' => array(),
						'em'     => array(),
					)
				),
				\esc_html( $account_name ),
				$mode ? $mode : Helpers::get_stripe_mode()
			) .
			'</p>' .
			'</div>';

		$connect_url = \wpforms_stripe()->connect->get_connect_with_stripe_url( $mode );

		$output .= '<p>';
		$output .= '<a href="' . \esc_url( $connect_url ) . '">' . \esc_html__( 'Switch Accounts', 'wpforms-stripe' ) . '</a>';
		$output .= '</p>';

		return $output;
	}

	/**
	 * Disconnected Status setting content.
	 *
	 * @since 2.3.0
	 *
	 * @param string $mode Stripe mode (e.g. 'live' or 'test').
	 *
	 * @return string
	 */
	public function get_disconnected_status_content( $mode = '' ) {

		$mode        = Helpers::validate_stripe_mode( $mode );
		$account     = \wpforms_stripe()->connect->get_connected_account( $mode );
		$connect_url = \wpforms_stripe()->connect->get_connect_with_stripe_url( $mode );
		$output      = '';

		$output .=
			'<div class="wpforms-connect">' .
			'<a href="' . \esc_url( $connect_url ) . '" title="' . \esc_html__( 'Connect with Stripe', 'wpforms-stripe' ) . '"><img src="' . \esc_url( \plugin_dir_url( \WPFORMS_STRIPE_FILE ) ) . '/assets/images/stripe-connect.png"></a>' .
			'<p>' .
			\sprintf(
				\wp_kses(
					/* translators: %s - WPForms.com Stripe documentation article URL. */
					\__( 'Securely connect to Stripe with just a few clicks to begin accepting payments! <a href="%s" target="_blank" rel="noopener noreferrer">Learn more</a> about connecting with Stripe.', 'wpforms-stripe' ),
					array(
						'a' => array(
							'href'   => array(),
							'target' => array(),
							'rel'    => array(),
						),
					)
				),
				'https://wpforms.com/docs/how-to-install-and-use-the-stripe-addon-with-wpforms/#connect-stripe'
			) .
			'</p>' .
			'</div>';

		if ( Helpers::has_stripe_keys( $mode ) && empty( $account->id ) ) {
			$output .=
				'<div class="wpforms-reconnect">' .
				'<p>' . \esc_html__( 'You are currently connected to Stripe using a deprecated authentication method.', 'wpforms-stripe' ) . '</p>' .
				'<p>' . \esc_html__( 'Please re-authenticate using Stripe Connect to use a more secure authentication method.', 'wpforms-stripe' ) . '</p>' .
				'</div>';
		}

		return $output;
	}

	/**
	 * Payment Collection Type setting after description.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	public function get_payment_collection_type_desc_after() {

		$payment_collection_type = \absint( \wpforms_setting( 'stripe-api-version' ) );
		$display                 = array();

		$update_notice = \sprintf(
			\wp_kses(
				/* translators: %s - WPForms.com Stripe documentation article URL. */
				\__( 'If you chose to update the payment collection type, the form(s) below will need to be manually updated. <a href="%s" target="_blank" rel="noopener noreferrer">Learn more</a>.', 'wpforms-stripe' ),
				array(
					'a' => array(
						'href'   => array(),
						'target' => array(),
						'rel'    => array(),
					),
				)
			),
			'https://wpforms.com/docs/how-to-update-to-the-stripe-credit-card-field'
		);

		$updated_notice = \sprintf(
			\wp_kses(
				/* translators: %s - WPForms.com Stripe documentation article URL. */
				\__( '<strong>IMPORTANT:</strong> The form(s) below need to be manually updated. Payments cannot be processed until the updates are completed. <a href="%s" target="_blank" rel="noopener noreferrer">Learn more</a>.', 'wpforms-stripe' ),
				array(
					'strong' => array(),
					'a'      => array(
						'href'   => array(),
						'target' => array(),
						'rel'    => array(),
					),
				)
			),
			'https://wpforms.com/docs/how-to-update-to-the-stripe-credit-card-field'
		);

		if ( 2 === $payment_collection_type && ! empty( $this->payment_forms['legacy'] ) ) {
			$display['text']  = 'update';
			$display['forms'] = 'legacy';
		} elseif ( 3 === $payment_collection_type && ! empty( $this->payment_forms['legacy'] ) ) {
			$display['text']  = 'updated';
			$display['forms'] = 'legacy';
		} elseif ( 3 === $payment_collection_type && ! empty( $this->payment_forms['elements'] ) ) {
			$display['text']  = 'update';
			$display['forms'] = 'elements';
		} elseif ( 2 === $payment_collection_type && ! empty( $this->payment_forms['elements'] ) ) {
			$display['text']  = 'updated';
			$display['forms'] = 'elements';
		}

		if ( empty( $display ) ) {
			return '';
		}

		$output  = '<div class="wpforms-' . \esc_attr( $display['text'] ) . '">';
		$output .= '<p class="desc">';
		$output .= 'update' === $display['text'] ? $update_notice : $updated_notice;
		$output .= '</p>';
		$output .= '<ul>';
		foreach ( $this->payment_forms[ $display['forms'] ] as $form ) {
			$output .= \sprintf(
				'<li><a href="%s" target="_blank">%s</a></li>',
				\esc_url( \admin_url( 'admin.php?page=wpforms-builder&view=fields&form_id=' . \absint( $form->ID ) ) ),
				\esc_html( $form->post_title )
			);
		}
		$output .= '</ul>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Stripe is not connected for the current payment mode notice.
	 *
	 * @since 2.3.0
	 */
	public function stripe_is_not_connected_admin_notice() {

		if ( ! isset( $_GET['view'] ) || 'payments' !== $_GET['view'] ) {
			return;
		}

		if ( Helpers::has_stripe_keys() ) {
			return;
		}

		$account = \wpforms_stripe()->connect->get_connected_account();

		if ( ! empty( $account->id ) ) {
			return;
		}

		\WPForms_Admin_Notice::warning(
			\esc_html__( 'Stripe is not connected for your current payment mode. Please press the "Connect with Stripe" button to complete this setup.', 'wpforms-stripe' )
		);
	}
}
