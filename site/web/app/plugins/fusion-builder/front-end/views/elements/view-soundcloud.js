var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Vimeo Element View.
		FusionPageBuilder.fusion_soundcloud = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Validate values.
				this.validateValues( atts.values );

				// Create attribute objects
				attributes.attr = this.buildAttr( atts.values );

				// Any extras that need passed on.
				attributes.values = atts.values;

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
				values.width  = _.fusionValidateAttrValue( values.width, 'px' );
				values.height = _.fusionValidateAttrValue( values.height, 'px' );

				values.autoplay = ( 'yes' === values.auto_play ) ? 'true' : 'false';
				values.comments = ( 'yes' === values.comments ) ? 'true' : 'false';

				if ( 'visual' === values.layout ) {
					values.visual = 'true';
					if ( ! values.height || '' === values.height ) {
						values.height = '450';
					}
				} else {
					values.visual = 'false';
					if ( ! values.height || '' === values.height ) {
						values.height = '166';
					}
				}

				values.height = parseInt( values.height, 10 );

				values.show_related = ( 'yes' === values.show_related ) ? 'false' : 'true';
				values.show_reposts = ( 'yes' === values.show_reposts ) ? 'true' : 'false';
				values.show_user    = ( 'yes' === values.show_user ) ? 'true' : 'false';

				if ( values.color ) {
					values.color = jQuery.Color( values.color ).toHexString();
					values.color = values.color.replace( '#', '' );
				}
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {

				// Attributes.
				var attr = {
					class: 'fusion-soundcloud'
				};
				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}
				if ( '' !== values.id ) {
					attr.id = values.id;
				}
				attr = _.fusionVisibilityAtts( values.hide_on_mobile, attr );

				return attr;
			}
		} );
	} );
}( jQuery ) );
