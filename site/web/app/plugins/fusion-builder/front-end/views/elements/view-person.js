var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Person Element View.
		FusionPageBuilder.fusion_person = FusionPageBuilder.ElementView.extend( {

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			beforePatch: function() {
				var tooltips = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el ).find( '[data-toggle="tooltip"]' );

				if ( tooltips.length && 'function' === typeof tooltips.tooltip ) {
					tooltips.tooltip( 'destroy' );
				}
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				var tooltips = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el ).find( '[data-toggle="tooltip"]' );

				setTimeout( function() {
					if ( tooltips.length && 'function' === typeof tooltips.tooltip ) {
						tooltips.tooltip( {
							container: 'body'
						} );
					}
				}, 150 );
				this._refreshJs();

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

				// Validate values.
				this.validateValues( atts.values );

				// Create attribute objects
				attributes.attr               = this.buildAttr( atts.values );
				attributes.imageAttr          = this.buildImageAttr( atts.values );
				attributes.hrefAttr           = this.buildHrefAttr( atts.values );
				attributes.wrapperAttr        = this.buildWrapperAttr( atts.values );
				attributes.imageContainerAttr = this.buildImageContainerAttr( atts.values );
				attributes.styles             = this.buildStyles( atts.values );
				attributes.socialAttr         = this.buildSocialAttr( atts.values );
				attributes.descAttr           = this.buildDescAttr( atts.values );
				attributes.socialNetworks     = this.getSocialNetworks( atts.values );
				attributes.icons              = _.fusionBuildSocialLinks( attributes.socialNetworks, this.personIconAttr, atts.values );

				// Any extras that need passed on.
				attributes.cid             = this.model.get( 'cid' );
				attributes.values          = atts.values;

				return attributes;
			},

			/**
			 * Modifies the values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values ) {
				values.pic_style_blur           = _.fusionValidateAttrValue( values.pic_style_blur, 'px' );
				values.pic_bordersize           = _.fusionValidateAttrValue( values.pic_bordersize, 'px' );
				values.pic_borderradius         = _.fusionValidateAttrValue( values.pic_borderradius, 'px' );
				values.social_icon_boxed_radius = _.fusionValidateAttrValue( values.social_icon_boxed_radius, 'px' );
				values.social_icon_font_size    = _.fusionValidateAttrValue( values.social_icon_font_size, 'px' );
				values.social_icon_padding      = _.fusionValidateAttrValue( values.social_icon_padding, 'px' );

				if ( 'round' === values.pic_borderradius ) {
					values.pic_borderradius = '50%';
				}

				this.stylecolor = ( '#' === values.pic_style_color.charAt( 0 ) ) ? jQuery.Color( values.pic_style_color ).alpha( 0.3 ).toRgbaString() : jQuery.Color( values.pic_style_color ).toRgbaString();
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {

				// Person Shortcode Attributes.
				var cid = this.model.get( 'cid' ),
					personShortcode = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-person fusion-person-' + cid + ' person fusion-person-' + values.content_alignment + ' fusion-person-icon-' + values.icon_position
					} );

				if ( '' !== values[ 'class' ] ) {
					personShortcode[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					personShortcode.id = values.id;
				}

				return personShortcode;
			},

			/**
			 * Builds image attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildImageAttr: function( values ) {

				// PersonShortcodeImg Attributes.
				var personShortcodeImg = {
					class: 'person-img img-responsive',
					style: ''
				};

				personShortcodeImg.src = values.picture;
				personShortcodeImg.alt = values.name;

				return personShortcodeImg;
			},

			/**
			 * Builds href attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildHrefAttr: function( values ) {

				// PersonShortcodeHref attributes.
				var personShortcodeHref = {
					href: values.pic_link
				};

				if ( 'yes' === values.lightbox ) {
					personShortcodeHref[ 'class' ] = 'lightbox-shortcode';
					personShortcodeHref.href  = values.picture;
				} else {
					personShortcodeHref.target = values.linktarget;
				}

				return personShortcodeHref;
			},

			/**
			 * Builds style block
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {string}
			 */
			buildStyles: function( values ) {
				var styles = '';

				if ( 'bottomshadow' === values.pic_style ) {
					styles += '.fusion-person-' + this.model.get( 'cid' ) + ' .element-bottomshadow:before, .fusion-person-' + this.model.get( 'cid' ) + ' .element-bottomshadow:after{';
					styles += '-webkit-box-shadow: 0 17px 10px ' + this.stylecolor + ';box-shadow: 0 17px 10px ' + this.stylecolor + ';}';
				}

				if ( 'liftup' === values.hover_type && '' !== values.pic_borderradius && values.pic_borderradius ) {
					styles  += '.fusion-person-' + this.model.get( 'cid' ) + ' .imageframe-liftup:before{';
					styles  += '-webkit-border-radius:' + values.pic_borderradius + ';-moz-border-radius:' + values.pic_borderradius + ';border-radius:' + values.pic_borderradius + ';';
				}

				if ( '' !== styles ) {
					styles = '<style>' + styles + '</style>';
				}
				return styles;
			},

			/**
			 * Builds wrapper attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildWrapperAttr: function( values ) {

				// PersonShortcodeImageWrapper Attributes.
				var personShortcodeImageWrapper = {
					class: 'person-shortcode-image-wrapper'
				};

				if ( 'liftup' === values.hover_type  ) {
					personShortcodeImageWrapper[ 'class' ] += ' imageframe-liftup';
				}

				return personShortcodeImageWrapper;
			},

			/**
			 * Builds image container attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildImageContainerAttr: function( values ) {

				// PersonShortcodeImageContainer Attributes.
				var personShortcodeImageContainer = {
						class: 'person-image-container',
						style: ''
					},
					styles = '',
					blur = values.pic_style_blur,
					blurRadius = ( parseInt( blur, 10 ) + 4 ) + 'px';

				if ( '' !== values.hover_type && 'liftup' !== values.hover_type  ) {
					personShortcodeImageContainer[ 'class' ] += ' hover-type-' + values.hover_type;
				}

				if ( 'round' === values.pic_borderradius ) {
					values.pic_borderradius = '50%';
				}

				if ( '0px' !== values.pic_borderradius && '' !== values.pic_borderradius && 'bottomshadow' === values.pic_style ) {
					values.pic_style = 'none';
				}

				if ( 'glow' === values.pic_style ) {
					personShortcodeImageContainer[ 'class' ] += ' glow';
				} else if ( 'dropshadow' === values.pic_style ) {
					personShortcodeImageContainer[ 'class' ] += ' dropshadow';
				} else if ( 'bottomshadow' === values.pic_style ) {
					personShortcodeImageContainer[ 'class' ] += ' element-bottomshadow';
				}

				if ( 'glow' === values.pic_style ) {
					styles += '-webkit-box-shadow: 0 0 ' + blur + ' ' + this.stylecolor + ';box-shadow: 0 0 ' + blur + ' ' + this.stylecolor + ';';
				} else if ( 'dropshadow' === values.pic_style ) {
					styles += '-webkit-box-shadow: ' + blur + ' ' + blur + ' ' + blurRadius + ' ' + this.stylecolor + ';box-shadow: ' + blur + ' ' + blur + ' ' + blurRadius + ' ' + this.stylecolor + ';';
				}

				if ( '' !== values.pic_borderradius ) {
					styles += '-webkit-border-radius:' + values.pic_borderradius + ';-moz-border-radius:' + values.pic_borderradius + ';border-radius:' + values.pic_borderradius + '; overflow:hidden;';
				}
				if ( '' !== values.pic_bordersize ) {
					styles += 'border:' + values.pic_bordersize + ' solid ' + values.pic_bordercolor + ';';
				}

				personShortcodeImageContainer.style += styles;

				return personShortcodeImageContainer;
			},

			/**
			 * Builds social attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildSocialAttr: function( values ) {

				// PersonShortcodeSocialNetworks Attributes.
				var personShortcodeSocialNetworks = {
					class: 'fusion-social-networks'
				};

				if ( 'yes' === values.social_icon_boxed ) {
					personShortcodeSocialNetworks[ 'class' ] += ' boxed-icons';
				}

				return personShortcodeSocialNetworks;
			},

			/**
			 * Builds description attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildDescAttr: function( values ) {

				// PersonDesc Attributes.
				var personDesc = {
					class: 'person-desc'
				};

				if ( values.background_color && 'transparent' !== values.background_color && 0 !== jQuery.Color( values.background_color ).alpha() ) {
					personDesc.style  = 'background-color:' + values.background_color + ';padding:40px;margin-top:0;';
				}

				return personDesc;
			},

			/**
			 * Builds person icon attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			personIconAttr: function( args, values ) {
				var attr = {
						class: 'fusion-social-network-icon fusion-tooltip fusion-' + args.social_network + ' fusion-icon-' + args.social_network
					},
					link    = '',
					target  = '',
					tooltip = '';

				attr[ 'aria-label' ] = 'fusion-' + args.social_network;

				link   = args.social_link,
				target = values.target;

				if ( 'mail' === args.social_network && 'undefined' !== typeof args.social_link ) {
					link   = 'mailto:' + args.social_link.replace( 'mailto:', '' );
					target = '_self';
				}

				if ( 'phone' === args.social_network && 'undefined' !== typeof args.social_link ) {
					link   = 'tel:' + args.social_link.replace( 'tel:', '' );
					target = '_self';
				}

				attr.href   = link;
				attr.target = target;

				if ( '_blank' === target ) {
					attr.rel = 'noopener noreferrer';
				}

				attr.style = '';

				if ( '' !== args.icon_color ) {
					attr.style = 'color:' + args.icon_color + ';';
				}
				if ( 'yes' === values.social_icon_boxed && '' !== args.box_color ) {
					attr.style += 'background-color:' + args.box_color + ';border-color:' + args.box_color + ';';
				}

				if ( ( 'yes' === values.social_icon_boxed && '' !== values.social_icon_boxed_radius ) || '0' === values.social_icon_boxed_radius ) {
					if ( 'round' === values.social_icon_boxed_radius ) {
						values.social_icon_boxed_radius = '50%';
					}
					attr.style += 'border-radius:' + values.social_icon_boxed_radius + ';';
				}

				if ( '' !== values.social_icon_font_size ) {
					attr.style += 'font-size:' + values.social_icon_font_size + ';';
				}

				if ( 'yes' === values.social_icon_boxed && '' !== values.social_icon_padding ) {
					attr.style += 'padding:' + values.social_icon_padding + ';';
				}

				attr[ 'data-placement' ] = values.social_icon_tooltip;
				tooltip = args.social_network;
				tooltip = ( 'youtube' === tooltip.toLowerCase() ) ? 'YouTube' : tooltip;

				attr[ 'data-title' ] = _.fusionUcFirst( tooltip );
				attr.title         = _.fusionUcFirst( tooltip );

				if ( 'none' !== values.social_icon_tooltip ) {
					attr[ 'data-toggle' ] = 'tooltip';
				}

				return attr;
			},

			/**
			 * Get list of social networks.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			getSocialNetworks: function( values ) {
				var socialNetworks = _.fusionGetSocialNetworks( values );
				socialNetworks     = _.fusionSortSocialNetworks( socialNetworks, values );
				return socialNetworks;
			}
		} );
	} );
}( jQuery ) );
