<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_social_links-shortcode">
<# if ( '' !== alignment ) { #>
	<div class="align{{{ alignment }}}">
<# } #>
<div {{{ _.fusionGetAttributes( socialLinksShortcode ) }}} >
	<div {{{ _.fusionGetAttributes( socialLinksShortcodeSocialNetworks ) }}}>
		<div class="fusion-social-networks-wrapper">
			{{{ icons }}}
		</div>
	</div>
</div>
<# if ( '' !== alignment ) { #>
	</div>
<# } #>
</script>
