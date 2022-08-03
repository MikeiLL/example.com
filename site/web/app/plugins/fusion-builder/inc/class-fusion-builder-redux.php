<?php
/**
 * The Fusion_Builder_Redux class.
 *
 * @package fusion-builder
 */

/**
 * The Fusion_Builder_Redux class.
 */
class Fusion_Builder_Redux extends Fusion_FusionRedux {

	/**
	 * Initializes and triggers all other actions/hooks.
	 *
	 * @access public
	 */
	public function init_fusionredux() {

		add_filter( 'fusion_options_font_size_dimension_fields', [ $this, 'fusion_options_font_size_dimension_fields' ] );
		add_filter( 'fusion_options_sliders_not_in_pixels', [ $this, 'fusion_options_sliders_not_in_pixels' ] );

		parent::init_fusionredux();
	}

	/**
	 * Adds options to be processes as font-sizes.
	 * Affects the field's sanitization call.
	 *
	 * @access public
	 * @since 1.1.0
	 * @param array $fields An array of fields.
	 * @return array
	 */
	public function fusion_options_font_size_dimension_fields( $fields ) {
		$extra_fields = [
			'content_box_title_size',
			'content_box_icon_size',
			'social_links_font_size',
		];
		return array_unique( array_merge( $fields, $extra_fields ) );
	}

	/**
	 * Sliders that are not in pixels.
	 *
	 * @access public
	 * @since 1.1.0
	 * @param array $fields An array of fields.
	 * @return array
	 */
	public function fusion_options_sliders_not_in_pixels( $fields ) {
		$extra_fields = [
			'before_after_offset',
			'before_after_transition_time',
			'blog_grid_columns',
			'gallery_columns',
			'carousel_speed',
			'counter_box_speed',
			'flip_boxes_flip_duration',
			'testimonials_speed',
			'text_columns',
		];
		return array_unique( array_merge( $fields, $extra_fields ) );
	}

	/**
	 * Extra functionality on save.
	 *
	 * @access public
	 * @since 1.1
	 * @param array $data           The data.
	 * @param array $changed_values The changed values to save.
	 * @return void
	 */
	public function save_as_option( $data, $changed_values ) {
		update_option( 'fusion_cache_server_ip', $data['cache_server_ip'] );
	}
}
