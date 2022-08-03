<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.2
 */

if ( fusion_is_element_enabled( 'fusion_tb_woo_additional_info' ) ) {

	if ( ! class_exists( 'FusionTB_Woo_Additional_Info' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.2
		 */
		class FusionTB_Woo_Additional_Info extends Fusion_Woo_Component {

			/**
			 * An array of the shortcode defaults.
			 *
			 * @access protected
			 * @since 3.2
			 * @var array
			 */
			protected $defaults;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 3.2
			 * @var array
			 */
			protected $args;

			/**
			 * The internal container counter.
			 *
			 * @access private
			 * @since 3.2
			 * @var int
			 */
			private $counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.2
			 */
			public function __construct() {
				parent::__construct( 'fusion_tb_woo_additional_info' );
				add_filter( 'fusion_attr_fusion_tb_woo_additional_info-shortcode', [ $this, 'attr' ] );

				// Ajax mechanism for live editor.
				add_action( 'wp_ajax_get_fusion_tb_woo_additional_info', [ $this, 'ajax_render' ] );
			}


			/**
			 * Check if component should render
			 *
			 * @access public
			 * @since 3.2
			 * @return boolean
			 */
			public function should_render() {
				return is_singular();
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 3.2
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = fusion_get_fusion_settings();
				return [
					'show_tab_title'                   => 'yes',
					'title_size'                       => 'h3',

					// Element margin.
					'margin_top'                       => '',
					'margin_right'                     => '',
					'margin_bottom'                    => '',
					'margin_left'                      => '',

					// Cell padding.
					'cell_padding_top'                 => '',
					'cell_padding_right'               => '',
					'cell_padding_bottom'              => '',
					'cell_padding_left'                => '',

					'table_cell_backgroundcolor'       => '',
					'heading_cell_backgroundcolor'     => '',

					// Heading styles.
					'heading_color'                    => '',
					'fusion_font_family_heading_font'  => '',
					'fusion_font_variant_heading_font' => '',
					'heading_font_size'                => '',

					// Text styles.
					'text_color'                       => '',
					'fusion_font_family_text_font'     => '',
					'fusion_font_variant_text_font'    => '',
					'text_font_size'                   => '',

					'border_color'                     => '',

					'hide_on_mobile'                   => fusion_builder_default_visibility( 'string' ),
					'class'                            => '',
					'id'                               => '',
					'animation_type'                   => '',
					'animation_direction'              => 'down',
					'animation_speed'                  => '0.1',
					'animation_offset'                 => $fusion_settings->get( 'animation_offset' ),
				];
			}

			/**
			 * Render for live editor.
			 *
			 * @static
			 * @access public
			 * @since 3.2
			 * @param array $defaults An array of defaults.
			 * @return void
			 */
			public function ajax_render( $defaults ) {
				global $product, $post;
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

				$live_request = false;

				// From Ajax Request.
				if ( isset( $_POST['model'] ) && isset( $_POST['model']['params'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$defaults     = $_POST['model']['params']; // phpcs:ignore WordPress.Security
					$return_data  = [];
					$live_request = true;
					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );
				}

				if ( class_exists( 'Fusion_App' ) && $live_request ) {

					$post_id = isset( $_POST['post_id'] ) ? $_POST['post_id'] : get_the_ID(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					if ( ( ! $post_id || -99 === $post_id ) || ( isset( $_POST['post_id'] ) && 'fusion_tb_section' === get_post_type( $_POST['post_id'] ) ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
						echo wp_json_encode( [] );
						wp_die();
					}

					$this->emulate_product();

					// We need to set global $post because Woo template expects it.
					$post = get_post( $product->get_id() );

					if ( ! $this->is_product() ) {
						echo wp_json_encode( $return_data );
						wp_die();
					}

					// Ensure legacy templates are not used.
					if ( function_exists( 'Fusion_Builder_WooCommerce' ) ) {
						Fusion_Builder_WooCommerce()->init_single_product();
					}

					$return_data['woo_additional_info'] = $this->get_woo_addtional_info_content( $defaults );
					$this->restore_product();

					// Restore global $post.
					$post = null;
				}

				echo wp_json_encode( $return_data );
				wp_die();
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 3.2
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_tb_woo_additional_info' );

				$this->emulate_product();

				if ( ! $this->is_product() ) {
					return;
				}

				$html  = $this->get_styles();
				$html .= '<div ' . FusionBuilder::attributes( 'fusion_tb_woo_additional_info-shortcode' ) . '>' . $this->get_woo_addtional_info_content( $this->args ) . '</div>';

				$this->restore_product();

				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle, $html, $args );
			}

			/**
			 * Builds HTML for Woo Rating element.
			 *
			 * @static
			 * @access public
			 * @since 3.2
			 * @param array $args The arguments.
			 * @return string
			 */
			public function get_woo_addtional_info_content( $args ) {
				global $product;

				if ( 'no' === $args['show_tab_title'] ) {
					add_filter( 'woocommerce_product_additional_information_heading', '__return_false', 99 );
				}

				$content = '';
				if ( function_exists( 'woocommerce_product_additional_information_tab' ) && is_object( $product ) ) {
					ob_start();
					woocommerce_product_additional_information_tab();
					$content = ob_get_clean();
				}

				if ( 'no' === $args['show_tab_title'] ) {
					remove_filter( 'woocommerce_product_additional_information_heading', '__return_false', 99 );
				}

				if ( 'yes' === $args['show_tab_title'] && ! $this->is_default( 'title_size' ) ) {
					$opening_tag = '<' . $this->args['title_size'] . ' class="fusion-woocommerce-tab-title';
					$closing_tag = '</' . $this->args['title_size'] . '>';
					$count       = 1;
					$content     = str_replace( [ '<h3 class="fusion-woocommerce-tab-title', '</h3>' ], [ $opening_tag, $closing_tag ], $content, $count );
				}

				return apply_filters( 'fusion_woo_component_content', $content, $this->shortcode_handle, $this->args );
			}

			/**
			 * Get the styles.
			 *
			 * @access protected
			 * @since 3.2
			 * @return string
			 */
			protected function get_styles() {
				$this->base_selector = '.fusion-woo-additional-info-tb.fusion-woo-additional-info-tb-' . $this->counter;
				$this->dynamic_css   = [];

				// Heading styles.
				if ( ! $this->is_default( 'heading_color' ) ) {
					$this->add_css_property( $this->base_selector . ' .shop_attributes tr th', 'color', $this->args['heading_color'] );
				}

				if ( ! $this->is_default( 'heading_font_size' ) ) {
					$this->add_css_property( $this->base_selector . ' .shop_attributes tr th', 'font-size', fusion_library()->sanitize->get_value_with_unit( $this->args['heading_font_size'] ) );
				}

				// Heading typography styles.
				$text_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'heading_font', 'array' );
				foreach ( $text_styles as $rule => $value ) {
					$this->add_css_property( $this->base_selector . ' .shop_attributes tr th', $rule, $value );
				}

				// Text styles.
				if ( ! $this->is_default( 'text_color' ) ) {
					$this->add_css_property( $this->base_selector . ' .shop_attributes tr td', 'color', $this->args['text_color'] );
				}

				if ( ! $this->is_default( 'text_font_size' ) ) {
					$this->add_css_property( $this->base_selector . ' .shop_attributes tr td', 'font-size', fusion_library()->sanitize->get_value_with_unit( $this->args['text_font_size'] ) );
				}

				// Text typography styles.
				$text_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'text_font', 'array' );
				foreach ( $text_styles as $rule => $value ) {
					$this->add_css_property( $this->base_selector . ' .shop_attributes tr td', $rule, $value );
				}

				// Table Border styles.
				if ( ! $this->is_default( 'border_color' ) ) {
					$this->add_css_property( $this->base_selector . ' .shop_attributes tr', 'border-color', $this->args['border_color'] );
				}

				// Cell background.
				if ( ! $this->is_default( 'table_cell_backgroundcolor' ) ) {
					$this->add_css_property( $this->base_selector . ' .shop_attributes td', 'background-color', $this->args['table_cell_backgroundcolor'] );
				}

				// Heading background.
				if ( ! $this->is_default( 'heading_cell_backgroundcolor' ) ) {
					$this->add_css_property( $this->base_selector . ' .shop_attributes th', 'background-color', $this->args['heading_cell_backgroundcolor'] );
				}

				// Get padding.
				$sides = [ 'top', 'right', 'bottom', 'left' ];

				$cell_selectors = [
					$this->base_selector . ' .shop_attributes tr th',
					$this->base_selector . ' .shop_attributes tr td',
				];

				foreach ( $sides as $side ) {

					// Cell padding.
					$cell_padding_name = 'cell_padding_' . $side;

					// Add cell padding to style.
					if ( '' !== $this->args[ $cell_padding_name ] ) {
						$this->add_css_property( $cell_selectors, 'padding-' . $side, fusion_library()->sanitize->get_value_with_unit( $this->args[ $cell_padding_name ] ) );
					}

					// Element margin.
					$margin_name = 'margin_' . $side;

					if ( '' !== $this->args[ $margin_name ] ) {
						$this->add_css_property( $this->base_selector, 'margin-' . $side, fusion_library()->sanitize->get_value_with_unit( $this->args[ $margin_name ] ) );
					}
				}

				$css = $this->parse_css();

				return $css ? '<style>' . $css . '</style>' : '';
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.2
			 * @return array
			 */
			public function attr() {
				$attr = [
					'class' => 'fusion-woo-additional-info-tb fusion-woo-additional-info-tb-' . $this->counter,
					'style' => '',
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
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
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function add_css_files() {
				if ( class_exists( 'Avada' ) ) {
					Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-additional-info.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-additional-info.min.css' );
				}
			}
		}
	}

	new FusionTB_Woo_Additional_Info();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.2
 */
function fusion_component_woo_additional_info() {
	global $fusion_settings;

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Woo_Additional_Info',
			[
				'name'      => esc_attr__( 'Woo Additional Information', 'fusion-builder' ),
				'shortcode' => 'fusion_tb_woo_additional_info',
				'icon'      => 'fusiona-woo-additional-info',
				'component' => true,
				'templates' => [ 'content', 'post_cards', 'page_title_bar' ],
				'params'    => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Heading', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to have heading displayed.', 'fusion-builder' ),
						'param_name'  => 'show_tab_title',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_additional_info',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'HTML Heading Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose HTML tag of the heading, either div or the heading tag, h1-h6.', 'fusion-builder' ),
						'param_name'  => 'title_size',
						'value'       => [
							'h1'  => 'H1',
							'h2'  => 'H2',
							'h3'  => 'H3',
							'h4'  => 'H4',
							'h5'  => 'H5',
							'h6'  => 'H6',
							'div' => 'DIV',
						],
						'default'     => 'h3',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_additional_info',
							'ajax'     => true,
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'margin',
						'callback'         => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
						'value'            => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Table Cell Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 10px or 10%. Leave empty to use default 5px 0 5px 0 value.', 'fusion-builder' ),
						'param_name'       => 'cell_padding',
						'value'            => [
							'cell_padding_top'    => '',
							'cell_padding_right'  => '',
							'cell_padding_bottom' => '',
							'cell_padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Heading Cell Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the heading cell background color. ', 'fusion-builder' ),
						'param_name'  => 'heading_cell_backgroundcolor',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Table Cell Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the table cell background color. ', 'fusion-builder' ),
						'param_name'  => 'table_cell_backgroundcolor',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Table Border Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the color of the table border, ex: #000.' ),
						'param_name'  => 'border_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Heading Cell Text Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the color of the heading text, ex: #000.' ),
						'param_name'  => 'heading_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'font_family',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Heading Cell Font Family', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the font family of the heading.', 'fusion-builder' ),
						'param_name'       => 'heading_font',
						'default'          => [
							'font-family'  => '',
							'font-variant' => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Heading Cell Font Size', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the font size of the text. Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'param_name'  => 'heading_font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Text Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the color of the text, ex: #000.' ),
						'param_name'  => 'text_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'font_family',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Text Font Family', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the font family of the text.', 'fusion-builder' ),
						'param_name'       => 'text_font',
						'default'          => [
							'font-family'  => '',
							'font-variant' => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Text Font Size', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the font size of the text. Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'param_name'  => 'text_font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
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
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
					],
					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-woo-additional-info-tb',
					],
				],
				'callback'  => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_tb_woo_additional_info',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_woo_additional_info' );
