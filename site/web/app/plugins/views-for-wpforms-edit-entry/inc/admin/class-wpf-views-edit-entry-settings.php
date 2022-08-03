<?php


class WPF_Views_Edit_Entry_Settings {

	public function __construct() {
		add_filter( 'wpforms_views_settings_defaults', array( $this, 'add_fields' ), 10 );

	}

	function add_fields( $fields ) {
		$fields['access'] ['edit-entries'] = array(
			'id'   => 'wpforms_views_edit_entries',
			'name' => esc_html__( 'Edit Entries', 'wpforms-views' ),
			'type'      => 'select',
			'choicesjs' => true,
			'multiple' => true,
			'options'   =>wpforms_views_get_user_roles_options(),
			'selected' => wpforms_views_get_roles_with_capabilites( 'wpforms_views_edit_entries' )
		);
		$fields['license'] ['edit_entry_license'] = array(
			'id'   => 'edit_entry_license',
			'name'    => esc_html__( 'Edit Entry License', 'wpforms-views' ),
			'type'    => 'license',
		);
		return $fields;
	}

}

new WPF_Views_Edit_Entry_Settings();
