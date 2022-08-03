<?php

class WPForms_Views_DataTables_Display {

	public function __construct() {

		add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ), 100 );
		add_filter( 'wpforms_view_table_classes', array( $this, 'add_datatable_class' ),10, 3);
		add_filter( 'wpforms_views_view_content', array( $this, 'maybe_show_datables' ), 10, 3 ) ;
		add_filter( 'wpfviews_widget_html', array( $this, 'datatables_widget_html' ), 10, 4 );
	}
	function add_scripts() {
		wp_enqueue_style( 'datatable', WPFORMS_VIEWS_DATATABLES_URL . '/assets/datatables.min.css', false );
		wp_register_script( 'datatable', WPFORMS_VIEWS_DATATABLES_URL . '/assets/datatables.min.js', array( 'jquery' ) );
	}

	function add_datatable_class( $classes,$view_id, $view_settings ) {
		if ( $view_settings->viewType == 'datatable' ) {
			wp_enqueue_script( 'datatable');
			$classes = str_replace( 'pure-table pure-table-bordered', ' ', $classes );
			$classes .= ' display';
		}

		return $classes;
	}

	function maybe_show_datables( $view_content, $view_id, $view_settings ) {

		if ( $view_settings->viewType == 'datatable' ) {
			$per_page = $view_settings->viewSettings->multipleentries->perPage;
			$buttons='false';
			foreach ( $view_settings->fields as $view_field ) {
				if ( $view_field->formFieldId == 'datatableButtons' ) {
					if ( ! empty( $view_field->fieldSettings->datatableButtons ) ) {
						$datatableButtons  = $view_field->fieldSettings->datatableButtons;
						$pdf_key = array_search( 'pdf', $datatableButtons );

						if ( $pdf_key ) {
							$orientation =  !empty( $view_settings->viewSettings->dataTable->pdfOrientation) ?$view_settings->viewSettings->dataTable->pdfOrientation:'portrait' ;
							$page_size =  !empty( $view_settings->viewSettings->dataTable->pdfPageSize) ?$view_settings->viewSettings->dataTable->pdfPageSize:'A4' ;
							$pdf_btn_obj = new stdClass;
							$pdf_btn_obj->extend = 'pdf';
							$pdf_btn_obj->orientation = $orientation;
							$pdf_btn_obj->pageSize = $page_size;
							$datatableButtons[$pdf_key] = $pdf_btn_obj;

						}
						$buttons = json_encode( $datatableButtons );

					}else {
						$buttons = "['copy', 'csv', 'excel', 'pdf', 'print']";
					}
				}
			}

			$responsive = !empty( $view_settings->viewSettings->dataTable->responsive) ?'true':'false' ;
			ob_start(); ?>
				<script>
				document.addEventListener("DOMContentLoaded", function(event) {
					(function( $ ){
					$(document).ready( function () {

						var dtOptions = 	{
								"paging":   true,
								"ordering": true,
								"order": [],
								"info":     true,
								"searching":     false,
								"lengthChange": false,
								"responsive": <?php echo $responsive; ?>,
								"pageLength": <?php echo $per_page; ?>,
								 "buttons": <?php echo $buttons; ?>
								}
						var wpfViews_dataTable = $(".<?php echo 'wpforms-view-' . $view_id . '-table' ?>").DataTable(
							dtOptions
						);
						$('.field-paginationInfo').html($('.dataTables_info')).addClass('dataTables_wrapper')
						$('.field-pagination').html($('.dataTables_paginate')).addClass('dataTables_wrapper')
						wpfViews_dataTable.buttons().container().appendTo( $('.datatable-btns' ) );
						} );
					})(jQuery);
				});
				</script>
			<?php
			$view_content .= ob_get_contents();
			ob_end_clean();
		}
		return $view_content;

	}


	function datatables_widget_html( $widgets_html, $field, $view_settings, $sub ){
		if( $field->formFieldId == 'datatableButtons'){
			$widgets_html ='<div class=" datatable-btns '.$field->fieldSettings->customClass.'"></div>';
		}

		return $widgets_html;
	}
}
new WPForms_Views_DataTables_Display();