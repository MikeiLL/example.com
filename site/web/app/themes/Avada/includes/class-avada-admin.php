<?php
/**
 * A class to manage various stuff in the WordPress admin area.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      3.8
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * A class to manage various stuff in the WordPress admin area.
 */
class Avada_Admin {

	/**
	 * Holds the current theme version.
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 * @var string
	 */
	private $theme_version;

	/**
	 * Holds the WP_Theme object of Avada.
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 * @var WP_Theme object
	 */
	private $theme_object;

	/**
	 * Holds the URL to the Avada live demo site.
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 * @var string
	 */
	private $theme_url = 'https://avada.theme-fusion.com/';

	/**
	 * Holds the URL to ThemeFusion company site.
	 *
	 * @static
	 * @since 5.0.0
	 * @access public
	 * @var string
	 */
	public static $theme_fusion_url = 'https://theme-fusion.com/';

	/**
	 * Normalized path to includes folder.
	 *
	 * @since 5.1.0
	 *
	 * @access private
	 * @var string
	 */
	private $includes_path = '';

	/**
	 * HS code.
	 *
	 * @static
	 * @since 7.0.2
	 * @access public
	 * @var string
	 */
	public static $hubspot_code = '36Jvjh';

	/**
	 * Construct the admin object.
	 *
	 * @since 3.9.0
	 * @return void
	 */
	public function __construct() {

		$this->includes_path = wp_normalize_path( dirname( __FILE__ ) );

		$this->set_theme_version();
		$this->set_theme_object();

		$this->register_product_envato_hosted();

		add_action( 'wp_before_admin_bar_render', [ $this, 'add_wp_toolbar_menu' ] );
		add_action( 'admin_init', [ $this, 'admin_init' ] );
		add_action( 'admin_init', [ $this, 'init_permalink_settings' ] );
		add_action( 'admin_init', [ $this, 'save_permalink_settings' ] );
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_menu', [ $this, 'edit_admin_menus' ], 999 );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
		add_action( 'after_switch_theme', [ $this, 'activation_redirect' ] );

		// Add dashboard header to TO page.
		global $avada_avadaredux_args;

		add_action( 'fusionredux/page/' . $avada_avadaredux_args['option_name'] . '/form/before', [ 'Avada_Admin', 'get_admin_screen_header_to' ] );

		add_action( 'fusionredux/page/' . $avada_avadaredux_args['option_name'] . '/form/after', [ 'Avada_Admin', 'get_admin_screen_footer_to' ] );

		add_filter( 'tgmpa_notice_action_links', [ $this, 'edit_tgmpa_notice_action_links' ] );
		$prefix = ( defined( 'WP_NETWORK_ADMIN' ) && WP_NETWORK_ADMIN ) ? 'network_admin_' : '';
		add_filter( "tgmpa_{$prefix}plugin_action_links", [ $this, 'edit_tgmpa_action_links' ], 10, 4 );

		// Get demos data on theme activation.
		if ( ! class_exists( 'Avada_Importer_Data' ) ) {
			include_once Avada::$template_dir_path . '/includes/importer/class-avada-importer-data.php';
		}
		add_action( 'after_switch_theme', [ 'Avada_Importer_Data', 'get_data' ], 5 );

		// Change auto update notes for LayerSlider.
		add_action( 'layerslider_ready', [ $this, 'layerslider_overrides' ] );

		// Facebook instant articles rule set definition.
		add_filter( 'instant_articles_transformer_rules_loaded', [ $this, 'add_instant_article_rules' ] );

		// Load jQuery in the demos and plugins page.
		if ( isset( $_GET['page'] ) && ( 'avada-prebuilt-websites' === $_GET['page'] || 'avada-plugins' === $_GET['page'] ) ) { // phpcs:ignore WordPress.Security
			add_action( 'admin_enqueue_scripts', [ $this, 'add_jquery' ] );

			if ( 'avada-plugins' === $_GET['page'] ) { // phpcs:ignore WordPress.Security
				add_action( 'admin_enqueue_scripts', [ $this, 'add_jquery_ui_styles' ] );
			}
		}

		add_action( 'wp_ajax_fusion_activate_plugin', [ $this, 'ajax_activate_plugin' ] );
		// By default TGMPA doesn't load in AJAX calls.
		// Filter is applied inside a method which is hooked to 'init'.
		add_filter( 'tgmpa_load', [ $this, 'enable_tgmpa' ], 10 );

		add_action( 'wp_ajax_fusion_install_plugin', [ $this, 'ajax_install_plugin' ] );

		// Add taxonomy meta boxes.
		if ( function_exists( 'update_term_meta' ) ) {
			add_action( 'wp_loaded', [ $this, 'avada_taxonomy_meta' ] );
		}

		add_action( 'admin_init', [ $this, 'ajax_plugins_manager' ] );

		// Notice for legacy countdown.
		add_action( 'current_screen', [ $this, 'legacy_countdown' ] );
	}

