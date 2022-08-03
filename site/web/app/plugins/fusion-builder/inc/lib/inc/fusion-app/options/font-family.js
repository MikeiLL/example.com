/* global FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionFontFamilyField = {

	/**
	 * Initialize the font family field.
	 *
	 * @since 2.0.0
	 * @param {Object} $element - The element jQuery object.
	 * @return {void}
	 */
	optionFontFamily: function( $element ) {
		var self = this;

		$element = 'undefined' !== typeof $element && $element.length ? $element : this.$el;
		if ( $element.find( '.wrapper .font-family' ).length ) {
			if ( _.isUndefined( FusionApp.assets ) || _.isUndefined( FusionApp.assets.webfonts ) ) {
				jQuery.when( FusionApp.assets.getWebFonts() ).done( function() {
					self.initAfterWebfontsLoaded( $element );
				} );
			} else {
				this.initAfterWebfontsLoaded( $element );
			}
		}
	}
};
