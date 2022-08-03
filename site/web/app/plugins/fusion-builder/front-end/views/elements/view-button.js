/* global fusionAllElements */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Button Element View.
		FusionPageBuilder.fusion_button = FusionPageBuilder.ElementView.extend( {

			/**
			 * Runs on render.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			onRender: function() {
				this.afterPatch();
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				var item    = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el.find( '[rel="iLightbox"]' ) ),
					params  = this.model.get( 'params' ),
					stretch = params.stretch;

				if ( 'default' === stretch || '' == stretch ) {
					stretch = fusionAllElements.fusion_button.defaults.stretch;
				}

				this.$el.removeClass( 'fusion-element-alignment-right fusion-element-alignment-left fusion-element-alignment-block fusion-element-alignment-textflow' );

				if ( 'yes' !== stretch && ! this.flexDisplay() ) {
					if ( 'undefined' !== typeof params.alignment && '' !== params.alignment ) {
						this.$el.addClass( 'fusion-element-alignment-' + params.alignment );
					} else if ( ! jQuery( 'body.rtl' ).length ) {
						this.$el.addClass( 'fusion-element-alignment-left fusion-element-alignment-textflow' );
					} else {
						this.$el.addClass( 'fusion-element-alignment-right fusion-element-alignment-textflow' );
					}
				} else {
					this.$el.addClass( 'fusion-element-alignment-block' );
				}

				if ( 'object' === typeof jQuery( '#fb-preview' )[ 0 ].contentWindow.avadaLightBox ) {
					if ( 'undefined' !== typeof this.iLightbox ) {
						this.iLightbox.destroy();
					}

					if ( item.length ) {
						this.iLightbox = item.iLightBox( jQuery( '#fb-preview' )[ 0 ].contentWindow.avadaLightBox.prepare_options( 'single' ) );
					}
				}
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				this.isFlex = this.flexDisplay();

				// Validate values.
				this.extrasCheck( atts.values, atts.extras );
				this.buildValues( atts.values );

				// Create attribute objects.
				attributes.wrapperAttr    = this.buildWrapperAttr( atts.values );
				attributes.attr           = this.buildAttr( atts.values );
				attributes.IconAttr       = this.buildIconAttr( atts.values );
				attributes.buttonStyles   = this.buildButtonStyles( atts.values );
				attributes.textAttr       = this.buildTextAttr( atts.values );

				// Any extras that need passed on.
				attributes.values = atts.values;

				return attributes;
			},

			extrasCheck: function( values, extras ) {
				var schemeId,
					customColor;
				if ( -1 !== values.color.indexOf( 'scheme-' ) && 'object' === typeof extras && 'object' === typeof extras.custom_color_schemes ) {
					schemeId    = values.color.replace( 'scheme-', '' );
					customColor = extras.custom_color_schemes[ schemeId ];

					// If the scheme exists and has options, use them.  Otherwise set the color scheme to default as fallback.
					if ( 'undefined' !== typeof customColor ) {
						values.accent_color          = 'undefined' !== typeof customColor.values.button_accent_color ? customColor.values.button_accent_color.toLowerCase() : '#ffffff';
						values.accent_hover_color    = 'undefined' !== typeof customColor.values.button_accent_hover_color ? customColor.values.button_accent_hover_color.toLowerCase() : '#ffffff';
						values.bevel_color           = 'undefined' !== typeof customColor.values.button_bevel_color ? customColor.values.button_bevel_color.toLowerCase() : '#54770F';
						values.gradient_colors       =  customColor.values.button_gradient_top_color + '|' + customColor.values.button_gradient_bottom_color;
						values.gradient_hover_colors =  customColor.values.button_gradient_top_color_hover + '|' + customColor.values.button_gradient_bottom_color_hover;
					} else {
						values.color = 'default';
					}
				}
			},

			/**
			 * Builds the values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {void}
			 */
			buildValues: function( values ) {

				// BC support for old 'gradient_colors' format.
				var buttonGradientTopColor         = values.button_gradient_top_color,
					buttonGradientBottomColor      = values.button_gradient_bottom_color,
					buttonGradientTopColorHover    = values.button_gradient_top_color_hover,
					buttonGradientBottomColorHover = values.button_gradient_bottom_color_hover,
					oldTextColor                   = '';

				if ( '' === values.gradient_colors ) {
					values.gradient_colors = values.button_gradient_top_color.toLowerCase() + '|' + values.button_gradient_bottom_color.toLowerCase();
				}

				if ( '' === values.gradient_hover_colors ) {
					values.gradient_hover_colors = values.button_gradient_top_color_hover.toLowerCase() + '|' + values.button_gradient_bottom_color_hover.toLowerCase();
				}

				// BC compatibility for button shape.
				if ( 'undefined' !== typeof values.shape && 'undefined' === typeof values.border_radius ) {
					if ( 'square' === values.shape ) {
						values.border_radius = '0';
					} else if ( 'round' === values.shape ) {
						values.border_radius = '2';

						if ( '3d' === values.type.toLowerCase() ) {
							values.border_radius = '4';
						}
					} else if ( 'pill' === values.shape ) {
						values.border_radius = '25';
					} else if ( '' === values.shape ) {
						values.border_radius = '';
					}
				}

				values.border_width = parseInt( values.border_width, 10 ) + 'px';
				values.border_radius = parseInt( values.border_radius, 10 ) + 'px';

				if ( 'default' === values.color ) {
					values.accent_color          = ( 'undefined' !== typeof values.button_accent_color && '' !== values.button_accent_color ) ? values.button_accent_color.toLowerCase() : '#ffffff';
					values.accent_hover_color    = ( 'undefined' !== typeof values.button_accent_hover_color && '' !== values.button_accent_hover_color ) ? values.button_accent_hover_color.toLowerCase() : '#ffffff';
					values.border_color          = ( 'undefined' !== typeof values.button_border_color && '' !== values.button_border_color ) ? values.button_border_color.toLowerCase() : '#ffffff';
					values.border_hover_color    = ( 'undefined' !== typeof values.button_border_hover_color && '' !== values.button_border_hover_color ) ? values.button_border_hover_color.toLowerCase() : '#ffffff';
					values.bevel_color           = ( 'undefined' !== typeof values.button_bevel_color && '' !== values.button_bevel_color ) ? values.button_bevel_color.toLowerCase() : '#54770F';
					values.gradient_colors       = buttonGradientTopColor.toLowerCase() + '|' + buttonGradientBottomColor.toLowerCase();
					values.gradient_hover_colors = buttonGradientTopColorHover.toLowerCase() + '|' + buttonGradientBottomColorHover.toLowerCase();
				}

				// Combined variable settings.
				oldTextColor   = values.text_color;

				if ( '' !== oldTextColor ) {
					values.text_color = oldTextColor;
				}

				if ( '' !== values.modal ) {
					values.link = '#';
				}

				values.margin_bottom = _.fusionValidateAttrValue( values.margin_bottom, 'px' );
				values.margin_left   = _.fusionValidateAttrValue( values.margin_left, 'px' );
				values.margin_right  = _.fusionValidateAttrValue( values.margin_right, 'px' );
				values.margin_top    = _.fusionValidateAttrValue( values.margin_top, 'px' );

				values.type = values.type.toLowerCase();
			},

			/**
			 * Builds the wrapper attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildWrapperAttr: function( values ) {
				var attr 		= {
					class: '',
					style: ''
				},
				isDefaultStretch = ( 'undefined' !== typeof values.stretch && ( '' === values.stretch || 'default' === values.stretch ) ) || 'undefined' === typeof values.stretch;

				if ( this.isFlex ) {
					if ( values.alignment ) {
						attr.style += 'text-align:' + values.alignment + ';';
					}
					if ( values.alignment_medium && values.alignment !== values.alignment_medium ) {
						attr[ 'class' ] += ' md-text-align-' + values.alignment_medium;
					}

					if ( values.alignment_small && values.alignment !== values.alignment_small ) {
						attr[ 'class' ] += ' sm-text-align-' + values.alignment_small;
					}
				} else {
					attr[ 'class' ] += 'fusion-button-wrapper';
					// Add wrapper to the button for alignment and scoped styling.
					if ( ( ( ! isDefaultStretch && 'yes' === values.stretch ) || ( isDefaultStretch && 'yes' === fusionAllElements.fusion_button.defaults.stretch ) ) ) {
						attr[ 'class' ] += ' fusion-align-block';
					} else if ( values.alignment ) {
						attr[ 'class' ] += ' fusion-align' + values.alignment;
					}
				}

				return attr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var params = this.model.get( 'params' ),
					attr = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-button button-' + values.type + ' button-' + values.color + ' button-cid' + this.model.get( 'cid' ),
						style: ''
					} ),
					sizeClass    = 'button-' + values.size,
					stretchClass = 'fusion-button-span-' + values.stretch,
					typeClass    = '';

				attr[ 'class' ] += _.fusionGetStickyClass( values.sticky_display );

				if ( ( 'undefined' !== typeof params.size && '' === params.size ) || 'undefined' === typeof params.size ) {
					sizeClass = 'fusion-button-default-size';
				}

				if ( ( 'undefined' !== typeof params.stretch && ( '' === params.stretch || 'default' === params.stretch ) ) || 'undefined' === typeof params.stretch ) {
					stretchClass = 'fusion-button-default-span';
				}

				if ( ( 'undefined' !== typeof params.type && ( '' === params.type || 'default' === params.type ) ) || 'undefined' === typeof params.type ) {
					typeClass = 'fusion-button-default-type';
				}

				attr[ 'class' ] += ' ' + sizeClass + ' ' + stretchClass + ' ' + typeClass;

				attr.target = values.target;
				if ( '_blank' === values.target ) {
					attr.rel = 'noopener noreferrer';
				} else if ( 'lightbox' === values.target ) {
					attr.rel = 'iLightbox';
				}

				attr =  _.fusionLinkAttributes( attr, values );

				attr.title = values.title;
				attr.href  = values.link;

				if ( '' !== values.modal ) {
					attr.data_toggle = 'modal';
					attr.data_target =  '.fusion-modal.' + values.modal;
				}

				if ( '' !== values.margin_top ) {
					attr.style += 'margin-top:' + values.margin_top + ';';
				}

				if ( '' !== values.margin_right ) {
					attr.style += 'margin-right:' + values.margin_right + ';';
				}

				if ( '' !== values.margin_bottom ) {
					attr.style += 'margin-bottom:' + values.margin_bottom + ';';
				}

				if ( '' !== values.margin_left ) {
					attr.style += 'margin-left:' + values.margin_left + ';';
				}

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				attr = _.fusionAnimations( values, attr );

				return attr;
			},

			/**
			 * Builds icon attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildIconAttr: function( values ) {
				var buttonShortcodeIcon = {
					class: _.fusionFontAwesome( values.icon ),
					'aria-hidden': 'true'
				};

				if ( 'yes' !== values.icon_divider ) {
					buttonShortcodeIcon[ 'class' ] += ' button-icon-' + values.icon_position;
				}

				if ( values.icon_color && values.icon_color !== values.accent_color ) {
					buttonShortcodeIcon.style = 'color:' + values.icon_color + ';';
				}

				return buttonShortcodeIcon;
			},

			/**
			 * Builds text attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildTextAttr: function( values ) {
				var buttonTextAttr = {
					class: 'fusion-button-text'
				};
				if ( '' !== values.icon && 'yes' === values.icon_divider ) {
					buttonTextAttr[ 'class' ] += ' fusion-button-text-' + values.icon_position;
				}
				buttonTextAttr = _.fusionInlineEditor( {
					cid: this.model.get( 'cid' ),
					'disable-return': true,
					'disable-extra-spaces': true,
					toolbar: 'simple'
				}, buttonTextAttr );

				return buttonTextAttr;
			},

			/**
			 * Builds the styles.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {string}
			 */
			buildButtonStyles: function( values ) {
				var params               = this.model.get( 'params' ),
					styles               = '',
					styleTag             = '',
					cid                  = 'cid' + this.model.get( 'cid' ),
					generalStyles        = '',
					textColorStyles      = '',
					button3DStyles       = '',
					hoverStyles          = '',
					textColorHoverStyles = '',
					gradientStyles       = '',
					gradientHoverStyles  = '',
					button3DAdd          = '',
					oldTextColor,
					gradHoverColors,
					gradColors,
					button3DShadow,
					button3DShadowPart1,
					button3DShadowPart2,
					button3DShadowPart3;

				if ( ( 'custom' === values.color || 'default' === values.color || ( -1 !== values.color.indexOf( 'scheme-' ) && ( '' !== values.bevel_color || '' !== values.accent_color || '' !== values.accent_hover_color || '' !== values.border_width || '' !== values.gradient_colors ) ) ) ) {

					oldTextColor   = values.text_color;

					if ( '3d' === values.type && '' !== values.bevel_color ) {
						if ( 'small' === values.size ) {
							button3DAdd = 0;
						} else if ( 'medium' === values.size ) {
							button3DAdd = 1;
						} else if ( 'large' === values.size ) {
							button3DAdd = 2;
						} else if ( 'xlarge' === values.size ) {
							button3DAdd = 3;
						}
						button3DShadowPart1 = 'inset 0px 1px 0px #fff,';
						button3DShadowPart2 = '0px ' + ( 2 + button3DAdd ) + 'px 0px ' + values.bevel_color + ',';
						button3DShadowPart3 = '1px ' + ( 4 + button3DAdd ) + 'px ' + ( 4 + button3DAdd ) + 'px 3px rgba(0,0,0,0.3)';

						if ( 'small' === values.size ) {
							button3DShadowPart3 = button3DShadowPart3.replace( '3px', '2px' );
						}
						button3DShadow = button3DShadowPart1 + button3DShadowPart2 + button3DShadowPart3;
						button3DStyles = '-webkit-box-shadow: ' + button3DShadow + ';-moz-box-shadow: ' + button3DShadow + ';box-shadow: ' + button3DShadow + ';';
					}

					if ( 'default' !== values.color ) {
						if ( oldTextColor ) {
							textColorStyles += 'color:' + oldTextColor + ';';
						} else if ( values.accent_color ) {
							textColorStyles += 'color:' + values.accent_color + ';';
						}

						if ( '' !== values.border_color ) {
							generalStyles += 'border-color:' + values.border_color + ';';
						}

						if ( '' !== oldTextColor ) {
							textColorHoverStyles += 'color:' + oldTextColor + ';';
						} else if ( '' !== values.accent_hover_color ) {
							textColorHoverStyles += 'color:' + values.accent_hover_color + ';';
						} else if ( '' !== values.accent_color ) {
							textColorHoverStyles += 'color:' + values.accent_color + ';';
						}

						if ( '' !== values.border_hover_color ) {
							hoverStyles += 'border-color:' + values.border_hover_color + ';';
						} else if ( '' !== values.accent_color ) {
							hoverStyles += 'border-color:' + values.accent_color + ';';
						}

						if ( '' !== textColorStyles ) {
							styles += '.fusion-button.button-' + cid + ' .fusion-button-text, .fusion-button.button-' + cid + ' i {' + textColorStyles + '}';
						}

						if ( '' !== values.accent_color ) {
							styles += '.fusion-button.button-' + cid + ' .fusion-button-icon-divider{border-color:' + values.accent_color + ';}';
						}

						if ( '' !== textColorHoverStyles ) {
							styles += '.fusion-button.button-' + cid + ':hover .fusion-button-text, .fusion-button.button-' + cid + '.hover .fusion-button-text, .fusion-button.button-' + cid + ':hover i, .fusion-button.button-' + cid + '.hover i, .fusion-button.button-' + cid + ':focus .fusion-button-text, .fusion-button.button-' + cid + ':focus i,.fusion-button.button-' + cid + ':active .fusion-button-text, .fusion-button.button-' + cid + ':active{' + textColorHoverStyles + '}';
						}

						if ( '' !== values.accent_hover_color ) {
							styles += '.fusion-button.button-' + cid + ':hover .fusion-button-icon-divider, .fusion-button.button-' + cid + '.hover .fusion-button-icon-divider, .fusion-button.button-' + cid + ':hover .fusion-button-icon-divider, .fusion-button.button-' + cid + '.hover .fusion-button-icon-divider, .fusion-button.button-' + cid + ':active .fusion-button-icon-divider{border-color:' + values.accent_hover_color + ';}';
						}
					}

					if ( '' !== values.border_width && 'custom' === values.color && ( 'undefined' === typeof params.border_width || '' !== params.border_width ) ) {
						generalStyles += 'border-width:' + values.border_width + ';';
						hoverStyles   += 'border-width:' + values.border_width + ';';
					}

					generalStyles += 'border-radius:' + values.border_radius + ';';

					if ( '' !== generalStyles ) {
						styles += '.fusion-button.button-' + cid + ' {' + generalStyles + '}';
					}

					if ( '' !== button3DStyles ) {
						styles += '.fusion-button.button-' + cid + '.button-3d{' + button3DStyles + '}.button-' + cid + '.button-3d:active{' + button3DStyles + '}';
					}

					if ( '' !== hoverStyles ) {
						styles += '.fusion-button.button-' + cid + ':hover, .fusion-button.button-' + cid + '.hover, .fusion-button.button-' + cid + ':focus, .fusion-button.button-' + cid + ':active{' + hoverStyles + '}';
					}

					if ( '' !== values.gradient_colors && 'default' !== values.color ) {
						gradColors = '';

						// Checking for deprecated separators.
						if ( -1 !== values.gradient_colors.indexOf( ';' ) ) {
							gradColors = values.gradient_colors.split( ';' );
						} else {
							gradColors = values.gradient_colors.split( '|' );
						}

						if ( 1 === gradColors.length || '' === gradColors[ 1 ] || gradColors[ 0 ] === gradColors[ 1 ] ) {
							gradientStyles += 'background:' + gradColors[ 0 ] + ';';
						} else {
							gradientStyles += 'background: ' + gradColors[ 0 ] + ';';
							gradientStyles += 'background-image: -webkit-gradient( linear, left bottom, left top, from( ' + gradColors[ 1 ] + ' ), to( ' + gradColors[ 0 ] + ' ) );';
							gradientStyles += 'background-image: -webkit-linear-gradient( bottom, ' + gradColors[ 1 ] + ', ' + gradColors[ 0 ] + ' );';
							gradientStyles += 'background-image:   -moz-linear-gradient( bottom, ' + gradColors[ 1 ] + ', ' + gradColors[ 0 ] + ' );';
							gradientStyles += 'background-image:     -o-linear-gradient( bottom, ' + gradColors[ 1 ] + ', ' + gradColors[ 0 ] + ' );';
							gradientStyles += 'background-image: linear-gradient( to top, ' + gradColors[ 1 ] + ', ' + gradColors[ 0 ] + ' );';
						}

						styles += '.fusion-button.button-' + cid + '{' + gradientStyles + '}';
					}

					if ( values.gradient_hover_colors && 'default' !== values.color ) {
						gradHoverColors = '';

						// Checking for deprecated separators.
						if ( -1 !== values.gradient_hover_colors.indexOf( ';' ) ) {
							gradHoverColors = values.gradient_hover_colors.split( ';' );
						} else {
							gradHoverColors = values.gradient_hover_colors.split( '|' );
						}

						if ( 1 == gradHoverColors.length || '' === gradHoverColors[ 1 ] || gradHoverColors[ 0 ] === gradHoverColors[ 1 ] ) {
							gradientHoverStyles += 'background: ' + gradHoverColors[ 0 ] + ';';
						} else {
							gradientHoverStyles += 'background: ' + gradHoverColors[ 0 ] + ';';
							gradientHoverStyles += 'background-image: -webkit-gradient( linear, left bottom, left top, from( ' + gradHoverColors[ 1 ] + ' ), to( ' + gradHoverColors[ 0 ] + ' ) );';
							gradientHoverStyles += 'background-image: -webkit-linear-gradient( bottom, ' + gradHoverColors[ 1 ] + ', ' + gradHoverColors[ 0 ] + ' );';
							gradientHoverStyles += 'background-image:   -moz-linear-gradient( bottom, ' + gradHoverColors[ 1 ] + ', ' + gradHoverColors[ 0 ] + ' );';
							gradientHoverStyles += 'background-image:     -o-linear-gradient( bottom, ' + gradHoverColors[ 1 ] + ', ' + gradHoverColors[ 0 ] + ' );';
							gradientHoverStyles += 'background-image: linear-gradient( to top, ' + gradHoverColors[ 1 ] + ', ' + gradHoverColors[ 0 ] + ' );';
						}

						styles += '.fusion-button.button-' + cid + ':hover, .fusion-button.button-' + cid + '.hover, .button-' + cid + ':focus,.fusion-button.button-' + cid + ':active{' + gradientHoverStyles + '}';
					}
				}

				if ( '' !== values.text_transform ) {
					styles += '.fusion-button.button-' + cid + ' .fusion-button-text{text-transform:' + values.text_transform + '}';
				}

				if ( '' !== styles ) {
					styleTag = '<style type="text/css">' + styles + '</style>';
				}

				return styleTag;
			}
		} );
	} );
}( jQuery ) );
