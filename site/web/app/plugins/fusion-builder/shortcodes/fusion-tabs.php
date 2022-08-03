<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_tabs' ) ) {

	if ( ! class_exists( 'FusionSC_Tabs' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Tabs extends Fusion_Element {

			/**
			 * Tabs counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $tabs_counter = 1;

			/**
			 * Tab counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $tab_counter = 1;

			/**
			 * Array of our tabs.
			 *
			 * @access private
			 * @since 1.0
			 * @var array
			 */
			private $tabs = [];

			/**
			 * Whether the tab is active or not.
			 *
			 * @access private
			 * @since 1.0
			 * @var bool
			 */
			private $active = false;

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
			 * Parent fusion_tabs SC arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $fusion_tabs_args;

			/**
			 * Child fusion_tab SC arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $fusion_tab_args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_tabs-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_tabs-shortcode-link', [ $this, 'link_attr' ] );
				add_filter( 'fusion_attr_tabs-shortcode-icon', [ $this, 'icon_attr' ] );
				add_filter( 'fusion_attr_tabs-shortcode-tab', [ $this, 'tab_attr' ] );

				add_shortcode( 'fusion_old_tabs', [ $this, 'render_parent' ] );
				add_shortcode( 'fusion_old_tab', [ $this, 'render_child' ] );

				add_shortcode( 'fusion_tabs', [ $this, 'fusion_tabs' ] );
				add_shortcode( 'fusion_tab', [ $this, 'fusion_tab' ] );

			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @param string $context Whether we want parent or child.
			 *                        Returns array( parent, child ) if empty.
			 * @return array
			 */
			public static function get_element_defaults( $context = '' ) {
				$fusion_settings = fusion_get_fusion_settings();

				$parent = [
					'hide_on_mobile'  => fusion_builder_default_visibility( 'string' ),
					'class'           => '',
					'id'              => '',
					'backgroundcolor' => $fusion_settings->get( 'tabs_bg_color' ),
					'bordercolor'     => $fusion_settings->get( 'tabs_border_color' ),
					'icon_position'   => $fusion_settings->get( 'tabs_icon_position' ),
					'icon_size'       => $fusion_settings->get( 'tabs_icon_size' ),
					'design'          => 'classic',
					'inactivecolor'   => $fusion_settings->get( 'tabs_inactive_color' ),
					'justified'       => 'yes',
					'layout'          => 'horizontal',
				];

				$child = [
					'icon'       => 'none',
					'id'         => '',
					'fusion_tab' => 'no',
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
					'tabs_bg_color'       => 'backgroundcolor',
					'tabs_border_color'   => 'bordercolor',
					'tabs_icon_position'  => 'icon_position',
					'tabs_icon_size'      => 'icon_size',
					'tabs_inactive_color' => 'inactivecolor',
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
			 * Render the parent shortcode.
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render_parent( $args, $content = '' ) {

				global $fusion_settings;

				$html     = '';
				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'parent' ), $args );

				extract( $defaults );

				$this->parent_args = $defaults;

				$justified_class = '';
				if ( 'yes' === $justified && 'vertical' !== $layout ) {
					$justified_class = ' nav-justified';
				}

				$styles = '.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs li a.tab-link{border-top-color:' . $this->parent_args['inactivecolor'] . ';background-color:' . $this->parent_args['inactivecolor'] . ';}';
				if ( 'clean' !== $design ) {
					$styles .= '.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs{background-color:' . $this->parent_args['backgroundcolor'] . ';}';
					$styles .= '.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs li.active a.tab-link,.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs li.active a.tab-link:hover,.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs li.active a.tab-link:focus{border-right-color:' . $this->parent_args['backgroundcolor'] . ';}';
				} else {
					$styles = '#wrapper .fusion-tabs.fusion-tabs-' . $this->tabs_counter . '.clean .nav-tabs li a.tab-link{border-color:' . $this->parent_args['bordercolor'] . ';}.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs li a.tab-link{background-color:' . $this->parent_args['inactivecolor'] . ';}';
				}
				$styles .= '.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs li.active a.tab-link,.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs li.active a.tab-link:hover,.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs li.active a.tab-link:focus{background-color:' . $this->parent_args['backgroundcolor'] . ';}';
				$styles .= '.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs li a:hover{background-color:' . $this->parent_args['backgroundcolor'] . ';border-top-color:' . $this->parent_args['backgroundcolor'] . ';}';
				$styles .= '.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .tab-pane{background-color:' . $this->parent_args['backgroundcolor'] . ';}';
				$styles .= '.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav,.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs,.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .tab-content .tab-pane{border-color:' . $this->parent_args['bordercolor'] . ';}';
				$styles  = '<style type="text/css">' . $styles . '</style>';

				$html = '<div ' . FusionBuilder::attributes( 'tabs-shortcode' ) . '>' . $styles . '<div ' . FusionBuilder::attributes( 'nav' ) . '><ul ' . FusionBuilder::attributes( 'nav-tabs' . $justified_class ) . ' role="tablist">';

				$is_first_tab = true;

				if ( empty( $this->tabs ) ) {
					$this->parse_tab_parameter( $content, 'fusion_old_tab', $args );
				}

				if ( strpos( $content, 'fusion_tab' ) ) {
					preg_match_all( '/(\[fusion_tab (.*?)\](.*?)\[\/fusion_tab\])/s', $content, $matches );
				} else {
					preg_match_all( '/(\[fusion_old_tab (.*?)\](.*?)\[\/fusion_old_tab\])/s', $content, $matches );
				}

				$tab_content = '';

				$tabs_count = ! empty( $this->tabs ) ? count( $this->tabs ) : 0;
				for ( $i = 0; $i < $tabs_count; $i++ ) {
					$icon = $tab_title = '';
					if ( 'none' !== $this->tabs[ $i ]['icon'] ) {
						$icon = '<i ' . FusionBuilder::attributes( 'tabs-shortcode-icon', [ 'index' => $i ] ) . '></i>';
					}

					if ( 'right' === $this->parent_args['icon_position'] ) {
						$tab_title = $this->tabs[ $i ]['title'] . $icon;
					} else {
						$tab_title = $icon . $this->tabs[ $i ]['title'];
					}

					if ( $is_first_tab ) {
						$tab_nav      = '<li ' . FusionBuilder::attributes( 'active' ) . '><a ' . FusionBuilder::attributes(
							'tabs-shortcode-link',
							[
								'index' => $i,
								'first' => 'true',
							]
						) . '><h4 ' . FusionBuilder::attributes( 'fusion-tab-heading' ) . '>' . $tab_title . '</h4></a></li>';
						$is_first_tab = false;
					} else {
						$tab_nav = '<li><a ' . FusionBuilder::attributes(
							'tabs-shortcode-link',
							[
								'index' => $i,
								'first' => 'false',
							]
						) . '><h4 ' . FusionBuilder::attributes( 'fusion-tab-heading' ) . '>' . $tab_title . '</h4></a></li>';
					}

					$html .= $tab_nav;

					// Change ID for mobile to ensure no duplicate ID.
					$tab_nav      = str_replace( 'id="fusion-tab-', 'id="mobile-fusion-tab-', $tab_nav );
					$tab_content .= '<div ' . FusionBuilder::attributes( 'nav fusion-mobile-tab-nav' ) . '><ul ' . FusionBuilder::attributes( 'nav-tabs' . $justified_class ) . '>' . $tab_nav . '</ul></div>';
					$tab_content .= ( isset( $matches[1][ $i ] ) ) ? do_shortcode( $matches[1][ $i ] ) : '';
				}

				$html .= '</ul></div><div ' . FusionBuilder::attributes( 'tab-content' ) . '>' . $tab_content . '</div></div>';

				$this->tabs_counter++;
				$this->tab_counter = 1;
				$this->active      = false;
				unset( $this->tabs );

				$this->on_render();

				return apply_filters( 'fusion_element_tabs_parent_content', $html, $args );

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
						'class' => 'fusion-tabs fusion-tabs-' . $this->tabs_counter . ' ' . $this->parent_args['design'],
					]
				);

				if ( 'yes' !== $this->parent_args['justified'] && 'vertical' !== $this->parent_args['layout'] ) {
					$attr['class'] .= ' nav-not-justified';
				}

				if ( $this->parent_args['class'] ) {
					$attr['class'] .= ' ' . $this->parent_args['class'];
				}

				$attr['class'] .= ( 'vertical' === $this->parent_args['layout'] ) ? ' vertical-tabs' : ' horizontal-tabs';

				$attr['class'] .= ( '' !== $this->parent_args['icon_position'] ) ? ' icon-position-' . $this->parent_args['icon_position'] : '';

				if ( $this->parent_args['id'] ) {
					$attr['id'] = $this->parent_args['id'];
				}

				return $attr;

			}

			/**
			 * Builds the link attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @param array $atts Default attributes.
			 * @return array
			 */
			public function link_attr( $atts ) {
				$index = $atts['index'];
				$attr  = [
					'class'         => 'tab-link',
					'data-toggle'   => 'tab',
					'role'          => 'tab',
					'aria-controls' => $this->tabs[ $index ]['unique_id'],
					'aria-selected' => $atts['first'],
				];

				if ( 'false' === $atts['first'] ) {
					$attr['tabindex'] = '-1';
				}

				$attr['id']   = 'fusion-tab-' . strtolower( preg_replace( '/\s+/', '', $this->tabs[ $index ]['title'] ) );
				$attr['href'] = '#' . $this->tabs[ $index ]['unique_id'];

				return $attr;
			}

			/**
			 * Builds the icon attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @param array $atts Default attributes.
			 * @return array
			 */
			public function icon_attr( $atts ) {
				$index = $atts['index'];
				$attr  = [
					'class'       => 'fontawesome-icon ' . fusion_font_awesome_name_handler( $this->tabs[ $index ]['icon'] ),
					'aria-hidden' => 'true',
				];

				if ( '' !== $this->parent_args['icon_size'] ) {
					$attr['style'] = 'font-size:' . $this->parent_args['icon_size'] . 'px;';
				}

				return $attr;
			}

			/**
			 * Render the child shortcode.
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args   Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string         HTML output.
			 */
			public function render_child( $args, $content = '' ) {

				$defaults = FusionBuilder::set_shortcode_defaults(
					[
						'icon'        => 'none',
						'id'          => '',
						'fusion_tab'  => 'no',
						'tab_counter' => 1,
					],
					$args
				);

				extract( $defaults );

				$this->child_args = $defaults;

				$html = '<div ' . FusionBuilder::attributes( 'tabs-shortcode-tab' ) . '>' . do_shortcode( $content ) . '</div>';

				return apply_filters( 'fusion_element_tabs_child_content', $html, $args );

			}

			/**
			 * Builds the tab attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function tab_attr() {
				$index = $this->child_args['tab_counter'] - 1;

				$attr = [
					'class'           => 'tab-pane fade fusion-clearfix',
					'role'            => 'tabpanel',
					'tabindex'        => '0',
					'aria-labelledby' => 'fusion-tab-' . ( isset( $this->tabs[ $index ] ) ? strtolower( preg_replace( '/\s+/', '', $this->tabs[ $index ]['title'] ) ) : '' ), // isset check needed for Yoast's page analysis tool.
				];

				if ( ! isset( $this->active ) ) {
					$this->active = false;
				}

				if ( ! $this->active ) {
					$attr['class'] = 'tab-pane fade fusion-clearfix in active';
					$this->active  = true;
				}

				if ( 'yes' === $this->child_args['fusion_tab'] ) {
					$attr['id'] = $this->child_args['id'];
				} else {
					$index      = $this->child_args['id'] - 1;
					$attr['id'] = $this->tabs[ $index ]['unique_id'];
				}

				return $attr;

			}

			/**
			 * Returns the fusion-tabs.
			 *
			 * @access public
			 * @since 1.0
			 * @param array       $atts    The attributes.
			 * @param null|string $content The content.
			 * @return string
			 */
			public function fusion_tabs( $atts, $content = null ) {

				global $fusion_settings;

				$defaults = FusionBuilder::set_shortcode_defaults(
					[
						'class'           => '',
						'id'              => '',
						'backgroundcolor' => $fusion_settings->get( 'tabs_bg_color' ),
						'bordercolor'     => $fusion_settings->get( 'tabs_border_color' ),
						'icon'            => '',
						'icon_position'   => $fusion_settings->get( 'tabs_icon_position' ),
						'icon_size'       => $fusion_settings->get( 'tabs_icon_size' ),
						'design'          => 'classic',
						'inactivecolor'   => $fusion_settings->get( 'tabs_inactive_color' ),
						'justified'       => 'yes',
						'layout'          => 'horizontal',
						'hide_on_mobile'  => fusion_builder_default_visibility( 'string' ),
					],
					$atts,
					'fusion_tabs'
				);

				extract( $defaults );

				$this->fusion_tabs_args = $defaults;

				$atts = $defaults;

				$content = preg_replace( '/tab\][^\[]*/', 'tab]', $content );
				$content = preg_replace( '/^[^\[]*\[/', '[', $content );

				$this->parse_tab_parameter( $content, 'fusion_tab' );

				$shortcode_wrapper  = '[fusion_old_tabs design="' . $atts['design'] . '" layout="' . $atts['layout'] . '" justified="' . $atts['justified'] . '" backgroundcolor="' . $atts['backgroundcolor'] . '" inactivecolor="' . $atts['inactivecolor'] . '" bordercolor="' . $atts['bordercolor'] . '" icon_position="' . $atts['icon_position'] . '" icon_size="' . $atts['icon_size'] . '" hide_on_mobile="' . $atts['hide_on_mobile'] . '" class="' . $atts['class'] . '" id="' . $atts['id'] . '"]';
				$shortcode_wrapper .= $content;
				$shortcode_wrapper .= '[/fusion_old_tabs]';

				fusion_element_rendering_elements( true );
				$html = do_shortcode( $shortcode_wrapper );
				fusion_element_rendering_elements( false );

				return $html;
			}

			/**
			 * Returns the fusion-tab.
			 *
			 * @access public
			 * @since 1.0
			 * @param array       $atts    The attributes.
			 * @param null|string $content The content.
			 * @return string
			 */
			public function fusion_tab( $atts, $content = null ) {
				$defaults = FusionBuilder::set_shortcode_defaults(
					[
						'id'    => '',
						'icon'  => $this->fusion_tabs_args['icon'],
						'title' => '',
					],
					$atts,
					'fusion_tab'
				);

				$content = apply_filters( 'fusion_shortcode_content', $content, 'fusion_tab', $atts );

				extract( $defaults );
				$this->fusion_tab_args = $defaults;

				$atts = $defaults;

				// Create unique tab id for linking.
				$sanitized_title = hash( 'md5', $title, false );
				$sanitized_title = 'tab' . str_replace( '-', '_', $sanitized_title );
				$unique_id       = 'tab-' . substr( md5( get_the_ID() . '-' . $this->tabs_counter . '-' . $this->tab_counter . '-' . $sanitized_title ), 13 );

				$shortcode_wrapper = '[fusion_old_tab id="' . $unique_id . '" icon="' . $icon . '" fusion_tab="yes" tab_counter="' . $this->tab_counter . '"]' . do_shortcode( $content ) . '[/fusion_old_tab]';

				$this->tab_counter++;

				return do_shortcode( $shortcode_wrapper );
			}

			/**
			 * Parses the tab parameters.
			 *
			 * @access public
			 * @since 1.0
			 * @param string $content The content.
			 * @param string $shortcode The shortcode.
			 * @param array  $args      The arguments.
			 */
			public function parse_tab_parameter( $content, $shortcode, $args = null ) {
				$preg_match_tabs_single = preg_match_all( FusionBuilder::get_shortcode_regex( $shortcode ), $content, $tabs_single );

				if ( is_array( $tabs_single[0] ) ) {
					foreach ( $tabs_single[0] as $key => $tab ) {

						if ( is_array( $args ) ) {
							$preg_match_titles = preg_match_all( '/' . $shortcode . ' id=([0-9]+)/i', $tab, $ids );

							if ( array_key_exists( '0', $ids[1] ) ) {
								$id = $ids[1][0];
							} else {
								$title = 'default';
							}

							foreach ( $args as $key => $value ) {
								if ( 'tab' . $id === $key ) {
									$title = $value;
								}
							}
						} else {
							$preg_match_titles = preg_match_all( '/' . $shortcode . ' title="([^\"]+)"/i', $tab, $titles );
							$title             = ( array_key_exists( '0', $titles[1] ) ) ? $titles[1][0] : 'default';
						}
						$preg_match_icons = preg_match_all( '/' . $shortcode . '( id=[0-9]+| title="[^\"]+")? icon="([^\"]+)"/i', $tab, $icons );
						$icon             = ( array_key_exists( '0', $icons[2] ) ) ? $icons[2][0] : 'none';

						if ( 'none' === $icon && ! empty( $this->fusion_tabs_args['icon'] ) ) {
							$icon = $this->fusion_tabs_args['icon'];
						}

						// Create unique tab id for linking.
						$sanitized_title = hash( 'md5', $title, false );
						$sanitized_title = 'tab' . str_replace( '-', '_', $sanitized_title );
						$unique_id       = 'tab-' . substr( md5( get_the_ID() . '-' . $this->tabs_counter . '-' . $this->tab_counter . '-' . $sanitized_title ), 13 );

						// Create array for every single tab shortcode.
						$this->tabs[] = [
							'title'     => $title,
							'icon'      => $icon,
							'unique_id' => $unique_id,
						];

						$this->tab_counter++;
					}

					$this->tab_counter = 1;
				}
			}

			/**
			 * Builds the dynamic styling.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function add_styling() {
				global $wp_version, $content_media_query, $six_fourty_media_query, $three_twenty_six_fourty_media_query, $ipad_portrait_media_query, $content_min_media_query, $fusion_settings, $dynamic_css_helpers;

				$css['global']['.fusion-tabs.icon-position-right .nav-tabs li .tab-link .fontawesome-icon']['margin-right'] = '0';
				$css['global']['.fusion-tabs.icon-position-right .nav-tabs li .tab-link .fontawesome-icon']['margin-left']  = '10px';
				$css['global']['.fusion-tabs.icon-position-top .nav-tabs li .tab-link .fontawesome-icon']['display']        = 'block';
				$css['global']['.fusion-tabs.icon-position-top .nav-tabs li .tab-link .fontawesome-icon']['margin']         = '0 auto';
				$css['global']['.fusion-tabs.icon-position-top .nav-tabs li .tab-link .fontawesome-icon']['margin-bottom']  = '10px';
				$css['global']['.fusion-tabs.icon-position-top .nav-tabs li .tab-link .fontawesome-icon']['text-align']     = 'center';

				$css[ $content_min_media_query ]['.fusion-tabs .nav']['display']                                     = 'block';
				$css[ $content_min_media_query ]['.fusion-tabs .fusion-mobile-tab-nav']['display']                   = 'none';
				$css[ $content_min_media_query ]['.fusion-tabs.clean .tab-pane']['margin']                           = 0;
				$css[ $content_min_media_query ]['.fusion-tabs .nav-tabs']['display']                                = 'inline-block';
				$css[ $content_min_media_query ]['.fusion-tabs .nav-tabs']['vertical-align']                         = 'middle';
				$css[ $content_min_media_query ]['.fusion-tabs .nav-tabs.nav-justified > li']['display']             = 'table-cell';
				$css[ $content_min_media_query ]['.fusion-tabs .nav-tabs.nav-justified > li']['width']               = '1%';
				$css[ $content_min_media_query ]['.fusion-tabs .nav-tabs li .tab-link']['margin-right']              = '1px';
				$css[ $content_min_media_query ]['.fusion-tabs .nav-tabs li:last-child .tab-link']['margin-right']   = '0';
				$css[ $content_min_media_query ]['.fusion-tabs.horizontal-tabs .nav-tabs']['margin']                 = '0 0 -1px';
				$css[ $content_min_media_query ]['.fusion-tabs.horizontal-tabs .nav']['border-bottom']               = '1px solid ' . fusion_library()->sanitize->color( $fusion_settings->get( 'tabs_border_color' ) );
				$css[ $content_min_media_query ]['.fusion-tabs.horizontal-tabs.clean .nav']['border']                = 'none';
				$css[ $content_min_media_query ]['.fusion-tabs.horizontal-tabs.clean .nav']['text-align']            = 'center';
				$css[ $content_min_media_query ]['.fusion-tabs.horizontal-tabs.clean .nav-tabs']['border']           = 'none';
				$css[ $content_min_media_query ]['.fusion-tabs.horizontal-tabs.clean .nav-tabs li']['margin-bottom'] = '0';
				$css[ $content_min_media_query ]['.fusion-tabs.horizontal-tabs.clean .nav-tabs li .tab-link']['margin-right'] = '-1px';
				$css[ $content_min_media_query ]['.fusion-tabs.horizontal-tabs.clean .tab-content']['margin-top']             = '40px';
				$css[ $content_min_media_query ]['.fusion-tabs.nav-not-justified']['border']                                  = 'none';
				$css[ $content_min_media_query ]['.fusion-tabs.nav-not-justified .nav-tabs li']['display']                    = 'inline-block';
				$css[ $content_min_media_query ]['.fusion-tabs.nav-not-justified.clean .nav-tabs li .tab-link']['padding']    = '14px 55px';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs']['display']                                     = '-webkit-flex';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs']['display']                                     = '-ms-flexbox';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs']['display']                                     = 'flex';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs']['border']                                      = 'none';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs']['clear']                                       = 'both';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs']['zoom']                                        = '1';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs:before, .fusion-tabs.vertical-tabs:after']['content'] = '" "';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs:before, .fusion-tabs.vertical-tabs:after']['display'] = 'table';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs:after']['clear']                                      = 'both';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs']['display']                                = 'block';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs']['position']                               = 'relative';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs']['left']                                   = '1px';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs']['border']                                 = '1px solid ' . fusion_library()->sanitize->color( $fusion_settings->get( 'tabs_border_color' ) );
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs']['border-right']                           = 'none';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs > li .tab-link']['margin-right']            = '0';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs > li .tab-link']['margin-bottom']           = '1px';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs > li .tab-link']['padding']                 = '10px 35px';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs > li .tab-link']['white-space']             = 'nowrap';

				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs > li .tab-link']['border-top']               = 'none';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs > li .tab-link']['text-align']               = 'left';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs > li:last-child .tab-link']['margin-bottom'] = '0';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs > li.active > .tab-link']['border-bottom']   = 'none';

				if ( is_rtl() ) {
					$css[ $content_min_media_query ]['.rtl .fusion-tabs.vertical-tabs .nav-tabs > li .tab-link']['border-right']          = '3px transparent solid';
					$css[ $content_min_media_query ]['.rtl .fusion-tabs.vertical-tabs .nav-tabs > li.active > .tab-link']['border-right'] = '3px solid var(--primary_color)';
				} else {
					$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs > li .tab-link']['border-left']          = '3px transparent solid';
					$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs > li.active > .tab-link']['border-left'] = '3px solid var(--primary_color)';
				}

				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs > li.active > .tab-link']['border-top']     = 'none';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs > li.active > .tab-link']['cursor']         = 'pointer';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav']['width']                                       = 'auto';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .tab-content']['width']                               = '84.5%';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .tab-pane']['padding']                                = '30px';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .tab-pane']['border']                                 = '1px solid ' . fusion_library()->sanitize->color( $fusion_settings->get( 'tabs_border_color' ) );
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs.clean .nav-tabs']['background-color']                 = 'transparent';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs.clean .nav-tabs']['border']                           = 'none';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs.clean .nav-tabs li .tab-link']['margin']              = '0';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs.clean .nav-tabs li .tab-link']['padding']             = '10px 35px';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs.clean .nav-tabs li .tab-link']['white-space']         = 'nowrap';
				$css[ $content_min_media_query ]['.fusion-body .fusion-tabs.vertical-tabs.clean .nav-tabs li .tab-link']['border'] = '1px solid';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs.clean .nav']['width']                                 = 'auto';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs.clean .tab-content']['margin']                        = '0';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs.clean .tab-content']['width']                         = '75%';

				if ( is_rtl() ) {
					$css[ $content_min_media_query ]['.rtl .fusion-tabs.vertical-tabs.clean .tab-content']['padding-right']  = '40px';
					$css[ $content_min_media_query ]['.rtl .fusion-tabs.vertical-tabs .nav-tabs li .tab-link']['text-align'] = 'right';
				} else {
					$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs.clean .tab-content']['padding-left'] = '40px';
				}

				return $css;
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Tabs settings.
			 */
			public function add_options() {

				return [
					'tabs_shortcode_section' => [
						'label'       => esc_html__( 'Tabs', 'fusion-builder' ),
						'description' => '',
						'id'          => 'tabs_shortcode_section',
						'icon'        => 'fusiona-folder',
						'type'        => 'accordion',
						'fields'      => [
							'tabs_info'           => [
								'id'          => 'social_links_info',
								'type'        => 'custom',
								'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> These tab global options control both the tab element and Avada tab widget, however the widget does not utilize icons.', 'fusion-builder' ) . '</div>',
							],
							'tabs_bg_color'       => [
								'label'       => esc_html__( 'Tabs Background Color + Hover Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the active tab, tab hover and content background.', 'fusion-builder' ),
								'id'          => 'tabs_bg_color',
								'default'     => '#ffffff',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
								'css_vars'    => [
									[
										'name'     => '--tabs_bg_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'tabs_inactive_color' => [
								'label'       => esc_html__( 'Tabs Inactive Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the inactive tabs as well as the post date box layout for the Avada Tab Widget.', 'fusion-builder' ),
								'id'          => 'tabs_inactive_color',
								'default'     => '#f9f9fb',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
								'css_vars'    => [
									[
										'name'     => '--tabs_inactive_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'tabs_border_color'   => [
								'label'       => esc_html__( 'Tabs Border Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the tab border.', 'fusion-builder' ),
								'id'          => 'tabs_border_color',
								'default'     => '#e2e2e2',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
								'css_vars'    => [
									[
										'name'     => '--tabs_border_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'tabs_icon_position'  => [
								'label'       => esc_html__( 'Icon Position', 'fusion-builder' ),
								'description' => esc_html__( 'Choose the position of the icon on the tab.', 'fusion-builder' ),
								'id'          => 'tabs_icon_position',
								'default'     => 'left',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'left'  => esc_attr__( 'Left', 'fusion-builder' ),
									'right' => esc_attr__( 'Right', 'fusion-builder' ),
									'top'   => esc_attr__( 'Top', 'fusion-builder' ),
								],
							],
							'tabs_icon_size'      => [
								'label'       => esc_html__( 'Tabs Icon Size', 'fusion-builder' ),
								'description' => esc_html__( 'Set the size of the icon.', 'fusion-builder' ),
								'id'          => 'tabs_icon_size',
								'default'     => '16',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '1',
									'max'  => '150',
									'step' => '1',
								],
								'type'        => 'slider',
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

				global $fusion_settings;

				Fusion_Dynamic_JS::enqueue_script(
					'fusion-tabs',
					FusionBuilder::$js_folder_url . '/general/fusion-tabs.js',
					FusionBuilder::$js_folder_path . '/general/fusion-tabs.js',
					[ 'modernizr', 'bootstrap-tab' ],
					'1',
					true
				);
				Fusion_Dynamic_JS::localize_script(
					'fusion-tabs',
					'fusionTabVars',
					[
						'content_break_point' => intval( $fusion_settings->get( 'content_break_point' ) ),
					]
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
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/tabs.min.css' );
			}
		}
	}

	new FusionSC_Tabs();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_tabs() {

	global $fusion_settings;

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Tabs',
			[
				'name'          => esc_attr__( 'Tabs', 'fusion-builder' ),
				'shortcode'     => 'fusion_tabs',
				'multi'         => 'multi_element_parent',
				'element_child' => 'fusion_tab',
				'icon'          => 'fusiona-folder',
				'preview'       => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-tabs-preview.php',
				'preview_id'    => 'fusion-builder-block-module-tabs-preview-template',
				'child_ui'      => true,
				'help_url'      => 'https://theme-fusion.com/documentation/fusion-builder/elements/tabs-element/',
				'sortable'      => false,
				'params'        => [
					[
						'type'        => 'tinymce',
						'heading'     => esc_attr__( 'Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter some content for this tabs element.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '[fusion_tab title="' . esc_attr__( 'Your Content Goes Here', 'fusion-builder' ) . '" icon=""]' . esc_attr__( 'Your Content Goes Here', 'fusion-builder' ) . '[/fusion_tab]',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Design', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a design for the element.', 'fusion-builder' ),
						'param_name'  => 'design',
						'value'       => [
							'classic' => esc_attr__( 'Classic', 'fusion-builder' ),
							'clean'   => esc_attr__( 'Clean', 'fusion-builder' ),
						],
						'default'     => 'classic',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Layout', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the layout of the element.' ),
						'param_name'  => 'layout',
						'value'       => [
							'horizontal' => esc_attr__( 'Horizontal', 'fusion-builder' ),
							'vertical'   => esc_attr__( 'Vertical', 'fusion-builder' ),
						],
						'default'     => 'horizontal',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Justify Tabs', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to get tabs stretched over full element width.', 'fusion-builder' ),
						'param_name'  => 'justified',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'horizontal',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background tab color. ', 'fusion-builder' ),
						'param_name'  => 'backgroundcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'tabs_bg_color' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Inactive Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the inactive tab color. ', 'fusion-builder' ),
						'param_name'  => 'inactivecolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'tabs_inactive_color' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the outer tab border. ', 'fusion-builder' ),
						'param_name'  => 'bordercolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'tabs_border_color' ),
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Icon', 'fusion-builder' ),
						'param_name'  => 'icon',
						'value'       => '',
						'description' => esc_attr__( 'Global setting for all tabs, this can be overridden individually. Click an icon to select, click again to deselect.', 'fusion-builder' ),
					],
					[
						'heading'     => esc_html__( 'Icon Position', 'fusion-builder' ),
						'description' => esc_html__( 'Choose the position of the icon on the tab. Icons are selected in each child tab element on the left side and do not have to be used.', 'fusion-builder' ),
						'param_name'  => 'icon_position',
						'default'     => '',
						'type'        => 'radio_button_set',
						'value'       => [
							''      => esc_attr__( 'Default', 'fusion-builder' ),
							'left'  => esc_attr__( 'Left', 'fusion-builder' ),
							'right' => esc_attr__( 'Right', 'fusion-builder' ),
							'top'   => esc_attr__( 'Top', 'fusion-builder' ),
						],
					],
					[
						'heading'     => esc_html__( 'Tabs Icon Size', 'fusion-builder' ),
						'description' => esc_html__( 'Set the size of the icon. In pixels (px), ex: 13px. Icons are selected in each child tab element on the left side and do not have to be used.', 'fusion-builder' ),
						'param_name'  => 'icon_size',
						'default'     => $fusion_settings->get( 'tabs_icon_size' ),
						'min'         => '1',
						'max'         => '150',
						'step'        => '1',
						'type'        => 'range',
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
add_action( 'fusion_builder_before_init', 'fusion_element_tabs' );

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_tab() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Tabs',
			[
				'name'              => esc_attr__( 'Tab', 'fusion-builder' ),
				'shortcode'         => 'fusion_tab',
				'hide_from_builder' => true,
				'allow_generator'   => true,
				'params'            => [
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Tab Title', 'fusion-builder' ),
						'description' => esc_attr__( 'Title of the tab.', 'fusion-builder' ),
						'param_name'  => 'title',
						'value'       => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder' => true,
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Icon', 'fusion-builder' ),
						'param_name'  => 'icon',
						'value'       => '',
						'description' => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
					],
					[
						'type'         => 'tinymce',
						'heading'      => esc_attr__( 'Tab Content', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add content for the tab.', 'fusion-builder' ),
						'param_name'   => 'element_content',
						'value'        => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
					],
				],
				'tag_name'          => 'li',
			],
			'child'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_tab' );
