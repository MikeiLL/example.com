<?php

class WPForms_Views_DataTables_Enable {

	public function __construct() {
		if ( is_admin() ) {
			add_filter('wpforms_views_config',  array( $this, 'add_to_addon_list' ) );
		}
	}

	function add_to_addon_list( $view_config ){
		$view_config['addons'][] = 'views_datatable';
		return $view_config;
	}

}

new WPForms_Views_DataTables_Enable();
