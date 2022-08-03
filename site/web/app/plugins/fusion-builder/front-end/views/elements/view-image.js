var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Image Frame Element View.
		FusionPageBuilder.fusion_imageframe = FusionPageBuilder.ElementView.extend( {

			/**
			 * Runs after view DOM is patched.
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
				var params = this.model.get( 'params' ),
					link  = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el.find( '.fusion-lightbox' ) );

				this.$el.removeClass( 'fusion-element-alignment-right fusion-element-alignment-left' );
				if ( ! this.flexDisplay() ) {
					if ( 'undefined' !== typeof params.align && ( 'right' === params.align || 'left' === params.align ) ) {
						this.$el.addClass( 'fusion-element-alignment-' + params.align );
					}
				}

				if ( 'object' === typeof jQuery( '#fb-preview' )[ 0 ].contentWindow.avadaLightBox ) {
					if ( 'undefined' !== typeof this.iLightbox ) {
						this.iLightbox.destroy();
					}

					if ( link.length ) {
						this.iLightbox = link.iLightBox( jQuery( '#fb-preview' )[ 0 ].contentWindow.avadaLightBox.prepare_options( 'single' ) );
					}
				}
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {

				if ( 'undefined' !== typeof atts.values.element_content ) {

					this.isFlex 	  = this.flexDisplay();
					// Validate values.
					this.validateValues( atts.values );
					// Create attribute objects
					atts.isFlex  	  	= this.isFlex;
					atts.attr         	= this.buildAttr( atts.values );
					atts.contentAttr  	= this.buildContentAttr( atts.values );
					atts.linkAttr     	= this.buildLinktAttr( atts.values );
					atts.borderRadius 	= this.buildBorderRadius( atts.values );
					atts.imgStyles    	= this.buildImgStyles( atts.values );
					if ( this.isFlex ) {
						atts.responsiveAttr = this.buildResponsiveAttr( atts.values );
					}

					this.buildElementContent( atts );

					atts.liftupClasses      = this.buildLiftupClasses( atts );
					atts.liftupStyles       = this.buildLiftupStyles( atts );

					// Add min height sticky.
					atts.stickyStyles = '' !== atts.values.sticky_max_width ? '<style>.fusion-sticky-container.fusion-sticky-transition .imageframe-cid' + this.model.get( 'cid' ) + '{ max-width:' + _.fusionGetValueWithUnit( atts.values.sticky_max_width ) + ' !important; }</style>' : false;
					atts.filter_style_block = _.fusionGetFilterStyleElem( atts.values, '.imageframe-cid' + this.model.get( 'cid' ), this.model.get( 'cid' )  );
				}

				return atts;
			},

			/**
			 * Modifies the values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values ) {
				values.borderradius  = _.fusionValidateAttrValue( values.borderradius, 'px' );
				values.bordersize    = _.fusionValidateAttrValue( values.bordersize, 'px' );
				values.blur          = _.fusionValidateAttrValue( values.blur, 'px' );
				values.margin_bottom = _.fusionValidateAttrValue( values.margin_bottom, 'px' );
				values.margin_left   = _.fusionValidateAttrValue( values.margin_left, 'px' );
				values.margin_right  = _.fusionValidateAttrValue( values.margin_right, 'px' );
				values.margin_top    = _.fusionValidateAttrValue( values.margin_top, 'px' );

				if ( ! values.style ) {
					values.style = values.style_type;
				}
				if ( values.borderradius && 'bottomshadow' === values.style ) {
					values.borderradius = '0';
				}

				if ( 'round' === values.borderradius ) {
					values.borderradius = '50%';
				}

			},

			/**
			 * Builds responsive container attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildResponsiveAttr: function( values ) {
				var attr = {
					style: '',
					class: ''
				},
				alignLarge = values.align && 'none' !== values.align ? values.align : false,
				alignMedium = values.align_medium && 'none' !== values.align_medium ? values.align_medium : false,
				alignSmall = values.align_small && 'none' !== values.align_small ? values.align_small : false;

				if ( alignLarge ) {
					attr.style += 'text-align:' + alignLarge + ';';
				}

				if ( alignMedium && alignLarge !== alignMedium ) {
					attr[ 'class' ] += ' md-text-align-' + alignMedium;
				}

				if ( alignSmall && alignLarge !== alignSmall ) {
					attr[ 'class' ] += ' sm-text-align-' + alignSmall;
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

				// Main wrapper attributes
				var attr = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-imageframe',
						style: ''
					} ),
					imgStyles,
					styleColorVal = values.stylecolor ? values.stylecolor : '',
					styleColor    = ( 0 === styleColorVal.indexOf( '#' ) ) ? jQuery.Color( styleColorVal ).alpha( 0.3 ).toRgbaString() : jQuery.Color( styleColorVal ).toRgbaString(),
					blur          = values.blur,
					blurRadius    = ( parseInt( blur, 10 ) + 4 ) + 'px';

				if (  ! this.isFlex ) {
					attr[ 'class' ] += ' fusion-imageframe-align-' + values.align;
				}

				attr[ 'class' ] += _.fusionGetStickyClass( values.sticky_display );

				if ( ! values.style ) {
					values.style = values.style_type;
				}

				imgStyles   = '';

				if ( '' != values.bordersize && '0' != values.bordersize && '0px' !== values.bordersize ) {
					imgStyles += 'border:' + values.bordersize + ' solid ' + values.bordercolor + ';';
				}

				if ( '0' != values.borderradius && '0px' !== values.borderradius ) {
					imgStyles += '-webkit-border-radius:' + values.borderradius + ';-moz-border-radius:' + values.borderradius + ';border-radius:' + values.borderradius + ';';

					if ( '50%' === values.borderradius || 100 < parseFloat( values.borderradius ) ) {
						imgStyles += '-webkit-mask-image: -webkit-radial-gradient(circle, white, black);';
					}
				}

				if ( 'glow' === values.style ) {
					imgStyles += '-moz-box-shadow: 0 0 ' + blur + ' ' + styleColor + ';-webkit-box-shadow: 0 0 ' + blur + ' ' + styleColor + ';box-shadow: 0 0 ' + blur + ' ' + styleColor + ';';
				} else if ( 'dropshadow' === values.style ) {
					imgStyles += '-moz-box-shadow: ' + blur + ' ' + blur + ' ' + blurRadius + ' ' + styleColor + ';-webkit-box-shadow: ' + blur + ' ' + blur + ' ' + blurRadius + ' ' + styleColor + ';box-shadow: ' + blur + ' ' + blur + ' ' + blurRadius + ' ' + styleColor + ';';
				}

				if ( '' !== imgStyles ) {
					attr.style += imgStyles;
				}

				attr[ 'class' ] += ' imageframe-' + values.style + ' imageframe-cid' + this.model.get( 'cid' );

				if ( 'bottomshadow' === values.style ) {
					attr[ 'class' ] += ' element-bottomshadow';
				}

				if ( 'liftup' !== values.hover_type ) {
					if ( ! this.isFlex ) {
						if ( 'left' === values.align ) {
							attr.style += 'margin-right:25px;float:left;';
						} else if ( 'right' === values.align ) {
							attr.style += 'margin-left:25px;float:right;';
						}
					}

					attr[ 'class' ] += ' hover-type-' + values.hover_type;
				}

				if ( 'liftup' !== values.hover_type && 'bottomshadow' !== values.style ) {
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
				}

				if ( '' !== values.max_width ) {
					attr.style += 'max-width:' + values.max_width + '';
				}

				if ( 'undefined' !== typeof values[ 'class' ] && '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( 'undefined' !== typeof values.id && '' !== values.id ) {
					attr.id = values.id;
				}

				attr = _.fusionAnimations( values, attr );

				return attr;
			},

			/**
			 * Builds link attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildLinktAttr: function( values ) {

				// Link Attributes
				var linkAttr = {};
				if ( 'yes' === values.lightbox ) {

					// Set the lightbox image to the dedicated link if it is set.
					if ( '' !== values.lightbox_image ) {
						values.pic_link = values.lightbox_image;
					}

					linkAttr.href  = values.pic_link;
					linkAttr[ 'class' ] = 'fusion-lightbox imageframe-shortcode-link';

					if ( '' !== values.gallery_id || '0' === values.gallery_id ) {
						linkAttr[ 'data-rel' ] = 'iLightbox[' + values.gallery_id + ']';
					} else {
						linkAttr[ 'data-rel' ] = 'iLightbox[image-' + this.model.get( 'cid' ) + ']';
					}
				} else if ( values.link ) {
					linkAttr[ 'class' ]  = 'fusion-no-lightbox';
					linkAttr.href   = values.link;
					linkAttr.target = values.linktarget;
					if ( '_blank' === values.linktarget ) {
						linkAttr.rel = 'noopener noreferrer';
					}
				}

				return linkAttr;
			},

			/**
			 * Builds content attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildContentAttr: function( values ) {
				var contentAttr = {},
					title       = '',
					src         = '';

				values.image_id = '';

				// Could add JS to get image dimensions if necessary.
				if ( ! values.element_content ) {
					return 'No Image Set';
				}
				// eslint-disable-next-line no-useless-escape
				src = values.element_content.match( /(src=["\'](.*?)["\'])/ );
				if ( src && 1 < src.length ) {
					src = src[ 2 ];
				} else if ( -1 === values.element_content.indexOf( '<img' ) && '' !== values.element_content ) {
					src = values.element_content;
				}

				if ( 'undefined' !== typeof src && src && '' !== src ) {

					src             = src.replace( '&#215;', 'x' );
					contentAttr.src = src;
					values.pic_link = src;

					if ( 'no' === values.lightbox && '' !== values.link ) {
						contentAttr.title = title;
					} else {
						contentAttr.title = '';
					}

					contentAttr.alt = values.alt;
				}

				return contentAttr;
			},

			/**
			 * Builds border radius.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {string}
			 */
			buildBorderRadius: function( values ) {
				var borderRadius = '';

				if ( values.borderradius && '' !== values.borderradius && 0 !== values.borderradius && '0' !== values.borderradius && '0px' !== values.borderradius ) {
					borderRadius += '-webkit-border-radius:{' + values.borderradius + '};-moz-border-radius:{' + values.borderradius + '};border-radius:{' + values.borderradius + '};';
				}

				return borderRadius;
			},

			/**
			 * Builds image styles.
			 *
			 * @since 2.0
			 * @param {Object} atts - The atts object.
			 * @return {string}
			 */
			buildImgStyles: function( atts ) {
				var imgStyles = '';
				if ( '' !== atts.borderRadius ) {
					imgStyles = ' style="' + atts.borderRadius + '"';
				}

				return imgStyles;
			},

			/**
			 * Builds element content.
			 *
			 * @since 2.0
			 * @param {Object} atts - The atts object.
			 */
			buildElementContent: function( atts ) {
				var imgClasses = 'img-responsive',
					classes = '';

				if ( _.FusionIsValidJSON( atts.contentAttr.src ) ) {
					atts.values.element_content = this.getLogoImages( atts );
				} else {
					atts.values.element_content = '<img ' + _.fusionGetAttributes( atts.contentAttr ) + ' />';
				}

				if ( '' !== atts.values.image_id ) {
					imgClasses += ' wp-image-' + atts.values.image_id;
				}

				// Get custom classes from the img tag.
				// eslint-disable-next-line no-useless-escape
				classes = atts.values.element_content.match( /(class=["\'](.*?)["\'])/ );

				if ( classes && 1 < classes.length ) {
					imgClasses += ' ' + classes[ 2 ];
				}

				imgClasses = 'class="' + imgClasses + '"';

				// Add custom and responsive class and the needed styles to the img tag.
				if ( classes && 'undefined' !== typeof classes[ 0 ] ) {
					atts.values.element_content = atts.values.element_content.replace( classes[ 0 ], imgClasses +  atts.imgStyles );
				} else {
					atts.values.element_content = atts.values.element_content.replace( '/>', imgClasses +  atts.imgStyles + '/>' );
				}

				// Set the lightbox image to the dedicated link if it is set.
				if ( '' !== atts.values.lightbox_image ) {
					atts.values.pic_link = atts.values.lightbox_image;
				}

				if ( 'yes' === atts.values.lightbox || atts.values.link ) {
					atts.values.element_content = '<a ' + _.fusionGetAttributes( atts.linkAttr ) + '>' + atts.values.element_content + '</a>';
				}
			},

			/**
			 * Builds liftup classes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The atts object.
			 * @return {string}
			 */
			buildLiftupClasses: function( atts ) {
				var liftupClasses = '',
					cid = this.model.get( 'cid' );

				if ( 'liftup' === atts.values.hover_type || ( 'bottomshadow' === atts.values.style_type && ( 'none' === atts.values.hover_type || 'zoomin' === atts.values.hover_type || 'zoomout' === atts.values.hover_type ) ) ) {
					if ( 'liftup' === atts.values.hover_type ) {
						liftupClasses = 'imageframe-liftup';
						if ( ! this.isFlex ) {
							if ( 'left' === atts.values.align ) {
								liftupClasses += ' fusion-imageframe-liftup-left';
							} else if ( 'right' === atts.values.align ) {
								liftupClasses += ' fusion-imageframe-liftup-right';
							}
						}

						if ( atts.borderRadius ) {
							liftupClasses += ' imageframe-cid' + cid;
						}
					} else {
						liftupClasses += 'fusion-image-frame-bottomshadow image-frame-shadow-cid' + cid;
					}

					liftupClasses += ' imageframe-cid' + cid;
				}

				return liftupClasses;
			},

			/**
			 * Builds liftup styles.
			 *
			 * @since 2.0
			 * @param {Object} atts - The atts object.
			 * @return {string}
			 */
			buildLiftupStyles: function( atts ) {
				var liftupStyles = '<style>',
					cid = this.model.get( 'cid' ),
					styleColor;

				if ( atts.borderRadius ) {
					liftupStyles += '.imageframe-liftup.imageframe-cid' + cid + ':before{' + atts.borderRadius + '}';
				}

				if ( '' !== atts.values.max_width ) {
					liftupStyles += '.imageframe-cid' + cid + '{max-width:' + atts.values.max_width + '}';
				}

				if ( '' !== atts.values.margin_top ) {
					liftupStyles += 'div.imageframe-cid' + cid + '{margin-top:' + atts.values.margin_top + ' !important;}';
				}

				if ( '' !== atts.values.margin_right ) {
					liftupStyles += 'div.imageframe-cid' + cid + '{margin-right:' + atts.values.margin_right + ' !important;}';
				}

				if ( '' !== atts.values.margin_bottom ) {
					liftupStyles += 'div.imageframe-cid' + cid + '{margin-bottom:' + atts.values.margin_bottom + ' !important;}';
				}

				if ( '' !== atts.values.margin_left ) {
					liftupStyles += 'div.imageframe-cid' + cid + '{margin-left:' + atts.values.margin_left + ' !important;}';
				}

				if ( 'liftup' === atts.values.hover_type || ( 'bottomshadow' === atts.values.style_type && ( 'none' === atts.values.hover_type || 'zoomin' === atts.values.hover_type || 'zoomout' === atts.values.hover_type ) ) ) {
					styleColor = ( 0 === atts.values.stylecolor.indexOf( '#' ) ) ? jQuery.Color( atts.values.stylecolor ).alpha( 0.4 ).toRgbaString() : jQuery.Color( atts.values.stylecolor ).toRgbaString();

					if ( 'liftup' === atts.values.hover_type ) {
						if ( 'bottomshadow' === atts.values.style_type ) {
							liftupStyles  += '.element-bottomshadow.imageframe-cid' + cid + ':before, .element-bottomshadow.imageframe-cid' + cid + ':after{';
							liftupStyles  += '-webkit-box-shadow: 0 17px 10px ' + styleColor + ';box-shadow: 0 17px 10px ' + styleColor + ';}';
						}
					} else {
						liftupStyles += '.imageframe-cid' + cid + '{display: inline-block}';
						liftupStyles  += '.element-bottomshadow.imageframe-cid' + cid + ':before, .element-bottomshadow.imageframe-cid' + cid + ':after{';
						liftupStyles  += '-webkit-box-shadow: 0 17px 10px ' + styleColor + ';box-shadow: 0 17px 10px ' + styleColor + ';}';
					}
				}

				liftupStyles += '</style>';

				return liftupStyles;
			},

			/**
			 * Generate logos images markup.
			 *
			 * @since 3.0
			 * @param {string} images - The atts object.
			 * @return {string}
			 */
			getLogoImages: function( atts ) {

				var data    	= JSON.parse( atts.contentAttr.src ),
					normalUrl 	= data[ 'default' ] && data[ 'default' ].normal &&  data[ 'default' ].normal.url,
					stickyUrl 	= data.sticky && data.sticky.normal && data.sticky.normal.url,
					mobileUrl	= data.mobile && data.mobile.normal && data.mobile.normal.url,
					content 	= '';

				if ( normalUrl ) {
					content += this.getLogoImage( atts, data[ 'default' ], 'fusion-standard-logo' );
				}
				if ( stickyUrl ) {
					content += this.getLogoImage( atts, data.sticky, 'fusion-sticky-logo' );
				}
				if ( mobileUrl ) {
					content += this.getLogoImage( atts, data.mobile, 'fusion-mobile-logo' );
				}

				return content;
			},

			/**
			 * Generate logos image markup.
			 *
			 * @since 3.0
			 * @param {Object} data      - The data object.
			 * @param {string} itemClass - Class for image.
			 * @return {string}
			 */
			getLogoImage: function( atts, data, itemClass ) {
				var content     = '',
					logoUrl    = '',
					logoData   = {
						'url': '',
						'srcset': '',
						'style': '',
						'retina_url': false,
						'width': '',
						'height': '',
						'class': itemClass
					},
					retinaUrl = ( data.retina && data.retina.url ) || '';

				logoUrl              = data.normal.url;
				logoData.srcset = logoUrl + ' 1x';

				// Get retina logo, if default one is not set.
				if ( '' === logoUrl ) {
					logoUrl            = retinaUrl;
					logoData.srcset = logoUrl + ' 1x';
					logoData.url    = logoUrl;
					logoData.width  = data.retina.width;
					logoData.height = data.retina.height;

					if ( '' !== logoData.width ) {
						logoData.style = 'max-height:' + logoData.height + 'px;height:auto;';
					}
				} else {
					logoData.url        = logoUrl;
					logoData.width      = ( data.normal && data.normal.width ) || '';
					logoData.height     = ( data.normal && data.normal.height ) || '';
				}

				if ( data.normal && '' !== data.normal && '' !== logoData.width && '' !== logoData.height ) {
					logoData.retina_url = retinaUrl;
					logoData.srcset    += ', ' + retinaUrl + ' 2x';

					if ( '' !== logoData.width ) {
						logoData.style = 'max-height:' + logoData.height + 'px;height:auto;';
					}
				}

				atts.attr[ 'class' ] += ' has-' + itemClass;

				content = '<img ' + _.fusionGetAttributes( logoData ) + ' />';

				return content;
			}
		} );
	} );
}( jQuery ) );
