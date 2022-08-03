<?php
/**
 * Settings API.
 *
 * @since 1.3.7
 */

/**
 * Settings output wrapper.
 *
 * @since 1.3.9
 *
 *
 * @param array   $args
 * @return string
 */
function wpforms_views_settings_output_field( $args ) {

	// Define default callback for this field type.
	$callback = ! empty( $args['type'] ) && function_exists( 'wpforms_views_settings_' . $args['type'] . '_callback' ) ? 'wpforms_views_settings_' . $args['type'] . '_callback' : 'wpforms_views_settings_missing_callback';

	// Allow custom callback to be provided via arg.
	if ( ! empty( $args['callback'] ) && function_exists( $args['callback'] ) ) {
		$callback = $args['callback'];
	}

	// Store returned markup from callback.
	$field = call_user_func( $callback, $args );

	// Allow arg to bypass standard field wrap for custom display.
	if ( ! empty( $args['wrap'] ) ) {
		return $field;
	}

	// Custom row classes.
	$class = ! empty( $args['class'] ) ? wpforms_views_sanitize_classes( (array) $args['class'], true ) : '';

	// Allow hiding blocks on page load (useful for JS toggles).
	$display_none = ! empty( $args['is_hidden'] ) ? 'style="display:none;"' : '';

	// Build standard field markup and return.
	$output = '<div class="wpforms-views-setting-row wpforms-views-setting-row-' . sanitize_html_class( $args['type'] ) . ' wpforms-views-clear ' . $class . '" id="wpforms-views-setting-row-' . wpforms_views_sanitize_key( $args['id'] ) . '" ' . $display_none . '>';

	if ( ! empty( $args['name'] ) && empty( $args['no_label'] ) ) {
		$output .= '<span class="wpforms-views-setting-label">';
		$output .= '<label for="wpforms-views-setting-' . wpforms_views_sanitize_key( $args['id'] ) . '">' . esc_html( $args['name'] ) . '</label>';
		$output .= '</span>';
	}

	$output .= '<span class="wpforms-views-setting-field">';
	$output .= $field;
	if ( ! empty( $args['desc_after'] ) ) {
		$output .= '<div class="wpforms-views-clear">' . $args['desc_after'] . '</div>';
	}
	$output .= '</span>';

	$output .= '</div>';

	return $output;
}

/**
 * Missing Callback.
 *
 * If a function is missing for settings callbacks alert the user.
 *
 * @since 1.3.9
 *
 *
 * @param array   $args Arguments passed by the setting.
 * @return string
 */
function wpforms_views_settings_missing_callback( $args ) {

	return sprintf(
		/* translators: %s - ID of a setting. */
		esc_html__( 'The callback function used for the %s setting is missing.', 'wpforms-lite' ),
		'<strong>' . wpforms_views_sanitize_key( $args['id'] ) . '</strong>'
	);
}

/**
 * Settings content field callback.
 *
 * @since 1.3.9
 *
 *
 * @param array   $args
 * @return string
 */
function wpforms_views_settings_content_callback( $args ) {
	return ! empty( $args['content'] ) ? $args['content'] : '';
}

