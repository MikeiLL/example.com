/* global fusionAllElements */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Counter box parent View
		FusionPageBuilder.fusion_counters_box = FusionPageBuilder.ParentElementView.extend( {

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {

				this.appendChildren( '.fusion-counters-box' );

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
				var countersBoxShortcode;

				// Validate values.
				this.validateValues( atts.values, atts.params );

				countersBoxShortcode = this.buildAtts( atts.values );

				// Reset attribute objet.
				atts = {};

				// Recreate attribute object.
				atts.countersBoxShortcode = countersBoxShortcode;

				return atts;
			},

			/**
			 * Modifies values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @param {Object} params - The parameters.
			 * @return {void}
			 */
			validateValues: function( values, params ) {
				values = jQuery.extend( true, {}, fusionAllElements.fusion_counters_box.defaults, _.fusionCleanParameters( params ) );

				values.title_size = _.fusionValidateAttrValue( values.title_size, '' );
				values.icon_size  = _.fusionValidateAttrValue( values.icon_size, '' );
				values.body_size  = _.fusionValidateAttrValue( values.body_size, '' );
				values.columns    = Math.min( 6, values.columns );
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildAtts: function( values ) {
				var countersBoxShortcode = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'fusion-counters-box counters-box row fusion-clearfix fusion-columns-' + values.columns
				} );

				if ( '' !== values[ 'class' ] ) {
					countersBoxShortcode[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					countersBoxShortcode.id += ' ' + values.id;
				}
				countersBoxShortcode[ 'class' ] += ' fusion-child-element';
				countersBoxShortcode[ 'data-empty' ] = this.emptyPlaceholderText;

				return countersBoxShortcode;
			}

		} );
	} );
}( jQuery ) );
