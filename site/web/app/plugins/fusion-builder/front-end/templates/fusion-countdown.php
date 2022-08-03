<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_countdown-shortcode">
<div {{{ _.fusionGetAttributes( wrapperAttributes ) }}} >
	<# if ( styles ) { #>
		<style type="text/css"> {{{ styles }}} </style>
	<# } #>
	<div class="fusion-countdown-heading-wrapper">
		<div {{{ _.fusionGetAttributes( subHeadingAttr ) }}}> {{ subheading_text }} </div>
		<div {{{ _.fusionGetAttributes( headingAttr ) }}}> {{ heading_text }} </div>
	</div>
	<div {{{ _.fusionGetAttributes( counterAttributes ) }}}>
		{{{ dashhtml }}}
	</div>

	<# if ( link_url ) { #>
		<div>
			<a {{{ _.fusionGetAttributes( countdownShortcodeLink ) }}}> {{ link_text }} </a>
		</div>
	<# } #>
	{{{ FusionPageBuilderApp.renderContent( element_content, cid, false ) }}}
</div>
</script>
