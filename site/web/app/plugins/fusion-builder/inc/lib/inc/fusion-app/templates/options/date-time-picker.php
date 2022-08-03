<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<# if ( 'undefined' !== typeof FusionApp ) { #>
	<#
	var value = ( '' !== option_value ) ? option_value.split( ' ' ) : '',
		date = ( '' !== value ) ? value[0] : ''
		time = ( '' !== value ) ? value[1] : '';
	#>
	<div class="fusion-datetime">
		<input
			type="hidden"
			data-format="yyyy-MM-dd hh:mm:ss"
			id="{{ param.param_name }}"
			class="fusion-date-time-picker"
			name="{{ param.param_name }}"
			value="{{ option_value }}" />
	</div>

	<div class="fusion-datetime-container">
		<div class="fusion-datetime-datepicker">
			<input
				type="text"
				data-format="yyyy-MM-dd"
				id="fusion-datetime-datepicker"
				class="fusion-date-picker fusion-hide-from-atts"
				value="{{ date }}" />
			<div class="fusion-date-picker-field add-on">
				<i class="fusiona-calendar-plus-o" data-date-icon="fusiona-calendar-plus-o" aria-hidden="true"></i>
			</div>
		</div>

		<div class="fusion-datetime-timepicker">
			<input
				type="text"
				data-format="hh:mm:ss"
				id="fusion-datetime-timepicker"
				class="fusion-time-picker fusion-hide-from-atts"
				value="{{ time }}" />
			<div class="fusion-time-picker-field add-on">
				<i data-time-icon="fusiona-clock" class="fusiona-clock" aria-hidden="true"></i>
			</div>
		</div>
	</div>
<# } else { #>
	<div class="fusion-datetime">
	<input
		type="text"
		data-format="yyyy-MM-dd hh:mm:ss"
		id="{{ param.param_name }}"
		class="fusion-date-time-picker"
		name="{{ param.param_name }}"
		value="{{ option_value }}"
	/>
	<div class="fusion-dt-picker-field add-on" >
		<i data-time-icon="fusiona-clock" data-date-icon="fusiona-calendar-plus-o" aria-hidden="true"></i>
	</div>
</div>
<# } #>
