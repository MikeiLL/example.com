<?php
class WPForms_Views_Shortcode {
	public $view_id;
	public $entries_count;
	public $table_heading_added;
	public $form;
	public $form_data;
	public $form_fields;
	public $seq_no = 1;
	function __construct() {
		add_shortcode( 'wpforms-views', array( $this, 'shortcode' ), 10 );
	}

	public function shortcode( $atts ) {
		$this->seq_no = 1;
		$atts = shortcode_atts(
			array(
				'id' => '',
			), $atts );

		if ( empty( $atts['id'] ) ) {
			return;
		}
		$view_id = $atts['id'];
		$this->view_id = $view_id;
		$this->table_heading_added = false;
		$view_settings_json = get_post_meta( $view_id, 'view_settings', true );
		if ( empty( $view_settings_json ) ) {
			return;
		}

		$view_settings =  json_decode( $view_settings_json );
		$view_type = $view_settings->viewType;

		//check if single entry
		$single_entry_id = get_query_var( 'entry' );
		if ( $single_entry_id ) {
			$view  = apply_filters( 'wpf_views_single_entry_content', '', $single_entry_id, $view_settings );
		}else {
			$method_name = 'get_view';
			$view  = $this->$method_name( $view_settings );
		}

		return $view;

	}

	function get_view( $view_settings ) {
		global $wpdb;
		$view_type        = $view_settings->viewType;
		$before_loop_rows = $view_settings->sections->beforeloop->rows;
		$loop_rows = $view_settings->sections->loop->rows;
		$after_loop_rows = $view_settings->sections->afterloop->rows;
		$per_page = $view_settings->viewSettings->multipleentries->perPage;
		$loggedin = isset( $view_settings->viewSettings->multipleentries->loggedin )?$view_settings->viewSettings->multipleentries->loggedin:false;
		$hideDataOnLoad = isset( $view_settings->viewSettings->multipleentries->hideDataOnLoad )?$view_settings->viewSettings->multipleentries->hideDataOnLoad:false;
		$sort_order = $view_settings->viewSettings->sort;
		$filter = $view_settings->viewSettings->filter;

		// Get the form,
		$this->form = wpforms()->form->get( absint( $view_settings->formId ) );
		$this->form_fields = wpforms_get_form_fields( $view_settings->formId );
		// If the form doesn't exists, abort.
		if ( empty( $this->form ) ) {
			return;
		}

		// Pull and format the form data out of the form object.
		$this->form_data = ! empty( $this->form->post_content ) ? wpforms_decode( $this->form->post_content ) : '';
		$viewed = isset( $view_settings->viewSettings->multipleentries->viewed )? $view_settings->viewSettings->multipleentries->viewed:false;
		$starred = isset( $view_settings->viewSettings->multipleentries->starred )?$view_settings->viewSettings->multipleentries->starred:false;
		$args = array(
			'form_id' => $view_settings->formId,
			'posts_per_page' =>$per_page,
			'view_id' => $this->view_id,
			'view_settings'=>$view_settings,
			'viewed' => $viewed,
			'starred' => $starred
		);
		if ( $loggedin ) {
			if ( is_user_logged_in() ) {
				$args['user_id'] = get_current_user_id();
			} else {
				$args['user_id'] = PHP_INT_MAX;
			}
		}

		// Filter params
		if ( ! empty( $filter ) ) {

			foreach ( $filter as $filterrow ) {
				switch ( $filterrow->field ) {
				case 'submission_id':
				case 'entryId':
					$args['seq_num']['compare'] = $filterrow->is;
					$args['seq_num']['value'] = $filterrow->value;
					break;
				case 'submission_date':
				case 'entryDate':
					$args['entry_date']['compare'] = $filterrow->is;
					$args['entry_date']['value'] = $filterrow->value;
					break;
				default:
					$args['filter'][] = array(
						'field' => $filterrow->field,
						'compare' => $filterrow->is,
						'value' => $filterrow->value,
					);
				}
			}

		}

		// Search Query
		if ( ! empty( $_GET['search_fields'] ) ) {
			foreach ( $_GET['search_fields'] as $field_id => $field_value ) {
				$form_fields = wpforms_get_form_fields( $view_settings->formId );
				$form_field_type = isset( $form_fields[$field_id]['type'] )?$form_fields[$field_id]['type']: '';
				if ( $field_value !== '' ) {
					if ( $field_id === 'submission_id' || $field_id === 'entryId' ) {
						$args['seq_num']['compare'] = '=';
						$args['seq_num']['value'] = $field_value;
					}else if ( $form_field_type === 'textarea' || $form_field_type === 'checkbox' || $form_field_type === 'name' ) {
						$args['filter'][] = array(
							'field' => $field_id,
							'compare' => 'LIKE',
							'value' => $field_value,
						);
					}else {
						$args['filter'][] = array(
							'field' => $field_id,
							'compare' => '=',
							'value' => $field_value,
						);

					}
				}
			}
		}
		// echo '<pre>';
		// print_r($args); die;

		// OrderBy Params
		if ( ! empty( $sort_order ) ) {
			foreach ( $sort_order as $sortrrow ) {
				if ( isset ( $sortrrow->field ) ) {
					$args['sort_order'][] = array(
						'field_id' => $sortrrow->field,
						'direction' => $sortrrow->value,
					);
				}
			}

		}

		// pagination
		if ( ! empty( $_GET['pagenum'] ) && ! empty( $_GET['view_id'] ) && ( $this->view_id === $_GET['view_id'] ) ) {
			$page_no = sanitize_text_field( $_GET['pagenum'] );
			$offset = $per_page * ( $page_no-1 );
			$args['offset'] = $offset;
			$this->seq_no = $offset+1;
		}


		// Get Entries
		$args = apply_filters( 'wpf_views_get_submissions_args', $args );

		if ( ! $hideDataOnLoad || $this->is_search_query() ) {
			$entries_data = wpforms_views_get_submissions( $args );
			$this->submissions_count = $entries_data['total_count'];
			$entries = $entries_data['subs'];
		} else {
			$this->submissions_count = 0;
			$entries = false;
		}

		$view_content = '<div class="wpforms-view wpforms-view-type-' . $view_type . ' wpforms-view-' . $this->view_id . '">';

		if ( ! empty ( $before_loop_rows ) ) {
			$view_content .= $this->get_sections_content( 'beforeloop', $view_settings, $entries );
		}

		if ( ! empty( $loop_rows ) && ( ! $hideDataOnLoad || $this->is_search_query() ) ) {
			if ( $view_type == 'table' || $view_type == 'datatable' ) {
				$view_content .= $this->get_table_content( 'loop', $view_settings, $entries );
			} else {
				$view_content .= $this->get_sections_content( 'loop', $view_settings, $entries );
			}
		} else {
			$view_content .= '<div class="views-no-records-cnt">No records found.</div>';
		}

		if ( ! empty ( $after_loop_rows ) ) {
			$view_content .= $this->get_sections_content( 'afterloop', $view_settings, $entries );
		}


		$view_content .= '</div>';

		$view_content = apply_filters( 'wpforms_views_view_content', $view_content, $this->view_id,  $view_settings );

		ob_start();
		echo $view_content;
		do_action( 'wpforms_views_after', $this->view_id, $view_content, $view_settings );
		$content = ob_get_contents();
		ob_end_clean();

		return $content;

	}


