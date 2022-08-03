var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Testimonials parent View.
		FusionPageBuilder.fusion_testimonials = FusionPageBuilder.ParentElementView.extend( {

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {

				// TODO: save DOM and apply instead of generating
				this.generateChildElements();

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
				var attributes = {};

				this.validateValues( atts.values );

				attributes.styles         = this.buildStyles( atts.values );
				attributes.attr           = this.buildAttr( atts.values );
				attributes.paginationAttr = this.buildPaginationAttr( atts.values );
				attributes.navigation     = atts.values.navigation;

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
				values.random = ( 'yes' === values.random || '1' === values.random ) ? 1 : 0;

				if ( 'clean' === values.design && '' === values.navigation ) {
					values.navigation = 'yes';
				} else if ( 'classic' === values.design && '' === values.navigation ) {
					values.navigation = 'no';
				}
			},

			buildStyles: function( values ) {
				var styles = '',
					cid = this.model.get( 'cid' );

				styles += '#fusion-testimonials-cid' + cid + ' a{border-color:' + values.textcolor + ';}';
				styles += '#fusion-testimonials-cid' + cid + ' a:hover, #fusion-testimonials-cid' + cid + ' .activeSlide{background-color: ' + values.textcolor + ';}';
				styles += '.fusion-testimonials.' + values.design + '.fusion-testimonials-cid' + cid + ' .author:after{border-top-color:' + values.backgroundcolor + ' !important;}';

				return styles;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'fusion-testimonials ' + values.design + ' fusion-testimonials-cid' + this.model.get( 'cid' ) + ' ' + values[ 'class' ]
				} );

				attr[ 'data-random' ] = values.random;
				attr[ 'data-speed' ]  = values.speed;

				attr.id = values.id;

				return attr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @return {Object}
			 */
			buildPaginationAttr: function() {
				var paginationAttr = {
					class: 'testimonial-pagination',
					id: 'fusion-testimonials-cid' + this.model.get( 'cid' )
				};
				return paginationAttr;
			}

		} );
	} );
}( jQuery ) );
