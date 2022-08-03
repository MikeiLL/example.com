<?php

namespace WPFormsStripe\Fields;

use WPFormsStripe\Helpers;

/**
 * Name text field.
 *
 * @since 2.3.0
 */
class StripeCreditCard extends \WPForms_Field {

	/**
	 * Primary class constructor.
	 *
	 * @since 2.3.0
	 */
	public function init() {

		// Define field type information.
		$this->name  = \esc_html__( 'Stripe Credit Card', 'wpforms' );
		$this->type  = 'stripe-credit-card';
		$this->icon  = 'fa-credit-card';
		$this->order = 90;
		$this->group = 'payment';

		// Define additional field properties.
		\add_filter( 'wpforms_field_properties_stripe-credit-card', array( $this, 'field_properties' ), 5, 3 );

		// Set field to required by default.
		\add_filter( 'wpforms_field_new_required', array( $this, 'default_required' ), 10, 2 );

		\add_action( 'wpforms_builder_enqueues', array( $this, 'builder_enqueues' ) );
		\add_filter( 'wpforms_builder_strings', array( $this, 'builder_js_strings' ) );
		\add_filter( 'wpforms_builder_field_button_attributes', array( $this, 'field_button_attributes' ), 10, 3 );
	}

	/**
	 * Define additional field properties.
	 *
	 * @since 2.3.0
	 *
	 * @param array $properties Field properties.
	 * @param array $field      Field settings.
	 * @param array $form_data  Form data and settings.
	 *
	 * @return array
	 */
	public function field_properties( $properties, $field, $form_data ) {

		// Remove primary for expanded formats since we have first, middle, last.
		unset( $properties['inputs']['primary'] );

		$form_id  = \absint( $form_data['id'] );
		$field_id = \absint( $field['id'] );

		$props = array(
			'inputs' => array(
				'number' => array(
					'attr'     => array(
						'name'  => '',
						'value' => '',
					),
					'block'    => array(
						'wpforms-field-stripe-credit-card-number',
					),
					'class'    => array(
						'wpforms-field-stripe-credit-card-cardnumber',
					),
					'data'     => array(),
					'id'       => "wpforms-{$form_id}-field_{$field_id}",
					'required' => ! empty( $field['required'] ) ? 'required' : '',
					'sublabel' => array(
						'hidden'   => ! empty( $field['sublabel_hide'] ),
						'value'    => \esc_html__( 'Card', 'wpforms' ),
						'position' => 'after',
					),
				),
				'name'   => array(
					'attr'     => array(
						'name'        => 'wpforms[stripe-credit-card-cardname]',
						'value'       => '',
						'placeholder' => ! empty( $field['cardname_placeholder'] ) ? $field['cardname_placeholder'] : '',
					),
					'block'    => array(
						'wpforms-field-stripe-credit-card-name',
					),
					'class'    => array(
						'wpforms-field-stripe-credit-card-cardname',
					),
					'data'     => array(),
					'id'       => "wpforms-{$form_id}-field_{$field_id}-cardname",
					'required' => ! empty( $field['required'] ) ? 'required' : '',
					'sublabel' => array(
						'hidden'   => ! empty( $field['sublabel_hide'] ),
						'value'    => \esc_html__( 'Name on Card', 'wpforms' ),
						'position' => 'after',
					),
				),
			),
		);

		$properties = \array_merge_recursive( $properties, $props );

		// If this field is required we need to make some adjustments.
		if ( ! empty( $field['required'] ) ) {

			// Add required class if needed (for multi-page validation).
			$properties['inputs']['number']['class'][] = 'wpforms-field-required';
			$properties['inputs']['name']['class'][]   = 'wpforms-field-required';
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
	 * Default to required.
	 *
	 * @since 2.3.0
	 *
	 * @param bool  $required Required status, true is required.
	 * @param array $field    Field settings.
	 *
	 * @return bool
	 */
	public function default_required( $required, $field ) {

		if ( 'stripe-credit-card' === $field['type'] ) {
			return true;
		}

		return $required;
	}

	/**
	 * Enqueue assets for the builder.
	 *
	 * @since 2.3.0
	 */
	public function builder_enqueues() {

		$min = \wpforms_get_min_suffix();

		\wp_enqueue_script(
			'wpforms-builder-stripe-card-field',
			wpforms_stripe()->url . "assets/js/admin-builder-stripe-card-field{$min}.js",
			array( 'jquery' ),
			\WPFORMS_STRIPE_VERSION,
			false
		);

		\wp_localize_script(
			'wpforms-builder-stripe-card-field',
			'wpforms_builder_stripe_card_field',
			array( 'field_slug' => \wpforms_stripe()->api->get_config( 'field_slug' ) )
		);
	}

	/**
	 * Add our localized strings to be available in the form builder.
	 *
	 * @since 2.3.0
	 *
	 * @param array $strings Form builder JS strings.
	 *
	 * @return array
	 */
	public function builder_js_strings( $strings ) {

		$strings['stripe_ajax_required'] = \wp_kses(
			__( '<p>AJAX form submissions are required when using the Stripe Credit Card field.</p><p>To proceed, please go to <strong>Settings » General</strong> and check <strong>Enable AJAX form submission</strong>.</p>', 'wpforms-stripe' ),
			array(
				'p'      => array(),
				'strong' => array(),
			)
		);

		$strings['stripe_keys_required'] = \wp_kses(
			__( '<p>Stripe account connection is required when using the Stripe Credit Card field.</p><p>To proceed, please go to <strong>WPForms Settings » Payments » Stripe</strong> and press <strong>Connect with Stripe</strong> button.</p>', 'wpforms-stripe' ),
			array(
				'p'      => array(),
				'strong' => array(),
			)
		);

		$strings['payments_enabled_required'] = \wp_kses(
			__( '<p>Stripe Payments must be enabled when using the Stripe Credit Card field.</p><p>To proceed, please go to <strong>Payments » Stripe</strong> and check <strong>Enable Stripe payments</strong>.</p>', 'wpforms-stripe' ),
			array(
				'p'      => array(),
				'strong' => array(),
			)
		);

		return $strings;
	}

	/**
	 * Define additional "Add Field" button attributes.
	 *
	 * @since 2.3.0
	 *
	 * @param array $attributes "Add Field" button attributes.
	 * @param array $field      Field settings.
	 * @param array $form_data  Form data and settings.
	 *
	 * @return array
	 */
	public function field_button_attributes( $attributes, $field, $form_data ) {

		if ( \wpforms_stripe()->api->get_config( 'field_slug' ) !== $field['type'] ) {
			return $attributes;
		}

		if ( Helpers::has_stripe_field( $form_data ) ) {
			$attributes['atts']['disabled'] = 'true';
			return $attributes;
		}

		if ( ! Helpers::has_stripe_keys() ) {
			$attributes['class'][] = 'warning-modal';
			$attributes['class'][] = 'stripe-keys-required';
		}

		return $attributes;
	}

	/**
	 * Field options panel inside the builder.
	 *
	 * @since 2.3.0
	 *
	 * @param array $field Field settings.
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

		// Description.
		$this->field_option( 'description', $field );

		// Required toggle.
		$this->field_option( 'required', $field );

		// Options close markup.
		$args = array(
			'markup' => 'close',
		);
		$this->field_option( 'basic-options', $field, $args );

		/*
		 * Advanced field options.
		 */

		// Options open markup.
		$args = array(
			'markup' => 'open',
		);
		$this->field_option( 'advanced-options', $field, $args );

		// Size.
		$this->field_option( 'size', $field );

		// Card Name.
		$cardname_placeholder = ! empty( $field['cardname_placeholder'] ) ? \esc_attr( $field['cardname_placeholder'] ) : '';
		\printf( '<div class="wpforms-clear wpforms-field-option-row wpforms-field-option-row-cardname" id="wpforms-field-option-row-%d-cardname" data-subfield="cardname" data-field-id="%d">', \absint( $field['id'] ), \absint( $field['id'] ) );
			$this->field_element(
				'label',
				$field,
				array(
					'slug'  => 'cardname_placeholder',
					'value' => \esc_html__( 'Name on Card Placeholder Text', 'wpforms' ),
				)
			);
			echo '<div class="placeholder">';
				\printf( '<input type="text" class="placeholder-update" id="wpforms-field-option-%d-cardname_placeholder" name="fields[%d][cardname_placeholder]" value="%s" data-field-id="%d" data-subfield="stripe-credit-card-cardname">', \absint( $field['id'] ), \absint( $field['id'] ), \esc_html( $cardname_placeholder ), \absint( $field['id'] ) );
			echo '</div>';
		echo '</div>';

		// Hide Label.
		$this->field_option( 'label_hide', $field );

		// Hide sub-labels.
		$this->field_option( 'sublabel_hide', $field );

		// Custom CSS classes.
		$this->field_option( 'css', $field );

		// Options close markup.
		$args = array(
			'markup' => 'close',
		);
		$this->field_option( 'advanced-options', $field, $args );
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 2.3.0
	 *
	 * @param array $field Field settings.
	 */
	public function field_preview( $field ) {

		// Define data.
		$name_placeholder = ! empty( $field['cardname_placeholder'] ) ? \esc_attr( $field['cardname_placeholder'] ) : '';

		// Label.
		$this->field_preview_option( 'label', $field );
		?>

		<div class="format-selected format-selected-full">

			<div class="wpforms-field-row">
				<input type="text" disabled>
				<label class="wpforms-sub-label"><?php \esc_html_e( 'Card', 'wpforms' ); ?></label>
			</div>

			<div class="wpforms-field-row">
				<div class="wpforms-credit-card-cardname">
					<input type="text" placeholder="<?php echo \esc_attr( $name_placeholder ); ?>" disabled>
					<label class="wpforms-sub-label"><?php \esc_html_e( 'Name on Card', 'wpforms' ); ?></label>
				</div>
			</div>
		</div>

		<?php
		// Description.
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 2.3.0
	 *
	 * @param array $field      Field data and settings.
	 * @param array $deprecated Deprecated field attributes. Use field properties.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $deprecated, $form_data ) {

		// Define data.
		$number = ! empty( $field['properties']['inputs']['number'] ) ? $field['properties']['inputs']['number'] : array();
		$name   = ! empty( $field['properties']['inputs']['name'] ) ? $field['properties']['inputs']['name'] : array();

		// Display warning for non SSL pages.

		if ( ! \is_ssl() ) {
			echo '<div class="wpforms-cc-warning wpforms-error-alert">';
			\esc_html_e( 'This page is insecure. Credit Card field should be used for testing purposes only.', 'wpforms' );
			echo '</div>';
		}

		if ( ! Helpers::has_stripe_keys() ) {
			echo '<div class="wpforms-cc-warning wpforms-error-alert">';
			\esc_html_e( 'Credit Card field is disabled, Stripe keys are missing.', 'wpforms' );
			echo '</div>';
		}

		if ( empty( $form_data['payments']['stripe']['enable'] ) ) {
			echo '<div class="wpforms-cc-warning wpforms-error-alert">';
			\esc_html_e( 'Credit Card field is disabled, Stripe payments are not enabled in the form settings.', 'wpforms' );
			echo '</div>';
		}

		// Row wrapper.
		echo '<div class="wpforms-field-row wpforms-field-' . \sanitize_html_class( $field['size'] ) . '">';

			echo '<div ' . \wpforms_html_attributes( false, $number['block'] ) . '>';
			$this->field_display_sublabel( 'number', 'before', $field );
			\printf(
				'<div %s data-required="%s"><!-- a Stripe Element will be inserted here. --></div>',
				\wpforms_html_attributes( $number['id'], $number['class'], $number['data'], $number['attr'] ),
				\esc_html( $number['required'] )
			);
			// Hidden input is needed for styling and validation.
			echo '<input type="text" class="wpforms-stripe-credit-card-hidden-input" name="wpforms[stripe-credit-card-hidden-input-' . \absint( $form_data['id'] ) . ']" disabled style="display: none;">';
			$this->field_display_sublabel( 'number', 'after', $field );
			$this->field_display_error( 'number', $field );
			echo '</div>';

		echo '</div>';

		// Row wrapper.
		echo '<div class="wpforms-field-row wpforms-field-' . \sanitize_html_class( $field['size'] ) . '">';

			// Name.
			echo '<div ' . \wpforms_html_attributes( false, $name['block'] ) . '>';
			$this->field_display_sublabel( 'name', 'before', $field );
			\printf(
				'<input type="text" %s %s>',
				\wpforms_html_attributes( $name['id'], $name['class'], $name['data'], $name['attr'] ),
				\esc_html( $name['required'] )
			);
			$this->field_display_sublabel( 'name', 'after', $field );
			$this->field_display_error( 'name', $field );
			echo '</div>';

		echo '</div>';
	}

	/**
	 * Currently validation happens on the front end. We do not do
	 * generic server-side validation because we do not allow the card
	 * details to POST to the server.
	 *
	 * @since 2.3.0
	 *
	 * @param int   $field_id     Field ID.
	 * @param array $field_submit Submitted field value.
	 * @param array $form_data    Form data and settings.
	 */
	public function validate( $field_id, $field_submit, $form_data ) {
	}

	/**
	 * Format field.
	 *
	 * @since 2.3.0
	 *
	 * @param int   $field_id     Field ID.
	 * @param array $field_submit Submitted field value.
	 * @param array $form_data    Form data and settings.
	 */
	public function format( $field_id, $field_submit, $form_data ) {

		// Define data.
		$name = ! empty( $form_data['fields'][ $field_id ]['label'] ) ? $form_data['fields'][ $field_id ]['label'] : '';

		// Set final field details.
		\wpforms()->process->fields[ $field_id ] = array(
			'name'  => \sanitize_text_field( $name ),
			'value' => '',
			'id'    => \absint( $field_id ),
			'type'  => $this->type,
		);
	}
}
