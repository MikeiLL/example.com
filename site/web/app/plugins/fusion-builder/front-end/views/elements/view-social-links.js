var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Social Links View.
		FusionPageBuilder.fusion_social_links = FusionPageBuilder.ElementView.extend( {

			/**
			 * Runs before view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			beforePatch: function() {
				var tooltips = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el.find( '.fusion-tooltip' ) );

				tooltips.tooltip( 'destroy' );
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				this._refreshJs();
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var socialLinksShortcode,
					socialLinksShortcodeSocialNetworks,
					icons,
					values = atts.values;

				// Validate values and extras.
				this.validateValuesExtras( atts.values, atts.extras );

				// Create attribute objects.
				socialLinksShortcode               = this.buildShortcodeAttr( atts.values );
				socialLinksShortcodeSocialNetworks = this.buildSocialNetworksAttr( atts.values );
				icons                              = this.buildIcons( atts.values );

				// Reset attributes.
				atts = {};

				atts.socialLinksShortcode               = socialLinksShortcode;
				atts.socialLinksShortcodeSocialNetworks = socialLinksShortcodeSocialNetworks;
				atts.icons                              = icons;

				// Any extras that need passed on.
				atts.alignment = values.alignment;

				return atts;
			},

			/**
			 * Modifies the values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @param {Object} extras - Extra args.
			 * @return {void}
			 */
			validateValuesExtras: function( values, extras ) {
				values.linktarget              = values.linktarget ? '_blank' : '_self';
				values.social_media_icons      = extras.social_media_icons;
				values.social_media_icons_icon = extras.social_media_icons.icon;
				values.social_media_icons_url  = extras.social_media_icons.url;
				values.icons_boxed_radius      = _.fusionValidateAttrValue( values.icons_boxed_radius, 'px' );
				values.font_size               = _.fusionValidateAttrValue( values.font_size, 'px' );
				values.boxed_padding           = _.fusionValidateAttrValue( extras.boxed_padding, 'px' );

				if ( '' == values.color_type ) {
					values.box_colors  = values.social_links_box_color;
					values.icon_colors = values.social_links_icon_color;
				}
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildShortcodeAttr: function( values ) {
				var socialLinksShortcode = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'fusion-social-links'
				} );

				socialLinksShortcode[ 'class' ] += _.fusionGetStickyClass( values.sticky_display );

				if ( '' !== values[ 'class' ] ) {
					socialLinksShortcode[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					socialLinksShortcode.id = values.id;
				}

				return socialLinksShortcode;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildSocialNetworksAttr: function( values ) {
				var socialLinksShortcodeSocialNetworks = {
					class: 'fusion-social-networks'
				};

				if ( 'yes' === values.icons_boxed ) {
					socialLinksShortcodeSocialNetworks[ 'class' ] += ' boxed-icons';
				}

				return socialLinksShortcodeSocialNetworks;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {string}
			 */
			buildIcons: function( values ) {
				var socialIcons = _.fusionGetSocialNetworks( values ),
					icons;

				socialIcons = _.fusionSortSocialNetworks( socialIcons, values );
				icons       = _.fusionBuildSocialLinks( socialIcons, this.socialLinksIconAttr, values );

				return icons;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} args - The arguments.
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			socialLinksIconAttr: function( args, values ) {
				var tooltip,
					link,

					attr = {
						class: '',
						style: ''
					};

				tooltip = _.fusionUcFirst( args.social_network );
				if ( 'custom_' === args.social_network.substr( 0, 7 ) ) {
					attr[ 'class' ] += 'custom ';
					tooltip = args.social_network.replace( 'custom_', '' );
					args.social_network = tooltip.toLowerCase();
				}

				attr[ 'class' ] += 'fusion-social-network-icon fusion-tooltip fusion-' + args.social_network + ' fusion-icon-' + args.social_network;

				attr[ 'aria-label' ] = 'fusion-' + args.social_network;
				link               = args.social_link;
				attr.target        = values.linktarget;

				if ( '_blank' === values.linktarget ) {
					attr.rel = 'noopener noreferrer';
				}

				if ( 'mail' === args.social_network ) {
					link = ( 'http' === args.social_link.substr( 0, 4 ) ) ? args.social_link : 'mailto:' + args.social_link.replace( 'mailto:', '' );
					attr.target = '_self';
				}

				if ( 'phone' === args.social_network ) {
					link = 'tel:' + args.social_link.replace( 'tel:', '' );
					attr.target = '_self';
				}

				attr.href = link;

				if ( 'undefined' !== typeof args.icon_color && '' !== args.icon_color ) {
					attr.style = 'color:' + args.icon_color + ';';
				}

				if ( 'yes' === values.icons_boxed && 'undefined' !== typeof args.box_color ) {
					attr.style += 'background-color:' + args.box_color + ';border-color:' + args.box_color + ';';
				}

				if ( ( 'yes' === values.icons_boxed && values.icons_boxed_radius ) || '0' === values.icons_boxed_radius ) {
					values.icons_boxed_radius = ( 'round' === values.icons_boxed_radius ) ? '50%' : values.icons_boxed_radius;
					attr.style               += 'border-radius:' + values.icons_boxed_radius + ';';
				}

				if ( values.font_size ) {
					attr.style += 'font-size:' + values.font_size + ';';

					if ( 'yes' === values.icons_boxed ) {
						attr.style += 'width:calc(' + values.font_size + ' + (2 * (' + values.boxed_padding + ')) + 2px);';
					}
				}

				if ( 'none' !== values.tooltip_placement.toLowerCase() ) {
					attr[ 'data-placement' ] = values.tooltip_placement.toLowerCase();
					tooltip                = ( 'youtube' === tooltip.toLowerCase() ) ? 'YouTube' : tooltip;
					attr[ 'data-title' ]     = tooltip;
					attr[ 'data-toggle' ]    = 'tooltip';
				}

				attr.title = tooltip;

				return attr;
			}

		} );
	} );
}( jQuery ) );
