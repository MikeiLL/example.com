<?php


class WPForms_Views_Common {

	public static function get_entry_table_name() {
		global $wpdb;

		return $wpdb->prefix . 'wpforms_entries';
	}

	public static function get_entry_fields_table_name() {
		global $wpdb;

		return $wpdb->prefix . 'wpforms_entry_fields';
	}

	/**
	 * Gets the lead (entry) notes table name, including the site's database prefix
	 *
	 * @access public
	 * @static
	 * @global $wpdb
	 *
	 * @return string The lead (entry) notes table name
	 */
	public static function get_entry_meta_table_name() {
		global $wpdb;

		return $wpdb->prefix . 'wpforms_entry_meta';
	}

	public static function get_mysql_date_string( $date_format ) {
		switch ( $date_format ) {
		case'm/d/Y':
			$date_string = '%m/%d/%Y';
			break;
		case'd/m/Y':
			$date_string = '%d/%m/%Y';
			break;
		case'F j, Y':
			$date_string = '%M %d, %Y';
			break;
		}
		return $date_string;
	}

	public static function get_mysql_time_string( $time_format ) {
		switch ( $time_format ) {
		case'g:i A':
			$time_string = '%l:%i %p';
			break;
		case'H:i':
			$time_string = '%H:%i';
			break;
		}
		return $time_string;
	}


}