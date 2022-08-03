<?php
/**
 * Avada Options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      4.0.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Menu
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_typography( $sections ) {

	if ( ! function_exists( 'avada_get_h6_typography_elements' ) ) {
		require_once Avada::$template_dir_path . '/includes/dynamic-css-helpers.php';
	}

	// An array of all the elements that will be targeted from the body typography settings.
	$body_typography_elements = wp_parse_args(
		apply_filters( 'avada_body_typography_elements', avada_get_body_typography_elements() ),
		[
			'family'      => [],
			'size'        => [],
			'color'       => [],
			'line-height' => [],
		]
	);
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-body-typography-elements-font-family', Fusion_Dynamic_CSS_Helpers::get_elements_string( $body_typography_elements['family'] ) );
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-body-typography-elements-font-size', Fusion_Dynamic_CSS_Helpers::get_elements_string( $body_typography_elements['size'] ) );
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-body-typography-elements-color', Fusion_Dynamic_CSS_Helpers::get_elements_string( $body_typography_elements['color'] ) );
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-body-typography-elements-line-height', Fusion_Dynamic_CSS_Helpers::get_elements_string( $body_typography_elements['line-height'] ) );

	// An array of all the elements that will be targeter from the h1_typography settings.
	$h1_typography_elements = wp_parse_args(
		apply_filters( 'avada_h1_typography_elements', avada_get_h1_typography_elements() ),
		[
			'family' => [],
			'size'   => [],
			'color'  => [],
		]
	);
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-h1-typography-elements-font-family', Fusion_Dynamic_CSS_Helpers::get_elements_string( $h1_typography_elements['family'] ) );
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-h1-typography-elements-font-size', Fusion_Dynamic_CSS_Helpers::get_elements_string( $h1_typography_elements['size'] ) );
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-h1-typography-elements-color', Fusion_Dynamic_CSS_Helpers::get_elements_string( $h1_typography_elements['color'] ) );

	// An array of all the elements that will be targeter from the h2_typography settings.
	$h2_typography_elements = wp_parse_args(
		apply_filters( 'avada_h2_typography_elements', avada_get_h2_typography_elements() ),
		[
			'family' => [],
			'size'   => [],
			'color'  => [],
		]
	);
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-h2-typography-elements-font-family', Fusion_Dynamic_CSS_Helpers::get_elements_string( $h2_typography_elements['family'] ) );
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-h2-typography-elements-font-size', Fusion_Dynamic_CSS_Helpers::get_elements_string( $h2_typography_elements['size'] ) );
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-h2-typography-elements-color', Fusion_Dynamic_CSS_Helpers::get_elements_string( $h2_typography_elements['color'] ) );

	// An array of all the elements that will be targeter from the h3_typography settings.
	$h3_typography_elements = wp_parse_args(
		apply_filters( 'avada_h3_typography_elements', avada_get_h3_typography_elements() ),
		[
			'family' => [],
			'size'   => [],
			'color'  => [],
		]
	);
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-h3-typography-elements-font-family', Fusion_Dynamic_CSS_Helpers::get_elements_string( $h3_typography_elements['family'] ) );
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-h3-typography-elements-font-size', Fusion_Dynamic_CSS_Helpers::get_elements_string( $h3_typography_elements['size'] ) );
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-h3-typography-elements-color', Fusion_Dynamic_CSS_Helpers::get_elements_string( $h3_typography_elements['color'] ) );

	// An array of all the elements that will be targeter from the h4_typography settings.
	$h4_typography_elements = wp_parse_args(
		apply_filters( 'avada_h4_typography_elements', avada_get_h4_typography_elements() ),
		[
			'family'      => [],
			'size'        => [],
			'color'       => [],
			'line-height' => [],
		]
	);
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-h4-typography-elements-font-family', Fusion_Dynamic_CSS_Helpers::get_elements_string( $h4_typography_elements['family'] ) );
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-h4-typography-elements-font-size', Fusion_Dynamic_CSS_Helpers::get_elements_string( $h4_typography_elements['size'] ) );
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-h4-typography-elements-color', Fusion_Dynamic_CSS_Helpers::get_elements_string( $h4_typography_elements['color'] ) );
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-h4-typography-elements-line-height', Fusion_Dynamic_CSS_Helpers::get_elements_string( $h4_typography_elements['line-height'] ) );

	// An array of all the elements that will be targeter from the h5_typography settings.
	$h5_typography_elements = wp_parse_args(
		apply_filters( 'avada_h5_typography_elements', avada_get_h5_typography_elements() ),
		[
			'family' => [],
			'size'   => [],
			'color'  => [],
		]
	);
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-h5-typography-elements-font-family', Fusion_Dynamic_CSS_Helpers::get_elements_string( $h5_typography_elements['family'] ) );
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-h5-typography-elements-font-size', Fusion_Dynamic_CSS_Helpers::get_elements_string( $h5_typography_elements['size'] ) );
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-h5-typography-elements-color', Fusion_Dynamic_CSS_Helpers::get_elements_string( $h5_typography_elements['color'] ) );

	// An array of all the elements that will be targeter from the h6_typography settings.
	$h6_typography_elements = wp_parse_args(
		apply_filters( 'avada_h6_typography_elements', avada_get_h6_typography_elements() ),
		[
			'family' => [],
			'size'   => [],
			'color'  => [],
		]
	);
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-h6-typography-elements-font-family', Fusion_Dynamic_CSS_Helpers::get_elements_string( $h6_typography_elements['family'] ) );
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-h6-typography-elements-font-size', Fusion_Dynamic_CSS_Helpers::get_elements_string( $h6_typography_elements['size'] ) );
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-h6-typography-elements-color', Fusion_Dynamic_CSS_Helpers::get_elements_string( $h6_typography_elements['color'] ) );

	// An array of all the elements that will be targeter from the post title typography settings.
	$post_title_typography_elements = wp_parse_args(
		apply_filters( 'avada_post_title_typography_elements', avada_get_post_title_typography_elements() ),
		[
			'family' => [],
			'size'   => [],
			'color'  => [],
		]
	);
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-post-title-elements-font-family', Fusion_Dynamic_CSS_Helpers::get_elements_string( $post_title_typography_elements['family'] ) );
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-post-title-elements-font-size', Fusion_Dynamic_CSS_Helpers::get_elements_string( $post_title_typography_elements['size'] ) );
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-post-title-elements-color', Fusion_Dynamic_CSS_Helpers::get_elements_string( $post_title_typography_elements['color'] ) );

	// An array of all the elements that will be targeter from the post title typography settings.
	$post_title_extras_typography_elements = wp_parse_args(
		apply_filters( 'avada_post_title_extras_typography_elements', avada_get_post_title_extras_typography_elements() ),
		[
			'family' => [],
			'size'   => [],
			'color'  => [],
		]
	);
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-post-title-extras-elements-font-family', Fusion_Dynamic_CSS_Helpers::get_elements_string( $post_title_extras_typography_elements['family'] ) );
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-post-title-extras-elements-font-size', Fusion_Dynamic_CSS_Helpers::get_elements_string( $post_title_extras_typography_elements['size'] ) );
	Fusion_Dynamic_CSS::add_replace_pattern( '.avada-post-title-extras-elements-color', Fusion_Dynamic_CSS_Helpers::get_elements_string( $post_title_extras_typography_elements['color'] ) );

	$sections['typography'] = [
		'label'    => esc_html__( 'Typography', 'Avada' ),
		'id'       => 'heading_typography',
		'is_panel' => true,
		'priority' => 12,
		'icon'     => 'el-icon-fontsize',
		'alt_icon' => 'fusiona-font-solid',
		'fields'   => [
			'body_typography'                   => [
				'label'  => esc_html__( 'Body Typography', 'Avada' ),
				'id'     => 'body_typography',
				'type'   => 'sub-section',
				'fields' => [
					'body_typography_important_note_info' => [
						'label'       => '',
						'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> This tab contains general typography options. Additional typography options for specific areas can be found within other tabs. Example: For menu typography options go to the menu tab.', 'Avada' ) . '</div>',
						'id'          => 'body_typography_important_note_info',
						'type'        => 'custom',
					],
					'body_typography'                     => [
						'id'          => 'body_typography',
						'label'       => esc_html__( 'Body Typography', 'Avada' ),
						'description' => esc_html__( 'These settings control the typography for all body text.', 'Avada' ),
						'type'        => 'typography',
						'choices'     => [
							'font-family'    => true,
							'font-size'      => true,
							'font-weight'    => true,
							'line-height'    => true,
							'letter-spacing' => true,
							'color'          => true,
						],
						'default'     => [
							'font-family'    => 'Open Sans',
							'font-size'      => '16px',
							'font-weight'    => '400',
							'line-height'    => '1.8',
							'letter-spacing' => '0',
							'color'          => '#4a4e57',
						],
						'css_vars'    => [
							[
								'name'     => '--body_typography-font-family',
								'choice'   => 'font-family',
								'callback' => [ 'combined_font_family', 'body_typography' ],
							],
							[
								'name'   => '--body_typography-font-size',
								'choice' => 'font-size',
							],
							[
								'name'     => '--base-font-size',
								'choice'   => 'font-size',
								'callback' => [ 'convert_font_size_to_px', '' ],
							],
							[
								'name'     => '--body_typography-font-weight',
								'choice'   => 'font-weight',
								'callback' => [ 'font_weight_no_regular', '' ],
							],
							[
								'name'   => '--body_typography-font-style',
								'choice' => 'font-style',
							],
							[
								'name'   => '--body_typography-line-height',
								'choice' => 'line-height',
							],
							[
								'name'     => '--body_typography-letter-spacing',
								'choice'   => 'letter-spacing',
								'callback' => [ 'maybe_append_px', '' ],
							],
							[
								'name'     => '--body_typography-color',
								'choice'   => 'color',
								'callback' => [ 'sanitize_color', '' ],
							],
						],
					],
					'link_color'                          => [
						'label'       => esc_html__( 'Link Color', 'Avada' ),
						'description' => esc_html__( 'Controls the color of all text links.', 'Avada' ),
						'id'          => 'link_color',
						'default'     => '#212934',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--link_color',
								'callback' => [ 'sanitize_color' ],
							],
							[
								'name'     => '--link_color-8a',
								'callback' => [ 'color_alpha_set', '0.8' ],
							],
						],
					],
				],
			],
			'headers_typography_section'        => [
				'label'  => esc_html__( 'Heading Typography', 'Avada' ),
				'id'     => 'headers_typography_section',
				'type'   => 'sub-section',
				'fields' => [
					'headers_typography_important_note_info' => [
						'label'       => '',
						'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> This tab contains heading typography options. Additional typography options for specific areas can be found within other tabs. Example: For menu typography options go to the menu tab.', 'Avada' ) . '</div>',
						'id'          => 'headers_typography_important_note_info',
						'type'        => 'custom',
					],
					'h1_typography'                 => [
						'id'          => 'h1_typography',
						'label'       => esc_html__( 'H1 Headings Typography', 'Avada' ),
						'description' => esc_html__( 'These settings control the typography for all H1 headings.', 'Avada' ),
						'type'        => 'typography',
						'choices'     => [
							'font-family'    => true,
							'font-size'      => true,
							'font-weight'    => true,
							'line-height'    => true,
							'letter-spacing' => true,
							'color'          => true,
							'margin-top'     => true,
							'margin-bottom'  => true,
						],
						'default'     => [
							'font-family'    => 'Zilla Slab',
							'font-size'      => '54px',
							'font-weight'    => '400',
							'line-height'    => '1.16',
							'letter-spacing' => '0',
							'color'          => '#212934',
							'margin-top'     => '0.67em',
							'margin-bottom'  => '0.67em',
						],
						'css_vars'    => [
							[
								'name'     => '--h1_typography-font-family',
								'choice'   => 'font-family',
								'callback' => [ 'combined_font_family', 'h1_typography' ],
							],
							[
								'name'     => '--h1_typography-font-weight',
								'choice'   => 'font-weight',
								'callback' => [ 'font_weight_no_regular', '' ],
							],
							[
								'name'   => '--h1_typography-line-height',
								'choice' => 'line-height',
							],
							[
								'name'     => '--h1_typography-letter-spacing',
								'choice'   => 'letter-spacing',
								'callback' => [ 'maybe_append_px', '' ],
							],
							[
								'name'   => '--h1_typography-font-style',
								'choice' => 'font-style',
							],
							[
								'name'   => '--h1_typography-font-size',
								'choice' => 'font-size',
							],
							[
								'name'     => '--h1_typography-color',
								'choice'   => 'color',
								'callback' => [ 'sanitize_color', '' ],
							],
							[
								'name'   => '--h1_typography-margin-top',
								'choice' => 'margin-top',
							],
							[
								'name'   => '--h1_typography-margin-bottom',
								'choice' => 'margin-bottom',
							],
						],
					],
					'h2_typography'                 => [
						'id'          => 'h2_typography',
						'label'       => esc_html__( 'H2 Headings Typography', 'Avada' ),
						'description' => esc_html__( 'These settings control the typography for all H2 headings.', 'Avada' ),
						'type'        => 'typography',
						'choices'     => [
							'font-family'    => true,
							'font-size'      => true,
							'font-weight'    => true,
							'line-height'    => true,
							'letter-spacing' => true,
							'color'          => true,
							'margin-top'     => true,
							'margin-bottom'  => true,
						],
						'default'     => [
							'font-family'    => 'Zilla Slab',
							'font-size'      => '40px',
							'font-weight'    => '400',
							'line-height'    => '1.2',
							'letter-spacing' => '0',
							'color'          => '#212934',
							'margin-top'     => '0em',
							'margin-bottom'  => '1.1em',
						],
						'css_vars'    => [
							[
								'name'     => '--h2_typography-font-family',
								'choice'   => 'font-family',
								'callback' => [ 'combined_font_family', 'h2_typography' ],
							],
							[
								'name'     => '--h2_typography-font-weight',
								'choice'   => 'font-weight',
								'callback' => [ 'font_weight_no_regular', '' ],
							],
							[
								'name'   => '--h2_typography-line-height',
								'choice' => 'line-height',
							],
							[
								'name'     => '--h2_typography-letter-spacing',
								'choice'   => 'letter-spacing',
								'callback' => [ 'maybe_append_px', '' ],
							],
							[
								'name'   => '--h2_typography-font-style',
								'choice' => 'font-style',
							],
							[
								'name'   => '--h2_typography-font-size',
								'choice' => 'font-size',
							],
							[
								'name'     => '--h2_typography-color',
								'choice'   => 'color',
								'callback' => [ 'sanitize_color', '' ],
							],
							[
								'name'   => '--h2_typography-margin-top',
								'choice' => 'margin-top',
							],
							[
								'name'   => '--h2_typography-margin-bottom',
								'choice' => 'margin-bottom',
							],
						],
					],
					'h3_typography'                 => [
						'id'          => 'h3_typography',
						'label'       => esc_html__( 'H3 Headings Typography', 'Avada' ),
						'description' => esc_html__( 'These settings control the typography for all H3 headings.', 'Avada' ),
						'type'        => 'typography',
						'choices'     => [
							'font-family'    => true,
							'font-size'      => true,
							'font-weight'    => true,
							'line-height'    => true,
							'letter-spacing' => true,
							'color'          => true,
							'margin-top'     => true,
							'margin-bottom'  => true,
						],
						'default'     => [
							'font-family'    => 'Zilla Slab',
							'font-size'      => '32px',
							'font-weight'    => '400',
							'line-height'    => '1.3',
							'letter-spacing' => '0',
							'color'          => '#212934',
							'margin-top'     => '1em',
							'margin-bottom'  => '1em',
						],
						'css_vars'    => [
							[
								'name'     => '--h3_typography-font-family',
								'choice'   => 'font-family',
								'callback' => [ 'combined_font_family', 'h3_typography' ],
							],
							[
								'name'     => '--h3_typography-font-weight',
								'choice'   => 'font-weight',
								'callback' => [ 'font_weight_no_regular', '' ],
							],
							[
								'name'   => '--h3_typography-line-height',
								'choice' => 'line-height',
							],
							[
								'name'     => '--h3_typography-letter-spacing',
								'choice'   => 'letter-spacing',
								'callback' => [ 'maybe_append_px', '' ],
							],
							[
								'name'   => '--h3_typography-font-style',
								'choice' => 'font-style',
							],
							[
								'name'   => '--h3_typography-font-size',
								'choice' => 'font-size',
							],
							[
								'name'     => '--h3_typography-color',
								'choice'   => 'color',
								'callback' => [ 'sanitize_color', '' ],
							],
							[
								'name'   => '--h3_typography-margin-top',
								'choice' => 'margin-top',
							],
							[
								'name'   => '--h3_typography-margin-bottom',
								'choice' => 'margin-bottom',
							],
						],
					],
					'h4_typography'                 => [
						'id'          => 'h4_typography',
						'label'       => esc_html__( 'H4 Headings Typography', 'Avada' ),
						'description' => esc_html__( 'These settings control the typography for all H4 headings.', 'Avada' ),
						'type'        => 'typography',
						'choices'     => [
							'font-family'    => true,
							'font-size'      => true,
							'font-weight'    => true,
							'line-height'    => true,
							'letter-spacing' => true,
							'color'          => true,
							'margin-top'     => true,
							'margin-bottom'  => true,
						],
						'default'     => [
							'font-family'    => 'Zilla Slab',
							'font-size'      => '28px',
							'font-weight'    => '400',
							'line-height'    => '1.36',
							'letter-spacing' => '0',
							'color'          => '#212934',
							'margin-top'     => '1.33em',
							'margin-bottom'  => '1.33em',
						],
						'css_vars'    => [
							[
								'name'     => '--h4_typography-font-family',
								'choice'   => 'font-family',
								'callback' => [ 'combined_font_family', 'h4_typography' ],
							],
							[
								'name'     => '--h4_typography-font-weight',
								'choice'   => 'font-weight',
								'callback' => [ 'font_weight_no_regular', '' ],
							],
							[
								'name'   => '--h4_typography-line-height',
								'choice' => 'line-height',
							],
							[
								'name'     => '--h4_typography-letter-spacing',
								'choice'   => 'letter-spacing',
								'callback' => [ 'maybe_append_px', '' ],
							],
							[
								'name'   => '--h4_typography-font-style',
								'choice' => 'font-style',
							],
							[
								'name'   => '--h4_typography-font-size',
								'choice' => 'font-size',
							],
							[
								'name'     => '--h4_typography-color',
								'choice'   => 'color',
								'callback' => [ 'sanitize_color', '' ],
							],
							[
								'name'   => '--h4_typography-margin-top',
								'choice' => 'margin-top',
							],
							[
								'name'   => '--h4_typography-margin-bottom',
								'choice' => 'margin-bottom',
							],
						],
					],
					'h5_typography'                 => [
						'id'          => 'h5_typography',
						'label'       => esc_html__( 'H5 Headings Typography', 'Avada' ),
						'description' => esc_html__( 'These settings control the typography for all H5 headings.', 'Avada' ),
						'type'        => 'typography',
						'choices'     => [
							'font-family'    => true,
							'font-size'      => true,
							'font-weight'    => true,
							'line-height'    => true,
							'letter-spacing' => true,
							'color'          => true,
							'margin-top'     => true,
							'margin-bottom'  => true,
						],
						'default'     => [
							'font-family'    => 'Zilla Slab',
							'font-size'      => '24px',
							'font-weight'    => '400',
							'line-height'    => '1.4',
							'letter-spacing' => '0',
							'color'          => '#212934',
							'margin-top'     => '1.67em',
							'margin-bottom'  => '1.67em',
						],
						'css_vars'    => [
							[
								'name'     => '--h5_typography-font-family',
								'choice'   => 'font-family',
								'callback' => [ 'combined_font_family', 'h5_typography' ],
							],
							[
								'name'     => '--h5_typography-font-weight',
								'choice'   => 'font-weight',
								'callback' => [ 'font_weight_no_regular', '' ],
							],
							[
								'name'   => '--h5_typography-line-height',
								'choice' => 'line-height',
							],
							[
								'name'     => '--h5_typography-letter-spacing',
								'choice'   => 'letter-spacing',
								'callback' => [ 'maybe_append_px', '' ],
							],
							[
								'name'   => '--h5_typography-font-style',
								'choice' => 'font-style',
							],
							[
								'name'   => '--h5_typography-font-size',
								'choice' => 'font-size',
							],
							[
								'name'     => '--h5_typography-color',
								'choice'   => 'color',
								'callback' => [ 'sanitize_color', '' ],
							],
							[
								'name'   => '--h5_typography-margin-top',
								'choice' => 'margin-top',
							],
							[
								'name'   => '--h5_typography-margin-bottom',
								'choice' => 'margin-bottom',
							],
						],
					],
					'h6_typography'                 => [
						'id'          => 'h6_typography',
						'label'       => esc_html__( 'H6 Headings Typography', 'Avada' ),
						'description' => esc_html__( 'These settings control the typography for all H6 headings.', 'Avada' ),
						'type'        => 'typography',
						'choices'     => [
							'font-family'    => true,
							'font-size'      => true,
							'font-weight'    => true,
							'line-height'    => true,
							'letter-spacing' => true,
							'color'          => true,
							'margin-top'     => true,
							'margin-bottom'  => true,
						],
						'default'     => [
							'font-family'    => 'Zilla Slab',
							'font-size'      => '18px',
							'font-weight'    => '400',
							'line-height'    => '1.5',
							'letter-spacing' => '0',
							'color'          => '#212934',
							'margin-top'     => '2.33em',
							'margin-bottom'  => '2.33em',
						],
						'css_vars'    => [
							[
								'name'     => '--h6_typography-font-family',
								'choice'   => 'font-family',
								'callback' => [ 'combined_font_family', 'h6_typography' ],
							],
							[
								'name'     => '--h6_typography-font-weight',
								'choice'   => 'font-weight',
								'callback' => [ 'font_weight_no_regular', '' ],
							],
							[
								'name'   => '--h6_typography-line-height',
								'choice' => 'line-height',
							],
							[
								'name'     => '--h6_typography-letter-spacing',
								'choice'   => 'letter-spacing',
								'callback' => [ 'maybe_append_px', '' ],
							],
							[
								'name'   => '--h6_typography-font-style',
								'choice' => 'font-style',
							],
							[
								'name'   => '--h6_typography-font-size',
								'choice' => 'font-size',
							],
							[
								'name'     => '--h6_typography-color',
								'choice'   => 'color',
								'callback' => [ 'sanitize_color', '' ],
							],
							[
								'name'   => '--h6_typography-margin-top',
								'choice' => 'margin-top',
							],
							[
								'name'   => '--h6_typography-margin-bottom',
								'choice' => 'margin-bottom',
							],
						],
					],
					'post_title_typography'         => [
						'id'          => 'post_title_typography',
						'label'       => esc_html__( 'Post Title Typography', 'Avada' ),
						'description' => __( 'These settings control the typography of all post titles including archive and single posts.<br /><strong>IMPORTANT:</strong> On archive pages and in blog elements the linked post titles will use link color.', 'Avada' ),
						'type'        => 'typography',
						'choices'     => [
							'font-family'    => true,
							'font-size'      => true,
							'font-weight'    => true,
							'line-height'    => true,
							'letter-spacing' => true,
							'color'          => true,
						],
						'default'     => [
							'font-family'    => 'Zilla Slab',
							'font-size'      => '32px',
							'font-weight'    => '400',
							'line-height'    => '1.3',
							'letter-spacing' => '0',
							'color'          => '#212934',
						],
						'css_vars'    => [
							[
								'name'     => '--post_title_typography-font-family',
								'choice'   => 'font-family',
								'callback' => [ 'combined_font_family', 'post_title_typography' ],
							],
							[
								'name'     => '--post_title_typography-font-weight',
								'choice'   => 'font-weight',
								'callback' => [ 'font_weight_no_regular', '' ],
							],
							[
								'name'   => '--post_title_typography-line-height',
								'choice' => 'line-height',
							],
							[
								'name'     => '--post_title_typography-letter-spacing',
								'choice'   => 'letter-spacing',
								'callback' => [ 'maybe_append_px', '' ],
							],
							[
								'name'   => '--post_title_typography-font-style',
								'choice' => 'font-style',
							],
							[
								'name'   => '--post_title_typography-font-size',
								'choice' => 'font-size',
							],
							[
								'name'     => '--post_title_typography-color',
								'choice'   => 'color',
								'callback' => [ 'sanitize_color', '' ],
							],
						],
					],
					'post_titles_extras_typography' => [
						'id'          => 'post_titles_extras_typography',
						'label'       => esc_html__( 'Post Title Extras Typography', 'Avada' ),
						'description' => esc_html__( 'These settings control the typography of single post title extras such as "Comments", "Related Posts or Projects" and "Author Titles"', 'Avada' ),
						'type'        => 'typography',
						'choices'     => [
							'font-family'    => true,
							'font-size'      => true,
							'font-weight'    => true,
							'line-height'    => true,
							'letter-spacing' => true,
							'color'          => true,
						],
						'default'     => [
							'font-family'    => 'Open Sans',
							'font-size'      => '18px',
							'font-weight'    => '400',
							'line-height'    => '1.5',
							'letter-spacing' => '0',
							'color'          => '#212934',
						],
						'css_vars'    => [
							[
								'name'     => '--post_titles_extras_typography-font-family',
								'choice'   => 'font-family',
								'callback' => [ 'combined_font_family', 'post_titles_extras_typography' ],
							],
							[
								'name'     => '--post_titles_extras_typography-font-weight',
								'choice'   => 'font-weight',
								'callback' => [ 'font_weight_no_regular', '' ],
							],
							[
								'name'   => '--post_titles_extras_typography-line-height',
								'choice' => 'line-height',
							],
							[
								'name'     => '--post_titles_extras_typography-letter-spacing',
								'choice'   => 'letter-spacing',
								'callback' => [ 'maybe_append_px', '' ],
							],
							[
								'name'   => '--post_titles_extras_typography-font-style',
								'choice' => 'font-style',
							],
							[
								'name'   => '--post_titles_extras_typography-font-size',
								'choice' => 'font-size',
							],
							[
								'name'     => '--post_titles_extras_typography-color',
								'choice'   => 'color',
								'callback' => [ 'sanitize_color', '' ],
							],
						],
					],
				],
			],
			'custom_webfont_typography_section' => [
				'label'  => esc_html__( 'Custom Fonts', 'Avada' ),
				'id'     => 'custom_webfont_typography_section',
				'type'   => 'sub-section',
				'fields' => [
					'custom_fonts_info' => [
						'label'       => '',
						'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> Please upload your custom fonts below. Once you upload a custom font, <strong>you will have to save your options and reload this page on your browser</strong>. After you reload the page you will be able to select your new fonts - they will be available at the top of the fonts-list in the typography controls.', 'Avada' ) . '</div>',
						'id'          => 'custom_fonts_info',
						'type'        => 'custom',
					],
					'custom_fonts'      => [
						'label'       => esc_html__( 'Custom Fonts', 'Avada' ),
						'description' => esc_html__( 'Upload a custom font to use throughout the site. All files are not necessary but are recommended for full browser support. You can upload as many custom fonts as you need. Click the "Add" button for additional upload boxes.', 'Avada' ),
						'id'          => 'custom_fonts',
						'default'     => [],
						'type'        => 'repeater',
						'bind_title'  => 'name',
						'limit'       => 50,
						// No need to refresh the page.
						'transport'   => 'postMessage',
						'fields'      => [
							'name'  => [
								'label'       => esc_html__( 'Font Name', 'Avada' ),
								'description' => esc_html__( 'This will be used in the font-family dropdown.' ),
								'id'          => 'name',
								'default'     => '',
								'type'        => 'text',
								'class'       => 'avada-custom-font-name',
							],
							'woff2' => [
								'label'   => 'WOFF2',
								'id'      => 'woff2',
								'default' => '',
								'type'    => 'upload',
								'mode'    => false,
								'preview' => false,
							],
							'woff'  => [
								'label'   => 'WOFF',
								'id'      => 'woff',
								'default' => '',
								'type'    => 'upload',
								'mode'    => false,
								'preview' => false,
							],
							'ttf'   => [
								'label'   => 'TTF',
								'id'      => 'ttf',
								'default' => '',
								'type'    => 'upload',
								'mode'    => false,
								'preview' => false,
							],
							'eot'   => [
								'label'   => 'EOT',
								'id'      => 'eot',
								'default' => '',
								'type'    => 'upload',
								'mode'    => false,
								'preview' => false,
							],
							'svg'   => [
								'label'   => 'SVG',
								'id'      => 'svg',
								'default' => '',
								'type'    => 'upload',
								'mode'    => false,
								'preview' => false,
							],
						],
					],
				],
			],
		],
	];

	return $sections;

}
