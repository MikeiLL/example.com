<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_sharing-shortcode">
{{{styles}}}
<div {{{ _.fusionGetAttributes( shortcodeAttr ) }}}>
	<# if ( 'show' === taglineVisibility && '' !== tagline ) { #>
	<h4 {{{ _.fusionGetAttributes( taglineAttr ) }}}>
		{{{ tagline }}}
	</h4>
	<# } #>
	<div {{{ _.fusionGetAttributes( socialNetworksAttr ) }}}>
		{{{ icons }}}
	</div>
</div>
</script>
