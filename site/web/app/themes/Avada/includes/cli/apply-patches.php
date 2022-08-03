<?php
/**
 * Apply Avada patches.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage CLI
 * @since      7.3
 *
 * @usage: wp fusion patch apply
 * @todo: make possible to apply only specific patch
 */

$fusion_apply_patches_cmd = function( $args, $assoc_args ) {

	$patches = Fusion_Patcher_Client::get_patches( [] );

	// Make sure we have a unique array.
	$available_patches = array_keys( $patches );
	// Sort the array by value (lowest to highest) and re-index the keys.
	sort( $available_patches );

	foreach ( $available_patches as $key => $patch_id ) {

		// Get an array of the already applied patches.
		$applied_patches = get_site_option( 'fusion_applied_patches', [] );

		// Get an array of patches that failed to be applied.
		$failed_patches = get_site_option( 'fusion_failed_patches', [] );

		// Do not allow applying the patch initially.
		// We'll have to check if they can later.
		$can_apply = false;

		/**
		 * Make sure the patch exists.
		 * if ( ! array_key_exists( $patch_id, $patches ) ) {
		 * continue;
		 * }
		 */

		// Get the patch arguments.
		$patch_args = $patches[ $patch_id ];

		// Has the patch been applied?
		$patch_applied = ( in_array( $patch_id, $applied_patches, true ) );

		// Has the patch failed?
		$patch_failed = ( in_array( $patch_id, $failed_patches, true ) );

		// If there is no previous patch, we can apply it.
		if ( ! isset( $available_patches[ $key - 1 ] ) ) {
			$can_apply = true;
		}

		// If the previous patch exists and has already been applied,
		// then we can apply this one.
		if ( isset( $available_patches[ $key - 1 ] ) ) {
			if ( in_array( $available_patches[ $key - 1 ], $applied_patches, true ) ) {
				$can_apply = true;
			}
		}

		if ( $can_apply && ! $patch_applied ) {
			Fusion_Patcher_Apply_Patch::cli_apply_patch( $patch_id, fusion_format_patch( $patch_args ) );
			WP_CLI::success( '#' . $patch_id . ' patch applied.' );
		} else {
			WP_CLI::log( 'Skip the patch: ' . $patch_id );
		}
	}
};

if ( class_exists( 'WP_CLI' ) ) {
	WP_CLI::add_command( 'fusion patch apply', $fusion_apply_patches_cmd );
}

/**
 * WIP: Helper function
 *
 * @param array $patch Patch array.
 * @return array
 */
function fusion_format_patch( $patch ) {
	global $avada_patcher;

	$patches = [];
	if ( ! isset( $patch['patch'] ) ) {
		return;
	}
	foreach ( $patch['patch'] as $key => $args ) {
		if ( ! isset( $args['context'] ) || ! isset( $args['path'] ) || ! isset( $args['reference'] ) ) {
			continue;
		}
		$valid_contexts   = [];
		$valid_contexts[] = $avada_patcher->get_args( 'context' );
		$bundled          = $avada_patcher->get_args( 'bundled' );
		if ( ! empty( $bundled ) ) {
			foreach ( $bundled as $product ) {
				$valid_contexts[] = $product;
			}
		}
		foreach ( $valid_contexts as $context ) {
			if ( $context === $args['context'] ) {
				$patcher_instance = $avada_patcher->get_instance( $context );
				if ( null === $patcher_instance ) {
					continue;
				}
				$v1 = Fusion_Helper::normalize_version( $patcher_instance->get_args( 'version' ) );
				$v2 = Fusion_Helper::normalize_version( $args['version'] );
				if ( version_compare( $v1, $v2, '==' ) ) {
					$patches[ $context ][ $args['path'] ] = $args['reference'];
				}
			}
		}
	}
	return $patches;
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
