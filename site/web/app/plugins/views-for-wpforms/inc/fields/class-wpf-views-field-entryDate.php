<?php
class WPF_Views_Field_EntryDate extends WPF_Views_Field {

	public $field_type ='entryDate' ;
	public function get_display_value( $field_value, $_view_field_id, $entry, $_view_settings,$view_Obj ) {
		return wpforms_datetime_format( $entry->date, '', true );
	}
}
new WPF_Views_Field_EntryDate();