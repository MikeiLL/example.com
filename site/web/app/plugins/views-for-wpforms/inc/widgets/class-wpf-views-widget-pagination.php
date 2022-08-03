<?php
class WPF_Views_Widget_Pagination extends WPF_Views_Widget {

	public $widget_type = 'pagination' ;


	public function get_widget_html( $html, $_view_field, $_view_settings, $view_Obj ) {

		$entries_count = $view_Obj->submissions_count;
		$per_page = $_view_settings->viewSettings->multipleentries->perPage;
		$pages = new WPForms_View_Paginator( $per_page, 'pagenum' );
		$pages->set_total( $entries_count ); //or a number of records
		$current_url = site_url( remove_query_arg( array( 'pagenum', 'view_id' ) ) );
		$current_url = add_query_arg( 'view_id', $view_Obj->view_id, $current_url );

		return $pages->page_links( $current_url . '&' );
	}
}
new WPF_Views_Widget_Pagination();
