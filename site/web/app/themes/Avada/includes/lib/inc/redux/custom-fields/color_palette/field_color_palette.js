/*global jQuery */

( function() {
	'use strict';

	fusionredux.field_objects               = fusionredux.field_objects || {};
	fusionredux.field_objects.color_palette = fusionredux.field_objects.color_palette || {};

	fusionredux.field_objects.color_palette.init = function( selector ) {

		if ( ! selector ) {
			selector = jQuery( document ).find( '.fusionredux-group-tab:visible' ).find( '.fusionredux-container-color_palette:visible' );
		}

		jQuery( selector ).each( function() {
			var $paletteContainer = jQuery( this );

			$paletteContainer.find( '.color-palette-color-picker-hex' ).wpColorPicker( {
				palettes: false,
				width: 350,
				hide: false,
				change: function( event, ui ) {
					var $colorItem = jQuery( this ).closest( '.fusion_theme_options-color_palette' ).find( '.color-palette-active' );

					$colorItem.find( 'span' ).css( 'background-color', ui.color.toString() );
					$colorItem.data( 'value', ui.color.toString() );
					fusionredux.field_objects.color_palette.updateColorPalette( $colorItem );
				}
			} );

			$paletteContainer.find( '.fusion-color-palette-item' ).each( function() {

				jQuery( this ).on( 'click', function( e ) {
					e.preventDefault();

					// Color picker for this item was already opened.
					// if ( jQuery( this ).hasClass( 'color-palette-active' ) ) {
					// 	fusionredux.field_objects.color_palette.closeColorPicker( jQuery( this ) );
					// 	return;
					// }

					if ( 0 < $paletteContainer.find( '.fusion-color-palette-item.color-palette-active' ).length ) {
						return;
					}

					fusionredux.field_objects.color_palette.showColorPicker( jQuery( this ) );
				} );
			} );

		} );
	};

	fusionredux.field_objects.color_palette.addOutsideClickListener = function( event ) {
		if ( 0 === jQuery( event.target ).closest( '.fusion-palette-colorpicker-container' ).length ) {
			fusionredux.field_objects.color_palette.closeColorPicker( jQuery( '.color-palette-active' ) );
		}
	};

	fusionredux.field_objects.color_palette.showColorPicker = function( $colorItem ) {
		var $colorPickerWrapper = $colorItem.closest( '.fusion_theme_options-color_palette' ).find( '.fusion-palette-colorpicker-container' );

		$colorItem.addClass( 'color-palette-active' );
		$colorPickerWrapper.find( '.color-palette-color-picker-hex' ).iris( 'option', 'color', $colorItem.data( 'value' ) );
		$colorPickerWrapper.css( 'display', 'inline-block' );

		event.stopPropagation();
		jQuery( document ).on( 'click', fusionredux.field_objects.color_palette.addOutsideClickListener );
	};

	fusionredux.field_objects.color_palette.closeColorPicker = function( $colorItem ) {
		var $colorPickerWrapper    = $colorItem.closest( '.fusion_theme_options-color_palette' ).find( '.fusion-palette-colorpicker-container' ),
			$storeInput            = $colorItem.closest( '.fusion_theme_options-color_palette' ).find( '.color-palette-colors' ),
			$generatedColorPickers = jQuery( '.fusionredux-container-color_alpha' ),
			colorValues            = [];

		// Wait for color picker's 'change' to finish.
		setTimeout( function() {

			if ( 'undefined' !== typeof fusionColorPalette ) {

				// Update fusionColorPalette global var.
				fusionColorPalette.color_palette = $storeInput.val();
				colorValues                      = fusionColorPalette.color_palette.split( '|' );
			}

			// Update any already generated color pickers.
			if ( 0 < $generatedColorPickers.length && 0 < colorValues.length ) {
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

		$colorItem.removeClass( 'color-palette-active' );
		$colorPickerWrapper.css( 'display', 'none' );

		jQuery( document ).off( 'click', fusionredux.field_objects.color_palette.addOutsideClickListener );
	};

	fusionredux.field_objects.color_palette.updateColorPalette = function( $colorItem ) {
		var $colorItems = $colorItem.closest( '.fusion_theme_options-color_palette' ).find( '.fusion-color-palette-item' ),
			colorValues = [],
			$storeInput = $colorItem.closest( '.fusion_theme_options-color_palette' ).find( '.color-palette-colors' );

		$colorItems.each( function() {
			colorValues.push( jQuery( this ).data( 'value' ) );
		} );

		$storeInput.val( colorValues.join( '|' ) );
	};

} ( jQuery ) );
