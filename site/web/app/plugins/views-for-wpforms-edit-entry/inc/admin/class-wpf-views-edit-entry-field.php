<?php

/**
 * Hidden text field.
 *
 * @since 1.0.0
 */
class WPF_Views_Edit_Entry_Field extends WPForms_Field {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Define field type information.
		$this->name  = esc_html__( 'Edit Entry Field', 'wpforms-views' );
		$this->type  = 'wpf_edit_entry';
		$this->icon  = 'fa-pencil';
		$this->order = 150;
		$this->group = 'fancy';
	}

	/**
	 * Field options panel inside the builder.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field data and settings.
	 */
	public function field_options( $field ) {
		/*
		 * Basic field options.
		 */

		// Options open markup.
		$args = array(
			'markup' => 'open',
		);
		$this->field_option( 'basic-options', $field, $args );

		// Label.
		$this->field_option( 'label', $field );

		// Set label to disabled.
		$args = array(
			'type'  => 'hidden',
			'slug'  => 'label_disable',
			'value' => '1',
		);
		$this->field_element( 'text', $field, $args );

		// Default value.
		//$this->field_option( 'default_value', $field );

		// Custom CSS classes.
		//$this->field_option( 'css', $field );

		// Options close markup.
		$args = array(
			'markup' => 'close',
		);
		$this->field_option( 'basic-options', $field, $args );
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field data and settings.
	 */
	public function field_preview( $field ) {

		// Label.
		$this->field_preview_option( 'label', $field );

		// Primary input.
		echo '<input type="text" class="primary-input" disabled>';
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field      Field data and settings.
	 * @param array $deprecated Not used any more field attributes.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $deprecated, $form_data ) {

		// Define data.
		$primary = $field['properties']['inputs']['primary'];

		// Primary field.
		printf(
			'<input type="hidden" %s>',
			wpforms_html_attributes( $primary['id'], $primary['class'], $primary['data'], $primary['attr'] )
		);
	}
}

new WPF_Views_Edit_Entry_Field();
