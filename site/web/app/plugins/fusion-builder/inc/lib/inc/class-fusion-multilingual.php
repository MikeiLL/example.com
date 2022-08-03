<?php
/**
 * Multilingual handling.
 *
 * @package Fusion-Library
 * @since 1.0.0
 */

/**
 * A helper class that depending on the active multilingual plugin
 * will get the available languages as well as the active language.
 * Currently handles compatibility with WPML & PolyLang.
 *
 * @since 1.0.0
 */
class Fusion_Multilingual {

	/**
	 * Are we using WPML?
	 *
	 * @static
	 * @access  private
	 * @var  bool
	 */
	private static $is_wpml = false;

	/**
	 * Are we using PolyLang?
	 *
	 * @static
	 * @access  private
	 * @var  bool
	 */
	private static $is_pll = false;

	/**
	 * An array of all available languages.
	 *
	 * @static
	 * @access  private
	 * @var  array
	 */
	private static $available_languages = [];

	/**
	 * The active language.
	 *
	 * @static
	 * @access  private
	 * @var  string
	 */
	private static $active_language = '';

	/**
	 * The "main" language.
	 *
	 * @static
	 * @access  private
	 * @var  string
	 */
	private static $main_language = 'en';

	/**
	 * Count amount of WPML footer language switcher.
	 *
	 * @access  private
	 * @var  int
	 */
	private $count_footer_ls = 1;

	/**
	 * The main class constructor.
	 * Sets the static properties of this object.
	 *
	 * @access  public
	 */
	public function __construct() {

		// Set the $is_pll property.
		self::$is_pll = self::is_pll();
		// Set the $is_wpml property.
		self::$is_wpml = self::is_wpml();

		// Set the $available_languages property.
		self::set_available_languages();
		// Set the $main_language properly.
		self::set_main_language();
		// Set the $active_language property.
		self::set_active_language();

		// Make form elements correctly translateable for WPML.
		add_filter( 'wpml_pb_shortcode_decode', [ $this, 'wpml_pb_shortcode_decode_forms' ], 10, 3 );
		add_filter( 'wpml_pb_shortcode_encode', [ $this, 'wpml_pb_shortcode_encode_forms' ], 10, 3 );

		add_filter( 'wpml_ls_html', [ $this, 'disable_wpml_footer_ls_html' ], 10, 3 );

		add_filter( 'avada_element_term_selection', [ $this, 'map_terms' ], 10, 3 );

		add_filter( 'fusion_layout_section_id', [ $this, 'pl_layout_section' ], 10, 3 );
	}

	/**
	 * Decodes urlencoded shortcodes.
	 *
	 * @since 3.4
	 * @access public
	 * @param string $string         The encoded string.
	 * @param string $encoding       Encoding type.
	 * @param string $encoded_string The encoded string.
	 * @return array|string          The decoded form input.
	 */
	public function wpml_pb_shortcode_decode_forms( $string, $encoding, $encoded_string ) {
		$decoded = json_decode( $string );

		if ( JSON_ERROR_NONE === json_last_error() && 'base64' === $encoding ) {
			$parsed_strings = [];
			$array          = $decoded;

			foreach ( $array as $item ) {
				$parsed_strings[] = [
					'value'     => $item,
					'translate' => ! ( empty( $item ) || is_numeric( $item ) ),
				];
			}

			return $parsed_strings;
		}

		return $string;
	}

