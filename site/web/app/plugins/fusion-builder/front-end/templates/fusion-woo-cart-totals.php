<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_woo_cart_totals-shortcode">
	{{{styles}}}
	<div {{{ _.fusionGetAttributes( wooCartTotalsWrapper ) }}}>
	<table {{{ _.fusionGetAttributes( wooCartTotals ) }}} cellspacing="0">
	{{{ cart_totals }}}
	</div>
</script>
