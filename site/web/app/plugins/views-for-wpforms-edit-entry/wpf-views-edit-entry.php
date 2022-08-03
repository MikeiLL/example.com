<?php if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Plugin Name: Views for WPForms - Edit Entries
 * Plugin URI: https://formviewswp.com
 * Description: Allow users to Edit their entries from site frontend.
 * Version: 1.9
 * Author: WebHolics
 * Author URI: https://webholics.org
 *
 * Copyright 2020 Aman Saini.
 */
define( "WPF_VIEWS_EDIT_ENTRY_DIR_URL", WP_PLUGIN_DIR . "/" . basename( dirname( __FILE__ ) ) );
define( "WPF_VIEWS_EDIT_ENTRY_URL", plugins_url() . "/" . basename( dirname( __FILE__ ) ) );

define( 'WPF_VIEWS_EDIT_ENTRY_VERSION',  '1.9' );
define( 'WPF_VIEWS_EDIT_PLUGIN_FILE', __FILE__ );

function wpf_views_edit_entry_include_files() {
	//require_once WPF_VIEWS_EDIT_ENTRY_DIR_URL . '/inc/admin/class-wpf-views-edit-entry-field.php';
	require_once WPF_VIEWS_EDIT_ENTRY_DIR_URL . '/inc/admin/class-wpf-views-edit-entry-enable.php';
	require_once WPF_VIEWS_EDIT_ENTRY_DIR_URL . '/inc/admin/updater/license.php';
		require_once WPF_VIEWS_EDIT_ENTRY_DIR_URL . '/inc/admin/class-wpf-views-edit-entry-settings.php';
	require_once WPF_VIEWS_EDIT_ENTRY_DIR_URL . '/inc/class-wpf-views-edit-entry-db.php';
	require_once WPF_VIEWS_EDIT_ENTRY_DIR_URL . '/inc/class-wpf-views-edit-entry-link.php';
	require_once WPF_VIEWS_EDIT_ENTRY_DIR_URL . '/inc/class-wpf-views-edit-entry-processing.php';

}

add_action( 'plugins_loaded', 'wpf_views_edit_entry_include_files', 15 );