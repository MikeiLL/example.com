<?php
/**
 * Form Submissions Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage forms
 */

/**
 * Form Submissions page settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_form_submission( $sections ) {

	if ( ! function_exists( 'get_editable_roles' ) ) {
		require_once ABSPATH . 'wp-admin/includes/user.php';
	}

	$user_roles       = get_editable_roles();
	$user_roles_array = [];
	foreach ( $user_roles as $id => $role ) {
		$user_roles_array[ $id ] = translate_user_role( $role['name'] );
	}

	$sections['form_submission'] = [
		'label'    => esc_html__( 'Submission', 'Avada' ),
		'alt_icon' => 'fusiona-submission',
		'id'       => 'form_submission',
		'fields'   => [
			'form_type'            => [
				'type'        => 'select',
				'label'       => esc_html__( 'Submission Type', 'Avada' ),
				'description' => esc_html__( 'Make a selection for form submission type.', 'Avada' ),
				'id'          => 'form_type',
				'default'     => 'database',
				'choices'     => [
					'database'       => esc_html__( 'Save To Database', 'Avada' ),
					'url'            => esc_html__( 'Send To URL', 'Avada' ),
					'email'          => esc_html__( 'Send To Email', 'Avada' ),
					'database_email' => esc_html__( 'Save to Database and Send To Email', 'Avada' ),
					'default'        => esc_html__( 'Default POST HTML Form (non-AJAX)', 'Avada' ),
				],
				'dependency'  => [],
				'transport'   => 'postMessage',
			],
			'entries_notice'       => [
				'type'        => 'custom',
				'label'       => '',
				/* translators: Form entries link. */
				'description' => '<div class="fusion-redux-important-notice">' . sprintf( __( '<strong>IMPORTANT NOTE:</strong>You can view and manage form submissions by going to <a href="%s" target="_blank">form entries</a> section and selecting this form from the dropdown list.', 'Avada' ), admin_url( 'admin.php?page=avada-form-entries' ) ) . '</div>',
				'id'          => 'entries_notice',
				'dependency'  => [
					[
						'field'      => 'form_type',
						'value'      => 'url',
						'comparison' => '!=',
					],
					[
						'field'      => 'form_type',
						'value'      => 'email',
						'comparison' => '!=',
					],
					[
						'field'      => 'form_type',
						'value'      => 'default',
						'comparison' => '!=',
					],
				],
			],
			'email_placeholders'   => [
				'type'        => 'custom',
				'label'       => '',
				/* translators: Documentation post link. */
				'description' => '<div class="fusion-redux-important-notice">' . sprintf( __( '<strong>IMPORTANT NOTE:</strong> In email options, field names within square brackets can be used as placeholders which will be replaced when the form is submitted, ie: [email_address]. For more information check out our <a href="%s" target="_blank">form placeholders post</a>.', 'Avada' ), 'https://theme-fusion.com/documentation/avada/forms/avada-forms-email-submission-placeholders/' ) . '</div>',
				'id'          => 'email_placeholders',
				'dependency'  => [
					[
						'field'      => 'form_type',
						'value'      => 'database',
						'comparison' => '!=',
					],
					[
						'field'      => 'form_type',
						'value'      => 'url',
						'comparison' => '!=',
					],
					[
						'field'      => 'form_type',
						'value'      => 'default',
						'comparison' => '!=',
					],
				],
			],
			'method'               => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'Submission Method', 'Avada' ),
				'description' => esc_html__( 'Make a selection for form submission method.', 'Avada' ),
				'id'          => 'method',
				'default'     => 'post',
				'transport'   => 'postMessage',
				'choices'     => [
					'post' => esc_html__( 'Post', 'Avada' ),
					'get'  => esc_html__( 'Get', 'Avada' ),
				],
				'dependency'  => [
					[
						'field'      => 'form_type',
						'value'      => 'database',
						'comparison' => '!=',
					],
					[
						'field'      => 'form_type',
						'value'      => 'email',
						'comparison' => '!=',
					],
					[
						'field'      => 'form_type',
						'value'      => 'database_email',
						'comparison' => '!=',
					],
				],
			],
			'action'               => [
				'type'        => 'text',
				'label'       => esc_html__( 'Form Submission URL', 'Avada' ),
				'id'          => 'action',
				'description' => esc_html__( 'Enter the URL where form data should be sent to.', 'Avada' ),
				'transport'   => 'postMessage',
				'dependency'  => [
					[
						'field'      => 'form_type',
						'value'      => 'url',
						'comparison' => '==',
					],
				],
			],
			'email'                => [
				'type'        => 'text',
				'label'       => esc_html__( 'Form Submission Email', 'Avada' ),
				'id'          => 'email',
				'description' => esc_html__( 'Enter email ID where form data should be sent to. If left empty, email will be sent to the WordPress admin.', 'Avada' ),
				'transport'   => 'postMessage',
				'dependency'  => [
					[
						'field'      => 'form_type',
						'value'      => 'database',
						'comparison' => '!=',
					],
					[
						'field'      => 'form_type',
						'value'      => 'url',
						'comparison' => '!=',
					],
					[
						'field'      => 'form_type',
						'value'      => 'default',
						'comparison' => '!=',
					],
				],
			],
			'email_subject'        => [
				'type'        => 'text',
				'label'       => esc_html__( 'Email Subject', 'Avada' ),
				'description' => esc_html__( 'Enter email subject. If left empty, the form title will be used.', 'Avada' ),
				'id'          => 'email_subject',
				'default'     => esc_html__( 'Form submission received', 'Avada' ),
				'transport'   => 'postMessage',
				'dependency'  => [
					[
						'field'      => 'form_type',
						'value'      => 'database',
						'comparison' => '!=',
					],
					[
						'field'      => 'form_type',
						'value'      => 'url',
						'comparison' => '!=',
					],
					[
						'field'      => 'form_type',
						'value'      => 'default',
						'comparison' => '!=',
					],
				],
			],
			'email_subject_encode' => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'Encode Email Subject', 'Avada' ),
				'description' => esc_html__( 'Select if you want to encode email subjects. This helps to display special characters correctly in the subject field. A few hosting environments and email clients might have issues with this setting.', 'Avada' ),
				'id'          => 'email_subject_encode',
				'default'     => '0',
				'transport'   => 'postMessage',
				'choices'     => [
					'1' => esc_html__( 'Yes', 'Avada' ),
					'0' => esc_html__( 'No', 'Avada' ),
				],
				'dependency'  => [
					[
						'field'      => 'form_type',
						'value'      => 'database',
						'comparison' => '!=',
					],
					[
						'field'      => 'form_type',
						'value'      => 'url',
						'comparison' => '!=',
					],
					[
						'field'      => 'form_type',
						'value'      => 'default',
						'comparison' => '!=',
					],
				],
			],
			'email_from'           => [
				'type'        => 'text',
				'label'       => esc_html__( 'Email From Name', 'Avada' ),
				'description' => esc_html__( 'Enter email from name. If left empty, WordPress will be used.', 'Avada' ),
				'id'          => 'email_from',
				'default'     => get_bloginfo( 'name' ),
				'transport'   => 'postMessage',
				'dependency'  => [
					[
						'field'      => 'form_type',
						'value'      => 'database',
						'comparison' => '!=',
					],
					[
						'field'      => 'form_type',
						'value'      => 'url',
						'comparison' => '!=',
					],
					[
						'field'      => 'form_type',
						'value'      => 'default',
						'comparison' => '!=',
					],
				],
			],
			'email_from_id'        => [
				'type'        => 'text',
				'label'       => esc_html__( 'Sender Email', 'Avada' ),
				'description' => esc_html__( 'Enter sender email address. If left empty, wordpress@sitename.com will be used.', 'Avada' ),
				'id'          => 'email_from_id',
				'default'     => get_bloginfo( 'admin_email' ),
				'transport'   => 'postMessage',
				'dependency'  => [
					[
						'field'      => 'form_type',
						'value'      => 'database',
						'comparison' => '!=',
					],
					[
						'field'      => 'form_type',
						'value'      => 'url',
						'comparison' => '!=',
					],
					[
						'field'      => 'form_type',
						'value'      => 'default',
						'comparison' => '!=',
					],
				],
			],
			'email_reply_to'       => [
				'type'        => 'text',
				'label'       => esc_html__( 'Reply To Email', 'Avada' ),
				'description' => esc_html__( 'Enter reply to email address. ', 'Avada' ),
				'id'          => 'email_reply_to',
				'default'     => '',
				'transport'   => 'postMessage',
				'dependency'  => [
					[
						'field'      => 'form_type',
						'value'      => 'database',
						'comparison' => '!=',
					],
					[
						'field'      => 'form_type',
						'value'      => 'url',
						'comparison' => '!=',
					],
					[
						'field'      => 'form_type',
						'value'      => 'default',
						'comparison' => '!=',
					],
				],
			],
			'member_only_form'     => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'Enable Member Only Form', 'Avada' ),
				'description' => esc_html__( 'Select if you want to display this form to only logged in users with specific user roles.', 'Avada' ),
				'id'          => 'member_only_form',
				'default'     => 'no',
				'transport'   => 'postMessage',
				'choices'     => [
					'yes' => esc_html__( 'Yes', 'Avada' ),
					'no'  => esc_html__( 'No', 'Avada' ),
				],
				'dependency'  => [],
			],
			'user_roles'           => [
				'type'        => 'multiple_select',
				'label'       => esc_html__( 'Select User Role(s)', 'Avada' ),
				'description' => esc_html__( 'Select user role(s) you want to display this form to. Leaving blank will display form to any logged in user.', 'Avada' ),
				'id'          => 'user_roles',
				'choices'     => $user_roles_array,
				'transport'   => 'postMessage',
				'dependency'  => [
					[
						'field'      => 'member_only_form',
						'value'      => 'no',
						'comparison' => '!=',
					],
				],
			],
			'custom_headers'       => [
				'type'        => 'repeater',
				'label'       => esc_html__( 'Custom Headers', 'Avada' ),
				'description' => esc_html__( 'If you are using this form to integrate with a third-party API, you can use custom headers to implement authentication or pass-on any extra headers the API requires.', 'Avada' ),
				'id'          => 'custom_headers',
				'default'     => [],
				'row_add'     => 'Add Header',
				'row_title'   => 'Custom Header',
				'bind_title'  => 'header_key',
				'transport'   => 'postMessage',
				'fields'      => [
					'header_key'   => [
						'id'          => 'header_key',
						'type'        => 'text',
						'label'       => esc_html__( 'Custom Header Key', 'Avada' ),
						'description' => __( 'Enter the key for the request\'s custom header. Example: <code>Content-Type</code>', 'Avada' ),
						'default'     => '',
					],
					'header_value' => [
						'id'          => 'header_value',
						'type'        => 'text',
						'label'       => esc_html__( 'Custom Header Value', 'Avada' ),
						'description' => esc_html__( 'Enter the value for your custom-header.', 'Avada' ),
						'default'     => '',
					],
				],
				'dependency'  => [
					[
						'field'      => 'form_type',
						'value'      => 'url',
						'comparison' => '==',
					],
				],
			],
		],
	];

	return apply_filters( 'avada_form_submission_sections', $sections );

}
