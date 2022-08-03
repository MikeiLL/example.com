<?php
/**
 * Fusion Hubspot.
 *
 * @package Fusion-Builder
 * @since 3.1
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Fusion Hubspot class.
 *
 * @since 3.1
 */
class Fusion_Hubspot {
	/**
	 * The one, true instance of this object.
	 *
	 * @static
	 * @access private
	 * @since 3.1
	 * @var object
	 */
	private static $instance;

	/**
	 * API key.
	 *
	 * @static
	 * @access private
	 * @since 3.1
	 * @var mixed
	 */
	private $key = null;

	/**
	 * Token data.
	 *
	 * @static
	 * @access private
	 * @since 3.1
	 * @var mixed
	 */
	private $token = null;

	/**
	 * Refresh token.
	 *
	 * @static
	 * @access private
	 * @since 3.1
	 * @var mixed
	 */
	private $refresh_token = null;

	/**
	 * Properties.
	 *
	 * @static
	 * @access private
	 * @since 3.1
	 * @var mixed
	 */
	private $properties = null;

	/**
	 * Properties.
	 *
	 * @static
	 * @access private
	 * @since 3.1
	 * @var mixed
	 */
	private $localize_status = null;

	/**
	 * Type of connection.
	 *
	 * @static
	 * @access private
	 * @since 3.1
	 * @var mixed
	 */
	private $type;

	/**
	 * Markup for notices.
	 *
	 * @static
	 * @access private
	 * @since 3.1
	 * @var mixed
	 */
	private $notices = '';

