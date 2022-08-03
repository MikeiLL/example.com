var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Image Carousel Child View.
		FusionPageBuilder.fusion_image = FusionPageBuilder.ChildElementView.extend( {

			/**
			 * Runs during render() call.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			onRender: function() {
				var parentView,
					queryData = this.model.get( 'query_data' );

				if ( 'undefined' !== typeof this.model.attributes.selectors ) {
					this.model.attributes.selectors[ 'class' ] += ' ' + this.className;
					this.setElementAttributes( this.$el, this.model.attributes.selectors );
				}

				// Update the parent image map with latest query data images.
				if ( 'undefined' !== typeof queryData ) {
					parentView = window.FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );
					parentView.updateImageMap( queryData );
				}
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			beforePatch: function() {
				var parentView = window.FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );

				if ( true === parentView.model.attributes.showPlaceholder && 'undefined' !== this.model.attributes.params.image && '' !== this.model.attributes.params.image ) {
					this.$el.closest( '.fusion-image-carousel' ).removeClass( 'fusion-show-placeholder' );
					parentView.model.attributes.showPlaceholder = false;
				}

			},

			/**
			 * Runs before element is removed.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			beforeRemove: function() {
				var parentView = window.FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );

				if ( false === parentView.model.attributes.showPlaceholder && 1 === parentView.model.children.length ) {
					this.$el.closest( '.fusion-image-carousel' ).addClass( 'fusion-show-placeholder' );
					parentView.model.attributes.showPlaceholder = true;
				}
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				var parentCid = this.model.get( 'parent' ),
					parentView,
					queryData = this.model.get( 'query_data' );

				if ( 'undefined' !== typeof this.model.attributes.selectors ) {
					this.model.attributes.selectors[ 'class' ] += ' ' + this.className;
					this.setElementAttributes( this.$el, this.model.attributes.selectors );
				}

				// Force re-render for child option changes.
				setTimeout( function() {
					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-element-render-fusion_images', parentCid );
				}, 10 );

				// Update the parent image map with latest query data images.
				if ( 'undefined' !== typeof queryData ) {
					parentView = window.FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );
					parentView.updateImageMap( queryData );
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
				var attributes = {};

				// Validate values.
				this.validateValues( atts.values );
				this.buildAttr( atts.values );
				this.extras = atts.extras;

				// Set selectors.
				this.wrapperSelector();

				// Create attribute objects
				attributes.attrCarouselLink = this.buildCarouselLinkAttr( atts );
				attributes.attrImageWrapper = this.buildImageWrapperAttr( atts );
				attributes.attrItemWrapper  = this.buildItemWrapperAttr( atts );
				attributes.imageElement     = this.buildImageElement( atts );

				// Any extras that need passed on.
				attributes.cid         = this.model.get( 'cid' );
				attributes.parent      = this.model.get( 'parent' );
				attributes.output      = atts.values.element_content;
				attributes.mouseScroll = atts.values.mouse_scroll;
				attributes.link        = atts.values.link;
				attributes.lightbox    = atts.parentValues.lightbox;

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

				// Make sure the title text is not wrapped with an unattributed p tag.
				if ( 'undefined' !== typeof values.element_content ) {
					values.element_content = values.element_content.trim();
					values.element_content = values.element_content.replace( /(<p[^>]+?>|<p>|<\/p>)/img, '' );
				}
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @return {Object}
			 */
			buildAttr: function() {
				var attr = {
					class: 'fusion-carousel-item'
				};

				this.model.set( 'selectors', attr );
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @return {Object}
			 */
			buildItemWrapperAttr: function() {
				var attr = {
					class: 'fusion-carousel-item-wrapper'
				};

				return attr;
			},

			/**
			 * Set image element.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildImageElement: function( atts ) {
				var html         = '',
					imageSize    = 'full',
					values       = atts.values,
					parentValues = atts.parentValues,
					queryData    = atts.query_data,
					parentView   = window.FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );

				if ( 'fixed' === parentValues.picture_size ) {
					imageSize = 'portfolio-two';
					if ( '6' === parentValues.columns || '5' === parentValues.columns || '4' === parentValues.columns ) {
						imageSize = 'blog-medium';
					}
				}

				if ( 'undefined' !== typeof queryData && 'undefined' !== typeof queryData[ values.image ] ) {
					html = queryData[ values.image ][ imageSize ];
				} else if ( 'undefined' !== typeof parentView.imageMap[ values.image ] ) {
					html = parentView.imageMap[ values.image ][ imageSize ];
				} else {
					html = '<img src="' + values.image + '" alt="' + values.alt + '"/>';
				}

				return html;
			},

			/**
			 * Set selectors.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			wrapperSelector: function() {
				var wrapperSelector = {
					class: 'fusion-carousel-item'
				};

				this.model.set( 'selectors', wrapperSelector );
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildCarouselLinkAttr: function( atts ) {
				var attr         = {},
					values       = atts.values,
					parentValues = atts.parentValues,
					queryData    = atts.query_data;

				if ( 'yes' === parentValues.lightbox ) {

					if ( ! values.link || null === values.link ) {
						values.link = values.image;
					}

					attr[ 'data-rel' ] = 'iLightbox[image_carousel_' + this.model.get( 'parent' ) + ']';

					if ( 'undefined' !== typeof queryData && 'undefined' !== typeof queryData.image_data ) {
						attr[ 'data-caption' ] = queryData.image_data.caption;
						attr[ 'data-title' ]   = queryData.image_data.title;
						attr[ 'aria-label' ]   = queryData.image_data.title;
					}
				}

				attr.href = values.link;

				attr.target = values.linktarget;
				if ( '_blank' === values.linktarget ) {
					attr.rel = 'noopener noreferrer';
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
			buildImageWrapperAttr: function( atts ) {
				var attr     = {
						class: 'fusion-image-wrapper'
					},
					parentValues = atts.parentValues;

				if ( parentValues.hover_type ) {
					attr[ 'class' ] += ' hover-type-' + parentValues.hover_type;
				}

				return attr;
			}
		} );
	} );
}( jQuery ) );
