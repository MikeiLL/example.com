/* global FusionApp, FusionPageBuilderApp, fusionAllElements, FusionPageBuilderViewManager, FusionEvents, fusionHistoryState, fusionAppConfig, fusionBuilderText, fusionGlobalManager */
/* eslint no-unused-vars: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Column Library View
		FusionPageBuilder.ContainerLibraryView = window.wp.Backbone.View.extend( {

			className: 'fusion_builder_modal_settings',
			template: FusionPageBuilder.template( jQuery( '#fusion-builder-container-library-template' ).html() ),
			events: {
				'click .fusion-builder-column-layouts li': 'addColumns',
				'click .fusion_builder_custom_sections_load': 'addCustomSection',
				'click .fusion-special-item': 'addSpecialItem'
			},

			/**
			 * Init.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			initialize: function( attributes ) {
				this.options = attributes;
			},

			/**
			 * Renders the view.
			 *
			 * @since 2.0.0
			 * @return {Object} this
			 */
			render: function() {
				this.$el.html( this.template( this.model.toJSON() ) );

				// Show saved custom sections
				FusionPageBuilderApp.showSavedElements( 'sections', this.$el.find( '#custom-sections' ) );

				FusionApp.elementSearchFilter( this.$el );

				FusionApp.dialog.dialogTabs( this.$el );

				return this;
			},

			/**
			 * Adds columns.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			addColumns: function( event ) {

				var that,
					$layoutEl,
					layout,
					layoutElementsNum,
					thisView,
					defaultParams,
					params,
					value,
					rowView,
					updateContent,
					columnAttributes,
					columnCids = [],
					columnCid,
					columnView;

				if ( event ) {
					event.preventDefault();
				}

				FusionPageBuilderApp.activeModal = 'column';

				that              = this;
				$layoutEl         = jQuery( event.target ).is( 'li' ) ? jQuery( event.target ) : jQuery( event.target ).closest( 'li' );
				layout            = '' !== $layoutEl.data( 'layout' ) ? $layoutEl.data( 'layout' ).split( ',' ) : false;
				layoutElementsNum = _.size( layout );
				thisView          = this.options.view;

				// Create row columns.
				if ( layout ) {
					_.each( layout, function( element, index ) {

						// Get default settings
						defaultParams = fusionAllElements.fusion_builder_column.params;
						params        = {};
						columnCid     = FusionPageBuilderViewManager.generateCid();
						columnCids.push( columnCid );

						// Process default parameters from shortcode
						_.each( defaultParams, function( param )  {
							value = ( _.isObject( param.value ) ) ? param[ 'default' ] : param.value;
							params[ param.param_name ] = value;
						} );

						params.type = element;

						updateContent    = layoutElementsNum == ( index + 1 ) ? 'true' : 'false'; // jshint ignore: line
						columnAttributes = {
							type: 'fusion_builder_column',
							element_type: 'fusion_builder_column',
							cid: columnCid,
							parent: that.model.get( 'cid' ),
							view: thisView,
							params: params
						};

						that.collection.add( [ columnAttributes ] );

					} );
				}

				// Unset 'added' attribute from newly created row model
				this.model.unset( 'added' );

				// Update view column calculations.
				rowView = FusionPageBuilderViewManager.getView( FusionPageBuilderApp.parentRowId );
				rowView.setRowData();

				FusionEvents.trigger( 'fusion-content-changed' );
				this.removeView();

				if ( event ) {

					_.each( columnCids, function( cid ) {
						columnView = FusionPageBuilderViewManager.getView( cid );
						if ( columnView ) {
							columnView.scrollHighlight( cid === columnCid );
						}
					} );

					// Save history state
					if ( true === FusionPageBuilderApp.newContainerAdded ) {
						window.fusionHistoryState = fusionBuilderText.added_section; // jshint ignore: line
						FusionPageBuilderApp.newContainerAdded = false;
					} else {
						window.fusionHistoryState = fusionBuilderText.added_columns; // jshint ignore: line
					}

					FusionEvents.trigger( 'fusion-history-save-step', window.fusionHistoryState );

					jQuery( '.fusion-builder-live' ).removeClass( 'fusion-builder-blank-page-active' );
				}
			},

			/**
			 * Adds a custom section.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			addCustomSection: function( event ) {
				var thisModel  = this.model,
					parentID   = this.model.get( 'parent' ),
					parentView = FusionPageBuilderViewManager.getView( parentID ),
					self       = this,
					layoutID,
					title,
					targetContainer,
					isGlobal;

				targetContainer = parentView.$el.prev( '.fusion-builder-container' );
				FusionPageBuilderApp.targetContainerCID = targetContainer.data( 'cid' );

				if ( event ) {
					event.preventDefault();
				}

				if ( 'undefined' !== typeof parentView ) {
					parentView.removeContainer();
				}

				if ( true === FusionPageBuilderApp.layoutIsLoading ) {
					return;
				}

				FusionPageBuilderApp.layoutIsLoading = true;

				layoutID = jQuery( event.currentTarget ).data( 'layout_id' );
				title    = jQuery( event.currentTarget ).find( '.fusion_module_title' ).text();
				isGlobal = jQuery( event.currentTarget ).closest( 'li' ).hasClass( 'fusion-global' );

				jQuery( event.currentTarget ).parent( '.fusion-builder-all-modules' ).css( 'opacity', '0' );
				jQuery( event.currentTarget ).parent( '.fusion-builder-all-modules' ).prev( '#fusion-loader' ).show();

				jQuery.ajax( {
					type: 'POST',
					url: fusionAppConfig.ajaxurl,
					data: {
						action: 'fusion_builder_load_layout',
						fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
						fusion_is_global: isGlobal,
						fusion_layout_id: layoutID
					}
				} )
				.done( function( data ) {
					var dataObj = JSON.parse( data );

					FusionPageBuilderApp.shortcodesToBuilder( dataObj.post_content, FusionPageBuilderApp.parentRowId );

					FusionPageBuilderApp.layoutIsLoading = false;

					jQuery( event.currentTarget ).parent( '.fusion-builder-all-modules' ).css( 'opacity', '1' );
					jQuery( event.currentTarget ).parent( '.fusion-builder-all-modules' ).prev( '#fusion-loader' ).hide();

					if ( isGlobal ) {
						setTimeout( fusionGlobalManager.handleGlobalsFromLibrary, 500, layoutID, FusionPageBuilderApp.parentRowId );
					}

				} )
				.always( function() {

					// Unset 'added' attribute from newly created section model
					thisModel.unset( 'added' );

					// Save history state
					FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.added_custom_section + title );

					jQuery( '.fusion-builder-live' ).removeClass( 'fusion-builder-blank-page-active' );

					FusionEvents.trigger( 'fusion-content-changed' );
					self.removeView();
				} );
			},

			/**
			 * Adds special item.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			addSpecialItem: function( event ) {
				var parentID   = this.model.get( 'parent' ),
					parentView = FusionPageBuilderViewManager.getView( parentID ),
					targetContainer,
					moduleID,
					params = {};

				if ( event ) {
					event.preventDefault();
				}

				targetContainer = parentView.$el.prev( '.fusion-builder-container' );
				FusionPageBuilderApp.targetContainerCID = targetContainer.find( '.fusion-builder-data-cid' ).data( 'cid' );
				moduleID = FusionPageBuilderViewManager.generateCid();

				this.collection.add( [
					{
						type: jQuery( event.currentTarget ).data( 'type' ),
						added: 'manually',
						module_type: jQuery( event.currentTarget ).data( 'type' ),
						cid: moduleID,
						params: params,
						view: parentView,
						appendAfter: targetContainer,
						created: 'auto'
					}
				] );

				if ( 'undefined' !== typeof parentView ) {
					FusionPageBuilderApp.targetContainerCID = '';
					parentView.removeContainer();
				}

				FusionEvents.trigger( 'fusion-content-changed' );

				FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.added_special_item + jQuery( event.currentTarget ).find( '.fusion_module_title' ).text() );

				this.removeView();

			},

			/**
			 * Removes the view.
			 *
			 * @since 4.0.0
			 * @return {void}
			 */
			removeView: function() {
				this.remove();
			}
		} );
	} );
}( jQuery ) );
