<?php
/**
 * The metaboxes class.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * The Metaboxes class.
 */
class PyreThemeFrameworkMetaboxes {

	/**
	 * An instance of this object.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @var PyreThemeFrameworkMetaboxes
	 */
	public static $instance;

	/**
	 * The settings.
	 *
	 * @access public
	 * @var array
	 */
	public $data;

	/**
	 * The class constructor.
	 *
	 * @access public
	 */
	public function __construct() {
		self::$instance = $this;
		$this->data     = Avada()->settings->get_all();

		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ], 11 );
		add_action( 'save_post', [ $this, 'save_meta_boxes' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_script_loader' ], 99 );
	}

	/**
	 * Get value for a setting.
	 *
	 * @access protected
	 * @since 6.2.0
	 * @param string $id The option-ID.
	 * @return mixed
	 */
	protected function get_value( $id ) {
		global $post;

		return fusion_data()->post_meta( $post->ID )->get( $id );
	}

	/**
	 * Format the option-name for use in our $_POST data.
	 *
	 * @access protected
	 * @since 6.2.0
	 * @param string $id The option-ID.
	 * @return string
	 */
	protected function format_option_name( $id ) {
		if ( false !== strpos( $id, '[' ) ) {
			$parts = explode( '[', $id );
			return Fusion_Data_PostMeta::ROOT . '[' . $parts[0] . '][' . $parts[1];
		}
		return Fusion_Data_PostMeta::ROOT . '[' . $id . ']';
	}

	/**
	 * Load backend scripts.
	 *
	 * @access public
	 */
	public function admin_script_loader() {

		$screen = get_current_screen();
		if ( isset( $screen->post_type ) && in_array( $screen->post_type, apply_filters( 'avada_hide_page_options', [] ) ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
			return;
		}
		$theme_info = wp_get_theme();

		wp_enqueue_script(
			'jquery.biscuit',
			Avada::$template_dir_url . '/assets/admin/js/jquery.biscuit.js',
			[ 'jquery' ],
			$theme_info->get( 'Version' ),
			false
		);
		wp_register_script(
			'avada_upload',
			Avada::$template_dir_url . '/assets/admin/js/upload.js',
			[ 'jquery' ],
			$theme_info->get( 'Version' ),
			false
		);
		wp_enqueue_script( 'avada_upload' );
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'jquery-ui-widget' );
		wp_enqueue_script( 'jquery-ui-button' );

		// Select field assets.
		wp_dequeue_script( 'tribe-events-select2' );

		wp_enqueue_style(
			'select2-css',
			Avada::$template_dir_url . '/assets/admin/css/select2.css',
			[],
			'4.0.3',
			'all'
		);
		wp_enqueue_script(
			'selectwoo-js',
			Avada::$template_dir_url . '/assets/admin/js/selectWoo.full.min.js',
			[ 'jquery' ],
			'1.0.2',
			false
		);

		// Range field assets.
		wp_enqueue_style(
			'avadaredux-nouislider-css',
			FUSION_LIBRARY_URL . '/inc/redux/framework/FusionReduxCore/inc/fields/slider/vendor/nouislider/fusionredux.jquery.nouislider.css',
			[],
			'5.0.0',
			'all'
		);

		wp_enqueue_script(
			'avadaredux-nouislider-js',
			Avada::$template_dir_url . '/assets/admin/js/jquery.nouislider.min.js',
			[ 'jquery' ],
			'5.0.0',
			true
		);
		wp_enqueue_script(
			'wnumb-js',
			Avada::$template_dir_url . '/assets/admin/js/wNumb.js',
			[ 'jquery' ],
			'1.0.2',
			true
		);

		// Color fields.
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_script(
			'wp-color-picker-alpha',
			Avada::$template_dir_url . '/assets/admin/js/wp-color-picker-alpha.js',
			[ 'wp-color-picker' ],
			$theme_info->get( 'Version' ),
			false
		);

		// General JS for fields.
		wp_enqueue_script(
			'avada-fusion-options',
			Avada::$template_dir_url . '/assets/admin/js/avada-fusion-options.js',
			[ 'jquery', 'jquery-ui-sortable' ],
			$theme_info->get( 'Version' ),
			false
		);

		do_action( 'avada_page_option_scripts', $screen->post_type );
	}

	/**
	 * Gets the tabs for post type.
	 *
	 * @access public
	 * @param string $posttype post type.
	 * @since 6.0
	 */
	public static function get_pagetype_tab( $posttype = false ) {
		$pagetype_data = [
			'page'              => [ 'page', 'header', 'sliders', 'pagetitlebar', 'content', 'sidebars', 'footer' ],
			'post'              => [ 'post', 'page', 'header', 'sliders', 'pagetitlebar', 'content', 'sidebars', 'footer' ],
			'avada_faq'         => [ 'post', 'page', 'header', 'sliders', 'pagetitlebar', 'content', 'sidebars', 'footer' ],
			'avada_portfolio'   => [ 'portfolio_post', 'page', 'header', 'sliders', 'pagetitlebar', 'content', 'sidebars', 'footer' ],
			'product'           => [ 'page', 'header', 'sliders', 'pagetitlebar', 'content', 'sidebars', 'footer' ],
			'tribe_events'      => [ 'page', 'header', 'sliders', 'pagetitlebar', 'content', 'sidebars', 'footer' ],
			'fusion_tb_section' => [ 'template', 'content', 'sidebars' ],
			'fusion_form'       => [ 'form_appearance', 'form_submission', 'form_confirmation', 'form_privacy' ],
			'fusion_element'    => [],
			'post_card'         => [ 'template' ],
		];

		$pagetype_data = apply_filters( 'fusion_pagetype_data', $pagetype_data, $posttype );

		if ( ! isset( $posttype ) || ! $posttype ) {
			$posttype = get_post_type();
		}

		// If editing a post card, treat separately.
		if ( fusion_is_post_card() ) {
			$posttype = 'post_card';
		}

		if ( isset( $pagetype_data[ $posttype ] ) ) {
			return $pagetype_data[ $posttype ];
		}
		return [ 'page', 'header', 'sliders', 'pagetitlebar', 'content', 'sidebars', 'footer' ];
	}

	/**
	 * Gets the options for page type.
	 *
	 * @access public
	 * @since 6.0
	 * @return array
	 */
	public function get_options() {
		if ( ! isset( $pagetype ) ) {
			$pagetype = get_post_type();
		}

		$tabs     = $this::get_pagetype_tab( $pagetype );
		$sections = [];

		if ( is_array( $tabs ) ) {
			foreach ( $tabs as $tab_name ) {
				$path = Avada::$template_dir_path . '/includes/metaboxes/tabs/tab_' . $tab_name . '.php';
				require_once wp_normalize_path( $path );
				if ( function_exists( 'avada_page_options_tab_' . $tab_name ) ) {
					$sections = call_user_func( 'avada_page_options_tab_' . $tab_name, $sections );
				}
			}
		}

		return $sections;
	}

	/**
	 * Adds the metaboxes.
	 *
	 * @access public
	 */
	public function add_meta_boxes() {
		$post_types = get_post_types(
			[
				'public' => true,
			]
		);

		// Libary is not public but post cards need PO.
		$post_types[] = 'fusion_element';

		$disallowed = [ 'page', 'post', 'attachment', 'avada_portfolio', 'themefusion_elastic', 'product', 'wpsc-product', 'slide', 'tribe_events', 'fusion_tb_section', 'fusion_form' ];
		$disallowed = array_merge( $disallowed, apply_filters( 'avada_hide_page_options', [] ) );
		foreach ( $post_types as $post_type ) {
			if ( in_array( $post_type, $disallowed, true ) ) {
				continue;
			}
			$this->add_meta_box(
				'post_options',
				apply_filters( 'avada_page_options_metabox_title', esc_html__( 'Avada Options', 'Avada' ) ),
				$post_type
			);
		}

		$this->add_meta_box( 'post_options', esc_html__( 'Avada Page Options', 'Avada' ), 'avada_faq' );
		$this->add_meta_box( 'post_options', esc_html__( 'Avada Page Options', 'Avada' ), 'post' );
		$this->add_meta_box( 'page_options', esc_html__( 'Avada Page Options', 'Avada' ), 'page' );
		$this->add_meta_box( 'portfolio_options', esc_html__( 'Avada Page Options', 'Avada' ), 'avada_portfolio' );
		$this->add_meta_box( 'es_options', esc_html__( 'Elastic Slide Options', 'Avada' ), 'themefusion_elastic' );
		$this->add_meta_box( 'woocommerce_options', esc_html__( 'Avada Page Options', 'Avada' ), 'product' );
		$this->add_meta_box( 'slide_options', esc_html__( 'Slide Options', 'Avada' ), 'slide' );
		$this->add_meta_box( 'events_calendar_options', esc_html__( 'Events Calendar Options', 'Avada' ), 'tribe_events' );
		$this->add_meta_box( 'fusion_tb_section', esc_html__( 'Layout Section Options', 'Avada' ), 'fusion_tb_section' );
		$this->add_meta_box( 'fusion_form', esc_html__( 'Form Options', 'Avada' ), 'fusion_form' );
	}

	/**
	 * Adds a metabox.
	 *
	 * @access public
	 * @param string $id        The metabox ID.
	 * @param string $label     The metabox label.
	 * @param string $post_type The post-type.
	 */
	public function add_meta_box( $id, $label, $post_type ) {
		if ( 'fusion_element' === $post_type && ! fusion_is_post_card() ) {
			return;
		}
		$label = '<span class="avada-logo-wrapper"><span class="avada-logo fusiona-avada-logo"></span><span class="avada-option-title">' . esc_html( $label ) . '</span></span>';

		add_meta_box( 'pyre_' . $id, $label, [ $this, $id ], $post_type, 'advanced', 'high' );
	}

	/**
	 * Saves the metaboxes.
	 *
	 * @access public
	 * @param string|int $post_id The post ID.
	 */
	public function save_meta_boxes( $post_id ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( isset( $_POST[ Fusion_Data_PostMeta::ROOT ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$fusion_meta = wp_unslash( $_POST[ Fusion_Data_PostMeta::ROOT ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification
			foreach ( $fusion_meta as $key => $val ) {

				if ( 0 === strpos( $key, '_' ) ) {
					$fusion_meta[ ltrim( $key, '_' ) ] = $val;
					unset( $fusion_meta[ $key ] );
				}

				if ( '' === $val || 'default' === $val || ( is_array( $val ) && isset( $val['url'] ) && empty( $val['url'] ) ) ) {
					unset( $fusion_meta[ $key ] );
				}

				if ( empty( $val ) ) {
					unset( $fusion_meta[ $key ] );
				}
			}
			update_post_meta( $post_id, Fusion_Data_PostMeta::ROOT, $fusion_meta );
		}
	}

	/**
	 * Handle rendering options for pages.
	 *
	 * @access public
	 */
	public function page_options() {
		$this->render_option_tabs( $this::get_pagetype_tab( 'page' ) );
	}

	/**
	 * Handle rendering options for posts.
	 *
	 * @access public
	 */
	public function post_options() {
		$this->render_option_tabs( $this::get_pagetype_tab( 'post' ) );
	}

	/**
	 * Handle rendering options for portfolios.
	 *
	 * @access public
	 */
	public function portfolio_options() {
		$this->render_option_tabs( $this::get_pagetype_tab( 'avada_portfolio' ) );
	}

	/**
	 * Handle rendering options for woocommerce.
	 *
	 * @access public
	 */
	public function woocommerce_options() {
		$this->render_option_tabs( $this::get_pagetype_tab( 'product' ), 'product' );
	}

	/**
	 * Handle rendering options for ES.
	 *
	 * @access public
	 */
	public function es_options() {
		include 'options/options_es.php';
	}

	/**
	 * Handle rendering options for slides.
	 *
	 * @access public
	 */
	public function slide_options() {
		include 'options/options_slide.php';
	}

	/**
	 * Handle rendering options for events.
	 *
	 * @access public
	 */
	public function events_calendar_options() {
		$this->render_option_tabs( $this::get_pagetype_tab( 'tribe_events' ) );
	}

	/**
	 * Handle rendering options for events.
	 *
	 * @access public
	 */
	public function fusion_tb_section() {
		$this->render_option_tabs( $this::get_pagetype_tab( 'fusion_tb_section' ) );
	}

	/**
	 * Handle rendering options for events.
	 *
	 * @access public
	 */
	public function fusion_form() {
		$this->render_option_tabs( $this::get_pagetype_tab( 'fusion_form' ) );
	}

	/**
	 * Render fields within tab.
	 *
	 * @access public
	 * @param array  $tab_data The tab map.
	 * @param string $repeater Used for repeater fields.
	 * @since 6.0
	 */
	public function render_tab_fields( $tab_data, $repeater = false ) {
		if ( ! is_array( $tab_data ) ) {
			return;
		}

		foreach ( $tab_data['fields'] as $field ) {
			// Defaults.
			$field['id']          = isset( $field['id'] ) ? $field['id'] : '';
			$field['label']       = isset( $field['label'] ) ? $field['label'] : '';
			$field['choices']     = isset( $field['choices'] ) ? $field['choices'] : [];
			$field['description'] = isset( $field['description'] ) ? $field['description'] : '';
			$field['default']     = isset( $field['default'] ) ? $field['default'] : '';
			$field['dependency']  = isset( $field['dependency'] ) ? $field['dependency'] : [];
			$field['ajax']        = isset( $field['ajax'] ) ? $field['ajax'] : false;
			$field['ajax_params'] = isset( $field['ajax_params'] ) ? $field['ajax_params'] : false;
			$field['max_input']   = isset( $field['max_input'] ) ? $field['max_input'] : 1000;
			$field['placeholder'] = isset( $field['placeholder'] ) ? $field['placeholder'] : 1000;

			switch ( $field['type'] ) {
				case 'radio-buttonset':
					$this->radio_buttonset( $field['id'], $field['label'], $field['choices'], $field['description'], $field['default'], $field['dependency'] );
					break;
				case 'color-alpha':
					$this->color( $field['id'], $field['label'], $field['description'], true, $field['dependency'], $field['default'] );
					break;
				case 'color':
					$this->color( $field['id'], $field['label'], $field['description'], false, $field['dependency'], $field['default'] );
					break;
				case 'media':
				case 'media_url':
					$this->upload( $field['id'], $field['label'], $field['description'], $field['dependency'] );
					break;
				case 'ajax_select':
				case 'multiple_select':
					$this->multiple( $field['id'], $field['label'], $field['choices'], $field['description'], $field['dependency'], $field['ajax'], $field['ajax_params'], $field['max_input'], $field['placeholder'], $repeater );
					break;
				case 'select':
					$this->select( $field['id'], $field['label'], $field['choices'], $field['description'], $field['default'], $field['dependency'], $repeater );
					break;
				case 'dimensions':
					$this->dimension( $field['id'], $field['value'], $field['label'], $field['description'], $field['dependency'] );
					break;
				case 'text':
					$this->text( $field['id'], $field['label'], $field['description'], $field['dependency'] );
					break;
				case 'textarea':
					$this->textarea( $field['id'], $field['label'], $field['description'], $field['default'], $field['dependency'] );
					break;
				case 'custom':
					$this->raw( $field['id'], $field['label'], $field['description'], $field['dependency'] );
					break;
				case 'hidden':
					$this->hidden( $field['id'], $field['default'] );
					break;
				case 'slider':
					$this->range( $field['id'], $field['label'], $field['description'], $field['choices']['min'], $field['choices']['max'], $field['choices']['step'], $field['default'], '', $field['dependency'] );
					break;
				case 'sortable':
					$this->sortable( $field['id'], $field['label'], $field['choices'], $field['description'], $field['dependency'], $field['default'] );
					break;
				case 'hubspot_map':
					$this->hubspot_map( $field['id'], $field['label'], $field['choices'], $field['description'], $field['dependency'], $field['default'] );
					break;
				case 'repeater':
					$labels = [
						'row_add'   => $field['row_add'],
						'row_title' => $field['row_title'],
					];
					$this->repeater( $field['id'], $field['label'], $field['description'], $field['dependency'], $field['fields'], $field['bind_title'], $labels );
					break;
			}
		}
	}

	/**
	 * Handle rendering options.
	 *
	 * @access public
	 * @param array  $requested_tabs The requested tabs.
	 * @param string $post_type      The post-type.
	 */
	public function render_option_tabs( $requested_tabs, $post_type = 'default' ) {
		$screen = get_current_screen();

		$tabs_names = [
			'sliders'           => esc_html__( 'Sliders', 'Avada' ),
			'page'              => esc_html__( 'Layout', 'Avada' ),
			'post'              => 'avada_faq' === $screen->post_type ? esc_html__( 'FAQ', 'Avada' ) : esc_html__( 'Post', 'Avada' ),
			'header'            => esc_html__( 'Header', 'Avada' ),
			'content'           => esc_html__( 'Content', 'Avada' ),
			'sidebars'          => esc_html__( 'Sidebars', 'Avada' ),
			'pagetitlebar'      => esc_html__( 'Page Title Bar', 'Avada' ),
			'portfolio_post'    => esc_html__( 'Portfolio', 'Avada' ),
			'product'           => esc_html__( 'Product', 'Avada' ),
			'template'          => 'fusion_element' === $screen->post_type ? esc_html__( 'Post Card', 'Avada' ) : esc_html__( 'Layout Section', 'Avada' ),
			'form_submission'   => esc_html__( 'Submission', 'Avada' ),
			'form_confirmation' => esc_html__( 'Confirmation', 'Avada' ),
			'form_appearance'   => esc_html__( 'Appearance', 'Avada' ),
			'form_privacy'      => esc_html__( 'Privacy', 'Avada' ),
			'footer'            => esc_html__( 'Footer', 'Avada' ),
		];

		$tabs = [
			'requested_tabs' => $requested_tabs,
			'tabs_names'     => $tabs_names,
			'tabs_path'      => [],
		];

		$tabs = apply_filters( 'avada_metabox_tabs', $tabs, $post_type );
		?>

		<ul class="pyre_metabox_tabs">

			<?php foreach ( $tabs['requested_tabs'] as $key => $tab_name ) : ?>
				<?php $class_active = ( 0 === $key ) ? 'active' : ''; ?>
				<?php if ( 'page' === $tab_name && 'product' === $post_type ) : ?>
					<li class="<?php echo esc_attr( $class_active ); ?>"><a href="<?php echo esc_attr( $tab_name ); ?>"><?php echo esc_attr( $tabs['tabs_names'][ $post_type ] ); ?></a></li>
				<?php else : ?>
					<li class="<?php echo esc_attr( $class_active ); ?>"><a href="<?php echo esc_attr( $tab_name ); ?>"><?php echo esc_attr( $tabs['tabs_names'][ $tab_name ] ); ?></a></li>
				<?php endif; ?>
			<?php endforeach; ?>

		</ul>

		<div class="pyre_metabox">

			<?php foreach ( $tabs['requested_tabs'] as $key => $tab_name ) : ?>
				<div class="pyre_metabox_tab" id="pyre_tab_<?php echo esc_attr( $tab_name ); ?>">
				<?php
				$path = ! empty( $tabs['tabs_path'][ $tab_name ] ) ? $tabs['tabs_path'][ $tab_name ] : dirname( __FILE__ ) . '/tabs/tab_' . $tab_name . '.php';
				if ( $path && file_exists( wp_normalize_path( $path ) ) ) {
					require_once wp_normalize_path( $path );
				}
				if ( function_exists( 'avada_page_options_tab_' . $tab_name ) ) {
					$tab_data = call_user_func( 'avada_page_options_tab_' . $tab_name, [] );
					$this->render_tab_fields( $tab_data[ $tab_name ], false );
				}
				?>
				</div>
			<?php endforeach; ?>

		</div>
		<div class="clear"></div>
		<?php

	}

	/**
	 * Text controls.
	 *
	 * @access public
	 * @param string $id         The ID.
	 * @param string $label      The label.
	 * @param string $desc       The description.
	 * @param array  $dependency The dependencies array.
	 */
	public function text( $id, $label, $desc = '', $dependency = [] ) {
		global $post;
		?>

		<div class="pyre_metabox_field">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo esc_textarea( $label ); ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field">
				<input type="text" id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( $id ) ); ?>" value="<?php echo esc_attr( $this->get_value( $id ) ); ?>" />
			</div>
		</div>
		<?php

	}

	/**
	 * Select controls.
	 *
	 * @access public
	 * @param string $id         The ID.
	 * @param string $label      The label.
	 * @param array  $options    The options array.
	 * @param string $desc       The description.
	 * @param string $default    The default value..
	 * @param array  $dependency The dependencies array.
	 * @param string $repeater   Used for repeater fields.
	 */
	public function select( $id, $label, $options, $desc = '', $default = '', $dependency = [], $repeater = false ) {
		global $post;
		$repeater = $repeater ? 'repeater' : '';
		$db_value = $this->get_value( $id );
		$default  = $this->is_meta_data_saved_in_db() ? '' : $default;
		$value    = $db_value ? $db_value : $default;

		?>

		<div class="pyre_metabox_field">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo esc_textarea( $label ); ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field">
				<select id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( "{$repeater}_{$id}" ) ); ?>" style="width:100%">
					<?php foreach ( $options as $key => $option ) : ?>
						<option <?php echo ( (string) $value === (string) $key ) ? 'selected="selected"' : ''; ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $option ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<?php

	}

	/**
	 * Color picker field.
	 *
	 * @access public
	 * @since 5.0.0
	 * @param string  $id         ID of input field.
	 * @param string  $label      Label of field.
	 * @param string  $desc       Description of field.
	 * @param boolean $alpha      Whether or not to show alpha.
	 * @param array   $dependency The dependencies array.
	 * @param string  $default    Default value from TO.
	 */
	public function color( $id, $label, $desc = '', $alpha = false, $dependency = [], $default = '' ) {
		global $post;
		$styling_class = ( $alpha ) ? 'colorpickeralpha' : 'colorpicker';

		if ( $default ) {
			if ( ! $alpha && ( 'transparent' === $default || ! is_string( $default ) ) ) {
				$default = '#ffffff';
			}
			$desc .= '  <span class="pyre-default-reset"><a href="#" id="default-' . $id . '" class="fusion-range-default fusion-hide-from-atts" type="radio" name="' . $id . '" value="" data-default="' . $default . '">' . esc_attr__( 'Reset to default.', 'Avada' ) . '</a><span>' . esc_attr__( 'Using default value.', 'Avada' ) . '</span></span>';
		}
		?>

		<div class="pyre_metabox_field">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field avada-color <?php echo esc_attr( $styling_class ); ?>">
				<input id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( $id ) ); ?>" class="fusion-builder-color-picker-hex color-picker" type="text" value="<?php echo esc_attr( $this->get_value( $id ) ); ?>" <?php echo ( $alpha ) ? 'data-alpha="true"' : ''; ?> <?php echo ( $default ) ? 'data-default="' . esc_attr( $default ) . '"' : ''; ?> />
			</div>
		</div>
		<?php

	}

	/**
	 * Range field.
	 *
	 * @since 5.0.0
	 * @param string           $id         ID of input field.
	 * @param string           $label      Label of field.
	 * @param string           $desc       The description.
	 * @param string|int|float $min        The minimum value.
	 * @param string|int|float $max        The maximum value.
	 * @param string|int|float $step       The steps value.
	 * @param string|int|float $default    The default value.
	 * @param string|int|float $value      The value.
	 * @param array            $dependency The dependencies array.
	 */
	public function range( $id, $label, $desc = '', $min = 0, $max = 0, $step = 1, $default = 0, $value = '', $dependency = [] ) {
		global $post;
		if ( isset( $default ) && '' !== $default ) {
			$desc .= '  <span class="pyre-default-reset"><a href="#" id="default-' . $id . '" class="fusion-range-default fusion-hide-from-atts" type="radio" name="' . $id . '" value="" data-default="' . $default . '">' . esc_attr__( 'Reset to default.', 'Avada' ) . '</a><span>' . esc_attr__( 'Using default value.', 'Avada' ) . '</span></span>';
		}
		?>

		<div class="pyre_metabox_field">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo esc_textarea( $label ); ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field avada-range">
				<?php
					$default_status = ( ( $default ) ? 'fusion-with-default' : '' );
					$is_checked     = ( '' == $this->get_value( $id ) ); // phpcs:ignore WordPress.PHP.StrictComparisons
					$regular_id     = ( ( '' != $this->get_value( $id ) ) ? $id : 'slider' . $id ); // phpcs:ignore WordPress.PHP.StrictComparisons
					$display_value  = ( ( '' == $this->get_value( $id ) ) ? $default : $this->get_value( $id ) ); // phpcs:ignore WordPress.PHP.StrictComparisons
				?>
				<input
					type="text"
					name="<?php echo esc_attr( $id ); ?>"
					id="<?php echo esc_attr( $regular_id ); ?>"
					value="<?php echo esc_attr( $display_value ); ?>"
					class="fusion-slider-input <?php echo esc_attr( $default_status ); ?> <?php echo ( isset( $default ) && '' !== $default ) ? 'fusion-hide-from-atts' : ''; ?>" />
				<div
					class="fusion-slider-container"
					data-id="<?php echo esc_attr( $id ); ?>"
					data-min="<?php echo esc_attr( $min ); ?>"
					data-max="<?php echo esc_attr( $max ); ?>"
					data-step="<?php echo esc_attr( $step ); ?>">
				</div>
				<?php if ( isset( $default ) && '' !== $default ) : ?>
					<input
						type="hidden"
						id="pyre_<?php echo esc_attr( $id ); ?>"
						name="<?php echo esc_attr( $this->format_option_name( $id ) ); ?>"
						value="<?php echo esc_attr( $this->get_value( $id ) ); ?>"
						class="fusion-hidden-value" />
				<?php endif; ?>

			</div>
		</div>
		<?php

	}

	/**
	 * Radio button set field.
	 *
	 * @since 5.0.0
	 * @param string           $id         ID of input field.
	 * @param string           $label      Label of field.
	 * @param array            $options    Options to select from.
	 * @param string           $desc       Description of field.
	 * @param string|int|float $default    The default value.
	 * @param array            $dependency The dependencies array.
	 */
	public function radio_buttonset( $id, $label, $options, $desc = '', $default = '', $dependency = [] ) {
		global $post;
		$options_reset = $options;

		reset( $options_reset );

		if ( '' === $default ) {
			$default = key( $options_reset );
		}

		$value = ( '' == $this->get_value( $id ) ) ? $default : $this->get_value( $id ); // phpcs:ignore WordPress.PHP.StrictComparisons
		?>

		<div class="pyre_metabox_field">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field avada-buttonset">
				<div class="fusion-form-radio-button-set ui-buttonset">
					<input type="hidden" id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( $id ) ); ?>" value="<?php echo esc_attr( $value ); ?>" class="button-set-value" />
					<?php foreach ( $options as $key => $option ) : ?>
						<?php $selected = ( $key == $value ) ? ' ui-state-active' : ''; // phpcs:ignore WordPress.PHP.StrictComparisons ?>
						<a href="#" class="ui-button buttonset-item<?php echo esc_attr( $selected ); ?>" data-value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $option ); ?></a>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php

	}

	/**
	 * Dimensions field.
	 *
	 * @since 5.0.0
	 * @param array  $main_id    Overall option ID.
	 * @param array  $ids        IDs of input fields.
	 * @param string $label      Label of field.
	 * @param string $desc       Description of field.
	 * @param array  $dependency The dependencies array.
	 */
	public function dimension( $main_id, $ids, $label, $desc = '', $dependency = [] ) {
		global $post;
		?>

		<div class="pyre_metabox_field">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( array_key_first( $ids ) ); ?>"><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput, PHPCompatibility.FunctionUse.NewFunctions.array_key_firstFound ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field avada-dimension">
				<?php foreach ( $ids as $field_id => $default ) : ?>
					<?php
					$display_value = $this->get_value( "{$main_id}[{$field_id}]" );
					$display_value = ( ( '' == $display_value ) ? $default : $display_value ); // phpcs:ignore WordPress.PHP.StrictComparisons
					$icon_class    = 'fusiona-expand width';
					if ( false !== strpos( $field_id, 'height' ) ) {
						$icon_class = 'fusiona-expand  height';
					}
					if ( false !== strpos( $field_id, 'top' ) ) {
						$icon_class = 'dashicons dashicons-arrow-up-alt';
					}
					if ( false !== strpos( $field_id, 'right' ) ) {
						$icon_class = 'dashicons dashicons-arrow-right-alt';
					}
					if ( false !== strpos( $field_id, 'bottom' ) ) {
						$icon_class = 'dashicons dashicons-arrow-down-alt';
					}
					if ( false !== strpos( $field_id, 'left' ) ) {
						$icon_class = 'dashicons dashicons-arrow-left-alt';
					}
					?>
					<div class="fusion-builder-dimension">
						<span class="add-on"><i class="<?php echo esc_attr( $icon_class ); ?>" aria-hidden="true"></i></span>
						<input type="text" name="<?php echo esc_attr( $this->format_option_name( "{$main_id}[{$field_id}]" ) ); ?>" id="pyre_<?php echo esc_attr( $field_id ); ?>" value="<?php echo esc_attr( $display_value ); ?>" />
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php

	}

	/**
	 * Multiselect field.
	 *
	 * @param array  $id          IDs of input fields.
	 * @param string $label       Label of field.
	 * @param array  $options     The options to choose from.
	 * @param string $desc        Description of field.
	 * @param array  $dependency  The dependencies array.
	 * @param mixed  $ajax        Ajax callback name if required.
	 * @param array  $ajax_params An array of our AJAX parameters.
	 * @param int    $max_input   Used as an attribute - defines the maximum number of inputs in a select field.
	 * @param string $placeholder The placeholder for our select field.
	 * @param string $repeater    Used for repeater fields.
	 */
	public function multiple( $id, $label, $options, $desc = '', $dependency = [], $ajax = false, $ajax_params = [], $max_input = 1000, $placeholder = '', $repeater = false ) {
		global $post;
		$repeater = $repeater ? 'repeater' : '';
		?>

		<div class="pyre_metabox_field">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field">
				<?php if ( $ajax ) : ?>
					<input type="hidden" value="<?php echo esc_attr( wp_json_encode( $ajax_params ) ); ?>" class="params" />
					<input type="hidden" value="<?php echo esc_attr( wp_json_encode( $this->get_value( $id ) ) ); ?>" class="initial-values" />
					<select multiple="multiple" data-max-input="<?php echo esc_attr( $max_input ); ?>" data-placeholder="<?php echo esc_attr( $placeholder ); ?>" <?php echo 'data-ajax="' . esc_attr( $ajax ) . '"'; ?>id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( "{$repeater}_{$id}" ) ); ?>[]">
					</select>
				<?php else : ?>
					<select multiple="multiple" id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( "{$repeater}_{$id}" ) ); ?>[]" style="width:100%;">
						<?php foreach ( $options as $key => $option ) : ?>
							<?php $selected = ( is_array( $this->get_value( $id ) ) && in_array( $key, $this->get_value( $id ) ) ) ? 'selected="selected"' : ''; // phpcs:ignore WordPress.PHP.StrictInArray ?>
							<option <?php echo esc_attr( $selected ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $option ); ?></option>
						<?php endforeach; ?>
					</select>
				<?php endif; ?>
			</div>
		</div>
		<?php

	}

	/**
	 * Textarea field.
	 *
	 * @param array  $id         IDs of input fields.
	 * @param string $label      Label of field.
	 * @param string $desc       Description of field.
	 * @param string $default    The default value.
	 * @param array  $dependency The dependencies array.
	 */
	public function textarea( $id, $label, $desc = '', $default = '', $dependency = [] ) {
		global $post;

		$db_value = $this->get_value( $id );
		$default  = $this->is_meta_data_saved_in_db() ? '' : $default;
		$value    = $db_value ? $db_value : $default;
		$rows     = 10;
		if ( 'heading' === $id || 'caption' === $id ) {
			$rows = 5;
		} elseif ( 'page_title_custom_text' === $id || 'page_title_custom_subheader' === $id ) {
			$rows = 1;
		}
		?>

		<div class="pyre_metabox_field">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field">
				<textarea cols="120" rows="<?php echo (int) $rows; ?>" id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( $id ) ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
			</div>
		</div>
		<?php

	}

	/**
	 * Upload field.
	 *
	 * @param array  $id         IDs of input fields.
	 * @param string $label      Label of field.
	 * @param string $desc       Description of field.
	 * @param array  $dependency The dependencies array.
	 */
	public function upload( $id, $label, $desc = '', $dependency = [] ) {
		global $post;
		?>

		<div class="pyre_metabox_field">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field">
				<div class="pyre_upload">
					<?php
					$image_url = $this->get_value( $id . '[url]' );
					if ( ! $image_url && $this->get_value( $id ) ) {
						$image_url = $this->get_value( $id );
					}
					?>
					<input name="<?php echo esc_attr( $this->format_option_name( $id . '[url]' ) ); ?>" class="upload_field" id="pyre_<?php echo esc_attr( $id ); ?>" type="text" value="<?php echo esc_attr( $image_url ); ?>" />
					<?php
					$image_id = $this->get_value( $id . '[id]' );

					if ( ! $image_id && $image_url ) {
						$image_id = Fusion_Images::get_attachment_id_from_url( $image_url );
					}
					?>
					<input name="<?php echo esc_attr( $this->format_option_name( $id . '[id]' ) ); ?>" class="upload_field_id" id="pyre_<?php echo esc_attr( $id ); ?>_id" type="hidden" value="<?php echo esc_attr( $image_id ); ?>" />
					<input class="fusion_upload_button button" type="button" value="<?php esc_attr_e( 'Browse', 'Avada' ); ?>" />
				</div>
			</div>
		</div>
		<?php

	}
	/**
	 * Hidden input.
	 *
	 * @since 5.0.0
	 * @param string $id    id of input field.
	 * @param string $value value of input field.
	 */
	public function hidden( $id, $value ) {
		global $post;
		?>
		<input type="hidden" id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( $id ) ); ?>" value="<?php echo esc_attr( $value ); ?>">
		<?php

	}

	/**
	 * Sortable controls.
	 *
	 * @since 5.7
	 * @access public
	 * @param string       $id         The ID.
	 * @param string       $label      The label.
	 * @param array        $options    The options array.
	 * @param string       $desc       The description.
	 * @param array        $dependency The dependencies array.
	 * @param string|array $default    The default value.
	 */
	public function sortable( $id, $label, $options, $desc = '', $dependency = [], $default = '' ) {
		global $post;
		$sort_order_saved = $this->get_value( $id );
		$sort_order_saved = ( ! $sort_order_saved ) ? '' : $sort_order_saved;
		$sort_order       = ( empty( $sort_order_saved ) ) ? $default : $sort_order_saved;
		$sort_order       = ( is_array( $sort_order ) ) ? $sort_order : explode( ',', $sort_order );
		?>

		<div class="pyre_metabox_field">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo esc_textarea( $label ); ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field">
				<ul class="fusion-sortable-options" id="pyre_<?php echo esc_attr( $id ); ?>">
					<?php foreach ( $sort_order as $item ) : ?>
						<?php $item = trim( $item ); ?>
						<?php if ( isset( $options[ $item ] ) ) : ?>
							<div class="fusion-sortable-option" data-value="<?php echo esc_attr( $item ); ?>">
								<span><?php echo esc_html( $options[ $item ] ); ?></span>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
				<input class="sort-order" type="hidden" id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( $id ) ); ?>" value="<?php echo esc_attr( $sort_order_saved ); ?>">
			</div>
		</div>
		<?php

	}

	/**
	 * Repeater controls.
	 *
	 * @since 6.2
	 * @access public
	 * @param string $id         The ID.
	 * @param string $label      The label.
	 * @param string $desc       The description.
	 * @param array  $dependency The dependencies array.
	 * @param array  $fields     An array of fields.
	 * @param string $bind_title What should be used for the title.
	 * @param array  $labels     An array of our labels.
	 */
	public function repeater( $id, $label, $desc = '', $dependency = [], $fields = [], $bind_title = '', $labels = [] ) {
		global $post;
		$add_label   = isset( $labels['row_add'] ) ? $labels['row_add'] : __( 'Add New', 'Avada' );
		$title_label = isset( $labels['row_title'] ) ? $labels['row_title'] : __( 'Repeater Row', 'Avada' );
		$value       = fusion_data()->post_meta( $post->ID )->get( $id );
		if ( is_array( $value ) ) {
			$value = wp_json_encode( $value );
		}
		?>

		<div class="pyre_metabox_field fusion-repeater-wrapper">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo esc_textarea( $label ); ?></label>
				<a class="fusion-add-row button button-primary button-large" href="#"><?php echo esc_html( $add_label ); ?></a>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field">
				<div class="fusion-repeater-default-fields" style="display: none">
					<div class="fusion-row-title">
						<span class="repeater-toggle-icon fusiona-pen"></span>
						<h4><?php echo esc_html( $title_label ); ?></h4>
						<span class="repeater-row-remove fusiona-trash-o"></span>
					</div>
					<div class="fusion-row-fields" style="display:none">
						<?php $this->render_tab_fields( [ 'fields' => $fields ], true ); ?>
					</div>
				</div>
				<div class="fusion-repeater-rows"></div>
				<input class="repeater-value" data-bind="<?php echo esc_attr( $bind_title ); ?>" type="hidden" id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( $id ) ); ?>" value="<?php echo esc_attr( $value ); ?>">
			</div>
		</div>
		<?php

	}

	/**
	 * HubSpot map control.
	 *
	 * @since 7.1
	 * @access public
	 * @param string       $id         The ID.
	 * @param string       $label      The label.
	 * @param array        $options    The options array.
	 * @param string       $desc       The description.
	 * @param array        $dependency The dependencies array.
	 * @param string|array $default    The default value.
	 */
	public function hubspot_map( $id, $label, $options, $desc = '', $dependency = [], $default = '' ) {
		$value = $this->get_value( $id );
		if ( is_array( $value ) ) {
			$value = wp_json_encode( $value );
		}
		?>

		<div class="pyre_metabox_field fusion-hubspot-option">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo esc_textarea( $label ); ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field">
				<div class="hubspot-map-holder">
					<div class="fusion-mapping">
						<span><?php esc_attr_e( 'No form fields or HubSpot properties found.', 'Avada' ); ?></span>
					</div>
				</div>
				<input type="hidden" id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( $id ) ); ?>" value="<?php echo esc_attr( $value ); ?>">
			</div>
		</div>
		<?php

	}

	/**
	 * Dependency markup.
	 *
	 * @since 5.0.0
	 * @param array $dependency dependence options.
	 * @return string $data_dependence markup
	 */
	private function dependency( $dependency = [] ) {

		// Disable dependencies if 'dependencies_status' is set to 0.
		if ( '0' === Avada()->settings->get( 'dependencies_status' ) ) {
			return '';
		}

		$data_dependency = '';
		if ( 0 < count( $dependency ) ) {
			$data_dependency .= '<div class="avada-dependency">';
			foreach ( $dependency as $dependence ) {
				$data_dependency .= '<span class="hidden" data-value="' . $dependence['value'] . '" data-field="' . $dependence['field'] . '" data-comparison="' . $dependence['comparison'] . '"></span>';
			}
			$data_dependency .= '</div>';
		}
		return $data_dependency;
	}

	/**
	 * Raw field.
	 *
	 * @since 5.3.0
	 * @param array  $id         IDs of input fields.
	 * @param string $label      Label of field.
	 * @param string $desc       Description of field.
	 * @param array  $dependency The dependencies array.
	 */
	public function raw( $id, $label, $desc = '', $dependency = [] ) {
		global $post;
		?>

		<div class="pyre_metabox_field">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc_raw">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php

	}

	/**
	 * Check if the meta data object has already been saved to database.
	 *
	 * @since 6.2.1
	 * @return bool True if meta data is already in db, false otherwise.
	 */
	public function is_meta_data_saved_in_db() {
		global $post;

		return ! empty( fusion_data()->post_meta( $post->ID )->get_all_meta() );
	}
}

global $pagenow;

if ( is_admin() && ( ( in_array( $pagenow, [ 'post-new.php', 'post.php' ], true ) ) || ! isset( $pagenow ) || apply_filters( 'fusion_page_options_init', false ) ) ) {
	if ( ! PyreThemeFrameworkMetaboxes::$instance ) {
		$metaboxes = new PyreThemeFrameworkMetaboxes();
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
