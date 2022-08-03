<?php

class WPF_Views_Edit_Entry_Enable {

	public function __construct() {
		if ( is_admin() ) {
			add_filter('wpforms_views_config',  array( $this, 'add_to_addon_list' ) );
		//	add_filter('wpforms_get_conditional_logic_form_fields_supported', array( $this, 'enable_conditional_logic' ) );
		}
	}

	function add_to_addon_list( $view_config ){
		$view_config['addons'][] = 'views_edit_entries';
		return $view_config;
	}

	function enable_conditional_logic( $fields ){
		$fields[]= 'wpf_edit_entry';
		return $fields;
	}

}

new WPF_Views_Edit_Entry_Enable();
