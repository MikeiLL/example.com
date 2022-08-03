<?php
/**
 * The main Patcher class.
 *
 * @package Fusion-Library
 * @subpackage Fusion-Patcher
 */

/**
 * The admin screen class for teh patcher.
 *
 * @since 1.0.0
 */
class Fusion_Patcher_Admin_Screen {

	/**
	 * Whether or not we've already added the menu.
	 *
	 * @static
	 * @access protected
	 * @since 1.0.0
	 * @var array
	 */
	protected static $menu_added = [];

	/**
	 * An array of printed forms.
	 *
	 * @static
	 * @access protected
	 * @since 1.0.0
	 * @var array
	 */
	protected static $printed_forms = [];

	/**
	 * An instance of the Fusion_Patcher class.
	 *
	 * @access private
	 * @since 1.0.0
	 * @var Object
	 */
	private $patcher = [];

	/**
	 * The patches.
	 *
	 * @access protected
	 * @since 1.0.0
	 * @var array
	 */
	protected $patches = [];

	/**
	 * Constructor.
	 *
	 * @access public
	 * @param object $patcher The Fusion_Patcher instance.
	 */
	public function __construct( $patcher ) {

		// Set the $patcher property.
		$this->patcher = $patcher;

		// If the product is bundled, early exit.
		$is_bundled = $this->patcher->is_bundled();
		if ( $is_bundled ) {
			return;
		}

		// Get the patches when we're in the patcher page.
		$args = $this->patcher->get_args();
		if ( isset( $args['is_patcher_page'] ) && true === $args['is_patcher_page'] ) {
			$this->patches = Fusion_Patcher_Client::get_patches( $args );
		}

		// Add menu page.
		add_action( 'avada_add_admin_menu_maintenance_pages', [ $this, 'admin_menu' ], 10 );

		// Call register settings function.
		add_action( 'admin_init', [ $this, 'settings' ] );

		add_action( 'admin_init', [ $this, 'init' ], 999 );

		if ( function_exists( 'add_allowed_options' ) ) {
			add_filter( 'allowed_options', [ $this, 'allowed_options' ] );
		} else {
			add_filter( 'whitelist_options', [ $this, 'allowed_options' ] );
		}
	}

	/**
	 * Additional actions.
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function init() {

		$is_patcher_page = $this->patcher->get_args( 'is_patcher_page' );
		if ( null === $is_patcher_page || false === $is_patcher_page ) {
			return;
		}

		// Set the $patches property.
		$bundles = $this->patcher->get_args( 'bundled' );
		if ( ! $bundles ) {
			$bundles = [];
		}
		foreach ( $bundles as $bundle ) {
			$instance = $this->patcher->get_instance( $bundle );
			if ( is_object( $instance ) ) {
				$bundle_patches = Fusion_Patcher_Client::get_patches( $instance->get_args() );
				foreach ( $bundle_patches as $key => $value ) {
					if ( ! isset( $this->patches[ $key ] ) ) {
						$this->patches[ $key ] = $value;
					}
				}
			}
		}

		// Add the patcher to the support screen.
		add_action( 'fusion_admin_pages_patcher', [ $this, 'form' ] );

	}


	/**
	 * Adds a submenu page.
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function admin_menu() {
		if ( isset( self::$menu_added[ $this->patcher->get_args( 'context' ) ] ) && self::$menu_added[ $this->patcher->get_args( 'context' ) ] ) {
			return;
		}

		add_submenu_page( // phpcs:ignore WPThemeReview.PluginTerritory.NoAddAdminPages
			$this->patcher->get_args( 'parent_slug' ),
			$this->patcher->get_args( 'page_title' ),
			$this->patcher->get_args( 'menu_title' ),
			'manage_options',
			$this->patcher->get_args( 'context' ) . '-patcher',
			[ $this, 'admin_page' ],
			9
		);
		self::$menu_added[ $this->patcher->get_args( 'context' ) ] = true;

	}

	/**
	 * The admin-page contents.
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function admin_page() {
		if ( class_exists( 'Avada_Admin' ) ) {
			Avada_Admin::get_admin_screens_header( 'patcher' );
		}

		/**
		 * Make sure that any patches marked as manually applied
		 * using the FUSION_MANUALLY_APPLIED_PATCHES constant are marked as complete.
		 */
		$this->manually_applied_patches();

		/**
		 * Adds the content of the form.
		 */
		do_action( 'fusion_admin_pages_patcher' );

