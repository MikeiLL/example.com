<?php
/**
 * Avada Builder Conditional Render Helper class.
 *
 * @package Avada-Builder
 * @since 3.3
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}


/**
 * Avada Builder Conditional Render Helper class.
 *
 * @since 3.3
 */
class Fusion_Builder_Conditional_Render_Helper {

	/**
	 * Class constructor.
	 *
	 * @since 3.3
	 * @access public
	 */
	public function __construct() {

	}

	/**
	 * Get conditional logic params.
	 *
	 * @since 3.3
	 * @access public
	 * @param array $args The placeholder arguments.
	 * @return array
	 */
	public static function get_params( $args ) {

		$params = [
			[
				'type'        => 'fusion_logics',
				'heading'     => esc_html__( 'Rendering Logic', 'fusion-builder' ),
				'param_name'  => 'render_logics',
				'description' => __( 'Add conditional rendering logic for the element. The element will only be part of the post / page contents, if the set conditions are met. <strong>Note:</strong> Server cache can interfere with results.', 'fusion-builder' ),
				'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
				'placeholder' => [
					'id'          => 'placeholder',
					'title'       => esc_html__( 'Select A Condition Type', 'fusion-builder' ),
					'type'        => 'text',
					'comparisons' => [
						'equal'     => esc_attr__( 'Equal To', 'fusion-builder' ),
						'not-equal' => esc_attr__( 'Not Equal To', 'fusion-builder' ),
					],
				],
				'choices'     => [
					[
						'id'          => 'device_type',
						'title'       => esc_html__( 'Device Type', 'fusion-builder' ),
						'type'        => 'select',
						'options'     => [
							'desktop'       => esc_html__( 'Desktop', 'fusion-builder' ),
							'mobile_tablet' => __( 'Mobile & Tablet', 'fusion-builder' ),
						],
						'comparisons' => [
							'equal'     => esc_attr__( 'Equal To', 'fusion-builder' ),
							'not-equal' => esc_attr__( 'Not Equal To', 'fusion-builder' ),
						],
					],
					[
						'id'          => 'get_var',
						'title'       => esc_html__( 'Get Variable', 'fusion-builder' ),
						'type'        => 'text',
						'additionals' => [
							'type'        => 'text',
							'title'       => esc_html__( 'Get', 'fusion-builder' ),
							'placeholder' => esc_html__( 'Variable Name', 'fusion-builder' ),
						],
						'comparisons' => [
							'equal'        => esc_attr__( 'Equal To', 'fusion-builder' ),
							'not-equal'    => esc_attr__( 'Not Equal To', 'fusion-builder' ),
							'greater-than' => esc_attr__( 'Greater Than', 'fusion-builder' ),
							'less-than'    => esc_attr__( 'Less Than', 'fusion-builder' ),
							'contains'     => esc_attr__( 'Contains', 'fusion-builder' ),
						],
					],
					[
						'id'          => 'user_agent',
						'title'       => esc_html__( 'User Agent', 'fusion-builder' ),
						'type'        => 'text',
						'comparisons' => [
							'equal'        => esc_attr__( 'Equal To', 'fusion-builder' ),
							'not-equal'    => esc_attr__( 'Not Equal To', 'fusion-builder' ),
							'greater-than' => esc_attr__( 'Greater Than', 'fusion-builder' ),
							'less-than'    => esc_attr__( 'Less Than', 'fusion-builder' ),
							'contains'     => esc_attr__( 'Contains', 'fusion-builder' ),
						],
					],
					[
						'id'          => 'user_role',
						'title'       => esc_html__( 'User Role', 'fusion-builder' ),
						'type'        => 'select',
						'options'     => self::get_user_roles(),
						'comparisons' => [
							'equal'     => esc_attr__( 'Equal To', 'fusion-builder' ),
							'not-equal' => esc_attr__( 'Not Equal To', 'fusion-builder' ),
						],
					],
					[
						'id'          => 'user_state',
						'title'       => esc_html__( 'User State', 'fusion-builder' ),
						'type'        => 'select',
						'options'     => [
							'logged_in'  => esc_html__( 'Logged In', 'fusion-builder' ),
							'logged_out' => esc_html__( 'Logged Out', 'fusion-builder' ),
						],
						'comparisons' => [
							'equal'     => esc_attr__( 'Equal To', 'fusion-builder' ),
							'not-equal' => esc_attr__( 'Not Equal To', 'fusion-builder' ),
						],
					],
				],
			],
		];

		$params = self::maybe_add_woo_options( $params );
		$params = self::maybe_add_ec_options( $params );

		// Override params.
		foreach ( $args as $key => $value ) {
			if ( 'fusion_remove_param' === $value && isset( $params[0][ $key ] ) ) {
				unset( $params[0][ $key ] );
				continue;
			}

			$params[0][ $key ] = $value;
		}

		return $params;

	}

