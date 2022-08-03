var FusionPageBuilder = FusionPageBuilder || {};

FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionColorPalette = {
	optionColorPalette: function( $element ) {
		var self = this,
			$palettes;

		$element  = $element || this.$el;
		$palettes = $element.find( '.fusion-color-palette-options' );

		$palettes.each( function() {
			var $paletteContainer = jQuery( this );

			$paletteContainer.find( '.fusion-color-palette-item' ).on( 'click', function( e ) {
				e.preventDefault();

				if ( 0 < $paletteContainer.find( '.fusion-color-palette-item.color-palette-active' ).length ) {
					return;
				}

				self.showColorPicker( jQuery( this ) );
			} );

			$paletteContainer.find( '.fusion-colorpicker-icon' ).on( 'click', function( e ) {
				e.preventDefault();

				self.hideColorPicker( $paletteContainer.find( '.fusion-color-palette-item.color-palette-active' ) );
			} );

		} );
	},

	showColorPicker: function( $colorItem ) {
		var $colorPickerWrapper = $colorItem.closest( '.fusion-color-palette-options' ).find( '.fusion-palette-colorpicker-container' );

		$colorItem.addClass( 'color-palette-active' );

		$colorPickerWrapper.find( '.fusion-builder-color-picker-hex' ).val( $colorItem.data( 'value' ) ).trigger( 'change' );

		setTimeout( function() {
			$colorPickerWrapper.find( '.wp-color-result' ).trigger( 'click' );
			$colorPickerWrapper.css( 'display', 'block' );
		}, 10 );
	},

	hideColorPicker: function( $colorItem ) {
		var $colorPickerWrapper = $colorItem.closest( '.fusion-color-palette-options' ).find( '.fusion-palette-colorpicker-container' );

		$colorItem.data( 'value', $colorPickerWrapper.find( '.fusion-builder-color-picker-hex' ).val() );
		$colorItem.children( 'span' ).css( 'background-color', $colorPickerWrapper.find( '.fusion-builder-color-picker-hex' ).val() );
		$colorItem.removeClass( 'color-palette-active' );
		$colorPickerWrapper.css( 'display', 'none' );
		this.updateColorPalette( $colorItem );
	},

	updateColorPalette: function( $colorItem ) {
		var $colorItems            = $colorItem.closest( '.fusion-color-palette-options' ).find( '.fusion-color-palette-item' ),
			colorValues            = [],
			$storeInput            = $colorItem.closest( '.fusion-color-palette-options' ).find( '.color-palette-colors' ),
			$generatedColorPickers = jQuery( '.fusion-builder-option.color-alpha, .fusion-builder-option.colorpickeralpha' );

		$colorItems.each( function() {
			colorValues.push( jQuery( this ).data( 'value' ) );
		} );

		// Wait for color picker's 'change' to finish.
		setTimeout( function() {
			$storeInput.val( colorValues.join( '|' ) ).trigger( 'change' );

			// Update any already generated color pickers.
			if ( 0 < $generatedColorPickers.length ) {
				jQuery.each( $generatedColorPickers, function() {

					jQuery.each( jQuery( this ).find( '.iris-palette' ), function( index, elem ) {

						// Skip first 2 colors.
						if ( 2 > index ) {
							return;
						}

						jQuery( elem ).data( 'color', colorValues[ index - 2 ] ).css( 'background-color', colorValues[ index - 2 ] );
					} );
				} );
			}
		}, 50 );

	}
};
