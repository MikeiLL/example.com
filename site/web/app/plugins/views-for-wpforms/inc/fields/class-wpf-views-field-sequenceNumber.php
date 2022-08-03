<?php
class WPF_Views_Field_SequenceNumber extends WPF_Views_Field {

	public $field_type ='sequenceNumber' ;
	public function get_display_value( $field_value, $_view_field_id, $entry, $_view_settings,$view_Obj ) {
		return $view_Obj->seq_no;
	}
}
new WPF_Views_Field_SequenceNumber();