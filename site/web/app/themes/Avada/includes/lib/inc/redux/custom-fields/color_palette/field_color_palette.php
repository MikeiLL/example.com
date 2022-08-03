<?php
/**
 * Custom Color Palette field Avada.
 *
 * @package Fusion-Library
 * @since 2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FusionReduxFramework_color_palette' ) ) {

	/**
	 * The field class.
	 *
	 * @since 2.0
	 */
	class FusionReduxFramework_color_palette {

		/**
		 * Field Constructor.
		 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
		 *
		 * @since FusionRedux_Options 2.0.1
		 */
		public function __construct( $field = array(), $value = '', $parent = null ) {
			$this->parent = $parent;
			$this->field  = $field;
			$this->value  = $value;
		}

		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @since FusionRedux_Options 2.0.1
		 */
		public function render() {
			$value = ( empty( $this->value ) || ! is_string( $this->value ) ) ? $this->field['default'] : $this->value;
			$value = explode( '|', $value );
			?>
			<ul id="<?php echo esc_attr( $this->field['id'] ); ?>-list" class="fusion-color-palette-list">
				<?php foreach ( $value as $color ) : ?>
					<li class="fusion-color-palette-item" data-value="<?php echo esc_attr( $color ); ?>">
						<span style="background-color: <?php echo esc_attr( $color ); ?>;"></span>
					</li>
				<?php endforeach; ?>
			</ul>

			<div class="fusion-palette-colorpicker-container">
				<div class="fusion-colorpicker-container">
					<input type="text" value="" class="color-palette-color-picker-hex fusion-builder-color-picker-hex fusion-color-palette-color-picker" data-alpha="true" />
				</div>
			</div>

			<input
				id="<?php echo esc_attr( $this->field['id'] ); ?>-hidden-value-csv"
				type="hidden"
				class="color-palette-colors"
				name="<?php echo esc_attr( $this->field['name'] . $this->field['name_suffix'] ); ?>"
				value="<?php echo esc_attr( implode( '|', $value ) ); ?>"/>
			<?php
		}

		/**
		 * Enqueue admin assets.
		 *
		 * @since 2.0
		 * @return void
		 */
		public function enqueue() {
			global $fusion_library_latest_version;
			wp_enqueue_script(
				'fusionredux-field-color-palette-js',
				trailingslashit( FUSION_LIBRARY_URL ) . 'inc/redux/custom-fields/color_palette/field_color_palette.js',
				array( 'jquery', 'fusionredux-js' ),
				$fusion_library_latest_version,
				true
			);
			wp_enqueue_style(
				'fusionredux-field-color-palette-css',
				trailingslashit( FUSION_LIBRARY_URL ) . 'inc/redux/custom-fields/color_palette/field_color_palette.css',
				array(),
				$fusion_library_latest_version,
				'all'
			);
		}
	}
}
