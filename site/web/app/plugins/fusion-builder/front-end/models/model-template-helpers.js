/* global FusionPageBuilderViewManager */
/* eslint no-useless-escape: 0 */
/* eslint max-depth: 0 */
/* eslint no-continue: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

_.mixin( {

	/**
	 * The FusionBuilder::attributes() function from PHP, translated to JS.
	 *
	 * @since 2.0.0
	 * @param {Object|string} attributes - The attributes.
	 * @return {string} Ready to use in templates/HTML.
	 */
	fusionGetAttributes: function( attributes ) {
		var out = '';
		if ( 'string' === typeof attributes ) {
			return 'class="' + attributes + '"';
		}
		_.each( attributes, function( value, name ) {
			if ( 'undefined' !== typeof value ) {
				value = value.toString();

				if ( 'valueless_attribute' === value ) {
					out += ' ' + name;
				} else if ( 0 < value.length ) {
					value = value.replace( /\s\s+/g, ' ' );
					out += ' ' + name + '="' + value + '"';
				}
			}
		} );
		return out;
	},

	/**
	 * Remove empty values from params so when merging with defaults, the defaults are used.
	 *
	 * @since 2.0.0
	 * @param {Object} params - The parameters.
	 * @return {Object} - Returns the params.
	 */
	fusionCleanParameters: function( params ) {
		Object.keys( params ).forEach( function( key ) {
			if ( params[ key ] && 'object' === typeof params[ key ] ) {
				_.fusionCleanParameters( params[ key ] );
			} else if ( null === params[ key ] || '' === params[ key ] ) {
				delete params[ key ];
			}
		} );
		return params;
	},

	/**
	 * Builds section title element.
	 *
	 * @since 2.2
	 * @param {Object} values - The values.
	 * @param {Object} extras - The extras.
	 * @return {string}
	 */
	buildTitleElement: function( values, extras, headingContent ) {
		var contentAlign = jQuery( 'body' ).hasClass( 'rtl' ) ? 'right' : 'left',
			size         = parseInt( values.heading_size ),
			sizeArray  = [
				'one',
				'two',
				'three',
				'four',
				'five',
				'six'
			],
			output = '',
			styles = '',
			headingStyles   = '',
			classes         = '',
			wrapperClasses  = '',
			marginTop       = extras.title_margin.top,
			marginBottom    = extras.title_margin.bottom,
			sepColor        = extras.title_border_color,
			styleType       = extras.title_style_type,
			underlineOrNone = -1 !== styleType.indexOf( 'underline' ) || -1 !== styleType.indexOf( 'none' );

		// Render title.

		_.each( styleType.split( ' ' ), function( className ) {
			classes += ' sep-' + className;
		} );

		wrapperClasses = ' fusion-title fusion-title-size-' + sizeArray[ size - 1 ] + classes;

		if ( marginTop ) {
			styles += 'margin-top:' + _.fusionGetValueWithUnit( marginTop ) + ';';
		}
		if ( marginBottom ) {
			styles += 'margin-bottom:' +  _.fusionGetValueWithUnit( marginBottom ) + ';';
		}

		if ( '' !== marginTop || '' !== marginBottom ) {
			headingStyles += 'margin:0;';
		}

		if ( false !== underlineOrNone ) {

			if ( -1 !== styleType.indexOf( 'underline' ) && sepColor ) {
				styles += 'border-bottom-color:' + sepColor + ';';
			} else if ( -1 !== styleType.indexOf( 'none' ) ) {
				classes += ' fusion-sep-none';
			}
		}

		output += '<div class="' + wrapperClasses + '" style="' + styles + '">';
		if ( false === underlineOrNone && 'right' === contentAlign ) {
			output += '<div class="title-sep-container">';
			output += '<div class="title-sep' + classes + '"></div>';
			output += '</div>';
		}

		output += '<h' + size + ' class="title-heading-' + contentAlign + '" style="' + headingStyles + '">';
		output += headingContent;
		output += '</h' + size + '>';

		if ( false === underlineOrNone && 'left' === contentAlign ) {
			output += '<div class="title-sep-container">';
			output += '<div class="title-sep' + classes + '"></div>';
			output += '</div>';
		}
		output += '</div>';

		return output;
	},

	/**
	 * Copy of our fusion_builder_visibility_atts() function in PHP.
	 *
	 * @since 2.0.0
	 * @param {string}        selection - The selection.
	 * @param {Object|string} attr - The attributes.
	 * @return {Object} The attributes modified to accomodate visibility options from the selection parameter.
	 */
	fusionVisibilityAtts: function( selection, attr ) {

		var allVisibilityValues = [
				'small-visibility',
				'medium-visibility',
				'large-visibility'
			],
			visibilityValues = allVisibilityValues,
			visibilityOptions;

		// If empty, show all.
		if ( '' === selection ) {
			selection = visibilityValues;
		}

		// If no is used, change that to all options selected, as fallback.
		if ( 'no' === selection ) {
			selection = visibilityValues;
		}

		// If yes is used, use all selections with mobile visibility removed.
		if ( 'yes' === selection ) {
			visibilityValues = visibilityValues.filter( function( e ) {
				return 'small-visibility' !== e;
			} );
			selection = visibilityValues;
		}

		// Make sure the selection is an array.
		if ( 'string' === typeof selection ) {
			selection = selection.split( ',' );
			_.each( selection, function( value, key ) {
				selection[ key ] = value.replace( new RegExp( ' ', 'g' ), '' );
			} );
		}

		visibilityOptions = allVisibilityValues;
		_.each( visibilityOptions, function( visibilityOption ) {
			if ( selection && -1 === selection.indexOf( visibilityOption ) ) {
				if ( 'object' === typeof attr ) {
					attr[ 'class' ] += ' fusion-no-' + visibilityOption;
				} else {
					attr += ' fusion-no-' + visibilityOption;
				}
			}
		} );

		return attr;
	},

	/**
	 * Returns the available animation types.
	 *
	 * @since 2.1
	 * @return {Object} Animation types.
	 */
	fusionGetAnimationTypes: function() {
		var animations = [
			'bounce',
			'bouncIn',
			'bounceInUp',
			'bounceInDown',
			'bounceInLeft',
			'bounceInRight',
			'fadeIn',
			'fadeInUp',
			'fadeInDown',
			'fadeInLeft',
			'fadeInRight',
			'flash',
			'rubberBand',
			'shake',
			'slideIn',
			'slideInUp',
			'slideInDown',
			'slideInLeft',
			'slideInRight',
			'zoomIn',
			'zoomInUp',
			'zoomInDown',
			'zoomInRight',
			'zoomInLeft',
			'flipinx',
			'flipiny',
			'lightspeedin'
		];

		return animations;
	},

	/**
	 * The FusionBuilder::animations() function from PHP, translated to JS.
	 *
	 * @since 2.0.0
	 * @param {Object}       args - The arguments.
	 * @param {string}       args.type - The animation type.
	 * @param {string}       args.direction - The animation direction.
	 * @param {string|number} args.speed - The animation speed, in seconds.
	 * @param {string}       args.offset - The animation offset.
	 * @return {Object} Animation attributes.
	 */
	fusionGetAnimations: function( args ) {
		var animationAttributes = {},
			directionSuffix,
			offset;

		args = _.defaults( args, {
			type: '',
			direction: 'left',
			speed: '0.1',
			offset: 'bottom-in-view'
		} );

		if ( args.type ) {

			animationAttributes.animation_class = 'fusion-animated';

			if ( 'static' === args.direction ) {
				args.direction = '';
			}

			if ( 'flash' !== args.type && 'shake' !== args.type && 'rubberBand' !== args.type && 'flipinx' !== args.type && 'flipiny' !== args.type && 'lightspeedin' !== args.type ) {
				directionSuffix = 'In' + args.direction.charAt( 0 ).toUpperCase() + args.direction.slice( 1 );
				args.type += directionSuffix;
			}

			animationAttributes[ 'data-animationType' ] = args.type;

			if ( args.speed ) {
				animationAttributes[ 'data-animationDuration' ] = args.speed;
			}
		}

		if ( args.offset ) {
			offset = args.offset;
			if ( 'top-into-view' === args.offset ) {
				offset = '100%';
			} else if ( 'top-mid-of-view' === args.offset ) {
				offset = '50%';
			}
			animationAttributes[ 'data-animationOffset' ] = offset;
		}

		return animationAttributes;

	},

	/**
	 * The FusionBuilder::font_awesome_name_handler() function from PHP, translated to JS.
	 *
	 * @since 2.0.0
	 * @param {string} icon - The icon we want.
	 * @return {string} - Returns the icon.
	 */
	fusionFontAwesome: function( icon ) {
		var oldIcons = {
				arrow: 'angle-right',
				asterik: 'asterisk',
				cross: 'times',
				'ban-circle': 'ban',
				'bar-chart': 'bar-chart-o',
				beaker: 'flask',
				bell: 'bell-o',
				'bell-alt': 'bell',
				'bitbucket-sign': 'bitbucket-square',
				'bookmark-empty': 'bookmark-o',
				building: 'building-o',
				'calendar-empty': 'calendar-o',
				'check-empty': 'square-o',
				'check-minus': 'minus-square-o',
				'check-sign': 'check-square',
				check: 'check-square-o',
				'chevron-sign-down': 'chevron-circle-down',
				'chevron-sign-left': 'chevron-circle-left',
				'chevron-sign-right': 'chevron-circle-right',
				'chevron-sign-up': 'chevron-circle-up',
				'circle-arrow-down': 'arrow-circle-down',
				'circle-arrow-left': 'arrow-circle-left',
				'circle-arrow-right': 'arrow-circle-right',
				'circle-arrow-up': 'arrow-circle-up',
				'circle-blank': 'circle-o',
				cny: 'rub',
				'collapse-alt': 'minus-square-o',
				'collapse-top': 'caret-square-o-up',
				collapse: 'caret-square-o-down',
				'comment-alt': 'comment-o',
				'comments-alt': 'comments-o',
				copy: 'files-o',
				cut: 'scissors',
				dashboard: 'tachometer',
				'double-angle-down': 'angle-double-down',
				'double-angle-left': 'angle-double-left',
				'double-angle-right': 'angle-double-right',
				'double-angle-up': 'angle-double-up',
				download: 'arrow-circle-o-down',
				'download-alt': 'download',
				'edit-sign': 'pencil-square',
				edit: 'pencil-square-o',
				'ellipsis-horizontal': 'ellipsis-h',
				'ellipsis-vertical': 'ellipsis-v',
				'envelope-alt': 'envelope-o',
				'exclamation-sign': 'exclamation-circle',
				'expand-alt': 'plus-square-o',
				expand: 'caret-square-o-right',
				'external-link-sign': 'external-link-square',
				'eye-close': 'eye-slash',
				'eye-open': 'eye',
				'facebook-sign': 'facebook-square',
				'facetime-video': 'video-camera',
				'file-alt': 'file-o',
				'file-text-alt': 'file-text-o',
				'flag-alt': 'flag-o',
				'folder-close-alt': 'folder-o',
				'folder-close': 'folder',
				'folder-open-alt': 'folder-open-o',
				food: 'cutlery',
				frown: 'frown-o',
				fullscreen: 'arrows-alt',
				'github-sign': 'github-square',
				group: 'users',
				'h-sign': 'h-square',
				'hand-down': 'hand-o-down',
				'hand-left': 'hand-o-left',
				'hand-right': 'hand-o-right',
				'hand-up': 'hand-o-up',
				hdd: 'hdd-o',
				'heart-empty': 'heart-o',
				hospital: 'hospital-o',
				'indent-left': 'outdent',
				'indent-right': 'indent',
				'info-sign': 'info-circle',
				keyboard: 'keyboard-o',
				legal: 'gavel',
				lemon: 'lemon-o',
				lightbulb: 'lightbulb-o',
				'linkedin-sign': 'linkedin-square',
				meh: 'meh-o',
				'microphone-off': 'microphone-slash',
				'minus-sign-alt': 'minus-square',
				'minus-sign': 'minus-circle',
				'mobile-phone': 'mobile',
				moon: 'moon-o',
				move: 'arrows',
				off: 'power-off',
				'ok-circle': 'check-circle-o',
				'ok-sign': 'check-circle',
				ok: 'check',
				'paper-clip': 'paperclip',
				paste: 'clipboard',
				'phone-sign': 'phone-square',
				picture: 'picture-o',
				'pinterest-sign': 'pinterest-square',
				'play-circle': 'play-circle-o',
				'play-sign': 'play-circle',
				'plus-sign-alt': 'plus-square',
				'plus-sign': 'plus-circle',
				pushpin: 'thumb-tack',
				'question-sign': 'question-circle',
				'remove-circle': 'times-circle-o',
				'remove-sign': 'times-circle',
				remove: 'times',
				reorder: 'bars',
				'resize-full': 'expand',
				'resize-horizontal': 'arrows-h',
				'resize-small': 'compress',
				'resize-vertical': 'arrows-v',
				'rss-sign': 'rss-square',
				save: 'floppy-o',
				screenshot: 'crosshairs',
				'share-alt': 'share',
				'share-sign': 'share-square',
				share: 'share-square-o',
				'sign-blank': 'square',
				signin: 'sign-in',
				signout: 'sign-out',
				smile: 'smile-o',
				'sort-by-alphabet-alt': 'sort-alpha-desc',
				'sort-by-alphabet': 'sort-alpha-asc',
				'sort-by-attributes-alt': 'sort-amount-desc',
				'sort-by-attributes': 'sort-amount-asc',
				'sort-by-order-alt': 'sort-numeric-desc',
				'sort-by-order': 'sort-numeric-asc',
				'sort-down': 'sort-asc',
				'sort-up': 'sort-desc',
				stackexchange: 'stack-overflow',
				'star-empty': 'star-o',
				'star-half-empty': 'star-half-o',
				sun: 'sun-o',
				'thumbs-down-alt': 'thumbs-o-down',
				'thumbs-up-alt': 'thumbs-o-up',
				time: 'clock-o',
				trash: 'trash-o',
				'tumblr-sign': 'tumblr-square',
				'twitter-sign': 'twitter-square',
				unlink: 'chain-broken',
				upload: 'arrow-circle-o-up',
				'upload-alt': 'upload',
				'warning-sign': 'exclamation-triangle',
				'xing-sign': 'xing-square',
				'youtube-sign': 'youtube-square',
				'zoom-in': 'search-plus',
				'zoom-out': 'search-minus'
			},
			faIcon = icon;

		if ( '' !== icon ) {

			// Custom icon is used so we need to remove our prefix.
			if ( 'fusion-prefix-' === icon.substr( 0, 14 ) ) {
				return icon.replace( 'fusion-prefix-', '' );
			}

			// FA icon, but we need to handle BC.
			if ( 'icon-' === icon.substr( 0, 5 ) || 'fa-' !== icon.substr( 0, 3 ) ) {
				icon = icon.replace( 'icon-', 'fa-' );

				if ( 'undefined' !== typeof oldIcons[ icon.replace( 'fa-', '' ) ] ) {
					faIcon = 'fa-' + oldIcons[ icon.replace( 'fa-', '' ) ];
				} else if ( 'fa-' !== icon.substr( 0, 3 ) ) {
					faIcon = 'fa-' + icon;
				}
			}

			if ( -1 === icon.trim().indexOf( ' ' ) ) {
				faIcon = 'fa ' + icon;
			}
		}

		return faIcon;
	},

	/**
	 * The FusionBuilder::validate_shortcode_attr_value() function from PHP, translated to JS.
	 *
	 * @since 2.0.0
	 * @param {string} value - The value.
	 * @param {string} acceptedUnit - The unit we're accepting.
	 * @param {boolean}   bcSupport - Should we add backwards-compatibility support?
	 * @return {string|false} - Returns the validated value.
	 */
	fusionValidateAttrValue: function( value, acceptedUnit, bcSupport ) {
		var validatedValue = '',
			numericValue,
			unit;

		bcSupport = 'undefined' !== typeof bcSupport ? bcSupport : true;
		value = String( value );
		if ( '' !== value ) {
			value        = value.trim();
			numericValue = parseFloat( value );
			unit         = value.replace( numericValue, '' );

			if ( 'undefined' === typeof acceptedUnit || '' === acceptedUnit ) {
				validatedValue = numericValue;

			} else if ( '' === unit ) {

				// Add unit if it's required.
				validatedValue = numericValue + acceptedUnit;
			} else if ( bcSupport || unit === acceptedUnit ) {

				// If unit was found use original value. BC support.
				validatedValue = value;
			} else {
				validatedValue = false;
			}
		}

		return validatedValue;
	},

	/**
	 * Clone of fusion_builder_get_video_provider.
	 *
	 * @since 2.0.0
	 * @param {string} videoString - The URL of the video.
	 * @return {Object} - Returns an object formatted {type: (string) The video-type, id: (string) The video ID }.
	 */
	fusionGetVideoProvider: function( videoString ) {

		var videoId,
			match;

		videoString = videoString.trim();

		// Check for YouTube.
		videoId = false;

		if ( match = videoString.match( /youtube\.com\/watch\?v=([^\&\?\/]+)/ ) ) { // eslint-disable-line no-cond-assign
			if ( 'undefined' !== typeof match[ 1 ] ) {
				videoId =  match[ 1 ];
			}
		} else if ( match = videoString.match( /youtube\.com\/embed\/([^\&\?\/]+)/ ) ) { // eslint-disable-line no-cond-assign
			if ( 'undefined' !== typeof match[ 1 ] ) {
				videoId =  match[ 1 ];
			}
		} else if ( match = videoString.match( /youtube\.com\/v\/([^\&\?\/]+)/ ) ) { // eslint-disable-line no-cond-assign
			if ( 'undefined' !== typeof match[ 1 ] ) {
				videoId =  match[ 1 ];
			}
		} else if ( match = videoString.match( /youtu\.be\/([^\&\?\/]+)/ ) ) { // eslint-disable-line no-cond-assign
			if ( 'undefined' !== typeof match[ 1 ] ) {
				videoId =  match[ 1 ];
			}
		}

		if ( false !== videoId ) {
			return {
				type: 'youtube',
				id: videoId
			};
		}

		// Check for Vimeo.
		if ( match = videoString.match( /vimeo\.com\/(\w*\/)*(\d+)/ ) ) { // eslint-disable-line no-cond-assign
			if ( 1 < match.length ) {
				return {
					type: 'vimeo',
					id: match[ match.length - 1 ]
				};
			}
		}
		if ( match = videoString.match( /^\d+$/ ) ) { // eslint-disable-line no-cond-assign
			if ( 'undefined' !== typeof match[ 0 ] ) {
				return {
					type: 'vimeo',
					id: match[ 0 ]
				};
			}
		}

		return {
			type: 'youtube',
			id: videoString
		};
	},

	/**
	 * JS clone of fusion_builder_check_value.
	 * If value is not in pixels or percent, appends 'px'.
	 *
	 * @since 2.0.0
	 * @param {string} value - The value.
	 * @return {string} - Returns the value.
	 */
	fusionCheckValue: function( value ) {
		if ( -1 === value.indexOf( '%' ) && -1 === value.indexOf( 'px' ) ) {
			value = value + 'px';
		}
		return value;
	},

	/**
	 * JS clone of get_value_with_unit.
	 *
	 * @param {string|number} value - The value.
	 * @param {string}           unit - The unit.
	 * @param {string}           unitHandling - Can be 'add'(default) or 'force_replace'.
	 * @return {string} - Returns the value with units.
	 */
	fusionGetValueWithUnit: function( value, unit, unitHandling ) {

		var rawValues,
			rawValue,
			values;

		unit         = 'undefined' !== typeof unit ? unit : 'px';
		unitHandling = 'undefined' !== typeof unitHandling ? unitHandling : 'add';

		rawValues = [];

		// Trim the value.
		value = 'undefined' === typeof value ? '' : value;
		value = value.trim();
		if ( -1 !== jQuery.inArray( value, [ 'auto', 'inherit', 'initial' ] ) ) {
			return value;
		}

		// Return empty if there are no numbers in the value.
		// Prevents some CSS errors.
		if ( ! ( /\d/ ).test( value ) ) {
			return;
		}

		// Explode if has multiple values.
		values = value.split( ' ' );
		if ( 1 < values.length ) {
			_.each( values, function( val ) {
				rawValue = parseFloat( val );

				if ( ! isNaN( rawValue ) ) {
					// Only == here deliberately.
					if ( val == rawValue ) {
						val = rawValue + unit;
					} else if ( 'force_replace' === unitHandling ) {
						val = rawValue + unit;
					}
				}
				rawValues.push( val );
			} );

			return rawValues.join( ' ' );

		}
		rawValue = parseFloat( value );

		// Only == here deliberately.
		if ( value == rawValue ) {
			return rawValue + unit;
		}
		if ( 'force_replace' === unitHandling ) {
			return rawValue + unit;
		}

		return value;
	},

	/**
	 * Returns a single side dimension.
	 *
	 * Copy of the PHP fusion_builder_single_dimension function.
	 *
	 * @param {Object} dimensions - The dimensions object{top:'',buttom:'',left:'',right:''}.
	 * @param {string} direction - Which one do we want? left/right/top/bottom.
	 * @return {string} - Returns a single dimension from the array.
	 */
	fusionSingleDimension: function( dimensions, direction ) {
		dimensions = dimensions.split( ' ' );

		if ( 4 === dimensions.length ) {
			if ( 'top' === direction ) {
				return dimensions[ 0 ];
			} else if ( 'right' === direction ) {
				return dimensions[ 1 ];
			} else if ( 'bottom' === direction ) {
				return dimensions[ 2 ];
			} else if ( 'left' === direction ) {
				return dimensions[ 3 ];
			}
		} else if ( 3 === dimensions.length ) {
			if ( 'top' === direction ) {
				return dimensions[ 0 ];
			} else if ( 'right' === direction || 'left' === direction ) {
				return dimensions[ 1 ];
			} else if ( 'bottom' === direction ) {
				return dimensions[ 2 ];
			}
		} else if ( 2 === dimensions.length ) {
			if ( 'top' === direction || 'bottom' === direction ) {
				return dimensions[ 0 ];
			} else if ( 'right' === direction || 'left' === direction ) {
				return dimensions[ 1 ];
			}
		}
		return dimensions[ 0 ];
	},

	/**
	 * Get the attributes for masonry.
	 *
	 * @since 2.0.0
	 * @param {Object}       data - The data.
	 * @param {string|number} data.blog_grid_column_spacing - Column spacing in pixels.
	 * @param {string}       data.element_orientation_class - The orientation class (fusion-element-portrain, fusion-element-landscape etc).
	 * @param {string}       data.timeline_color - The timeline color.
	 * @param {string}       data.masonry_attribute_style - Masonry styles.
	 * @return {Object} - Returns the masonry attributes.
	 */
	fusionGetMasonryAttribute: function( data ) {
		var masonryColumnOffset,
			masonryColumnSpacing,
			masonryAttributes = {};

		masonryColumnOffset = ' - ' + ( parseFloat( data.blog_grid_column_spacing ) / 2 ) + 'px';
		if ( 'string' === typeof data.element_orientation_class && -1 !== data.element_orientation_class.indexOf( 'fusion-element-portrait' ) ) {
			masonryColumnOffset = '';
		}

		masonryColumnSpacing = ( parseFloat( data.blog_grid_column_spacing ) ) + 'px';

		// Calculate the correct size of the image wrapper container, based on orientation and column spacing.
		if ( 'transparent' !== data.timeline_color && 0 !== jQuery.Color( data.timeline_color ).alpha() ) {

			masonryColumnOffset = ' - ' + ( parseFloat( data.blog_grid_column_spacing ) / 2 ) + 'px';
			if ( 'string' === typeof data.element_orientation_class && -1 !== data.element_orientation_class.indexOf( 'fusion-element-portrait' ) ) {
				masonryColumnOffset = ' + 4px';
			}

			masonryColumnSpacing = ( parseFloat( data.blog_grid_column_spacing ) - 2 ) + 'px';
			if ( 'string' === typeof data.element_orientation_class && -1 !== data.element_orientation_class.indexOf( 'fusion-element-landscape' ) ) {
				masonryColumnSpacing = ( parseFloat( data.blog_grid_column_spacing ) - 6 ) + 'px';
			}
		}

		// Calculate the correct size of the image wrapper container, based on orientation and column spacing.
		masonryAttributes[ 'class' ] = 'fusion-masonry-element-container';
		masonryAttributes.style = data.masonry_attribute_style + 'padding-top:calc((100% + ' + masonryColumnSpacing + ') * ' + data.element_base_padding + masonryColumnOffset + ');';

		return masonryAttributes;
	},

	/**
	 * Combination of first featured image and rollover.
	 *
	 * @since 2.0.0
	 * @param {Object}         data - The data.
	 * @param {string}         data.layout - The layout.
	 * @param {string}         data.masonry_data - The masonry data.
	 * @param {string|boolean} data.enable_rollover - Should we enable the rollover?
	 * @param {string}         data.display_rollover - Should we display the rollover? (yes|no|force_yes).
	 * @param {Object}         data.featured_images - The featured images.
	 * @param {string}         data.image_rollover_icons - no|zoom|link|linkzoom.
	 * @param {string}         data.post_type - The post-type.
	 * @param {string|number}  data.post_id - The post-ID.
	 * @param {string}         data.icon_permalink - URL.
	 * @param {string}         data.link_target - Leave empty or use target="_blank".
	 * @param {string}         data.icon_permalink_title - The icon permalink title.
	 * @param {string}         data.full_image - URL.
	 * @param {string}         data.data_rel - Used in data-rel="".
	 * @param {string}         data.data_title - Used in data-title="".
	 * @param {string}         data.data_caption - Used in data-caption="".
	 * @param {string}         data.lightbox_content - The contents of the lightbox.
	 * @param {string|boolean} data.display_post_title - Should we display the post-title?
	 * @param {string}         data.permalink - URL.
	 * @param {string}         data.title - The title.
	 * @param {string|boolean} data.display_post_categories - Should we display the post categories?
	 * @param {string}         data.terms - The post category terms (HTML).
	 * @param {boolean}        data.display_woo_rating - SHould we display Woo rating?
	 * @param {string}         data.rating - The rating (HTML).
	 * @param {boolean}        data.display_woo_price - Should we display Woo Prices?
	 * @param {string}         data.price - The price (HTML).
	 * @param {boolean}        data.display_woo_buttons - Should we display the Woo buttons?
	 * @param {string}         data.buttons - The buttons (HTML).
	 * @return {string} - Returns the template.
	 */
	fusionFeaturedImage: function( data ) {
		var featuredImageTemplate = FusionPageBuilder.template( jQuery( '#tmpl-featured-image' ).html() ),
			attributes = {};

		if ( 'object' !== typeof data || 'undefined' === typeof data.featured_images ) {
			return '';
		}
		attributes.data = data;
		return featuredImageTemplate( attributes );
	},

	/**
	 * Get element orientation class based on image dimensions and ratio and widthDouble params.
	 *
	 * @since 2.0.0
	 * @param {Object} attachment - Image object.
	 * @param {number} attachment.imageWidth - Image width.
	 * @param {number} attachment.imageHeight - Image height.
	 * @param {number} ratio - Height / Width ratio. Portrait images have larger height / width ratio.
	 * @param {number} widthDouble - Wider images are considered as 2x2.
	 * @return {string} - Returns the element class.
	 */
	fusionGetElementOrientationClass: function( attachment, ratio, widthDouble ) {
		var elementClass = 'fusion-element-grid',
			fallbackRatio = 0.8,
			lowerLimit,
			upperLimit;

		if ( 'undefined' !== typeof attachment.imageWidth && 'undefined' !== typeof attachment.imageHeight ) {

			// Fallback to legacy calcs of Avada 5.4.2 or earlier.
			if ( '1.0' === ratio ) {
				lowerLimit = ( fallbackRatio / 2 ) + ( fallbackRatio / 4 );
				upperLimit = ( fallbackRatio * 2 ) - ( fallbackRatio / 2 );

				if ( lowerLimit > attachment.imageHeight / attachment.imageWidth ) {

					// Landscape image.
					elementClass = 'fusion-element-landscape';
				} else if ( upperLimit < attachment.imageHeight / attachment.imageWidth ) {

					// Portrait image.
					elementClass = 'fusion-element-portrait';
				} else if ( attachment.imageWidth > widthDouble ) {

					// 2x2 image.
					elementClass = 'fusion-element-landscape fusion-element-portrait';
				}
			} else if ( ratio < attachment.imageWidth / attachment.imageHeight ) {

				// Landscape image.
				elementClass = 'fusion-element-landscape';

			} else if ( ratio < attachment.imageHeight / attachment.imageWidth ) {

				// Portrait image.
				elementClass = 'fusion-element-portrait';
			} else if ( attachment.imageWidth > widthDouble ) {

				// 2x2 image.
				elementClass = 'fusion-element-landscape fusion-element-portrait';
			}
		}

		return elementClass;
	},

	/**
	 * Get base element padding based on orientation CSS class.
	 *
	 * @since 2.0.0
	 * @param {string} elementOrientationClass - CSS class
	 * @return {number} - Returns the padding.
	 */
	fusionGetElementBasePadding: function( elementOrientationClass ) {
		var fusionElementGridPadding = 0.8,
			masonryElementPadding = {
				'fusion-element-grid': fusionElementGridPadding,
				'fusion-element-landscape': fusionElementGridPadding / 2,
				'fusion-element-portrait': fusionElementGridPadding * 2
			};

		if ( 'undefined' !== typeof masonryElementPadding[ elementOrientationClass ] ) {
			fusionElementGridPadding = masonryElementPadding[ elementOrientationClass ];
		}

		return fusionElementGridPadding;
	},

	/**
	 * JS copy of fusion_builder_render_post_metadata.
	 *
	 * @since 2.0.0
	 * @param {string}         layout - The layout.
	 * @param {Object}         settings - The settings.
	 * @param {boolean|string} settings.post_meta - Should we display the post-meta?
	 * @param {boolean|string} settings.post_meta_author - Should we display the author?
	 * @param {boolean|string} settings.post_meta_date - Should we display the date?
	 * @param {boolean|string} settings.post_meta_cats - Should we display the categories?
	 * @param {boolean|string} settings.post_meta_tags - Should we display the tags?
	 * @param {boolean|string} settings.post_meta_comments - Should we display comments?
	 * @param {boolean|string} settings.disable_date_rich_snippet_pages - Should we disable the date rich snippet?
	 * @param {Object}         data - The data.
	 * @param {string}         data.post_meta - yes|no.
	 * @param {string}         data.author_post_link - The link to the post-author (HTML, not just URL).
	 * @param {string}         data.formatted_date - Formatted date (HTML).
	 * @param {string}         data.categories - The categories (HTML).
	 * @param {string}         data.tags - The Tags (HTML)
	 * @param {string}         data.comments - The comments (HTML)
	 * @param {string}         data.disable_date_rich_snippet_pages - Disable date rich snippets?
	 * @return {string} - Returns HTML.
	 */
	fusionRenderPostMetadata: function( layout, settings, data ) {

		var metadata = '',
			author   = '',
			date     = '',
			output   = '',
			dateMarkup;

		// Check if meta data is enabled.
		if ( 'undefined' === typeof data ) {
			return;
		}

		if ( ( settings.post_meta && 'no' !== data.post_meta ) || ( ! settings.post_meta && 'yes' === data.post_meta ) ) {

			// For alternate, grid and timeline layouts return empty single-line-meta if all meta data for that position is disabled.
			if ( -1 !== jQuery.inArray( layout, [ 'alternate', 'grid_timeline' ] ) && ! settings.post_meta_author && ! settings.post_meta_date && ! settings.post_meta_cats && ! settings.post_meta_tags && ! settings.post_meta_comments ) {
				return '';
			}

			// Render author meta data.
			if ( settings.post_meta_author ) {

				// Check if rich snippets are enabled.
				if ( ! settings.disable_date_rich_snippet_pages ) {
					metadata += 'By <span>' + data.author_post_link + '</span>';
				} else {
					metadata += 'By <span class="vcard"><span class="fn">' + data.author_post_link + '</span></span>';
				}
				metadata += '<span class="fusion-inline-sep">|</span>';
			}

			// Render the updated meta data or at least the rich snippet if enabled.
			if ( settings.post_meta_date ) {
				metadata  += _.fusionRenderRichSnippets( data, false, false, true );
				dateMarkup = '<span>' + data.formatted_date + '</span><span class="fusion-inline-sep">|</span>';
				metadata  += dateMarkup;
			}

			// Render rest of meta data.
			// Render categories.
			if ( settings.post_meta_cats ) {

				if ( data.categories ) {
					metadata += ( settings.post_meta_tags ) ? 'Categories: ' + data.categories : data.categories;
					metadata += '<span class="fusion-inline-sep">|</span>';
				}
			}

			// Render tags.
			if ( settings.post_meta_tags ) {

				if ( data.tags ) {
					metadata += '<span class="meta-tags">' + window.fusionBuilderText.tags.replace( '%s', data.tags ) + '</span><span class="fusion-inline-sep">|</span>';
				}
			}

			// Render comments.
			if ( settings.post_meta_comments && 'grid_timeline' !== layout ) {
				metadata += '<span class="fusion-comments">' + data.comments + '</span>';
			}

			// Render the HTML wrappers for the different layouts.
			if ( metadata ) {
				metadata = author + date + metadata;

				if ( 'single' === layout ) {
					output += '<div class="fusion-meta-info"><div class="fusion-meta-info-wrapper">' + metadata + '</div></div>';
				} else if ( -1 !== jQuery.inArray( layout, [ 'alternate', 'grid_timeline' ] ) ) {
					output += '<p class="fusion-single-line-meta">' + metadata + '</p>';
				} else if ( 'recent_posts' === layout ) {
					output += metadata;
				} else {
					output += '<div class="fusion-alignleft">' + metadata + '</div>';
				}
			} else {
				output += author + date;
			}
		} else if ( data.disable_date_rich_snippet_pages ) {

			// Render author and updated rich snippets for grid and timeline layouts.
			output += _.fusionRenderRichSnippets( data, false );
		}

		return output;
	},

	/**
	 * JS Copy of fusion_builder_render_rich_snippets_for_pages.
	 *
	 * @since 2.0.0
	 * @param {Object}  data - The data.
	 * @param {boolean} data.disable_date_rich_snippet_pages Should we display the rich snippets?
	 * @param {string}  data.title - The title.
	 * @param {string}  data.the_author_posts_link The link to the author (HTML, not just the URL).
	 * @param {string}  data.get_the_modified_time - The modified timestamp.
	 * @return {string} - Returns the output.
	 */
	fusionRenderRichSnippets: function( data ) {
		var output = '';

		if ( 'undefined' === typeof data ) {
			return;
		}
		if ( data.disable_date_rich_snippet_pages ) {
			output = '';
		}
		return output;
	},

	/**
	 * JS copy of new-slideshow-blog-shortcode.
	 *
	 * @since 2.0.0
	 * @param {Object} data - The data.
	 * @param {string} data.layout - The layout.
	 * @param {string} data.featured_image_width - The featured image width.
	 * @param {string} data.id - The ID.
	 * @param {string} data.featured_image_height - The featured image height.
	 * @param {string} data.thumbnail - The thumbnail.
	 * @param {string} data.video - The video
	 * @param {Object} data.image_data - The image data.
	 * @param {Object} data.multiple_featured - Multiple featured images data.
	 * @param {string} data.permalink - The permalink (URL).
	 * @param {string} data.title - The title.
	 * @param {string} data.image_size - The image size.
	 * @return {string}
	 */
	fusionGetBlogSlideshow: function( data ) {
		var slideshowTemplate = FusionPageBuilder.template( jQuery( '#tmpl-new-slideshow-blog-shortcode' ).html() ),
			attributes        = {};

		if ( 'object' !== typeof data ) {
			return '';
		}
		attributes.data = data;
		return slideshowTemplate( attributes );
	},

	/**
	 * Ability to change length of content and display correct contents.
	 *
	 * @since 2.0.0
	 * @param {Object}  data - The data.
	 * @param {string}  data.read_more - The read more text.
	 * @param {string}  data.full_content - The full content.
	 * @param {string}  data.excerpt - The excerpt.
	 * @param {string}  data.excerpt_stripped - Stripped excerpt.
	 * @param {string}  data.excerpt_base - Defaults to 'characters'.
	 * @param {string}  excerpt - Do we want excerpt (yes/no)?
	 * @param {number} excerptLength - How long?
	 * @param {boolean} stripHtml - Should we strip HTML?
	 * @return {string}
	 */
	fusionGetFixedContent: function( data, excerpt, excerptLength, stripHtml ) {
		var content,
			readMoreContent = '';

		excerpt        = 'undefined' !== typeof excerpt ? excerpt : 'no';
		excerptLength  = 'undefined' !== typeof excerptLength ? excerptLength : 55;
		stripHtml      = 'undefined' !== typeof stripHtml ? stripHtml : false;
		stripHtml      = ( 'yes' === stripHtml || stripHtml || '1' == stripHtml );
		data.read_more = data.hasOwnProperty( 'read_more' ) ? data.read_more : '';

		// Return full contents.
		if ( 'no' === excerpt ) {
			return data.full_content;
		}

		// Set correct stripped data.
		content = ( stripHtml ) ? data.excerpt_stripped : data.excerpt;

		// It has a read more, remove it.
		content = content
			.replace( /\[/g, '&#91;' )
			.replace( /\]/g, '&#93;' )
			.replace( /\.\.\./g, '&#8230;' );

		readMoreContent = ' ' + data.read_more
			.replace( /\[/g, '&#91;' )
			.replace( /\]/g, '&#93;' )
			.replace( /\.\.\./g, '&#8230;' )
			.trim();

		if ( -1 !== content.indexOf( readMoreContent ) ) {
			content  = content.replace( readMoreContent, '' );
		}

		if ( 'characters' === data.excerpt_base.toLowerCase() ) {
			if ( excerptLength < content.length ) {
				content = content.substring( 0, excerptLength );
			}
		} else {
			content = content.split( ' ' ).splice( 0, excerptLength ).join( ' ' );
		}

		// Add read more.
		content += readMoreContent;

		return _.fusionFixHtml( content );
	},

	/**
	 * Helper method used in getFixedContent.
	 *
	 * @since 2.0.0
	 * @param {string} html - The html string.
	 * @return {string}
	 */
	fusionFixHtml: function( html ) {
		var div = document.createElement( 'div' );
		div.innerHTML = html;
		return ( div.innerHTML );
	},

	/**
	 * Capitalize the 1st letter.
	 *
	 * @since 2.0.0
	 * @param {string} string - The string we want to modify.
	 * @return {string}
	 */
	fusionUcFirst: function( string ) {
		return string.charAt( 0 ).toUpperCase() + string.slice( 1 );
	},

	/**
	 * JS port of PHP's rawurlencode function.
	 *
	 * @since 2.0.0
	 * @param {string} string - The URL.
	 * @return {string}
	 */
	fusionRawUrlEncode: function( string ) {
		string = ( string + '' );

		return encodeURIComponent( string )
			.replace( /!/g, '%21' )
			.replace( /'/g, '%27' )
			.replace( /\(/g, '%28' )
			.replace( /\)/g, '%29' )
			.replace( /\*/g, '%2A' );
	},

	/**
	 * Auto calculate accent color.
	 * copy of fusion_auto_calculate_accent_color from PHP.
	 *
	 * @since 2.0.0
	 * @param {string} color - The color.
	 * @return {string}
	 */
	fusionAutoCalculateAccentColor: function( color ) {
		var colorObj  = jQuery.Color( color ),
			lightness = parseInt( colorObj.lightness() * 100, 10 );

		if ( 0 < lightness ) { // Not black.
			if ( 50 <= lightness ) {
				return colorObj.lightness( lightness / 200 ).toRgbaString();
			}
			return colorObj.lightness( lightness / 50 ).toRgbaString();
		}
		return colorObj.lightness( 70 ).toRgbaString();
	},

	/**
	 * JS copy of fusion_builder_build_social_links.
	 *
	 * @since 2.0.0
	 * @param {Array|Object} socialNetworks - The social networks array.
	 * @param {string}       functionName - Callable function-name.
	 * @param {Object}       params - The parameters.
	 * @param {number}      i - Not used?
	 * @return {string}
	 */
	fusionBuildSocialLinks: function( socialNetworks, functionName, params, i ) {

		var useBrandColors    = false,
			icons             = '',
			shortcodeDefaults = {},
			boxColors,
			iconColors,
			numOfIconColors,
			numOfBoxColors,
			socialNetworksCount,
			k = 0;

		socialNetworks = ! _.isUndefined( socialNetworks ) ? socialNetworks : '';
		i              = ! _.isUndefined( i ) ? i : 0;

		if ( ! _.isUndefined( params.social_icon_boxed ) ) {
			params.icons_boxed = params.social_icon_boxed;
		}

		if ( '' != socialNetworks && Array.isArray( socialNetworks ) ) {

			// Add compatibility for different key names in shortcodes.
			_.each( params, function( value, key ) {
				key = ( 'social_icon_boxed'        === key ) ? 'icons_boxed' : key;
				key = ( 'social_icon_colors'       === key ) ? 'icon_colors' : key;
				key = ( 'social_icon_boxed_colors' === key ) ? 'box_colors'  : key;
				key = ( 'social_icon_color_type'   === key ) ? 'color_type'  : key;

				shortcodeDefaults[ key ] = value;
			} );

			// Check for icon color type.
			if ( 'brand' === shortcodeDefaults.color_type ) {
				useBrandColors = true;

				boxColors = _.fusionSocialIcons( true, true );

				// Backwards compatibility for old social network names.
				boxColors.mail = {
					label: 'Email Address',
					color: '#000000'
				};
				iconColors = {};

			} else {

				// Custom social icon colors.
				iconColors = ( 'undefined' !== typeof shortcodeDefaults.icon_colors ) ? shortcodeDefaults.icon_colors.split( '|' ) : '';
				boxColors  = ( 'undefined' !== typeof shortcodeDefaults.box_colors ) ? shortcodeDefaults.box_colors.split( '|' ) : '';

				numOfIconColors = iconColors.length;
				numOfBoxColors  = boxColors.length;

				socialNetworksCount = socialNetworks.length;

				for ( k = 0; k < socialNetworksCount; k++ ) {
					if ( 1 === numOfIconColors ) {
						iconColors[ k ] = iconColors[ 0 ];
					}
					if ( 1 === numOfBoxColors ) {
						boxColors[ k ] = boxColors[ 0 ];
					}
				}
			}

			// Process social networks.
			_.each( socialNetworks, function( value ) {

				_.each( value, function( link, network ) {
					var iconOptions;

					if ( 'custom' === network && link ) {

						_.each( link, function( url, customKey ) {
							var customIconBoxColor = '',
								socialMediaIcons,
								width,
								height;

							if ( 'yes' === params.icons_boxed ) {

								customIconBoxColor = i < boxColors.length ? boxColors[ i ] : '';
								if ( true === useBrandColors ) {
									customIconBoxColor = ( boxColors[ network ].color ) ? boxColors[ network ].color : '';
								}
							}

							socialMediaIcons = params.social_media_icons;

							if ( ! _.isObject( socialMediaIcons ) ) {
								socialMediaIcons = {};
							}
							if ( _.isUndefined( socialMediaIcons.custom_title ) ) {
								socialMediaIcons.custom_title = {};
							}
							if ( _.isUndefined( socialMediaIcons.custom_source ) ) {
								socialMediaIcons.custom_source = {};
							}
							if ( _.isUndefined( socialMediaIcons.custom_title[ customKey ] ) ) {
								socialMediaIcons.custom_title[ customKey ] = '';
							}
							if ( _.isUndefined( socialMediaIcons.custom_source[ customKey ] ) ) {
								socialMediaIcons.custom_source[ customKey ] = '';
							}

							iconOptions = {
								social_network: socialMediaIcons.custom_title[ customKey ],
								social_link: url,
								icon_color: i < iconColors.length ? iconColors[ i ] : '',
								box_color: customIconBoxColor
							};
							if ( _.isFunction( functionName ) ) {
								iconOptions = functionName( iconOptions, params );
							}
							icons += '<a ' + _.fusionGetAttributes( iconOptions ) + '>';
							icons += '<img';

							if ( ! _.isUndefined( socialMediaIcons.custom_source[ customKey ].url ) ) {
								icons += ' src="' + socialMediaIcons.custom_source[ customKey ].url + '"';
							}
							if ( ! _.isUndefined( socialMediaIcons.custom_title[ customKey ] ) && '' != socialMediaIcons.custom_title[ customKey ] ) {
								icons += ' alt="' + socialMediaIcons.custom_title[ customKey ] + '"';
							}
							if ( ! _.isUndefined( socialMediaIcons.custom_source[ customKey ].width ) && socialMediaIcons.custom_source[ customKey ].width ) {
								width = parseInt( socialMediaIcons.custom_source[ customKey ].width, 10 );
								icons += ' width="' + width + '"';
							}
							if ( 'undefined' !== socialMediaIcons.custom_source[ customKey ].height && socialMediaIcons.custom_source[ customKey ].height ) {
								height = parseInt( socialMediaIcons.custom_source[ customKey ].height, 10 );
								icons += ' height="' + height + '"';
							}
							icons += ' /></a>';
						} );
					} else {
						if ( true == useBrandColors ) {
							iconOptions = {
								social_network: network,
								social_link: link,
								icon_color: ( 'yes' === params.icons_boxed ) ? '#ffffff' : boxColors[ network ].color,
								box_color: ( 'yes' === params.icons_boxed ) ? boxColors[ network ].color : ''
							};

						} else {
							iconOptions = {
								social_network: network,
								social_link: link,
								icon_color: i < iconColors.length ? iconColors[ i ] : '',
								box_color: i < boxColors.length ? boxColors[ i ] : ''
							};
						}
						if ( _.isFunction( functionName ) ) {
							iconOptions = functionName( iconOptions, params );
						}
						icons += '<a ' + _.fusionGetAttributes( iconOptions ) + '></a>';
					}
					i++;
				} );
			} );
		}

		return icons;
	},

	/**
	 * JS copy of Fusion_Data::fusion_social_icons
	 *
	 * @since 2.0.0
	 * @param {boolean} custom - Do we want the custom network?
	 * @param {boolean} colors - Do we want the colors?
	 * @return {Object}
	 */
	fusionSocialIcons: function( custom, colors ) {

		var networks,
			simpleNetworks;

		custom = ! _.isUndefined( custom ) ? custom : true;
		colors = ! _.isUndefined( colors ) ? colors : false;

		networks = {
			blogger: {
				label: 'Blogger',
				color: '#f57d00'
			},
			deviantart: {
				label: 'Deviantart',
				color: '#4dc47d'
			},
			discord: {
				label: 'Discord',
				color: '#26262B'
			},
			digg: {
				label: 'Digg',
				color: '#000000'
			},
			dribbble: {
				label: 'Dribbble',
				color: '#ea4c89'
			},
			dropbox: {
				label: 'Dropbox',
				color: '#007ee5'
			},
			facebook: {
				label: 'Facebook',
				color: '#3b5998'
			},
			flickr: {
				label: 'Flickr',
				color: '#0063dc'
			},
			forrst: {
				label: 'Forrst',
				color: '#5b9a68'
			},
			instagram: {
				label: 'Instagram',
				color: '#3f729b'
			},
			linkedin: {
				label: 'LinkedIn',
				color: '#0077b5'
			},
			myspace: {
				label: 'Myspace',
				color: '#000000'
			},
			paypal: {
				label: 'Paypal',
				color: '#003087'
			},
			pinterest: {
				label: 'Pinterest',
				color: '#bd081c'
			},
			reddit: {
				label: 'Reddit',
				color: '#ff4500'
			},
			rss: {
				label: 'RSS',
				color: '#f26522'
			},
			skype: {
				label: 'Skype',
				color: '#00aff0'
			},
			soundcloud: {
				label: 'Soundcloud',
				color: '#ff8800'
			},
			spotify: {
				label: 'Spotify',
				color: '#2ebd59'
			},
			tiktok: {
				label: 'Tiktok',
				color: '#010101'
			},
			tumblr: {
				label: 'Tumblr',
				color: '#35465c'
			},
			twitch: {
				label: 'Twitch',
				color: '#6441a5'
			},
			twitter: {
				label: 'Twitter',
				color: '#55acee'
			},
			vimeo: {
				label: 'Vimeo',
				color: '#1ab7ea'
			},
			vk: {
				label: 'VK',
				color: '#45668e'
			},
			wechat: {
				label: 'WeChat',
				color: '#7bb22e'
			},
			whatsapp: {
				label: 'WhatsApp',
				color: '#77e878'
			},
			xing: {
				label: 'Xing',
				color: '#026466'
			},
			yahoo: {
				label: 'Yahoo',
				color: '#410093'
			},
			yelp: {
				label: 'Yelp',
				color: '#af0606'
			},
			youtube: {
				label: 'Youtube',
				color: '#cd201f'
			},
			email: {
				label: 'Email Address',
				color: '#000000'
			},
			phone: {
				label: 'Phone',
				color: '#000000'
			}
		};

		// Add a "custom" entry.
		if ( custom ) {
			networks.custom = {
				label: 'Custom',
				color: ''
			};
		}

		if ( ! colors ) {
			simpleNetworks = {};
			_.each( networks, function( networkArgs ) {
				simpleNetworks.network_id = networkArgs.label;
			} );
			networks = simpleNetworks;
		}

		return networks;

	},

	/**
	 * JS copy of fusion_builder_sort_social_networks.
	 *
	 * @param {Object} socialNetworksOriginal - The original object.
	 * @param {Object} params - Any parameters we want to pass.
	 * @return {Object}
	 */
	fusionSortSocialNetworks: function( socialNetworksOriginal, params ) {

		var socialNetworks = [],
			iconOrder      = '',
			newNetwork,
			newCustom;

		// Get social networks order from  optionthemes.
		if ( params.social_media_icons_icon && Array.isArray( params.social_media_icons_icon ) ) {
			iconOrder = params.social_media_icons_icon.join( '|' );
		}

		if ( ! Array.isArray( iconOrder ) ) {
			iconOrder = iconOrder.split( '|' );
		}

		if ( Array.isArray( iconOrder ) ) {

			// First put the icons that exist in the Global Options,
			// and order them using tha same order as in Global Options.
			_.each( iconOrder, function( value, key ) {
				var newKey;

				// Backwards compatibility for old social network names.
				newKey = ( 'email' === value ) ? 'mail' : value;

				// Check if social network from TO exists in element.
				if ( ! _.isUndefined( socialNetworksOriginal[ value ] ) ) {
					newNetwork = {};
					if ( 'custom' === value ) {
						if ( socialNetworksOriginal[ value ] ) {
							newNetwork[ key ]  = socialNetworksOriginal[ value ][ key ];
							newCustom          = {};
							newCustom[ value ] = newNetwork;
							socialNetworks.push( newCustom );
						}
					} else {
						newNetwork[ newKey ] = socialNetworksOriginal[ value ];
						socialNetworks.push( newNetwork );
						delete socialNetworksOriginal[ value ];
					}
				}
			} );

			// Put any remaining icons after the ones from the Global Options.
			_.each( socialNetworksOriginal, function( networkurl, name ) {
				if ( 'custom' !== name ) {
					newNetwork         = {};
					newNetwork[ name ] = networkurl;
					socialNetworks.push( newNetwork );
				}
			} );
		} else {
			console.warn( 'OUT' );
		}

		return socialNetworks;
	},

	/**
	 * JS copy of fusion_builder_get_social_networks.
	 * Gets the social networks.
	 *
	 * @since 2.0.0
	 * @param {Object} params - The parameters.
	 * @return {Object}
	 */
	fusionGetSocialNetworks: function( params ) {

		var socialLinksArray = {},
			socialLinks      = {
				facebook: 'facebook',
				tiktok: 'tiktok',
				twitch: 'twitch',
				twitter: 'twitter',
				instagram: 'instagram',
				linkedin: 'linkedin',
				discord: 'discord',
				dribbble: 'dribbble',
				rss: 'rss',
				youtube: 'youtube',
				pinterest: 'pinterest',
				flickr: 'flickr',
				vimeo: 'vimeo',
				tumblr: 'tumblr',
				digg: 'digg',
				blogger: 'blogger',
				skype: 'skype',
				myspace: 'myspace',
				deviantart: 'deviantart',
				yahoo: 'yahoo',
				reddit: 'reddit',
				forrst: 'forrst',
				paypal: 'paypal',
				dropbox: 'dropbox',
				soundcloud: 'soundcloud',
				vk: 'vk',
				wechat: 'wechat',
				whatsapp: 'whatsapp',
				xing: 'xing',
				yelp: 'yelp',
				spotify: 'spotify',
				email: 'mail',
				phone: 'phone'
			};

		_.each( socialLinks, function( val, key ) {
			if ( 'undefined' !== typeof params[ key ] && '' !== params[ key ] ) {
				socialLinksArray[ val ] = params[ key ];
			}
		} );

		if ( params.show_custom && 'yes' === params.show_custom ) {
			socialLinksArray.custom = {};

			if ( Array.isArray( params.social_media_icons_icon ) ) {
				_.each( params.social_media_icons_icon, function( icon, key ) {

					if ( 'custom' === icon && Array.isArray( params.social_media_icons_url ) && ! _.isUndefined( params.social_media_icons_url[ key ] ) && '' !== params.social_media_icons_url[ key ] ) {

						// Check if there is a default set for this, if so use that rather than TO link.
						if ( params[ 'custom_' + key ] && '' !== params[ 'custom_' + key ] ) {
							socialLinksArray.custom[ key ] = params[ 'custom_' + key ];
						} else {
							socialLinksArray.custom[ key ] = params.social_media_icons_url[ key ];
						}
					}
				} );
			}
		}

		return socialLinksArray;
	},

	// WIP: If padding (combined all 4) is not set in params, then use individual variables.
	fusionGetPadding: function( values ) {
		values.padding_top    = 'undefined' !== typeof values.padding_top ? _.fusionGetValueWithUnit( values.padding_top ) : '0px';
		values.padding_right  = 'undefined' !== typeof values.padding_right ? _.fusionGetValueWithUnit( values.padding_right ) : '0px';
		values.padding_bottom = 'undefined' !== typeof values.padding_bottom ? _.fusionGetValueWithUnit( values.padding_bottom ) : '0px';
		values.padding_left   = 'undefined' !== typeof values.padding_left ? _.fusionGetValueWithUnit( values.padding_left ) : '0px';
		values.padding = values.padding_top + ' ' + values.padding_right + ' ' + values.padding_bottom + ' ' + values.padding_left;

		return values;
	},

	fusionGetMargin: function( values ) {
		if ( '' !== values.margin_bottom ) {
			values.margin_bottom = _.fusionGetValueWithUnit( values.margin_bottom );
		}
		if ( '' !== values.margin_top ) {
			values.margin_top = _.fusionGetValueWithUnit( values.margin_top );
		}

		return values;
	},

	fusionAnimations: function( values, attributes, parent ) {
		var animations = false;

		if ( values && 'undefined' !== typeof values.animation_type && '' !== values.animation_type ) {
			animations = _.fusionGetAnimations( {
				type: values.animation_type,
				direction: values.animation_direction,
				speed: values.animation_speed,
				offset: values.animation_offset
			} );

			attributes = jQuery.extend( attributes, animations );

			// Class to mark as editor.
			if ( 'undefined' === typeof parent || ! parent ) {
				if ( 'undefined' !== typeof attributes[ 'class' ] ) {
					attributes[ 'class' ] += ' ' + attributes.animation_class;
				} else {
					attributes[ 'class' ] = attributes.animation_class;
				}
			}

			delete attributes.animation_class;
		}

		return attributes;
	},

	fusionPagination: function( maxPages, currentPage, range, pagination, globalPagination, globalStartEndRange ) {
		var paginationCode = '',
			i,
			globalStartRange,
			globalEndRange,
			start,
			end;

		globalStartEndRange = ( 'undefined' !== typeof globalStartEndRange ) ? parseInt( globalStartEndRange, 10 ) : 2;
		currentPage         = ( 'undefined' !== typeof currentPage ) ? parseInt( currentPage, 10 ) : 1;
		range               = parseInt( range, 10 );
		maxPages            = parseInt( maxPages, 10 );

		globalStartRange = globalStartEndRange;
		globalEndRange   = globalStartEndRange;

		if ( 1 !== maxPages ) {

			if ( ( 'pagination' !== pagination && 'pagination' !== globalPagination.toLowerCase() ) ) {
				paginationCode += '<div class="fusion-infinite-scroll-trigger"></div>';
				paginationCode += '<div class="pagination infinite-scroll clearfix">';
			} else {
				paginationCode += '<div class="pagination clearfix">';
			}

			start = currentPage - range;
			end   = currentPage + range;
			if ( 0 >= start ) {
				start = ( 0 < currentPage - 1 ) ? currentPage - 1 : 1;
			}

			if ( maxPages < end ) {
				end = maxPages;
			}

			if ( 'pagination' === pagination ) {
				if ( 1 < currentPage ) {
					paginationCode += '<a class="pagination-prev" href="#"><span class="page-prev"></span><span class="page-text">Previous</span></a>';

					if ( 0 < globalStartRange ) {
						if ( globalStartRange >= start ) {
							globalStartRange = start - 1;
						}

						for ( i = 1; i <= globalStartRange; i++ ) {
							paginationCode += '<a href="#" class="inactive">' + i + '</a>';
						}

						if ( 0 < globalStartRange && globalStartRange < start - 1 ) {
							paginationCode += '<span class="pagination-dots paginations-dots-start">&middot;&middot;&middot;</span>';
						}
					}
				}

				for ( i = start; i <= end; i++ ) {
					if ( currentPage == i ) {
						paginationCode += '<span class="current">' + i + '</span>';
					} else {
						paginationCode += '<a href="#" class="inactive">' + i + '</a>';
					}
				}

				if ( currentPage < maxPages ) {

					if ( 0 < globalEndRange ) {

						if ( maxPages - globalEndRange <= end ) {
							globalEndRange = maxPages - end;
						}

						globalEndRange--;

						if ( end + 1 < maxPages - globalEndRange ) {
							paginationCode += '<span class="pagination-dots paginations-dots-end">&middot;&middot;&middot;</span>';
						}

						for ( i = maxPages - globalEndRange; i <= maxPages; i++ ) {
							paginationCode += '<a href="#" class="inactive">' + i + '</a>';
						}
					}

					paginationCode += '<a class="pagination-next" href="#"><span class="page-text">Next</span><span class="page-next"></span></a>';
				}
			}

			paginationCode += '</div>';
			paginationCode += '<div class="fusion-clearfix"></div>';
		}

		return paginationCode;
	},

	fusionInlineEditor: function( args, attributes ) {
		var defaults = {
				cid: false,
				param: 'element_content',
				encoding: false,
				'disable-return': false,
				'disable-extra-spaces': false,
				toolbar: 'full',
				overrides: false
			},
			config = _.extend( defaults, args ),
			view   = FusionPageBuilderViewManager.getView( config.cid );

		// If cid is not a number then this is a nested render and do not use live editor.
		if ( 'number' !== typeof config.cid ) {
			return attributes;
		}

		attributes[ 'data-inline-parent-cid' ] = config.cid;

		// Class to mark as editor.
		if ( 'undefined' !== typeof attributes[ 'class' ] ) {
			attributes[ 'class' ] += ' fusion-live-editable';
		} else {
			attributes[ 'class' ] = 'fusion-live-editable';
		}

		if ( config[ 'disable-return' ] ) {
			attributes[ 'data-disable-return' ] = 'true';
		}

		if ( config[ 'disable-extra-spaces' ] ) {
			attributes[ 'data-disable-extra-spaces' ] = 'true';
		}

		if ( config.encoding ) {
			attributes[ 'data-encoding' ] = 'true';
		}

		if ( 'object' === typeof config.overrides ) {
			_.each( config.overrides, function( elementParam, inlineParam ) {
				attributes[ 'data-inline-override-' + inlineParam ] = elementParam;
			} );
		}
		attributes[ 'data-toolbar' ] = config.toolbar.toString();
		attributes[ 'data-param' ]   = config.param;

		if ( 'object' === typeof view && 'object' === typeof view.dynamicParams ) {
			if ( view.dynamicParams.hasDynamicParam( config.param ) ) {
				attributes[ 'data-dynamic-content-overriding' ] = 'true';
			}
		}

		return attributes;
	},

	/**
	 * JS copy of fusion_section_deprecated_args.
	 * Maps the dprecated container args.
	 *
	 * @since 2.0.0
	 * @param {Object} args - The parameters.
	 * @return {Object}
	 */
	fusionContainerMapDeprecatedArgs: function( args ) {
		var paramMapping = {
			backgroundposition: 'background_position',
			backgroundattachment: 'background_parallax',
			background_attachment: 'background_parallax',
			bordersize: 'border_size',
			bordercolor: 'border_color',
			borderstyle: 'border_style',
			paddingtop: 'padding_top',
			paddingbottom: 'padding_bottom',
			paddingleft: 'padding_left',
			paddingright: 'padding_right',
			backgroundcolor: 'background_color',
			backgroundimage: 'background_image',
			backgroundrepeat: 'background_repeat',
			paddingBottom: 'padding_bottom',
			paddingTop: 'padding_top'
		};

		if ( ( 'undefined' !== typeof args.backgroundattachment  && 'scroll' === args.backgroundattachment ) || ( 'undefined' !== typeof args.background_attachment && 'scroll' === args.background_attachment ) ) {
			args.backgroundattachment  = 'none';
			args.background_attachment = 'none';
		}

		_.each( paramMapping, function( newName, oldName ) {
			if ( 'undefined' === typeof args[ newName ] && 'undefined' !== typeof args[ oldName ] ) {
				args[ newName ] = args[ oldName ];
				delete args[ oldName ];
			}
		} );

		return args;
	},

	/**
	 * Replaces double line-breaks with paragraph elements.
	 *
	 * JS version of the wpautop() PHP function and based on the portation
	 * for the Gutenberg block editor.
	 *
	 * @since 2.0.0
	 * @param  {string}    text The text which has to be formatted.
	 * @param  {boolean}   br   Optional. If set, will convert all remaining line-
	 *                          breaks after paragraphing. Default true.
	 * @return {string}         Text which has been converted into paragraph tags.
	 */
	autop: function( text, br ) {
		var preTags = [],
			textParts,
			lastText,
			i,
			textPart,
			start,
			name,
			allBlocks,
			texts;

		if ( 'string' !== typeof text || '' === text.trim() ) {
			return '';
		}

		br = ( 'undefined' === typeof br ) ? true : br;

		// Just to make things a little easier, pad the end.
		text = text + '\n';

		/*
		 * Pre tags shouldn't be touched by autop.
		 * Replace pre tags with placeholders and bring them back after autop.
		 */
		if ( -1 !== text.indexOf( '<pre' ) ) {
			textParts = text.split( '</pre>' );
			lastText = textParts.pop();
			text = '';

			for ( i = 0; i < textParts.length; i++ ) {
				textPart = textParts[ i ];
				start = textPart.indexOf( '<pre' );

				// Malformed html?
				if ( -1 === start ) {
					text += textPart;
					continue;
				}

				name = '<pre wp-pre-tag-' + i + '></pre>';
				preTags.push( [ name, textPart.substr( start ) + '</pre>' ] );

				text += textPart.substr( 0, start ) + name;
			}

			text += lastText;
		}

		// Change multiple <br>s into two line breaks, which will turn into paragraphs.
		text = text.replace( /<br\s*\/?>\s*<br\s*\/?>/g, '\n\n' );

		allBlocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';

		// Add a double line break above block-level opening tags.
		text = text.replace( new RegExp( '(<' + allBlocks + '[\\s\/>])', 'g' ), '\n\n$1' );

		// Add a double line break below block-level closing tags.
		text = text.replace( new RegExp( '(<\/' + allBlocks + '>)', 'g' ), '$1\n\n' );

		// Standardize newline characters to "\n".
		text = text.replace( /\r\n|\r/g, '\n' );

		// Find newlines in all elements and add placeholders.
		text = this.replaceInHtmlTags( text, { '\n': ' <!-- wpnl --> ' } );

		// Collapse line breaks before and after <option> elements so they don't get autop'd.
		if ( -1 !== text.indexOf( '<option' ) ) {
			text = text.replace( /\s*<option/g, '<option' );
			text = text.replace( /<\/option>\s*/g, '</option>' );
		}

		/*
		 * Collapse line breaks inside <object> elements, before <param> and <embed> elements
		 * so they don't get autop'd.
		 */
		if ( -1 !== text.indexOf( '</object>' ) ) {
			text = text.replace( /(<object[^>]*>)\s*/g, '$1' );
			text = text.replace( /\s*<\/object>/g, '</object>' );
			text = text.replace( /\s*(<\/?(?:param|embed)[^>]*>)\s*/g, '$1' );
		}

		/*
		 * Collapse line breaks inside <audio> and <video> elements,
		 * before and after <source> and <track> elements.
		 */
		if ( -1 !== text.indexOf( '<source' ) || -1 !== text.indexOf( '<track' ) ) {
			text = text.replace( /([<\[](?:audio|video)[^>\]]*[>\]])\s*/g, '$1' );
			text = text.replace( /\s*([<\[]\/(?:audio|video)[>\]])/g, '$1' );
			text = text.replace( /\s*(<(?:source|track)[^>]*>)\s*/g, '$1' );
		}

		// Collapse line breaks before and after <figcaption> elements.
		if ( -1 !== text.indexOf( '<figcaption' ) ) {
			text = text.replace( /\s*(<figcaption[^>]*>)/, '$1' );
			text = text.replace( /<\/figcaption>\s*/, '</figcaption>' );
		}

		// Remove more than two contiguous line breaks.
		text = text.replace( /\n\n+/g, '\n\n' );

		// Split up the contents into an array of strings, separated by double line breaks.
		texts = text.split( /\n\s*\n/ ).filter( Boolean );

		// Reset text prior to rebuilding.
		text = '';

		// Rebuild the content as a string, wrapping every bit with a <p>.
		texts.forEach( function( textPiece ) {
			text += '<p>' + textPiece.replace( /^\n*|\n*$/g, '' ) + '</p>\n';
		} );

		// Under certain strange conditions it could create a P of entirely whitespace.
		text = text.replace( /<p>\s*<\/p>/g, '' );

		// Add a closing <p> inside <div>, <address>, or <form> tag if missing.
		text = text.replace( /<p>([^<]+)<\/(div|address|form)>/g, '<p>$1</p></$2>' );

		// If an opening or closing block element tag is wrapped in a <p>, unwrap it.
		text = text.replace( new RegExp( '<p>\\s*(<\/?' + allBlocks + '[^>]*>)\\s*<\/p>', 'g' ), '$1' );

		// In some cases <li> may get wrapped in <p>, fix them.
		text = text.replace( /<p>(<li.+?)<\/p>/g, '$1' );

		// If a <blockquote> is wrapped with a <p>, move it inside the <blockquote>.
		text = text.replace( /<p><blockquote([^>]*)>/gi, '<blockquote$1><p>' );
		text = text.replace( /<\/blockquote><\/p>/g, '</p></blockquote>' );

		// If an opening or closing block element tag is preceded by an opening <p> tag, remove it.
		text = text.replace( new RegExp( '<p>\\s*(<\/?' + allBlocks + '[^>]*>)', 'g' ), '$1' );

		// If an opening or closing block element tag is followed by a closing <p> tag, remove it.
		text = text.replace( new RegExp( '(<\/?' + allBlocks + '[^>]*>)\\s*<\/p>', 'g' ), '$1' );

		// Optionally insert line breaks.
		if ( br ) {

			// Replace newlines that shouldn't be touched with a placeholder.
			text = text.replace( /<(script|style).*?<\/\\1>/g, function( match ) {
				return match[ 0 ].replace( /\n/g, '<WPPreserveNewline />' );
			} );

			// Normalize <br>
			text = text.replace( /<br>|<br\/>/g, '<br />' );

			// Replace any new line characters that aren't preceded by a <br /> with a <br />.
			text = text.replace( /(<br \/>)?\s*\n/g, function( a, b ) {
				return b ? a : '<br />\n';
			} );

			// Replace newline placeholders with newlines.
			text = text.replace( /<WPPreserveNewline \/>/g, '\n' );
		}

		// If a <br /> tag is after an opening or closing block tag, remove it.
		text = text.replace( new RegExp( '(<\/?' + allBlocks + '[^>]*>)\\s*<br \/>', 'g' ), '$1' );

		// If a <br /> tag is before a subset of opening or closing block tags, remove it.
		text = text.replace( /<br \/>(\s*<\/?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)/g, '$1' );
		text = text.replace( /\n<\/p>$/g, '</p>' );

		// Replace placeholder <pre> tags with their original content.
		if ( Object.keys( preTags ).length ) {
			text = text.replace( new RegExp( Object.keys( preTags ).join( '|' ), 'gi' ), function( matched ) {
				return preTags[ matched ];
			} );
		}

		// Restore newlines in all elements.
		if ( -1 !== text.indexOf( '<!-- wpnl -->' ) ) {
			text = text.replace( /\s?<!-- wpnl -->\s?/g, '\n' );
		}

		return text;
	},

	/**
	 * Separate HTML elements and comments from the text.
	 *
	 * JS version of the html_split() PHP function and based on the portation
	 * for the Gutenberg block editor.
	 *
	 * @since 2.0
	 * @param  {string} input The text which has to be formatted.
	 * @return {Array}        The formatted text.
	 */
	htmlSplit: function( input ) {
		var parts = [],
			workingInput = input,
			match,
			htmlSplitRegex = ( function() {

				/* eslint-disable no-multi-spaces */
				var comments =
					'!' +           // Start of comment, after the <.
					'(?:' +         // Unroll the loop: Consume everything until --> is found.
						'-(?!->)' + // Dash not followed by end of comment.
						'[^\\-]*' + // Consume non-dashes.
					')*' +          // Loop possessively.
					'(?:-->)?',     // End of comment. If not found, match all input.

					cdata =
					'!\\[CDATA\\[' + // Start of comment, after the <.
					'[^\\]]*' +      // Consume non-].
					'(?:' +          // Unroll the loop: Consume everything until ]]> is found.
						'](?!]>)' +  // One ] not followed by end of comment.
						'[^\\]]*' +  // Consume non-].
					')*?' +          // Loop possessively.
					'(?:]]>)?',      // End of comment. If not found, match all input.

					escaped =
					'(?=' +              // Is the element escaped?
						'!--' +
					'|' +
						'!\\[CDATA\\[' +
					')' +
					'((?=!-)' +          // If yes, which type?
						comments +
					'|' +
						cdata +
					')',

					regex =
					'(' +               // Capture the entire match.
						'<' +           // Find start of element.
						'(' +           // Conditional expression follows.
							escaped +   // Find end of escaped element.
						'|' +           // ... else ...
							'[^>]*>?' + // Find end of normal element.
						')' +
					')';

				return new RegExp( regex );
				/* eslint-enable no-multi-spaces */
			}() );

		while ( ( match = workingInput.match( htmlSplitRegex ) ) ) {
			parts.push( workingInput.slice( 0, match.index ) );
			parts.push( match[ 0 ] );
			workingInput = workingInput.slice( match.index + match[ 0 ].length );
		}

		if ( workingInput.length ) {
			parts.push( workingInput );
		}

		return parts;
	},

	/**
	 * Replace characters or phrases within HTML elements only.
	 *
	 * JS version of the replace_html_tags() PHP function and based on the portation
	 * for the Gutenberg block editor.
	 *
	 * @since 2.0
	 * @param  {string} haystack     The text which has to be formatted.
	 * @param  {Object} replacePairs In the form {from: 'to', ...}.
	 * @return {string}              The formatted text.
	 */
	replaceInHtmlTags: function( haystack, replacePairs ) {

		// Find all elements.
		var textArr = this.htmlSplit( haystack ),
			changed = false,
			needles = Object.keys( replacePairs ), // Extract all needles.
			i, j,
			needle;

		// Loop through delimiters (elements) only.
		for ( i = 1; i < textArr.length; i += 2 ) {
			for ( j = 0; j < needles.length; j++ ) {
				needle = needles[ j ];

				if ( -1 !== textArr[ i ].indexOf( needle ) ) {
					textArr[ i ] = textArr[ i ].replace( new RegExp( needle, 'g' ), replacePairs[ needle ] );
					changed = true;

					// After one strtr() break out of the foreach loop and look at next element.
					break;
				}
			}
		}

		if ( changed ) {
			haystack = textArr.join( '' );
		}

		return haystack;
	},

	/**
	 * Generates filter CSS.
	 *
	 * @since 2.1
	 * @param {Object} values - The values.
	 * @return {String}
	 */
	fusionGetFilterStyle: function( values, state ) {
		var filters = {
			filter_hue: { property: 'hue-rotate', unit: 'deg', default: '0' },
			filter_saturation: { property: 'saturate', unit: '%', default: '100' },
			filter_brightness: { property: 'brightness', unit: '%', default: '100' },
			filter_contrast: { property: 'contrast', unit: '%', default: '100' },
			filter_invert: { property: 'invert', unit: '%', default: '0' },
			filter_sepia: { property: 'sepia', unit: '%', default: '0' },
			filter_opacity: { property: 'opacity', unit: '%', default: '100' },
			filter_blur: { property: 'blur', unit: 'px', default: '0' }
		},
		stateSuffix        = 'regular' === state ? '' : '_hover',
		otherStateSuffix   = 'regular' === state ? '_hover' : '',
		filter_id_state = '',
		filter_id_other = '',
		filter_style    = '';

		_.each( filters, function( filter, filter_id ) {
			filter_id_state = filter_id + stateSuffix;
			filter_id_other = filter_id + otherStateSuffix;
			if ( filter[ 'default' ] !== values[ filter_id_state ] || filter[ 'default' ] !== values[ filter_id_other ] ) {
				filter_style += filter.property + '(' + values[ filter_id_state ] + filter.unit + ') ';
			}
		} );

		return filter_style.trim();
	},

	/**
	 * Generates filter style element.
	 *
	 * @since 2.1
	 * @param {Object} values - The values.
	 * @param {string|object} selector - Element selector.
	 * @param {integer} cid - Element cid.
	 * @return {String}
	 */
	fusionGetFilterStyleElem: function( values, selector, cid ) {
		var filter_style       = '',
			filter_style_hover = '',
			regularSelector    = 'body:not(.fusion-builder-ui-wireframe):not(.fusion-disable-element-filters) ',
			hoverSelector      = 'body:not(.fusion-builder-ui-wireframe):not(.fusion-disable-element-filters) ';

		if ( 'object' === typeof selector ) {
			regularSelector += selector.regular;
			hoverSelector   += selector.hover;
		} else {
			regularSelector += selector;
			hoverSelector   += selector + ':hover';
		}

		// Get filter CSS.
		filter_style = this.fusionGetFilterStyle( values, 'regular' );
		if ( '' !== filter_style ) {
			filter_style = regularSelector + '{filter: ' + filter_style + ';}';
		}

		filter_style_hover = this.fusionGetFilterStyle( values, 'hover' );
		if ( '' !== filter_style_hover ) {

			// Add transition.
			filter_style = filter_style.replace( '}', 'transition: filter 0.3s ease-in-out;}' );

			// Hover state.
			filter_style += hoverSelector + '{filter: ' + filter_style_hover + ';}';
		}

		// We need empty style element as well.
		return '<style id="fusion-filter-' + cid + '-style">' + filter_style + '</style>';
	},

	/**
	 * Generates gradient string for provided options.
	 *
	 * @since 2.1
	 * @param {Object} values - Element params.
	 * @param {string} type - Required gradient string type.
	 * @return {string} - Generated string.
	 */
	getGradientString: function( values, type ) {
		var gradientString          = '',
			alphaGradientStartColor = jQuery.Color( values.gradient_start_color ).alpha(),
			alphaGradientEndColor   = jQuery.Color( values.gradient_end_color ).alpha(),
			isGradientColor         = ( ! _.isEmpty( values.gradient_start_color ) && 0 !== alphaGradientStartColor ) || ( ! _.isEmpty( values.gradient_end_color ) && 0 !== alphaGradientEndColor ) ? true : false;

		if ( isGradientColor ) {
			if ( 'linear' === values.gradient_type ) {
				gradientString += 'linear-gradient(' + values.linear_angle + 'deg,';
			} else if ( 'radial' === values.gradient_type ) {
				gradientString += 'radial-gradient(circle at ' + values.radial_direction + ', ';
			}

			gradientString += values.gradient_start_color + ' ' + values.gradient_start_position + '%,';
			gradientString += values.gradient_end_color + ' ' + values.gradient_end_position + '%)';

			switch ( type ) {

			case 'main_bg':
			case 'parallax':
				if ( '' !== values.background_image && 'yes' !== values.fade ) {
					gradientString += ',url(\'' + values.background_image + '\')';
				}
				break;
			case 'fade':
			case 'column':
				if ( '' !== values.background_image ) {
					gradientString += ',url(\'' + values.background_image + '\')';
				}
				break;
			}
		}

		return gradientString;
	},

	/**
	 * Generates box-shadow style for provided options.
	 *
	 * @since 2.1
	 * @param {Object} values - The values.
	 * @return {string} - The generated CSS.
	 */
	fusionGetBoxShadowStyle: function( values ) {
		var style = '';

		style += _.fusionGetValueWithUnit( values.box_shadow_horizontal );
		style += ' ' + _.fusionGetValueWithUnit( values.box_shadow_vertical );
		style += ' ' + _.fusionGetValueWithUnit( values.box_shadow_blur );
		style += ' ' + _.fusionGetValueWithUnit( values.box_shadow_spread );
		style += ' ' + values.box_shadow_color;

		if ( 'undefined' !== typeof values.box_shadow_style && '' !== values.box_shadow_style ) {
			style += ' ' + values.box_shadow_style;
		}

		style += ';';

		return style;
	},

	/**
	 * Generates text-shadow style for provided options.
	 *
	 * @since 2.1
	 * @param {Object} values - The values.
	 * @return {string} - The generated CSS.
	 */
	fusionGetTextShadowStyle: function( values ) {
		var style = '';

		style += _.fusionGetValueWithUnit( values.text_shadow_horizontal );
		style += ' ' + _.fusionGetValueWithUnit( values.text_shadow_vertical );
		style += ' ' + _.fusionGetValueWithUnit( values.text_shadow_blur );
		style += ' ' + values.text_shadow_color;
		style += ';';

		return style;
	},

	/**
	 * Get font family styling.
	 *
	 * @since 2.1
	 * @param {string} param_id - Param ID.
	 * @param {Object} values - The values.
	 * @param {string} format - Format of returned value, string or object.
	 * @return {mixed} - The generated styling.
	 */
	fusionGetFontStyle: function( param_id, values, format = 'string' ) {
		var style     = {},
			style_str = '',
			weight    = '';

		if ( '' !== values[ 'fusion_font_family_' + param_id ] ) {
			if ( values[ 'fusion_font_family_' + param_id ].includes( '\'' ) || 'inherit' === values[ 'fusion_font_family_' + param_id ] ) {
				style[ 'font-family' ] = values[ 'fusion_font_family_' + param_id ];
			} else {
				style[ 'font-family' ] = '\'' + values[ 'fusion_font_family_' + param_id ] + '\'';
			}

			if ( '' !== values[ 'fusion_font_variant_' + param_id ] ) {
				weight = values[ 'fusion_font_variant_' + param_id ].replace( 'italic', '' );
				if ( weight !== values[ 'fusion_font_variant_' + param_id ] ) {
					style[ 'font-style' ] = 'italic';
				}
				if ( '' !== weight ) {
					style[ 'font-weight' ] = weight;
				}
			}
		}

		if ( 'string' === format ) {
			jQuery.each( style, function( key, value ) {
				style_str += key + ':' + value + ';';
			} );

			return style_str;
		}

		return style;
	},

	/**
	 * Checks if valid JSON or not.
	 *
	 * @since 3.0
	 * @param {String} value - The value to check.
	 * @return {Bolean}
	 */
	FusionIsValidJSON: function( value ) {
		try {
			JSON.parse( value );
		} catch ( e ) {
			return false;
		}
		return true;
	},

	/**
	 * Returns sticky class string.
	 *
	 * @since 3.0
	 * @param {String} value - The value to check.
	 * @return {String}
	 */
	fusionGetStickyClass: function( value ) {
		return '' !== value && ! value.includes( ',' ) ? ' fusion-display-' + value + '-only' : '';
	},

	/**
	 * Link attributes.
	 *
	 * @since 3.3
	 * @param {Object} attr - Element attributes.
	 * @param {Object} values - Element values.
	 * @return {Object}
	 */
	fusionLinkAttributes: function( attr, values ) {
		var linkAttributes;
		if ( 'undefined' !== typeof values.link_attributes && '' !== values.link_attributes ) {
			linkAttributes = values.link_attributes.split( ' ' );

			_.each( linkAttributes, function( linkAttribute ) {
				var attributeKeyValue = linkAttribute.split( '=' );

				if ( ! _.isUndefined( attributeKeyValue[ 0 ] ) ) {
					if ( ! _.isUndefined( attributeKeyValue[ 1 ] ) ) {
						attributeKeyValue[ 1 ] = attributeKeyValue[ 1 ].trim().replace( /{/g, '[' ).replace( /}/g, ']' ).replace( /'/g, '' ).trim();
						if ( 'rel' === attributeKeyValue[ 0 ] ) {
							attr.rel += ' ' + attributeKeyValue[ 1 ];
						} else if ( 'string' === typeof attr[ attributeKeyValue[ 0 ] ] ) {
							attr[ attributeKeyValue[ 0 ] ] += ' ' + attributeKeyValue[ 1 ];
						} else {
							attr[ attributeKeyValue[ 0 ] ] = attributeKeyValue[ 1 ];
						}
					} else {
						attr[ attributeKeyValue[ 0 ] ] = 'valueless_attribute';
					}
				}
			} );
		}
		return attr;
	},

	fusionSanitize: function( str ) {

		var map = {
				'À': 'A',
				'Á': 'A',
				'Â': 'A',
				'Ã': 'A',
				'Ä': 'A',
				'Å': 'A',
				'Æ': 'AE',
				'Ç': 'C',
				'È': 'E',
				'É': 'E',
				'Ê': 'E',
				'Ë': 'E',
				'Ì': 'I',
				'Í': 'I',
				'Î': 'I',
				'Ï': 'I',
				'Ð': 'D',
				'Ñ': 'N',
				'Ò': 'O',
				'Ó': 'O',
				'Ô': 'O',
				'Õ': 'O',
				'Ö': 'O',
				'Ø': 'O',
				'Ù': 'U',
				'Ú': 'U',
				'Û': 'U',
				'Ü': 'U',
				'Ý': 'Y',
				'ß': 's',
				'à': 'a',
				'á': 'a',
				'â': 'a',
				'ã': 'a',
				'ä': 'a',
				'å': 'a',
				'æ': 'ae',
				'ç': 'c',
				'è': 'e',
				'é': 'e',
				'ê': 'e',
				'ë': 'e',
				'ì': 'i',
				'í': 'i',
				'î': 'i',
				'ï': 'i',
				'ñ': 'n',
				'ò': 'o',
				'ó': 'o',
				'ô': 'o',
				'õ': 'o',
				'ö': 'o',
				'ø': 'o',
				'ù': 'u',
				'ú': 'u',
				'û': 'u',
				'ü': 'u',
				'ý': 'y',
				'ÿ': 'y',
				'Ā': 'A',
				'ā': 'a',
				'Ă': 'A',
				'ă': 'a',
				'Ą': 'A',
				'ą': 'a',
				'Ć': 'C',
				'ć': 'c',
				'Ĉ': 'C',
				'ĉ': 'c',
				'Ċ': 'C',
				'ċ': 'c',
				'Č': 'C',
				'č': 'c',
				'Ď': 'D',
				'ď': 'd',
				'Đ': 'D',
				'đ': 'd',
				'Ē': 'E',
				'ē': 'e',
				'Ĕ': 'E',
				'ĕ': 'e',
				'Ė': 'E',
				'ė': 'e',
				'Ę': 'E',
				'ę': 'e',
				'Ě': 'E',
				'ě': 'e',
				'Ĝ': 'G',
				'ĝ': 'g',
				'Ğ': 'G',
				'ğ': 'g',
				'Ġ': 'G',
				'ġ': 'g',
				'Ģ': 'G',
				'ģ': 'g',
				'Ĥ': 'H',
				'ĥ': 'h',
				'Ħ': 'H',
				'ħ': 'h',
				'Ĩ': 'I',
				'ĩ': 'i',
				'Ī': 'I',
				'ī': 'i',
				'Ĭ': 'I',
				'ĭ': 'i',
				'Į': 'I',
				'į': 'i',
				'İ': 'I',
				'ı': 'i',
				'Ĳ': 'IJ',
				'ĳ': 'ij',
				'Ĵ': 'J',
				'ĵ': 'j',
				'Ķ': 'K',
				'ķ': 'k',
				'Ĺ': 'L',
				'ĺ': 'l',
				'Ļ': 'L',
				'ļ': 'l',
				'Ľ': 'L',
				'ľ': 'l',
				'Ŀ': 'L',
				'ŀ': 'l',
				'Ł': 'l',
				'ł': 'l',
				'Ń': 'N',
				'ń': 'n',
				'Ņ': 'N',
				'ņ': 'n',
				'Ň': 'N',
				'ň': 'n',
				'ŉ': 'n',
				'Ō': 'O',
				'ō': 'o',
				'Ŏ': 'O',
				'ŏ': 'o',
				'Ő': 'O',
				'ő': 'o',
				'Œ': 'OE',
				'œ': 'oe',
				'Ŕ': 'R',
				'ŕ': 'r',
				'Ŗ': 'R',
				'ŗ': 'r',
				'Ř': 'R',
				'ř': 'r',
				'Ś': 'S',
				'ś': 's',
				'Ŝ': 'S',
				'ŝ': 's',
				'Ş': 'S',
				'ş': 's',
				'Š': 'S',
				'š': 's',
				'Ţ': 'T',
				'ţ': 't',
				'Ť': 'T',
				'ť': 't',
				'Ŧ': 'T',
				'ŧ': 't',
				'Ũ': 'U',
				'ũ': 'u',
				'Ū': 'U',
				'ū': 'u',
				'Ŭ': 'U',
				'ŭ': 'u',
				'Ů': 'U',
				'ů': 'u',
				'Ű': 'U',
				'ű': 'u',
				'Ų': 'U',
				'ų': 'u',
				'Ŵ': 'W',
				'ŵ': 'w',
				'Ŷ': 'Y',
				'ŷ': 'y',
				'Ÿ': 'Y',
				'Ź': 'Z',
				'ź': 'z',
				'Ż': 'Z',
				'ż': 'z',
				'Ž': 'Z',
				'ž': 'z',
				'ſ': 's',
				'ƒ': 'f',
				'Ơ': 'O',
				'ơ': 'o',
				'Ư': 'U',
				'ư': 'u',
				'Ǎ': 'A',
				'ǎ': 'a',
				'Ǐ': 'I',
				'ǐ': 'i',
				'Ǒ': 'O',
				'ǒ': 'o',
				'Ǔ': 'U',
				'ǔ': 'u',
				'Ǖ': 'U',
				'ǖ': 'u',
				'Ǘ': 'U',
				'ǘ': 'u',
				'Ǚ': 'U',
				'ǚ': 'u',
				'Ǜ': 'U',
				'ǜ': 'u',
				'Ǻ': 'A',
				'ǻ': 'a',
				'Ǽ': 'AE',
				'ǽ': 'ae',
				'Ǿ': 'O',
				'ǿ': 'o',
				'α': 'a',
				'Α': 'A',
				'β': 'v',
				'Β': 'V',
				'γ': 'g',
				'Γ': 'G',
				'δ': 'd',
				'Δ': 'D',
				'ε': 'e',
				'Ε': 'E',
				'ζ': 'z',
				'Ζ': 'Z',
				'η': 'i',
				'Η': 'I',
				'θ': 'th',
				'Θ': 'TH',
				'ι': 'i',
				'Ι': 'I',
				'κ': 'k',
				'Κ': 'K',
				'λ': 'l',
				'Λ': 'L',
				'μ': 'm',
				'Μ': 'M',
				'ν': 'n',
				'Ν': 'N',
				'ξ': 'ks',
				'Ξ': 'KS',
				'ο': 'o',
				'Ο': 'O',
				'π': 'p',
				'Π': 'P',
				'ρ': 'r',
				'Ρ': 'R',
				'σ': 's',
				'Σ': 'S',
				'ς': 's',
				'τ': 't',
				'Τ': 'T',
				'υ': 'y',
				'Υ': 'Y',
				'φ': 'f',
				'Φ': 'F',
				'χ': 'x',
				'Χ': 'X',
				'ψ': 'ps',
				'Ψ': 'PS',
				'ω': 'o',
				'Ω': 'O',
				' ': '_',
				'\'': '',
				'?': '',
				'/': '',
				'\\': '',
				'.': '',
				',': '',
				'`': '',
				'>': '',
				'<': '',
				'"': '',
				'[': '',
				']': '',
				'|': '',
				'{': '',
				'}': '',
				'(': '',
				')': ''
			},
			nonWord = /\W/g,
			mapping = function ( c ) {
				return ( map[ c ] !== undefined ) ? map[ c ] : c;
			};
		return str.replace( nonWord, mapping ).toLowerCase();
	}
} );
