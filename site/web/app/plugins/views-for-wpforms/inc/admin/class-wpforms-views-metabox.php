<?php

class WPForms_Views_Metabox {

	function __construct() {

		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );

	}
	/**
	 * Register meta box(es).
	 */
	function register_meta_boxes() {
		add_meta_box( 'wpforms-views-metabox', __( 'View Settings', 'wpforms-views' ),  array( $this, 'views_metabox' ), 'wpforms-views', 'normal', 'high' );
		add_meta_box( 'wpforms-views-shortcode-metabox', __( 'Shortcode', 'wpforms-views' ),  array( $this, 'shortcode_metabox' ), 'wpforms-views', 'side', 'high' );
	}

	function views_metabox( $post ) {
		if (  function_exists( 'wpforms' ) && wpforms()->pro ) {

			$forms = wpforms()->form->get();
			$view_forms = array( array( 'id' => '', 'label' => 'Select' ) );
			if ( ! empty( $forms ) ) {
				foreach ( $forms as $form ) {
					$view_forms[] = array( 'id' => $form->ID, 'label' => $form->post_title );
				}
			}
			// Add an nonce field so we can check for it later.
			wp_nonce_field( 'wpforms_views_metabox', 'wpforms_views_nonce' );
			// delete_post_meta($post->ID, 'view_settings');
			$nf_view_saved_settings = get_post_meta( $post->ID, 'view_settings', true );
			if ( empty( $nf_view_saved_settings ) ) {
				$nf_view_saved_settings = '{}';
				$form_id = '';
				if ( ! empty( $view_forms[1]['id'] ) ) {
					$form_id = $view_forms[1]['id'];
				}
			} else {
				$view_settings = json_decode( html_entity_decode( $nf_view_saved_settings ) );
				$form_id = $view_settings->formId;
			}
			$form_fields = wpforms_views_get_form_fields( $form_id );
			$wpforms_views_config = apply_filters( 'wpforms_views_config', array( 'prefix'=>'wpforms', 'addons'=>array( '' ) ) );
?>
				<script>
					var view_forms = '<?php echo  addslashes( json_encode( $view_forms ) ); ?>';
					var _view_saved_settings = '<?php echo addslashes( $nf_view_saved_settings ) ?>';
					var _view_form_fields =  '<?php echo addslashes( $form_fields ); ?>';
					var _view_config =  '<?php echo  addslashes( json_encode( $wpforms_views_config ) ); ?>';
					var wpforms_views_active_addons = [];
				</script>
			<?php do_action( 'before_wpforms_views_builder' ) ?>
		   		<div id="views-container"></div>
		   <?php do_action( 'after_wpforms_views_builder' ) ?>
		<?php
		} else {
			echo 'Please install WPForms Pro to use this plugin';
		}
	}

	/**
	 * Save meta box content.
	 *
	 * @param int     $post_id Post ID
	 */
	function save_meta_box( $post_id ) {

		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['wpforms_views_nonce'] ) ) {
			return $post_id;
		}

		$nonce = $_POST['wpforms_views_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'wpforms_views_metabox' ) ) {
			return $post_id;
		}

		/*
		 * If this is an autosave, our form has not been submitted,
		 * so we don't want to do anything.
		 */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}
		$finale_view_settings = isset( $_POST['final_view_settings'] ) ?  $_POST['final_view_settings']  : '';

		update_post_meta( $post_id, 'view_settings', $finale_view_settings );

	}

	public function shortcode_metabox( $post ) {
		echo '<code>[wpforms-views id=' . $post->ID . ']</code>';
		echo '<p style="margin-top:10px" class="description">Use this shortcode to show view anywhere on your site.</p>';
	}

	function get_setting( $setting_name ) {
		$settings = $this->settings;
		return isset( $settings[$setting_name] )?$settings[$setting_name]: '';
	}

}

new WPForms_Views_Metabox();
