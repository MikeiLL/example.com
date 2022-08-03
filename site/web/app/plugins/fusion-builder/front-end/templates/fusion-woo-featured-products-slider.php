<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_featured_products_slider-shortcode">
	<# if ( product_list ) { #>
		<div {{{ _.fusionGetAttributes( wooFeaturedProductsSliderShortcode ) }}}>
			<div {{{ _.fusionGetAttributes( wooFeaturedProductsSliderShortcodeCarousel ) }}}>
				<div class="fusion-carousel-positioner">
				<ul class="fusion-carousel-holder">
					{{{ product_list }}}
				</ul>
				<# if ( 'yes' === show_nav ) { #>
					<div class="fusion-carousel-nav"><span class="fusion-nav-prev"></span><span class="fusion-nav-next"></span></div>
				<# } #>
				</div>
			</div>
		</div>
	<# } else if ( placeholder ) { #>
		{{{ placeholder }}}
	<# } #>
</script>
