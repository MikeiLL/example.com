<?php
/**
 * Prebuilt Websites Admin page.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
?>

<?php self::get_admin_screens_header( 'prebuilt-websites' ); ?>

	<div class="updated error avada-db-card avada-db-notice importer-notice importer-notice-1" style="display: none;">
		<h2><?php esc_html_e( 'We\'re sorry but the demo data could not be imported', 'Avada' ); ?></h2>
		<p><?php esc_html_e( 'This is most likely due to low PHP configurations on your server. There are two possible solutions.', 'Avada' ); ?></p>

		<p><strong><?php esc_html_e( 'Solution 1:', 'Avada' ); ?></strong> <?php esc_html_e( 'Import the demo using an alternate method.', 'Avada' ); ?><a href="https://theme-fusion.com/documentation/avada/demo-content-info/alternate-demo-method/" class="button-primary" target="_blank" style="margin-left: 10px;"><?php esc_html_e( 'Alternate Method', 'Avada' ); ?></a></p>
		<?php /* translators: %1$s: RED. %2$s: "Reset WordPress Plugin" link. */ ?>
		<p><strong><?php esc_html_e( 'Solution 2:', 'Avada' ); ?></strong> <?php printf( __( 'Fix the PHP configurations reported in %1$s on the Status page, then use the %2$s, then reimport.', 'Avada' ), '<strong style="color: red;">' . esc_html__( 'RED', 'Avada' ) . '</strong>', '<a href="' . esc_url( admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=wordpress-reset&amp;TB_iframe=true&amp;width=830&amp;height=472' ) ) . '">' . esc_html__( 'Reset WordPress Plugin', 'Avada' ) . '</a>' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><a href="<?php echo esc_url( admin_url( 'admin.php?page=avada-status' ) ); ?>" class="button-primary" target="_blank" style="margin-left: 10px;"><?php esc_html_e( 'Status', 'Avada' ); ?></a></p>
	</div>

	<div class="updated avada-db-card avada-db-notice avada-db-notice-success importer-notice importer-notice-2" style="display: none;">
		<h2><?php esc_html_e( 'Demo data successfully imported', 'Avada' ); ?></h2>
		<?php /* translators: "Regenerate Thumbnails" plugin link. */ ?>
		<p><?php printf( esc_html__( 'Install and run %s plugin once if you would like images generated to the specific theme sizes. This is not needed if you upload your own images because WP does it automatically.', 'Avada' ), '<a href="' . esc_url( admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=regenerate-thumbnails&amp;TB_iframe=true&amp;width=830&amp;height=472' ) ) . '" class="thickbox" title="' . esc_html__( 'Regenerate Thumbnails', 'Avada' ) . '">' . esc_html__( 'Regenerate Thumbnails', 'Avada' ) . '</a>' ); ?></p>
		<?php /* translators: "Permalinks" link. */ ?>
		<p><?php printf( esc_html__( 'Please visit the %s page and change your permalinks structure to "Post Name" so that content links work properly.', 'Avada' ), '<a href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '">' . esc_html__( 'Permalinks', 'Avada' ) . '</a>' ); ?></p>
	</div>

	<div class="updated error avada-db-card avada-db-notice importer-notice importer-notice-3" style="display: none;">
		<h2><?php esc_html_e( 'We\'re sorry but the demo data could not be imported', 'Avada' ); ?></h2>
		<p><?php esc_html_e( 'This is most likely due to low PHP configurations on your server. There are two possible solutions.', 'Avada' ); ?></p>

		<p><strong><?php esc_html_e( 'Solution 1:', 'Avada' ); ?></strong> <?php esc_html_e( 'Import the demo using an alternate method.', 'Avada' ); ?><a href="https://theme-fusion.com/documentation/avada/demo-content-info/alternate-demo-method/" class="button-primary" target="_blank" style="margin-left: 10px;"><?php esc_html_e( 'Alternate Method', 'Avada' ); ?></a></p>
		<?php /* translators: %1$s: RED. %2$s: "Reset WordPress Plugin" link. */ ?>
		<p><strong><?php esc_html_e( 'Solution 2:', 'Avada' ); ?></strong> <?php printf( esc_html__( 'Fix the PHP configurations reported in %1$s on the Status page, then use the %2$s, then reimport.', 'Avada' ), '<strong style="color: red;">' . esc_html__( 'RED', 'Avada' ) . '</strong>', '<a href="' . esc_url_raw( admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=wordpress-reset&amp;TB_iframe=true&amp;width=830&amp;height=472' ) ) . '">' . esc_html__( 'Reset WordPress Plugin', 'Avada' ) . '</a>' ); ?><a href="<?php echo esc_url( admin_url( 'admin.php?page=avada-status' ) ); ?>" class="button-primary" target="_blank" style="margin-left: 10px;"><?php esc_html_e( 'Status', 'Avada' ); ?></a></p>
	</div>

	<div class="updated error avada-db-card avada-db-notice importer-notice importer-notice-4" style="display: none;">
		<h2><?php esc_html_e( 'We\'re sorry but the demo data could not be imported. We were unable to find import file.', 'Avada' ); ?></h2>

		<p><strong><?php esc_html_e( 'Solution 1:', 'Avada' ); ?></strong> <?php esc_html_e( 'Import the demo using an alternate method.', 'Avada' ); ?><a href="https://theme-fusion.com/documentation/avada/demo-content-info/alternate-demo-method/" class="button-primary" target="_blank" style="margin-left: 10px;"><?php esc_html_e( 'Alternate Method', 'Avada' ); ?></a></p>
		<p><strong><?php esc_html_e( 'Solution 2:', 'Avada' ); ?></strong> <?php esc_html_e( 'Make sure WordPress directory permissions are correct and uploads directory is writable.', 'Avada' ); ?><a href="https://codex.wordpress.org/Changing_File_Permissions" class="button-primary" target="_blank" style="margin-left: 10px;"><?php esc_html_e( 'Learn More', 'Avada' ); ?></a></p>
	</div>

	<?php if ( Avada()->registration->should_show( 'prebuilt' ) ) : ?>
		<?php
		// Include the Avada_Importer_Data class if it doesn't exist.
		if ( ! class_exists( 'Avada_Importer_Data' ) ) {
			include_once Avada::$template_dir_path . '/includes/importer/class-avada-importer-data.php';
		}
		?>

		<script type="text/javascript">
			var DemoImportNonce = '<?php echo esc_attr( wp_create_nonce( 'avada_demo_ajax' ) ); ?>';
		</script>

		<section class="avada-db-card avada-db-card-first avada-db-demos-start">
			<h1 class="avada-db-demos-heading"><?php esc_html_e( 'Import A Prebuilt Website', 'Avada' ); ?></h1>
			<p><?php esc_html_e( 'Import any of the prebuilt websites below. Once done, your site will have the exact same look and feel as the the sites in the preview.', 'Avada' ); ?></p>

			<div class="avada-db-card-notice">
				<i class="fusiona-info-circle"></i>
				<p class="avada-db-card-notice-heading">
					<?php
					printf(
						/* translators: %1$s: "Status" link. %2$s: "View more info here" link. */
						esc_html__( 'Prebuilt website imports can vary in time. Please check the %1$s page to ensure your server meets all requirements for a successful import. Settings that need attention will be listed in red. %2$s.', 'Avada' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=avada-status' ) ) . '" target="_blank">' . esc_html__( 'Status', 'Avada' ) . '</a>',
						'<a href="' . esc_url( self::$theme_fusion_url ) . 'documentation/avada/demo-content-info/import-all-demo-content/" target="_blank">' . esc_attr__( 'View more info here', 'Avada' ) . '</a>'
					);
					?>
				</p>
			</div>
		</section>
		<?php
		// Get theme version for later.
		$theme_version = Avada_Helper::normalize_version( $this->theme_version );

		$demos = Avada_Importer_Data::get_data();

		// Collect and sort all tags to setup demo filtering.
		$all_tags   = [];
		$count_tags = [];
		foreach ( $demos as $demo => $demo_details ) {
			if ( ! isset( $demo_details['tags'] ) ) {
				$demo_details['tags'] = [];
			}
			$all_tags = array_replace_recursive( $all_tags, $demo_details['tags'] );

			// Count for tags.
			$demo_details_tags = array_keys( $demo_details['tags'] );
			foreach ( $demo_details_tags as $demo_tag ) {
				if ( ! isset( $count_tags[ $demo_tag ] ) ) {
					$count_tags[ $demo_tag ] = 0;
				}
				$count_tags[ $demo_tag ]++;
			}
		}

		arsort( $count_tags );

		// Check which recommended plugins are installed and activated.
		$plugin_dependencies = Avada_TGM_Plugin_Activation::$instance->plugins;

		foreach ( $plugin_dependencies as $key => $plugin_args ) {
			$plugin_dependencies[ $key ]['active']    = fusion_is_plugin_activated( $plugin_args['file_path'] );
			$plugin_dependencies[ $key ]['installed'] = file_exists( WP_PLUGIN_DIR . '/' . $plugin_args['file_path'] );
		}

		// Import / Remove demo.
		$imported_data = get_option( 'fusion_import_data', [] );

		$import_stages = avada_get_demo_import_stages();

		// Get all demos that have been imported to the site.
		$imported_demos_count = [];
		foreach ( $imported_data as $stage => $imported_demos ) {
			foreach ( $imported_demos as $imported_demo ) {
				if ( ! in_array( $imported_demo, $imported_demos_count, true ) ) {
					$imported_demos_count[] = $imported_demo;
				}
			}
		}
		?>
		<div class="avada-db-demos-wrapper">
			<?php
			/**
			 * Add the tag-selector.
			 */
			?>
			<section class="avada-db-demo-selector avada-db-card">
				<div class="avada-importer-tags-selector">
					<h2><?php esc_html_e( 'Filter Sites', 'Avada' ); ?></h2>
					<input id="avada-demos-search" class="avada-db-demos-search" type="text" placeholder="<?php esc_attr_e( 'Search prebuilt sites', 'Avada' ); ?>"/>
					<ul>
						<li data-tag="all">
							<button class="button avada-db-demos-filter current-filter" data-tag="all">
								<span class="avada-db-demos-filter-text"><?php esc_html_e( 'All Prebuilt Sites', 'Avada' ); ?></span>
								<span class="count">(<?php echo esc_html( count( $demos ) ); ?>)</span>
							</button>
						</li>
						<li data-tag="all">
							<button class="button avada-db-demos-filter avada-db-demos-filter-imported" data-tag="imported" data-count="<?php echo esc_attr( count( $imported_demos_count ) ); ?>">
								<span class="avada-db-demos-filter-text"><?php esc_html_e( 'Imported', 'Avada' ); ?></span>
								<span class="count">(<?php echo esc_html( count( $imported_demos_count ) ); ?>)</span>
							</button>
						</li>

						<?php foreach ( $count_tags as $key => $count ) : ?>
							<li>
								<button class="button avada-db-demos-filter" data-tag="<?php echo esc_attr( $key ); ?>">
									<span class="avada-db-demos-filter-text">
									<?php
									printf(
										/* Translators: Tag name (string) */
										esc_html( $all_tags[ $key ] )
									);
									?>
									</span>
									<span class="count">(<?php echo esc_html( absint( $count ) ); ?>)</span>
								</button>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</section>

			<section class="avada-db-demos-themes avada-db-card avada-db-card-transparent">
				<div class="feature-section theme-browser rendered">

					<?php foreach ( $demos as $demo => $demo_details ) : // Loop through all demos. ?>

						<?php
						// Make sure we don't show demos that can't be applied to this version.
						if ( isset( $demo_details['minVersion'] ) ) {
							$min_version = Avada_Helper::normalize_version( $demo_details['minVersion'] );
							if ( version_compare( $theme_version, $min_version ) < 0 ) {
								continue;
							}
						}
						// Set tags.
						if ( ! isset( $demo_details['tags'] ) ) {
							$demo_details['tags'] = [];
						}

						$tags = array_keys( $demo_details['tags'] );
						$tags = implode( ',', $tags );

						if ( empty( $demo_details['plugin_dependencies'] ) ) {
							$demo_details['plugin_dependencies'] = [];
						}

						$demo_details['plugin_dependencies']['fusion-core']    = true;
						$demo_details['plugin_dependencies']['fusion-builder'] = true;

						$demo_imported = false;
						// Generate Import / Remove forms.
						$import_form  = '<form id="import-' . esc_attr( strtolower( $demo ) ) . '" data-demo-id=' . esc_attr( strtolower( $demo ) ) . '>';
						$import_form .= '<p><input type="checkbox" value="all" id="import-all-' . esc_attr( strtolower( $demo ) ) . '"/> <label for="import-all-' . esc_attr( strtolower( $demo ) ) . '">' . esc_html__( 'All', 'Avada' ) . '</label></p>';
						$remove_form  = '<form id="remove-' . esc_attr( strtolower( $demo ) ) . '" data-demo-id=' . esc_attr( strtolower( $demo ) ) . '>';

						foreach ( $import_stages as $import_stage ) {

							$import_checked  = '';
							$remove_disabled = 'disabled';
							$data            = '';
							if ( ! empty( $import_stage['plugin_dependency'] ) && empty( $demo_details['plugin_dependencies'][ $import_stage['plugin_dependency'] ] ) ) {
								continue;
							}

							if ( ! empty( $import_stage['feature_dependency'] ) && ! in_array( $import_stage['feature_dependency'], $demo_details['features'] ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
								continue;
							}

							if ( ! empty( $imported_data[ $import_stage['value'] ] ) ) {
								if ( in_array( strtolower( $demo ), $imported_data[ $import_stage['value'] ] ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
									$import_checked  = 'checked="checked" disabled';
									$remove_disabled = 'checked="checked"';
									$demo_imported   = true;
								}
							}
							if ( ! empty( $import_stage['data'] ) ) {
								$data = 'data-type="' . esc_attr( $import_stage['data'] ) . '"';
							}
							$import_form .= '<p><input type="checkbox" value="' . esc_attr( $import_stage['value'] ) . '" ' . $import_checked . ' ' . $data . ' id="import-' . esc_attr( $import_stage['value'] ) . '-' . esc_attr( strtolower( $demo ) ) . '" /> <label for="import-' . esc_attr( $import_stage['value'] ) . '-' . esc_attr( strtolower( $demo ) ) . '">' . $import_stage['label'] . '</label></p>';
							$remove_form .= '<p><input type="checkbox" value="' . esc_attr( $import_stage['value'] ) . '" ' . $remove_disabled . ' ' . $data . ' id="remove-' . esc_attr( $import_stage['value'] ) . '-' . esc_attr( strtolower( $demo ) ) . '" /> <label for="remove-' . esc_attr( $import_stage['value'] ) . '-' . esc_attr( strtolower( $demo ) ) . '">' . $import_stage['label'] . '</label></p>';
						}
						$import_form .= '</form>';
						$remove_form .= '</form>';

						$install_button_label = ! $demo_imported ? __( 'Import', 'Avada' ) : __( 'Modify', 'Avada' );

						if ( ! empty( $imported_data['all'] ) && in_array( strtolower( $demo ), $imported_data['all'] ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
							$demo_import_badge = __( 'Full Import', 'Avada' );
						} else {
							$demo_import_badge = __( 'Partial Import', 'Avada' );
						}

						$new_imported = '';
						$tags         = true === $demo_imported ? $tags . ',imported' : $tags;
						?>
						<div class="fusion-admin-box" data-imported="<?php echo esc_attr( true === $demo_imported ? '1' : '0' ); ?>" data-tags="<?php echo esc_attr( $tags ); ?>" data-title="<?php echo esc_attr( ucwords( str_replace( '_', ' ', $demo ) ) ); ?>">
							<div id="theme-demo-<?php echo esc_attr( strtolower( $demo ) ); ?>" class="theme">
								<div class="theme-wrapper">
									<div class="theme-screenshot">
										<img src="" <?php echo ( ! empty( $demo_details['previewImage'] ) ) ? 'data-src="' . esc_url_raw( $demo_details['previewImage'] ) . '"' : ''; ?> <?php echo ( ! empty( $demo_details['previewImageRetina'] ) ) ? 'data-src-retina="' . esc_url_raw( $demo_details['previewImageRetina'] ) . '"' : ''; ?>>
										<noscript>
											<img src="<?php echo esc_url_raw( $demo_details['previewImage'] ); ?>" width="325" height="244"/>
										</noscript>
									</div>
									<h3 class="theme-name" id="<?php esc_attr( $demo ); ?>"><?php echo esc_html( ucwords( str_replace( '_', ' ', $demo ) ) ); ?></h3>
									<div class="theme-actions">
										<a class="button button-primary button-install-open-modal" data-demo-id="<?php echo esc_attr( strtolower( $demo ) ); ?>" href="#"><?php echo esc_html( $install_button_label ); ?></a>
										<?php $preview_url = $this->theme_url . str_replace( '_', '-', $demo ); ?>
										<a class="button button-primary" target="_blank" href="<?php echo esc_url( $preview_url ); ?>"><?php esc_html_e( 'Preview', 'Avada' ); ?></a>
									</div>

									<?php if ( isset( $demo_details['new'] ) && true === $demo_details['new'] ) : ?>
										<?php $new_imported = ' plugin-required-premium'; ?>
										<div class="plugin-required"><?php esc_html_e( 'New', 'Avada' ); ?></div>
									<?php endif; ?>

									<div class="plugin-premium<?php echo esc_attr( $new_imported ); ?>" style="display: <?php echo esc_attr( true === $demo_imported ? 'block' : 'none' ); ?>;"><?php echo esc_html( $demo_import_badge ); ?></div>


								</div>
								<div id="demo-modal-<?php echo esc_attr( strtolower( $demo ) ); ?>" class="demo-update-modal-wrap" style="display:none;">

<div class="demo-update-modal-inner">

	<div class="demo-modal-thumbnail" style="background-image:url(<?php echo esc_attr( $demo_details['previewImage'] ); ?>);">
		<a class="demo-modal-preview" target="_blank" href="<?php echo esc_url( $preview_url ); ?>"><?php esc_html_e( 'Live Preview', 'Avada' ); ?></a>
	</div>

	<div class="demo-update-modal-content">

						<?php if ( in_array( true, $demo_details['plugin_dependencies'] ) ) : // phpcs:ignore WordPress.PHP.StrictInArray ?>
			<div class="demo-required-plugins">
				<h3><?php esc_html_e( 'Required Plugins To Import Content', 'Avada' ); ?></h3>
				<ul class="required-plugins-list">

							<?php foreach ( $demo_details['plugin_dependencies'] as $slug => $required ) : ?>

								<?php if ( true === $required ) : ?>
							<li>
								<span class="required-plugin-name">
									<?php
										$plugin_name = isset( $plugin_dependencies[ $slug ] ) ? $plugin_dependencies[ $slug ]['plugin_name'] : $slug;
										echo 'HubSpot' === $plugin_name ? sprintf(
											/* translators: %1$s: Plugin Slugh. %2$s: Documentation Link. */
											esc_html__( '%1$s (%2$s)', 'Avada' ),
											esc_html( $plugin_name ),
											'<a href="https://theme-fusion.com/documentation/avada/plugins/how-to-setup-hubspot-live-chat-with-avada/" rel="noopener noreferrer" target="_blank">' . esc_html__( 'Setup Required', 'Avada' ) . '</a>'
										) : esc_html( $plugin_name );
									?>
								</span>

									<?php
									$label  = __( 'Install', 'Avada' );
									$status = 'install'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
									if ( isset( $plugin_dependencies[ $slug ] ) && $plugin_dependencies[ $slug ]['active'] ) {
										$label  = __( 'Active', 'Avada' );
										$status = 'active'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
									} elseif ( isset( $plugin_dependencies[ $slug ] ) && $plugin_dependencies[ $slug ]['installed'] ) {
										$label  = __( 'Activate', 'Avada' );
										$status = 'activate'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
									}
									?>
									<span class="required-plugin-status <?php echo esc_attr( $status ); ?> ">
										<?php if ( 'activate' === $status ) : ?>
											<a href="<?php echo esc_url( admin_url( 'admin.php?page=avada-plugins' ) ); ?>"
												target="_blank"
												data-nonce="<?php echo esc_attr( wp_create_nonce( 'avada-activate' ) ); ?>"
												data-plugin="<?php echo esc_attr( $slug ); ?>"
												data-plugin_name="<?php echo esc_attr( $plugin_dependencies[ $slug ]['name'] ); ?>"
											>
										<?php elseif ( 'install' === $status ) : ?>
											<a href="<?php echo esc_url( admin_url( 'admin.php?page=avada-plugins' ) ); ?>"
												target="_blank"
												data-nonce="<?php echo esc_attr( wp_create_nonce( 'avada-activate' ) ); ?>"
												data-plugin="<?php echo esc_attr( $slug ); ?>"
												data-plugin_name="<?php echo esc_attr( $plugin_dependencies[ $slug ]['name'] ); ?>"
												data-tgmpa_nonce="<?php echo esc_attr( wp_create_nonce( 'tgmpa-install' ) ); ?>"
											>
										<?php endif; ?>

											<?php echo esc_html( $label ); ?>

										<?php if ( 'active' !== $status ) : ?>
											</a>
										<?php endif; ?>
									</span>
							</li>
						<?php endif; ?>

					<?php endforeach; ?>

				</ul>

			</div>
		<?php endif; ?>

		<div class="demo-update-form-wrap">
			<div class="demo-import-form">
				<h4 class="demo-form-title">
						<?php esc_html_e( 'Import Content', 'Avada' ); ?> <span><?php esc_html_e( '(menus only import with "All")', 'Avada' ); ?></span>
				</h4>
						<?php echo $import_form; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>

			<div class="demo-remove-form">
				<h4 class="demo-form-title">
						<?php esc_html_e( 'Remove Content', 'Avada' ); ?>
				</h4>

				<p>
					<input type="checkbox" value="uninstall" id="uninstall-<?php echo esc_attr( strtolower( $demo ) ); ?>" /> <label for="uninstall-<?php echo esc_attr( strtolower( $demo ) ); ?>"><?php esc_html_e( 'Remove', 'Avada' ); ?></label>
				</p>
						<?php echo $remove_form; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		</div>
	</div>

	<div class="demo-update-modal-status-bar">
		<div class="demo-update-modal-status-bar-label"><span></span></div>
		<div class="demo-update-modal-status-bar-progress-bar"></div>

		<a class="button-install-demo" data-demo-id="<?php echo esc_attr( strtolower( $demo ) ); ?>" href="#">
						<?php esc_html_e( 'Import', 'Avada' ); ?>
		</a>

		<a class="button-uninstall-demo" data-demo-id="<?php echo esc_attr( strtolower( $demo ) ); ?>" href="#">
						<?php esc_html_e( 'Remove', 'Avada' ); ?>
		</a>

		<a class="button-done-demo demo-update-modal-close" href="#">
						<?php esc_html_e( 'Done', 'Avada' ); ?>
		</a>
	</div>
</div>

<a href="#" class="demo-update-modal-corner-close demo-update-modal-close"><span class="dashicons dashicons-no-alt"></span></a>
</div> <!-- .demo-update-modal-wrap -->
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</section>
		</div>

		<div class="demo-import-overlay preview-all"></div>
		<div id="dialog-demo-confirm" title="<?php esc_attr_e( 'Warning ', 'Avada' ); ?>"></div>

		<script>
			!function(t){t.fn.unveil=function(i,e){function n(){var i=a.filter(function(){var i=t(this);if(!i.is(":hidden")){var e=o.scrollTop(),n=e+o.height(),r=i.offset().top,s=r+i.height();return s>=e-u&&n+u>=r}});r=i.trigger("unveil"),a=a.not(r)}var r,o=t(window),u=i||0,s=window.devicePixelRatio>1,l=s?"data-src-retina":"data-src",a=this;return this.one("unveil",function(){var t=this.getAttribute(l);t=t||this.getAttribute("data-src"),t&&(this.setAttribute("src",t),"function"==typeof e&&e.call(this))}),o.on("scroll.unveil resize.unveil lookup.unveil",n),n(),this}}(window.jQuery||window.Zepto);
			jQuery(document).ready(function() { jQuery( 'img' ).unveil( 200 ); });
		</script>
	<?php else : ?>
		<div class="avada-db-card avada-db-notice">
			<h2><?php esc_html_e( 'Avada\'s Prebuilt Websites Can Only Be Imported With A Valid Token Registration', 'Avada' ); ?></h2>

			<?php /* translators: "Product Registration" link. */ ?>
			<p><?php printf( esc_html__( 'Please visit the %s page and enter a valid purchase code to import the full prebuilt websites and the single pages through the page builder.', 'Avada' ), '<a href="' . esc_url_raw( admin_url( 'admin.php?page=avada#avada-db-registration' ) ) . '">' . esc_attr__( 'Product Registration', 'Avada' ) . '</a>' ); ?></p>
		</div>
	<?php endif; ?>
<?php $this->get_admin_screens_footer(); ?>