	function get_sections_content( $section_type, $view_settings, $entries ) {
		$content = '';
		$section_rows = $view_settings->sections->{$section_type}->rows;
		if ( $section_type == 'loop' ) {
			foreach ( $entries as $entry ) {
				foreach ( $section_rows as $row_id ) {
					$content .= $this->get_grid_row_html( $row_id, $view_settings, $entry );
					$this->seq_no++;
				}
			}
		} else {
			foreach ( $section_rows as $row_id ) {
				$content .= $this->get_grid_row_html( $row_id, $view_settings );
			}
		}
		return $content;
	}



	function get_table_content( $section_type, $view_settings, $entries ) {
		$content = '';
		$section_rows = $view_settings->sections->{$section_type}->rows;
		$table_classes = apply_filters( 'wpforms_view_table_classes', 'pure-table pure-table-bordered', $this->view_id, $view_settings );
		$content = apply_filters( 'wpf_views_before_table_content', '', $this->view_id, $view_settings );
		$content .= ' <div class="wpf-views-cont wpf-views-' . $this->view_id . '-cont"><table class="wpforms-views-table wpforms-view-' . $this->view_id . '-table ' . $table_classes . '">';
		$content .= '<thead>';
		$row_count = 1;
		foreach ( $entries as $entry ) {
			$content .= '<tr>';
			foreach ( $section_rows as $row_id ) {
				$content .= $this->get_table_row_html( $row_id, $view_settings, $entry );
				$this->seq_no++;
			}
			$content .= '</tr>';
			$content = apply_filters( 'wpf_views_after_table_row', $content, $row_count, $entry, $view_settings );


			$row_count++;
		}
		$content .= '</tbody></table></div>';

		return $content;
	}

