<?php
/**
 * Handle Form Submit.
 *
 * @since 3.1
 * @package fusion-builder
 */

/**
 * Handle Form Submit.
 *
 * @since 3.1
 */
class Fusion_Form_Submit {

	/**
	 * The reCAPTCHA class instance
	 *
	 * @access public
	 * @var bool|object
	 */
	public $re_captcha = false;

	/**
	 * Whats the error?
	 *
	 * @access public
	 * @var string
	 */
	public $captcha_error = '';

	/**
	 * ReCapatcha error flag.
	 *
	 * @access public
	 * @var bool
	 */
	public $has_error = false;

	/**
	 * Initializes hooks, filters and administrative functions.
	 *
	 * @since 3.1
	 * @access public
	 */
	public function __construct() {

		foreach ( [ 'database', 'email', 'url', 'database_email' ] as $method ) {
			add_action( "wp_ajax_fusion_form_submit_form_to_$method", [ $this, "ajax_submit_form_to_$method" ] );
			add_action( "wp_ajax_nopriv_fusion_form_submit_form_to_$method", [ $this, "ajax_submit_form_to_$method" ] );
		}

		$this->init_recaptcha();
	}

	/**
	 * Ajax callback for 'store in the database and send to email' submission type.
	 *
	 * @since 3.1
	 * @access public
	 * @return void
	 */
	public function ajax_submit_form_to_database_email() {

		// Checks nonce, recaptcha and similar. Dies if checks fail.
		$this->pre_process_form_submit();

		$data = $this->get_submit_data();

		$sendmail = $this->submit_form_to_email( $data );

		// We don't want internal email fields to be saved in db.
		$data = $this->remove_internal_email_fields( $data );

		$this->submit_form_to_database( $data );

		if ( $data['submission']['form_id'] && true === $sendmail ) {
			$fusion_forms = new Fusion_Form_DB_Forms();
			$fusion_forms->increment_submissions_count( $data['submission']['form_id'] );
			die( wp_json_encode( $this->get_results_from_message( 'success', 'db_email_saved' ) ) );
		}

		die( wp_json_encode( $this->get_results_from_message( 'error', 'db_email_failed' ) ) );
	}

	/**
	 * Ajax callback for 'store in the database' submission type.
	 *
	 * @since 3.1
	 * @access public
	 * @return void
	 */
	public function ajax_submit_form_to_database() {

		// Checks nonce, recaptcha and similar. Dies if checks fail.
		$this->pre_process_form_submit();

		$data = $this->get_submit_data();

		$this->submit_form_to_database( $data );

		if ( $data['submission']['form_id'] ) {
			$fusion_forms = new Fusion_Form_DB_Forms();
			$fusion_forms->increment_submissions_count( $data['submission']['form_id'] );
			die( wp_json_encode( $this->get_results_from_message( 'success', 'db_saved' ) ) );
		}

		die( wp_json_encode( $this->get_results_from_message( 'error', 'db_failed' ) ) );
	}

	/**
	 * Form submission will be stored in the database.
	 *
	 * @access protected
	 * @since 3.1.1
	 * @param array $data Form data array.
	 * @return void
	 */
	protected function submit_form_to_database( $data ) {

		if ( ! $data ) {
			$data = $this->get_submit_data();
		}

		$fusion_forms  = new Fusion_Form_DB_Forms();
		$submission    = new Fusion_Form_DB_Submissions();
		$submission_id = $submission->insert( $data['submission'] );

		foreach ( $data['data'] as $field => $value ) {
			$field_data  = ( is_array( $value ) ) ? implode( ' | ', $value ) : $value;
			$field_label = isset( $data['field_labels'][ $field ] ) ? $data['field_labels'][ $field ] : '';
			$db_field_id = $fusion_forms->insert_form_field( $data['submission']['form_id'], $field, $field_label );

			$entries = new Fusion_Form_DB_Entries();
			$entries->insert(
				[
					'form_id'       => absint( $data['submission']['form_id'] ),
					'submission_id' => absint( $submission_id ),
					'field_id'      => sanitize_key( $db_field_id ),
					'value'         => $field_data,
					'privacy'       => in_array( $field, $data['fields_holding_privacy_data'], true ),
				]
			);
		}
	}

