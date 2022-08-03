<?php
class WPF_Views_Field_Email extends WPF_Views_Field {

	public $field_type ='email' ;

	public function get_display_value( $field_value, $_view_field_id, $entry, $_view_settings,$view_Obj ) {
			$_view_field = $_view_settings->fields->{$_view_field_id};
			if ( ! empty( $_view_field->fieldSettings->emailClickable ) ) {
			$params = array();
			$href = 'mailto:'.$field_value;
			// check if it has default subject
			if ( ! empty( $_view_field->fieldSettings->emailSubject ) ) {
				$subject = wp_strip_all_tags( trim( do_shortcode( $_view_field->fieldSettings->emailSubject  ) ) );
				$params[] = 'subject='.str_replace('+', '%20', urlencode( $subject ) );
			}
			// check if it has default email body
			if ( ! empty( $_view_field->fieldSettings->emailBody ) ) {
				$body = wp_strip_all_tags( trim( do_shortcode( $_view_field->fieldSettings->emailBody ) ) );

				$params[] = 'body='.str_replace('+', '%20', urlencode( $body ) );
			}
			if( !empty( $params) ) {
				$href .= '?'.implode( '&', $params );
			}

			$field_value = '<a target="_blank" href="' . esc_url( $href ) . '" >' . $field_value . '</a>';
		}

		return $field_value;
	}

}
new WPF_Views_Field_Email();