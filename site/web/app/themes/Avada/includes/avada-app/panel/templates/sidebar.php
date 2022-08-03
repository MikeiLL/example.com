<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

$can_edit_theme_options   = current_user_can( 'edit_theme_options' );
$can_edit_published_pages = current_user_can( 'edit_published_pages' );
$can_edit_published_posts = current_user_can( 'edit_published_posts' );
$_post_type               = get_post_type();
$is_fusion_element        = apply_filters( 'fusion_hide_live_library_tab', ( 'fusion_element' === $_post_type && ! fusion_is_post_card() ) );
$po_name_array            = [
	'default'           => __( 'Page Options', 'Avada' ),
	'fusion_tb_section' => __( 'Layout Section Options', 'Avada' ),
	'fusion_form'       => __( 'Form Options', 'Avada' ),
];

$po_name = isset( $po_name_array[ $_post_type ] ) ? $po_name_array[ $_post_type ] : $po_name_array['default'];
?>
<script type="text/template" id="fusion-builder-sidebar-template">
	<?php if ( $can_edit_theme_options || $can_edit_published_pages || $can_edit_published_posts ) : ?>
		<# var editorActive = 'undefined' !== typeof FusionApp ? FusionApp.builderActive : false; #>
		<div id="customize-controls" class="wrap wp-full-overlay-sidebar" data-context="{{ context }}" data-editor="{{ editorActive }}" data-dialog="{{ dialog }}" data-archive="<?php echo ( ( function_exists( 'is_archive' ) && is_archive() && ( ! function_exists( 'is_shop' ) || function_exists( 'is_shop' ) && ! is_shop() ) ) ? 'true' : 'false' ); ?>">
			<div id="customizer-content">
				<div class="fusion-builder-toggles">
					<?php if ( $can_edit_theme_options ) : ?>
						<a href="#fusion-builder-sections-to" class="fusion-active">
							<span class="icon fusiona-cog"></span>
							<span class="label"><?php esc_html_e( 'Global Options', 'Avada' ); ?></span>
						</a>
					<?php endif; ?>
					<?php if ( $can_edit_published_pages || $can_edit_published_posts ) : ?>
						<?php if ( ! $is_fusion_element ) : ?>
							<a href="#fusion-builder-sections-po">
								<span class="icon fusiona-settings"></span>
								<span class="label fusion-po-only" data_name="<?php echo esc_html( $po_name ); ?>" data-layout="<?php esc_attr_e( 'Layout Section Options', 'Avada' ); ?>" data-page="<?php esc_attr_e( 'Page Options', 'Avada' ); ?>"><?php echo esc_html( $po_name ); ?></span>
								<span class="label fusion-tax-only"><?php esc_html_e( 'Taxonomy Options', 'Avada' ); ?></span>
							</a>
						<?php endif; ?>
						<a href="#fusion-builder-sections-eo">
							<span class="icon fusiona-pen"></span>
							<span class="label"><?php esc_html_e( 'Element Options', 'Avada' ); ?></span>
						</a>
					<?php endif; ?>
				</div>

				<?php if ( $can_edit_theme_options ) : ?>
					<div id="fusion-builder-sections-to" class="fusion-sidebar-section" data-context="TO">
						<div class="fusion-builder-search-wrapper">
							<input type="text" placeholder="<?php esc_attr_e( 'Search for global options', 'Avada' ); ?>" class="fusion-builder-search"/>
						</div>
						<div class="fusion-panels">
							<div class="fusion-panel-section-header-wrapper" data-context="FBE">
								<a href="#" class="fusion-builder-go-back" data-trigger="shortcode_styling" data-context="TO" title="<?php esc_attr_e( 'Back', 'Avada' ); ?>" aria-label="<?php esc_attr_e( 'Back', 'Avada' ); ?>">
									<svg version="1.1" width="18" height="18" viewBox="0 0 32 32"><path d="M12.586 27.414l-10-10c-0.781-0.781-0.781-2.047 0-2.828l10-10c0.781-0.781 2.047-0.781 2.828 0s0.781 2.047 0 2.828l-6.586 6.586h19.172c1.105 0 2 0.895 2 2s-0.895 2-2 2h-19.172l6.586 6.586c0.39 0.39 0.586 0.902 0.586 1.414s-0.195 1.024-0.586 1.414c-0.781 0.781-2.047 0.781-2.828 0z"></path></svg>
								</a>
								<span class="fusion-builder-tab-section-title"><?php esc_html_e( 'Avada Builder Elements', 'Avada' ); ?></span>
							</div>
							<div class="fusion-panel-section-header-wrapper" data-context="FBAO">
								<a href="#" class="fusion-builder-go-back" data-trigger="shortcode_styling" data-context="TO" title="<?php esc_attr_e( 'Back', 'Avada' ); ?>" aria-label="<?php esc_attr_e( 'Back', 'Avada' ); ?>">
									<svg version="1.1" width="18" height="18" viewBox="0 0 32 32"><path d="M12.586 27.414l-10-10c-0.781-0.781-0.781-2.047 0-2.828l10-10c0.781-0.781 2.047-0.781 2.828 0s0.781 2.047 0 2.828l-6.586 6.586h19.172c1.105 0 2 0.895 2 2s-0.895 2-2 2h-19.172l6.586 6.586c0.39 0.39 0.586 0.902 0.586 1.414s-0.195 1.024-0.586 1.414c-0.781 0.781-2.047 0.781-2.828 0z"></path></svg>
								</a>
								<span class="fusion-builder-tab-section-title"><?php esc_html_e( 'Add-on Elements', 'Avada' ); ?></span>
							</div>
						</div>
						<div class="fusion-tabs"></div>
					</div>
				<?php endif; ?>

				<?php if ( $can_edit_published_pages || $can_edit_published_posts ) : ?>
					<?php if ( ! $is_fusion_element ) : ?>
						<div id="fusion-builder-sections-po" style="display:none" class="fusion-sidebar-section">
							<div class="fusion-builder-search-wrapper">
								<input type="text" placeholder="<?php esc_attr_e( 'Search for page option(s)', 'Avada' ); ?>" class="fusion-builder-search fusion-po-only"/>
								<input type="text" placeholder="<?php esc_attr_e( 'Search for taxonomy option(s)', 'Avada' ); ?>" class="fusion-builder-search fusion-tax-only"/>
							</div>
							<div class="fusion-panels">
								<div class="fusion-empty-section">
									<?php esc_html_e( 'No page specific options are available for this page.', 'Avada' ); ?>
								</div>
							</div>
							<div class="fusion-tabs"></div>
						</div>
					<?php endif; ?>
					<div id="fusion-builder-sections-eo" style="display:none" class="fusion-sidebar-section">
						<div class="fusion-empty-section">
							<div class="fusion-centered-empty-contents">
								<i class="fusiona-pen" aria-hidden="true"></i>
								<h3><?php esc_html_e( 'Select an Element', 'Avada' ); ?></h3>
								<p><?php esc_html_e( 'Choose an existing element on the right to edit.', 'Avada' ); ?></p>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
</script>
