/* global FusionPageBuilderViewManager, FusionEvents, FusionPageBuilderApp, fusionGlobalManager */
/* eslint no-unused-vars: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Builder Element View
		FusionPageBuilder.ElementSettingsParent = window.wp.Backbone.View.extend( {

			template: FusionPageBuilder.template( jQuery( '#fusion-builder-child-sortables' ).html() ),
			events: {
				'click .fusion-builder-add-multi-child': 'addChildElement',
				'click .fusion-builder-multi-setting-remove': 'removeChildElement',
				'click .fusion-builder-multi-setting-clone': 'cloneChildElement',
				'click .fusion-builder-multi-setting-options': 'editChildElement'
			},

			/**
			 * Init.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			initialize: function() {
				this.elementView = FusionPageBuilderViewManager.getView( this.model.get( 'cid' ) );
				this.listenTo( FusionEvents, 'fusion-child-changed', this.render );
				this.listenTo( this.model.children, 'add', this.render );
				this.listenTo( this.model.children, 'remove', this.render );
				this.listenTo( this.model.children, 'sort', this.render );
				this.settingsView = this.attributes.settingsView;
			},

			/**
			 * Renders the view.
			 *
			 * @since 2.0.0
			 * @return {Object} this
			 */
			render: function() {
				this.$el.html( this.template( this.model ) );
				this.sortableOptions();

				return this;
			},

			/**
			 * Make sortable.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			sortableOptions: function() {
				var self = this;

				this.$el.find( '.fusion-builder-sortable-children' ).sortable( {
					axis: 'y',
					cancel: '.fusion-builder-multi-setting-remove, .fusion-builder-multi-setting-options, .fusion-builder-multi-setting-clone',
					helper: 'clone',

					update: function( event, ui ) {
						var content   = '',
							newIndex    = ui.item.parent().children( '.ui-sortable-handle' ).index( ui.item ),
							elementView = FusionPageBuilderViewManager.getView( ui.item.data( 'cid' ) ),
							childView;

						// Update collection
						FusionPageBuilderApp.onDropCollectionUpdate( elementView.model, newIndex, self.model.get( 'cid' ) );

						self.$el.find( '.fusion-builder-sortable-children li' ).each( function() {
							childView = FusionPageBuilderViewManager.getView( jQuery( this ).data( 'cid' ) );
							content  += FusionPageBuilderApp.generateElementShortcode( childView.$el, false );
						} );

						self.elementView.setContent( content );

						// After sorting of children remove the preview block class, as the mouseleave sometimes isn't triggered.
						if ( ! jQuery( 'body' ).hasClass( 'fusion-sidebar-resizing' ) && jQuery( 'body' ).hasClass( 'fusion-preview-block' ) ) {
							jQuery( 'body' ).removeClass( 'fusion-preview-block' );
						}

						// Save history state
						FusionEvents.trigger( 'fusion-history-save-step', window.fusionBuilderText.moved + ' ' + window.fusionAllElements[ childView.model.get( 'element_type' ) ].name + ' ' + window.fusionBuilderText.element );
					}
				} );
			},

			/**
			 * Adds a child element view and renders it.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			addChildElement: function( event ) {

				event.preventDefault();
				this.elementView.addChildElement();
				this.render();

				this.settingsView.optionHasChanged = true;
			},

			/**
			 * Removes a child element view and rerenders.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			removeChildElement: function( event ) {
				var childCid,
					childView,
					MultiGlobalArgs;

				childCid  = jQuery( event.target ).closest( '.fusion-builder-data-cid' ).data( 'cid' );
				childView = FusionPageBuilderViewManager.getView( childCid );

				event.preventDefault();

				childView.removeElement( event );
				this.render();

				// Handle multiple global elements.
				MultiGlobalArgs = {
					currentModel: childView.model,
					handleType: 'changeOption'
				};
				fusionGlobalManager.handleMultiGlobal( MultiGlobalArgs );

				this.elementView.childViewRemoved();

				this.settingsView.optionHasChanged = true;
			},

			/**
			 * Clones a child element view and rerenders.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			cloneChildElement: function( event ) {
				var childCid,
					childView,
					parentView,
					MultiGlobalArgs;

				childCid   = jQuery( event.target ).closest( '.fusion-builder-data-cid' ).data( 'cid' );
				childView  = FusionPageBuilderViewManager.getView( childCid );
				parentView = FusionPageBuilderViewManager.getView( this.model.get( 'cid' ) );

				event.preventDefault();

				childView.cloneElement();

				this.render();

				// Handle multiple global elements.
				MultiGlobalArgs = {
					currentModel: childView.model,
					handleType: 'changeOption'
				};
				fusionGlobalManager.handleMultiGlobal( MultiGlobalArgs );

				this.elementView.childViewCloned();

				this.settingsView.optionHasChanged = true;
			},

			/**
			 * Edits a child element view and rerenders.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			editChildElement: function( event ) {
				var childCid  = jQuery( event.target ).closest( '.fusion-builder-data-cid' ).data( 'cid' ),
					childView = FusionPageBuilderViewManager.getView( childCid );

				event.preventDefault();

				childView.settings();
			}
		} );
	} );
}( jQuery ) );
