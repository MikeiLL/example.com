<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/template" id="fusion-builder-blank-form-template">
	<div class="fusion-builder-blank-page-content fusion-builder-data-cid" data-cid="{{ cid }}">
		<!-- The title, depending on whether this is a template or not, and the context of that template.  -->
		<# if ( 'fusion_form' === FusionApp.data.postDetails.post_type ) { #>
			<h1 class="title">{{ fusionBuilderText.to_get_started_form }}</h1>
		<# } else { #>
			<h1 class="title">{{ fusionBuilderText.to_get_started }}</h1>
		<# } #>
		<h2 class="subtitle">{{ fusionBuilderText.to_get_started_sub }}</h2>
		<a href="#" class="fusion-builder-new-section-add fusion-builder-module-control fusion-builder-submit-button"><span class="fusiona-add-container"></span><?php esc_html_e( 'Add Container', 'fusion-builder' ); ?></a>
		<!-- Allow pre-built pages on the content override, or when this is not a template. -->
		<# if ( 'fusion_form' === FusionApp.data.postDetails.post_type ) { #>
			<a href="#" id="fusion-load-template-dialog" class="fusion-builder-module-control fusion-builder-submit-button"><span class="fusiona-plus"></span> {{ fusionBuilderText.pre_built_form }}</a>
		<# } else { #>
			<a href="#" id="fusion-load-template-dialog" class="fusion-builder-module-control fusion-builder-submit-button"><span class="fusiona-plus"></span> {{ fusionBuilderText.pre_built_page }}</a>
		<# } #>
	</div>
	<div class="fusion-builder-blank-page-info fusion-builder-blank-page-video">
		<a href="#" class="info-icon fusion-builder-video-button">
			<span class="fa-play fas"></span>
		</a>
		<h3>{{{ fusionBuilderText.get_started_video }}}</h3>
		<p class="fusion-video-description">{{ fusionBuilderText.get_started_video_description }}</p>
		<a href="#" class="fusion-builder-submit-button fusion-builder-video-button">{{ fusionBuilderText.watch_the_video_link }}<span class="fa-long-arrow-alt-right fas"></span> </a>
	</div>

	<div class="fusion-builder-blank-page-info fusion-builder-blank-page-docs">
		<a href="https://theme-fusion.com/documentation/fusion-builder/" target="_blank" class="info-icon fusion-builder-docs-button">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="20" height="22" viewBox="0 0 20 22">
				<image id="Ellipse_2" data-name="Ellipse 2" width="20" height="22" xlink:href="data:img/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAWCAQAAABqSHSNAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAAAmJLR0QAAKqNIzIAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfiCQYBAS3iJSqHAAAAS0lEQVQoz9WQMQrAMAwDT8UP98/VKaFLiQ2BEE0ajkO2zMynzmiUh2LkIlg2BgC5oPLwxqKyebUWlNtG+P9lNjfK3rzxBjDGVduML101DCz6qjHzAAAAAElFTkSuQmCC"/>
			</svg>
		</a>
		<h3>{{{ fusionBuilderText.fusion_builder_docs }}}</h3>
		<p class="fusion-docs-description">{{ fusionBuilderText.fusion_builder_docs_description }}</p>
		<a href="https://theme-fusion.com/documentation/fusion-builder/" target="_blank" class="fusion-builder-submit-button fusion-builder-docs-button">{{ fusionBuilderText.fusion_builder_docs }}<span class="fa-long-arrow-alt-right fas"></span></a>
	</div>

	<div id="video-dialog" title="{{{ fusionBuilderText.getting_started_video }}}">
		<p><iframe width="640" height="360" src="https://www.youtube.com/embed/83cp_MoZuAw?rel=0&enablejsapi=1" frameborder="0" allowfullscreen></iframe></p>
	</div>
</script>
