<?php


class WPF_Views_Single_Entry_Settings {

	public function __construct() {
		add_filter( 'wpforms_views_settings_defaults', array( $this, 'add_fields' ), 10 );

	}

	function add_fields( $fields ) {
		$fields['license'] ['single_entry_license'] = array(
			'id'   => 'single_entry_license',
			'name'    => esc_html__( 'Single Entry License', 'wpforms-views' ),
			'type'    => 'license',
		);
		return $fields;
	}

}

new WPF_Views_Single_Entry_Settings();
