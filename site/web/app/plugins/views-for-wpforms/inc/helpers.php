<?php

function wpforms_views_get_form_fields( $form_id ) {
	if ( empty( $form_id ) ) {
		return '{}';
	}
	$form_fields_obj = new stdClass();
	$form = wpforms()->form->get( absint( $form_id ), array( 'content_only'=> true ) );
	foreach ( $form['fields'] as $field ) {

		//var_dump( $field );
		$values = [];
		if ( ! empty( $field['choices'] ) ) {
			foreach ( $field['choices'] as $choice ) {
				// TODO: Check if values are different then label
				$values[$choice['label']] = $choice['label'];
			}
		}


		$field['label'] = isset( $field['label'] ) ? $field['label']: '';
		$form_fields_obj->{$field['id']} = (object)array(
			'id' => $field['id'],
			'label' =>$field['label'],
			'fieldType' => $field['type'],
			'values' => $values
		);
	}
	return json_encode( $form_fields_obj );

}



function wpforms_views_get_capability_manage_options() {
	return apply_filters( 'wpforms_views_full_access_cap', 'manage_options' );
}

function wpforms_views_is_admin_page($view = '' ) {

	if (
		! is_admin() ||
		empty( $_REQUEST['page'] ) ||
		strpos( $_REQUEST['page'], 'wpforms-views-settings' ) === false
	) {
		return false;
	}

	// Check against sub-level page view.
	if (
		! empty( $view ) &&
		( empty( $_REQUEST['view'] ) || $view !== $_REQUEST['view'] )
	) {
		return false;
	}
	// phpcs:enable

	return true;
}

function wpforms_views_sanitize_classes( $classes, $convert = false ) {

	$array = is_array( $classes );
	$css   = array();

	if ( ! empty( $classes ) ) {
		if ( ! $array ) {
			$classes = explode( ' ', trim( $classes ) );
		}
		foreach ( $classes as $class ) {
			if ( ! empty( $class ) ) {
				$css[] = sanitize_html_class( $class );
			}
		}
	}
	if ( $array ) {
		return $convert ? implode( ' ', $css ) : $css;
	}

	return $convert ? $css : implode( ' ', $css );
}
function wpforms_views_sanitize_key( $key = '' ) {
	return preg_replace( '/[^a-zA-Z0-9_\-\.\:\/]/', '', $key );
}

function wpforms_views_setting( $key, $default = false, $option = 'wpforms_views_settings' ) {

	$key     = wpforms_views_sanitize_key( $key );
	$options = get_option( $option, false );
	$value   = is_array( $options ) && ! empty( $options[ $key ] ) ? wp_unslash( $options[ $key ] ) : $default;

	return $value;
}

function wpforms_views_html_attributes( $id = '', $class = array(), $datas = array(), $atts = array(), $echo = false ) {

	$id    = trim( $id );
	$parts = array();

	if ( ! empty( $id ) ) {
		$id = sanitize_html_class( $id );
		if ( ! empty( $id ) ) {
			$parts[] = 'id="' . $id . '"';
		}
	}

	if ( ! empty( $class ) ) {
		$class = wpforms_views_sanitize_classes( $class, true );
		if ( ! empty( $class ) ) {
			$parts[] = 'class="' . $class . '"';
		}
	}

	if ( ! empty( $datas ) ) {
		foreach ( $datas as $data => $val ) {
			$parts[] = 'data-' . sanitize_html_class( $data ) . '="' . esc_attr( $val ) . '"';
		}
	}

	if ( ! empty( $atts ) ) {
		foreach ( $atts as $att => $val ) {
			if ( '0' == $val || ! empty( $val ) ) {
				if ( '[' === $att[0] ) {
					// Handle special case for bound attributes in AMP.
					$escaped_att = '[' . sanitize_html_class( trim( $att, '[]' ) ) . ']';
				} else {
					$escaped_att = sanitize_html_class( $att );
				}
				$parts[] = $escaped_att . '="' . esc_attr( $val ) . '"';
			}
		}
	}

	$output = implode( ' ', $parts );

	if ( $echo ) {
		echo trim( $output ); // phpcs:ignore
	} else {
		return trim( $output );
	}
}


function wpforms_views_get_user_roles_options() {
		$roles      = get_editable_roles();
		$master_cap = wpforms_views_get_capability_manage_options();
		foreach ( $roles as $role => $details ) {
			if ( $role === $master_cap || ! empty( $details['capabilities'][ $master_cap ] ) ) {
				continue;
			}
			$options[ $role ]   = $details['name'];
		}

		return $options;
	}

 function wpforms_views_get_roles_with_capabilites( $cap_to_check ) {
		$roles_with_capabilites = array();
		$roles      = get_editable_roles();
		foreach ( $roles as $role => $details ) {
			if ( array_key_exists( $cap_to_check, $details['capabilities'] ) ) {
				$roles_with_capabilites[$role] = $role;
			}
		}
		return $roles_with_capabilites;
	}



