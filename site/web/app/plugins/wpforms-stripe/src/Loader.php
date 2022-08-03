<?php

namespace WPFormsStripe;

/**
 * WPForms Stripe loader class.
 *
 * @since 2.0.0
 */
final class Loader {

	/**
	 * Have the only available instance of the class.
	 *
	 * @since 2.0.0
	 *
	 * @var Loader
	 */
	private static $instance;

	/**
	 * Payment API.
	 *
	 * @since 2.3.0
	 *
	 * @var API\ApiInterface
	 */
	public $api;

	/**
	 * Stripe Connect.
	 *
	 * @since 2.3.0
	 *
	 * @var Admin\Connect
	 */
	public $connect;

	/**
	 * Stripe processing instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Process
	 */
	public $process;

	/**
	 * URL to a plugin directory. Used for assets.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $url = '';

	/**
	 * Path to a plugin directory. Used for loading Stripe PHP library.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $path = '';

	/**
	 * Initiate main plugin instance.
	 *
	 * @since 2.0.0
	 *
	 * @return Loader
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
			self::$instance = new Loader();
		}

		return self::$instance;
	}

	/**
	 * Loader constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->url  = WPFORMS_STRIPE_URL;
		$this->path = WPFORMS_STRIPE_PATH;

		new Install();

		\add_action( 'wpforms_loaded', [ $this, 'init' ] );
	}

	/**
	 * All the actual plugin loading is done here.
	 *
	 * @since 2.0.0
	 */
	public function init() {

		$this->api = Helpers::get_api_class()->init();

		if ( \wpforms_is_admin_page( 'builder' ) ) {
			new Admin\StripePayment();
		}

		if ( \wpforms_is_admin_page( 'builder' ) || $this->is_new_field_ajax() ) {
			new Admin\Builder();
		}

		if ( \wpforms_is_admin_page( 'settings' ) ) {
			new Admin\Settings();
			$this->connect = new Admin\Connect();
		}

		if ( \is_admin() ) {
			new Admin\Upgrades();
			new Admin\Notices();
		}

		new Frontend();

		$this->process = new Process();
	}

	/**
	 * Check if the new field is being added via AJAX call.
	 *
	 * @since 2.3.0
	 */
	protected function is_new_field_ajax() {

		if ( ! \defined( 'DOING_AJAX' ) || ! \DOING_AJAX ) {
			return false;
		}

		if ( ! isset( $_POST['nonce'] ) || ! \wp_verify_nonce( \sanitize_key( $_POST['nonce'] ), 'wpforms-builder' ) ) {
			return false;
		}

		if ( empty( $_POST['action'] ) ) {
			return false;
		}

		$action = 'wpforms_new_field_' . $this->api->get_config( 'field_slug' );

		if ( $action !== $_POST['action'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Load the plugin updater.
	 *
	 * @since 2.0.0
	 * @deprecated 2.5.0
	 *
	 * @param string $key License key.
	 */
	public function updater( $key ) {

		_deprecated_function( __CLASS__ . '::' . __METHOD__, '2.5.0' );

		wpforms_stripe_updater( $key );
	}
}
