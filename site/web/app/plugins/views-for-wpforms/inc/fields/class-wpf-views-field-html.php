<?php
class WPF_Views_Field_Html extends WPF_Views_Field {

	public $field_type ='html' ;
	public function get_display_value( $field_value, $_view_field_id, $entry, $_view_settings,$view_Obj ) {
		$_view_field = $_view_settings->fields->{$_view_field_id};
		$_view_fieldSettings = $_view_field->fieldSettings;
		$entry_fields = json_decode( $entry->fields, true );

		$widgets_html = do_shortcode( $_view_fieldSettings->html );
		return apply_filters( 'wpforms_process_smart_tags', $widgets_html, $view_Obj->form_data, $entry_fields, $entry->entry_id );
	}
}
new WPF_Views_Field_Html();