<?php

namespace WPFormsStripe\Admin;

use WPFormsStripe\Helpers;

/**
 * Plugin upgrade routines.
 *
 * @since 2.3.0
 */
class Upgrades {

	/**
	 * Have we upgraded?
	 *
	 * @since 2.3.0
	 *
	 * @var bool
	 */
	private $upgraded = false;

	/**
	 * Constructor.
	 *
	 * @since 2.3.0
	 */
	public function __construct() {

		\add_action( 'admin_init', array( $this, 'init' ), - 9999 );
	}

	/**
	 * Check if a new version is detected, if so perform update.
	 *
	 * @since 2.3.0
	 */
	public function init() {

		// Retrieve last known version.
		$version = \get_option( 'wpforms_stripe_version' );

		if ( empty( $version ) ) {
			// Version is set on plugin activation/install. Because it's missing
			// we can safely assume th plugin was updated. We can't tell the
			// exact previous version, so we use the last release.
			$version = '2.2.0';
		}

		if ( \version_compare( $version, '2.3.0', '<' ) ) {
			$this->v230_upgrade();
		}

		// If upgrade has occurred, update version options in database.
		if ( $this->upgraded ) {
			\update_option( 'wpforms_stripe_version_upgraded_from', $version );
			\update_option( 'wpforms_stripe_version', WPFORMS_STRIPE_VERSION );
		}
	}

	/**
	 * Upgrade for v2.3.0.
	 *
	 * @since 2.3.0
	 */
	private function v230_upgrade() {

		$legacy_payment_forms = Helpers::get_forms_by_payment_collection_type();
		$wpforms_settings     = \get_option( 'wpforms_settings', array() );

		if ( Helpers::has_stripe_keys() || ! empty( $legacy_payment_forms ) ) {

			$wpforms_settings['stripe-api-version'] = '2';

			\update_option(
				'wpforms_stripe_v230_upgrade',
				array(
					'upgraded' => time(),
				)
			);
		} else {
			$wpforms_settings['stripe-api-version'] = '3';
		}

		\update_option( 'wpforms_settings', $wpforms_settings );

		$this->upgraded = true;
	}
}
