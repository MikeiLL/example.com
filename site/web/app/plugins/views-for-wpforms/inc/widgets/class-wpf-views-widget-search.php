<?php
class WPF_Views_Widget_Search extends WPF_Views_Widget {

	public $widget_type = 'search' ;


	public function get_widget_html( $html, $_view_field, $_view_settings, $view_Obj ) {


		$search_form_layout = $_view_field->fieldSettings->search->layout;
		$clearButton = $_view_field->fieldSettings->search->clearButton;
		$html = '<form action="" method="get" class="wpforms-view-search-form ' . $search_form_layout . '">';
		if ( ! empty( $_view_field->fieldSettings->search->fields ) ) {
			$search_fields = $_view_field->fieldSettings->search->fields;
			$form_fields = wpforms_get_form_fields( $_view_settings->formId );
			foreach ( $search_fields as $search_field ) {
				$html .= '<div class="search-form-field">';
				$html .= '<div><label>' . $search_field->label . '</label></div>';

				if ( isset( $form_fields[$search_field->fieldId]['type'] ) ) {
					$field_type = $form_fields[$search_field->fieldId]['type'] ;
				}else {
					$field_type = $search_field->fieldId;
				}
				//echo $field_type . '=====' . $field_type . '<br/>';
				// echo '<pre>';
				// print_r( $form_fields );


				switch ( $field_type ) {
				case 'textbox':
				case 'text':
				case 'email':
				case 'address':
				case 'name':
				case 'date':
				case 'phone':
				case 'hidden':
				case 'number':
				case 'starrating':
				case 'submission_id':
				case 'textarea':
				case 'all_fields':
					$value = '';
					// check if user has searched already then prefill search field
					if ( isset( $_GET['search_fields'] ) && ! empty( $_GET['search_fields'][$search_field->fieldId] ) ) {
						$value = $_GET['search_fields'][$search_field->fieldId];
					}
					$html .= '<input type="text" value="' . esc_attr( $value ) . '" name="search_fields[' . $search_field->fieldId . ']" />';
					break;

				case 'checkbox':
				case 'radio':
				case 'multiselect':
				case 'select':
				case 'state':

					$options = $form_fields[$search_field->fieldId]['choices'];

					$searched_value = '';
					if ( isset( $_GET['search_fields'] ) && ! empty( $_GET['search_fields'][$search_field->fieldId] ) ) {
						$searched_value = $_GET['search_fields'][$search_field->fieldId];
					}

					$html .= '<select name="search_fields[' . $search_field->fieldId . ']" >';
					$html .= '<option value="">All</option>';
					foreach ( $options as $option ) {
						$selected = selected( $option['label'], $searched_value, false );
						$html .= '<option ' . $selected . ' value="' . $option['label'] . '">' . $option['label'] . '</option>';
					}
					$html .= '</select>';
					//echo '<pre>';print_r( $model->get_settings() );
					break;
				}
				$html .= '</div>';

			}

		}
		//die;
		$html .= '<input type="submit" value="' . __( 'Search', 'wpforms-views' ) . '">';

		if ( ! empty( $clearButton ) ) {
			$html .= '<button onClick="this.form.reset();return false;" class="view-clr-btn">Clear</button>';
		}
		$html .= '</form>';
		return $html;
	}
}
new WPF_Views_Widget_Search();
