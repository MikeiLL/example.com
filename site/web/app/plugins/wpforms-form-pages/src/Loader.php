<?php

namespace WPFormsFormPages;

/**
 * Form Pages loader class.
 *
 * @since 1.0.0
 */
final class Loader {

	/**
	 * Have the only available instance of the class.
	 *
	 * @var Loader
	 *
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * URL to a plugin directory. Used for assets.
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	public $url = '';

	/**
	 * Initiate main plugin instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Loader
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) || ! ( self::$instance instanceof self ) ) {
			self::$instance = new Loader();
		}

		return self::$instance;
	}

	/**
	 * Loader constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->url = \plugin_dir_url( __DIR__ );

		\add_action( 'wpforms_loaded', array( $this, 'init' ) );
	}

	/**
	 * All the actual plugin loading is done here.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// WPForms Pro is required.
		if ( ! \wpforms()->pro ) {
			return;
		}

		// Load translated strings.
		\load_plugin_textdomain( 'wpforms-form-pages', false, \dirname( \plugin_basename( WPFORMS_FORM_PAGES_FILE ) ) . '/languages/' );

		if ( \wpforms_is_admin_page( 'builder' ) ) {
			new Admin\Builder();
		}

		if ( \wpforms_is_admin_page( 'overview' ) ) {
			new Admin\Overview();
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			new Admin\Ajax();
		}

		if ( ! \is_admin() ) {
			new Frontend();
		}

		// Register the updater of this plugin.
		$this->updater();
	}

	/**
	 * Load the plugin updater.
	 *
	 * @since 1.0.0
	 */
	private function updater() {

		\add_action(
			'wpforms_updater',
			function ( $key ) {
				new \WPForms_Updater(
					array(
						'plugin_name' => 'WPForms Form Pages',
						'plugin_slug' => 'wpforms-form-pages',
						'plugin_path' => \plugin_basename( \WPFORMS_FORM_PAGES_FILE ),
						'plugin_url'  => \trailingslashit( $this->url ),
						'remote_url'  => \WPFORMS_UPDATER_API,
						'version'     => \WPFORMS_FORM_PAGES_VERSION,
						'key'         => $key,
					)
				);
			}
		);
	}
}