function wpforms_views_settings_license_callback( $args ) {
	$old_setting = get_option( 'wpforms_views_pro_license' );
	if ( ! empty( $old_setting ) ) {
		$settings = get_option( 'wpforms_views_settings', array() );
		foreach ( $old_setting as $name => $license_key ) {
			$settings[ $name ] = $license_key;
		}

		update_option( 'wpforms_views_settings', $settings );
		// delete old settings
		//delete_option( 'wpforms_views_pro_license' );
	}

	$default = isset( $args['default'] ) ? esc_html( $args['default'] ) : '';
	$value   = wpforms_views_setting( $args['id'], $default );
	$id      = wpforms_views_sanitize_key( $args['id'] );
	$status = get_option( 'wpforms_views_' . $id . '_status' );

	$output = '<input type="text" id="wpforms-views-setting-' . $id . '" name="' . $id . '" value="' . esc_attr( $value ) . '">';
	if (  $status == 'valid' ) {
		$output .= '<span style="color:green;vertical-align:middle" class="dashicons dashicons-yes-alt"></span><span>Active</span>';
	} else {
		$output .= '<span style="color:red;vertical-align:middle" class="dashicons dashicons-no-alt"></span><span >InActive</span>';
		$status_message = get_option( 'wpforms_views_' . $id . '_status_message' );
		if ( ! empty( $status_message ) ) {
			$output .= '<p class="desc">' . wp_kses_post( $status_message ) . '</p>';
		}
	}


	if ( ! empty( $args['desc'] ) ) {
		$output .= '<p class="desc">' . wp_kses_post( $args['desc'] ) . '</p>';
	}

	return $output;

}





/**
 * Settings text input field callback.
 *
 *
 *
 * @param array   $args
 * @return string
 */
function wpforms_views_settings_text_callback( $args ) {

	$default = isset( $args['default'] ) ? esc_html( $args['default'] ) : '';
	$value   = wpforms_views_setting( $args['id'], $default );
	$id      = wpforms_views_sanitize_key( $args['id'] );

	$output = '<input type="text" id="wpforms-views-setting-' . $id . '" name="' . $id . '" value="' . esc_attr( $value ) . '">';

	if ( ! empty( $args['desc'] ) ) {
		$output .= '<p class="desc">' . wp_kses_post( $args['desc'] ) . '</p>';
	}

	return $output;
}

/**
 * Settings number input field callback.
 *
 *
 * @param array   $args Setting field arguments.
 * @return string
 */
function wpforms_views_settings_number_callback( $args ) {

	$default = isset( $args['default'] ) ? esc_html( $args['default'] ) : '';
	$id      = 'wpforms-views-setting-' . wpforms_views_sanitize_key( $args['id'] );
	$attr    =  array(
		'value' => wpforms_views_setting( $args['id'], $default ),
		'name'  => wpforms_views_sanitize_key( $args['id'] ),
	);
	$data    = ! empty( $args['data'] ) ? $args['data'] : array();

	if ( ! empty( $args['attr'] ) ) {
		$attr = array_merge( $attr, $args['attr'] );
	}

	$output = sprintf(
		'<input type="number" %s>',
		wpforms_views_html_attributes( $id, array(), $data, $attr )
	);

	if ( ! empty( $args['desc'] ) ) {
		$output .= '<p class="desc">' . wp_kses_post( $args['desc'] ) . '</p>';
	}

	return $output;
}

/**
 * Settings select field callback.
 *
 * @since 1.3.9
 *
 *
 * @param array   $args
 * @return string
 */
function wpforms_views_settings_select_callback( $args ) {

	$default     = isset( $args['default'] ) ? esc_html( $args['default'] ) : '';
	$value       = wpforms_views_setting( $args['id'], $default );
	$id          = wpforms_views_sanitize_key( $args['id'] );
	$select_name = $id;
	$class       = ! empty( $args['choicesjs'] ) ? 'choicesjs-select' : '';
	$choices     = ! empty( $args['choicesjs'] ) ? true : false;
	$data        = isset( $args['data'] ) ? (array) $args['data'] : array();
	$attr        = isset( $args['attr'] ) ? (array) $args['attr'] : array();

	if ( $choices && ! empty( $args['search'] ) ) {
		$data['search'] = 'true';
	}

	if ( ! empty( $args['placeholder'] ) ) {
		$data['placeholder'] = $args['placeholder'];
	}

	if ( $choices && ! empty( $args['multiple'] ) ) {
		$attr[]      = 'multiple';
		$select_name = $id . '[]';
	}

	foreach ( $data as $name => $val ) {
		$data[ $name ] = 'data-' . sanitize_html_class( $name ) . '="' . esc_attr( $val ) . '"';
	}

	$data = implode( ' ', $data );
	$attr = implode( ' ', array_map( 'sanitize_html_class', $attr ) );

	$output  = $choices ? '<span class="choicesjs-select-wrap">' : '';
	$output .= '<select id="wpforms-views-setting-' . $id . '" name="' . $select_name . '" class="' . $class . '"' . $data . $attr . '>';

	foreach ( $args['options'] as $option => $name ) {
		if ( empty( $args['selected'] ) ) {
			$selected = selected( $value, $option, false );
		} else {
			$selected = is_array( $args['selected'] ) && in_array( $option, $args['selected'], true ) ? 'selected' : '';
		}
		$output .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
	}

	$output .= '</select>';
	$output .= $choices ? '</span>' : '';

	if ( ! empty( $args['desc'] ) ) {
		$output .= '<p class="desc">' . wp_kses_post( $args['desc'] ) . '</p>';
	}

	return $output;
}


