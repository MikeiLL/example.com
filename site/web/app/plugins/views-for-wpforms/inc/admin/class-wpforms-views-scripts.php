<?php
class WPForms_Views_Scripts {

	function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

			if( isset($_GET['page']) && ($_GET['page'] == 'wpforms-views-settings' )){
			add_filter('wpforms_admin_header', '__return_false');
			add_filter('wpforms_admin_flyoutmenu', '__return_false');
		}

	}
	function admin_scripts( $hook ) {
		if ( $hook == 'wpforms-views_page_wpforms-views-settings' ) {
			wp_enqueue_style( 'wpforms_views_admin_settings', WPFORMS_VIEWS_URL . '/assets/css/admin-settings.css' );
			wp_enqueue_style( 'wpforms_views_choice', WPFORMS_VIEWS_URL . '/assets/css/choices.min.css' );
			wp_enqueue_script( 'wpforms_views_choice', WPFORMS_VIEWS_URL . '/assets/js/choices.min.js' );
			wp_enqueue_script( 'wpforms_views_admin_settings', WPFORMS_VIEWS_URL . '/assets/js/admin-settings.js' );

		}
	}


}

new WPForms_Views_Scripts();