	/**
	 * Class constructor.
	 *
	 * @since 3.1
	 * @access private
	 */
	private function __construct() {
		$fusion_settings = fusion_get_fusion_settings();
		$this->type      = $fusion_settings->get( 'hubspot_api' );

		// Enqueue the OAuth script where required.
		$this->oauth_enqueue();

		// Add the PO options to the form CPT.
		add_filter( 'avada_form_submission_sections', [ $this, 'maybe_add_option' ] );

		// This is a redirect from our site with token.
		if ( is_admin() && current_user_can( 'manage_options' ) ) {

			// Trying to save a token.
			if ( isset( $_GET['hubspot'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$this->authenticate();
			}

			// Trying to revoke a token.
			if ( isset( $_GET['revoke_hubspot'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$this->revoke_access();
			}
		}

		// Render notices if we have any.
		add_action( 'avada_dashboard_notices', [ $this, 'render_notices' ] );

		// If not enabled, no need to load anything.
		if ( ! apply_filters( 'fusion_load_hubspot', ( 'off' !== $this->type ) ) ) {
			return;
		}

		// Enqueue the JS script for the PO mapping option.
		add_action( 'avada_page_option_scripts', [ $this, 'option_script' ], 10, 2 );

		// Add property list to live editor.
		add_action( 'fusion_app_preview_data', [ $this, 'add_preview_data' ], 10, 3 );
	}

	/**
	 * If set, render admin notices.
	 *
	 * @access public
	 * @since 3.1
	 * @return void
	 */
	public function render_notices() {
		if ( '' !== $this->notices ) {
			echo $this->notices; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Add note for rendering.
	 *
	 * @access public
	 * @since 3.1
	 * @param string $message The message to display.
	 * @param string $type The type of message.
	 * @return void
	 */
	public function add_notice( $message = '', $type = 'success' ) {
		$this->notices .= '<div id="fusion-hubspot-notice" class="notice notice-' . esc_attr( $type ) . ' avada-db-card avada-db-' . esc_attr( $type ) . '" style="display:block !important;"><h2>' . esc_html( $message ) . '</h2></div>';
	}

	/**
	 * Add property options to live editor.
	 *
	 * @access public
	 * @since 3.1
	 * @return void
	 */
	public function oauth_enqueue() {

		// Back-end TO page, enqueue so markup is updated.
		if ( is_admin() && current_user_can( 'manage_options' ) && ( ( isset( $_GET['page'] ) && 'avada_options' === $_GET['page'] ) || isset( $_GET['hubspot'] ) || isset( $_GET['revoke_hubspot'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_api_script' ] );
		}

		// Live editor JS script always, in case they change value.
		add_action( 'fusion_builder_enqueue_live_scripts', [ $this, 'enqueue_api_script' ] );
	}

	/**
	 * Add property options to live editor.
	 *
	 * @access public
	 * @since  3.1
	 * @param  array  $data The data already added.
	 * @param  int    $page_id The post ID being edited.
	 * @param  string $post_type The post type being edited.
	 * @return array $data The data with panel data added.
	 */
	public function add_preview_data( $data, $page_id = 0, $post_type = 'page' ) {
		if ( 'fusion_form' === $post_type ) {
			$data['hubspot'] = [
				'properties' => $this->get_properties(),
				'automatic'  => __( 'Automatic Property', 'fusion-builder' ),
				'none'       => __( 'No Property', 'fusion-builder' ),
				'common'     => __( 'Common Properties', 'fusion-builder' ),
				'other'      => __( 'Other Properties', 'fusion-builder' ),
			];

		}
		return $data;
	}

	/**
	 * Enqueue script for handling OAuth.
	 *
	 * @since 3.1
	 * @access public
	 * @return mixed
	 */
	public function enqueue_api_script() {
		wp_enqueue_script( 'fusion_hubspot_api', FUSION_BUILDER_PLUGIN_URL . 'assets/admin/js/fusion-hubspot-oauth.js', [], FUSION_BUILDER_VERSION, true );

		wp_localize_script(
			'fusion_hubspot_api',
			'fusionHubspotOAuth',
			[
				'status' => $this->localize_status,
			]
		);
	}

	/**
	 * If we have details to try and connect.
	 *
	 * @since 3.1
	 * @access public
	 * @return mixed
	 */
	public function can_connect() {
		if ( 'auth' === $this->type ) {
			return $this->get_token();
		} elseif ( 'key' === $this->type ) {
			return $this->get_api_key();
		}
		return false;
	}

	/**
	 * Get the API key
	 *
	 * @since 3.1
	 * @access public
	 * @return mixed
	 */
	public function get_api_key() {

		// We already have retrieved key.
		if ( null !== $this->key ) {
			return $this->key;
		}

		// No transient, attempt to refresh token.
		$fusion_settings = fusion_get_fusion_settings();
		$this->key       = $fusion_settings->get( 'hubspot_key' );

		if ( empty( $this->key ) ) {
			$this->key = false;
		}

		return $this->key;
	}

	/**
	 * Get the token data.
	 *
	 * @since 3.1
	 * @access public
	 * @return mixed
	 */
	public function get_token() {

		// We already have retrieved a token, continue to use it.
		if ( null !== $this->token ) {
			return $this->token;
		}

		$this->token = get_transient( 'fusion_hubspot_token' );

		// No transient, attempt to refresh token.
		if ( ! $this->token ) {
			$this->refresh_token();
		}

		// Return what we have.
		return $this->token;
	}

	/**
	 * Render info about connection status.
	 *
	 * @since 3.1
	 * @access public
	 * @return string
	 */
	public function maybe_render_button() {
		$auth_url = 'https://app.hubspot.com/oauth/authorize?client_id=999cc7c3-e358-4a3b-9984-a37dfbd319fa&redirect_uri=' . FUSION_UPDATES_URL . '/hubspot-api&scope=contacts%20actions%20timeline%20forms&state=' . rawurlencode( admin_url( 'admin.php?page=avada' ) );

		$type = 'connected';
		if ( isset( $_GET['error'] ) && ! empty( $_GET['error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
			$type = 'error';
		} elseif ( ! $this->get_token() ) {
			$type = 'no_token';
		}

		$output  = '<div id="fusion-hubspot-content">';
		$output .= '<div data-id="error" style="display:' . ( 'error' === $type ? 'flex' : 'none' ) . '">';
		$output .= '<span><strong>' . esc_html__( 'There was a problem when trying to connect. ', 'fusion-builder' ) . '</strong><br />';
		$output .= '<a target="_blank" href="https://theme-fusion.com/documentation/avada/forms/how-to-integrate-hubspot-with-avada-forms/">' . esc_html__( 'HubSpot integration with Avada Forms documentation.', 'fusion-builder' ) . '</a></span>';
		$output .= '<a class="button-primary" target="_blank" href="' . $auth_url . '">' . esc_html__( 'Try again.', 'fusion-builder' ) . '</a>';
		$output .= '</div>';
		$output .= '<div data-id="no_token"  style="display:' . ( 'no_token' === $type ? 'flex' : 'none' ) . '">';
		$output .= '<span><strong>' . esc_html__( 'Currently not connected. ', 'fusion-builder' ) . '</strong><br />';
		$output .= '<a target="_blank" href="https://theme-fusion.com/documentation/avada/forms/how-to-integrate-hubspot-with-avada-forms/">' . esc_html__( 'HubSpot integration with Avada Forms documentation.', 'fusion-builder' ) . '</a></span>';
		$output .= '<a class="button-primary" target="_blank" href="' . $auth_url . '">' . esc_html__( 'Connect with HubSpot', 'fusion-builder' ) . '</a>';
		$output .= '</div>';
		$output .= '<div data-id="connected"  style="display:' . ( 'connected' === $type ? 'flex' : 'none' ) . '">';
		$output .= '<strong>' . esc_html__( 'Connected with HubSpot', 'fusion-builder' ) . '</strong>';
		$output .= '<a class="button-primary" target="_blank" href="' . esc_url( admin_url( 'admin.php?page=avada&revoke_hubspot=1' ) ) . '">' . __( 'Revoke Access', 'fusion-builder' ) . '</a>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Revoke account access.
	 *
	 * @since 3.1
	 * @access public
	 * @return void
	 */
	public function revoke_access() {
		$this->reset_token();
		$this->reset_refresh_token();
		$this->localize_response( 'revoke' );
		$this->add_notice( __( 'Your HubSpot account has been disconnected.', 'fusion-builder' ), 'success' );
		$this->update_global( 'off' );
	}

	/**
	 * Update global hubspot option.
	 *
	 * @since 3.1
	 * @access public
	 * @param string $type Type of Hubspot api.
	 * @return void
	 */
	private function update_global( $type = 'auth' ) {
		$fusion_settings = fusion_get_fusion_settings();
		$fusion_settings->set( 'hubspot_api', $type );
		$this->type = $type;

		delete_transient( 'fusion_tos' );
		delete_transient( 'fusion_fb_tos' );
	}

	/**
	 * Localize the API scripts.
	 *
	 * @since 3.1
	 * @access public
	 * @param string $status Status type for localization.
	 * @return void
	 */
	private function localize_response( $status = 'error' ) {
		$this->localize_status = $status;
	}

	/**
	 * Reset stored access token.
	 *
	 * @since 3.1
	 * @access public
	 * @return void
	 */
	public function reset_token() {
		delete_transient( 'fusion_hubspot_token' );
		$this->token = null;
	}

	/**
	 * Reset stored refresh token.
	 *
	 * @since 3.1
	 * @access public
	 * @return void
	 */
	public function reset_refresh_token() {
		delete_option( 'fusion_hubspot_refresh' );
		$this->refresh_token = null;
	}

	/**
	 * Retrieve API response.
	 *
	 * @since 3.1
	 * @access public
	 * @param string $action Action/endpoint for API.
	 * @param array  $options Options for request.
	 * @return mixed
	 */
	public function api_request( $action = '', $options = [] ) {
		if ( '' === $action || ! $this->can_connect() ) {
			return false;
		}

		$method = 'GET';
		$url    = false;
		$args   = [
			'headers'      => [
				'User-Agent' => 'Fusion Hubspot',
			],
			'timeout'      => 60,
			'headers_data' => false,
		];

		// Shared args.
		if ( 'auth' === $this->type ) {
			$args['headers']['Authorization'] = 'Bearer ' . $this->token;
		}

		// Switch for action, vary url and args.
		switch ( $action ) {
			case 'get_properties':
				$url = 'https://api.hubapi.com/properties/v1/contacts/properties';
				break;
			case 'update_contact':
				$url                             = 'https://api.hubapi.com/contacts/v1/contact/createOrUpdate/email/' . $options['email'];
				$args['body']                    = wp_json_encode( $options['body'] );
				$args['headers']['content-type'] = 'application/json';
				$method                          = 'POST';
				break;
		}

		// Check for no URL.
		if ( ! $url ) {
			return;
		}

		// If we are connecting via key, add to URL.
		if ( 'key' === $this->type ) {
			$url .= '?hapikey=' . $this->get_api_key();
		}

		// We have URL, token, action and args.  Send the API request.
		if ( 'GET' === $method ) {
			$response = wp_remote_get( $url, $args );
		} else {
			$response = wp_remote_post( $url, $args );
		}

		// Token invalid, reset token.  Next request will then trigger refresh if possible.
		if ( 401 === (int) wp_remote_retrieve_response_code( $response ) ) {
			$this->reset_token();
		}

		// Check for error.
		if ( ! is_wp_error( $response ) && isset( $response['body'] ) ) {

			$response = json_decode( $response['body'], true );

			// Do more error checking here.
			if ( is_array( $response ) && isset( $response['status'] ) && 'error' === $response['status'] ) {
				return false;
			}

			return $response;
		}

		return false;
	}

	/**
	 * Add property data.
	 *
	 * @since 3.1
	 * @access public
	 * @param string $post_type Post type being added to.
	 * @return void
	 */
	public function option_script( $post_type ) {
		// Not editing a form then we don't need it.
		if ( 'fusion_form' !== $post_type ) {
			return;
		}

		// No connection to API then it can't work.
		if ( ! $this->can_connect() ) {
			return;
		}

		wp_enqueue_script( 'fusion_hubspot_option', FUSION_BUILDER_PLUGIN_URL . 'assets/admin/js/fusion-hubspot-option.js', [], FUSION_BUILDER_VERSION, true );

		$properties = $this->get_properties();
		if ( $properties ) {

			// Add property data.
			wp_localize_script(
				'fusion_hubspot_option',
				'fusionHubspot',
				[
					'properties' => $properties,
					'automatic'  => __( 'Automatic Property', 'fusion-builder' ),
					'none'       => __( 'No Property', 'fusion-builder' ),
					'common'     => __( 'Common Properties', 'fusion-builder' ),
					'other'      => __( 'Other Properties', 'fusion-builder' ),
				]
			);
		}
	}

	/**
	 * Get full array of properties;
	 *
	 * @since 3.1
	 * @access public
	 * @return mixed
	 */
	public function get_properties() {

		// Have already retrieved, return.
		if ( null !== $this->properties ) {
			return $this->properties;
		}

		// Retrieve from transient if available.
		$properties = get_transient( 'fusion_hubspot_properties' );
		if ( $properties ) {
			$this->properties = $properties;
			return $this->properties;
		}

		// Not in transient, need to request it.
		$this->properties = $this->api_request( 'get_properties' );
		if ( $this->properties ) {
			set_transient( 'fusion_hubspot_properties', $this->properties, DAY_IN_SECONDS );
		}
		return $this->properties;
	}

	/**
	 * Get the token data and store it.
	 *
	 * @since 3.1
	 * @access public
	 * @return void
	 */
	public function authenticate() {

		// Some kind of error reporting here.
		if ( ! isset( $_GET['token'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$this->localize_response( 'error' );
			$this->add_notice( __( 'There was an error authenticating your HubSpot token.', 'fusion-builder' ), 'notice' );
			return;
		}

		// Transient with expiry to match.
		$token      = sanitize_text_field( wp_unslash( $_GET['token'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$expiration = isset( $_GET['expires'] ) ? (int) sanitize_text_field( wp_unslash( $_GET['expires'] ) ) : 1000; // phpcs:ignore WordPress.Security.NonceVerification
		set_transient( 'fusion_hubspot_token', $token, $expiration );
		$this->token = $token;

		// Update refresh token, which does not expire.
		$refresh_token = isset( $_GET['refresh'] ) ? sanitize_text_field( wp_unslash( $_GET['refresh'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification
		update_option( 'fusion_hubspot_refresh', $refresh_token );
		$this->refresh_token = $refresh_token;

		$this->localize_response( 'success' );
		$this->add_notice( __( 'Your HubSpot account has been successfully connected.', 'fusion-builder' ), 'success' );

		$this->update_global( 'auth' );
	}

	/**
	 * Return stored refresh token.
	 *
	 * @since 3.1
	 * @access public
	 * @return mixed
	 */
	public function get_refresh_token() {
		if ( null !== $this->refresh_token ) {
			return $this->refresh_token;
		}

		$this->refresh_token = get_option( 'fusion_hubspot_refresh', false );

		return $this->refresh_token;
	}

	/**
	 * Refresh the token via our website.
	 *
	 * @since 3.1
	 * @access public
	 * @return void
	 */
	public function refresh_token() {
		if ( ! $this->get_refresh_token() ) {
			return;
		}

		$args = [
			'timeout'    => 60,
			'user-agent' => 'fusion-hubspot-refresh',
		];

		$response = wp_remote_get( 'https://updates.theme-fusion.com/wp-json/hubspot/' . $this->refresh_token, $args );

		if ( ! is_wp_error( $response ) && isset( $response['body'] ) ) {

			$response = json_decode( $response['body'], true );
			if ( $response ) {
				// Transient with expiry to match.
				set_transient( 'fusion_hubspot_token', esc_attr( $response['access_token'] ), (int) $response['expires_in'] );

				// Update refresh token, which does not expire.
				update_option( 'fusion_hubspot_refresh', esc_attr( $response['refresh_token'] ) );

				$this->token         = esc_attr( $response['access_token'] );
				$this->refresh_token = esc_attr( $response['refresh_token'] );
				return;
			}

			// Invalid refresh token most likely, wipe out option to prevent looping.
			$this->reset_refresh_token();
			return;
		}

		// Response failed entirely.
	}

	/**
	 * Create a HubSpot contact.
	 *
	 * @since 3.1
	 * @access public
	 * @param array $form_data Data from form which needs to be stored.
	 * @param array $mapping Information to map from the form to HubSpot properties.
	 * @param array $labels Array of label and field names.
	 * @return void
	 */
	public function create_contact( $form_data, $mapping = [], $labels = [] ) {
		if ( is_string( $mapping ) ) {
			$mapping = json_decode( $mapping, true );
		}

		// Property list, try to auto match.
		$properties = (array) $this->get_properties();

		// Empty starting data.
		$mapped_data = [
			'properties' => [],
		];

		// Request options.
		$options = [];

		// Array of assigned properties.
		$used_properties = [];

		// Loop each form field to check for mapping match.
		foreach ( $form_data['data'] as $field => $value ) {
			$field_value = ( is_array( $value ) ) ? implode( ' | ', $value ) : $value;

			// Update to correct format.
			$form_data['data'][ $field ] = $field_value;

			// Check if we have a desired property set in map.
			if ( isset( $mapping[ $field ] ) && '' !== $mapping[ $field ] ) {

				// If its set to have no property match, entirely exclude from matching.
				if ( 'fusion-none' === $mapping[ $field ] ) {
					unset( $form_data['data'][ $field ] );
					unset( $labels[ $field ] );
					continue;
				}

				// If we are matching to email, set as target contact.
				if ( 'email' === $mapping[ $field ] ) {
					$options['email'] = $field_value;
				}

				// Add to mapped data we will send.
				$mapped_data['properties'][] = [
					'property' => $mapping[ $field ],
					'value'    => $field_value,
				];

				$used_properties[ $mapping[ $field ] ] = true;
			}
		}

		// Auto matching if not all are set already.
		if ( count( $form_data['data'] ) !== count( $mapped_data['properties'] ) ) {
			foreach ( $properties as $property ) {

				$value = false;

				// Property is already assigned, do not assign again.
				if ( isset( $used_properties[ $property['name'] ] ) ) {
					continue;
				}

				// Property name matches input name.
				if ( isset( $form_data['data'][ $property['name'] ] ) ) {
					$value                       = $form_data['data'][ $property['name'] ];
					$mapped_data['properties'][] = [
						'property' => $property['name'],
						'value'    => $value,
					];

					// Property label matches input label.
				} elseif ( in_array( $property['label'], $labels, true ) ) {
					$field_id                    = array_search( $property['label'], $labels, true );
					$value                       = $form_data['data'][ $field_id ];
					$mapped_data['properties'][] = [
						'property' => $property['name'],
						'value'    => $value,
					];

					// Property name matches input label.
				} elseif ( in_array( $property['name'], $labels, true ) ) {
					$field_id                    = array_search( $property['name'], $labels, true );
					$value                       = $form_data['data'][ $field_id ];
					$mapped_data['properties'][] = [
						'property' => $property['name'],
						'value'    => $value,
					];

					// Property label matches input name.
				} elseif ( isset( $form_data['data'][ str_replace( ' ', '', strtolower( $property['label'] ) ) ] ) ) {
					$field_id                    = str_replace( ' ', '', strtolower( $property['label'] ) );
					$value                       = $form_data['data'][ $field_id ];
					$mapped_data['properties'][] = [
						'property' => $property['name'],
						'value'    => $value,
					];
				}

				// If email is one, add to options.
				if ( $value && 'email' === $property['name'] ) {
					$options['email'] = $value;
				}
			}
		}

		// No valid email, contact cannot be created or updated.
		if ( ! isset( $options['email'] ) || empty( $options['email'] ) ) {
			return;
		}

		// We made it this far, add data and set request.
		$options['body'] = $mapped_data;

		// Do some error logging here.
		$this->api_request( 'update_contact', $options );
	}

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @static
	 * @access public
	 * @param array $sections Page options.
	 * @since 3.1
	 */
	public function maybe_add_option( $sections ) {
		if ( 'off' === $this->type ) {
			$hubspot_link  = '<a target="_blank" rel="noopener noreferrer" href="https://hubs.to/39wVbJ">HubSpot account</a>';
			$document_link = '<a target="_blank" rel="noopener noreferrer" href="https://theme-fusion.com/documentation/avada/forms/how-to-integrate-hubspot-with-avada-forms/">HubSpot integration guide</a>';
			$sections['form_submission']['fields']['hubspot_info'] = [
				'type'        => 'custom',
				'label'       => '',

				/* translators: 1: HubSpot link. 2: Documentation link. */
				'description' => '<div class="fusion-redux-important-notice">' . sprintf( __( 'Sign up for a %1$s and manage your contacts in their free CRM.  For more information check out our %2$s. ', 'fusion-builder' ), $hubspot_link, $document_link ) . '</div>',
				'id'          => 'hubspot_info',
			];

			return $sections;
		}

		$properties = $this->get_properties();
		if ( $this->can_connect() && $properties ) {
			$sections['form_submission']['fields']['hubspot_info']   = [
				'type'        => 'custom',
				'label'       => '',
				'description' => '<div class="fusion-redux-important-notice">' . __( 'You are currently connected to the HubSpot API.', 'fusion-builder' ) . '</div>',
				'id'          => 'hubspot_info',
			];
			$sections['form_submission']['fields']['hubspot_action'] = [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'HubSpot Action', 'fusion-builder' ),
				'description' => esc_html__( 'Select if you want to perform a HubSpot action after form submission.', 'fusion-builder' ),
				'id'          => 'hubspot_action',
				'default'     => 'no',
				'transport'   => 'postMessage',
				'choices'     => [
					'no'      => esc_html__( 'None', 'fusion-builder' ),
					'contact' => esc_html__( 'Create/Update Contact', 'fusion-builder' ),
				],
			];
			$sections['form_submission']['fields']['hubspot_map']    = [
				'type'        => 'hubspot_map',
				'label'       => esc_html__( 'HubSpot Mapping', 'fusion-builder' ),
				'description' => __( 'Map fields from the form to HubSpot contact properties.  <strong>Note</strong>, the email property is required for creating or updating a contact.', 'fusion-builder' ),
				'id'          => 'hubspot_map',
				'transport'   => 'postMessage',
				'dependency'  => [
					[
						'field'      => 'hubspot_action',
						'value'      => 'contact',
						'comparison' => '==',
					],
				],
			];
			return $sections;
		}

		$hubspot_link = '<a target="_blank" rel="noopener noreferrer" href="' . esc_url( admin_url( 'themes.php?page=avada_options#hubspot_api' ) ) . '">HubSpot</a>';
		$sections['form_submission']['fields']['hubspot_info'] = [
			'type'        => 'custom',
			'label'       => '',
			/* translators: Global link. */
			'description' => '<div class="fusion-redux-important-notice">' . sprintf( __( 'Connect to your %s account to create contacts from your form.', 'fusion-builder' ), $hubspot_link ) . '</div>',
			'id'          => 'hubspot_info',
		];

		return $sections;
	}

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @static
	 * @access public
	 * @since 3.1
	 */
	public static function get_instance() {

		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null === self::$instance ) {
			self::$instance = new Fusion_Hubspot();
		}
		return self::$instance;
	}
}

/**
 * Instantiates the Fusion_Hubspot class.
 * Make sure the class is properly set-up.
 *
 * @since object 3.1
 * @return object Fusion_App
 */
function Fusion_Hubspot() { // phpcs:ignore WordPress.NamingConventions
	return Fusion_Hubspot::get_instance();
}
Fusion_Hubspot();
