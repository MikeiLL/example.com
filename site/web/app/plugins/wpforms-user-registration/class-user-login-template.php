<?php

/**
 * User login form template.
 *
 * @since 1.0.0
 */
class WPForms_Template_User_Login extends WPForms_Template {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$this->name        = esc_html__( 'User Login Form', 'wpforms-user-registration' );
		$this->slug        = 'user_login';
		$this->description = esc_html__( 'Allow your users to easily login to your site with their username and password.', 'wpforms-user-registration' );
		$this->includes    = '';
		$this->icon        = '';
		$this->modal       = '';
		$this->core        = true;
		$this->data        = array(
			'field_id' => '2',
			'fields'   => array(
				'0' => array(
					'id'       => '0',
					'type'     => 'text',
					'label'    => esc_html__( 'Username or Email', 'wpforms-user-registration' ),
					'required' => '1',
					'size'     => 'medium',
					'meta'     => array(
						'nickname' => 'login',
						'delete'   => false,
					),
				),
				'1' => array(
					'id'       => '1',
					'type'     => 'password',
					'label'    => esc_html__( 'Password', 'wpforms-user-registration' ),
					'required' => '1',
					'size'     => 'medium',
					'meta'     => array(
						'nickname' => 'password',
						'delete'   => false,
					),
				),
			),
			'settings' => array(
				'confirmation_type'     => 'redirect',
				'confirmation_redirect' => home_url(),
				'notification_enable'   => '0',
				'disable_entries'       => '1',
				'user_login_hide'       => '1',
				'antispam'              => true,
			),
			'meta'     => array(
				'template' => $this->slug,
			),
		);
	}
}

new WPForms_Template_User_Login;
