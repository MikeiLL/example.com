<?php

namespace WPFormsFormPages\Helpers;

/**
 * Form Pages colors helper.
 *
 * @since 1.0.0
 */
class Colors {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
	}

	/**
	 * Validate 3 or 6 digit hex.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hex Hex color.
	 *
	 * @return bool
	 */
	public function is_valid_hex( $hex ) {

		return \wpforms_sanitize_hex_color( $hex ) === $hex;
	}

	/**
	 * Convert 3 digit hex to 6 digit hex.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hex Hex color.
	 *
	 * @return string
	 */
	public function normalize_short_hex( $hex ) {

		if ( ! $this->is_valid_hex( $hex ) ) {
			return $hex;
		}

		$hex = str_replace( '#', '', $hex );
		if ( strlen( $hex ) === 3 ) {
			$hex = preg_replace( '/(\w)/', '$1$1', (string) $hex );
		}

		return $hex;
	}

	/**
	 * Make hex darker/lighter emulating opacity with black/white color as a background.
	 *
	 * Examples:
	 * $alpha = 0    Unchanged color
	 * $alpha = 1    Unchanged color
	 * $alpha = -1   Unchanged color
	 * $alpha = 0.1  Very light, almost white
	 * $alpha = -0.1 Very dark, almost black
	 * $alpha = 0.9  Slightly lighter, almost unchanged
	 * $alpha = -0.9 Slightly darker, almost unchanged
	 *
	 * @since 1.0.0
	 *
	 * @param string $hex   Hex color.
	 * @param float  $alpha Emulates alpha channel in RGBa. Min -1, max 1.
	 *
	 * @return string
	 */
	public function hex_opacity( $hex, $alpha ) {

		if ( ! $this->is_valid_hex( $hex ) ) {
			return $hex;
		}

		$alpha = (float) $alpha;

		if ( empty( $alpha ) ) {
			return $hex;
		}

		// Limit $alpha min -1 and max 1.
		$alpha = max( - 1, min( 1, $alpha ) );

		$blend_color = $alpha > 0 ? 255 : 0;

		// Normalize into a six character hex string.
		$hex = $this->normalize_short_hex( $hex );

		// Split into three parts: R, G and B.
		$color_parts = str_split( $hex, 2 );
		$return      = '#';

		foreach ( $color_parts as $color ) {

			// Convert to decimal.
			$color = hexdec( $color );

			// Adjust color.
			$color = \abs( $alpha ) * $color + ( 1 - \abs( $alpha ) ) * $blend_color;

			// Pad left with zeroes if hex $color is less than two characters long.
			$return .= str_pad( dechex( $color ), 2, '0', STR_PAD_LEFT );
		}

		return $return;
	}
}
