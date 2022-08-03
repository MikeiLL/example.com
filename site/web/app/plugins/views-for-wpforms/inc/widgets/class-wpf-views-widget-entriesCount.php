<?php
class WPF_Views_Widget_EntriesCount extends WPF_Views_Widget {

	public $widget_type = 'entriesCount' ;


	public function get_widget_html( $html, $_view_field, $_view_settings, $view_Obj ) {
		$fieldSettings = $_view_field->fieldSettings;
		$label = $fieldSettings->useCustomLabel ? $fieldSettings->label : $_view_field->label;
		if ( ! empty( $label ) ) {
			$widgets_html = '<span class="wpforms-view-field-label">' . $label . '</span>';
		}
		$widgets_html .= '<span class="wpforms-view-field-value wpforms-view-field-type-submissionCount-value">';
		$widgets_html .= $view_Obj->submissions_count;
		$widgets_html .= '</span>';

		return $widgets_html;
	}
}
new WPF_Views_Widget_EntriesCount();
