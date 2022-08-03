<?php if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Plugin Name: Views for WPForms - DataTables
 * Plugin URI: https://formviewswp.com
 * Description: Display WPForms submissions using DataTables.
 * Version: 1.4
 * Author: Webholics
 * Author URI: https://webholics.org
 *
 * Copyright 2020 Aman Saini.
 */
define( "WPFORMS_VIEWS_DATATABLES_DIR_URL", WP_PLUGIN_DIR . "/" . basename( dirname( __FILE__ ) ) );
define( "WPFORMS_VIEWS_DATATABLES_URL", plugins_url() . "/" . basename( dirname( __FILE__ ) ) );

define( 'WPFORMS_VIEWS_DATATABLES_VERSION',  '1.4' );
define( 'WPFORMS_VIEWS_DATATABLES_PLUGIN_FILE', __FILE__ );
function wpf_views_datattables_include_files() {
	require_once WPFORMS_VIEWS_DATATABLES_DIR_URL . '/inc/admin/updater/license.php';
	require_once WPFORMS_VIEWS_DATATABLES_DIR_URL . '/inc/admin/class-wpforms-views-datatables-settings.php';
	require_once WPFORMS_VIEWS_DATATABLES_DIR_URL . '/inc/admin/class-wpforms-views-datatables-enable.php';
	require_once WPFORMS_VIEWS_DATATABLES_DIR_URL . '/inc/class-wpforms-views-datatables-display.php';

}

add_action( 'plugins_loaded', 'wpf_views_datattables_include_files', 15 );
