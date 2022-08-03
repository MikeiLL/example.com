<?php

class WPF_Approve_Entries_View {

	public function __construct() {
		add_filter( 'wpforms_view_query_joins', array( $this, 'approved_entries_join' ), 10, 2 );
		add_filter( 'wpforms_view_query_where', array( $this, 'approved_entries_where' ), 10, 2 );
	}

	function approved_entries_join( $join, $args ) {

		if ( ! empty( $args['view_settings'] ) ) {
			$approved = $args['view_settings']->viewSettings->multipleentries->approvedSubmissions;
			if (  $approved == '1' ) {
				$entry_meta_table = WPForms_Views_Common::get_entry_meta_table_name();
				$join[] = "LEFT JOIN `$entry_meta_table` AS `apprvtable` ON ( `apprvtable`.`entry_id` = `t1`.`entry_id` AND `apprvtable`.`type` = 'approve') ";

			}
		}
		return $join;
	}

	function approved_entries_where( $where, $args ) {

		if ( ! empty( $args['view_settings'] ) ) {
			$approved = $args['view_settings']->viewSettings->multipleentries->approvedSubmissions;
			if (  $approved == '1' ) {
				$where[] = "(`apprvtable`.`data` = '1')";
			}
		}
		return $where;
	}

}

new WPF_Approve_Entries_View();