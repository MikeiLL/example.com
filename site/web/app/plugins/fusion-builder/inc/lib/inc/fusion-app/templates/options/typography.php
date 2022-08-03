<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<div class="wrapper fusion-builder-typography">

	<# if ( param.default['font-family'] ) { #>
		<# if ( '' == option_value['font-family'] ) { option_value['font-family'] = param.default['font-family']; } #>
		<# if ( param.choices['fonts'] ) { param.fonts = param.choices['fonts']; } #>
		<div class="font-family">
			<h5><?php esc_html_e( 'Font Family', 'fusion-builder' ); ?></h5>

			<div class="fusion-skip-init fusion-select-field<?php echo ( is_rtl() ) ? ' fusion-select-field-rtl' : ''; ?>">
				<div class="fusion-select-preview-wrap">
					<span class="fusion-select-preview">
						<# if ( 'undefined' !== typeof option_value['font-family'] ) { #>
							{{ option_value['font-family'] }}
						<# } else { #>
							<span class="fusion-select-placeholder"><?php esc_attr_e( 'Select Font Family', 'fusion-builder' ); ?></span>
						<# } #>
					</span>
					<div class="fusiona-arrow-down"></div>
				</div>
				<div class="fusion-select-dropdown">
					<div class="fusion-select-search">
						<input type="text" class="fusion-hide-from-atts fusion-dont-update" placeholder="<?php esc_attr_e( 'Search Font Families', 'fusion-builder' ); ?>" />
					</div>
					<div class="fusion-select-options"></div>
				</div>
				<input type="hidden" id="fusion-typography-font-family-{{{ param.id }}}" name="font-family" class="fusion-select-option-value">
			</div>
		</div>

		<div class="font-backup hide-on-standard-fonts fusion-font-backup-wrapper">
			<h5><?php esc_html_e( 'Backup Font', 'fusion-builder' ); ?></h5>

			<div class="fusion-skip-init fusion-select-field<?php echo ( is_rtl() ) ? ' fusion-select-field-rtl' : ''; ?>">
				<div class="fusion-select-preview-wrap">
					<span class="fusion-select-preview">
						<# if ( 'string' === typeof option_value['font-backup'] && '' !== option_value['font-backup'] ) { #>
							{{ option_value['font-backup'] }}
						<# } else { #>
							<span class="fusion-select-placeholder"><?php esc_attr_e( 'Select Backup Font Family', 'fusion-builder' ); ?></span>
						<# } #>
					</span>
					<div class="fusiona-arrow-down"></div>
				</div>
				<div class="fusion-select-dropdown">
					<div class="fusion-select-search">
						<input type="text" class="fusion-hide-from-atts fusion-dont-update" placeholder="<?php esc_attr_e( 'Search Font Families', 'fusion-builder' ); ?>" />
					</div>
					<div class="fusion-select-options"></div>
				</div>
				<input type="hidden" id="fusion-typography-font-backup-{{{ param.id }}}" name="font-backup" class="fusion-select-option-value">
			</div>

		</div>
		<div class="variant fusion-variant-wrapper">
			<h5><?php esc_html_e( 'Variant', 'fusion-builder' ); ?></h5>
			<div class="fusion-typography-select-wrapper">
				<select name="variant" class="variant" id="fusion-typography-variant-{{{ param.id }}}"></select>
				<div class="fusiona-arrow-down"></div>
			</div>
		</div>

	<# } #>

	<# if ( param.default['font-size'] ) { #>
		<div class="font-size">
			<h5><?php esc_html_e( 'Font Size', 'fusion-builder' ); ?></h5>
			<input name="font-size" type="text" value="{{ option_value['font-size'] }}"/>
		</div>
	<# } #>

	<# if ( param.default['line-height'] ) { #>
		<div class="line-height">
			<h5><?php esc_html_e( 'Line Height', 'fusion-builder' ); ?></h5>
			<input name="line-height" type="text" value="{{ option_value['line-height'] }}"/>
		</div>
	<# } #>

	<# if ( param.default['letter-spacing'] ) { #>
		<div class="letter-spacing">
			<h5><?php esc_html_e( 'Letter Spacing', 'fusion-builder' ); ?></h5>
			<input name="letter-spacing" type="text" value="{{ option_value['letter-spacing'] }}"/>
		</div>
	<# } #>

	<# if ( param.default['word-spacing'] ) { #>
		<div class="word-spacing">
			<h5><?php esc_html_e( 'Word Spacing', 'fusion-builder' ); ?></h5>
			<input name="word-spacing" type="text" value="{{ option_value['word-spacing'] }}"/>
		</div>
	<# } #>

	<# if ( param.default['text-align'] ) { #>
		<div class="text-align">
			<h5><?php esc_html_e( 'Text Align', 'fusion-builder' ); ?></h5>
			<input type="radio" value="inherit" name="text-align" id="{{ param.id }}-text-align-inherit" <# if ( option_value['text-align'] === 'inherit' ) { #> checked="checked"<# } #>>
				<label for="{{ param.id }}-text-align-inherit">
					<span class="dashicons dashicons-editor-removeformatting"></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Inherit', 'fusion-builder' ); ?></span>
				</label>
			</input>
			<input type="radio" value="left" name="text-align" id="{{ param.id }}-text-align-left" <# if ( option_value['text-align'] === 'left' ) { #> checked="checked"<# } #>>
				<label for="{{ param.id }}-text-align-left">
					<span class="dashicons dashicons-editor-alignleft"></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Left', 'fusion-builder' ); ?></span>
				</label>
			</input>
			<input type="radio" value="center" name="text-align" id="{{ param.id }}-text-align-center" <# if ( option_value['text-align'] === 'center' ) { #> checked="checked"<# } #>>
				<label for="{{ param.id }}-text-align-center">
					<span class="dashicons dashicons-editor-aligncenter"></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Center', 'fusion-builder' ); ?></span>
				</label>
			</input>
			<input type="radio" value="right" name="text-align" id="{{ param.id }}-text-align-right" <# if ( option_value['text-align'] === 'right' ) { #> checked="checked"<# } #>>
				<label for="{{ param.id }}-text-align-right">
					<span class="dashicons dashicons-editor-alignright"></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Right', 'fusion-builder' ); ?></span>
				</label>
			</input>
			<input type="radio" value="justify" name="text-align" id="{{ param.id }}-text-align-justify" <# if ( option_value['text-align'] === 'justify' ) { #> checked="checked"<# } #>>
				<label for="{{ param.id }}-text-align-justify">
					<span class="dashicons dashicons-editor-justify"></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Justify', 'fusion-builder' ); ?></span>
				</label>
			</input>
		</div>
	<# } #>

	<# if ( param.default['text-transform'] ) { #>
		<div class="text-transform">
			<h5><?php esc_html_e( 'Text Transform', 'fusion-builder' ); ?></h5>
			<select name="text-transform" id="fusion-typography-text-transform-{{{ param.id }}}">
				<option value="none"<# if ( 'none' === option_value['text-transform'] ) { #>selected<# } #>><?php esc_html_e( 'None', 'fusion-builder' ); ?></option>
				<option value="capitalize"<# if ( 'capitalize' === option_value['text-transform'] ) { #>selected<# } #>><?php esc_html_e( 'Capitalize', 'fusion-builder' ); ?></option>
				<option value="uppercase"<# if ( 'uppercase' === option_value['text-transform'] ) { #>selected<# } #>><?php esc_html_e( 'Uppercase', 'fusion-builder' ); ?></option>
				<option value="lowercase"<# if ( 'lowercase' === option_value['text-transform'] ) { #>selected<# } #>><?php esc_html_e( 'Lowercase', 'fusion-builder' ); ?></option>
				<option value="initial"<# if ( 'initial' === option_value['text-transform'] ) { #>selected<# } #>><?php esc_html_e( 'Initial', 'fusion-builder' ); ?></option>
				<option value="inherit"<# if ( 'inherit' === option_value['text-transform'] ) { #>selected<# } #>><?php esc_html_e( 'Inherit', 'fusion-builder' ); ?></option>
			</select>
		</div>
	<# } #>

	<# if ( false !== param.default['color'] && param.default['color'] ) { #>
		<#
		var fieldId = param.id;
		#>
		<# if ( 'undefined' !== typeof FusionApp ) { #>
			<# var location = 'undefined' !== typeof param.location ? param.location : ''; #>
			<div class="fusion-colorpicker-container">
				<h5><?php esc_html_e( 'Font Color', 'fusion-builder' ); ?></h5>
				<input
					id="color"
					name="color"
					class="fusion-builder-color-picker-hex color-picker"
					type="text"
					value="{{ option_value['color'] }}"
					data-alpha="true"
					data-default="{{ param.default['color'] }}"
					data-location="{{ location }}"
				/>
				<span class="wp-picker-input-container">
					<label>
						<input name="{{ fieldId }}" class="{{ fieldId }} color-picker color-picker-placeholder" type="text" value="{{ option_value['color'] }}">
					</label>
					<button class="button button-small wp-picker-clear"><i class="fusiona-eraser-solid" aria-hidden="true"></i></button>
				</span>
				<span class="fusion-colorpicker-icon fusiona-color-dropper"></span>
			</div>
		<# } else { #>
			<input
				id="{{ fieldId }}"
				name="color"
				class="fusion-builder-color-picker-hex color-picker"
				type="text"
				value="{{ option_value['color'] }}"
				data-alpha="true"
				data-default="{{ param.default['color'] }}"
			/>
		<# } #>
	<# } #>

	<# if ( param.default['margin-top'] ) { #>
		<div class="margin-top">
			<h5><?php esc_html_e( 'Margin Top', 'fusion-builder' ); ?></h5>
			<input name="margin-top" type="text" value="{{ option_value['margin-top'] }}"/>
		</div>
	<# } #>

	<# if ( param.default['margin-bottom'] ) { #>
		<div class="margin-bottom">
			<h5><?php esc_html_e( 'Margin Bottom', 'fusion-builder' ); ?></h5>
			<input name="margin-bottom" type="text" value="{{ option_value['margin-bottom'] }}"/>
		</div>
	<# } #>
</div>
