<?php
class WPF_Views_Field_File_Upload extends WPF_Views_Field {

	public $field_type = 'file-upload' ;

	public function get_display_value( $field_value, $_view_field_id, $entry, $_view_settings,$view_Obj ) {
		$_view_field = $_view_settings->fields->{$_view_field_id};
		$_view_fieldSettings = $_view_field->fieldSettings;

		$entry_fields = json_decode( $entry->fields, true );
		$field_id = $_view_field->formFieldId;


		if ( isset( $_view_fieldSettings->displayFileType ) && $_view_fieldSettings->displayFileType == 'Image' ) {
			$field_value = '<img class="wpforms-view-img" src="' . wp_strip_all_tags( $entry_fields[$field_id ]['value'] ) . '">';
		}else {
			// Process modern uploader.
			if ( ! empty( $entry_fields[$field_id ]['value_raw']  ) ) {
				$field_value = wpforms_chain(  $entry_fields[$field_id ]['value_raw'] )
				->map(
					static function ( $file ) {

						if ( empty( $file['value'] ) || empty( $file['file_original'] ) ) {
							return '';
						}

						return sprintf(
							'<a href="%s" rel="noopener noreferrer" target="_blank">%s</a>',
							esc_url( $file['value'] ),
							esc_html( $file['file_original'] )
						);
					}
				)
				->array_filter()
				->implode( '<br>' )
				->value();
			}else {
				// Process classic uploader.
				$field_value = sprintf(
					'<a href="%s" rel="noopener" target="_blank">%s</a>',
					esc_url( $entry_fields[$field_id ]['value'] ),
					esc_html( $entry_fields[$field_id ]['file_original'] )
				);
			}
		}

		return $field_value;
	}

}
new WPF_Views_Field_File_Upload();