/**
 * Settings checkbox field callback.
 *
 * @since 1.3.9
 *
 *
 * @param array   $args
 * @return string
 */
function wpforms_views_settings_checkbox_callback( $args ) {

	$value   = wpforms_views_setting( $args['id'] );
	$id      = wpforms_views_sanitize_key( $args['id'] );
	$checked = ! empty( $value ) ? checked( 1, $value, false ) : '';

	$output = '<input type="checkbox" id="wpforms-views-setting-' . $id . '" name="' . $id . '" ' . $checked . '>';

	if ( ! empty( $args['desc'] ) ) {
		$output .= '<p class="desc">' . wp_kses_post( $args['desc'] ) . '</p>';
	}

	return $output;
}

/**
 * Settings radio field callback.
 *
 * @since 1.3.9
 *
 *
 * @param array   $args
 * @return string
 */
function wpforms_views_settings_radio_callback( $args ) {

	$default = isset( $args['default'] ) ? esc_html( $args['default'] ) : '';
	$value   = wpforms_views_setting( $args['id'], $default );
	$id      = wpforms_views_sanitize_key( $args['id'] );
	$output  = '';
	$x       = 1;

	foreach ( $args['options'] as $option => $name ) {

		$checked = checked( $value, $option, false );
		$output .= '<input type="radio" id="wpforms-views-setting-' . $id . '[' . $x . ']" name="' . $id . '" value="' . esc_attr( $option ) . '" ' . $checked . '>';
		$output .= '<label for="wpforms-views-setting-' . $id . '[' . $x . ']" class="option-' . sanitize_html_class( $option ) . '">';
		$output .= esc_html( $name );
		$output .= '</label>';
		$x ++;
	}

	if ( ! empty( $args['desc'] ) ) {
		$output .= '<p class="desc">' . wp_kses_post( $args['desc'] ) . '</p>';
	}

	return $output;
}





/**
 * Settings field columns callback.
 *
 * @since 1.5.8
 *
 *
 * @param array   $args Arguments passed by the setting.
 * @return string
 */
function wpforms_views_settings_columns_callback( $args ) {

	if ( empty( $args['columns'] ) || ! is_array( $args['columns'] ) ) {
		return '';
	}

	$output = '<div class="wpforms-views-setting-columns">';

	foreach ( $args['columns'] as $column ) {

		// Define default callback for this field type.
		$callback = ! empty( $column['type'] ) ? 'wpforms_views_settings_' . $column['type'] . '_callback' : '';

		// Allow custom callback to be provided via arg.
		if ( ! empty( $column['callback'] ) ) {
			$callback = $column['callback'];
		}

		$output .= '<div class="wpforms-views-setting-column">';

		if ( ! empty( $column['name'] ) ) {
			$output .= '<label><b>' . wp_kses_post( $column['name'] ) . '</b></label>';
		}

		if ( function_exists( $callback ) ) {
			$output .= call_user_func( $callback, $column );
		}

		$output .= '</div>';
	}

	$output .= '</div>';

	return $output;
}