	/**
	 * Returns all user roles.
	 *
	 * @since 3.3
	 * @return array states.
	 */
	public static function get_user_roles() {
		global $wp_roles;
		$roles = [];
		if ( is_array( $wp_roles->roles ) ) {
			foreach ( $wp_roles->roles as $key => $role ) {
				$roles[ $key ] = $role['name'];
			}
		}

		return $roles;
	}

	/**
	 * Adds WooCommerce Options.
	 *
	 * @since 3.3
	 * @param array $params The existing params.
	 * @return array.
	 */
	public static function maybe_add_woo_options( $params ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return $params;
		}

		$woo_options = [
			[
				'id'          => 'cart_status',
				'title'       => esc_html__( 'Cart Status', 'fusion-builder' ),
				'type'        => 'select',
				'options'     => [
					'in'    => esc_html__( 'In', 'fusion-builder' ),
					'empty' => esc_html__( 'Empty', 'fusion-builder' ),
				],
				'comparisons' => [
					'equal'     => esc_attr__( 'Equal To', 'fusion-builder' ),
					'not-equal' => esc_attr__( 'Not Equal To', 'fusion-builder' ),
				],
			],
			[
				'id'          => 'sale_status',
				'title'       => esc_html__( 'Sale Status', 'fusion-builder' ),
				'type'        => 'select',
				'options'     => [
					'started' => esc_html__( 'Started', 'fusion-builder' ),
					'ended'   => esc_html__( 'Ended', 'fusion-builder' ),
				],
				'comparisons' => [
					'equal'     => esc_attr__( 'Equal To', 'fusion-builder' ),
					'not-equal' => esc_attr__( 'Not Equal To', 'fusion-builder' ),
				],
			],
			[
				'id'          => 'stock_quantity',
				'title'       => esc_html__( 'Stock Quantity', 'fusion-builder' ),
				'type'        => 'text',
				'comparisons' => [
					'equal'        => esc_attr__( 'Equal To', 'fusion-builder' ),
					'not-equal'    => esc_attr__( 'Not Equal To', 'fusion-builder' ),
					'greater-than' => esc_attr__( 'Greater Than', 'fusion-builder' ),
					'less-than'    => esc_attr__( 'Less Than', 'fusion-builder' ),
				],
			],
		];

		$params[0]['choices'] = array_merge( $params[0]['choices'], $woo_options );

