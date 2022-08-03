/* global FusionPageBuilderViewManager, FusionPageBuilderApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

		FusionPageBuilder.Wireframe = Backbone.Model.extend( {

			initialize: function() {
				this.listenTo( window.FusionEvents, 'fusion-preview-update', this.updateWireframe );
				this.listenTo( window.FusionEvents, 'fusion-builder-loaded', this.openWireframeAfterFullRefresh );
				this.listenTo( window.FusionEvents, 'fusion-undo-state', this.setUpWireFrameAfterUndo );
			},

			updateWireframe: function( id, value ) {
				if ( 'site_width' === id ) {
					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '.fusion-builder-ui-wireframe' ).find( '#fusion_builder_container' ).css( 'max-width', value );
				}
			},

			/**
			 * Re-opens Wireframe mode after full refresh.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			openWireframeAfterFullRefresh: function() {
				if ( FusionPageBuilderApp.wireframeActive ) {
					jQuery( 'body' ).removeClass( 'fusion-builder-ui-wireframe' );
					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).removeClass( 'fusion-builder-ui-wireframe' );
					this.toggleWireframe();
				}
			},

			/**
			 * Sets Up wireframe after history undo.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			setUpWireFrameAfterUndo: function( event ) {
				var contentWindow                   = jQuery( '#fb-preview' )[ 0 ].contentWindow,
					fusionBuilderContainer          = contentWindow.jQuery( 'body' ).find( '#fusion_builder_container' ),
					fusionBuilderContainerOffsetTop = fusionBuilderContainer.offset().top,
					fusionHeaderWrapper             = contentWindow.jQuery( 'body' ).find( '.fusion-header-wrapper' ),
					fusionHeaderWrapperOffsetBottom = fusionHeaderWrapper.length ? fusionHeaderWrapper.offset().top + fusionHeaderWrapper.outerHeight() : 0;

				if ( event ) {
					event.preventDefault();
				}
				setTimeout( function() {

					if ( contentWindow.jQuery( 'body' ).hasClass( 'fusion-builder-ui-wireframe' ) ) {
						fusionBuilderContainer.css( 'max-width', window.FusionApp.settings.site_width );

						if ( fusionHeaderWrapperOffsetBottom > fusionBuilderContainerOffsetTop ) {
							fusionBuilderContainer.css( 'margin-top', ( fusionHeaderWrapperOffsetBottom + 25 ) + 'px' );
						}
					} else {
						fusionBuilderContainer.css( {
							'margin-top': '',
							'max-width': ''
						} );
					}

					window.FusionEvents.trigger( 'fusion-wireframe-toggle' );
				}, 100 );
			},

			/**
			 * Toggles the 'fusion-builder-ui-wireframe' class.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			toggleWireframe: function( event ) {
				var contentWindow                   = jQuery( '#fb-preview' )[ 0 ].contentWindow,
					fusionBuilderContainer          = contentWindow.jQuery( 'body' ).find( '#fusion_builder_container' ),
					fusionBuilderContainerOffsetTop = fusionBuilderContainer.offset().top,
					fusionHeaderWrapper             = contentWindow.jQuery( 'body' ).find( '.fusion-header-wrapper' ),
					fusionHeaderWrapperOffsetBottom = fusionHeaderWrapper.length ? fusionHeaderWrapper.offset().top + fusionHeaderWrapper.outerHeight() : 0;

				if ( event ) {
					event.preventDefault();
				}
				setTimeout( function() {
					jQuery( 'body' ).toggleClass( 'fusion-builder-ui-wireframe' );
					jQuery( '.fusion-builder-wireframe-toggle' ).toggleClass( 'active' );
					contentWindow.jQuery( 'body' ).toggleClass( 'fusion-builder-ui-wireframe' );

					if ( contentWindow.jQuery( 'body' ).hasClass( 'fusion-builder-ui-wireframe' ) ) {
						FusionPageBuilderApp.wireframeActive = true;
						fusionBuilderContainer.css( 'max-width', window.FusionApp.settings.site_width );

						if ( fusionHeaderWrapperOffsetBottom > fusionBuilderContainerOffsetTop ) {
							fusionBuilderContainer.css( 'margin-top', ( fusionHeaderWrapperOffsetBottom + 25 ) + 'px' );
						}

						// Close nested column edit mode if needed.
						if ( contentWindow.jQuery( 'body' ).hasClass( 'nested-columns-edited' ) ) {
							FusionPageBuilderViewManager.getView( contentWindow.jQuery( '.fusion-builder-nested-element.editing' ).data( 'cid' ) ).stopEdit();
						}
					} else {
						FusionPageBuilderApp.wireframeActive = false;
						fusionBuilderContainer.css( {
							'margin-top': '',
							'max-width': ''
						} );
					}

					window.FusionEvents.trigger( 'fusion-wireframe-toggle' );
				}, 100 );
			}

		} );

	jQuery( document ).ready( function() {
		// Column sortables
		_.extend( FusionPageBuilder.BaseColumnView.prototype, {

			/**
			 * Destroy or disable element sortable.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			disableSortableElements: function() {
				var sortableContainer = this.$el.find( '.fusion-builder-column-content' );

				if ( 'undefined' !== typeof sortableContainer.sortable( 'instance' ) ) {
					sortableContainer.sortable( 'disable' );
				}

				sortableContainer.removeClass( 'ui-sortable' );
				sortableContainer.removeClass( 'ui-sortable-disabled' );
			},

			/**
			 * Initialize or enable the element sortable.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			enableSortableElements: function() {
				var sortableContainer = this.$el.find( '.fusion-builder-column-content' ),
					sortables;

				if ( 'fusion_builder_column' === this.model.get( 'element_type' ) ) {
					sortables = 'undefined' !== typeof sortableContainer.sortable( 'instance' ) ? true : false;
				} else {
					sortables = sortableContainer.data( 'sortable' );
				}

				if ( sortables ) {
					sortableContainer.sortable( 'enable' );
					sortableContainer.addClass( 'ui-sortable' );
				} else {
					this.sortableElements();
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
					this.disableDroppableColumn();
					this.enableSortableElements();
				} else {
					this.enableDroppableColumn();
					this.disableSortableElements();
					this._equalHeights();
				}
			},

			onSortOver: function( event ) {

				// Move sortable palceholder above +Element button for empty columns.
				if ( 1 === jQuery( event.target ).find( '.fusion-builder-live-element, .fusion_builder_row_inner' ).length ) {
					jQuery( event.target ).find( '.fusion-builder-column-content' ).append( '.ui-sortable-placeholder' );
				}
			},

			onSortUpdate: function() {
				this._equalHeights();
			},

			onSortStop: function( event, ui ) {
				var elementView = window.FusionPageBuilderViewManager.getView( ui.item.data( 'cid' ) ),
					newIndex    = ui.item.parent().children( '.fusion-builder-live-element, .fusion_builder_row_inner' ).index( ui.item ),
					MultiGlobalArgs;

				// Update collection
				window.FusionPageBuilderApp.onDropCollectionUpdate( elementView.model, newIndex, ui.item.parent().data( 'cid' ) );

				// Handle multiple global elements.
				MultiGlobalArgs = {
					currentModel: elementView.model,
					handleType: 'save',
					attributes: elementView.model.attributes
				};
				window.fusionGlobalManager.handleMultiGlobal( MultiGlobalArgs );

				// Save history
				window.FusionEvents.trigger( 'fusion-history-save-step', window.fusionBuilderText.moved + ' ' + window.fusionAllElements[ elementView.model.get( 'element_type' ) ].name + ' ' + window.fusionBuilderText.element );

				window.FusionEvents.trigger( 'fusion-content-changed' );
			}
		} );

		// Row sortables
		_.extend( FusionPageBuilder.BaseRowView.prototype, {

			/**
			 * Initialize or enable the column sortable.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			enableSortableColumns: function() {
				var rowContainer = this.$el.find( '.fusion-builder-row-container' ),
					sortables;

				if ( 'fusion_builder_row' === this.model.get( 'element_type' ) ) {
					sortables = 'undefined' !== typeof rowContainer.sortable( 'instance' ) ? true : false;
				} else {
					sortables = rowContainer.data( 'sortable' );
				}

				if ( sortables ) {
					rowContainer.sortable( 'enable' );
				} else {
					this.sortableColumns();
				}
			},

			onSortUpdate: function() {
				return undefined;
			},

			onSortStop: function( event, ui, items ) {
				var elementCid  = ui.item.data( 'cid' ),
					elementView = window.FusionPageBuilderViewManager.getView( elementCid ),
					originalCid = elementView.model.get( 'parent' ),
					parentCid   = ui.item.parent().data( 'cid' ),
					newIndex    = ui.item.parent().children( items ).index( ui.item ),
					originalView,
					destinationRow;

				// Update collection.
				window.FusionPageBuilderApp.onDropCollectionUpdate( elementView.model, newIndex, parentCid );

				destinationRow = FusionPageBuilderViewManager.getView( parentCid );
				destinationRow.setRowData();

				// If destination row and original row are different, update original as well.
				if ( parentCid !== originalCid ) {
					originalView = FusionPageBuilderViewManager.getView( originalCid );
					originalView.setRowData();
				}

				// History.
				window.FusionEvents.trigger( 'fusion-history-save-step', window.fusionBuilderText.column + ' order changed' );
			}
		} );
	} );
}( jQuery ) );
