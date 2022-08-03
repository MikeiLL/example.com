/* global ajaxurl, fusionAppConfig */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.Assets = Backbone.Model.extend( {

		/**
		 * Init.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		initialize: function() {
			this.webfonts;
			this.webfontsArray;
			this.webfontsGoogleArray;
			this.webfontsStandardArray;
			this.webfontRequest = false;
		},

		/**
		 * Gets the webfonts via AJAX.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		getWebFonts: function() {
			var self = this;

			if ( self.webfonts && self.webfontsArray ) {
				return;
			}

			if ( 'undefined' !== typeof fusionAppConfig && 'object' === typeof fusionAppConfig.fusion_web_fonts ) {
				self.webfonts = fusionAppConfig.fusion_web_fonts;
				self.setFontArrays();
				return;
			}

			if ( false !== self.webfontRequest ) {
				return self.webfontRequest;
			}

			return self.webfontRequest = jQuery.post( ajaxurl, { action: 'fusion_get_webfonts_ajax' }, function( response ) { // eslint-disable-line no-return-assign
				self.webfonts = JSON.parse( response );
				self.setFontArrays();
			} );
		},

		setFontArrays: function() {
			var self = this;

			// Create web font array.
			self.webfontsArray = [];
			_.each( self.webfonts.google, function( font ) {
				self.webfontsArray.push( font.family );
			} );
			self.webfontsGoogleArray = self.webfontsArray;

			self.webfontsStandardArray = [];
			_.each( self.webfonts.standard, function( font ) {
				self.webfontsArray.push( font.family );
				self.webfontsStandardArray.push( font.family );
			} );
		}
	} );

}( jQuery ) );
