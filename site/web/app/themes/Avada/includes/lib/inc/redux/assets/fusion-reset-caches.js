/* global fusionReduxResetCaches */
function fusionResetCaches( e ) { // jshint ignore:line
	var data = {
			action: 'fusion_reset_all_caches'
		},
		confirm = window.confirm( fusionReduxResetCaches.confirm );

	e.preventDefault();

	if ( true === confirm ) {
		jQuery( '.spinner.fusion-spinner' ).addClass( 'is-active' );
		jQuery.post( fusionReduxResetCaches.ajaxurl, data, function() {
			jQuery( '.spinner.fusion-spinner' ).removeClass( 'is-active' );
			alert( fusionReduxResetCaches.success ); // jshint ignore: line
		} );
	}
}
