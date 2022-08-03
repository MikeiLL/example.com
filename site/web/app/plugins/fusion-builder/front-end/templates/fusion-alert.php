<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_alert-shortcode">
<div {{{ _.fusionGetAttributes( attr ) }}}>
	{{{ contentStyles }}}
	<div class="fusion-alert-content-wrapper">
		<#
		if ( 'yes' === values.dismissable ) {
		#>
		<button type="button" class="close toggle-alert" data-dismiss="alert" aria-hidden="true" style="{{ buttonStyles }}">&times;</button>
		<# } #>
		<# if ( 'undefined' !== typeof values.icon && 'none' !== values.icon ) { #>
			<span class="alert-icon">
				<i class="fa fa-lg {{ _.fusionFontAwesome( values.icon ) }}" aria-hidden="true"></i>
			</span>
		<# } #>
		<span {{{ _.fusionGetAttributes( contentAttr ) }}}>
			{{{ FusionPageBuilderApp.renderContent( values.element_content, cid, false ) }}}
		</span>
	</div>
</div>
</script>
