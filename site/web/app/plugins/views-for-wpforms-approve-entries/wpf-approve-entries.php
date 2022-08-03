<?php if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Plugin Name: Views for WPForms - Approve Entries
 * Plugin URI: https://nfviews.com
 * Description: Approve WPForms Entries before you can show them in your View.
 * Version: 1.1
 * Author: Webholics
 * Author URI: https://webholics.org
 *
 * Copyright 2020 Aman Saini.
 */
define( "WPF_VIEWS_APPROVE_SUBMISSIONS_DIR_URL", WP_PLUGIN_DIR . "/" . basename( dirname( __FILE__ ) ) );

define( 'WPF_VIEWS_APPROVE_SUBMISSIONS_VERSION',  '1.1' );
define( 'WPF_VIEWS_APPROVE_SUBMISSIONS_PLUGIN_FILE', __FILE__ );
function wpf_views_approve_entries_include_files() {
	require_once WPF_VIEWS_APPROVE_SUBMISSIONS_DIR_URL . '/inc/admin/updater/license.php';
	require_once WPF_VIEWS_APPROVE_SUBMISSIONS_DIR_URL . '/inc/admin/class-wpf-approve-entries-metabox.php';
	require_once WPF_VIEWS_APPROVE_SUBMISSIONS_DIR_URL . '/inc/admin/class-wpf-approve-entries-settings.php';
	require_once WPF_VIEWS_APPROVE_SUBMISSIONS_DIR_URL . '/inc/wpf-approve-entries-view.php';

}

add_action( 'plugins_loaded', 'wpf_views_approve_entries_include_files', 15 );
