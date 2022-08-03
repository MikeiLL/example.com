<?php

/**
 * Signature field.
 *
 * @since 1.0.0
 */
class WPForms_Field_Signature extends WPForms_Field {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Define field type information.
		$this->name  = esc_html__( 'Signature', 'wpforms-signatures' );
		$this->type  = 'signature';
		$this->icon  = 'fa-pencil';
		$this->order = 310;
		$this->group = 'fancy';

		// Form frontend javascript.
		add_action( 'wpforms_frontend_js', array( $this, 'frontend_js' ) );

		// Form frontend CSS.
		add_action( 'wpforms_frontend_css', array( $this, 'frontend_css' ) );

		// Admin form builder enqueues.
		add_action( 'wpforms_builder_enqueues', array( $this, 'admin_builder_enqueues' ) );

		// Field styles for Gutenberg.
		add_action( 'enqueue_block_editor_assets', array( $this, 'gutenberg_enqueues' ) );

		// Admin form builder default field settings.
		add_filter( 'wpforms_field_new_default', array( $this, 'admin_builder_defaults' ) );

		// Customize HTML field values.
		add_filter( 'wpforms_html_field_value', array( $this, 'field_html_value' ), 10, 4 );

		// Define additional field properties.
		add_filter( 'wpforms_field_properties_signature', array( $this, 'field_properties' ), 5, 3 );
	}

	/**
	 * Enqueue frontend field js.
	 *
	 * @since 1.0.0
	 *
	 * @param array $forms Forms on the current page.
	 */
	public function frontend_js( $forms ) {

		if (
			wpforms()->frontend->assets_global() ||
			true === wpforms_has_field_type( 'signature', $forms, true )
		) {

			$min = wpforms_get_min_suffix();

			wp_enqueue_script(
				'wpforms-signature_pad',
				plugin_dir_url( __FILE__ ) . 'assets/js/signature_pad.min.js',
				array(),
				'2.3.2',
				true
			);

			wp_enqueue_script(
				'wpforms-signature',
				plugin_dir_url( __FILE__ ) . "assets/js/wpforms-signatures{$min}.js",
				array( 'jquery', 'wpforms-signature_pad' ),
				WPFORMS_SIGNATURES_VERSION,
				true
			);
		}
	}

	/**
	 * Enqueue frontend field CSS.
	 *
	 * @since 1.0.0
	 *
	 * @param array $forms Forms on the current page.
	 */
	public function frontend_css( $forms ) {

		if (
			true === wpforms_has_field_type( 'signature', $forms, true ) ||
			wpforms()->frontend->assets_global()
		) {

			$min = wpforms_get_min_suffix();

			wp_enqueue_style(
				'wpforms-signatures',
				plugin_dir_url( __FILE__ ) . "assets/css/wpforms-signatures{$min}.css",
				array(),
				WPFORMS_SIGNATURES_VERSION
			);
		}
	}

	/**
	 * Admin form builder enqueues.
	 *
	 * @since 1.0.0
	 */
	public function admin_builder_enqueues() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-builder-signatures',
			plugin_dir_url( __FILE__ ) . "assets/css/admin-builder-signatures{$min}.css",
			array(),
			WPFORMS_SIGNATURES_VERSION
		);
	}

	/**
	 * Load enqueues for the Gutenberg editor.
	 *
	 * @since 1.1.3
	 */
	public function gutenberg_enqueues() {

		$min = wpforms_get_min_suffix();

		// CSS.
		wp_enqueue_style(
			'wpforms-signatures',
			plugin_dir_url( __FILE__ ) . "assets/css/wpforms-signatures{$min}.css",
			array(),
			WPFORMS_SIGNATURES_VERSION
		);
	}

	/**
	 * Field defaults when creating new field.
	 *
	 * Default size to large.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Current field settings.
	 *
	 * @return array
	 */
	public function admin_builder_defaults( $field ) {

		if ( 'signature' === $field['type'] && empty( $field['size'] ) ) {
			$field['size'] = 'large';
		}

		return $field;
	}

	/**
	 * Return signature link/image for HTML supported values.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value     Field value.
	 * @param array  $field     Field settings.
	 * @param array  $form_data Form data and settings.
	 * @param string $context   Value display context.
	 *
	 * @return string
	 */
	public function field_html_value( $value, $field, $form_data = array(), $context = '' ) {

		if ( ! empty( $field['value'] ) && 'signature' === $field['type'] ) {

			$value = sanitize_text_field( $field['value'] );

			return sprintf(
				'<a href="%s" rel="noopener noreferrer" target="_blank" style="max-width:500px;display:block;margin:0;"><img src="%s" style="max-width:100%%;display:block;margin:0;"></a>',
				$value,
				$value
			);
		}

		return $value;
	}

	/**
	 * Define additional field properties.
	 *
	 * @since 1.1.0
	 *
	 * @param array $properties Field properties.
	 * @param array $field      Field settings.
	 * @param array $form_data  Form data and settings.
	 *
	 * @return array
	 */
	public function field_properties( $properties, $field, $form_data ) {

		$properties['inputs']['primary']['class'] = array( 'wpforms-signature-input', 'wpforms-screen-reader-element' );

		if ( ! empty( $properties['inputs']['primary']['required'] ) ) {
			$properties['inputs']['primary']['class'][] = 'wpforms-field-required';
		}

		return $properties;
	}

	/**
	 * @inheritdoc
	 */
	public function is_dynamic_population_allowed( $properties, $field ) {

		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function is_fallback_population_allowed( $properties, $field ) {

		return false;
	}

	/**
	 * Field options panel inside the builder.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field settings.
	 */
	public function field_options( $field ) {
		/*
		 * Basic field options.
		 */

		// Options open markup.
		$this->field_option(
			'basic-options',
			$field,
			array(
				'markup' => 'open',
			)
		);

		// Label.
		$this->field_option( 'label', $field );

		// Description.
		$this->field_option( 'description', $field );

		// Required toggle.
		$this->field_option( 'required', $field );

		// Options close markup.
		$this->field_option(
			'basic-options',
			$field,
			array(
				'markup' => 'close',
			)
		);

		/*
		 * Advanced field options.
		 */

		// Options open markup.
		$this->field_option(
			'advanced-options',
			$field,
			array(
				'markup' => 'open',
			)
		);

		// Ink color picker.
		$lbl = $this->field_element(
			'label',
			$field,
			array(
				'slug'    => 'ink_color',
				'value'   => esc_html__( 'Ink Color', 'wpforms-signatures' ),
				'tooltip' => esc_html__( 'Select the color for the signature ink.', 'wpforms-signatures' ),
			),
			false
		);
		$fld = $this->field_element(
			'text',
			$field,
			array(
				'slug'  => 'ink_color',
				'value' => ! empty( $field['ink_color'] ) ? esc_attr( $field['ink_color'] ) : '#000000',
				'class' => 'wpforms-color-picker',
			),
			false
		);
		$this->field_element(
			'row',
			$field,
			array(
				'slug'    => 'ink_color',
				'content' => $lbl . $fld,
				'class'   => 'color-picker-row',
			)
		);

		// Size.
		$this->field_option( 'size', $field );

		// Hide label.
		$this->field_option( 'label_hide', $field );

		// Custom CSS classes.
		$this->field_option( 'css', $field );

		// Options close markup.
		$this->field_option(
			'advanced-options',
			$field,
			array(
				'markup' => 'close',
			)
		);
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field settings.
	 */
	public function field_preview( $field ) {

		// Label.
		$this->field_preview_option( 'label', $field );

		// Signature placeholder.
		echo '<div class="wpforms-signature-wrap"></div>';

		// Description.
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field      Field settings.
	 * @param array $field_atts Deprecated array.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $field_atts, $form_data ) {

		// Define data.
		$primary = $field['properties']['inputs']['primary'];
		$color   = ! empty( $field['ink_color'] ) ? wpforms_sanitize_hex_color( $field['ink_color'] ) : '#000000';
		$size    = ! empty( $field['size'] ) ? ' wpforms-field-' . sanitize_html_class( $field['size'] ) : '';

		// Signature element wrapper.
		printf( '<div class="wpforms-signature-wrap%s">', esc_attr( $size ) );

			// Signature canvas.
			printf(
				'<canvas class="wpforms-signature-canvas" id="wpforms-%d-field_%d-signature" data-color="%s"></canvas>',
				absint( $form_data['id'] ),
				absint( $field['id'] ),
				esc_attr( $color )
			);

			// Clear button to reset canvas.
			printf(
				'<button class="wpforms-signature-clear" title="%s">%s</button>',
				esc_attr__( 'Clear Signature', 'wpforms-signatures' ),
				esc_html__( 'Clear Signature', 'wpforms-signature' )
			);

		echo '</div>';

		// Hidden input that contains dataURL.
		printf(
			'<input type="text" %s %s>',
			wpforms_html_attributes( $primary['id'], $primary['class'], $primary['data'], $primary['attr'] ),
			$primary['required']
		); // WPCS: XSS ok.
	}

	/**
	 * Validate signature on form submit.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $field_id     Field ID.
	 * @param string $field_submit Submitted form field value.
	 * @param array  $form_data    Form data and settings.
	 */
	public function validate( $field_id, $field_submit, $form_data ) {

		$form_id = absint( $form_data['id'] );

		// Basic required check - If field is marked as required, check for entry data.
		if ( ! empty( $form_data['fields'][ $field_id ]['required'] ) && empty( $field_submit ) && '0' !== $field_submit ) {

			wpforms()->process->errors[ $form_id ][ $field_id ] = wpforms_get_required_label();

			return;
		}

		// Simple format check.
		if ( ! empty( $field_submit ) && substr( $field_submit, 0, 22 ) !== 'data:image/png;base64,' ) {

			wpforms()->process->errors[ $form_id ][ $field_id ] = esc_html__( 'Invalid signature image format', 'wpforms-signatures' );

			return;
		}

		// Image check.
		$base = str_replace( 'data:image/png;base64,', '', $field_submit );

		if ( ! empty( $field_submit ) && ! imagecreatefromstring( base64_decode( $base ) ) ) {

			wpforms()->process->errors[ $form_id ][ $field_id ] = esc_html__( 'Invalid signature image format', 'wpforms-signatures' );

			return;
		}
	}

	/**
	 * Format and sanitize field.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $field_id     Field ID.
	 * @param string $field_submit Submitted form data.
	 * @param array  $form_data    Form data and settings.
	 */
	public function format( $field_id, $field_submit, $form_data ) {

		// Define data.
		$name                 = ! empty( $form_data['fields'][ $field_id ]['label'] ) ? sanitize_text_field( $form_data['fields'][ $field_id ]['label'] ) : '';
		$value                = sanitize_text_field( $field_submit );
		$uploads              = wp_upload_dir();
		$form_directory       = absint( $form_data['id'] ) . '-' . md5( $form_data['id'] . $form_data['created'] );
		$wpforms_uploads_root = trailingslashit( $uploads['basedir'] ) . 'wpforms';
		$wpforms_uploads_form = trailingslashit( $wpforms_uploads_root ) . $form_directory;
		$file_name            = sanitize_file_name( esc_html__( 'signature', 'wpforms-signatures' ) . '-' . uniqid() . '.png' );
		$file_new             = trailingslashit( $wpforms_uploads_form ) . $file_name;
		$file_url             = trailingslashit( $uploads['baseurl'] ) . 'wpforms/' . trailingslashit( $form_directory ) . $file_name;

		// Double check we have a image passed.
		if ( ! empty( $value ) && substr( $value, 0, 22 ) === 'data:image/png;base64,' ) {

			// Check for form upload directory destination.
			if ( ! file_exists( $wpforms_uploads_form ) ) {
				wp_mkdir_p( $wpforms_uploads_form );
			}

			// Check if the index.html exists in the root uploads director, if not create it.
			if ( ! file_exists( trailingslashit( $wpforms_uploads_root ) . 'index.html' ) ) {
				file_put_contents( trailingslashit( $wpforms_uploads_root ) . 'index.html', '' );
			}

			// Check if the index.html exists in the form uploads director, if not create it.
			if ( ! file_exists( trailingslashit( $wpforms_uploads_form ) . 'index.html' ) ) {
				file_put_contents( trailingslashit( $wpforms_uploads_form ) . 'index.html', '' );
			}

			// Compile image data.
			$data = base64_decode( preg_replace( '#^data:image/\w+;base64,#i', '', $value ) );

			// Save image.
			$save_signature = file_put_contents( $file_new, $data );

			if ( false === $save_signature ) {

				$value = '';

				wpforms_log(
					esc_html__( 'Upload Error, could not upload signature', 'wpforms_signature' ),
					$file_url,
					array(
						'type'    => array(
							'entry',
							'error',
						),
						'form_id' => $form_data['id'],
					)
				);

			} else {

				// Everything's done, so we provide the URL to the image.
				$value = $file_url;

				// Set correct file permissions.
				$stat  = stat( dirname( $file_new ) );
				$perms = $stat['mode'] & 0000666;
				@ chmod( $file_new, $perms );
			}
		} else {

			$value = '';
		}

		wpforms()->process->fields[ $field_id ] = array(
			'name'  => $name,
			'value' => $value,
			'id'    => absint( $field_id ),
			'type'  => $this->type,
		);
	}
}

new WPForms_Field_Signature();
