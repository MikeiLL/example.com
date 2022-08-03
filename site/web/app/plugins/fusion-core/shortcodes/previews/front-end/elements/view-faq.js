/* global data */
/* jshint -W024 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// FAQ Element View.
		FusionPageBuilder.fusion_faq = FusionPageBuilder.ElementView.extend( {

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @returns {void}
			 */
			beforePatch: function() {
				var toggles = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el.find( '.panel-collapse' ) );

				toggles.removeData();
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0.0
			 * @returns null
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Validate values.
				this.validateValues( atts.values );

				attributes.attr        = this.buildAttr( atts.values );
				attributes.attrWrapper = this.buildWrapperAttr( atts.values );
				attributes.faqFilters  = '';
				attributes.faqList     = '';
				attributes.styles      = this.buildStyles( atts );

				if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.faq_items ) {
					attributes.faqFilters = this.buildFaqFilters( atts );
					attributes.faqList    = this.buildFaqList( atts );
				}

				attributes.query_data = atts.query_data;

				return attributes;
			},

			validateValues: function( values ) {
				values.cat_slugs       = values.cats_slug;
				values.icon_size       = _.fusionValidateAttrValue( values.icon_size, 'px' );
				values.title_font_size = _.fusionValidateAttrValue( values.title_font_size, 'px' );
				values.border_size     = _.fusionValidateAttrValue( values.border_size, 'px' );
			},

			buildAttr: function( values ) {
				var attr = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'fusion-faq-shortcode',
					style: ''
				} );

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				return attr;
			},

			buildWrapperAttr: function( values ) {
				var attr = {
						class: 'panel-group'
					},
					cid = this.model.get( 'cid' );

				if ( 'right' === values.icon_alignment ) {
					attr[ 'class' ] += ' fusion-toggle-icon-right';
				}

				if ( '0' === values.icon_boxed_mode || 0 === values.icon_boxed_mode || 'no' === values.icon_boxed_mode ) {
					attr[ 'class' ] += ' fusion-toggle-icon-unboxed';
				}

				attr.id = 'accordian-cid' + cid;

				return attr;
			},

			buildFaqFilters: function( atts ) {
				var queryData   = atts.query_data,
					values      = atts.values,
					extras      = atts.extras,
					html        = '',
					catSlugs    = '',
					excludeCats = '',
					firstFilter;

				// Transform $cat_slugs to array.
				if ( '' !== values.cat_slugs ) {
					catSlugs = values.cat_slugs.replace( /\s+/g, '' );
					catSlugs = catSlugs.split( ',' );
				} else {
					catSlugs = [];
				}

				// Transform $cats_to_exclude to array.
				if ( '' !== values.exclude_cats ) {
					excludeCats = values.exclude_cats.replace( /\s+/g, '' );
					excludeCats = excludeCats.split( ',' );
				} else {
					excludeCats = [];
				}

				if ( false !== queryData.faq_terms && 'no' !== values.filters ) {

					html += '<ul class="fusion-filters clearfix" style="display:block;">';

					// Check if the "All" filter should be displayed.
					firstFilter = true;
					if ( 'yes' === values.filters ) {
						html += '<li class="fusion-filter fusion-filter-all fusion-active">';
						html += '<a data-filter="*" href="#">' + extras.all_text + '</a>';
						html += '</li>';
						firstFilter = false;
					}

					// Loop through the terms to setup all filters.
					_.each( queryData.faq_terms, function( faqTerm ) {

						// Only display filters of non excluded categories.
						if ( -1 === jQuery.inArray( faqTerm.slug, excludeCats ) ) {

							// Check if current term is part of chosen terms, or if no terms at all have been chosen.
							if ( ( 0 < catSlugs.length && -1 !== jQuery.inArray( faqTerm.slug, catSlugs ) ) || 0 === catSlugs.length ) {

								// If the "All" filter is disabled, set the first real filter as active.
								if ( firstFilter ) {
									html += '<li class="fusion-filter fusion-active">';
									html += '<a data-filter=".' + decodeURI( faqTerm.slug ) + '" href="#">' + faqTerm.name + '</a>';
									html += '</li>';
									firstFilter = false;
								} else {
									html += '<li class="fusion-filter">';
									html += '<a data-filter=".' + decodeURI( faqTerm.slug ) + '" href="#">' + faqTerm.name + '</a>';
									html += '</li>';
								}
							}
						}
					} );

					html += '</ul>';
				}

				return html;
			},

			buildFaqList: function( atts ) {
				var queryData = atts.query_data,
					values    = atts.values,
					cid       = this.model.get( 'cid' ),
					html      = '';

				_.each( queryData.faq_items, function( faq ) {

					// If used on a faq item itself, this is needed to prevent an infinite loop.
					if ( 'undefined' !== typeof data && faq.id === data.postID ) {
						return;
					}

					if ( '1' === values.boxed_mode || 1 === values.boxed_mode || 'yes' === values.boxed_mode ) {
						faq.post_classes += ' fusion-toggle-no-divider fusion-toggle-boxed-mode';
					} else if ( '0' === values.divider_line || 0 === values.divider_line || 'no' === values.divider_line ) {
						faq.post_classes += ' fusion-toggle-no-divider';
					}

					html += '<div class="fusion-panel panel-default fusion-faq-post ' + faq.post_classes + '">';

					// Get the rich snippets for the post.
					html += faq.rich_snippets;

					html += '<div class="panel-heading">';
					html += '<h4 class="panel-title toggle">';

					if ( 'toggles' === values.type ) {
						html += '<a data-toggle="collapse" class="collapsed" data-target="#collapse-' + cid + '-' + faq.id + '" href="#collapse-' + cid + '-' + faq.id + '">';
					} else {
						html += '<a data-toggle="collapse" class="collapsed" data-parent="#accordian-cid' + cid + '" data-target="#collapse-' + cid + '-' + faq.id + '" href="#collapse-' + cid + '-' + faq.id + '">';
					}

					html += '<div class="fusion-toggle-icon-wrapper"><i class="fa-fusion-box" aria-hidden="true"></i></div>';
					html += '<div class="fusion-toggle-heading">' + faq.title + '</div>';
					html += '</a>';
					html += '</h4>';
					html += '</div>';

					html += '<div id="collapse-' + cid + '-' + faq.id + '" class="panel-collapse collapse">';
					html += '<div class="panel-body toggle-content post-content">';

					// Render the featured image of the post.
					if ( ( '1' === values.featured_image || 'yes' === values.featured_image ) && false !== faq.thumbnail ) {

						html += '<div class="fusion-flexslider flexslider fusion-flexslider-loading post-slideshow fusion-post-slideshow">';
						html += '<ul class="slides">';
						html += '<li>';
						html += '<a href="' + faq.thumbnail_full + '" data-rel="iLightbox[gallery]" data-title="' + faq.thumbnail_title + '" data-caption="' + faq.thumbnail_caption + '">';
						html += '<span class="screen-reader-text">View Larger Image</span>';
						html += faq.thumbnail;
						html += '</a>';
						html += '</li>';
						html += '</ul>';
						html += '</div>';
					}

					html += faq.content;
					html += '</div>';
					html += '</div>';
					html += '</div>';
				} );

				return html;
			},

			buildStyles: function( atts ) {
				var values = atts.values,
					cid    = this.model.get( 'cid' ),
					styles = '';

				if ( '1' === values.boxed_mode || 1 === values.boxed_mode || 'yes' === values.boxed_mode ) {
					if ( '' !== values.hover_color ) {
						styles += '#accordian-cid' + cid + ' .fusion-panel:hover{ background-color: ' + values.hover_color + ' }';
						styles += '#accordian-cid' + cid + ' .fusion-panel.hover{ background-color: ' + values.hover_color + ' }';
					}
					styles += ' #accordian-cid' + cid + ' .fusion-panel {';
					if ( '' !== values.border_color ) {
						styles += ' border-color:' + values.border_color + ';';
					}
					if ( '' !== values.border_size ) {
						styles += ' border-width:' + values.border_size + ';';
					}
					if ( '' !== values.background_color ) {
						styles += ' background-color:' + values.background_color + ';';
					}
					styles += ' }';
				}

				if ( '' !== values.icon_size ) {
					styles += '.fusion-accordian #accordian-cid' + cid + ' .panel-title a .fa-fusion-box:before{ font-size: ' + values.icon_size + ';}';
				}
				if ( '' !== values.icon_color ) {
					styles += '.fusion-accordian #accordian-cid' + cid + ' .panel-title a .fa-fusion-box{ color: ' + values.icon_color + ';}';
				}
				if ( '' !== values.icon_alignment && 'right' === values.icon_alignment ) {
					styles += '.fusion-accordian #accordian-cid' + cid + '.fusion-toggle-icon-right .fusion-toggle-heading{ margin-right: ' + _.fusionValidateAttrValue( parseFloat( values.icon_size ) + 18, 'px' ) + ';}';
				}

				if ( '' !== values.title_font_size ) {
					styles += '.fusion-accordian #accordian-cid' + cid + ' .panel-title a{font-size:' + values.title_font_size + ';}';
				}

				if ( ( '1' === values.icon_boxed_mode || 'yes' === values.icon_boxed_mode ) && '' !== values.icon_box_color ) {
					styles += '.fusion-accordian #accordian-cid' + cid + ' .fa-fusion-box { background-color: ' + values.icon_box_color + ';border-color: ' + values.icon_box_color + ';}';
				}

				if ( '' !== values.toggle_hover_accent_color ) {
					styles += '.fusion-accordian #accordian-cid' + cid + ' .panel-title a:hover { color: ' + values.toggle_hover_accent_color + ';}';
					styles += '.fusion-accordian #accordian-cid' + cid + ' .panel-title a.hover { color: ' + values.toggle_hover_accent_color + ';}';

					if ( '1' === values.icon_boxed_mode || 'yes' === values.icon_boxed_mode ) {
						styles += '.fusion-accordian #accordian-cid' + cid + ' .panel-title .active .fa-fusion-box,';
						styles += '.fusion-accordian #accordian-cid' + cid + ' .panel-title a:hover .fa-fusion-box { background-color: ' + values.toggle_hover_accent_color + '!important;border-color: ' + values.toggle_hover_accent_color + '!important;}';
						styles += '.fusion-accordian #accordian-cid' + cid + ' .panel-title a.hover .fa-fusion-box { background-color: ' + values.toggle_hover_accent_color + '!important;border-color: ' + values.toggle_hover_accent_color + '!important;}';
					} else {
						styles += '.fusion-accordian #accordian-cid' + cid + '.fusion-toggle-icon-unboxed .panel-title a:hover .fa-fusion-box { color: ' + values.toggle_hover_accent_color + '; }';
						styles += '.fusion-accordian #accordian-cid' + cid + '.fusion-toggle-icon-unboxed .panel-title a.hover .fa-fusion-box { color: ' + values.toggle_hover_accent_color + '; }';
					}
				}

				return styles;
			}
		} );
	} );
}( jQuery ) );
