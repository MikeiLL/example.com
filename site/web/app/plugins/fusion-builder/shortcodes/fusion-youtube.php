<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_youtube' ) ) {

	if ( ! class_exists( 'FusionSC_Youtube' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Youtube extends Fusion_Element {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $args;

			/**
			 * The video counter.
			 *
			 * @access private
			 * @since 5.3
			 * @var int
			 */
			private $video_counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_youtube-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_youtube-shortcode-video-sc', [ $this, 'video_sc_attr' ] );

				add_shortcode( 'fusion_youtube', [ $this, 'render' ] );

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
					'api_params'      => '',
					'autoplay'        => 'false',
					'alignment'       => '',
					'center'          => 'no',
					'class'           => '',
					'css_id'          => '',
					'height'          => 360,
					'hide_on_mobile'  => fusion_builder_default_visibility( 'string' ),
					'id'              => '',
					'title_attribute' => '',
					'width'           => 600,
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

				// Make videos 16:9 by default.
				if ( isset( $args['width'] ) && '' !== $args['width'] && ( ! isset( $args['height'] ) || '' === $args['height'] ) ) {
					$args['height'] = round( $args['width'] * 9 / 16 );
				}

				if ( isset( $args['height'] ) && '' !== $args['height'] && ( ! isset( $args['width'] ) || '' === $args['width'] ) ) {
					$args['width'] = round( $args['height'] * 16 / 9 );
				}

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_youtube' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_youtube', $args );

				$defaults['height'] = FusionBuilder::validate_shortcode_attr_value( $defaults['height'], '' );
				$defaults['width']  = FusionBuilder::validate_shortcode_attr_value( $defaults['width'], '' );

				extract( $defaults );

				$this->args = $defaults;

				// Make sure only the video ID is passed to the iFrame.
				$pattern = '~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{11})[a-z0-9;:@#?&%=+\/\$_.-]*~i';
				preg_match( $pattern, $id, $matches );
				if ( isset( $matches[1] ) ) {
					$id = $matches[1];
				}

				$html  = '<div ' . FusionBuilder::attributes( 'youtube-shortcode' ) . '>';
				$html .= '<div ' . FusionBuilder::attributes( 'youtube-shortcode-video-sc' ) . '>';
				$title = $this->args['title_attribute'] ? $this->args['title_attribute'] : 'YouTube video player ' . $this->video_counter;
				$html .= '<iframe title="' . esc_attr( $title ) . '" src="https://www.youtube.com/embed/' . $id . '?wmode=transparent&autoplay=0' . $api_params . '" width="' . $width . '" height="' . $height . '" allowfullscreen allow="autoplay; fullscreen"></iframe>';
				$html .= '</div></div>';

				$this->on_render();

				$this->video_counter++;

				return apply_filters( 'fusion_element_youtube_content', $html, $args );

			}

			/**
			 * Parses the arguments.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$attr = fusion_builder_visibility_atts(
					$this->args['hide_on_mobile'],
					[
						'class' => 'fusion-video fusion-youtube',
					]
				);

				if ( 'yes' === $this->args['center'] ) {
					$attr['class'] .= ' center-video';
				} else {
					$attr['style'] = 'max-width:' . $this->args['width'] . 'px;max-height:' . $this->args['height'] . 'px;';
				}

				if ( '' !== $this->args['alignment'] ) {
					if ( fusion_element_rendering_is_flex() ) {
						// RTL adjust.
						if ( is_rtl() && 'center' !== $this->args['alignment'] ) {
							$this->args['alignment'] = 'left' === $this->args['alignment'] ? 'right' : 'left';
						}

						if ( 'left' === $this->args['alignment'] ) {
							$attr['style'] .= 'align-self:flex-start;';
						} elseif ( 'right' === $this->args['alignment'] ) {
							$attr['style'] .= 'align-self:flex-end;';
						} else {
							$attr['style'] .= 'align-self:center;';
						}
					} else {
						$attr['class'] .= ' fusion-align' . $this->args['alignment'];
					}
					$attr['style'] .= ' width:100%';
				}

				if ( 'true' === $this->args['autoplay'] || true === $this->args['autoplay'] || 'yes' === $this->args['autoplay'] ) {
					$attr['data-autoplay'] = 1;
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['css_id'] ) {
					$attr['id'] = $this->args['css_id'];
				}

				return $attr;

			}

			/**
			 * The video ShortCode arguments.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function video_sc_attr() {

				$attr = [
					'class' => 'video-shortcode',
				];

				if ( 'yes' === $this->args['center'] ) {
					$attr['style'] = 'max-width:' . $this->args['width'] . 'px;max-height:' . $this->args['height'] . 'px;';
				}

				return $attr;

			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function on_first_render() {
				Fusion_Dynamic_JS::enqueue_script( 'fusion-video' );
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/youtube.min.css' );
			}
		}
	}

	new FusionSC_Youtube();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_youtube() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Youtube',
			[
				'name'       => esc_attr__( 'Youtube', 'fusion-builder' ),
				'shortcode'  => 'fusion_youtube',
				'icon'       => 'fusiona-youtube',
				'preview'    => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-youtube-preview.php',
				'preview_id' => 'fusion-builder-block-module-youtube-preview-template',
				'help_url'   => 'https://theme-fusion.com/documentation/fusion-builder/elements/youtube-element/',
				'params'     => [
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Video ID', 'fusion-builder' ),
						'description' => esc_attr__( 'For example the Video ID for https://www.youtube.com/watch?v=569TlvRLn90 is 569TlvRLn90.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( "Select the video's alignment.", 'fusion-builder' ),
						'param_name'  => 'alignment',
						'default'     => '',
						'value'       => [
							''       => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Dimensions', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels but only enter a number, ex: 600.', 'fusion-builder' ),
						'param_name'       => 'dimensions',
						'value'            => [
							'width'  => '600',
							'height' => '350',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Autoplay Video', 'fusion-builder' ),
						'description' => esc_attr__( 'Set to yes to make video autoplaying.', 'fusion-builder' ),
						'param_name'  => 'autoplay',
						'value'       => [
							'false' => esc_attr__( 'No', 'fusion-builder' ),
							'true'  => esc_attr__( 'Yes', 'fusion-builder' ),
						],
						'default'     => 'false',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Additional API Parameter', 'fusion-builder' ),
						'description' => esc_attr__( 'Use an additional API parameter, for example, &rel=0 to only display related videos from the same channel.', 'fusion-builder' ),
						'param_name'  => 'api_params',
						'value'       => '',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Title Attribute', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the title attribute for the iframe embed of your video. Leave empty to use default value of "YouTube video player #".', 'fusion-builder' ),
						'param_name'  => 'title_attribute',
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
						'param_name'  => 'class',
						'value'       => '',
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'css_id',
						'value'       => '',
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_youtube' );
