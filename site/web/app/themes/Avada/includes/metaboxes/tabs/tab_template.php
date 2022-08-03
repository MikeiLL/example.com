<?php
/**
 * Page Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

/**
 * Build options for template edit.
 *
 * @since 6.2
 */
class Avada_Template_Page_Options {

	/**
	 * Holds the section map for options.
	 *
	 * @since 6.2
	 *
	 * @access private
	 * @var sections
	 */
	private $sections;

	/**
	 * Target post ID.
	 *
	 * @since 6.2
	 *
	 * @access private
	 * @var target_id
	 */
	private $target_id;

	/**
	 * Target post object.
	 *
	 * @since 6.2
	 *
	 * @access private
	 * @var target_post
	 */
	private $target_post;

	/**
	 * Actual post ID.
	 *
	 * @since 6.2
	 *
	 * @access private
	 * @var post_id
	 */
	private $post_id;

	/**
	 * Template type.
	 *
	 * @since 6.2
	 *
	 * @access private
	 * @var template_type
	 */
	private $template_type;

	/**
	 * Post type
	 *
	 * @since 7.3
	 *
	 * @access private
	 * @var post_type
	 */
	private $post_type;

	/**
	 * Temporal variable for memoization.
	 *
	 * @since 6.2
	 *
	 * @access private
	 * @var tmp
	 */
	private $tmp;

	/**
	 * Min count to show ajax selector.
	 *
	 * @since 6.2
	 *
	 * @access private
	 * @var ajax_min_count
	 */
	private $ajax_min_count = 25;

	/**
	 * The class constructor.
	 *
	 * @access public
	 * @param array $sections Current options.
	 * @since 6.2
	 */
	public function __construct( $sections ) {
		$this->sections = $sections;
		$this->set_target();
		$this->set_options();
	}

	/**
	 * Set target post and required vars.
	 *
	 * @access public
	 * @since 6.2
	 */
	public function set_target() {
		global $post;
		$real_page_id = is_admin() ? $post->ID : (int) str_replace( 'archive-', '', fusion_library()->get_page_id() );
		$terms        = get_the_terms( $real_page_id, 'fusion_tb_category' );
		$type         = is_array( $terms ) ? $terms[0]->name : false;
		$_post        = get_post( $real_page_id );

		$this->target_id     = apply_filters( 'fusion_dynamic_post_id', $real_page_id );
		$this->target_post   = $_post;
		$this->post_id       = $real_page_id;
		$this->template_type = $type;
		$this->post_type     = get_post_type( $real_page_id );
	}

	/**
	 * Build the option map and set to object.
	 *
	 * @access public
	 * @since 6.2
	 */
	public function set_options() {
		$this->sections['template'] = [
			'label'    => 'fusion_element' === $this->post_type ? esc_html__( 'Post Card', 'Avada' ) : esc_html__( 'Layout Section', 'Avada' ),
			'id'       => 'template',
			'alt_icon' => 'fusiona-file',
			'fields'   => [],
		];

		$this->add_dynamic_preview_options();

		switch ( $this->template_type ) {
			case 'footer':
			case 'page_title_bar':
				$this->set_module_options();
				break;
		}

		if ( 'fusion_element' === $this->post_type ) {
			$this->sections['template']['fields']['preview_width'] = [
				'id'          => 'preview_width',
				'label'       => esc_attr__( 'Preview Width', 'Avada' ),
				/* translators: Additional description (defaults). */
				'description' => esc_html__( 'Select the width to preview the card at in the live editor.  Note: this is only used for previewing purposes.', 'fusion-builder' ),
				'dependency'  => [],
				'type'        => 'slider',
				'transport'   => 'postMessage',
				'events'      => [
					'fusion-card-preview-width',
				],
				'default'     => '50',
				'choices'     => [
					'min'  => '0',
					'max'  => '100',
					'step' => '1',
				],
			];
		}
	}