		if ( class_exists( 'Avada_Admin' ) ) {
			Avada_Admin::get_admin_screens_footer();
		}

	}

	/**
	 * Register the settings.
	 *
	 * @access public
	 * @return void
	 */
	public function settings() {

		if ( empty( $this->patches ) ) {
			return;
		}
		// Register settings for the patch contents.
		foreach ( $this->patches as $key => $value ) {
			register_setting( 'fusion_patcher_' . $key, 'fusion_patch_contents_' . $key );
		}
	}

	/**
	 * The page contents.
	 *
	 * @access public
	 * @return void
	 */
	public function form() {

		if ( isset( self::$printed_forms[ $this->patcher->get_args( 'context' ) ] ) ) {
			return;
		}

		// Determine if there are available patches, and build an array of them.
		$available_patches = [];
		foreach ( $this->patches as $patch_id => $patch_args ) {
			if ( ! isset( $patch_args['patch'] ) ) {
				continue;
			}
			foreach ( $patch_args['patch'] as $key => $unique_patch_args ) {
				// Make sure the context is right.
				if ( $this->patcher->get_args( 'context' ) === $unique_patch_args['context'] ) {
					// Make sure the version is right.
					if ( $this->patcher->get_args( 'version' ) === $unique_patch_args['version'] ) {
						$available_patches[]                              = $patch_id;
						$context[ $this->patcher->get_args( 'context' ) ] = true;
					}
				}
				// Check for bundled products.
				$bundles = $this->patcher->get_args( 'bundled' );
				if ( ! $bundles ) {
					$bundles = [];
				}
				foreach ( $bundles as $bundle ) {
					$instance = $this->patcher->get_instance( $bundle );
					if ( is_object( $instance ) ) {
						// Make sure the context is right.
						if ( $instance->get_args( 'context' ) === $unique_patch_args['context'] ) {
							// Make sure the version is right.
							if ( $instance->get_args( 'version' ) === $unique_patch_args['version'] ) {
								$available_patches[]                         = $patch_id;
								$context[ $instance->get_args( 'context' ) ] = true;
							}
						}
					}
				}
			}
		}
		// Make sure we have a unique array.
		$available_patches = array_unique( $available_patches );
		// Sort the array by value and re-index the keys.
		sort( $available_patches );

		// Get an array of the already applied patches.
		$applied_patches = get_site_option( 'fusion_applied_patches', [] );

		// Get an array of patches that failed to be applied.
		$failed_patches = get_site_option( 'fusion_failed_patches', [] );

		// Get the array of messages to display.
		$messages = Fusion_Patcher_Admin_Notices::get_messages();
		?>
		<section class="avada-db-card avada-db-card-first avada-db-support-start">
			<h1 class="avada-db-support-heading"><?php esc_html_e( 'Avada Patcher', 'fusion-builder' ); ?></h1>
			<p><?php esc_html_e( 'The Patcher allows you to apply small fixes to your site between Avada releases, thereby keeping your site up to date.', 'fusion-builder' ); ?></p>

			<div class="avada-db-card-notice">
				<i class="fusiona-info-circle"></i>
				<p class="avada-db-card-notice-heading">
					<a href="https://theme-fusion.com/documentation/avada/install-update/avada-patcher/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Learn more about the Patcher.', 'fusion-builder' ); ?></a>
				</p>
			</div>
		</section>

		<div class="fusion-patcher avada-db-card">
			<h2 class="avada-db-patcher-heading">
				<?php
				if ( empty( $available_patches ) ) {
					/* translators: The product name and its version. */
					printf( esc_html__( 'Patches: Currently there are no patches available for %1$s %2$s', 'fusion-builder' ), esc_html( $this->patcher->get_args( 'name' ) ), esc_html( $this->patcher->get_args( 'version' ) ) );
				} else {
					/* translators: The product name and its version. */
					printf( esc_html__( 'Patches: The following patches are available for %1$s %2$s', 'fusion-builder' ), esc_html( $this->patcher->get_args( 'name' ) ), esc_html( $this->patcher->get_args( 'version' ) ) );
				}
				?>
			</h2>
			<?php if ( ! empty( $available_patches ) ) : ?>
				<p><?php esc_html_e( 'The status column displays if a patch was applied. However, a patch can be reapplied if necessary.', 'fusion-builder' ); ?></p>
			<?php endif; ?>


			<?php if ( ! empty( $messages ) ) : ?>
				<?php foreach ( $messages as $message_id => $message ) : ?>
					<?php
					if ( false !== strpos( $message_id, 'write-permissions-' ) ) {
						continue;
					}
					?>
					<div class="avada-db-card-error"><?php echo $message; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php if ( ! empty( $available_patches ) ) : // Only display the table if we have patches to apply. ?>
				<table class="fusion-patcher-table">
					<thead>
						<tr class="fusion-patcher-headings">
							<th><?php esc_html_e( 'Patch #', 'fusion-builder' ); ?></th>
							<th>
								<?php if ( ! empty( $bundles ) ) : ?>
									<?php esc_html_e( 'Product', 'fusion-builder' ); ?>
								<?php else : ?>
									<?php esc_html_e( 'Issue Date', 'fusion-builder' ); ?>
								<?php endif; ?>
							</th>
							<th><?php esc_html_e( 'Description', 'fusion-builder' ); ?></th>
							<th><?php esc_html_e( 'Status', 'fusion-builder' ); ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody>

						<?php foreach ( $available_patches as $key => $patch_id ) : ?>
							<?php

							// Do not allow applying the patch initially.
							// We'll have to check if they can later.
							$can_apply = false;

							// Make sure the patch exists.
							if ( ! array_key_exists( $patch_id, $this->patches ) ) {
								continue;
							}

							// Get the patch arguments.
							$patch_args = $this->patches[ $patch_id ];

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
							?>
							<tr class="fusion-patcher-table-head">
								<td class="patch-id">
									#<?php echo intval( $patch_id ); ?>
									<?php if ( ! empty( $bundles ) ) : ?>
										<div class="date">
											<?php echo esc_html( $patch_args['date'][0] ); ?>
										</div>
									<?php endif; ?>
								</td>
								<?php if ( ! empty( $bundles ) ) : ?>
									<?php
									// Splitting to multiple lines for PHP 5.2 compatibility.
									$product_name = str_replace( [ '-', '_' ], ' ', $patch_args['patch'][0]['context'] );
									$product_name = str_replace( 'fusion', 'avada', $product_name );
									$product_name = ucwords( $product_name );
									?>
									<td class="patch-product"><?php echo esc_html( $product_name ); ?></td>
								<?php else : ?>
									<td class="patch-date"><?php echo esc_html( $patch_args['date'][0] ); ?></td>
								<?php endif; ?>
								<td class="patch-description">
									<?php if ( isset( $messages[ 'write-permissions-' . $patch_id ] ) ) : ?>
										<div class="fusion-patcher-error" style="font-size:.85rem;">
											<?php echo $messages[ 'write-permissions-' . $patch_id ]; // phpcs:ignore WordPress.Security.EscapeOutput ?>
										</div>
									<?php endif; ?>
									<?php echo $patch_args['description'][0]; // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</td>
								<td class="patch-status">
									<?php if ( $patch_failed ) : ?>
										<span  class="fusiona-cross"></span>
									<?php elseif ( $patch_applied ) : ?>
										<span class="fusiona-checkmark"></span>
									<?php endif; ?>
								</td>
								<td class="patch-apply">
									<?php if ( $can_apply ) : ?>
										<form method="post" action="options.php">
											<?php settings_fields( 'fusion_patcher_' . $patch_id ); ?>
											<?php do_settings_sections( 'fusion_patcher_' . $patch_id ); ?>
											<input type="hidden" name="fusion_patch_contents_<?php echo intval( $patch_id ); ?>" value="<?php echo esc_attr( $this->format_patch( $patch_args ) ); ?>" />
											<?php if ( $patch_applied ) : ?>
												<?php submit_button( esc_html__( 'Patch Applied', 'fusion-builder' ), 'primary', 'submit', false ); ?>
											<?php else : ?>
												<?php submit_button( esc_html__( 'Apply Patch', 'fusion-builder' ), 'primary', 'submit', false ); ?>
												<?php if ( $patch_failed ) : ?>
													<div class="dismiss-notices"><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=avada-patcher&manually-applied-patch=' . $patch_id ) ); ?>"><?php esc_html_e( 'Dismiss Notices', 'fusion-builder' ); ?></a><div>
												<?php endif; ?>
											<?php endif; ?>
										</form>
									<?php else : ?>
										<span class="button disabled">
											<?php if ( isset( $available_patches[ $key - 1 ] ) ) : ?>
												<?php /* translators: The patch-ID. */ ?>
												<?php printf( esc_html__( 'Please apply patch #%s first.', 'fusion-builder' ), intval( $available_patches[ $key - 1 ] ) ); ?>
											<?php else : ?>
												<?php esc_html_e( 'Patch cannot be currently aplied.', 'fusion-builder' ); ?>
											<?php endif; ?>
										</span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
		self::$printed_forms[ $this->patcher->get_args( 'context' ) ] = true;
		// Delete some messages.
		Fusion_Patcher_Admin_Notices::remove_messages_option();
	}

	/**
	 * Format the patch.
	 * We're encoding everything here for security reasons.
	 * We're also going to check the current versions of Avada & Avada-Core,
	 * and then build the hash for this patch using the files that are needed.
	 *
	 * @since 4.0.0
	 * @access private
	 * @param array $patch The patch array.
	 * @return string
	 */
	private function format_patch( $patch ) {
		$patches = [];
		if ( ! isset( $patch['patch'] ) ) {
			return;
		}
		foreach ( $patch['patch'] as $key => $args ) {
			if ( ! isset( $args['context'] ) || ! isset( $args['path'] ) || ! isset( $args['reference'] ) ) {
				continue;
			}
			$valid_contexts   = [];
			$valid_contexts[] = $this->patcher->get_args( 'context' );
			$bundled          = $this->patcher->get_args( 'bundled' );
			if ( ! empty( $bundled ) ) {
				foreach ( $bundled as $product ) {
					$valid_contexts[] = $product;
				}
			}
			foreach ( $valid_contexts as $context ) {
				if ( $context === $args['context'] ) {
					$patcher_instance = $this->patcher->get_instance( $context );
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
		return fusion_encode_input( wp_json_encode( $patches ) );
	}

	/**
	 * Make sure manually applied patches show as successful.
	 *
	 * @access private
	 * @since 5.0.3
	 */
	private function manually_applied_patches() {

		$manual_patches_found = '';
		if ( isset( $_GET['manually-applied-patch'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$manual_patches_found = sanitize_text_field( wp_unslash( $_GET['manually-applied-patch'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		}

		if ( defined( 'FUSION_MANUALLY_APPLIED_PATCHES' ) ) {
			$manual_patches_found = FUSION_MANUALLY_APPLIED_PATCHES . ',' . $manual_patches_found;
		}
		if ( empty( $manual_patches_found ) ) {
			return;
		}
		$messages_option = get_site_option( Fusion_Patcher_Admin_Notices::$option_name );
		$manual_patches  = explode( ',', $manual_patches_found );
		$applied_patches = get_site_option( 'fusion_applied_patches', [] );
		$failed_patches  = get_site_option( 'fusion_failed_patches', [] );

		foreach ( $manual_patches as $patch ) {
			$patch = (int) trim( $patch );

			// Update the applied-patches option.
			if ( ! in_array( $patch, $applied_patches, true ) ) {
				$applied_patches[] = $patch;
				update_site_option( 'fusion_applied_patches', $applied_patches );
			}

			// If the patch is in the array of failed patches, remove it.
			if ( in_array( $patch, $failed_patches, true ) ) {
				$failed_key = array_search( $patch, $failed_patches, true );
				unset( $failed_patches[ $failed_key ] );
				update_site_option( 'fusion_failed_patches', $failed_patches );
			}

			// Remove messages if they exist.
			if ( isset( $this->patches[ $patch ] ) ) {
				foreach ( $this->patches[ $patch ]['patch'] as $args ) {
					$message_id = 'write-permissions-' . $patch;
					if ( isset( $messages_option[ $message_id ] ) ) {
						unset( $messages_option[ $message_id ] );
						update_site_option( Fusion_Patcher_Admin_Notices::$option_name, $messages_option );
					}
				}
			}
		}
	}

	/**
	 * Whitelist options.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param array $options The whitelisted options.
	 * @return array
	 */
	public function allowed_options( $options ) {

		$added = [];
		// Register settings for the patch contents.
		foreach ( $this->patches as $key => $value ) {
			$added[ 'fusion_patcher_' . $key ] = [
				'fusion_patch_contents_' . $key,
			];
		}

		if ( function_exists( 'add_allowed_options' ) ) {
			$options = add_allowed_options( $added, $options );
		} else {
			$options = add_option_whitelist( $added, $options );
		}
		return $options;
	}
}
