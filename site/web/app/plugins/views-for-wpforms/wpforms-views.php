<?php
/*
 * Plugin Name:Views for WPForms
 * Plugin URI: https://formviewswp.com/
 * Description: Display WPForms Entries in frontend.
 * Version: 1.9.2
 * Author: WebHolics
 * Author URI: https://formviewswp.com/
 * Text Domain: wpforms-views
 *
 * Copyright 2021
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( "WPFORMS_VIEWS_URL", plugins_url() . "/" . basename( dirname( __FILE__ ) ) );
define( "WPFORMS_VIEWS_DIR_URL", WP_PLUGIN_DIR . "/" . basename( dirname( __FILE__ ) ) );

define( 'WPFORMS_VIEWS_STORE_URL', 'https://formviewswp.com' );
define( 'WPFORMS_VIEWS_ITEM_ID', '1863' );
define( 'WPFORMS_VIEWS_VERSION',  '1.9.2' );
define( 'WPFORMS_VIEWS_PLUGIN_FILE', __FILE__ );

function wpforms_views_pro_activate() {
	if ( is_plugin_active( 'views-for-wpforms-lite/wpforms-views.php' ) ) {
		deactivate_plugins( 'views-for-wpforms-lite/wpforms-views.php' );
	}
}
register_activation_hook( __FILE__, 'wpforms_views_pro_activate' );




add_action( 'plugins_loaded', 'wpforms_views_pro_include_files' );

function wpforms_views_pro_include_files() {

	if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
		// load our custom updater
		include WPFORMS_VIEWS_DIR_URL . '/inc/admin/updater/EDD_SL_Plugin_Updater.php';
	}

	require_once WPFORMS_VIEWS_DIR_URL . '/inc/helpers.php';
	require_once WPFORMS_VIEWS_DIR_URL . '/inc/admin/class-wpforms-views-roles-capabilities.php';
	//Backend
	require_once WPFORMS_VIEWS_DIR_URL . '/inc/admin/updater/license.php';
	require_once WPFORMS_VIEWS_DIR_URL . '/inc/admin/class-wpforms-views-posttype.php';
	require_once WPFORMS_VIEWS_DIR_URL . '/inc/admin/class-wpforms-views-metabox.php';
	require_once WPFORMS_VIEWS_DIR_URL . '/inc/admin/class-wpforms-views-ajax.php';
	require_once WPFORMS_VIEWS_DIR_URL . '/inc/admin/class-wpforms-views-settings.php';
	require_once WPFORMS_VIEWS_DIR_URL . '/inc/admin/class-wpforms-views-settings-api.php';
	require_once WPFORMS_VIEWS_DIR_URL . '/inc/admin/class-wpforms-views-scripts.php';

	//Frontend
	require_once WPFORMS_VIEWS_DIR_URL . '/inc/class-wpforms-views-common.php';
	require_once WPFORMS_VIEWS_DIR_URL . '/inc/class-wpforms-views-mergetags.php';
	require_once WPFORMS_VIEWS_DIR_URL . '/inc/pagination.php';
	require_once WPFORMS_VIEWS_DIR_URL . '/inc/class-wpforms-views-shortcode.php';
	require_once WPFORMS_VIEWS_DIR_URL . '/inc/fields/class-wpf-views-field.php';
	// Load all form field files automatically
	foreach ( glob( WPFORMS_VIEWS_DIR_URL . '/inc/fields/form-fields/class-wpf-views-field*.php' ) as $field_filename ) {
		include_once $field_filename;
	}
	// Load all field files automatically
	foreach ( glob( WPFORMS_VIEWS_DIR_URL . '/inc/fields/class-wpf-views-field*.php' ) as $field_filename ) {
		include_once $field_filename;
	}

	require_once WPFORMS_VIEWS_DIR_URL . '/inc/widgets/class-wpf-views-widget.php';
	// Load all Widget files automatically
	foreach ( glob( WPFORMS_VIEWS_DIR_URL . '/inc/widgets/class-wpf-views-widget*.php' ) as $field_filename ) {
		//echo $field_filename;
		include_once $field_filename;
	}
}

add_action( 'admin_enqueue_scripts', 'wpforms_views_pro_admin_scripts' );

add_action( 'wp_enqueue_scripts', 'wpforms_views_pro_frontend_scripts' );

function wpforms_views_pro_admin_scripts( $hook ) {
	global $post;
	if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
		if ( 'wpforms-views' === $post->post_type ) {

			wp_enqueue_style( 'fontawesome', WPFORMS_VIEWS_URL . '/assets/css/font-awesome.css' );
			wp_enqueue_style( 'pure-css', WPFORMS_VIEWS_URL . '/assets/css/pure-min.css' );
			wp_enqueue_style( 'pure-grid-css', WPFORMS_VIEWS_URL . '/assets/css/grids-responsive-min.css' );
			wp_enqueue_style( 'wpforms-views-admin', WPFORMS_VIEWS_URL . '/assets/css/wpforms-views-admin.css' );


			$js_dir    = WPFORMS_VIEWS_DIR_URL . '/build/static/js';
			$js_files = array_diff( scandir( $js_dir ), array( '..', '.' ) );
			$count = 0;
			foreach ( $js_files as $js_file ) {
				if ( strpos( $js_file , '.js.map'  )  === false  ) {
					$js_file_name = $js_file;
					wp_enqueue_script( 'wpforms_views_script' . $count, WPFORMS_VIEWS_URL . '/build/static/js/' . $js_file_name, array( 'jquery' ), '', true );
					$count++;
					// wp_localize_script( 'react_grid_script'.$count, 'formData' , $form_data );
				}
			}

			$css_dir    = WPFORMS_VIEWS_DIR_URL . '/build/static/css';
			$css_files = array_diff( scandir( $css_dir ), array( '..', '.' ) );

			foreach ( $css_files as $css_file ) {
				if ( strpos( $css_file , '.css.map'  ) === false ) {
					$css_file_name = $css_file;
				}
			}
			// $grid_options = get_option( 'gf_stla_form_id_grid_layout_4');
			wp_enqueue_style( 'wpforms_views_style', WPFORMS_VIEWS_URL . '/build/static/css/' . $css_file_name );
		}
	}
}


function wpforms_views_pro_frontend_scripts() {
	wp_enqueue_style( 'pure-css', WPFORMS_VIEWS_URL . '/assets/css/pure-min.css' );
	wp_enqueue_style( 'pure-grid-css', WPFORMS_VIEWS_URL . '/assets/css/grids-responsive-min.css' );
	wp_enqueue_style( 'wpforms-views-front', WPFORMS_VIEWS_URL . '/assets/css/wpforms-views-display.css' );
}
