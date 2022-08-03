<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Avada Builder Library.
 *
 * @package Avada-Builder
 * @since 2.2
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Main Fusion_Builder_Library Class.
 *
 * @since 1.0
 */
class Fusion_Builder_Library {

	/**
	 * The one, true instance of this object.
	 *
	 * @static
	 * @access private
	 * @since 1.0
	 * @var object
	 */
	private static $instance;

	/**
	 * Location.
	 *
	 * @access private
	 * @since 1.0
	 * @var object
	 */
	private $location;

	/**
	 * Layouts have been registered.
	 *
	 * @access private
	 * @since 3.3
	 * @var bool
	 */
	private $layouts_registered = false;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 */
	public static function get_instance() {

		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null === self::$instance ) {
			self::$instance = new Fusion_Builder_Library();
		}
		return self::$instance;
	}

	/**
	 * Initializes the class by setting hooks, filters,
	 * and administrative functions.
	 *
	 * @access private
	 * @since 1.0
	 */
	private function __construct() {
		// Register custom post types.
		add_action( 'wp_loaded', [ $this, 'register_layouts' ] );

		add_action( 'wp_ajax_fusion_builder_delete_layout', [ $this, 'delete_layout' ] );
		add_action( 'wp_ajax_fusion_builder_save_layout', [ $this, 'save_layout' ] );
		add_action( 'wp_ajax_fusion_load_custom_elements', [ $this, 'load_custom_elements' ] );
		add_action( 'wp_ajax_fusion_builder_load_layout', [ $this, 'load_layout' ] );
		add_action( 'wp_ajax_fusion_builder_load_demo', [ $this, 'load_demo' ] );
		add_action( 'wp_ajax_fusion_builder_load_demo_layout', [ $this, 'load_demo_layout' ] );
		add_action( 'wp_ajax_fusion_builder_update_layout', [ $this, 'update_layout' ] );
		add_action( 'wp_ajax_fusion_builder_get_image_url', [ $this, 'get_image_url' ] );

		add_filter( 'fusion_set_overrides', [ $this, 'set_template_content_override' ], 10, 3 );

		// Polylang sync taxonomies.
		add_filter( 'pll_copy_taxonomies', [ $this, 'copy_taxonomies' ], 10, 2 );

		// Clone library element.
		add_action( 'admin_action_clone_library_element', [ $this, 'clone_library_element' ] );

		$this->location = true === Fusion_App()->is_builder || ( isset( $_POST ) && isset( $_POST['fusion_front_end'] ) && $_POST['fusion_front_end'] ) ? 'front' : 'back'; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput

		// Check for action and action2 and trigger appropriate function.
		if ( is_admin() ) {
			$this->bulk_actions();
		}
	}

	/**
	 * Get action based on request.
	 * phpcs:disable WordPress.Security
	 *
	 * @since 3.2
	 * @access public
	 */
	public function get_action() {

		if ( isset( $_REQUEST['action'] ) ) {
			if ( -1 !== $_REQUEST['action'] && '-1' !== $_REQUEST['action'] ) {
				return sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
			}
		}
		if ( isset( $_REQUEST['action2'] ) ) {
			if ( -1 !== $_REQUEST['action2'] && '-1' !== $_REQUEST['action2'] ) {
				return sanitize_text_field( wp_unslash( $_REQUEST['action2'] ) );
			}
		}
		return false;
	}
	// phpcs:enable WordPress.Security

	/**
	 * Apply bulk action.
	 *
	 * @since 3.2
	 * @access public
	 */
	public function bulk_actions() {
		$action = $this->get_action();

		if ( $action ) {
			switch ( $action ) {
				case 'fusion_library_new':
					// Action with priority 11 to ensure it is after post type is registered.
					add_action( 'wp_loaded', [ $this, 'add_new_library_element' ], 11 );
					break;
				case 'fusion_trash_element':
					$this->trash_element();
					break;
				case 'fusion_restore_element':
					$this->restore_element();
					break;
				case 'fusion_delete_element':
					$this->delete_element_post();
					break;
			}
		}
	}

	/**
	 * Setup the post type and taxonomies.
	 *
	 * @since 2.2
	 * @access public
	 */
	public function register_layouts() {

		// If they are already registered, don't repeat.
		if ( $this->layouts_registered ) {
			return;
		}

		$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );

		$labels = [
			'name'                     => _x( 'Fusion Templates', 'Layout type general name', 'fusion-builder' ),
			'singular_name'            => _x( 'Layout', 'Layout type singular name', 'fusion-builder' ),
			'add_new'                  => _x( 'Add New', 'Layout item', 'fusion-builder' ),
			'add_new_item'             => esc_html__( 'Add New Layout', 'fusion-builder' ),
			'edit_item'                => esc_html__( 'Edit Layout', 'fusion-builder' ),
			'new_item'                 => esc_html__( 'New Layout', 'fusion-builder' ),
			'all_items'                => esc_html__( 'All Layouts', 'fusion-builder' ),
			'view_item'                => esc_html__( 'View Layout', 'fusion-builder' ),
			'search_items'             => esc_html__( 'Search Layouts', 'fusion-builder' ),
			'not_found'                => esc_html__( 'Nothing found', 'fusion-builder' ),
			'not_found_in_trash'       => esc_html__( 'Nothing found in Trash', 'fusion-builder' ),
			'item_published'           => esc_html__( 'Layout published.', 'fusion-builder' ),
			'item_published_privately' => esc_html__( 'Layout published privately.', 'fusion-builder' ),
			'item_reverted_to_draft'   => esc_html__( 'Layout reverted to draft.', 'fusion-builder' ),
			'item_scheduled'           => esc_html__( 'Layout scheduled.', 'fusion-builder' ),
			'item_updated'             => esc_html__( 'Layout updated.', 'fusion-builder' ),
			'parent_item_colon'        => '',
		];

		$args = [
			'labels'              => $labels,
			'public'              => false,
			'publicly_queryable'  => $is_builder,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'exclude_from_search' => true,
			'can_export'          => true,
			'query_var'           => true,
			'has_archive'         => false,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'show_in_nav_menus'   => false,
			'supports'            => [ 'title', 'editor', 'revisions' ],
		];

		register_post_type( 'fusion_template', apply_filters( 'fusion_layout_template_args', $args ) );

		$labels = [
			'name'                     => _x( 'Fusion Elements', 'element type general name', 'fusion-builder' ),
			'singular_name'            => _x( 'Element', 'Element type singular name', 'fusion-builder' ),
			'add_new'                  => _x( 'Add New', 'Element item', 'fusion-builder' ),
			'add_new_item'             => esc_html__( 'Add New Element', 'fusion-builder' ),
			'edit_item'                => esc_html__( 'Edit Element', 'fusion-builder' ),
			'new_item'                 => esc_html__( 'New Element', 'fusion-builder' ),
			'all_items'                => esc_html__( 'All Elements', 'fusion-builder' ),
			'view_item'                => esc_html__( 'View Element', 'fusion-builder' ),
			'search_items'             => esc_html__( 'Search Elements', 'fusion-builder' ),
			'not_found'                => esc_html__( 'Nothing found', 'fusion-builder' ),
			'not_found_in_trash'       => esc_html__( 'Nothing found in Trash', 'fusion-builder' ),
			'item_published'           => esc_html__( 'Element published.', 'fusion-builder' ),
			'item_published_privately' => esc_html__( 'Element published privately.', 'fusion-builder' ),
			'item_reverted_to_draft'   => esc_html__( 'Element reverted to draft.', 'fusion-builder' ),
			'item_scheduled'           => esc_html__( 'Element scheduled.', 'fusion-builder' ),
			'item_updated'             => esc_html__( 'Element updated.', 'fusion-builder' ),
			'parent_item_colon'        => '',
		];

		$args = [
			'labels'              => $labels,
			'public'              => false,
			'publicly_queryable'  => $is_builder,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'exclude_from_search' => true,
			'can_export'          => true,
			'query_var'           => true,
			'has_archive'         => false,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'supports'            => [ 'title', 'editor', 'revisions' ],

		/**
		 * Removed because of a WPML issue, see #2335
		'capabilities'       => array(
			'create_posts' => 'do_not_allow',
		),
		*/
		];

		register_post_type( 'fusion_element', apply_filters( 'fusion_layout_element_args', $args ) );

		$labels = [
			'name' => esc_attr__( 'Category', 'fusion-builder' ),
		];

		register_taxonomy(
			'element_category',
			[ 'fusion_element' ],
			[
				'hierarchical'       => true,
				'labels'             => $labels,
				'publicly_queryable' => $is_builder,
				'show_ui'            => false,
				'show_admin_column'  => true,
				'query_var'          => true,
				'show_in_nav_menus'  => false,
			]
		);

		$labels = [
			'name' => esc_attr__( 'Category', 'fusion-builder' ),
		];

		register_taxonomy(
			'template_category',
			[ 'fusion_template' ],
			[
				'hierarchical'       => true,
				'labels'             => $labels,
				'publicly_queryable' => $is_builder,
				'show_ui'            => false,
				'show_admin_column'  => false,
				'query_var'          => true,
				'show_in_nav_menus'  => false,
			]
		);

		$this->layouts_registered = true;
	}

	/**
	 * Delete custom template or element.
	 */
	public function delete_layout() {

		check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

		if ( isset( $_POST['fusion_layout_id'] ) && '' !== $_POST['fusion_layout_id'] && current_user_can( 'delete_post', $_POST['fusion_layout_id'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

			$layout_id = (int) $_POST['fusion_layout_id'];

			wp_delete_post( $layout_id, true );
		}

		wp_die();
	}

	/**
	 * Add custom template or element.
	 *
	 * @param string $post_type The post-type.
	 * @param string $name      The post-title.
	 * @param string $content   The post-content.
	 * @param array  $meta      The post-meta.
	 * @param array  $taxonomy  Taxonomies.
	 * @param string $term      Term.
	 */
	public function create_layout( $post_type, $name, $content, $meta = [], $taxonomy = [], $term = '' ) {

		$layout = [
			'post_title'   => sanitize_text_field( $name ),
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_type'    => $post_type,
		];

		$layout_id = wp_insert_post( $layout );

		if ( ! empty( $meta ) ) {
			foreach ( $meta as $meta_key => $meta_value ) {
				add_post_meta( $layout_id, $meta_key, sanitize_text_field( $meta_value ) );
			}
		}

		if ( '' !== $term ) {
			wp_insert_term( $term, $taxonomy );
			$term_id = term_exists( $term, $taxonomy );
			wp_set_post_terms( $layout_id, $term_id, $taxonomy );
		}

		do_action( 'fusion_builder_create_layout_after' );

		return $layout_id;
	}

	/**
	 * Save custom layout.
	 */
	public function save_layout() {

		check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

		if ( isset( $_POST['fusion_layout_name'] ) && '' !== $_POST['fusion_layout_name'] ) {

			$layout_name = wp_unslash( $_POST['fusion_layout_name'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$taxonomy    = 'element_category';
			$term        = '';
			$meta        = [];
			$layout_type = '';
			$global_data = '';
			$global_type = [
				'elements'   => 'element',
				'columns'    => 'column',
				'sections'   => 'container',
				'post_cards' => 'post_card',
			];

			if ( isset( $_POST['fusion_layout_post_type'] ) && '' !== $_POST['fusion_layout_post_type'] ) {

				$post_type = sanitize_text_field( wp_unslash( $_POST['fusion_layout_post_type'] ) );

				// Make sure only our library post types can be created.
				$post_type_object = get_post_type_object( $post_type );
				if ( ! current_user_can( $post_type_object->cap->edit_posts ) || ( 'fusion_template' !== $post_type && 'fusion_element' !== $post_type ) ) {
					return;
				}

				if ( isset( $_POST['fusion_current_post_id'] ) && '' !== $_POST['fusion_current_post_id'] ) {
					$post_id = wp_unslash( $_POST['fusion_current_post_id'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				}

				if ( isset( $_POST['fusion_layout_element_type'] ) && '' !== $_POST['fusion_layout_element_type'] ) {
					$meta['_fusion_element_type'] = wp_unslash( $_POST['fusion_layout_element_type'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					$layout_type                  = ' fusion-element-type-' . wp_unslash( $_POST['fusion_layout_element_type'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				}

				if ( 'fusion_template' === $post_type ) {
					$meta['fusion_builder_status'] = 'active';

					// Save custom css.
					if ( isset( $_POST['fusion_custom_css'] ) && '' !== $_POST['fusion_custom_css'] ) {
						$meta['_fusion_builder_custom_css'] = wp_unslash( $_POST['fusion_custom_css'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					}

					// Save page template.
					if ( isset( $_POST['fusion_page_template'] ) && '' !== $_POST['fusion_page_template'] ) {
						$meta['_wp_page_template'] = wp_unslash( $_POST['fusion_page_template'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					}

					// Save globals.
					$_POST['fusion_layout_content'] = apply_filters( 'content_save_pre', $_POST['fusion_layout_content'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				}

				// Globals.
				if ( isset( $_POST['fusion_save_global'] ) && 'false' !== $_POST['fusion_save_global'] ) {
					$meta['_fusion_is_global'] = 'yes';
					$global_data               = 'fusion-global';
				} else {
					$position = false;
					if ( isset( $_POST['fusion_layout_content'] ) ) {
						$position = strpos( sanitize_text_field( wp_unslash( $_POST['fusion_layout_content'] ) ), 'fusion_global' );
					}

					if ( false !== $position ) {
						// Remove fusion_global attributes from content if it is simple library element.
						$_POST['fusion_layout_content'] = preg_replace( '/fusion_global=[^][^][0-9]*[^][^]/', '', wp_unslash( $_POST['fusion_layout_content'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					}
				}

				// Add Fusion Options to meta data.
				if ( isset( $_POST['fusion_options'] ) && '' !== wp_unslash( $_POST['fusion_options'] ) && is_array( wp_unslash( $_POST['fusion_options'] ) ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					$_fusion_options = wp_unslash( $_POST['fusion_options'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					if ( isset( $_POST['fusion_po_type'] ) && 'object' === $_POST['fusion_po_type'] ) {
						foreach ( $_fusion_options as $option => $value ) {
							$meta[ $option ] = $value;
						}
					} else {
						foreach ( $_fusion_options as $option ) {
							$meta[ $option[0] ] = $option[1];
						}
					}
				}
				// Post category.
				if ( isset( $_POST['fusion_layout_new_cat'] ) && '' !== $_POST['fusion_layout_new_cat'] ) {
					$term        = sanitize_text_field( wp_unslash( $_POST['fusion_layout_new_cat'] ) );
					$global_type = $global_type[ $term ];
				}

				$post_fusion_layout_content = ( isset( $_POST['fusion_layout_content'] ) ) ? wp_unslash( $_POST['fusion_layout_content'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				$new_layout_id              = $this->create_layout( $post_type, $layout_name, $post_fusion_layout_content, $meta, $taxonomy, $term );
				?>

				<?php if ( 'fusion_element' === $post_type ) : ?>

					<li class="<?php echo esc_attr( $global_data ); ?> fusion-page-layout<?php echo esc_attr( $layout_type ); ?>" data-layout_id="<?php echo esc_attr( $new_layout_id ); ?>">
						<h4 class="fusion-page-layout-title" title="<?php echo esc_attr( get_the_title( $new_layout_id ) ); ?>">
							<?php echo get_the_title( $new_layout_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<?php if ( 'false' !== $_POST['fusion_save_global'] && 'front' !== $this->location ) : ?>
								<?php /* translators: The global's type. */ ?>
								<div class="fusion-global-tooltip-wrapper"><span class="fusion-global-tooltip"><?php printf( esc_attr__( 'This is a global %s.', 'fusion-builder' ), esc_attr( $global_type ) ); ?></span></div>
							<?php endif; ?>
						</h4>
						<span class="fusion-layout-buttons">
							<a href="#" class="fusion-builder-layout-button-delete">
								<?php if ( 'front' === $this->location ) : ?>
									<span class="fusiona-trash-o"></span>
									<span class="screen-reader-text">
								<?php endif; ?>
								<?php esc_html_e( 'Delete', 'fusion-builder' ); ?>
								<?php if ( 'front' === $this->location ) : ?>
									</span>
								<?php endif; ?>
							</a>

							<?php $edit_link = 'front' !== $this->location ? get_edit_post_link( $new_layout_id ) : add_query_arg( 'fb-edit', '1', get_permalink( $new_layout_id ) ); ?>
							<a href="<?php echo esc_url( htmlspecialchars_decode( $edit_link ) ); ?>" class="" target="_blank" rel="noopener noreferrer">
								<?php if ( 'front' === $this->location ) : ?>
									<span class="fusiona-pen"></span>
									<span class="screen-reader-text">
								<?php endif; ?>
								<?php esc_html_e( 'Edit', 'fusion-builder' ); ?>
								<?php if ( 'front' === $this->location ) : ?>
									</span>
								<?php endif; ?>
							</a>
						</span>
					</li>

				<?php elseif ( 'fusion_template' === $post_type ) : ?>

					<li class="<?php echo esc_attr( $global_data ); ?> fusion-page-layout" data-layout_id="<?php echo esc_attr( $new_layout_id ); ?>">
						<h4 class="fusion-page-layout-title" title="<?php echo esc_attr( get_the_title( $new_layout_id ) ); ?>">
							<?php echo get_the_title( $new_layout_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</h4>
						<span class="fusion-layout-buttons">
							<a href="javascript:void(0)" class="fusion-builder-layout-button-load-dialog">
								<?php if ( 'front' === $this->location ) : ?>
									<span class="fusiona-plus"></span>
									<span class="screen-reader-text">
								<?php endif; ?>
								<?php esc_html_e( 'Load', 'fusion-builder' ); ?>
								<?php if ( 'front' === $this->location ) : ?>
									</span>
								<?php endif; ?>
								<div class="fusion-builder-load-template-dialog-container">
									<div class="fusion-builder-load-template-dialog">
										<span class="fusion-builder-save-element-title<?php echo ( 'front' === $this->location ) ? ' screen-reader-text' : ''; ?>">
											<?php esc_html_e( 'How To Load Template?', 'fusion-builder' ); ?>
										</span>
										<div class="fusion-builder-save-element-container">
											<span class="fusion-builder-layout-button-load" data-load-type="replace">
												<?php esc_html_e( 'Replace all page content', 'fusion-builder' ); ?>
											</span>
											<span class="fusion-builder-layout-button-load" data-load-type="above">
												<?php esc_html_e( 'Insert above current content', 'fusion-builder' ); ?>
											</span>
											<span class="fusion-builder-layout-button-load" data-load-type="below">
												<?php esc_html_e( 'Insert below current content', 'fusion-builder' ); ?>
											</span>
										</div>
									</div>
								</div>
							</a>
							<a href="<?php echo esc_url( htmlspecialchars_decode( get_edit_post_link( $new_layout_id ) ) ); ?>" class="" target="_blank" rel="noopener noreferrer">
								<?php if ( 'front' === $this->location ) : ?>
									<span class="fusiona-pen"></span>
									<span class="screen-reader-text">
								<?php endif; ?>
								<?php esc_html_e( 'Edit', 'fusion-builder' ); ?>
								<?php if ( 'front' === $this->location ) : ?>
									</span>
								<?php endif; ?>
							</a>
							<a href="#" class="fusion-builder-layout-button-delete">
								<?php if ( 'front' === $this->location ) : ?>
									<span class="fusiona-trash-o"></span>
									<span class="screen-reader-text">
								<?php endif; ?>
								<?php esc_html_e( 'Delete', 'fusion-builder' ); ?>
								<?php if ( 'front' === $this->location ) : ?>
									</span>
								<?php endif; ?>
							</a>
						</span>
					</li>
				<?php endif; ?>
				<?php
			}
		}

		wp_die();
	}

	/**
	 * Create a new library element, fired from library page.
	 */
	public function add_new_library_element() {
		check_admin_referer( 'fusion_library_new_element' );

		// Work out post type based on type being added.
		$post_type = isset( $_GET['fusion_library_type'] ) && 'templates' === $_GET['fusion_library_type'] ? 'fusion_template' : 'fusion_element';

		$post_type_object = get_post_type_object( $post_type );
		if ( ! current_user_can( $post_type_object->cap->edit_posts ) ) {
			return;
		}

		$category = 'elements';
		if ( isset( $_GET['fusion_library_type'] ) ) {
			$category = sanitize_text_field( wp_unslash( $_GET['fusion_library_type'] ) );
		}

		$post_content = '';
		switch ( $category ) {
			case 'sections':
				$post_content = '[fusion_builder_container][fusion_builder_row][/fusion_builder_row][/fusion_builder_container]';
				break;
			case 'columns':
				$post_content = '[fusion_builder_column type="1_1"][/fusion_builder_column]';
				break;
			case 'post_cards':
				$post_content = '[fusion_builder_column type="1_1"][/fusion_builder_column]';
				break;
		}

		$library_element = [
			'post_title'   => isset( $_GET['name'] ) ? sanitize_text_field( wp_unslash( $_GET['name'] ) ) : '',
			'post_status'  => 'publish',
			'post_type'    => $post_type,
			'post_content' => $post_content,
		];

		// Set global if checked.
		if ( 'fusion_element' === $post_type && isset( $_GET['global'] ) && sanitize_text_field( wp_unslash( $_GET['global'] ) ) ) {
			$library_element['meta_input'] = [
				'_fusion_is_global' => 'yes',
			];
		}

		$library_id = wp_insert_post( $library_element );
		if ( is_wp_error( $library_id ) ) {
			$error_string = $library_id->get_error_message();
			wp_die( esc_html( $error_string ) );
		}

		// If we are adding element, add type.
		if ( 'fusion_element' === $post_type ) {
			$library_type = wp_set_object_terms( $library_id, $category, 'element_category' );
			if ( is_wp_error( $library_type ) ) {
				$error_string = $library_type->get_error_message();
				wp_die( esc_html( $error_string ) );
			}
		}

		// Just redirect to back-end editor.  In future tie it to default editor option.
		wp_safe_redirect( get_edit_post_link( $library_id, false ) );
		die();
	}

	/**
	 * Load custom elements.
	 */
	public function load_custom_elements() {

		check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

		if ( isset( $_POST['cat'] ) && '' !== $_POST['cat'] ) {

			$cat = wp_unslash( $_POST['cat'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

			// Query elements.
			$query = fusion_cached_query(
				[
					'post_status'    => 'publish',
					'post_type'      => 'fusion_element',
					'posts_per_page' => '-1',
					'tax_query'      => [ // phpcs:ignore WordPress.DB.SlowDBQuery
						[
							'taxonomy' => 'element_category',
							'field'    => 'slug',
							'terms'    => $cat,
						],
					],
				]
			);
			?>

			<ul class="fusion-builder-all-modules">
				<?php while ( $query->have_posts() ) : ?>
					<?php $query->the_post(); ?>
					<?php global $post; ?>
					<?php $element_type = esc_attr( get_post_meta( $post->ID, '_fusion_element_type', true ) ); ?>
					<?php $element_type_class = ( isset( $element_type ) && '' !== $element_type ) ? 'fusion-element-type-' . $element_type : ''; ?>

					<li class="fusion-page-layout fusion_builder_custom_<?php echo esc_attr( $cat ); ?>_load <?php echo esc_attr( $element_type_class ); ?>" data-layout_id="<?php echo get_the_ID(); ?>">
						<h4 class="fusion_module_title" title="<?php the_title_attribute(); ?>">
							<?php the_title(); ?>
						</h4>
					</li>

				<?php endwhile; ?>

				<?php if ( 'front' === $this->location || ( 'front' !== $this->location && ! $query->have_posts() ) ) : ?>
					<p class="fusion-empty-library-message">
						<?php if ( 'front' === $this->location ) : ?>
							<span class="icon"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 297.1 248.9" style="enable-background:new 0 0 297.1 248.9;" xml:space="preserve"><style type="text/css">.st0{fill:none;stroke:#E0E0E0;stroke-width:4;stroke-miterlimit:10;}.st1{fill:none;stroke:#E0E0E0;stroke-width:4;stroke-miterlimit:10;stroke-dasharray:12.0345,4.0115;}.st2{fill:none;stroke:#E0E0E0;stroke-width:4;stroke-miterlimit:10;stroke-dasharray:12.1323,4.0441;}</style><g><g><g><path class="st0" d="M290.4,143.8c-0.6-2-1.4-3.9-2.2-5.6h0l-2.3-5.5"/><path class="st1" d="M284.3,129L237.6,18c-4-8-14.6-14.6-23.6-14.6H83.4c-9,0-19.6,6.6-23.6,14.6L9.3,138.3c-4,8-6.1,21.8-4.6,30.7l10.9,59.2c1.5,8.9,10,16.1,19,16.1h228.3c9,0,17.5-7.2,19-16.1l10.9-59.2c1-6.5,0.2-15.6-1.9-23.2"/></g><g><path class="st0" d="M266,174.5c0.4,1.9,0.5,3.9,0.2,6h0l-1,5.9"/><path class="st2" d="M264.6,190.3l-2.4,14.2c-1.3,9.3-9.6,16.8-18.5,16.8H53.9c-8.9,0-17.3-7.6-18.5-16.8l-4.2-24.1c-1.2-9.3,5-16.8,14-16.8h207c6.2,0,11.1,3.6,13.2,8.9"/></g></g></g></svg></span>
						<?php endif; ?>
						<span class="text"><?php esc_html_e( 'There are no custom elements in your library', 'fusion-builder' ); ?></span>
					</p>
				<?php endif; ?>
			</ul>

			<?php
			wp_reset_postdata();
		}

		wp_die();
	}

	/**
	 * Load custom page layout.
	 */
	public function load_layout() {

		check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

		if ( ! isset( $_POST['fusion_layout_id'] ) && '' === $_POST['fusion_layout_id'] ) {
			die( -1 );
		}

		$data      = [];
		$layout_id = (int) $_POST['fusion_layout_id'];
		$layout    = get_post( $layout_id );

		// Globals.
		if ( isset( $_POST['fusion_is_global'] ) && 'false' !== $_POST['fusion_is_global'] ) {
			$position = strpos( $layout->post_content, ']' );
			if ( false !== $position ) {
				$layout->post_content = apply_filters( 'content_edit_pre', $layout->post_content, $layout->post_content, $layout_id );
				$layout->post_content = substr_replace( $layout->post_content, ' fusion_global="' . $layout_id . '"]', $position, 1 );
			}
		}

		if ( $layout ) {

			// Set page content.
			$data['post_content'] = apply_filters( 'content_edit_pre', $layout->post_content, $layout_id );

			// Set page template.
			if ( 'fusion_template' === get_post_type( $layout_id ) ) {

				$page_template = get_post_meta( $layout_id, '_wp_page_template', true );

				if ( isset( $page_template ) && ! empty( $page_template ) ) {
					$data['page_template'] = $page_template;
				}

				$custom_css = get_post_meta( $layout_id, '_fusion_builder_custom_css', true );

				$data['post_meta'] = get_post_meta( $layout_id );

				if ( isset( $custom_css ) && ! empty( $custom_css ) ) {
					$data['custom_css'] = $custom_css;
				}
			}
		}

		$json_data = wp_json_encode( $data );

		die( $json_data ); // phpcs:ignore WordPress.Security.EscapeOutput

	}

	/**
	 * Load custom header demo.
	 */
	public function load_demo_layout() {
		check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );
		$data        = [];
		$layout_name = isset( $_POST['layout_name'] ) && '' !== $_POST['layout_name'] ? sanitize_text_field( wp_unslash( $_POST['layout_name'] ) ) : '';// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$filter      = 'fusion_builder_get_demo_headers';

		if ( false !== strpos( $layout_name, 'form' ) ) {
			$filter = 'fusion_builder_get_demo_forms';
		} elseif ( 0 === strpos( $layout_name, 'single-' ) ) {
			$filter = 'fusion_builder_get_content_sections';
		} elseif ( 0 === strpos( $layout_name, 'card-' ) ) {
			$filter = 'fusion_builder_get_post_cards';
		}

		$fusion_builder_demo_layouts = apply_filters( $filter, [] );

		if ( '' !== $layout_name && isset( $_POST['post_id'] ) && '' !== $_POST['post_id'] ) {
			$post_id = (int) $_POST['post_id'];

			if ( isset( $fusion_builder_demo_layouts[ $layout_name ] ) ) {
				$data['post_content'] = $fusion_builder_demo_layouts[ $layout_name ]['content'];

				// Add _fusion PO if it exists.
				if ( isset( $fusion_builder_demo_layouts[ $layout_name ]['_fusion'] ) ) {
					$data['_fusion'] = $fusion_builder_demo_layouts[ $layout_name ]['_fusion'];
				}
				wp_send_json_success( $data );
			}
		}
		wp_send_json_error( $fusion_builder_demo_layouts, 500 );
	}

	/**
	 * Load custom page layout.
	 */
	public function load_demo() {

		check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

		if ( ! isset( $_POST['page_name'] ) && '' === $_POST['page_name'] ) {
			die( -1 );
		}

		if ( ! isset( $_POST['demo_name'] ) && '' === $_POST['demo_name'] ) {
			die( -1 );
		}

		if ( ! isset( $_POST['post_id'] ) && '' === $_POST['post_id'] ) {
			die( -1 );
		}

		$data      = [];
		$page_name = sanitize_text_field( wp_unslash( $_POST['page_name'] ) );
		$demo_name = sanitize_text_field( wp_unslash( $_POST['demo_name'] ) );
		$post_id   = (int) $_POST['post_id'];

		$fusion_builder_demos = apply_filters( 'fusion_builder_get_demo_pages', [] );

		if ( isset( $fusion_builder_demos[ $demo_name ]['pages'][ $page_name ] ) && ! empty( $fusion_builder_demos[ $demo_name ]['pages'][ $page_name ] ) ) {

			// Set page content.
			$data['post_content'] = $fusion_builder_demos[ $demo_name ]['pages'][ $page_name ]['content'];

			// Set page template.
			$page_template = $fusion_builder_demos[ $demo_name ]['pages'][ $page_name ]['page_template'];

			if ( isset( $page_template ) && ! empty( $page_template ) ) {
				$data['page_template'] = $page_template;
			}
		}

		if ( isset( $fusion_builder_demos[ $demo_name ]['pages'][ $page_name ]['meta'] ) && ! empty( $fusion_builder_demos[ $demo_name ]['pages'][ $page_name ]['meta'] ) ) {

			$data['meta'] = $fusion_builder_demos[ $demo_name ]['pages'][ $page_name ]['meta'];
		}

		$json_data = wp_json_encode( $data );

		die( $json_data ); // phpcs:ignore WordPress.Security.EscapeOutput

	}

	/**
	 * Save custom layout.
	 */
	public function update_layout() {

		check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

		if ( isset( $_POST['fusion_layout_id'] ) && '' !== $_POST['fusion_layout_id'] && current_user_can( 'edit_post', $_POST['fusion_layout_id'] ) && apply_filters( 'fusion_global_save', true, 'ajax' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

			$layout_id  = wp_unslash( $_POST['fusion_layout_id'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$content    = isset( $_POST['fusion_layout_content'] ) ? wp_unslash( $_POST['fusion_layout_content'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$to_replace = addslashes( ' fusion_global="' . $layout_id . '"' );
			$content    = str_replace( $to_replace, '', $content );

			// Filter nested globals.
			$content = apply_filters( 'content_save_pre', $content, $content, $layout_id );

			$post = [
				'ID'           => $layout_id,
				'post_content' => $content,
			];

			wp_update_post( $post );

		}
		wp_die();
	}

	/**
	 * Get image URL from image ID.
	 */
	public function get_image_url() {

		check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

		if ( ! isset( $_POST['fusion_image_ids'] ) && '' === $_POST['fusion_image_ids'] ) {
			die( -1 );
		}

		$data      = [];
		$image_ids = wp_unslash( $_POST['fusion_image_ids'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		foreach ( $image_ids as $image_id ) {
			if ( '' !== $image_id ) {
				$image_url        = wp_get_attachment_url( $image_id, 'thumbnail' );
				$image_html       = '<div class="fusion-multi-image" data-image-id="' . $image_id . '">';
				$image_html      .= '<img src="' . $image_url . '"/>';
				$image_html      .= '<span class="fusion-multi-image-remove dashicons dashicons-no-alt"></span>';
				$image_html      .= '</div>';
				$data['images'][] = $image_html;
			}
		}
		$json_data = wp_json_encode( $data );

		die( $json_data ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * Process action for trash element.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function trash_element() {
		if ( current_user_can( 'delete_published_pages' ) ) {
			$element_ids = '';

			if ( isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$element_ids = wp_unslash( $_GET['post'] ); // phpcs:ignore WordPress.Security
			}

			if ( '' !== $element_ids ) {
				$element_ids = (array) $element_ids;
			}

			if ( ! empty( $element_ids ) ) {
				foreach ( $element_ids as $id ) {
					wp_trash_post( $id );
				}
			}
		}

		$referer = fusion_get_referer();
		if ( $referer ) {
			wp_safe_redirect( $referer );
			exit;
		}
	}

	/**
	 * Process action for restore element.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function restore_element() {
		if ( current_user_can( 'publish_pages' ) ) {
			$element_ids = '';

			if ( isset( $_GET['post'] ) ) { // // phpcs:ignore WordPress.Security.NonceVerification
				$element_ids = wp_unslash( $_GET['post'] ); // phpcs:ignore WordPress.Security
			}

			if ( '' !== $element_ids ) {
				$element_ids = (array) $element_ids;
			}

			if ( ! empty( $element_ids ) ) {
				foreach ( $element_ids as $id ) {
					wp_untrash_post( $id );
				}
			}
		}

		$referer = fusion_get_referer();
		if ( $referer ) {
			wp_safe_redirect( $referer );
			exit;
		}
	}

	/**
	 * Process action for untrash element.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function delete_element_post() {
		if ( current_user_can( 'delete_published_pages' ) ) {
			$element_ids = '';

			if ( isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$element_ids = wp_unslash( $_GET['post'] ); // phpcs:ignore WordPress.Security
			}

			if ( '' !== $element_ids ) {
				$element_ids = (array) $element_ids;
			}

			if ( ! empty( $element_ids ) ) {
				foreach ( $element_ids as $id ) {
					wp_delete_post( $id, true );
				}
			}
		}

		$referer = fusion_get_referer();
		if ( $referer ) {
			wp_safe_redirect( $referer );
			exit;
		}
	}

	/**
	 * Get the library-edit link.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param int $id       The post-ID.
	 * @return string
	 */
	public function get_library_item_edit_link( $id ) {
		if ( 'front' === $this->location ) {
			return esc_url( add_query_arg( 'fb-edit', '1', get_the_permalink( $id ) ) );
		}
		return esc_url_raw( htmlspecialchars_decode( get_edit_post_link( $id ) ) );
	}

	/**
	 * Display library content in builder.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function display_library_content() {
		global $post;
		$saved_post = $post;
		$post_type  = get_query_var( 'post_type', get_post_type() );
		$post_card  = fusion_is_post_card();
		?>
		<div class="fusion_builder_modal_settings">
			<div class="fusion-builder-modal-top-container">
				<?php if ( 'front' !== $this->location ) : ?>
					<div class="fusion-builder-modal-close fusiona-plus2"></div>
					<h2 class="fusion-builder-settings-heading"><?php esc_html_e( 'Library', 'fusion-builder' ); ?></h2>
				<?php endif; ?>
				<ul class="fusion-tabs-menu">
					<?php if ( current_theme_supports( 'fusion-builder-demos' ) && 'fusion_tb_section' !== $post_type && 'fusion_form' !== $post_type && ! $post_card ) : ?>
						<li><a href="#fusion-builder-layouts-demos" id="fusion-builder-layouts-demos-trigger"><?php esc_html_e( 'Websites', 'fusion-builder' ); ?></a></li>
					<?php endif; ?>
					<?php if ( current_theme_supports( 'fusion-builder-demos' ) && Fusion_Template_Builder()->is_template( 'header' ) ) : ?>
						<li><a href="#fusion-builder-layouts-headers" id="fusion-builder-layouts-headers-trigger"><?php esc_attr_e( 'Prebuilt Headers', 'fusion-builder' ); ?></a></li>
					<?php endif; ?>
					<?php if ( current_theme_supports( 'fusion-builder-demos' ) && Fusion_Template_Builder()->is_template( 'content' ) ) : ?>
						<li><a href="#fusion-builder-layouts-content" id="fusion-builder-layouts-content-trigger"><?php esc_attr_e( 'Prebuilt Content', 'fusion-builder' ); ?></a></li>
					<?php endif; ?>
					<?php if ( current_theme_supports( 'fusion-builder-demos' ) && $post_card ) : ?>
						<li><a href="#fusion-builder-layouts-cards" id="fusion-builder-layouts-cards-trigger"><?php esc_attr_e( 'Prebuilt Post Cards', 'fusion-builder' ); ?></a></li>
					<?php endif; ?>
					<?php if ( 'fusion_form' === $post_type ) : ?>
						<li><a href="#fusion-builder-layouts-forms" id="fusion-builder-layouts-forms-trigger"><?php esc_attr_e( 'Prebuilt Forms', 'fusion-builder' ); ?></a></li>
					<?php endif; ?>
					<?php if ( ! $post_card ) : ?>
						<li><a href="#fusion-builder-layouts-templates" id="fusion-builder-layouts-templates-trigger"><?php esc_attr_e( 'Templates', 'fusion-builder' ); ?></a></li>
						<li><a href="#fusion-builder-layouts-sections" id="fusion-builder-layouts-sections-trigger"><?php esc_attr_e( 'Containers', 'fusion-builder' ); ?></a></li>
						<li><a href="#fusion-builder-layouts-columns" id="fusion-builder-layouts-columns-trigger"><?php esc_attr_e( 'Columns', 'fusion-builder' ); ?></a></li>
					<?php endif; ?>
					<li><a href="#fusion-builder-layouts-elements" id="fusion-builder-layouts-elements-trigger"><?php esc_attr_e( 'Elements', 'fusion-builder' ); ?></a></li>
				</ul>
			</div>

			<div class="fusion-layout-tabs">
				<?php if ( current_theme_supports( 'fusion-builder-demos' ) && 'fusion_tb_section' !== $post_type ) : // Display demos tab. ?>
					<div id="fusion-builder-layouts-demos" class="fusion-builder-layouts-tab">
						<div class="fusion-builder-layouts-header">
							<?php $fusion_builder_demos = apply_filters( 'fusion_builder_get_demo_pages', [] ); ?>

							<div class="fusion-builder-layouts-header-fields fusion-demo-selection-header">
								<?php if ( $fusion_builder_demos ) : ?>
									<?php asort( $fusion_builder_demos ); ?>
									<div class="fusion-demo-selection-wrapper">
										<h2><?php echo apply_filters( 'fusion_builder_import_title', esc_html__( 'Select a prebuilt website to view the pages you can import', 'fusion-builder' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
										<select class="fusion-builder-demo-select fusion-select-field">
											<option value="" selected><?php esc_html_e( 'Select Website', 'fusion-builder' ); ?></option>
											<?php foreach ( $fusion_builder_demos as $key => $fusion_builder_demo ) : ?>
												<option value="<?php echo esc_attr( $key ); ?>">
													<?php echo esc_html( $fusion_builder_demo['category'] ); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</div>
									<div class="fusion-demo-selection-wrapper">
										<h2><?php echo apply_filters( 'fusion_builder_demo_import_link_title', esc_html__( 'Paste URL of a specific live prebuilt website page to import  ', 'fusion-builder' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
										<input type="text" class="fusion-builder-demo-page-link" name="fusion-builder-demo-page-link" id="fusion-builder-demo-page-link" placeholder="https://avada.theme-fusion.com/"/>
									</div>
								<?php endif; ?>
							</div>

							<div class="fusion-builder-layouts-header-info">
								<span class="fusion-builder-layout-info">
									<?php echo apply_filters( 'fusion_builder_import_message', esc_html__( 'Select a prebuilt website and the pages that are available to import will display.', 'fusion-builder' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</span>
							</div>
						</div>

						<h2 id="fusion-builder-demo-url-invalid" class="hidden"><?php esc_html_e( 'Unfortunately, no prebuilt website page matches the URL you entered. Please try again.', 'fusion-builder' ); ?></h2>

						<?php foreach ( $fusion_builder_demos as $key => $fusion_builder_demo ) : ?>

							<ul class="fusion-page-layouts demo-<?php echo esc_attr( $key ); ?> hidden">

								<?php if ( isset( $fusion_builder_demo['pages'] ) && ! empty( $fusion_builder_demo['pages'] ) ) : ?>
									<?php asort( $fusion_builder_demo['pages'] ); ?>
									<?php foreach ( $fusion_builder_demo['pages'] as $page_key => $page ) : ?>
										<?php $data_page_link = isset( $page['link'] ) ? str_replace( [ 'https://', 'http://', 'avada-xml/' ], '', esc_url( $page['link'] ) ) : ''; ?>
										<li class="fusion-page-layout" data-layout_id="<?php echo esc_attr( $page['name'] ); ?>" data-page-link="<?php echo esc_attr( $data_page_link ); ?>" >
											<h4 class="fusion-page-layout-title"><?php echo esc_html( ucwords( strtolower( $page['name'] ) ) ); ?></h4>
											<span class="fusion-layout-buttons">
												<a href="#" class="fusion-builder-demo-button-load" data-page-name="<?php echo esc_attr( $page_key ); ?>" data-demo-name="<?php echo esc_attr( $key ); ?>" data-post-id="<?php echo esc_attr( get_the_ID() ); ?>">
													<?php if ( 'front' === $this->location ) : ?>
														<span class="fusiona-plus"></span>
														<span class="screen-reader-text">
													<?php endif; ?>
													<?php esc_html_e( 'Load', 'fusion-builder' ); ?>
													<?php if ( 'front' === $this->location ) : ?>
														</span>
													<?php endif; ?>
												</a>
											</span>
										</li>
									<?php endforeach; ?>
								<?php else : ?>
									<li><p><?php esc_html_e( 'There are no prebuilt websites in your library', 'fusion-builder' ); ?></p></li>
								<?php endif; ?>

							</ul>

						<?php endforeach; ?>

					</div>
				<?php endif; ?>

				<?php
				// Display headers tab.
				if ( current_theme_supports( 'fusion-builder-demos' ) && Fusion_Template_Builder()->is_template( 'header' ) ) :
					?>
					<div id="fusion-builder-layouts-headers" class="fusion-builder-layouts-tab">
						<div class="fusion-builder-layouts-header ">
							<div class="fusion-builder-layouts-header-info">
								<h2><?php esc_html_e( 'Prebuilt Header Layout Sections', 'fusion-builder' ); ?></h2>
								<span class="fusion-builder-layout-info"><?php esc_html_e( 'Click to import one of our prebuilt header layout sections.  Please note, the visual appearance may vary depending on your global options.  The menu content will also depend on the menus already created on your site and are not included with the import.', 'fusion-builder' ); ?></span>
							</div>
						</div>
						<ul class="fusion-page-layouts">
							<?php $fusion_builder_demo_headers = apply_filters( 'fusion_builder_get_demo_headers', [] ); ?>
							<?php if ( ! empty( $fusion_builder_demo_headers ) ) : ?>
								<?php asort( $fusion_builder_demo_headers ); ?>
								<?php foreach ( $fusion_builder_demo_headers as $header_key => $header ) : ?>
									<li class="fusion-page-layout">
										<?php if ( isset( $header['image'] ) ) : ?>
											<?php
											$position_css   = isset( $header['position'] ) ? 'background-position:' . $header['position'] . ' 0;' : ';';
											$additional_css = isset( $header['css'] ) ? $header['css'] : '';
											?>
											<a href="#" class="fusion-builder-demo-layout-button-load" data-layout-name="<?php echo esc_attr( $header_key ); ?>" data-post-id="<?php echo esc_attr( get_the_ID() ); ?>">
												<div class="preview-image" style="background-image: url( '<?php echo esc_url( $header['image'] ); ?>' );<?php echo esc_html( $position_css ); ?><?php echo esc_html( $additional_css ); ?>" data-key="<?php echo esc_attr( $header_key ); ?>" aria-label="<?php echo esc_attr( ucwords( strtolower( $header['name'] ) ) ); ?>">
													<div class="fusion-layout-info">
														<span class="button button-primary"><?php echo esc_html( ucwords( strtolower( $header['name'] ) ) ); ?></span>
													</div>
												</div>
											</a>
										<?php endif; ?>

									</li>
								<?php endforeach; ?>
							<?php else : ?>
								<li><p><?php esc_html_e( 'There are no headers in your library', 'fusion-builder' ); ?></p></li>
							<?php endif; ?>
						</ul>
					</div>
				<?php endif; ?>

				<?php
				// Display content layout section tab.
				if ( current_theme_supports( 'fusion-builder-demos' ) && Fusion_Template_Builder()->is_template( 'content' ) ) :
					?>
					<div id="fusion-builder-layouts-content" class="fusion-builder-layouts-tab fusion-builder-grid-layout">
						<div class="fusion-builder-layouts-header ">
							<div class="fusion-builder-layouts-header-info">
								<h2><?php esc_html_e( 'Prebuilt Content Layout Sections', 'fusion-builder' ); ?></h2>
								<span class="fusion-builder-layout-info"><?php esc_html_e( 'Click to import one of our prebuilt content layout sections.  Please note, the visual appearance and content of these layout sections will depend on the context. For example, Blog Post 1 is ideally used as a layout for a single post.  For a realistic preview in Avada Live please ensure you have set the View Dynamic Content As option for this layout section.', 'fusion-builder' ); ?></span>
							</div>
						</div>
						<ul class="fusion-page-layouts">
							<?php $fusion_builder_demo_content = apply_filters( 'fusion_builder_get_content_sections', [] ); ?>
							<?php if ( ! empty( $fusion_builder_demo_content ) ) : ?>
								<?php asort( $fusion_builder_demo_content ); ?>
								<?php foreach ( $fusion_builder_demo_content as $content_key => $content ) : ?>
									<li class="fusion-page-layout">
										<?php if ( isset( $content['image'] ) ) : ?>
											<a href="#" class="fusion-builder-demo-layout-button-load" data-layout-name="<?php echo esc_attr( $content_key ); ?>" data-post-id="<?php echo esc_attr( get_the_ID() ); ?>">
												<div class="preview">
													<img src="<?php echo esc_url( $content['image'] ); ?>" alt="<?php echo esc_html( ucwords( strtolower( $content['name'] ) ) ); ?>" data-src="<?php echo esc_url( $content['image'] ); ?>" data-alt="<?php echo esc_html( ucwords( strtolower( $content['name'] ) ) ); ?>">
												</div>
												<div class="bar">
													<span class="fusion_module_title"><?php echo esc_html( ucwords( strtolower( $content['name'] ) ) ); ?></span>
												</div>
											</a>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							<?php else : ?>
								<li><p><?php esc_html_e( 'There are no content layout sections in your library', 'fusion-builder' ); ?></p></li>
							<?php endif; ?>
						</ul>
					</div>
				<?php endif; ?>

				<?php
				// Display content layout section tab.
				if ( current_theme_supports( 'fusion-builder-demos' ) && $post_card ) :
					?>
					<div id="fusion-builder-layouts-cards" class="fusion-builder-layouts-tab fusion-builder-grid-layout">
						<div class="fusion-builder-layouts-header ">
							<div class="fusion-builder-layouts-header-info">
								<h2><?php esc_html_e( 'Prebuilt Post Cards', 'fusion-builder' ); ?></h2>
								<span class="fusion-builder-layout-info"><?php esc_html_e( 'Click to import one of our prebuilt post cards.  Please note, the visual appearance and content of these post cards will depend on the context. For example, Blog Post Card 1 is ideally used as a post card for blog posts and with a post cards element set to grid. For a realistic preview in Avada Live please ensure you have set the View Dynamic Content As option for this post card.', 'fusion-builder' ); ?></span>
							</div>
						</div>
						<ul class="fusion-page-layouts">
							<?php $fusion_builder_post_cards = apply_filters( 'fusion_builder_get_post_cards', [] ); ?>
							<?php if ( ! empty( $fusion_builder_post_cards ) ) : ?>
								<?php ksort( $fusion_builder_post_cards ); ?>
								<?php foreach ( $fusion_builder_post_cards as $content_key => $content ) : ?>
									<li class="fusion-page-layout">
										<?php if ( isset( $content['image'] ) ) : ?>
											<a href="#" class="fusion-builder-demo-layout-button-load" data-layout-name="<?php echo esc_attr( $content_key ); ?>" data-post-id="<?php echo esc_attr( get_the_ID() ); ?>">
												<div class="preview">
													<img src="<?php echo esc_url( $content['image'] ); ?>" alt="<?php echo esc_html( ucwords( strtolower( $content['name'] ) ) ); ?>" data-src="<?php echo esc_url( $content['image'] ); ?>" data-alt="<?php echo esc_html( ucwords( strtolower( $content['name'] ) ) ); ?>">
												</div>
												<div class="bar">
													<span class="fusion_module_title"><?php echo esc_html( ucwords( strtolower( $content['name'] ) ) ); ?></span>
												</div>
											</a>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							<?php else : ?>
								<li><p><?php esc_html_e( 'There are no post cards in your library', 'fusion-builder' ); ?></p></li>
							<?php endif; ?>
						</ul>
					</div>
				<?php endif; ?>

				<?php
				// Display forms tab.
				if ( 'fusion_form' === $post_type ) :
					?>
					<div id="fusion-builder-layouts-forms" class="fusion-builder-layouts-tab fusion-builder-grid-layout">
						<ul class="fusion-page-layouts">
							<?php $fusion_builder_demo_forms = apply_filters( 'fusion_builder_get_demo_forms', [] ); ?>
							<?php if ( ! empty( $fusion_builder_demo_forms ) ) : ?>
								<?php asort( $fusion_builder_demo_forms ); ?>
								<?php foreach ( $fusion_builder_demo_forms as $form_key => $form ) : ?>
									<li class="fusion-page-layout">
										<?php if ( isset( $form['image'] ) ) : ?>
											<a href="#" class="fusion-builder-demo-layout-button-load" data-layout-name="<?php echo esc_attr( $form_key ); ?>" data-post-id="<?php echo esc_attr( get_the_ID() ); ?>">
												<div class="preview">
													<img src="<?php echo esc_url( $form['image'] ); ?>" alt="<?php echo esc_html( str_replace( 'Rsvp', 'RSVP', ucwords( strtolower( $form['name'] ) ) ) ); ?>" data-src="<?php echo esc_url( $form['image'] ); ?>" data-alt="<?php echo esc_html( ucwords( strtolower( $form['name'] ) ) ); ?>">
												</div>
												<div class="bar">
													<span class="fusion_module_title"><?php echo esc_html( str_replace( 'Rsvp', 'RSVP', ucwords( strtolower( $form['name'] ) ) ) ); ?></span>
												</div>
											</a>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							<?php else : ?>
								<li><p><?php esc_html_e( 'There are no headers in your library', 'fusion-builder' ); ?></p></li>
							<?php endif; ?>
						</ul>
					</div>
				<?php endif; ?>

				<?php
				// Display containers tab.
				?>

				<div id="fusion-builder-layouts-sections" class="fusion-builder-layouts-tab">

					<div class="fusion-builder-layouts-header">
						<div class="fusion-builder-layouts-header-fields fusion-builder-layouts-header-element-fields"></div>
						<div class="fusion-builder-layouts-header-info">
							<h2><?php esc_html_e( 'Saved Containers', 'fusion-builder' ); ?></h2>
							<span class="fusion-builder-layout-info">
								<?php
								printf(
									/* translators: The "Fusion Documentation" link. */
									__( 'Manage your saved containers. Containers cannot be inserted from the library window. The globe icon indicates the element is a <a href="%s" target="_blank">global element</a>.', 'fusion-builder' ), // phpcs:ignore WordPress.Security.EscapeOutput
									'https://theme-fusion.com/documentation/fusion-builder/fusion-builder-library/fusion-builder-global-elements/'
								);
								?>
							</span>
						</div>
					</div>

					<?php
					// Query containers.
					$query = fusion_cached_query(
						[
							'post_status'    => 'publish',
							'post_type'      => 'fusion_element',
							'posts_per_page' => '-1',
							'tax_query'      => [ // phpcs:ignore WordPress.DB.SlowDBQuery
								[
									'taxonomy' => 'element_category',
									'field'    => 'slug',
									'terms'    => 'sections',
								],
							],
						]
					);
					?>

					<ul class="fusion-page-layouts fusion-layout-sections">

						<?php
						while ( $query->have_posts() ) :
							$query->the_post();
							$is_global = ( 'yes' === get_post_meta( get_the_ID(), '_fusion_is_global', true ) ? 'fusion-global' : '' );
							global $post;
							?>

							<li class="<?php echo esc_attr( $is_global ); ?> fusion-page-layout" data-layout_id="<?php echo get_the_ID(); ?>">
								<h4 class="fusion-page-layout-title" title="<?php the_title(); ?>">
									<?php the_title(); ?>
									<?php if ( '' !== $is_global && 'front' !== $this->location ) : ?>
										<div class="fusion-global-tooltip-wrapper"><span class="fusion-global-tooltip"><?php esc_html_e( 'This is a global container.', 'fusion-builder' ); ?></span></div>
									<?php endif; ?>
								</h4>
								<span class="fusion-layout-buttons">
									<a href="#" class="fusion-builder-layout-button-delete">
										<?php if ( 'front' === $this->location ) : ?>
											<span class="fusiona-trash-o"></span>
											<span class="screen-reader-text">
										<?php endif; ?>
										<?php esc_html_e( 'Delete', 'fusion-builder' ); ?>
										<?php if ( 'front' === $this->location ) : ?>
											</span>
										<?php endif; ?>
									</a>
									<a href="<?php echo $this->get_library_item_edit_link( $post->ID ); // phpcs:ignore WordPress.Security.EscapeOutput ?>" class="fusion-builder-layout-button-edit" target="_blank">
										<?php if ( 'front' === $this->location ) : ?>
											<span class="fusiona-pen"></span>
											<span class="screen-reader-text">
										<?php endif; ?>
										<?php esc_html_e( 'Edit', 'fusion-builder' ); ?>
										<?php if ( 'front' === $this->location ) : ?>
											</span>
										<?php endif; ?>
									</a>
								</span>
							</li>
						<?php endwhile; ?>
						<?php if ( 'front' === $this->location || ( 'front' !== $this->location && ! $query->have_posts() ) ) : ?>
							<p class="fusion-empty-library-message">
								<?php if ( 'front' === $this->location ) : ?>
									<span class="icon"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 297.1 248.9" style="enable-background:new 0 0 297.1 248.9;" xml:space="preserve"><style type="text/css">.st0{fill:none;stroke:#E0E0E0;stroke-width:4;stroke-miterlimit:10;}.st1{fill:none;stroke:#E0E0E0;stroke-width:4;stroke-miterlimit:10;stroke-dasharray:12.0345,4.0115;}.st2{fill:none;stroke:#E0E0E0;stroke-width:4;stroke-miterlimit:10;stroke-dasharray:12.1323,4.0441;}</style><g><g><g><path class="st0" d="M290.4,143.8c-0.6-2-1.4-3.9-2.2-5.6h0l-2.3-5.5"/><path class="st1" d="M284.3,129L237.6,18c-4-8-14.6-14.6-23.6-14.6H83.4c-9,0-19.6,6.6-23.6,14.6L9.3,138.3c-4,8-6.1,21.8-4.6,30.7l10.9,59.2c1.5,8.9,10,16.1,19,16.1h228.3c9,0,17.5-7.2,19-16.1l10.9-59.2c1-6.5,0.2-15.6-1.9-23.2"/></g><g><path class="st0" d="M266,174.5c0.4,1.9,0.5,3.9,0.2,6h0l-1,5.9"/><path class="st2" d="M264.6,190.3l-2.4,14.2c-1.3,9.3-9.6,16.8-18.5,16.8H53.9c-8.9,0-17.3-7.6-18.5-16.8l-4.2-24.1c-1.2-9.3,5-16.8,14-16.8h207c6.2,0,11.1,3.6,13.2,8.9"/></g></g></g></svg></span>
								<?php endif; ?>
								<span class="text"><?php esc_html_e( 'There are no custom containers in your library', 'fusion-builder' ); ?></span>
							</p>
						<?php endif; ?>
					</ul>

					<?php
					$post = $saved_post ? $saved_post : $post;
					wp_reset_postdata();
					?>

				</div>

				<?php
				// Display columns tab.
				?>

				<div id="fusion-builder-layouts-columns" class="fusion-builder-layouts-tab">

					<div class="fusion-builder-layouts-header">
						<div class="fusion-builder-layouts-header-fields fusion-builder-layouts-header-element-fields"></div>
						<div class="fusion-builder-layouts-header-info">
							<h2><?php esc_html_e( 'Saved Columns', 'fusion-builder' ); ?></h2>
							<span class="fusion-builder-layout-info">
								<?php
								printf(
									/* translators: The "Fusion Documentation" link. */
									__( 'Manage your saved columns. Columns cannot be inserted from the library window and they must always go inside a container. The globe icon indicates the element is a <a href="%s" target="_blank">global element</a>.', 'fusion-builder' ), // phpcs:ignore WordPress.Security.EscapeOutput
									'https://theme-fusion.com/documentation/fusion-builder/fusion-builder-library/fusion-builder-global-elements/'
								);
								?>
							</span>
						</div>
					</div>

					<?php
					// Query columns.
					$query = fusion_cached_query(
						[
							'post_status'    => 'publish',
							'post_type'      => 'fusion_element',
							'posts_per_page' => '-1',
							'tax_query'      => [ // phpcs:ignore WordPress.DB.SlowDBQuery
								[
									'taxonomy' => 'element_category',
									'field'    => 'slug',
									'terms'    => 'columns',
								],
							],
						]
					);
					?>

					<ul class="fusion-page-layouts fusion-layout-columns">

						<?php while ( $query->have_posts() ) : ?>
							<?php
							$query->the_post();
							$is_global = ( 'yes' === get_post_meta( get_the_ID(), '_fusion_is_global', true ) ? 'fusion-global' : '' );
							global $post;
							?>

							<li class="<?php echo esc_attr( $is_global ); ?> fusion-page-layout" data-layout_id="<?php echo get_the_ID(); ?>">
								<h4 class="fusion-page-layout-title" title="<?php the_title(); ?>">
									<?php the_title(); ?>
									<?php if ( '' !== $is_global && 'front' !== $this->location ) : ?>
										<div class="fusion-global-tooltip-wrapper"><span class="fusion-global-tooltip"><?php esc_html_e( 'This is a global column.', 'fusion-builder' ); ?></span></div>
									<?php endif; ?>
								</h4>
								<span class="fusion-layout-buttons">
									<a href="<?php echo $this->get_library_item_edit_link( $post->ID ); // phpcs:ignore WordPress.Security.EscapeOutput ?>" class="fusion-builder-layout-button-edit" target="_blank">
										<?php if ( 'front' === $this->location ) : ?>
											<span class="fusiona-pen"></span>
											<span class="screen-reader-text">
										<?php endif; ?>
										<?php esc_html_e( 'Edit', 'fusion-builder' ); ?>
										<?php if ( 'front' === $this->location ) : ?>
											</span>
										<?php endif; ?>
									</a>
									<a href="#" class="fusion-builder-layout-button-delete">
										<?php if ( 'front' === $this->location ) : ?>
											<span class="fusiona-trash-o"></span>
											<span class="screen-reader-text">
										<?php endif; ?>
										<?php esc_html_e( 'Delete', 'fusion-builder' ); ?>
										<?php if ( 'front' === $this->location ) : ?>
											</span>
										<?php endif; ?>
									</a>
								</span>
							</li>
						<?php endwhile; ?>

						<?php if ( 'front' === $this->location || ( 'front' !== $this->location && ! $query->have_posts() ) ) : ?>
							<p class="fusion-empty-library-message">
								<?php if ( 'front' === $this->location ) : ?>
									<span class="icon"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 297.1 248.9" style="enable-background:new 0 0 297.1 248.9;" xml:space="preserve"><style type="text/css">.st0{fill:none;stroke:#E0E0E0;stroke-width:4;stroke-miterlimit:10;}.st1{fill:none;stroke:#E0E0E0;stroke-width:4;stroke-miterlimit:10;stroke-dasharray:12.0345,4.0115;}.st2{fill:none;stroke:#E0E0E0;stroke-width:4;stroke-miterlimit:10;stroke-dasharray:12.1323,4.0441;}</style><g><g><g><path class="st0" d="M290.4,143.8c-0.6-2-1.4-3.9-2.2-5.6h0l-2.3-5.5"/><path class="st1" d="M284.3,129L237.6,18c-4-8-14.6-14.6-23.6-14.6H83.4c-9,0-19.6,6.6-23.6,14.6L9.3,138.3c-4,8-6.1,21.8-4.6,30.7l10.9,59.2c1.5,8.9,10,16.1,19,16.1h228.3c9,0,17.5-7.2,19-16.1l10.9-59.2c1-6.5,0.2-15.6-1.9-23.2"/></g><g><path class="st0" d="M266,174.5c0.4,1.9,0.5,3.9,0.2,6h0l-1,5.9"/><path class="st2" d="M264.6,190.3l-2.4,14.2c-1.3,9.3-9.6,16.8-18.5,16.8H53.9c-8.9,0-17.3-7.6-18.5-16.8l-4.2-24.1c-1.2-9.3,5-16.8,14-16.8h207c6.2,0,11.1,3.6,13.2,8.9"/></g></g></g></svg></span>
								<?php endif; ?>
								<span class="text"><?php esc_html_e( 'There are no custom columns in your library', 'fusion-builder' ); ?></span>
							</p>
						<?php endif; ?>

					</ul>

					<?php
					$post = $saved_post ? $saved_post : $post;
					wp_reset_postdata();
					?>

				</div>

				<?php
				// Display elements tab.
				?>

				<div id="fusion-builder-layouts-elements" class="fusion-builder-layouts-tab">

					<div class="fusion-builder-layouts-header">
						<div class="fusion-builder-layouts-header-fields fusion-builder-layouts-header-element-fields"></div>
						<div class="fusion-builder-layouts-header-info">
							<h2><?php esc_html_e( 'Saved Elements', 'fusion-builder' ); ?></h2>
							<span class="fusion-builder-layout-info">
								<?php

								printf(
									/* translators: The "Fusion Documentation" link. */
									__( 'Manage your saved elements. Elements cannot be inserted from the library window and they must always go inside a column. The globe icon indicates the element is a <a href="%s" target="_blank">global element</a>.', 'fusion-builder' ), // phpcs:ignore WordPress.Security.EscapeOutput
									'https://theme-fusion.com/documentation/fusion-builder/fusion-builder-library/fusion-builder-global-elements/'
								);
								?>
							</span>
						</div>
					</div>

					<?php
					// Query elements.
					$query = fusion_cached_query(
						[
							'post_status'    => 'publish',
							'post_type'      => 'fusion_element',
							'posts_per_page' => '-1',
							'tax_query'      => [ // phpcs:ignore WordPress.DB.SlowDBQuery
								[
									'taxonomy' => 'element_category',
									'field'    => 'slug',
									'terms'    => 'elements',
								],
							],
						]
					);
					?>

					<ul class="fusion-page-layouts fusion-layout-elements">

						<?php
						while ( $query->have_posts() ) :
							$query->the_post();
							$is_global = ( 'yes' === get_post_meta( get_the_ID(), '_fusion_is_global', true ) ? 'fusion-global' : '' );
							global $post;
							$element_type = esc_attr( get_post_meta( $post->ID, '_fusion_element_type', true ) );
							?>

							<li class="<?php echo esc_attr( $is_global ); ?> fusion-page-layout" data-layout_type="<?php echo esc_attr( $element_type ); ?>" data-layout_id="<?php echo esc_attr( get_the_ID() ); ?>">
								<h4 class="fusion-page-layout-title" title="<?php the_title(); ?>">
									<?php the_title(); ?>
									<?php if ( '' !== $is_global && 'front' !== $this->location ) : ?>
										<div class="fusion-global-tooltip-wrapper">
											<span class="fusion-global-tooltip"><?php esc_html_e( 'This is a global element.', 'fusion-builder' ); ?></span>
										</div>
									<?php endif; ?>
								</h4>
								<span class="fusion-layout-buttons">
									<a href="<?php echo $this->get_library_item_edit_link( $post->ID ); // phpcs:ignore WordPress.Security.EscapeOutput ?>" class="fusion-builder-layout-button-edit" target="_blank">
										<?php if ( 'front' === $this->location ) : ?>
											<span class="fusiona-pen"></span>
											<span class="screen-reader-text">
										<?php endif; ?>
										<?php esc_html_e( 'Edit', 'fusion-builder' ); ?>
										<?php if ( 'front' === $this->location ) : ?>
											</span>
										<?php endif; ?>
									</a>
									<a href="#" class="fusion-builder-layout-button-delete">
										<?php if ( 'front' === $this->location ) : ?>
											<span class="fusiona-trash-o"></span>
											<span class="screen-reader-text">
										<?php endif; ?>
										<?php esc_html_e( 'Delete', 'fusion-builder' ); ?>
										<?php if ( 'front' === $this->location ) : ?>
											</span>
										<?php endif; ?>
									</a>
								</span>
							</li>
						<?php endwhile; ?>

						<?php if ( 'front' === $this->location || ( 'front' !== $this->location && ! $query->have_posts() ) ) : ?>
							<p class="fusion-empty-library-message">
								<?php if ( 'front' === $this->location ) : ?>
									<span class="icon"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 297.1 248.9" style="enable-background:new 0 0 297.1 248.9;" xml:space="preserve"><style type="text/css">.st0{fill:none;stroke:#E0E0E0;stroke-width:4;stroke-miterlimit:10;}.st1{fill:none;stroke:#E0E0E0;stroke-width:4;stroke-miterlimit:10;stroke-dasharray:12.0345,4.0115;}.st2{fill:none;stroke:#E0E0E0;stroke-width:4;stroke-miterlimit:10;stroke-dasharray:12.1323,4.0441;}</style><g><g><g><path class="st0" d="M290.4,143.8c-0.6-2-1.4-3.9-2.2-5.6h0l-2.3-5.5"/><path class="st1" d="M284.3,129L237.6,18c-4-8-14.6-14.6-23.6-14.6H83.4c-9,0-19.6,6.6-23.6,14.6L9.3,138.3c-4,8-6.1,21.8-4.6,30.7l10.9,59.2c1.5,8.9,10,16.1,19,16.1h228.3c9,0,17.5-7.2,19-16.1l10.9-59.2c1-6.5,0.2-15.6-1.9-23.2"/></g><g><path class="st0" d="M266,174.5c0.4,1.9,0.5,3.9,0.2,6h0l-1,5.9"/><path class="st2" d="M264.6,190.3l-2.4,14.2c-1.3,9.3-9.6,16.8-18.5,16.8H53.9c-8.9,0-17.3-7.6-18.5-16.8l-4.2-24.1c-1.2-9.3,5-16.8,14-16.8h207c6.2,0,11.1,3.6,13.2,8.9"/></g></g></g></svg></span>
								<?php endif; ?>
								<span class="text"><?php esc_html_e( 'There are no custom elements in your library', 'fusion-builder' ); ?></span>
							</p>
						<?php endif; ?>
					</ul>

					<?php
					$post = $saved_post ? $saved_post : $post;
					wp_reset_postdata();
					?>

				</div>

				<?php
				// Display templates tab.
				?>
				<div id="fusion-builder-layouts-templates" class="fusion-builder-layouts-tab">
					<div class="fusion-builder-layouts-header">

						<div class="fusion-builder-layouts-header-fields">
							<a href="#" class="fusion-builder-layout-button-save"><?php esc_html_e( 'Save Template', 'fusion-builder' ); ?></a>
							<input type="text" id="new_template_name" value="" placeholder="<?php esc_attr_e( 'Custom template name', 'fusion-builder' ); ?>" />
						</div>

						<div class="fusion-builder-layouts-header-info">
							<h2><?php esc_html_e( 'Save current page layout as a template', 'fusion-builder' ); ?></h2>
							<span class="fusion-builder-layout-info"><?php esc_html_e( 'Enter a name for your template and click the Save button. This will save the entire page layout, page template from the page attributes box, custom CSS, and Avada Page Options. IMPORTANT: When loading a saved template through the "Replace All Content" option, everything will load, including the page template and Avada Page Options. When inserting above or below existing content only the saved content will be added.', 'fusion-builder' ); ?></span>
						</div>

					</div>

					<?php
					// Query page templates.
					$query = fusion_cached_query(
						[
							'post_status'    => 'publish',
							'post_type'      => 'fusion_template',
							'posts_per_page' => '-1',
						]
					);
					?>

					<ul class="fusion-page-layouts fusion-layout-templates">

						<?php while ( $query->have_posts() ) : ?>
							<?php $query->the_post(); ?>
							<?php global $post; ?>
							<li class="fusion-page-layout" data-layout_id="<?php echo get_the_ID(); ?>">
								<h4 class="fusion-page-layout-title" title="<?php the_title_attribute(); ?>">
									<?php the_title(); ?>
								</h4>
								<span class="fusion-layout-buttons">
									<a href="javascript:void(0)" class="fusion-builder-layout-button-load-dialog">
										<?php if ( 'front' === $this->location ) : ?>
											<span class="fusiona-plus"></span>
											<span class="screen-reader-text"><?php esc_html_e( 'Load' ); ?></span>
											<div class="fusion-builder-load-template-dialog-container">
												<div class="fusion-builder-load-template-dialog">
													<?php if ( 'front' === $this->location ) : ?>
														<span class="screen-reader-text">
													<?php endif; ?>
													<span class="fusion-builder-save-element-title"><?php esc_html_e( 'How To Load Template?', 'fusion-builder' ); ?></span>
													<?php if ( 'front' === $this->location ) : ?>
														</span>
													<?php endif; ?>
													<div class="fusion-builder-save-element-container">
														<span class="fusion-builder-layout-button-load" data-load-type="replace">
															<?php esc_html_e( 'Replace all page content', 'fusion-builder' ); ?>
														</span>
														<span class="fusion-builder-layout-button-load" data-load-type="above">
															<?php esc_html_e( 'Insert above current content', 'fusion-builder' ); ?>
														</span>
														<span class="fusion-builder-layout-button-load" data-load-type="below">
															<?php esc_html_e( 'Insert below current content', 'fusion-builder' ); ?>
														</span>
													</div>
												</div>
											</div>
										<?php else : ?>
											<?php
											printf(
												/* translators: content. */
												esc_html__( 'Load %s', 'fusion-builder' ),
												'<div class="fusion-builder-load-template-dialog-container"><div class="fusion-builder-load-template-dialog"><span class="fusion-builder-save-element-title">' . esc_html__( 'How To Load Template?', 'fusion-builder' ) . '</span><div class="fusion-builder-save-element-container"><span class="fusion-builder-layout-button-load" data-load-type="replace">' . esc_attr__( 'Replace all page content', 'fusion-builder' ) . '</span><span class="fusion-builder-layout-button-load" data-load-type="above">' . esc_attr__( 'Insert above current content', 'fusion-builder' ) . '</span><span class="fusion-builder-layout-button-load" data-load-type="below">' . esc_attr__( 'Insert below current content', 'fusion-builder' ) . '</span></div></div></div>'
											);
											?>
										<?php endif; ?>
									</a>
									<a href="<?php echo esc_url( htmlspecialchars_decode( get_edit_post_link( $post->ID ) ) ); ?>" class="" target="_blank" rel="noopener noreferrer">
										<?php if ( 'front' === $this->location ) : ?>
											<span class="fusiona-pen"></span>
											<span class="screen-reader-text">
										<?php endif; ?>
										<?php esc_html_e( 'Edit', 'fusion-builder' ); ?>
										<?php if ( 'front' === $this->location ) : ?>
											</span>
										<?php endif; ?>
									</a>
									<a href="#" class="fusion-builder-layout-button-delete">
										<?php if ( 'front' === $this->location ) : ?>
											<span class="fusiona-trash-o"></span>
											<span class="screen-reader-text">
										<?php endif; ?>
										<?php esc_html_e( 'Delete', 'fusion-builder' ); ?>
										<?php if ( 'front' === $this->location ) : ?>
											</span>
										<?php endif; ?>
									</a>
								</span>
							</li>
						<?php endwhile; ?>

						<?php if ( 'front' === $this->location || ( 'front' !== $this->location && ! $query->have_posts() ) ) : ?>
							<p class="fusion-empty-library-message">
								<?php if ( 'front' === $this->location ) : ?>
									<span class="icon"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 297.1 248.9" style="enable-background:new 0 0 297.1 248.9;" xml:space="preserve"><style type="text/css">.st0{fill:none;stroke:#E0E0E0;stroke-width:4;stroke-miterlimit:10;}.st1{fill:none;stroke:#E0E0E0;stroke-width:4;stroke-miterlimit:10;stroke-dasharray:12.0345,4.0115;}.st2{fill:none;stroke:#E0E0E0;stroke-width:4;stroke-miterlimit:10;stroke-dasharray:12.1323,4.0441;}</style><g><g><g><path class="st0" d="M290.4,143.8c-0.6-2-1.4-3.9-2.2-5.6h0l-2.3-5.5"/><path class="st1" d="M284.3,129L237.6,18c-4-8-14.6-14.6-23.6-14.6H83.4c-9,0-19.6,6.6-23.6,14.6L9.3,138.3c-4,8-6.1,21.8-4.6,30.7l10.9,59.2c1.5,8.9,10,16.1,19,16.1h228.3c9,0,17.5-7.2,19-16.1l10.9-59.2c1-6.5,0.2-15.6-1.9-23.2"/></g><g><path class="st0" d="M266,174.5c0.4,1.9,0.5,3.9,0.2,6h0l-1,5.9"/><path class="st2" d="M264.6,190.3l-2.4,14.2c-1.3,9.3-9.6,16.8-18.5,16.8H53.9c-8.9,0-17.3-7.6-18.5-16.8l-4.2-24.1c-1.2-9.3,5-16.8,14-16.8h207c6.2,0,11.1,3.6,13.2,8.9"/></g></g></g></svg></span>
								<?php endif; ?>
								<span class="text"><?php esc_html_e( 'There are no custom templates in your library', 'fusion-builder' ); ?></span>
							</p>
						<?php endif; ?>

						<?php wp_reset_postdata(); ?>

					</ul>

				</div>

			</div>

		</div>

		<?php
		if ( $saved_post ) {
			$post = $saved_post;
		}
	}

	/**
	 * Template content override.
	 *
	 * @access public
	 * @since 2.2
	 * @param array $overrides The overrides array.
	 * @return array
	 */
	public function set_template_content_override( $overrides ) {
		global $post;

		if ( 'fusion_tb_section' === get_post_type() ) {

			if ( has_term( [ 'footer', 'page_title_bar', 'header' ], 'fusion_tb_category' ) ) {
				$_post   = $post;
				$builder = Fusion_Builder_Front::get_instance();

				do_action( 'fusion_resume_live_editor_filter' );

				if ( has_term( 'footer', 'fusion_tb_category' ) ) {
					$_post->post_content = fusion_is_preview_frame() ? $builder->front_end_content( $_post->post_content ) : $_post->post_content;
					$overrides['footer'] = $_post;

				} elseif ( has_term( 'page_title_bar', 'fusion_tb_category' ) ) {
					$_post->post_content         = fusion_is_preview_frame() ? $builder->front_end_content( $_post->post_content ) : $_post->post_content;
					$overrides['page_title_bar'] = $_post;
				} elseif ( has_term( 'header', 'fusion_tb_category' ) ) {
					$_post->post_content = fusion_is_preview_frame() ? $builder->front_end_content( $_post->post_content ) : $_post->post_content;
					$overrides['header'] = $_post;
				}

				// Prevent main content being filtered.
				remove_filter( 'the_content', [ $builder, 'front_end_content' ], 99 );
				remove_filter( 'body_class', [ $builder, 'body_class' ] );
				remove_filter( 'do_shortcode_tag', [ $builder, 'create_shortcode_contents_map' ], 10, 4 );

				// Create a dummy post to use as content.
				if ( ! isset( $overrides['content'] ) ) {
					$overrides['content'] = Fusion_Dummy_Post::get_dummy_post();
				}
			} else {
				// Reset the content override because we are editing content directly.
				if ( isset( $overrides['content'] ) ) {
					$overrides['content'] = false;
				}
			}
		}

		return $overrides;
	}

	/**
	 * Copies taxonomies.
	 *
	 * @access public
	 * @param array $taxonomies Taxonomies.
	 * @param mixed $sync Whether to sync.
	 * @return array
	 * @since 3.1
	 */
	public function copy_taxonomies( $taxonomies, $sync ) {
		$taxonomies[] = 'element_category';
		return $taxonomies;
	}

	/**
	 * Saves a new element.
	 *
	 * @access public
	 * @since 3.3
	 */
	public function clone_library_element() {

		if ( ! ( isset( $_GET['item'] ) || isset( $_POST['item'] ) || ( isset( $_REQUEST['action'] ) && 'clone_library_element' === $_REQUEST['action'] ) ) ) { // phpcs:ignore WordPress.Security
			wp_die( esc_attr__( 'No element to clone.', 'fusion-builder' ) );
		}

		if ( isset( $_REQUEST['_fusion_library_clone_nonce'] ) && check_admin_referer( 'clone_element', '_fusion_library_clone_nonce' ) && current_user_can( 'edit_others_posts' ) ) {

			// Get the post being copied.
			$id   = isset( $_GET['item'] ) ? wp_unslash( $_GET['item'] ) : wp_unslash( $_POST['item'] ); // phpcs:ignore WordPress.Security
			$post = get_post( $id );

			// Copy the section and insert it.
			if ( isset( $post ) && $post ) {
				$this->clone_element( $post );

				// Redirect to the all sections screen.
				wp_safe_redirect( admin_url( 'admin.php?page=avada-library' ) );

				exit;

			} else {

				/* translators: The ID not found. */
				wp_die( sprintf( esc_attr__( 'Cloning failed. Element not found. ID: %s', 'fusion-builder' ), htmlspecialchars( $id ) ) ); // phpcs:ignore WordPress.Security
			}
		}
	}

	/**
	 * Clones a section.
	 *
	 * @access public
	 * @since 3.3
	 * @param object $post The post object.
	 * @return int
	 */
	public function clone_element( $post ) {

		// Ignore revisions.
		if ( 'revision' === $post->post_type ) {
			return;
		}

		$post_meta       = fusion_data()->post_meta( $post->ID )->get_all_meta();
		$new_post_parent = $post->post_parent;

		$new_post = [
			'menu_order'     => $post->menu_order,
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'post_author'    => $post->post_author,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_mime_type' => $post->post_mime_type,
			'post_parent'    => $new_post_parent,
			'post_password'  => $post->post_password,
			'post_status'    => 'publish',

			/* translators: The post title. */
			'post_title'     => sprintf( esc_attr__( '%s ( Cloned )', 'fusion-builder' ), $post->post_title ),
			'post_type'      => $post->post_type,
		];

		// Add new section post.
		$new_post_id = wp_insert_post( $new_post );

		// Set a proper slug.
		$post_name             = wp_unique_post_slug( $post->post_name, $new_post_id, 'publish', $post->post_type, $new_post_parent );
		$new_post              = [];
		$new_post['ID']        = $new_post_id;
		$new_post['post_name'] = $post_name;

		wp_update_post( $new_post );

		$taxonomy = 'fusion_element' === $post->post_type ? 'element_category' : 'template_category';

		// Post terms.
		wp_set_object_terms(
			$new_post_id,
			wp_get_object_terms(
				$post->ID,
				$taxonomy,
				[ 'fields' => 'ids' ]
			),
			$taxonomy
		);

		// Clone section meta.
		if ( ! empty( $post_meta ) ) {
			foreach ( $post_meta as $key => $val ) {
				fusion_data()->post_meta( $new_post_id )->set( $key, $val );
			}
		}

		return $new_post_id;
	}

}

/**
 * Instantiates the Fusion_Builder_Library class.
 * Make sure the class is properly set-up.
 *
 * @since object 2.2
 * @return object Fusion_Builder_Library
 */
function Fusion_Builder_Library() { // phpcs:ignore WordPress.NamingConventions
	return Fusion_Builder_Library::get_instance();
}
Fusion_Builder_Library();
