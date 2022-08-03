<?php if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Plugin Name: Views for WPForms - Single Entry
 * Plugin URI: https://formviewswp.com
 * Description: Display Entry details on site frontend.
 * Version: 1.6
 * Author: Webholics
 * Author URI: https://webholics.org
 *
 * Copyright 2020 Aman Saini.
 */
define( "WPF_VIEWS_SINGLE_ENTRY_DIR_URL", WP_PLUGIN_DIR . "/" . basename( dirname( __FILE__ ) ) );

define( 'WPF_VIEWS_SINGLE_ENTRY_VERSION',  '1.6' );
define( 'WPF_VIEWS_SINGLE_ENTRY_PLUGIN_FILE', __FILE__ );

add_action( 'plugins_loaded', 'wpf_views_singl_entry_init', 12 );
function wpf_views_singl_entry_init() {
	require_once WPF_VIEWS_SINGLE_ENTRY_DIR_URL . '/inc/admin/class-wpf-views-single-entry-enable.php';
	require_once WPF_VIEWS_SINGLE_ENTRY_DIR_URL . '/inc/admin/updater/license.php';
	require_once WPF_VIEWS_SINGLE_ENTRY_DIR_URL . '/inc/admin/class-wpf-views-single-entry-settings.php';
	require_once WPF_VIEWS_SINGLE_ENTRY_DIR_URL . '/inc/class-wpf-views-single-entry-view.php';
	require_once WPF_VIEWS_SINGLE_ENTRY_DIR_URL . '/inc/class-wpf-views-field-single-entry-link.php';

}
