<?php

class WPForms_Views_License {

	public $id = 'license_key';
	public $store_url = 'https://formviewswp.com';
	public $item_id = WPFORMS_VIEWS_ITEM_ID;
	public $version = WPFORMS_VIEWS_VERSION;
	public $plugin_file = WPFORMS_VIEWS_PLUGIN_FILE;

	function __construct() {
		$this->add_hooks();
	}

	public function  add_hooks(){
		add_action( 'wpforms_views_after_settings_udpate',  array( $this, 'activate_license' ), 10, 2 );
		add_action( 'admin_notices',  array( $this, 'admin_notices' ) );
		add_action( 'admin_init', array( $this, 'include_updater' ) );
		add_action( 'admin_init', array( $this, 'check_license' ) );
	}

	public function include_updater() {
		$license_key = wpforms_views_setting( $this->id, '' );
		if ( ! empty( $license_key ) ) {
			// setup the updater
			$edd_updater = new EDD_SL_Plugin_Updater( $this->store_url, $this->plugin_file, array(
					'version'  => $this->version,
					'license'  => $license_key,
					'item_id' => $this->item_id,
					'author'  => 'Webholics'
				)
			);
		}

	}


	function activate_license( $view, $settings ) {
		if ( $view === 'license' && ! empty( $settings[$this->id] ) ) {
			// data to send in our API request
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $settings[$this->id],
				'item_id'  => urlencode( $this->item_id ),
				'url'        => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post( $this->store_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				} else {
					$message = __( 'An error occurred, please try again.' );
				}

			} else {

				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				if ( false === $license_data->success ) {

					$message = $this->get_error_message( $license_data );

				}

			}

			// Check if anything passed on a message constituting a failure
			if ( ! empty( $message ) ) {
				update_option( 'wpforms_views_' . $this->id . '_status_message', $message );
				delete_transient( 'wpforms_views_' . $this->id . '_status' );
				delete_option( 'wpforms_views_' . $this->id . '_status' );

			}else{
			delete_option( 'wpforms_views_' . $this->id . '_status_message' );
			update_option( 'wpforms_views_' . $this->id . '_status', $license_data->license );

			}


		}

		//}
	}

	public  function check_license() {
		$license_status = get_transient(  'wpforms_views_' . $this->id . '_status' );
		if ( ! $license_status ) {
			// check license

			$license_key = wpforms_views_setting( $this->id, '' );
			if ( ! empty( $license_key ) ) {
				$api_params = array(
					'edd_action'=> 'check_license',
					'license'  => $license_key,
					'item_id' => urlencode( $this->item_id ),
					'url' => home_url()
				);
				// Call the custom API.
				$response = wp_remote_get( add_query_arg( $api_params, $this->store_url ), array( 'timeout' => 15, 'sslverify' => false ) );

				// make sure the response came back okay
				if ( is_wp_error( $response ) )
					return false;

				// decode the license data
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				// $license_data->license will be either "active" or "inactive"

				update_option( 'wpforms_views_' . $this->id . '_status', $license_data->license );
				set_transient( 'wpforms_views_' . $this->id . '_status', $license_data->license, 60 * 60 * 24 );

				// if ( false === $license_data->success ) {
				// 	$message = $this->get_error_message( $license_data );
				// 	update_option( 'wpforms_views_' . $this->id . '_status_message', $message );
				// }

			}
		}

	}


	function get_error_message( $license_data ) {

		switch ( $license_data->error ) {

		case 'expired' :

			$message = sprintf(
				__( 'Your license key expired on %s.' ),
				date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
			);
			break;

		case 'revoked' :

			$message = __( 'Your license key has been disabled.' );
			break;

		case 'missing' :

			$message = __( 'Invalid license Key' );
			break;

		case 'invalid' :
		case 'site_inactive' :

			$message = __( 'Your license is not active for this URL.' );
			break;

		case 'item_name_mismatch' :

			$message = printf( __( 'This appears to be an invalid license key for NFViews.' ) );
			break;

		case 'no_activations_left':

			$message = __( 'Your license key has reached its activation limit.' );
			break;

		default :

			$message = __( 'An error occurred, please try again.' );
			break;
		}

		return $message;
	}


	function admin_notices() {
		if ( isset( $_GET['wpforms_views__sl_activation'] ) && ! empty( $_GET['message'] ) ) {

			switch ( $_GET['wpforms_views__sl_activation'] ) {

			case 'false':
				$message = urldecode( $_GET['message'] );
?>
				<div class="error">
					<p><?php echo $message; ?></p>
				</div>
				<?php
				break;

			case 'true':
			default:
				// Developers can put a custom success message here for when activation is successful if they way.
				break;

			}
		}
	}
}

new WPForms_Views_License();
