<?php

class WPF_Views_Single_Entry_View {
	public $entry;
	public $form_data;
	public function __construct() {
		add_action( 'init', array( $this, 'wpf_add_rewrite_endpoint'  ), 10 );

		add_filter( 'wpf_views_single_entry_content' , array( $this, 'single_entry_content'  ), 10, 3 );
	}

	function single_entry_content( $content, $entry_id, $view_settings ) {
		$this->form = wpforms()->form->get( absint( $view_settings->formId ) );
		// If the form doesn't exists, abort.
		if ( empty( $this->form ) ) {
			return $content;
		}

		// Pull and format the form data out of the form object.
		$this->form_data = ! empty( $this->form->post_content ) ? wpforms_decode( $this->form->post_content ) : '';

		$entry = wpforms()->entry->get( absint( $entry_id ) );
		$single_loop_rows = $view_settings->sections->singleloop->rows;
		if ( ! empty( $entry ) ) {
			$this->entry = $entry;
			$go_back_text = ! empty( $view_settings->viewSettings->singleEntry->backLinkLabel ) ? $view_settings->viewSettings->singleEntry->backLinkLabel : 'Go back';
			$content .= '<div class="single-entry-view-cont">';
			$content .= '<div class="single-entry-go-back-cont"><a href="' . get_the_permalink() . '">' . $go_back_text . '</a></div>';
			foreach ( $single_loop_rows as $row_id ) {
				$content .= $this->get_grid_row_html( $row_id, $view_settings );
			}
			$content .= '</div>';
		}
		return $content;
	}

	function get_grid_row_html( $row_id, $view_settings ) {
		$row_columns = $view_settings->rows->{$row_id}->cols;

		$row_content = '<div class="pure-g wpforms-view-row">';
		foreach ( $row_columns as $column_id ) {
			$row_content .= $this->get_grid_column_html( $column_id, $view_settings );
		}
		$row_content .= '</div>'; // row ends
		return $row_content;
	}

	function get_grid_column_html( $column_id, $view_settings ) {
		$column_size = $view_settings->columns->{$column_id}->size;
		$column_fields = $view_settings->columns->{$column_id}->fields;

		$column_content = '<div class=" wpforms-view-col pure-u-1 pure-u-md-' . $column_size . '">';


		foreach ( $column_fields as $field_id ) {

			$column_content .= $this->get_field_html( $field_id, $view_settings );

		}
		$column_content .= '</div>'; // column ends
		return $column_content;
	}

	function get_field_html( $field_id, $view_settings ) {
		$entry = $this->entry;
		$field = $view_settings->fields->{$field_id};
		$form_field_id = $field->formFieldId;
		$fieldSettings = $field->fieldSettings;
		$label = $fieldSettings->useCustomLabel ? $fieldSettings->label : $field->label;
		$class = $fieldSettings->customClass;
		$field_html = '';

		$field_html .= '<div class="wpforms-view-field-cont  field-' . $form_field_id . ' ' . $class . '">';

		// Entry field values are in JSON, so we need to decode.
		$entry_fields = json_decode( $entry->fields, true );

		$form_field_type = isset( $entry_fields[$form_field_id ] ) ? $entry_fields[$form_field_id ]['type']: $form_field_id;
		if ( ! empty( $label ) ) {
			$field_html .= '<div class="wpforms-view-field-label">' . $label . '</div>';
		}
		$field_html .= '<div class="wpforms-view-field-value wpforms-view-field-type-' . $form_field_type . '-value">';

		$field_value = apply_filters( "wpf-views/field-value", '', $field_id, $entry, $view_settings, $this );

		$field_value = apply_filters( "wpf-views/{$form_field_type}-value", $field_value, $field_id, $entry, $view_settings, $this );

		$field_html .= $field_value;

		$field_html .= '</div>';


		$field_html .= '</div>';

		return $field_html;
	}

	function wpf_add_rewrite_endpoint() {
		global $wp_rewrite;

		$endpoint = 'entry';
		if ( in_array( array( EP_PERMALINK | EP_PERMALINK | EP_ROOT, $endpoint, $endpoint ), $wp_rewrite->endpoints ) ) {
			return;
		}
		add_rewrite_endpoint( $endpoint, EP_PAGES | EP_PERMALINK | EP_ROOT );
		$wp_rewrite->flush_rules();
	}

}

new WPF_Views_Single_Entry_View();