	function get_table_row_html( $row_id, $view_settings, $entry = false ) {
		$row_content = '';
		$row_columns = $view_settings->rows->{$row_id}->cols;
		foreach ( $row_columns as $column_id ) {
			$row_content .= $this->get_table_column_html( $column_id, $view_settings, $entry );
		}
		//$row_content .= '</table>'; // row ends
		return $row_content;
	}

	function get_table_column_html( $column_id, $view_settings, $entry ) {
		$column_size = $view_settings->columns->{$column_id}->size;
		$column_fields = $view_settings->columns->{$column_id}->fields;

		$column_content = '';

		if ( ! ( $this->table_heading_added ) ) {

			foreach ( $column_fields as $field_id ) {
				$column_content .= $this->get_table_headers( $field_id, $view_settings, $entry );
			}
			$this->table_heading_added = true;
			$column_content .= '</tr></thead><tbody><tr>';
		}
		foreach ( $column_fields as $field_id ) {

			$column_content .= $this->get_field_html( $field_id, $view_settings,  $entry );

		}

		return $column_content;
	}



	function get_grid_row_html( $row_id, $view_settings, $entry = false ) {
		$row_columns = $view_settings->rows->{$row_id}->cols;

		$row_content = '<div class="pure-g wpforms-view-row">';
		foreach ( $row_columns as $column_id ) {
			$row_content .= $this->get_grid_column_html( $column_id, $view_settings, $entry );
		}
		$row_content .= '</div>'; // row ends
		return $row_content;
	}

	function get_grid_column_html( $column_id, $view_settings, $entry ) {
		$column_size = $view_settings->columns->{$column_id}->size;
		$column_fields = $view_settings->columns->{$column_id}->fields;

		$column_content = '<div class=" wpforms-view-col pure-u-1 pure-u-md-' . $column_size . '">';


		foreach ( $column_fields as $field_id ) {

			$column_content .= $this->get_field_html( $field_id, $view_settings,  $entry );

		}
		$column_content .= '</div>'; // column ends
		return $column_content;
	}

