<?php

namespace WPFormsFormPages;

/**
 * Form Pages frontend functionality.
 *
 * @since 1.0.0
 */
class Frontend {

	/**
	 * Current form data.
	 *
	 * @var array
	 *
	 * @since 1.0.0
	 */
	protected $form_data;

	/**
	 * Color helper instance.
	 *
	 * @var \WPFormsFormPages\Helpers\Colors
	 *
	 * @since 1.0.0
	 */
	public $colors;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->colors = new Helpers\Colors();

		$this->init();
	}

	/**
	 * Initialize.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		\add_action( 'parse_request', array( $this, 'handle_request' ) );
	}

	/**
	 * Handle the request.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP $wp WP instance.
	 */
	public function handle_request( $wp ) {

		if ( ! empty( $wp->query_vars['name'] ) ) {
			$request = $wp->query_vars['name'];
		}

		if ( empty( $request ) && ! empty( $wp->query_vars['pagename'] ) ) {
			$request = $wp->query_vars['pagename'];
		}

		if ( empty( $request ) ) {
			$request = ! empty( $_SERVER['REQUEST_URI'] ) ? \esc_url_raw( \wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			$request = ! empty( $request ) ? \sanitize_key( \wp_parse_url( $request, PHP_URL_PATH ) ) : '';
		}

		$forms = ! empty( $request ) ? \wpforms()->form->get( '', array( 'name' => $request ) ) : array();

		$form = ! empty( $forms[0] ) ? $forms[0] : null;

		if ( ! isset( $form->post_type ) || 'wpforms' !== $form->post_type ) {
			return;
		}

		$form_data = \wpforms_decode( $form->post_content );

		if ( empty( $form_data['settings']['form_pages_enable'] ) ) {
			return;
		}

		// Set form data to be used by other methods of the class.
		$this->form_data = $form_data;

		// Override page URLs with the same slug.
		if ( ! empty( $wp->query_vars['pagename'] ) ) {
			$wp->query_vars['name'] = $wp->query_vars['pagename'];
			unset( $wp->query_vars['pagename'] );
		}

		if ( empty( $wp->query_vars['name'] ) ) {
			$wp->query_vars['name'] = $request;
		}

		$wp->query_vars['post_type'] = 'wpforms';

		// Unset 'error' query var that may appear if custom permalink structures used.
		unset( $wp->query_vars['error'] );

		// Enabled form page detected. Adding the hooks.
		$this->form_page_hooks();
	}

	/**
	 * Form Page specific hooks.
	 *
	 * @since 1.0.0
	 */
	public function form_page_hooks() {

		add_filter( 'template_include', array( $this, 'get_form_template' ), PHP_INT_MAX );
		add_filter( 'document_title_parts', array( $this, 'change_form_page_title' ) );
		add_filter( 'post_type_link', array( $this, 'modify_permalink' ), 10, 2 );

		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
		remove_action( 'wp_head','wp_oembed_add_discovery_links' );
		remove_action( 'wp_head','wp_oembed_add_host_js' );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wpforms_wp_footer', array( $this, 'dequeue_scripts' ) );
		add_action( 'wp_print_styles', array( $this, 'css_compatibility_mode' ) );
		add_action( 'wp_head', array( $this, 'print_form_styles' ) );
		add_filter( 'body_class', array( $this, 'set_body_classes' ) );

		add_action( 'wpforms_form_pages_content_before', array( $this, 'form_logo_html' ) );
		add_action( 'wpforms_frontend_output', array( $this, 'form_head_html' ), 5, 4 );
		add_action( 'wpforms_form_pages_footer', array( $this, 'form_footer_html' ) );

		add_action( 'wp', array( $this, 'meta_tags' ) );
	}

	/**
	 * Form Page template.
	 *
	 * @since 1.0.0
	 */
	public function get_form_template() {

		return \plugin_dir_path( \WPFORMS_FORM_PAGES_FILE ) . 'templates/single-form.php';
	}

	/**
	 * Change document title to a custom form title.
	 *
	 * @since 1.0.0
	 *
	 * @param array $title Original document title parts.
	 *
	 * @return mixed
	 */
	public function change_form_page_title( $title ) {

		$title['title'] = $this->get_title();

		return $title;
	}

	/**
	 * Modify permalink for a form page.
	 *
	 * @since 1.0.0
	 *
	 * @param string   $post_link The post's permalink.
	 * @param \WP_Post $post      The post object.
	 *
	 * @return string
	 */
	public function modify_permalink( $post_link, $post ) {

		if ( empty( $this->form_data['id'] ) || \absint( $this->form_data['id'] ) !== $post->ID ) {
			return $post_link;
		}

		if ( empty( $this->form_data['settings']['form_pages_enable'] ) ) {
			return $post_link;
		}

		return \home_url( $post->post_name );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		$min = \wpforms_get_min_suffix();

		\wp_enqueue_style(
			'wpforms-form-pages',
			\wpforms_form_pages()->url . "assets/css/form-pages{$min}.css",
			array(),
			\WPFORMS_FORM_PAGES_VERSION
		);
	}

	/**
	 * Dequeue scripts and styles.
	 *
	 * @since 1.2.2
	 */
	public function dequeue_scripts() {

		\wp_dequeue_script( 'popup-maker-site' );
	}

	/**
	 * Unload CSS potentially interfering with Form Pages layout.
	 *
	 * @since 1.0.0
	 */
	public function css_compatibility_mode() {

		if ( ! \apply_filters( 'wpforms_form_pages_css_compatibility_mode', true ) ) {
			return;
		}

		$styles = \wp_styles();

		if ( empty( $styles->queue ) ) {
			return;
		}

		$theme_uri        = \wp_make_link_relative( \get_stylesheet_directory_uri() );
		$parent_theme_uri = \wp_make_link_relative( \get_template_directory_uri() );

		$upload_uri = \wp_get_upload_dir();
		$upload_uri = isset( $upload_uri['baseurl'] ) ? \wp_make_link_relative( $upload_uri['baseurl'] ) : $theme_uri;

		foreach ( $styles->queue as $handle ) {

			if ( ! isset( $styles->registered[ $handle ]->src ) ) {
				continue;
			}

			$src = \wp_make_link_relative( $styles->registered[ $handle ]->src );

			// Dequeue theme or upload folder CSS.
			foreach ( array( $theme_uri, $parent_theme_uri, $upload_uri ) as $uri ) {
				if ( \strpos( $src, $uri ) !== false ) {
					\wp_dequeue_style( $handle );
					break;
				}
			}
		}

		\do_action( 'wpforms_form_pages_enqueue_styles' );
	}

	/**
	 * Print dynamic form styles.
	 *
	 * @since 1.0.0
	 */
	public function print_form_styles() {

		if ( empty( $this->form_data['settings']['form_pages_color_scheme'] ) ) {
			return;
		}

		$color = \sanitize_hex_color( $this->form_data['settings']['form_pages_color_scheme'] );

		if ( empty( $color ) ) {
			$color = '#ffffff';
		}

		?>
		<style type="text/css">
			.wpforms-form-page-modern #wpforms-form-page-page {
				border-top-color: <?php echo \esc_attr( $color ); ?>;
				background-color: <?php echo \esc_attr( $this->colors->hex_opacity( $color, 0.92 ) ); ?>;
			}

			.wpforms-form-page-classic #wpforms-form-page-page {
				border-top-color: <?php echo \esc_attr( $color ); ?>;
				background-color: <?php echo \esc_attr( $this->colors->hex_opacity( $color, 0.15 ) ); ?>;
			}

			.wpforms-form-page-modern #wpforms-form-page-page .wpforms-form-page-wrap {
				box-shadow: 0 30px 40px 0 rgba(0, 0, 0, 0.25), inset 0 4px 0 0<?php echo \esc_attr( $this->colors->hex_opacity( $color, 0.5 ) ); ?>;
			}

			.wpforms-form-page-classic #wpforms-form-page-page .wpforms-form-page-wrap {
				box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.25), inset 0 8px 0 0<?php echo \esc_attr( $this->colors->hex_opacity( $color, 0.5 ) ); ?>;
			}

			#wpforms-form-page-page .wpforms-form-page-main .wpforms-submit {
				background-color: <?php echo \esc_attr( $color ); ?>;
				color: <?php echo \esc_attr( \wpforms_light_or_dark( $color, '#444', '#fff' ) ); ?>;
				border: 1px solid<?php echo \esc_attr( $this->colors->hex_opacity( $color, -0.75 ) ); ?>;
			}

			#wpforms-form-page-page .wpforms-form-page-main .wpforms-submit:hover,
			#wpforms-form-page-page .wpforms-form-page-main .wpforms-submit:active{
				background-color: <?php echo \esc_attr( $this->colors->hex_opacity( $color, -0.85 ) ); ?>;
			}

			#wpforms-form-page-page .wpforms-field-net_promoter_score input[type=radio]:checked+label {
				background-color: <?php echo \esc_attr( \wpforms_light_or_dark( $color, $this->colors->hex_opacity( $color, -0.9 ), $color ) ); ?>;
				color: <?php echo \esc_attr( \wpforms_light_or_dark( $color, '#444', '#fff' ) ); ?>;
			}

			#wpforms-form-page-page .wpforms-field-likert_scale input[type=radio]:checked+label:after,
			#wpforms-form-page-page .wpforms-field-likert_scale input[type=checkbox]:checked+label:after {
				background-color: <?php echo \esc_attr( \wpforms_light_or_dark( $color, $this->colors->hex_opacity( $color, -0.9 ), $color ) ); ?>;
			}

			.wpforms-form-page-modern .wpforms-form-page-footer {
				color: <?php echo \esc_attr( \wpforms_light_or_dark( $color, $this->colors->hex_opacity( $color, -0.8 ), '#fff' ) ); ?>;
			}

			.wpforms-form-page-classic .wpforms-form-page-footer {
				color: <?php echo \esc_attr( \wpforms_light_or_dark( $color, $this->colors->hex_opacity( $color, -0.9 ), $this->colors->hex_opacity( $color, 0.45 ) ) ); ?>;
			}

			.wpforms-form-page-modern .cls-1 {
				fill: <?php echo \esc_attr( \wpforms_light_or_dark( $color, $this->colors->hex_opacity( $color, -0.8 ), '#fff' ) ); ?>;
			}

			.wpforms-form-page-classic .cls-1 {
				fill: <?php echo \esc_attr( \wpforms_light_or_dark( $color, $this->colors->hex_opacity( $color, -0.9 ), $this->colors->hex_opacity( $color, 0.45 ) ) ); ?>;
			}
		</style>
		<?php
	}

	/**
	 * Set body classes to apply different form styling.
	 *
	 * @since 1.0.0
	 *
	 * @param array $classes Body classes.
	 *
	 * @return array
	 */
	public function set_body_classes( $classes ) {

		if ( empty( $this->form_data['settings']['form_pages_style'] ) ) {
			return $classes;
		}

		$form_style = $this->form_data['settings']['form_pages_style'];

		if ( 'modern' === $form_style ) {
			$classes[] = 'wpforms-form-page-modern';
		}

		if ( 'classic' === $form_style ) {
			$classes[] = 'wpforms-form-page-classic';
		}

		if ( ! empty( $this->form_data['settings']['form_pages_custom_logo'] ) ) {
			$classes[] = 'wpforms-form-page-custom-logo';
		}

		return $classes;
	}

	/**
	 * Form custom logo HTML.
	 *
	 * @since 1.0.0
	 */
	public function form_logo_html() {

		if ( empty( $this->form_data['settings']['form_pages_custom_logo'] ) ) {
			return;
		}

		$custom_logo_url = wp_get_attachment_image_src( $this->form_data['settings']['form_pages_custom_logo'], 'full' );
		$custom_logo_url = isset( $custom_logo_url[0] ) ? $custom_logo_url[0] : '';

		?>
		<div class="wpforms-custom-logo">
			<img src="<?php echo \esc_url( $custom_logo_url ); ?>" alt="<?php \esc_html_e( 'Form Logo', 'wpforms-form-pages' ); ?>">
		</div>
		<?php
	}

	/**
	 * Form head area HTML.
	 *
	 * @since 1.0.0
	 */
	public function form_head_html() {

		$settings = $this->form_data['settings'];

		$title       = ! empty( $settings['form_pages_title'] ) ? $settings['form_pages_title'] : '';
		$description = ! empty( $settings['form_pages_description'] ) ? $settings['form_pages_description'] : '';

		if ( empty( $title ) && empty( $description ) ) {
			return;
		}

		// Save our original form title in a settings var so we can use it correctly in smart tags.
		$settings['form_name'] = $settings['form_title'];

		$settings['form_title'] = $title;
		$settings['form_desc']  = $description;

		\wpforms()->frontend->head( \array_merge( $this->form_data, array( 'settings' => $settings ) ), null, true, true, array() );
	}

	/**
	 * Form footer area.
	 *
	 * @since 1.0.0
	 */
	public function form_footer_html() {

		if ( ! empty( $this->form_data['settings']['form_pages_footer'] ) ) {
			printf(
				'<p>%s</p>',
				wp_kses(
					$this->form_data['settings']['form_pages_footer'],
					array(
						'em'     => array(),
						'strong' => array(),
						'a'      => array(
							'href'   => array(),
							'target' => array(),
						),
					)
				)
			);
		}

		if ( empty( $this->form_data['settings']['form_pages_brand_disable'] ) ) : ?>
		<div class="wpforms-form-page-created-with">
			<a href="https://wpforms.com/?utm_source=poweredby&utm_medium=link&utm_campaign=formpages" rel="nofollow">
			<span><?php esc_html_e( 'created with', 'wpforms-form-pages' ); ?></span>
			<?php // Require is needed to apply SVG dynamic styling. ?>
			<?php require plugin_dir_path( WPFORMS_FORM_PAGES_FILE ) . 'assets/images/wpforms-text-logo.svg'; ?>
			</a>
		</div>
		<?php endif;
	}

	/**
	 * Meta robots.
	 *
	 * @since 1.2.2
	 *
	 * @deprecated 1.4.0
	 */
	public function meta_robots() {

		_deprecated_function( __CLASS__ . '::' . __METHOD__, '1.4.0 of the Form Pages WPForms addon', __CLASS__ . '::meta_tags()' );

		$seo_plugin_enabled = false;

		if ( class_exists( 'WPSEO_Options' ) ) {
			\add_filter( 'wpseo_robots', array( $this, 'get_meta_robots' ), PHP_INT_MAX );
			$seo_plugin_enabled = true;
		}

		if ( class_exists( 'All_in_One_SEO_Pack' ) ) {
			\add_filter( 'aioseop_robots_meta', array( $this, 'get_meta_robots' ), PHP_INT_MAX );
			$seo_plugin_enabled = true;
		}

		if ( ! $seo_plugin_enabled ) {
			\add_action( 'wp_head', array( $this, 'output_meta_robots_tag' ) );
		}
	}

	/**
	 * Get meta robots value.
	 *
	 * @since 1.2.2
	 *
	 * @return string Meta robots value.
	 */
	public function get_meta_robots() {

		return \apply_filters( 'wpforms_form_pages_meta_robots_value', 'noindex,nofollow' );
	}

	/**
	 * Output meta robots tag.
	 *
	 * @since 1.2.2
	 */
	public function output_meta_robots_tag() {

		echo sprintf(
			'<meta name="robots" content="%s"/>%s',
			esc_attr( $this->get_meta_robots() ),
			"\n"
		);
	}

	/**
	 * Rank Math robots filter.
	 *
	 * @since 1.4.0
	 *
	 * @return array Robots data.
	 */
	public function get_rank_math_meta_robots() {

		return explode( ',', $this->get_meta_robots() );
	}

	/**
	 * Meta tags.
	 *
	 * @since 1.4.0
	 */
	public function meta_tags() {

		$seo_plugin_enabled = false;

		if ( class_exists( 'WPSEO_Options' ) ) {
			add_filter( 'wpseo_title', array( $this, 'get_seo_title' ), PHP_INT_MAX );
			add_filter( 'wpseo_opengraph_desc', array( $this, 'get_description' ), PHP_INT_MAX );
			add_filter( 'wpseo_twitter_description', array( $this, 'get_description' ), PHP_INT_MAX );
			add_filter( 'wpseo_robots', array( $this, 'get_meta_robots' ), PHP_INT_MAX );
			$seo_plugin_enabled = true;
		}

		if ( class_exists( 'All_in_One_SEO_Pack' ) ) {
			add_filter( 'aioseop_title', array( $this, 'get_seo_title' ), PHP_INT_MAX );
			add_filter( 'aioseop_description', array( $this, 'get_description' ), PHP_INT_MAX );
			add_filter( 'aioseop_robots_meta', array( $this, 'get_meta_robots' ), PHP_INT_MAX );
			$seo_plugin_enabled = true;
		}

		if ( class_exists( 'RankMath' ) ) {
			add_filter( 'rank_math/frontend/title', array( $this, 'get_seo_title' ), PHP_INT_MAX );
			add_filter( 'rank_math/frontend/description', array( $this, 'get_description' ), PHP_INT_MAX );
			add_filter( 'rank_math/frontend/robots', array( $this, 'get_rank_math_meta_robots' ), PHP_INT_MAX );
			$seo_plugin_enabled = true;
		}

		if ( ! $seo_plugin_enabled ) {
			add_action( 'wp_head', array( $this, 'output_meta_robots_tag' ) );
		}
	}

	/**
	 * Get title value.
	 *
	 * @since 1.4.0
	 *
	 * @return string Title value.
	 */
	public function get_title() {

		$title = ! empty( $this->form_data['settings']['form_title'] ) ? $this->form_data['settings']['form_title'] : '';
		if ( ! empty( $this->form_data['settings']['form_pages_title'] ) ) {
			$title = $this->form_data['settings']['form_pages_title'];
		}

		return wp_strip_all_tags( $title, true );
	}

	/**
	 * Get SEO plugin title value.
	 *
	 * @since 1.4.0
	 *
	 * @param string $title Original title.
	 *
	 * @return string Title value.
	 */
	public function get_seo_title( $title = '' ) {

		if ( ! empty( $this->form_data['settings']['form_title'] ) ) {
			$title = str_replace( $this->form_data['settings']['form_title'], $this->get_title(), $title );
		}

		return $title;
	}

	/**
	 * Get description value.
	 *
	 * @since 1.4.0
	 *
	 * @return string Description value.
	 */
	public function get_description() {

		return ! empty( $this->form_data['settings']['form_pages_description'] ) ?
			wp_strip_all_tags( $this->form_data['settings']['form_pages_description'], true ) :
			'';
	}

	/**
	 * Force Yoast SEO og/twitter descriptions.
	 *
	 * @since 1.0.0
	 *
	 * @deprecated 1.4.0
	 *
	 * @return string
	 */
	public function yoast_seo_description() {

		_deprecated_function( __CLASS__ . '::' . __METHOD__, '1.4.0 of the Form Pages WPForms addon', __CLASS__ . '::get_description()' );

		return ! empty( $this->form_data['settings']['form_pages_description'] ) ? wp_strip_all_tags( $this->form_data['settings']['form_pages_description'], true ) : '';
	}
}
