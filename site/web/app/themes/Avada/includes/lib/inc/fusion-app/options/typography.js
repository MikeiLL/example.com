/* global FusionApp, Fuse, fusionBuilderText */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionTypographyField = {

	/**
	 * Initialize the typography field.
	 *
	 * @since 2.0.0
	 * @param {Object} $element - The element jQuery object.
	 * @return {void}
	 */
	optionTypography: function( $element ) {
		var self = this;

		$element = 'undefined' !== typeof $element && $element.length ? $element : this.$el;

		if ( $element.find( '.wrapper .font-family' ).length ) {
			if ( _.isUndefined( FusionApp.assets ) || _.isUndefined( FusionApp.assets.webfonts ) ) {
				jQuery.when( FusionApp.assets.getWebFonts() ).done( function() {
					self.initAfterWebfontsLoaded( $element );
				} );
			} else {
				this.initAfterWebfontsLoaded( $element );
			}
		}
	},

	/**
	 * Make sure we initialize the field only after the webfonts are available.
	 * Since webfonts are loaded via AJAX we need this to make sure there are no errors.
	 *
	 * @since 2.0.0
	 * @param {Object} $element - The element jQuery object.
	 * @return {void}
	 */
	initAfterWebfontsLoaded: function( $element ) {
		this.renderFontSelector( $element );
	},

	/**
	 * Adds the font-families to the font-family dropdown
	 * and instantiates select2.
	 *
	 * @since 2.0.0
	 * @param {Object} $element - The element jQuery object.
	 * @return {void}
	 */
	renderFontSelector: function( $element ) {
		var self          = this,
			fonts         = FusionApp.assets.webfonts,
			standardFonts = [],
			googleFonts   = [],
			customFonts   = [],
			selectors     = $element.find( '.font-family .fusion-select-field' ),
			data          = [],
			$fusionSelect;

		// Format standard fonts as an array.
		if ( ! _.isUndefined( fonts.standard ) ) {
			_.each( fonts.standard, function( font ) {
				standardFonts.push( {
					id: font.family.replace( /&quot;/g, '&#39' ),
					text: font.label
				} );
			} );
		}

		// Format google fonts as an array.
		if ( ! _.isUndefined( fonts.google ) ) {
			_.each( fonts.google, function( font ) {
				googleFonts.push( {
					id: font.family,
					text: font.label
				} );
			} );
		}

		// Format custom fonts as an array.
		if ( ! _.isUndefined( fonts.custom ) ) {
			_.each( fonts.custom, function( font ) {
				if ( font.family && '' !== font.family ) {
					customFonts.push( {
						id: font.family.replace( /&quot;/g, '&#39' ),
						text: font.label
					} );
				}
			} );
		}

		// Combine forces and build the final data.
		if ( customFonts[ 0 ] ) {
			data.push( { text: 'Custom Fonts', children: customFonts } );
		}
		data.push( { text: 'Standard Fonts', children: standardFonts } );
		data.push( { text: 'Google Fonts',   children: googleFonts } );

		_.each( jQuery( selectors ), function( selector ) {
			var fontFamily = self.getTypographyVal( selector, 'font-family' ).replace( /'/g, '"' ),
				id         = jQuery( selector ).closest( '.fusion-builder-option' ).attr( 'data-option-id' );

			$fusionSelect = jQuery( selector ).fusionSelect( {
				fieldId: id,
				fieldName: 'font-family',
				fieldValue: fontFamily,
				data: data
			} );

			// Render dependent choices.
			setTimeout( function() {
				self.renderBackupFontSelector( id, fontFamily );
				self.renderVariantSelector( id, fontFamily );
			}, 70 );

			$fusionSelect.find( '.fusion-select-option-value' ).on( 'change', function() {

				// Re-render dependent elements on-change.
				self.renderBackupFontSelector( id, jQuery( this ).val() );
				self.renderVariantSelector( id, jQuery( this ).val() );

				// Load new font using the webfont-loader.
				self.webFontLoad( jQuery( this ).val(), self.getTypographyVal( id, 'variant' ), selector );
			} );
		} );
	},

	/**
	 * Adds the font-families to the font-family dropdown
	 * and instantiates select2.
	 *
	 * @since 2.0.0
	 * @param {string} id - The option ID.
	 * @param {string} fontFamily - The font-family selected.
	 * @return {void}
	 */
	renderBackupFontSelector: function( id, fontFamily ) {
		var self          = this,
			$option       = jQuery( '.fusion-builder-option[data-option-id="' + id + '"] .font-backup' ),
			standardFonts = [],
			$fusionSelect; // eslint-disable-line no-unused-vars

		// Format standard fonts as an array.
		if ( ! _.isUndefined( FusionApp.assets.webfonts.standard ) ) {
			_.each( FusionApp.assets.webfonts.standard, function( font ) {
				standardFonts.push( {
					id: font.family.replace( /&quot;/g, '&#39' ),
					text: font.label
				} );
			} );
		}

		$fusionSelect = $option.find( '.fusion-select-field' ).fusionSelect( { // eslint-disable-line no-unused-vars
			fieldId: id,
			fieldName: 'font-backup',
			data: [ { text: 'Standard Fonts', children: standardFonts } ]
		} );

		// Hide if we're not on a google-font and early exit.
		if ( false === self.isGoogleFont( fontFamily ) ) {
			$option.hide();
			self.setTypographyVal( id, 'font-backup', '' );
			return;
		}

		$option.show();
	},

	/**
	 * Renders the variants selector using select2
	 * Displays font-variants for the currently selected font-family.
	 *
	 * @since 2.0.0
	 * @param {string} id - The option ID.
	 * @param {string} fontFamily - The font-family selected.
	 * @return {void}
	 */
	renderVariantSelector: function( id, fontFamily ) {

		var self       = this,
			selector   = jQuery( '.fusion-builder-option[data-option-id="' + id + '"] .variant select' ),
			variants   = self.getVariants( fontFamily ),
			data       = [],
			variant    = self.getTypographyVal( id, 'variant' ),
			params;

		if ( false === variants ) {
			jQuery( selector ).closest( '.variant' ).hide();
		}

		if ( jQuery( selector ).closest( '.fusion-builder-option' ).hasClass( 'font_family' ) && '' === fontFamily ) {

			// Element, and switched to empty family, clear out variant param.
			if ( 'EO' == this.type ) {
				params                                = this.model.get( 'params' );
				params[ 'fusion_font_variant_' + id ] = '';
				jQuery( selector ).val( '' );
			}
			jQuery( selector ).closest( '.fusion-variant-wrapper' ).hide();
			return;
		}

		// If we got this far, show the selector.
		jQuery( selector ).closest( '.variant' ).show();
		jQuery( selector ).closest( '.fusion-variant-wrapper' ).show();
		jQuery( selector ).show();

		_.each( variants, function( scopedVariant ) {

			if ( scopedVariant.id && 'italic' === scopedVariant.id ) {
				scopedVariant.id = '400italic';
			}

			data.push( {
				id: scopedVariant.id,
				text: scopedVariant.label
			} );
		} );

		variant = self.getValidVariant( fontFamily, variant );

		// Clear old values.
		jQuery( selector ).empty();

		_.each( data, function( font ) {
			var selected = font.id === variant ? 'selected' : '';
			jQuery( selector ).append( '<option value="' + font.id + '" ' + selected + '>' + font.text + '</option>' );
		} );

		if ( self.isCustomFont( fontFamily ) ) {
			self.setTypographyVal( id, 'variant', '400' );
			self.setTypographyVal( id, 'font-weight', '400' );
		}

		// When the value changes.
		jQuery( selector ).on( 'fusion.typo-variant-loaded change', function() {
			self.getFontWeightFromVariant( jQuery( this ).val() );
			self.getFontStyleFromVariant( jQuery( this ).val() );

			// Load new font using the webfont-loader.
			self.webFontLoad( self.getTypographyVal( id, 'font-family' ), jQuery( this ).val(), selector );
		} );

		jQuery( selector ).val( variant ).trigger( 'fusion.typo-variant-loaded' );
	},

	/**
	 * Gets the font-weight from a variant.
	 *
	 * @since 2.0.0
	 * @param {string} variant The variant.
	 * @return {string} - Returns the font-weight.
	 */
	getFontWeightFromVariant: function( variant ) {
		if ( ! _.isString( variant ) ) {
			return '400';
		}
		if ( ! _.isObject( variant.match( /\d/g ) ) ) {
			return '400';
		}
		return variant.match( /\d/g ).join( '' );
	},

	/**
	 * Gets the font-weight from a variant.
	 *
	 * @since 2.0.0
	 * @param {string} variant - The variant.
	 * @return {string} - Returns the font-style.
	 */
	getFontStyleFromVariant: function( variant ) {
		if ( ! _.isUndefined( variant ) && _.isString( variant ) && -1 !== variant.indexOf( 'italic' ) ) {
			return 'italic';
		}
		return '';
	},

	/**
	 * Get variants for a font-family.
	 *
	 * @since 2.0.0
	 * @param {string} fontFamily - The font-family name.
	 * @return {Object} - Returns the variants for the selected font-family.
	 */
	getVariants: function( fontFamily ) {
		var variants = false;

		if ( this.isCustomFont( fontFamily ) ) {
			return [
				{
					id: '400',
					label: 'Normal 400'
				}
			];
		}

		_.each( FusionApp.assets.webfonts.standard, function( font ) {
			if ( fontFamily && font.family === fontFamily ) {
				variants = font.variants;
				return font.variants;
			}
		} );

		_.each( FusionApp.assets.webfonts.google, function( font ) {
			if ( font.family === fontFamily ) {
				variants = font.variants;
				return font.variants;
			}
		} );
		return variants;
	},

	/**
	 * Gets the value for this typography field.
	 *
	 * @since 2.0.0
	 * @param {string} selector - The selector for this option.
	 * @param {string} property - The property we want to get.
	 * @return {string|Object} - Returns a string if we have defined a property.
	 *                            If no property is defined, returns the full set of options.
	 */
	getTypographyVal: function( selector, property ) {
		var id,
			value = {},
			$option,
			optionName,
			params;

		// For element options, take from params.
		if ( 'EO' == this.type ) {
			if ( 'string' !== typeof selector ) {
				$option = jQuery( selector ).closest( '.fusion-builder-option' );
			} else {
				$option = jQuery( '.fusion-builder-option[data-option-id="' + selector + '"]' );
			}
			property      = property.replace( '-', '_' );
			optionName    = $option.find( '.input-' + property ).attr( 'name' );
			params        = this.model.get( 'params' );
			value         = params[ optionName ];

			if ( 'undefined' === typeof value || '' === value ) {
				value = $option.find( '.input-' + property ).attr( 'data-default' );
			}
			return value;
		}

		// The selector can be an ID or an actual element.
		if ( ! _.isUndefined( FusionApp.settings[ selector ] ) ) {
			id = selector;
		} else {
			id = jQuery( selector ).closest( '.fusion-builder-option' ).attr( 'data-option-id' );
		}

		// Get all values.
		if ( ! _.isUndefined( FusionApp.settings[ id ] ) ) {
			value = FusionApp.settings[ id ];
		}

		value = this.removeEmpty( value );

		// Define some defaults.
		value = _.defaults( value, {
			'font-family': '',
			'font-backup': '',
			variant: '400',
			'font-style': '',
			'font-weight': '400',
			'font-size': '',
			'line-height': '',
			'letter-spacing': '',
			'word-spacing': '',
			'text-align': '',
			'text-transform': '',
			color: '',
			'margin-top': '',
			'margin-bottom': ''
		} );

		// Variant specific return.
		if ( 'variant' === property && ! _.isUndefined( value[ property ] ) ) {
			if ( 'italic' === value[ 'font-style' ] ) {
				return value[ 'font-weight' ] + value[ 'font-style' ];
			}
			return value[ 'font-weight' ];
		}

		// Only return a specific property if one is defined.
		if ( ! _.isUndefined( property ) && property && ! _.isUndefined( value[ property ] ) )  {
			return value[ property ];
		}
		return value;
	},

	/**
	 * Remove empty values from params so when merging with defaults, the defaults are used.
	 *
	 * @since 2.0.0
	 * @param {Object} params - The parameters.
	 * @return {Object} - Returns the parameters without the emoty values.
	 */
	removeEmpty: function( params ) {
		var self = this;
		Object.keys( params ).forEach( function( key ) {
			if ( params[ key ] && 'object' === typeof params[ key ] ) {
				self.removeEmpty( params[ key ] );
			} else if ( null === params[ key ] || '' === params[ key ] ) {
				delete params[ key ];
			}
		} );
		return params;
	},

	/**
	 * Sets a parameter of the value in FusionApp.settings.
	 *
	 * @since 2.0.0
	 * @param {string} id - The option ID.
	 * @param {string} param - Where we'll save the value.
	 * @param {string} value - The value to set.
	 * @return {void}
	 */
	setTypographyVal: function( id, param, value ) {
		if ( 'EO' == this.type ) {
			return;
		}
		if ( _.isUndefined( FusionApp.settings[ id ] ) ) {
			FusionApp.settings[ id ] = {};
		}
		FusionApp.settings[ id ][ param ] = value;
	},

	/**
	 * Load the typography using webfont-loader.
	 *
	 * @param {string} family - The font-family
	 * @param {string} variant - The variant to load.
	 * @param {string} selector - The selector.
	 * @return {void}
	 */
	webFontLoad: function( family, variant, selector ) {
		var self         = this,
			isGoogleFont = self.isGoogleFont( family ),
			scriptID,
			script;

		// Get a valid variant.
		variant = self.getValidVariant( family, variant );

		// Early exit if there is no font-family defined.
		if ( _.isUndefined( family ) || '' === family || ! family ) {
			return;
		}

		// Check font has actually changed from default.
		if ( 'undefined' !== typeof selector && selector && ! this.checkFontChanged( family, variant, selector ) ) {
			return;
		}

		// Early exit if not a google-font.
		if ( false === isGoogleFont ) {
			return;
		}

		variant = ( _.isUndefined( variant ) || ! variant ) ? ':regular' : ':' + variant;
		family  = family.replace( /"/g, '&quot' );

		script  = family;
		script += ( variant ) ? variant : '';

		scriptID = script.replace( /:/g, '' ).replace( /"/g, '' ).replace( /'/g, '' ).replace( / /g, '' ).replace( /,/, '' );

		if ( ! jQuery( 'head' ).find( '#' + scriptID ).length ) {
			jQuery( 'head' ).first().append( '<script id="' + scriptID + '">WebFont.load({google:{families:["' + script + '"]},context:FusionApp.previewWindow,active: function(){ jQuery( window ).trigger( "fusion-font-loaded"); },});</script>' );
			return false;
		}
		return true;
	},

	/**
	 * Check if a font-family is a google-font or not.
	 *
	 * @since 2.0.0
	 * @param {string} family - The font-family to check.
	 * @return {boolean} - Whether the font-family is a google font or not.
	 */
	isGoogleFont: function( family ) {
		var isGoogleFont = false;

		// Figure out if this is a google-font.
		_.each( FusionApp.assets.webfonts.google, function( font ) {
			if ( font.family === family ) {
				isGoogleFont = true;
			}
		} );

		return isGoogleFont;
	},

	/**
	 * Check if a font-family is a custom font or not.
	 *
	 * @since 2.0.0
	 * @param {string} family - The font-family to check.
	 * @return {boolean} - Whether the font-family is a custom font or not.
	 */
	isCustomFont: function( family ) {
		var isCustom = false;

		// Figure out if this is a google-font.
		_.each( FusionApp.assets.webfonts.custom, function( font ) {
			if ( font.family === family ) {
				isCustom = true;
			}
		} );

		return isCustom;
	},

	/**
	 * Gets a valid variant for the font-family.
	 * This method checks if a defined variant is valid,
	 * and if not provides a valid fallback.
	 *
	 * @since 2.0.0
	 * @param {string} [family]  The font-family we'll be checking against.
	 * @param {string} [variant] The variant we want.
	 * @return {string} - Returns a valid variant for the defined font-family.
	 */
	getValidVariant: function( family, variant ) {

		var self       = this,
			variants   = self.getVariants( family ),
			isValid    = false,
			hasRegular = false,
			first      = ( ! _.isUndefined( variants[ 0 ] ) && ! _.isUndefined( variants[ 0 ].id ) ) ? variants[ 0 ].id : '';

		if ( this.isCustomFont( family ) ) {
			return '400';
		}

		_.each( variants, function( v ) {
			if ( variant === v.id ) {
				isValid = true;
			}
			if ( 'regular' === v.id || '400' === v.id || 400 === v.id ) {
				hasRegular = true;
			}
		} );

		if ( isValid ) {
			return variant;
		} else if ( hasRegular ) {
			return '400';
		}
		return first;
	},

	/**
	 * Checks that font has actually been changed.
	 *
	 * @since 2.0.0
	 * @param {string} family - The font-family.
	 * @param {string} variant - The variant for the defined font-family.
	 * @param {string} element - The element we're checking.
	 * @return {boolean} - Whether there was a change or not.
	 */
	checkFontChanged: function( family, variant, element ) {
		var id     = jQuery( element ).closest( '.fusion-builder-option' ).attr( 'data-option-id' ),
			values = FusionApp.settings[ id ];

		if ( 'EO' == this.type ) {
			return true;
		}
		variant = 'regular' === variant ? '400' : variant;

		if ( values[ 'font-family' ] !== family ) {
			return true;
		}
		if ( values.variant !== variant && values[ 'font-weight' ] !== variant ) {
			return true;
		}
		return false;
	}
};

jQuery.fn.fusionSelect = function( options ) {
	var checkBoxes         = '',
		$selectField       = jQuery( this ),
		$selectValue       = $selectField.find( '.fusion-select-option-value' ),
		$selectDropdown    = $selectField.find( '.fusion-select-dropdown' ),
		$selectPreview     = $selectField.find( '.fusion-select-preview-wrap' ),
		$selectSearchInput = $selectField.find( '.fusion-select-search input' );

	if ( $selectField.hasClass( 'fusion-select-inited' ) ) {
		return $selectField;
	}

	$selectField.addClass( 'fusion-select-inited' );

	if ( $selectField.closest( '.fusion-builder-option' ).hasClass( 'font_family' ) ) {
		checkBoxes += '<label class="fusion-select-label' + ( '' === $selectValue.val() ? ' fusion-option-selected' : '' ) + '" data-value="" data-id="">' + fusionBuilderText.typography_default + '</label>';
	}
	_.each( options.data, function( subset ) {
		checkBoxes += 'string' === typeof subset.text && 'font-family' === options.fieldName ? '<div class="fusion-select-optiongroup">' + subset.text + '</div>' : '';
		_.each( subset.children, function( name ) {
			var checked = name.id === $selectValue.val() ? ' fusion-option-selected' : '',
				id      = 'string' === typeof name.id ? name.id.replace( /"/g, '' ).replace( /'/g, '' ).toLowerCase() : '';

			checkBoxes += '<label class="fusion-select-label' + checked + '" data-value="' + name.id + '" data-id="' + id + '">' + name.text + '</label>';
		} );
	} );
	$selectField.find( '.fusion-select-options' ).html( checkBoxes );

	// Open select dropdown.
	$selectPreview.on( 'click', function( event ) {
		var open = $selectField.hasClass( 'fusion-open' );

		event.preventDefault();

		if ( ! open ) {
			$selectField.addClass( 'fusion-open' );
			if ( $selectSearchInput.length ) {
				$selectSearchInput.focus();
			}
		} else {
			$selectField.removeClass( 'fusion-open' );
			if ( $selectSearchInput.length ) {
				$selectSearchInput.val( '' ).blur();
			}
			$selectField.find( '.fusion-select-label' ).css( 'display', 'block' );
		}
	} );

	// Option is selected.
	$selectField.on( 'click', '.fusion-select-label', function() {
		$selectPreview.find( '.fusion-select-preview' ).html( jQuery( this ).html() );
		$selectPreview.trigger( 'click' );

		$selectDropdown.find( '.fusion-select-label' ).removeClass( 'fusion-option-selected' );
		jQuery( this ).addClass( 'fusion-option-selected' );

		$selectField.find( '.fusion-select-option-value' ).val( jQuery( this ).data( 'value' ) ).trigger( 'change', [ { userClicked: true } ] );
	} );

	$selectField.find( '.fusion-select-option-value' ).on( 'change', function( event, data ) {
		if ( 'undefined' !== typeof data && 'undefined' !== typeof data.userClicked && true !== data.userClicked ) {
			return;
		}

		// Option changed progamatically, we need to update preview.
		$selectPreview.find( '.fusion-select-preview' ).html( $selectField.find( '.fusion-select-label[data-value="' + jQuery( this ).val() + '"]' ).html() );
		$selectDropdown.find( '.fusion-select-label' ).removeClass( 'fusion-option-selected' );
		$selectDropdown.find( '.fusion-select-label[data-value="' + jQuery( this ).val() + '"]' ).addClass( 'fusion-option-selected' );
	} );

	// Search field.
	if ( 'font-family' === options.fieldName ) {
		$selectSearchInput.on( 'keyup change paste', function() {
			var value         = jQuery( this ).val(),
				standardFonts = 'object' === typeof options.data[ 0 ] ? jQuery.extend( true, options.data[ 0 ].children, {} ) : {},
				googleFonts   = 'object' === typeof options.data[ 1 ] ? jQuery.extend( true, options.data[ 1 ].children, {} ) : {},
				customFonts   = 'object' === typeof options.data[ 2 ] ? jQuery.extend( true, options.data[ 2 ].children, {} ) : {},
				fuseOptions,
				fuse,
				result;

			if ( 3 > value.length ) {
				$selectField.find( '.fusion-select-label' ).css( 'display', 'block' );
				return;
			}

			// Select option on "Enter" press if only 1 option is visible.
			if ( 'keyup' === event.type && 13 === event.keyCode && 1 === $selectField.find( '.fusion-select-label:visible' ).length ) {
				$selectField.find( '.fusion-select-label:visible' ).trigger( 'click' );
				return;
			}

			$selectField.find( '.fusion-select-label' ).css( 'display', 'none' );

			fuseOptions = {
				threshold: 0.2,
				location: 0,
				distance: 100,
				maxPatternLength: 32,
				minMatchCharLength: 3,
				keys: [ 'text' ]
			};

			fuse   = new Fuse( jQuery.extend( true, googleFonts, standardFonts, customFonts, {} ), fuseOptions );
			result = fuse.search( value );

			_.each( result, function( resultFont ) {
				$selectField.find( '.fusion-select-label[data-id="' + resultFont.id.replace( /"/g, '' ).replace( /'/g, '' ).toLowerCase() + '"]' ).css( 'display', 'block' );
			} );
		} );
	} else {
		$selectSearchInput.on( 'keyup change paste', function() {
			var val          = jQuery( this ).val(),
				optionInputs = $selectField.find( '.fusion-select-label' );

			// Select option on "Enter" press if only 1 option is visible.
			if ( 'keyup' === event.type && 13 === event.keyCode && 1 === $selectField.find( '.fusion-select-label:visible' ).length ) {
				$selectField.find( '.fusion-select-label:visible' ).trigger( 'click' );
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
	}

	return $selectField;
};
