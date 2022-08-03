/* global fusionAllElements */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionSelectField = {
	optionSelect: function( $element ) {
		var $selectField,
			conditions,
			param,
			defaultValue,
			value,
			params,
			self = this;

		$element     = $element || this.$el;
		$selectField = $element.find( '.fusion-select-field:not(.fusion-select-inited):not(.fusion-form-multiple-select):not(.fusion-ajax-select):not(.fusion-skip-init)' );

		if ( $selectField.length ) {

			$selectField.each( function() {
				var $self              = jQuery( this ),
					$selectDropdown    = $self.find( '.fusion-select-dropdown' ),
					$selectPreview     = $self.find( '.fusion-select-preview-wrap' ),
					$selectSearchInput = $self.find( '.fusion-select-search input' ),
					$selectPreviewText = $selectPreview.find( '.fusion-select-preview' );

				$self.addClass( 'fusion-select-inited' );

				// Open select dropdown.
				$selectPreview.on( 'click', function( event ) {
					var open = $self.hasClass( 'fusion-open' );

					event.preventDefault();

					if ( ! open ) {
						$self.addClass( 'fusion-open' );
						if ( $selectSearchInput.length ) {
							$selectSearchInput.focus();
						}
					} else {
						$self.removeClass( 'fusion-open' );
						if ( $selectSearchInput.length ) {
							$selectSearchInput.val( '' ).blur();
						}
						$self.find( '.fusion-select-label' ).css( 'display', 'block' );
					}
				} );

				// Option is selected.
				$self.on( 'click', '.fusion-select-label', function() {
					$selectPreviewText.html( jQuery( this ).html() );
					$selectPreview.trigger( 'click' );

					$selectDropdown.find( '.fusion-select-label' ).removeClass( 'fusion-option-selected' );
					jQuery( this ).addClass( 'fusion-option-selected' );

					$self.find( '.fusion-select-option-value' ).val( jQuery( this ).data( 'value' ) ).trigger( 'change', [ { userClicked: true } ] );
				} );

				// Conditional select init.
				if ( $self.is( '[data-conditions]' ) ) {
					conditions   = $self.data( 'conditions' ),
					param        = $self.closest( '.fusion-builder-option' ).attr( 'data-option-id' ),
					defaultValue = 'object' === typeof fusionAllElements[ self.model.get( 'element_type' ) ].params[ param ] ? fusionAllElements[ self.model.get( 'element_type' ) ].params[ param ][ 'default' ] : '',
					value        = null,
					params       = self.model.get( 'params' );

					conditions = conditions ? JSON.parse( _.unescape( conditions ) ) : false;
					if ( false !== conditions ) {
						if ( 'string' !== typeof conditions.option || 'object' !== typeof conditions.map ) {
							return;
						}

						// Check for value and if param exists.
						if ( 'undefined' !== params[ conditions.option ] ) {
							value = params[ conditions.option ];
						} else if ( 'object' === typeof fusionAllElements[ self.model.get( 'element_type' ) ].params[ param ] ) {
							value = fusionAllElements[ self.model.get( 'element_type' ) ].params[ param ][ 'default' ];
						}

						// Param exists and we have value.
						if ( null !== value ) {

							// We have accepted values, disable rest.
							if ( 'object' === typeof conditions.map[ value ] ) {
								$self.find( '.fusion-select-label' ).addClass( 'fusion-disabled' );
								_.each( conditions.map[ value ], function( acceptedValue ) {
									$self.find( '.fusion-select-label[data-value="' + acceptedValue + '"]' ).removeClass( 'fusion-disabled' );
								} );
							} else {
								$self.find( '.fusion-select-label' ).removeClass( 'fusion-disabled' );
							}

							// Listen for changes to other option.
							self.$el.find( '#' + conditions.option ).on( 'change', function() {
								var itemValue = jQuery( this ).val();

								// Find and disable options not valid.
								if ( 'object' === typeof conditions.map[ itemValue ] ) {
									$self.find( '.fusion-select-label' ).addClass( 'fusion-disabled' );
									_.each( conditions.map[ value ], function( acceptedValue ) {
										$self.find( '.fusion-select-label[data-value="' + acceptedValue + '"]' ).removeClass( 'fusion-disabled' );
									} );
								} else {
									$self.find( '.fusion-select-label' ).removeClass( 'fusion-disabled' );
								}

								// If selection is now invalid, reset to default.
								if ( $self.find( '.fusion-option-selected.fusion-disabled' ).length ) {
									$self.find( '.fusion-select-option-value' ).val( defaultValue ).trigger( 'change', [ { userClicked: true } ] );
								}
							} );
						}
					}
				}
				$self.find( '.fusion-select-option-value' ).on( 'change', function( event, data ) {

					if ( 'undefined' !== typeof data && 'undefined' !== typeof data.userClicked && true !== data.userClicked ) {
						return;
					}

					// Option changed progamatically, we need to update preview.
					$selectPreview.find( '.fusion-select-preview' ).html( $self.find( '.fusion-select-label[data-value="' + jQuery( this ).val() + '"]' ).html() );
					$selectDropdown.find( '.fusion-select-label' ).removeClass( 'fusion-option-selected' );
					$selectDropdown.find( '.fusion-select-label[data-value="' + jQuery( this ).val() + '"]' ).addClass( 'fusion-option-selected' );
				} );

				// Search field.
				$selectSearchInput.on( 'keyup change paste', function() {
					var val = jQuery( this ).val(),
						optionInputs = $self.find( '.fusion-select-label' );

					// Select option on "Enter" press if only 1 option is visible.
					if ( 'keyup' === event.type && 13 === event.keyCode && 1 === $self.find( '.fusion-select-label:visible' ).length ) {
						$self.find( '.fusion-select-label:visible' ).trigger( 'click' );
						return;
					}

					_.each( optionInputs, function( optionInput ) {
						if ( -1 === jQuery( optionInput ).html().toLowerCase().indexOf( val.toLowerCase() ) ) {
							jQuery( optionInput ).css( 'display', 'none' );
						} else {
							jQuery( optionInput ).css( 'display', 'block' );
						}
					} );
				} );

			} );
		}
	}
};
