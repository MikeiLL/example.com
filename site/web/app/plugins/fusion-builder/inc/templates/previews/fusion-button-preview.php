<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

$fusion_settings = fusion_get_fusion_settings();

$size         = strtolower( $fusion_settings->get( 'button_size' ) );
$type         = strtolower( $fusion_settings->get( 'button_type' ) );
$gradient_top = $gradient_bottom = $accent_color = $border_color = $border_width = $border_radius = '';

$gradient_top    = fusion_color_needs_adjustment( $fusion_settings->get( 'button_gradient_top_color' ) ) ? '#f8f8f8' : $fusion_settings->get( 'button_gradient_top_color' );
$gradient_bottom = fusion_color_needs_adjustment( $fusion_settings->get( 'button_gradient_bottom_color' ) ) ? '#f8f8f8' : $fusion_settings->get( 'button_gradient_bottom_color' );
$accent_color    = fusion_color_needs_adjustment( $fusion_settings->get( 'button_accent_color' ) ) ? '#f8f8f8' : $fusion_settings->get( 'button_accent_color' );
$border_color    = fusion_color_needs_adjustment( $fusion_settings->get( 'button_border_color' ) ) ? '#f8f8f8' : $fusion_settings->get( 'button_border_color' );
$border_width    = $fusion_settings->get( 'button_border_width' );
$border_radius   = $fusion_settings->get( 'button_border_radius' );
$text_transform  = $fusion_settings->get( 'button_text_transform' );
?>

<script type="text/template" id="fusion-builder-block-module-button-preview-template">

	<#
	var button_style  = '';
	var button_icon   = '';
	var border_radius = '<?php echo esc_attr( $border_radius ); ?>';

	if ( '' !== params.border_radius ) {
		border_radius = params.border_radius;
	}

	if ( '' === params.type ) {
		var button_type = '<?php echo esc_attr( $type ); ?>';
	} else {
		var button_type = params.type;
	}

	if ( '' === params.size || ! params.size ) {
		var button_size = '<?php echo esc_attr( $size ); ?>';
	} else {
		var button_size = params.size;
	}

	if ( 'default' === params.color ) {
		var accent_color      = '<?php echo esc_attr( $accent_color ); ?>';
		var border_color      = '<?php echo esc_attr( $border_color ); ?>';
		var border_width      = '<?php echo esc_attr( $border_width ); ?>';
		var button_background = 'linear-gradient(<?php echo esc_attr( $gradient_top ); ?>, <?php echo esc_attr( $gradient_bottom ); ?>)';

	} else if ( 'custom' === params.color ) {
		var accent_color = ( params.accent_color ) ? params.accent_color : '<?php echo esc_attr( $accent_color ); ?>';
		var accent_color = ( params.border_color ) ? params.border_color : '<?php echo esc_attr( $border_color ); ?>';

		if ( params.border_width ) {
			var border_width = ( -1 === params.border_width.indexOf( 'px' ) ) ? params.border_width + 'px' : params.border_width;
		} else {
			var border_width = '<?php echo esc_attr( $border_width ); ?>';
		}

		var gradient_top = ( params.button_gradient_top_color ) ? params.button_gradient_top_color : '<?php echo esc_attr( $gradient_top ); ?>';
		var gradient_bottom = ( params.button_gradient_bottom_color ) ? params.button_gradient_bottom_color : '<?php echo esc_attr( $gradient_bottom ); ?>';

		if ( '' !== gradient_top && '' !== gradient_bottom ) {
			var button_background = 'linear-gradient(' + gradient_top + ', ' + gradient_bottom + ')';
		} else {
			var button_background = gradient_top;
		}

		if ( ( '' === button_background || ( -1 !== gradient_top.indexOf( 'rgba(255,255,255' ) && -1 !== gradient_bottom.indexOf( 'rgba(255,255,255' ) ) ) && ( '#ffffff' === accent_color || -1 !== accent_color.indexOf( 'rgba(255,255,255' ) ) ) {
			button_background = '#dddddd';
		}

	} else {
		var button_color = params.color;
	}

	if ( 'undefined' !== typeof params.icon && '' !== params.icon ) {
		var button_icon = params.icon;
	} else {
		var button_icon = 'no-icon';
	}

	if ( 'undefined' !== typeof button_icon && -1 === button_icon.trim().indexOf( ' ' ) ) {
		button_icon = 'fa ' + button_icon;
	}

	if ( '' === params.text_transform ) {
		var text_transform = '<?php echo esc_attr( $text_transform ); ?>';
	} else {
		var text_transform = params.text_transform;
	}
	#>

	<#
	if ( 'left' === params.icon_position ) {
		var buttonContent = '<span class="fusion-module-icon ' + button_icon + '"></span>' + params.element_content;
	} else {
		var buttonContent = params.element_content + '<span class="fusion-module-icon ' + button_icon + '" style="margin-left:0.5em;margin-right:0;"></span>';
	}
	#>

	<# if ( 'custom' === params.color || 'default' === params.color ) { #>

		<a class="fusion-button button-default button-{{ button_type }} button-{{ button_size }}" style="background: {{ button_background }}; border-radius: {{ border_radius }}px; border: {{ border_width }} solid {{ border_color }}; color: {{ accent_color }}; text-transform: {{ text_transform }};"><span class="fusion-button-text">{{{ buttonContent }}}</span></a>

	<# } else { #>

		<a class="fusion-button button-default button-{{ button_type }} button-{{ button_size }} button-{{ button_color }}" style="border-radius: {{ border_radius }}px; text-transform: {{ text_transform }};"><span class="fusion-button-text">{{{ buttonContent }}}</span></a>

	<# }#>
</script>
