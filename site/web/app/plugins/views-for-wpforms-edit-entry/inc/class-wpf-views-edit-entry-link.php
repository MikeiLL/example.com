<?php

class WPF_Views_Edit_Entry_Link {

	public function __construct() {
		add_filter( 'wpfviews_widget_html', array( $this, 'edit_entry_link' ), 10, 4 );
	}

	function edit_entry_link( $widgets_html, $field, $view_settings, $entry ) {

		if ( ! empty( $entry ) && $this->user_has_permission( $entry->user_id, $view_settings  ) ) {

			if ( $field->formFieldId == 'editEntry'  && isset( $view_settings->viewSettings->editEntries ) ) {
				$edit_page_url = $view_settings->viewSettings->editEntries->editPage;
				$edit_page_url = add_query_arg( 'edit_wpfentry', 'true', $edit_page_url );
				$edit_page_url = add_query_arg( 'wpfentry_id', $entry->entry_id, $edit_page_url );
				$link_text = isset( $field->fieldSettings->linkText ) ? $field->fieldSettings->linkText : 'Edit Entry';
				$widgets_html = '<a href="' . esc_url_raw( $edit_page_url ) . '" class="' . $field->fieldSettings->customClass . '">' . $link_text . '</a>';
			} else if ( $field->formFieldId == 'deleteEntry' ) {
				$widgets_html = '<a class="' . $field->fieldSettings->customClass . '">' . $field->fieldSettings->linkText . '</a>';
			}
		}
		return $widgets_html;
	}

	function user_has_permission( $user_id, $view_settings ) {
		$logged_in_user_id = get_current_user_id();
		if ( ! empty( $logged_in_user_id ) ) {
			$admin_allowed = ! empty( $view_settings->viewSettings->editEntries->allowAdminToEdit ) ? $view_settings->viewSettings->editEntries->allowAdminToEdit: false;
			//var_dump($logged_in_user_id); die;
			if ( ( $logged_in_user_id == $user_id ) || ( WPForms_Views_Roles_Capabilities::current_user_can( 'wpforms_views_edit_entries' )  ) ) {
				return true;
			}
		}
		return false;
	}
}

new WPF_Views_Edit_Entry_Link();
