/* global fusionAppConfig, FusionPageBuilderViewManager, imagesLoaded */
/* jshint -W098 */
/* eslint no-unused-vars: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Gallery View.
		FusionPageBuilder.fusion_gallery = FusionPageBuilder.ParentElementView.extend( {

			/**
			 * Image map of child element images and thumbs.
			 *
			 * @since 2.0
			 */
			imageMap: {
				images: {}
			},

			onInit: function() {
				this.fusionIsotope = new FusionPageBuilder.IsotopeManager( {
					selector: '.fusion-gallery-layout-grid, .fusion-gallery-layout-masonry',
					layoutMode: 'packery',
					itemSelector: '.fusion-gallery-column',
					isOriginLeft: jQuery( 'body.rtl' ).length ? false : true,
					resizable: true,
					initLayout: true,
					view: this
				} );
			},

			onRender: function() {
				var galleryElements = this.$el.find( '.fusion-gallery-column' ),
					self = this;

				imagesLoaded( galleryElements, function() {
					self.fusionIsotope.updateLayout();

					self.setOutlineControlsPosition();
				} );
			},

			/**
			 * Sets position of outlines and controls for the child elements to match column spacing..
			 *
			 * @since 2.0
			 * @return {void}
			 */
			setOutlineControlsPosition: function() {
				var cid = this.model.get( 'cid' ),
					params = this.model.get( 'params' ),
					halfColumnSpacing = ( parseFloat( params.column_spacing ) / 2 ) + 'px',
					css = '';

				this.$el.children( 'style' ).remove();

				css += '<style type="text/css">';
				css += '.fusion-builder-live:not(.fusion-builder-ui-wireframe) div[data-cid="' + cid + '"] .fusion-builder-live-child-element:hover:after{ margin:' + halfColumnSpacing + ';}';
				css += '.fusion-builder-live:not(.fusion-builder-ui-wireframe) div[data-cid="' + cid + '"] .fusion-builder-live-child-element:hover .fusion-builder-module-controls-container{ bottom: ' + halfColumnSpacing + '; right:' + halfColumnSpacing + ';}';
				css += '</style>';

				this.$el.prepend( css );
			},

			/**
			 * Extendable function for when child elements get generated.
			 *
			 * @since 2.0.0
			 * @param {Object} modules An object of modules that are not a view yet.
			 * @return {void}
			 */
			onGenerateChildElements: function( modules ) {
				var i = 1;

				this.fusionIsotope.init();
				this.addImagesToImageMap( modules, false, false );

				// Set child counter. Used for grid layout clearfix.
				_.each( this.model.children, function( child ) {
					child.set( 'counter', i );
					i++;
				} );
			},

			/**
			 * Add images to the view's image map.
			 *
			 * @since 2.0
			 * @param {Object} childrenData - The children for which images need added to the map.
			 * @param bool async - Determines if the AJAX call should be async.
			 * @param bool async - Determines if the view should be re-rendered.
			 * @return void
			 */
			addImagesToImageMap: function( childrenData, async, reRender, forceQuery ) {
				var view      = this,
					queryData = {};

				async    = ( 'undefined' === typeof async ) ? true : async;
				reRender = ( 'undefined' === typeof reRender ) ?  true : reRender;

				_.each( childrenData, function( child ) {
					var params = ( 'undefined' !== typeof child.get ) ? child.get( 'params' ) : child.params,
						cid    = ( 'undefined' !== typeof child.get ) ? child.get( 'cid' ) : child.cid,
						image  = params.image;

					if ( 'undefined' === typeof view.imageMap.images[ params.image_id ] || forceQuery ) {
						queryData[ params.image_id ] = params;
					}
				} );

				// Send this data with ajax or rest.
				if ( ! _.isEmpty( queryData ) ) {
					jQuery.ajax( {
						async: async,
						url: fusionAppConfig.ajaxurl,
						type: 'post',
						dataType: 'json',
						data: {
							action: 'get_fusion_gallery',
							children: queryData,
							fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
							gallery: view.model.get( 'params' )
						}
					} )
					.done( function( response ) {
						view.updateImageMap( response, forceQuery );
						view.model.set( 'query_data', response );

						if ( reRender ) {
							view.reRender();
						}
					} );
				}
			},

			/**
			 * Update the view's image map.
			 *
			 * @since 2.0
			 * @param {Object} images - The images object to inject.
			 * @return void
			 */
			updateImageMap: function( images, forceUpdate ) {
				var imageMap = this.imageMap;

				_.each( images.images, function( image, imageId ) {
					if ( 'undefined' === typeof imageMap.images[ imageId ] || forceUpdate ) {
						imageMap.images[ imageId ] = image;
					}
				} );

				// TODO: needed ?
				this.imageMap = imageMap;
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				this.appendChildren( '.fusion-gallery-container' );
				this.fusionIsotope.reInit();
				this.checkVerticalImages();

				this.setOutlineControlsPosition();
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

				attributes.values     = atts.values;
				attributes.query_data = atts.query_data;

				// // Create attribute objects.
				attributes.attr       = this.buildAttr( atts.values );

				// Whether it has a dynamic data stream.
				attributes.usingDynamic = 'undefined' !== typeof atts.values.multiple_upload && 'Select Images' !== atts.values.multiple_upload;

				return attributes;
			},

			checkVerticalImages: function() {
				var container = this.$el.find( '.fusion-gallery-layout-grid, .fusion-gallery-layout-masonry' );

				if ( container.hasClass( 'fusion-gallery-layout-masonry' ) && 0 < container.find( '.fusion-grid-column:not(.fusion-grid-sizer)' ).not( '.fusion-element-landscape' ).length ) {
					container.addClass( 'fusion-masonry-has-vertical' );
				} else {
					container.removeClass( 'fusion-masonry-has-vertical' );
				}
			},

			/**
			 * Modifies the values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values ) {
				values.column_spacing = ( parseFloat( values.column_spacing ) / 2 ) + 'px';
				values.bordersize     = _.fusionValidateAttrValue( values.bordersize, 'px' );
				values.border_radius  = _.fusionValidateAttrValue( values.border_radius, 'px' );

				if ( 'round' === values.border_radius ) {
					values.border_radius = '50%';
				}
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var totalNumOfColumns = this.model.children.length,
					attr              = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-gallery fusion-gallery-container fusion-child-element fusion-grid-' + values.columns + ' fusion-columns-total-' + totalNumOfColumns + ' fusion-gallery-layout-' + values.layout
					} ),
					margin;

				if ( values.column_spacing ) {
					margin = ( -1 ) * parseFloat( values.column_spacing );
					attr.style = 'margin:' + margin + 'px;';
				}

				attr[ 'data-empty' ] = this.emptyPlaceholderText;

				return attr;
			}

		} );

		// Fetch image_date for single image
		_.extend( FusionPageBuilder.Callback.prototype, {
			fusion_gallery_image: function( name, value, modelData, args, cid, action, model, elementView ) {
				var queryData  = {},
					reRender   = true,
					async      = true,
					parentView = FusionPageBuilderViewManager.getView( model.attributes.parent ),
					params     = jQuery.extend( true, {}, model.attributes.params ),
					imageId;

				params[ name ] = value;
				imageId        = params.image_id;

				if ( 'undefined' === typeof parentView.imageMap.images[ imageId ] && 'undefined' !== typeof value && '' !== value ) {
					queryData[ imageId ] = params;
				}

				// Send this data with ajax or rest.
				if ( ! _.isEmpty( queryData ) ) {
					jQuery.ajax( {
						async: async,
						url: fusionAppConfig.ajaxurl,
						type: 'post',
						dataType: 'json',
						data: {
							action: 'get_fusion_gallery',
							children: queryData,
							fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
							gallery: parentView.model.get( 'params' )
						}
					} )
					.done( function( response ) {
						parentView.updateImageMap( response );

						if ( 'undefined' !== typeof response.images[ value ] ) {
							if ( 'undefined' !== typeof response.images[ value ].image_data && 'image_id' === name && 'undefined' !== typeof response.images[ value ].image_data.url ) {
								if ( ! args.skip ) {
									elementView.changeParam( 'image', response.images[ value ].image_data.url );
								}
							}
						}

						elementView.changeParam( name, value );

						if ( reRender ) {
							elementView.reRender();
						}
					} );
				} else {
					if ( ! args.skip && 'undefined' !== typeof name ) {
						elementView.changeParam( name, value );
					}
					if ( reRender ) {
						elementView.reRender();
					}
				}
			}
		} );

		_.extend( FusionPageBuilder.Callback.prototype, {
			fusion_gallery_images: function( name, value, modelData, args, cid, action, model, view ) {
				view.model.attributes.params[ name ] = value;
				view.addImagesToImageMap( view.model.children.models, true, true, true );
			}
		} );

	} );
}( jQuery ) );
