<?php

/**
 * User registration form template.
 *
 * @since 1.0.0
 */
class WPForms_Template_User_Registration extends WPForms_Template {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		$this->name        = esc_html__( 'User Registration Form', 'wpforms-user-registration' );
		$this->slug        = 'user_registration';
		$this->description = esc_html__( 'Create customized WordPress user registration form and add them anywhere on your website.', 'wpforms-user-registration' );
		$this->includes    = '';
		$this->icon        = '';
		$this->core        = true;
		$this->modal       = array(
			'title'   => esc_html__( 'Don&#39;t Forget', 'wpforms-user-registration' ),
			'message' => esc_html__( 'Additional user registration options are available in the settings panel.', 'wpforms-user-registration' ),
		);
		$this->data        = array(
			'field_id' => '6',
			'fields'   => array(
				'1' => array(
					'id'       => '1',
					'type'     => 'name',
					'label'    => esc_html__( 'Name', 'wpforms-user-registration' ),
					'format'   => 'first-last',
					'required' => '1',
				),
				'2' => array(
					'id'       => '2',
					'type'     => 'text',
					'label'    => esc_html__( 'Username', 'wpforms-user-registration' ),
					'required' => '1',
					'size'     => 'medium',
				),
				'3' => array(
					'id'       => '3',
					'type'     => 'email',
					'label'    => esc_html__( 'Email', 'wpforms-user-registration' ),
					'required' => '1',
					'size'     => 'medium',
					'meta'     => array(
						'nickname' => 'email',
						'delete'   => false,
					),
				),
				'4' => array(
					'id'       => '4',
					'type'     => 'password',
					'label'    => esc_html__( 'Password', 'wpforms-user-registration' ),
					'required' => '1',
					'size'     => 'medium',
				),
				'5' => array(
					'id'          => '5',
					'type'        => 'textarea',
					'label'       => esc_html__( 'Short Bio', 'wpforms-user-registration' ),
					'description' => esc_html__( 'Share a little information about yourself.', 'wpforms-user-registration' ),
					'size'        => 'small',
				),
			),
			'settings' => array(
				'antispam'                    => '1',
				'confirmation_message_scroll' => '1',
				'registration_username'       => '2',
				'registration_name'           => '1',
				'registration_password'       => '4',
				'registration_bio'            => '5',
				'registration_email_user'     => '1',
				'registration_email_admin'    => '1',
			),
			'meta'     => array(
				'template' => $this->slug,
			),
		);
	}

	/**
	 * Conditional to determine if the template informational modal screens
	 * should display.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data
	 *
	 * @return bool
	 */
	public function template_modal_conditional( $form_data ) {

		return true;
	}
}

new WPForms_Template_User_Registration;
