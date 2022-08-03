<?php

function theme_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'avada-stylesheet' ) );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );

function intensity_custom_modal_scripts() {
    wp_enqueue_script( 'modal-custom-2-js', get_stylesheet_directory_uri() . '/js/custom-modal-2.js', array(), false, true );
}
add_action( 'wp_enqueue_scripts', 'intensity_custom_modal_scripts' );

function avada_lang_setup() {
	$lang = get_stylesheet_directory() . '/languages';
	load_child_theme_textdomain( 'Avada', $lang );
}
add_action( 'after_setup_theme', 'avada_lang_setup' );

/**
 * Custom shortcode to display WPForms form entries.
 *
 * Basic usage: [wpforms_entries_table id="FORMID"].
 *
 * Possible shortcode attributes:
 * id (required)  Form ID of which to show entries.
 * user           User ID, or "current" to default to current logged in user.
 * fields         Comma seperated list of form field IDs.
 *
 * @link https://wpforms.com/developers/how-to-display-form-entries/
 *
 * @param array $atts Shortcode attributes.
 *
 * @return string
 */
function wpf_entries_table( $atts ) {

    // Pull ID shortcode attributes.
    $atts = shortcode_atts(
        [
            'id'     => '',
            'user'   => '',
            'fields' => '',
        ],
        $atts
    );

    // Check for an ID attribute (required) and that WPForms is in fact
    // installed and activated.
    if ( empty( $atts['id'] ) || ! function_exists( 'wpforms' ) ) {
        return;
    }

    // Get the form, from the ID provided in the shortcode.
    $form = wpforms()->form->get( absint( $atts['id'] ) );

    // If the form doesn't exists, abort.
    if ( empty( $form ) ) {
        return;
    }

    // Pull and format the form data out of the form object.
    $form_data = ! empty( $form->post_content ) ? wpforms_decode( $form->post_content ) : '';

    // Check to see if we are showing all allowed fields, or only specific ones.
    $form_field_ids = ! empty( $atts['fields'] ) ? explode( ',', str_replace( ' ', '', $atts['fields'] ) ) : [];

    // Setup the form fields.
    if ( empty( $form_field_ids ) ) {
        $form_fields = $form_data['fields'];
    } else {
        $form_fields = [];
        foreach ( $form_field_ids as $field_id ) {
            if ( isset( $form_data['fields'][ $field_id ] ) ) {
                $form_fields[ $field_id ] = $form_data['fields'][ $field_id ];
            }
        }
    }

    if ( empty( $form_fields ) ) {
        return;
    }

    // Here we define what the types of form fields we do NOT want to include,
    // instead they should be ignored entirely.
    $form_fields_disallow = apply_filters( 'wpforms_frontend_entries_table_disallow', [ 'divider', 'html', 'pagebreak', 'captcha' ] );

    // Loop through all form fields and remove any field types not allowed.
    foreach ( $form_fields as $field_id => $form_field ) {
        if ( in_array( $form_field['type'], $form_fields_disallow, true ) ) {
            unset( $form_fields[ $field_id ] );
        }
    }

    $entries_args = [
        'form_id' => absint( $atts['id'] ),
    ];

    // Narrow entries by user if user_id shortcode attribute was used.
    if ( ! empty( $atts['user'] ) ) {
        if ( $atts['user'] === 'current' && is_user_logged_in() ) {
            $entries_args['user_id'] = get_current_user_id();
        } else {
            $entries_args['user_id'] = absint( $atts['user'] );
        }
    }

    // Get all entries for the form, according to arguments defined.
    $entries = wpforms()->entry->get_entries( $entries_args );

    if ( empty( $entries ) ) {
        return '<p>No entries found.</p>';
    }

    // Remove entries which will appear, by fields value, as duplicates
 	$entries = l5pp86DPvaAi_DeDupeArrayOfObjectsByProps($entries, ['fields']);

    ob_start();

    echo '<table class="wpforms-frontend-entries">';

        echo '<thead><tr>';

            // Loop through the form data so we can output form field names in
            // the table header.
            foreach ( $form_fields as $form_field ) {

                // Output the form field name/label.
                echo '<th>';
                    echo esc_html( sanitize_text_field( $form_field['label'] ) );
                echo '</th>';
            }

        echo '</tr></thead>';

        echo '<tbody>';

            // Now, loop through all the form entries.
            foreach ( $entries as $entry ) {

                echo '<tr>';

                // Entry field values are in JSON, so we need to decode.
                $entry_fields = json_decode( $entry->fields, true );

                foreach ( $form_fields as $form_field ) {

                    echo '<td>';

                        foreach ( $entry_fields as $entry_field ) {
                            if ( absint( $entry_field['id'] ) === absint( $form_field['id'] ) ) {
                                echo apply_filters( 'wpforms_html_field_value', wp_strip_all_tags( $entry_field['value'] ), $entry_field, $form_data, 'entry-frontend-table' );
                                break;
                            }
                        }

                    echo '</td>';
                }

                echo '</tr>';
            }

        echo '</tbody>';

    echo '</table>';

    $output = ob_get_clean();

    return $output;

}
add_shortcode( 'wpforms_entries_table', 'wpf_entries_table' );

/**
 * Iterates over the array of objects and looks for matching property values.
 *
 * source: https://stackoverflow.com/a/40731014/2223106
 * If a match is found the later object is removed from the array, which is returned
 * @param array $objects    The objects to iterate over
 * @param array $props      Array of the properties to dedupe on.
 *   If more than one property is specified then all properties must match for it to be deduped.
 * @return array
 */
function l5pp86DPvaAi_DeDupeArrayOfObjectsByProps($objects, $props) {
    if (empty($objects) || empty($props))
        return $objects;
    $results = array();
    foreach ($objects as $object) {
        $matched = false;
        foreach ($results as $result) {
            $matchs = 0;
            foreach ($props as $prop) {
                if ($object->$prop == $result->$prop)
                    $matchs++;
            }
            if ($matchs == count($props)) {
                $matched = true;
                break;
            }

        }
        if (!$matched)
            $results[] = $object;
    }
    return $results;
}

// For webp-converter-for-media plugin
add_filter( 'webpc_site_root', function( $path ) {
    return '/srv/www/intensity.club/current'; // your valid path to root
} );
add_filter( 'webpc_dir_name', function( $path, $directory ) {
    if ( $directory !== 'uploads' ) {
        return $path;
    }
    return '/web/app/uploads';
}, 10, 2 );
add_filter( 'webpc_dir_name', function( $path, $directory ) {
    if ( $directory !== 'webp' ) {
        return $path;
    }
    return '/web/app/uploads-webpc';
}, 10, 2 );
add_filter( 'webpc_uploads_prefix', function( $prefix ) {
    return '/';
} );
