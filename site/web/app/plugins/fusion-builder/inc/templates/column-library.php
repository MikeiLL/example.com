<?php
/**
 * An underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-column-library-template">
	<div class="fusion-builder-modal-top-container">
		<h2 class="fusion-builder-settings-heading">
			<# if ( FusionPageBuilderApp.activeModal == 'container' ) { #>
				{{ fusionBuilderText.insert_section }}
			<# } else { #>
				{{ fusionBuilderText.insert_columns }}
			<# } #>
			<input type="text" class="fusion-elements-filter" placeholder="{{ fusionBuilderText.search_containers }}" />
		</h2>
		<ul class="fusion-tabs-menu">

			<# if ( FusionPageBuilderApp.activeModal !== 'container' ) { #>
				<li><a href="#default-columns">{{ fusionBuilderText.builder_columns }}</a></li>
				<li><a href="#custom-columns">{{ fusionBuilderText.library_columns }}</a></li>
			<# } #>
			<# if ( FusionPageBuilderApp.activeModal === 'container' ) { #>
				<li><a href="#default-columns">{{ fusionBuilderText.builder_sections }}</a></li>
				<li><a href="#custom-sections">{{ fusionBuilderText.library_sections }}</a></li>
				<li><a href="#misc">{{ fusionBuilderText.library_misc }}</a></li>
			<# } #>
		</ul>
	</div>
	<div class="fusion-builder-main-settings fusion-builder-main-settings-full">
		<div class="fusion-builder-column-layouts-container">
			<div class="fusion-tabs">
				<div id="default-columns" class="fusion-tab-content">
					<# if ( FusionPageBuilderApp.activeModal == 'container' ) { #>
						<?php echo fusion_builder_column_layouts( 'container' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<# } else { #>
						<?php echo fusion_builder_column_layouts(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<# } #>
				</div>

				<# if ( FusionPageBuilderApp.activeModal !== 'container' ) { #>
					<div id="custom-columns" class="fusion-tab-content">
						<div id="fusion-loader"><span class="fusion-builder-loader"></span></div>
					</div>
				<# } #>
				<# if ( FusionPageBuilderApp.activeModal == 'container' ) { #>
					<div id="custom-sections" class="fusion-tab-content">
						<div id="fusion-loader"><span class="fusion-builder-loader"></span></div>
					</div>
					<div id="misc" class="fusion-tab-content">
						<div class="fusion-builder-layouts-header">
							<div class="fusion-builder-layouts-header-info">
								<h2>{{ fusionBuilderText.special_title }}</h2>
								<span class="fusion-builder-layout-info">{{ fusionBuilderText.next_page_description }} <br> {{ fusionBuilderText.checkout_form_description }}</span>
							</div>
						</div>
						<ul class="fusion-builder-all-modules">
							<li class="fusion-special-item fusion-builder-section-next-page" data-type="fusion_builder_next_page">
								<h4 class="fusion_module_title">{{ fusionBuilderText.nextpage }}</h4>
							</li>
							<?php if ( class_exists( 'WooCommerce' ) ) { ?>
							<li class="fusion-special-item fusion-builder-section-checkout-form" data-type="fusion_woo_checkout_form">
								<h4 class="fusion_module_title">{{ fusionBuilderText.checkout_form }}</h4>
							</li>
						<?php } ?>
						</ul>
					</div>
				<# } #>
			</div>
		</div>
	</div>

	<div class="fusion-builder-modal-bottom-container">
		<a href="#" class="fusion-builder-modal-close"><span>{{ fusionBuilderText.cancel }}</span></a>
	</div>
</script>
