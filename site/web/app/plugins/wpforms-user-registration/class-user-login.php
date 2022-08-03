<?php

/**
 * Login form.
 *
 * @since 1.0.0
 */
class WPForms_User_Login {

	/**
	 * Flag to see if a user login form has been submitted.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	private $submitted = false;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		add_action( 'wpforms_loaded', array( $this, 'init' ) );
	}

	/**
	 * Initialize.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Actions/Filters.
		add_action( 'init', array( $this, 'load_template' ), 15, 1 );
		add_action( 'wpforms_form_settings_general', array( $this, 'template_settings' ) );
		add_filter( 'wpforms_frontend_load', array( $this, 'maybe_hide_login_form' ), 999, 3 );
		add_action( 'wpforms_process_before', array( $this, 'validate_user_login' ), 10, 2 );
		add_action( 'wpforms_process', array( $this, 'process_user_login' ), 10, 3 );
		add_filter( 'wpforms_process_after_filter', array( $this, 'remove_password_value' ), 10, 3 );
	}

	/**
	 * Load the user login form template.
	 *
	 * @since 1.0.2
	 */
	public function load_template() {

		// Template.
		require_once plugin_dir_path( __FILE__ ) . 'class-user-login-template.php';
	}

	/**
	 * Load the custom template settings for the form template.
	 *
	 * @since 1.0.0
	 *
	 * @param WPForms_Builder_Panel_Settings $instance
	 */
	public function template_settings( $instance ) {

		if ( ! empty( $instance->form_data['meta']['template'] ) && 'user_login' === $instance->form_data['meta']['template'] ) {

			wpforms_panel_field(
				'checkbox',
				'settings',
				'user_login_hide',
				$instance->form_data,
				esc_html__( 'Hide the form if a user is already logged in', 'wpforms-user-registration' )
			);

		}
	}

	/**
	 * Maybe hides the login form if the setting is selected.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $bool Value to determine if form should be displayed.
	 * @param array $form_data The information for the form.
	 * @param object $form The form object.
	 *
	 * @return bool
	 */
	public function maybe_hide_login_form( $bool, $form_data, $form ) {

		$is_gb_editor = \defined( 'REST_REQUEST' ) && REST_REQUEST && ! empty( $_REQUEST['context'] ) && 'edit' === $_REQUEST['context'];

		if ( $is_gb_editor ) {
			return true;
		}

		if ( empty( $form_data['settings']['user_login_hide'] ) ) {
			return $bool;
		}

		return is_user_logged_in() ? false : $bool;
	}

	/**
	 * Validate the user login form.
	 *
	 * @since 1.0.0
	 *
	 * @param array $entry The post data submitted by the form.
	 * @param array $form_data The information for the form.
	 */
	public function validate_user_login( $entry, $form_data ) {

		// If no form template can be found, return early.
		if ( empty( $form_data['meta']['template'] ) ) {
			return;
		}

		// If the form template doesn't match the user login template, return early.
		if ( 'user_login' !== $form_data['meta']['template'] ) {
			return;
		}

		// Set our flag to true so that we know a user login request has been submitted.
		$this->submitted = true;
	}

	/**
	 * Validate the user login form.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields The fields that have been submitted.
	 * @param array $entry The post data submitted by the form.
	 * @param array $form_data The information for the form.
	 */
	public function process_user_login( $fields, $entry, $form_data ) {

		// If a user login request has not been submitted, return early.
		if ( ! $this->submitted ) {
			return;
		}

		// If we can't get the form ID, return early.
		if ( empty( $form_data['id'] ) ) {
			return;
		}

		// Find the appropriate fields to process the login submission.
		$required     = array( 'login', 'password' );
		$login_fields = array();
		foreach ( $fields as $i => $field ) {
			if ( ! isset( $field['id'] ) ) {
				continue;
			}

			if ( ! isset( $field['value'] ) ) {
				continue;
			}

			if ( ! isset( $form_data['fields'][ $field['id'] ]['meta'] ) ) {
				continue;
			}

			$meta = $form_data['fields'][ $field['id'] ]['meta'];
			if ( empty( $meta['nickname'] ) ) {
				continue;
			}

			if ( ! in_array( $meta['nickname'], $required, true ) ) {
				continue;
			}

			$login_fields[ $meta['nickname'] ] = $field['value'];
		}

		// If no login fields have been found, return early.
		if ( empty( $login_fields ) || empty( $login_fields['login'] ) || empty( $login_fields['password'] ) ) {
			return;
		}

		// Now prepare the variables to log in.
		$username = $login_fields['login'];
		$password = $login_fields['password'];

		// Log in to WordPress.
		$creds = array(
			'user_login'    => $username,
			'user_password' => $password,
			'remember'      => true,
		);

		$creds = apply_filters( 'wpforms_user_registration_login_creds', $creds, $fields, $entry, $form_data );

		if ( $creds ) {
			$user = wp_signon( $creds, '' );
			if ( is_wp_error( $user ) ) {
				wpforms()->process->errors[ $form_data['id'] ]['header'] = apply_filters( 'wpforms_user_registration_login_error', $user->get_error_message(), $user->get_error_code() );

				return;
			}
		}
	}

	/**
	 * Don't store the real password value.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields
	 * @param array $entry
	 * @param array $form_data
	 *
	 * @return mixed
	 */
	public function remove_password_value( $fields, $entry, $form_data ) {

		if ( empty( $form_data['meta']['template'] ) || 'user_login' !== $form_data['meta']['template'] ) {
			return $fields;
		}

		foreach ( $fields as $id => $field ) {
			if ( 'password' === $field['type'] ) {
				$fields[ $id ]['value'] = '**********';
			}
		}

		return $fields;
	}
}

new WPForms_User_Login;
