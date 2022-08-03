<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_social_links' ) ) {

	if ( ! class_exists( 'FusionSC_SocialLinks' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_SocialLinks extends Fusion_Element {

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
				add_filter( 'fusion_attr_social-links-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_social-links-shortcode-social-networks', [ $this, 'social_networks_attr' ] );
				add_filter( 'fusion_attr_social-links-shortcode-icon', [ $this, 'icon_attr' ] );

				add_shortcode( 'fusion_social_links', [ $this, 'render' ] );

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
					'hide_on_mobile'     => fusion_builder_default_visibility( 'string' ),
					'sticky_display'     => '',
					'class'              => '',
					'id'                 => '',
					'font_size'          => $fusion_settings->get( 'social_links_font_size' ),
					'icons_boxed'        => ( 1 == $fusion_settings->get( 'social_links_boxed' ) ) ? 'yes' : $fusion_settings->get( 'social_links_boxed' ), // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					'icons_boxed_radius' => fusion_library()->sanitize->size( $fusion_settings->get( 'social_links_boxed_radius' ) ),
					'color_type'         => $fusion_settings->get( 'social_links_color_type' ),
					'icon_colors'        => $fusion_settings->get( 'social_links_icon_color' ),
					'box_colors'         => $fusion_settings->get( 'social_links_box_color' ),
					'icon_order'         => '',
					'show_custom'        => 'no',
					'alignment'          => '',
					'tooltip_placement'  => strtolower( $fusion_settings->get( 'social_links_tooltip_placement' ) ),
					'facebook'           => '',
					'tiktok'             => '',
					'twitch'             => '',
					'twitter'            => '',
					'instagram'          => '',
					'linkedin'           => '',
					'dribbble'           => '',
					'rss'                => '',
					'youtube'            => '',
					'pinterest'          => '',
					'flickr'             => '',
					'vimeo'              => '',
					'tumblr'             => '',
					'discord'            => '',
					'digg'               => '',
					'blogger'            => '',
					'skype'              => '',
					'myspace'            => '',
					'deviantart'         => '',
					'yahoo'              => '',
					'reddit'             => '',
					'forrst'             => '',
					'paypal'             => '',
					'dropbox'            => '',
					'soundcloud'         => '',
					'vk'                 => '',
					'wechat'             => '',
					'whatsapp'           => '',
					'xing'               => '',
					'yelp'               => '',
					'spotify'            => '',
					'email'              => '',
					'phone'              => '',
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
					'social_links_boxed'             => [
						'param'    => 'icons_boxed',
						'callback' => 'toYes',
					],
					'social_links_boxed_radius'      => 'icons_boxed_radius',
					'social_links_color_type'        => 'color_type',
					'social_links_icon_color'        => 'icon_colors',
					'social_links_box_color'         => 'box_colors',
					'social_links_font_size'         => 'font_size',
					'social_links_tooltip_placement' => [
						'param'    => 'tooltip_placement',
						'callback' => 'toLowerCase',
					],
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
				$fusion_settings = fusion_get_fusion_settings();
				return [
					'linktarget'              => $fusion_settings->get( 'social_icons_new' ),
					'social_links_box_color'  => $fusion_settings->get( 'social_links_box_color' ),
					'social_links_icon_color' => $fusion_settings->get( 'social_links_icon_color' ),
					'social_media_icons'      => $fusion_settings->get( 'social_media_icons' ),
					'boxed_padding'           => $fusion_settings->get( 'social_links_boxed_padding' ),
				];
			}

			/**
			 * Maps settings to extra variables.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function settings_to_extras() {

				return [
					'social_icons_new'        => 'linktarget',
					'social_links_box_color'  => 'social_links_box_color',
					'social_links_icon_color' => 'social_links_icon_color',
					'social_media_icons'      => 'social_media_icons',
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

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_social_links' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_social_links', $args );

				foreach ( $args as $key => $arg ) {
					if ( false !== strpos( $key, 'custom_' ) ) {
						$defaults[ $key ] = $arg;
					}
				}
				$defaults['icons_boxed_radius'] = FusionBuilder::validate_shortcode_attr_value( $defaults['icons_boxed_radius'], 'px' );
				$defaults['font_size']          = FusionBuilder::validate_shortcode_attr_value( $defaults['font_size'], 'px' );

				extract( $defaults );

				$this->args = $defaults;

				if ( empty( $defaults['color_type'] ) ) {
					$defaults['box_colors']  = $fusion_settings->get( 'social_links_box_color' );
					$defaults['icon_colors'] = $fusion_settings->get( 'social_links_icon_color' );
				}

				$social_networks = fusion_builder_get_social_networks( $defaults );

				$social_networks = fusion_builder_sort_social_networks( $social_networks );

				$icons = fusion_builder_build_social_links( $social_networks, 'social-links-shortcode-icon', $defaults );

				$html  = '<div ' . FusionBuilder::attributes( 'social-links-shortcode' ) . '>';
				$html .= '<div ' . FusionBuilder::attributes( 'social-links-shortcode-social-networks' ) . '>';
				$html .= '<div ' . FusionBuilder::attributes( 'fusion-social-networks-wrapper' ) . '>';
				$html .= $icons;
				$html .= '</div>';
				$html .= '</div>';
				$html .= '</div>';

				if ( $alignment && ! fusion_element_rendering_is_flex() ) {
					$html = '<div class="align' . $alignment . '">' . $html . '</div>';
				}

				$this->on_render();

				return apply_filters( 'fusion_element_social_links_content', $html, $args );

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
					$this->args['hide_on_mobile'],
					[
						'class' => 'fusion-social-links',
					]
				);

				$attr['class'] .= Fusion_Builder_Sticky_Visibility_Helper::get_sticky_class( $this->args['sticky_display'] );

				if ( fusion_element_rendering_is_flex() && $this->args['alignment'] ) {
					$attr['style'] = 'text-align:' . $this->args['alignment'];
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
			 * Builds the social-networks attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function social_networks_attr() {

				$attr = [
					'class' => 'fusion-social-networks',
				];

				if ( 'yes' === $this->args['icons_boxed'] ) {
					$attr['class'] .= ' boxed-icons';
				}

				return $attr;

			}

			/**
			 * Builds the icon attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @param array $args The arguments array.
			 * @return array
			 */
			public function icon_attr( $args ) {

				global $fusion_settings;

				$attr = [
					'class' => '',
					'style' => '',
				];

				$tooltip = Fusion_Social_Icon::get_social_network_name( $args['social_network'] );
				if ( '' === $args['social_network'] || 'custom_' === substr( $args['social_network'], 0, 7 ) ) {
					$attr['class']         .= 'custom ';
					$args['social_network'] = strtolower( str_replace( ' ', '-', $tooltip ) );
				}

				if ( 'none' !== strtolower( $this->args['tooltip_placement'] ) ) {
					$attr['data-placement'] = strtolower( $this->args['tooltip_placement'] );
					$attr['data-title']     = $tooltip;
					$attr['data-toggle']    = 'tooltip';
				}

				$attr['title'] = $tooltip;

				$attr['class'] .= 'fusion-social-network-icon fusion-tooltip fusion-' . $args['social_network'] . ' fusion-icon-' . $args['social_network'];

				$attr['aria-label'] = $args['social_network'];

				$link = $args['social_link'];

				$attr['target'] = ( $fusion_settings->get( 'social_icons_new' ) ) ? '_blank' : '_self';

				if ( '_blank' === $attr['target'] ) {
					$attr['rel'] = 'noopener noreferrer';
				}

				if ( 'mail' === $args['social_network'] ) {
					$link = $args['social_link'];
					if ( 'http' !== substr( $args['social_link'], 0, 4 ) ) {
						if ( apply_filters( 'fusion_disable_antispambot', false ) ) {
							$link = 'mailto:' . str_replace( 'mailto:', '', $args['social_link'] );
						} else {
							$link = 'mailto:' . antispambot( str_replace( 'mailto:', '', $args['social_link'] ) );
						}
					}
				}

				if ( 'phone' === $args['social_network'] ) {
					$link           = 'tel:' . str_replace( 'tel:', '', $args['social_link'] );
					$attr['target'] = '_self';
				}

				$attr['href'] = $link;

				if ( $fusion_settings->get( 'nofollow_social_links' ) ) {
					$attr['rel'] = ( isset( $attr['rel'] ) ) ? $attr['rel'] . ' nofollow' : 'nofollow';
				}

				if ( $args['icon_color'] ) {
					$attr['style'] = 'color:' . $args['icon_color'] . ';';
				}

				if ( $this->args['font_size'] ) {
					$attr['style'] .= 'font-size:' . $this->args['font_size'] . ';';

					if ( 'yes' === $this->args['icons_boxed'] ) {
						$attr['style'] .= 'width: calc(' . $this->args['font_size'] . ' + (2 * (' . $fusion_settings->get( 'social_links_boxed_padding' ) . ')) + 2px);';
					}
				}

				if ( 'yes' === $this->args['icons_boxed'] && $args['box_color'] ) {
					$attr['style'] .= 'background-color:' . $args['box_color'] . ';border-color:' . $args['box_color'] . ';';
				}

				if ( 'yes' === $this->args['icons_boxed'] && $this->args['icons_boxed_radius'] || '0' === $this->args['icons_boxed_radius'] ) {
					if ( 'round' === $this->args['icons_boxed_radius'] ) {
						$this->args['icons_boxed_radius'] = '50%';
					}
					$attr['style'] .= 'border-radius:' . $this->args['icons_boxed_radius'] . ';';
				}

				return $attr;

			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Social Links settings.
			 */
			public function add_options() {
				global $fusion_settings;

				return [
					'social_links_shortcode_section' => [
						'label'       => esc_html__( 'Social Links Element', 'fusion-builder' ),
						'description' => '',
						'id'          => 'social_links_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-link',
						'fields'      => [
							'social_links_info'          => [
								'id'          => 'social_links_info',
								'type'        => 'custom',
								'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> These social icon global options control both the social link element and person element.', 'fusion-builder' ) . '</div>',
							],
							'social_links_font_size'     => [
								'label'       => esc_html__( 'Social Links Icons Font Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the font size for the social link icons.', 'fusion-builder' ),
								'id'          => 'social_links_font_size',
								'default'     => '16px',
								'type'        => 'dimension',
								'css_vars'    => [
									[
										'name'    => '--social_links_font_size',
										'element' => '.fusion-social-links',
									],
								],
							],
							'social_links_color_type'    => [
								'label'       => esc_html__( 'Social Links Icon Color Type', 'fusion-builder' ),
								'description' => esc_html__( 'Custom colors allow you to choose a color for icons and boxes. Brand colors will use the exact brand color of each network for the icons or boxes.', 'fusion-builder' ),
								'id'          => 'social_links_color_type',
								'default'     => 'custom',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'custom' => esc_html__( 'Custom Colors', 'fusion-builder' ),
									'brand'  => esc_html__( 'Brand Colors', 'fusion-builder' ),
								],
							],
							'social_links_icon_color'    => [
								'label'           => esc_html__( 'Social Links Custom Icons Color', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the color of the custom icons.', 'fusion-builder' ),
								'id'              => 'social_links_icon_color',
								'default'         => '#9ea0a4',
								'type'            => 'color-alpha',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
							],
							'social_links_boxed'         => [
								'label'       => esc_html__( 'Social Links Icons Boxed', 'fusion-builder' ),
								'description' => esc_html__( 'Turn on to have the icon displayed in a small box. Turn off to have the icon displayed with no box.', 'fusion-builder' ),
								'id'          => 'social_links_boxed',
								'default'     => '0',
								'type'        => 'switch',
								'transport'   => 'postMessage',
							],
							'social_links_box_color'     => [
								'label'           => esc_html__( 'Social Links Icons Custom Box Color', 'fusion-builder' ),
								'description'     => esc_html__( 'Select a custom social icon box color.', 'fusion-builder' ),
								'id'              => 'social_links_box_color',
								'default'         => '#f2f3f5',
								'type'            => 'color-alpha',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
							],
							'social_links_boxed_radius'  => [
								'label'           => esc_html__( 'Social Links Icons Boxed Radius', 'fusion-builder' ),
								'description'     => esc_html__( 'Box radius for the social icons.', 'fusion-builder' ),
								'id'              => 'social_links_boxed_radius',
								'default'         => '4px',
								'type'            => 'dimension',
								'choices'         => [ 'px', 'em' ],
								'transport'       => 'postMessage',
								'soft_dependency' => true,
							],
							'social_links_boxed_padding' => [
								'label'           => esc_html__( 'Social Links Icons Boxed Padding', 'fusion-builder' ),
								'id'              => 'social_links_boxed_padding',
								'default'         => '8px',
								'type'            => 'dimension',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
								'css_vars'        => [
									[
										'name'    => '--social_links_boxed_padding',
										'element' => '.fusion-social-links',
									],
								],
							],
							'social_links_tooltip_placement' => [
								'label'       => esc_html__( 'Social Links Icons Tooltip Position', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the tooltip position of the social links icons.', 'fusion-builder' ),
								'id'          => 'social_links_tooltip_placement',
								'default'     => 'Top',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'Top'    => esc_html__( 'Top', 'fusion-builder' ),
									'Right'  => esc_html__( 'Right', 'fusion-builder' ),
									'Bottom' => esc_html__( 'Bottom', 'fusion-builder' ),
									'Left'   => esc_html__( 'Left', 'fusion-builder' ),
									'None'   => esc_html__( 'None', 'fusion-builder' ),
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
				Fusion_Dynamic_JS::enqueue_script( 'fusion-tooltip' );
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/social-links.min.css' );
			}
		}
	}

	new FusionSC_SocialLinks();

}
/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_social_links() {
	$social_options         = [
		'name'      => esc_attr__( 'Social Links', 'fusion-builder' ),
		'shortcode' => 'fusion_social_links',
		'icon'      => 'fusiona-link',
		'help_url'  => 'https://theme-fusion.com/documentation/fusion-builder/elements/social-links-element/',
		'params'    => [
			[
				'type'        => 'textfield',
				'heading'     => esc_attr__( 'Social Icons Font Size', 'fusion-builder' ),
				'description' => esc_attr__( 'Controls the font size for the social icons. Enter value including CSS unit (px, em, rem), ex: 10px', 'fusion-builder' ),
				'param_name'  => 'font_size',
				'value'       => '',
				'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Boxed Social Icons', 'fusion-builder' ),
				'description' => esc_attr__( 'Choose to get boxed icons.', 'fusion-builder' ),
				'param_name'  => 'icons_boxed',
				'value'       => [
					''    => esc_attr__( 'Default', 'fusion-builder' ),
					'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
					'no'  => esc_attr__( 'No', 'fusion-builder' ),
				],
				'default'     => '',
				'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			],
			[
				'type'        => 'textfield',
				'heading'     => esc_attr__( 'Social Icon Box Radius', 'fusion-builder' ),
				'description' => esc_attr__( 'Choose the border radius of the boxed icons. In pixels (px), ex: 1px, or "round". ', 'fusion-builder' ),
				'param_name'  => 'icons_boxed_radius',
				'value'       => '',
				'dependency'  => [
					[
						'element'  => 'icons_boxed',
						'value'    => 'no',
						'operator' => '!=',
					],
				],
				'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Social Icons Color Type', 'fusion-builder' ),
				'description' => esc_attr__( 'Choose the color type of social icons. Brand colors will use the exact brand color of each network for the icons or boxes.', 'fusion-builder' ),
				'param_name'  => 'color_type',
				'value'       => [
					''       => esc_attr__( 'Default', 'fusion-builder' ),
					'custom' => esc_attr__( 'Custom Colors', 'fusion-builder' ),
					'brand'  => esc_attr__( 'Brand Colors', 'fusion-builder' ),
				],
				'default'     => '',
				'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			],
			[
				'type'        => 'textarea',
				'heading'     => esc_attr__( 'Social Icon Custom Colors', 'fusion-builder' ),
				'description' => esc_attr__( 'Specify the color of social icons.', 'fusion-builder' ),
				'param_name'  => 'icon_colors',
				'value'       => '',
				'dependency'  => [
					[
						'element'  => 'color_type',
						'value'    => 'brand',
						'operator' => '!=',
					],
				],
				'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			],
			[
				'type'        => 'textarea',
				'heading'     => esc_attr__( 'Social Icon Custom Box Colors', 'fusion-builder' ),
				'description' => esc_attr__( 'Specify the box color of social icons.', 'fusion-builder' ),
				'param_name'  => 'box_colors',
				'value'       => '',
				'dependency'  => [
					[
						'element'  => 'icons_boxed',
						'value'    => 'no',
						'operator' => '!=',
					],
					[
						'element'  => 'color_type',
						'value'    => 'brand',
						'operator' => '!=',
					],
				],
				'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Social Icon Tooltip Position', 'fusion-builder' ),
				'description' => esc_attr__( 'Choose the display position for tooltips.', 'fusion-builder' ),
				'param_name'  => 'tooltip_placement',
				'value'       => [
					''       => esc_attr__( 'Default', 'fusion-builder' ),
					'top'    => esc_attr__( 'Top', 'fusion-builder' ),
					'bottom' => esc_attr__( 'Bottom', 'fusion-builder' ),
					'left'   => esc_attr__( 'Left', 'fusion-builder' ),
					'Right'  => esc_attr__( 'Right', 'fusion-builder' ),
					'none'   => esc_html__( 'None', 'fusion-builder' ),
				],
				'default'     => '',
				'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Blogger Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Blogger link.', 'fusion-builder' ),
				'param_name'   => 'blogger',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Deviantart Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Deviantart link.', 'fusion-builder' ),
				'param_name'   => 'deviantart',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Discord Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Discord link.', 'fusion-builder' ),
				'param_name'   => 'discord',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Digg Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Digg link.', 'fusion-builder' ),
				'param_name'   => 'digg',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Dribbble Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Dribbble link.', 'fusion-builder' ),
				'param_name'   => 'dribbble',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Dropbox Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Dropbox link.', 'fusion-builder' ),
				'param_name'   => 'dropbox',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Facebook Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Facebook link.', 'fusion-builder' ),
				'param_name'   => 'facebook',
				'value'        => '#',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Flickr Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Flickr link.', 'fusion-builder' ),
				'param_name'   => 'flickr',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Forrst Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Forrst link.', 'fusion-builder' ),
				'param_name'   => 'forrst',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Instagram Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Instagram link.', 'fusion-builder' ),
				'param_name'   => 'instagram',
				'value'        => '#',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'LinkedIn Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom LinkedIn link.', 'fusion-builder' ),
				'param_name'   => 'linkedin',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Myspace Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Myspace link.', 'fusion-builder' ),
				'param_name'   => 'myspace',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'PayPal Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom PayPal link.', 'fusion-builder' ),
				'param_name'   => 'paypal',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Pinterest Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Pinterest link.', 'fusion-builder' ),
				'param_name'   => 'pinterest',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Reddit Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Reddit link.', 'fusion-builder' ),
				'param_name'   => 'reddit',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'RSS Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom RSS link.', 'fusion-builder' ),
				'param_name'   => 'rss',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Skype Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Skype link.', 'fusion-builder' ),
				'param_name'   => 'skype',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'SoundCloud Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom SoundCloud link.', 'fusion-builder' ),
				'param_name'   => 'soundcloud',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Spotify Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Spotify link.', 'fusion-builder' ),
				'param_name'   => 'spotify',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Tiktok Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Tiktok link.', 'fusion-builder' ),
				'param_name'   => 'tiktok',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Tumblr Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Tumblr link.', 'fusion-builder' ),
				'param_name'   => 'tumblr',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Twitch Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Twitch link.', 'fusion-builder' ),
				'param_name'   => 'twitch',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Twitter Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Twitter link.', 'fusion-builder' ),
				'param_name'   => 'twitter',
				'value'        => '#',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Vimeo Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Vimeo link.', 'fusion-builder' ),
				'param_name'   => 'vimeo',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'VK Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom VK link.', 'fusion-builder' ),
				'param_name'   => 'vk',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'WeChat Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom WeChat link.', 'fusion-builder' ),
				'param_name'   => 'wechat',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'WhatsApp Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom WhatsApp link.', 'fusion-builder' ),
				'param_name'   => 'whatsapp',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Xing Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Xing link.', 'fusion-builder' ),
				'param_name'   => 'xing',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Yahoo Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Yahoo link.', 'fusion-builder' ),
				'param_name'   => 'yahoo',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Yelp Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Yelp link.', 'fusion-builder' ),
				'param_name'   => 'yelp',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Youtube Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Youtube link.', 'fusion-builder' ),
				'param_name'   => 'youtube',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Email Address', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert an email address to display the email icon.', 'fusion-builder' ),
				'param_name'   => 'email',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Phone Number', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert a phone number to display the phone icon.', 'fusion-builder' ),
				'param_name'   => 'phone',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Show Custom Social Icon', 'fusion-builder' ),
				'description' => esc_attr__( 'Show the custom social icon specified in Global Options.', 'fusion-builder' ),
				'param_name'  => 'show_custom',
				'value'       => [
					'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
					'no'  => esc_attr__( 'No', 'fusion-builder' ),
				],
				'default'     => 'no',
			],
		],
	];
	$custom_social_networks = fusion_builder_get_custom_social_networks();
	if ( is_array( $custom_social_networks ) ) {
		$custom_networks = [];
		foreach ( $custom_social_networks as $key => $custom_network ) {
			$social_options['params'][] = [
				'type'        => 'textfield',
				/* translators: The network-name. */
				'heading'     => sprintf( esc_attr__( '%s Link', 'fusion-builder' ), $custom_network['title'] ),
				'description' => esc_attr__( 'Insert your custom social link.', 'fusion-builder' ),
				'param_name'  => 'custom_' . $key,
				'value'       => '',
				'dependency'  => [
					[
						'element'  => 'show_custom',
						'value'    => 'yes',
						'operator' => '==',
					],
				],
			];
		}
	}
	$social_options['params'][]                                       = [
		'type'        => 'radio_button_set',
		'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
		'description' => esc_attr__( "Select the icon's alignment.", 'fusion-builder' ),
		'param_name'  => 'alignment',
		'value'       => [
			''       => esc_attr__( 'Text Flow', 'fusion-builder' ),
			'left'   => esc_attr__( 'Left', 'fusion-builder' ),
			'center' => esc_attr__( 'Center', 'fusion-builder' ),
			'right'  => esc_attr__( 'Right', 'fusion-builder' ),
		],
		'default'     => '',
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
	];
	$social_options['params'][]                                       = [
		'type'        => 'checkbox_button_set',
		'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
		'param_name'  => 'hide_on_mobile',
		'value'       => fusion_builder_visibility_options( 'full' ),
		'default'     => fusion_builder_default_visibility( 'array' ),
		'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
	];
	$social_options['params']['fusion_sticky_visibility_placeholder'] = [];
	$social_options['params'][]                                       = [
		'type'        => 'textfield',
		'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
		'param_name'  => 'class',
		'value'       => '',
		'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
	];
	$social_options['params'][]                                       = [
		'type'        => 'textfield',
		'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
		'param_name'  => 'id',
		'value'       => '',
		'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
	];
	fusion_builder_map( fusion_builder_frontend_data( 'FusionSC_SocialLinks', $social_options ) );
}
add_action( 'fusion_builder_before_init', 'fusion_element_social_links' );