		return $params;
	}

	/**
	 * Adds Event Calendar Options.
	 *
	 * @since 3.3
	 * @param array $params The existing params.
	 * @return array.
	 */
	public static function maybe_add_ec_options( $params ) {
		if ( ! class_exists( 'Tribe__Events__Main' ) ) {
			return $params;
		}

		$ec_options = [
			[
				'id'          => 'event_status',
				'title'       => esc_html__( 'Event Status', 'fusion-builder' ),
				'type'        => 'select',
				'options'     => [
					'started' => esc_html__( 'Started', 'fusion-builder' ),
					'ended'   => esc_html__( 'Ended', 'fusion-builder' ),
				],
				'comparisons' => [
					'equal'     => esc_attr__( 'Equal To', 'fusion-builder' ),
					'not-equal' => esc_attr__( 'Not Equal To', 'fusion-builder' ),
				],
			],
		];

		$params[0]['choices'] = array_merge( $params[0]['choices'], $ec_options );

		return $params;
	}

	/**
	 * Checks if element should render or not.
	 *
	 * @since 3.3
	 * @param array $atts The attributes.
	 * @return bool.
	 */
	public static function should_render( $atts ) {
		$logics = ( isset( $atts['render_logics'] ) && '' !== $atts['render_logics'] ) ? json_decode( base64_decode( $atts['render_logics'] ) ) : [];
		$checks = [];

		if ( empty( $logics ) ) {
			return true;
		}

		foreach ( $logics as $logic ) {
			$check         = [];
			$operator      = isset( $logic->operator ) ? $logic->operator : '';
			$comparison    = isset( $logic->comparison ) ? $logic->comparison : '';
			$field_name    = isset( $logic->field ) ? $logic->field : '';
			$desired_value = isset( $logic->value ) ? $logic->value : '';
			$additionals   = isset( $logic->additionals ) ? $logic->additionals : '';
			$current_value = self::get_value( $field_name, $desired_value, $additionals );

			array_push( $check, $operator );
			array_push( $check, '' !== $current_value ? self::is_match( $current_value, $desired_value, $comparison ) : false );
			array_push( $checks, $check );

			fusion_library()->conditional_loading[] = [
				'operator'      => $operator,
				'comparison'    => $comparison,
				'field_name'    => $field_name,
				'desired_value' => $desired_value,
				'additionals'   => $additionals,
				'current_value' => $current_value,
			];
		}

		if ( count( $checks ) ) {
			return self::match_conditions( $checks );
		}
	}

	/**
	 * Gets value.
	 *
	 * @since 3.3
	 * @param string $name        The item name.
	 * @param string $value       The desired name.
	 * @param string $additionals The additional data.
	 * @return mixed.
	 */
	public static function get_value( $name, $value, $additionals ) {
		$woo_options   = [ 'cart_status', 'sale_status', 'stock_quantity' ];
		$event_options = [ 'event_status' ];

		if ( in_array( $name, $woo_options, true ) && ! class_exists( 'WooCommerce' ) || in_array( $name, $event_options, true ) && ! class_exists( 'Tribe__Events__Main' ) ) {
			return '';
		}

		switch ( $name ) {

			case 'user_state':
				return is_user_logged_in() ? 'logged_in' : 'logged_out';

			case 'cart_status':
				if ( 'in' === $value ) {
					$is_in_cart = false;
					$product_id = get_the_ID();
					$parent_id  = wp_get_post_parent_id( $product_id );
					$product_id = $parent_id > 0 ? $parent_id : $product_id;

					foreach ( WC()->cart->get_cart() as $cart_item ) {
						if ( $cart_item['product_id'] === $product_id ) {
							$is_in_cart = true;
						}
					}

					return $is_in_cart ? 'in' : null;
				} else {
					return 0 === WC()->cart->get_cart_contents_count() ? 'empty' : null;
				}

			case 'sale_status':
				$product = wc_get_product( get_the_ID() );

				if ( false === $product ) {
					return '';
				}

				if ( 'started' === $value ) {
					return $product->is_on_sale() ? 'started' : null;
				} else {
					return ! $product->is_on_sale() ? 'ended' : null;
				}

			case 'stock_quantity':
				$product = wc_get_product( get_the_ID() );

				if ( false === $product ) {
					return 0;
				}

				return $product->get_stock_quantity();

			case 'event_status':
				$id = get_the_ID();

				if ( ! tribe_is_event( $id ) ) {
					return '';
				}

				$event      = tribe_events_get_event( $id );
				$end_date   = tribe_get_end_date( $event, true, 'U' );
				$start_date = tribe_get_start_date( $event, true, 'U' );

				if ( 'started' === $value ) {
					return time() < $end_date ? 'started' : null;
				} else {
					return time() > $end_date ? 'ended' : null;
				}

			case 'device_type':
				return wp_is_mobile() ? 'mobile_tablet' : 'desktop';

			case 'user_agent':
				return isset( $_SERVER['HTTP_USER_AGENT'] ) ? wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			case 'user_role':
				$user = wp_get_current_user();
				return 0 !== $user->ID ? $user->roles : [];

			case 'get_var':
				return isset( $_GET[ $additionals ] ) ? sanitize_text_field( wp_unslash( $_GET[ $additionals ] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
	}

	/**
	 * Matches current and desired values.
	 *
	 * @since 3.3
	 * @param mixed  $current_value The current value.
	 * @param string $desired_value The desired value.
	 * @param string $comparison    The desired comparison.
	 * @return bool.
	 */
	public static function is_match( $current_value, $desired_value, $comparison ) {
		$current_value = is_array( $current_value ) ? $current_value : strtolower( $current_value );
		$desired_value = is_array( $desired_value ) ? $desired_value : strtolower( $desired_value );

		switch ( $comparison ) {
			case 'equal':
				return is_array( $current_value ) ? in_array( $desired_value, $current_value ) : $current_value === $desired_value; // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict

			case 'not-equal':
				return is_array( $current_value ) ? ! in_array( $desired_value, $current_value ) : $current_value !== $desired_value; // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict

			case 'greater-than':
				return floatval( $current_value ) > floatval( $desired_value );

			case 'less-than':
				return floatval( $current_value ) < floatval( $desired_value );

			case 'contains':
				return false !== strpos( $current_value, $desired_value );
		}
	}

	/**
	 * Matches conditions.
	 *
	 * @since 3.3
	 * @param array $checks An array of all the conditions.
	 * @return bool.
	 */
	public static function match_conditions( $checks ) {
		$is_match = null;
		$encoded  = wp_json_encode( $checks );

		// If all conditions are of OR type.
		if ( false === strpos( $encoded, 'and' ) ) {
			foreach ( $checks as $check ) {
				$is_match = null === $is_match ? $check[1] : $is_match;
				$is_match = $is_match || $check[1];
			}
			return $is_match;
		}

		// If all conditions are of AND type.
		if ( false === strpos( $encoded, 'or' ) ) {
			foreach ( $checks as $check ) {
				$is_match = null === $is_match ? $check[1] : $is_match;
				$is_match = $is_match && $check[1];
			}
			return $is_match;
		}

		return self::match_mixed_conditions( $checks );
	}

	/**
	 * Matches mixed conditions.
	 *
	 * @since 3.3
	 * @param array $checks An array of all the conditions.
	 * @return bool.
	 */
	public static function match_mixed_conditions( $checks ) {
		$collected_conditions = [];
		$current_operation    = '';
		$size                 = count( $checks );
		$j                    = 0;
		$k                    = 0;

		// Combine conditions based on comparison operator change.
		for ( $i = 0; $i < $size; $i++ ) {

			if ( '' === $current_operation || $current_operation === $checks[ $i ][0] ) {
				$collected_conditions[ $j ][ $k ] = $checks[ $i ][1];
				$k++;
				$collected_conditions[ $j ][ $k ] = $checks[ $i ][0];
				$k++;
				$current_operation = $checks[ $i ][0];
			} else {
				$collected_conditions[ $j ][ $k ] = $checks[ $i ][1];
				$k++;
				$collected_conditions[ $j ][ $k ] = $checks[ $i ][0];
				$j++;
				$k                 = 0;
				$current_operation = '';
			}
		}

		// Process conditions.
		$final_conditions = [];
		$main_operator    = '';
		$temp_result      = '';
		$inner_operator   = '';
		$operand_first    = '';
		$operand_second   = '';

		foreach ( $collected_conditions as $condition ) {
			$size = count( $condition );
			if ( $size < 3 ) {
				$final_conditions[] = $condition[0];
				$final_conditions[] = $condition[1];
				continue;
			}

			for ( $i = 0; $i < $size - 1; $i++ ) {

				if ( '' === $temp_result ) {
					$operand_first  = $condition[ $i ];
					$operand_second = $condition[ $i + 2 ];
					$inner_operator = $condition[ $i + 1 ];
					$i              = $i + 2;

					$temp_result = 'or' === $inner_operator ? ( $operand_first || $operand_second ) : ( $operand_first && $operand_second );
				} else {
					$operand_first  = $temp_result;
					$operand_second = $condition[ $i + 1 ];
					$inner_operator = $condition[ $i ];

					$temp_result = 'or' === $inner_operator ? ( $operand_first || $operand_second ) : ( $operand_first && $operand_second );
					$i++;
				}

				if ( true !== $temp_result ) {
					$temp_result = false;
				}
			}
			$main_operator = $condition;

			$final_conditions[] = $temp_result;
			$final_conditions[] = $main_operator[ $size - 1 ];
			$temp_result        = '';
		}

		// Final comparisons.
		$temp_result    = '';
		$inner_operator = '';
		$operand_first  = '';
		$operand_second = '';
		$size           = count( $final_conditions );

		if ( 3 > $size ) {
			return $final_conditions[0];
		}

		for ( $i = 0; $i < $size - 1; $i++ ) {
			if ( '' === $temp_result ) {
				$operand_first  = $final_conditions[ $i ];
				$operand_second = $final_conditions[ $i + 2 ];
				$inner_operator = $final_conditions[ $i + 1 ];
				$i              = $i + 2;

				$temp_result = 'or' === $inner_operator ? ( $operand_first || $operand_second ) : ( $operand_first && $operand_second );
			} else {
				$operand_first  = $temp_result;
				$operand_second = $final_conditions[ $i + 1 ];
				$inner_operator = $final_conditions[ $i ];

				$temp_result = 'or' === $inner_operator ? ( $operand_first || $operand_second ) : ( $operand_first && $operand_second );
				$i++;
			}

			if ( true !== $temp_result ) {
				$temp_result = false;
			}
		}

		return $temp_result;
	}
}
