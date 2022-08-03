var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Progress Bar Element View.
		FusionPageBuilder.fusion_progress = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Validate values.
				this.validateValues( atts.values );

				// Create attribute objects
				attributes.attr        = this.buildAttr( atts.values );
				attributes.attrBar     = this.buildBarAttr( atts.values );
				attributes.attrSpan    = this.buildSpanAttr( atts.values );
				attributes.attrEditor  = this.buildInlineEditorAttr( atts.values );
				attributes.attrContent = this.buildContentAttr( atts.values );

				// Any extras that need passed on.
				attributes.cid    = this.model.get( 'cid' );
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
				values.filledbordersize = _.fusionValidateAttrValue( values.filledbordersize, 'px' );
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
					class: 'fusion-progressbar',
					style: ''
				} );

				if ( 'above_bar' === values.text_position ) {
					attr[ 'class' ] += ' fusion-progressbar-text-above-bar';
				} else if ( 'below_bar' === values.text_position ) {
					attr[ 'class' ] += ' fusion-progressbar-text-below-bar';
				} else {
					attr[ 'class' ] += ' fusion-progressbar-text-on-bar';
				}

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				return attr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @return {Object}
			 */
			buildInlineEditorAttr: function() {
				var attr = {
					class: 'fusion-progressbar-text'
				};

				attr = _.fusionInlineEditor( {
					cid: this.model.get( 'cid' ),
					'disable-return': true,
					'disable-extra-spaces': true,
					toolbar: 'simple'
				}, attr );

				return attr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildContentAttr: function( values ) {
				var attr = {
					class: 'progress progress-bar-content',
					role: 'progressbar',
					style: ''
				};

				attr.style += 'width:' + values.percentage + '%;';
				attr.style += 'background-color:' + values.filledcolor + ';';

				if ( '' !== values.filledbordersize && '' !== values.filledbordercolor ) {
					attr.style += 'border: ' + values.filledbordersize + ' solid ' + values.filledbordercolor + ';';
				}

				return attr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildBarAttr: function( values ) {
				var attr = {
					class: 'fusion-progressbar-bar progress-bar',
					style: ''
				};

				attr.style += 'background-color:' + values.unfilledcolor + ';';

				if ( '' !== values.height ) {
					attr.style += 'height:' + values.height + ';';
				}

				if ( 'yes' === values.striped ) {
					attr[ 'class' ] += ' progress-striped';
				}

				if ( 'yes' === values.animated_stripes ) {
					attr[ 'class' ] += ' active';
				}

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				return attr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildSpanAttr: function( values ) {
				var attr = {
					class: 'progress-title',
					style: ''
				};

				attr.style += 'color:' + values.textcolor + ';';

				return attr;
			}
		} );
	} );
}( jQuery ) );
