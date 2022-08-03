( function ( $ ) {
	$( function ( $ ) {
		$('.choicesjs-select').each(function( ){
		// Passing options (with default options)
		const choices = new Choices( $( this )[0], {
			searchEnabled: false,
			searchChoices: false,
			removeItems: true,
			removeItemButton: true,
		});

		})

	} );
} )( jQuery )