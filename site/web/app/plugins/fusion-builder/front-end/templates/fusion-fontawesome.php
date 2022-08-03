<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_fontawesome-shortcode">
<# if ( alignment ) { #>
<div class="fusion-fa-align-{{ alignment }}">
<# } #>
<# if ( hasLink ) { #>
<a {{{ _.fusionGetAttributes( attr ) }}}>{{{ FusionPageBuilderApp.renderContent( output, cid, false ) }}}</a>
<# } else { #>
<i {{{ _.fusionGetAttributes( attr ) }}}>{{{ FusionPageBuilderApp.renderContent( output, cid, false ) }}}</i>
<# } #>
<# if ( alignment ) { #>
</div>
<# } #>
{{{ styleBlock }}}
</script>
