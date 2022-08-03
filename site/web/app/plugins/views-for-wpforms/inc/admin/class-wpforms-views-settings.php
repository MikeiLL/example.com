<?php

class WPForms_Views_Settings {

	public $view;

	public function __construct() {

		// Maybe load settings page.
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_filter( 'wpforms_views_settings_exclude_view', array( $this, 'exclude_view' ) );
		add_filter( 'wpforms_views_settings_custom_process', array( $this, 'process_settings' ), 10, 2 );

	}

	public function init() {
		// Only load if we are actually on the settings page.
		if ( ! wpforms_views_is_admin_page(  ) ) {
			return;
		}

		$this->save_settings();

		// Determine the current active settings tab.
		$this->view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : 'license';


		add_action( 'admin_init', array( $this, 'initialize_options' ) );

		add_action( 'wpforms_views_settings_page',  array( $this, 'display' ) );

	}

	public function save_settings() {

		// Check nonce and other various security checks.
		if ( ! isset( $_POST['wpforms-views-settings-submit'] ) || empty( $_POST['nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wpforms-views-settings-nonce' ) ) {
			return;
		}


		if ( ! WPForms_Views_Roles_Capabilities::current_user_can( 'wpforms_views_edit_settings' ) ) {
			return;
		}

		if ( empty( $_POST['view'] ) ) {
			return;
		}
		$current_view = sanitize_key( $_POST['view'] );

		// Get registered fields and current settings.
		$fields   = $this->get_registered_settings( $current_view );
		$settings = get_option( 'wpforms_views_settings', array() );

		// Views excluded from saving list.
		$exclude_views = apply_filters( 'wpforms_views_settings_exclude_view', array(), $fields, $settings );

		if ( is_array( $exclude_views ) && in_array( $current_view, $exclude_views, true ) ) {
			// Run a custom save processing for excluded views.
			do_action( 'wpforms_views_settings_custom_process', $current_view, $fields, $settings );

			return;
		}

		if ( empty( $fields ) || ! is_array( $fields ) ) {
			return;
		}

		// Sanitize and prep each field.
		foreach ( $fields as $id => $field ) {

			// Certain field types are not valid for saving and are skipped.
			$exclude = apply_filters( 'wpforms_views_settings_exclude_type', array( 'content' ) );

			if ( empty( $field['type'] ) || in_array( $field['type'], $exclude, true ) ) {
				continue;
			}

			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$value      = isset( $_POST[ $id ] ) ? trim( wp_unslash( $_POST[ $id ] ) ) : false;
			$value_prev = isset( $settings[ $id ] ) ? $settings[ $id ] : false;

			// Custom filter can be provided for sanitizing, otherwise use defaults.
			if ( ! empty( $field['filter'] ) && is_callable( $field['filter'] ) ) {

				$value = call_user_func( $field['filter'], $value, $id, $field, $value_prev );

			} else {
				switch ( $field['type'] ) {
				case 'checkbox':
					$value = (bool) $value;
					break;
				case 'image':
					$value = esc_url_raw( $value );
					break;
				case 'number':
					$value = (float) $value;
					break;
				case 'text':
				case 'radio':
				case 'select':
				case 'license':
				default:
					$value = sanitize_text_field( $value );
					break;
				}
			}

			// Add to settings.
			$settings[ $id ] = $value;

		}
		// Save settings.
		$updated = update_option( 'wpforms_views_settings', $settings );
		do_action( 'wpforms_views_after_settings_udpate', $current_view, $settings );

		// \WPForms\Admin\Notice::success( esc_html__( 'Settings were successfully saved.', 'wpforms-lite' ) );
	}


	/**
	 * Register submenu
	 *
	 * @return void
	 */
	public function register_menu() {
		add_submenu_page( 'edit.php?post_type=wpforms-views', 'Settings', 'Settings', WPForms_Views_Roles_Capabilities::get_menu_cap( 'wpforms_views_edit_settings' ), 'wpforms-views-settings', array( &$this, 'settings_layout' ) );
	}


	public function get_registered_settings( $view = '' ) {

		$defaults = [
			// General Settings tab.
			'license'      => [
				'license-heading' => [
					'id'       => 'license-heading',
					'content'  => '<h4>' . esc_html__( 'License', 'wpforms-views' ) . '</h4><p>' . esc_html__( 'Your license key provides access to regular plugin updates & security fixes.', 'wpforms-views' ) . '</p>',
					'type'     => 'content',
					'no_label' => true,
					'class'    => [ 'section-heading' ],
				],
				'license_key' => [
					'id'      => 'license_key',
					'name'    => esc_html__( 'License', 'wpforms-views' ),
					'type'    => 'license',
				],


			],

			// Access settings tab.
			'access'        => [
				'access-heading'          => [
					'id'       => 'access-heading',
					'content'  => '<h4>' . esc_html__( 'Access Permissions', 'wpforms-views' ) . '</h4><p>' . esc_html__( 'Select which user roles can access to Views functionality. Administrator role has full access.', 'wpforms-views' ) . '</p>',
					'type'     => 'content',
					'no_label' => true,
					'class'    => [ 'section-heading', 'no-desc' ],
					//
				],
				'create-views'            => [
					'id'   => 'wpforms_views_create_views',
					'name' => esc_html__( 'Create Views', 'wpforms-views' ),
					'type'      => 'select',
					'choicesjs' => true,
					'multiple' => true,
					'options'   =>wpforms_views_get_user_roles_options(),
					'selected' => wpforms_views_get_roles_with_capabilites( 'wpforms_views_create_views' )
				],
				'edit-settings'            => [
					'id'   => 'wpforms_views_edit_settings',
					'name' => esc_html__( 'Edit Settings', 'wpforms-views' ),
					'type'      => 'select',
					'choicesjs' => true,
					'multiple' => true,
					'options'   =>wpforms_views_get_user_roles_options(),
					'selected' => wpforms_views_get_roles_with_capabilites( 'wpforms_views_edit_settings' )
				],
			]
		];
		$defaults = apply_filters( 'wpforms_views_settings_defaults', $defaults );
		return empty( $view ) ? $defaults : $defaults[ $view ];
	}

	public function get_settings_fields( $view = '' ) {

		$fields   = array();
		$settings = $this->get_registered_settings( $view );

		foreach ( $settings as $id => $args ) {

			$fields[ $id ] = wpforms_views_settings_output_field( $args );
		}

		return apply_filters( 'wpforms_settings_fields', $fields, $view );
	}



	public function get_tabs() {

		$tabs = [
			'license'      => [
				'name'   => esc_html__( 'License', 'wpforms-views' ),
				'form'   => true,
				'submit' => esc_html__( 'Save Settings', 'wpforms-views' ),
			],
			'access'        => [
				'name'   => esc_html__( 'Access', 'wpforms-views' ),
				'form'   => true,
				'submit' => esc_html__( 'Save Settings', 'wpforms-views' ),
			]
		];

		return apply_filters( 'wpforms_views_settings_tabs', $tabs );
	}

	public function tabs() {

		$tabs = $this->get_tabs();

		echo '<ul class="wpforms-views-admin-tabs">';
		foreach ( $tabs as $id => $tab ) {

			$active = $id === $this->view ? 'active' : '';
			$link   = add_query_arg( 'view', $id, admin_url( 'edit.php?post_type=wpforms-views&page=wpforms-views-settings' ) );

			echo '<li><a href="' . esc_url_raw( $link ) . '" class="' . esc_attr( $active ) . '">' . esc_html( $tab['name'] ) . '</a></li>';
		}
		echo '</ul>';
	}


	public function display() {

		$tabs   = $this->get_tabs();
		$fields = $this->get_settings_fields( $this->view );
?>

		<div id="views-settings-cont" class="wrap views-settings-cont">

			<?php $this->tabs(); ?>


			<div class=" wpforms-views-admin-settings wpforms-views-admin-settings-<?php echo esc_attr( $this->view ); ?>">

				<form class="wpforms-views-admin-settings-form" method="post">
					<input type="hidden" name="action" value="update-settings">
					<input type="hidden" name="view" value="<?php echo esc_attr( $this->view ); ?>">
					<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpforms-views-settings-nonce' ) ); ?>">


					<?php do_action( 'wpforms_views_admin_settings_before', $this->view, $fields ); ?>

					<?php
		foreach ( $fields as $field ) {
			echo $field;
		}
?>

					<?php if ( ! empty( $tabs[ $this->view ]['submit'] ) ) : ?>
						<p class="submit">
							<button type="submit" class="button button-primary" name="wpforms-views-settings-submit">
								<?php

			echo $tabs[ $this->view ]['submit'];
?>
							</button>
						</p>
					<?php endif; ?>

					<?php do_action( 'wpforms_views_admin_settings_after', $this->view, $fields ); ?>

					<?php if ( ! empty( $tabs[ $this->view ]['form'] ) ) : ?>
				</form>
			<?php endif; ?>

			</div>

		</div>
		<?php

	}



	/**
	 * Render submenu
	 *
	 * @return void
	 */
	public function settings_layout() {

		do_action( 'wpforms_views_settings_page' );
	}


	function exclude_view( $views ) {

		$views[] = 'access';
		return $views;

	}


	public function process_settings( $view, $fields ) {

		if ( $view !== 'access' ) {
			return;
		}

		foreach ( $fields as  $field ) {
			$cap_id = $field['id'];

			$value      = isset( $_POST[ $cap_id ] ) && \is_array( $_POST[ $cap_id ] ) ? \array_map( 'sanitize_text_field', \wp_unslash( $_POST[ $cap_id ] ) ) : array();
			$value_prev = isset( $field['selected'] ) ? $field['selected'] : array();

			$add_cap_roles    = \array_diff( $value, $value_prev );
			$remove_cap_roles = \array_diff( $value_prev, $value );

			// If create view access permission, add/remove extra capabilities
			if( $cap_id === 'wpforms_views_create_views'){
				$capabilities = WPForms_Views_Roles_Capabilities::posttype_caps();
				foreach ( $capabilities as $posttype_cap_id => $cap ) {
					$this->save_caps( $posttype_cap_id, $add_cap_roles, $remove_cap_roles );
				}
			}

			$this->save_caps( $cap_id, $add_cap_roles, $remove_cap_roles );
		}
	}

	protected function save_caps( $cap_id, $add_cap_roles, $remove_cap_roles ) {

		if ( empty( $add_cap_roles ) && empty( $remove_cap_roles ) ) {
			return;
		}

		$roles = \get_editable_roles();

		foreach ( $add_cap_roles as $role ) {
			if ( \array_key_exists( $role, $roles ) ) {
				\get_role( $role )->add_cap( $cap_id );
			}
		}

		foreach ( $remove_cap_roles as $role ) {
			if ( \array_key_exists( $role, $roles ) ) {
				\get_role( $role )->remove_cap( $cap_id );
			}
		}
	}

}

new WPForms_Views_Settings();
