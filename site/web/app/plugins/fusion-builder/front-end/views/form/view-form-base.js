/* global FusionApp, FusionPageBuilderApp, fusionBuilderText */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {
		// Fusion Form Password View.
		FusionPageBuilder.FormComponentView = FusionPageBuilder.ElementView.extend( {

			onInit: function() {
				this.formData = FusionApp.data.postMeta;
				this.listenTo( window.FusionEvents, 'fusion-rerender-form-inputs', this.reRender );
			},

			/**
			 * Builds attributes.
			 *
			 * @since 3.1
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildStyles: function() {
				return '';
			},

			elementData: function( values ) {
				var data  = {};

				data.checked               = '';
				data.required              = '';
				data.required_label        = '';
				data.required_placeholder  = '';
				data.disabled              = '';
				data[ 'class' ]            = '';
				data.id                    = '';
				data.placeholder           = '';
				data.label                 = '';
				data.label_class           = '';
				data.holds_private_data    = 'no';
				data.upload_size           = '';
				data.pattern			   = '';

				if ( 'undefined' === typeof values ) {
					return data;
				}

				if ( 'fusion_form_phone_number' === this.model.get( 'element_type' ) ) {
					data.pattern = ' pattern="[0-9()#&+*-=.]+" title="' + fusionBuilderText.phone_pattern_text + '"';
				}

				if ( 'fusion_form_checkbox' === this.model.get( 'element_type' ) && 'undefined' !== typeof values.checked && values.checked ) {
					data.checked = ' checked="checked"';
				}

				if ( 'fusion_form_upload' === this.model.get( 'element_type' ) && 'undefined' !== typeof values.upload_size && values.upload_size ) {
					data.upload_size = ' data-size="' + values.upload_size + '"';
				}

				if ( 'undefined' !== typeof values.required && 'yes' === values.required ) {
					data.required             = ' required="true" aria-required="true"';
					data.required_label       = ' <abbr class="fusion-form-element-required" title="' + fusionBuilderText.required + '">*</abbr>';
					data.required_placeholder = '*';
				}

				if ( 'undefined' !== typeof values.disabled && 'yes' === values.disabled ) {
					data.disabled = ' disabled';

					if ( 'undefined' !== typeof values.placeholder && '' !== values.placeholder ) {
						data.value = values.placeholder;
					}
				}

				data[ 'class' ] = ' class="fusion-form-input"';

				if ( 'undefined' !== typeof values.placeholder && '' !== values.placeholder ) {
					if ( 'fusion_form_dropdown' === this.model.get( 'element_type' ) ) {
						data.placeholder = values.placeholder + data.required_placeholder;
					} else {
						data.placeholder = ' placeholder="' + values.placeholder + data.required_placeholder + '"';
					}
				}

				if ( 'fusion_form_checkbox' === this.model.get( 'element_type' ) ) {
					data.label_class = ' class="fusion-form-checkbox-label"';
				}

				if ( 'undefined' !== typeof values.label && '' !== values.label ) {
					data.label = '<label for="' + values.name + '"' + data.label_class + '>' + values.label + data.required_label + '</label>';
				}

				data.holds_private_data = ' data-holds-private-data="false"';

				if ( 'undefined' !== typeof values.holds_private_data && '' !== values.holds_private_data ) {
					data.holds_private_data = ' data-holds-private-data="true"';
				}

				return data;
			},

			checkbox: function( values, type ) {
				var options	= '',
					elementData,
					elementName,
					elementHtml,
					checkboxClass,
					html = '';

				if ( 'undefined' === typeof values.options || ! values.options ) {
					return html;
				}

				values.options = JSON.parse( FusionPageBuilderApp.base64Decode( values.options ) );

				elementData = this.elementData( values );

				_.each( values.options, function( option, key ) {
					var checked = option[ 0 ] ? ' checked ' : '',
						label   = ( 'undefined' !== typeof option[ 1 ] ) ? option[ 1 ].trim() : '',
						value   = ! _.isEmpty( option[ 2 ] ) ? option[ 2 ].trim() : label, // eslint-disable-line no-unused-vars
						labelId;

					elementName   = ( 'checkbox' === type ) ? values.name + '[]' : values.name;
					checkboxClass = ( 'floated' === values.form_field_layout ) ? 'fusion-form-' + type + ' option-inline' : 'fusion-form-' + type;
					labelId       = type + '-' + label.replace( ' ', '-' ).toLowerCase() + '-' + key;

					options       += '<div class="' + checkboxClass + '">';
					options       += '<input id="' + labelId + '" type="' + type + '" value="' + label + '" name="' + elementName + '"' + elementData[ 'class' ] + elementData.id + elementData.required + checked + elementData.holds_private_data + '/>';
					options       += '<label for="' + labelId + '">';
					options       += label + '</label>';
					options       += '</div>';
				} );

				elementHtml = '<fieldset>';
				elementHtml += options;
				elementHtml += '</fieldset>';

				if ( '' !== values.tooltip ) {
					elementData.label += this.getFieldTooltip( values );
				}

				html = this.generateLabelHtml( html, elementHtml, elementData.label );

				return html;
			},

			generateInputField: function( values, type ) {
				var elementData,
					elementHtml,
					html = '';

				elementData = this.elementData( values );

				if ( '' !== values.tooltip ) {
					elementData.label += this.getFieldTooltip( values );
				}

				values.value = 'undefined' !== typeof values.value && '' !== values.value ? values.value : '';
				values.value = 'undefined' !== typeof elementData.value && '' !== elementData.value ? elementData.value : values.value;

				elementHtml = '<input type="' + type + '" name="' + values.name + '" value="' + values.value + '" ' + elementData[ 'class' ] + elementData.id + elementData.required + elementData.disabled + elementData.placeholder + elementData.holds_private_data + elementData.pattern + '/>';

				elementHtml = this.generateIconHtml( values, elementHtml );

				html = this.generateLabelHtml( html, elementHtml, elementData.label );

				return html;
			},

			getFieldTooltip: function( values ) {
				var html = '';

				if ( '' !== values.tooltip ) {
					html = '<div class="fusion-form-tooltip">';
					html += '<i class="fusion-icon-question-circle"></i>';
					html += '<span class="fusion-form-tooltip-content">' + values.tooltip + '</span>';
					html += '</div>';
				}

				return html;
			},

			addFieldWrapperHtml: function() {
				var html,
					labelPosition = 'above',
					params = this.model.get( 'params' );


				if ( 'undefined' !== typeof this.formData._fusion.label_position ) {
					labelPosition = this.formData._fusion.label_position;
				}

				html = '<div ';

				// Add custom ID if it's there.
				if ( 'undefined' !== typeof params.id && '' !== params.id ) {
					html += 'id="' + params.id + '" ';
				}

				// Start building class.
				html += 'class="fusion-form-field ' + this.model.get( 'element_type' ).replace( /_/g, '-' ) + '-field ' + this.model.get( 'cid' ) + ' ' + this.model.get( 'element_type' ).replace( /_/g, '-' ) + '-field fusion-form-label-' + labelPosition;

				// Add custom class if it's there.
				if ( 'undefined' !== typeof params[ 'class' ] && '' !== params[ 'class' ] ) {
					html += ' ' + params[ 'class' ];
				}

				// Close class quotes.
				html += '"';

				html += ' data-form-id="' + FusionApp.data.postDetails.post_id + '">';

				return html;
			},

			generateFormFieldHtml: function( fieldHtml ) {
				var html = this.addFieldWrapperHtml();
				html += fieldHtml;
				html += '</div>';

				return html;
			},

			generateIconHtml: function( atts, html ) {
				var icon;

				if ( 'undefined' !== typeof atts.input_field_icon && '' !== atts.input_field_icon ) {
					icon = '<div class="fusion-form-input-with-icon">';
					icon += '<i class="' + _.fusionFontAwesome( atts.input_field_icon ) + '"></i>';
					html = icon + html;
					html += '</div>';
				}

				return html;
			},

			generateLabelHtml: function( html, elementHtml, label ) {

				if ( '' !== label ) {
					label = '<div class="fusion-form-label-wrapper">' + label + '</div>';
				}

				if ( 'undefined' === typeof this.formData._fusion.label_position || 'above' === this.formData._fusion.label_position ) {
					html += label + elementHtml;
				} else {
					html += elementHtml + label;
				}

				return html;
			},

			generateTooltipHtml: function( values, elementData ) {
				if ( '' !== values.tooltip ) {
					elementData.label += this.getFieldTooltip( values );
				}

				return elementData;
			}

		} );
	} );
}( jQuery ) );
