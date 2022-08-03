<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.3
 */

if ( fusion_is_element_enabled( 'fusion_tb_woo_checkout_order_review' ) ) {

	if ( ! class_exists( 'FusionTB_Woo_Checkout_Order_Review' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.3
		 */
		class FusionTB_Woo_Checkout_Order_Review extends Fusion_Woo_Component {

			/**
			 * An array of the shortcode defaults.
			 *
			 * @access protected
			 * @since 3.3
			 * @var array
			 */
			protected $defaults;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 3.3
			 * @var array
			 */
			protected $args;

			/**
			 * The internal container counter.
			 *
			 * @access private
			 * @since 3.3
			 * @var int
			 */
			private $counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.3
			 */
			public function __construct() {
				parent::__construct( 'fusion_tb_woo_checkout_order_review' );
				add_filter( 'fusion_attr_fusion_tb_woo_checkout_order_review-shortcode', [ $this, 'attr' ] );

				// Ajax mechanism for live editor.
				add_action( 'wp_ajax_get_fusion_tb_woo_checkout_order_review', [ $this, 'ajax_render' ] );
			}


			/**
			 * Check if component should render
			 *
			 * @access public
			 * @since 3.3
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
			 * @since 3.3
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = fusion_get_fusion_settings();
				return [
					// General.
					'margin_bottom'                   => '',
					'margin_left'                     => '',
					'margin_right'                    => '',
					'margin_top'                      => '',
					'border_color'                    => '',
					'cell_padding_top'                => '',
					'cell_padding_right'              => '',
					'cell_padding_bottom'             => '',
					'cell_padding_left'               => '',

					// Header.
					'table_header'                    => 'show',
					'header_cell_backgroundcolor'     => '',
					'header_color'                    => '',
					'fusion_font_family_header_font'  => '',
					'fusion_font_variant_header_font' => '',
					'header_font_size'                => '',

					// Body.
					'table_cell_backgroundcolor'      => '',
					'text_color'                      => '',
					'fusion_font_family_text_font'    => '',
					'fusion_font_variant_text_font'   => '',
					'text_font_size'                  => '',

					// Footer.
					'footer_cell_backgroundcolor'     => '',
					'footer_color'                    => '',
					'fusion_font_family_footer_font'  => '',
					'fusion_font_variant_footer_font' => '',
					'footer_font_size'                => '',

					// General.
					'hide_on_mobile'                  => fusion_builder_default_visibility( 'string' ),
					'class'                           => '',
					'id'                              => '',
					'animation_type'                  => '',
					'animation_direction'             => 'down',
					'animation_speed'                 => '0.1',
					'animation_offset'                => $fusion_settings->get( 'animation_offset' ),
				];
			}

			/**
			 * Render for live editor.
			 *
			 * @static
			 * @access public
			 * @since 3.3
			 * @param array $defaults An array of defaults.
			 * @return void
			 */
			public function ajax_render( $defaults ) {
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

				$return_data = [];
				// From Ajax Request.
				if ( isset( $_POST['model'] ) && isset( $_POST['model']['params'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$args           = $_POST['model']['params']; // phpcs:ignore WordPress.Security
					$post_id        = isset( $_POST['post_id'] ) ? $_POST['post_id'] : get_the_ID(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					$this->defaults = self::get_element_defaults();
					$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_tb_woo_checkout_order_review' );

					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );
					$return_data['woo_checkout_order_review'] = $this->get_woo_checkout_order_review_content();
				}

				echo wp_json_encode( $return_data );
				wp_die();
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 3.3
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_tb_woo_checkout_order_review' );

				$html  = $this->get_styles();
				$html .= '<div ' . FusionBuilder::attributes( 'fusion_tb_woo_checkout_order_review-shortcode' ) . '>' . $this->get_woo_checkout_order_review_content() . '</div>';

				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $html, $args );
			}

			/**
			 * Builds HTML for Woo Checkout Order Review element.
			 *
			 * @static
			 * @access public
			 * @since 3.3
			 * @return string
			 */
			public function get_woo_checkout_order_review_content() {
				$content = '';

				if ( 0 === WC()->cart->get_cart_contents_count() ) {
					return $content;
				}

				if ( function_exists( 'woocommerce_order_review' ) ) {
					ob_start();
					woocommerce_order_review();
					$content .= ob_get_clean();
				}
				return apply_filters( 'fusion_woo_component_content', $content, $this->shortcode_handle, $this->args );
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.3
			 * @return array
			 */
			public function attr() {
				$attr = [
					'class' => 'fusion-woo-checkout-order-review-tb fusion-woo-checkout-order-review-tb-' . $this->counter,
					'style' => '',
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;
			}

			/**
			 * Get the styles.
			 *
			 * @access protected
			 * @since 3.3
			 * @return string
			 */
			protected function get_styles() {
				$this->base_selector = '.fusion-woo-checkout-order-review-tb-' . $this->counter;
				$this->dynamic_css   = [];

				$selector = [
					$this->base_selector . ' tbody tr td',
					$this->base_selector . ' thead tr th',
					$this->base_selector . ' tfoot tr th',
					$this->base_selector . ' tfoot tr td',
				];

				if ( ! $this->is_default( 'cell_padding_top' ) ) {
					$this->add_css_property( $selector, 'padding-top', $this->args['cell_padding_top'] );
				}

				if ( ! $this->is_default( 'cell_padding_bottom' ) ) {
					$this->add_css_property( $selector, 'padding-bottom', $this->args['cell_padding_bottom'] );
				}

				if ( ! $this->is_default( 'cell_padding_left' ) ) {
					$this->add_css_property( $selector, 'padding-left', $this->args['cell_padding_left'] );
				}

				if ( ! $this->is_default( 'cell_padding_right' ) ) {
					$this->add_css_property( $selector, 'padding-right', $this->args['cell_padding_right'] );
				}

				$selector = $this->base_selector . ' thead tr th';
				if ( ! $this->is_default( 'header_cell_backgroundcolor' ) ) {
					$this->add_css_property( $selector, 'background-color', $this->args['header_cell_backgroundcolor'] );
				}

				if ( ! $this->is_default( 'header_color' ) ) {
					$this->add_css_property( $selector, 'color', $this->args['header_color'] );
				}

				if ( ! $this->is_default( 'fusion_font_family_header_font' ) ) {
					$this->add_css_property( $selector, 'font-family', $this->args['fusion_font_family_header_font'] );
				}

				if ( ! $this->is_default( 'fusion_font_variant_header_font' ) ) {
					$this->add_css_property( $selector, 'font-weight', $this->args['fusion_font_variant_header_font'] );
				}

				if ( ! $this->is_default( 'header_font_size' ) ) {
					$this->add_css_property( $selector, 'font-size', $this->args['header_font_size'] );
				}

				$selector = $this->base_selector . ' tbody tr td';
				if ( ! $this->is_default( 'table_cell_backgroundcolor' ) ) {
					$this->add_css_property( $selector, 'background-color', $this->args['table_cell_backgroundcolor'] );
				}

				if ( ! $this->is_default( 'text_color' ) ) {
					$this->add_css_property( $selector, 'color', $this->args['text_color'] );
				}

				if ( ! $this->is_default( 'fusion_font_family_text_font' ) ) {
					$this->add_css_property( $selector, 'font-family', $this->args['fusion_font_family_text_font'] );
				}

				if ( ! $this->is_default( 'fusion_font_variant_text_font' ) ) {
					$this->add_css_property( $selector, 'font-weight', $this->args['fusion_font_variant_text_font'] );
				}

				if ( ! $this->is_default( 'text_font_size' ) ) {
					$this->add_css_property( $selector, 'font-size', $this->args['text_font_size'] );
				}

				$selector = $this->base_selector . ' tr, ' . $this->base_selector . ' tr td, ' . $this->base_selector . ' tr th, ' . $this->base_selector . ' tfoot';
				if ( ! $this->is_default( 'border_color' ) ) {
					$this->add_css_property( $selector, 'border-color', $this->args['border_color'], true );
				}

				$selector = $this->base_selector . ' tfoot tr th, ' . $this->base_selector . ' tfoot tr td';
				if ( ! $this->is_default( 'footer_cell_backgroundcolor' ) ) {
					$this->add_css_property( $selector, 'background-color', $this->args['footer_cell_backgroundcolor'] );
				}

				$selector .= ', ' . $this->base_selector . ' .shop_table tfoot .order-total .amount';
				if ( ! $this->is_default( 'footer_color' ) ) {
					$this->add_css_property( $selector, 'color', $this->args['footer_color'] );
				}

				if ( ! $this->is_default( 'fusion_font_family_footer_font' ) ) {
					$this->add_css_property( $selector, 'font-family', $this->args['fusion_font_family_footer_font'] );
				}

				if ( ! $this->is_default( 'fusion_font_variant_footer_font' ) ) {
					$this->add_css_property( $selector, 'font-weight', $this->args['fusion_font_variant_footer_font'] );
				}

				if ( ! $this->is_default( 'footer_font_size' ) ) {
					$this->add_css_property( $selector, 'font-size', $this->args['footer_font_size'] );
				}

				if ( 'show' !== $this->args['table_header'] ) {
					$this->add_css_property( $this->base_selector . ' thead', 'display', 'none' );
				}

				$css = $this->parse_css();

				return $css ? '<style>' . $css . '</style>' : '';
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.3
			 * @return void
			 */
			public function on_first_render() {
				wp_enqueue_script( 'wc-checkout' );
			}
		}
	}

	new FusionTB_Woo_Checkout_Order_Review();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.3
 */
function fusion_component_woo_checkout_order_review() {

	global $fusion_settings;

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Woo_Checkout_Order_Review',
			[
				'name'      => esc_attr__( 'Woo Checkout Order Review', 'fusion-builder' ),
				'shortcode' => 'fusion_tb_woo_checkout_order_review',
				'icon'      => 'fusiona-checkout-order-review',
				'params'    => [
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'margin',
						'value'            => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Table Headers', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to have table headers displayed.', 'fusion-builder' ),
						'param_name'  => 'table_header',
						'value'       => [
							'show' => esc_attr__( 'Show', 'fusion-builder' ),
							'hide' => esc_attr__( 'Hide', 'fusion-builder' ),
						],
						'default'     => 'show',
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
						'heading'     => esc_attr__( 'Header Cell Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the header cell background color. ', 'fusion-builder' ),
						'param_name'  => 'header_cell_backgroundcolor',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'table_header',
								'value'    => 'show',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Header Cell Text Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the color of the header text, ex: #000.' ),
						'param_name'  => 'header_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'table_header',
								'value'    => 'show',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'font_family',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Header Cell Font Family', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the font family of the header.', 'fusion-builder' ),
						'param_name'       => 'header_font',
						'default'          => [
							'font-family'  => '',
							'font-variant' => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'table_header',
								'value'    => 'show',
								'operator' => '==',
							],
						],
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Header Cell Font Size', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the font size of the text. Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'param_name'  => 'header_font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'table_header',
								'value'    => 'show',
								'operator' => '==',
							],
						],
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
						'heading'     => esc_attr__( 'Table Cell Text Color', 'fusion-builder' ),
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
						'heading'          => esc_attr__( 'Table Cell Text Font Family', 'fusion-builder' ),
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
						'heading'     => esc_attr__( 'Table Cell Text Font Size', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the font size of the text. Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'param_name'  => 'text_font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Footer Cell Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the footer cell background color. ', 'fusion-builder' ),
						'param_name'  => 'footer_cell_backgroundcolor',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Footer Cell Text Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the color of the footer text, ex: #000.' ),
						'param_name'  => 'footer_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'font_family',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Footer Cell Font Family', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the font family of the footer cells.', 'fusion-builder' ),
						'param_name'       => 'footer_font',
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
						'heading'     => esc_attr__( 'Footer Cell Font Size', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the font size of the text. Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'param_name'  => 'footer_font_size',
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
						'preview_selector' => '.fusion-woo-checkout-order-review-tb',
					],
				],
				'callback'  => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_tb_woo_checkout_order_review',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_woo_checkout_order_review' );