	/**
	 * Encodes shortcodes.
	 *
	 * @since 3.4
	 * @access public
	 * @param string|array $string         The encoded string.
	 * @param string       $encoding       Encoding type.
	 * @param string|array $decoded_string The decoded string.
	 * @return string The encoded form input.
	 */
	public function wpml_pb_shortcode_encode_forms( $string, $encoding, $decoded_string ) {
		if ( is_array( $decoded_string ) && 'base64' === $encoding ) {
			return fusion_encode_input( json_encode( $decoded_string ), 'base64' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
		}

		return $string;
	}

	/**
	 * Filters the WPML language switcher content.
	 *
	 * @since 1.0
	 * @access public
	 * @param string       $html   The HTML for the language switcher.
	 * @param array        $model  The model passed to the template.
	 * @param WPML_LS_slot $slot   The language switcher settings for this slot.
	 * @return string The HTML of the language switcher or empty string.
	 */
	public function disable_wpml_footer_ls_html( $html, $model, $slot ) {
		if ( 'footer' === $slot->get( 'slot_slug' ) && 1 < $this->count_footer_ls ) {
			return '';
		} elseif ( 'footer' === $slot->get( 'slot_slug' ) && 1 === $this->count_footer_ls ) {
			$this->count_footer_ls++;
			return $html;
		} else {
			return $html;
		}
	}

	/**
	 * Sets the available languages depending on the active plugin.
	 */
	private static function set_available_languages() {
		if ( self::$is_pll ) {
			self::$available_languages = self::get_available_languages_pll();
		} elseif ( self::$is_wpml ) {
			self::$available_languages = self::get_available_languages_wpml();
		}
	}

	/**
	 * Gets the $active_language protected property.
	 */
	public static function get_active_language() {
		if ( ! self::$active_language ) {
			self::set_active_language();
		}
		return self::$active_language;
	}

	/**
	 * Sets the active language.
	 *
	 * @param string|bool $lang The language code to set.
	 */
	public static function set_active_language( $lang = false ) {
		if ( is_string( $lang ) && ! empty( $lang ) ) {
			self::$active_language = $lang;
			return;
		}

		/**
		 * If we have not defined a language, then autodetect.
		 * No need to proceed if both WPML & PLL are inactive.
		 */
		if ( ! self::$is_pll && ! self::$is_wpml ) {
			self::$active_language = 'en';
			return;
		}

		// Preliminary work for PLL - adds the WPML compatibility layer.
		if ( function_exists( 'pll_define_wpml_constants' ) ) {
			pll_define_wpml_constants();
		}

		// WPML (Or the PLL with WPML compatibility layer) is active.
		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			self::$active_language = ICL_LANGUAGE_CODE;
			if ( 'all' === ICL_LANGUAGE_CODE ) {
				do_action( 'fusion_library_set_language_is_all' );
				if ( self::$is_wpml ) {
					global $sitepress;
					self::$active_language = $sitepress->get_default_language();
				} elseif ( self::$is_pll ) {
					self::$active_language = pll_default_language( 'slug' );
				}
			}
			return;
		}

		// PLL without WPML compatibility layer.
		if ( function_exists( 'PLL' ) ) {
			$pll_obj = PLL();
			if ( is_object( $pll_obj ) && property_exists( $pll_obj, 'curlang' ) ) {
				if ( is_object( $pll_obj->curlang ) && property_exists( $pll_obj->curlang, 'slug' ) ) {
					self::$active_language = $pll_obj->curlang->slug;
				} elseif ( false === $pll_obj->curlang ) {
					self::$active_language = 'all';
					do_action( 'fusion_library_set_language_is_all' );
				}
			}
		}
	}

	/**
	 * Gets the $available_languages protected property.
	 */
	public static function get_available_languages() {
		if ( empty( self::$available_languages ) ) {
			self::set_available_languages();
		}
		return self::$available_languages;
	}

	/**
	 * Gets the data for front-end.
	 *
	 * @since 6.0
	 * @return array
	 */
	public static function get_language_switcher_data() {
		if ( self::$is_pll ) {
			return self::get_language_switcher_pll();
		} elseif ( self::$is_wpml ) {
			return self::get_language_switcher_wpml();
		}
	}

	/**
	 * Get the available languages from WPML.
	 *
	 * @return array
	 */
	private static function get_available_languages_wpml() {
		// Do not continue processing if we're not using WPML.
		if ( ! self::$is_wpml ) {
			return [];
		}
		$wpml_languages = icl_get_languages( 'skip_missing=0' );
		$languages      = [];
		foreach ( $wpml_languages as $language_key => $args ) {
			$languages[] = $args['code'];
		}
		return $languages;

	}

	/**
	 * Gets the default language.
	 *
	 * @return string
	 */
	public static function get_default_language() {
		self::set_main_language();
		return self::$main_language;
	}

