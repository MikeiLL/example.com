<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_highlight' ) ) {

	if ( ! class_exists( 'FusionSC_Highlight' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Highlight extends Fusion_Element {

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
				add_filter( 'fusion_attr_highlight-shortcode', [ $this, 'attr' ] );
				add_shortcode( 'fusion_highlight', [ $this, 'render' ] );
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
					'class'      => '',
					'id'         => '',
					'color'      => $fusion_settings->get( 'primary_color' ),
					'text_color' => '',
					'rounded'    => 'no',
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
					'primary_color' => 'color',
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

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_highlight' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_highlight', $args );

				$this->args = $defaults;

				$html = '<span ' . FusionBuilder::attributes( 'highlight-shortcode' ) . '>' . do_shortcode( $content ) . '</span>';

				$this->on_render();

				return apply_filters( 'fusion_element_highlight_content', $html, $args );

			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/highlight.min.css' );
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
					'class' => 'fusion-highlight',
				];

				if ( $this->args['text_color'] ) {
					$attr['class'] .= ' custom-textcolor';
				} else {
					$brightness_level = Fusion_Color::new_color( $this->args['color'] )->brightness;
					$attr['class']   .= ( $brightness_level['total'] > 140 ) ? ' light' : ' dark';
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( 'yes' === $this->args['rounded'] ) {
					$attr['class'] .= ' rounded';
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				if ( 'black' === $this->args['color'] ) {
					$attr['class'] .= ' highlight2';
				} else {
					$attr['class'] .= ' highlight1';
				}

				$attr['style'] = 'background-color:' . $this->args['color'] . ';';
				if ( $this->args['text_color'] ) {
					$attr['style'] .= 'color:' . $this->args['text_color'] . ';';
				}

				return $attr;
			}
		}
	}

	new FusionSC_Highlight();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 1.0
 */
function fusion_element_highlight() {

	global $fusion_settings;

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Highlight',
			[
				'name'           => esc_attr__( 'Highlight', 'fusion-builder' ),
				'shortcode'      => 'fusion_highlight',
				'icon'           => 'fusiona-H',
				'generator_only' => true,
				'help_url'       => 'https://theme-fusion.com/documentation/fusion-builder/elements/highlight-element/',
				'params'         => [
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Highlight Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Pick a highlight color.', 'fusion-builder' ),
						'param_name'  => 'color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'primary_color' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Highlight Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Pick a text-color for your highlight. Leave empty to use an auto-calculated value.', 'fusion-builder' ),
						'param_name'  => 'text_color',
						'value'       => '',
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Highlight With Round Edges', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to have rounded edges.', 'fusion-builder' ),
						'param_name'  => 'rounded',
						'value'       => [
							'no'  => __( 'No', 'fusion-builder' ),
							'yes' => __( 'Yes', 'fusion-builder' ),
						],
						'default'     => 'no',
					],
					[
						'type'        => 'textarea',
						'heading'     => esc_attr__( 'Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter some text to highlight.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '',
					],
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
add_action( 'fusion_builder_before_init', 'fusion_element_highlight' );
