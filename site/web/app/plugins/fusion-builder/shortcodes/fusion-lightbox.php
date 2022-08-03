<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_lightbox' ) ) {

	if ( ! class_exists( 'FusionSC_FusionLightbox' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_FusionLightbox extends Fusion_Element {

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
				add_shortcode( 'fusion_lightbox', [ $this, 'render' ] );
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
					'type'            => '',
					'full_image'      => '',
					'video_url'       => '',
					'thumbnail_image' => '',
					'alt_text'        => '',
					'description'     => '',
					'class'           => '',
					'id'              => '',
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
				$html = apply_filters( 'fusion_shortcode_content', $content, 'fusion_lightbox', $args );

				$this->on_render();

				return apply_filters( 'fusion_element_lightbox_content', $html, $args );

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
			}
		}
	}

	new FusionSC_FusionLightbox();

}

/**
 * Map shortcode to Avada Builder
 *
 * @since 1.0
 */
function fusion_element_lightbox() {
	fusion_builder_map(
		[
			'name'             => esc_attr__( 'Lightbox', 'fusion-builder' ),
			'shortcode'        => 'fusion_lightbox',
			'icon'             => 'fusiona-uniF602',
			'on_save'          => 'lightboxShortcodeFilter',
			'on_change'        => 'lightboxShortcodeFilter',
			'admin_enqueue_js' => FUSION_BUILDER_PLUGIN_URL . 'shortcodes/js/fusion-lightbox.js',
			'help_url'         => 'https://theme-fusion.com/documentation/fusion-builder/elements/lightbox-element/',
			'params'           => [
				[
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Content Type', 'fusion-builder' ),
					'description' => esc_attr__( 'Select what you want to display in the lightbox.', 'fusion-builder' ),
					'param_name'  => 'type',
					'defaults'    => '',
					'value'       => [
						''      => esc_attr__( 'Image', 'fusion-builder' ),
						'video' => esc_attr__( 'Video', 'fusion-builder' ),
					],
				],
				[
					'type'        => 'upload',
					'heading'     => esc_attr__( 'Full Image', 'fusion-builder' ),
					'description' => esc_attr__( 'Upload an image that will show up in the lightbox.', 'fusion-builder' ),
					'param_name'  => 'full_image',
					'value'       => '',
					'dependency'  => [
						[
							'element'  => 'type',
							'value'    => '',
							'operator' => '==',
						],
					],
				],
				[
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'Video URL', 'fusion-builder' ),
					'description' => esc_attr__( 'Insert the video URL that will show in the lightbox. This can be a YouTube, Vimeo or a self-hosted video URL.', 'fusion-builder' ),
					'param_name'  => 'video_url',
					'value'       => '',
					'dependency'  => [
						[
							'element'  => 'type',
							'value'    => '',
							'operator' => '!=',
						],
					],
				],
				[
					'type'        => 'upload',
					'heading'     => esc_attr__( 'Thumbnail Image', 'fusion-builder' ),
					'description' => esc_attr__( 'Clicking this image will show lightbox.', 'fusion-builder' ),
					'param_name'  => 'thumbnail_image',
					'value'       => '',
				],
				[
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'Alt Text', 'fusion-builder' ),
					'param_name'  => 'alt_text',
					'value'       => '',
					'description' => esc_attr__( 'The alt attribute provides alternative information if an image cannot be viewed.', 'fusion-builder' ),
				],
				[
					'type'         => 'textfield',
					'heading'      => esc_attr__( 'Lightbox Title', 'fusion-builder' ),
					'param_name'   => 'title',
					'value'        => '',
					'description'  => esc_attr__( 'This will show up in the lightbox as a title above the image.', 'fusion-builder' ),
					'dynamic_data' => true,
				],
				[
					'type'         => 'textfield',
					'heading'      => esc_attr__( 'Lightbox Description', 'fusion-builder' ),
					'param_name'   => 'description',
					'value'        => '',
					'description'  => esc_attr__( 'This will show up in the lightbox as a description below the image.', 'fusion-builder' ),
					'dynamic_data' => true,
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
					'param_name'  => 'id',
					'value'       => '',
					'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
				],
			],
		]
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_lightbox' );