	/**
	 * Adds dynamic content preview options.
	 *
	 * @access public
	 * @since 6.2
	 */
	public function add_dynamic_preview_options() {
		$preview_fields  = [];
		$archive_choices = [
			'post' => esc_attr__( 'Post', 'Avada' ),
		];
		$choices         = [
			'default' => esc_attr__( 'Default', 'Avada' ),
		];
		$args            = [
			'public'            => true,
			'show_in_nav_menus' => true,
		];

		if ( 'fusion_element' !== $this->post_type ) {
			$choices['archives'] = esc_attr__( 'Archives', 'Avada' );
			$choices['search']   = esc_attr__( 'Search', 'Avada' );
			$choices['404']      = esc_attr__( '404', 'Avada' );
		} else {
			$choices['term'] = esc_attr__( 'Term', 'Avada' );
		}

		$post_types      = get_post_types( $args, 'objects', 'and' );
		$post_taxonomies = [];
		// Filter out not relevant post types (can add filter later).
		$disabled_post_types = [ 'attachment', 'slide', 'themefusion_elastic', 'fusion_template', 'fusion_tb_section' ];

		// No need for pages in post card.
		if ( 'fusion_element' === $this->post_type ) {
			$disabled_post_types[] = 'page';
		}

		foreach ( $disabled_post_types as $disabled ) {
			unset( $post_types[ $disabled ] );
		}

		foreach ( $post_types as $post_type ) {
			$selection   = [];
			$field_type  = 'ajax_select';
			$ajax        = 'fusion_search_query';
			$ajax_params = [
				'post_type' => $post_type,
			];

			if ( 'fusion_element' === $this->post_type ) {
				$new_taxonomies = get_object_taxonomies( $post_type->name, 'objects' );
				foreach ( $new_taxonomies as $new_taxonomy ) {
					$post_taxonomies[ $new_taxonomy->name ] = ucwords( esc_html( $new_taxonomy->label ) );
				}
			}

			if ( $this->ajax_min_count > wp_count_posts( $post_type->name )->publish ) {
				$ajax       = '';
				$field_type = 'select';
				$posts      = get_posts(
					[
						'post_type'   => $post_type->name,
						'numberposts' => -1, // phpcs:ignore WPThemeReview.CoreFunctionality.PostsPerPage
					]
				);

				/* translators: The post name. */
				$selection[0] = sprintf( esc_attr__( 'Any %s item', 'Avada' ), $post_type->labels->singular_name );

				foreach ( $posts as $single_post ) {
					$selection[ $single_post->ID ] = $single_post->post_title;
				}
			}

			$preview_fields[ 'preview_' . $post_type->name ] = [
				'id'          => 'preview_' . $post_type->name,
				/* translators: The post name. */
				'label'       => sprintf( esc_attr__( 'Select %s', 'Avada' ), $post_type->labels->singular_name ),
				/* translators: The post name. */
				'placeholder' => sprintf( esc_attr__( 'Any %s item', 'Avada' ), $post_type->labels->singular_name ),
				/* translators: The post name. */
				'description' => sprintf( esc_attr__( 'Choose to view dynamic content as %s. Leave Empty for random selection.', 'Avada' ), $post_type->labels->singular_name ),
				'type'        => $field_type,
				'choices'     => $selection,
				'ajax'        => $ajax,
				'max_input'   => 1,
				'transport'   => 'postMessage',
				'class'       => 'fusion-no-bottom-border',
				'ajax_params' => $ajax_params,
				'dependency'  => [
					[
						'field'      => 'dynamic_content_preview_type',
						'value'      => $post_type->name,
						'comparison' => '==',
					],
				],
			];

			$choices[ $post_type->name ] = $post_type->labels->singular_name;

			if ( ! empty( $post_type->has_archive ) ) {
				$archive_choices[ $post_type->name ] = $post_type->labels->singular_name;
			}
		}

		$this->sections['template']['fields']['dynamic_content_preview_type'] = [
			'id'          => 'dynamic_content_preview_type',
			'type'        => 'select',
			'label'       => esc_attr__( 'View Dynamic Content As', 'Avada' ),
			'description' => esc_html__( 'Make a selection to view dynamic content as another item.', 'Avada' ),
			'default'     => 'default',
			'transport'   => 'postMessage',
			'class'       => 'fusion-no-bottom-border',
			'choices'     => $choices,
		];

		if ( 'fusion_element' !== $this->post_type ) {
			$preview_fields['preview_archives'] = [
				'id'          => 'preview_archives',
				'label'       => esc_attr__( 'Select Archive Type', 'Avada' ),
				'description' => esc_attr__( 'Choose to view dynamic content as archive type. Leave Empty for random selection.', 'Avada' ),
				'type'        => 'select',
				'default'     => 'post',
				'choices'     => $archive_choices,
				'transport'   => 'postMessage',
				'class'       => 'fusion-no-bottom-border',
				'dependency'  => [
					[
						'field'      => 'dynamic_content_preview_type',
						'value'      => 'archives',
						'comparison' => '==',
					],
				],
			];
		} elseif ( ! empty( $post_taxonomies ) ) {
			unset( $post_taxonomies['post_format'] );
			unset( $post_taxonomies['product_visibility'] );

			$preview_fields['preview_term'] = [
				'id'          => 'preview_term',
				'label'       => esc_attr__( 'Select Taxonomy', 'Avada' ),
				'description' => esc_attr__( 'Select a taxonomy to pull a term from.  The most recent term in the taxonomy will be used.', 'Avada' ),
				'type'        => 'select',
				'default'     => '',
				'choices'     => $post_taxonomies,
				'transport'   => 'postMessage',
				'class'       => 'fusion-no-bottom-border',
				'dependency'  => [
					[
						'field'      => 'dynamic_content_preview_type',
						'value'      => 'term',
						'comparison' => '==',
					],
				],
			];
		}

		foreach ( $preview_fields as $field_id => $field ) {
			$this->sections['template']['fields'][ $field_id ] = $field;
		}

		$this->sections['template']['fields']['dynamic_content_preview_action'] = [
			'id'        => 'dynamic_content_preview_action',
			'type'      => 'button',
			'label'     => esc_attr__( 'Preview', 'Avada' ),
			'class'     => 'fusion-no-top-padding',
			'action'    => 'FusionApp.fullRefresh()',
			'transport' => 'postMessage',
		];
	}

