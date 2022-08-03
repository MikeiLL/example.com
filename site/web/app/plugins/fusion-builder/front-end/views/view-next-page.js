/* global FusionApp, FusionPageBuilderViewManager, FusionEvents, FusionPageBuilderApp, fusionBuilderText */
/* eslint no-shadow: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Builder Next Page View.
		FusionPageBuilder.NextPage = FusionPageBuilder.BaseView.extend( {

			className: 'fusion-builder-next-page fusion-special-item',
			template: FusionPageBuilder.template( jQuery( '#fusion-builder-next-page-template' ).html() ),
			events: {
				'click .fusion-builder-delete-next-page': 'removeNextPage',
				'click .fusion-builder-next-page-toggle': 'toggleNextPagePreview',
				'click .fusion-builder-next-page-link': 'changePageTrigger'
			},

			initialize: function() {
				var params = this.model.get( 'params' );

				this.$el.attr( 'data-cid', this.model.get( 'cid' ) );

				if ( FusionApp.data.next_page_elements_count <= FusionPageBuilderViewManager.countElementsByType( 'fusion_builder_next_page' ) ) {
					FusionApp.data.next_page_elements_count += 1;
				}

				if ( params.last ) {
					this.$el.addClass( 'fusion-builder-next-page-last' );
				}

				this.listenTo( FusionEvents, 'fusion-wireframe-toggle', this.wireFrameToggled );
			},

			render: function() {
				var self = this,
					data = this.getTemplateAtts();

				this.$el.html( this.template( data ) );

				this.addPaginationLinks();

				setTimeout( function() {
					self.droppableContainer();
				}, 100 );

				return this;
			},

			/**
						 * Adds drop zones for continers and makes container draggable.
						 *
						 * @since 2.0.0
						 * @return {void}
						 */
			droppableContainer: function() {

				var $el   = this.$el,
					cid   = this.model.get( 'cid' ),
					$body = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' );

				if ( ! $el ) {
					return;
				}

				$el.draggable( {
					appendTo: FusionPageBuilderApp.$el,
					zIndex: 999999,
					delay: 100,
					cursorAt: { top: 15, left: 15 },
					iframeScroll: true,
					containment: $body,
					cancel: '.fusion-builder-column',
					helper: function() {
						var $classes = FusionPageBuilderApp.DraggableHelpers.draggableClasses( cid );
						return jQuery( '<div class="fusion-container-helper ' + $classes + '" data-cid="' + cid + '"><span class="fusiona-container"></span></div>' );
					},
					start: function() {
						$body.addClass( 'fusion-container-dragging fusion-active-dragging' );
						$el.addClass( 'fusion-being-dragged' );

						//  Add a class to hide the unnecessary target after.
						if ( $el.prev( '.fusion-builder-container' ).length ) {
							$el.prev( '.fusion-builder-container' ).addClass( 'hide-target-after' );
						}

						if ( $el.prev( '.fusion-fusion-builder-next-pager' ).length ) {
							$el.prev( '.fusion-fusion-builder-next-page' ).addClass( 'hide-target-after' );
						}
					},
					stop: function() {
						setTimeout( function() {
							$body.removeClass( 'fusion-container-dragging fusion-active-dragging' );
						}, 10 );
						$el.removeClass( 'fusion-being-dragged' );
						FusionPageBuilderApp.$el.find( '.hide-target-after' ).removeClass( 'hide-target-after' );
					}
				} );

				$el.find( '.fusion-container-target' ).droppable( {
					tolerance: 'touch',
					hoverClass: 'ui-droppable-active',
					accept: '.fusion-builder-container, .fusion-builder-next-page',
					drop: function( event, ui ) {

						// Move the actual html.
						if ( jQuery( event.target ).hasClass( 'target-after' ) ) {
							$el.after( ui.draggable );
						} else {
							$el.before( ui.draggable );
						}

						FusionEvents.trigger( 'fusion-content-changed' );

						FusionPageBuilderApp.scrollingContainers();

						FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.nextpage + ' Element order changed' );
					}
				} );

				// If we are in wireframe mode, then disable.
				if ( FusionPageBuilderApp.wireframeActive ) {
					this.disableDroppableContainer();
				}
			},

			/**
						 * Enable the droppable and draggable.
						 *
						 * @since 2.0.0
						 * @return {void}
						 */
			enableDroppableContainer: function() {
				var $el = this.$el;

				if ( 'undefined' !== typeof $el.draggable( 'instance' ) && 'undefined' !== typeof $el.find( '.fusion-container-target' ).droppable( 'instance' ) ) {
					$el.draggable( 'enable' );
					$el.find( '.fusion-container-target' ).droppable( 'enable' );
				} else {

					// No sign of init, then need to call it.
					this.droppableContainer();
				}
			},

			/**
						 * Destroy or disable the droppable and draggable.
						 *
						 * @since 2.0.0
						 * @return {void}
						 */
			disableDroppableContainer: function() {
				var $el = this.$el;

				// If its been init, just disable.
				if ( 'undefined' !== typeof $el.draggable( 'instance' ) ) {
					$el.draggable( 'disable' );
				}

				// If its been init, just disable.
				if ( 'undefined' !== typeof $el.find( '.fusion-container-target' ).droppable( 'instance' ) ) {
					$el.find( '.fusion-container-target' ).droppable( 'disable' );
				}
			},

			/**
						 * Fired when wireframe mode is toggled.
						 *
						 * @since 2.0.0
						 * @return {void}
						 */
			wireFrameToggled: function() {
				if ( FusionPageBuilderApp.wireframeActive ) {
					this.disableDroppableContainer();
				} else {
					this.enableDroppableContainer();
				}
			},

			/**
			 * Get template attributes.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			getTemplateAtts: function() {
				var templateAttributes = {},
					pages = Math.max( FusionApp.data.next_page_elements_count, FusionPageBuilderViewManager.countElementsByType( 'fusion_builder_next_page' ) );

				templateAttributes.pages = pages;

				templateAttributes = this.filterTemplateAtts( templateAttributes );

				return templateAttributes;
			},

			/**
			 * Add the pagination links.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			addPaginationLinks: function() {
				var allNextPageElements = jQuery( '#fb-preview' ).contents().find( '.fusion-builder-next-page' ),
					i,
					additionalClasses = '';

				for ( i = 1; i <= FusionApp.data.next_page_elements_count; i++ ) {
					additionalClasses = ( 1 === i ) ? ' current' : '';

					this.$el.find( '.fusion-builder-next-page-pagination' ).append( '<a class="fusion-builder-next-page-link' + additionalClasses + '" href="#" data-page="' + i + '">' + i + '</a>' );
				}

				if ( allNextPageElements.find( '.fusion-builder-next-page-pagination' ).eq( 0 ).find( '.fusion-builder-next-page-link' ).length < FusionApp.data.next_page_elements_count ) {
					allNextPageElements.each( function() {
						jQuery( this ).find( '.fusion-builder-next-page-pagination' ).append( '<a class="fusion-builder-next-page-link" href="#" data-page="' + FusionApp.data.next_page_elements_count + '">' + ( FusionApp.data.next_page_elements_count ) + '</a>' );
					} );
				}
			},

			removeNextPage: function( event ) {
				var allNextPageElements = jQuery( '#fb-preview' ).contents().find( '.fusion-builder-next-page' ),
					allContainers = jQuery( '#fb-preview' ).contents().find( '.fusion-builder-container' ),
					isLivePreviewActive = this.$el.hasClass( 'live-preview-active' ),
					index = allNextPageElements.index( this.$el );

				if ( event ) {
					event.preventDefault();
				}

				FusionPageBuilderViewManager.removeView( this.model.get( 'cid' ) );

				this.model.destroy();

				this.remove();

				FusionApp.data.next_page_elements_count -= 1;

				jQuery( '#fb-preview' ).contents().find( '.fusion-builder-next-page' ).each( function() {
					jQuery( this ).find( '.fusion-builder-next-page-link' ).eq( 0 ).remove();

					jQuery( this ).find( '.fusion-builder-next-page-link' ).each( function( index ) {
						jQuery( this ).attr( 'data-page', index + 1 );
						jQuery( this ).html( index + 1 );
					} );
				} );

				if ( isLivePreviewActive ) {
					if ( jQuery( allNextPageElements.get( index ) ).length && 2 < allNextPageElements.length ) {
						this.changePage( index );
					} else {
						allContainers.show();

						if ( 2 === allNextPageElements.length ) {
							this.toggleNextPagePreview();
						}
					}
				}

				FusionEvents.trigger( 'fusion-content-changed' );

				FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.deleted_nextpage );
			},

			toggleNextPagePreview: function( event ) {
				var allNextPageElements = jQuery( '#fb-preview' ).contents().find( '.fusion-builder-next-page' ),
					lastNextPageElement = jQuery( '#fb-preview' ).contents().find( '.fusion-builder-next-page-last' ),
					allContainers = jQuery( '#fb-preview' ).contents().find( '.fusion-builder-container' ),
					index = allNextPageElements.index( this.$el );

				if ( event ) {
					event.preventDefault();
				}

				if ( this.$el.hasClass( 'live-preview-active' ) ) {
					allNextPageElements.show();
					lastNextPageElement.hide();
					allContainers.show();
				} else {
					this.changePage( index );
				}

				allNextPageElements.toggleClass( 'live-preview-active' );
			},

			changePageTrigger: function( event ) {
				var newPage = parseInt( jQuery( event.target ).html(), 10 ) - 1;

				event.preventDefault();

				this.changePage( newPage );
			},

			changePage: function( newPage ) {
				var newNextPageElement = jQuery( '#fb-preview' ).contents().find( '.fusion-builder-next-page' ).eq( newPage ),
					ancestorNextPageElement = jQuery( '#fb-preview' ).contents().find( '.fusion-builder-next-page' ).eq( newPage - 1 ),
					allNextPageElements = jQuery( '#fb-preview' ).contents().find( '.fusion-builder-next-page' ),
					allContainers = jQuery( '#fb-preview' ).contents().find( '.fusion-builder-container' );

				allNextPageElements.removeClass( 'fusion-next-page-active' );
				allNextPageElements.find( '.fusion-builder-next-page-link' ).removeClass( 'current' );
				allNextPageElements.hide();
				allContainers.hide();

				newNextPageElement.addClass( 'fusion-next-page-active' );
				newNextPageElement.show();
				newNextPageElement.find( '.fusion-builder-next-page-link[data-page="' + ( newPage + 1 ) + '"]' ).addClass( 'current' );

				if ( 0 === newPage ) {
					newNextPageElement.prevAll( '.fusion-builder-container' ).show();
				} else {
					newNextPageElement.prevAll( '.fusion-builder-container' ).show();
					ancestorNextPageElement.prevAll( '.fusion-builder-container' ).hide();
				}
			},

			nextPageTriggerEvent: function( event ) {
				FusionEvents.trigger( 'fusion-next-page' );

				if ( event ) {
					event.preventDefault();
					FusionEvents.trigger( 'fusion-next-page' );
				}
			}

		} );

	} );

}( jQuery ) );