	/**
	 * Ajax callback for 'send to email' submission type.
	 *
	 * @access public
	 * @since 3.1
	 * @return void
	 */
	public function ajax_submit_form_to_email() {

		// Checks nonce, recaptcha and similar. Dies if checks fail.
		$this->pre_process_form_submit();

		$data = $this->get_submit_data();

		$sendmail = $this->submit_form_to_email( $data );

		if ( $sendmail ) {
			$fusion_forms = new Fusion_Form_DB_Forms();
			$fusion_forms->increment_submissions_count( $data['submission']['form_id'] );

			die( wp_json_encode( $this->get_results_from_message( 'success', 'email_sent' ) ) );
		}

		die( wp_json_encode( $this->get_results_from_message( 'error', 'email_failed' ) ) );
	}

	/**
	 * Form submission will be sent to email.
	 *
	 * @access protected
	 * @since 3.1.1
	 * @param array $data Form data array.
	 * @return bool
	 */
	protected function submit_form_to_email( $data ) {

		if ( ! $data ) {
			$data = $this->get_submit_data();
		}

		$form_post_id   = isset( $_POST['form_id'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['form_id'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
		$to             = $data['data']['fusion_form_email'];
		$reply_to_email = fusion_data()->post_meta( $form_post_id )->get( 'email_reply_to' );
		$from_name      = ( isset( $data['data']['fusion_form_email_from'] ) && '' !== trim( $data['data']['fusion_form_email_from'] ) ) ? $data['data']['fusion_form_email_from'] : 'WordPress';
		$from_id        = ( isset( $data['data']['fusion_form_email_from_id'] ) && '' !== trim( $data['data']['fusion_form_email_from_id'] ) ) ? $data['data']['fusion_form_email_from_id'] : 'wordpress@' . home_url();
		$subject        = ( isset( $data['data']['fusion_form_email_subject'] ) && '' !== trim( $data['data']['fusion_form_email_subject'] ) ) ? $data['data']['fusion_form_email_subject'] : sprintf(
			/* Translators: The form-ID. */
			esc_html__( '%d form submissions received!', 'fusion-builder' ),
			$form_post_id
		);
		$subject_encode = ( isset( $data['data']['fusion_form_email_subject_encode'] ) ) ? $data['data']['fusion_form_email_subject_encode'] : 0;

		// We don't want internal email fields to sent in email.
		$data = $this->remove_internal_email_fields( $data );

		$email_data = '';
		foreach ( $data['data'] as $field => $value ) {
			$value       = is_array( $value ) ? implode( ' | ', $value ) : $value;
			$field_label = isset( $data['field_labels'][ $field ] ) && '' !== $data['field_labels'][ $field ] ? $data['field_labels'][ $field ] : $field;

			$email_data .= '<tr>';
			$email_data .= '<th align="left">' . htmlentities( $field_label, ENT_COMPAT, 'UTF-8' ) . '</th>';
			$email_data .= '<td>' . htmlentities( $value, ENT_COMPAT, 'UTF-8' ) . '</td>';
			$email_data .= '</tr>';

			// Replace placholders.
			if ( '' !== $to && false !== strpos( $to, '[' . $field . ']' ) ) {
				$to = str_replace( '[' . $field . ']', $value, $to );
			}

			if ( '' !== $reply_to_email && false !== strpos( $reply_to_email, '[' . $field . ']' ) ) {
				$reply_to_email = str_replace( '[' . $field . ']', $value, $reply_to_email );
			}

			if ( false !== strpos( $from_name, '[' . $field . ']' ) ) {
				$from_name = str_replace( '[' . $field . ']', $value, $from_name );
			}

			if ( false !== strpos( $from_id, '[' . $field . ']' ) ) {
				$from_id = str_replace( '[' . $field . ']', $value, $from_id );
			}

			if ( false !== strpos( $subject, '[' . $field . ']' ) ) {
				$subject = str_replace( '[' . $field . ']', $value, $subject );
			}
		}

		$title   = htmlentities( $subject );
		$message = "<html><head><title>$title</title></head><body><table cellspacing='4' cellpadding='4' align='left'>$email_data</table></body></html>";

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=UTF8' . "\r\n";
		$headers .= 'From: ' . $from_name . ' <' . $from_id . '>' . "\r\n";

		if ( '' !== $reply_to_email ) {
			$headers .= 'Reply-to: ' . $reply_to_email . "\r\n";
		}

		if ( $subject_encode ) {
			$subject = '=?utf-8?B?' . base64_encode( $subject ) . '?=';
		}

		$sendmail_args = apply_filters(
			'fusion_form_send_mail_args',
			[
				'to'      => $to,
				'subject' => $subject,
				'message' => $message,
				'headers' => $headers,
			],
			$data['submission']['form_id'],
			$data
		);

		$sendmail = wp_mail(
			$sendmail_args['to'],
			$sendmail_args['subject'],
			$sendmail_args['message'],
			$sendmail_args['headers']
		);

		return $sendmail;
	}

	/**
	 * Ajax callback for 'send to url' submission type.
	 *
	 * @access public
	 * @since 3.1
	 * @return void
	 */
	public function ajax_submit_form_to_url() {

		// Checks nonce, recaptcha and similar. Dies if checks fail.
		$this->pre_process_form_submit();

		$data = $this->get_submit_data();

		// Get the form-ID.
		$form_id = $data['submission']['form_id'];
		if ( isset( $_POST['form_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$form_id = absint( str_replace( 'fusion-form-', '', sanitize_text_field( wp_unslash( $_POST['form_id'] ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		if ( isset( $_POST['fusionAction'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

			// Get the URL.
			$url = sanitize_text_field( wp_unslash( $_POST['fusionAction'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

			// Error if no URL was found.
			if ( ! $url ) {
				die( wp_json_encode( $this->get_results_from_message( 'error', 'no_url' ) ) );
			}

			$request_args = [
				'method' => 'POST',
			];

			// Get the form method.
			if ( isset( $_POST['fusionActionMethod'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$request_args['method'] = sanitize_text_field( wp_unslash( $_POST['fusionActionMethod'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$request_args['method'] = strtoupper( $request_args['method'] );

				// Fallback in case we don't have a valid value.
				if ( ! in_array( $request_args['method'], [ 'POST', 'GET', 'HEAD', 'PUT', 'DELETE' ], true ) ) {
					$request_args['method'] = 'POST';
				}
			}

			// Add the submission arguments to our request.
			$request_args['body']            = wp_parse_args( $data['data'], $data['submission'] );
			$request_args['body']['form_id'] = $form_id;

			// Add custom headers if defined.
			$custom_headers = fusion_data()->post_meta( $form_id )->get( 'custom_headers' );
			if ( $custom_headers && is_string( $custom_headers ) && 5 < strlen( $custom_headers ) ) {
				$custom_headers = json_decode( $custom_headers );

				$request_args['headers'] = [];
				foreach ( $custom_headers as $header ) {
					$request_args['headers'][ $header->header_key ] = $header->header_value;
				}
			}

			// Make the request.
			$response = wp_remote_request( $url, $request_args );

			if ( ! is_wp_error( $response ) && isset( $response['body'] ) ) {
				$forms = new Fusion_Form_DB_Forms();
				$forms->increment_submissions_count( $form_id );

				$data['response_body'] = ( is_string( $response['body'] ) ) ? $response['body'] : wp_json_encode( $response['body'] );
				$type                  = $this->get_response_type_string( $response );

				die( wp_json_encode( $this->get_results_from_message( $type, $data['response_body'] ) ) );
			}
		}
		die( wp_json_encode( $this->get_results_from_message( 'error', 'url_failed' ) ) );
	}

	/**
	 * Proces nonce, recaptcha and similar checks.
	 * Dies if checks fail.
	 *
	 * @access protected
	 * @since 3.1.1
	 * @return void
	 */
	protected function pre_process_form_submit() {
		// Verify the form submission nonce.
		check_ajax_referer( 'fusion_form_nonce', 'fusion_form_nonce' );

		// If we are in demo mode, just pretend it has sent.
		if ( apply_filters( 'fusion_form_demo_mode', false ) ) {
			die( wp_json_encode( $this->get_results_from_message( 'success', 'demo' ) ) );
		}

		// Check reCAPTCHA response and die if error.
		$this->check_recaptcha_response();
	}

	/**
	 * Proces nonce, recaptcha and similar checks.
	 * Dies if checks fail.
	 *
	 * @access protected
	 * @since 3.1.1
	 * @param array $data Form data array.
	 * @return array
	 */
	protected function remove_internal_email_fields( $data ) {

		// Remove data used for internal purpose, only for email submission type.
		if ( isset( $data['data']['fusion_form_email'] ) ) {
			unset( $data['data']['fusion_form_email'] );
		}

		if ( isset( $data['data']['fusion_form_email_from'] ) ) {
			unset( $data['data']['fusion_form_email_from'] );
		}

		if ( isset( $data['data']['fusion_form_email_from_id'] ) ) {
			unset( $data['data']['fusion_form_email_from_id'] );
		}

		if ( isset( $data['data']['fusion_form_email_subject'] ) ) {
			unset( $data['data']['fusion_form_email_subject'] );
		}

		if ( isset( $data['data']['fusion_form_email_subject_encode'] ) ) {
			unset( $data['data']['fusion_form_email_subject_encode'] );
		}

		return $data;
	}

	/**
	 * Get the submission data.
	 *
	 * @access public
	 * @since 3.1.0
	 * @return array
	 */
	public function get_submit_data() {
		$form_data    = wp_unslash( $_POST['formData'] ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
		$files        = isset( $_FILES ) && ! empty( $_FILES ) ? $_FILES : [];
		$uploads      = ! empty( $files ) ? $this->handle_upload( $files ) : [];
		$field_labels = (array) json_decode( stripcslashes( $_POST['field_labels'] ), true ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput

		parse_str( $form_data, $form_data_array ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput

		if ( ! empty( $uploads ) && is_array( $uploads ) ) {
			foreach ( $uploads as $upload_name => $upload_url ) {
				$form_data_array[ $upload_name ] = $upload_url;
			}
		}

		$user_agent = '';
		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$user_agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
		}
		$source_url = '';
		if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			$source_url = sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) );
		}
		$ip = '';
		if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}
		if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		}
		$post_id      = isset( $_POST['post_id'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
		$form_post_id = isset( $_POST['form_id'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['form_id'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

		$fusion_forms = new Fusion_Form_DB_Forms();
		$form_id      = $fusion_forms->insert(
			[
				'form_id' => $form_post_id, // phpcs:ignore WordPress.Security.NonceVerification
				'views'   => 0,
			]
		);

		$data = [
			'submission'   => [
				'form_id'            => absint( $form_id ),
				'time'               => gmdate( 'Y-m-d H:i:s' ),
				'source_url'         => sanitize_text_field( $source_url ),
				'post_id'            => absint( $post_id ),
				'user_id'            => absint( get_current_user_id() ),
				'user_agent'         => sanitize_text_field( $user_agent ),
				'ip'                 => sanitize_text_field( $ip ),
				'is_read'            => false,
				'privacy_scrub_date' => gmdate( 'Y-m-d' ),
				'on_privacy_scrub'   => 'anonymize',
			],
			'data'         => $form_data_array,
			'field_labels' => $field_labels,
		];

		// Allow filtering the submission data.
		$data = apply_filters( 'fusion_builder_form_submission_data', $data );

		$fields_holding_privacy_data = [];
		if ( isset( $data['data']['fusion-fields-hold-private-data'] ) ) {
			$fields_holding_privacy_data = explode( ',', $data['data']['fusion-fields-hold-private-data'] );
			unset( $data['data']['fusion-fields-hold-private-data'] );
		}

		$data['fields_holding_privacy_data'] = $fields_holding_privacy_data;

		unset( $data['data']['fusion_privacy_store_ip_ua'] );
		unset( $data['data']['fusion_privacy_expiration_interval'] );
		unset( $data['data']['privacy_expiration_action'] );
		unset( $data['data'][ 'fusion-form-nonce-' . $form_post_id ] );

		if ( isset( $data['data']['g-recaptcha-response'] ) ) {
			unset( $data['data']['g-recaptcha-response'] );
		}

		if ( isset( $data['data']['fusion-form-recaptcha-response'] ) ) {
			unset( $data['data']['fusion-form-recaptcha-response'] );
		}

		// HubSpot data options.  Add do_action here for further extensions.
		if ( class_exists( 'Fusion_Hubspot' ) && 'contact' === fusion_data()->post_meta( $form_post_id )->get( 'hubspot_action' ) ) {
			Fusion_Hubspot()->create_contact( $data, fusion_data()->post_meta( $form_post_id )->get( 'hubspot_map' ), $field_labels );
		}

		return $data;
	}

	/**
	 * Check the reCAPTCHA response and die if error.
	 *
	 * @access protected
	 * @since 3.1
	 * @return void
	 */
	protected function check_recaptcha_response() {
		if ( isset( $_POST['g-recaptcha-response'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$this->process_recaptcha( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification
			if ( $this->has_error ) {
				$results         = [
					'status'  => 'error',
					'captcha' => 'failed',
					'info'    => 'captcha',
					'message' => $this->captcha_error,
				];
				$this->has_error = false;
				die( wp_json_encode( $results ) );
			}
		}
	}

	/**
	 * Setup reCAPTCHA.
	 *
	 * @since 3.1
	 * @access private
	 * @return void
	 */
	private function init_recaptcha() {
		global $fusion_settings;

		if ( $fusion_settings->get( 'recaptcha_public' ) && $fusion_settings->get( 'recaptcha_private' ) && ! function_exists( 'recaptcha_get_html' ) ) {
			if ( version_compare( PHP_VERSION, '5.3' ) >= 0 && ! class_exists( 'ReCaptcha' ) ) {
				require_once FUSION_LIBRARY_PATH . '/inc/recaptcha/src/autoload.php';

				// We use a wrapper class to avoid fatal errors due to syntax differences on PHP 5.2.
				require_once FUSION_LIBRARY_PATH . '/inc/recaptcha/class-fusion-recaptcha.php';

				// Instantiate reCAPTCHA object.
				$re_captcha_wrapper = new Fusion_ReCaptcha( $fusion_settings->get( 'recaptcha_private' ) );
				$this->re_captcha   = $re_captcha_wrapper->recaptcha;
			}
		}
	}

	/**
	 * Check reCAPTCHA.
	 *
	 * @since 3.1
	 * @access private
	 * @return void
	 */
	private function process_recaptcha() {
		global $fusion_settings;

		if ( $this->re_captcha ) {
			$re_captcha_response = null;
			// Was there a reCAPTCHA response?
			$post_recaptcha_response = ( isset( $_POST['g-recaptcha-response'] ) ) ? trim( wp_unslash( $_POST['g-recaptcha-response'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification

			$server_remote_addr = ( isset( $_SERVER['REMOTE_ADDR'] ) ) ? trim( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification

			if ( $post_recaptcha_response && ! empty( $post_recaptcha_response ) ) {
				if ( 'v2' === $fusion_settings->get( 'recaptcha_version' ) ) {
					$re_captcha_response = $this->re_captcha->verify( $post_recaptcha_response, $server_remote_addr );
				} else {
					$site_url            = get_option( 'siteurl' );
					$url_parts           = wp_parse_url( $site_url );
					$site_url            = isset( $url_parts['host'] ) ? $url_parts['host'] : $site_url;
					$re_captcha_response = $this->re_captcha->setExpectedHostname( apply_filters( 'avada_recaptcha_hostname', $site_url ) )->setExpectedAction( 'contact_form' )->setScoreThreshold( $fusion_settings->get( 'recaptcha_score' ) )->verify( $post_recaptcha_response, $server_remote_addr );
				}
			} else {
				$this->has_error     = true;
				$this->captcha_error = __( 'Sorry, ReCaptcha could not verify that you are a human. Please try again.', 'fusion-builder' );
			}

			// Check the reCAPTCHA response.
			if ( null === $re_captcha_response || ! $re_captcha_response->isSuccess() ) {
				$this->has_error = true;

				$error_codes = [];
				if ( null !== $re_captcha_response ) {
					$error_codes = $re_captcha_response->getErrorCodes();
				}

				if ( empty( $error_codes ) || in_array( 'score-threshold-not-met', $error_codes, true ) ) {
					$this->captcha_error = __( 'Sorry, ReCaptcha could not verify that you are a human. Please try again.', 'fusion-builder' );
				} else {
					$this->captcha_error = __( 'ReCaptcha configuration error. Please check the Global Options settings and your Recaptcha account settings.', 'fusion-builder' );
				}
			}
		}
	}

	/**
	 * Handles the file upload using wp native function.
	 *
	 * @since 3.1
	 * @param array $files The uploaded files array.
	 * @return array $moved_files Array containing uploaded files data or the error.
	 */
	public function handle_upload( $files ) {
		$uploaded_files = [];
		$moved_files    = [];

		foreach ( $files as $file ) {
			foreach ( $file as $key => $data ) {
				foreach ( $data as $key2 => $file_data ) {
					$uploaded_files[ $key2 ][ $key ] = $file_data;
				}
			}
		}

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		add_filter( 'sanitize_file_name', [ $this, 'randomize_name' ] );
		add_filter( 'upload_dir', [ $this, 'custom_upload_dir' ] );

		// Create form directory if not already there.
		$upload = wp_upload_dir();
		if ( ! file_exists( $upload['path'] ) ) {
			wp_mkdir_p( $upload['path'] );
		}

		foreach ( $uploaded_files as $field_name => $uploaded_file ) {
			$upload_overrides = [
				'test_form' => false,
			];

			$move_file = wp_handle_upload( $uploaded_file, $upload_overrides );

			if ( $move_file && isset( $move_file['error'] ) ) {
				die( wp_json_encode( $this->get_results_from_message( 'error', 'upload_failed' ) ) );
			}
			$moved_files[ $field_name ] = $move_file['url'];
		}

		remove_filter( 'sanitize_file_name', [ $this, 'randomize_name' ] );
		remove_filter( 'upload_dir', [ $this, 'custom_upload_dir' ] );

		return $moved_files;
	}

	/**
	 * Change the upload location to a separate folder.
	 *
	 * @since 3.1
	 * @param array $dir Upload directory info.
	 * @return array
	 */
	public function custom_upload_dir( $dir = [] ) {
		$dir['path']   = $dir['basedir'] . '/fusion-forms';
		$dir['url']    = $dir['baseurl'] . '/fusion-forms';
		$dir['subdir'] = '/fusion-forms';
		return $dir;
	}

	/**
	 * Change upload file name to a random string.
	 *
	 * @since 3.1
	 * @param string $filename File name.
	 * @return string File name.
	 */
	public function randomize_name( $filename = '' ) {
		$ext = empty( pathinfo( $filename, PATHINFO_EXTENSION ) ) ? '' : '.' . pathinfo( $filename, PATHINFO_EXTENSION );
		return uniqid() . $ext;
	}

	/**
	 * Get status string from status code.
	 *
	 * @access protected
	 * @since 3.2.1
	 * @param array $response The HTTP response array.
	 * @return string The status string.
	 */
	protected function get_response_type_string( $response ) {
		$code  = (string) wp_remote_retrieve_response_code( $response );
		$types = [
			'1' => 'info',
			'2' => 'success',
			'3' => 'redirect',
			'4' => 'client_error',
			'5' => 'server_error',
		];

		return isset( $types[ $code[0] ] ) ? $types[ $code[0] ] : 'error';
	}

	/**
	 * Get results message.
	 *
	 * @access protected
	 * @param string $type Can be success|error.
	 * @param string $info Type of success/error.
	 * @return string
	 */
	protected function get_results_from_message( $type, $info ) {
		return [
			'status' => $type,
			'info'   => $info,
		];
	}
}
