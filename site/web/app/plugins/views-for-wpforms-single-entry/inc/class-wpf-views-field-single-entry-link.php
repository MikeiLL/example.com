<?php

if ( class_exists( 'WPF_Views_Field' ) ) {

	class WPF_Views_Field_SingleEntryLink extends WPF_Views_Field {

		public $field_type = 'singleEntryLink' ;

		public function get_display_value( $field_value, $_view_field_id, $entry, $_view_settings, $view_Obj ) {
			$_view_field = $_view_settings->fields->{$_view_field_id};
			$permalink = get_the_permalink();
			$singleEntryLink = $permalink . 'entry/' . $entry->entry_id;
			$link_text = isset( $_view_field->fieldSettings->linkText ) ? $_view_field->fieldSettings->linkText : 'Single Entry Link';
			return '<a href="' . esc_url_raw( $singleEntryLink ) . '"  class="' . $_view_field->fieldSettings->customClass . '">' . $link_text . '</a>';
		}
	}
	new WPF_Views_Field_SingleEntryLink();
}
