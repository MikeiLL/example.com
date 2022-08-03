<?php
/**
 * Handles the data table creation for form entries.
 *
 * @package fusion-builder
 * @since 3.1
 */

// WP_List_Table is not loaded automatically so we need to load it in our application.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Create a new table class that will extend the WP_List_Table.
 */
class Fusion_Form_List_Table extends WP_List_Table {

	/**
	 * Form ID.
	 *
	 * @since 3.1
	 * @var array
	 */
	public $form_id = [];

	/**
	 * Data columns.
	 *
	 * @since 3.1
	 * @var array
	 */
	public $columns = [];

	/**
	 * Form Fields.
	 *
	 * @since 3.1
	 * @var array
	 */
	public $form_fields = [];

	/**
	 * Form sumissions data.
	 *
	 * @since 3.1
	 * @var array
	 */
	public $form_submissions = [];

	/**
	 * No entries text.
	 *
	 * @since 3.1
	 * @var string
	 */
	public $no_entries_text;

	/**
	 * Class constructor.
	 *
	 * @since 3.1
	 * @access public
	 * @param array $form_id Form id.
	 */
	public function __construct( $form_id ) {
		parent::__construct();
		$this->form_id     = $form_id;
		$fusion_forms      = new Fusion_Form_DB_Forms();
		$this->form_fields = $fusion_forms->get_form_fields( $this->form_id );

		foreach ( array_slice( $this->form_fields, 0, 7 ) as $key => $field_object ) {

			// Use field name if label is empty, for example hidden fields.
			if ( isset( $field_object->field_label ) && '' !== $field_object->field_label ) {
				array_push( $this->columns, $field_object->field_label );
			} else {
				array_push( $this->columns, $field_object->field_name );
			}
		}

		// Add actions column at the end.
		if ( 0 !== count( $this->form_fields ) ) {
			array_push( $this->columns, 'Actions' );
		}

		$this->no_entries_text = __( 'No form entries submitted yet.', 'fusion-builder' );
	}