	/**
	 * Sets the $main_language based on the active plugin.
	 *
	 * @return void
	 */
	private static function set_main_language() {
		if ( self::$is_pll ) {
			self::$main_language = self::get_main_language_pll();
		} elseif ( self::$is_wpml ) {
			self::$main_language = self::get_main_language_wpml();
		}
	}

	/**
	 * Get the default language for WPML.
	 *
	 * @return string
	 */
	private static function get_main_language_wpml() {
		global $sitepress;
		return $sitepress->get_default_language();
	}

	/**
	 * Get the default language for PolyLang.
	 *
	 * @return string
	 */
	private static function get_main_language_pll() {
		return pll_default_language( 'slug' );
	}

	/**
	 * Get the available languages from PolyLang.
	 *
	 * @return array
	 */
	private static function get_available_languages_pll() {
		// Do not continue processing if we're not using PLL.
		if ( ! self::$is_pll ) {
			return [];
		}

		global $polylang;
		// Get the PLL languages object.
		$pll_languages_obj = $polylang->model->get_languages_list();
		// Parse the object and get a usable array.
		$pll_languages = [];
		foreach ( $pll_languages_obj as $pll_language_obj ) {
			$pll_languages[] = $pll_language_obj->slug;
		}

		return $pll_languages;
	}

	/**
	 * Get the PolyLang data for front-end.
	 *
	 * @since 6.0
	 * @return array
	 */
	private static function get_language_switcher_pll() {
		// Do not continue processing if we're not using PLL.
		if ( ! self::$is_pll || ! function_exists( 'pll_the_languages' ) ) {
			return [];
		}

		return pll_the_languages( [ 'raw' => 1 ] );
	}

	/**
	 * Get the WPML data for front-end.
	 *
	 * @since 6.0
	 * @return array
	 */
	private static function get_language_switcher_wpml() {
		// Do not continue processing if we're not using WPML.
		if ( ! self::$is_wpml ) {
			return [];
		}

		return apply_filters( 'wpml_active_languages', null, 'skip_missing=0&orderby=id&order=desc' );
	}

	/**
	 * Determine if we're using PolyLang.
	 *
	 * @return bool
	 */
	public static function is_pll() {
		if ( function_exists( 'pll_default_language' ) ) {
			return true;
		}

		if ( fusion_is_plugin_activated( 'polylang\polylang.php' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if we're using WPML.
	 * Since PLL has a compatibility layer for WPML, we'll have to consider that too.
	 */
	public static function is_wpml() {
		return ( ( defined( 'WPML_PLUGIN_FILE' ) || defined( 'ICL_PLUGIN_FILE' ) ) && false === self::$is_pll ) ? true : false;
	}

	/**
	 * Filters terms data language specific.
	 *
	 * @access public
	 * @since 3.0.2
	 * @param array  $term_slugs The term slugs to be filtered.
	 * @param string $cpt The post type the terms belong to.
	 * @param string $taxonomy The taxonomy the terms belong to.
	 * @return array The filtered terms.
	 */
	public function map_terms( $term_slugs, $cpt, $taxonomy ) {
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			foreach ( $term_slugs as $term_slug ) {
				$term = get_term_by( 'slug', $term_slug, $taxonomy );
				if ( $term ) {
					$translated_id   = apply_filters( 'wpml_object_id', $term->term_id, $cpt, true );
					$translated_term = get_term_by( 'id', $translated_id, $taxonomy );

					if ( $translated_term ) {
						$term_slugs[] = $translated_term->slug;
					}
				}
			}

			$term_slugs = array_unique( $term_slugs );
		}

		return $term_slugs;

	}

	/**
	 * Filter layout section post ID.
	 *
	 * @access public
	 * @since 3.1
	 * @param int    $layout_section_id Post ID of layout section.
	 * @param string $type Type of layout section.
	 * @param mixed  $layout Layout iD.
	 * @return int The filtered layout section ID..
	 */
	public function pl_layout_section( $layout_section_id = 0, $type = 'header', $layout = 0 ) {
		if ( self::is_pll() && function_exists( 'pll_get_post' ) ) {
			return pll_get_post( $layout_section_id );
		}
		return $layout_section_id;
	}
}
