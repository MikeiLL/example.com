<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<#
var fieldId  = 'undefined' === typeof param.param_name ? param.id : param.param_name,
	location = ( param.location ) ? param.location : '';
#>
<# if ( 'undefined' !== typeof FusionApp ) { #>
	<div class="fusion-colorpicker-container">
		<input
			id="{{ fieldId }}"
			name="{{ fieldId }}"
			class="fusion-builder-color-picker-hex color-picker"
			type="text"
			value="{{ option_value }}"
			data-alpha="false"
			data-location="{{ location }}"
			<# if ( param.default ) { #>
				data-default="{{ param.default }}"
			<# } #>
		/>
		<span class="wp-picker-input-container">
			<label>
				<input name="{{ fieldId }}" class="{{ fieldId }} color-picker color-picker-placeholder" type="text" value="{{ option_value }}">
			</label>
		</span>
		<button class="button button-small wp-picker-clear"><i class="fusiona-eraser-solid" aria-hidden="true"></i></button>
	</div>
<# } else { #>
	<input
		id="{{ fieldId }}"
		name="{{ fieldId }}"
		class="fusion-builder-color-picker-hex color-picker"
		type="text"
		value="{{ option_value }}"
		data-alpha="false"
		data-location="{{ location }}"
		<# if ( param.default ) { #>
			data-default="{{ param.default }}"
		<# } #>
	/>
<# } #>
