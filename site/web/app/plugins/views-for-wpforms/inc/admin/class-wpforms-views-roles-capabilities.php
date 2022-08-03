<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * NFViews Roles Class
 *
 * This class handles the role creation and assignment of capabilities for those roles.
 *
 * @since 1.15
 */
class WPForms_Views_Roles_Capabilities {


	static $instance = null;

	public static function get_instance() {

		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Get things going
	 *
	 * @since 1.15
	 */
	public function __construct() {
		$this->add_hooks();
	}

	/**
	 *
	 * @since 1.15
	 */
	private function add_hooks() {
		//add_filter( 'user_has_cap', array( $this, 'filter_user_has_cap' ), 10, 4 );
		add_action( 'admin_init', array( $this, 'add_caps' ) );
	}

	public static function posttype_caps(  ) {

		$administrator_caps = array(
			'publish_wpforms_views' => true,
			'edit_wpforms_views' => true,
			'edit_others_wpforms_views' => true,
			'delete_wpforms_views' => true,
			'delete_others_wpforms_views' => true,
			'read_private_wpforms_views' => true,
			'edit_wpforms_view' => true,
			'delete_wpforms_view' => true,
			'read_wpforms_view' => true,
		);
		return $administrator_caps;
	}


	/**
	 * Add capabilities to their respective roles if they don't already exist
	 * This could be simpler, but the goal is speed.
	 *
	 * @since 1.15
	 * @return void
	 */
	public function add_caps() {
		$capabilities = self::posttype_caps( );
		foreach ( $capabilities as $cap_id => $cap ) {
			get_role( 'administrator' )->add_cap( $cap_id );
		}

	}


	protected static function get_first_valid_cap( $caps, $id = 0 ) {

		$manage_cap = \wpforms_views_get_capability_manage_options();

		if ( \current_user_can( $manage_cap ) ) {
			return $manage_cap;
		}

		if ( empty( $caps ) && ! \current_user_can( $manage_cap ) ) {
			return '';
		}
		foreach ( (array) $caps as $cap ) {
			if ( \current_user_can( $cap ) ) {
				return $cap;
			}
		}

		return '';
	}

	public static function current_user_can( $caps = array(), $id = 0 ) {

		return (bool) self::get_first_valid_cap( $caps, $id );
	}


	public static function get_menu_cap( $caps ) {

		$valid = self::get_first_valid_cap( $caps );

		return $valid ? $valid : wpforms_views_get_capability_manage_options();
	}



}

add_action( 'init', array( 'WPForms_Views_Roles_Capabilities', 'get_instance' ), 1 );
