/* global FusionPageBuilderApp */
( function( $ ) {

	$( document ).ready( function() {

		// Generate Lightbox shortcode content
		FusionPageBuilderApp.lightboxShortcodeFilter = function( attributes ) {
			var $id           = attributes.params.id,
				$class        = attributes.params[ 'class' ],
				$title        = attributes.params.title,
				$description  = attributes.params.description,
				$href         = ( '' === attributes.params.type ) ? attributes.params.full_image : attributes.params.video_url,
				$src          = attributes.params.thumbnail_image,
				$alt          = attributes.params.alt_text,
				$lightboxCode = '<a id="' + $id + '" class="' + $class + '" title="' + $title + '" data-title="' + $title + '" data-caption="' + $description + '" href="' + $href + '" data-rel="iLightbox"><img src="' + $src + '" alt="' + $alt + '"></a>';

			attributes.params.element_content = $lightboxCode;

			return attributes;
		};
	} );

}( jQuery ) );