/**
 * Get submissions based on specific critera.
 *
 * @since 2.7
 * @param array   $args
 * @return array $sub_ids
 */
function wpforms_views_get_submissions( $args ) {
	global $wpdb;
	$form_id = $args['form_id'];
	$form = wpforms()->form->get( $form_id );
	$form_data = wpforms_decode( $form->post_content );

	$limit =  ! empty( $args['posts_per_page'] ) ? absint( $args['posts_per_page'] ): 25;
	$offset = ! empty( $args['offset'] ) ? absint( $args['offset'] ) : 0;

	if ( $args['view_settings']->viewType == 'datatable' ) {
		$limit = PHP_INT_MAX;
		$offset = 0;
	}

	$where = array();
	$join_sql = array();
	$join = array();
	$filter_field_ids = array();
	$order_by = array();
	$i = 1;
	$entry_table = WPForms_Views_Common::get_entry_table_name();
	$entry_fields_table = WPForms_Views_Common::get_entry_fields_table_name();

	if ( ! empty( $args['filter'] ) ) {
		foreach ( $args['filter'] as $filter ) {
			$field_id = $filter['field'];
			if ( $field_id !== 'all_fields' ) {
				$comparison_operator = $filter['compare'];
				$value = apply_filters( 'wpforms_views_filter_value', $filter['value'], $filter['field'], $args['view_id'], $form_id );
				$value  = WPForms_Views_MergeTags()->replace( $value );
				$join[] = "LEFT JOIN `$entry_fields_table` AS `m$i` ON ( `m$i`.`entry_id` = `t1`.`entry_id` AND `m$i`.`field_id` = '$field_id') ";
				if ( $comparison_operator == 'LIKE' ) {
					$value =  '%' . $value . '%';
				}
				$where[] = "(`m$i`.`field_id` = '$field_id' AND `m$i`.`value` $comparison_operator '$value')";

				// save field Id in an array to use in sort if need
				$filter_field_ids[$i] = $field_id;
			} else {

				// Search All Fields
				$search_all_fields_join = array();
				$search_all_fields_where = array();
				$x = 0;
				foreach ( $args['view_settings']->fields as $field ) {
					if ( is_numeric( $field->formFieldId ) ) {
						$value = apply_filters( 'wpforms_views_filter_value', $filter['value'], $filter['field'], $args['view_id'], $form_id );
						$value  = WPForms_Views_MergeTags()->replace( $value );

						$search_all_fields_join[] = "LEFT JOIN `$entry_fields_table` AS `a$x` ON ( `a$x`.`entry_id` = `t1`.`entry_id` AND `a$x`.`field_id` = '$field->formFieldId') ";
						$search_all_fields_where[] = "(LOCATE('$value',`a$x`.`value`) > 0)";
					}
					$x++;
				}

				if ( ! empty( $search_all_fields_join ) ) {
					$all_fields_join_sql = implode( ' ', $search_all_fields_join );
					$all_fields_where_sql = implode( ' OR ', $search_all_fields_where );

					$join[] = $all_fields_join_sql;
					$where[] = $all_fields_where_sql;
				}

			}

			$i++;
		}
	}
	//echo date('%d/%m/%Y %g:%i %A', '2021-07-02'); die;
	if ( ! empty( $args['sort_order'] ) ) {
		$k = 0;
		foreach ( $args['sort_order'] as $sort ) {
			$field_id = $sort['field_id'];
			$direction = $sort['direction'];
			if ( $field_id === 'submission_id' || $field_id === 'entryId') {
				$order_by[] = "`t1`.`entry_id` $direction";
			} else {
				$key = array_search( $field_id, $filter_field_ids );

				//  if field id exist in already created join by filter
				if ( $key ) {
					$col_name = "`m$key`.`value`";
				} else {
					//create a new join for sort
					$join[] = "LEFT JOIN `$entry_fields_table` AS `s$k` ON ( `s$k`.`entry_id` = `t1`.`entry_id` AND `s$k`.`field_id` = '$field_id') ";
					$col_name = "`s$k`.`value`";
				}
				$k++;

				// check if the field is date-time
				if ( $form_data['fields'][$field_id]['type'] === 'date-time' ) {
					$date_format = $form_data['fields'][$field_id]['date_format'];
					$time_format = $form_data['fields'][$field_id]['time_format'];
					$date_col_string = "STR_TO_DATE($col_name, ";
					switch ( $form_data['fields'][$field_id]['format'] ) {
					case'date-time':
						$date_col_string .= "'" . WPForms_Views_Common::get_mysql_date_string( $date_format );
						$date_col_string .= ' ' . WPForms_Views_Common::get_mysql_time_string( $time_format ) . "'";
						break;
					case 'date':
						$date_col_string .= "'" . WPForms_Views_Common::get_mysql_date_string( $date_format ) . "'";
						break;
					case 'time':
						$date_col_string .= "'" . WPForms_Views_Common::get_mysql_time_string( $time_format ) . "'";
						break;

					}
					$date_col_string .= ")";
					$order_by[] = "$date_col_string $direction ";
				} else if ( $form_data['fields'][$field_id]['type'] === 'number' ) {
					$order_by[] = "cast( $col_name as unsigned ) $direction ";
				}else {
					$order_by[] = "$col_name $direction ";
				}

			}
		}
	}

	// Filter by Entry Date
	if ( ! empty( $args['entry_date'] ) ) {
		$date = WPForms_Views_MergeTags()->replace( $args['entry_date']['value'] );

		switch ( $date ) {
		case'[today]':
			$date_start = wpforms_get_day_period_date( 'start_of_day', strtotime( date( "Y-m-d" ) ) );
			$date_end   = wpforms_get_day_period_date( 'end_of_day', strtotime( date( "Y-m-d" ) ) );
			break;
		default:
			$date_start = wpforms_get_day_period_date( 'start_of_day', strtotime( $date ) );
			$date_end   = wpforms_get_day_period_date( 'end_of_day', strtotime( $date ) );
			break;
		}
		$comparison_operator = $args['entry_date']['compare'];
		switch ( $comparison_operator ) {
		case'=':
			$where[] = "`t1`.date >= '{$date_start}'";
			$where[] = "`t1`.date <= '{$date_end}'";
			break;
		case'!=':
			$where[] = "`t1`.date < '{$date_start}' OR `t1`.date > '{$date_end}'";
			break;
		case'<=':
			$where[] = "`t1`.date {$comparison_operator} '{$date_end}'";
			break;
		case'>=':
			$where[] = "`t1`.date {$comparison_operator} '{$date_start}'";
			break;
		case'<':
			$where[] = "`t1`.date {$comparison_operator} '{$date_start}'";
			break;
		case'>':
			$where[] = "`t1`.date {$comparison_operator} '{$date_end}'";
			break;
		}
	}


	// Show only starred entries
	if ( ! empty( $args['starred'] ) ) {
		$where[] = "`t1`.starred IN (1)";
	}
	// Show only Viewed Entries
	if ( ! empty( $args['viewed'] ) ) {
		$where[] = "`t1`.viewed IN (1)";
	}

	// Display only Logged In user Entries
	if ( ! empty( $args['user_id'] ) ) {
		$where[] = "`t1`.user_id IN (" . $args['user_id'] . ")";
	}

	// Filter by Submission ID
	if ( ! empty( $args['seq_num'] ) ) {
		$comparison_operator = $args['seq_num']['compare'];
		$value = $args['seq_num']['value'];
		$where[] = "`t1`.entry_id {$comparison_operator} '{$value}'";
	}

	if ( ! empty( $order_by ) ) {
		$order_by_sql = implode( ', ', $order_by );
	}else {
		$order_by_sql = "`t1`.`entry_id` DESC";
	}

	$join = apply_filters( 'wpforms_view_query_joins', $join, $args );
	if ( ! empty( $join ) ) {
		$join_sql = implode( ' ', $join );
	}else {
		$join_sql = '';
	}

	$where = apply_filters( 'wpforms_view_query_where', $where, $args );
	if ( ! empty( $where ) ) {
		$where_sql = implode( ' AND ', $where );
	}else {
		$where_sql = '1=1';
	}


	$sql_query = "SELECT `t1`.* FROM `$entry_table` AS `t1` $join_sql WHERE `t1`.`form_id` IN ($form_id) AND( $where_sql ) GROUP BY `t1`.`entry_id` ORDER BY $order_by_sql ";
	$results = $wpdb->get_results( " {$sql_query} LIMIT {$offset},{$limit} " );

	// Total entries count
	$sql_query_for_total_rows = "SELECT `t1`.* FROM `$entry_table` AS `t1` $join_sql WHERE `t1`.`form_id` IN ($form_id) AND( $where_sql ) GROUP BY `t1`.`entry_id` ORDER BY `t1`.`entry_id` DESC";
	$total_rows_results = $wpdb->get_results( "{$sql_query_for_total_rows}" );

	// echo '<pre>';
	//echo " {$sql_query} LIMIT {$offset},{$limit} ";
	//  print_r($args);
	// print_r( $results ); die;
	$submissions['total_count'] = count( $total_rows_results );
	$submissions['subs'] = $results;
	return $submissions;

}
