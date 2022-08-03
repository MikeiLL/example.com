<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_checklist-shortcode">
<# if ( 'yes' === values.divider ) { #>
	<style type="text/css">.fusion-checklist-{{ cid }}.fusion-checklist-divider .fusion-li-item { border-bottom-color:{{ values.divider_color }} !important ;}</style>
<# } #>
<ul {{{ _.fusionGetAttributes( checklistShortcode ) }}}></ul>
</script>

<script type="text/html" id="tmpl-fusion_li_item-shortcode">
<span {{{ _.fusionGetAttributes( checklistShortcodeSpan ) }}}>
	<i {{{ _.fusionGetAttributes( checklistShortcodeIcon ) }}}></i>
</span>
<div {{{ _.fusionGetAttributes( checklistShortcodeItemContent ) }}}>{{{ FusionPageBuilderApp.renderContent( output, cid, false ) }}}</div>
</script>
