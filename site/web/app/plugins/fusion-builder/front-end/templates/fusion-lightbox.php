<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_lightbox-shortcode">
<#
var values = jQuery.extend( true, {}, fusionAllElements.fusion_lightbox.defaults, _.fusionCleanParameters( params ) );
#>
<# if ( 'undefined' === typeof values.thumbnail_image ) { #>
<div class="fusion-builder-placeholder-preview">
	<i class="{{ icon }}" aria-hidden="true"></i> {{ label }} ({{ name }})
</div>
<# } #>
{{{ values.element_content }}}
</script>