	/**
	 * Prepare the items for the table to process.
	 *
	 * @since 3.1
	 * @access public
	 * @param int $per_page     number of items per page.
	 * @param int $current_page Current page.
	 * @return void
	 */
	public function prepare_items( $per_page = 15, $current_page = 0 ) {
		$submissions = new Fusion_Form_DB_Submissions();
		$columns     = $this->get_columns();

		if ( 0 === $current_page ) {
			$current_page = $this->get_pagenum();
		}

		$submission_args = [
			'where'    => [ 'form_id' => (int) $this->form_id ],
			'order by' => 'id DESC',
		];

		// If we want small section, limit query.
		if ( 1 < $per_page ) {
			$submission_args['limit']  = $per_page;
			$submission_args['offset'] = absint( ( $current_page - 1 ) * $per_page );
		}

		// Get submissions.
		$this->form_submissions = $submissions->get( $submission_args );
		$data                   = $this->table_data();
		$hidden                 = $this->get_hidden_columns();
		$sortable               = $this->get_sortable_columns();
		// Check the form submission type.
		$fusion_forms = new Fusion_Form_DB_Forms();
		$forms        = $fusion_forms->get_formatted();
		// Count number of entries.
		$total_items = count(
			$submissions->get(
				[
					'where' => [ 'form_id' => (int) $this->form_id ],
				]
			)
		);

		$this->set_pagination_args(
			[
				'total_items' => $total_items,
				'per_page'    => $per_page,
			]
		);

		$this->_column_headers = [ $columns, $hidden, $sortable ];
		$this->items           = $data;
	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table.
	 *
	 * @since 3.1
	 * @access public
	 * @return array
	 */
	public function get_columns() {
		return $this->columns;
	}

	/**
	 * Define which columns are hidden
	 *
	 * @since 3.1
	 * @access public
	 * @return array
	 */
	public function get_hidden_columns() {
		return [];
	}

	/**
	 * Define the sortable columns
	 *
	 * @since 3.1
	 * @access public
	 * @return array
	 */
	public function get_sortable_columns() {
		return [];
	}

	/**
	 * Get the table data.
	 *
	 * @since 3.1
	 * @access public
	 * @return array
	 */
	private function table_data() {

		$data           = [];
		$form_entries   = [];
		$fusion_entries = new Fusion_Form_DB_Entries();

		foreach ( $this->form_submissions as $submission ) {
			$form_entries[ $submission->id ] = $fusion_entries->get(
				[
					'where' => [ 'submission_id' => $submission->id ],
				]
			);
		}

		foreach ( $form_entries as $key => $entries ) {

			$entries = (array) $entries;

			foreach ( $entries as $entry ) {

				$entry = (array) $entry;
				foreach ( $this->form_fields as $field ) {
					if ( isset( $entry['field_id'] ) && $entry['field_id'] === $field->id ) {
						$field_label                  = '' !== $field->field_label ? $field->field_label : $field->field_name;
						$field_data                   = $entry['value'];
						$data[ $key ][ $field_label ] = $field_data;
						break;
					}
				}
			}

			if ( ! isset( $data[ $key ] ) ) {
				$data[ $key ] = [];
			}

			// Add actions column at the end.
			$data[ $key ]['Actions'] = $this->column_actions( $data[ $key ], $key );
		}

		return $data;
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @since 3.1
	 * @access public
	 * @param  array  $item        Data.
	 * @param  string $column_id - Current column id.
	 * @return string
	 */
	public function column_default( $item, $column_id ) {
		$column_name = $this->columns[ $column_id ];
		$value       = isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';

		return $value;
	}

	/**
	 * Display button with link to display all form fields in popup.
	 *
	 * @since 3.1
	 * @access public
	 * @param  array $entry Singhe entry data.
	 * @param  int   $key   Singhe entry key.
	 * @return string
	 */
	public function column_actions( $entry, $key ) {

		$submissions_obj = new Fusion_Form_DB_Submissions();
		$html            = '<div class="row-actions fusion-form-entries">';
		$html           .= '<span class"view_details"><a href="#" onclick="jQuery(\'.single-entry-' . $key . '\').toggleClass( \'hidden\' ); return false;">' . __( 'View All Details', 'fusion-builder' ) . '</a> | </span>';
		$html           .= '<span class="trash"><a href="#" class="fusion-remove-form-entry" data-key="' . $key . '">' . __( 'Delete', 'fusion-builder' ) . '</a></span>';
		$html           .= '</div>';
		$html           .= '<div class="single-entry-' . $key . ' fusion-form-single-entry-popup-overlay hidden" onclick="jQuery(\'.single-entry-' . $key . '\').toggleClass( \'hidden\' ); return false;"></div>';
		$html           .= '<div class="single-entry-' . $key . ' fusion-form-single-entry-popup hidden">';
		$html           .= '<a href="#" onclick="jQuery(\'.single-entry-' . $key . '\').toggleClass( \'hidden\' ); return false;" class="single-entry-' . $key . ' dashicons dashicons-no-alt fusion-form-single-entry-popup-close hidden"></a>';
		$html           .= '<div class="fusion-form-single-entry-popup-inner">';

		foreach ( $entry as $label => $value ) {
			$html .= '<div class="fusion-form-single-entry">';
			$html .= '<div class="fusion-form-single-entry-label">';
			$html .= $label;
			$html .= '</div>';
			$html .= '<div class="fusion-form-single-entry-value">';
			$html .= $value;
			$html .= '</div>';
			$html .= '</div>';
		}

		$submissions = $submissions_obj->get(
			[
				'where' => [ 'id' => (int) $key ],
			]
		);

		if ( isset( $submissions[0] ) ) {

			// remove form DB ID.
			unset( $submissions[0]->form_id );

			// remove is_read (we don't use it for now).
			unset( $submissions[0]->is_read );

			// remove serialized data (we don't use it for now).
			unset( $submissions[0]->data );
		}

		$html .= '<div class="fusion-form-single-entry fusion-form-single-entry-submission-meta">';
		$html .= '<h3>' . __( 'Additional Information', 'fusion-builder' ) . '</h3>';
		$html .= '</div>';

		foreach ( $submissions[0] as $label => $value ) {
			$label = 'id' === $label ? __( 'Submission Id', 'fusion-builder' ) : $label;

			$html .= '<div class="fusion-form-single-entry">';
			$html .= '<div class="fusion-form-single-entry-label">';
			$html .= ucwords( str_replace( '_', ' ', $label ) );
			$html .= '</div>';
			$html .= '<div class="fusion-form-single-entry-value">';
			$html .= $value;
			$html .= '</div>';
			$html .= '</div>';
		}

		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Display custom text if no form entries are submitted.
	 *
	 * @since 3.1
	 * @access public
	 * @return void
	 */
	public function no_items() {
		echo esc_html( $this->no_entries_text );
	}
}
