<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_images' ) ) {

	if ( ! class_exists( 'FusionSC_ImageCarousel' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_ImageCarousel extends Fusion_Element {

			/**
			 * Image Carousels counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $image_carousel_counter = 1;

			/**
			 * Total number of images.
			 *
			 * @access private
			 * @since 1.8
			 * @var int
			 */
			private $number_of_images = 1;

			/**
			 * The image data.
			 *
			 * @access private
			 * @since 1.0
			 * @var false|array
			 */
			private $image_data = false;

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
				add_filter( 'fusion_attr_image-carousel-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_image-carousel-shortcode-carousel', [ $this, 'carousel_attr' ] );
				add_filter( 'fusion_attr_image-carousel-shortcode-slide-link', [ $this, 'slide_link_attr' ] );
				add_filter( 'fusion_attr_fusion-image-wrapper', [ $this, 'image_wrapper' ] );

				add_shortcode( 'fusion_images', [ $this, 'render_parent' ] );
				add_shortcode( 'fusion_image', [ $this, 'render_child' ] );

				add_shortcode( 'fusion_clients', [ $this, 'render_parent' ] );
				add_shortcode( 'fusion_client', [ $this, 'render_child' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_fusion_image_carousel', [ $this, 'ajax_query_single_child' ] );

				add_action( 'wp_ajax_get_fusion_image_carousel_children_data', [ $this, 'query_children' ] );

			}

			/**
			 * Gets the query data.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return void
			 */
			public function ajax_query_single_child() {
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );
				$this->query_single_child();
			}

			/**
			 * Gets the query data for single children.
			 *
			 * @access public
			 * @since 2.0.0
			 */
			public function query_single_child() {
				global $fusion_settings;

				// From Ajax Request.
				if ( isset( $_POST['model'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$defaults = [
						'image_id' => '',
					];
					if ( isset( $_POST['model']['params'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
						$defaults = $_POST['model']['params']; // phpcs:ignore WordPress.Security
					}

					$return_data['image_data'] = fusion_library()->images->get_attachment_data_by_helper( $defaults['image_id'] );

					$image_sizes = [ 'full', 'portfolio-two', 'blog-medium' ];
					foreach ( $image_sizes as $image_size ) {
						$return_data[ $return_data['image_data']['url'] ][ $image_size ] = wp_get_attachment_image( $return_data['image_data']['id'], $image_size );
					}
					echo wp_json_encode( $return_data );
				}
				wp_die();
			}

			/**
			 * Gets the query data for all children.
			 *
			 * @access public
			 * @since 2.0.0
			 */
			public function query_children() {
				global $fusion_settings;

				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

				$return_data = [];

				// From Ajax Request.
				if ( isset( $_POST['children'] ) ) {
					$children    = $_POST['children']; // phpcs:ignore WordPress.Security
					$image_sizes = [ 'full', 'portfolio-two', 'blog-medium' ];

					foreach ( $children as $cid => $image_data ) {
						if ( isset( $children[ $cid ]['image_id'] ) && $children[ $cid ]['image_id'] ) {
							$image_id = explode( '|', $children[ $cid ]['image_id'] );
							$image_id = $image_id[0];
						} else {
							$image_data = fusion_library()->images->get_attachment_data_by_helper( '', $children[ $cid ]['image'] );
							$image_id   = $image_data['id'];
						}

						foreach ( $image_sizes as $image_size ) {
							$return_data[ $children[ $cid ]['image'] ][ $image_size ] = wp_get_attachment_image( $image_id, $image_size );
						}
					}

					echo wp_json_encode( $return_data );
				}
				wp_die();
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

				$parent = [
					'hide_on_mobile' => fusion_builder_default_visibility( 'string' ),
					'class'          => '',
					'id'             => '',
					'autoplay'       => 'no',
					'border'         => 'yes',
					'columns'        => '5',
					'column_spacing' => '13',
					'image_id'       => '',
					'lightbox'       => 'no',
					'mouse_scroll'   => 'no',
					'picture_size'   => 'fixed',
					'scroll_items'   => '',
					'show_nav'       => 'yes',
					'hover_type'     => 'none',
				];

				$child = [
					'alt'        => '',
					'image'      => '',
					'image_id'   => '',
					'link'       => '',
					'linktarget' => '_self',
				];

				if ( 'parent' === $context ) {
					return $parent;
				} elseif ( 'child' === $context ) {
					return $child;
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

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'parent' ), $args, 'fusion_images' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_images', $args );

				$defaults['column_spacing'] = FusionBuilder::validate_shortcode_attr_value( $defaults['column_spacing'], '' );

				extract( $defaults );

				$this->parent_args = $defaults;

				preg_match_all( '/\[fusion_image (.*?)\]/s', $content, $matches );

				preg_match_all( '/\[fusion_image (.*?)\]/s', $content, $matches );

				if ( isset( $matches[0] ) ) {
					$this->number_of_images = count( $matches[0] );
				}

				$html  = '<div ' . FusionBuilder::attributes( 'image-carousel-shortcode' ) . '>';
				$html .= '<div ' . FusionBuilder::attributes( 'image-carousel-shortcode-carousel' ) . '>';
				$html .= '<div ' . FusionBuilder::attributes( 'fusion-carousel-positioner' ) . '>';

				// The main carousel.
				$html .= '<ul ' . FusionBuilder::attributes( 'fusion-carousel-holder' ) . '>';
				$html .= do_shortcode( $content );
				$html .= '</ul>';

				// Check if navigation should be shown.
				if ( 'yes' === $show_nav ) {
					$html .= '<div ' . FusionBuilder::attributes( 'fusion-carousel-nav' ) . '>';
					$html .= '<span ' . FusionBuilder::attributes( 'fusion-nav-prev' ) . '></span>';
					$html .= '<span ' . FusionBuilder::attributes( 'fusion-nav-next' ) . '></span>';
					$html .= '</div>';
				}
				$html .= '</div>';
				$html .= '</div>';
				$html .= '</div>';

				$this->image_carousel_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_image_carousel_parent_content', $html, $args );

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
						'class' => 'fusion-image-carousel fusion-image-carousel-' . $this->parent_args['picture_size'],
					]
				);

				if ( 'yes' === $this->parent_args['lightbox'] ) {
					$attr['class'] .= ' lightbox-enabled';
				}

				if ( 'yes' === $this->parent_args['border'] ) {
					$attr['class'] .= ' fusion-carousel-border';
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
			 * Builds the carousel attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function carousel_attr() {

				$attr['class']            = 'fusion-carousel';
				$attr['data-autoplay']    = $this->parent_args['autoplay'];
				$attr['data-columns']     = $this->parent_args['columns'];
				$attr['data-itemmargin']  = $this->parent_args['column_spacing'];
				$attr['data-itemwidth']   = 180;
				$attr['data-touchscroll'] = $this->parent_args['mouse_scroll'];
				$attr['data-imagesize']   = $this->parent_args['picture_size'];
				$attr['data-scrollitems'] = $this->parent_args['scroll_items'];
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

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'child' ), $args, 'fusion_image' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_image', $args );

				extract( $defaults );

				$this->child_args = $defaults;

				$width = $height = '';

				$image_size = 'full';
				if ( 'fixed' === $this->parent_args['picture_size'] ) {
					$image_size = 'portfolio-two';
					if ( '6' === $this->parent_args['columns'] || '5' === $this->parent_args['columns'] || '4' === $this->parent_args['columns'] ) {
						$image_size = 'blog-medium';
					}
				}

				$this->image_data = fusion_library()->images->get_attachment_data_by_helper( $this->child_args['image_id'], $image );

				$output = '';
				if ( $this->image_data && $this->image_data['id'] ) {

					// Responsive images.
					$number_of_columns = ( $this->number_of_images < $this->parent_args['columns'] ) ? $this->number_of_images : $this->parent_args['columns'];

					if ( 1 < $number_of_columns || 'full' !== $image_size ) {
						fusion_library()->images->set_grid_image_meta(
							[
								'layout'       => 'grid',
								'columns'      => $number_of_columns,
								'gutter_width' => $this->parent_args['column_spacing'],
							]
						);
					}

					if ( $alt ) {
						$output = wp_get_attachment_image( $this->image_data['id'], $image_size, false, [ 'alt' => $alt ] );
					} else {
						$output = wp_get_attachment_image( $this->image_data['id'], $image_size );
					}

					if ( 'full' === $image_size ) {
						$output = fusion_library()->images->edit_grid_image_src( $output, null, $this->image_data['id'], 'full' );
					}

					fusion_library()->images->set_grid_image_meta( [] );

				} else {
					$output = '<img src="' . $image . '" alt="' . $alt . '"/>';
				}

				$output = fusion_library()->images->apply_lazy_loading( $output, null, $this->image_data['id'], 'full' );

				if ( 'no' === $this->parent_args['mouse_scroll'] && ( $link || 'yes' === $this->parent_args['lightbox'] ) ) {
					$output = '<a ' . FusionBuilder::attributes( 'image-carousel-shortcode-slide-link' ) . '>' . $output . '</a>';
				}

				$output = '<li ' . FusionBuilder::attributes( 'fusion-carousel-item' ) . '><div ' . FusionBuilder::attributes( 'fusion-carousel-item-wrapper' ) . '><div ' . FusionBuilder::attributes( 'fusion-image-wrapper' ) . '>' . $output . '</div></div></li>';

				return apply_filters( 'fusion_element_image_carousel_child_content', $output, $args );
			}

			/**
			 * Builds the slide-link attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function slide_link_attr() {

				$attr = [];

				if ( 'yes' === $this->parent_args['lightbox'] ) {

					if ( ! $this->child_args['link'] ) {
						$this->child_args['link'] = $this->child_args['image'];
					}

					$attr['data-rel'] = 'iLightbox[image_carousel_' . $this->image_carousel_counter . ']';

					if ( $this->image_data ) {
						$attr['data-caption'] = $this->image_data['caption'];
						$attr['data-title']   = $this->image_data['title'];
						$attr['aria-label']   = $this->image_data['title'];
					}
				}

				$attr['href'] = $this->child_args['link'];

				$attr['target'] = $this->child_args['linktarget'];
				if ( '_blank' === $this->child_args['linktarget'] ) {
					$attr['rel'] = 'noopener noreferrer';
				}
				return $attr;

			}

			/**
			 * Builds the image-wrapper attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function image_wrapper() {
				if ( $this->parent_args['hover_type'] ) {
					return [
						'class' => 'fusion-image-wrapper hover-type-' . $this->parent_args['hover_type'],
					];
				}
				return [
					'class' => 'fusion-image-wrapper',
				];
			}

			/**
			 * Builds the "previous" nav attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function fusion_nav_prev() {
				return [
					'class' => 'fusion-nav-prev fusion-icon-left',
				];
			}

			/**
			 * Builds the "next" nav attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function fusion_nav_next() {
				return [
					'class' => 'fusion-nav-next fusion-icon-right',
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
				Fusion_Dynamic_JS::enqueue_script( 'fusion-lightbox' );
				Fusion_Dynamic_JS::enqueue_script( 'fusion-carousel' );
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/image-carousel.min.css' );
			}
		}
	}

	new FusionSC_ImageCarousel();

}

/**
 * Map shortcode to Avada Builder.
 */
function fusion_element_images() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_ImageCarousel',
			[
				'name'          => esc_attr__( 'Image Carousel', 'fusion-builder' ),
				'shortcode'     => 'fusion_images',
				'multi'         => 'multi_element_parent',
				'element_child' => 'fusion_image',
				'icon'          => 'fusiona-images',
				'preview'       => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-image-carousel-preview.php',
				'preview_id'    => 'fusion-builder-block-module-image-carousel-preview-template',
				'child_ui'      => true,
				'sortable'      => false,
				'help_url'      => 'https://theme-fusion.com/documentation/fusion-builder/elements/image-carousel-element/',
				'params'        => [
					[
						'type'        => 'tinymce',
						'heading'     => esc_attr__( 'Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter some content for this image carousel.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '[fusion_image link="" linktarget="_self" alt="" image_id="" /]',
					],
					[
						'type'             => 'multiple_upload',
						'heading'          => esc_attr__( 'Bulk Image Upload', 'fusion-builder' ),
						'description'      => __( 'This option allows you to select multiple images at once and they will populate into individual items. It saves time instead of adding one image at a time.', 'fusion-builder' ),
						'param_name'       => 'multiple_upload',
						'dynamic_data'     => true,
						'child_params'     => [
							'image'    => 'url',
							'image_id' => 'id',
						],
						'remove_from_atts' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Picture Size', 'fusion-builder' ),
						'description' => __( 'fixed = width and height will be fixed <br />auto = width and height will adjust to the image.', 'fusion-builder' ),
						'param_name'  => 'picture_size',
						'value'       => [
							'fixed' => esc_attr__( 'Fixed', 'fusion-builder' ),
							'auto'  => esc_attr__( 'Auto', 'fusion-builder' ),
						],
						'default'     => 'fixed',
						'callback'    => [
							'function' => 'fusion_carousel_images',
							'action'   => 'get_fusion_image_carousel_children_data',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Hover Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the hover effect type.', 'fusion-builder' ),
						'param_name'  => 'hover_type',
						'value'       => [
							'none'    => esc_attr__( 'None', 'fusion-builder' ),
							'zoomin'  => esc_attr__( 'Zoom In', 'fusion-builder' ),
							'zoomout' => esc_attr__( 'Zoom Out', 'fusion-builder' ),
							'liftup'  => esc_attr__( 'Lift Up', 'fusion-builder' ),
						],
						'default'     => 'none',
						'preview'     => [
							'selector' => '.fusion-image-wrapper',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Autoplay', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to autoplay the carousel.', 'fusion-builder' ),
						'param_name'  => 'autoplay',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'no',
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Maximum Columns', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the number of max columns to display.', 'fusion-builder' ),
						'param_name'  => 'columns',
						'value'       => '5',
						'min'         => '1',
						'max'         => '6',
						'step'        => '1',
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Column Spacing', 'fusion-builder' ),
						'description' => esc_attr__( 'Insert the amount of spacing between items without "px". ex: 13.', 'fusion-builder' ),
						'param_name'  => 'column_spacing',
						'value'       => '13',
						'min'         => '0',
						'max'         => '300',
						'step'        => '1',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Scroll Items', 'fusion-builder' ),
						'description' => esc_attr__( 'Insert the amount of items to scroll. Leave empty to scroll number of visible items.', 'fusion-builder' ),
						'param_name'  => 'scroll_items',
						'value'       => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Navigation', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show navigation buttons on the carousel.', 'fusion-builder' ),
						'param_name'  => 'show_nav',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Mouse Scroll', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to enable mouse drag control on the carousel. IMPORTANT: For easy draggability, when mouse scroll is activated, links will be disabled.', 'fusion-builder' ),
						'param_name'  => 'mouse_scroll',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'no',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Border', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to enable a border around the images.', 'fusion-builder' ),
						'param_name'  => 'border',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Image lightbox', 'fusion-builder' ),
						'description' => esc_attr__( 'Show image in lightbox. Lightbox must be enabled in Global Options or the image will open up in the same tab by itself.', 'fusion-builder' ),
						'param_name'  => 'lightbox',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'no',
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => __( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
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
add_action( 'fusion_builder_before_init', 'fusion_element_images' );

/**
 * Map shortcode to Avada Builder.
 */
function fusion_element_fusion_image() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_ImageCarousel',
			[
				'name'              => esc_attr__( 'Image', 'fusion-builder' ),
				'description'       => esc_attr__( 'Enter some content for this textblock.', 'fusion-builder' ),
				'shortcode'         => 'fusion_image',
				'hide_from_builder' => true,
				'params'            => [
					[
						'type'        => 'upload',
						'heading'     => esc_attr__( 'Image', 'fusion-builder' ),
						'description' => esc_attr__( 'Upload an image to display.', 'fusion-builder' ),
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
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_image_carousel',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'link_selector',
						'heading'     => esc_attr__( 'Image Link', 'fusion-builder' ),
						'description' => esc_attr__( 'Add the url the image should link to. If lightbox option is enabled, you can also use this to open a different image in the lightbox.', 'fusion-builder' ),
						'param_name'  => 'link',
						'value'       => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Link Target', 'fusion-builder' ),
						'description' => __( '_self = open in same window <br />_blank = open in new window.', 'fusion-builder' ),
						'param_name'  => 'linktarget',
						'value'       => [
							'_self'  => esc_attr__( '_self', 'fusion-builder' ),
							'_blank' => esc_attr__( '_blank', 'fusion-builder' ),
						],
						'default'     => '_self',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Image Alt Text', 'fusion-builder' ),
						'description' => esc_attr__( 'The alt attribute provides alternative information if an image cannot be viewed.', 'fusion-builder' ),
						'param_name'  => 'alt',
						'value'       => '',
					],
				],
				'tag_name'          => 'li',
				'callback'          => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_image_carousel',
					'ajax'     => true,
				],
			],
			'child'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_fusion_image' );
