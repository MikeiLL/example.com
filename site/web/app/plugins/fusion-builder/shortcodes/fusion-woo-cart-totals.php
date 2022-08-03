<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.3
 */

if ( class_exists( 'WooCommerce' ) ) {

	if ( ! class_exists( 'FusionSC_WooCartTotals' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.3
		 */
		class FusionSC_WooCartTotals extends Fusion_Element {

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
				parent::__construct();
				add_filter( 'fusion_attr_woo-cart-totals-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_woo-cart-totals-shortcode-wrapper', [ $this, 'wrapper_attr' ] );
				add_shortcode( 'fusion_woo_cart_totals', [ $this, 'render' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_fusion_get_woo_cart_totals', [ $this, 'ajax_query' ] );
			}


			/**
			 * Gets the query data.
			 *
			 * @access public
			 * @since 3.3
			 * @param array $defaults An array of defaults.
			 * @return void
			 */
			public function ajax_query( $defaults ) {
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );
				$this->args = $_POST['model']['params']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				$html       = $this->generate_element_content();

				echo wp_json_encode( $html );
				wp_die();
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
					// Element margin.
					'margin_top'                       => '',
					'margin_right'                     => '',
					'margin_bottom'                    => '',
					'margin_left'                      => '',

					// Element margin.
					'button_margin_top'                => '',
					'button_margin_right'              => '',
					'button_margin_bottom'             => '',
					'button_margin_left'               => '',

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

					'class'                            => '',
					'id'                               => '',
					'animation_type'                   => '',
					'animation_direction'              => 'down',
					'animation_speed'                  => '0.1',
					'animation_offset'                 => $fusion_settings->get( 'animation_offset' ),

					'buttons_visibility'               => '',
					'buttons_layout'                   => '',
					'floated_buttons_alignment'        => '',
					'stacked_buttons_alignment'        => '',
					'button_span'                      => '',
				];
			}


			/**
			 * Render the shortcode.
			 *
			 * @access public
			 * @since 3.3
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output
			 */
			public function render( $args, $content = '' ) {
				if ( WC()->cart->is_empty() && ! fusion_is_preview_frame() ) {
					return;
				}
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_tb_woo_cart_totals' );
				WC()->cart->calculate_totals();
				ob_start();
				?>


				<div <?php echo FusionBuilder::attributes( 'woo-cart-totals-shortcode-wrapper' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<table cellspacing="0" <?php echo FusionBuilder::attributes( 'woo-cart-totals-shortcode' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php echo $this->generate_element_content(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>

				<?php
				$html  = ob_get_clean();
				$html .= $this->get_styles();

				$this->on_render();
				$this->counter++;
				return apply_filters( 'fusion_element_cart_totals_content', $html, $args );

			}

			/**
			 * Generates element content
			 *
			 * @return string
			 */
			public function generate_element_content() {
				// phpcs:disable WordPress.Security
				ob_start();
				?>
				<?php echo $this->get_table_subtotal(); ?>

				<?php echo $this->get_table_coupons(); ?>

				<?php echo $this->get_table_shipping(); ?>

				<?php echo $this->get_table_fee(); ?>

				<?php echo $this->get_table_tax(); ?>

				<?php echo $this->get_table_total(); ?>

				</table>
				<div class="wc-proceed-to-checkout">
						<?php echo $this->get_button_update_cart(); ?>
						<?php echo $this->get_button_checkout(); ?>
				</div>

				<?php
				// phpcs:enable WordPress.Security
				return ob_get_clean();
			}

			/**
			 * Generates subtotal row
			 *
			 * @return string
			 */
			public function get_table_subtotal() {
				ob_start();
				?>
					<tr class="cart-subtotal">
						<th><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
						<td data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce' ); ?>"><?php wc_cart_totals_subtotal_html(); ?></td>
					</tr>
				<?php
				return ob_get_clean();
			}


			/**
			 * Generates coupons row
			 *
			 * @return string
			 */
			public function get_table_coupons() {
				ob_start();
				foreach ( WC()->cart->get_coupons() as $code => $coupon ) :
					?>
					<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
						<td data-title="<?php echo esc_attr( wc_cart_totals_coupon_label( $coupon, false ) ); ?>"><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
					</tr>
					<?php
				endforeach;
				return ob_get_clean();
			}


			/**
			 * Generates shipping row
			 *
			 * @return string
			 */
			public function get_table_shipping() {

				ob_start();

				if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) :
					if ( empty( WC()->shipping()->get_packages() ) ) {
						WC()->cart->calculate_totals();
					}
					do_action( 'woocommerce_cart_totals_before_shipping' );
					echo wc_cart_totals_shipping_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					do_action( 'woocommerce_cart_totals_after_shipping' );

				elseif ( WC()->cart->needs_shipping() && 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) :
					?>
					<tr class="shipping">
						<th><?php esc_html_e( 'Shipping', 'woocommerce' ); ?></th>
						<td data-title="<?php esc_attr_e( 'Shipping', 'woocommerce' ); ?>"><?php woocommerce_shipping_calculator(); ?></td>
					</tr>
					<?php
				endif;
				return ob_get_clean();
			}


			/**
			 * Generates fee row
			 *
			 * @return string
			 */
			public function get_table_fee() {
				ob_start();
				foreach ( WC()->cart->get_fees() as $fee ) :
					?>
					<tr class="fee">
						<th><?php echo esc_html( $fee->name ); ?></th>
						<td data-title="<?php echo esc_attr( $fee->name ); ?>"><?php wc_cart_totals_fee_html( $fee ); ?></td>
					</tr>
					<?php
				endforeach;
				return ob_get_clean();
			}


			/**
			 * Generates tax row
			 *
			 * @return string
			 */
			public function get_table_tax() {
				ob_start();
				if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) {
					$taxable_address = WC()->customer->get_taxable_address();
					$estimated_text  = '';

					if ( WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping() ) {
						/* translators: %s location. */
						$estimated_text = sprintf( ' <small>' . esc_html__( '(estimated for %s)', 'woocommerce' ) . '</small>', WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] );
					}

					if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
						foreach ( WC()->cart->get_tax_totals() as $code => $tax ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
							?>
							<tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
								<th><?php echo esc_html( $tax->label ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></th>
								<td data-title="<?php echo esc_attr( $tax->label ); ?>"><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
							</tr>
							<?php
						}
					} else {
						?>
						<tr class="tax-total">
							<th><?php echo esc_html( WC()->countries->tax_or_vat() ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></th>
							<td data-title="<?php echo esc_attr( WC()->countries->tax_or_vat() ); ?>"><?php wc_cart_totals_taxes_total_html(); ?></td>
						</tr>
						<?php
					}
				}
				return ob_get_clean();
			}


			/**
			 * Generates the 'Total' row
			 *
			 * @sisnce 3.3
			 * @return string
			 */
			public function get_table_total() {
				ob_start();
				do_action( 'woocommerce_cart_totals_before_order_total' );
				?>
					<tr class="order-total">
						<th><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
						<td data-title="<?php esc_attr__( 'Total', 'woocommerce' ); ?>"><?php wc_cart_totals_order_total_html(); ?></td>
					</tr>
				<?php
				do_action( 'woocommerce_cart_totals_after_order_total' );
				$html = ob_get_clean();
				return $html;
			}


			/**
			 * Generates the 'Update Card' button
			 *
			 * @sisnce 3.3
			 * @return string
			 */
			public function get_button_update_cart() {
				return 'show' === $this->args['buttons_visibility'] ?
						'<a href="" class="fusion-button button-default fusion-button-default-size button fusion-update-cart">
							' . esc_attr__( 'Update cart', 'woocommerce' ) . '
						</a>' : '';
			}


			/**
			 * Generates the 'Procced to Checkout' button
			 *
			 * @since 3.3
			 * @return string
			 */
			public function get_button_checkout() {
				return 'show' === $this->args['buttons_visibility'] ?
						'<a href="' . esc_url( wc_get_checkout_url() ) . '" class="fusion-button button-default fusion-button-default-size button checkout-button button alt wc-forward">
							' . esc_html__( 'Proceed to checkout', 'woocommerce' ) . '
						</a>' : '';
			}


			/**
			 * Generates the element styles
			 *
			 * @access protected
			 * @since 3.3
			 * @return string
			 */
			public function get_styles() {
				$this->base_selector = '.fusion-woo-cart-totals-' . $this->counter;
				$this->dynamic_css   = [];

				if ( ! $this->is_default( 'margin_top' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-top', $this->args['margin_top'] );
				}

				if ( ! $this->is_default( 'margin_bottom' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-bottom', $this->args['margin_bottom'] );
				}

				if ( ! $this->is_default( 'margin_left' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-left', $this->args['margin_left'] );
				}

				if ( ! $this->is_default( 'margin_right' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-right', $this->args['margin_right'] );
				}

				$selector = $this->base_selector . ' tbody tr td, ' . $this->base_selector . ' tbody tr th';
				if ( ! $this->is_default( 'cell_padding_top' ) ) {
					$this->add_css_property( $selector, 'padding-top', $this->args['cell_padding_top'], true );
				}

				if ( ! $this->is_default( 'cell_padding_bottom' ) ) {
					$this->add_css_property( $selector, 'padding-bottom', $this->args['cell_padding_bottom'], true );
				}

				if ( ! $this->is_default( 'cell_padding_left' ) ) {
					$this->add_css_property( $selector, 'padding-left', $this->args['cell_padding_left'], true );
				}

				if ( ! $this->is_default( 'cell_padding_right' ) ) {
					$this->add_css_property( $selector, 'padding-right', $this->args['cell_padding_right'], true );
				}

				$selector = $this->base_selector . ' tbody tr th';
				if ( ! $this->is_default( 'heading_cell_backgroundcolor' ) ) {
					$this->add_css_property( $selector, 'background-color', $this->args['heading_cell_backgroundcolor'] );
				}

				if ( ! $this->is_default( 'fusion_font_family_heading_font' ) ) {
					$this->add_css_property( $selector, 'font-family', $this->args['fusion_font_family_heading_font'] );
				}

				if ( ! $this->is_default( 'fusion_font_variant_heading_font' ) ) {
					$this->add_css_property( $selector, 'font-weight', $this->args['fusion_font_variant_heading_font'] );
				}

				if ( ! $this->is_default( 'heading_font_size' ) ) {
					$this->add_css_property( $selector, 'font-size', $this->args['heading_font_size'] );
				}

				$selector = $this->base_selector . ' tbody tr td';
				if ( ! $this->is_default( 'table_cell_backgroundcolor' ) ) {
					$this->add_css_property( $selector, 'background-color', $this->args['table_cell_backgroundcolor'] );
				}

				$text_selector = $selector . ', ' . $this->base_selector . ' a, ' . $this->base_selector . ' .amount';
				if ( ! $this->is_default( 'text_color' ) ) {
					$this->add_css_property( $text_selector, 'color', $this->args['text_color'], true );
				}

				if ( ! $this->is_default( 'heading_color' ) ) {
					$this->add_css_property( $this->base_selector . ' tbody tr th', 'color', $this->args['heading_color'], true );
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

				$selector = $this->base_selector . ' tr, ' . $this->base_selector . ' tr td, ' . $this->base_selector . ' tr th';
				if ( ! $this->is_default( 'border_color' ) ) {
					$this->add_css_property( $selector, 'border-color', $this->args['border_color'], true );
				}

				$selector = '.fusion-woo-cart-totals-wrapper-' . $this->counter . ' div.wc-proceed-to-checkout';
				if ( 'floated' === $this->args['buttons_layout'] ) {
					$this->add_css_property( $selector, 'flex-direction', 'row' );

					if ( 'yes' === $this->args['button_span'] ) {
						$this->add_css_property( $selector, 'justify-content', 'stretch', true );
						$this->add_css_property( $selector . ' a', 'flex', '1' );
					} else {
						$this->add_css_property( $selector, 'justify-content', $this->args['floated_buttons_alignment'], true );
					}
				} else {
					$this->add_css_property( $selector, 'flex-direction', 'column', true );
					$this->add_css_property( $selector, 'align-items', $this->args['stacked_buttons_alignment'], true );
					if ( 'yes' === $this->args['button_span'] ) {
						$this->add_css_property( $selector, 'align-items', 'stretch', true );
					} else {
						$this->add_css_property( $selector, 'align-items', $this->args['stacked_buttons_alignment'], true );
					}
				}

				if ( ! $this->is_default( 'button_margin_top' ) ) {
					$this->add_css_property( $selector . ' a', 'margin-top', $this->args['button_margin_top'] );
				}

				if ( ! $this->is_default( 'button_margin_bottom' ) ) {
					$this->add_css_property( $selector . ' a', 'margin-bottom', $this->args['button_margin_bottom'] );
				}

				if ( ! $this->is_default( 'button_margin_left' ) ) {
					$this->add_css_property( $selector . ' a', 'margin-left', $this->args['button_margin_left'] );
				}

				if ( ! $this->is_default( 'button_margin_right' ) ) {
					$this->add_css_property( $selector . ' a', 'margin-right', $this->args['button_margin_right'] );
				}

				$css = $this->parse_css();

				return $css ? '<style>' . $css . '</style>' : '';
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
					'class' => 'shop_table shop_table_responsive fusion-woo-cart-totals fusion-woo-cart-totals-' . $this->counter,
					'style' => '',
				];

				return $attr;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.3
			 * @return array
			 */
			public function wrapper_attr() {

				$attr = [
					'class' => 'cart_totals fusion-woo-cart-totals-wrapper fusion-woo-cart-totals-wrapper-' . $this->counter,
					'style' => '',
				];
				if ( WC()->customer->has_calculated_shipping() ) {
					$attr['class'] .= ' calculated_shipping';
				}

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
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/woo-cart-totals.min.css' );
			}
		}
	}

	new FusionSC_WooCartTotals();

}

/**
 * Map shortcode to Avada Builder.
 */
function fusion_element_woo_cart_totals() {
	if ( class_exists( 'WooCommerce' ) ) {
		fusion_builder_map(
			fusion_builder_frontend_data(
				'FusionSC_WooCartTotals',
				[
					'name'          => esc_attr__( 'Woo Cart Totals', 'fusion-builder' ),
					'shortcode'     => 'fusion_woo_cart_totals',
					'icon'          => 'fusiona-cart-totals',
					'help_url'      => '',
					'inline_editor' => true,
					'params'        => [
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Show Buttons', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose to show or hide buttons.', 'fusion-builder' ),
							'param_name'  => 'buttons_visibility',
							'default'     => 'show',
							'value'       => [
								'show' => esc_html__( 'Show', 'fusion-builder' ),
								'hide' => esc_html__( 'Hide', 'fusion-builder' ),
							],
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'fusion_get_woo_cart_totals',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Buttons Layout', 'fusion-builder' ),
							'description' => esc_attr__( 'Select the layout of buttons.', 'fusion-builder' ),
							'param_name'  => 'buttons_layout',
							'value'       => [
								'floated' => esc_attr__( 'Floated', 'fusion-builder' ),
								'stacked' => esc_attr__( 'Stacked', 'fusion-builder' ),
							],
							'default'     => 'floated',
							'dependency'  => [
								[
									'element'  => 'buttons_visibility',
									'value'    => 'show',
									'operator' => '==',
								],
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_html__( 'Buttons Horizontal Align', 'fusion-builder' ),
							'description' => esc_html__( 'Change the horizontal alignment of buttons within its container column.', 'fusion-builder' ),
							'param_name'  => 'floated_buttons_alignment',
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
								// We use "start/end" terminology because flex direction changes depending on RTL/LTR.
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
									'element'  => 'buttons_visibility',
									'value'    => 'show',
									'operator' => '==',
								],
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_html__( 'Buttons Horizontal Align', 'fusion-builder' ),
							'description' => esc_html__( 'Change the horizontal alignment of buttons within its container column.', 'fusion-builder' ),
							'param_name'  => 'stacked_buttons_alignment',
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
							'default'     => 'flex-start',
							'dependency'  => [
								[
									'element'  => 'buttons_layout',
									'value'    => 'stacked',
									'operator' => '==',
								],
								[
									'element'  => 'buttons_visibility',
									'value'    => 'show',
									'operator' => '==',
								],
							],

						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Button Span', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose to have the button span the full width.', 'fusion-builder' ),
							'param_name'  => 'button_span',
							'value'       => [
								'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
								'no'  => esc_attr__( 'No', 'fusion-builder' ),
							],
							'default'     => 'no',
							'dependency'  => [
								[
									'element'  => 'buttons_visibility',
									'value'    => 'show',
									'operator' => '==',
								],
							],
						],
						[
							'type'             => 'dimension',
							'remove_from_atts' => true,
							'heading'          => esc_attr__( 'Buttons Margin', 'fusion-builder' ),
							'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
							'param_name'       => 'buttonsmargin',
							'value'            => [
								'button_margin_top'    => '',
								'button_margin_right'  => '',
								'button_margin_bottom' => '',
								'button_margin_left'   => '',
							],
							'dependency'       => [
								[
									'element'  => 'buttons_visibility',
									'value'    => 'show',
									'operator' => '==',
								],
							],
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
							'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						],
						'fusion_animation_placeholder' => [
							'preview_selector' => '.fusion-woo-cart-totals-wrapper',
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

					],
					'callback'      => [
						'function' => 'fusion_ajax',
						'action'   => 'fusion_get_woo_cart_totals',
						'ajax'     => true,
					],
				]
			)
		);
	}
}
add_action( 'wp_loaded', 'fusion_element_woo_cart_totals' );
