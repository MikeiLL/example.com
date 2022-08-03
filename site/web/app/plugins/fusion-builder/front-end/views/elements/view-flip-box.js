/* global fusionAllElements, FusionPageBuilderElements */
/* eslint no-unused-vars: 0 */

var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Counter flip box view
		FusionPageBuilder.fusion_flip_box = FusionPageBuilder.ChildElementView.extend( {

			/**
			 * Runs during render() call.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			onRender: function() {

				if ( 'undefined' !== typeof this.model.attributes.selectors ) {
					this.model.attributes.selectors[ 'class' ] += ' ' + this.className;
					this.setElementAttributes( this.$el, this.model.attributes.selectors );
				}
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {

				if ( 'undefined' !== typeof this.model.attributes.selectors ) {
					this.model.attributes.selectors[ 'class' ] += ' ' + this.className;
					this.setElementAttributes( this.$el, this.model.attributes.selectors );
				}

				// Using non debounced version for smoothness.
				this.refreshJs();
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var computedAtts = this.computeAtts( atts.values );

				atts.cid    = this.model.get( 'cid' );
				atts.parent = this.model.get( 'parent' );

				atts.flipBoxShortcodeBackBox  = computedAtts.flipBoxShortcodeBackBox;
				atts.flipBoxAttributes        = computedAtts.flipBoxAttributes;
				atts.flipBoxShortcodeFrontBox = computedAtts.flipBoxShortcodeFrontBox;
				atts.icon_output              = computedAtts.icon_output;
				atts.title_front_output       = computedAtts.title_front_output;
				atts.title_back_output        = computedAtts.title_back_output;
				atts.icon_output              = computedAtts.icon_output;

				return atts;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			computeAtts: function( values ) {
				var parent                       = this.model.get( 'parent' ),
					parentModel                  = FusionPageBuilderElements.find( function( model ) {
						return model.get( 'cid' ) == parent;
					} ),
					parentValues                 = 'undefined' !== typeof parentModel ? jQuery.extend( true, {}, fusionAllElements.fusion_flip_boxes.defaults, _.fusionCleanParameters( parentModel.get( 'params' ) ) ) : {},
					params                       = this.model.get( 'params' ),
					style                        = '',
					flipBoxAttributes            = '',
					flipBoxShortcode,
					flipBoxShortcodeIcon         = {
						'aria-hidden': 'true'
					},
					iconOutput                   = '',
					animations                   = '',
					flipBoxShortcodeGrafix       = '',
					flipBoxShortcodeHeadingFront = '',
					titleFrontOutput             = '',
					flipBoxShortcodeHeadingBack  = '',
					flipBoxShortcodeBackBox      = '',
					titleBackOutput              = '',
					frontInner                   = '',
					columns                      = '',
					flipBoxShortcodeFrontBox,
					atts,
					alpha;

				values.border_size   = _.fusionValidateAttrValue( values.border_size, 'px' );
				values.border_radius = _.fusionValidateAttrValue( values.border_radius, 'px' );

				// Case when image is set on parent element and icon on child element.
				if ( ( 'undefined' === typeof params.image || '' === params.image ) && ( 'undefined' !== typeof params.icon && '' !== params.icon ) ) {
					values.image = '';
				}

				// Backwards compatibility for when we had image width and height params.
				if ( 'undefined' !== typeof params.image_width && params.image_width ) {
					values.image_width = params.image_width;
				} else {
					values.image_width = values.image_max_width;
				}

				values.image_width  = _.fusionValidateAttrValue( values.image_width, '' );

				if ( 'undefined' !== typeof values.image && ( '' !== values.image || '' !== values.image_id ) ) {

					if ( -1 === parseInt( values.image_width ) ) {
						values.image_width = '35';
					}

					values.image_height = values.image_width;

				} else {
					values.image_width  = '' === values.image_width ? '35' : values.image_width;
					values.image_height = '35';
				}

				if ( 'round' === values.border_radius ) {
					values.border_radius = '50%';
				}

				style             = '';
				iconOutput        = '';
				titleFrontOutput  = '';
				titleBackOutput   = '';
				flipBoxAttributes = {
					class: 'fusion-flip-box',
					tabindex: 0
				};

				flipBoxAttributes[ 'class' ] += ' flip-' + values.flip_direction;

				if ( values.animation_type ) {
					flipBoxAttributes = _.fusionAnimations( values, flipBoxAttributes );
				}

				if ( values.image && '' !== values.image ) {

					iconOutput = '<img src="' + values.image + '" width="' + values.image_width + '" height="' + values.image_height + '" alt="' + values.alt + '" />';

				} else if ( values.icon ) {

					if ( values.image ) {
						flipBoxShortcodeIcon[ 'class' ] = 'image';
					} else if ( values.icon ) {
						flipBoxShortcodeIcon[ 'class' ] = _.fusionFontAwesome( values.icon );
					}

					if ( values.icon_color ) {
						flipBoxShortcodeIcon.style = 'color:' + values.icon_color + ';';
					}

					if ( values.icon_flip ) {
						flipBoxShortcodeIcon[ 'class' ] += ' fa-flip-' + values.icon_flip;
					}

					if ( values.icon_rotate ) {
						flipBoxShortcodeIcon[ 'class' ] += ' fa-rotate-' + values.icon_rotate;
					}

					if ( 'yes' === values.icon_spin ) {
						flipBoxShortcodeIcon[ 'class' ] += ' fa-spin';
					}

					iconOutput = '<i ' + _.fusionGetAttributes( flipBoxShortcodeIcon ) + '></i>';

				}

				if ( '' !== iconOutput ) {

					flipBoxShortcodeGrafix = {
						class: 'flip-box-grafix'
					};

					if ( ! values.image ) {

						if ( 'yes' === values.circle ) {
							flipBoxShortcodeGrafix[ 'class' ] += ' flip-box-circle';

							if ( values.circle_color ) {
								flipBoxShortcodeGrafix.style = 'background-color:' + values.circle_color + ';';
							}

							if ( values.circle_border_color ) {
								flipBoxShortcodeGrafix.style += 'border-color:' + values.circle_border_color + ';';
							}
						} else {
							flipBoxShortcodeGrafix[ 'class' ] += ' flip-box-no-circle';
						}
					} else {
						flipBoxShortcodeGrafix[ 'class' ] += ' flip-box-image';
					}

					iconOutput = '<div ' + _.fusionGetAttributes( flipBoxShortcodeGrafix ) + '>' + iconOutput + '</div>';
				}

				if ( '' !== values.title_front ) {
					flipBoxShortcodeHeadingFront = {
						class: 'flip-box-heading'
					};

					if ( ! values.text_front ) {
						flipBoxShortcodeHeadingFront[ 'class' ] += ' without-text';
					}

					if ( values.title_front_color ) {
						flipBoxShortcodeHeadingFront.style = 'color:' + values.title_front_color + ';';
					}

					titleFrontOutput = '<h2 ' + _.fusionGetAttributes( flipBoxShortcodeHeadingFront ) + '>' + values.title_front + '</h2>';
				}

				if ( '' !== values.title_back ) {
					flipBoxShortcodeHeadingBack = {
						class: 'flip-box-heading-back'
					};

					if ( values.title_back_color ) {
						flipBoxShortcodeHeadingBack.style = 'color:' + values.title_back_color + ';';
					}
					titleBackOutput = '<h3 ' + _.fusionGetAttributes( flipBoxShortcodeHeadingBack ) + '>' + values.title_back + '</h3>';
				}

				frontInner = '<div class="flip-box-front-inner">' + iconOutput + titleFrontOutput + values.text_front + '</div>';

				// flipBoxShortcodeFrontBox Attributes.
				flipBoxShortcodeFrontBox = {
					class: 'flip-box-front',
					style: ''
				};

				if ( values.background_color_front ) {
					flipBoxShortcodeFrontBox.style += 'background-color:' + values.background_color_front + ';';
				}

				if ( values.border_color ) {
					flipBoxShortcodeFrontBox.style += 'border-color:' + values.border_color + ';';
				}

				if ( values.border_radius ) {
					flipBoxShortcodeFrontBox.style += 'border-radius:' + values.border_radius + ';';
				}

				if ( values.border_size ) {
					flipBoxShortcodeFrontBox.style += 'border-style:solid;border-width:' + values.border_size + ';';
				}

				if ( values.text_front_color ) {
					flipBoxShortcodeFrontBox.style += 'color:' + values.text_front_color + ';';
				}

				if ( parentValues.flip_duration ) {
					flipBoxShortcodeFrontBox.style += 'transition-duration:' + parentValues.flip_duration + 's;';
				}

				if ( values.background_image_front ) {
					flipBoxShortcodeFrontBox.style += 'background-image: url(\'' + values.background_image_front + '\');';
					if ( values.background_color_front ) {
						alpha = jQuery.Color( values.background_color_front ).alpha();
						if ( 1 > alpha && 0 !== alpha ) {
							flipBoxShortcodeFrontBox.style += 'background-blend-mode: overlay;';
						}
					}
				}

				// flipBoxShortcodeBackBox Attributes.
				flipBoxShortcodeBackBox = {
					class: 'flip-box-back',
					style: ''
				};

				if ( values.background_color_back ) {
					flipBoxShortcodeBackBox.style += 'background-color:' + values.background_color_back + ';';
				}

				if ( values.border_color ) {
					flipBoxShortcodeBackBox.style += 'border-color:' + values.border_color + ';';
				}

				if ( values.border_radius ) {
					flipBoxShortcodeBackBox.style += 'border-radius:' + values.border_radius + ';';
				}

				if ( values.border_size ) {
					flipBoxShortcodeBackBox.style += 'border-style:solid;border-width:' + values.border_size + ';';
				}

				if ( values.text_back_color ) {
					flipBoxShortcodeBackBox.style += 'color:' + values.text_back_color + ';';
				}

				if ( parentValues.flip_duration ) {
					flipBoxShortcodeBackBox.style += 'transition-duration:' + parentValues.flip_duration + 's;';
				}

				if ( values.background_image_back ) {
					flipBoxShortcodeBackBox.style += 'background-image: url(\'' + values.background_image_back + '\');';
					if ( values.background_color_back ) {
						alpha = jQuery.Color( values.background_color_back ).alpha();
						if ( 1 > alpha && 0 !== alpha ) {
							flipBoxShortcodeBackBox.style += 'background-blend-mode: overlay;';
						}
					}
				}

				// flipBoxShortcode Attributes.
				columns = 1;
				if ( parentValues.columns ) {
					columns = 12 / parseInt( parentValues.columns, 10 );
				}

				flipBoxShortcode = {
					class: 'fusion-flip-box-wrapper fusion-column col-lg-' + columns + ' col-md-' + columns + ' col-sm-' + columns
				};

				if ( 5 === parseInt( parentValues.columns, 10 ) ) {
					flipBoxShortcode[ 'class' ] = 'fusion-flip-box-wrapper col-lg-2 col-md-2 col-sm-2';
				}

				if ( '' !== values[ 'class' ] ) {
					flipBoxShortcode[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					flipBoxShortcode.id = values.id;
				}

				this.model.set( 'selectors', flipBoxShortcode );

				atts = {};

				atts.flipBoxShortcodeBackBox  = flipBoxShortcodeBackBox;
				atts.flipBoxAttributes        = flipBoxAttributes;
				atts.flipBoxShortcodeFrontBox = flipBoxShortcodeFrontBox;
				atts.icon_output              = iconOutput;
				atts.title_front_output       = titleFrontOutput;
				atts.title_back_output        = titleBackOutput;
				atts.icon_output              = iconOutput;

				return atts;
			}

		} );
	} );
}( jQuery ) );
