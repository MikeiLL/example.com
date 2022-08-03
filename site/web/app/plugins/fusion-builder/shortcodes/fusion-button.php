<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_button' ) ) {

	if ( ! class_exists( 'FusionSC_Button' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Button extends Fusion_Element {

			/**
			 * The button counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $button_counter = 1;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_button-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_button-shortcode-icon-divder', [ $this, 'icon_divider_attr' ] );
				add_filter( 'fusion_attr_button-shortcode-icon', [ $this, 'icon_attr' ] );
				add_filter( 'fusion_attr_button-shortcode-button-text', [ $this, 'button_text_attr' ] );
				add_filter( 'fusion_attr_button-shortcode-container', [ $this, 'container_attr' ] );

				add_shortcode( 'fusion_button', [ $this, 'render' ] );
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = fusion_get_fusion_settings();
				return [
					'button_el_type'                     => 'link',
					'hide_on_mobile'                     => fusion_builder_default_visibility( 'string' ),
					'sticky_display'                     => '',
					'class'                              => '',
					'id'                                 => '',
					'accent_color'                       => ( '' !== $fusion_settings->get( 'button_accent_color' ) ) ? strtolower( $fusion_settings->get( 'button_accent_color' ) ) : '#ffffff',
					'accent_hover_color'                 => ( '' !== $fusion_settings->get( 'button_accent_hover_color' ) ) ? strtolower( $fusion_settings->get( 'button_accent_hover_color' ) ) : '#ffffff',
					'bevel_color'                        => ( '' !== $fusion_settings->get( 'button_bevel_color' ) ) ? strtolower( $fusion_settings->get( 'button_bevel_color' ) ) : '#54770F',
					'border_color'                       => ( '' !== $fusion_settings->get( 'button_border_color' ) ) ? strtolower( $fusion_settings->get( 'button_border_color' ) ) : '#ffffff',
					'border_hover_color'                 => ( '' !== $fusion_settings->get( 'button_border_hover_color' ) ) ? strtolower( $fusion_settings->get( 'button_border_hover_color' ) ) : '#ffffff',
					'border_radius'                      => intval( $fusion_settings->get( 'button_border_radius' ) ) . 'px',
					'border_width'                       => intval( $fusion_settings->get( 'button_border_width' ) ) . 'px',
					'color'                              => 'default',
					'gradient_colors'                    => '',
					'icon'                               => '',
					'icon_divider'                       => 'no',
					'icon_position'                      => 'left',
					'link'                               => '',
					'link_attributes'                    => '',
					'modal'                              => '',
					'size'                               => ( '' !== $fusion_settings->get( 'button_size' ) ) ? strtolower( $fusion_settings->get( 'button_size' ) ) : 'large',
					'margin_bottom'                      => '',
					'margin_left'                        => '',
					'margin_right'                       => '',
					'margin_top'                         => '',
					'stretch'                            => ( '' !== $fusion_settings->get( 'button_span' ) ) ? $fusion_settings->get( 'button_span' ) : 'no',
					'default_stretch_value'              => ( '' !== $fusion_settings->get( 'button_span' ) ) ? $fusion_settings->get( 'button_span' ) : 'no',
					'target'                             => '_self',
					'text_transform'                     => '',
					'title'                              => '',
					'type'                               => ( '' !== $fusion_settings->get( 'button_type' ) ) ? strtolower( $fusion_settings->get( 'button_type' ) ) : 'flat',
					'alignment'                          => '',
					'alignment_medium'                   => '',
					'alignment_small'                    => '',
					'animation_type'                     => '',
					'animation_direction'                => 'down',
					'animation_speed'                    => '',
					'animation_offset'                   => $fusion_settings->get( 'animation_offset' ),

					// Combined in accent_color.
					'icon_color'                         => '',
					'text_color'                         => '',

					// Combined in accent_hover_color.
					'icon_hover_color'                   => '',
					'text_hover_color'                   => '',

					// Combined with gradient_colors.
					'gradient_hover_colors'              => '',

					'button_gradient_top_color'          => ( '' !== $fusion_settings->get( 'button_gradient_top_color' ) ) ? $fusion_settings->get( 'button_gradient_top_color' ) : '#65bc7b',
					'button_gradient_bottom_color'       => ( '' !== $fusion_settings->get( 'button_gradient_bottom_color' ) ) ? $fusion_settings->get( 'button_gradient_bottom_color' ) : '#65bc7b',
					'button_gradient_top_color_hover'    => ( '' !== $fusion_settings->get( 'button_gradient_top_color_hover' ) ) ? $fusion_settings->get( 'button_gradient_top_color_hover' ) : '#5aa86c',
					'button_gradient_bottom_color_hover' => ( '' !== $fusion_settings->get( 'button_gradient_bottom_color_hover' ) ) ? $fusion_settings->get( 'button_gradient_bottom_color_hover' ) : '#5aa86c',
					'button_accent_color'                => ( '' !== $fusion_settings->get( 'button_accent_color' ) ) ? $fusion_settings->get( 'button_accent_color' ) : '#ffffff',
					'button_accent_hover_color'          => ( '' !== $fusion_settings->get( 'button_accent_hover_color' ) ) ? $fusion_settings->get( 'button_accent_hover_color' ) : '#ffffff',
					'button_bevel_color'                 => ( '' !== $fusion_settings->get( 'button_bevel_color' ) ) ? $fusion_settings->get( 'button_bevel_color' ) : '#54770F',

				];
			}

			/**
			 * Used to set any other variables for use on front-end editor template.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function get_element_extras() {
				return apply_filters( 'fusion_button_extras', [] );
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function settings_to_params() {
				return [
					'button_size'                        => [
						'param'    => 'size',
						'callback' => 'toLowerCase',
					],
					'button_type'                        => 'type',
					'button_gradient_top_color'          => 'button_gradient_top_color',
					'button_gradient_bottom_color'       => 'button_gradient_bottom_color',
					'button_gradient_top_color_hover'    => 'button_gradient_top_color_hover',
					'button_gradient_bottom_color_hover' => 'button_gradient_bottom_color_hover',
					'button_accent_color'                => 'accent_color',
					'button_accent_hover_color'          => 'accent_hover_color',
					'button_border_color'                => 'border_color',
					'button_border_hover_color'          => 'border_hover_color',
					'button_bevel_color'                 => 'bevel_color',
					'button_border_width'                => 'border_width',
					'button_border_radius'               => 'border_radius',
					'button_span'                        => 'stretch',
				];
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				global $fusion_settings;
				$this->set_element_id( $this->button_counter );

				$using_default_size = false;
				if ( ( isset( $args['size'] ) && '' === $args['size'] ) || ! isset( $args['size'] ) ) {
					$using_default_size = true;
				}

				$using_default_stretch = false;
				if ( ( isset( $args['stretch'] ) && ( '' === $args['stretch'] || 'default' === $args['stretch'] ) ) || ! isset( $args['stretch'] ) ) {
					$using_default_stretch = true;
				}

				$using_default_type = false;
				if ( ( isset( $args['type'] ) && ( '' === $args['type'] || 'default' === $args['type'] ) ) || ! isset( $args['type'] ) ) {
					$using_default_type = true;
				}

				$using_default_border_width = false;
				if ( ( isset( $args['border_width'] ) && ( '' === $args['border_width'] || 'default' === $args['border_width'] ) ) || ! isset( $args['border_width'] ) ) {
					$using_default_border_width = true;
				}

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_button' );
				$defaults = apply_filters( 'fusion_builder_default_args', $defaults, 'fusion_button', $args );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_button', $args );

				// BC support for old 'gradient_colors' format.
				$button_gradient_top_color    = $defaults['button_gradient_top_color'];
				$button_gradient_bottom_color = $defaults['button_gradient_bottom_color'];

				$button_gradient_top_color_hover    = $defaults['button_gradient_top_color_hover'];
				$button_gradient_bottom_color_hover = $defaults['button_gradient_bottom_color_hover'];

				$defaults['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_bottom'], 'px' );
				$defaults['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_left'], 'px' );
				$defaults['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_right'], 'px' );
				$defaults['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_top'], 'px' );

				if ( empty( $defaults['gradient_colors'] ) ) {
					$defaults['gradient_colors'] = strtolower( $defaults['button_gradient_top_color'] ) . '|' . strtolower( $defaults['button_gradient_bottom_color'] );
				}

				if ( empty( $defaults['gradient_hover_colors'] ) ) {
					$defaults['gradient_hover_colors'] = strtolower( $defaults['button_gradient_top_color_hover'] ) . '|' . strtolower( $defaults['button_gradient_bottom_color_hover'] );
				}

				// Combined variable settings.
				$old_text_color = $defaults['text_color'];

				$defaults['icon_color']       = $defaults['text_color'] = $defaults['accent_color'];
				$defaults['icon_hover_color'] = $defaults['text_hover_color'] = $defaults['accent_hover_color'];

				if ( ! isset( $args['border_color'] ) ) {
					$defaults['border_color'] = $defaults['accent_color'];
				}

				if ( ! isset( $args['border_hover_color'] ) ) {
					$defaults['border_hover_color'] = $defaults['accent_hover_color'];
				}

				if ( $old_text_color ) {
					$defaults['text_color'] = $old_text_color;
				}

				if ( $defaults['modal'] ) {
					$defaults['link'] = '#';
				}

				$defaults['type'] = strtolower( $defaults['type'] );

				// BC compatibility for button shape.
				if ( isset( $args['shape'] ) && ! isset( $args['border_radius'] ) ) {
					$args['shape'] = strtolower( $args['shape'] );

					$button_radius = [
						'square'  => '0px',
						'round'   => '2px',
						'round3d' => '4px',
						'pill'    => '25px',
					];

					if ( '3d' === $defaults['type'] && 'round' === $args['shape'] ) {
						$args['shape'] = 'round3d';
					}

					$defaults['border_radius'] = isset( $button_radius[ $args['shape'] ] ) ? $button_radius[ $args['shape'] ] : $defaults['border_radius'];
				}

				$defaults['border_width']  = FusionBuilder::validate_shortcode_attr_value( $defaults['border_width'], 'px' );
				$defaults['border_radius'] = FusionBuilder::validate_shortcode_attr_value( $defaults['border_radius'], 'px' );

				$defaults['default_size']    = $using_default_size;
				$defaults['default_stretch'] = $using_default_stretch;
				$defaults['default_type']    = $using_default_type;

				extract( $defaults );

				$this->args = $defaults;

				$style_tag = $styles = '';

				// If its custom, default or a custom color scheme.
				if ( ( 'custom' === $color || 'default' === $color || false !== strpos( $color, 'scheme-' ) ) && ( $bevel_color || $accent_color || $accent_hover_color || $border_color || $border_hover_color || $border_width || $gradient_colors ) ) {

					$general_styles = $text_color_styles = $button_3d_styles = $hover_styles = $text_color_hover_styles = $gradient_styles = $gradient_hover_styles = '';

					if ( ( '3d' === $type ) && $bevel_color ) {
						if ( 'small' === $size ) {
							$button_3d_add = 0;
						} elseif ( 'medium' === $size ) {
							$button_3d_add = 1;
						} elseif ( 'large' === $size ) {
							$button_3d_add = 2;
						} elseif ( 'xlarge' === $size ) {
							$button_3d_add = 3;
						}

						$button_3d_shadow_part_1 = 'inset 0px 1px 0px #fff,';

						$button_3d_shadow_part_2 = '0px ' . ( 2 + $button_3d_add ) . 'px 0px ' . $bevel_color . ',';

						$button_3d_shadow_part_3 = '1px ' . ( 4 + $button_3d_add ) . 'px ' . ( 4 + $button_3d_add ) . 'px 3px rgba(0,0,0,0.3)';
						if ( 'small' === $size ) {
							$button_3d_shadow_part_3 = str_replace( '3px', '2px', $button_3d_shadow_part_3 );
						}
						$button_3d_shadow = $button_3d_shadow_part_1 . $button_3d_shadow_part_2 . $button_3d_shadow_part_3;

						$button_3d_styles = '-webkit-box-shadow: ' . $button_3d_shadow . ';-moz-box-shadow: ' . $button_3d_shadow . ';box-shadow: ' . $button_3d_shadow . ';';
					}
					if ( 'default' !== $color ) {
						if ( $old_text_color ) {
							$text_color_styles .= 'color:' . $old_text_color . ';';
						} elseif ( $accent_color ) {
							$text_color_styles .= 'color:' . $accent_color . ';';
						}

						if ( $border_color ) {
							$general_styles .= 'border-color:' . $border_color . ';';
						}

						if ( $old_text_color ) {
							$text_color_hover_styles .= 'color:' . $old_text_color . ';';
						} elseif ( $accent_hover_color ) {
							$text_color_hover_styles .= 'color:' . $accent_hover_color . ';';
						} elseif ( $accent_color ) {
							$text_color_hover_styles .= 'color:' . $accent_color . ';';
						}

						if ( $border_hover_color ) {
							$hover_styles .= 'border-color:' . $border_hover_color . ';';
						} elseif ( $accent_color ) {
							$hover_styles .= 'border-color:' . $accent_color . ';';
						}

						if ( $text_color_styles ) {
							$styles .= '.fusion-button.button-' . $this->element_id . ' .fusion-button-text, .fusion-button.button-' . $this->element_id . ' i {' . $text_color_styles . '}';
						}

						if ( $accent_color ) {
							$styles .= '.fusion-button.button-' . $this->element_id . ' .fusion-button-icon-divider{border-color:' . $accent_color . ';}';
						}

						if ( $text_color_hover_styles ) {
							$styles .= '.fusion-button.button-' . $this->element_id . ':hover .fusion-button-text, .fusion-button.button-' . $this->element_id . ':hover i,.fusion-button.button-' . $this->element_id . ':focus .fusion-button-text, .fusion-button.button-' . $this->element_id . ':focus i,.fusion-button.button-' . $this->element_id . ':active .fusion-button-text, .fusion-button.button-' . $this->element_id . ':active{' . $text_color_hover_styles . '}';
						}

						if ( $accent_hover_color ) {
							$styles .= '.fusion-button.button-' . $this->element_id . ':hover .fusion-button-icon-divider, .fusion-button.button-' . $this->element_id . ':hover .fusion-button-icon-divider, .fusion-button.button-' . $this->element_id . ':active .fusion-button-icon-divider{border-color:' . $accent_hover_color . ';}';
						}
					}

					if ( $border_width && 'custom' === $color && ! $using_default_border_width ) {
						$general_styles .= 'border-width:' . $border_width . ';';
						$hover_styles   .= 'border-width:' . $border_width . ';';
					}

					$general_styles .= 'border-radius:' . $border_radius . ';';

					if ( $hover_styles ) {
						$styles .= '.fusion-button.button-' . $this->element_id . ':hover, .fusion-button.button-' . $this->element_id . ':focus, .fusion-button.button-' . $this->element_id . ':active{' . $hover_styles . '}';
					}

					if ( $general_styles ) {
						$styles .= '.fusion-button.button-' . $this->element_id . ' {' . $general_styles . '}';
					}

					if ( $button_3d_styles ) {
						$styles .= '.fusion-button.button-' . $this->element_id . '.button-3d{' . $button_3d_styles . '}.button-' . $this->element_id . '.button-3d:active{' . $button_3d_styles . '}';
					}

					if ( $gradient_colors && 'default' !== $color ) {
						// Checking for deprecated separators.
						if ( strpos( $gradient_colors, ';' ) ) {
							$grad_colors = explode( ';', $gradient_colors );
						} else {
							$grad_colors = explode( '|', $gradient_colors );
						}

						if ( 1 === count( $grad_colors ) || empty( $grad_colors[1] ) || $grad_colors[0] === $grad_colors[1] ) {
							$gradient_styles = "background: {$grad_colors[0]};";
						} else {
							$gradient_styles =
							"background: {$grad_colors[0]};
							background-image: -webkit-gradient( linear, left bottom, left top, from( {$grad_colors[1]} ), to( {$grad_colors[0]} ) );
							background-image: -webkit-linear-gradient( bottom, {$grad_colors[1]}, {$grad_colors[0]} );
							background-image:   -moz-linear-gradient( bottom, {$grad_colors[1]}, {$grad_colors[0]} );
							background-image:     -o-linear-gradient( bottom, {$grad_colors[1]}, {$grad_colors[0]} );
							background-image: linear-gradient( to top, {$grad_colors[1]}, {$grad_colors[0]} );";
						}

						$styles .= '.fusion-button.button-' . $this->element_id . '{' . $gradient_styles . '}';
					}

					if ( $gradient_hover_colors && 'default' !== $color ) {

						// Checking for deprecated separators.
						if ( strpos( $gradient_hover_colors, ';' ) ) {
							$grad_hover_colors = explode( ';', $gradient_hover_colors );
						} else {
							$grad_hover_colors = explode( '|', $gradient_hover_colors );
						}

						if ( 1 === count( $grad_hover_colors ) || '' === $grad_hover_colors[1] || $grad_hover_colors[0] === $grad_hover_colors[1] ) {
							$gradient_hover_styles = "background: {$grad_hover_colors[0]};";
						} else {
							$gradient_hover_styles .=
							"background: {$grad_hover_colors[0]};
							background-image: -webkit-gradient( linear, left bottom, left top, from( {$grad_hover_colors[1]} ), to( {$grad_hover_colors[0]} ) );
							background-image: -webkit-linear-gradient( bottom, {$grad_hover_colors[1]}, {$grad_hover_colors[0]} );
							background-image:   -moz-linear-gradient( bottom, {$grad_hover_colors[1]}, {$grad_hover_colors[0]} );
							background-image:     -o-linear-gradient( bottom, {$grad_hover_colors[1]}, {$grad_hover_colors[0]} );
							background-image: linear-gradient( to top, {$grad_hover_colors[1]}, {$grad_hover_colors[0]} );";
						}

						$styles .= '.fusion-button.button-' . $this->element_id . ':hover,.button-' . $this->element_id . ':focus,.fusion-button.button-' . $this->element_id . ':active{' . $gradient_hover_styles . '}';
					}
				}

				if ( $text_transform ) {
					$styles .= '.fusion-button.button-' . $this->element_id . ' .fusion-button-text {text-transform:' . $text_transform . ';}';
				}

				if ( $styles ) {
					$style_tag = '<style type="text/css">' . $styles . '</style>';
				}

				$icon_html = '';
				if ( $icon ) {
					$icon_html = '<i ' . FusionBuilder::attributes( 'button-shortcode-icon' ) . '></i>';

					if ( 'yes' === $icon_divider ) {
						$icon_html = '<span ' . FusionBuilder::attributes( 'button-shortcode-icon-divder' ) . '>' . $icon_html . '</span>';
					}
				}

				$button_text = '<span ' . FusionBuilder::attributes( 'button-shortcode-button-text' ) . '>' . do_shortcode( $content ) . '</span>';

				$inner_content = ( 'left' === $icon_position ) ? $icon_html . $button_text : $button_text . $icon_html;

				$html = $style_tag;

				if ( isset( $args['button_el_type'] ) && 'submit' === $args['button_el_type'] ) {
					unset( $this->args['link'] );
					unset( $this->args['target'] );
					$html .= '<button type="submit" ' . FusionBuilder::attributes( 'button-shortcode' ) . ' tabindex="' . $args['tab_index'] . '">' . $inner_content . '</button>';
				} else {
					$html .= '<a ' . FusionBuilder::attributes( 'button-shortcode' ) . '>' . $inner_content . '</a>';
				}

				$html = '<div ' . FusionBuilder::attributes( 'button-shortcode-container' ) . '>' . $html . '</div>';

				$this->button_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_button_content', $html, $args );

			}

			/**
			 * Builds the container attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function container_attr() {
				$attr = [
					'class' => '',
					'style' => '',
				];

				if ( fusion_element_rendering_is_flex() ) {

					if ( ! empty( $this->args['alignment'] ) ) {
						$attr['style'] .= 'text-align:' . $this->args['alignment'] . ';';
					}

					if ( ! empty( $this->args['alignment_medium'] ) && $this->args['alignment'] !== $this->args['alignment_medium'] ) {
						$attr['class'] .= ' md-text-align-' . $this->args['alignment_medium'];
					}

					if ( ! empty( $this->args['alignment_small'] ) && $this->args['alignment'] !== $this->args['alignment_small'] ) {
						$attr['class'] .= ' sm-text-align-' . $this->args['alignment_small'];
					}
				} else {
					$attr['class'] = 'fusion-button-wrapper';
					// Add wrapper to the button for alignment and scoped styling.
					if ( ( ! $this->args['default_stretch'] && 'yes' === $this->args['stretch'] ) || ( $this->args['default_stretch'] && 'yes' === $this->args['default_stretch_value'] ) ) {
						$attr['class'] = ' fusion-align-block';
					} elseif ( $this->args['alignment'] ) {
						$attr['class'] = ' fusion-align' . $this->args['alignment'];
					}
				}

				$attr['class'] = ltrim( $attr['class'] );
				return $attr;
			}



			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {
				$size = 'button-' . $this->args['size'];
				if ( $this->args['default_size'] ) {
					$size = 'fusion-button-default-size';
				}

				$stretch = 'fusion-button-span-' . $this->args['stretch'];
				if ( $this->args['default_stretch'] ) {
					$stretch = 'fusion-button-default-span';
				}

				$type = '';
				if ( $this->args['default_type'] ) {
					$type = 'fusion-button-default-type';
				}

				$attr['class'] = 'fusion-button button-' . $this->args['type'] . ' ' . $size . ' button-' . $this->args['color'] . ' button-' . $this->element_id . ' ' . $stretch . ' ' . $type;

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				$attr['class'] .= Fusion_Builder_Sticky_Visibility_Helper::get_sticky_class( $this->args['sticky_display'] );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				if ( isset( $this->args['target'] ) ) {
					$attr['target'] = $this->args['target'];
					if ( '_blank' === $this->args['target'] ) {
						$attr['rel'] = 'noopener noreferrer';
					} elseif ( 'lightbox' === $this->args['target'] ) {
						$attr['rel'] = 'iLightbox';
					}
				}

				// Add additional, custom link attributes correctly formatted to the anchor.
				$attr = fusion_get_link_attributes( $this->args, $attr );

				if ( isset( $this->args['title'] ) && '' !== $this->args['title'] ) {
					$attr['title'] = $this->args['title'];
				}

				if ( isset( $this->args['link'] ) && '' !== $this->args['link'] ) {
					$attr['href'] = $this->args['link'];
				}

				if ( isset( $this->args['modal'] ) && '' !== $this->args['modal'] ) {
					$attr['data-toggle'] = 'modal';
					$attr['data-target'] = '.fusion-modal.' . $this->args['modal'];
				}

				$attr['style'] = Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				if ( isset( $this->args['class'] ) && '' !== $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( isset( $this->args['id'] ) && '' !== $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function icon_divider_attr() {

				$attr = [];

				$attr['class'] = 'fusion-button-icon-divider button-icon-divider-' . $this->args['icon_position'];

				return $attr;

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function icon_attr() {

				$attr = [
					'class'       => fusion_font_awesome_name_handler( $this->args['icon'] ),
					'aria-hidden' => 'true',
				];

				if ( 'yes' !== $this->args['icon_divider'] ) {
					$attr['class'] .= ' button-icon-' . $this->args['icon_position'];
				}

				if ( $this->args['icon_color'] !== $this->args['accent_color'] ) {
					$attr['style'] = 'color:' . $this->args['icon_color'] . ';';
				}

				return $attr;

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function button_text_attr() {

				$attr = [
					'class' => 'fusion-button-text',
				];

				if ( $this->args['icon'] && 'yes' === $this->args['icon_divider'] ) {
					$attr['class'] = 'fusion-button-text fusion-button-text-' . $this->args['icon_position'];
				}

				return $attr;

			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Button settings.
			 */
			public function add_options() {
				global $fusion_settings, $dynamic_css_helpers;

				$option_name           = Fusion_Settings::get_option_name();
				$main_elements         = apply_filters( 'fusion_builder_element_classes', [ '.fusion-button-default' ], '.fusion-button-default' );
				$all_elements          = array_merge( [ '.fusion-button' ], $main_elements );
				$default_size_selector = apply_filters( 'fusion_builder_element_classes', [ ' .fusion-button-default-size' ], '.fusion-button-default-size' );
				$quantity_elements     = apply_filters( 'fusion_builder_element_classes', [ '.fusion-button-quantity' ], '.fusion-button-quantity' );

				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-all', Fusion_Dynamic_CSS_Helpers::get_elements_string( $all_elements ) );

				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-default-size-default', Fusion_Dynamic_CSS_Helpers::get_elements_string( $default_size_selector ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-default-size-3d', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $default_size_selector, '', '.fusion-button_type-3d' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-default-size-small-default', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $default_size_selector, '', '.fusion-button_size-small' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-default-size-small-3d', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $default_size_selector, '', '.fusion-button_size-small.fusion-button_type-3d' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-default-size-large-default', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $default_size_selector, '', '.fusion-button_size-large' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-default-size-large-3d', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $default_size_selector, '', '.fusion-button_size-large.fusion-button_type-3d' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-default-size-xlarge-default', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $default_size_selector, '', '.fusion-button_size-xlarge' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-default-size-xlarge-3d', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $default_size_selector, '', '.fusion-button_size-xlarge.fusion-button_type-3d' ) ) );

				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-quantity-default', Fusion_Dynamic_CSS_Helpers::get_elements_string( $quantity_elements ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-quantity-small', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $quantity_elements, '', '.fusion-button_size-small' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-quantity-large', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $quantity_elements, '', '.fusion-button_size-large' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-quantity-xlarge', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $quantity_elements, '', '.fusion-button_size-xlarge' ) ) );

				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-default', Fusion_Dynamic_CSS_Helpers::get_elements_string( $main_elements ) );

				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-default:hover .fusion-button-text', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':hover .fusion-button-text', '' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-default:focus .fusion-button-text', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':focus .fusion-button-text', '' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-default:active .fusion-button-text', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':active .fusion-button-text', '' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-default .fusion-button-text', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ' .fusion-button-text', '' ) ) );

				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-gradient', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, '', '.fusion-has-button-gradient' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-hover-gradient', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':hover', '.fusion-has-button-gradient' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-hover', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':hover' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-focus-gradient', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':focus', '.fusion-has-button-gradient' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-focus', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':focus' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-active-gradient', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':active', '.fusion-has-button-gradient' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-active', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':active' ) ) );

				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-visited', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':visited' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-span-yes', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':not(.fusion-button-span-no)', '.fusion-button_span-yes' ) ) );

				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-small-3d-default', Fusion_Dynamic_CSS_Helpers::get_elements_string( array_merge( $dynamic_css_helpers->map_selector( $main_elements, '.button-3d.button-small' ), $dynamic_css_helpers->map_selector( $main_elements, '.button-3d.button-small', '.fusion-button_type-3d' ), $dynamic_css_helpers->map_selector( $main_elements, '.button-small', '.fusion-button_type-3d' ) ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-small-3d-active', Fusion_Dynamic_CSS_Helpers::get_elements_string( array_merge( $dynamic_css_helpers->map_selector( $main_elements, '.button-3d.button-small:active' ), $dynamic_css_helpers->map_selector( $main_elements, '.button-3d.button-small:active', '.fusion-button_type-3d' ), $dynamic_css_helpers->map_selector( $main_elements, '.button-small:active', '.fusion-button_type-3d' ) ) ) );

				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-medium-3d-default', Fusion_Dynamic_CSS_Helpers::get_elements_string( array_merge( $dynamic_css_helpers->map_selector( $main_elements, '.button-3d.button-medium' ), $dynamic_css_helpers->map_selector( $main_elements, '.button-3d.button-medium', '.fusion-button_type-3d' ), $dynamic_css_helpers->map_selector( $main_elements, '.button-medium', '.fusion-button_type-3d' ) ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-medium-3d-active', Fusion_Dynamic_CSS_Helpers::get_elements_string( array_merge( $dynamic_css_helpers->map_selector( $main_elements, '.button-3d.button-medium:active' ), $dynamic_css_helpers->map_selector( $main_elements, '.button-3d.button-medium:active', '.fusion-button_type-3d' ), $dynamic_css_helpers->map_selector( $main_elements, '.button-medium:active', '.fusion-button_type-3d' ) ) ) );

				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-large-3d-default', Fusion_Dynamic_CSS_Helpers::get_elements_string( array_merge( $dynamic_css_helpers->map_selector( $main_elements, '.button-3d.button-large' ), $dynamic_css_helpers->map_selector( $main_elements, '.button-3d.button-large', '.fusion-button_type-3d' ), $dynamic_css_helpers->map_selector( $main_elements, '.button-large', '.fusion-button_type-3d' ) ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-large-3d-active', Fusion_Dynamic_CSS_Helpers::get_elements_string( array_merge( $dynamic_css_helpers->map_selector( $main_elements, '.button-3d.button-large:active' ), $dynamic_css_helpers->map_selector( $main_elements, '.button-3d.button-large:active', '.fusion-button_type-3d' ), $dynamic_css_helpers->map_selector( $main_elements, '.button-large:active', '.fusion-button_type-3d' ) ) ) );

				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-xlarge-3d-default', Fusion_Dynamic_CSS_Helpers::get_elements_string( array_merge( $dynamic_css_helpers->map_selector( $main_elements, '.button-3d.button-xlarge' ), $dynamic_css_helpers->map_selector( $main_elements, '.button-3d.button-xlarge', '.fusion-button_type-3d' ), $dynamic_css_helpers->map_selector( $main_elements, '.button-xlarge', '.fusion-button_type-3d' ) ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-xlarge-3d-active', Fusion_Dynamic_CSS_Helpers::get_elements_string( array_merge( $dynamic_css_helpers->map_selector( $main_elements, '.button-3d.button-xlarge:active' ), $dynamic_css_helpers->map_selector( $main_elements, '.button-3d.button-xlarge:active', '.fusion-button_type-3d' ), $dynamic_css_helpers->map_selector( $main_elements, '.button-xlarge:active', '.fusion-button_type-3d' ) ) ) );

				return [
					'button_shortcode_section' => [
						'label'  => esc_html__( 'Button', 'fusion-builder' ),
						'id'     => 'button_shortcode_section',
						'type'   => 'accordion',
						'icon'   => 'fusiona-check-empty',
						'fields' => [
							'button_size'                  => [
								'label'       => esc_html__( 'Button Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the default button size.', 'fusion-builder' ),
								'id'          => 'button_size',
								'default'     => 'Large',
								'type'        => 'radio-buttonset',
								'choices'     => [
									'Small'  => esc_html__( 'Small', 'fusion-builder' ),
									'Medium' => esc_html__( 'Medium', 'fusion-builder' ),
									'Large'  => esc_html__( 'Large', 'fusion-builder' ),
									'XLarge' => esc_html__( 'XLarge', 'fusion-builder' ),
								],
								'output'      => [
									[
										'element'       => 'body',
										'function'      => 'attr',
										'attr'          => 'class',
										'value_pattern' => 'fusion-button_size-$',
										'remove_attrs'  => [ 'fusion-button-small', 'fusion-button_size-medium', 'fusion-button_size-large', 'fusion-button_size-xlarge' ],
										'toLowerCase'   => true,
									],
								],
							],
							'button_span'                  => [
								'label'       => esc_html__( 'Button Span', 'fusion-builder' ),
								'description' => esc_html__( 'Controls if the button spans the full width of its container.', 'fusion-builder' ),
								'id'          => 'button_span',
								'default'     => 'no',
								'type'        => 'radio-buttonset',
								'choices'     => [
									'yes' => esc_html__( 'Yes', 'fusion-builder' ),
									'no'  => esc_html__( 'No', 'fusion-builder' ),
								],
								'output'      => [
									[
										'element'       => 'body',
										'function'      => 'attr',
										'attr'          => 'class',
										'value_pattern' => 'fusion-button_span-$',
										'remove_attrs'  => [ 'fusion-button_span-yes', 'fusion-button_span-no' ],
										'toLowerCase'   => true,
									],
								],
							],
							'button_type'                  => [
								'label'       => esc_html__( 'Button Type', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the default button type.', 'fusion-builder' ),
								'id'          => 'button_type',
								'default'     => 'Flat',
								'type'        => 'radio-buttonset',
								'choices'     => [
									'Flat' => esc_html__( 'Flat', 'fusion-builder' ),
									'3d'   => esc_html__( '3D', 'fusion-builder' ),
								],
								'output'      => [
									[
										'element'       => 'body',
										'function'      => 'attr',
										'attr'          => 'class',
										'value_pattern' => 'fusion-button_type-$',
										'remove_attrs'  => [ 'fusion-button_type-flat', 'fusion-button_type-3d' ],
										'toLowerCase'   => true,
									],
									[
										'element'       => '.fusion-button-default-type',
										'function'      => 'attr',
										'attr'          => 'class',
										'value_pattern' => 'button-$',
										'remove_attrs'  => [ 'button-3d', 'button-flat' ],
										'toLowerCase'   => true,
									],
								],
							],
							'button_typography'            => [
								'id'          => 'button_typography',
								'label'       => esc_html__( 'Button Typography', 'fusion-builder' ),
								'description' => esc_html__( 'These settings control the typography for all button text.', 'fusion-builder' ),
								'type'        => 'typography',
								'choices'     => [
									'font-family'    => true,
									'font-weight'    => true,
									'letter-spacing' => true,
								],
								'default'     => [
									'font-family'    => 'Open Sans',
									'font-weight'    => '600',
									'letter-spacing' => '0',
								],
								'css_vars'    => [
									[
										'name'   => '--button_typography-font-family',
										'choice' => 'font-family',
									],
									[
										'name'     => '--button_typography-font-weight',
										'choice'   => 'font-weight',
										'callback' => [ 'font_weight_no_regular', '' ],
									],
									[
										'name'     => '--button_typography-letter-spacing',
										'choice'   => 'letter-spacing',
										'callback' => [ 'maybe_append_px', '' ],
									],
									[
										'name'   => '--button_typography-font-style',
										'choice' => 'font-style',
									],
								],
							],
							'button_text_transform'        => [
								'label'       => esc_attr__( 'Text Transform', 'fusion-builder' ),
								'description' => esc_attr__( 'Choose how the text is displayed.', 'fusion-builder' ),
								'id'          => 'button_text_transform',
								'default'     => 'none',
								'type'        => 'radio-buttonset',
								'choices'     => [
									'none'      => esc_attr__( 'Normal', 'fusion-builder' ),
									'uppercase' => esc_attr__( 'Uppercase', 'fusion-builder' ),
								],
								'css_vars'    => [
									[
										'name' => '--button_text_transform',
									],
								],
							],
							'button_gradient_top_color'    => [
								'label'       => esc_html__( 'Button Gradient Top Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the top color of the button background.', 'fusion-builder' ),
								'id'          => 'button_gradient_top_color',
								'default'     => '#65bc7b',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--button_gradient_top_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
								'output'      => [
									[
										'element'  => 'helperElement',
										'property' => 'dummy',
										'callback' => [
											'toggle_class',
											[
												'condition' => [ 'button_gradient_bottom_color', 'not-equal-to-option' ],
												'element' => 'body',
												'className' => 'fusion-has-button-gradient',
											],
										],
										'sanitize_callback' => '__return_empty_string',
									],
								],
							],
							'button_gradient_bottom_color' => [
								'label'       => esc_html__( 'Button Gradient Bottom Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the bottom color of the button background.', 'fusion-builder' ),
								'id'          => 'button_gradient_bottom_color',
								'default'     => '#65bc7b',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--button_gradient_bottom_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'button_gradient_top_color_hover' => [
								'label'       => esc_html__( 'Button Gradient Top Hover Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the top hover color of the button background.', 'fusion-builder' ),
								'id'          => 'button_gradient_top_color_hover',
								'default'     => '#5aa86c',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--button_gradient_top_color_hover',
										'callback' => [ 'sanitize_color' ],
									],
								],
								'preview'     => [
									'selector' => '.fusion-button,.fusion-button .wpcf7-submit',
									'type'     => 'class',
									'toggle'   => 'hover',
								],
							],
							'button_gradient_bottom_color_hover' => [
								'label'       => esc_html__( 'Button Gradient Bottom Hover Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the bottom hover color of the button background.', 'fusion-builder' ),
								'id'          => 'button_gradient_bottom_color_hover',
								'default'     => '#5aa86c',
								'type'        => 'color-alpha',
								'preview'     => [
									'selector' => '.fusion-button,.fusion-button .wpcf7-submit',
									'type'     => 'class',
									'toggle'   => 'hover',
								],
								'css_vars'    => [
									[
										'name'     => '--button_gradient_bottom_color_hover',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'button_accent_color'          => [
								'label'       => esc_html__( 'Button Text Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the button text, divider and icon.', 'fusion-builder' ),
								'id'          => 'button_accent_color',
								'default'     => '#ffffff',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--button_accent_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'button_accent_hover_color'    => [
								'label'       => esc_html__( 'Button Text Hover Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the hover color of the button text, divider and icon.', 'fusion-builder' ),
								'id'          => 'button_accent_hover_color',
								'default'     => '#ffffff',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--button_accent_hover_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
								'preview'     => [
									'selector' => '.fusion-button,.fusion-button .wpcf7-submit',
									'type'     => 'class',
									'toggle'   => 'hover',
								],
							],
							'button_bevel_color'           => [
								'label'       => esc_html__( 'Button Bevel Color For 3D Mode', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the bevel color of the buttons when using 3D button type.', 'fusion-builder' ),
								'id'          => 'button_bevel_color',
								'default'     => '#5db072',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--button_bevel_color',
										'callback' => [ 'sanitize_color' ],
									],
									[
										'name'     => '--button_box_shadow',
										'callback' => [
											'conditional_return_value',
											[
												'value_pattern' => [ 'inset 0px 1px 0px #ffffff, 0px 3px 0px $, 1px 5px 5px 3px rgba(0, 0, 0, 0.3)', 'none' ],
												'conditions'    => [
													[ 'button_type', '===', '3d' ],
												],
											],
										],
									],
								],
							],
							'button_border_width'          => [
								'label'       => esc_html__( 'Button Border Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border size for buttons.', 'fusion-builder' ),
								'id'          => 'button_border_width',
								'default'     => '0',
								'type'        => 'slider',
								'choices'     => [
									'min'  => '0',
									'max'  => '20',
									'step' => '1',
								],
								'css_vars'    => [
									[
										'name'          => '--button_border_width',
										'value_pattern' => '$px',
									],
								],
							],
							'button_border_radius'         => [
								'label'       => esc_html__( 'Button Border Radius', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border radius for buttons.', 'fusion-builder' ),
								'id'          => 'button_border_radius',
								'default'     => '4',
								'type'        => 'slider',
								'choices'     => [
									'min'  => '0',
									'max'  => '50',
									'step' => '1',
								],
								'css_vars'    => [
									[
										'name'          => '--button_border_radius',
										'value_pattern' => '$px',
									],
								],
							],
							'button_border_color'          => [
								'label'       => esc_html__( 'Button Border Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border color for buttons.', 'fusion-builder' ),
								'id'          => 'button_border_color',
								'default'     => '#ffffff',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--button_border_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'button_border_hover_color'    => [
								'label'       => esc_html__( 'Button Border Hover Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the hover border color of the button.', 'fusion-builder' ),
								'id'          => 'button_border_hover_color',
								'default'     => '#ffffff',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--button_border_hover_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
						],
					],
				];
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function on_first_render() {

				Fusion_Dynamic_JS::enqueue_script( 'fusion-button' );
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/button.min.css' );
			}
		}
	}

	new FusionSC_Button();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_button() {

	global $fusion_settings;

	$standard_schemes = [
		'default'   => esc_attr__( 'Default', 'fusion-builder' ),
		'custom'    => esc_attr__( 'Custom', 'fusion-builder' ),
		'green'     => esc_attr__( 'Green', 'fusion-builder' ),
		'darkgreen' => esc_attr__( 'Dark Green', 'fusion-builder' ),
		'orange'    => esc_attr__( 'Orange', 'fusion-builder' ),
		'blue'      => esc_attr__( 'Blue', 'fusion-builder' ),
		'red'       => esc_attr__( 'Red', 'fusion-builder' ),
		'pink'      => esc_attr__( 'Pink', 'fusion-builder' ),
		'darkgray'  => esc_attr__( 'Dark Gray', 'fusion-builder' ),
		'lightgray' => esc_attr__( 'Light Gray', 'fusion-builder' ),
	];
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Button',
			[
				'name'          => esc_attr__( 'Button', 'fusion-builder' ),
				'shortcode'     => 'fusion_button',
				'icon'          => 'fusiona-check-empty',
				'preview'       => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-button-preview.php',
				'preview_id'    => 'fusion-builder-block-module-button-preview-template',
				'help_url'      => 'https://theme-fusion.com/documentation/fusion-builder/elements/button-element/',
				'inline_editor' => true,
				'params'        => [
					[
						'type'         => 'link_selector',
						'heading'      => esc_attr__( 'Button URL', 'fusion-builder' ),
						'param_name'   => 'link',
						'value'        => '',
						'description'  => esc_attr__( "Add the button's url ex: http://example.com.", 'fusion-builder' ),
						'dynamic_data' => true,
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Button Text', 'fusion-builder' ),
						'param_name'   => 'element_content',
						'value'        => esc_attr__( 'Button Text', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add the text that will display on button.', 'fusion-builder' ),
						'dynamic_data' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Text Transform', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose how the text is displayed.', 'fusion-builder' ),
						'param_name'  => 'text_transform',
						'default'     => '',
						'value'       => [
							''          => esc_attr__( 'Default', 'fusion-builder' ),
							'none'      => esc_attr__( 'Normal', 'fusion-builder' ),
							'uppercase' => esc_attr__( 'Uppercase', 'fusion-builder' ),
						],
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Button Title Attribute', 'fusion-builder' ),
						'param_name'   => 'title',
						'value'        => '',
						'description'  => esc_attr__( 'Set a title attribute for the button link.', 'fusion-builder' ),
						'dynamic_data' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Target', 'fusion-builder' ),
						'description' => esc_attr__( '_self = open in same browser tab, _blank = open in new browser tab.', 'fusion-builder' ),
						'param_name'  => 'target',
						'default'     => '_self',
						'value'       => [
							'_self'    => esc_attr__( '_self', 'fusion-builder' ),
							'_blank'   => esc_attr__( '_blank', 'fusion-builder' ),
							'lightbox' => esc_attr__( 'Lightbox', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Button Additional Attributes', 'fusion-builder' ),
						'param_name'  => 'link_attributes',
						'value'       => '',
						'description' => esc_attr__( "Add additional attributes to the anchor tag. Separate attributes with a whitespace and use single quotes on the values, doubles don't work. If you need to add square brackets, [ ], to your attributes, please use curly brackets, { }, instead. They will be replaced correctly on the frontend. ex: rel='nofollow'.", 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( "Select the button's alignment.", 'fusion-builder' ),
						'param_name'  => 'alignment',
						'default'     => '',
						'responsive'  => [
							'state' => 'large',
						],
						'value'       => [
							''       => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Modal Window Anchor', 'fusion-builder' ),
						'param_name'  => 'modal',
						'value'       => '',
						'description' => __( 'Add the class name of the modal window you want to open on button click. <strong>Note:</strong> The corresponding Modal Element must be added to the same page.', 'fusion-builder' ),
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Button Style', 'fusion-builder' ),
						'description' => esc_attr__( "Select the button's color. Select default or color name for Global Options, or select custom to use advanced color options below.", 'fusion-builder' ),
						'param_name'  => 'color',
						'value'       => [
							'default'   => esc_attr__( 'Default', 'fusion-builder' ),
							'custom'    => esc_attr__( 'Custom', 'fusion-builder' ),
							'green'     => esc_attr__( 'Green', 'fusion-builder' ),
							'darkgreen' => esc_attr__( 'Dark Green', 'fusion-builder' ),
							'orange'    => esc_attr__( 'Orange', 'fusion-builder' ),
							'blue'      => esc_attr__( 'Blue', 'fusion-builder' ),
							'red'       => esc_attr__( 'Red', 'fusion-builder' ),
							'pink'      => esc_attr__( 'Pink', 'fusion-builder' ),
							'darkgray'  => esc_attr__( 'Dark Gray', 'fusion-builder' ),
							'lightgray' => esc_attr__( 'Light Gray', 'fusion-builder' ),
						],
						'default'     => 'default',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Top Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the top color of the button background.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_top_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_top_color' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Bottom Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the bottom color of the button background.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_bottom_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_bottom_color' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Top Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the top hover color of the button background.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_top_color_hover',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_top_color_hover' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'preview'     => [
							'selector' => '.fusion-button',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Bottom Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the bottom hover color of the button background.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_bottom_color_hover',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_bottom_color_hover' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'preview'     => [
							'selector' => '.fusion-button',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the button text, divider and icon.', 'fusion-builder' ),
						'param_name'  => 'accent_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_accent_color' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Accent Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the hover color of the button text, divider and icon.', 'fusion-builder' ),
						'param_name'  => 'accent_hover_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_accent_hover_color' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'preview'     => [
							'selector' => '.fusion-button',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the button type.', 'fusion-builder' ),
						'param_name'  => 'type',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							''     => esc_attr__( 'Default', 'fusion-builder' ),
							'flat' => esc_attr__( 'Flat', 'fusion-builder' ),
							'3d'   => esc_attr__( '3D', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Bevel Color For 3D Mode', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the bevel color of the button when using 3D button type.', 'fusion-builder' ),
						'param_name'  => 'bevel_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_bevel_color' ),
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'flat',
								'operator' => '!=',
							],
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Button Border Size', 'fusion-builder' ),
						'param_name'  => 'border_width',
						'description' => esc_attr__( 'Controls the border size. In pixels.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'min'         => '0',
						'max'         => '20',
						'step'        => '1',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_border_width' ),
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Button Border Radius', 'fusion-builder' ),
						'param_name'  => 'border_radius',
						'description' => esc_attr__( 'Controls the border radius. In pixels.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'min'         => '0',
						'max'         => '50',
						'step'        => '1',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_border_radius' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the button.', 'fusion-builder' ),
						'param_name'  => 'border_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_border_color' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Border Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the hover border color of the button.', 'fusion-builder' ),
						'param_name'  => 'border_hover_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_border_hover_color' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'preview'     => [
							'selector' => '.fusion-button',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the button size.', 'fusion-builder' ),
						'param_name'  => 'size',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'small'  => esc_attr__( 'Small', 'fusion-builder' ),
							'medium' => esc_attr__( 'Medium', 'fusion-builder' ),
							'large'  => esc_attr__( 'Large', 'fusion-builder' ),
							'xlarge' => esc_attr__( 'XLarge', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Span', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls if the button spans the full width of its container.', 'fusion-builder' ),
						'param_name'  => 'stretch',
						'default'     => 'default',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							'default' => esc_attr__( 'Default', 'fusion-builder' ),
							'yes'     => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'      => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					'fusion_margin_placeholder'            => [
						'param_name' => 'margin',
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Icon', 'fusion-builder' ),
						'param_name'  => 'icon',
						'value'       => '',
						'description' => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Icon Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the position of the icon on the button.', 'fusion-builder' ),
						'param_name'  => 'icon_position',
						'value'       => [
							'left'  => esc_attr__( 'Left', 'fusion-builder' ),
							'right' => esc_attr__( 'Right', 'fusion-builder' ),
						],
						'default'     => 'left',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'icon',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Icon Divider', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to display a divider between icon and text.', 'fusion-builder' ),
						'param_name'  => 'icon_divider',
						'default'     => 'no',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'icon',
								'value'    => '',
								'operator' => '!=',
							],
						],
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					'fusion_animation_placeholder'         => [
						'preview_selector' => '.fusion-button',
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
					],
					'fusion_sticky_visibility_placeholder' => [],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_button' );
