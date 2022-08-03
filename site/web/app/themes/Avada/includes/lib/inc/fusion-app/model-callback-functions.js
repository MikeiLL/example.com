/* global FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.Callback = Backbone.Model.extend( {
		fusionOption: function( value, args ) {
			var poValue = false;
			if ( 'object' === typeof args && 'string' === typeof args.id && 'string' === typeof args.type ) {
				if ( 'PO' === args.type && '' !== value ) {
					return value;
				}  else if ( 'PO' === args.type ) {
					return FusionApp.settings[ args.id ];
				}
				poValue = 'undefined' !== typeof FusionApp.data.postMeta._fusion && 'undefined' !== typeof FusionApp.data.postMeta._fusion[ args.id ] ? FusionApp.data.postMeta._fusion[ args.id ] : false;
				if ( poValue && '' !== poValue ) {
					return poValue;
				}
				return value;
			}
			return value;
		}
	} );

}( jQuery ) );
