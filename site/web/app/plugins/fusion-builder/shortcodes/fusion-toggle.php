<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_accordion' ) ) {

	if ( ! class_exists( 'FusionSC_Toggle' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Toggle extends Fusion_Element {

			/**
			 * Counter for accordians.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $accordian_counter = 1;

			/**
			 * Counter for collapsed items.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $collapse_counter = 1;

			/**
			 * The ID of the collapsed item.
			 *
			 * @access private
			 * @since 1.0
			 * @var string
			 */
			private $collapse_id;

			/**
			 * Parent SC arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $parent_args;

			/**
			 * Child SC arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $child_args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_toggle-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_toggle-shortcode-panelgroup', [ $this, 'panelgroup_attr' ] );
				add_filter( 'fusion_attr_toggle-shortcode-panel', [ $this, 'panel_attr' ] );
				add_filter( 'fusion_attr_toggle-shortcode-title', [ $this, 'title_attr' ] );
				add_filter( 'fusion_attr_toggle-shortcode-fa-icon', [ $this, 'fa_icon_attr' ] );
				add_filter( 'fusion_attr_toggle-shortcode-data-toggle', [ $this, 'data_toggle_attr' ] );
				add_filter( 'fusion_attr_toggle-shortcode-collapse', [ $this, 'collapse_attr' ] );

				add_shortcode( 'fusion_accordion', [ $this, 'render_parent' ] );
				add_shortcode( 'fusion_toggle', [ $this, 'render_child' ] );

			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @param string $context Whether we want parent or child.
			 * @return array
			 */
			public static function get_element_defaults( $context ) {
				global $fusion_settings;

				$parent = [
					'background_color'          => ( '' !== $fusion_settings->get( 'accordian_background_color' ) ) ? $fusion_settings->get( 'accordian_background_color' ) : '#ffffff',
					'border_color'              => ( '' !== $fusion_settings->get( 'accordian_border_color' ) ) ? $fusion_settings->get( 'accordian_border_color' ) : '#cccccc',
					'border_size'               => intval( $fusion_settings->get( 'accordion_border_size' ) ) . 'px',
					'boxed_mode'                => ( '' !== $fusion_settings->get( 'accordion_boxed_mode' ) ) ? $fusion_settings->get( 'accordion_boxed_mode' ) : 'no',
					'class'                     => '',
					'divider_line'              => $fusion_settings->get( 'accordion_divider_line' ),
					'hide_on_mobile'            => fusion_builder_default_visibility( 'string' ),
					'hover_color'               => ( '' !== $fusion_settings->get( 'accordian_hover_color' ) ) ? $fusion_settings->get( 'accordian_hover_color' ) : fusion_library()->sanitize->color( $fusion_settings->get( 'primary_color' ) ),
					'icon_alignment'            => ( '' !== $fusion_settings->get( 'accordion_icon_align' ) ) ? $fusion_settings->get( 'accordion_icon_align' ) : 'left',
					'icon_boxed_mode'           => ( '' !== $fusion_settings->get( 'accordion_icon_boxed' ) ) ? $fusion_settings->get( 'accordion_icon_boxed' ) : 'no',
					'icon_box_color'            => $fusion_settings->get( 'accordian_inactive_color' ),
					'icon_color'                => ( '' !== $fusion_settings->get( 'accordian_icon_color' ) ) ? $fusion_settings->get( 'accordian_icon_color' ) : '#ffffff',
					'icon_size'                 => ( '' !== $fusion_settings->get( 'accordion_icon_size' ) ) ? $fusion_settings->get( 'accordion_icon_size' ) : '13px',
					'id'                        => '',
					'title_font_size'           => $fusion_settings->get( 'accordion_title_font_size' ),
					'toggle_hover_accent_color' => $fusion_settings->get( 'accordian_active_color' ),
					'type'                      => ( '' !== $fusion_settings->get( 'accordion_type' ) ) ? $fusion_settings->get( 'accordion_type' ) : 'accordions',
				];

				$child = [
					'open'  => 'no',
					'title' => '',
					'class' => '',
					'id'    => '',
				];

				if ( 'parent' === $context ) {
					return $parent;
				} elseif ( 'child' === $context ) {
					return $child;
				}
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @param string $context Whether we want parent or child.
			 * @since 2.0.0
			 * @return array
			 */
			public static function settings_to_params( $context = '' ) {

				$parent = [
					'accordion_divider_line'     => 'divider_line',
					'accordion_boxed_mode'       => 'boxed_mode',
					'accordion_border_size'      => 'border_size',
					'accordian_border_color'     => 'border_color',
					'accordian_background_color' => 'background_color',
					'accordian_hover_color'      => 'hover_color',
					'accordion_type'             => 'type',
				];

				$child = [];

				if ( 'parent' === $context ) {
					return $parent;
				} elseif ( 'child' === $context ) {
					return $child;
				} else {
					return [
						'parent' => $parent,
						'child'  => $child,
					];
				}
			}

			/**
			 * Render the parent shortcode
			 *
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render_parent( $args, $content = '' ) {

				global $fusion_settings;

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'parent' ), $args, 'fusion_accordion' );

				$defaults['border_size'] = FusionBuilder::validate_shortcode_attr_value( $defaults['border_size'], 'px' );
				$defaults['icon_size']   = FusionBuilder::validate_shortcode_attr_value( $defaults['icon_size'], 'px' );

				extract( $defaults );

				$this->parent_args = $defaults;

				$style_tag = $styles = '';

				if ( '1' === $this->parent_args['boxed_mode'] || 1 === $this->parent_args['boxed_mode'] || 'yes' === $this->parent_args['boxed_mode'] ) {

					if ( ! empty( $this->parent_args['hover_color'] ) ) {
						$styles .= '#accordion-' . get_the_ID() . '-' . $this->accordian_counter . ' .fusion-panel:hover, #accordion-' . get_the_ID() . '-' . $this->accordian_counter . ' .fusion-panel.hover{ background-color: ' . $this->parent_args['hover_color'] . ' }';
					}

					$styles .= ' #accordion-' . get_the_ID() . '-' . $this->accordian_counter . ' .fusion-panel {';

					if ( ! empty( $this->parent_args['border_color'] ) ) {
						$styles .= ' border-color:' . $this->parent_args['border_color'] . ';';
					}

					if ( ! empty( $this->parent_args['border_size'] ) ) {
						$styles .= ' border-width:' . $this->parent_args['border_size'] . ';';
					}

					if ( ! empty( $this->parent_args['background_color'] ) ) {
						$styles .= ' background-color:' . $this->parent_args['background_color'] . ';';
					}

					$styles .= ' }';
				}

				if ( ! empty( $this->parent_args['icon_color'] ) ) {
					$styles .= '.fusion-accordian  #accordion-' . get_the_ID() . '-' . $this->accordian_counter . ' .panel-title a .fa-fusion-box{ color: ' . $this->parent_args['icon_color'] . ';}';
				}

				if ( ! empty( $this->parent_args['icon_size'] ) ) {
					$styles .= '.fusion-accordian  #accordion-' . get_the_ID() . '-' . $this->accordian_counter . ' .panel-title a .fa-fusion-box:before{ font-size: ' . $this->parent_args['icon_size'] . '; width: ' . $this->parent_args['icon_size'] . ';}';
				}

				if ( ! empty( $this->parent_args['icon_alignment'] ) && 'right' === $this->parent_args['icon_alignment'] ) {
					$styles .= '.fusion-accordian  #accordion-' . get_the_ID() . '-' . $this->accordian_counter . '.fusion-toggle-icon-right .fusion-toggle-heading{ margin-right: ' . FusionBuilder::validate_shortcode_attr_value( intval( $this->parent_args['icon_size'] ) + 18, 'px' ) . ';}';
				}

				if ( ! empty( $this->parent_args['title_font_size'] ) ) {
					$styles .= '.fusion-accordian  #accordion-' . get_the_ID() . '-' . $this->accordian_counter . ' .panel-title a{font-size:' . FusionBuilder::validate_shortcode_attr_value( $this->parent_args['title_font_size'], 'px' ) . ';}';
				}

				if ( ( '1' === $this->parent_args['icon_boxed_mode'] || 'yes' === $this->parent_args['icon_boxed_mode'] ) && ! empty( $this->parent_args['icon_box_color'] ) ) {
					$icon_box_color = fusion_library()->sanitize->color( $this->parent_args['icon_box_color'] );
					$styles        .= '.fusion-accordian  #accordion-' . get_the_ID() . '-' . $this->accordian_counter . ' .fa-fusion-box { background-color: ' . $icon_box_color . ';border-color: ' . $icon_box_color . ';}';
				}

				if ( ! empty( $this->parent_args['toggle_hover_accent_color'] ) ) {
					$toggle_hover_accent_color = fusion_library()->sanitize->color( $this->parent_args['toggle_hover_accent_color'] );
					$styles                   .= '.fusion-accordian  #accordion-' . get_the_ID() . '-' . $this->accordian_counter . ' .panel-title a:hover, #accordion-' . get_the_ID() . '-' . $this->accordian_counter . ' .fusion-toggle-boxed-mode:hover .panel-title a { color: ' . $toggle_hover_accent_color . ';}';

					if ( '1' === $this->parent_args['icon_boxed_mode'] || 'yes' === $this->parent_args['icon_boxed_mode'] ) {
						$styles .= '.fusion-accordian  #accordion-' . get_the_ID() . '-' . $this->accordian_counter . ' .panel-title .active .fa-fusion-box,';
						$styles .= '.fusion-accordian  #accordion-' . get_the_ID() . '-' . $this->accordian_counter . ' .panel-title a:hover .fa-fusion-box { background-color: ' . $toggle_hover_accent_color . '!important;border-color: ' . $toggle_hover_accent_color . '!important;}';
					} else {
						$styles .= '.fusion-accordian  #accordion-' . get_the_ID() . '-' . $this->accordian_counter . ' .fusion-toggle-boxed-mode:hover .panel-title a .fa-fusion-box{ color: ' . $toggle_hover_accent_color . ';}';
						$styles .= '.fusion-accordian  #accordion-' . get_the_ID() . '-' . $this->accordian_counter . '.fusion-toggle-icon-unboxed .fusion-panel .panel-title a:hover .fa-fusion-box{ color: ' . $toggle_hover_accent_color . ' !important;}';
					}
				}

				if ( $styles ) {

					$style_tag = '<style type="text/css">' . $styles . '</style>';

				}

				$html = sprintf(
					'%s<div %s><div %s>%s</div></div>',
					$style_tag,
					FusionBuilder::attributes( 'toggle-shortcode' ),
					FusionBuilder::attributes( 'toggle-shortcode-panelgroup' ),
					do_shortcode( $content )
				);

				$this->accordian_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_toggles_parent_content', $html, $args );

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$attr = fusion_builder_visibility_atts(
					$this->parent_args['hide_on_mobile'],
					[
						'class' => 'accordian fusion-accordian',
					]
				);

				if ( $this->parent_args['class'] ) {
					$attr['class'] .= ' ' . $this->parent_args['class'];
				}

				if ( $this->parent_args['id'] ) {
					$attr['id'] = $this->parent_args['id'];
				}

				return $attr;

			}

			/**
			 * Builds the panel-group attributes.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function panelgroup_attr() {
				$attr = [
					'class' => 'panel-group',
					'id'    => 'accordion-' . get_the_ID() . '-' . $this->accordian_counter,
				];

				if ( 'right' === $this->parent_args['icon_alignment'] ) {
					$attr['class'] .= ' fusion-toggle-icon-right';
				}

				if ( '0' === $this->parent_args['icon_boxed_mode'] || 0 === $this->parent_args['icon_boxed_mode'] || 'no' === $this->parent_args['icon_boxed_mode'] ) {
					$attr['class'] .= ' fusion-toggle-icon-unboxed';
				}

				return $attr;
			}

			/**
			 * Render the child shortcode.
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render_child( $args, $content = '' ) {

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'child' ), $args, 'fusion_toggle' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_toggle', $args );

				extract( $defaults );

				$this->child_args                 = $defaults;
				$this->child_args['toggle_class'] = '';

				if ( 'yes' === $open ) {
					$this->child_args['toggle_class'] = 'in';
				}

				$this->collapse_id = substr( md5( sprintf( 'collapse-%s-%s-%s', get_the_ID(), $this->accordian_counter, $this->collapse_counter ) ), 15 );

				$html = sprintf(
					'<div %s><div %s><h4 %s><a %s><span %s><i %s></i></span><span %s>%s</span></a></h4></div><div %s><div %s>%s</div></div></div>',
					FusionBuilder::attributes( 'toggle-shortcode-panel' ),
					FusionBuilder::attributes( 'panel-heading' ),
					FusionBuilder::attributes( 'panel-title toggle' ),
					FusionBuilder::attributes( 'toggle-shortcode-data-toggle' ),
					FusionBuilder::attributes(
						'fusion-toggle-icon-wrapper',
						[
							'class'       => 'fusion-toggle-icon-wrapper',
							'aria-hidden' => 'true',
						]
					),
					FusionBuilder::attributes( 'toggle-shortcode-fa-icon' ),
					FusionBuilder::attributes( 'fusion-toggle-heading' ),
					$title,
					FusionBuilder::attributes( 'toggle-shortcode-collapse' ),
					FusionBuilder::attributes( 'panel-body toggle-content fusion-clearfix' ),
					do_shortcode( $content )
				);

				$this->collapse_counter++;

				return apply_filters( 'fusion_element_toggles_child_content', $html, $args );

			}

			/**
			 * Builds the panel attributes.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function panel_attr() {

				$attr = [
					'class' => 'fusion-panel panel-default',
				];

				if ( $this->child_args['class'] ) {
					$attr['class'] .= ' ' . $this->child_args['class'];
				}

				if ( $this->child_args['id'] ) {
					$attr['id'] = $this->child_args['id'];
				}

				if ( '1' === $this->parent_args['boxed_mode'] || 1 === $this->parent_args['boxed_mode'] || 'yes' === $this->parent_args['boxed_mode'] ) {
					$attr['class'] .= ' fusion-toggle-no-divider fusion-toggle-boxed-mode';
				} elseif ( '0' === $this->parent_args['divider_line'] || 0 === $this->parent_args['divider_line'] || 'no' === $this->parent_args['divider_line'] ) {
					$attr['class'] .= ' fusion-toggle-no-divider';
				}

				return $attr;

			}

			/**
			 * Builds the font-awesome icon attributes.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function fa_icon_attr() {
				return [
					'class'       => 'fa-fusion-box',
					'aria-hidden' => 'true',
				];
			}

			/**
			 * Builds the data-toggle attributes.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function data_toggle_attr() {

				$attr = [];

				if ( 'yes' === $this->child_args['open'] ) {
					$attr['class'] = 'active';
				}

				// Accessibility enhancements.
				$attr['aria-expanded'] = ( 'yes' === $this->child_args['open'] ) ? 'true' : 'false';
				$attr['aria-controls'] = $this->collapse_id;
				$attr['role']          = 'button';

				$attr['data-toggle'] = 'collapse';
				if ( 'toggles' !== $this->parent_args['type'] ) {
					$attr['data-parent'] = sprintf( '#accordion-%s-%s', get_the_ID(), $this->accordian_counter );
				}
				$attr['data-target'] = '#' . $this->collapse_id;
				$attr['href']        = '#' . $this->collapse_id;

				return $attr;

			}

			/**
			 * Builds the collapse attributes.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function collapse_attr() {
				return [
					'id'    => $this->collapse_id,
					'class' => 'panel-collapse collapse ' . $this->child_args['toggle_class'],
				];
			}

			/**
			 * Builds the dynamic styling.
			 *
			 * @access public
			 * @since 1.1
			 * @return array
			 */
			public function add_styling() {

				global $fusion_settings, $dynamic_css_helpers;

				$elements = $dynamic_css_helpers->map_selector( apply_filters( 'fusion_builder_element_classes', [ '.fusion-accordian' ], '.fusion-accordian' ), ' .fusion-panel' );
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = fusion_library()->sanitize->color( $fusion_settings->get( 'sep_color' ) );

				return $css;

			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Toggles settings.
			 */
			public function add_options() {

				global $fusion_settings, $dynamic_css_helpers;

				$accordian_active_color_main_elements   = apply_filters( 'fusion_builder_element_classes', [ '.fusion-accordian' ], '.fusion-accordian' );
				$accordian_active_color_color_elements  = array_merge( $dynamic_css_helpers->map_selector( $accordian_active_color_main_elements, ' .panel-title a:hover' ), $dynamic_css_helpers->map_selector( $accordian_active_color_main_elements, ' .fusion-toggle-boxed-mode:hover .panel-title a' ) );
				$accordian_active_color_hover_elements  = $dynamic_css_helpers->map_selector( $accordian_active_color_main_elements, ' .panel-title a:hover .fa-fusion-box' );
				$accordian_active_color_active_elements = $dynamic_css_helpers->map_selector( $accordian_active_color_main_elements, ' .panel-title .active .fa-fusion-box' );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-toggle-inactive-color', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( apply_filters( 'fusion_builder_element_classes', [ '.fusion-accordian' ], '.fusion-accordian' ), ' .panel-title a .fa-fusion-box' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-toggle-active_color_color_elements', Fusion_Dynamic_CSS_Helpers::get_elements_string( $accordian_active_color_color_elements ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-toggle-active_color_hover_elements', Fusion_Dynamic_CSS_Helpers::get_elements_string( $accordian_active_color_hover_elements ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-toggle-active_color_active_elements', Fusion_Dynamic_CSS_Helpers::get_elements_string( $accordian_active_color_active_elements ) );

				return [
					'toggles_shortcode_section' => [
						'label'  => esc_html__( 'Toggles', 'fusion-builder' ),
						'id'     => 'toggles_shortcode_section',
						'type'   => 'accordion',
						'icon'   => 'fusiona-expand-alt',
						'fields' => [
							'accordion_type'             => [
								'label'       => esc_html__( 'Toggles or Accordions', 'fusion-builder' ),
								'description' => esc_html__( 'Toggles allow several items to be open at a time. Accordions only allow one item to be open at a time.', 'fusion-builder' ),
								'id'          => 'accordion_type',
								'default'     => 'accordions',
								'type'        => 'radio-buttonset',
								'choices'     => [
									'toggles'    => esc_html__( 'Toggles', 'fusion-builder' ),
									'accordions' => esc_html__( 'Accordions', 'fusion-builder' ),
								],
							],
							'accordion_boxed_mode'       => [
								'label'       => esc_html__( 'Toggle Boxed Mode', 'fusion-builder' ),
								'description' => esc_html__( 'Turn on to display items in boxed mode. Toggle divider line must be disabled for this option to work.', 'fusion-builder' ),
								'id'          => 'accordion_boxed_mode',
								'default'     => '0',
								'type'        => 'switch',
							],
							'accordion_border_size'      => [
								'label'           => esc_html__( 'Toggle Boxed Mode Border Width', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the border size of the toggle item.', 'fusion-builder' ),
								'id'              => 'accordion_border_size',
								'default'         => '1',
								'type'            => 'slider',
								'soft_dependency' => true,
								'choices'         => [
									'min'  => '0',
									'max'  => '20',
									'step' => '1',
								],
							],
							'accordian_border_color'     => [
								'label'           => esc_html__( 'Toggle Boxed Mode Border Color', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the border color of the toggle item.', 'fusion-builder' ),
								'id'              => 'accordian_border_color',
								'default'         => '#e2e2e2',
								'type'            => 'color-alpha',
								'soft_dependency' => true,
							],
							'accordian_background_color' => [
								'label'           => esc_html__( 'Toggle Boxed Mode Background Color', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the background color of the toggle item.', 'fusion-builder' ),
								'id'              => 'accordian_background_color',
								'default'         => '#ffffff',
								'type'            => 'color-alpha',
								'soft_dependency' => true,
							],
							'accordian_hover_color'      => [
								'label'           => esc_html__( 'Toggle Boxed Mode Background Hover Color', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the background hover color of the toggle item.', 'fusion-builder' ),
								'id'              => 'accordian_hover_color',
								'default'         => '#f9f9fb',
								'type'            => 'color-alpha',
								'soft_dependency' => true,
							],
							'accordion_divider_line'     => [
								'label'           => esc_html__( 'Toggle Divider Line', 'fusion-builder' ),
								'description'     => esc_html__( 'Turn on to display a divider line between each item.', 'fusion-builder' ),
								'id'              => 'accordion_divider_line',
								'default'         => '1',
								'type'            => 'switch',
								'soft_dependency' => true,
							],
							'accordion_title_font_size'  => [
								'label'       => esc_html__( 'Toggle Title Font Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the size of the title text.', 'fusion-builder' ),
								'id'          => 'accordion_title_font_size',
								'default'     => $fusion_settings->get( 'h4_typography', 'font-size' ),
								'type'        => 'dimension',
							],
							'accordion_icon_size'        => [
								'label'       => esc_html__( 'Toggle Icon Size', 'fusion-builder' ),
								'description' => esc_html__( 'Set the size of the icon.', 'fusion-builder' ),
								'id'          => 'accordion_icon_size',
								'default'     => '16',
								'type'        => 'slider',
								'choices'     => [
									'min'  => '1',
									'max'  => '40',
									'step' => '1',
								],
							],
							'accordian_icon_color'       => [
								'label'       => esc_html__( 'Toggle Icon Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of icon in toggle box.', 'fusion-builder' ),
								'id'          => 'accordian_icon_color',
								'default'     => '#ffffff',
								'type'        => 'color-alpha',
							],
							'accordion_icon_boxed'       => [
								'label'       => esc_html__( 'Toggle Icon Boxed Mode', 'fusion-builder' ),
								'description' => esc_html__( 'Turn on to display toggle icon in boxed mode.', 'fusion-builder' ),
								'id'          => 'accordion_icon_boxed',
								'default'     => '1',
								'type'        => 'switch',
							],
							'accordian_inactive_color'   => [
								'label'           => esc_html__( 'Toggle Icon Inactive Box Color', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the color of the inactive toggle box.', 'fusion-builder' ),
								'id'              => 'accordian_inactive_color',
								'default'         => '#212934',
								'type'            => 'color-alpha',
								'soft_dependency' => true,
								'css_vars'        => [
									[
										'name' => '--accordian_inactive_color',
									],
								],
							],
							'accordian_active_color'     => [
								'label'       => esc_html__( 'Toggle Hover Accent Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the accent color on hover for icon box and title.', 'fusion-builder' ),
								'id'          => 'accordian_active_color',
								'default'     => '#65bc7b',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--accordian_active_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'accordion_icon_align'       => [
								'label'       => esc_html__( 'Toggle Icon Alignment', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the alignment of toggle icon.', 'fusion-builder' ),
								'id'          => 'accordion_icon_align',
								'default'     => 'left',
								'type'        => 'radio-buttonset',
								'choices'     => [
									'left'  => esc_html__( 'Left', 'fusion-builder' ),
									'right' => esc_html__( 'Right', 'fusion-builder' ),
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

				Fusion_Dynamic_JS::enqueue_script( 'fusion-toggles' );
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/toggles.min.css' );
			}
		}
	}

	new FusionSC_Toggle();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_accordion() {

	global $fusion_settings;

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Toggle',
			[
				'name'          => esc_attr__( 'Toggles', 'fusion-builder' ),
				'shortcode'     => 'fusion_accordion',
				'multi'         => 'multi_element_parent',
				'element_child' => 'fusion_toggle',
				'icon'          => 'fusiona-expand-alt',
				'preview'       => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-toggles-preview.php',
				'preview_id'    => 'fusion-builder-block-module-toggles-preview-template',
				'help_url'      => 'https://theme-fusion.com/documentation/fusion-builder/elements/toggles-element/',
				'params'        => [
					[
						'type'        => 'tinymce',
						'heading'     => esc_attr__( 'Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter some content for this toggles element.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '[fusion_toggle title="' . esc_attr__( 'Your Content Goes Here', 'fusion-builder' ) . '" open="no" ]' . esc_attr__( 'Your Content Goes Here', 'fusion-builder' ) . '[/fusion_toggle]',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Toggles or Accordions', 'fusion-builder' ),
						'description' => esc_attr__( 'Toggles allow several items to be open at a time. Accordions only allow one item to be open at a time.', 'fusion-builder' ),
						'param_name'  => 'type',
						'value'       => [
							''           => esc_attr__( 'Default', 'fusion-builder' ),
							'toggles'    => esc_attr__( 'Toggles', 'fusion-builder' ),
							'accordions' => esc_attr__( 'Accordions', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Boxed Mode', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to display items in boxed mode.', 'fusion-builder' ),
						'param_name'  => 'boxed_mode',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Boxed Mode Border Width', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the border width for toggle item. In pixels.', 'fusion-builder' ),
						'param_name'  => 'border_size',
						'value'       => $fusion_settings->get( 'accordion_border_size' ),
						'default'     => $fusion_settings->get( 'accordion_border_size' ),
						'min'         => '0',
						'max'         => '20',
						'step'        => '1',
						'dependency'  => [
							[
								'element'  => 'boxed_mode',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Boxed Mode Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the border color for toggle item.', 'fusion-builder' ),
						'param_name'  => 'border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'accordian_border_color' ),
						'dependency'  => [
							[
								'element'  => 'boxed_mode',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'border_size',
								'value'    => '0',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Boxed Mode Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the background color for toggle item.', 'fusion-builder' ),
						'param_name'  => 'background_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'accordian_background_color' ),
						'dependency'  => [
							[
								'element'  => 'boxed_mode',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Boxed Mode Background Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the background hover color for toggle item.', 'fusion-builder' ),
						'param_name'  => 'hover_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'accordian_hover_color' ),
						'dependency'  => [
							[
								'element'  => 'boxed_mode',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
						'preview'     => [
							'selector' => '.fusion-builder-live-child-element,.panel-title>a',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Divider Line', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to display a divider line between each item.', 'fusion-builder' ),
						'param_name'  => 'divider_line',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'boxed_mode',
								'value'    => 'yes',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Title Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the size of the title. Enter value including any valid CSS unit, ex: 13px.', 'fusion-builder' ),
						'param_name'  => 'title_font_size',
						'value'       => '',
					],
					[
						'heading'     => esc_html__( 'Toggle Icon Size', 'fusion-builder' ),
						'description' => esc_html__( 'Set the size of the icon. In pixels (px), ex: 13px.', 'fusion-builder' ),
						'param_name'  => 'icon_size',
						'default'     => $fusion_settings->get( 'accordion_icon_size' ),
						'min'         => '1',
						'max'         => '40',
						'step'        => '1',
						'type'        => 'range',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Toggle Icon Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the color of icon in toggle box.', 'fusion-builder' ),
						'param_name'  => 'icon_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'accordian_icon_color' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Toggle Icon Boxed Mode', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to display icon in boxed mode.', 'fusion-builder' ),
						'param_name'  => 'icon_boxed_mode',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Toggle Icon Inactive Box Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the inactive toggle box.', 'fusion-builder' ),
						'param_name'  => 'icon_box_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'accordian_inactive_color' ),
						'dependency'  => [
							[
								'element'  => 'icon_boxed_mode',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Toggle Icon Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the alignment of toggle icon.', 'fusion-builder' ),
						'param_name'  => 'icon_alignment',
						'value'       => [
							''      => esc_attr__( 'Default', 'fusion-builder' ),
							'left'  => esc_attr__( 'Left', 'fusion-builder' ),
							'right' => esc_attr__( 'Right', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Toggle Hover Accent Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the accent color on hover for icon box and title.', 'fusion-builder' ),
						'param_name'  => 'toggle_hover_accent_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'accordian_active_color' ),
						'preview'     => [
							'selector' => '.panel-title>a,.fusion-toggle-boxed-mode',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],
				],
			],
			'parent'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_accordion' );

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_toggle() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Toggle',
			[
				'name'                     => esc_attr__( 'Toggle', 'fusion-builder' ),
				'shortcode'                => 'fusion_toggle',
				'hide_from_builder'        => true,
				'allow_generator'          => true,
				'inline_editor'            => true,
				'inline_editor_shortcodes' => true,
				'params'                   => [
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Title', 'fusion-builder' ),
						'description'  => esc_attr__( 'Insert the toggle title.', 'fusion-builder' ),
						'param_name'   => 'title',
						'value'        => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Open by Default', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to have the toggle open when page loads.', 'fusion-builder' ),
						'param_name'  => 'open',
						'value'       => [
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						],
						'default'     => 'no',
					],
					[
						'type'         => 'tinymce',
						'heading'      => esc_attr__( 'Toggle Content', 'fusion-builder' ),
						'description'  => esc_attr__( 'Insert the toggle content.', 'fusion-builder' ),
						'param_name'   => 'element_content',
						'value'        => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'description' => esc_attr__( 'Add a class to the wrapping child HTML element.', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Add an ID to the wrapping child HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
					],
				],
			],
			'child'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_toggle' );
