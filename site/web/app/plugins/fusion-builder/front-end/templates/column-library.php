<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/template" id="fusion-builder-column-library-template">
	<div class="fusion-builder-modal-top-container">
		<div class="fusion-builder-modal-search">
			<label for="fusion-modal-search" class="fusiona-search"><span><?php esc_html_e( 'Search', 'fusion-builder' ); ?></span></label>
			<input type="text" id="fusion-modal-search" class="fusion-elements-filter" placeholder="{{ fusionBuilderText.search_columns }}" />
		</div>

		<# if ( 'undefined' === typeof nested ) { #>
			<ul class="fusion-tabs-menu">
				<li><a href="#default-columns">{{ fusionBuilderText.builder_columns }}</a></li>
				<li><a href="#custom-columns">{{ fusionBuilderText.library_columns }}</a></li>
			</ul>
		<# } #>
	</div>
	<div class="fusion-builder-main-settings fusion-builder-main-settings-full">
		<div class="fusion-builder-column-layouts-container">
			<# if ( 'undefined' !== typeof nested && nested ) { #>
				<?php echo fusion_builder_inner_column_layouts(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<# } else { #>
				<div class="fusion-tabs">
					<div id="default-columns" class="fusion-tab-content">
						<?php echo fusion_builder_column_layouts(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>

					<div id="custom-columns" class="fusion-tab-content">
						<div id="fusion-loader"><span class="fusion-builder-loader"></span></div>
					</div>
				</div>
			<# } #>
		</div>
	</div>
</script>
