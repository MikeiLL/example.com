<?php
class WPF_Views_Field_EntryId extends WPF_Views_Field {

	public $field_type ='entryId' ;
	public function get_display_value( $field_value, $_view_field_id, $entry, $_view_settings,$view_Obj ) {
		return $entry->entry_id;
	}
}
new WPF_Views_Field_EntryId();