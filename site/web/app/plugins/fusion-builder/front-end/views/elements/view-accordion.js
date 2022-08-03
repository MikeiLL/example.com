var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Accordion View.
		FusionPageBuilder.fusion_accordion = FusionPageBuilder.ParentElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes object.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				this.validateValues( atts.values );

				// Create attribute objects.
				attributes.toggleShortcode           = this.buildToggleAttr( atts.values );
				attributes.toggleShortcodePanelGroup = this.buildPanelGroupAttr( atts.values );
				attributes.styles                    = this.buildStyles( atts.values );

				return attributes;
			},

			/**
			 * Modifies the values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values ) {
				values.icon_size       = _.fusionValidateAttrValue( values.icon_size, 'px' );
				values.border_size     = _.fusionValidateAttrValue( values.border_size, 'px' );
				values.title_font_size = _.fusionValidateAttrValue( values.title_font_size, 'px' );
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildToggleAttr: function( values ) {
				var toggleShortcode = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'accordian fusion-accordian'
				} );

				if ( ' ' !== values[ 'class' ] ) {
					toggleShortcode[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					toggleShortcode.id = values.id;
				}

				return toggleShortcode;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildPanelGroupAttr: function( values ) {
				var toggleShortcodePanelGroup = {
					class: 'panel-group fusion-child-element',
					id: 'accordion-cid' + this.model.get( 'cid' )
				};

				if ( 'right' === values.icon_alignment ) {
					toggleShortcodePanelGroup[ 'class' ] += ' fusion-toggle-icon-right';
				}

				if ( '0' === values.icon_boxed_mode || 'no' === values.icon_boxed_mode ) {
					toggleShortcodePanelGroup[ 'class' ] += ' fusion-toggle-icon-unboxed';
				}

				toggleShortcodePanelGroup[ 'data-empty' ] = this.emptyPlaceholderText;

				return toggleShortcodePanelGroup;
			},

			/**
			 * Builds the stylesheet.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {string}
			 */
			buildStyles: function( values ) {
				var styles = '',
					cid = this.model.get( 'cid' );

				if ( '' !== values.title_font_size ) {
					styles += '.fusion-accordian  #accordion-cid' + cid + ' .panel-title a{ font-size: ' + values.title_font_size + ';}';
				}

				if ( '' !== values.icon_size ) {
					styles += '.fusion-accordian  #accordion-cid' + cid + ' .panel-title a .fa-fusion-box:before{ font-size: ' + values.icon_size + '; width: ' + values.icon_size + ';}';
				}

				if ( '' !== values.icon_color ) {
					styles += '.fusion-accordian  #accordion-cid' + cid + ' .panel-title a .fa-fusion-box{ color: ' + values.icon_color + ';}';
				}

				if ( 'right' === values.icon_alignment ) {
					styles += '.fusion-accordian  #accordion-cid' + cid + '.fusion-toggle-icon-right .fusion-toggle-heading{ margin-right: ' + ( parseInt( values.icon_size, 10 ) + 18 ) + 'px;}';
				}

				if ( ( '1' === values.icon_boxed_mode || 'yes' === values.icon_boxed_mode ) && ! _.isEmpty( values.icon_box_color ) ) {
					styles += '.fusion-accordian  #accordion-cid' + cid + ' .fa-fusion-box { background-color: ' + values.icon_box_color + ';border-color: ' + values.icon_box_color + ';}';
				}

				if ( ! _.isEmpty( values.toggle_hover_accent_color ) ) {
					styles += '.fusion-accordian  #accordion-cid' + cid + ' .panel-title a:hover, #accordion-cid' + cid + ' .fusion-toggle-boxed-mode:hover .panel-title a { color: ' + values.toggle_hover_accent_color + ';}';
					styles += '.fusion-accordian  #accordion-cid' + cid + ' .panel-title a.hover, #accordion-cid' + cid + ' .fusion-toggle-boxed-mode.hover .panel-title a { color: ' + values.toggle_hover_accent_color + ';}';

					if ( '1' === values.icon_boxed_mode || 'yes' === values.icon_boxed_mode ) {
						styles += '.fusion-accordian  #accordion-cid' + cid + ' .panel-title .active .fa-fusion-box,';
						styles += '.fusion-accordian  #accordion-cid' + cid + ' .panel-title a:hover .fa-fusion-box { background-color: ' + values.toggle_hover_accent_color + '!important;border-color: ' + values.toggle_hover_accent_color + '!important;}';
						styles += '.fusion-accordian  #accordion-cid' + cid + ' .panel-title a.hover .fa-fusion-box { background-color: ' + values.toggle_hover_accent_color + '!important;border-color: ' + values.toggle_hover_accent_color + '!important;}';
					} else {
						styles += '.fusion-accordian  #accordion-cid' + cid + ' .fusion-toggle-boxed-mode:hover .panel-title a .fa-fusion-box{ color: ' + values.toggle_hover_accent_color + ';}';
						styles += '.fusion-accordian  #accordion-cid' + cid + '.fusion-toggle-icon-unboxed .fusion-panel .panel-title a:hover .fa-fusion-box{ color: ' + values.toggle_hover_accent_color + ' !important;}';
						styles += '.fusion-accordian  #accordion-cid' + cid + ' .fusion-toggle-boxed-mode.hover .panel-title a .fa-fusion-box{ color: ' + values.toggle_hover_accent_color + ';}';
						styles += '.fusion-accordian  #accordion-cid' + cid + '.fusion-toggle-icon-unboxed .fusion-panel .panel-title a.hover .fa-fusion-box{ color: ' + values.toggle_hover_accent_color + ' !important;}';
					}
				}

				if ( '1' == values.boxed_mode || 'yes' === values.boxed_mode ) {

					if ( '' !== values.hover_color ) {
						styles += '#accordion-cid' + cid + ' .fusion-panel:hover, #accordion-cid' + cid + ' .fusion-panel.hover{ background-color: ' + values.hover_color + ' }';
					}

					styles += '#accordion-cid' + cid + ' .fusion-panel {';
					if ( '' !== values.border_color ) {
						styles += ' border-color:' + values.border_color + ';';
					}

					if ( '' !== values.border_size ) {
						styles += ' border-width:' + values.border_size + ';';
					}

					if ( '' !== values.background_color ) {
						styles += ' background-color:' + values.background_color + ';';
					}
					styles += ' }';
				}

				return styles;
			}
		} );
	} );
}( jQuery ) );
