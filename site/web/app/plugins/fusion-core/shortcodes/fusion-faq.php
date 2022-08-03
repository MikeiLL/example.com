<?php
/**
 * Avada-Builder Shortcode Element.
 *
 * @package Avada-Core
 * @since 3.1.0
 */

if ( function_exists( 'fusion_is_element_enabled' ) && fusion_is_element_enabled( 'fusion_faq' ) ) {

	if ( ! class_exists( 'FusionSC_Faq' ) && class_exists( 'Fusion_Element' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-core
		 * @since 1.0
		 */
		class FusionSC_Faq extends Fusion_Element {

			/**
			 * FAQ counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $faq_counter = 1;

			/**
			 * FAQ default values.
			 *
			 * @static
			 * @access private
			 * @since 4.0
			 * @var array
			 */
			private static $default_values;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @static
			 * @access public
			 * @since 1.0
			 * @var array
			 */
			public static $args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				self::$default_values = fusion_get_faq_default_values();
				add_shortcode( 'fusion_faq', [ $this, 'render' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_fusion_faqs', [ $this, 'ajax_query' ] );
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
				return [
					'hide_on_mobile'            => fusion_builder_default_visibility( 'string' ),
					'class'                     => '',
					'id'                        => '',
					'cats_slug'                 => '',
					'exclude_cats'              => '',
					'order'                     => 'DESC',
					'orderby'                   => 'date',
					'featured_image'            => FusionCore_Plugin::get_option_default_value( 'faq_featured_image', self::$default_values ),
					'filters'                   => FusionCore_Plugin::get_option_default_value( 'faq_filters', self::$default_values ),
					'type'                      => FusionCore_Plugin::get_option_default_value( 'faq_accordion_type', self::$default_values ),
					'boxed_mode'                => '0' !== FusionCore_Plugin::get_option_default_value( 'faq_accordion_boxed_mode', self::$default_values ) ? 'yes' : 'no',
					'border_size'               => intval( FusionCore_Plugin::get_option_default_value( 'faq_accordion_border_size', self::$default_values ) ) . 'px',
					'border_color'              => FusionCore_Plugin::get_option_default_value( 'faq_accordian_border_color', self::$default_values ),
					'background_color'          => FusionCore_Plugin::get_option_default_value( 'faq_accordian_background_color', self::$default_values ),
					'hover_color'               => FusionCore_Plugin::get_option_default_value( 'faq_accordian_hover_color', self::$default_values ),
					'divider_line'              => FusionCore_Plugin::get_option_default_value( 'faq_accordion_divider_line', self::$default_values ),
					'icon_size'                 => FusionCore_Plugin::get_option_default_value( 'faq_accordion_icon_size', self::$default_values ),
					'icon_color'                => FusionCore_Plugin::get_option_default_value( 'faq_accordian_icon_color', self::$default_values ),
					'icon_boxed_mode'           => FusionCore_Plugin::get_option_default_value( 'faq_accordion_icon_boxed', self::$default_values ),
					'icon_alignment'            => FusionCore_Plugin::get_option_default_value( 'faq_accordion_icon_align', self::$default_values ),
					'icon_box_color'            => FusionCore_Plugin::get_option_default_value( 'faq_accordian_inactive_color', self::$default_values ),
					'number_posts'              => '-1',
					'post_status'               => '',
					'title_font_size'           => FusionCore_Plugin::get_option_default_value( 'faq_accordion_title_font_size', self::$default_values ),
					'toggle_hover_accent_color' => FusionCore_Plugin::get_option_default_value( 'faq_accordian_active_color', self::$default_values ),
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
					'faq_featured_image'             => 'featured_image',
					'faq_filters'                    => 'filters',
					'faq_accordion_type'             => 'type',
					'faq_accordion_boxed_mode'       => 'boxed_mode',
					'faq_accordion_border_size'      => 'border_size',
					'faq_accordian_border_color'     => 'border_color',
					'faq_accordian_background_color' => 'background_color',
					'faq_accordian_hover_color'      => 'hover_color',
					'faq_accordion_type'             => 'type',
					'faq_accordion_divider_line'     => 'divider_line',
					'faq_accordion_icon_size'        => 'icon_size',
					'faq_accordian_icon_color'       => 'icon_color',
					'faq_accordion_icon_boxed'       => 'icon_boxed_mode',
					'faq_accordion_icon_align'       => 'icon_alignment',
					'faq_accordian_inactive_color'   => 'icon_box_color',
					'faq_accordion_title_font_size'  => 'title_font_size',
					'faq_accordian_active_color'     => 'toggle_hover_accent_color',
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
				return [
					'all_text' => apply_filters( 'fusion_faq_all_filter_name', esc_html__( 'All', 'fusion-core' ) ),
				];
			}

			/**
			 * Gets the query data.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @param array $defaults An array of defaults.
			 * @return void
			 */
			public function ajax_query( $defaults ) {
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );
				$this->query( $defaults );
			}

			/**
			 * Gets the query data.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @param array $defaults An array of defaults.
			 * @return array
			 */
			public function query( $defaults ) {
				$live_request      = false;
				$thumbnail_full    = '';
				$thumbnail         = '';
				$thumbnail_title   = '';
				$thumbnail_caption = '';

				// From Ajax Request. @codingStandardsIgnoreLine
				if ( isset( $_POST['model'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) {

					// Ignore WordPress.CSRF.NonceVerification.NoNonceVerification.
					// No nonce verification is needed here.
					// @codingStandardsIgnoreLine
					$defaults = $_POST['model']['params'];
					$return_data  = [];
					$live_request = true;
					add_filter( 'fusion_builder_live_request', '__return_true' );
				}

				if ( $live_request ) {
					$defaults['cat_slugs'] = $defaults['cats_slug'];

					// Transform $cat_slugs to array.
					if ( $defaults['cat_slugs'] ) {
						$defaults['cat_slugs'] = preg_replace( '/\s+/', '', $defaults['cat_slugs'] );
						$defaults['cat_slugs'] = explode( ',', $defaults['cat_slugs'] );
					} else {
						$defaults['cat_slugs'] = [];
					}

					// Transform $cats_to_exclude to array.
					if ( $defaults['exclude_cats'] ) {
						$defaults['exclude_cats'] = preg_replace( '/\s+/', '', $defaults['exclude_cats'] );
						$defaults['exclude_cats'] = explode( ',', $defaults['exclude_cats'] );
					} else {
						$defaults['exclude_cats'] = [];
					}
				}

				// Filter terms, e.g. useful for WPML.
				$defaults['cat_slugs']    = apply_filters( 'avada_element_term_selection', $defaults['cat_slugs'], 'avada_faq', 'faq_category' );
				$defaults['exclude_cats'] = apply_filters( 'avada_element_term_selection', $defaults['exclude_cats'], 'avada_faq', 'faq_category' );

				// Initialize the query array.
				$args = [
					'post_type'      => 'avada_faq',
					'posts_per_page' => '0' === $defaults['number_posts'] ? get_option( 'posts_per_page' ) : $defaults['number_posts'],
					'has_password'   => false,
					'orderby'        => $defaults['orderby'],
					'order'          => $defaults['order'],
				];

				// Check if the are categories that should be excluded.
				if ( ! empty( $defaults['exclude_cats'] ) ) {

					// Exclude the correct cats from tax_query.
					$args['tax_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery
						[
							'taxonomy' => 'faq_category',
							'field'    => 'slug',
							'terms'    => $defaults['exclude_cats'],
							'operator' => 'NOT IN',
						],
					];

					// Include the correct cats in tax_query.
					if ( ! empty( $defaults['cat_slugs'] ) ) {
						$args['tax_query']['relation'] = 'AND';
						$args['tax_query'][]           = [
							'taxonomy' => 'faq_category',
							'field'    => 'slug',
							'terms'    => $defaults['cat_slugs'],
							'operator' => 'IN',
						];
					}
				} else {
					// Include the cats from $cat_slugs in tax_query.
					if ( ! empty( $defaults['cat_slugs'] ) ) {
						$args['tax_query']['relation'] = 'AND';
						$args['tax_query']             = [ // phpcs:ignore WordPress.DB.SlowDBQuery
							[
								'taxonomy' => 'faq_category',
								'field'    => 'slug',
								'terms'    => $defaults['cat_slugs'],
								'operator' => 'IN',
							],
						];
					}
				}

				if ( '' === $defaults['post_status'] ) {
					if ( $live_request ) {
						$args['post_status'] = 'publish';
					} else {
						unset( $defaults['post_status'] );
					}
				} else {
					$args['post_status'] = explode( ',', $defaults['post_status'] );
				}

				$faq_items = FusionCore_Plugin::fusion_core_cached_query( $args );

				if ( ! $live_request ) {
					return $faq_items;
				}

				if ( ! $faq_items->have_posts() ) {
					$return_data['placeholder'] = fusion_builder_placeholder( 'avada_faq', 'FAQ posts' );
					echo wp_json_encode( $return_data );
					die();
				}

				$return_data['faq_terms'] = get_terms( 'faq_category' );

				if ( $faq_items->have_posts() ) {
					while ( $faq_items->have_posts() ) {
						$faq_items->the_post();

						$post_classes = '';
						$post_id      = get_the_ID();
						$post_terms   = get_the_terms( $post_id, 'faq_category' );
						if ( $post_terms ) {
							foreach ( $post_terms as $post_term ) {
								$post_classes .= urldecode( $post_term->slug ) . ' ';
							}
						}

						$featured_image_src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
						$thumbnail          = false;
						if ( $featured_image_src[0] ) {
							$thumbnail_full    = $featured_image_src[0];
							$thumbnail         = get_the_post_thumbnail( $post_id, 'blog-large' );
							$thumbnail_title   = get_post_field( 'post_title', get_post_thumbnail_id() );
							$thumbnail_caption = get_post_field( 'post_excerpt', get_post_thumbnail_id() );
						}

						ob_start();
						the_content();
						$content = ob_get_clean();

						$return_data['faq_items'][] = [
							'title'             => get_the_title(),
							'id'                => $post_id,
							'post_classes'      => $post_classes,
							'rich_snippets'     => avada_render_rich_snippets_for_pages(),
							'thumbnail'         => $thumbnail,
							'thumbnail_full'    => $thumbnail_full,
							'thumbnail_title'   => $thumbnail_title,
							'thumbnail_caption' => $thumbnail_caption,
							'content'           => $content,
						];
					}
				}
				echo wp_json_encode( $return_data );
				die();
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
				$fusion_settings = FusionCore_Plugin::get_fusion_settings();
				$defaults        = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_faq' );

				$defaults['border_size'] = FusionBuilder::validate_shortcode_attr_value( $defaults['border_size'], 'px' );
				$defaults['icon_size']   = FusionBuilder::validate_shortcode_attr_value( $defaults['icon_size'], 'px' );
				$defaults['cat_slugs']   = $defaults['cats_slug'];

				// Transform $cat_slugs to array.
				if ( $defaults['cat_slugs'] ) {
					$defaults['cat_slugs'] = preg_replace( '/\s+/', '', $defaults['cat_slugs'] );
					$defaults['cat_slugs'] = explode( ',', $defaults['cat_slugs'] );
				} else {
					$defaults['cat_slugs'] = [];
				}

				// Transform $cats_to_exclude to array.
				if ( $defaults['exclude_cats'] ) {
					$defaults['exclude_cats'] = preg_replace( '/\s+/', '', $defaults['exclude_cats'] );
					$defaults['exclude_cats'] = explode( ',', $defaults['exclude_cats'] );
				} else {
					$defaults['exclude_cats'] = [];
				}

				// @codingStandardsIgnoreLine
				extract( $defaults );

				self::$args = $defaults;

				$style_tag = '';
				$styles    = '';
				if ( 1 === self::$args['boxed_mode'] || '1' === self::$args['boxed_mode'] || 'yes' === self::$args['boxed_mode'] ) {
					if ( ! empty( self::$args['hover_color'] ) ) {
						$styles .= '#accordian-' . $this->faq_counter . ' .fusion-panel:hover,#accordian-' . $this->faq_counter . ' .fusion-panel.hover{ background-color: ' . self::$args['hover_color'] . ' }';
					}
					$styles .= ' #accordian-' . $this->faq_counter . ' .fusion-panel {';
					if ( ! empty( self::$args['border_color'] ) ) {
						$styles .= ' border-color:' . self::$args['border_color'] . ';';
					}
					if ( ! empty( self::$args['border_size'] ) ) {
						$styles .= ' border-width:' . self::$args['border_size'] . ';';
					}
					if ( ! empty( self::$args['background_color'] ) ) {
						$styles .= ' background-color:' . self::$args['background_color'] . ';';
					}
					$styles .= ' }';
				}
				if ( ! empty( self::$args['icon_size'] ) ) {
					$styles .= '.fusion-accordian #accordian-' . $this->faq_counter . ' .panel-title a .fa-fusion-box:before{ font-size: ' . self::$args['icon_size'] . ';}';
				}
				if ( ! empty( self::$args['icon_color'] ) ) {
					$styles .= '.fusion-accordian #accordian-' . $this->faq_counter . ' .panel-title a .fa-fusion-box{ color: ' . self::$args['icon_color'] . ';}';
				}
				if ( ! empty( self::$args['icon_alignment'] ) && 'right' === self::$args['icon_alignment'] ) {
					$styles .= '.fusion-accordian #accordian-' . $this->faq_counter . '.fusion-toggle-icon-right .fusion-toggle-heading{ margin-right: ' . FusionBuilder::validate_shortcode_attr_value( intval( self::$args['icon_size'] ) + 18, 'px' ) . ';}';
				}

				if ( ! empty( self::$args['title_font_size'] ) ) {
					$styles .= '.fusion-accordian #accordian-' . $this->faq_counter . ' .panel-title a{font-size:' . FusionBuilder::validate_shortcode_attr_value( self::$args['title_font_size'], 'px' ) . ';}';
				}

				if ( ( '1' === self::$args['icon_boxed_mode'] || 'yes' === self::$args['icon_boxed_mode'] ) && ! empty( self::$args['icon_box_color'] ) ) {
					$icon_box_color = Fusion_Sanitize::color( self::$args['icon_box_color'] );
					$styles        .= '.fusion-accordian #accordian-' . $this->faq_counter . ' .fa-fusion-box { background-color: ' . $icon_box_color . ';border-color: ' . $icon_box_color . ';}';
				}

				if ( ! empty( self::$args['toggle_hover_accent_color'] ) ) {
					$toggle_hover_accent_color = Fusion_Sanitize::color( self::$args['toggle_hover_accent_color'] );
					$styles                   .= '.fusion-accordian #accordian-' . $this->faq_counter . ' .panel-title a:hover,.fusion-accordian #accordian-' . $this->faq_counter . ' .panel-title a.hover { color: ' . $toggle_hover_accent_color . ';}';
					$styles                   .= '.fusion-faq-shortcode .fusion-accordian #accordian-' . $this->faq_counter . ' .fusion-toggle-boxed-mode:hover .panel-title a { color: ' . $toggle_hover_accent_color . ';}';

					if ( '1' === self::$args['icon_boxed_mode'] || 'yes' === self::$args['icon_boxed_mode'] ) {
						$styles .= '.fusion-accordian #accordian-' . $this->faq_counter . ' .panel-title .active .fa-fusion-box,';
						$styles .= '.fusion-accordian #accordian-' . $this->faq_counter . ' .panel-title a:hover .fa-fusion-box,.fusion-accordian #accordian-' . $this->faq_counter . ' .panel-title a.hover .fa-fusion-box { background-color: ' . $toggle_hover_accent_color . '!important;border-color: ' . $toggle_hover_accent_color . '!important;}';
					} else {
						$styles .= '.fusion-accordian #accordian-' . $this->faq_counter . '.fusion-toggle-icon-unboxed .panel-title a:hover .fa-fusion-box,.fusion-accordian #accordian-' . $this->faq_counter . '.fusion-toggle-icon-unboxed .panel-title a.hover .fa-fusion-box { color: ' . $toggle_hover_accent_color . '; }';
					}
				}

				if ( $styles ) {
					$style_tag = '<style type="text/css">' . $styles . '</style>';
				}

				$class = fusion_builder_visibility_atts( $hide_on_mobile, $class );
				$class = ( $class ) ? ' ' . $class : '';

				$html  = $style_tag;
				$html .= '<div class="fusion-faq-shortcode' . $class . '">';

				// Setup the filters.
				$faq_terms = get_terms( 'faq_category' );

				// Check if we should display filters.
				if ( $faq_terms && 'no' !== $filters ) {

					$html .= '<ul class="fusion-filters clearfix">';

					// Check if the "All" filter should be displayed.
					$first_filter = true;
					if ( 'yes' === $filters ) {
						$html        .= '<li class="fusion-filter fusion-filter-all fusion-active">';
						$html        .= '<a data-filter="*" href="#">' . apply_filters( 'fusion_faq_all_filter_name', esc_html__( 'All', 'fusion-core' ) ) . '</a>';
						$html        .= '</li>';
						$first_filter = false;
					}

					// Loop through the terms to setup all filters.
					foreach ( $faq_terms as $faq_term ) {
						// Only display filters of non excluded categories.
						if ( ! in_array( $faq_term->slug, $exclude_cats, true ) ) {
							// Check if current term is part of chosen terms, or if no terms at all have been chosen.
							if ( ( ! empty( $cat_slugs ) && in_array( $faq_term->slug, $cat_slugs, true ) ) || empty( $cat_slugs ) ) {
								// If the "All" filter is disabled, set the first real filter as active.
								if ( $first_filter ) {
									$html        .= '<li class="fusion-filter fusion-active">';
									$html        .= '<a data-filter=".' . urldecode( $faq_term->slug ) . '" href="#">' . $faq_term->name . '</a>';
									$html        .= '</li>';
									$first_filter = false;
								} else {
									$html .= '<li class="fusion-filter fusion-hidden">';
									$html .= '<a data-filter=".' . urldecode( $faq_term->slug ) . '" href="#">' . $faq_term->name . '</a>';
									$html .= '</li>';
								}
							}
						}
					}

					$html .= '</ul>';
				}

				// Setup the posts.
				$faq_items = $this->query( $defaults );

				if ( ! $faq_items->have_posts() ) {
					return fusion_builder_placeholder( 'avada_faq', 'FAQ posts' );
				}

				$wrapper_classes = '';

				if ( 'right' === self::$args['icon_alignment'] ) {
					$wrapper_classes .= ' fusion-toggle-icon-right';
				}

				if ( 0 === self::$args['icon_boxed_mode'] || '0' === self::$args['icon_boxed_mode'] || 'no' === self::$args['icon_boxed_mode'] ) {
					$wrapper_classes .= ' fusion-toggle-icon-unboxed';
				}

				$html .= '<div class="fusion-faqs-wrapper">';
				$html .= '<div class="accordian fusion-accordian">';
				$html .= '<div class="panel-group ' . $wrapper_classes . '" id="accordian-' . $this->faq_counter . '">';

				$this_post_id = get_the_ID();

				do_action( 'fusion_pause_live_editor_filter' );

				while ( $faq_items->have_posts() ) :
					$faq_items->the_post();

					// If used on a faq item itself, thzis is needed to prevent an infinite loop.
					if ( get_the_ID() === $this_post_id ) {
						continue;
					}

					// Get all terms of the post and it as classes; needed for filtering.
					$post_classes = '';
					$item_classes = '';
					$post_id      = get_the_ID();
					$post_terms   = get_the_terms( $post_id, 'faq_category' );
					if ( $post_terms ) {
						foreach ( $post_terms as $post_term ) {
							$post_classes .= urldecode( $post_term->slug ) . ' ';
						}
					}

					if ( 1 === self::$args['boxed_mode'] || '1' === self::$args['boxed_mode'] || 'yes' === self::$args['boxed_mode'] ) {
						$item_classes .= ' fusion-toggle-no-divider fusion-toggle-boxed-mode';
					} elseif ( 0 === self::$args['divider_line'] || '0' === self::$args['divider_line'] || 'no' === self::$args['divider_line'] ) {
						$item_classes .= ' fusion-toggle-no-divider';
					}

					$html .= '<div class="fusion-panel' . $item_classes . ' panel-default fusion-faq-post fusion-faq-post-' . $post_id . ' ' . $post_classes . '">';
					// Get the rich snippets for the post.
					$html .= avada_render_rich_snippets_for_pages();

					$html .= '<div class="panel-heading">';
					$html .= '<h4 class="panel-title toggle">';
					if ( 'toggles' === self::$args['type'] ) {
						$html .= '<a data-toggle="collapse" class="collapsed" data-target="#collapse-' . $this->faq_counter . '-' . $post_id . '" href="#collapse-' . $this->faq_counter . '-' . $post_id . '">';
					} else {
						$html .= '<a data-toggle="collapse" class="collapsed" data-parent="#accordian-' . $this->faq_counter . '" data-target="#collapse-' . $this->faq_counter . '-' . $post_id . '" href="#collapse-' . $this->faq_counter . '-' . $post_id . '">';
					}

					$html .= '<div class="fusion-toggle-icon-wrapper"><div class="fusion-toggle-icon-wrapper-main"><div class="fusion-toggle-icon-wrapper-sub"><i class="fa-fusion-box" aria-hidden="true"></i></div></div></div>';
					$html .= '<div class="fusion-toggle-heading">' . get_the_title() . '</div>';
					$html .= '</a>';
					$html .= '</h4>';
					$html .= '</div>';

					$html .= '<div id="collapse-' . $this->faq_counter . '-' . $post_id . '" class="panel-collapse collapse">';
					$html .= '<div class="panel-body toggle-content post-content">';

					// Render the featured image of the post.
					if ( ( '1' === $featured_image || 'yes' === $featured_image ) && has_post_thumbnail() ) {
						$featured_image_src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );

						if ( $featured_image_src[0] ) {
							$html .= '<div class="fusion-flexslider flexslider fusion-flexslider-loading post-slideshow fusion-post-slideshow">';
							$html .= '<ul class="slides">';
							$html .= '<li>';
							$html .= '<a href="' . $featured_image_src[0] . '" data-rel="iLightbox[gallery]" data-title="' . get_post_field( 'post_title', get_post_thumbnail_id() ) . '" data-caption="' . get_post_field( 'post_excerpt', get_post_thumbnail_id() ) . '">';
							$html .= '<span class="screen-reader-text">' . esc_html__( 'View Larger Image', 'fusion-core' ) . '</span>';
							$html .= get_the_post_thumbnail( $post_id, 'blog-large' );
							$html .= '</a>';
							$html .= '</li>';
							$html .= '</ul>';
							$html .= '</div>';
						}
					}

					$content = get_the_content();

					// Nested containers are invalid for scrolling sections.
					$content = str_replace( '[fusion_builder_container', '[fusion_builder_container is_nested="1"', $content );
					$content = apply_filters( 'the_content', $content );
					$content = str_replace( ']]>', ']]&gt;', $content );
					$html   .= $content;

					$html .= '</div>';
					$html .= '</div>';
					$html .= '</div>';

					// Add JSON-LD data.
					if ( class_exists( 'Fusion_JSON_LD' ) && $fusion_settings->get( 'disable_date_rich_snippet_pages' ) && $fusion_settings->get( 'disable_rich_snippet_faq' ) ) {

						new Fusion_JSON_LD(
							'fusion-faq',
							[
								'@context'   => 'https://schema.org',
								'@type'      => [ 'WebPage', 'FAQPage' ],
								'mainEntity' => [
									[
										'@type'          => 'Question',
										'name'           => get_the_title(),
										'acceptedAnswer' => [
											'@type' => 'Answer',
											'text'  => $content,
										],
									],
								],
							]
						);
					}

				endwhile; // Loop through faq_items.
				wp_reset_postdata();

				do_action( 'fusion_resume_live_editor_filter' );

				$html .= '</div>';
				$html .= '</div>';
				$html .= '</div>';

				$html .= '</div>';

				$this->faq_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_faq_content', $html, $args );

			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections FAQ settings.
			 */
			public function add_options() {
				if ( ! class_exists( 'Fusion_Settings' ) ) {
					return;
				}

				$fusion_settings = FusionCore_Plugin::get_fusion_settings();
				$option_name     = Fusion_Settings::get_option_name();

				return [
					'faq_shortcode_section' => [
						'label'       => esc_html__( 'FAQ', 'fusion-core' ),
						'description' => '',
						'id'          => 'faq_shortcode_section',
						'type'        => 'sub-section',
						'icon'        => 'fusiona-exclamation-sign',
						'help_url'    => 'https://theme-fusion.com/documentation/avada/elements/faq-element/',
						'fields'      => [
							'faq_featured_image'           => [
								'label'       => esc_html__( 'FAQ Featured Images', 'fusion-core' ),
								'description' => esc_html__( 'Turn on to display featured images.', 'fusion-core' ),
								'id'          => 'faq_featured_image',
								'default'     => '0',
								'type'        => 'switch',
								'option_name' => $option_name,
								'transport'   => 'postMessage',
							],
							'faq_filters'                  => [
								'label'       => esc_html__( 'FAQ Filters', 'fusion-core' ),
								'description' => esc_html__( 'Controls how the filters display for FAQs.', 'fusion-core' ),
								'id'          => 'faq_filters',
								'default'     => 'yes',
								'type'        => 'radio-buttonset',
								'choices'     => [
									'yes'             => esc_html__( 'Show', 'fusion-core' ),
									'yes_without_all' => esc_html__( 'Show without "All"', 'fusion-core' ),
									'no'              => esc_html__( 'Hide', 'fusion-core' ),
								],
								'option_name' => $option_name,
								'transport'   => 'postMessage',
							],
							'faq_accordion_type'           => [
								'label'       => esc_html__( 'FAQs in Toggles or Accordions', 'fusion-core' ),
								'description' => esc_html__( 'Toggles allow several items to be open at a time. Accordions only allow one item to be open at a time.', 'fusion-core' ),
								'id'          => 'faq_accordion_type',
								'default'     => 'accordions',
								'type'        => 'radio-buttonset',
								'choices'     => [
									'toggles'    => esc_html__( 'Toggles', 'fusion-core' ),
									'accordions' => esc_html__( 'Accordions', 'fusion-core' ),
								],
								'transport'   => 'postMessage',
							],
							'faq_accordion_boxed_mode'     => [
								'label'       => esc_html__( 'FAQ Items in Boxed Mode', 'fusion-core' ),
								'description' => esc_html__( 'Turn on to display items in boxed mode. FAQ Item divider line must be disabled for this option to work.', 'fusion-core' ),
								'id'          => 'faq_accordion_boxed_mode',
								'default'     => '0',
								'type'        => 'switch',
								'transport'   => 'postMessage',
							],
							'faq_accordion_border_size'    => [
								'label'           => esc_html__( 'FAQ Item Boxed Mode Border Width', 'fusion-core' ),
								'description'     => esc_html__( 'Controls the border size of the FAQ item.', 'fusion-core' ),
								'id'              => 'faq_accordion_border_size',
								'default'         => '1',
								'type'            => 'slider',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
								'choices'         => [
									'min'  => '0',
									'max'  => '20',
									'step' => '1',
								],
							],
							'faq_accordian_border_color'   => [
								'label'           => esc_html__( 'FAQ Item Boxed Mode Border Color', 'fusion-core' ),
								'description'     => esc_html__( 'Controls the border color of the FAQ item.', 'fusion-core' ),
								'id'              => 'faq_accordian_border_color',
								'default'         => '#e2e2e2',
								'type'            => 'color-alpha',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
							],
							'faq_accordian_background_color' => [
								'label'           => esc_html__( 'FAQ Item Boxed Mode Background Color', 'fusion-core' ),
								'description'     => esc_html__( 'Controls the background color of the FAQ item.', 'fusion-core' ),
								'id'              => 'faq_accordian_background_color',
								'default'         => '#ffffff',
								'type'            => 'color-alpha',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
							],
							'faq_accordian_hover_color'    => [
								'label'           => esc_html__( 'FAQ Item Boxed Mode Background Hover Color', 'fusion-core' ),
								'description'     => esc_html__( 'Controls the background hover color of the FAQ item.', 'fusion-core' ),
								'id'              => 'faq_accordian_hover_color',
								'default'         => '#f9f9fb',
								'type'            => 'color-alpha',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
							],
							'faq_accordion_divider_line'   => [
								'label'           => esc_html__( 'FAQ Item Divider Line', 'fusion-core' ),
								'description'     => esc_html__( 'Turn on to display a divider line between each item.', 'fusion-core' ),
								'id'              => 'faq_accordion_divider_line',
								'default'         => '1',
								'type'            => 'switch',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
							],
							'faq_accordion_title_font_size' => [
								'label'       => esc_html__( 'FAQ Title Font Size', 'fusion-core' ),
								'description' => esc_html__( 'Controls the size of the title text.', 'fusion-core' ),
								'id'          => 'faq_accordion_title_font_size',
								'default'     => $fusion_settings->get( 'h4_typography', 'font-size' ),
								'type'        => 'dimension',
								'transport'   => 'postMessage',
							],
							'faq_accordion_icon_size'      => [
								'label'       => esc_html__( 'FAQ Item Icon Size', 'fusion-core' ),
								'description' => esc_html__( 'Set the size of the icon.', 'fusion-core' ),
								'id'          => 'faq_accordion_icon_size',
								'default'     => '16',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '0',
									'max'  => '40',
									'step' => '1',
								],
								'type'        => 'slider',
							],
							'faq_accordian_icon_color'     => [
								'label'       => esc_html__( 'FAQ Item Icon Color', 'fusion-core' ),
								'description' => esc_html__( 'Controls the color of icon in FAQ box.', 'fusion-core' ),
								'id'          => 'faq_accordian_icon_color',
								'default'     => '#ffffff',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'faq_accordion_icon_boxed'     => [
								'label'       => esc_html__( 'FAQ Item Icon Boxed Mode', 'fusion-core' ),
								'description' => esc_html__( 'Turn on to display icon in boxed mode.', 'fusion-core' ),
								'id'          => 'faq_accordion_icon_boxed',
								'default'     => '1',
								'type'        => 'switch',
								'transport'   => 'postMessage',
							],
							'faq_accordian_inactive_color' => [
								'label'           => esc_html__( 'FAQ Item Icon Inactive Box Color', 'fusion-core' ),
								'description'     => esc_html__( 'Controls the color of the inactive FAQ box.', 'fusion-core' ),
								'id'              => 'faq_accordian_inactive_color',
								'default'         => '#212934',
								'type'            => 'color-alpha',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
							],
							'faq_accordion_icon_align'     => [
								'label'       => esc_html__( 'FAQ Item Icon Alignment', 'fusion-core' ),
								'description' => esc_html__( 'Controls the alignment of the icon.', 'fusion-core' ),
								'id'          => 'faq_accordion_icon_align',
								'default'     => 'left',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'left'  => esc_html__( 'Left', 'fusion-core' ),
									'right' => esc_html__( 'Right', 'fusion-core' ),
								],
							],
							'faq_accordian_active_color'   => [
								'label'       => esc_html__( 'FAQ Item Icon Toggle Hover Accent Color', 'fusion-core' ),
								'description' => esc_html__( 'Controls the accent color on hover for icon box and title.', 'fusion-core' ),
								'id'          => 'faq_accordian_active_color',
								'default'     => '#65bc7b',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
						],
					],
				];
			}

			/**
			 * Builds the dynamic styling.
			 *
			 * @access public
			 * @since 3.1
			 * @return array
			 */
			public function add_styling() {
				global $content_media_query, $dynamic_css_helpers;

				$faq_accordian_active_color = FusionCore_Plugin::get_option_default_value( 'faq_accordian_active_color', self::$default_values, 'color' );

				$css['global']['.fusion-faq-shortcode .fusion-accordian .panel-title a .fa-fusion-box']['background-color']       = FusionCore_Plugin::get_option_default_value( 'faq_accordian_inactive_color', self::$default_values, 'color' );
				$css['global']['.fusion-faq-shortcode .fusion-accordian .panel-title .active .fa-fusion-box']['background-color'] = $faq_accordian_active_color;
				$css['global']['.fusion-faq-shortcode .fusion-accordian .panel-title a:hover .fa-fusion-box']['background-color'] = $faq_accordian_active_color . ' !important';

				$elements = [
					'.fusion-faq-shortcode .fusion-accordian .panel-title a:hover',
					'.fusion-faq-shortcode .fusion-accordian .fusion-toggle-boxed-mode:hover .panel-title a',
				];

				if ( '1' !== FusionCore_Plugin::get_option_default_value( 'faq_accordion_icon_boxed', self::$default_values ) && 'yes' !== FusionCore_Plugin::get_option_default_value( 'faq_accordion_icon_boxed', self::$default_values ) ) {
					$elements[] = '.fusion-faq-shortcode .fusion-accordian .fusion-toggle-icon-unboxed .panel-title a:hover .fa-fusion-box';
				}

				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = $faq_accordian_active_color;

				$css['global']['.fusion-filters .fusion-filter.fusion-active a']['color']        = 'var(--primary_color)';
				$css['global']['.fusion-filters .fusion-filter.fusion-active a']['border-color'] = 'var(--primary_color)';

				$css[ $content_media_query ]['.fusion-filters']['border-bottom'] = '0';
				$css[ $content_media_query ]['.fusion-filter']['float']          = 'none';
				$css[ $content_media_query ]['.fusion-filter']['margin']         = '0';
				$css[ $content_media_query ]['.fusion-filter']['border-bottom']  = '1px solid ' . FusionCore_Plugin::get_option_default_value( 'sep_color', self::$default_values, 'color' );

				return $css;
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 5.2
			 * @return void
			 */
			public function on_first_render() {

				Fusion_Dynamic_JS::enqueue_script(
					'avada-faqs',
					FusionCore_Plugin::$js_folder_url . '/avada-faqs.js',
					FusionCore_Plugin::$js_folder_path . '/avada-faqs.js',
					[ 'jquery', 'isotope', 'jquery-infinite-scroll', 'fusion-toggles' ],
					FUSION_CORE_VERSION,
					true
				);
			}
		}
	}

	new FusionSC_Faq();
}

/**
 * Returns the default option values.
 *
 * @since 4.0
 * @return array
 */
function fusion_get_faq_default_values() {
	return [
		'faq_featured_image'             => '1',
		'faq_filters'                    => 'yes',
		'faq_accordion_type'             => 'accordions',
		'faq_accordion_boxed_mode'       => 'no',
		'faq_accordion_border_size'      => '1px',
		'faq_accordian_border_color'     => '#cccccc',
		'faq_accordian_background_color' => '#ffffff',
		'faq_accordian_hover_color'      => '#f9f9f9',
		'faq_accordion_divider_line'     => '1',
		'faq_accordion_icon_size'        => '13px',
		'faq_accordian_icon_color'       => '#ffffff',
		'faq_accordion_icon_boxed'       => 'no',
		'faq_accordion_icon_align'       => 'left',
		'faq_accordian_inactive_color'   => '#333333',
		'faq_accordion_title_font_size'  => '#333333',
		'faq_accordian_active_color'     => '#65bc7b',
	];
}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_faq() {
	$fusion_settings = FusionCore_Plugin::get_fusion_settings();
	if ( ! function_exists( 'fusion_builder_map' ) || ! function_exists( 'fusion_builder_frontend_data' ) ) {
		return;
	}

	$builder_status = function_exists( 'is_fusion_editor' ) && is_fusion_editor();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Faq',
			[
				'name'       => esc_html__( 'FAQ', 'fusion-core' ),
				'shortcode'  => 'fusion_faq',
				'icon'       => 'fusiona-exclamation-sign',
				'preview'    => FUSION_CORE_PATH . '/shortcodes/previews/fusion-faq-preview.php',
				'front-end'  => FUSION_CORE_PATH . '/shortcodes/previews/front-end/fusion-faq.php',
				'preview_id' => 'fusion-builder-block-module-faq-preview-template',
				'params'     => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Display Filters', 'fusion-core' ),
						'description' => esc_html__( 'Display the FAQ filters.', 'fusion-core' ),
						'param_name'  => 'filters',
						'value'       => [
							''                => esc_html__( 'Default', 'fusion-core' ),
							'yes'             => esc_html__( 'Show', 'fusion-core' ),
							'yes-without-all' => esc_html__( 'Show without "All"', 'fusion-core' ),
							'no'              => esc_html__( 'Hide', 'fusion-core' ),
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Maximum FAQ Element', 'fusion-core' ),
						'description' => esc_attr__( 'Select the maximum number of FAQ elements. Set to -1 to display all. Set to 0 to use number of posts from Settings > Reading.', 'fusion-core' ),
						'param_name'  => 'number_posts',
						'value'       => '-1',
						'min'         => '-1',
						'max'         => '25',
						'step'        => '1',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_faqs',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'multiple_select',
						'heading'     => esc_attr__( 'Post Status', 'fusion-core' ),
						'placeholder' => esc_attr__( 'Post Status', 'fusion-core' ),
						'description' => esc_attr__( 'Select the status(es) of the posts that should be included or leave blank for published only posts.', 'fusion-core' ),
						'param_name'  => 'post_status',
						'value'       => [
							'publish' => esc_attr__( 'Published', 'fusion-core' ),
							'draft'   => esc_attr__( 'Drafted', 'fusion-core' ),
							'future'  => esc_attr__( 'Scheduled', 'fusion-core' ),
							'private' => esc_attr__( 'Private', 'fusion-core' ),
							'pending' => esc_attr__( 'Pending', 'fusion-core' ),
						],
						'default'     => '',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_faqs',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'multiple_select',
						'heading'     => esc_html__( 'Categories', 'fusion-core' ),
						'placeholder' => esc_html__( 'Categories', 'fusion-core' ),
						'description' => esc_html__( 'Select categories to include or leave blank for all.', 'fusion-core' ),
						'param_name'  => 'cats_slug',
						'value'       => $builder_status ? fusion_builder_shortcodes_categories( 'faq_category' ) : [],
						'default'     => '',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_faqs',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'multiple_select',
						'heading'     => esc_html__( 'Exclude Categories', 'fusion-core' ),
						'placeholder' => esc_html__( 'Categories', 'fusion-core' ),
						'description' => esc_html__( 'Select categories to exclude.', 'fusion-core' ),
						'param_name'  => 'exclude_cats',
						'value'       => $builder_status ? fusion_builder_shortcodes_categories( 'faq_category' ) : [],
						'default'     => '',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_faqs',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_html__( 'Order By', 'fusion-core' ),
						'description' => esc_html__( 'Defines how FAQs should be ordered.', 'fusion-core' ),
						'param_name'  => 'orderby',
						'default'     => 'date',
						'value'       => [
							'date'       => esc_html__( 'Date', 'fusion-core' ),
							'title'      => esc_html__( 'Post Title', 'fusion-core' ),
							'menu_order' => esc_html__( 'FAQ Order', 'fusion-core' ),
							'name'       => esc_html__( 'Post Slug', 'fusion-core' ),
							'author'     => esc_html__( 'Author', 'fusion-core' ),
							'modified'   => esc_html__( 'Last Modified', 'fusion-core' ),
							'rand'       => esc_html__( 'Random', 'fusion-core' ),
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_faqs',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Order', 'fusion-core' ),
						'description' => esc_html__( 'Defines the sorting order of FAQs.', 'fusion-core' ),
						'param_name'  => 'order',
						'default'     => 'DESC',
						'value'       => [
							'DESC' => esc_html__( 'Descending', 'fusion-core' ),
							'ASC'  => esc_html__( 'Ascending', 'fusion-core' ),
						],
						'dependency'  => [
							[
								'element'  => 'orderby',
								'value'    => 'rand',
								'operator' => '!=',
							],
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_faqs',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Display Featured Images', 'fusion-core' ),
						'description' => esc_html__( 'Display the FAQ featured images.', 'fusion-core' ),
						'param_name'  => 'featured_image',
						'value'       => [
							''    => esc_html__( 'Default', 'fusion-core' ),
							'yes' => esc_html__( 'Yes', 'fusion-core' ),
							'no'  => esc_html__( 'No', 'fusion-core' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Toggles or Accordions', 'fusion-core' ),
						'description' => esc_html__( 'Toggles allow several items to be open at a time. Accordions only allow one item to be open at a time.', 'fusion-core' ),
						'param_name'  => 'type',
						'value'       => [
							''           => esc_html__( 'Default', 'fusion-core' ),
							'toggles'    => esc_html__( 'Toggles', 'fusion-core' ),
							'accordions' => esc_html__( 'Accordions', 'fusion-core' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Boxed Mode', 'fusion-core' ),
						'description' => esc_html__( 'Choose to display FAQs items in boxed mode.', 'fusion-core' ),
						'param_name'  => 'boxed_mode',
						'value'       => [
							''    => esc_html__( 'Default', 'fusion-core' ),
							'yes' => esc_html__( 'Yes', 'fusion-core' ),
							'no'  => esc_html__( 'No', 'fusion-core' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'range',
						'heading'     => esc_html__( 'Boxed Mode Border Width', 'fusion-core' ),
						'description' => esc_html__( 'Set the border width for FAQ item. In pixels.', 'fusion-core' ),
						'param_name'  => 'border_size',
						'value'       => '',
						'default'     => $fusion_settings->get( 'faq_accordion_border_size' ),
						'min'         => '0',
						'max'         => '20',
						'step'        => '1',
						'dependency'  => [
							[
								'element'  => 'boxed_mode',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Boxed Mode Border Color', 'fusion-core' ),
						'description' => esc_html__( 'Set the border color for FAQ item.', 'fusion-core' ),
						'param_name'  => 'border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'faq_accordian_border_color' ),
						'dependency'  => [
							[
								'element'  => 'boxed_mode',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'border_size',
								'value'    => '0',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Boxed Mode Background Color', 'fusion-core' ),
						'description' => esc_html__( 'Set the background color for FAQ item.', 'fusion-core' ),
						'param_name'  => 'background_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'accordian_background_color' ),
						'dependency'  => [
							[
								'element'  => 'boxed_mode',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Boxed Mode Background Hover Color', 'fusion-core' ),
						'description' => esc_html__( 'Set the background hover color for FAQ item.', 'fusion-core' ),
						'param_name'  => 'hover_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'faq_accordian_hover_color' ),
						'dependency'  => [
							[
								'element'  => 'boxed_mode',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
						'preview'     => [
							'selector' => '.fusion-panel, .panel-title a',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Divider Line', 'fusion-core' ),
						'description' => esc_html__( 'Choose to display a divider line between each item.', 'fusion-core' ),
						'param_name'  => 'divider_line',
						'value'       => [
							''    => esc_html__( 'Default', 'fusion-core' ),
							'yes' => esc_html__( 'Yes', 'fusion-core' ),
							'no'  => esc_html__( 'No', 'fusion-core' ),
						],
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'boxed_mode',
								'value'    => 'yes',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Title Size', 'fusion-core' ),
						'description' => esc_html__( 'Controls the size of the title. Enter value including any valid CSS unit, ex: 13px.', 'fusion-core' ),
						'param_name'  => 'title_font_size',
						'value'       => '',
					],
					[
						'heading'     => esc_html__( 'Icon Size', 'fusion-core' ),
						'description' => esc_html__( 'Set the size of the icon. In pixels (px), ex: 13px.', 'fusion-core' ),
						'param_name'  => 'icon_size',
						'default'     => $fusion_settings->get( 'faq_accordion_icon_size' ),
						'min'         => '1',
						'max'         => '40',
						'step'        => '1',
						'type'        => 'range',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Icon Color', 'fusion-core' ),
						'description' => esc_html__( 'Set the color of icon in toggle box.', 'fusion-core' ),
						'param_name'  => 'icon_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'faq_accordian_icon_color' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Icon Boxed Mode', 'fusion-core' ),
						'description' => esc_html__( 'Choose to display icon in boxed mode.', 'fusion-core' ),
						'param_name'  => 'icon_boxed_mode',
						'value'       => [
							''    => esc_html__( 'Default', 'fusion-core' ),
							'yes' => esc_html__( 'Yes', 'fusion-core' ),
							'no'  => esc_html__( 'No', 'fusion-core' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Icon Inactive Box Color', 'fusion-core' ),
						'description' => esc_html__( 'Controls the color of the inactive toggle box.', 'fusion-core' ),
						'param_name'  => 'icon_box_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'faq_accordian_inactive_color' ),
						'dependency'  => [
							[
								'element'  => 'icon_boxed_mode',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Icon Alignment', 'fusion-core' ),
						'description' => esc_html__( 'Controls the alignment of FAQ icon.', 'fusion-core' ),
						'param_name'  => 'icon_alignment',
						'value'       => [
							''      => esc_html__( 'Default', 'fusion-core' ),
							'left'  => esc_html__( 'Left', 'fusion-core' ),
							'right' => esc_html__( 'Right', 'fusion-core' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'FAQ Toggle Hover Accent Color', 'fusion-core' ),
						'description' => esc_html__( 'Controls the accent color on hover for icon box and title.', 'fusion-core' ),
						'param_name'  => 'toggle_hover_accent_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'faq_accordian_active_color' ),
						'preview'     => [
							'selector' => '.fusion-panel, .panel-title a',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_html__( 'Element Visibility', 'fusion-core' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_html__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-core' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_html__( 'CSS Class', 'fusion-core' ),
						'description' => esc_html__( 'Add a class to the wrapping HTML element.', 'fusion-core' ),
						'param_name'  => 'class',
						'value'       => '',
						'group'       => esc_html__( 'General', 'fusion-core' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_html__( 'CSS ID', 'fusion-core' ),
						'description' => esc_html__( 'Add an ID to the wrapping HTML element.', 'fusion-core' ),
						'param_name'  => 'id',
						'value'       => '',
						'group'       => esc_html__( 'General', 'fusion-core' ),
					],
				],
				'callback'   => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_faqs',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'wp_loaded', 'fusion_element_faq' );
