/* eslint no-mixed-spaces-and-tabs: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {


	jQuery( document ).ready( function() {

		// Fusion Post Cards View.
		FusionPageBuilder.fusion_post_cards = FusionPageBuilder.ElementView.extend( {

			onInit: function() {
				if ( this.model.attributes.markup && '' === this.model.attributes.markup.output ) {
					this.model.attributes.markup.output = this.getComponentPlaceholder();
				}
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 3.3
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Validate values.
				this.validateValues( atts.values );
				this.values = atts.values;
				this.extras = atts.extras;

				// Any extras that need passed on.
				attributes.cid           = this.model.get( 'cid' );
				attributes.attr          = this.buildAttr( atts.values );
				attributes.styles        = this.buildStyleBlock( atts );
				attributes.productsLoop  = this.buildOutput( atts );
				attributes.productsAttrs = this.buildProductsAttrs( atts.values );
				attributes.query_data    = atts.query_data;
				attributes.values        = atts.values;
				attributes.loadMoreText  = _.has( atts.extras, 'load_more_text_' + atts.values.post_type ) ? atts.extras[ 'load_more_text_' + atts.values.post_type ] : atts.extras.load_more_text;

				// carousel.
				if ( 'carousel' === atts.values.layout ) {
					attributes.carouselNav = this.buildCarouselNav();
				}

				if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.max_num_pages ) {
					if ( 'undefined' !== typeof atts.query_data.paged ) {
						attributes.pagination = this.buildPagination( atts );
					}
				}

				return attributes;
			},

			/**
			 * Modifies the values.
			 *
			 * @since  3.3
			 * @param  {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values ) {
				if ( 'undefined' !== typeof values.margin_top && '' !== values.margin_top ) {
					values.margin_top = _.fusionGetValueWithUnit( values.margin_top );
				}

				if ( 'undefined' !== typeof values.margin_right && '' !== values.margin_right ) {
					values.margin_right = _.fusionGetValueWithUnit( values.margin_right );
				}

				if ( 'undefined' !== typeof values.margin_bottom && '' !== values.margin_bottom ) {
					values.margin_bottom = _.fusionGetValueWithUnit( values.margin_bottom );
				}

				if ( 'undefined' !== typeof values.margin_left && '' !== values.margin_left ) {
					values.margin_left = _.fusionGetValueWithUnit( values.margin_left );
				}

				if ( 'undefined' !== typeof values.nav_margin_top && '' !== values.nav_margin_top ) {
					values.nav_margin_top = _.fusionGetValueWithUnit( this.getReverseNum( values.nav_margin_top ) );
				}

				if ( 1 === parseInt( values.columns ) && 'grid' === values.layout ) {
					values.column_spacing = '0px';
				}

				// No delay offering for carousels and sliders.
				if ( 'grid' !== values.layout ) {
					values.animation_delay = 0;
				}
			},

			/**
			 * Builds attributes.
			 *
			 * @since  3.3
			 * @param  {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr         = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-post-cards fusion-post-cards-' + this.model.get( 'cid' )
					} );


				if ( '' !== values.animation_type ) {

					// Grid and has delay, set parent args here, otherwise it will be on children.
					if ( 'grid' === values.layout && 0 !== parseInt( values.animation_delay ) ) {
						attr = _.fusionAnimations( values, attr, false );
						attr[ 'data-animation-delay' ] = values.animation_delay;
						attr[ 'class' ]               += ' fusion-delayed-animation';
					} else {

						// Not grid always no delay, add to parent.
						attr = _.fusionAnimations( values, attr );
					}
				}

				if ( 'slider' === values.layout ) {
					attr[ 'class' ] += ' fusion-slider-sc fusion-flexslider-loading flexslider';

					attr[ 'data-slideshow_autoplay' ]  = 'no' === values.autoplay ? false : true;
					attr[ 'data-slideshow_animation' ] = values.slider_animation;
					attr[ 'data-slideshow_control_nav' ]  = 'no' === values.show_nav ? false : true;
				} else if ( 'carousel' === values.layout ) {
					attr[ 'class' ] += ' fusion-carousel fusion-carousel-responsive';

					attr[ 'data-autoplay' ]      = values.autoplay;
					attr[ 'data-columns' ]       = values.columns;
					attr[ 'data-columnsmedium' ] = values.columns_medium;
					attr[ 'data-columnssmall' ]  = values.columns_small;
					attr[ 'data-itemmargin' ]    = values.column_spacing;
					attr[ 'data-itemwidth' ]     = 180;
					attr[ 'data-touchscroll' ]   = values.mouse_scroll;
					attr[ 'data-imagesize' ]     = 'auto';
					attr[ 'data-scrollitems' ]   = values.scroll_items;
				} else if ( 'grid' === values.layout && 'terms' !== values.source ) {
					attr[ 'class' ] += ' fusion-grid-archive';
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
			 * Builds carousel nav.
			 *
			 * @since 3.3
			 * @return {string}
			 */
			buildCarouselNav: function() {
				var output = '';

				output += '<div class="fusion-carousel-nav">';
				output += '<span class="fusion-nav-prev"></span>';
				output += '<span class="fusion-nav-next"></span>';
				output += '</div>';

				return output;
			},

			/**
			 * Builds items UL attributes.
			 *
			 * @since 3.3
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildProductsAttrs: function( values ) {
				var attr = {
					class: ''
				};

				if ( 'grid' === values.layout ) {
					attr[ 'class' ] += 'fusion-grid fusion-grid-' + values.columns + ' fusion-flex-align-items-' + values.flex_align_items;
				} else if ( 'slider' === values.layout ) {
					attr[ 'class' ] += 'slides';
				} else if ( 'carousel' === values.layout ) {
					attr[ 'class' ] += 'fusion-carousel-holder';
				}

				if ( this.isLoadMore() ) {
					attr[ 'class' ] += ' fusion-grid-container-infinite';
				}

				if ( 'load_more_button' === values.scrolling ) {
					attr[ 'class' ] += ' fusion-grid-container-load-more';
				}
				return attr;
			},

			/**
			 * Builds columns classes.
			 *
			 * @since 3.3
			 * @param {Object} atts - The attributes.
			 * @return {string}
			 */
			buildColumnClasses: function( atts ) {
				var classes = '';

				if ( 'grid' === atts.values.layout ) {
					classes += 'fusion-grid-column fusion-post-cards-grid-column';
				} else if ( 'carousel' === atts.values.layout ) {
					classes += 'fusion-carousel-item';
				}

				if ( 'product' === atts.values.post_type && 'posts' === atts.values.source ) {
					classes += ' product';
				}
				return classes;
			},

			/**
			 * Builds columns wrapper.
			 *
			 * @since 3.3
			 * @param {Object} atts - The attributes.
			 * @return {string}
			 */
			buildColumnWrapper: function( atts ) {
				var classes = '';

				if ( 'carousel' === atts.values.layout ) {
					classes += 'fusion-carousel-item-wrapper';
				}
				return classes;
			},

			/**
			 * Builds the pagination.
			 *
			 * @since 3.3
			 * @param {Object} atts - The attributes.
			 * @return {string}
			 */
			buildPagination: function( atts ) {
				var globalPagination  = atts.extras.pagination_global,
					globalStartEndRange = atts.extras.pagination_start_end_range_global,
					range            = atts.extras.pagination_range_global,
					paged            = '',
					pages            = '',
					paginationCode   = '',
					queryData        = atts.query_data,
					values           = atts.values;

				if ( -1 == values.number_posts ) {
					values.scrolling = 'no';
				}

				if ( 'no' !== values.scrolling ) {
					paged = queryData.paged;
					pages = queryData.max_num_pages;

					paginationCode = _.fusionPagination( pages, paged, range, values.scrolling, globalPagination, globalStartEndRange );
				}
				return paginationCode;
			},

			/**
			 * Check is load more.
			 *
			 * @since 3.3
			 * @return {boolean}
			 */
			isLoadMore: function() {
				return -1 !== jQuery.inArray( this.values.scrolling, [ 'infinite', 'load_more_button' ] );
			},

			/**
			 * Get reverse number.
			 *
			 * @since 3.3
			 * @param {String} value - the number value.
			 * @return {String}
			 */
			getReverseNum: function( value ) {
				return -1 !== value.indexOf( '-' ) ? value.replace( '-', '' ) : '-' + value;
			},

			/**
			 * Get grid width value.
			 *
			 * @since 3.3
			 * @param {String} columns - the columns count.
			 * @return {String}
			 */
			getGridWidthVal: function( columns ) {
				var cols = [ '100%', '50%', '33.3333%', '25%', '20%', '16.6666%' ];
				return cols[ columns - 1 ];
			},

			/**
			 * Builds output.
			 *
			 * @since  3.3
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildOutput: function( atts ) {
				var output = '',
					_self = this,
					lists;

				if ( 'undefined' !== typeof atts.markup && 'undefined' !== typeof atts.markup.output && 'undefined' === typeof atts.query_data ) {
					output = jQuery( jQuery.parseHTML( atts.markup.output ) ).html();
					output = ( 'undefined' === typeof output ) ? atts.markup.output : output;
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.loop_product ) {
					lists = jQuery( '<ul>' + atts.query_data.loop_product + '</ul>' );
					lists.find( 'li' ).each( function() {
						jQuery( this ).removeClass( 'fusion-grid-column fusion-post-cards-grid-column fusion-carousel-item product' )
						.addClass( _self.buildColumnClasses( atts ) )
						.find( '.fusion-column-wrapper' ).removeClass( 'fusion-carousel-item-wrapper' )
						.addClass( _self.buildColumnWrapper( atts ) );

						// Separators are always added into data, just remove if not valid.
						if ( 'grid' !== _self.values.layout || 1 < parseInt( _self.values.columns ) ) {
							jQuery( this ).find( '.fusion-absolute-separator' ).remove();
						} else {
							jQuery( this ).find( '.fusion-absolute-separator' ).css( { display: 'block' } );
						}
					} );
					output = lists.html();
				}
				return output;
			},

			/**
			 * Builds styles.
			 *
			 * @since  3.3
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildStyleBlock: function( atts ) {
				var css, selectors, media, column_spacing, row_spacing,
					self             = this,
					values           = atts.values,
					responsive_style = '',
					nestedCSS        = 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.nested_css ? atts.query_data.nested_css : null;

				this.baseSelector = '.fusion-post-cards.fusion-post-cards-' +  this.model.get( 'cid' );
				this.dynamic_css  = {};

				selectors = [ this.baseSelector + ' .infinite-scroll-hide' ];
				if ( this.isLoadMore() ) {
					this.addCssProperty( selectors, 'display', 'none' );
				}
				if ( 1 < parseInt( values.columns ) ) {
					selectors = [ this.baseSelector + ' ul.fusion-grid' ];
					column_spacing = _.fusionGetValueWithUnit( this.values.column_spacing );
					  this.addCssProperty( selectors, 'margin-right', 'calc((' + column_spacing + ')/ -2)' );
					  this.addCssProperty( selectors, 'margin-left', 'calc((' + column_spacing + ')/ -2)' );
					  selectors = [ this.baseSelector + ' ul.fusion-grid > .fusion-grid-column' ];
					  this.addCssProperty( selectors, 'padding-left', 'calc((' + column_spacing + ')/ 2)' );
					  this.addCssProperty( selectors, 'padding-right', 'calc((' + column_spacing + ')/ 2)' );
					  selectors = [ this.baseSelector + ' ul.fusion-grid > .fusion-grid-column .fusion-column-inner-bg' ];
					  this.addCssProperty( selectors, 'margin-left', 'calc((' + column_spacing + ')/ 2)' );
					  this.addCssProperty( selectors, 'margin-right', 'calc((' + column_spacing + ')/ 2)' );
				}

				if ( 'grid' === this.values.layout ) {
				  row_spacing =  _.fusionGetValueWithUnit( this.values.row_spacing );
				  selectors = [ this.baseSelector + ' ul.fusion-grid' ];
				  this.addCssProperty( selectors, 'margin-top', 'calc((' + row_spacing + ')/ -2)' );
				  selectors = [ this.baseSelector + ' ul.fusion-grid > .fusion-grid-column' ];
				  this.addCssProperty( selectors, 'padding-top', 'calc((' + row_spacing + ')/ 2)' );
				  this.addCssProperty( selectors, 'padding-bottom', 'calc((' + row_spacing + ')/ 2)' );
				  selectors = [ this.baseSelector + ' ul.fusion-grid > .fusion-grid-column > .fusion-column-inner-bg' ];
				  this.addCssProperty( selectors, 'margin-top', 'calc((' + row_spacing + ')/ 2)' );
				  this.addCssProperty( selectors, 'margin-bottom', 'calc((' + row_spacing + ')/ 2)' );
				}

				selectors = [ this.baseSelector ];
				// Margin styles.
				if ( ! this.isDefault( 'margin_top' ) ) {
				  this.addCssProperty( selectors, 'margin-top', values.margin_top );
				}
				if ( ! this.isDefault( 'margin_right' ) ) {
				  this.addCssProperty( selectors, 'margin-right', values.margin_right );
				}
				if ( ! this.isDefault( 'margin_bottom' ) ) {
				  this.addCssProperty( selectors, 'margin-bottom', values.margin_bottom );
				}
				if ( ! this.isDefault( 'margin_left' ) ) {
				  this.addCssProperty( selectors, 'margin-left', values.margin_left );
				}

				selectors = [ this.baseSelector + ' .flex-control-nav' ];
				if ( 'slider' === values.layout ) {
					this.addCssProperty( selectors, 'bottom', values.nav_margin_top );
				}

				// Process children css if it's there.
				if ( Array.isArray( nestedCSS ) ) {
					jQuery.each( nestedCSS, function( index, rules ) {

						if ( Array.isArray( rules ) ) {
							jQuery.each( rules, function( key, rule ) {
								var important = 'undefined' !== typeof rule.important ? rule.important : false;
								self.addCssProperty( self.baseSelector + ' ' + rule.selector, rule.rule, rule.value, important );
							} );
						}

					} );
				}

				css = this.parseCSS();

				if ( 'grid' === values.layout ) {
					_.each( [ 'medium', 'small' ], function( size ) {
						var key = 'columns_' + size;

						// Check for default value.
						if ( this.isDefault( key ) ) {
							return;
						}

						this.dynamic_css  = {};

						// Build responsive styles.
						selectors = [ this.baseSelector + ' .fusion-grid .fusion-grid-column' ];
						this.addCssProperty( selectors, 'width', this.getGridWidthVal( values[ key ] ) + '!important' );

						media            = '@media only screen and (max-width:' + this.extras[ 'visibility_' + size ] + 'px)';
						responsive_style += media + '{' + this.parseCSS() + '}';
					}, this );
					css += responsive_style;
				}
				return ( css ) ? '<style>' + css + '</style>' : '';
			}
		} );
	} );
}( jQuery ) );
