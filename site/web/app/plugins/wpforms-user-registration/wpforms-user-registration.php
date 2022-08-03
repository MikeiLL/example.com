<?php
/**
 * Plugin Name:       WPForms User Registration
 * Plugin URI:        https://wpforms.com
 * Description:       User Registration and Login forms with WPForms.
 * Requires at least: 4.9
 * Requires PHP:      5.5
 * Author:            WPForms
 * Author URI:        https://wpforms.com
 * Version:           1.3.3
 * Text Domain:       wpforms-user-registration
 * Domain Path:       languages
 *
 * WPForms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * WPForms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WPForms. If not, see <https://www.gnu.org/licenses/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin version.
define( 'WPFORMS_USER_REGISTRATION_VERSION', '1.3.3' );

/**
 * Load the classes.
 *
 * @since 1.0.0
 */
require_once plugin_dir_path( __FILE__ ) . 'class-user-login.php';
require_once plugin_dir_path( __FILE__ ) . 'class-user-registration.php';
require_once plugin_dir_path( __FILE__ ) . 'class-user-activation.php';

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function wpforms_user_registration_textdomain() {
	load_plugin_textdomain( 'wpforms-user-registration', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'wpforms_user_registration_textdomain' );

/**
 * Load the plugin updater.
 *
 * @since 1.0.0
 *
 * @param string $key
 */
function wpforms_user_registration_updater( $key ) {

	new WPForms_Updater(
		array(
			'plugin_name' => 'WPForms User Registration',
			'plugin_slug' => 'wpforms-user-registration',
			'plugin_path' => plugin_basename( __FILE__ ),
			'plugin_url'  => trailingslashit( plugin_dir_url( __FILE__ ) ),
			'remote_url'  => WPFORMS_UPDATER_API,
			'version'     => WPFORMS_USER_REGISTRATION_VERSION,
			'key'         => $key,
		)
	);
}
add_action( 'wpforms_updater', 'wpforms_user_registration_updater' );
