<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_flip_boxes' ) ) {

	if ( ! class_exists( 'FusionSC_FlipBoxes' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_FlipBoxes extends Fusion_Element {

			/**
			 * The flip-boxes counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $flipbox_counter = 1;

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
				add_filter( 'fusion_attr_flip-boxes-shortcode', [ $this, 'parent_attr' ] );
				add_shortcode( 'fusion_flip_boxes', [ $this, 'render_parent' ] );

				add_filter( 'fusion_attr_flip-box-shortcode', [ $this, 'child_attr' ] );
				add_filter( 'fusion_attr_flip-box-shortcode-front-box', [ $this, 'front_box_attr' ] );
				add_filter( 'fusion_attr_flip-box-shortcode-back-box', [ $this, 'back_box_attr' ] );
				add_filter( 'fusion_attr_flip-box-shortcode-heading-front', [ $this, 'heading_front_attr' ] );
				add_filter( 'fusion_attr_flip-box-shortcode-heading-back', [ $this, 'heading_back_attr' ] );
				add_filter( 'fusion_attr_flip-box-shortcode-grafix', [ $this, 'grafix_attr' ] );
				add_filter( 'fusion_attr_flip-box-shortcode-icon', [ $this, 'icon_attr' ] );
				add_filter( 'fusion_attr_flip-box-animation', [ $this, 'flip_box_animation_attr' ] );
				add_shortcode( 'fusion_flip_box', [ $this, 'render_child' ] );

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
					'hide_on_mobile'      => fusion_builder_default_visibility( 'string' ),
					'class'               => '',
					'id'                  => '',
					'columns'             => '1',
					'circle'              => '',
					'circle_color'        => $fusion_settings->get( 'icon_circle_color' ),
					'circle_border_color' => $fusion_settings->get( 'icon_border_color' ),
					'equal_heights'       => $fusion_settings->get( 'flip_boxes_equal_heights' ),
					'flip_direction'      => $fusion_settings->get( 'flip_boxes_flip_direction' ),
					'flip_effect'         => $fusion_settings->get( 'flip_boxes_flip_effect' ),
					'flip_duration'       => $fusion_settings->get( 'flip_boxes_flip_duration' ),
					'icon'                => '',
					'icon_color'          => $fusion_settings->get( 'icon_color' ),
					'icon_flip'           => '',
					'icon_rotate'         => '',
					'icon_spin'           => '',
					'image'               => '',
					'image_id'            => '',
					'image_max_width'     => '',
				];

				$child = [
					'class'                  => '',
					'id'                     => '',
					'background_color_back'  => $fusion_settings->get( 'flip_boxes_back_bg' ),
					'background_color_front' => $fusion_settings->get( 'flip_boxes_front_bg' ),
					'background_image_back'  => '',
					'background_image_front' => '',
					'border_color'           => $fusion_settings->get( 'flip_boxes_border_color' ),
					'border_radius'          => $fusion_settings->get( 'flip_boxes_border_radius' ),
					'border_size'            => ( $fusion_settings->get( 'flip_boxes_border_size' ) ) ? $fusion_settings->get( 'flip_boxes_border_size' ) . 'px' : '',
					'circle'                 => '',
					'circle_color'           => $fusion_settings->get( 'icon_circle_color' ),
					'circle_border_color'    => $fusion_settings->get( 'icon_border_color' ),
					'flip_direction'         => $fusion_settings->get( 'flip_boxes_flip_direction' ),
					'icon'                   => '',
					'icon_color'             => $fusion_settings->get( 'icon_color' ),
					'icon_flip'              => '',
					'icon_rotate'            => '',
					'icon_spin'              => '',
					'image'                  => '',
					'image_id'               => $parent['image_id'],
					'image_max_width'        => '',
					'text_back_color'        => $fusion_settings->get( 'flip_boxes_back_text' ),
					'text_front'             => '',
					'text_front_color'       => $fusion_settings->get( 'flip_boxes_front_text' ),
					'title_front'            => '',
					'title_front_color'      => $fusion_settings->get( 'flip_boxes_front_heading' ),
					'title_back'             => '',
					'title_back_color'       => $fusion_settings->get( 'flip_boxes_back_heading' ),
					'animation_type'         => '',
					'animation_direction'    => 'left',
					'animation_speed'        => '0.1',
					'animation_offset'       => $fusion_settings->get( 'animation_offset' ),
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
					'flip_boxes_flip_effect'    => 'flip_effect',
					'flip_boxes_flip_direction' => 'flip_direction',
					'flip_boxes_flip_duration'  => 'flip_duration',
					'flip_boxes_equal_heights'  => 'equal_heights',
					'icon_color'                => 'icon_color',
					'circle_color'              => 'icon_circle_color',
					'circle_border_color'       => 'icon_border_color',
				];

				$child = [
					'flip_boxes_front_bg'       => 'background_color_front',
					'flip_boxes_back_bg'        => 'background_color_back',
					'flip_boxes_border_color'   => 'border_color',
					'flip_boxes_border_radius'  => 'border_radius',
					'flip_boxes_border_size'    => 'border_size',
					'icon_circle_color'         => 'circle_color',
					'icon_border_color'         => 'circle_border_color',
					'icon_color'                => 'icon_color',
					'flip_boxes_back_text'      => 'text_back_color',
					'flip_boxes_front_text'     => 'text_front_color',
					'flip_boxes_front_heading'  => 'title_front_color',
					'flip_boxes_back_heading'   => 'title_back_color',
					'flip_boxes_flip_direction' => 'flip_direction',
					'animation_offset'          => 'animation_offset',
				];

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
			 * Render the shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render_parent( $args, $content = '' ) {
				global $fusion_settings;

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'parent' ), $args, 'fusion_flip_boxes' );

				// Backwards compatibility for when we had image width and height params.
				if ( isset( $args['image_width'] ) ) {
					$defaults['image_width'] = ( $args['image_width'] ) ? $args['image_width'] : '35';
				} else {
					$defaults['image_width'] = $defaults['image_max_width'];
				}

				extract( $defaults );

				$this->parent_args = $defaults;

				if ( $this->parent_args['columns'] > 6 ) {
					$this->parent_args['columns'] = 6;
				}

				$html = '<div ' . FusionBuilder::attributes( 'flip-boxes-shortcode' ) . '>' . do_shortcode( $content ) . '</div>';

				$html = fusion_maybe_add_clearfix( $html );

				$this->on_render();

				return apply_filters( 'fusion_element_flip_boxes_parent_content', $html, $args );
			}

			/**
			 * Builds the prent attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function parent_attr() {

				$attr = fusion_builder_visibility_atts(
					$this->parent_args['hide_on_mobile'],
					[
						'class' => 'fusion-flip-boxes flip-boxes row fusion-columns-' . $this->parent_args['columns'],
					]
				);

				$attr['class'] .= ' flip-effect-' . $this->parent_args['flip_effect'];

				if ( 'yes' === $this->parent_args['equal_heights'] ) {
					$attr['class'] .= ' equal-heights';
				}

				if ( $this->parent_args['class'] ) {
					$attr['class'] .= ' ' . $this->parent_args['class'];
				}

				if ( $this->parent_args['id'] ) {
					$attr['id'] = $this->parent_args['id'];
				}

				return $attr;

			}

			/**
			 * Render the child shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render_child( $args, $content = '' ) {
				global $fusion_settings;

				$defaults                        = self::get_element_defaults( 'child' );
				$defaults['circle']              = $this->parent_args['circle'];
				$defaults['circle_color']        = $this->parent_args['circle_color'];
				$defaults['circle_border_color'] = $this->parent_args['circle_border_color'];
				$defaults['flip_direction']      = $this->parent_args['flip_direction'];
				$defaults['icon']                = $this->parent_args['icon'];
				$defaults['icon_color']          = $this->parent_args['icon_color'];
				$defaults['icon_flip']           = $this->parent_args['icon_flip'];
				$defaults['icon_rotate']         = $this->parent_args['icon_rotate'];
				$defaults['icon_spin']           = $this->parent_args['icon_spin'];
				$defaults['image']               = $this->parent_args['image'];
				$defaults['image_max_width']     = $this->parent_args['image_max_width'];

				$defaults = FusionBuilder::set_shortcode_defaults( $defaults, $args, 'fusion_flip_box' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_flip_box', $args );

				// Case when image is set on parent element and icon on child element.
				if ( empty( $args['image'] ) && ! empty( $args['icon'] ) ) {
					$defaults['image'] = '';
				}

				$defaults['border_size']   = FusionBuilder::validate_shortcode_attr_value( $defaults['border_size'], 'px' );
				$defaults['border_radius'] = FusionBuilder::validate_shortcode_attr_value( $defaults['border_radius'], 'px' );

				// Case when image is set on parent element and icon on child element.
				if ( empty( $args['image'] ) && ! empty( $args['icon'] ) ) {
					$defaults['image'] = '';
				}

				// Backwards compatibility for when we had image width and height params.
				if ( isset( $args['image_width'] ) && $args['image_width'] ) {
					$defaults['image_width'] = $args['image_width'];
				} else {
					$defaults['image_width'] = $defaults['image_max_width'];
				}

				$defaults['image_width'] = FusionBuilder::validate_shortcode_attr_value( $defaults['image_width'], '' );

				if ( $defaults['image'] ) {
					$image_data   = fusion_library()->images->get_attachment_data_by_helper( $defaults['image_id'], $defaults['image'] );
					$image_width  = $image_data['width'];
					$image_height = $image_data['height'];

					if ( '-1' === $defaults['image_width'] || '' === $defaults['image_width'] ) {
						$defaults['image_width'] = ( $image_width ) ? $image_width : '35';
					}

					$defaults['image_height'] = ( $image_width ) ? round( $defaults['image_width'] / $image_width * $image_height, 2 ) : $defaults['image_width'];

				} else {
					$defaults['image_width']  = '' === $defaults['image_width'] ? '35' : $defaults['image_width'];
					$defaults['image_height'] = '35';
				}

				if ( 'round' === $defaults['border_radius'] ) {
					$defaults['border_radius'] = '50%';
				}

				extract( $defaults );

				$this->child_args = $defaults;

				$style = $icon_output = $title_output = $title_front_output = $title_back_output = '';

				if ( $image && $image_width && $image_height ) {

					$image_data = fusion_library()->images->get_attachment_data_by_helper( $this->child_args['image_id'], $image );

					if ( $image_data['url'] ) {
						$image = $image_data['url'];
					}
					$image       = '<img src="' . $image . '" width="' . $image_width . '" height="' . $image_height . '" alt="' . $image_data['alt'] . '" />';
					$image       = fusion_library()->images->apply_lazy_loading( $image, null, $this->child_args['image_id'], 'full' );
					$icon_output = $image;

				} elseif ( $icon ) {

					$icon_output = '<i ' . FusionBuilder::attributes( 'flip-box-shortcode-icon' ) . '></i>';

				}

				if ( $icon_output ) {
					$icon_output = '<div ' . FusionBuilder::attributes( 'flip-box-shortcode-grafix' ) . '>' . $icon_output . '</div>';
				}

				if ( $title_front ) {
					$title_front_output = '<h2 ' . FusionBuilder::attributes( 'flip-box-shortcode-heading-front' ) . '>' . $title_front . '</h2>';
				}

				if ( $title_back ) {
					$title_back_output = '<h3 ' . FusionBuilder::attributes( 'flip-box-shortcode-heading-back' ) . '>' . $title_back . '</h3>';
				}

				$front_inner = '<div ' . FusionBuilder::attributes( 'flip-box-front-inner' ) . '>' . $icon_output . $title_front_output . $text_front . '</div>';
				$back_inner  = '<div ' . FusionBuilder::attributes( 'flip-box-back-inner' ) . '>' . $title_back_output . do_shortcode( $content ) . '</div>';

				$front = '<div ' . FusionBuilder::attributes( 'flip-box-shortcode-front-box' ) . '>' . $front_inner . '</div>';
				$back  = '<div ' . FusionBuilder::attributes( 'flip-box-shortcode-back-box' ) . '>' . $back_inner . '</div>';

				$html  = '<div ' . FusionBuilder::attributes( 'flip-box-shortcode' ) . '>';
				$html .= '<div ' . FusionBuilder::attributes( 'flip-box-animation' ) . '>';
				$html .= '<div ' . FusionBuilder::attributes( 'flip-box-inner-wrapper' ) . '>';
				$html .= $front . $back;
				$html .= '</div></div></div>';

				$this->flipbox_counter++;

				return apply_filters( 'fusion_element_flip_boxes_child_content', $html, $args );

			}

			/**
			 * Builds the child attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function child_attr() {

				$columns = 1;
				if ( $this->parent_args['columns'] && ! empty( $this->parent_args['columns'] ) ) {
					$columns = 12 / $this->parent_args['columns'];
				}

				$attr = [
					'class' => 'fusion-flip-box-wrapper fusion-column col-lg-' . $columns . ' col-md-' . $columns . ' col-sm-' . $columns,
				];

				if ( '5' == $this->parent_args['columns'] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					$attr['class'] = 'fusion-flip-box-wrapper col-lg-2 col-md-2 col-sm-2';
				}

				if ( $this->child_args['class'] ) {
					$attr['class'] .= ' ' . $this->child_args['class'];
				}

				if ( $this->child_args['id'] ) {
					$attr['id'] = $this->child_args['id'];
				}

				return $attr;

			}

			/**
			 * Builds the animations attributes array.
			 *
			 * @access public
			 * @since 1.5
			 * @return array
			 */
			public function flip_box_animation_attr() {

				$attr = [
					'class'    => 'fusion-flip-box',
					'tabindex' => 0,
				];

				$attr['class'] .= ' flip-' . $this->child_args['flip_direction'];

				if ( $this->child_args['animation_type'] ) {
					$animations = FusionBuilder::animations(
						[
							'type'      => $this->child_args['animation_type'],
							'direction' => $this->child_args['animation_direction'],
							'speed'     => $this->child_args['animation_speed'],
							'offset'    => $this->child_args['animation_offset'],
						]
					);

					$attr = array_merge( $attr, $animations );

					$attr['class'] .= ' ' . $attr['animation_class'];
					unset( $attr['animation_class'] );
				}

				return $attr;
			}

			/**
			 * Builds the front-box attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function front_box_attr() {

				$attr = [
					'class' => 'flip-box-front',
				];

				if ( $this->child_args['background_color_front'] ) {
					$attr['style'] = 'background-color:' . $this->child_args['background_color_front'] . ';';
				}

				if ( $this->child_args['border_color'] ) {
					$attr['style'] .= 'border-color:' . $this->child_args['border_color'] . ';';
				}

				if ( $this->child_args['border_radius'] ) {
					$attr['style'] .= 'border-radius:' . $this->child_args['border_radius'] . ';';
				}

				if ( $this->child_args['border_size'] ) {
					$attr['style'] .= 'border-style:solid;border-width:' . $this->child_args['border_size'] . ';';
				}

				if ( $this->child_args['text_front_color'] ) {
					$attr['style'] .= 'color:' . $this->child_args['text_front_color'] . ';';
				}

				if ( $this->parent_args['flip_duration'] ) {
					$attr['style'] .= 'transition-duration:' . $this->parent_args['flip_duration'] . 's;';
				}

				if ( $this->child_args['background_image_front'] ) {
					$attr['style'] .= 'background-image: url(\'' . esc_attr( $this->child_args['background_image_front'] ) . '\');';

					if ( $this->child_args['background_color_front'] ) {
						$alpha = Fusion_Color::new_color( $this->child_args['background_color_front'] )->alpha;

						if ( 1 > $alpha && 0 !== $alpha ) {
							$attr['style'] .= 'background-blend-mode: overlay;';
						}
					}
				}

				return $attr;

			}

			/**
			 * Builds the back-box attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function back_box_attr() {

				$attr = [
					'class' => 'flip-box-back',
				];

				if ( $this->child_args['background_color_back'] ) {
					$attr['style'] = 'background-color:' . $this->child_args['background_color_back'] . ';';
				}

				if ( $this->child_args['border_color'] ) {
					$attr['style'] .= 'border-color:' . $this->child_args['border_color'] . ';';
				}

				if ( $this->child_args['border_radius'] ) {
					$attr['style'] .= 'border-radius:' . $this->child_args['border_radius'] . ';';
				}

				if ( $this->child_args['border_size'] ) {
					$attr['style'] .= 'border-style:solid;border-width:' . $this->child_args['border_size'] . ';';
				}

				if ( $this->child_args['text_back_color'] ) {
					$attr['style'] .= 'color:' . $this->child_args['text_back_color'] . ';';
				}

				if ( $this->parent_args['flip_duration'] ) {
					$attr['style'] .= 'transition-duration:' . $this->parent_args['flip_duration'] . 's;';
				}

				if ( $this->child_args['background_image_back'] ) {
					$attr['style'] .= 'background-image: url(\'' . esc_attr( $this->child_args['background_image_back'] ) . '\');';

					if ( $this->child_args['background_color_back'] ) {
						$alpha = Fusion_Color::new_color( $this->child_args['background_color_back'] )->alpha;

						if ( 1 > $alpha && 0 !== $alpha ) {
							$attr['style'] .= 'background-blend-mode: overlay;';
						}
					}
				}

				return $attr;

			}

			/**
			 * Builds the "grafix" attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function grafix_attr() {

				$attr = [
					'class' => 'flip-box-grafix',
				];

				if ( ! $this->child_args['image'] ) {

					if ( 'yes' === $this->child_args['circle'] ) {
						$attr['class'] .= ' flip-box-circle';

						if ( $this->child_args['circle_color'] ) {
							$attr['style'] = 'background-color:' . $this->child_args['circle_color'] . ';';
						}

						if ( $this->child_args['circle_border_color'] ) {
							$attr['style'] .= 'border-color:' . $this->child_args['circle_border_color'] . ';';
						}
					} else {
						$attr['class'] .= ' flip-box-no-circle';
					}
				} else {
					$attr['class'] .= ' flip-box-image';
				}

				return $attr;

			}

			/**
			 * Builds the icon attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function icon_attr() {

				$attr = [
					'aria-hidden' => 'true',
				];

				if ( $this->child_args['image'] ) {
					$attr['class'] = 'image';
				} elseif ( $this->child_args['icon'] ) {
					$attr['class'] = fusion_font_awesome_name_handler( $this->child_args['icon'] );
				}

				if ( $this->child_args['icon_color'] ) {
					$attr['style'] = 'color:' . $this->child_args['icon_color'] . ';';
				}

				if ( $this->child_args['icon_flip'] && 'none' !== $this->child_args['icon_flip'] ) {
					$attr['class'] .= ' fa-flip-' . $this->child_args['icon_flip'];
				}

				if ( $this->child_args['icon_rotate'] && 'none' !== $this->child_args['icon_rotate'] ) {
					$attr['class'] .= ' fa-rotate-' . $this->child_args['icon_rotate'];
				}

				if ( 'yes' === $this->child_args['icon_spin'] && 'none' !== $this->child_args['icon_spin'] ) {
					$attr['class'] .= ' fa-spin';
				}

				return $attr;

			}

			/**
			 * Builds the heading-front attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function heading_front_attr() {

				$attr = [
					'class' => 'flip-box-heading',
				];

				if ( ! $this->child_args['text_front'] ) {
					$attr['class'] .= ' without-text';
				}

				if ( $this->child_args['title_front_color'] ) {
					$attr['style'] = 'color:' . $this->child_args['title_front_color'] . ';';
				}

				return $attr;

			}

			/**
			 * Builds the heading-back attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function heading_back_attr() {

				$attr = [
					'class' => 'flip-box-heading-back',
				];

				if ( $this->child_args['title_back_color'] ) {
					$attr['style'] = 'color:' . $this->child_args['title_back_color'] . ';';
				}

				return $attr;

			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Flip Boxes settings.
			 */
			public function add_options() {

				return [
					'flip_boxes_shortcode_section' => [
						'label'       => esc_html__( 'Flip Boxes', 'fusion-builder' ),
						'description' => '',
						'id'          => 'flip_boxes_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-loop-alt2',
						'fields'      => [
							'flip_boxes_flip_effect'    => [
								'label'       => esc_html__( 'Flip Effect', 'fusion-builder' ),
								'description' => esc_html__( 'Set the flip effect for the boxes.', 'fusion-builder' ),
								'id'          => 'flip_boxes_flip_effect',
								'default'     => 'classic',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'classic' => esc_html__( 'Classic', 'fusion-builder' ),
									'3d'      => esc_html__( '3d', 'fusion-builder' ),
								],
							],
							'flip_boxes_flip_direction' => [
								'label'       => esc_html__( 'Flip Direction', 'fusion-builder' ),
								'description' => esc_attr__( 'Set the direction in which the boxes should flip.', 'fusion-builder' ),
								'id'          => 'flip_boxes_flip_direction',
								'default'     => 'right',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'left'  => esc_html__( 'Flip Left', 'fusion-builder' ),
									'right' => esc_html__( 'Flip Right', 'fusion-builder' ),
									'up'    => esc_html__( 'Flip Up', 'fusion-builder' ),
									'down'  => esc_html__( 'Flip Down', 'fusion-builder' ),
								],
							],
							'flip_boxes_flip_duration'  => [
								'label'       => esc_html__( 'Flip Duration', 'fusion-core' ),
								'description' => esc_html__( 'Set the speed at which the boxes flip.', 'fusion-core' ),
								'id'          => 'flip_boxes_flip_duration',
								'default'     => '0.8',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '0.1',
									'max'  => '2',
									'step' => '0.1',
								],
							],
							'flip_boxes_equal_heights'  => [
								'label'       => esc_html__( 'Equal Heights', 'fusion-builder' ),
								'description' => esc_html__( 'Set to yes to display flip boxes to equal heights.', 'fusion-builder' ),
								'id'          => 'flip_boxes_equal_heights',
								'default'     => 'no',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'yes' => esc_html__( 'Yes', 'fusion-builder' ),
									'no'  => esc_html__( 'No', 'fusion-builder' ),
								],
							],
							'flip_boxes_front_bg'       => [
								'label'       => esc_html__( 'Flip Box Background Color Frontside', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the frontside background.', 'fusion-builder' ),
								'id'          => 'flip_boxes_front_bg',
								'default'     => '#212934',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'flip_boxes_front_heading'  => [
								'label'       => esc_html__( 'Flip Box Heading Color Frontside', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the frontside heading.', 'fusion-builder' ),
								'id'          => 'flip_boxes_front_heading',
								'default'     => '#f9f9fb',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'flip_boxes_front_text'     => [
								'label'       => esc_html__( 'Flip Box Text Color Frontside', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the frontside text.', 'fusion-builder' ),
								'id'          => 'flip_boxes_front_text',
								'default'     => '#4a4e57',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'flip_boxes_back_bg'        => [
								'label'       => esc_html__( 'Flip Box Background Color Backside', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the backside background.', 'fusion-builder' ),
								'id'          => 'flip_boxes_back_bg',
								'default'     => '#65bc7b',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'flip_boxes_back_heading'   => [
								'label'       => esc_html__( 'Flip Box Heading Color Backside', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the backside heading.', 'fusion-builder' ),
								'id'          => 'flip_boxes_back_heading',
								'default'     => '#ffffff',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'flip_boxes_back_text'      => [
								'label'       => esc_html__( 'Flip Box Text Color Backside', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the backside text.', 'fusion-builder' ),
								'id'          => 'flip_boxes_back_text',
								'default'     => 'rgba(255,255,255,0.8)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'flip_boxes_border_size'    => [
								'label'       => esc_html__( 'Flip Box Border Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border size of the flip box background.', 'fusion-builder' ),
								'id'          => 'flip_boxes_border_size',
								'default'     => '1',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '0',
									'max'  => '50',
									'step' => '1',
								],
							],
							'flip_boxes_border_color'   => [
								'label'       => esc_html__( 'Flip Box Border Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border color of flip box background.', 'fusion-builder' ),
								'id'          => 'flip_boxes_border_color',
								'default'     => 'rgba(0,0,0,0)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'flip_boxes_border_radius'  => [
								'label'       => esc_html__( 'Flip Box Border Radius', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border radius of the flip box background.', 'fusion-builder' ),
								'id'          => 'flip_boxes_border_radius',
								'default'     => '6px',
								'type'        => 'dimension',
								'choices'     => [ 'px', '%', 'em' ],
								'transport'   => 'postMessage',
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

				Fusion_Dynamic_JS::enqueue_script(
					'fusion-flip-boxes',
					FusionBuilder::$js_folder_url . '/general/fusion-flip-boxes.js',
					FusionBuilder::$js_folder_path . '/general/fusion-flip-boxes.js',
					[ 'jquery', 'fusion-animations' ],
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
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/flip-boxes.min.css' );
			}
		}
	}

	new FusionSC_FlipBoxes();

}

/**
 * Map shortcode to Avada Builder
 *
 * @since 1.0
 */
function fusion_element_flip_boxes() {
	$fusion_settings = fusion_get_fusion_settings();
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_FlipBoxes',
			[
				'name'          => esc_attr__( 'Flip Boxes', 'fusion-builder' ),
				'shortcode'     => 'fusion_flip_boxes',
				'multi'         => 'multi_element_parent',
				'element_child' => 'fusion_flip_box',
				'icon'          => 'fusiona-loop-alt2',
				'preview'       => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-flipboxes-preview.php',
				'preview_id'    => 'fusion-builder-block-module-flipboxes-preview-template',
				'help_url'      => 'https://theme-fusion.com/documentation/fusion-builder/elements/flip-boxes-element/',
				'params'        => [
					[
						'type'        => 'tinymce',
						'heading'     => esc_attr__( 'Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter some content for this filp box.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '[fusion_flip_box title_front="' . esc_attr__( 'Your Content Goes Here', 'fusion-builder' ) . '" title_back="' . esc_attr__( 'Your Content Goes Here', 'fusion-builder' ) . '" text_front="' . esc_attr__( 'Your Content Goes Here', 'fusion-builder' ) . '" background_color_front="" title_front_color="" text_front_color="" background_color_back="" title_back_color="" text_back_color="" border_size="" border_color="" border_radius="" icon="" icon_color="" circle="" circle_color="" circle_border_color="" icon_flip="" icon_rotate="" icon_spin="" image="" image_max_width="" animation_offset="" animation_type="" animation_direction="left" animation_speed="0.1"]' . esc_attr__( 'Your Content Goes Here', 'fusion-builder' ) . '[/fusion_flip_box]',
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Number of Columns', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the number of columns per row.', 'fusion-builder' ),
						'param_name'  => 'columns',
						'value'       => '1',
						'min'         => '1',
						'max'         => '6',
						'step'        => '1',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Flip Effect', 'fusion-builder' ),
						'description' => esc_html__( 'Set the flip effect for the boxes.', 'fusion-builder' ),
						'param_name'  => 'flip_effect',
						'default'     => '',
						'value'       => [
							''        => esc_attr__( 'Default', 'fusion-builder' ),
							'classic' => esc_attr__( 'Classic', 'fusion-builder' ),
							'3d'      => esc_attr__( '3d', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Flip Direction', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the direction in which the boxes should flip.', 'fusion-builder' ),
						'param_name'  => 'flip_direction',
						'default'     => '',
						'value'       => [
							''      => esc_attr__( 'Default', 'fusion-builder' ),
							'left'  => esc_attr__( 'Flip Left', 'fusion-builder' ),
							'right' => esc_attr__( 'Flip Right', 'fusion-builder' ),
							'up'    => esc_attr__( 'Flip Up', 'fusion-builder' ),
							'down'  => esc_attr__( 'Flip Down', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Flip Duration', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the speed at which the boxes flip.', 'fusion-builder' ),
						'param_name'  => 'flip_duration',
						'value'       => '',
						'default'     => $fusion_settings->get( 'flip_boxes_flip_duration' ),
						'min'         => '0.1',
						'max'         => '2',
						'step'        => '0.1',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Equal Heights', 'fusion-builder' ),
						'description' => esc_attr__( 'Set to yes to display flip boxes to equal heights.', 'fusion-builder' ),
						'param_name'  => 'equal_heights',
						'default'     => '',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Icon', 'fusion-builder' ),
						'param_name'  => 'icon',
						'value'       => '',
						'description' => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Icon Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the icon. ', 'fusion-builder' ),
						'param_name'  => 'icon_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'icon_color' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Icon Circle', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to use a circled background on the icon.', 'fusion-builder' ),
						'param_name'  => 'circle',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Icon Circle Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the circle. ', 'fusion-builder' ),
						'param_name'  => 'circle_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'icon_circle_color' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Icon Circle Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the circle border. ', 'fusion-builder' ),
						'param_name'  => 'circle_border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'icon_border_color' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Flip Icon', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to flip the icon.', 'fusion-builder' ),
						'param_name'  => 'icon_flip',
						'value'       => [
							''           => esc_attr__( 'None', 'fusion-builder' ),
							'horizontal' => esc_attr__( 'Horizontal', 'fusion-builder' ),
							'vertical'   => esc_attr__( 'Vertical', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Rotate Icon', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to rotate the icon.', 'fusion-builder' ),
						'param_name'  => 'icon_rotate',
						'value'       => [
							''    => esc_attr__( 'None', 'fusion-builder' ),
							'90'  => '90',
							'180' => '180',
							'270' => '270',
						],
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Spinning Icon', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to let the icon spin.', 'fusion-builder' ),
						'param_name'  => 'icon_spin',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'no',
					],
					[
						'type'        => 'upload',
						'heading'     => esc_attr__( 'Icon Image', 'fusion-builder' ),
						'description' => esc_attr__( 'To upload your own icon image, deselect the icon above and then upload your icon image.', 'fusion-builder' ),
						'param_name'  => 'image',
						'value'       => '',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Icon Image ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Icon Image ID from Media Library.', 'fusion-builder' ),
						'param_name'  => 'image_id',
						'value'       => '',
						'hidden'      => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Icon Image Max Width', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the icon image max width. Leave empty to use image\'s native width. In pixels, ex: 35.', 'fusion-builder' ),
						'param_name'  => 'image_max_width',
						'default'     => '35',
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
				],
			],
			'parent'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_flip_boxes' );

/**
 * Map shortcode to Avada Builder
 */
function fusion_element_flip_box() {

	global $fusion_settings;

	$hover_preview = [
		'selector' => '.fusion-flip-box',
		'type'     => 'class',
		'toggle'   => 'hover',
	];

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_FlipBoxes',
			[
				'name'              => esc_attr__( 'Flip Box', 'fusion-builder' ),
				'description'       => esc_attr__( 'Enter some content for this textblock', 'fusion-builder' ),
				'shortcode'         => 'fusion_flip_box',
				'hide_from_builder' => true,
				'allow_generator'   => true,
				'params'            => [
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Flip Direction', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the direction in which the boxes should flip.', 'fusion-builder' ),
						'param_name'  => 'flip_direction',
						'default'     => '',
						'value'       => [
							''      => esc_attr__( 'Default', 'fusion-builder' ),
							'left'  => esc_attr__( 'Flip Left', 'fusion-builder' ),
							'right' => esc_attr__( 'Flip Right', 'fusion-builder' ),
							'up'    => esc_attr__( 'Flip Up', 'fusion-builder' ),
							'down'  => esc_attr__( 'Flip Down', 'fusion-builder' ),
						],
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Flip Box Frontside Heading', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add a heading for the frontside of the flip box.', 'fusion-builder' ),
						'param_name'   => 'title_front',
						'value'        => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Flip Box Backside Heading', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add a heading for the backside of the flip box.', 'fusion-builder' ),
						'param_name'   => 'title_back',
						'value'        => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder'  => true,
						'preview'      => $hover_preview,
						'dynamic_data' => true,
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Flip Box Frontside Content', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add content for the frontside of the flip box.', 'fusion-builder' ),
						'param_name'   => 'text_front',
						'value'        => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
					],
					[
						'type'         => 'tinymce',
						'heading'      => esc_attr__( 'Flip Box Backside Content', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add content for the backside of the flip box.', 'fusion-builder' ),
						'param_name'   => 'element_content',
						'value'        => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder'  => true,
						'preview'      => $hover_preview,
						'dynamic_data' => true,
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color Frontside', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the frontside. IMPORTANT: Flip boxes must have background colors to work correctly in all browsers.', 'fusion-builder' ),
						'param_name'  => 'background_color_front',
						'value'       => '',
						'default'     => $fusion_settings->get( 'flip_boxes_front_bg' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'upload',
						'heading'     => esc_attr__( 'Background Image Frontside', 'fusion-builder' ),
						'description' => esc_attr__( 'Upload an image to display in the background of the frontside.', 'fusion-builder' ),
						'param_name'  => 'background_image_front',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Background Image ID Frontside', 'fusion-builder' ),
						'description' => esc_attr__( 'Background Image ID from Media Library.', 'fusion-builder' ),
						'param_name'  => 'background_image_id_front',
						'value'       => '',
						'hidden'      => true,
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Heading Color Frontside', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the heading color of the frontside. ', 'fusion-builder' ),
						'param_name'  => 'title_front_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'flip_boxes_front_heading' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Text Color Frontside', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the frontside. ', 'fusion-builder' ),
						'param_name'  => 'text_front_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'flip_boxes_front_text' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color Backside', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the backside. IMPORTANT: Flip boxes must have background colors to work correctly in all browsers.', 'fusion-builder' ),
						'param_name'  => 'background_color_back',
						'value'       => '',
						'default'     => $fusion_settings->get( 'flip_boxes_back_bg' ),
						'preview'     => $hover_preview,
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'upload',
						'heading'     => esc_attr__( 'Background Image Backside', 'fusion-builder' ),
						'description' => esc_attr__( 'Upload an image to display in the background of the backside.', 'fusion-builder' ),
						'param_name'  => 'background_image_back',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Background Image ID Backside', 'fusion-builder' ),
						'description' => esc_attr__( 'Background Image ID from Media Library.', 'fusion-builder' ),
						'param_name'  => 'background_image_id_back',
						'value'       => '',
						'hidden'      => true,
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Heading Color Backside', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the heading color of the backside. ', 'fusion-builder' ),
						'param_name'  => 'title_back_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'flip_boxes_back_heading' ),
						'preview'     => $hover_preview,
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Text Color Backside', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the backside. ', 'fusion-builder' ),
						'param_name'  => 'text_back_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'flip_boxes_back_text' ),
						'preview'     => $hover_preview,
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'In pixels.', 'fusion-builder' ),
						'param_name'  => 'border_size',
						'value'       => '',
						'min'         => '0',
						'max'         => '50',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'flip_boxes_border_size' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color. ', 'fusion-builder' ),
						'param_name'  => 'border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'flip_boxes_border_color' ),
						'dependency'  => [
							[
								'element'  => 'border_size',
								'value'    => '0',
								'operator' => '!=',
							],
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Border Radius', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the flip box border radius. In pixels (px), ex: 1px, or "round". ', 'fusion-builder' ),
						'param_name'  => 'border_radius',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Icon', 'fusion-builder' ),
						'param_name'  => 'icon',
						'value'       => '',
						'description' => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Icon Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the icon. ', 'fusion-builder' ),
						'param_name'  => 'icon_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'icon_color' ),
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
						'heading'     => esc_attr__( 'Icon Circle', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to use a circled background on the icon.', 'fusion-builder' ),
						'param_name'  => 'circle',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'icon',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Icon Circle Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the circle. ', 'fusion-builder' ),
						'param_name'  => 'circle_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'icon_circle_color' ),
						'dependency'  => [
							[
								'element'  => 'icon',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'circle',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Icon Circle Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the circle border. ', 'fusion-builder' ),
						'param_name'  => 'circle_border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'icon_border_color' ),
						'dependency'  => [
							[
								'element'  => 'icon',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'circle',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Flip Icon', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to flip the icon.', 'fusion-builder' ),
						'param_name'  => 'icon_flip',
						'value'       => [
							''           => esc_attr__( 'Default', 'fusion-builder' ),
							'none'       => esc_attr__( 'None', 'fusion-builder' ),
							'horizontal' => esc_attr__( 'Horizontal', 'fusion-builder' ),
							'vertical'   => esc_attr__( 'Vertical', 'fusion-builder' ),
						],
						'default'     => '',
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
						'heading'     => esc_attr__( 'Rotate Icon', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to rotate the icon.', 'fusion-builder' ),
						'param_name'  => 'icon_rotate',
						'value'       => [
							''     => esc_attr__( 'Default', 'fusion-builder' ),
							'none' => esc_attr__( 'None', 'fusion-builder' ),
							'90'   => '90',
							'180'  => '180',
							'270'  => '270',
						],
						'default'     => '',
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
						'heading'     => esc_attr__( 'Spinning Icon', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to let the icon spin.', 'fusion-builder' ),
						'param_name'  => 'icon_spin',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'icon',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'upload',
						'heading'     => esc_attr__( 'Icon Image', 'fusion-builder' ),
						'description' => esc_attr__( 'To upload your own icon image, deselect the icon above and then upload your icon image.', 'fusion-builder' ),
						'param_name'  => 'image',
						'value'       => '',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Image ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Image ID from Media Library.', 'fusion-builder' ),
						'param_name'  => 'image_id',
						'value'       => '',
						'hidden'      => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Icon Image Max Width', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the icon image max width. Leave empty for value set in parent option. Set to -1 to use image\'s native width. In pixels, ex: 35.', 'fusion-builder' ),
						'param_name'  => 'image_max_width',
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'image',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-flip-box',
					],
				],
			],
			'child'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_flip_box' );
