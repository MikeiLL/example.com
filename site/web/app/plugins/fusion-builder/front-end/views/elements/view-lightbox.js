var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Lightbox View.
		FusionPageBuilder.fusion_lightbox = FusionPageBuilder.ElementView.extend( {

			/**
			 * Runs on render.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			onRender: function() {
				this.afterPatch();
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				var item = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el.find( '[data-rel="iLightbox"]' ) );

				if ( 'object' === typeof jQuery( '#fb-preview' )[ 0 ].contentWindow.avadaLightBox ) {
					if ( 'undefined' !== typeof this.iLightbox ) {
						this.iLightbox.destroy();
					}

					if ( item.length ) {
						this.iLightbox = item.iLightBox( jQuery( '#fb-preview' )[ 0 ].contentWindow.avadaLightBox.prepare_options( 'single' ) );
					}
				}
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {

				// Create attribute objects.
				atts.name   = atts.params.alt_text;
				atts.label  = window.fusionAllElements[ this.model.get( 'element_type' ) ].name;
				atts.icon   = window.fusionAllElements[ this.model.get( 'element_type' ) ].icon;

				return atts;
			}

		} );
	} );

	_.extend( FusionPageBuilder.Callback.prototype, {
		lightboxShortcodeFilter: function( attributes, view ) {

			var lightbox      = view.$el,
				id            = attributes.params.id,
				className     = attributes.params[ 'class' ],
				title         = attributes.params.title,
				description   = attributes.params.description,
				type          = lightbox.find( '#type' ).val(),
				href          = ( '' === type || 'undefined' === typeof type ) ? lightbox.find( '#full_image' ).val() : lightbox.find( '#video_url' ).val(),
				src           = lightbox.find( '#thumbnail_image' ).val(),
				alt           = attributes.params.alt_text,
				dataRel       = ( href )  ? ' data-rel="iLightbox"' : '',
				lightboxCode  = '';

			if ( '' !== src ) {
				lightboxCode =  '<a id="' + id + '" class="' + className + '" title="' + title + '" data-title="' + title + '" data-caption="' + description + '"  href="' + href + '"' + dataRel + '><img src="' + src + '" alt="' + alt + '" /></a>';
			}

			attributes.params.element_content = lightboxCode;

			return attributes;
		}
	} );
}( jQuery ) );