	function get_field_html( $field_id, $view_settings, $entry ) {
		$field = $view_settings->fields->{$field_id};
		$form_field_id = $field->formFieldId;
		$fieldSettings = $field->fieldSettings;
		$label = $fieldSettings->useCustomLabel ? $fieldSettings->label : $field->label;
		$class = $fieldSettings->customClass;
		$view_type = $view_settings->viewType;
		$field_html = '';
		$entry_fields = array();
		if ( $entry ) {
			// Entry field values are in JSON, so we need to decode.
			$entry_fields = json_decode( $entry->fields, true );
		}

		// Return if Hide Empty Fields is activated & field value is empty
		if ( $this->is_form_field( $entry_fields, $form_field_id )  && $view_type === 'list' && empty( $entry_fields[$form_field_id ]['value'] ) ) {
			if ( ! empty( $view_settings->viewSettings->multipleentries->hideEmptyFields ) ) {
				return '';
			}
		}

		if ( $view_type === 'table' || $view_type === 'datatable' ) {
			$field_html .= '<td  class="col-field-value">';
		}
		$field_html .= '<div class="wpforms-view-field-cont  field-' . $form_field_id . ' ' . $class . '">';

		// check if it's a loop field
		if ( $this->is_form_field( $entry_fields, $form_field_id ) ||  $this->is_loop_field( $form_field_id )) {

			$form_field_type = isset( $entry_fields[$form_field_id ] ) ? $entry_fields[$form_field_id ]['type']: $form_field_id;
			//  if view type is table then don't send label
			if (  $view_type !== 'table' && $view_type !== 'datatable' ) {
				if ( ! empty( $label ) ) {
					$field_html .= '<div class="wpforms-view-field-label">' . $label . '</div>';
				}
			}

			$field_html .= '<div class="wpforms-view-field-value wpforms-view-field-type-' . $form_field_type . '-value">';
			if ( $form_field_type == 'date-time' ) {
				if ( ! empty( $field_value ) ) {
					$sortDate = date( "Y-m-d h:i:s", strtotime( $field_value ) );
					$field_html = str_replace( 'class="col-field-value"', 'data-order="' . $sortDate . '"', $field_html );
				}
			}else if($form_field_type == 'entryDate'){
				$field_html = str_replace( 'class="col-field-value"', 'data-order="' . $entry->date . '"', $field_html );
			}
			$field_value = apply_filters( "wpf-views/field-value", '', $field_id, $entry, $view_settings, $this );

			$field_value = apply_filters( "wpf-views/{$form_field_type}-value", $field_value, $field_id, $entry, $view_settings, $this );

			//$field_html .=apply_filters( 'wpf-views-field-display-value', $display_value, $field_value, $view_settings, $entry, $form_field_id, $this->form_fields, $this->form_data, $this->view_id );
			$field_html .= $field_value;


			//$field_html .= apply_filters( 'wpf-views-field-display-value', $display_value, $field_value, $view_settings, $entry, $form_field_id, $this->form_fields, $this->form_data, $this->view_id );
			$field_html .= '</div>';
		} else {

			$widgets_html = apply_filters( "wpf-views/{$field->formFieldId}-html", '', $field, $view_settings, $this  );

			$field_html .= apply_filters( 'wpfviews_widget_html', $widgets_html, $field, $view_settings, $entry );
		}

		$field_html .= '</div>';
		if ( $view_type === 'table' || $view_type == 'datatable' ) {
			$field_html .= '</td>';
		}


		return $field_html;
	}

	function get_table_headers( $field_id, $view_settings, $entry ) {
		$field = $view_settings->fields->{$field_id};
		$fieldSettings = $field->fieldSettings;
		$label = $fieldSettings->useCustomLabel ? $fieldSettings->label : $field->label;
		return '<th>' . $label . '</th>';
	}


	function is_search_query() {
		if ( isset( $_GET['search_fields'] ) ) {
			$search_fields = implode( " ", $_GET['search_fields'] );
			if ( empty( trim( $search_fields ) ) ) {
				return false;
			}
			return true;
		}
		return false;
	}

	function is_form_field( $entry_fields, $form_field_id ) {
		if ( ! empty( $entry_fields ) && is_array( $entry_fields ) && is_numeric( $form_field_id ) && isset( $entry_fields[$form_field_id ] ) ) {
			return true;
		}
		return false;
	}
	function is_loop_field( $form_field_id ) {
		$loop_fields = array( 'html','entryId', 'entryDate', 'sequenceNumber' );
		$loop_fields = apply_filters('wpf-views/loop-fields', $loop_fields);
		if ( in_array( $form_field_id,  $loop_fields) ) {
			return true;
		}
		return false;
	}



}
new WPForms_Views_Shortcode();
