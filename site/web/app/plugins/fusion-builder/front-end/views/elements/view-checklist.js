var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Accordion View.
		FusionPageBuilder.fusion_checklist = FusionPageBuilder.ParentElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				this.validateValues( atts.values );

				// Create attribute objects.
				attributes.checklistShortcode = this.buildChecklistAttr( atts.values );

				// Add computed values that child uses.
				this.buildExtraVars( atts.values );

				// Any extras that need passed on.
				attributes.values = atts.values;
				attributes.cid    = this.model.get( 'cid' );

				return attributes;
			},

			/**
			 * Modify values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values ) {
				values.size = _.fusionValidateAttrValue( values.size, 'px' );

				// Fallbacks for old size parameter and 'px' check+
				if ( 'small' === values.size ) {
					values.size = '13px';
				} else if ( 'medium' === values.size ) {
					values.size = '18px';
				} else if ( 'large' === values.size ) {
					values.size = '40px';
				} else if ( -1 === values.size.indexOf( 'px' ) ) {
					values.size = values.size + 'px';
				}

				values.circle = ( 1 == values.circle ) ? 'yes' : values.circle;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildChecklistAttr: function( values ) {

				// Main Attributes
				var checklistShortcode = {};

				checklistShortcode[ 'class' ] = 'fusion-checklist fusion-checklist-' + this.model.get( 'cid' );

				checklistShortcode = _.fusionVisibilityAtts( values.hide_on_mobile, checklistShortcode );

				this.font_size   = parseFloat( values.size );
				this.line_height = this.font_size * 1.7;

				checklistShortcode.style = 'font-size:' + this.font_size + 'px;line-height:' + this.line_height + 'px;';

				if ( 'yes' === values.divider ) {
					checklistShortcode[ 'class' ] += ' fusion-checklist-divider';
				}

				if ( '' !== values[ 'class' ] ) {
					checklistShortcode[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					checklistShortcode.id = values.id;
				}
				checklistShortcode[ 'class' ] += ' fusion-child-element';
				checklistShortcode[ 'data-empty' ] = this.emptyPlaceholderText;

				return checklistShortcode;
			},

			/**
			 * Sets extra args in the model.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			buildExtraVars: function() {
				var extras = {};

				extras.font_size               = this.font_size;
				extras.line_height             = this.line_height;
				extras.circle_yes_font_size    = extras.font_size * 0.88;
				extras.icon_margin             = extras.font_size * 0.7;
				extras.icon_margin_position    = ( jQuery( 'body' ).hasClass( 'rtl' ) ) ? 'left' : 'right';
				extras.content_margin          = extras.line_height + extras.icon_margin;
				extras.content_margin_position =  ( jQuery( 'body' ).hasClass( 'rtl' ) ) ? 'right' : 'left';

				this.model.set( 'extras', extras );
			}

		} );
	} );
}( jQuery ) );
