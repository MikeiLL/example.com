<?php

namespace WPFormsStripe;

/**
 * Plugin install / activation actions.
 *
 * @since 2.3.0
 */
class Install {

	/**
	 * Constructor.
	 *
	 * @since 2.3.0
	 */
	public function __construct() {

		$this->init();
	}

	/**
	 * Initialize.
	 *
	 * @since 2.3.0
	 */
	public function init() {

		// When activated, trigger install method.
		\register_activation_hook( WPFORMS_STRIPE_FILE, array( $this, 'install' ) );

		// Watch for new multisite blogs.
		\add_action( 'wpmu_new_blog', array( $this, 'new_multisite_blog' ), 10, 6 );
	}

	/**
	 * Let's get the party started.
	 *
	 * @since 2.3.0
	 *
	 * @param bool $network_wide Is plugin activated network wide.
	 */
	public function install( $network_wide = false ) {

		// Check if we are on multisite and network activating.
		if ( \is_multisite() && $network_wide ) {

			$sites = \get_sites();

			foreach ( $sites as $site ) {
				\switch_to_blog( $site->blog_id );
				$this->run();
				\restore_current_blog();
			}
		} else {
			$this->run();
		}
	}

	/**
	 * Run install actions.
	 *
	 * @since 2.3.0
	 */
	public function run() {

		// Set current version, to be referenced in future updates.
		\update_option( 'wpforms_stripe_version', WPFORMS_STRIPE_VERSION );
	}

	/**
	 * When a new site is created in multisite, see if we are network activated,
	 * and if so run the installer.
	 *
	 * @since 2.3.0
	 *
	 * @param int    $blog_id Blog ID.
	 * @param int    $user_id User ID.
	 * @param string $domain  Site domain.
	 * @param string $path    Site path.
	 * @param int    $site_id Site ID. Only relevant on multi-network installs.
	 * @param array  $meta    Meta data. Used to set initial site options.
	 */
	public function new_multisite_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

		if ( \is_plugin_active_for_network( \plugin_basename( WPFORMS_STRIPE_FILE ) ) ) {
			\switch_to_blog( $blog_id );
			$this->run();
			\restore_current_blog();
		}
	}
}
