<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<#
	var fieldId = 'undefined' === typeof param.param_name ? param.id : param.param_name;
#>
<div class="fusion-color-palette-options {{ fieldId }}">
	<ul class="fusion-color-palette-list">
		<#
			var colors = 'undefined' !== typeof option_value && '' !== option_value ? option_value : param.default;

				if ( 'string' === typeof colors ) {
					colors = colors.split( '|' );
				}
		#>
		<# _.each( colors, function( color ) { #>
			<li class="fusion-color-palette-item" data-value="{{ color }}">
				<span style="background-color: {{ color }};"></span>
			</li>
		<# }); #>
	</ul>

	<div class="fusion-palette-colorpicker-container">
		<div class="fusion-colorpicker-container">
			<input type="text" value="" class="fusion-builder-color-picker-hex color-picker fusion-color-palette-color-picker" data-alpha="true" />
			<span class="wp-picker-input-container">
				<label>
					<input class="color-picker color-picker-placeholder" type="text" value="">
				</label>
				<button type="button" class="button button-small wp-picker-clear"><i class="fusiona-eraser-solid" aria-hidden="true"></i></button>
			</span>
			<span class="fusion-colorpicker-icon fusiona-color-dropper"></span>
			<# if ( 'undefined' !== typeof FusionApp ) { #>
				<button class="button button-small wp-picker-clear"><i class="fusiona-eraser-solid" aria-hidden="true"></i></button>
			<# } #>
		</div>
	</div>

	<input class="color-palette-colors" type="hidden" id="{{ fieldId }}" name="{{ fieldId }}" value="{{ option_value }}">
</div>
