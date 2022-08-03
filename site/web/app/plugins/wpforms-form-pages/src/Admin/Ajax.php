<?php

namespace WPFormsFormPages\Admin;

/**
 * Form Pages AJAX related functionality.
 *
 * @since 1.0.0
 */
class Ajax {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->init();
	}

	/**
	 * Initialize.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		\add_filter( 'wpforms_builder_save_form_response_data', array( $this, 'change_form_slug' ), 10, 3 );
	}

	/**
	 * Change form slug.
	 *
	 * @since 1.0.0
	 *
	 * @param array $response Form save response data.
	 * @param int   $form_id  Form ID.
	 * @param array $data     Form data.
	 *
	 * @return array
	 */
	public function change_form_slug( $response, $form_id, $data ) {

		if ( ! \current_user_can( \wpforms_get_capability_manage_options() ) ) {
			return $response;
		}

		if ( empty( $data['settings']['form_pages_enable'] ) ) {
			return $response;
		}

		$form_slug = ! empty( $data['settings']['form_pages_page_slug'] ) ? \sanitize_title( $data['settings']['form_pages_page_slug'] ) : '';

		if ( empty( $form_id ) ) {
			return $response;
		}

		if ( empty( $form_slug ) ) {
			$form = \wpforms()->form->get( $form_id );

			// Return original slug if user input is empty.
			$response['form_pages'] = array(
				'slug' => isset( $form->post_name ) ? $form->post_name : '',
				'url'  => \esc_url( \home_url( isset( $form->post_name ) ? $form->post_name : '' ) ),
			);

			return $response;
		}

		$unique_slug = $this->unique_slug( $form_slug, $form_id );

		$result = \wp_update_post(
			array(
				'ID'        => $form_id,
				'post_name' => $unique_slug,
			)
		);

		if ( $result !== $form_id ) {
			return $response;
		}

		$response['form_pages'] = array(
			'slug' => $unique_slug,
			'url'  => \esc_url( \home_url( $unique_slug ) ),
		);

		return $response;
	}

	/**
	 * Check if the slug is unique.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug    Slug to check.
	 * @param int    $post_id Post ID to exclude from the search.
	 *
	 * @return string
	 */
	public function unique_slug( $slug, $post_id ) {

		global $wpdb;

		$check_sql       = "SELECT post_name FROM $wpdb->posts WHERE post_name = %s AND ID != %d LIMIT 1";
		$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $slug, $post_id ) );

		if ( null === $post_name_check ) {
			return $slug;
		}

		$suffix = 2;

		do {
			$alt_post_name   = \_truncate_post_slug( $slug, 200 - ( \strlen( $suffix ) + 1 ) ) . "-$suffix";
			$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $alt_post_name, $post_id ) );
			$suffix ++;
		} while ( $post_name_check );

		return $alt_post_name;
	}
}
