<?php


class WPForms_Views_DataTables_Settings {

	public function __construct() {
		add_filter( 'wpforms_views_settings_defaults', array( $this, 'add_fields' ), 10 );

	}

	function add_fields( $fields ) {
		$fields['license'] ['datatables_license'] = array(
			'id'   => 'datatables_license',
			'name'    => esc_html__( 'DataTables License', 'wpforms-views' ),
			'type'    => 'license',
		);
		return $fields;
	}

}

new WPForms_Views_DataTables_Settings();
