<?php
class WPF_Views_Field_Url extends WPF_Views_Field {

	public $field_type ='url' ;
	public function get_display_value( $field_value, $_view_field_id, $entry, $_view_settings,$view_Obj ) {
		if ( ! empty( $field_value ) ) {
			$field_value = '<a target="_blank" href="' . esc_url( $field_value ) . '" >' . $field_value . '</a>';
		}

		return $field_value;
	}
}
new WPF_Views_Field_Url();