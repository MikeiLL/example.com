<?php


class WPForms_Views_Approve_Entries_Settings {

	public function __construct() {
		add_filter( 'wpforms_views_settings_defaults', array( $this, 'add_fields' ), 10 );

	}

	function add_fields( $fields ) {
		$fields['license'] ['approve_entries_license'] = array(
			'id'   => 'approve_entries_license',
			'name'    => esc_html__( 'Approve Entries License', 'wpforms-views' ),
			'type'    => 'license',
		);
		return $fields;
	}

}

new WPForms_Views_Approve_Entries_Settings();
