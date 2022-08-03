<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_progress' ) ) {

	if ( ! class_exists( 'FusionSC_Progressbar' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Progressbar extends Fusion_Element {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $args = [];

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_progressbar-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_progressbar-shortcode-bar', [ $this, 'bar_attr' ] );
				add_filter( 'fusion_attr_progressbar-shortcode-content', [ $this, 'content_attr' ] );
				add_filter( 'fusion_attr_progressbar-shortcode-span', [ $this, 'span_attr' ] );

				add_shortcode( 'fusion_progress', [ $this, 'render' ] );

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

				global $fusion_settings;

				return [
					'hide_on_mobile'    => fusion_builder_default_visibility( 'string' ),
					'class'             => '',
					'id'                => '',
					'animated_stripes'  => 'no',
					'filledcolor'       => $fusion_settings->get( 'progressbar_filled_color' ),
					'height'            => $fusion_settings->get( 'progressbar_height' ),
					'percentage'        => '70',
					'show_percentage'   => 'yes',
					'striped'           => 'no',
					'textcolor'         => $fusion_settings->get( 'progressbar_text_color' ),
					'text_position'     => $fusion_settings->get( 'progressbar_text_position' ),
					'unfilledcolor'     => $fusion_settings->get( 'progressbar_unfilled_color' ),
					'unit'              => '',
					'filledbordercolor' => $fusion_settings->get( 'progressbar_filled_border_color' ),
					'filledbordersize'  => $fusion_settings->get( 'progressbar_filled_border_size' ),
				];
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
					'progressbar_filled_color'        => 'filledcolor',
					'progressbar_height'              => 'height',
					'progressbar_text_color'          => 'textcolor',
					'progressbar_text_position'       => 'text_position',
					'progressbar_unfilled_color'      => 'unfilledcolor',
					'progressbar_filled_border_color' => 'filledbordercolor',
					'progressbar_filled_border_size'  => 'filledbordersize',
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

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_progress' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_progress', $args );

				$defaults['filledbordersize'] = FusionBuilder::validate_shortcode_attr_value( $defaults['filledbordersize'], 'px' );

				extract( $defaults );

				$this->args = $defaults;

				$text = '<span ' . FusionBuilder::attributes( 'fusion-progressbar-text' ) . '>' . $content . '</span>';

				$value = '';
				if ( 'yes' === $show_percentage ) {
					$value = '<span ' . FusionBuilder::attributes( 'fusion-progressbar-value' ) . '>' . $percentage . $unit . '</span>';
				}

				$text_wrapper = '<span ' . FusionBuilder::attributes( 'progressbar-shortcode-span' ) . '>' . $text . ' ' . $value . '</span>';

				$bar = '<div ' . FusionBuilder::attributes( 'progressbar-shortcode-bar' ) . '><div ' . FusionBuilder::attributes( 'progressbar-shortcode-content' ) . '></div></div>';

				if ( 'above_bar' === $text_position ) {
					$html = '<div ' . FusionBuilder::attributes( 'progressbar-shortcode' ) . '>' . $text_wrapper . ' ' . $bar . '</div>';
				} else {
					$html = '<div ' . FusionBuilder::attributes( 'progressbar-shortcode' ) . '>' . $bar . ' ' . $text_wrapper . '</div>';
				}

				$this->on_render();

				return apply_filters( 'fusion_element_progress_content', $html, $args );

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$attr = [
					'class' => 'fusion-progressbar',
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( 'above_bar' === $this->args['text_position'] ) {
					$attr['class'] .= ' fusion-progressbar-text-above-bar';
				} elseif ( 'below_bar' === $this->args['text_position'] ) {
					$attr['class'] .= ' fusion-progressbar-text-below-bar';
				} else {
					$attr['class'] .= ' fusion-progressbar-text-on-bar';
				}

				return $attr;

			}

			/**
			 * Builds the bar attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function bar_attr() {

				$attr = [
					'style' => 'background-color:' . $this->args['unfilledcolor'] . ';',
					'class' => 'fusion-progressbar-bar progress-bar',
				];

				if ( $this->args['height'] ) {
					$attr['style'] .= 'height:' . $this->args['height'] . ';';
				}

				if ( 'yes' === $this->args['striped'] ) {
					$attr['class'] .= ' progress-striped';
				}

				if ( 'yes' === $this->args['animated_stripes'] ) {
					$attr['class'] .= ' active';
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;

			}

			/**
			 * Builds the content attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function content_attr() {

				$attr = [
					'class' => 'progress progress-bar-content',
					'style' => 'width:0%;background-color:' . $this->args['filledcolor'] . ';',
				];

				if ( $this->args['filledbordersize'] && $this->args['filledbordercolor'] ) {
					$attr['style'] .= 'border: ' . $this->args['filledbordersize'] . ' solid ' . $this->args['filledbordercolor'] . ';';
				}

				$attr['role']          = 'progressbar';
				$attr['aria-valuemin'] = '0';
				$attr['aria-valuemax'] = '100';

				$attr['aria-valuenow'] = $this->args['percentage'];

				return $attr;

			}

			/**
			 * Builds the span attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function span_attr() {
				return [
					'class' => 'progress-title',
					'style' => 'color:' . $this->args['textcolor'] . ';',
				];
			}

			/**
			 * Builds the dynamic styling.
			 *
			 * @access protected
			 * @since 1.1
			 * @return array
			 */
			protected function add_styling() {

				global $wp_version, $content_media_query, $six_fourty_media_query, $three_twenty_six_fourty_media_query, $ipad_portrait_media_query, $fusion_settings, $dynamic_css_helpers;

				$main_elements = apply_filters( 'fusion_builder_element_classes', [ '.fusion-progressbar-bar' ], '.fusion-progressbar-bar' );

				$elements = $dynamic_css_helpers->map_selector( $main_elements, ' .progress-bar-content' );
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = fusion_library()->sanitize->color( $fusion_settings->get( 'counter_filled_color' ) );
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color']     = fusion_library()->sanitize->color( $fusion_settings->get( 'counter_filled_color' ) );

				$css['global'][ $dynamic_css_helpers->implode( $main_elements ) ]['background-color'] = fusion_library()->sanitize->color( $fusion_settings->get( 'counter_unfilled_color' ) );
				$css['global'][ $dynamic_css_helpers->implode( $main_elements ) ]['border-color']     = fusion_library()->sanitize->color( $fusion_settings->get( 'counter_unfilled_color' ) );

				$css[ $content_media_query ]['.fusion-progressbar']['margin-bottom']                 = '10px !important';
				$css[ $six_fourty_media_query ]['.fusion-progressbar']['margin-bottom']              = '10px !important';
				$css[ $three_twenty_six_fourty_media_query ]['.fusion-progressbar']['margin-bottom'] = '10px !important';
				$css[ $ipad_portrait_media_query ]['.fusion-progressbar']['margin-bottom']           = '10px !important';

				return $css;

			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access protected
			 * @since 1.1
			 * @return array $sections Progress Bar settings.
			 */
			protected function add_options() {

				return [
					'progress_shortcode_section' => [
						'label'       => esc_html__( 'Progress Bar', 'fusion-builder' ),
						'description' => '',
						'id'          => 'progress_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-tasks',
						'fields'      => [
							'progressbar_height'         => [
								'label'       => esc_html__( 'Progress Bar Height', 'fusion-builder' ),
								'description' => esc_html__( 'Insert a height for the progress bar.', 'fusion-builder' ),
								'id'          => 'progressbar_height',
								'default'     => '48px',
								'type'        => 'dimension',
								'transport'   => 'postMessage',
							],
							'progressbar_text_position'  => [
								'label'       => esc_html__( 'Progress Bar Text Position', 'fusion-builder' ),
								'description' => esc_html__( 'Select the position of the progress bar text. Choose "Default" for Global Options selection.', 'fusion-builder' ),
								'id'          => 'progressbar_text_position',
								'default'     => 'on_bar',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'on_bar'    => esc_html__( 'On Bar', 'fusion-builder' ),
									'above_bar' => esc_html__( 'Above Bar', 'fusion-builder' ),
									'below_bar' => esc_html__( 'Below Bar', 'fusion-builder' ),
								],
							],
							'progressbar_filled_color'   => [
								'label'       => esc_html__( 'Progress Bar Filled Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the progress bar filled area.', 'fusion-builder' ),
								'id'          => 'progressbar_filled_color',
								'default'     => '#65bc7b',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'progressbar_filled_border_color' => [
								'label'       => esc_html__( 'Progress Bar Filled Border Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border color of the progress bar filled area.', 'fusion-builder' ),
								'id'          => 'progressbar_filled_border_color',
								'default'     => '#ffffff',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'progressbar_filled_border_size' => [
								'label'       => esc_html__( 'Progress Bar Filled Border Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border size of the progress bar filled area.', 'fusion-builder' ),
								'id'          => 'progressbar_filled_border_size',
								'default'     => '0',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '0',
									'max'  => '20',
									'step' => '1',
								],
							],
							'progressbar_unfilled_color' => [
								'label'       => esc_html__( 'Progress Bar Unfilled Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the progress bar unfilled area.', 'fusion-builder' ),
								'id'          => 'progressbar_unfilled_color',
								'default'     => '#f2f3f5',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'progressbar_text_color'     => [
								'label'       => esc_html__( 'Progress Bar Text Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the progress bar text.', 'fusion-builder' ),
								'id'          => 'progressbar_text_color',
								'default'     => '#ffffff',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
						],
					],
				];
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access protected
			 * @since 3.2
			 * @return void
			 */
			protected function on_first_render() {
				Fusion_Dynamic_JS::enqueue_script(
					'fusion-progress',
					FusionBuilder::$js_folder_url . '/general/fusion-progress.js',
					FusionBuilder::$js_folder_path . '/general/fusion-progress.js',
					[ 'jquery', 'jquery-waypoints', 'fusion-waypoints', 'jquery-appear' ],
					'1',
					true
				);
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/progressbar.min.css' );
			}
		}
	}

	new FusionSC_Progressbar();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_progress() {

	global $fusion_settings;

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Progressbar',
			[
				'name'          => esc_attr__( 'Progress Bar', 'fusion-builder' ),
				'shortcode'     => 'fusion_progress',
				'icon'          => 'fusiona-tasks',
				'preview'       => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-progress-preview.php',
				'preview_id'    => 'fusion-builder-block-module-progress-preview-template',
				'help_url'      => 'https://theme-fusion.com/documentation/fusion-builder/elements/progress-bar-element/',
				'inline_editor' => true,
				'params'        => [
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Progress Bar Height', 'fusion-builder' ),
						'description'      => esc_attr__( 'Insert a height for the progress bar. Enter value including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'dimensions',
						'value'            => [
							'height' => '',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Text Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the position of the progress bar text. Choose "Default" for Global Options selection.', 'fusion-builder' ),
						'param_name'  => 'text_position',
						'value'       => [
							''          => esc_attr__( 'Default', 'fusion-builder' ),
							'on_bar'    => esc_attr__( 'On Bar', 'fusion-builder' ),
							'above_bar' => esc_attr__( 'Above Bar', 'fusion-builder' ),
							'below_bar' => esc_attr__( 'Below Bar', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Display Percentage Value', 'fusion-builder' ),
						'description' => esc_attr__( 'Select if you want the filled area percentage value to be shown.', 'fusion-builder' ),
						'param_name'  => 'show_percentage',
						'value'       => [
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						],
						'default'     => 'yes',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Progress Bar Unit', 'fusion-builder' ),
						'description' => esc_attr__( 'Insert a unit for the progress bar. ex %.', 'fusion-builder' ),
						'param_name'  => 'unit',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'show_percentage',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Filled Area Percentage', 'fusion-builder' ),
						'description' => esc_attr__( 'From 1% to 100%.', 'fusion-builder' ),
						'param_name'  => 'percentage',
						'value'       => '70',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Filled Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the filled in area. ', 'fusion-builder' ),
						'param_name'  => 'filledcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'progressbar_filled_color' ),
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Filled Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'In pixels.', 'fusion-builder' ),
						'param_name'  => 'filledbordersize',
						'value'       => '',
						'min'         => '0',
						'max'         => '20',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'progressbar_filled_border_size' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Filled Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the filled in area. ', 'fusion-builder' ),
						'param_name'  => 'filledbordercolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'progressbar_filled_border_color' ),
						'dependency'  => [
							[
								'element'  => 'filledbordersize',
								'value'    => '0',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Unfilled Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the unfilled in area. ', 'fusion-builder' ),
						'param_name'  => 'unfilledcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'progressbar_unfilled_color' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Striped Filling', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to get the filled area striped.', 'fusion-builder' ),
						'param_name'  => 'striped',
						'value'       => [
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						],
						'default'     => 'no',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Animated Stripes', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to get the the stripes animated.', 'fusion-builder' ),
						'param_name'  => 'animated_stripes',
						'value'       => [
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						],
						'default'     => 'no',
						'dependency'  => [
							[
								'element'  => 'striped',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Progress Bar Text', 'fusion-builder' ),
						'description' => esc_attr__( 'Text will show up on progress bar.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder' => true,
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color. ', 'fusion-builder' ),
						'param_name'  => 'textcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'progressbar_text_color' ),
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
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_progress' );
