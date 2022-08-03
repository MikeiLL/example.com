/* global FusionApp, FusionPageBuilderApp, fusionAppConfig, fusionBuilderText, FusionEvents, fusionAllElements, FusionPageBuilderViewManager, fusionHistoryState */
/* eslint no-unused-vars: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Column Library View
		FusionPageBuilder.ColumnLibraryView = window.wp.Backbone.View.extend( {

			className: 'fusion_builder_modal_settings',
			template: FusionPageBuilder.template( jQuery( '#fusion-builder-column-library-template' ).html() ),
			events: {
				'click .fusion-builder-column-layouts li': 'addColumns',
				'click .fusion_builder_custom_columns_load': 'addCustomColumn'
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

				FusionPageBuilderApp.showSavedElements( 'columns', this.$el.find( '#custom-columns' ) );

				FusionApp.elementSearchFilter( this.$el );

				FusionApp.dialog.dialogTabs( this.$el );

				return this;
			},

			/**
			 * Adds a custom column.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			addCustomColumn: function( event ) {
				var thisModel,
					layoutID,
					title,
					self = this,
					isGlobal;

				if ( event ) {
					event.preventDefault();
				}

				FusionPageBuilderApp.activeModal = 'column';

				if ( true === FusionPageBuilderApp.layoutIsLoading ) {
					return;
				}
				FusionPageBuilderApp.layoutIsLoading = true;

				thisModel = this.model;
				layoutID  = jQuery( event.currentTarget ).data( 'layout_id' );
				title     = jQuery( event.currentTarget ).find( '.fusion_module_title' ).text();
				isGlobal  = jQuery( event.currentTarget ).closest( 'li' ).hasClass( 'fusion-global' );

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
						setTimeout( window.fusionGlobalManager.handleGlobalsFromLibrary, 500, layoutID, FusionPageBuilderApp.parentRowId );
					}

				} )
				.always( function() {

					// Unset 'added' attribute from newly created row model
					thisModel.unset( 'added' );

					// Save history state
					FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.added_custom_column + title );

					FusionEvents.trigger( 'fusion-content-changed' );
					self.removeView();
				} );
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
					columnView,
					atIndex,
					targetElement,
					lastCreated;

				if ( event ) {
					event.preventDefault();
				}

				FusionPageBuilderApp.activeModal = 'column';

				that              = this;
				$layoutEl         = jQuery( event.target ).is( 'li' ) ? jQuery( event.target ) : jQuery( event.target ).closest( 'li' );
				layout            = $layoutEl.data( 'layout' ).split( ',' );
				layoutElementsNum = _.size( layout );
				thisView          = this.options.view,
				targetElement     = ( 'undefined' !== typeof this.options.targetElement ) ? this.options.targetElement : false;

				atIndex = FusionPageBuilderApp.getCollectionIndex( targetElement );

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
						params: params,
						at_index: atIndex
					};

					// Append to last created column
					if ( lastCreated ) {
						targetElement = FusionPageBuilderViewManager.getView( lastCreated );
						targetElement = targetElement.$el;
					}

					if ( targetElement ) {
						columnAttributes.targetElement = targetElement;
						columnAttributes.targetElementPosition = 'after';
					}

					FusionPageBuilderApp.collection.add( [ columnAttributes ] );

					lastCreated = columnCid;

					if ( 'new' === atIndex ) {
						atIndex = 1;
					} else {
						atIndex++;
					}
				} );

				// Unset 'added' attribute from newly created row model
				this.model.unset( 'added' );

				// Update view column calculations.
				rowView = FusionPageBuilderViewManager.getView( FusionPageBuilderApp.parentRowId );

				if ( rowView ) {
					rowView.createVirtualRows();
					rowView.updateColumnsPreview();
				}

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
				}
			},

			/**
			 * Removes the view.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			removeView: function() {
				this.remove();
			}
		} );
	} );
}( jQuery ) );
