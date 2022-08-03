<?php
/**
 * Output callbacks for options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since 6.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * A wrapper for static methods.
 */
class Avada_Output_Callbacks {

	/**
	 * Callback for the menu_sub_sep_color option.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @param string $value The value.
	 * @return string
	 */
	public static function menu_sub_sep_color( $value ) {
		return fusion_is_color_transparent( $value ) ? '0' : '';
	}

	/**
	 * Callback for the page_title_border_color option.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @param string $value The value.
	 * @return string
	 */
	public static function page_title_border_color( $value ) {
		$po_ptb_border_color = fusion_get_page_option( 'page_title_border_color', Avada()->fusion_library->get_page_id() );
		return ( ( fusion_is_color_transparent( Avada()->settings->get( 'page_title_border_color' ) ) && empty( $po_ptb_border_color ) ) || fusion_is_color_transparent( $po_ptb_border_color ) ) ? 'none' : '';
	}

	/**
	 * Returns unfiltered option.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @param string $value The value.
	 * @return string
	 */
	public static function unfiltered( $value ) {
		return $value;
	}
}
