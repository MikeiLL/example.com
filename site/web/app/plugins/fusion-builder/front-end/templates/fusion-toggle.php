<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_accordion-shortcode">
<# if ( '' !== styles ) { #>
	<style type="text/css">{{{ styles }}}</style>
<# } #>
<div {{{ _.fusionGetAttributes( toggleShortcode ) }}}>
	<div {{{ _.fusionGetAttributes( toggleShortcodePanelGroup ) }}}></div>
</div>
</script>
<script type="text/html" id="tmpl-fusion_toggle-shortcode">
<div class="panel-heading">
	<h4 class="panel-title toggle">
		<a {{{ _.fusionGetAttributes( toggleShortcodeDataToggle ) }}}>
			<span class="fusion-toggle-icon-wrapper" aria-hidden="true">
				<i class="fa-fusion-box" aria-hidden="true"></i>
			</span>
			<span {{{ _.fusionGetAttributes( headingAttr ) }}}>
				{{{ title }}}
			</span>
		</a>
	</h4>
</div>
<div {{{ _.fusionGetAttributes( toggleShortcodeCollapse ) }}}>
	<div {{{ _.fusionGetAttributes( contentAttr ) }}}>
		{{{ FusionPageBuilderApp.renderContent( elementContent, cid, false ) }}}
	</div>
</div>
</script>