	/**
	 * Adds general module options (header, footer).
	 *
	 * @access public
	 * @since 6.2
	 */
	public function set_module_options() {

		if ( 'footer' === $this->template_type ) {
			$this->sections['template']['fields']['special_effect'] = [
				'id'          => 'special_effect',
				'label'       => esc_attr__( 'Special Effect', 'Avada' ),
				/* translators: Additional description (defaults). */
				'description' => esc_html__( 'Select a special effect for the footer template.', 'fusion-builder' ),
				'dependency'  => [],
				'type'        => 'radio-buttonset',
				'transport'   => 'postMessage',
				'choices'     => [
					'none'                   => esc_attr__( 'None', 'Avada' ),
					'footer_parallax_effect' => esc_attr__( 'Parallax', 'Avada' ),
					'footer_sticky'          => esc_attr__( 'Sticky', 'Avada' ),
				],
				'default'     => 'none',
			];
		}
	}

	/**
	 * Gets a taxonomy for a post-type.
	 *
	 * @access public
	 * @since 6.2.0
	 * @param string $post_type The post-type.
	 * @return array            Returns an array of taxonomies.
	 */
	public function get_taxonomy( $post_type ) {
		if ( ! isset( $this->tmp['taxonomy'] ) ) {
			$this->tmp['taxonomy'] = [];
		}
		if ( ! isset( $this->tmp['taxonomy'][ $post_type ] ) ) {
			$this->tmp['taxonomy'][ $post_type ] = get_object_taxonomies( $post_type, 'objects' );
		}
		return $this->tmp['taxonomy'][ $post_type ];
	}

	/**
	 * Retrieve the options.
	 *
	 * @access public
	 * @since 6.2
	 */
	public function get_options() {
		return $this->sections;
	}
}

/**
 * Template settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_template( $sections ) {
	$template_options = new Avada_Template_Page_Options( $sections );
	return $template_options->get_options();
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
