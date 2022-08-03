/* global FusionApp, fusionAllElements, FusionEvents, FusionPageBuilderViewManager, FusionPageBuilderApp, FusionPageBuilderElements, fusionBuilderText, fusionGlobalManager, fusionSanitize */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Builder Inner Row View
		FusionPageBuilder.InnerRowView = FusionPageBuilder.BaseRowView.extend( {

			className: 'fusion_builder_row_inner fusion_builder_column_element fusion-nested-columns fusion-builder-nested-element',

			template: FusionPageBuilder.template( jQuery( '#fusion-builder-row-inner-template' ).html() ),

			defaultZIndex: 10,

			events: {
				'click .fusion-builder-row-remove': 'removeRow',
				'click .fusion-builder-row-save': 'openLibrary',
				'click .fusion-builder-row-clone': 'cloneNestedRow',
				'click .fusion-builder-row-settings': 'editRow',
				'click .fusion-builder-stop-editing': 'stopEdit',
				'click .fusion-builder-cancel-row': 'cancelChanges',
				'click .fusion-builder-row-add-child': 'displayInnerColumn',
				'click .fusion-builder-insert-inner-column': 'displayInnerColumn',
				'click .fusion-builder-settings': 'editNestedColumn',
				'click .fusion-builder-modal-save': 'closeNestedPopupAndSave',
				'click .fusion-builder-inner-row-close': 'closeNestedPopup',
				'click .fusion-builder-modal-close': 'closeNestedPopup',
				'mousedown .fusion-builder-nested-columns-settings-overlay': 'overlayMousedown'
			},

			/**
			 * Init.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			initialize: function() {

				this.baseRowInit();
				this.nestedRow = true;

				this.model.set( 'rows', {} );
				this.$el.attr( 'data-cid', this.model.get( 'cid' ) );

				if ( 'undefined' !== typeof this.model.attributes.params && 'undefined' !== typeof this.model.attributes.params.fusion_global ) {
					this.$el.attr( 'fusion-global-layout', this.model.attributes.params.fusion_global );
					this.$el.removeClass( 'fusion-global-nested-row' ).addClass( 'fusion-global-nested-row' );
				}

				// Close modal view
				this.listenTo( FusionEvents, 'fusion-close-inner-modal', this.hideInnerRowDialog );
				this.model.children = new FusionPageBuilder.Collection();
				this.savedContent   = '';

				this.listenTo( this.model.children, 'add', this.addChildView );
				this.listenTo( FusionEvents, 'fusion-builder-loaded', this.updateColumnsPreview );

				this.listenTo( FusionEvents, 'fusion-wireframe-toggle', this.wireFrameToggled );
			},

			/**
			 * Renders the view.
			 *
			 * @since 2.0.0
			 * @return {Object} this
			 */
			render: function() {
				var self = this;

				this.$el.html( this.template( this.getTemplateAtts() ) );

				setTimeout( function() {
					self.droppableColumn();
					self.droppableElement();
					self.updateWireframeUI();
				}, 100 );

				return this;
			},

			updateWireframeUI: function() {
				var innerColumnsWrapper = this.$el,
					innerColumnsString  = '';

				this.$el.find( '.fusion-nested-column-preview-columns' ).remove();

				innerColumnsWrapper.find( '.fusion-builder-column-inner' ).each( function() {
					innerColumnsString += String( jQuery( this ).data( 'column-size' ) ).replace( '_', '/' ) + ' + ';
				} );

				innerColumnsWrapper.find( '.fusion-nested-column-preview-title' ).after( '<p class="fusion-nested-column-preview-columns">' + innerColumnsString.slice( 0, innerColumnsString.length - 3 ) + '</p>' );
			},

			/**
			 * Stop propagation on overlay mousedown.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			overlayMousedown: function( event ) {
				event.stopPropagation();
			},

			/**
			 * The row attributes.
			 *
			 * @since 3.0
			 * @return {Object}
			 */
			attr: function() {
				var attr 			= {
						style: ''
					},
					parentContainer = FusionPageBuilderApp.getParentContainer( this ),
					isFlex			= parentContainer && parentContainer.isFlex(),
					containerParams = jQuery.extend( true, {}, fusionAllElements.fusion_builder_container.defaults, _.fusionCleanParameters( parentContainer.model.attributes.params ) ),
					width,
					widthValue,
					columnSpacingUnit,
					columnSpacingValue,
					widthUnit;

				attr.id 			= 'fusion-builder-row-' + this.model.get( 'cid' );
				attr[ 'class' ] 	= 'fusion-row fusion-builder-row-container-inner fusion-builder-row-inner fusion-builder-row';
				attr[ 'data-cid' ] 	= this.model.get( 'cid' );

				if ( isFlex ) {
					attr[ 'class' ]  += ' fusion-flex-align-items-' + containerParams.flex_align_items;
						if ( 'stretch' !== containerParams.align_content ) {
							attr[ 'class' ] += ' fusion-flex-align-content-' + containerParams.align_content;
						}
						if ( 'flex-start' !== containerParams.flex_justify_content ) {
							attr[ 'class' ] += ' fusion-flex-justify-content-' + containerParams.flex_justify_content;
						}
						width             = '100%';
						columnSpacingUnit = fusionSanitize.get_unit( containerParams.flex_column_spacing );

						if ( '%' === columnSpacingUnit ) {
							columnSpacingValue = fusionSanitize.number( containerParams.flex_column_spacing );
							widthValue         = fusionSanitize.number( width );
							widthUnit          = fusionSanitize.get_unit( width );

							width = ( widthValue * ( 1 + ( columnSpacingValue / 100 ) ) ) + widthUnit;
						} else {
							width = 'calc( ' + width + ' + ' + containerParams.flex_column_spacing + ' )';
						}

						attr.style += 'max-width:' + width + ' !important;';
						attr.style += 'width:' + width + ';';

						attr.style += 'margin-left: calc(-' + containerParams.flex_column_spacing + ' / 2 );';
						attr.style += 'margin-right: calc(-' + containerParams.flex_column_spacing + ' / 2 );';
				}

				return attr;
			},

			/**
			 * Get template attributes.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			getTemplateAtts: function()  {
				var templateAttributes 	= {};

				templateAttributes.id		= this.model.get( 'cid' );
				templateAttributes.title 	= fusionAllElements[ this.model.get( 'element_type' ) ].name;
				templateAttributes.params 	= this.model.get( 'params' );
				templateAttributes.attr		= this.attr();

				return templateAttributes;
			},

			/**
			 * Get the content.
			 *
			 * @since 2.0.0
			 * @return {string}
			 */
			getContent: function() {
				return this.getInnerRowContent();
			},

			/**
			 * Creates drop zone for empty nested row.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			droppableColumn: function() {
				var $el  = this.$el;

				$el.find( '.fusion-nested-column-target' ).droppable( {
					tolerance: 'touch',
					hoverClass: 'ui-droppable-active',
					accept: '.fusion-builder-column',
					drop: function( scopedEvent, ui ) {
						var parentCid      = jQuery( scopedEvent.target ).closest( '.fusion-builder-row-content' ).data( 'cid' ),
							destinationRow = FusionPageBuilderViewManager.getView( parentCid ),
							columnCid      = ui.draggable.data( 'cid' ),
							columnView     = FusionPageBuilderViewManager.getView( columnCid ),
							originalCid    = columnView.model.get( 'parent' ),
							originalView,
							newIndex;

						newIndex = ui.draggable.parent().children( '.fusion-builder-column' ).index( ui.draggable );

						FusionPageBuilderApp.onDropCollectionUpdate( columnView.model, newIndex, self.model.get( 'cid' ) );

						// Move the actual html.
						$el.find( '.fusion-builder-row-container-inner' ).append( ui.draggable );

						// Update destination row which is this current one.
						destinationRow.setRowData();

						// If destination row and original row are different, update original as well.
						if ( parentCid !== originalCid ) {
							originalView = FusionPageBuilderViewManager.getView( originalCid );
							originalView.setRowData();
						}

						FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.column + ' order changed' );

						setTimeout( function() {
							columnView.droppableColumn();
						}, 300 );
					}
				} );
			},

			/**
			 * Destroy or disable the droppable and draggable.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			disableDroppableElement: function() {
				var $el = this.$el;

				// If its been init, just disable.
				if ( 'undefined' !== typeof $el.draggable( 'instance' ) ) {
					$el.draggable( 'disable' );
				}

				// If its been init, just disable.
				if ( 'undefined' !== typeof $el.find( '> .fusion-element-target' ).droppable( 'instance' ) ) {
					$el.find( '> .fusion-element-target' ).droppable( 'disable' );
				}
			},

			/**
			 * Enable the droppable and draggable.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			enableDroppableElement: function() {
				var $el = this.$el;

				// If they have been init, then just disable.
				if ( 'undefined' !== typeof $el.draggable( 'instance' ) && 'undefined' !== typeof $el.find( '> .fusion-element-target' ).droppable( 'instance' ) ) {
					$el.draggable( 'enable' );
					$el.find( '> .fusion-element-target' ).droppable( 'enable' );
				} else {

					// No sign of init, then need to call it.
					this.droppableElement();
				}
			},

			/**
			 * Creates droppable zone and makes element draggable.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			droppableElement: function() {
				var $el   = this.$el,
					cid   = this.model.get( 'cid' ),
					$body = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' );

				$el.draggable( {
					appendTo: FusionPageBuilderApp.$el,
					zIndex: 999999,
					delay: 100,
					cursorAt: { top: 15, left: 15 },
					iframeScroll: true,
					containment: $body,
					cancel: '.fusion-live-editable, .fusion-builder-live-child-element:not( [data-fusion-no-dragging] )',
					helper: function() {
						var $classes = FusionPageBuilderApp.DraggableHelpers.draggableClasses( cid );
						return jQuery( '<div class="fusion-element-helper ' + $classes + '" data-cid="' + cid + '"><span class="fusiona-column"></span></div>' );
					},
					start: function() {
						$body.addClass( 'fusion-element-dragging fusion-active-dragging' );
						$el.addClass( 'fusion-being-dragged' );
						$el.prev( '.fusion-builder-live-element' ).find( '.target-after' ).addClass( 'target-disabled' );
					},
					stop: function() {
						setTimeout( function() {
							$body.removeClass( 'fusion-element-dragging fusion-active-dragging' );
						}, 10 );
						$el.removeClass( 'fusion-being-dragged' );
						FusionPageBuilderApp.$el.find( '.target-disabled' ).removeClass( 'target-disabled' );
					}
				} );

				$el.find( '.fusion-element-target' ).droppable( {
					tolerance: 'touch',
					hoverClass: 'ui-droppable-active',
					accept: '.fusion-builder-live-element, .fusion_builder_row_inner',
					drop: function( event, ui ) {
						var parentCid      = jQuery( event.target ).closest( '.fusion-builder-column' ).data( 'cid' ),
							columnView     = FusionPageBuilderViewManager.getView( parentCid ),
							elementCid     = ui.draggable.data( 'cid' ),
							elementView    = FusionPageBuilderViewManager.getView( elementCid ),
							MultiGlobalArgs,
							newIndex;

						// Move the actual html.
						if ( jQuery( event.target ).hasClass( 'target-after' ) ) {
							$el.after( ui.draggable );
						} else {
							$el.before( ui.draggable );
						}

						newIndex = ui.draggable.parent().children( '.fusion-builder-live-element, .fusion_builder_row_inner' ).index( ui.draggable );

						FusionPageBuilderApp.onDropCollectionUpdate( elementView.model, newIndex, parentCid );

						// Save history state
						FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.moved + ' ' + fusionAllElements[ elementView.model.get( 'element_type' ) ].name + ' ' + fusionBuilderText.element );

						// Handle multiple global elements.
						MultiGlobalArgs = {
							currentModel: elementView.model,
							handleType: 'save',
							attributes: elementView.model.attributes
						};
						fusionGlobalManager.handleMultiGlobal( MultiGlobalArgs );

						FusionEvents.trigger( 'fusion-content-changed' );

						columnView._equalHeights();
					}
				} );

				// If we are in wireframe mode, then disable.
				if ( FusionPageBuilderApp.wireframeActive ) {
					this.disableDroppableElement();
				}
			},

			/**
			 * Clones a nested row.
			 *
			 * @since 2.0.0
			 * @param {Object}    event - The event.
			 * @param {parentCID} parentCID - The parent element's CID.
			 * @return {void}
			 */
			cloneNestedRow: function( event, parentCID ) {
				var innerRowAttributes,
					thisInnerRow,
					innerColAttributes;

				if ( 'object' === typeof event ) {
					event.preventDefault();
				}

				innerRowAttributes          = jQuery.extend( true, {}, this.model.attributes );
				innerRowAttributes.created  = 'manually';
				innerRowAttributes.cid      = FusionPageBuilderViewManager.generateCid();
				innerRowAttributes.at_index = FusionPageBuilderApp.getCollectionIndex( this.$el );

				if ( 'object' === typeof event ) {
					innerRowAttributes.targetElement         = this.$el;
					innerRowAttributes.targetElementPosition = 'after';
				} else if ( 'clone' === event ) {
					// Don't need target element, position is defined from order.
					delete innerRowAttributes.targetElementPosition;
				}

				if ( parentCID ) {
					innerRowAttributes.parent = parentCID;
				}

				FusionPageBuilderApp.collection.add( innerRowAttributes );

				// Parse inner columns
				thisInnerRow = this.$el;
				thisInnerRow.find( '.fusion-builder-column-inner' ).each( function() {
					var $thisColumnInner  = jQuery( this ),
						columnInnerCID    = $thisColumnInner.data( 'cid' ),
						innerColumnModule = FusionPageBuilderViewManager.getView( columnInnerCID );

					// Clone model attritubes
					innerColAttributes = jQuery.extend( true, {}, innerColumnModule.model.attributes );

					innerColAttributes.created = 'manually';
					innerColAttributes.cid     = FusionPageBuilderViewManager.generateCid();
					innerColAttributes.parent  = innerRowAttributes.cid;

					FusionPageBuilderApp.collection.add( innerColAttributes );

					// Parse elements inside inner col
					$thisColumnInner.find( '.fusion-builder-live-element' ).each( function() {
						var thisModule = jQuery( this ),
							moduleCID  = 'undefined' === typeof thisModule.data( 'cid' ) ? thisModule.find( '.fusion-builder-data-cid' ).data( 'cid' ) : thisModule.data( 'cid' ),

							// Get model from collection by cid
							module = FusionPageBuilderElements.find( function( model ) {
								return model.get( 'cid' ) == moduleCID; // jshint ignore: line
							} ),

							// Clone model attritubes
							innerElementAttributes = jQuery.extend( true, {}, module.attributes );

						innerElementAttributes.created = 'manually';
						innerElementAttributes.cid     = FusionPageBuilderViewManager.generateCid();
						innerElementAttributes.parent  = innerColAttributes.cid;
						innerElementAttributes.from    = 'fusion_builder_row_inner';

						// Don't need target element, position is defined from order.
						delete innerElementAttributes.targetElementPosition;

						FusionPageBuilderApp.collection.add( innerElementAttributes );
					} );
				} );

				if ( ! parentCID ) {

					// Save history state
					FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.cloned_nested_columns );

					FusionEvents.trigger( 'fusion-content-changed' );
				}
			},

			/**
			 * Adds the 'editing' and 'nested-ui-active' classes.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			editRow: function( event ) {
				var parentColumn,
					parentRow;

				if ( event ) {
					event.preventDefault();
				}

				// We have opened, means we no longer need added flag.
				this.model.unset( 'added' );

				this.updateSavedContent();

				parentColumn = FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );
				parentColumn.$el.addClass( 'fusion-builder-editing-child' );
				parentColumn.$el.removeClass( 'active' );
				parentColumn.$el.closest( '.fusion-builder-container' ).removeClass( 'fusion-column-sizer-active' ).addClass( 'fusion-container-editing-child' );

				parentRow = parentColumn.$el.closest( '.fusion-builder-row' );
				parentRow.addClass( 'fusion-builder-row-editing-child' );
				parentRow.parent().closest( '.fusion-builder-row' ).addClass( 'fusion-builder-row-editing-child' );

				this.$el.addClass( 'editing' );
				this.$el.append( '<div class="fusion-row-overlay"></div>' );
				jQuery( 'body' ).addClass( 'nested-ui-active' );
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).append( '<div class="fusion-row-overlay"></div>' );
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).addClass( 'nested-ui-active nested-columns-edited' );

				FusionEvents.trigger( 'fusion-row-nested-edit' );
				FusionEvents.trigger( 'fusion-history-pause-tracking' );
			},

			updateSavedContent: function() {
				this.savedContent = this.getInnerRowContent();
			},

			/**
			 * Removes the 'editing' and 'nested-ui-active' classes, saves history step.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			stopEdit: function( event ) {
				if ( event ) {
					event.preventDefault();
				}

				this.removeEditUI();

				// Close children settings.
				this.closeChildrenSettings();

				FusionEvents.trigger( 'fusion-history-resume-tracking' );

				if ( true === this.contentChanged() ) {
					window.fusionHistoryState = fusionBuilderText.edited + ' ' + fusionAllElements[ this.model.get( 'element_type' ) ].name + ' ' + fusionBuilderText.element;
					FusionEvents.trigger( 'fusion-history-save-step', window.fusionHistoryState );
				}
			},

			/**
			 * Checks if content changed.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			contentChanged: function() {
				var content = this.getInnerRowContent();

				if ( content !== this.savedContent ) {
					return true;
				}
				return false;
			},

			/**
			 * Removes the 'editing' and 'nested-ui-active' classes.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			removeEditUI: function( event ) {
				var parentColumn;

				if ( event ) {
					event.preventDefault();
				}

				parentColumn = FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );
				parentColumn.$el.removeClass( 'fusion-builder-editing-child' );
				parentColumn.$el.closest( '.fusion-builder-row' ).removeClass( 'fusion-builder-row-editing-child' );
				parentColumn.$el.closest( '.fusion-container-editing-child' ).removeClass( 'fusion-container-editing-child' );

				this.$el.removeClass( 'editing' );
				this.$el.find( '.fusion-row-overlay' ).remove();
				jQuery( 'body' ).removeClass( 'nested-ui-active' );
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).find( '.fusion-row-overlay' ).remove();
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).removeClass( 'nested-ui-active nested-columns-edited' );
			},

			/**
			 * Closes children settings.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			closeChildrenSettings: function() {
				_.each( this.collection.models, function( model ) {
					if ( ( 'element' === model.attributes.type || 'fusion_builder_column_inner' === model.attributes.type ) && 0 < jQuery( '.fusion_builder_module_settings[data-cid="' + model.attributes.cid + '"]' ).length ) {
						FusionEvents.trigger( 'fusion-close-settings-' + model.attributes.cid );
					}
				} );
			},

			/**
			 * Gets the contents of the inner row.
			 *
			 * @since 2.0.0
			 * @return {string}
			 */
			getInnerRowContent: function() {
				var shortcode       = '',
					$thisRowInner   = this.$el,
					thisRowInnerCID = $thisRowInner.data( 'cid' ),
					module          = FusionPageBuilderElements.findWhere( { cid: thisRowInnerCID } ); // eslint-disable-line no-unused-vars

				shortcode += '[fusion_builder_row_inner]';

				$thisRowInner.find( '.fusion-builder-column-inner' ).each( function() {
					var $thisColumnInner = jQuery( this ),
						columnCID        = $thisColumnInner.data( 'cid' ),
						columnView       = FusionPageBuilderViewManager.getView( columnCID );

					shortcode += columnView.getColumnContent();
				} );

				shortcode += '[/fusion_builder_row_inner]';

				return shortcode;
			},

			/**
			 * Removes a row.
			 *
			 * @since 2.0.0
			 * @param {Object}         event - The event.
			 * @param {boolean|undefined} force - Should we force-remove the row?
			 * @return {void}
			 */
			removeRow: function( event ) {
				var columns,
					parentView = FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );

				if ( event ) {
					event.preventDefault();
				}

				columns = FusionPageBuilderViewManager.getChildViews( this.model.get( 'cid' ) );

				// Remove columns
				_.each( columns, function( column ) {
					column.removeColumn();
				} );

				FusionPageBuilderViewManager.removeView( this.model.get( 'cid' ) );

				FusionEvents.trigger( 'fusion-element-removed', this.model.get( 'cid' ) );

				this.model.destroy();

				this.remove();

				// If row ( nested columns ) is removed manually
				if ( event ) {

					// Save history state
					FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.deleted_nested_columns );

					FusionEvents.trigger( 'fusion-content-changed' );

					parentView.$el.find( '.fusion-builder-module-controls-container a' ).trigger( 'mouseleave' );

					this.removeEditUI();
				}
			},

			/**
			 * Reverts nested column changes.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			cancelChanges: function( event ) {
				if ( event ) {
					event.preventDefault();
				}

				this.removeEditUI();

				// Close children settings.
				this.closeChildrenSettings();

				if ( true === this.contentChanged() ) {
					FusionPageBuilderApp.shortcodesToBuilder( this.savedContent, this.model.get( 'parent' ), 'undefined', 'undefined', this.$el );
					this.removeRow();
				}

				FusionEvents.trigger( 'fusion-history-resume-tracking' );
			},

			addNestedColumn: function( element, appendAfter, targetElement, atIndex ) {
				var that,
					thisView,
					defaultParams,
					params,
					parent,
					value,
					columnAttributes;

				parent   = this.model.get( 'cid' );
				that     = this;
				thisView = this.options.view;

				// Get default options
				defaultParams = fusionAllElements.fusion_builder_column_inner.params;
				params = {};

				// Process default parameters from shortcode
				_.each( defaultParams, function( param )  {
					value = ( _.isObject( param.value ) ) ? param[ 'default' ] : param.value;
					params[ param.param_name ] = value;
				} );

				params.type = element;

				columnAttributes = {
					type: 'fusion_builder_column_inner',
					element_type: 'fusion_builder_column_inner',
					cid: FusionPageBuilderViewManager.generateCid(),
					parent: parent,
					view: thisView,
					params: params,
					targetElement: targetElement,
					at_index: atIndex
				};

				// Make sure not clones
				columnAttributes = jQuery.extend( true, {}, columnAttributes );

				that.collection.add( [ columnAttributes ] );

				return columnAttributes.cid;
			},

			/**
			 * Appends children. Calls the delegateEvents function in the view.
			 *
			 * @since 2.0.0
			 * @param {bool} reRender
			 * @return {void}
			 */
			appendChildren: function( reRender = true ) {

				var self = this,
					cid,
					view;

				this.model.children.each( function( child ) {

					cid  = child.attributes.cid;
					view = FusionPageBuilderViewManager.getView( cid );

					self.$el.find( '.fusion-builder-row-container-inner' ).append( reRender ? view.render().$el : view.$el );

					view.delegateEvents();
				} );
			},

			/**
			 * Adds a child view.
			 *
			 * @since 2.0.0
			 * @param {Object} element - The element model.
			 * @return {void}
			 */
			addChildView: function( element ) {
				var view,
					viewSettings = {
						model: element,
						collection: FusionPageBuilderElements,
						attributes: {
							'data-cid': element.get( 'cid' )
						}
					};

				viewSettings.className = 'fusion-builder-column fusion-builder-column-inner';

				// Calculate virtual rows
				this.createVirtualRows();

				view = new FusionPageBuilder.NestedColumnView( viewSettings );

				// This column was cloned
				if ( ! _.isUndefined( element.get( 'cloned' ) ) && true === element.get( 'cloned' ) ) {
					element.targetElement = view.$el;
					element.unset( 'cloned' );
				}

				FusionPageBuilderViewManager.addView( element.get( 'cid' ), view );

				if ( ! _.isUndefined( element.get( 'targetElement' ) ) && 'undefined' === typeof element.get( 'from' ) ) {
					if ( 'undefined' === typeof element.get( 'targetElementPosition' ) || 'after' === element.get( 'targetElementPosition' ) ) {
						element.get( 'targetElement' ).after( view.render().el );
					} else {
						element.get( 'targetElement' ).before( view.render().el );
					}
				} else {
					if ( 'undefined' === typeof element.get( 'targetElementPosition' ) || 'end' === element.get( 'targetElementPosition' ) ) {
						this.$el.find( '.fusion-builder-row-container-inner' ).append( view.render().el );
					} else {
						this.$el.find( '.fusion-builder-row-container-inner' ).prepend( view.render().el );
					}
					element.unset( 'from' );
				}

				this.updateColumnsPreview();
			},

			displayInnerColumn: function( event ) {
				var view,
					viewSettings,
					columnCID;

				if ( event ) {
					event.preventDefault();
				}

				columnCID = jQuery( event.currentTarget ).closest( '.fusion-builder-column-inner' ).data( 'cid' );

				FusionPageBuilderApp.parentRowId = this.model.get( 'cid' );

				viewSettings = {
					model: this.model,
					collection: this.collection,
					view: this,
					attributes: {
						'data-modal_view': 'nested_column_library',
						'data-parent_cid': this.model.get( 'cid' ),
						'data-nested_column_cid': columnCID
					},
					nested: true
				};

				view = new FusionPageBuilder.NestedColumnLibraryView( viewSettings );

				jQuery( view.render().el ).dialog( {
					title: 'Select Column',
					width: FusionApp.dialog.dialogWidth,
					height: FusionApp.dialog.dialogHeight,
					draggable: false,
					modal: true,
					resizable: false,
					dialogClass: 'fusion-builder-dialog fusion-builder-element-library-dialog',
					open: function( scopedEvent ) {
						var $dialogContent = jQuery( scopedEvent.target );
						$dialogContent.find( '.fusion-builder-modal-top-container' ).appendTo( '.fusion-builder-element-library-dialog .ui-dialog-titlebar' );
						FusionApp.dialog.resizeDialog();
					},
					close: function() {
						view.remove();
					}
				} );
			},

			/**
			 * Opens nested columns in popup for wireframe mode.
			 *
			 * @since 2.0.0
			 * @param {Object} element - The element model.
			 * @return {void}
			 */
			editNestedColumn: function( event ) {
				var self = this;

				if ( event ) {
					event.preventDefault();
				}

				this.savedContent = this.getInnerRowContent();

				this.$el.find( '.fusion-builder-row-content' ).addClass( 'fusion-builder-row-content-active' );
				self.$el.closest( '.fusion-row' ).addClass( 'fusion-builder-row-active' );
				this.$el.closest( '.fusion-builder-ui-wireframe' ).addClass( 'fusion-builder-nested-cols-dialog-open' );

				// Hides column size popup.
				this.$el.closest( '.fusion-builder-column' ).removeClass( 'active' );
				this.$el.closest( '.fusion-builder-container' ).removeClass( 'fusion-column-sizer-active' );

				FusionEvents.trigger( 'fusion-history-turn-on-tracking' );
				FusionEvents.trigger( 'fusion-history-capture-editor' );
				FusionEvents.trigger( 'fusion-history-turn-off-tracking' );
				FusionEvents.trigger( 'fusion-history-pause-tracking' );
			},

			/**
			 * Closes nested columns in popup for wireframe mode.
			 *
			 * @since 2.0.0
			 * @param {Object} element - The element model.
			 * @return {void}
			 */
			closeNestedPopupAndSave: function( event ) {
				if ( event ) {
					event.preventDefault();
				}

				this.updateWireframeUI();
				this.$el.find( '.fusion-builder-row-content' ).removeClass( 'fusion-builder-row-content-active' );
				this.$el.closest( '.fusion-row' ).removeClass( 'fusion-builder-row-active' );
				this.$el.closest( '.fusion-builder-ui-wireframe' ).removeClass( 'fusion-builder-nested-cols-dialog-open' );

				FusionEvents.trigger( 'fusion-history-resume-tracking' );
			},

			/**
			 * Closes nested columns in popup for wireframe mode and revert changes.
			 *
			 * @since 2.0.0
			 * @param {Object} element - The element model.
			 * @return {void}
			 */
			closeNestedPopup: function( event ) {
				if ( event ) {
					event.preventDefault();
				}

				this.$el.find( '.fusion-builder-row-content' ).removeClass( 'fusion-builder-row-content-active' );
				this.$el.closest( '.fusion-row' ).removeClass( 'fusion-builder-row-active' );
				this.$el.closest( '.fusion-builder-ui-wireframe' ).removeClass( 'fusion-builder-nested-cols-dialog-open' );

				if ( true === this.contentChanged() ) {
					FusionPageBuilderApp.shortcodesToBuilder( this.savedContent, this.model.get( 'parent' ), 'undefined', 'undefined', this.$el );
					this.removeRow();
				}
				FusionEvents.trigger( 'fusion-history-resume-tracking' );
			},

			/**
			 * Opens the library. Builds the settings for this view
			 * and then calls FusionPageBuilder.LibraryView and renders it.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The js event.
			 * @return {void}
			 */
			openLibrary: function( event ) {
				var view,
					libraryModel = {
						target: jQuery( event.currentTarget ).data( 'target' ),
						focus: jQuery( event.currentTarget ).data( 'focus' ),
						element_cid: this.model.get( 'cid' ),
						element_name: 'undefined' !== typeof this.model.get( 'admin_label' ) && '' !== this.model.get( 'admin_label' ) ? this.model.get( 'admin_label' ) : ''
					},
					viewSettings = {
						model: libraryModel
					};

				if ( event ) {
					event.preventDefault();
					event.stopPropagation();
					FusionPageBuilderApp.sizesHide( event );
				}

				view = new FusionPageBuilder.LibraryView( viewSettings );
				view.render();

				// Make sure to close any context menus which may be open.
				FusionPageBuilderApp.removeContextMenu();
			},

			/**
			 * Fired when wireframe mode is toggled.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			wireFrameToggled: function() {
				if ( FusionPageBuilderApp.wireframeActive ) {

					if ( jQuery( 'body' ).hasClass( 'nested-ui-active' ) ) {
						this.stopEdit();
					}

					this.enableSortableColumns();
					this.disableDroppableElement();
					this.updateWireframeUI();
				} else {
					this.disableSortableColumns();
					this.enableDroppableElement();
				}
			},

			recalculateMargins: function() {
				var attr = this.attr();

				this.$el.find( '.fusion-builder-row-container-inner' ).first().attr( 'style', attr.style );
			},

			/**
			 * Destroy or disable column sortable.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			disableSortableColumns: function() {
				var rowContainer = this.$el.find( '.fusion-builder-row-container' );

				rowContainer.sortable( 'disable' );
			},

			/**
			 * Initialize column sortable.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			sortableColumns: function() {
				var sortableColumns = this.$el.find( '.fusion-builder-row-container-inner' ),
					items = '.fusion-builder-column-inner',
					self = this;

				sortableColumns.sortable( {
					items: items,
					helper: 'clone',
					cancel: '.fusion-builder-settings-column, .fusion-builder-column-size, .fusion-builder-column-clone, .fusion-builder-column-remove, .fusion-builder-add-element, .fusion-builder-insert-column, .fusion-builder-save-column, .column-sizes, .fusion-builder-modal-save, .fusion-builder-inner-row-close',
					tolerance: 'pointer',

					update: function() {
						self.onSortUpdate();
					},

					stop: function( event, ui ) {
						self.onSortStop( event, ui, items );
					}

				} ).disableSelection();
			}
		} );
	} );
}( jQuery ) );
