<?php
class WPF_Views_Field {

	public $field_type ;

	public function __construct() {
		$this->add_hooks();
	}


	/**
	 * Add the filter to display values
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	protected function add_hooks() {
		add_filter( "wpf-views/field-value", array( $this, 'get_field_value', ), 10, 6 );

		add_filter( "wpf-views/{$this->field_type}-value", array( $this, 'get_display_value', ), 10, 5 );

		add_filter( "wpf-views/loop-fields", array( $this, 'maybe_add_to_loop_field_list', ), 10, 5 );

	}
	public function get_field_value( $field_value, $_view_field_id, $entry, $_view_settings, $view_Obj ) {
		$_view_field = $_view_settings->fields->{$_view_field_id};
		$field_id = $_view_field->formFieldId;
		if ( $entry ) {
			// Entry field values are in JSON, so we need to decode.
			$entry_fields = json_decode( $entry->fields, true );
			if ( isset( $entry_fields[$field_id ] ) ) {
				$field_value_pre_processed =  $entry_fields[$field_id ]['value'] ;
				$field_value =  apply_filters( 'wpforms_html_field_value', wp_strip_all_tags( $field_value_pre_processed ), $entry_fields[$field_id ], $view_Obj->form_data, 'entry-frontend-table' );
			}

		}
		return $field_value;

	}

	public function get_display_value( $field_value, $_view_field_id, $entry, $_view_settings, $view_Obj ) {
		return $field_value;

	}
	public function maybe_add_to_loop_field_list( $fields ) {

		if ( ! in_array( $this->field_type, $fields ) ) {
			$fields[] = $this->field_type;
		}
		return $fields;
	}

}

new WPF_Views_Field();
