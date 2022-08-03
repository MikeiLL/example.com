<?php
if ( class_exists( 'WPForms_DB' ) ) {
	class WPF_Views_Edit_Entry_Db extends WPForms_DB {
		private static $instance;


		public function __construct() {

			global $wpdb;

			$this->table_name  = $wpdb->prefix . 'wpforms_entries';
			$this->primary_key = 'entry_id';
			$this->type        = 'entries';

		}


		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof NF_HubSpot ) ) {
				self::$instance = new WPF_Views_Edit_Entry_Db();

			}

			return self::$instance;
		}

		/**
		 * Get table columns.
		 *
		 * @since 1.0.0
		 * @since 1.5.7 Added an `Entry Notes` column.
		 */
		public function get_columns() {

			return array(
				'entry_id'      => '%d',
				'notes_count'   => '%d',
				'form_id'       => '%d',
				'post_id'       => '%d',
				'user_id'       => '%d',
				'status'        => '%s',
				'type'          => '%s',
				'viewed'        => '%d',
				'starred'       => '%d',
				'fields'        => '%s',
				'meta'          => '%s',
				'date'          => '%s',
				'date_modified' => '%s',
				'ip_address'    => '%s',
				'user_agent'    => '%s',
				'user_uuid'     => '%s',
			);
		}

		public function update( $id, $data = array(), $where = '', $type = '', $args = array() ) {

			return parent::update( $id, $data, $where, $type );
		}

	}

	function WPF_Views_Edit_Entry_Db() {
		return WPF_Views_Edit_Entry_Db::instance();
	}
}