	/**
	 * Adds jQuery.
	 *
	 * @access public
	 * @since 5.0.0
	 * @return void
	 */
	public function add_jquery() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-dialog' );
	}

	/**
	 * Adds jQuery UI styles.
	 *
	 * @access public
	 * @since 5.4.1
	 * @return void
	 */
	public function add_jquery_ui_styles() {
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
	}

	/**
	 * Create the admin toolbar menu items.
	 *
	 * @access public
	 * @since 3.8.0
	 * @return void
	 */
	public function add_wp_toolbar_menu() {

		global $wp_admin_bar, $avada_patcher, $fusion_settings;

		if ( current_user_can( 'edit_pages' ) ) {
			$patches              = $avada_patcher->get_patcher_checker()->get_cache();
			$avada_updates_styles = 'display:inline-block;background-color:#d54e21;color:#fff;font-size:9px;line-height:17px;font-weight:600;border-radius:10px;padding:0 6px;';

			// Done for white label plugin.
			$avada_parent_menu_name  = __( 'Avada', 'Avada' );
			$avada_parent_menu_title = '<span class="ab-label">' . esc_html( $avada_parent_menu_name ) . '</span>';
			if ( isset( $patches['avada'] ) && 1 <= $patches['avada'] ) {
				$patches_label           = '<span style="' . $avada_updates_styles . '">' . $patches['avada'] . '</span>';
				$avada_parent_menu_title = '<span class="ab-label">' . esc_html( $avada_parent_menu_name ) . ' ' . $patches_label . '</span>';
			}

			if ( ! is_admin() ) {
				$this->add_wp_toolbar_menu_item(
					apply_filters( 'avada_wpadminbar_menu_title', $avada_parent_menu_title ),
					false,
					admin_url( 'admin.php?page=avada' ),
					[
						'class' => 'avada-menu',
					],
					'avada'
				);
			}

			$this->add_wp_toolbar_menu_item( esc_html__( 'Global Options', 'Avada' ), 'avada', admin_url( 'themes.php?page=avada_options' ) );
			$this->add_wp_toolbar_menu_item( esc_html__( 'Websites', 'Avada' ), 'avada', admin_url( 'admin.php?page=avada-prebuilt-websites' ) );
			$this->add_wp_toolbar_menu_item( esc_html__( 'Layouts', 'Avada' ), 'avada', admin_url( 'admin.php?page=avada-layouts' ) );
			$this->add_wp_toolbar_menu_item( esc_html__( 'Icons', 'Avada' ), 'avada', admin_url( 'admin.php?page=avada-icons' ) );

			if ( class_exists( 'Fusion_Form_Builder' ) && Fusion_Form_Builder::is_enabled() ) {
				$this->add_wp_toolbar_menu_item( esc_html__( 'Forms', 'Avada' ), 'avada', admin_url( 'admin.php?page=avada-forms' ) );
			}

			if ( $fusion_settings->get( 'status_fusion_slider' ) ) {
				$this->add_wp_toolbar_menu_item( esc_html__( 'Sliders', 'Avada' ), 'avada', admin_url( 'edit-tags.php?taxonomy=slide-page&post_type=slide' ) );
			}

			$this->add_wp_toolbar_menu_item( esc_html__( 'Library', 'Avada' ), 'avada', admin_url( 'admin.php?page=avada-library' ) );

			if ( isset( $patches['avada'] ) && 1 <= $patches['avada'] ) {
				$patches_label = '<span style="' . $avada_updates_styles . '">' . $patches['avada'] . '</span>';
				/* translators: The patches numeric counter. */
				$this->add_wp_toolbar_menu_item( sprintf( esc_html__( 'Patcher %s', 'Avada' ), $patches_label ), 'avada', admin_url( 'admin.php?page=avada-patcher' ) );
			}

			$this->add_wp_toolbar_menu_item( esc_html__( 'Plugins', 'Avada' ), 'avada', admin_url( 'admin.php?page=avada-plugins' ) );
			$this->add_wp_toolbar_menu_item( esc_html__( 'Status', 'Avada' ), 'avada', admin_url( 'admin.php?page=avada-status' ) );

			if ( AVADA_DEV_MODE ) {
				$on_click = 'jQuery.post( "' . esc_url( admin_url( 'admin-ajax.php' ) ) . '", { "action": "fusion_reset_all_caches" }, function() {alert("' . esc_html__( 'All Avada caches have been reset.', 'Avada' ) . '");} );';
				$this->add_wp_toolbar_menu_item(
					esc_html__( 'Reset Avada Caches', 'Avada' ),
					'avada',
					'#',
					[
						'onclick' => $on_click,
						'target'  => '_self',
					]
				);
			}
		}
	}

	/**
	 * Add the top-level menu item to the adminbar.
	 *
	 * @access public
	 * @since 3.8.0
	 * @param  string       $title       The title.
	 * @param  string|false $parent      The parent node.
	 * @param  string       $href        Link URL.
	 * @param  array        $custom_meta An array of custom meta to apply.
	 * @param  string       $custom_id   A custom ID.
	 */
	public function add_wp_toolbar_menu_item( $title, $parent = false, $href = '', $custom_meta = [], $custom_id = '' ) {

		global $wp_admin_bar;

		if ( current_user_can( 'edit_pages' ) ) {
			if ( ! is_super_admin() || ! is_admin_bar_showing() ) {
				return;
			}

			// Set custom ID.
			if ( $custom_id ) {
				$id = $custom_id;
			} else { // Generate ID based on $title.
				$id = strtolower( str_replace( ' ', '-', $title ) );
			}

			// Links from the current host will open in the current window.
			$meta = strpos( $href, site_url() ) !== false ? [] : [
				'target' => '_blank',
			]; // External links open in new tab/window.
			$meta = array_merge( $meta, $custom_meta );

			$wp_admin_bar->add_node(
				[
					'parent' => $parent,
					'id'     => $id,
					'title'  => $title,
					'href'   => $href,
					'meta'   => $meta,
				]
			);
		}

	}

	/**
	 * Modify the menu.
	 *
	 * @access public
	 * @since 3.8.0
	 * @return void
	 */
	public function edit_admin_menus() {
		global $submenu;

		// Change Avada to Dashboard.
		if ( current_user_can( 'switch_themes' ) ) {
			$submenu['avada'][0][0] = esc_html__( 'Dashboard', 'Avada' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		}

		if ( isset( $submenu['themes.php'] ) && ! empty( $submenu['themes.php'] ) ) {
			foreach ( $submenu['themes.php'] as $key => $value ) {
				// Remove "Header" submenu.
				if ( isset( $value[2] ) && false !== strpos( $value[2], 'customize.php' ) && false !== strpos( $value[2], '=header_image' ) ) {
					unset( $submenu['themes.php'][ $key ] );
				}
				// Remove "Background" submenu.
				if ( isset( $value[2] ) && false !== strpos( $value[2], 'customize.php' ) && false !== strpos( $value[2], '=background_image' ) ) {
					unset( $submenu['themes.php'][ $key ] );
				}
			}

			// Reorder items in the array.
			$submenu['themes.php'] = array_values( $submenu['themes.php'] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		}

		// Remove TGMPA menu from Appearance.
		remove_submenu_page( 'themes.php', 'install-required-plugins' );

	}

	/**
	 * Redirect to admin page on theme activation.
	 *
	 * @access public
	 * @since 3.8.0
	 * @return void
	 */
	public function activation_redirect() {
		if ( current_user_can( 'switch_themes' ) ) {
			// Do not redirect if a migration is needed for Avada 5.0.0.
			if ( true === Fusion_Builder_Migrate::needs_migration() ) {
				return;
			}
			header( 'Location:' . admin_url( 'admin.php?page=avada' ) );
		}
	}

	/**
	 * Actions to run on initial theme activation.
	 *
	 * @access public
	 * @since 3.8.0
	 * @return void
	 */
	public function admin_init() {

		if ( current_user_can( 'switch_themes' ) ) {

			if ( isset( $_GET['avada-deactivate'] ) && 'deactivate-plugin' === $_GET['avada-deactivate'] ) { // phpcs:ignore WordPress.Security
				check_admin_referer( 'avada-deactivate', 'avada-deactivate-nonce' );

				$plugins = Avada_TGM_Plugin_Activation::$instance->plugins;

				foreach ( $plugins as $plugin ) {
					if ( isset( $_GET['plugin'] ) && $plugin['slug'] === $_GET['plugin'] ) {
						deactivate_plugins( $plugin['file_path'] );
					}
				}
			}
			if ( isset( $_GET['avada-activate'] ) && 'activate-plugin' === $_GET['avada-activate'] ) {
				check_admin_referer( 'avada-activate', 'avada-activate-nonce' );

				$plugins = Avada_TGM_Plugin_Activation::$instance->plugins;

				foreach ( $plugins as $plugin ) {
					if ( isset( $_GET['plugin'] ) && $plugin['slug'] === $_GET['plugin'] ) {
						activate_plugin( $plugin['file_path'] );

						if ( 'leadin' === $_GET['plugin'] ) {
							add_option( 'hubspot_affiliate_code', self::$hubspot_code );
						}

						wp_safe_redirect( admin_url( 'admin.php?page=avada-plugins' ) );
						exit;
					}
				}
			}
		}
	}

	/**
	 * Adds the admin menu.
	 *
	 * @access public
	 * @since 5.0
	 * @return void
	 */
	public function admin_menu() {
		global $submenu;

		if ( current_user_can( 'switch_themes' ) ) {

			// Work around for theme check.
			$avada_menu_page_creation_method    = 'add_menu_page';
			$avada_submenu_page_creation_method = 'add_submenu_page';

			$dashboard         = $avada_menu_page_creation_method( 'Avada Website Builder', 'Avada', 'switch_themes', 'avada', [ $this, 'dashboard_screen' ], 'dashicons-avada', '2.111111' );
			$options           = $avada_submenu_page_creation_method( 'avada', esc_html__( 'Options', 'Avada' ), esc_html__( 'Options', 'Avada' ), 'switch_themes', 'themes.php?page=avada_options', '', 1 );
			$prebuilt_websites = $avada_submenu_page_creation_method( 'avada', esc_html__( 'Websites', 'Avada' ), esc_html__( 'Websites', 'Avada' ), 'manage_options', 'avada-prebuilt-websites', [ $this, 'prebuilt_websites_tab' ], 2 );

			// Add in pages from Avada Builder.
			do_action( 'avada_add_admin_menu_pages' );

			$maintenance = $avada_submenu_page_creation_method( 'avada', esc_html__( 'Maintenance', 'Avada' ), esc_html__( 'Maintenance', 'Avada' ), 'manage_options', 'avada-maintenance', null, 8 );

			// Patcher is added in through patcher class, order is 9.
			do_action( 'avada_add_admin_menu_maintenance_pages' );

			$plugins_callback = [ $this, 'plugins_tab' ];
			if ( isset( $_GET['tgmpa-install'] ) || isset( $_GET['tgmpa-update'] ) ) { // phpcs:ignore WordPress.Security
				if ( isset( $_GET['plugin'] ) && 'leadin' === $_GET['plugin'] ) { // phpcs:ignore WordPress.Security
					add_option( 'hubspot_affiliate_code', self::$hubspot_code );
				}
				require_once $this->includes_path . '/class-avada-tgm-plugin-activation.php';
				remove_action( 'admin_notices', [ $GLOBALS['avada_tgmpa'], 'notices' ] );
				$plugins_callback = [ $GLOBALS['avada_tgmpa'], 'install_plugins_page' ];
			}

			$plugins = $avada_submenu_page_creation_method( 'avada', esc_html__( 'Plugins / Add-ons', 'Avada' ), esc_html__( 'Plugins / Add-ons', 'Avada' ), 'install_plugins', 'avada-plugins', $plugins_callback, 10 );
			$support = $avada_submenu_page_creation_method( 'avada', esc_html__( 'Support', 'Avada' ), esc_html__( 'Support', 'Avada' ), 'manage_options', 'avada-support', [ $this, 'support_tab' ], 11 );
			$status  = $avada_submenu_page_creation_method( 'avada', esc_html__( 'Status', 'Avada' ), esc_html__( 'Status', 'Avada' ), 'switch_themes', 'avada-status', [ $this, 'status_tab' ], 12 );

			if ( ! class_exists( 'FusionReduxFrameworkPlugin' ) ) {
				$theme_options_global = $avada_submenu_page_creation_method( 'themes.php', esc_html__( 'Options', 'Avada' ), esc_html__( 'Options', 'Avada' ), 'switch_themes', 'themes.php?page=avada_options' );
			}

			if ( array_key_exists( 'avada', $submenu ) ) {
				foreach ( $submenu['avada'] as $key => $value ) {
					$k = array_search( 'avada-maintenance', $value, true );
					if ( $k ) {
						$submenu['avada'][ $key ][ $k ] = ( current_user_can( $submenu['avada'][ $key ][1] ) ) ? esc_url( admin_url( 'admin.php?page=avada-patcher' ) ) : ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
					}
				}
			}

			add_action( 'admin_print_styles-' . $dashboard, [ $this, 'styles_general' ] );
			add_action( 'admin_print_scripts-' . $dashboard, [ $this, 'scripts_general' ] );

			add_action( 'admin_print_styles', [ $this, 'styles_theme_options' ] );
			add_action( 'admin_print_scripts', [ $this, 'scripts_theme_options' ] );

			add_action( 'admin_print_styles-' . $prebuilt_websites, [ $this, 'styles_general' ] );
			add_action( 'admin_print_scripts-' . $prebuilt_websites, [ $this, 'scripts_general' ] );

			add_action( 'admin_print_styles-' . $maintenance, [ $this, 'styles_general' ] );

			add_action( 'admin_print_styles-' . $plugins, [ $this, 'styles_general' ] );
			add_action( 'admin_print_scripts-' . $plugins, [ $this, 'scripts_general' ] );

			add_action( 'admin_print_styles-' . $support, [ $this, 'styles_general' ] );

			add_action( 'admin_print_styles-' . $status, [ $this, 'styles_general' ] );
			add_action( 'admin_print_scripts-' . $status, [ $this, 'scripts_general' ] );

			add_action( 'admin_footer', 'fusion_the_admin_font_async' );
		}
	}

	/**
	 * Include file.
	 *
	 * @access public
	 * @since 5.0
	 * @return void
	 */
	public function dashboard_screen() {
		require_once $this->includes_path . '/admin-screens/dashboard.php';
	}

	/**
	 * Include file.
	 *
	 * @access public
	 * @since 5.0
	 * @return void
	 */
	public function prebuilt_websites_tab() {
		require_once $this->includes_path . '/admin-screens/prebuilt-websites.php';
	}

	/**
	 * Include file.
	 *
	 * @access public
	 * @since 5.0
	 * @return void
	 */
	public function plugins_tab() {
		require_once $this->includes_path . '/admin-screens/plugins.php';
	}

	/**
	 * Include file.
	 *
	 * @access public
	 * @since 5.0
	 * @return void
	 */
	public function support_tab() {
		require_once $this->includes_path . '/admin-screens/support.php';
	}

	/**
	 * Include file.
	 *
	 * @access public
	 * @since 5.0
	 * @return void
	 */
	public function status_tab() {
		require_once $this->includes_path . '/admin-screens/status.php';
	}

	/**
	 * Renders the admin screens header with title, logo and tabs.
	 *
	 * @static
	 * @access public
	 * @since 5.0
	 * @param string $screen The current screen.
	 * @return void
	 */
	public static function get_admin_screens_header( $screen = 'welcome' ) {
		if ( ! current_user_can( 'switch_themes' ) ) {
			return;
		}

		if ( 'welcome' === $screen ) {
			Avada()->registration->check_registration();
		}

		$screen_classes  = 'wrap avada-dashboard avada-db-' . $screen;
		$screen_classes .= Avada()->registration->appear_registered() ? ' avada-registration-completed' : ' avada-registration-pending';

		if ( in_array( $screen, [ 'builder-options', 'layout-sections', 'layouts', 'icons', 'forms', 'form-entries', 'library' ], true ) ) {
			$screen_classes .= ' fusion-builder-wrap';

			if ( 'builder-options' === $screen ) {
				$screen_classes .= ' fusion-builder-settings';
			}
		} elseif ( in_array( $screen, [ 'sliders', 'slides', 'slide-edit' ], true ) ) {
			$screen_classes .= ' avada-db-edit-screen';
		} else {
			$screen_classes .= ' about-wrap';
		}
		?>
		<div class="<?php echo esc_html( $screen_classes ); ?>">
			<header class="avada-db-header-main">
				<div class="avada-db-header-main-container">
					<a class="avada-db-logo" href="<?php echo esc_url( admin_url( 'admin.php?page=avada' ) ); ?>" aria-label="<?php esc_attr_e( 'Link to Avada dashboard', 'Avada' ); ?>">
						<i class="avada-db-logo-icon fusiona-avada-logo"></i>
						<div class="avada-db-logo-image">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo@2x.png' ); ?>" alt="<?php esc_html_e( 'Avada', 'Avada' ); ?>" width="115" height="25" />
						</div>
					</a>
					<nav class="avada-db-menu-main">
						<ul class="avada-db-menu">
							<li class="avada-db-menu-item avada-db-menu-item-options"><a class="avada-db-menu-item-link<?php echo ( 'to' === $screen || 'builder-options' === $screen ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'to' === $screen ) ? '#' : admin_url( 'themes.php?page=avada_options' ) ); ?>" ><span class="avada-db-menu-item-text"><?php esc_html_e( 'Options', 'Avada' ); ?></span></a>
								<ul class="avada-db-menu-sub avada-db-menu-sub-options">
									<li class="avada-db-menu-sub-item">
										<a class="avada-db-menu-sub-item-link<?php echo ( 'to' === $screen ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'to' === $screen ) ? '#' : admin_url( 'themes.php?page=avada_options' ) ); ?>">
											<i class="fusiona-cog"></i>
											<div class="avada-db-menu-sub-item-text">
												<div class="avada-db-menu-sub-item-label"><?php esc_html_e( 'Global Options', 'fusion-builder' ); ?></div>
												<div class="avada-db-menu-sub-item-desc"><?php esc_html_e( 'Edit the global site options & styles.', 'fusion-builder' ); ?></div>
											</div>
										</a>
									</li>
									<?php do_action( 'avada_dashboard_main_menu_options_sub_menu_items', $screen ); ?>
								</ul>
							</li>
							<li class="avada-db-menu-item avada-db-menu-item-prebuilt-websites"><a class="avada-db-menu-item-link<?php echo ( 'prebuilt-websites' === $screen ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'prebuilt-websites' === $screen ) ? '#' : admin_url( 'admin.php?page=avada-prebuilt-websites' ) ); ?>" ><span class="avada-db-menu-item-text"><?php esc_html_e( 'Websites', 'Avada' ); ?></span></a></li>
							<li class="avada-db-menu-item avada-db-menu-item-maintenance"><a class="avada-db-menu-item-link<?php echo ( in_array( $screen, [ 'patcher', 'plugins', 'support', 'status' ], true ) ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'patcher' === $screen ) ? '#' : admin_url( 'admin.php?page=avada-patcher' ) ); ?>"><span class="avada-db-menu-item-text"><?php esc_html_e( 'Maintenance', 'Avada' ); ?></span><span class="avada-db-maintenance-counter"></span></a>
								<ul class="avada-db-menu-sub avada-db-menu-sub-maintenance">
									<li class="avada-db-menu-sub-item avada-db-menu-sub-item-patcher">
										<a class="avada-db-menu-sub-item-link<?php echo ( 'patcher' === $screen ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'patcher' === $screen ) ? '#' : admin_url( 'admin.php?page=avada-patcher' ) ); ?>">
											<i class="fusiona-patcher"></i>
											<div class="avada-db-menu-sub-item-text">
												<div class="avada-db-menu-sub-item-label"><?php esc_html_e( 'Patcher', 'Avada' ); ?></div>
												<div class="avada-db-menu-sub-item-desc"><?php esc_html_e( 'Apply patches for your version.', 'Avada' ); ?></div>
											</div>
										</a>
									</li>
									<li class="avada-db-menu-sub-item avada-db-menu-sub-item-plugins">
										<a class="avada-db-menu-sub-item-link<?php echo ( 'plugins' === $screen ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'plugins' === $screen ) ? '#' : admin_url( 'admin.php?page=avada-plugins' ) ); ?>">
											<i class="fusiona-plugins"></i>
											<div class="avada-db-menu-sub-item-text">
												<div class="avada-db-menu-sub-item-label"><?php esc_html_e( 'Plugins & Add-ons', 'Avada' ); ?></div>
												<div class="avada-db-menu-sub-item-desc"><?php esc_html_e( 'Manage plugins & get add-ons.', 'Avada' ); ?></div>
											</div>
										</a>
									</li>
									<li class="avada-db-menu-sub-item avada-db-menu-sub-item-changelog">
										<a class="avada-db-menu-sub-item-link avada-db-changelog-link" href="#">
											<i class="fusiona-documentation"></i>
											<div class="avada-db-menu-sub-item-text">
												<div class="avada-db-menu-sub-item-label"><?php esc_html_e( 'Changelog', 'Avada' ); ?></div>
												<div class="avada-db-menu-sub-item-desc"><?php esc_html_e( 'View the Avada changelog.', 'Avada' ); ?></div>
											</div>
										</a>
									</li>
									<li class="avada-db-menu-sub-item avada-db-menu-sub-item-support">
										<a class="avada-db-menu-sub-item-link<?php echo ( 'support' === $screen ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'support' === $screen ) ? '#' : admin_url( 'admin.php?page=avada-support' ) ); ?>">
											<i class="fusiona-help-outlined"></i>
											<div class="avada-db-menu-sub-item-text">
												<div class="avada-db-menu-sub-item-label"><?php esc_html_e( 'Support', 'Avada' ); ?></div>
												<div class="avada-db-menu-sub-item-desc"><?php esc_html_e( 'View the different support channels', 'Avada' ); ?></div>
											</div>
										</a>
									</li>
									<li class="avada-db-menu-sub-item avada-db-menu-sub-item-status">
										<a class="avada-db-menu-sub-item-link<?php echo ( 'status' === $screen ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'status' === $screen ) ? '#' : admin_url( 'admin.php?page=avada-status' ) ); ?>">
											<i class="fusiona-status"></i>
											<div class="avada-db-menu-sub-item-text">
												<div class="avada-db-menu-sub-item-label"><?php esc_html_e( 'Status', 'Avada' ); ?></div>
												<div class="avada-db-menu-sub-item-desc"><?php esc_html_e( 'View the system status of your install.', 'Avada' ); ?></div>
											</div>
										</a>
									</li>
								</ul>
							</li>
						</ul>
					</nav>
					<?php if ( class_exists( 'FusionBuilder' ) ) : ?>
					<a class="button button-primary avada-db-live" href="<?php echo esc_url( add_query_arg( 'fb-edit', '1', get_site_url() ) ); ?>"><?php esc_html_e( 'Avada Live', 'Avada' ); ?> </a>
					<?php endif; ?>
				</div>
			</header>

			<header class="avada-db-header-sticky">
				<div class="avada-db-menu-sticky">
					<div class="avada-db-menu-sticky-label<?php echo ( Avada()->registration->appear_registered() ) ? ' completed' : ''; ?>">
						<span class="avada-db-version"><span><?php echo esc_html( apply_filters( 'avada_db_version', 'v' . esc_html( AVADA_VERSION ) ) ); ?></span> |</span>
						<span class="avada-db-version-label avada-db-registered"><?php esc_html_e( 'Registered', 'Avada' ); ?></span>
						<span class="avada-db-version-label avada-db-unregistered"><?php esc_html_e( 'Unregistered', 'Avada' ); ?></span>
					</div>

					<?php if ( class_exists( 'FusionBuilder' ) || class_exists( 'FusionCore_Plugin' ) ) : ?>
						<nav class="avada-db-menu-sticky-nav">
							<ul class="avada-db-menu">
								<?php do_action( 'avada_dashboard_sticky_menu_items', $screen ); ?>
							</ul>
						</nav>
					<?php endif; ?>
				</div>
			</header>

			<div class="avada-db-demos-notices"><h1></h1> <?php do_action( 'avada_dashboard_notices' ); ?></div>
		<?php
	}

	/**
	 * Renders the admin screens footer.
	 *
	 * @static
	 * @access public
	 * @since 7.0
	 * @param string $screen The current screen.
	 * @return void
	 */
	public static function get_admin_screens_footer( $screen = 'dashboard' ) {
		if ( ! current_user_can( 'switch_themes' ) ) {
			return;
		}
		?>
			<?php if ( 'slide-edit' !== $screen ) : ?>
				<footer class="avada-db-footer">
					<div class="avada-db-footer-top">
						<nav class="avada-db-footer-menu">
							<span class="avada-db-footer-company"><i class="fusiona-TFicon"></i><strong><?php esc_html_e( 'ThemeFusion', 'Avada' ); ?></strong></span>
							<ul>
								<li>
									<a href="<?php echo esc_url( self::$theme_fusion_url ) . 'documentation/avada/'; ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Documentation', 'Avada' ); ?></a>
								</li>
								<li>
									<a href="<?php echo esc_url( self::$theme_fusion_url ) . 'documentation/avada/videos/'; ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Video Tutorials', 'Avada' ); ?></a>
								</li>
								<li>
									<a href="<?php echo esc_url_raw( self::$theme_fusion_url ) . 'support/submit-a-ticket/'; ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Submit A Ticket', 'Avada' ); ?></a>
								</li>
							</ul>
						</nav>

						<?php echo self::get_social_media_links(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>

					<div class="avada-db-footer-bottom">
						<div class="avada-db-footer-thanks"><?php esc_html_e( 'Thank you for choosing Avada. We are honored and are fully dedicated to making your experience perfect.', 'Avada' ); ?></div>
						<nav class="avada-db-footer-menu-bottom">
							<a href="<?php echo esc_url_raw( self::$theme_fusion_url ) . 'support-policy/'; ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Support Policy', 'Avada' ); ?></a>
						</nav>
					</div>
				</footer>
			<?php endif; ?>

			<div class="avada-db-changelog avada-db-card avada-db-card-first">
				<div class="avada-db-changelog-heading">
					<h2><?php esc_html_e( 'Avada Changelog', 'Avada' ); ?></h2>
					<i class="fusiona-cross"></i>
				</div>
				<iframe src="<?php echo esc_url( get_template_directory_uri() . '/changelog.txt' ); ?>"></iframe>
			</div>


			<div class="avada-db-overlay"></div>
			<script type="text/javascript">
				jQuery( document ).ready( function() {
					setTimeout( function() {
						jQuery( '.avada-db-notice' ).not( '.inline, .below-h2' ).insertAfter( '.avada-db-demos-notices h1' );
					}, 10 );

					jQuery( '.avada-db-changelog-link' ).on( 'click', function( e ) {
						e.preventDefault();

						jQuery( '.avada-db-changelog' ).show();
						jQuery( '.avada-db-overlay' ).show();
					} );

					jQuery( '.avada-db-overlay, .avada-db-changelog .fusiona-cross' ).on( 'click', function( e ) {
						e.preventDefault();

						jQuery( '.avada-db-changelog' ).hide();
						jQuery( '.avada-db-overlay' ).hide();
					} );

					jQuery( document ).on( 'keydown', function( e ) {
						if ( 'block' === jQuery( '.avada-db-overlay' ).css( 'display' ) && 27 === e.keyCode ) {
							jQuery( '.avada-db-changelog' ).hide();
							jQuery( '.avada-db-overlay' ).hide();
						}
					} );
				} );
			</script>
		</div>
		<?php
	}

	/**
	 * Renders the admin screens header for TO page.
	 *
	 * @static
	 * @access public
	 * @since 7.0
	 * @return void
	 */
	public static function get_admin_screen_header_to() {
		self::get_admin_screens_header( 'to' );
		?>
		</div>
		<?php
	}

	/**
	 * Renders the admin screens footer for TO page.
	 *
	 * @static
	 * @access public
	 * @since 7.0
	 * @return void
	 */
	public static function get_admin_screen_footer_to() {
		?>
		<div class="avada-dashboard">
		<?php
		do_action( 'avada_admin_screen_footer_to' );

		self::get_admin_screens_footer();
	}

	/**
	 * Get social media links
	 *
	 * @static
	 * @access public
	 * @since 7.0
	 * @return string The social media link markup
	 */
	public static function get_social_media_links() {
		$social_media_markup = '<div class="avada-db-footer-social-media">
		<a href="https://www.facebook.com/ThemeFusion-101565403356430/" target="_blank" class="avada-db-social-icon dashicons dashicons-facebook-alt"></a>
		<a href="https://twitter.com/theme_fusion" target="_blank" class="avada-db-social-icon dashicons dashicons-twitter"></a>
		<a href="https://www.instagram.com/themefusion/" target="_blank" class="avada-db-social-icon dashicons dashicons-instagram"></a>
		<a href="https://www.youtube.com/channel/UC_C7uAOAH9RMzZs-CKCZ62w" target="_blank" class="avada-db-social-icon fusiona-youtube"></a>
		</div>';

		return apply_filters( 'fusion_admin_social_media_links', $social_media_markup );
	}

	/**
	 * Enqueues scripts.
	 *
	 * @since 5.0.3
	 * @access  public
	 * @return void
	 */
	public function admin_scripts() {
		global $pagenow;
		$version = Avada::get_theme_version();

		wp_enqueue_style( 'avada-wp-admin-css', get_template_directory_uri() . '/assets/admin/css/admin.css', false, $version );
		wp_enqueue_style( 'fusion-font-icomoon', FUSION_LIBRARY_URL . '/assets/fonts/icomoon-admin/icomoon.css', false, $version, 'all' );

		if ( current_user_can( 'switch_themes' ) ) {

			// Add script to check for fusion option slider changes.
			if ( 'post-new.php' === $pagenow || 'edit.php' === $pagenow || 'post.php' === $pagenow ) {
				wp_enqueue_script( 'slider_preview', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/fusion-builder-slider-preview.js', [], $version, true );
			}

			if ( 'nav-menus.php' === $pagenow || 'widgets.php' === $pagenow ) {
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
				wp_enqueue_script( 'jquery-color' );
				wp_enqueue_script( 'wp-color-picker' );
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_style( 'fusion-font-icomoon', FUSION_LIBRARY_URL . '/assets/fonts/icomoon-admin/icomoon.css', false, $version, 'all' );
				// ColorPicker Alpha Channel.
				wp_enqueue_script( 'wp-color-picker-alpha', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/wp-color-picker-alpha.js', [ 'wp-color-picker', 'jquery-color' ], $version, false );

				wp_enqueue_style( 'fontawesome', Fusion_Font_Awesome::get_backend_css_url(), [], $version );

				if ( '1' === Avada()->settings->get( 'fontawesome_v4_compatibility' ) ) {
					wp_enqueue_script( 'fontawesome-shim-script', FUSION_LIBRARY_URL . '/assets/fonts/fontawesome/js/fa-v4-shims.js', [], $version, false );
					wp_enqueue_style( 'fontawesome-shims', Fusion_Font_Awesome::get_backend_shims_css_url(), [], $version );
				}
				if ( '1' === Avada()->settings->get( 'status_fontawesome_pro' ) ) {
					wp_enqueue_script( 'fontawesome-search-script', FUSION_LIBRARY_URL . '/assets/fonts/fontawesome/js/icons-search-pro.js', [], $version, false );
				} else {
					wp_enqueue_script( 'fontawesome-search-script', FUSION_LIBRARY_URL . '/assets/fonts/fontawesome/js/icons-search-free.js', [], $version, false );
				}
				wp_enqueue_script( 'fuse-script', FUSION_LIBRARY_URL . '/assets/min/js/library/fuse.js', [], $version, false );

				wp_enqueue_script( 'fusion-menu-options', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/fusion-menu-options.js', [ 'selectwoo-js' ], $version, true );

				wp_localize_script(
					'fusion-menu-options',
					'fusionMenuConfig',
					[
						'fontawesomeicons'   => fusion_get_icons_array(),
						'fontawesomesubsets' => Avada()->settings->get( 'status_fontawesome' ),
						'customIcons'        => fusion_get_custom_icons_array(),

						/* translators: The iconset name. */
						'no_results_in'      => esc_html__( 'No Results in "%s"', 'fusion-builder' ),
					]
				);
			}

			// @codingStandardsIgnoreLine
			//wp_enqueue_script( 'beta-test', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/avada-beta-testing.js', [], $version, true );
		}

		// Color palette should be available to all users.
		if ( in_array( $pagenow, [ 'themes.php', 'nav-menus.php', 'widgets.php', 'post-new.php', 'edit.php', 'post.php', 'edit-tags.php', 'term.php' ], true ) ) {
			wp_localize_script(
				'wp-color-picker',
				'fusionColorPalette',
				[
					'color_palette' => fusion_get_option( 'color_palette' ),
				]
			);
		}
	}

	/**
	 * Enqueues styles.
	 *
	 * @access public
	 * @return void
	 */
	public function styles_general() {
		$ver = Avada::get_theme_version();
		wp_enqueue_style( 'avada_admin_css', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/css/avada-admin.css', [], $ver );
	}

	/**
	 * Enqueues scripts & styles.
	 *
	 * @access public
	 * @return void
	 */
	public function scripts_general() {
		$ver = Avada::get_theme_version();

		wp_enqueue_script( 'avada_zeroclipboard', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/zeroclipboard.js', [], $ver, false );
		wp_enqueue_script( 'tiptip_jquery', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/tiptip.jquery.min.js', [], $ver, false );
		wp_enqueue_script( 'avada_admin_js', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/avada-admin.js', [ 'tiptip_jquery', 'avada_zeroclipboard', 'underscore' ], $ver, true );
		wp_localize_script( 'avada_admin_js', 'avadaAdminL10nStrings', $this->get_admin_script_l10n_strings() );
	}

	/**
	 * Enqueues sstyles.
	 *
	 * @access  public
	 * @return void
	 */
	public function styles_theme_options() {
		$ver    = Avada::get_theme_version();
		$screen = get_current_screen();

		if ( 'appearance_page_avada_options' === $screen->id ) {
			$this->styles_general();
		}
	}

	/**
	 * Enqueues scripts & styles.
	 *
	 * @access  public
	 * @return void
	 */
	public function scripts_theme_options() {
		$ver    = Avada::get_theme_version();
		$screen = get_current_screen();

		if ( 'appearance_page_avada_options' === $screen->id ) {
			wp_enqueue_script( 'avada_theme_options_menu_mod', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/avada-theme-options-menu-mod.js', [ 'jquery' ], $ver, false );
		}
	}

	/**
	 * AJAX callback method. Used to activate plugin.
	 *
	 * @access public
	 * @since 5.2
	 * @return void
	 */
	public function ajax_activate_plugin() {

		if ( current_user_can( 'activate_plugins' ) ) {

			if ( isset( $_GET['avada_activate'] ) && 'activate-plugin' === $_GET['avada_activate'] ) { // phpcs:ignore WordPress.Security

				check_admin_referer( 'avada-activate', 'avada_activate_nonce' );

				$plugins = Avada_TGM_Plugin_Activation::$instance->plugins;

				foreach ( $plugins as $plugin ) {
					if ( isset( $_GET['plugin'] ) && $plugin['slug'] === $_GET['plugin'] ) {
						$result   = activate_plugin( $plugin['file_path'] );
						$response = [];

						// Make sure woo setup won't run after this.
						if ( 'woocommerce' === $_GET['plugin'] ) {
							delete_transient( '_wc_activation_redirect' );
						}

						// Make sure bbpress welcome screen won't run after this.
						if ( 'bbpress' === $_GET['plugin'] ) {
							delete_transient( '_bbp_activation_redirect' );
						}

						// Make sure Convert Plus welcome screen won't run after this.
						if ( 'convertplug' === $_GET['plugin'] ) {
							delete_option( 'convert_plug_redirect' );
						}

						// Make sure events calendar welcome screen won't run after this.
						if ( 'the-events-calendar' === $_GET['plugin'] ) {
							delete_transient( '_tribe_events_activation_redirect' );
						}

						// Add HubSpot option.
						if ( 'leadin' === $_GET['plugin'] ) {
							add_option( 'hubspot_affiliate_code', self::$hubspot_code );

							// Make sure Hubspot welcome screen won't run after this.
							delete_transient( 'leadin_redirect_after_activation' );
						}

						if ( ! is_wp_error( $result ) ) {
							$response['message'] = 'plugin activated';
							$response['error']   = false;
						} else {
							$response['message'] = $result->get_error_message();
							$response['error']   = true;
						}

						echo wp_json_encode( $response );
						die();
					}
				}
			}
		}
	}

	/**
	 * AJAX callback method.
	 * Used to install and activate plugin.
	 *
	 * @access public
	 * @since 5.0
	 * @return void
	 */
	public function ajax_install_plugin() {

		if ( current_user_can( 'install_plugins' ) ) {

			if ( isset( $_GET['avada_activate'] ) && 'activate-plugin' === $_GET['avada_activate'] ) { // phpcs:ignore WordPress.Security

				check_admin_referer( 'avada-activate', 'avada_activate_nonce' );

				if ( isset( $_GET['plugin'] ) && 'leadin' === $_GET['plugin'] ) { // phpcs:ignore WordPress.Security
					add_option( 'hubspot_affiliate_code', self::$hubspot_code );
				}

				// Unfortunately 'output buffering' doesn't work here as eventually 'wp_ob_end_flush_all' function is called.
				$GLOBALS['avada_tgmpa']->install_plugins_page();

				die();
			}
		}

	}

	/**
	 * Get the plugin link.
	 *
	 * @access  public
	 * @param array $item The plugin in question.
	 * @return  array
	 */
	public function plugin_link( $item ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once wp_normalize_path( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$installed_plugins = get_plugins();

		$item['sanitized_plugin'] = $item['name'];

		$actions = [];

		// We have a repo plugin.
		if ( ! $item['version'] ) {
			$item['version'] = Avada_TGM_Plugin_Activation::$instance->does_plugin_have_update( $item['slug'] );
		}

		$disable_class         = '';
		$data_version          = '';
		$fusion_builder_action = '';

		if ( 'fusion-builder' === $item['slug'] && false !== get_option( 'avada_previous_version' ) ) {
			$fusion_core_version = Avada_TGM_Plugin_Activation::$instance->get_installed_version( Avada_TGM_Plugin_Activation::$instance->plugins['fusion-core']['slug'] );

			if ( version_compare( $fusion_core_version, '3.0', '<' ) ) {
				$disable_class         = ' disabled fusion-builder';
				$data_version          = ' data-version="' . $fusion_core_version . '"';
				$fusion_builder_action = [
					'install' => '<div class="fusion-builder-plugin-install-nag">' . esc_html__( 'Please update Avada Core to latest version.', 'Avada' ) . '</div>',
				];
			} elseif ( ! Avada()->registration->should_show( 'plugins' ) ) {
				$disable_class = ' disabled avada-no-token';
			}
		} elseif ( $item['premium'] && ! Avada()->registration->should_show( 'plugins' ) ) {
			$disable_class = ' disabled avada-no-token';
		}

		// We need to display the 'Install' hover link.
		if ( ! isset( $installed_plugins[ $item['file_path'] ] ) ) {
			if ( ! $disable_class ) {
				$url = esc_url(
					wp_nonce_url(
						add_query_arg(
							[
								'page'          => rawurlencode( Avada_TGM_Plugin_Activation::$instance->menu ),
								'plugin'        => rawurlencode( $item['slug'] ),
								'plugin_name'   => rawurlencode( $item['sanitized_plugin'] ),
								'tgmpa-install' => 'install-plugin',
								'return_url'    => 'fusion_plugins',
							],
							Avada_TGM_Plugin_Activation::$instance->get_tgmpa_url()
						),
						'tgmpa-install',
						'tgmpa-nonce'
					)
				);
			} else {
				$url = '#';
			}
			if ( $fusion_builder_action ) {
				$actions = $fusion_builder_action;
			} else {
				$actions = [
					/* translators: Plugin name. */
					'install' => '<a href="' . $url . '" class="button button-primary' . $disable_class . '"' . $data_version . ' title="' . sprintf( esc_attr__( 'Install %s', 'Avada' ), $item['sanitized_plugin'] ) . '">' . esc_attr__( 'Install', 'Avada' ) . '</a>',
				];
			}
		} elseif ( is_plugin_inactive( $item['file_path'] ) ) {
			// We need to display the 'Activate' hover link.
			$url = esc_url(
				add_query_arg(
					[
						'plugin'               => rawurlencode( $item['slug'] ),
						'plugin_name'          => rawurlencode( $item['sanitized_plugin'] ),
						'avada-activate'       => 'activate-plugin',
						'avada-activate-nonce' => wp_create_nonce( 'avada-activate' ),
					],
					admin_url( 'admin.php?page=avada-plugins' )
				)
			);

			$actions = [
				/* translators: Plugin Name. */
				'activate' => '<a href="' . $url . '" class="button button-primary"' . $data_version . ' title="' . sprintf( esc_attr__( 'Activate %s', 'Avada' ), $item['sanitized_plugin'] ) . '">' . esc_attr__( 'Activate', 'Avada' ) . '</a>',
			];
		} elseif ( version_compare( $installed_plugins[ $item['file_path'] ]['Version'], $item['version'], '<' ) ) {

			// We need to display the 'Update' hover link.
			$url = wp_nonce_url(
				add_query_arg(
					[
						'page'         => rawurlencode( Avada_TGM_Plugin_Activation::$instance->menu ),
						'plugin'       => rawurlencode( $item['slug'] ),
						'tgmpa-update' => 'update-plugin',
						'version'      => rawurlencode( $item['version'] ),
						'return_url'   => 'fusion_plugins',
					],
					Avada_TGM_Plugin_Activation::$instance->get_tgmpa_url()
				),
				'tgmpa-update',
				'tgmpa-nonce'
			);

			$actions = [
				/* translators: Plugin Name. */
				'update' => '<a href="' . $url . '" class="button button-primary' . $disable_class . '" title="' . sprintf( esc_attr__( 'Update %s', 'Avada' ), $item['sanitized_plugin'] ) . '">' . esc_attr__( 'Update', 'Avada' ) . '</a>',
			];
		} elseif ( fusion_is_plugin_activated( $item['file_path'] ) ) {
			$url = esc_url(
				add_query_arg(
					[
						'plugin'                 => rawurlencode( $item['slug'] ),
						'plugin_name'            => rawurlencode( $item['sanitized_plugin'] ),
						'avada-deactivate'       => 'deactivate-plugin',
						'avada-deactivate-nonce' => wp_create_nonce( 'avada-deactivate' ),
					],
					admin_url( 'admin.php?page=avada-plugins' )
				)
			);

			$actions = [
				/* translators: Plugin name. */
				'deactivate' => '<a href="' . $url . '" class="button button-primary" title="' . sprintf( esc_attr__( 'Deactivate %s', 'Avada' ), $item['sanitized_plugin'] ) . '">' . esc_attr__( 'Deactivate', 'Avada' ) . '</a>',
			];
		}

		return $actions;
	}

	/**
	 * Needed in order to enable TGMP in AJAX call.
	 *
	 * @access public
	 * @since 5.0
	 * @param bool $load Whether TGMP should be inited or not.
	 * @return bool
	 */
	public function enable_tgmpa( $load ) {
		return true;
	}

	/**
	 * Removes install link for Avada Builder, if Avada Core was not updated to 3.0
	 *
	 * @since 5.0.0
	 * @param array  $action_links The action link(s) for a required plugin.
	 * @param string $item_slug The slug of a required plugin.
	 * @param array  $item Data belonging to a required plugin.
	 * @param string $view_context Specifying the kind of action (install, activate, update).
	 * @return array The action link(s) for a required plugin.
	 */
	public function edit_tgmpa_action_links( $action_links, $item_slug, $item, $view_context ) {
		if ( 'fusion-builder' === $item_slug && 'install' === $view_context ) {
			$fusion_core_version = Avada_TGM_Plugin_Activation::$instance->get_installed_version( Avada_TGM_Plugin_Activation::$instance->plugins['fusion-core']['slug'] );

			if ( version_compare( $fusion_core_version, '3.0', '<' ) ) {
				$action_links['install'] = '<span class="avada-not-installable" style="color:#555555;">' . esc_attr__( 'Avada Builder will be installable, once Avada Core plugin is updated.', 'Avada' ) . '<span class="screen-reader-text">' . esc_attr__( 'Avada Builder', 'Avada' ) . '</span></span>';
			}
		}

		return $action_links;
	}

	/**
	 * Removes install link for Avada Builder, if Avada Core was not updated to 3.0
	 *
	 * @since 5.0.0
	 * @param array $action_links The action link(s) for a required plugin.
	 * @return array The action link(s) for a required plugin.
	 */
	public function edit_tgmpa_notice_action_links( $action_links ) {
		$fusion_core_version = Avada_TGM_Plugin_Activation::$instance->get_installed_version( Avada_TGM_Plugin_Activation::$instance->plugins['fusion-core']['slug'] );
		$current_screen      = get_current_screen();

		if ( 'avada_page_avada-plugins' === $current_screen->id ) {
			$link_template = '<a id="manage-plugins" class="button-primary" style="margin-top:1em;" href="#avada-install-plugins">' . esc_attr__( 'Manage Plugins Below', 'Avada' ) . '</a>';
			$action_links  = [
				'install' => $link_template,
			];
		} elseif ( version_compare( $fusion_core_version, '3.0', '<' ) ) {
			$link_template = '<a id="manage-plugins" class="button-primary" style="margin-top:1em;" href="' . esc_url( self_admin_url( 'admin.php?page=avada-plugins' ) ) . '#avada-install-plugins">' . esc_attr__( 'Go Manage Plugins', 'Avada' ) . '</a>';
			$action_links  = [
				'install' => $link_template,
			];
		}

		return $action_links;
	}

	/**
	 * Initialize the permalink settings.
	 *
	 * @since 3.9.2
	 */
	public function init_permalink_settings() {
		add_settings_field(
			'avada_portfolio_category_slug',                        // ID.
			esc_attr__( 'Avada portfolio category base', 'Avada' ), // Setting title.
			[ $this, 'permalink_slug_input' ],                 // Display callback.
			'permalink',                                            // Settings page.
			'optional',                                             // Settings section.
			[
				'taxonomy' => 'portfolio_category',
			]             // Args.
		);

		add_settings_field(
			'avada_portfolio_skills_slug',
			esc_attr__( 'Avada portfolio skill base', 'Avada' ),
			[ $this, 'permalink_slug_input' ],
			'permalink',
			'optional',
			[
				'taxonomy' => 'portfolio_skills',
			]
		);

		add_settings_field(
			'avada_portfolio_tag_slug',
			esc_attr__( 'Avada portfolio tag base', 'Avada' ),
			[ $this, 'permalink_slug_input' ],
			'permalink',
			'optional',
			[
				'taxonomy' => 'portfolio_tags',
			]
		);
	}

	/**
	 * Show a slug input box.
	 *
	 * @since 3.9.2
	 * @access  public
	 * @param  array $args The argument.
	 */
	public function permalink_slug_input( $args ) {
		$permalinks     = get_option( 'avada_permalinks' );
		$permalink_base = $args['taxonomy'] . '_base';
		$input_name     = 'avada_' . $args['taxonomy'] . '_slug';
		$placeholder    = $args['taxonomy'];
		?>
		<input name="<?php echo esc_attr( $input_name ); ?>" type="text" class="regular-text code" value="<?php echo ( isset( $permalinks[ $permalink_base ] ) ) ? esc_attr( $permalinks[ $permalink_base ] ) : ''; ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" />
		<?php
	}

	/**
	 * Save the permalink settings.
	 *
	 * @since 3.9.2
	 */
	public function save_permalink_settings() {

		if ( ! is_admin() ) {
			return;
		}

		if ( fusion_doing_ajax() ) {
			return;
		}
		if ( isset( $_POST['permalink_structure'] ) || isset( $_POST['category_base'] ) ) { // phpcs:ignore WordPress.Security
			// Cat and tag bases.
			$portfolio_category_slug = ( isset( $_POST['avada_portfolio_category_slug'] ) ) ? sanitize_text_field( wp_unslash( $_POST['avada_portfolio_category_slug'] ) ) : ''; // phpcs:ignore WordPress.Security
			$portfolio_skills_slug   = ( isset( $_POST['avada_portfolio_skills_slug'] ) ) ? sanitize_text_field( wp_unslash( $_POST['avada_portfolio_skills_slug'] ) ) : ''; // phpcs:ignore WordPress.Security
			$portfolio_tags_slug     = ( isset( $_POST['avada_portfolio_tags_slug'] ) ) ? sanitize_text_field( wp_unslash( $_POST['avada_portfolio_tags_slug'] ) ) : ''; // phpcs:ignore WordPress.Security

			$permalinks = get_option( 'avada_permalinks' );

			if ( ! $permalinks ) {
				$permalinks = [];
			}

			$permalinks['portfolio_category_base'] = untrailingslashit( $portfolio_category_slug );
			$permalinks['portfolio_skills_base']   = untrailingslashit( $portfolio_skills_slug );
			$permalinks['portfolio_tags_base']     = untrailingslashit( $portfolio_tags_slug );

			update_option( 'avada_permalinks', $permalinks );
		}
	}

	/**
	 * Check for Envato hosted and register product.
	 *
	 * @since 5.3
	 *
	 * @access public
	 * @return void
	 */
	public function register_product_envato_hosted() {
		if ( defined( 'ENVATO_HOSTED_SITE' ) && ENVATO_HOSTED_SITE && defined( 'SUBSCRIPTION_CODE' ) && ! Avada()->registration->is_registered() ) {

			$license_status = Avada()->remote_install->validate_envato_hosted_subscription_code();

			$registration_args = Avada()->registration->get_args();
			$product_id        = sanitize_key( $registration_args['name'] );

			$registration_array = [
				$product_id => $license_status,
				'scopes'    => [],
			];
			update_option( 'fusion_registered', $registration_array );

			$registration_array = [
				$product_id => [
					'token' => SUBSCRIPTION_CODE,
				],
			];

			update_option( 'fusion_registration', $registration_array );
		}
	}

	/**
	 * Sets the theme version.
	 *
	 * @since 5.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function set_theme_version() {
		$this->theme_version = Avada()->get_normalized_theme_version();
	}

	/**
	 * Sets the WP_Object for the theme.
	 *
	 * @since 5.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function set_theme_object() {
		$theme_object = wp_get_theme();
		if ( $theme_object->parent_theme ) {
			$template_dir = basename( Avada::$template_dir_path );
			$theme_object = wp_get_theme( $template_dir );
		}

		$this->theme_object = $theme_object;
	}

	/**
	 * Override some LayerSlider data.
	 *
	 * @since 5.0.5
	 * @access public
	 * @return void
	 */
	public function layerslider_overrides() {

		// Disable auto-updates.
		$GLOBALS['lsAutoUpdateBox'] = false;
	}

	/**
	 * Add custom rules to Facebook instant articles plugin.
	 *
	 * @since 5.1
	 * @access public
	 * @param object $transformers The transformers object from the Facebook Instant Articles plugin.
	 * @return object
	 */
	public function add_instant_article_rules( $transformers ) {
		$selectors_pass   = [ 'fusion-fullwidth', 'fusion-builder-row', 'fusion-layout-column', 'fusion-column-wrapper', 'fusion-title', 'fusion-imageframe', 'imageframe-align-center', 'fusion-checklist', 'fusion-li-item', 'fusion-li-item-content' ];
		$selectors_ignore = [ 'fusion-column-inner-bg-image', 'fusion-clearfix', 'title-sep-container', 'fusion-sep-clear', 'fusion-separator' ];

		$avada_rules = '{ "rules" : [';
		foreach ( $selectors_pass as $selector ) {
			$avada_rules .= '{ "class": "PassThroughRule", "selector" : "div.' . $selector . '" },';
		}

		foreach ( $selectors_ignore as $selector ) {
			$avada_rules .= '{ "class": "IgnoreRule", "selector" : "div.' . $selector . '" },';
		}

		$avada_rules = trim( $avada_rules, ',' ) . ']}';

		$transformers->loadRules( $avada_rules );

		return $transformers;
	}

	/**
	 * Returns an array of strings that will be used by avada-admin.js for translations.
	 *
	 * @access private
	 * @since 5.2
	 * @return array
	 */
	private function get_admin_script_l10n_strings() {
		return [
			'content'               => esc_attr__( 'Content', 'Avada' ),
			'modify'                => esc_attr__( 'Modify', 'Avada' ),
			'full_import'           => esc_attr__( 'Full Import', 'Avada' ),
			'partial_import'        => esc_attr__( 'Partial Import', 'Avada' ),
			'import'                => esc_attr__( 'Import', 'Avada' ),
			'download'              => esc_attr__( 'Download', 'Avada' ),
			'classic'               => __( 'Importing demo content will give you sliders, pages, posts, global options, widgets, sidebars and other settings. This will replicate the live demo. <strong>Clicking this option will replace your current global options and widgets.</strong> It can also take a minute to complete.<br /><br />REQUIREMENTS:<br /><br />• Memory Limit of 256 MB and max execution time (php time limit) of 300 seconds.<br /><br />• Slider Revolution and LayerSlider must be activated for sliders to import.<br /><br />• Avada Core must be activated for Avada Slider, portfolio and FAQs to be imported.<br /><br />• Avada Builder must be activated for page content to display as intended.', 'Avada' ),
			'caffe'                 => __( 'Importing demo content will give you sliders, pages, posts, global options, widgets, sidebars and other settings. This will replicate the live demo. <strong>Clicking this option will replace your current global options and widgets.</strong> It can also take a minute to complete.<br /><br /> REQUIREMENTS:<br /><br />• Memory Limit of 128 MB and max execution time (php time limit) of 180 seconds.<br /><br />• Avada Core must be activated for sliders, portfolios and FAQs to import.<br /><br />• Contact Form 7 plugin must be activated for the form to import.<br /><br />• Avada Builder must be activated for page content to display as intended.', 'Avada' ),
			'church'                => __( 'Importing demo content will give you sliders, pages, posts, global options, widgets, sidebars and other settings. This will replicate the live demo. <strong>Clicking this option will replace your current global options and widgets.</strong> It can also take a minute to complete.<br /><br /> REQUIREMENTS:<br /><br />• Memory Limit of 128 MB and max execution time (php time limit) of 180 seconds.<br /><br />• Avada Core must be activated for sliders, portfolios and FAQs to import.<br /><br />• The Events Calendar Plugin must be activated for all event data to import.<br /><br />• Contact Form 7 plugin must be activated for the form to import.<br /><br />• Avada Builder must be activated for page content to display as intended.', 'Avada' ),
			'modern_shop'           => __( 'Importing demo content will give you sliders, pages, posts, global options, widgets, sidebars and other settings. This will replicate the live demo. <strong>Clicking this option will replace your current global options and widgets.</strong> It can also take a minute to complete.<br /><br /> REQUIREMENTS:<br /><br />• Memory Limit of 128 MB and max execution time (php time limit) of 180 seconds.<br /><br />• Avada Core must be activated for sliders, portfolios and FAQs to import.<br /><br />• WooCommerce must be activated for all shop data to import.<br /><br />• Contact Form 7 plugin must be activated for the form to import.<br /><br />• Avada Builder must be activated for page content to display as intended.', 'Avada' ),
			'classic_shop'          => __( 'Importing demo content will give you sliders, pages, posts, global options, widgets, sidebars and other settings. This will replicate the live demo. <strong>Clicking this option will replace your current global options and widgets.</strong> It can also take a minute to complete.<br /><br /> REQUIREMENTS:<br /><br />• Memory Limit of 128 MB and max execution time (php time limit) of 180 seconds.<br /><br />• Slider Revolution must be activated for sliders to import.<br /><br />• Avada Core must be activated for Avada Slider, portfolio and FAQs to be imported.<br /><br />• WooCommerce must be activated for all shop data to import.<br /><br />• Contact Form 7 plugin must be activated for the form to import.<br /><br />• Avada Builder must be activated for page content to display as intended.', 'Avada' ),
			'landing_product'       => __( 'Importing demo content will give you sliders, pages, posts, global options, widgets, sidebars and other settings. This will replicate the live demo. <strong>Clicking this option will replace your current global options and widgets.</strong> It can also take a minute to complete.<br /><br /> REQUIREMENTS:<br /><br />• Memory Limit of 128 MB and max execution time (php time limit) of 180 seconds.<br /><br />• Slider Revolution must be activated for sliders to import.<br /><br />• Avada Core must be activated for Avada Slider, portfolio and FAQs to be imported.<br /><br />• WooCommerce must be activated for all shop data to import.<br /><br />• Avada Builder must be activated for page content to display as intended.', 'Avada' ),
			'forum'                 => __( 'Importing demo content will give you sliders, pages, posts, global options, widgets, sidebars and other settings. This will replicate the live demo. <strong>Clicking this option will replace your current global options and widgets.</strong> It can also take a minute to complete.<br /><br /> REQUIREMENTS:<br /><br />• Memory Limit of 128 MB and max execution time (php time limit) of 180 seconds.<br /><br />• Avada Core must be activated for sliders, portfolios and FAQs to import.<br /><br />• bbPress must be activated for all forum data to import.<br /><br />• Contact Form 7 plugin must be activated for the form to import.<br /><br />• Avada Builder must be activated for page content to display as intended.', 'Avada' ),
			'technology'            => __( 'Importing demo content will give you sliders, pages, posts, global options, widgets, sidebars and other settings. This will replicate the live demo. <strong>Clicking this option will replace your current global options and widgets.</strong> It can also take a minute to complete.<br /><br /> REQUIREMENTS:<br /><br />• Memory Limit of 256 MB and max execution time (php time limit) of 300 seconds.<br /><br />• Avada Core and LayerSlider must be activated for sliders to import.<br /><br />• Contact Form 7 plugin must be activated for the form to import.<br /><br />• Avada Builder must be activated for page content to display as intended.', 'Avada' ),
			'creative'              => __( 'Importing demo content will give you sliders, pages, posts, global options, widgets, sidebars and other settings. This will replicate the live demo. <strong>Clicking this option will replace your current global options and widgets.</strong> It can also take a minute to complete.<br /><br /> REQUIREMENTS:<br /><br />• Memory Limit of 128 MB and max execution time (php time limit) of 180 seconds.<br /><br />• Slider Revolution must be activated for sliders to import.<br /><br />• Contact Form 7 plugin must be activated for the form to import.<br /><br />• Avada Core must be activated for Avada Slider, portfolio and FAQs to be imported. <br /><br />• Avada Builder must be activated for page content to display as intended.', 'Avada' ),
			'default'               => __( 'Importing demo content will give you sliders, pages, posts, global options, widgets, sidebars and other settings. This will replicate the live demo. <strong>Clicking this option will replace your current global options and widgets.</strong> It can also take a minute to complete.<br /><br /> REQUIREMENTS:<br /><br />• Memory Limit of 128 MB and max execution time (php time limit) of 180 seconds.<br /><br />• Avada Core must be activated for sliders, portfolios and FAQs to import.<br /><br />• Contact Form 7 plugin must be activated for the form to import.<br /><br />• Avada Builder must be activated for page content to display as intended.', 'Avada' ),
			/* translators: The current step label. */
			'currently_processing'  => esc_attr__( 'Currently Processing: %s', 'Avada' ),
			/* translators: The current step label. */
			'currently_removing'    => esc_attr__( 'Currently Removing: %s', 'Avada' ),
			'file_does_not_exist'   => esc_attr__( 'The file does not exist', 'Avada' ),
			/* translators: URL. */
			'error_timeout'         => wp_kses_post( sprintf( __( 'Demo server couldn\'t be reached. Please check for wp_remote_get on the <a href="%s" target="_blank">Status</a> page.', 'Avada' ), admin_url( 'admin.php?page=avada-status' ) ) ),
			/* translators: URL. */
			'error_php_limits'      => wp_kses_post( sprintf( __( 'Demo import failed. Please check for PHP limits in red on the <a href="%s" target="_blank">Status</a> page. Change those to the recommended value and try again.', 'Avada' ), admin_url( 'admin.php?page=avada-status' ) ) ),
			'remove_demo'           => esc_attr__( 'Removing demo content will remove ALL previously imported demo content from this demo and restore your site to the previous state it was in before this demo content was imported.', 'Avada' ),
			'update_fc'             => __( 'Avada Builder Plugin can only be installed and activated if Avada Core plugin is at version 3.0 or higher. Please update Avada Core first.', 'Avada' ),
			/* translators: URL. */
			'register_first'        => sprintf( __( 'This plugin can only be installed or updated, after you have successfully completed the Avada product registration on the <a href="%s" target="_blank">Dashboard Welcome</a> tab.', 'Avada' ), admin_url( 'admin.php?page=avada#avada-db-registration' ) ),
			'plugin_install_failed' => __( 'Plugin install failed. Please try Again.', 'Avada' ),
			'plugin_active'         => __( 'Active', 'Avada' ),
			'please_wait'           => esc_html__( 'Please wait, this may take a minute...', 'Avada' ),
		];
	}

	/**
	 * Add meta boxes to taxonomies
	 *
	 * @access public
	 * @since 3.1.1
	 * @return void
	 */
	public function avada_taxonomy_meta() {
		global $pagenow;

		if ( ! ( 'term.php' === $pagenow || 'edit-tags.php' === $pagenow || ( fusion_doing_ajax() && ! empty( $_REQUEST['action'] ) && 'add-tag' === $_REQUEST['action'] ) ) ) { // phpcs:ignore WordPress.Security
			return;
		}

		// Include Tax meta class.
		include_once Avada::$template_dir_path . '/includes/class-avada-taxonomy-meta.php';

		// Where to add meta fields.
		$args = [
			'screens' => apply_filters( 'fusion_tax_meta_allowed_screens', [ 'category', 'portfolio_category', 'faq_category', 'product_cat', 'tribe_events_cat', 'post_tag', 'portfolio_tags', 'product_tag', 'topic-tag', 'portfolio_skills' ] ),
		];

		// Init taxonomy meta boxes.
		$avada_meta = new Avada_Taxonomy_Meta( $args );

		$options = $avada_meta::avada_taxonomy_map();
		if ( isset( $options['taxonomy_options']['fields'] ) ) {
			foreach ( $options['taxonomy_options']['fields'] as $field ) {
				// Defaults.
				$field['id']          = isset( $field['id'] ) ? $field['id'] : '';
				$field['label']       = isset( $field['label'] ) ? $field['label'] : '';
				$field['choices']     = isset( $field['choices'] ) ? $field['choices'] : [];
				$field['description'] = isset( $field['description'] ) ? $field['description'] : '';
				$field['default']     = isset( $field['default'] ) ? $field['default'] : '';
				$field['dependency']  = isset( $field['dependency'] ) ? $field['dependency'] : [];
				$field['class']       = isset( $field['class'] ) ? $field['class'] : '';

				switch ( $field['type'] ) {
					case 'header':
						$args = [
							'value' => $field['label'],
							'class' => $field['class'],
						];
						$avada_meta->header( $field['id'], $args );
						break;
					case 'select':
						$args = [
							'name'       => $field['label'],
							'default'    => $field['default'],
							'class'      => $field['class'],
							'desc'       => $field['description'],
							'dependency' => $field['dependency'],
						];
						$avada_meta->select( $field['id'], $field['choices'], $args );
						break;
					case 'radio-buttonset':
						$args = [
							'name'       => $field['label'],
							'default'    => $field['default'],
							'class'      => $field['class'],
							'desc'       => $field['description'],
							'dependency' => $field['dependency'],
						];
						$avada_meta->buttonset( $field['id'], $field['choices'], $args );
						break;
					case 'text':
						$args = [
							'name'       => $field['label'],
							'class'      => $field['class'],
							'desc'       => $field['description'],
							'dependency' => $field['dependency'],
						];
						$avada_meta->text( $field['id'], $args );
						break;
					case 'dimensions':
						$args = [
							'name'       => $field['label'],
							'class'      => $field['class'],
							'desc'       => $field['description'],
							'dependency' => $field['dependency'],
							'default'    => $field['value'],
						];
						$avada_meta->dimensions( $field['id'], $args );
						break;
					case 'color-alpha':
						$args = [
							'name'       => $field['label'],
							'class'      => $field['class'],
							'desc'       => $field['description'],
							'default'    => $field['default'],
							'dependency' => $field['dependency'],
						];
						$avada_meta->colorpicker( $field['id'], $args );
						break;
					case 'media_url':
					case 'media':
						$args = [
							'name'       => $field['label'],
							'class'      => $field['class'],
							'desc'       => $field['description'],
							'dependency' => $field['dependency'],
						];
						$avada_meta->image( $field['id'], $args );
						break;
				}
			}
		}
	}

	/**
	 * Handles an ajax request for the plugins page.
	 *
	 * @access public
	 * @since 6.1
	 * @return void
	 */
	public function ajax_plugins_manager() {

		// These are not the droids you're looking for.
		if ( ! isset( $_POST['action'] ) || 'avada_ajax_plugin_manager' !== $_POST['action'] || ! isset( $_POST['actionToDo'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		/**
		 * There's no reason for security checks here.
		 * This method simply pings a URL and gets the result,
		 * so it's the same as entering the URL in the browser.
		 *
		 * All we need is the sanity check below to make sure the user can activate plugins.
		 * More checks are performed in the native WP functions.
		 */
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		switch ( $_POST['actionToDo'] ) { // phpcs:ignore WordPress.Security.NonceVerification

			/**
			 * Plugin install.
			 *
			 * @uses wp_ajax_install_plugin function.
			 * @uses plugins_api_result filter.
			 */
			case 'install-plugin':
				// Set nonce.
				$_REQUEST['_ajax_nonce'] = wp_create_nonce( 'updates' );

				// Add a filter to hijack the URL.
				add_filter( 'plugins_api_result', [ $this, 'hijack_plugins_api' ], 10, 3 );

				// Perform the installation. This will automatically return the correct JSON response.
				wp_ajax_install_plugin();
				break;

			/**
			 * Plugin Update.
			 *
			 * @uses wp_ajax_update_plugin function.
			 */
			case 'update-plugin':
				// Set nonce.
				$_REQUEST['_ajax_nonce'] = wp_create_nonce( 'updates' );

				// Add a filter to hijack the URL.
				add_filter( 'site_transient_update_plugins', [ $this, 'hijack_plugins_transient_api' ] );

				// Perform the update. This will automatically return the correct JSON response.
				wp_ajax_update_plugin();
				break;

			/**
			 * Refresh the template.
			 */
			case 'refresh-container':
				// Get the contents of the plugins page wrapper.
				ob_start();
				include get_template_directory() . '/includes/admin-screens/plugins.php';
				wp_send_json_success( ob_get_clean() );
				break;
		}
		wp_die();
	}

	/**
	 * Hijack the plugins API to provide our own custom response for AJAX installers.
	 *
	 * @access public
	 * @since 6.1
	 * @param object $value The transient value.
	 * @return object
	 */
	public function hijack_plugins_transient_api( $value ) {

		// Sanity check: make sure response exists.
		if ( ! is_object( $value ) || ! isset( $value->response ) ) {
			return $value;
		}

		// These are not the droids you're looking for.
		if ( isset( $_POST['pluginPath'] ) && isset( $_POST['slug'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$plugin_path = sanitize_text_field( wp_unslash( $_POST['pluginPath'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$plugin_slug = sanitize_text_field( wp_unslash( $_POST['slug'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

			// Get the plugin name.
			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_path );
			$plugin_name = $plugin_data['Name'];

			if ( ! isset( $value->response[ $plugin_path ] ) ) {
				$value->response[ $plugin_path ]         = new stdClass();
				$value->response[ $plugin_path ]->slug   = $plugin_slug;
				$value->response[ $plugin_path ]->plugin = $plugin_path;
			}

			// Set the package URL.
			$value->response[ $plugin_path ]->package = Avada()->remote_install->get_package( $plugin_name );
		}

		// Return the value.
		return $value;
	}

	/**
	 * Hijack the plugins API to provide our own custom response for AJAX installers.
	 *
	 * @access public
	 * @since 6.1
	 * @param object|WP_Error $res    Response object or WP_Error.
	 * @param string          $action The type of information being requested from the Plugin Installation API.
	 * @param object          $args   Plugin API arguments.
	 * @return object
	 */
	public function hijack_plugins_api( $res, $action, $args ) {

		// Sanity check: Only hijack relevant responses IF they err.
		if ( 'plugin_information' !== $action || ! is_wp_error( $res ) ) {
			return $res;
		}

		// Make sure arguments is an array.
		$args = (array) $args;

		// Get the plugin info.
		$custom_plugins = Avada_TGM_Plugin_Activation::$instance->plugins;
		$plugin_located = false;
		foreach ( $custom_plugins as $slug => $plugin ) {
			$plugin = (array) $plugin;
			if ( ! isset( $plugin['slug'] ) || ! isset( $args['slug'] ) ) {
				continue;
			}
			if ( strtolower( $slug ) === strtolower( $args['slug'] ) || strtolower( $plugin['slug'] ) === strtolower( $args['slug'] ) ) {
				$plugin_located = $plugin;
				break;
			}
		}

		// If we successfully got the plugin info, change the response object.
		if ( $plugin_located ) {
			$res                = new stdClass();
			$res->name          = $plugin_located['name'];
			$res->slug          = $plugin_located['slug'];
			$res->version       = $plugin_located['version'];
			$res->download_link = Avada()->remote_install->get_package( $plugin_located['name'] );
		}

		// Return response.
		return $res;
	}

	/**
	 * Gets the dashboard-screen video URL.
	 *
	 * @static
	 * @access public
	 * @since 6.2.0
	 * @return string Returns a URL.
	 */
	public static function get_dashboard_screen_video_url() {

		// Fallback values.
		$video_url = 'https://www.youtube.com/watch?v=Y5k5UMgOpXs?rel=0';
		$data      = Avada::get_data();

		if ( isset( $data['video_url'] ) ) {
			$video_url = $data['video_url'];
		}

		if ( false !== strpos( $video_url, 'https://www.youtube.com/watch?v=' ) ) {
			$video_url = str_replace( [ 'https://www.youtube.com/watch?v=', '?rel=0' ], [ 'https://www.youtube.com/embed/', '' ], $video_url ) . '?rel=0';
		}

		return $video_url;
	}

	/**
	 * Get plugin info from plugins with plugin name.
	 *
	 * @since 7.0
	 * @param string $plugin_name Plugin name to search for.
	 * @param array  $plugins     Plugins array containing all plugins data.
	 * @return array
	 */
	public function fusion_get_plugin_info( $plugin_name, $plugins ) {
		$plugin_info_return = null;
		foreach ( $plugins as $plugin_file => $plugin_info ) {
			if ( $plugin_info['Name'] === $plugin_name ) {
				$plugin_info['plugin_file'] = $plugin_file;
				$plugin_info['is_active']   = fusion_is_plugin_activated( $plugin_file );

				$plugin_info_return = $plugin_info;
			}
		}
		return apply_filters( 'fusion_get_plugin_info', $plugin_info_return, $plugin_name, $plugins );
	}

	/**
	 * Deprecated method, exists to prevent error.
	 *
	 * @since 7.3
	 * @param bool $update Deprecated param.
	 * @return void
	 */
	public static function set_dashboard_data( $update = false ) {
	}

	/**
	 * Deprecated method, exists to prevent error.
	 *
	 * @since 7.3
	 * @return array
	 */
	public static function get_dashboard_data() {
		return [];
	}

	/**
	 * Output warning for legacy mode.
	 *
	 * @since 7.3
	 * @return void
	 */
	public function legacy_countdown() {

		$data = Avada::get_data();

		if ( ! Avada()->registration->is_registered() && '' !== Avada()->registration->get_token() && isset( $data['legacy_end'] ) && '' !== $data['legacy_end'] && ! Avada()->registration->is_legacy_update() ) {

			$legacy_active = Avada()->registration->legacy_support();
			$button_url    = isset( $_GET['page'] ) && 'avada' === $_GET['page'] ? '#avada-db-registration' : admin_url( 'admin.php?page=avada#_avada-db-registration' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$message = '<h2>' . esc_html__( 'Avada Product Registration Needs To Be Updated', 'Avada' ) . '</h2>';

			if ( $legacy_active ) {
				$current    = new DateTime( gmdate( 'D, d M Y H:i' ) );
				$legacy_end = new DateTime( gmdate( 'D, d M Y H:i', $data['legacy_end'] ) );
				$diff       = $legacy_end->diff( $current );
				$days       = 1 !== $diff->d ? esc_html__( 'days', 'Avada' ) : esc_html__( 'day', 'Avada' );
				$hours      = 1 !== $diff->h ? esc_html__( 'hours', 'Avada' ) : esc_html__( 'hour', 'Avada' );

				if ( 0 === $diff->d ) {
					$remaining = $diff->format( '%h ' . $hours );
				} else {
					$remaining = $diff->format( '%a ' . $days . ' and %h ' . $hours );
				}
				$message .= '<p>' . esc_html__( 'To receive further product updates, the registration has to be updated using your Avada purchase code. Updates, using your Envato token will continue to work for a limited period of time. The time at which this support will end can be found below.', 'Avada' ) . '</p>';
				$message .= '<div class="notice-flex-wrapper">';
				/* translators: The remaining  time. */
				$message .= '<p>' . sprintf( __( 'Updates with the token will stop working in <strong>%s</strong>.', 'Avada' ), $remaining ) . '</p>';
			} else {
				$message .= '<p>' . esc_html__( 'To receive further product updates, the registration has to be updated using your Avada purchase code. The grace period for updates using the Envato token has ended. Please complete registration in order to receive automatic updates, plugin installs and prebuilt website imports.', 'Avada' ) . '</p>';
				$message .= '<div class="notice-flex-wrapper">';
				/* translators: The expiry date. */
				$message .= '<p>' . sprintf( __( 'Token updates expired on <strong>%s</strong>.', 'Avada' ), gmdate( 'D, d M Y H:i', $data['legacy_end'] ) ) . '</p>';
			}
			$message .= '<a class="button" href="' . $button_url . '">' . esc_html__( 'Update Registration', 'Avada' ) . '</a>';
			$message .= '</div>';
			new Fusion_Admin_Notice(
				'fusion-legacy-notice',
				$message,
				is_super_admin(),
				'error',
				true,
				'user_meta',
				'avada-reg',
				[
					'avada_page_avada-prebuilt-websites',
					'avada_page_avada-plugins',
					'avada_page_avada-patcher',
					'toplevel_page_avada',
				]
			);
		}
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
