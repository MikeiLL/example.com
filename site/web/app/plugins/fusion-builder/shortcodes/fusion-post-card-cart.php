<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.3
 */

if ( fusion_is_element_enabled( 'fusion_post_card_cart' ) ) {

	if ( ! class_exists( 'FusionSC_PostCardCart' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.3
		 */
		class FusionSC_PostCardCart extends Fusion_Element {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 3.3
			 * @var array
			 */
			protected $args;

			/**
			 * Whether styles are already generated or not.
			 *
			 * @access protected
			 * @since 3.3
			 * @var bool
			 */
			protected $styles_generated = false;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.3
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_post-card-cart', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_post-card-cart-icon', [ $this, 'icon_attr' ] );
				add_filter( 'fusion_attr_post-card-cart-details-icon', [ $this, 'icon_details_attr' ] );
				add_shortcode( 'fusion_post_card_cart', [ $this, 'render' ] );

				// Ajax mechanism for live editor.
				add_action( 'wp_ajax_get_fusion_post_card_cart', [ $this, 'ajax_render' ] );
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
					$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_tb_woo_checkout_tabs' );

					// Check if dynamic source is a term and if so emulate.
					if ( isset( $_POST['fusion_meta'] ) ) {
						$meta = fusion_string_to_array( wp_unslash( $_POST['fusion_meta'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
						if ( isset( $meta['_fusion']['dynamic_content_preview_type'] ) && 'term' === $meta['_fusion']['dynamic_content_preview_type'] && isset( $meta['_fusion']['preview_term'] ) && '' !== $meta['_fusion']['preview_term'] ) {
							$GLOBALS['wp_query']->is_tax         = true;
							$GLOBALS['wp_query']->is_archive     = true;
							$GLOBALS['wp_query']->queried_object = get_term_by( 'id', $post_id, (string) $meta['_fusion']['preview_term'] );
						}
					}

					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );

					global $product;

					if ( is_null( $product ) ) {
						$product = wc_get_product( $post_id );
						$this->in_cart();
					}

					if ( ! empty( $product ) ) {
						$return_data['fusion_post_card_cart'] = $this->get_cart_content();
					}
				}

				echo wp_json_encode( $return_data );
				wp_die();
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
				global $fusion_settings;
				return [
					'hide_on_mobile'                       => fusion_builder_default_visibility( 'string' ),
					'class'                                => '',
					'id'                                   => '',
					'animation_type'                       => '',
					'animation_direction'                  => 'down',
					'animation_speed'                      => '0.1',
					'animation_offset'                     => $fusion_settings->get( 'animation_offset' ),

					'buttons_layout'                       => '',
					'cart_layout'                          => '',
					'justify'                              => '',
					'align'                                => '',
					'buttons_justify'                      => '',
					'buttons_alignment'                    => '',

					'show_product_link_button'             => '',
					'show_add_to_cart_button'              => '',
					'show_quantity_input'                  => 'no',
					'buttons_stretch'                      => 'no',
					'enable_quick_view'                    => $fusion_settings->get( 'woocommerce_enable_quick_view' ),

					'margin_top'                           => '',
					'margin_right'                         => '',
					'margin_bottom'                        => '',
					'margin_left'                          => '',

					'quantity_style'                       => '',
					'button_style'                         => '',
					'product_link_style'                   => '',

					'quantity_width'                       => '',
					'quantity_height'                      => '',
					'quantity_radius_top_left'             => '',
					'quantity_radius_top_right'            => '',
					'quantity_radius_bottom_right'         => '',
					'quantity_radius_bottom_left'          => '',
					'quantity_font_size'                   => '',
					'quantity_color'                       => '',
					'quantity_background'                  => '',
					'quantity_border_sizes_top'            => '',
					'quantity_border_sizes_right'          => '',
					'quantity_border_sizes_bottom'         => '',
					'quantity_border_sizes_left'           => '',
					'quantity_border_color'                => '',
					'qbutton_border_sizes_top'             => '',
					'qbutton_border_sizes_right'           => '',
					'qbutton_border_sizes_bottom'          => '',
					'qbutton_border_sizes_left'            => '',
					'qbutton_color'                        => '',
					'qbutton_background'                   => '',
					'qbutton_border_color'                 => '',
					'qbutton_color_hover'                  => '',
					'qbutton_background_hover'             => '',
					'qbutton_border_color_hover'           => '',
					'quantity_margin_top'                  => '',
					'quantity_margin_right'                => '',
					'quantity_margin_bottom'               => '',
					'quantity_margin_left'                 => '',

					'button_size'                          => '',

					'button_border_width'                  => '',
					'button_icon'                          => '',
					'icon_position'                        => 'left',
					'button_color'                         => '',
					'button_gradient_top'                  => $fusion_settings->get( 'button_gradient_top_color' ),
					'button_gradient_bottom'               => $fusion_settings->get( 'button_gradient_bottom_color' ),
					'button_border_color'                  => $fusion_settings->get( 'button_gradient_top_color_hover' ),
					'button_color_hover'                   => $fusion_settings->get( 'button_gradient_bottom_color_hover' ),
					'button_gradient_top_hover'            => '',
					'button_gradient_bottom_hover'         => '',
					'button_border_color_hover'            => '',
					'button_margin_top'                    => '',
					'button_margin_right'                  => '',
					'button_margin_bottom'                 => '',
					'button_margin_left'                   => '',
					'link_font_size'                       => '',
					'link_color'                           => '',
					'link_hover_color'                     => '',
					'button_details_size'                  => '',
					'button_details_border_width'          => '',
					'button_details_icon'                  => '',
					'icon_details_position'                => 'left',
					'button_details_color'                 => '',
					'button_details_gradient_top'          => $fusion_settings->get( 'button_gradient_top_color' ),
					'button_details_gradient_bottom'       => $fusion_settings->get( 'button_gradient_bottom_color' ),
					'button_details_border_color'          => $fusion_settings->get( 'button_gradient_top_color_hover' ),
					'button_details_color_hover'           => $fusion_settings->get( 'button_gradient_bottom_color_hover' ),
					'button_details_gradient_top_hover'    => '',
					'button_details_gradient_bottom_hover' => '',
					'button_details_border_color_hover'    => '',

					'button_details_margin_top'            => '',
					'button_details_margin_right'          => '',
					'button_details_margin_bottom'         => '',
					'button_details_margin_left'           => '',
					'product_link_font_size'               => '',
					'product_link_color'                   => '',
					'product_link_hover_color'             => '',
				];
			}

			/**
			 * Render the shortcode.
			 *
			 * @access public
			 * @since 3.3
			 * @param  array  $args   Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string   HTML output.
			 */
			public function render( $args, $content = '' ) {
				global $product;
				if ( ! class_exists( 'WooCommerce', false ) ) {
					return;
				}
				if ( empty( $product ) || ! $product->is_purchasable() ) {
					return;
				}
				$this->params   = $args;
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $this->params, 'fusion_post_card_cart' );

				$this->validate_args();

				// Not a product therefore nothing to render.
				if ( ! function_exists( 'wc_get_product' ) || 'product' !== get_post_type( get_the_ID() ) ) {
					return '';
				}

				// Check items in cart.
				$this->in_cart();

				$html  = '<div ' . FusionBuilder::attributes( 'post-card-cart' ) . '>';
				$html .= $this->get_cart_content();
				$html .= '</div>';
				$html .= $this->get_styles();

				$this->on_render();

				return apply_filters( 'fusion_element_post_card_cart_content', $html, $args );

			}

			/**
			 * Validate args to format we want.
			 *
			 * @access public
			 * @since 3.3
			 * @return void
			 */
			public function validate_args() {
				if ( 'yes' === $this->args['enable_quick_view'] ) {
					$this->args['enable_quick_view'] = '1';
				} elseif ( 'no' === $this->args['enable_quick_view'] ) {
					$this->args['enable_quick_view'] = '0';
				}
			}

			/**
			 * Set default icons for text links
			 *
			 * @access public
			 * @since 3.3
			 */
			public function set_icon_defaults() {
				if ( 'custom' !== $this->args['button_style'] ) {
					$this->args['icon_position'] = 'left';
					$this->args['button_icon']   = 'fa-shopping-cart fas';
				}

				if ( 'custom' !== $this->args['product_link_style'] ) {
					$this->args['icon_details_position'] = 'left';
					$this->args['button_details_icon']   = 'fa-list-ul fas';
				}
			}

			/**
			 * Generates the post card cart content
			 *
			 * @access public
			 * @since 3.3
			 * @return string HTML output.
			 */
			public function get_cart_content() {
				global $product;
				$this->set_icon_defaults();
				if ( ! empty( $this->args['button_icon'] ) ) {
					add_filter( 'woocommerce_product_add_to_cart_text', [ $this, 'add_icon_placeholder' ], 20 );
				}

				$show_quantity = 'yes' === $this->args['show_quantity_input'] && $product->is_purchasable() && $product->is_in_stock() && $product->is_type( 'simple' );
				ob_start();
				?>
					<?php if ( apply_filters( 'fusion_cart_show_quantity', $show_quantity, $this->args ) ) { ?>
						<div class="fusion-post-card-cart-quantity"><?php echo $this->get_quantity(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<?php } ?>

					<?php if ( $this->has_buttons_wrapper() ) { ?>
						<div class="fusion-post-card-cart-button-wrapper">
					<?php } ?>
					<?php
					if ( 'yes' === $this->args['show_add_to_cart_button'] ) {
						?>
						<?php echo $this->get_add_to_cart(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php
					}
					?>

					<?php if ( 'yes' === $this->args['show_product_link_button'] ) { ?>
						<?php echo $this->get_product_link(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php } ?>

					<?php if ( $this->has_buttons_wrapper() ) { ?>
						</div>
					<?php } ?>
				<?php
				$html = ob_get_clean();
				if ( ! empty( $this->args['button_icon'] ) ) {
					remove_filter( 'woocommerce_product_add_to_cart_text', [ $this, 'add_icon_placeholder' ], 20 );
					$html = str_replace( '@|@', '<i ' . FusionBuilder::attributes( 'post-card-cart-icon' ) . '></i>', $html );
				}
				return $html;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.3
			 * @return array
			 */
			public function icon_attr() {

				$attr = [
					'class'       => fusion_font_awesome_name_handler( $this->args['button_icon'] ),
					'aria-hidden' => 'true',
				];

				$attr['class'] .= ' button-icon-' . $this->args['icon_position'];
				return $attr;

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.3
			 * @return array
			 */
			public function icon_details_attr() {

				$attr = [
					'class'       => fusion_font_awesome_name_handler( $this->args['button_details_icon'] ),
					'aria-hidden' => 'true',
				];

				$attr['class'] .= ' button-icon-' . $this->args['icon_details_position'];
				return $attr;

			}

			/**
			 * Add an icon to the button text.
			 *
			 * @access public
			 * @since 3.3
			 * @param string $text Button text.
			 * @return string
			 */
			public function add_icon_placeholder( $text = '' ) {
				if ( 'left' === $this->args['icon_position'] ) {
					return '@|@' . $text;
				}
				return $text . '@|@';
			}

			/**
			 * Generates the quantity input
			 *
			 * @access public
			 * @since 3.3
			 */
			public function get_quantity() {
				global $product;
				woocommerce_quantity_input(
					[
						'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
						'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
						'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( sanitize_text_field( wp_unslash( $_POST['quantity'] ) ) ) : $product->get_min_purchase_quantity(), // phpcs:ignore WordPress.Security
					]
				);
			}

			/**
			 * Generates the 'Add to cart' button
			 *
			 * @access public
			 * @since 3.3
			 * @return string
			 */
			public function get_add_to_cart() {
				if ( class_exists( 'Avada' ) ) {
					global $product;
					$button_class  = empty( $this->args['button_size'] ) ? ' fusion-button-default-size' : '';
					$button_class .= 'custom' === $this->args['button_style'] ? ' button-default' : '';
					$button_class .= $product->is_type( 'simple' ) ? ' add_to_cart_button ajax_add_to_cart' : '';
					global $avada_woocommerce;
					ob_start();
					$avada_woocommerce->template_loop_add_to_cart( [ 'class' => 'fusion-post-card-cart-add-to-cart' . $button_class ] );
					return ob_get_clean();
				}
			}

			/**
			 * Generates the Details/Quick view button
			 *
			 * @access public
			 * @since 3.2
			 * @return string
			 */
			public function get_product_link() {
				global $product;
				ob_start();
				$has_quick_view     = '1' === $this->args['enable_quick_view'] ? ' fusion-has-quick-view' : '';
				$button_size_class  = empty( $this->args['button_details_size'] ) ? ' fusion-button-default-size' : '';
				$button_size_class .= 'custom' === $this->args['product_link_style'] ? ' button-default' : '';
				$add_styles         = (bool) ( ( ! $product->is_purchasable() || ! $product->is_in_stock() ) && ! $product->is_type( 'external' ) );
				$icon               = ! empty( $this->args['button_details_icon'] ) ? '<i ' . FusionBuilder::attributes( 'post-card-cart-details-icon' ) . '></i>' : '';
				?>

				<a href="<?php echo esc_url_raw( get_permalink() ); ?>" class="fusion-post-card-cart-product-link show_details_button<?php echo esc_attr( $has_quick_view . $button_size_class ); ?>"<?php echo ( $add_styles ) ? ' style="float:none;max-width:none;text-align:center;"' : ''; ?>>
					<?php echo 'left' === $this->args['icon_details_position'] ? $icon : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php esc_html_e( 'Details', 'Avada' ); ?>
					<?php echo 'right' === $this->args['icon_details_position'] ? $icon : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>

				<?php
				if ( '1' === $this->args['enable_quick_view'] ) :
					$product_id = $product->get_ID();
					if ( ! empty( $product_id ) ) {
						$image_info = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'single-post-thumbnail' );
					}
					$image_height = isset( $image_info['2'] ) ? $image_info['2'] : 0;
					$image_width  = isset( $image_info['1'] ) ? $image_info['1'] : 0;
					?>
					<a href="#fusion-quick-view" class="fusion-post-card-cart-product-link fusion-quick-view <?php echo esc_attr( $button_size_class ); ?>" data-image-height="<?php echo $image_height; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" data-image-width="<?php echo $image_width; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"<?php echo ( $add_styles ) ? ' style="float:none;max-width:none;text-align:center;"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<?php echo 'left' === $this->args['icon_details_position'] ? $icon : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php esc_html_e( 'Quick View', 'Avada' ); ?>
						<?php echo 'right' === $this->args['icon_details_position'] ? $icon : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
					<?php
				endif;
				$html = ob_get_clean();
				return $html;
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
				return [
					'add_to_cart_text' => esc_attr__( 'Add to cart', 'fusion-builder' ),
					'quick_view_text'  => esc_attr__( 'Quick View', 'Avada' ),
					'details_text'     => esc_attr__( 'Details', 'Avada' ),
				];
			}

			/**
			 * Get the styles.
			 *
			 * @access public
			 * @since 3.0
			 * @return string
			 */
			public function get_styles() {

				if ( $this->styles_generated ) {
					return;
				}
				$this->base_selector = '.fusion-post-card-cart';
				$this->dynamic_css   = [];

				if ( ! $this->is_default( 'margin_top' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-top', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_top'] ) );
				}
				if ( ! $this->is_default( 'margin_right' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-right', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_right'] ) );
				}
				if ( ! $this->is_default( 'margin_bottom' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-bottom', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_bottom'] ) );
				}
				if ( ! $this->is_default( 'margin_left' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-left', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_left'] ) );
				}

				$selector = $this->base_selector . ' .fusion-post-card-cart-quantity';
				if ( 'floated' === $this->args['cart_layout'] ) {
					$this->add_css_property( $this->base_selector, 'flex-direction', 'row' );
					$this->add_css_property( $selector, 'flex-direction', 'row' );
					$this->add_css_property( $this->base_selector, 'justify-content', $this->args['justify'] );
					$this->add_css_property( $this->base_selector, 'align-items', 'center' );
				} else {
					$this->add_css_property( $selector, 'flex-direction', 'column' );
					$this->add_css_property( $this->base_selector, 'flex-direction', 'column' );
					$this->add_css_property( $selector, 'display', 'flex' );
					$this->add_css_property( $selector, 'align-items', $this->args['align'] );
				}

				// Button wrapper if both buttons are used.
				if ( $this->has_buttons_wrapper() ) {
					$selector = $this->base_selector . ' .fusion-post-card-cart-button-wrapper';
					if ( 'floated' === $this->args['buttons_layout'] ) {
						$this->add_css_property( $selector, 'flex-direction', 'row' );
						$this->add_css_property( $selector, 'align-items', 'center' );
						if ( 'stacked' === $this->args['cart_layout'] ) {
							$this->add_css_property( $selector, 'justify-content', $this->args['buttons_justify'] );
						}
					} elseif ( 'stacked' === $this->args['buttons_layout'] ) {
						$this->add_css_property( $selector, 'flex-direction', 'column' );
						$this->add_css_property( $selector, 'align-items', $this->args['buttons_alignment'] );
					}

					// Button wrapper expand full width.
					if ( 'yes' === $this->args['buttons_stretch'] ) {
						$this->add_css_property( $selector . ' a', 'justify-content', 'center' );
						// Stacked buttons next to quantity.
						if ( 'floated' === $this->args['cart_layout'] ) {
							if ( 'stacked' === $this->args['buttons_layout'] ) {

								// Make the buttons the same width and wrapper expand..
								$this->add_css_property( $selector, 'flex', '1' );
								$this->add_css_property( $selector, 'align-items', 'stretch' );
							} else {

								// Both floated, button wrapper expand then buttons expand.
								$this->add_css_property( $selector, 'flex', '1' );
								$this->add_css_property( $selector . ' a', 'flex', '1' );
							}
						} else {
							if ( 'stacked' === $this->args['buttons_layout'] ) {

								// Make the buttons the same width.
								$this->add_css_property( $selector, 'align-items', 'stretch' );
							} else {

								// Allow each button to grow equally.
								$this->add_css_property( $selector . ' a', 'flex', '1' );
							}
						}
					}
				}

				if ( 'custom' === $this->args['quantity_style'] ) {
					$quantity_input   = '.fusion-body #main ' . $this->base_selector . ' .quantity input[type="number"].qty';
					$quantity_buttons = '.fusion-body #main ' . $this->base_selector . ' .quantity input[type="button"]';
					$quantity_both    = [ $quantity_input, $quantity_buttons ];

					$selector = $this->base_selector . ' .fusion-post-card-cart-quantity';
					if ( ! $this->is_default( 'quantity_margin_top' ) ) {
						$this->add_css_property( $selector, 'margin-top', fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_margin_top'] ) );
					}
					if ( ! $this->is_default( 'quantity_margin_right' ) ) {
						$this->add_css_property( $selector, 'margin-right', fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_margin_right'] ) );
					}
					if ( ! $this->is_default( 'quantity_margin_bottom' ) ) {
						$this->add_css_property( $selector, 'margin-bottom', fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_margin_bottom'] ) );
					}
					if ( ! $this->is_default( 'quantity_margin_left' ) ) {
						$this->add_css_property( $selector, 'margin-left', fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_margin_left'] ) );
					}

					// Quantity height.
					$height = '36px';
					if ( ! $this->is_default( 'quantity_height' ) ) {
						$height = fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_height'] );
						$this->add_css_property( $quantity_both, 'height', $height );
						$this->add_css_property( $quantity_buttons, 'width', $height );
					}

					// Quantity width.
					$width = '36px';
					if ( ! $this->is_default( 'quantity_width' ) ) {
						$width = fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_width'] );

						if ( false !== strpos( $width, '%' ) ) {
							$this->add_css_property( $quantity_input, 'width', 'calc( 100% - ' . $height . ' - ' . $height . ' )' );
						} else {
							$this->add_css_property( $quantity_input, 'width', $width );
						}
					}

					// Quantity wrapper.
					if ( ! $this->is_default( 'quantity_width' ) || ! $this->is_default( 'quantity_height' ) ) {
						$this->add_css_property( $this->base_selector . ' .quantity', 'width', 'calc( ' . $width . ' + ' . $height . ' + ' . $height . ' )' );
					}

					// Quantity border radius left side.
					if ( ! $this->is_default( 'quantity_radius_top_left' ) ) {
						$this->add_css_property( $this->base_selector . ' .quantity .minus', 'border-top-left-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_radius_top_left'] ) );
					}
					if ( ! $this->is_default( 'quantity_radius_bottom_left' ) ) {
						$this->add_css_property( $this->base_selector . ' .quantity .minus', 'border-bottom-left-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_radius_bottom_left'] ) );
					}

					// Quantity border radius right side.
					if ( ! $this->is_default( 'quantity_radius_top_right' ) ) {
						$this->add_css_property( $this->base_selector . ' .quantity .plus', 'border-top-right-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_radius_top_right'] ) );
					}
					if ( ! $this->is_default( 'quantity_radius_bottom_left' ) ) {
						$this->add_css_property( $this->base_selector . ' .quantity .plus', 'border-bottom-right-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_radius_bottom_right'] ) );
					}

					// Quantity input font size.
					if ( ! $this->is_default( 'quantity_font_size' ) ) {
						$quantity_font = [
							$quantity_input,
							$quantity_buttons,
							$this->base_selector . ' .quantity',
						];
						$this->add_css_property( $quantity_font, 'font-size', fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_font_size'] ) );
					}

					// Quantity input text color.
					if ( ! $this->is_default( 'quantity_color' ) ) {
						$this->add_css_property( $quantity_input, 'color', $this->args['quantity_color'] );
					}

					// Quantity input background color.
					if ( ! $this->is_default( 'quantity_background' ) ) {
						$this->add_css_property( $quantity_input, 'background-color', $this->args['quantity_background'] );
					}

					// Quantity input border size.
					if ( ! $this->is_default( 'quantity_border_sizes_top' ) ) {
						$this->add_css_property( $quantity_input, 'border-top-width', fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_border_sizes_top'] ) );
					}
					if ( ! $this->is_default( 'quantity_border_sizes_right' ) ) {
						$this->add_css_property( $quantity_input, 'border-right-width', fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_border_sizes_right'] ) );
					}
					if ( ! $this->is_default( 'quantity_border_sizes_bottom' ) ) {
						$this->add_css_property( $quantity_input, 'border-bottom-width', fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_border_sizes_bottom'] ) );
					}
					if ( ! $this->is_default( 'quantity_border_sizes_left' ) ) {
						$this->add_css_property( $quantity_input, 'border-left-width', fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_border_sizes_left'] ) );
					}

					// Quantity input border color.
					if ( ! $this->is_default( 'quantity_border_color' ) ) {
						$this->add_css_property( $quantity_input, 'border-color', $this->args['quantity_border_color'] );
					}

					// Quantity buttons border size.
					if ( ! $this->is_default( 'qbutton_border_sizes_top' ) ) {
						$this->add_css_property( $quantity_buttons, 'border-top-width', fusion_library()->sanitize->get_value_with_unit( $this->args['qbutton_border_sizes_top'] ) );
					}
					if ( ! $this->is_default( 'qbutton_border_sizes_right' ) ) {
						$this->add_css_property( $quantity_buttons, 'border-right-width', fusion_library()->sanitize->get_value_with_unit( $this->args['qbutton_border_sizes_right'] ) );
					}
					if ( ! $this->is_default( 'qbutton_border_sizes_bottom' ) ) {
						$this->add_css_property( $quantity_buttons, 'border-bottom-width', fusion_library()->sanitize->get_value_with_unit( $this->args['qbutton_border_sizes_bottom'] ) );
					}
					if ( ! $this->is_default( 'qbutton_border_sizes_left' ) ) {
						$this->add_css_property( $quantity_buttons, 'border-left-width', fusion_library()->sanitize->get_value_with_unit( $this->args['qbutton_border_sizes_left'] ) );
					}

					// Quantity button text color.
					if ( ! $this->is_default( 'qbutton_color' ) ) {
						$this->add_css_property( $quantity_buttons, 'color', $this->args['qbutton_color'] );
					}

					// Quantity button background color.
					if ( ! $this->is_default( 'qbutton_background' ) ) {
						$this->add_css_property( $quantity_buttons, 'background-color', $this->args['qbutton_background'] );
					}

					// Quantity button border color.
					if ( ! $this->is_default( 'qbutton_border_color' ) ) {
						$this->add_css_property( $quantity_buttons, 'border-color', $this->args['qbutton_border_color'] );
					}

					$hover_buttons = [
						$quantity_buttons . ':hover',
						$quantity_buttons . ':focus',
					];

					// Quantity button hover text color.
					if ( ! $this->is_default( 'qbutton_color_hover' ) ) {
						$this->add_css_property( $hover_buttons, 'color', $this->args['qbutton_color_hover'] );
					}

					// Quantity button hover background color.
					if ( ! $this->is_default( 'qbutton_background_hover' ) ) {
						$this->add_css_property( $hover_buttons, 'background-color', $this->args['qbutton_background_hover'] );
					}

					// Quantity button hover border color.
					if ( ! $this->is_default( 'qbutton_border_color_hover' ) ) {
						$this->add_css_property( $hover_buttons, 'border-color', $this->args['qbutton_border_color_hover'] );
					}
				}

				$selector = $this->base_selector . ' .fusion-post-card-cart-add-to-cart';
				if ( ! $this->is_default( 'button_margin_top' ) ) {
					$this->add_css_property( $selector, 'margin-top', fusion_library()->sanitize->get_value_with_unit( $this->args['button_margin_top'] ) );
				}
				if ( ! $this->is_default( 'button_margin_right' ) ) {
					$this->add_css_property( $selector, 'margin-right', fusion_library()->sanitize->get_value_with_unit( $this->args['button_margin_right'] ) );
				}
				if ( ! $this->is_default( 'button_margin_bottom' ) ) {
					$this->add_css_property( $selector, 'margin-bottom', fusion_library()->sanitize->get_value_with_unit( $this->args['button_margin_bottom'] ) );
				}
				if ( ! $this->is_default( 'button_margin_left' ) ) {
					$this->add_css_property( $selector, 'margin-left', fusion_library()->sanitize->get_value_with_unit( $this->args['button_margin_left'] ) );
				}

				if ( 'custom' === $this->args['button_style'] ) {
					$button       = '.fusion-body ' . $this->base_selector . ' .fusion-post-card-cart-add-to-cart';
					$button_hover = $button . ':hover';

					// Button size.
					if ( ! $this->is_default( 'button_size' ) ) {

						$button_size_map = [
							'small'  => [
								'padding'     => '9px 20px',
								'line_height' => '14px',
								'font_size'   => '12px',
							],
							'medium' => [
								'padding'     => '11px 23px',
								'line_height' => '16px',
								'font_size'   => '13px',
							],
							'large'  => [
								'padding'     => '13px 29px',
								'line_height' => '17px',
								'font_size'   => '14px',
							],
							'xlarge' => [
								'padding'     => '17px 40px',
								'line_height' => '21px',
								'font_size'   => '18px',
							],
						];

						if ( isset( $button_size_map[ $this->args['button_size'] ] ) ) {
							$button_dimensions = $button_size_map[ $this->args['button_size'] ];
							$this->add_css_property( $button, 'padding', $button_dimensions['padding'] );
							$this->add_css_property( $button, 'line-height', $button_dimensions['line_height'] );
							$this->add_css_property( $button, 'font-size', $button_dimensions['font_size'] );
						}
					}

					// Button border width.
					if ( ! $this->is_default( 'button_border_width' ) ) {
						$this->add_css_property( $button, 'border-width', fusion_library()->sanitize->get_value_with_unit( $this->args['button_border_width'] ) );
					}

					// Button text color.
					if ( ! $this->is_default( 'button_color' ) ) {
						$this->add_css_property( $button, 'color', $this->args['button_color'] );
					}

					// Button gradient.
					if ( ( isset( $this->args['button_gradient_top'] ) && '' !== $this->args['button_gradient_top'] ) || ( isset( $this->args['button_gradient_bottom'] ) && '' !== $this->args['button_gradient_bottom'] ) ) {
						$this->add_css_property( $button, 'background', $this->args['button_gradient_top'] );
						$this->add_css_property( $button, 'background-image', 'linear-gradient( to top, ' . $this->args['button_gradient_bottom'] . ', ' . $this->args['button_gradient_top'] . ' )' );
					}

					// Button border color.
					if ( ! $this->is_default( 'button_border_color' ) ) {
						$this->add_css_property( $button, 'border-color', $this->args['button_border_color'] );
					}

					// Button hover text color.
					if ( ! $this->is_default( 'button_color_hover' ) ) {
						$this->add_css_property( $button_hover, 'color', $this->args['button_color_hover'] );
					}

					// Button gradient.
					if ( ( isset( $this->args['button_gradient_top_hover'] ) && '' !== $this->args['button_gradient_top_hover'] ) || ( isset( $this->args['button_gradient_bottom_hover'] ) && '' !== $this->args['button_gradient_bottom_hover'] ) ) {
						$this->add_css_property( $button_hover, 'background', $this->args['button_gradient_top_hover'] );
						$this->add_css_property( $button_hover, 'background-image', 'linear-gradient( to top, ' . $this->args['button_gradient_bottom_hover'] . ', ' . $this->args['button_gradient_top_hover'] . ' )' );
					}

					// Button border color.
					if ( ! $this->is_default( 'button_border_color_hover' ) ) {
						$this->add_css_property( $button_hover, 'border-color', $this->args['button_border_color_hover'] );
					}
				} else {

					// Link text color.
					if ( ! $this->is_default( 'link_color' ) ) {
						$this->add_css_property( $selector, 'color', $this->args['link_color'] );
					}

					// Link hover text color.
					if ( ! $this->is_default( 'link_hover_color' ) ) {
						$this->add_css_property( $selector . ':hover', 'color', $this->args['link_hover_color'] );
					}

					// Link font size.
					if ( ! $this->is_default( 'link_font_size' ) ) {
						$this->add_css_property( $selector, 'font-size', $this->args['link_font_size'] );
					}
				}

				// Product link button styling.
				$selector = $this->base_selector . ' .fusion-post-card-cart-product-link';
				if ( ! $this->is_default( 'button_details_margin_top' ) ) {
					$this->add_css_property( $selector, 'margin-top', fusion_library()->sanitize->get_value_with_unit( $this->args['button_details_margin_top'] ) );
				}
				if ( ! $this->is_default( 'button_details_margin_right' ) ) {
					$this->add_css_property( $selector, 'margin-right', fusion_library()->sanitize->get_value_with_unit( $this->args['button_details_margin_right'] ) );
				}
				if ( ! $this->is_default( 'button_details_margin_bottom' ) ) {
					$this->add_css_property( $selector, 'margin-bottom', fusion_library()->sanitize->get_value_with_unit( $this->args['button_details_margin_bottom'] ) );
				}
				if ( ! $this->is_default( 'button_details_margin_left' ) ) {
					$this->add_css_property( $selector, 'margin-left', fusion_library()->sanitize->get_value_with_unit( $this->args['button_details_margin_left'] ) );
				}
				if ( 'custom' === $this->args['product_link_style'] ) {
					$button = '.fusion-body ' . $this->base_selector . ' .fusion-post-card-cart-product-link';

					// Button size.
					if ( ! $this->is_default( 'button_details_size' ) ) {

						$button_size_map = [
							'small'  => [
								'padding'     => '9px 20px',
								'line_height' => '14px',
								'font_size'   => '12px',
							],
							'medium' => [
								'padding'     => '11px 23px',
								'line_height' => '16px',
								'font_size'   => '13px',
							],
							'large'  => [
								'padding'     => '13px 29px',
								'line_height' => '17px',
								'font_size'   => '14px',
							],
							'xlarge' => [
								'padding'     => '17px 40px',
								'line_height' => '21px',
								'font_size'   => '18px',
							],
						];

						if ( isset( $button_size_map[ $this->args['button_details_size'] ] ) ) {
							$button_dimensions = $button_size_map[ $this->args['button_details_size'] ];
							$this->add_css_property( $button, 'padding', $button_dimensions['padding'] );
							$this->add_css_property( $button, 'line-height', $button_dimensions['line_height'] );
							$this->add_css_property( $button, 'font-size', $button_dimensions['font_size'] );
						}
					}

					// Button border width.
					if ( ! $this->is_default( 'button_details_border_width' ) ) {
						$this->add_css_property( $button, 'border-width', fusion_library()->sanitize->get_value_with_unit( $this->args['button_details_border_width'] ) );
					}

					// Button text color.
					if ( ! $this->is_default( 'button_details_color' ) ) {
						$this->add_css_property( $button, 'color', $this->args['button_details_color'] );
					}

					// Button gradient.
					if ( ( isset( $this->args['button_details_gradient_top'] ) && '' !== $this->args['button_details_gradient_top'] ) || ( isset( $this->args['button_details_gradient_bottom'] ) && '' !== $this->args['button_details_gradient_bottom'] ) ) {
						$this->add_css_property( $button, 'background', $this->args['button_details_gradient_top'] );
						$this->add_css_property( $button, 'background-image', 'linear-gradient( to top, ' . $this->args['button_details_gradient_bottom'] . ', ' . $this->args['button_details_gradient_top'] . ' )' );
					}

					// Button border color.
					if ( ! $this->is_default( 'button_details_border_color' ) ) {
						$this->add_css_property( $button, 'border-color', $this->args['button_details_border_color'] );
					}

					$button_hover = $button . ':hover';

					// Button hover text color.
					if ( ! $this->is_default( 'button_details_color_hover' ) ) {
						$this->add_css_property( $button_hover, 'color', $this->args['button_details_color_hover'] );
					}

					// Button gradient.
					if ( ( isset( $this->args['button_details_gradient_top_hover'] ) && '' !== $this->args['button_details_gradient_top_hover'] ) || ( isset( $this->args['button_details_gradient_bottom_hover'] ) && '' !== $this->args['button_details_gradient_bottom_hover'] ) ) {
						$this->add_css_property( $button_hover, 'background', $this->args['button_details_gradient_top_hover'] );
						$this->add_css_property( $button_hover, 'background-image', 'linear-gradient( to top, ' . $this->args['button_details_gradient_bottom_hover'] . ', ' . $this->args['button_details_gradient_top_hover'] . ' )' );
					}

					// Button border color.
					if ( ! $this->is_default( 'button_details_border_color_hover' ) ) {
						$this->add_css_property( $button_hover, 'border-color', $this->args['button_details_border_color_hover'] );
					}
				} else {

					// Link text color.
					if ( ! $this->is_default( 'product_link_color' ) ) {
						$this->add_css_property( $selector, 'color', $this->args['product_link_color'] );
					}

					// Link hover text color.
					if ( ! $this->is_default( 'product_link_hover_color' ) ) {
						$this->add_css_property( $selector . ':hover', 'color', $this->args['product_link_hover_color'] );
					}

					// Link font size.
					if ( ! $this->is_default( 'product_link_font_size' ) ) {
						$this->add_css_property( $selector, 'font-size', $this->args['product_link_font_size'] );
					}
				}

				$css                    = $this->parse_css();
				$this->styles_generated = true;
				return $css ? '<style>' . $css . '</style>' : '';
			}

			/**
			 * Do we have wrapper for buttons.
			 *
			 * @access public
			 * @since 3.3
			 * @return boolean
			 */
			public function has_buttons_wrapper() {
				return ( 'yes' === $this->args['show_product_link_button'] || 'yes' === $this->args['show_add_to_cart_button'] )
					&& ! ( 'floated' === $this->args['cart_layout'] && 'floated' === $this->args['buttons_layout'] && 'no' === $this->args['buttons_stretch'] );
			}

			/**
			 * Check if current product is in cart.
			 *
			 * @access public
			 * @since 3.3
			 * @return void
			 */
			public function in_cart() {
				$this->args['in_cart'] = fusion_library()->woocommerce->is_product_in_cart( get_the_ID() );
			}

			/**
			 * Builds the array of atributes.
			 *
			 * @access public
			 * @since 3.3
			 * @return array
			 */
			public function attr() {
				$attr = fusion_builder_visibility_atts(
					$this->args['hide_on_mobile'],
					[
						'class' => 'fusion-woo-cart fusion-post-card-cart',
					]
				);

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				if ( $this->args['in_cart'] ) {
					$attr['class'] .= ' fusion-item-in-cart';
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
			 * @since 3.3
			 * @return void
			 */
			public function add_css_files() {
				if ( class_exists( 'Avada' ) && class_exists( 'WooCommerce', false ) ) {
					global $avada_woocommerce;
					$avada_woocommerce->quick_view_init();
					$version = Avada::get_theme_version();
					Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-quick-view.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-quick-view.min.css' );

					Fusion_Media_Query_Scripts::$media_query_assets[] = [
						'avada-max-sh-cbp-woo-quick-view',
						get_template_directory_uri() . '/assets/css/media/max-sh-cbp-woo-quick-view.min.css',
						[],
						$version,
						Fusion_Media_Query_Scripts::get_media_query_from_key( 'fusion-max-sh-cbp' ),
					];

					Fusion_Media_Query_Scripts::$media_query_assets[] = [
						'avada-min-sh-cbp-woo-quick-view',
						get_template_directory_uri() . '/assets/css/media/min-sh-cbp-woo-quick-view.min.css',
						[],
						$version,
						Fusion_Media_Query_Scripts::get_media_query_from_key( 'fusion-min-sh-cbp' ),
					];
				}
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/post-card-cart.min.css' );
			}
		}
	}

	new FusionSC_PostCardCart();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 3.3
 */
function fusion_element_post_card_cart() {
	global $fusion_settings;

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_PostCardCart',
			[
				'name'      => esc_attr__( 'Post Card Cart', 'fusion-builder' ),
				'shortcode' => 'fusion_post_card_cart',
				'icon'      => 'fusiona-post-cards-cart',
				'templates' => [ 'post_cards' ],
				'component' => true,
				'params'    => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Cart Layout', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the layout of cart components. Floated will have components side by side. Stacked will have one component per row.', 'fusion-builder' ),
						'param_name'  => 'cart_layout',
						'default'     => 'stacked',
						'value'       => [
							'floated' => esc_attr__( 'Floated', 'fusion-builder' ),
							'stacked' => esc_attr__( 'Stacked', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Cart Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the content alignment.', 'fusion-builder' ),
						'param_name'  => 'justify',
						'default'     => 'flex-start',
						'grid_layout' => true,
						'back_icons'  => true,
						'icons'       => [
							'flex-start'    => '<span class="fusiona-horizontal-flex-start"></span>',
							'center'        => '<span class="fusiona-horizontal-flex-center"></span>',
							'flex-end'      => '<span class="fusiona-horizontal-flex-end"></span>',
							'space-between' => '<span class="fusiona-horizontal-space-between"></span>',
							'space-around'  => '<span class="fusiona-horizontal-space-around"></span>',
							'space-evenly'  => '<span class="fusiona-horizontal-space-evenly"></span>',
						],
						'value'       => [
							'flex-start'    => esc_html__( 'Flex Start', 'fusion-builder' ),
							'center'        => esc_html__( 'Center', 'fusion-builder' ),
							'flex-end'      => esc_html__( 'Flex End', 'fusion-builder' ),
							'space-between' => esc_html__( 'Space Between', 'fusion-builder' ),
							'space-around'  => esc_html__( 'Space Around', 'fusion-builder' ),
							'space-evenly'  => esc_html__( 'Space Evenly', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'cart_layout',
								'value'    => 'floated',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Cart Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the content alignment.', 'fusion-builder' ),
						'param_name'  => 'align',
						'default'     => 'flex-start',
						'grid_layout' => true,
						'back_icons'  => true,
						'icons'       => [
							'flex-start' => '<span class="fusiona-horizontal-flex-start"></span>',
							'center'     => '<span class="fusiona-horizontal-flex-center"></span>',
							'flex-end'   => '<span class="fusiona-horizontal-flex-end"></span>',
						],
						'value'       => [
							'flex-start' => esc_html__( 'Flex Start', 'fusion-builder' ),
							'center'     => esc_html__( 'Center', 'fusion-builder' ),
							'flex-end'   => esc_html__( 'Flex End', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'cart_layout',
								'value'    => 'floated',
								'operator' => '!=',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Quantity', 'fusion-builder' ),
						'description' => esc_attr__( 'Display the quantity input.', 'fusion-builder' ),
						'param_name'  => 'show_quantity_input',
						'default'     => 'no',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Add To Cart', 'fusion-builder' ),
						'description' => esc_attr__( 'Display the Add To Cart button.', 'fusion-builder' ),
						'param_name'  => 'show_add_to_cart_button',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Product Link', 'fusion-builder' ),
						'description' => esc_attr__( 'Display the Product Link .', 'fusion-builder' ),
						'param_name'  => 'show_product_link_button',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Product Quick View', 'fusion-builder' ),
						'description' => esc_attr__( 'Enable product quick view for products.', 'fusion-builder' ),
						'param_name'  => 'enable_quick_view',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'show_product_link_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Buttons Span', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls if buttons spans the full width/remaining width of row.', 'fusion-builder' ),
						'param_name'  => 'buttons_stretch',
						'default'     => 'no',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'or'          => true,
						'dependency'  => [
							[
								'element'  => 'show_product_link_button',
								'value'    => 'yes',
								'operator' => '==',
							],
							[
								'element'  => 'show_add_to_cart_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Buttons Layout', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the layout for buttons.  Floated will have them side by side.  Stacked will have one per row.', 'fusion-builder' ),
						'param_name'  => 'buttons_layout',
						'default'     => 'floated',
						'value'       => [
							'floated' => esc_attr__( 'Floated', 'fusion-builder' ),
							'stacked' => esc_attr__( 'Stacked', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'show_product_link_button',
								'value'    => 'yes',
								'operator' => '==',
							],
							[
								'element'  => 'show_add_to_cart_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Buttons Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the content alignment.', 'fusion-builder' ),
						'param_name'  => 'buttons_alignment',
						'default'     => 'flex-start',
						'grid_layout' => true,
						'back_icons'  => true,
						'icons'       => [
							'flex-start' => '<span class="fusiona-horizontal-flex-start"></span>',
							'center'     => '<span class="fusiona-horizontal-flex-center"></span>',
							'flex-end'   => '<span class="fusiona-horizontal-flex-end"></span>',
						],
						'value'       => [
							'flex-start' => esc_html__( 'Flex Start', 'fusion-builder' ),
							'center'     => esc_html__( 'Center', 'fusion-builder' ),
							'flex-end'   => esc_html__( 'Flex End', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'buttons_layout',
								'value'    => 'floated',
								'operator' => '!=',
							],
							[
								'element'  => 'buttons_stretch',
								'value'    => 'yes',
								'operator' => '!=',
							],
							[
								'element'  => 'show_product_link_button',
								'value'    => 'yes',
								'operator' => '==',
							],
							[
								'element'  => 'show_add_to_cart_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Buttons Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the content alignment.', 'fusion-builder' ),
						'param_name'  => 'buttons_justify',
						'default'     => 'space-between',
						'grid_layout' => true,
						'back_icons'  => true,
						'icons'       => [
							'flex-start'    => '<span class="fusiona-horizontal-flex-start"></span>',
							'center'        => '<span class="fusiona-horizontal-flex-center"></span>',
							'flex-end'      => '<span class="fusiona-horizontal-flex-end"></span>',
							'space-between' => '<span class="fusiona-horizontal-space-between"></span>',
							'space-around'  => '<span class="fusiona-horizontal-space-around"></span>',
							'space-evenly'  => '<span class="fusiona-horizontal-space-evenly"></span>',
						],
						'value'       => [
							'flex-start'    => esc_html__( 'Flex Start', 'fusion-builder' ),
							'center'        => esc_html__( 'Center', 'fusion-builder' ),
							'flex-end'      => esc_html__( 'Flex End', 'fusion-builder' ),
							'space-between' => esc_html__( 'Space Between', 'fusion-builder' ),
							'space-around'  => esc_html__( 'Space Around', 'fusion-builder' ),
							'space-evenly'  => esc_html__( 'Space Evenly', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'buttons_layout',
								'value'    => 'floated',
								'operator' => '==',
							],
							[
								'element'  => 'cart_layout',
								'value'    => 'stacked',
								'operator' => '==',
							],
							[
								'element'  => 'buttons_stretch',
								'value'    => 'yes',
								'operator' => '!=',
							],
							[
								'element'  => 'show_product_link_button',
								'value'    => 'yes',
								'operator' => '==',
							],
							[
								'element'  => 'show_add_to_cart_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
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
						'callback'         => [
							'function' => 'fusion_style_block',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Quantity Style', 'fusion-builder' ),
						'description' => esc_attr__( 'Select whether you want to custom style the quantity field.', 'fusion-builder' ),
						'param_name'  => 'quantity_style',
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'custom' => esc_attr__( 'Custom', 'fusion-builder' ),
						],
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'show_quantity_input',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Quantity Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'quantity_margin',
						'value'            => [
							'quantity_margin_top'    => '',
							'quantity_margin_right'  => '',
							'quantity_margin_bottom' => '',
							'quantity_margin_left'   => '',
						],
						'callback'         => [
							'function' => 'fusion_style_block',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Quantity Input Dimensions', 'fusion-builder' ),
						'description'      => esc_html__( 'Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'quantity_height_field',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'value'            => [
							'quantity_width'  => '',
							'quantity_height' => '',
						],
						'dependency'       => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Quantity Border Radius', 'fusion-builder' ),
						'description'      => esc_html__( 'Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'quantity_border_radius',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'value'            => [
							'quantity_radius_top_left'     => '',
							'quantity_radius_top_right'    => '',
							'quantity_radius_bottom_right' => '',
							'quantity_radius_bottom_left'  => '',
						],
						'dependency'       => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'         => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Quantity Input Font Size', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the font size of the select field. Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'param_name'  => 'quantity_font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Quantity Input Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the select fields.', 'fusion-builder' ),
						'param_name'  => 'quantity_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'body_typography', 'color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Quantity Input Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the select fields.', 'fusion-builder' ),
						'param_name'  => 'quantity_background',
						'value'       => '',
						'default'     => 'rgba(255,255,255,0)',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'dimension',
						'heading'     => esc_attr__( 'Quantity Input Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border size of the select fields.', 'fusion-builder' ),
						'param_name'  => 'quantity_border_sizes',
						'value'       => [
							'quantity_border_sizes_top'    => '',
							'quantity_border_sizes_right'  => '',
							'quantity_border_sizes_bottom' => '',
							'quantity_border_sizes_left'   => '',
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Quantity Input Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the select fields.', 'fusion-builder' ),
						'param_name'  => 'quantity_border_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'sep_color' ),
						'dependency'  => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'dimension',
						'heading'     => esc_attr__( 'Quantity Button Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border size of the select fields.', 'fusion-builder' ),
						'param_name'  => 'qbutton_border_sizes',
						'value'       => [
							'qbutton_border_sizes_top'    => '',
							'qbutton_border_sizes_right'  => '',
							'qbutton_border_sizes_bottom' => '',
							'qbutton_border_sizes_left'   => '',
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'Quantity Button Styling', 'fusion-builder' ),
						'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
						'param_name'       => 'quantity_styling',
						'default'          => 'regular',
						'group'            => esc_html__( 'Design', 'fusion-builder' ),
						'remove_from_atts' => true,
						'value'            => [
							'regular' => esc_html__( 'Regular', 'fusion-builder' ),
							'hover'   => esc_html__( 'Hover / Active', 'fusion-builder' ),
						],
						'icons'            => [
							'regular' => '<span class="fusiona-regular-state" style="font-size:18px;"></span>',
							'hover'   => '<span class="fusiona-hover-state" style="font-size:18px;"></span>',
						],
						'dependency'       => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Quantity Button Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the select fields.', 'fusion-builder' ),
						'param_name'  => 'qbutton_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'body_typography', 'color' ),
						'subgroup'    => [
							'name' => 'quantity_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Quantity Button Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the select fields.', 'fusion-builder' ),
						'param_name'  => 'qbutton_background',
						'value'       => '',
						'default'     => $fusion_settings->get( 'qty_bg_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'quantity_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Quantity Button Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the select fields.', 'fusion-builder' ),
						'param_name'  => 'qbutton_border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'sep_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'quantity_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Quantity Button Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the select fields.', 'fusion-builder' ),
						'param_name'  => 'qbutton_color_hover',
						'value'       => '',
						'default'     => $fusion_settings->get( 'body_typography', 'color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'quantity_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Quantity Button Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the select fields.', 'fusion-builder' ),
						'param_name'  => 'qbutton_background_hover',
						'value'       => '',
						'default'     => $fusion_settings->get( 'qty_bg_hover_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'quantity_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Quantity Button Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the select fields.', 'fusion-builder' ),
						'param_name'  => 'qbutton_border_color_hover',
						'value'       => '',
						'default'     => $fusion_settings->get( 'sep_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'quantity_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Add To Cart Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'button_margin',
						'value'            => [
							'button_margin_top'    => '',
							'button_margin_right'  => '',
							'button_margin_bottom' => '',
							'button_margin_left'   => '',
						],
						'callback'         => [
							'function' => 'fusion_style_block',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'show_add_to_cart_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Add To Cart Link Style', 'fusion-builder' ),
						'description' => esc_attr__( 'Select whether you want to custom style the add to cart button.', 'fusion-builder' ),
						'param_name'  => 'button_style',
						'value'       => [
							''       => esc_attr__( 'Text Link', 'fusion-builder' ),
							'custom' => esc_attr__( 'Button', 'fusion-builder' ),
						],
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'show_add_to_cart_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the button size.', 'fusion-builder' ),
						'param_name'  => 'button_size',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'small'  => esc_attr__( 'Small', 'fusion-builder' ),
							'medium' => esc_attr__( 'Medium', 'fusion-builder' ),
							'large'  => esc_attr__( 'Large', 'fusion-builder' ),
							'xlarge' => esc_attr__( 'XLarge', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'show_add_to_cart_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],

					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Button Border Size', 'fusion-builder' ),
						'param_name'  => 'button_border_width',
						'description' => esc_attr__( 'Controls the border size. In pixels.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'show_add_to_cart_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'min'         => '0',
						'max'         => '20',
						'step'        => '1',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_border_width' ),
						'callback'    => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Icon', 'fusion-builder' ),
						'param_name'  => 'button_icon',
						'value'       => '',
						'description' => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'show_add_to_cart_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
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
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'button_icon',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'show_add_to_cart_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Add To Cart Link Font Size', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the font size of the text link. Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'param_name'  => 'link_font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '!=',
							],
							[
								'element'  => 'show_add_to_cart_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'Add To Cart Link Styling', 'fusion-builder' ),
						'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
						'param_name'       => 'link_styling',
						'default'          => 'regular',
						'group'            => esc_html__( 'Design', 'fusion-builder' ),
						'remove_from_atts' => true,
						'value'            => [
							'regular' => esc_html__( 'Regular', 'fusion-builder' ),
							'hover'   => esc_html__( 'Hover / Active', 'fusion-builder' ),
						],
						'icons'            => [
							'regular' => '<span class="fusiona-regular-state" style="font-size:18px;"></span>',
							'hover'   => '<span class="fusiona-hover-state" style="font-size:18px;"></span>',
						],
						'dependency'       => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '!=',
							],
							[
								'element'  => 'show_add_to_cart_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Link Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the link.', 'fusion-builder' ),
						'param_name'  => 'link_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'link_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'link_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '!=',
							],
							[
								'element'  => 'show_add_to_cart_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Link Text Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text hover color of the link.', 'fusion-builder' ),
						'param_name'  => 'link_hover_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'primary_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'link_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '!=',
							],
							[
								'element'  => 'show_add_to_cart_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'Button Styling', 'fusion-builder' ),
						'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
						'param_name'       => 'button_styling',
						'default'          => 'regular',
						'group'            => esc_html__( 'Design', 'fusion-builder' ),
						'remove_from_atts' => true,
						'value'            => [
							'regular' => esc_html__( 'Regular', 'fusion-builder' ),
							'hover'   => esc_html__( 'Hover / Active', 'fusion-builder' ),
						],
						'icons'            => [
							'regular' => '<span class="fusiona-regular-state" style="font-size:18px;"></span>',
							'hover'   => '<span class="fusiona-hover-state" style="font-size:18px;"></span>',
						],
						'dependency'       => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'show_add_to_cart_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_accent_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'button_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'show_add_to_cart_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Top Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_top',
						'value'       => '',
						'default'     => $fusion_settings->get( 'primary_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'button_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'show_add_to_cart_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Bottom Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_bottom',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_bottom_color' ),
						'subgroup'    => [
							'name' => 'button_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'show_add_to_cart_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_border_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_border_color' ),
						'subgroup'    => [
							'name' => 'button_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'show_add_to_cart_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Text Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_color_hover',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_accent_hover_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'button_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'show_add_to_cart_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Top Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_top_hover',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_gradient_top_color_hover' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'button_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'show_add_to_cart_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Bottom Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_bottom_hover',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_bottom_color_hover' ),
						'subgroup'    => [
							'name' => 'button_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'show_add_to_cart_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Border Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_border_color_hover',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_border_hover_color' ),
						'subgroup'    => [
							'name' => 'button_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'show_add_to_cart_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Product Link Style', 'fusion-builder' ),
						'description' => esc_attr__( 'Select whether you want to custom style the product link.', 'fusion-builder' ),
						'param_name'  => 'product_link_style',
						'value'       => [
							''       => esc_attr__( 'Text Link', 'fusion-builder' ),
							'custom' => esc_attr__( 'Button', 'fusion-builder' ),
						],
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'show_product_link_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Product Link Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'button_details_margin',
						'value'            => [
							'button_details_margin_top'    => '',
							'button_details_margin_right'  => '',
							'button_details_margin_bottom' => '',
							'button_details_margin_left'   => '',
						],
						'callback'         => [
							'function' => 'fusion_style_block',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'show_product_link_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Product Link Font Size', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the font size of the text link. Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'param_name'  => 'product_link_font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'product_link_style',
								'value'    => 'custom',
								'operator' => '!=',
							],
							[
								'element'  => 'show_product_link_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'Product Link Styling', 'fusion-builder' ),
						'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
						'param_name'       => 'product_link_styling_options',
						'default'          => 'regular',
						'group'            => esc_html__( 'Design', 'fusion-builder' ),
						'remove_from_atts' => true,
						'value'            => [
							'regular' => esc_html__( 'Regular', 'fusion-builder' ),
							'hover'   => esc_html__( 'Hover / Active', 'fusion-builder' ),
						],
						'icons'            => [
							'regular' => '<span class="fusiona-regular-state" style="font-size:18px;"></span>',
							'hover'   => '<span class="fusiona-hover-state" style="font-size:18px;"></span>',
						],
						'dependency'       => [
							[
								'element'  => 'product_link_style',
								'value'    => 'custom',
								'operator' => '!=',
							],
							[
								'element'  => 'show_product_link_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Product Link Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the link.', 'fusion-builder' ),
						'param_name'  => 'product_link_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_accent_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'product_link_styling_options',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'product_link_style',
								'value'    => 'custom',
								'operator' => '!=',
							],
							[
								'element'  => 'show_product_link_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Product Link Text Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text hover color of the link.', 'fusion-builder' ),
						'param_name'  => 'product_link_hover_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'primary_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'product_link_styling_options',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'product_link_style',
								'value'    => 'custom',
								'operator' => '!=',
							],
							[
								'element'  => 'show_product_link_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the button size.', 'fusion-builder' ),
						'param_name'  => 'button_details_size',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'small'  => esc_attr__( 'Small', 'fusion-builder' ),
							'medium' => esc_attr__( 'Medium', 'fusion-builder' ),
							'large'  => esc_attr__( 'Large', 'fusion-builder' ),
							'xlarge' => esc_attr__( 'XLarge', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
						'dependency'  => [
							[
								'element'  => 'product_link_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'show_product_link_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Button Border Size', 'fusion-builder' ),
						'param_name'  => 'button_details_border_width',
						'description' => esc_attr__( 'Controls the border size. In pixels.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'product_link_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'show_product_link_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'min'         => '0',
						'max'         => '20',
						'step'        => '1',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_border_width' ),
						'callback'    => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Icon', 'fusion-builder' ),
						'param_name'  => 'button_details_icon',
						'value'       => '',
						'description' => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'product_link_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'show_product_link_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Icon Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the position of the icon on the button.', 'fusion-builder' ),
						'param_name'  => 'icon_details_position',
						'value'       => [
							'left'  => esc_attr__( 'Left', 'fusion-builder' ),
							'right' => esc_attr__( 'Right', 'fusion-builder' ),
						],
						'default'     => 'left',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'product_link_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'button_icon',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'show_product_link_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'Button Styling', 'fusion-builder' ),
						'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
						'param_name'       => 'button_details_styling',
						'default'          => 'regular',
						'group'            => esc_html__( 'Design', 'fusion-builder' ),
						'remove_from_atts' => true,
						'value'            => [
							'regular' => esc_html__( 'Regular', 'fusion-builder' ),
							'hover'   => esc_html__( 'Hover / Active', 'fusion-builder' ),
						],
						'icons'            => [
							'regular' => '<span class="fusiona-regular-state" style="font-size:18px;"></span>',
							'hover'   => '<span class="fusiona-hover-state" style="font-size:18px;"></span>',
						],
						'dependency'       => [
							[
								'element'  => 'product_link_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'show_product_link_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_details_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_accent_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'button_details_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'product_link_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'show_product_link_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Top Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_details_gradient_top',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_gradient_top_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'button_details_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'product_link_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'show_product_link_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Bottom Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_details_gradient_bottom',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_bottom_color' ),
						'subgroup'    => [
							'name' => 'button_details_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'product_link_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'show_product_link_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_details_border_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_border_color' ),
						'subgroup'    => [
							'name' => 'button_details_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'product_link_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'show_product_link_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Text Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_details_color_hover',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_accent_hover_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'button_details_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'product_link_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'show_product_link_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Top Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_details_gradient_top_hover',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_gradient_top_color_hover' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'button_details_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'product_link_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'show_product_link_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Bottom Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_details_gradient_bottom_hover',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_bottom_color_hover' ),
						'subgroup'    => [
							'name' => 'button_details_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'product_link_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'show_product_link_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Border Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_details_border_color_hover',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_border_hover_color' ),
						'subgroup'    => [
							'name' => 'button_details_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'product_link_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'show_product_link_button',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],

					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-post-card-cart',
					],
				],
				'callback'  => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_post_card_cart',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'wp_loaded', 'fusion_element_post_card_cart' );
