/* global FusionPageBuilderViewManager */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Image Carousel Parent View.
		FusionPageBuilder.fusion_images = FusionPageBuilder.ParentElementView.extend( {

			/**
			 * Image map of child element images and thumbs.
			 *
			 * @since 2.0
			 */
			imageMap: {},

			/**
			 * Initial data has run.
			 *
			 * @since 2.0
			 */
			initialData: false,

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				this.appendChildren( '.fusion-carousel-holder' );
				this._refreshJs();
			},

			onRender: function() {
				var columnView = FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );
				setTimeout( function() {
					if ( columnView && 'function' === typeof columnView._equalHeights ) {
						columnView._equalHeights();
					}
				}, 500 );
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {},
					images = window.FusionPageBuilderApp.findShortcodeMatches( atts.params.element_content, 'fusion_image' ),
					imageElement,
					imageElementAtts;

				this.model.attributes.showPlaceholder = false;

				if ( 1 <= images.length ) {
					imageElement     = images[ 0 ].match( window.FusionPageBuilderApp.regExpShortcode( 'fusion_image' ) );
					imageElementAtts = '' !== imageElement[ 3 ] ? window.wp.shortcode.attrs( imageElement[ 3 ] ) : '';

					this.model.attributes.showPlaceholder = ( 'undefined' === typeof imageElementAtts.named || 'undefined' === typeof imageElementAtts.named.image ) ? true : false;
				}

				// Validate values.
				this.validateValues( atts.values );
				this.extras = atts.extras;

				// Create attribute objects
				attributes.attr         = this.buildAttr( atts.values );
				attributes.attrCarousel = this.buildCarouselAttr( atts.values );

				// Whether it has a dynamic data stream.
				attributes.usingDynamic = 'undefined' !== typeof atts.values.multiple_upload && 'Select Images' !== atts.values.multiple_upload;

				// Any extras that need passed on.
				attributes.show_nav = atts.values.show_nav;

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
				values.column_spacing = _.fusionValidateAttrValue( values.column_spacing, 'px' );
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'fusion-image-carousel',
					style: ''
				} );

				attr[ 'class' ] += ' fusion-image-carousel-' + values.picture_size;

				if ( true === this.model.attributes.showPlaceholder ) {
					attr[ 'class' ] += ' fusion-show-placeholder';
				}

				if ( 'yes' === values.lightbox ) {
					attr[ 'class' ] += ' lightbox-enabled';
				}

				if ( 'yes' === values.border ) {
					attr[ 'class' ] += ' fusion-carousel-border';
				}

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
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
			buildCarouselAttr: function( values ) {
				var attr = {
					class: 'fusion-carousel',
					style: ''
				};

				attr[ 'data-autoplay' ]    = values.autoplay;
				attr[ 'data-columns' ]     = values.columns;
				attr[ 'data-itemmargin' ]  = values.column_spacing.toString();
				attr[ 'data-itemwidth' ]   = '180';
				attr[ 'data-touchscroll' ] = values.mouse_scroll;
				attr[ 'data-imagesize' ]   = values.picture_size;
				attr[ 'data-scrollitems' ] = values.scroll_items;

				return attr;
			},

			/**
			 * Extendable function for when child elements get generated.
			 *
			 * @since 2.0.0
			 * @param {Object} modules An object of modules that are not a view yet.
			 * @return {void}
			 */
			onGenerateChildElements: function( modules ) {
				this.addImagesToImageMap( modules, false, false );
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
			addImagesToImageMap: function( childrenData, async, reRender ) {
				var view      = this,
					queryData = {};

				async     = ( 'undefined' === typeof async ) ? true : async;
				reRender  = ( 'undefined' === typeof reRender ) ?  true : reRender;

				view.initialData = true;

				_.each( childrenData, function( child ) {
					var params = ( 'undefined' !== typeof child.get ) ? child.get( 'params' ) : child.params,
						cid    = ( 'undefined' !== typeof child.get ) ? child.get( 'cid' ) : child.cid,
						image  = params.image;

					if ( 'undefined' === typeof view.imageMap[ image ] && image ) {
						queryData[ cid ] = params;
					}
				} );

				// Send this data with ajax or rest.
				if ( ! _.isEmpty( queryData ) ) {
					jQuery.ajax( {
						async: async,
						url: window.fusionAppConfig.ajaxurl,
						type: 'post',
						dataType: 'json',
						data: {
							action: 'get_fusion_image_carousel_children_data',
							children: queryData,
							fusion_load_nonce: window.fusionAppConfig.fusion_load_nonce
						}
					} )
					.done( function( response ) {
						view.updateImageMap( response );

						_.each( response, function( imageSizes, image ) {
							if ( 'undefined' === typeof view.imageMap[ image ] ) {
								view.imageMap[ image ] = imageSizes;
							}
						} );

						view.model.set( 'query_data', response );

						if ( reRender ) {
							view.reRender();
						}
					} );
				} else if ( reRender ) {
					view.reRender();
				}
			},

			/**
			 * Update the view's image map.
			 *
			 * @since 2.0
			 * @param {Object} images - The images object to inject.
			 * @return void
			 */
			updateImageMap: function( images ) {
				var imageMap = this.imageMap;

				_.each( images, function( imageSizes, image ) {
					if ( 'undefined' === typeof imageMap[ image ] ) {
						imageMap[ image ] = imageSizes;
					}
				} );
			}
		} );

		// Image carousel children data callback.
		_.extend( FusionPageBuilder.Callback.prototype, {
			fusion_carousel_images: function( name, value, modelData, args, cid, action, model, view ) { // jshint ignore: line
				view.model.attributes.params[ name ] = value;

				// TODO: on initial load we shouldn't really need to re-render, but may cause issues.
				view.addImagesToImageMap( view.model.children.models, true, view.initialData );

			}
		} );
	} );
}( jQuery ) );
