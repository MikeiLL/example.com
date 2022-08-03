var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionColorPicker = {
	optionColorpicker: function( $element ) {
		var that = this,
			$colorPicker;

		$element     = 'undefined' !== typeof $element && $element.length ? $element : this.$el;
		$colorPicker = $element.find( '.fusion-builder-color-picker-hex' );

		if ( $colorPicker.length ) {
			$colorPicker.each( function() {
				var self          = jQuery( this ),
					$defaultReset = self.closest( '.fusion-builder-option' ).find( '.fusion-builder-default-reset' ),
					parentValue   = 'undefined' !== typeof that.parentValues && 'undefined' !== typeof that.parentValues[ self.attr( 'id' ) ] ? that.parentValues[ self.attr( 'id' ) ] : false;

				setTimeout( function() {
					var pickerWithDefault = self.data( 'default' ) && self.data( 'default' ).length;

					self.wpColorPicker( {
						create: function() {
							jQuery( self ).addClass( 'fusion-color-created' );
							that.updatePickerIconColor( self.val(), self );
						},
						change: function( event, ui ) {
							if ( pickerWithDefault ) {
								that.colorChange( ui.color.toString(), self, $defaultReset, parentValue, event.target.value );
							} else {
								that.colorChange( ui.color.toString(), self, undefined, undefined, event.target.value );
							}
							that.updatePickerIconColor( ui.color.toString(), self );
						},
						clear: function( event ) {
							if ( pickerWithDefault ) {
								that.colorClear( event, self, parentValue );
							} else {
								self.val( '' ).trigger( 'fusion-change' );
								self.closest( '.fusion-colorpicker-container' ).find( '.color-picker-placeholder' ).val( '' );
							}
						}
					} );

					// Make it so the reset link also clears color.
					if ( pickerWithDefault ) {
						$defaultReset.on( 'click', 'a', function( event ) {
							event.preventDefault();
							that.colorClear( event, self, parentValue );
						} );
					}

					// For some reason non alpha are not triggered straight away.
					if ( true !== self.data( 'alpha' ) ) {
						self.wpColorPicker().change();
					}

					self.closest( '.fusion-colorpicker-container' ).find( '.color-picker-placeholder' ).on( 'change', function() {
						var $el = jQuery( this );

						setTimeout( function() {
							var value = $el.val();

							if ( ! value ) {
								$el.closest( '.fusion-colorpicker-container' ).find( '.wp-color-picker' ).val( value ).attr( 'value', value ).trigger( 'change' );
							}
						}, 10 );
					} );

					self.on( 'blur', function() {
						if ( jQuery( this ).hasClass( 'iris-error' ) ) {
							jQuery( this ).removeClass( 'iris-error' );
							jQuery( this ).val( '' );
						}
					} );
				}, 10 );
			} );
		}
	},

	colorChange: function( value, self, defaultReset, parentValue, prevValue ) { // jshint ignore: line
		var defaultColor = parentValue ? parentValue : self.data( 'default' ),
			$placeholder = self.closest( '.fusion-colorpicker-container' ).find( '.color-picker-placeholder' ),
			valueRGBA = value.replace( / |\(|\)|rgba/g, '' ).split( ',' ),
			prevValueRGBA = prevValue.replace( / |\(|\)|rgba/g, '' ).split( ',' );

		// Initial preview for empty.
		if ( '' === value ) {
			self.addClass( 'fusion-using-default' );
			$placeholder.addClass( 'fusion-color-picker-placeholder-using-default' );
			self.val( defaultColor ).change();
			self.val( '' );
			return;
		}

		if ( value === defaultColor && 'TO' !== self.attr( 'data-location' ) && 'PO' !== self.attr( 'data-location' ) && 'FBE' !== self.attr( 'data-location' ) ) {
			setTimeout( function() {
				self.val( '' ).change();
			}, 10 );
			if ( defaultReset ) {
				defaultReset.addClass( 'checked' );

				// Update default value in description.
				defaultReset.parent().find( '> a' ).html( defaultColor );
			}
		} else {
			self.removeClass( 'fusion-using-default' );
			$placeholder.removeClass( 'fusion-color-picker-placeholder-using-default' );
			if ( defaultReset ) {
				defaultReset.removeClass( 'checked' );
			}
			self.val( value ).change();
		}

		// If alpha is 0 and we're changing to a different color reset alpha to 1
		if (
			value !== prevValue &&
			( valueRGBA[ 3 ] && '0' == valueRGBA[ 3 ] ) &&
			( ( prevValueRGBA[ 3 ] && prevValueRGBA[ 3 ] === valueRGBA[ 3 ] ) || ( '' === prevValue ) )
		) {
			valueRGBA[ 3 ] = 1;
			self.val( 'rgba( ' + valueRGBA.join( ',' ) + ' )' ).change();
		}

		setTimeout( function() {
			self.closest( '.wp-picker-container' ).find( '.wp-color-result' ).css( {
				backgroundImage: '',
				backgroundColor: value
			} );
		}, 100 );
	},

	colorChanged: function( value, self ) {
		self.val( value );
		self.change();
	},

	updatePickerIconColor: function( value, self ) {
		var colorObj  = jQuery.Color( value ),
			lightness = parseInt( colorObj.lightness() * 100, 10 );

		if ( 0.3 < colorObj.alpha() && 70 > lightness ) {
			self.closest( '.fusion-colorpicker-container' ).find( '.fusion-colorpicker-icon' ).css( 'color', '#fff' );
		} else {
			self.closest( '.fusion-colorpicker-container' ).find( '.fusion-colorpicker-icon' ).removeAttr( 'style' );
		}
	},

	colorClear: function( event, self, parentValue ) {
		var defaultColor = parentValue ? parentValue : self.data( 'default' ),
			$placeholder = self.closest( '.fusion-colorpicker-container' ).find( '.color-picker-placeholder' );

		$placeholder.val( '' );

		if ( ! self.hasClass( 'fusion-default-changed' ) && self.hasClass( 'fusion-using-default' ) ) {
			self.closest( '.wp-picker-container' ).find( '.wp-color-result' ).css( 'background-color', defaultColor );
			return;
		}

		if ( null !== defaultColor && ( 'TO' !== self.closest( '.fusion-builder-option' ).data( 'type' ) || 'FBE' !== self.closest( '.fusion-builder-option' ).data( 'type' ) ) ) {
			self.addClass( 'fusion-using-default' );
			$placeholder.addClass( 'fusion-color-picker-placeholder-using-default' );
			self.removeClass( 'fusion-default-changed' );
			self.val( defaultColor ).change();
			self.val( '' );
			self.closest( '.wp-picker-container' ).find( '.wp-color-result' ).css( 'background-color', defaultColor );
		} else if ( null !== defaultColor && ( 'TO' === self.closest( '.fusion-builder-option' ).data( 'type' ) || 'FBE' === self.closest( '.fusion-builder-option' ).data( 'type' ) ) ) {
			self.val( defaultColor ).change();
			self.closest( '.wp-picker-container' ).find( '.wp-color-result' ).css( 'background-color', defaultColor );
		}
	}
};
