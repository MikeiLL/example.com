<?php
class WPF_Views_Widget_PaginationInfo extends WPF_Views_Widget {

	public $widget_type = 'paginationInfo' ;


	public function get_widget_html( $html, $_view_field, $_view_settings, $view_Obj ) {

		$page_no = empty(  $_GET['pagenum'] ) ? 1 : sanitize_text_field( $_GET['pagenum'] );
		$entries_count = $view_Obj->submissions_count;
		if ( $entries_count <= 0 ) return;
		$per_page = $_view_settings->viewSettings->multipleentries->perPage;
		$from = ( $page_no-1 ) * $per_page;
		$of = $per_page * $page_no;
		if ( $of > $entries_count ) {
			$of = $entries_count;
		}
		if ( $from == 0 ) {
			$from = 1;
		}

		return 'Displaying ' . $from . ' - ' . $of . ' of ' . $entries_count ;
	}
}
new WPF_Views_Widget_PaginationInfo();
