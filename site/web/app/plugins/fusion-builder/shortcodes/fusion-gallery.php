<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_gallery' ) ) {

	if ( ! class_exists( 'FusionSC_FusionGallery' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-builder
		 * @since 1.0
		 */
		class FusionSC_FusionGallery extends Fusion_Element {

			/**
			 * An array of the parent shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $parent_args;

			/**
			 * An array of the child shortcode arguments.
			 *
			 * @access protected
			 * @since 1.8
			 * @var array
			 */
			protected $child_args;

			/**
			 * Number of columns.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $num_of_columns = 1;

			/**
			 * Total number of columns.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $total_num_of_columns = 1;

			/**
			 * The image szie.
			 *
			 * @access private
			 * @since 1.8
			 * @var string
			 */
			private $image_size = '';

			/**
			 * The image data.
			 *
			 * @access private
			 * @since 1.0
			 * @var false|array
			 */
			private $image_data = false;

			/**
			 * The image counter.
			 *
			 * @access private
			 * @since 1.8
			 * @var int
			 */
			private $image_counter = 1;

			/**
			 * The gallery counter.
			 *
			 * @access private
			 * @since 1.5.3
			 * @var int
			 */
			private $gallery_counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_shortcode( 'fusion_gallery', [ $this, 'render' ] );
				add_shortcode( 'fusion_gallery_image', [ $this, 'render_child' ] );

				add_filter( 'fusion_attr_gallery-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_gallery-shortcode-masonry-wrapper', [ $this, 'masonry_wrapper_attr' ] );
				add_filter( 'fusion_attr_gallery-shortcode-images', [ $this, 'image_attr' ] );
				add_filter( 'fusion_attr_gallery-shortcode-link', [ $this, 'link_attr' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_fusion_gallery', [ $this, 'query' ] );
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
				global $fusion_library, $fusion_settings;

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'parent' ), $args, 'fusion_gallery' );
				$defaults = apply_filters( 'fusion_builder_default_args', $defaults, 'fusion_gallery', $args );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_gallery', $args );

				$defaults['bordersize']    = FusionBuilder::validate_shortcode_attr_value( $defaults['bordersize'], 'px' );
				$defaults['border_radius'] = FusionBuilder::validate_shortcode_attr_value( $defaults['border_radius'], 'px' );

				if ( 'round' === $defaults['border_radius'] ) {
					$defaults['border_radius'] = '50%';
				}

				extract( $defaults );

				$this->parent_args = $defaults;

				$this->num_of_columns = $this->parent_args['columns'];
				$image_ids            = '';

				$this->parent_args['original_picture_size'] = $this->parent_args['picture_size'];
				if ( 'fixed' === $this->parent_args['picture_size'] && 'masonry' !== $this->parent_args['layout'] ) {
					$this->parent_args['picture_size'] = 'portfolio-two';
					if ( in_array( (int) $this->parent_args['columns'], [ 4, 5, 6 ], true ) ) {
						$this->parent_args['picture_size'] = 'blog-medium';
					}
				} else {
					$this->parent_args['picture_size'] = 'full';
				}

				if ( $this->parent_args['image_ids'] ) {
					$image_ids                  = explode( ',', $this->parent_args['image_ids'] );
					$this->total_num_of_columns = count( $image_ids );
					$this->child_args           = [];
				} else {
					preg_match_all( '/\[fusion_gallery_image (.*?)\]/s', $content, $matches );

					if ( is_array( $matches ) && ! empty( $matches ) ) {
						$this->total_num_of_columns = count( $matches[0] );
					}
				}

				$this->parent_args['column_spacing'] = $fusion_library->sanitize->get_value_with_unit( $this->parent_args['column_spacing'] / 2 );

				$html = '<div ' . FusionBuilder::attributes( 'gallery-shortcode' ) . '>';

				if ( 'masonry' === $this->parent_args['layout'] ) {
					$this->parent_args['grid_sizer'] = true;
					$html                           .= '<div ' . FusionBuilder::attributes( 'gallery-shortcode-images' ) . '></div>';
				}

				if ( $image_ids ) {
					foreach ( $image_ids as $image_id ) {
						$html .= $this->get_image_markup( $image_id, '', false );
					}
				} else {
					$html .= do_shortcode( $content );
				}

				$html .= '</div>';

				$styles = '';

				if ( '' !== $this->parent_args['bordersize'] && 0 !== $this->parent_args['bordersize'] ) {
					$styles .= '.fusion-gallery-' . $this->gallery_counter . ' .fusion-gallery-image {';
					$styles .= "border:{$bordersize} solid {$bordercolor};";
					if ( '0' !== $this->parent_args['border_radius'] && 0 !== $this->parent_args['border_radius'] && '0px' !== $this->parent_args['border_radius'] && 'px' !== $this->parent_args['border_radius'] ) {
						$styles .= "-webkit-border-radius:{$border_radius};-moz-border-radius:{$border_radius};border-radius:{$border_radius};";
						if ( '50%' === $this->parent_args['border_radius'] || 100 < (int) $this->parent_args['border_radius'] ) {
								$styles .= '-webkit-mask-image: -webkit-radial-gradient(circle, white, black);';
						}
					}
					$styles .= '}';
				}

				if ( '' !== $styles ) {
					$style_tag = '<style type="text/css">' . $styles . '</style>';
				}

				$this->gallery_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_gallery_parent_content', $style_tag . $html, $args );
			}

			/**
			 * Render the child shortcode.
			 *
			 * @access public
			 * @since 1.8
			 * @param  array  $args   Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string         HTML output.
			 */
			public function render_child( $args, $content = '' ) {
				global $fusion_library;

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'child' ), $args, 'fusion_gallery_image' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_gallery_image', $args );

				extract( $defaults );

				$this->child_args = $defaults;

				$image_url = '' === $this->child_args['image_id'] ? $this->child_args['image'] : '';
				$html      = $this->get_image_markup( $this->child_args['image_id'], $image_url, false );

				return apply_filters( 'fusion_element_gallery_child_content', $html, $args );
			}

			/**
			 * Render the markup of an image.
			 *
			 * @access public
			 * @since 1.8
			 * @param int    $image_id    The image ID.
			 * @param string $image       The image URL.
			 * @param array  $parent_args Parent element arguments.
			 * @return string The HTML output of the image.
			 */
			public function get_image_markup( $image_id, $image = '', $parent_args = false ) {
				global $fusion_library;

				$ajax_request = false;

				if ( false === $parent_args ) {
					$parent_args = $this->parent_args;
				} else {
					$ajax_request = true;
				}
				$image_html = $image_url = $image_class = '';

				$image_id = explode( '|', $image_id );
				$image_id = $image_id[0];

				$this->image_data = $fusion_library->get_images_obj()->get_attachment_data_by_helper( $image_id . '|' . $parent_args['picture_size'], $image );

				if ( $this->image_data['url'] ) {
					$image_url = $this->image_data['url'];
				}

				$css_class = 'img-responsive wp-image-' . $image_id;

				if ( isset( $parent_args['original_picture_size'] ) && 'fixed' === $parent_args['original_picture_size'] ) {
					$css_class .= ' fusion-gallery-image-size-fixed';
				}

				$image = '<img src="' . $image_url . '" width="' . $this->image_data['width'] . '" height="' . $this->image_data['height'] . '" alt="' . $this->image_data['alt'] . '" title="' . $this->image_data['title'] . '" aria-label="' . $this->image_data['title'] . '" class="' . $css_class . '" />';

				// For masonry layout, set the correct column size and classes.
				$element_orientation_class = '';
				$responsive_images_columns = $parent_args['columns'];
				if ( 'masonry' === $parent_args['layout'] && $this->image_data ) {

					// Get the correct image orientation class.
					$element_orientation_class                = $fusion_library->get_images_obj()->get_element_orientation_class( $this->image_data['id'], [], $parent_args['gallery_masonry_grid_ratio'], $parent_args['gallery_masonry_width_double'] );
					$element_base_padding                     = $fusion_library->get_images_obj()->get_element_base_padding( $element_orientation_class );
					$this->image_data['orientation_class']    = $element_orientation_class;
					$this->image_data['element_base_padding'] = $element_base_padding;

					// Check if we have a landscape image, then it has to stretch over 2 cols.
					if ( 1 !== $parent_args['columns'] && '1' !== $parent_args['columns'] && false !== strpos( $element_orientation_class, 'fusion-element-landscape' ) ) {
						$responsive_images_columns = (int) $parent_args['columns'] / 2;
					}
				}

				// Responsive images.
				$fusion_library->get_images_obj()->set_grid_image_meta(
					[
						'layout'       => $parent_args['layout'],
						'columns'      => $responsive_images_columns,
						'gutter_width' => $parent_args['column_spacing'],
					]
				);

				if ( 'full' === $parent_args['picture_size'] ) {
					$image = $fusion_library->get_images_obj()->edit_grid_image_src( $image, null, $image_id, 'full' );
				}

				$image = fusion_add_responsive_image_markup( $image );

				$image = $fusion_library->get_images_obj()->apply_lazy_loading( $image, null, $image_id, $this->parent_args['picture_size'] );

				$fusion_library->get_images_obj()->set_grid_image_meta( [] );

				if ( ! $ajax_request ) {
					if ( 'masonry' === $parent_args['layout'] ) {
						$image = '<div ' . FusionBuilder::attributes( 'gallery-shortcode-masonry-wrapper' ) . '>' . $image . '</div>';
					}

					$image_html .= '<div ' . FusionBuilder::attributes( 'gallery-shortcode-images' ) . '>';

					if ( 'liftup' === $parent_args['hover_type'] ) {
						$image_class = ' fusion-gallery-image-liftup';
					}
					$image_html .= '<div class="fusion-gallery-image' . $image_class . '">';

					if ( ! empty( $this->child_args['link'] ) || ( 'no' !== $parent_args['lightbox'] && $parent_args['lightbox'] ) ) {
						$image_html .= '<a ' . FusionBuilder::attributes( 'gallery-shortcode-link' ) . '>' . $image . '</a>';
					} else {
						$image_html .= $image;
					}

					$image_html .= '</div>';
					$image_html .= '</div>';

					if ( 0 === $this->image_counter % $this->num_of_columns && 'grid' === $parent_args['layout'] ) {
						$image_html .= '<div class="clearfix"></div>';
					}
				} else {
					$image_html .= $image;
				}

				$this->image_counter++;

				return $image_html;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$attr = [];

				$attr['class']  = 'fusion-gallery fusion-gallery-container';
				$attr['class'] .= ' fusion-grid-' . $this->num_of_columns;
				$attr['class'] .= ' fusion-columns-total-' . $this->total_num_of_columns;
				$attr['class'] .= ' fusion-gallery-layout-' . $this->parent_args['layout'];
				$attr['class'] .= ' fusion-gallery-' . $this->gallery_counter;

				if ( $this->parent_args['column_spacing'] ) {
					$margin        = ( -1 ) * (int) $this->parent_args['column_spacing'];
					$attr['style'] = 'margin:' . $margin . 'px;';
				}

				$attr = fusion_builder_visibility_atts( $this->parent_args['hide_on_mobile'], $attr );

				if ( $this->parent_args['class'] ) {
					$attr['class'] .= ' ' . $this->parent_args['class'];
				}

				if ( $this->parent_args['id'] ) {
					$attr['id'] = $this->parent_args['id'];
				}

				return $attr;
			}

			/**
			 * Builds the attributes array for the masonry image wrapper.
			 *
			 * @access public
			 * @since 1.2
			 * @return array
			 */
			public function masonry_wrapper_attr() {
				global $fusion_settings;

				$lazy_load = ( 'avada' === $fusion_settings->get( 'lazy_load' ) && ! is_feed() ) ? true : false;

				$attr = [
					'style' => '',
					'class' => 'fusion-masonry-element-container',
				];

				if ( isset( $this->image_data['url'] ) ) {
					$attr['style'] .= $lazy_load ? '' : 'background-image:url(' . $this->image_data['url'] . ');';
				}

				if ( isset( $this->image_data['element_base_padding'] ) ) {

					// If portrait it requires more spacing.
					$column_offset = ' - ' . $this->parent_args['column_spacing'];
					if ( false !== strpos( $this->image_data['orientation_class'], 'fusion-element-portrait' ) ) {
						$column_offset = '';
					}

					$column_spacing = 2 * (int) $this->parent_args['column_spacing'] . 'px';

					// Calculate the correct size of the image wrapper container, based on orientation and column spacing.
					$attr['style'] .= 'padding-top:calc((100% + ' . $column_spacing . ') * ' . $this->image_data['element_base_padding'] . $column_offset . ');';
				}

				if ( $lazy_load && isset( $this->image_data['url'] ) ) {
					$attr['data-bg'] = $this->image_data['url'];
					$attr['class']  .= ' lazyload';
				}

				return $attr;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.2
			 * @return array
			 */
			public function image_attr() {
				global $fusion_library;

				$columns = 12 / $this->num_of_columns;

				$attr           = [
					'style' => '',
					'class' => 'fusion-grid-column',
				];
				$attr['class'] .= ' fusion-gallery-column fusion-gallery-column-' . $this->num_of_columns;

				if ( 'liftup' !== $this->parent_args['hover_type'] ) {
					$attr['class'] .= ' hover-type-' . $this->parent_args['hover_type'];
				}

				if ( isset( $this->image_data['orientation_class'] ) ) {
					$attr['class'] .= ' ' . $this->image_data['orientation_class'];
				}

				if ( '' !== $this->parent_args['column_spacing'] && ! ( isset( $this->parent_args['grid_sizer'] ) && $this->parent_args['grid_sizer'] ) ) {
					$attr['style'] = 'padding:' . $this->parent_args['column_spacing'] . ';';
				}

				if ( isset( $this->parent_args['grid_sizer'] ) && $this->parent_args['grid_sizer'] ) {
					$this->parent_args['grid_sizer'] = false;
					$attr['class']                  .= ' fusion-grid-sizer';
				}

				return $attr;
			}

			/**
			 * Builds the link attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function link_attr() {

				$attr = [];

				if ( ! empty( $this->child_args['link'] ) ) {
					$attr['href'] = $this->child_args['link'];
				} elseif ( 'fixed' === $this->parent_args['original_picture_size'] && $this->image_data['id'] ) {
					$image_data = fusion_library()->get_images_obj()->get_attachment_data( $this->image_data['id'], 'full' );

					if ( $image_data['url'] ) {
						$attr['href'] = $image_data['url'];
					}
				}

				if ( ! isset( $attr['href'] ) ) {
					$attr['href'] = $this->image_data['url'];
				}

				if ( 'yes' === $this->parent_args['lightbox'] ) {
					if ( $this->image_data ) {

						if ( false !== strpos( $this->parent_args['lightbox_content'], 'title' ) ) {
							$attr['data-title'] = $this->image_data['title'];
							$attr['title']      = $this->image_data['title'];
						}

						if ( false !== strpos( $this->parent_args['lightbox_content'], 'caption' ) ) {
							$attr['data-caption'] = $this->image_data['caption'];
						}
					}
					$attr['rel']      = 'noreferrer';
					$attr['data-rel'] = 'iLightbox[gallery_image_' . $this->gallery_counter . ']';
					$attr['class']    = 'fusion-lightbox';
				}

				if ( ! empty( $this->child_args['linktarget'] ) ) {
					$attr['target'] = $this->child_args['linktarget'];
				}

				return $attr;

			}

			/**
			 * Gets the query data.
			 *
			 * @access public
			 * @since 2.0.0
			 * @return void
			 */
			public function query() {
				global $fusion_settings;

				if ( isset( $_POST['children'] ) && isset( $_POST['gallery'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$image_ids        = $_POST['children']; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
					$gallery_settings = wp_unslash( $_POST['gallery'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification

					if ( ! isset( $gallery_settings['layout'] ) || ! $gallery_settings['layout'] ) {
						$gallery_settings['layout'] = ( '' !== $fusion_settings->get( 'gallery_layout' ) ) ? strtolower( $fusion_settings->get( 'gallery_layout' ) ) : 'grid';
					}

					if ( ! isset( $gallery_settings['gallery_masonry_grid_ratio'] ) || ! $gallery_settings['gallery_masonry_grid_ratio'] ) {
						$gallery_settings['gallery_masonry_grid_ratio'] = $fusion_settings->get( 'masonry_grid_ratio' );
					}

					if ( ! isset( $gallery_settings['gallery_masonry_width_double'] ) || ! $gallery_settings['gallery_masonry_width_double'] ) {
						$gallery_settings['gallery_masonry_width_double'] = $fusion_settings->get( 'masonry_width_double' );
					}

					if ( ! isset( $gallery_settings['columns'] ) || ! $gallery_settings['columns'] ) {
						$gallery_settings['columns'] = ( '' !== $fusion_settings->get( 'gallery_columns' ) ) ? (int) $fusion_settings->get( 'gallery_columns' ) : 3;
					}

					if ( ! isset( $gallery_settings['column_spacing'] ) || ! $gallery_settings['column_spacing'] ) {
						$gallery_settings['column_spacing'] = ( '' !== $fusion_settings->get( 'gallery_column_spacing' ) ) ? strtolower( $fusion_settings->get( 'gallery_column_spacing' ) ) : '';
					}

					if ( ! isset( $gallery_settings['picture_size'] ) || ! $gallery_settings['picture_size'] ) {
						$gallery_settings['picture_size'] = ( '' !== $fusion_settings->get( 'gallery_picture_size' ) ) ? strtolower( $fusion_settings->get( 'gallery_picture_size' ) ) : '';
					}

					if ( 'fixed' === $gallery_settings['picture_size'] && 'masonry' !== $gallery_settings['layout'] ) {
						$gallery_settings['picture_size'] = 'portfolio-two';
						if ( in_array( $gallery_settings['columns'], [ 4, 5, 6 ] ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
							$gallery_settings['picture_size'] = 'blog-medium';
						}
					} else {
						$gallery_settings['picture_size'] = 'full';
					}

					$image_url            = '';
					$regular_images_found = false;
					$element_base_padding = '';
					$image_size           = 'full';

					if ( is_array( $image_ids ) && ! empty( $image_ids ) ) {
						foreach ( $image_ids as $index => $image ) {

							$image_id    = isset( $image['image_id'] ) ? $image['image_id'] : 0;
							$image_index = $image_id;
							$image_id    = explode( '|', $image_id );
							$image_id    = $image_id[0];

							$image_data = fusion_library()->get_images_obj()->get_attachment_data( $image_id, $image_size );
							$image_url  = '';
							$pic_link   = '';

							if ( $image_data['url'] ) {
								$image_url = $pic_link = $image_data['url'];
							}

							// For masonry layout, set the correct column size and classes.
							$element_orientation_class = '';
							$responsive_images_columns = $gallery_settings['columns'];

							// Get the correct image orientation class.
							$element_orientation_class          = fusion_library()->get_images_obj()->get_element_orientation_class( $image_data['id'], [], $gallery_settings['gallery_masonry_grid_ratio'], $gallery_settings['gallery_masonry_width_double'] );
							$element_base_padding               = fusion_library()->get_images_obj()->get_element_base_padding( $element_orientation_class );
							$image_data['orientation_class']    = $element_orientation_class;
							$image_data['element_base_padding'] = $element_base_padding;

							$image_data['specific_element_orientation_class'] = ( '' !== get_post_meta( $image_id, 'fusion_masonry_element_layout', true ) ) ? true : false;

							// Check if we have a landscape image, then it has to stretch over 2 cols.
							if ( 1 !== $gallery_settings['columns'] && '1' !== $gallery_settings['columns'] && false !== strpos( $element_orientation_class, 'fusion-element-landscape' ) ) {
								$responsive_images_columns = (int) $gallery_settings['columns'] / 2;
							} else {
								$regular_images_found = true;
							}

							// Responsive images.
							fusion_library()->get_images_obj()->set_grid_image_meta(
								[
									'layout'       => $gallery_settings['layout'],
									'columns'      => $responsive_images_columns,
									'gutter_width' => $gallery_settings['column_spacing'],
								]
							);

							fusion_library()->get_images_obj()->set_grid_image_meta( [] );

							$url = $image_data['url'];

							$image_html = $this->get_image_markup( $image_id, '', $gallery_settings );

							$return_data['images'][ $image_index ] = [
								'image_html'           => $image_html,
								'image_data'           => $image_data,
								'url'                  => $url,
								'element_orientation_class' => $element_orientation_class,
								'element_base_padding' => $element_base_padding,
								'pic_link'             => $pic_link,
							];
						}

						$return_data['regular_images_found'] = $regular_images_found;
					} else {
						$return_data['placeholder'] = fusion_builder_placeholder( 'gallery', 'gallery images' );
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
			 * @param string $context Can be either "parent" or "child".
			 * @return array
			 */
			public static function get_element_defaults( $context ) {

				global $fusion_settings;

				$parent = [
					'hide_on_mobile'               => fusion_builder_default_visibility( 'string' ),
					'class'                        => '',
					'id'                           => '',
					'image_ids'                    => '',
					'columns'                      => ( '' !== $fusion_settings->get( 'gallery_columns' ) ) ? (int) $fusion_settings->get( 'gallery_columns' ) : 3,
					'hover_type'                   => ( '' !== $fusion_settings->get( 'gallery_hover_type' ) ) ? strtolower( $fusion_settings->get( 'gallery_hover_type' ) ) : 'none',
					'lightbox_content'             => ( '' !== $fusion_settings->get( 'gallery_lightbox_content' ) ) ? strtolower( $fusion_settings->get( 'gallery_lightbox_content' ) ) : '',
					'lightbox'                     => $fusion_settings->get( 'status_lightbox' ),
					'column_spacing'               => ( '' !== $fusion_settings->get( 'gallery_column_spacing' ) ) ? strtolower( $fusion_settings->get( 'gallery_column_spacing' ) ) : '',
					'picture_size'                 => ( '' !== $fusion_settings->get( 'gallery_picture_size' ) ) ? strtolower( $fusion_settings->get( 'gallery_picture_size' ) ) : '',
					'layout'                       => ( '' !== $fusion_settings->get( 'gallery_layout' ) ) ? strtolower( $fusion_settings->get( 'gallery_layout' ) ) : 'grid',
					'gallery_masonry_grid_ratio'   => $fusion_settings->get( 'masonry_grid_ratio' ),
					'gallery_masonry_width_double' => $fusion_settings->get( 'masonry_width_double' ),
					'bordersize'                   => $fusion_settings->get( 'gallery_border_size' ),
					'bordercolor'                  => $fusion_settings->get( 'gallery_border_color' ),
					'border_radius'                => (int) $fusion_settings->get( 'gallery_border_radius' ) . 'px',
				];

				$child = [
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
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @param string $context Can be 'parent' or 'child'.
			 * @return array
			 */
			public static function settings_to_params( $context ) {
				$parent = [
					'gallery_columns'          => 'columns',
					'gallery_hover_type'       => 'hover_type',
					'gallery_lightbox_content' => 'lightbox_content',
					'status_lightbox'          => 'lightbox',
					'gallery_column_spacing'   => 'column_spacing',
					'gallery_picture_size'     => 'picture_size',
					'gallery_layout'           => 'layout',
					'masonry_grid_ratio'       => 'gallery_masonry_grid_ratio',
					'masonry_width_double'     => 'gallery_masonry_width_double',
					'gallery_border_size'      => 'bordersize',
					'gallery_border_color'     => 'bordercolor',
					'gallery_border_radius'    => 'border_radius',
				];

				$child = [];

				if ( 'parent' === $context ) {
					return $parent;
				} elseif ( 'child' === $context ) {
					return $child;
				}
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
					'fusion-gallery',
					FusionBuilder::$js_folder_url . '/general/fusion-gallery.js',
					FusionBuilder::$js_folder_path . '/general/fusion-gallery.js',
					[ 'jquery', 'fusion-animations', 'packery', 'isotope', 'fusion-lightbox', 'images-loaded' ],
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
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/grid.min.css' );
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/gallery.min.css' );
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1.6
			 * @return array $sections Button settings.
			 */
			public function add_options() {

				return [
					'gallery_shortcode_section' => [
						'label'  => esc_html__( 'Gallery', 'fusion-builder' ),
						'id'     => 'gallery_shortcode_section',
						'type'   => 'accordion',
						'icon'   => 'fusiona-dashboard',
						'fields' => [
							'gallery_picture_size'     => [
								'type'        => 'radio-buttonset',
								'label'       => esc_attr__( 'Picture Size', 'fusion-builder' ),
								'description' => __( 'Fixed = width and height will be fixed<br/>Auto = width and height will adjust to the image.', 'fusion-builder' ),
								'id'          => 'gallery_picture_size',
								'choices'     => [
									'fixed' => esc_attr__( 'Fixed', 'fusion-builder' ),
									'auto'  => esc_attr__( 'Auto', 'fusion-builder' ),
								],
								'default'     => 'auto',
								'transport'   => 'postMessage',
							],
							'gallery_layout'           => [
								'type'        => 'radio-buttonset',
								'label'       => esc_attr__( 'Gallery Layout', 'fusion-builder' ),
								'description' => __( 'Select the gallery layout type.', 'fusion-builder' ),
								'id'          => 'gallery_layout',
								'choices'     => [
									'grid'    => esc_attr__( 'Grid', 'fusion-builder' ),
									'masonry' => esc_attr__( 'Masonry', 'fusion-builder' ),
								],
								'default'     => 'grid',
								'transport'   => 'postMessage',
							],
							'gallery_columns'          => [
								'type'        => 'slider',
								'label'       => esc_attr__( 'Number of Columns', 'fusion-builder' ),
								'description' => __( 'Set the number of columns per row. <strong>IMPORTANT:</strong> Masonry layout does not work with 1 column.', 'fusion-builder' ),
								'id'          => 'gallery_columns',
								'default'     => '3',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '1',
									'max'  => '6',
									'step' => '1',
								],
							],
							'gallery_column_spacing'   => [
								'label'       => esc_attr__( 'Column Spacing', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls the column spacing for gallery images.', 'fusion-builder' ),
								'id'          => 'gallery_column_spacing',
								'default'     => '10',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '0',
									'max'  => '300',
									'step' => '1',
								],
							],
							'gallery_hover_type'       => [
								'type'        => 'select',
								'label'       => esc_attr__( 'Hover Type', 'fusion-builder' ),
								'description' => esc_attr__( 'Select the hover effect type.', 'fusion-builder' ),
								'id'          => 'gallery_hover_type',
								'choices'     => [
									''        => esc_attr__( 'Default', 'fusion-builder' ),
									'none'    => esc_attr__( 'None', 'fusion-builder' ),
									'zoomin'  => esc_attr__( 'Zoom In', 'fusion-builder' ),
									'zoomout' => esc_attr__( 'Zoom Out', 'fusion-builder' ),
									'liftup'  => esc_attr__( 'Lift Up', 'fusion-builder' ),
								],
								'default'     => 'none',
								'transport'   => 'postMessage',
							],
							'gallery_lightbox_content' => [
								'type'        => 'radio-buttonset',
								'label'       => esc_attr__( 'Lightbox Content', 'fusion-builder' ),
								'id'          => 'gallery_lightbox_content',
								'default'     => 'none',
								'choices'     => [
									'none'              => esc_attr__( 'None', 'fusion-builder' ),
									'titles'            => esc_attr__( 'Titles', 'fusion-builder' ),
									'captions'          => esc_attr__( 'Captions', 'fusion-builder' ),
									'title_and_caption' => esc_attr__( 'Titles & Captions', 'fusion-builder' ),
								],
								'description' => esc_attr__( 'Choose if titles and captions will display in the lightbox.', 'fusion-builder' ),
								'transport'   => 'postMessage',
							],
							'gallery_border_size'      => [
								'label'       => esc_html__( 'Gallery Image Border Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border size of the image.', 'fusion-builder' ),
								'id'          => 'gallery_border_size',
								'default'     => '0',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '0',
									'max'  => '50',
									'step' => '1',
								],
							],
							'gallery_border_color'     => [
								'label'       => esc_html__( 'Gallery Image Border Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border color of the image.', 'fusion-builder' ),
								'id'          => 'gallery_border_color',
								'default'     => '#e2e2e2',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'gallery_border_radius'    => [
								'label'       => esc_html__( 'Gallery Image Border Radius', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border radius of the gallery images.', 'fusion-builder' ),
								'id'          => 'gallery_border_radius',
								'default'     => '0px',
								'type'        => 'dimension',
								'choices'     => [ 'px', '%' ],
								'transport'   => 'postMessage',
							],
						],
					],
				];
			}
		}
	}

	new FusionSC_FusionGallery();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_gallery() {
	$fusion_settings = fusion_get_fusion_settings();
	$is_builder      = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );

	if ( $is_builder ) {
		$to_link_lightbox_status = '<span class="fusion-panel-shortcut" data-fusion-option="lightbox_status">' . esc_html__( 'Global Options', 'fusion-builder' ) . '</span>';
		$to_link_lightbox_title  = '<span class="fusion-panel-shortcut" data-fusion-option="lightbox_title">' . esc_html__( 'Global Options', 'fusion-builder' ) . '</span>';
	} else {
		$to_link_lightbox_status = '<a href="' . esc_url( $fusion_settings->get_setting_link( 'lightbox_status' ) ) . '" target="_blank">' . esc_html__( 'Global Options', 'fusion-builder' ) . '</a>';
		$to_link_lightbox_title  = '<a href="' . esc_url( $fusion_settings->get_setting_link( 'lightbox_title' ) ) . '" target="_blank">' . esc_html__( 'Global Options', 'fusion-builder' ) . '</a>';
	}

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_FusionGallery',
			[
				'name'            => esc_attr__( 'Gallery', 'fusion-builder' ),
				'shortcode'       => 'fusion_gallery',
				'icon'            => 'fusiona-dashboard',
				'preview'         => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-gallery-preview.php',
				'preview_id'      => 'fusion-builder-block-module-gallery-preview-template',
				'allow_generator' => true,
				'multi'           => 'multi_element_parent',
				'element_child'   => 'fusion_gallery_image',
				'sortable'        => false,
				'help_url'        => 'https://theme-fusion.com/documentation/fusion-builder/elements/gallery-element/',
				'params'          => [
					[
						'type'        => 'tinymce',
						'heading'     => esc_attr__( 'Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter some content for this gallery.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '[fusion_gallery_image link="" linktarget="_self" alt="" /]',
					],
					[
						'type'             => 'multiple_upload',
						'heading'          => esc_attr__( 'Bulk Image Upload', 'fusion-builder' ),
						'description'      => __( 'This option allows you to select multiple images at once and they will populate into individual items. It saves time instead of adding one image at a time.', 'fusion-builder' ),
						'param_name'       => 'multiple_upload',
						'child_params'     => [
							'image'    => 'url',
							'image_id' => 'id',
						],
						'remove_from_atts' => true,
						'dynamic_data'     => true,
						'callback'         => [
							'function' => 'fusion_gallery_images',
							'action'   => 'get_fusion_gallery',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Gallery Layout', 'fusion-builder' ),
						'description' => __( 'Select the gallery layout type.', 'fusion-builder' ),
						'param_name'  => 'layout',
						'value'       => [
							''        => esc_attr__( 'Default', 'fusion-builder' ),
							'grid'    => esc_attr__( 'Grid', 'fusion-builder' ),
							'masonry' => esc_attr__( 'Masonry', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Picture Size', 'fusion-builder' ),
						'description' => __( 'Fixed = width and height will be fixed.<br/>Auto = width and height will adjust to the image.<br/>', 'fusion-builder' ),
						'param_name'  => 'picture_size',
						'value'       => [
							''      => esc_attr__( 'Default', 'fusion-builder' ),
							'fixed' => esc_attr__( 'Fixed', 'fusion-builder' ),
							'auto'  => esc_attr__( 'Auto', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'masonry',
								'operator' => '!=',
							],
						],
						'default'     => '',
						'callback'    => [
							'function' => 'fusion_gallery_images',
							'action'   => 'get_fusion_gallery',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Number of Columns', 'fusion-builder' ),
						'description' => __( 'Set the number of columns per row. <strong>IMPORTANT:</strong> Masonry layout does not work with 1 column.', 'fusion-builder' ),
						'param_name'  => 'columns',
						'value'       => '',
						'min'         => '1',
						'max'         => '6',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'gallery_columns' ),
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Column Spacing', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the column spacing for gallery images.', 'fusion-builder' ),
						'param_name'  => 'column_spacing',
						'value'       => '',
						'min'         => '0',
						'max'         => '300',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'gallery_column_spacing' ),
						'dependency'  => [
							[
								'element'  => 'columns',
								'value'    => '1',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Masonry Image Aspect Ratio', 'fusion-builder' ),
						'description' => __( 'Set the ratio to decide when an image should become landscape (ratio being width : height) and portrait (ratio being height : width). <strong>IMPORTANT:</strong> The value of "1.0" represents a special case, which will use the auto calculated ratios like in versions prior to Avada 5.5.', 'fusion-builder' ),
						'param_name'  => 'gallery_masonry_grid_ratio',
						'value'       => '',
						'min'         => '1',
						'max'         => '4',
						'step'        => '0.1',
						'default'     => $fusion_settings->get( 'masonry_grid_ratio' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'masonry',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Masonry 2x2 Width', 'fusion-builder' ),
						'description' => __( 'This option decides when a square 1x1 image should become 2x2. This will not apply to images that highly favor landscape or portrait layouts. <strong>IMPORTANT:</strong> There is a “Masonry Image Layout” setting for every image in the WP media library that allows you to manually set how an image will appear (1x1, landscape, portrait or 2x2), regardless of the original ratio. In pixels.', 'fusion-builder' ),
						'param_name'  => 'gallery_masonry_width_double',
						'value'       => '',
						'min'         => '200',
						'max'         => '5120',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'masonry_width_double' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'masonry',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Hover Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the hover effect type.', 'fusion-builder' ),
						'param_name'  => 'hover_type',
						'value'       => [
							''        => esc_attr__( 'Default', 'fusion-builder' ),
							'none'    => esc_attr__( 'None', 'fusion-builder' ),
							'zoomin'  => esc_attr__( 'Zoom In', 'fusion-builder' ),
							'zoomout' => esc_attr__( 'Zoom Out', 'fusion-builder' ),
							'liftup'  => esc_attr__( 'Lift Up', 'fusion-builder' ),
						],
						'default'     => '',
						'preview'     => [
							'selector' => '.fusion-grid-column,.fusion-gallery-image-liftup',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Image Lightbox', 'fusion-builder' ),
						/* translators: Link containing the "Global Options" text. */
						'description' => sprintf( esc_html__( 'Show image in lightbox. Lightbox must be enabled in %s or the image will open up in the same tab by itself.', 'fusion-builder' ), $to_link_lightbox_status ),
						'param_name'  => 'lightbox',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => $fusion_settings->get( 'status_lightbox' ) ? 'yes' : 'no',
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Lightbox Content', 'fusion-builder' ),
						/* translators: Link containing the "Global Options" text. */
						'description' => sprintf( esc_html__( 'Choose if titles and captions will display in the lightbox. Titles and captions can only be displayed when this is globally enabled for the lightbox on the corresponding %s tab.', 'fusion-builder' ), $to_link_lightbox_title ),
						'param_name'  => 'lightbox_content',
						'default'     => '',
						'value'       => [
							''                  => esc_attr__( 'Default', 'fusion-builder' ),
							'none'              => esc_attr__( 'None', 'fusion-builder' ),
							'titles'            => esc_attr__( 'Titles', 'fusion-builder' ),
							'captions'          => esc_attr__( 'Captions', 'fusion-builder' ),
							'title_and_caption' => esc_attr__( 'Titles and Captions', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'lightbox',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Gallery Image Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'In pixels.', 'fusion-builder' ),
						'param_name'  => 'bordersize',
						'value'       => '',
						'min'         => '0',
						'max'         => '50',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'gallery_border_size' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Gallery Image Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the border color. ', 'fusion-builder' ),
						'param_name'  => 'bordercolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'gallery_border_color' ),
						'dependency'  => [
							[
								'element'  => 'bordersize',
								'value'    => '0',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Gallery Image Border Radius', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the gallery image border radius. In pixels (px), ex: 1px, or "round". ', 'fusion-builder' ),
						'param_name'  => 'border_radius',
						'value'       => '',
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
add_action( 'fusion_builder_before_init', 'fusion_element_gallery' );

/**
 * Map shortcode to Avada Builder.
 */
function fusion_element_fusion_gallery_image() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_FusionGallery',
			[
				'name'              => esc_attr__( 'Image', 'fusion-builder' ),
				'description'       => esc_attr__( 'Enter some content for this textblock.', 'fusion-builder' ),
				'shortcode'         => 'fusion_gallery_image',
				'hide_from_builder' => true,
				'params'            => [
					[
						'type'         => 'upload',
						'heading'      => esc_attr__( 'Image', 'fusion-builder' ),
						'description'  => esc_attr__( 'Upload an image to display.', 'fusion-builder' ),
						'param_name'   => 'image',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Image ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Image ID from Media Library.', 'fusion-builder' ),
						'param_name'  => 'image_id',
						'value'       => '',
						'hidden'      => true,
						'callback'    => [
							'function' => 'fusion_gallery_image',
							'action'   => 'get_fusion_gallery',
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
				],
				'callback'          => [
					'function' => 'fusion_gallery_image',
					'action'   => 'get_fusion_gallery',
					'ajax'     => true,
				],
			],
			'child'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_fusion_gallery_image' );
