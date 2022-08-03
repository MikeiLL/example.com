/* global FusionApp, FusionPageBuilderViewManager, fusionBuilderText, FusionPageBuilderApp, fusionAppConfig, FusionEvents, fusionGlobalManager */
/* eslint no-undef: 0 */
/* eslint no-alert: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Builder Library
		FusionPageBuilder.LibraryView = window.wp.Backbone.View.extend( {

			el: '#fusion-builder-front-end-library',

			events: {
				'click .fusion-tabs-menu > li > a': 'switchTab',
				'change .fusion-builder-demo-select': 'demoSelect',
				'input .fusion-builder-demo-page-link': 'demoSelectByURL',
				'click .fusion-builder-demo-button-load': 'loadDemoPage',
				'click .fusion-builder-demo-layout-button-load': 'loadDemoLayout',
				'click .ui-dialog-titlebar-close': 'removeView',
				'click .fusion-builder-layout-button-load': 'loadLayout',
				'click .fusion-builder-layout-button-save': 'saveLayout',
				'click .fusion-builder-layout-button-delete': 'deleteLayout',
				'click .fusion-builder-element-button-save': 'saveElement'
			},

			/**
			 * Init.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			initialize: function() {
				var self = this;
				jQuery( document ).on( 'click', '.fusion-builder-library-dialog .fusion-tabs-menu > li > a', function( event ) {
					self.switchTab( event );
				} );

				// Loader animation
				this.listenTo( FusionEvents, 'fusion-show-loader', this.showLoader );
				this.listenTo( FusionEvents, 'fusion-hide-loader', this.hideLoader );
			},

			showLoader: function() {
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '.fusion-builder-live-editor' ).css( 'height', '148px' );
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '.fusion-builder-live-editor' ).append( '<div class="fusion-builder-element-content fusion-loader"><span class="fusion-builder-loader"></span></div>' );
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '#fusion_builder_container' ).hide();
			},

			hideLoader: function() {
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '#fusion_builder_container' ).fadeIn( 'fast' );
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '.fusion-builder-live-editor > .fusion-builder-element-content.fusion-loader' ).remove();
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '.fusion-builder-live-editor' ).removeAttr( 'style' );
			},

			/**
			 * Renders the view.
			 *
			 * @since 2.0.0
			 * @return {Object} this
			 */
			render: function() {
				var self = this;

				this.$el = this.$el.dialog( {
					title: fusionBuilderText.builder_ibrary_title,
					width: FusionApp.dialog.dialogWidth,
					height: FusionApp.dialog.dialogHeight,
					draggable: false,
					resizable: false,
					modal: true,
					dialogClass: 'fusion-builder-large-library-dialog fusion-builder-dialog fusion-builder-library-dialog',

					open: function() {
						FusionApp.dialog.resizeDialog();
					},

					close: function() {
						self.removeView();
					}
				} ).closest( '.ui-dialog' );

				this.appendSave();
				this.targetTab();
				this.focusInput();

				return this;
			},

			/**
			 * Find the target tab and trigger the 'click' event on it.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			targetTab: function() {
				if ( 'undefined' !== typeof this.model.target ) {
					this.$el.find( '.fusion-tabs-menu > li > a[href="' + this.model.target + '"]' ).trigger( 'click' );
				} else {
					this.$el.find( '.fusion-tabs-menu > li:first-child > a' ).trigger( 'click' );
				}
			},

			/**
			 * Focus on an element.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			focusInput: function() {
				var self = this;
				if ( 'undefined' !== typeof this.model.focus ) {
					setTimeout( function() {
						self.$el.find( self.model.target ).find( self.model.focus ).focus();
					}, 200 );
				}
			},

			/**
			 * Appends the HTML that allows users to save an element.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			appendSave: function() {
				var elementView,
					elementSave;

				if ( 'undefined' !== typeof this.model.target && 'undefined' !== typeof this.model.element_cid ) {
					elementView = FusionPageBuilderViewManager.getView( this.model.element_cid );
					elementSave = 'undefined' !== typeof elementView.getSaveLabel ? elementView.getSaveLabel() : fusionBuilderText.save_element;
					jQuery( this.model.target ).find( '.fusion-builder-layouts-header-element-fields' ).append( '<div class="fusion-save-element-fields"><div class="save-as-global"><label><input type="checkbox" id="fusion_save_global" name="fusion_save_global">' + fusionBuilderText.save_global + '</label></div><input type="text" value="' + this.model.element_name + '" id="fusion-builder-save-element-input" class="fusion-builder-save-element-input" placeholder="' + fusionBuilderText.enter_name + '" /><a href="#" class="fusion-builder-save-column fusion-builder-element-button-save" data-element-cid="' + this.model.element_cid + '">' + elementSave + '</a></div>' );
				}
			},

			/**
			 * Switches a tab. Takes care of toggling the 'current' & 'inactive' classes
			 * and also changes the 'display' property of elements to properly make the switch.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			switchTab: function( event ) {
				var $tabLink = jQuery( event.target ),
					tab = $tabLink.attr( 'href' );

				if ( event ) {
					event.preventDefault();
				}

				FusionEvents.trigger( 'fusion-switch-element-option-tabs' );

				$tabLink.parent( 'li' ).addClass( 'current' ).removeClass( 'inactive' );
				$tabLink.parent( 'li' ).siblings().removeClass( 'current' ).addClass( 'inactive' );

				this.$el.find( '.fusion-builder-layouts-tab' ).css( 'display', 'none' );
				this.$el.find( tab ).css( 'display', 'block' );
			},

			/**
			 * Shows/Hides demos on select.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			demoSelect: function( event ) {
				var $selectedDemo = jQuery( event.target ).val();

				jQuery( '#fusion-builder-layouts-demos .fusion-page-layouts' ).addClass( 'hidden' );
				jQuery( '#fusion-builder-demo-url-invalid' ).addClass( 'hidden' );
				jQuery( '.fusion-builder-demo-page-link' ).val( '' );
				jQuery( '#fusion-builder-layouts-demos .demo-' + $selectedDemo ).removeClass( 'hidden' );
			},

			/**
			 * Shows/Hides demos on added URL change.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			demoSelectByURL: function( event ) {
				var demoPageLink = jQuery( event.target ).val(),
					demoPage,
					parentDemo,
					demoSelectorVal;

				demoPageLink = demoPageLink.replace( 'https://', '' ).replace( 'http://', '' );
				if ( '/' !== demoPageLink[ demoPageLink.length - 1 ] && ! _.isEmpty( demoPageLink ) ) {
					demoPageLink += '/';
				}

				demoPage   = jQuery( '#fusion-builder-layouts-demos' ).find( '.fusion-page-layout[data-page-link="' + demoPageLink + '"]' );
				parentDemo = demoPage.closest( '.fusion-page-layouts' );

				jQuery( '#fusion-builder-layouts-demos .fusion-page-layouts' ).addClass( 'hidden' );
				jQuery( '#fusion-builder-demo-url-invalid' ).addClass( 'hidden' );

				if ( _.isEmpty( demoPageLink ) ) {
					demoSelectorVal = jQuery( '.fusion-builder-demo-select' ).val();
					jQuery( '#fusion-builder-layouts-demos .demo-' + demoSelectorVal ).removeClass( 'hidden' );
				} else if ( ! demoPage.length ) {
					jQuery( '#fusion-builder-demo-url-invalid' ).removeClass( 'hidden' );
				} else {
					parentDemo.show();
					parentDemo.find( '.fusion-page-layout' ).hide();
					demoPage.show();
				}
			},

			/**
			 * Loads the demo pages via an ajax call.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			loadDemoPage: function( event ) {
				var pageName,
					demoName,
					postId,
					content,
					self          = this,
					frameDocument = document.getElementById( 'fb-preview' ).contentWindow.document,
					oldWrite      = frameDocument.write; // jshint ignore:line

				// Turn document write off before page request.
				frameDocument.write = function() {}; // eslint-disable-line no-empty-function
				document.write      = function() {}; // eslint-disable-line no-empty-function

				if ( event ) {
					event.preventDefault();
				}

				FusionApp.confirmationPopup( {
					title: fusionBuilderText.import_demo_page,
					content: fusionBuilderText.importing_single_page,
					actions: [
						{
							label: fusionBuilderText.cancel,
							classes: 'no',
							callback: function() {
								FusionApp.confirmationPopup( {
									action: 'hide'
								} );
							}
						},
						{
							label: fusionBuilderText.ok,
							classes: 'yes',
							callback: function() {
								if ( true === FusionPageBuilderApp.layoutIsLoading ) {
									return;
								}
								FusionPageBuilderApp.layoutIsLoading = true;
								FusionPageBuilderApp.loaded          = false;

								pageName = jQuery( event.currentTarget ).data( 'page-name' );
								demoName = jQuery( event.currentTarget ).data( 'demo-name' );
								postId   = jQuery( event.currentTarget ).data( 'post-id' );

								jQuery.ajax( {
									type: 'POST',
									url: fusionAppConfig.ajaxurl,
									data: {
										action: 'fusion_builder_load_demo',
										fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
										page_name: pageName,
										demo_name: demoName,
										post_id: postId
									},

									beforeSend: function() {
										FusionEvents.trigger( 'fusion-show-loader' );

										// Hide confirmation popup.
										FusionApp.confirmationPopup( {
											action: 'hide'
										} );

										// Hide library dialog.
										self.$el.css( 'display', 'none' );
										self.$el.next( '.ui-widget-overlay' ).css( 'display', 'none' );
									}
								} )
								.done( function( data ) {
									var dataObj,
										needsRefresh = false,
										newCustomCss = false;

									// New layout loaded
									FusionPageBuilderApp.layoutLoaded();

									dataObj = JSON.parse( data );
									newCustomCss = 'undefined' !== typeof dataObj.custom_css ? dataObj.custom_css : false;

									content = dataObj.post_content;
									if ( newCustomCss ) {
										FusionApp.data.postMeta._fusion_builder_custom_css = newCustomCss;
									}
									jQuery.each( dataObj.post_meta, function( name, value ) {
										needsRefresh = true;
										FusionApp.data.postMeta[ name ] = value[ 0 ];
									} );

									if ( 'undefined' !== typeof dataObj.page_template && FusionApp.data.postMeta._wp_page_template !== dataObj.page_template ) {
										FusionApp.data.postMeta._wp_page_template = dataObj.page_template;
										needsRefresh = true;
									}

									if ( needsRefresh ) {
										FusionApp.contentChange( 'page', 'page-option' );
									}

									FusionApp.data.postContent = content;
									FusionApp.contentChange( 'page', 'builder-content' );

									if ( newCustomCss && 'undefined' !== typeof avadaPanelIFrame ) {

										// Add the CSS to the page.
										avadaPanelIFrame.liveUpdatePageCustomCSS( newCustomCss );
									}

									// Create new builder layout.
									FusionPageBuilderApp.clearBuilderLayout( false );

									FusionPageBuilderApp.createBuilderLayout( content );

									// Refresh frame if needed.
									if ( needsRefresh ) {
										FusionApp.fullRefresh();
									}

									FusionPageBuilderApp.layoutIsLoading = false;

								} )
								.always( function() {

									// Add success/transition of some kind here.
									FusionEvents.trigger( 'fusion-hide-loader' );

									frameDocument.write = oldWrite;
									document.write      = oldWrite;

									FusionPageBuilderApp.loaded = true;
									FusionEvents.trigger( 'fusion-builder-loaded' );

									self.removeView();
								} );
							}
						}
					]
				} );
			},

			/**
			 * Loads the demo pages via an ajax call.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			loadDemoLayout: function( event ) {
				var postId,
					content,
					self          = this,
					frameDocument = document.getElementById( 'fb-preview' ).contentWindow.document,
					confirmTitle  = fusionBuilderText.import_demo_layout,
					confirmText   = fusionBuilderText.importing_demo_layout,
					oldWrite      = frameDocument.write, // jshint ignore:line
					cardLayout    = false;

				// Turn document write off before page request.
				frameDocument.write = function() {}; // eslint-disable-line no-empty-function
				document.write      = function() {}; // eslint-disable-line no-empty-function

				if ( event ) {
					event.preventDefault();
				}

				if ( jQuery( event.target ).closest( '.fusion-builder-layouts-tab' ).is( '#fusion-builder-layouts-forms' ) ) {
					confirmText  = fusionBuilderText.importing_demo_form;
					confirmTitle = fusionBuilderText.importing_demo_form_title;
				} else if ( jQuery( event.target ).closest( '.fusion-builder-layouts-tab' ).is( '#fusion-builder-layouts-cards' ) ) {
					confirmText  = fusionBuilderText.importing_post_card;
					confirmTitle = fusionBuilderText.importing_post_card_title;
					cardLayout   = true;
				}

				FusionApp.confirmationPopup( {
					title: confirmTitle,
					content: confirmText,
					actions: [
						{
							label: fusionBuilderText.cancel,
							classes: 'no',
							callback: function() {
								FusionApp.confirmationPopup( {
									action: 'hide'
								} );
							}
						},
						{
							label: fusionBuilderText.ok,
							classes: 'yes',
							callback: function() {
								if ( true === FusionPageBuilderApp.layoutIsLoading ) {
									return;
								}
								FusionPageBuilderApp.layoutIsLoading = true;
								FusionPageBuilderApp.loaded          = false;

								layoutName = jQuery( event.currentTarget ).data( 'layout-name' );
								postId     = jQuery( event.currentTarget ).data( 'post-id' );

								jQuery.ajax( {
									type: 'POST',
									url: fusionAppConfig.ajaxurl,
									data: {
										action: 'fusion_builder_load_demo_layout',
										fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
										layout_name: layoutName,
										post_id: postId
									},

									beforeSend: function() {
										FusionEvents.trigger( 'fusion-show-loader' );

										// Hide confirmation popup.
										FusionApp.confirmationPopup( {
											action: 'hide'
										} );

										// Hide library dialog.
										self.$el.css( 'display', 'none' );
										self.$el.next( '.ui-widget-overlay' ).css( 'display', 'none' );
									}
								} )
								.done( function( data ) {
									// New layout loaded
									FusionPageBuilderApp.layoutLoaded();

									content = data.data.post_content;

									FusionApp.data.postContent = content;
									FusionApp.contentChange( 'page', 'builder-content' );

									// If we have fusion PO data, add that too.
									if ( 'object' == typeof data.data._fusion ) {
										_.each( data.data._fusion, function( value, param ) {
											FusionApp.data.postMeta._fusion[ param ] = value;
										} );

										FusionApp.contentChange( 'page', 'page-option' );
									}

									// Create new builder layout.
									FusionPageBuilderApp.clearBuilderLayout( false );

									FusionPageBuilderApp.createBuilderLayout( content );

									FusionPageBuilderApp.layoutIsLoading = false;

									if ( cardLayout ) {
										FusionPageBuilderApp.cardPreviewWidth();
									}
								} )
								.always( function() {

									// Add success/transition of some kind here.
									FusionEvents.trigger( 'fusion-hide-loader' );

									frameDocument.write = oldWrite;
									document.write      = oldWrite;

									FusionPageBuilderApp.loaded = true;
									FusionEvents.trigger( 'fusion-builder-loaded' );

									jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-sticky-header-reinit' );

									self.removeView();
								} );
							}
						}
					]
				} );
			},

			/**
			 * Loads the layout via AJAX.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			loadLayout: function( event ) {
				var $layout,
					contentPlacement,
					content,
					self = this;

				if ( event ) {
					event.preventDefault();
				}

				if ( true === FusionPageBuilderApp.layoutIsLoading ) {
					return;
				}
				FusionPageBuilderApp.layoutIsLoading = true;

				$layout          = jQuery( event.currentTarget ).closest( '.fusion-page-layout' );
				contentPlacement = jQuery( event.currentTarget ).data( 'load-type' );

				// Get correct content.
				FusionPageBuilderApp.builderToShortcodes();
				content = FusionApp.getPost( 'post_content' ); // eslint-disable-line camelcase

				FusionPageBuilderApp.loaded = false;

				jQuery.ajax( {
					type: 'POST',
					url: fusionAppConfig.ajaxurl,
					data: {
						action: 'fusion_builder_load_layout',
						fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
						fusion_layout_id: $layout.data( 'layout_id' )
					},

					beforeSend: function() {
						FusionEvents.trigger( 'fusion-show-loader' );

						// Hide library dialog.
						self.$el.css( 'display', 'none' );
						self.$el.next( '.ui-widget-overlay' ).css( 'display', 'none' );
					}
				} )
				.done( function( data ) {
					var dataObj,
						newCustomCss,
						needsRefresh = false,
						existingCss = 'undefined' !== typeof FusionApp.data.postMeta._fusion_builder_custom_css ? FusionApp.data.postMeta._fusion_builder_custom_css : '';

					// New layout loaded
					FusionPageBuilderApp.layoutLoaded();

					dataObj = JSON.parse( data );
					newCustomCss = 'undefined' !== typeof dataObj.custom_css ? dataObj.custom_css : false;

					if ( 'above' === contentPlacement ) {
						content = dataObj.post_content + content;
						if ( newCustomCss ) {
							FusionApp.data.postMeta._fusion_builder_custom_css = newCustomCss + '\n' + existingCss;
						}

					} else if ( 'below' === contentPlacement ) {
						content = content + dataObj.post_content;
						if ( newCustomCss ) {
							FusionApp.data.postMeta._fusion_builder_custom_css = existingCss + '\n' + newCustomCss;
						}

					} else {
						content = dataObj.post_content;
						if ( newCustomCss ) {
							FusionApp.data.postMeta._fusion_builder_custom_css = newCustomCss;
						}
						jQuery.each( dataObj.post_meta, function( name, value ) {
							needsRefresh = true;
							FusionApp.data.postMeta[ name ] = value[ 0 ];
						} );

						if ( 'undefined' !== typeof dataObj.page_template && FusionApp.data.postMeta._wp_page_template !== dataObj.page_template ) {
							FusionApp.data.postMeta._wp_page_template = dataObj.page_template;
							needsRefresh = true;
						}

						if ( needsRefresh ) {
							FusionApp.contentChange( 'page', 'page-option' );
						}
					}

					FusionApp.setPost( 'post_content', content );
					FusionApp.contentChange( 'page', 'builder-content' );

					if ( needsRefresh ) {

						// Set new content and refresh frame.
						FusionApp.fullRefresh( false, {}, { post_content: content } );
					} else {
						if ( newCustomCss && 'undefined' !== typeof avadaPanelIFrame ) {

							// Add the CSS to the page.
							avadaPanelIFrame.liveUpdatePageCustomCSS( newCustomCss );
						}

						// Create new builder layout.
						FusionPageBuilderApp.clearBuilderLayout( false );
						FusionPageBuilderApp.createBuilderLayout( content );
					}

					FusionPageBuilderApp.layoutIsLoading = false;
				} )
				.always( function() {
					FusionPageBuilderApp.loaded = true;
					FusionEvents.trigger( 'fusion-builder-loaded' );

					FusionEvents.trigger( 'fusion-hide-loader' );

					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-sticky-header-reinit' );

					self.removeView();
				} );
			},

			/**
			 * Saves the layout via AJAX.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			saveLayout: function( event ) {

				var templateContent,
					templateName,
					layoutsContainer,
					currentPostID,
					customCSS,
					pageTemplate;

				if ( event ) {
					event.preventDefault();
				}

				FusionPageBuilderApp.builderToShortcodes();
				templateContent  = FusionApp.getPost( 'post_content' ); // eslint-disable-line camelcase
				templateName     = jQuery( '#new_template_name' ).val();
				layoutsContainer = jQuery( '#fusion-builder-layouts-templates .fusion-page-layouts' );
				currentPostID    = jQuery( '#fusion_builder_main_container' ).data( 'post-id' );

				customCSS    = 'undefined' !== typeof FusionApp.data.postMeta._fusion_builder_custom_css ? FusionApp.data.postMeta._fusion_builder_custom_css : '';
				pageTemplate = 'undefined' !== typeof FusionApp.data.postMeta._wp_page_template ? FusionApp.data.postMeta._wp_page_template : '';

				if ( '' !== templateName ) {

					jQuery.ajax( {
						type: 'POST',
						url: fusionAppConfig.ajaxurl,
						dataType: 'json',
						data: {
							action: 'fusion_builder_save_layout',
							fusion_po_type: 'object',
							fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
							fusion_layout_name: templateName,
							fusion_layout_content: templateContent,
							fusion_layout_post_type: 'fusion_template',
							fusion_current_post_id: currentPostID,
							fusion_custom_css: customCSS,
							fusion_page_template: pageTemplate,
							fusion_options: FusionApp.data.postMeta,
							fusion_front_end: true
						},

						complete: function( data ) {
							layoutsContainer.prepend( data.responseText );
						}
					} );

					jQuery( '#new_template_name' ).val( '' );

				} else {
					alert( fusionBuilderText.please_enter_template_name ); // jshint ignore: line
				}
			},

			/**
			 * Deletes a layout via AJAX.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			deleteLayout: function( event ) {

				var $layout;

				if ( event ) {
					event.preventDefault();

					FusionApp.confirmationPopup( {
						title: fusionBuilderText.are_you_sure,
						content: fusionBuilderText.are_you_sure_you_want_to_delete_this,
						actions: [
							{
								label: fusionBuilderText.cancel,
								classes: 'no',
								callback: function() {
									FusionApp.confirmationPopup( {
										action: 'hide'
									} );
								}
							},
							{
								label: fusionBuilderText.im_sure,
								classes: 'yes',
								callback: function() {

									if ( true === FusionPageBuilderApp.layoutIsDeleting ) {
										return;
									}
									FusionPageBuilderApp.layoutIsDeleting = true;

									$layout = jQuery( event.currentTarget ).closest( '.fusion-page-layout' );

									jQuery.ajax( {
										type: 'POST',
										url: fusionAppConfig.ajaxurl,
										data: {
											action: 'fusion_builder_delete_layout',
											fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
											fusion_layout_id: $layout.data( 'layout_id' )
										}
									} )
									.done( function() {
										$layout.remove();
										FusionPageBuilderApp.layoutIsDeleting = false;
									} );

									FusionApp.confirmationPopup( {
										action: 'hide'
									} );
								}
							}
						]
					} );
				}
			},

			/**
			 * Saves an element via AJAX.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			saveElement: function( event ) {
				var elementName      = jQuery( '#fusion-builder-save-element-input' ).val(),
					layoutsContainer = jQuery( this.model.target ).find( '.fusion-page-layouts' ),
					saveGlobal       = jQuery( this.model.target ).find( '#fusion_save_global' ).is( ':checked' ),
					elementView      = FusionPageBuilderViewManager.getView( this.model.element_cid ),
					elementContent   = elementView.getContent(),
					elementCategory  = 'undefined' !== typeof elementView.getCategory ? elementView.getCategory() : 'elements',
					isDuplicate      = false,
					oldGLobalID      = null,
					wrapperClass     = '',
					params           = {};

				if ( event ) {
					event.preventDefault();
				}

				if ( 'undefined' !== typeof elementView.model.attributes.params && 'undefined' !== typeof elementView.model.attributes.params.fusion_global ) {

					// Make a copy.
					oldGLobalID = elementView.model.attributes.params.fusion_global;
					params      = elementView.model.get( 'params' );

					// Remove temporarily and update model
					delete params.fusion_global;
					elementView.model.set( 'params', params );

					// Get content.
					elementContent   = elementView.getColumnContent();

					// Add it back.
					params.fusion_global = oldGLobalID;
					elementView.model.set( 'params', params );
				}

				switch ( elementCategory ) {
					case 'sections':
						wrapperClass = 'ul.fusion-page-layouts.fusion-layout-sections li';
						break;

					case 'columns':
						wrapperClass = 'ul.fusion-page-layouts.fusion-layout-columns li';
						break;

					case 'elements':
						wrapperClass = 'ul.fusion-page-layouts.fusion-layout-elements li';
						break;
				}

				jQuery.each( jQuery( wrapperClass ), function() {
					var templateName = jQuery( this ).find( 'h4.fusion-page-layout-title' ).html().split( '<div ' )[ 0 ];
					templateName     = templateName.replace( /\u2013|\u2014/g, '-' );

					if ( elementName.toLowerCase().trim() === templateName.toLowerCase().trim() ) {
						alert( fusionBuilderText.duplicate_element_name_error ); // jshint ignore:line
						isDuplicate = true;
						return false;
					}
				} );

				if ( true === FusionPageBuilderApp.layoutIsSaving || true === isDuplicate ) {
					return;
				}
				FusionPageBuilderApp.layoutIsSaving = true;

				if ( '' !== elementName ) {
					jQuery.ajax( {
						type: 'POST',
						url: fusionAppConfig.ajaxurl,
						dataType: 'json',
						data: {
							action: 'fusion_builder_save_layout',
							fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
							fusion_layout_name: elementName,
							fusion_layout_content: elementContent,
							fusion_layout_post_type: 'fusion_element',
							fusion_layout_new_cat: elementCategory,
							fusion_save_global: saveGlobal,
							fusion_front_end: true
						},
						complete: function( data ) {
							var MultiGlobalArgs,
								cid      = elementView.model.get( 'cid' ),
								globalID = jQuery( data.responseText ).attr( 'data-layout_id' );

							FusionPageBuilderApp.layoutIsSaving = false;
							layoutsContainer.prepend( data.responseText );
							jQuery( '.fusion-save-element-fields' ).remove();

							// If global, make it.
							if ( saveGlobal ) {

								// For nested elements.
								if ( 'undefined' === typeof elementView.model.attributes.params ) {
									elementView.model.attributes.params = {};
								}

								elementView.model.attributes.params.fusion_global = globalID;

								if ( 'sections' === elementCategory ) {
									FusionPageBuilderApp.$el.find( 'div[data-cid="' + cid + '"] .fusion-builder-module-controls-type-container .fusion-builder-module-controls' ).after( '<a href="#" class="fusion-builder-container-global fusion-builder-module-control fusion-builder-unglobal-tooltip" data-cid=' + cid + '><span class="fusiona-globe"></span><span class="fusion-container-tooltip"><span class="fusion-tooltip-text">' + fusionBuilderText.global_container + '</span></span></a>' );
									FusionPageBuilderApp.$el.find( 'div[data-cid="' + cid + '"] .fusion-builder-wireframe-utility-toolbar' ).first().append( '<a href="#" class="fusion-builder-container-global fusion-builder-module-control fusion-builder-unglobal-tooltip" data-cid=' + cid + '><span class="fusiona-globe"></span><span class="fusion-container-tooltip"><span class="fusion-tooltip-text">' + fusionBuilderText.global_container + '</span></span></a>' );
									FusionPageBuilderApp.$el.find( 'div[data-cid="' + cid + '"]' ).addClass( 'fusion-global-container' );
								} else if ( 'columns' === elementCategory ) {
									FusionPageBuilderApp.$el.find( 'div[data-cid="' + cid + '"] .fusion-builder-module-controls-inner.fusion-builder-column-controls-inner' ).after( '<a href="#" class="fusion-builder-column-global fusion-builder-module-control fusion-builder-unglobal-tooltip" data-cid=' + cid + '><span class="fusiona-globe"></span><span class="fusion-column-tooltip"><span class="fusion-tooltip-text">' + fusionBuilderText.global_column + '</span></span></a>' );
									FusionPageBuilderApp.$el.find( 'div[data-cid="' + cid + '"] .fusion-builder-wireframe-utility-toolbar' ).first().append( '<a href="#" class="fusion-builder-column-global fusion-builder-module-control fusion-builder-unglobal-tooltip" data-cid=' + cid + '><span class="fusiona-globe"></span><span class="fusion-column-tooltip"><span class="fusion-tooltip-text">' + fusionBuilderText.global_column + '</span></span></a>' );
									FusionPageBuilderApp.$el.find( 'div[data-cid="' + cid + '"]' ).addClass( 'fusion-global-column' );
								} else if ( 'elements' === elementCategory && 'undefined' !== typeof elementView.model.get( 'multi' ) && 'multi_element_parent' === elementView.model.get( 'multi' ) ) {
									FusionPageBuilderApp.$el.find( 'div[data-cid="' + cid + '"]' ).addClass( 'fusion-global-parent-element' );
									FusionPageBuilderApp.$el.find( 'div[data-cid="' + cid + '"] .fusion-builder-module-controls-inner' ).after( '<a href="#" class="fusion-builder-element-global fusion-builder-module-control fusion-builder-unglobal-tooltip" data-cid=' + cid + '><span class="fusiona-globe"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text">' + fusionBuilderText.global_element + '</span></span></a>' );
									FusionPageBuilderApp.$el.find( 'div[data-cid="' + cid + '"] .fusion-builder-wireframe-utility-toolbar' ).first().append( '<a href="#" class="fusion-builder-element-global fusion-builder-module-control fusion-builder-unglobal-tooltip" data-cid=' + cid + '><span class="fusiona-globe"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text">' + fusionBuilderText.global_element + '</span></span></a>' );
								} else if ( 'elements' === elementCategory && 'fusion_builder_row_inner' === elementView.model.get( 'element_type' )  ) {
									FusionPageBuilderApp.$el.find( 'div[data-cid="' + cid + '"]' ).addClass( 'fusion-global-nested-row' );
									FusionPageBuilderApp.$el.find( 'div[data-cid="' + cid + '"] .fusion-builder-module-controls-inner' ).after( '<a href="#" class="fusion-builder-element-global fusion-builder-module-control fusion-builder-unglobal-tooltip" data-cid=' + cid + '><span class="fusiona-globe"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text">' + fusionBuilderText.global_element + '</span></span></a>' );
									FusionPageBuilderApp.$el.find( 'div[data-cid="' + cid + '"] .fusion-builder-wireframe-utility-toolbar' ).last().append( '<a href="#" class="fusion-builder-element-global fusion-builder-module-control fusion-builder-unglobal-tooltip" data-cid=' + cid + '><span class="fusiona-globe"></span><span class="fusion-element-tooltip">' + fusionBuilderText.global_element + '</span></span></a>' );
								} else if ( 'elements' === elementCategory  ) {
									FusionPageBuilderApp.$el.find( 'div[data-cid="' + cid + '"]' ).addClass( 'fusion-global-element' );
									FusionPageBuilderApp.$el.find( 'div[data-cid="' + cid + '"] .fusion-builder-module-controls-inner' ).after( '<a href="#" class="fusion-builder-element-global fusion-builder-module-control fusion-builder-unglobal-tooltip" data-cid=' + cid + '><span class="fusiona-globe"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text">' + fusionBuilderText.global_element + '</span></span></a>' );
									FusionPageBuilderApp.$el.find( 'div[data-cid="' + cid + '"] .fusion-builder-wireframe-utility-toolbar' ).first().append( '<a href="#" class="fusion-builder-element-global fusion-builder-module-control fusion-builder-unglobal-tooltip" data-cid=' + cid + '><span class="fusiona-globe"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text">' + fusionBuilderText.global_element + '</span></span></a>' );
								}

								FusionPageBuilderApp.$el.find( 'div[data-cid="' + cid + '"]' ).attr( 'fusion-global-layout', globalID );
								FusionEvents.trigger( 'fusion-element-added' );
								FusionPageBuilderApp.saveGlobal = true;

								// Check for globals.
								MultiGlobalArgs = {
									currentModel: elementView.model,
									handleType: 'save',
									attributes: elementView.model.attributes
								};
								setTimeout( fusionGlobalManager.handleMultiGlobal, 500, MultiGlobalArgs );

								// Save history
								FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.saved + ' ' + fusionAllElements[ elementView.model.get( 'element_type' ) ].name + ' ' + fusionBuilderText.as_global );

								if ( 'undefined' !== typeof FusionApp.contentChange ) {
									FusionApp.contentChange( 'page', 'builder-content' );
								}
							}
						}
					} );

				} else {
					alert( fusionBuilderText.please_enter_element_name ); // jshint ignore: line
				}
			},

			/**
			 * Removes the view.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			removeView: function() {
				this.$el.find( '.fusion-save-element-fields' ).remove();
				this.$el.find( '.fusion-builder-modal-top-container' ).prependTo( '#fusion-builder-front-end-library' );

				FusionApp.dialogCloseResets( this );

				this.remove();
			}
		} );
	} );
}( jQuery ) );
