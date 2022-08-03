/* global FusionPageBuilderApp, FusionPageBuilderEvents, fusionAllElements, FusionPageBuilderViewManager, fusionMultiElements */
/* jshint -W024 */
/* eslint no-unused-vars: 0 */
/* eslint guard-for-in: 0 */
/* eslint no-shadow: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function( $ ) {

	$( document ).ready( function() {

		// Multi Element Sortable UI
		FusionPageBuilder.MultiElementSortablesView = window.wp.Backbone.View.extend( {
			initialize: function() {

				if ( true === FusionPageBuilderApp.shortcodeGenerator ) {
					FusionPageBuilderApp.shortcodeGeneratorMultiElement = true;
				}

				this.listenTo( FusionPageBuilderEvents, 'fusion-multi-element-edited', this.generateContent );
				this.listenTo( FusionPageBuilderEvents, 'fusion-multi-remove-sortables-view', this.removeView );

				this.element_type = this.$el.data( 'element_type' );

				this.child_views = [];

				this.fetchIds = [];

				this.childIds = [];

				this.updateGallery = false;

				this.$el.attr( 'data-cid', this.attributes.cid );

				this.$sortable_options = this.$el.find( '.fusion-builder-sortable-options' );

				this.$sortable_options.sortable( {
					axis: 'y',
					cancel: '.fusion-builder-multi-setting-remove, .fusion-builder-multi-setting-options, .fusion-builder-multi-setting-clone',
					helper: 'clone',

					update: function() {
						FusionPageBuilderEvents.trigger( 'fusion-multi-element-edited' );
					}
				} );

				this.$add_sortable_item = this.$el.find( '.fusion-builder-add-multi-child' ).addClass( 'fusion-builder-add-sortable-initial' );
			},

			events: {
				'click .fusion-builder-add-multi-child': 'addChildElement'
			},

			render: function() {
				return this;
			},

			addChildElement: function( event ) {

				var params = {},
					defaultParams,
					value,
					allowGenerator;

				if ( event ) {
					event.preventDefault();
				}

				defaultParams = fusionAllElements[ this.element_type ].params;

				allowGenerator = ( 'undefined' !== typeof fusionAllElements[ this.element_type ].allow_generator ) ? fusionAllElements[ this.element_type ].allow_generator : '';

				// Process default parameters from shortcode
				_.each( defaultParams, function( param )  {
					if ( _.isObject( param.value ) ) {
						value = param[ 'default' ];
					} else {
						value = param.value;
					}
					params[ param.param_name ] = value;
				} );

				this.model.collection.add( [
					{
						type: 'element',
						element_type: this.element_type,
						cid: FusionPageBuilderViewManager.generateCid(),
						view: this,
						created: 'manually',
						multi: 'multi_element_child',
						child_element: 'true',
						parent: this.attributes.cid,
						params: params,
						allow_generator: allowGenerator
					}
				] );

				this.$add_sortable_item.removeClass( 'fusion-builder-add-sortable-initial' );

				FusionPageBuilderEvents.trigger( 'fusion-multi-element-edited' );

			},

			generateContent: function() {
				var content = '';

				this.$sortable_options.find( 'li' ).each( function() {
					var $thisEl = $( this );
					content += FusionPageBuilderApp.generateElementShortcode( $thisEl, false );
				} );

				this.$el.parents().find( '#fusion_builder_content_main' ).html( content );

				if ( ! this.$sortable_options.find( 'li' ).length ) {
					this.$add_sortable_item.addClass( 'fusion-builder-add-sortable-initial' );
				} else {
					this.$add_sortable_item.removeClass( 'fusion-builder-add-sortable-initial' );
				}

			},

			updateGalleryContent: function() {
				var content = '',
					self = this,
					parentModel = FusionPageBuilderApp.collection.find( function( model ) {
						return model.get( 'cid' ) === self.attributes.parentCid;
					} );

				this.$sortable_options.find( 'li' ).each( function() {
					var $thisEl = $( this );
					content += FusionPageBuilderApp.generateElementShortcode( $thisEl, false );
				} );

				parentModel.attributes.params.element_content = content;

				this.$el.parents().find( '#fusion_builder_content_main' ).html( content );

				if ( ! this.$sortable_options.find( 'li' ).length ) {
					this.$add_sortable_item.addClass( 'fusion-builder-add-sortable-initial' );
				} else {
					this.$add_sortable_item.removeClass( 'fusion-builder-add-sortable-initial' );
				}

				// Update child previews
				FusionPageBuilderEvents.trigger( 'fusion-multi-child-update-preview' );

				// Update shortcodes
				FusionPageBuilderEvents.trigger( 'fusion-element-added' );

				this.fetchIds = [];
				this.childIds = [];
			},

			removeView: function( event ) {
				if ( event ) {
					event.preventDefault();
				}

				this.remove();
			},

			generateMultiElementChildSortables: function( content, moduleType, fixSettingsLvl, parentAtts ) {
				var thisEl        = this,
					shortcodeTags = jQuery.map( fusionMultiElements, function( val, i ) { // jshint ignore:line
						return val;
					} ).join( '|' ),
					regExp      = window.wp.shortcode.regexp( shortcodeTags ),
					innerRegExp = FusionPageBuilderApp.regExpShortcode( shortcodeTags ),
					matches     = content.match( regExp );

				this.updateGallery = false;

				if ( '' !== content ) {
					this.$add_sortable_item.removeClass( 'fusion-builder-add-sortable-initial' );
				}

				_.each( matches, function( shortcode ) {
					var shortcodeElement     = shortcode.match( innerRegExp ),
						shortcodeName        = shortcodeElement[ 2 ],
						shortcodeAttributes  = '' !== shortcodeElement[ 3 ] ? window.wp.shortcode.attrs( shortcodeElement[ 3 ] ) : '',
						shortcodeContent     = shortcodeElement[ 5 ],
						elementName          = '',
						moduleCID            = FusionPageBuilderViewManager.generateCid(), // jshint ignore:line
						prefixedAttributes   = { params: ( {} ) },

						// TODO: check if needed.  Commented out for FB item 420.
						//shortcodesInContent = 'undefined' !== typeof shortcodeContent && '' !== shortcodeContent && shortcodeContent.match( regExp ),

						// Check if shortcode allows generator
						allowGenerator = 'undefined' !== typeof fusionAllElements[ shortcodeName ].allow_generator ? fusionAllElements[ shortcodeName ].allow_generator : '',
						moduleSettings,
						key,
						prefixedKey,
						dependencyOption,
						dependencyOptionValue,
						moduleContent;

					if ( 'undefined' !== typeof shortcodeAttributes.named && 'undefined' !== typeof shortcodeAttributes.named.title && shortcodeAttributes.named.title.length ) {
						elementName = shortcodeAttributes.named.title;
					} else if ( 'undefined' !== typeof shortcodeAttributes.named && 'undefined' !== typeof shortcodeAttributes.named.title_front && shortcodeAttributes.named.title_front.length ) {
						elementName = shortcodeAttributes.named.title_front;
					} else if ( 'undefined' !== typeof shortcodeAttributes.named && 'undefined' !== typeof shortcodeAttributes.named.name && shortcodeAttributes.named.name.length ) {
						elementName = shortcodeAttributes.named.name;

						if ( 'undefined' !== typeof shortcodeAttributes.named.company && shortcodeAttributes.named.company.length ) {
							elementName += ', ' + shortcodeAttributes.named.company;
						}
					} else if ( 'undefined' !== typeof shortcodeAttributes.named && 'undefined' !== typeof shortcodeAttributes.named.image && shortcodeAttributes.named.image.length && 'fusion_testimonial' !== shortcodeName ) {
						elementName = shortcodeAttributes.named.image;

						// If contains backslash, retrieve only last part.
						if ( -1 !== elementName.indexOf( '/' ) && -1 === elementName.indexOf( '[' ) ) {
							elementName = elementName.split( '/' );
							elementName = elementName.slice( -1 )[ 0 ];
						}
					} else if ( 'undefined' !== typeof shortcodeAttributes.named && 'image' === shortcodeAttributes.named.type && 'undefined' !== typeof shortcodeContent && shortcodeContent.length ) {
						elementName = shortcodeContent;

						// If contains backslash, retrieve only last part.
						if ( -1 !== elementName.indexOf( '/' ) && -1 === elementName.indexOf( '[' ) ) {
							elementName = elementName.split( '/' );
							elementName = elementName.slice( -1 )[ 0 ];
						}
					} else if ( 'undefined' !== typeof shortcodeAttributes.named && 'undefined' !== typeof shortcodeAttributes.named.video && shortcodeAttributes.named.video.length ) {
						elementName = shortcodeAttributes.named.video;
					} else if ( 'undefined' !== typeof shortcodeAttributes.named && 'undefined' !== typeof shortcodeContent && shortcodeContent.length ) {
						elementName = shortcodeContent;
					}

					// Remove HTML tags but keep quotation marks etc.
					elementName = jQuery( '<div/>' ).html( elementName ).text();
					elementName = jQuery( '<div/>' ).html( elementName ).text();

					moduleSettings = {
						type: 'element',
						element_type: moduleType,
						element_name: elementName,
						cid: FusionPageBuilderViewManager.generateCid(),
						view: thisEl,
						created: 'auto',
						multi: 'multi_element_child',
						child_element: 'true',
						allow_generator: allowGenerator,
						params: {},
						parent: thisEl.attributes.cid
					};

					if ( _.isObject( shortcodeAttributes.named ) ) {

						for ( key in shortcodeAttributes.named ) {

							prefixedKey = key;

							if ( ( 'fusion_builder_column' === shortcodeName && 'type' === prefixedKey ) || ( 'fusion_builder_column_inner' === shortcodeName && 'type' === prefixedKey ) ) {
								prefixedKey = 'layout';

								prefixedAttributes[ prefixedKey ] = shortcodeAttributes.named[ key ];
							}

							prefixedAttributes.params[ prefixedKey ] = shortcodeAttributes.named[ key ];

						}

						moduleSettings = _.extend( moduleSettings, prefixedAttributes );
					}

					// TODO: check if needed.  Commented out for FB item 420.
					// if ( ! shortcodesInContent ) {
					moduleSettings.params.element_content = shortcodeContent;

					// }.

					// Set module settings for modules with dependency options
					if ( 'undefined' !== typeof fusionAllElements[ shortcodeName ].option_dependency ) {

						dependencyOption      = fusionAllElements[ shortcodeName ].option_dependency;
						dependencyOptionValue = prefixedAttributes.params[ dependencyOption ];
						moduleContent         = prefixedAttributes.params.element_content;
						prefixedAttributes.params[ dependencyOptionValue ] = moduleContent;

					}

					// Fix for deprecated 'settings_lvl' attribute
					if ( true === fixSettingsLvl ) {
						if ( 'fusion_content_box' === moduleType ) {

							// Reset values that are inherited from parent
							moduleSettings.params.iconcolor              = '';
							moduleSettings.params.backgroundcolor        = '';
							moduleSettings.params.circlecolor            = '';
							moduleSettings.params.circlebordercolor      = '';
							moduleSettings.params.circlebordersize       = '';
							moduleSettings.params.outercirclebordercolor = '';
							moduleSettings.params.outercirclebordersize  = '';

							// Set values from parent element
							moduleSettings.params.animation_type      = parentAtts.animation_type;
							moduleSettings.params.animation_direction = parentAtts.animation_direction;
							moduleSettings.params.animation_speed     = parentAtts.animation_speed;
							moduleSettings.params.link_target         = parentAtts.link_target;
						}
					}

					if ( 'fusion_gallery' === thisEl.model.attributes[ 'data-element_type' ] ) {

						if ( 'undefined' === typeof moduleSettings.params.image || '' === moduleSettings.params.image ) {

							if ( '' !== moduleSettings.params.image_id && 'NaN' !== moduleSettings.params.image_id && 'undefined' !== typeof moduleSettings.params.image_id ) {

								if ( 'undefined' === typeof wp.media.attachment( moduleSettings.params.image_id ).get( 'url' ) ) {
									thisEl.fetchIds.push( moduleSettings.params.image_id );
									thisEl.childIds.push( moduleSettings.cid );
									thisEl.updateGallery = true;

								} else {
									moduleSettings.params.image = wp.media.attachment( moduleSettings.params.image_id ).get( 'url' );
									thisEl.updateGallery = true;
								}
							}
						}
					}

					thisEl.model.collection.add( [ moduleSettings ] );

				} );

				if ( 'fusion_gallery' === thisEl.model.attributes[ 'data-element_type' ] ) {

					// Fetch attachments if neccessary.
					if ( thisEl.updateGallery ) {

						if ( 'undefined' !== typeof thisEl.fetchIds && 0 < thisEl.fetchIds.length ) {

							wp.media.query( { post__in: thisEl.fetchIds, posts_per_page: thisEl.fetchIds.length } ).more().then( function() {

								_.each( thisEl.childIds, function( cid ) {
									var model = FusionPageBuilderApp.collection.find( function( model ) {
										return model.get( 'cid' ) === cid;
									} );

									if ( 'undefined' !== typeof wp.media.attachment( model.attributes.params.image_id ).get( 'url' ) ) {
										model.attributes.params.image = wp.media.attachment( model.attributes.params.image_id ).get( 'url' );
									}

								} );

								setTimeout( function() {
									thisEl.updateGalleryContent();
								}, 200 );

							} );

						} else {
							setTimeout( function() {
								thisEl.updateGalleryContent();
							}, 200 );
						}
					}
				}
			}
		} );
	} );
}( jQuery ) );
