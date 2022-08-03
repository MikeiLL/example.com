var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Counter circles parent View
		FusionPageBuilder.fusion_counters_circle = FusionPageBuilder.ParentElementView.extend( {

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				this.appendChildren( '.fusion-counters-circle' );

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
				var countersCircleAtts = this.computeAtts( atts.values );

				atts = {};
				atts.countersCircleAtts = countersCircleAtts;

				return atts;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			computeAtts: function( values ) {
				var countersCircleAtts = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'fusion-counters-circle counters-circle'
				} );

				if ( '' !== values[ 'class' ] ) {
					countersCircleAtts[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					countersCircleAtts.id += ' ' + values.id;
				}

				countersCircleAtts[ 'class' ] += ' fusion-child-element';

				countersCircleAtts[ 'data-empty' ] = this.emptyPlaceholderText;

				return countersCircleAtts;
			}

		} );
	} );
}( jQuery ) );
