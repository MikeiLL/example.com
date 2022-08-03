<?php

class WPForms_Views_Ajax {

	function __construct() {
		add_action( 'wp_ajax_wpforms_views_get_form_fields', array( $this, 'get_form_fields' ) );
	}

	public function get_form_fields() {
		if ( empty( $_POST['form_id'] ) ) return ;

		echo wpforms_views_get_form_fields( sanitize_text_field( $_POST['form_id'] ) );
		wp_die();
	}

}
new WPForms_Views_Ajax();