<?php

class WPF_Approve_Entries_Metabox {

	public function __construct() {
		if ( is_admin() ) {
			add_filter( 'wpforms_entry_details_sidebar_actions_link', array( $this, 'add_approval_action' ), 10, 3 );
			// Entry processing and setup.
			add_action( 'wpforms_entries_init', array( $this, 'process_approval_action' ), 8, 1 );
			add_filter( 'wpforms_views_config',  array( $this, 'add_to_addon_list' ) );

		}
	}

	function add_approval_action( $action_links, $entry, $form_data ) {
		global $wpdb;
		//echo 'here0'; die;
		$entry_meta_table = WPForms_Views_Common::get_entry_meta_table_name();
		$results = $wpdb->get_results( "SELECT * FROM {$entry_meta_table} where `entry_id`={$entry->entry_id} && `type`='approve'" );
		$approved = false;

		if ( ! empty( $results ) && is_array( $results ) ) {
			if( $results[0]->data == '1' ){
				$approved = true;
			}
		}else {

		}
		//var_dump($results);die;
		$base = add_query_arg(
			array(
				'page'     => 'wpforms-entries',
				'view'     => 'details',
				'entry_id' => $entry->entry_id,
			),
			admin_url( 'admin.php' )
		);

		// Approve Entry URL.
		$approval_url  = wp_nonce_url(
			add_query_arg(
				array(
					'action' => $approved ? 'unapprove' : 'approve',
					'form'   => absint( $form_data['id'] ),
				),
				$base
			),
			'wpf_views_approve_entry'
		);
		$approval_icon = $approved ? 'dashicons-dismiss':'dashicons-yes-alt' ;
		$approval_text = $approved ? esc_html__( 'Unapprove', 'wpforms' ) : esc_html__( 'Approve', 'wpforms' );


		$action_links['approve'] = array(
			'url'   => $approval_url,
			'icon'  => $approval_icon,
			'label' => $approval_text,
		);

		return $action_links;

	}



	function process_approval_action() {
		// Security check.
		if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'wpf_views_approve_entry' ) ) {
			return;
		}
		$entry_id = absint( $_GET['entry_id'] );
		global $wpdb;
		$entry_meta_table = WPForms_Views_Common::get_entry_meta_table_name();
		$results = $wpdb->get_results( "SELECT * FROM {$entry_meta_table} where `entry_id`={$entry_id} && `type`='approve'" );

		if ( ! empty( $results ) && is_array( $results ) ) {
			$meta_id = $results[0]->id;
		}

		// check for Approval
		if ( ! empty( $_GET['entry_id'] ) && ! empty( $_GET['action'] ) && ! empty( $_GET['form'] ) && 'approve' === $_GET['action'] ) {


			// approval status row exists
			if ( ! empty( $meta_id ) ) {
				wpforms()->entry_meta->update( $meta_id, array( 'data'=>'1' ) );

			}else {
				// Add new approval status
				wpforms()->entry_meta->add(
					array(
						'entry_id' => absint( $_GET['entry_id'] ),
						'form_id'  => absint( $_GET['form'] ),
						'user_id'  => get_current_user_id(),
						'type'     => 'approve',
						'data'     => 1,
					),
					'entry_meta'
				);
			}



		}
		// check for UnApproval
		if ( ! empty( $_GET['entry_id'] ) && ! empty( $_GET['action'] ) && ! empty( $_GET['form'] ) && 'unapprove' === $_GET['action'] ) {
			if ( ! empty( $meta_id ) ) {
				wpforms()->entry_meta->update( $meta_id, array( 'data'=>'0' ) );
			}

		}

	}

	// Add to addon list
	function add_to_addon_list( $view_config ) {
		$view_config['addons'][] = 'views_approved_submissions';
		return $view_config;
	}

}

new WPF_Approve_Entries_Metabox();
