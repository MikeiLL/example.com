/* globals wpforms_admin_builder_form_pages, WPFormsBuilder, wpf */

'use strict';

/**
 * WPForms Builder Form Pages function.
 *
 * @since 1.0.0
 * @package WPFormsFormPages
 */
var WPFormsBuilderFormPages = window.WPFormsBuilderFormPages || ( function( document, window, $ ) {

	/**
	 * Elements.
	 *
	 * @since 1.0.0
	 *
	 * @type {object}
	 */
	var $el = {
		toggleSettingsCheckbox: $( '#wpforms-panel-field-settings-form_pages_enable' ),
		previewFormBtn        : $( '#wpforms-form-pages-preview-form-page' ),
		slug                  : {
			textField: $( '#wpforms-panel-field-settings-form_pages_page_slug' ),
			editBtn  : $( '.wpforms-form-pages-page-slug-edit' ),
			viewBtn  : $( '.wpforms-form-pages-page-slug-view' ),
			cancelBtn: $( '.wpforms-form-pages-page-slug-cancel' ),
		},
		logo                  : {
			previewContainer: $( '.wpforms-form-pages-custom-logo-container' ),
			textField       : $( '#wpforms-panel-field-settings-form_pages_custom_logo' ),
			addBtn          : $( '.wpforms-form-pages-custom-logo-upload' ),
			deleleBtn       : $( '.wpforms-form-pages-custom-logo-delete' ),
		},
	};

	/**
	 * Public functions and properties.
	 *
	 * @since 1.0.0
	 *
	 * @type {object}
	 */
	var app = {

		/**
		 * All settings / constants.
		 *
		 * @since 1.2.1
		 */
		settings: {
			minicolorsChangeDelay: 750,
			minicolorsInputEventRunLength: 6,
		},

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		init: function() {

			$( app.ready );
		},

		/**
		 * Document ready.
		 *
		 * @since 1.0.0
		 */
		ready: function() {

			app.conditionals();
			app.events();
			app.actions();
		},

		/**
		 * Register and load conditionals.
		 *
		 * @since 1.0.0
		 */
		conditionals: function() {

			if ( typeof $.fn.conditions === 'undefined' ) {
				return;
			}

			$el.toggleSettingsCheckbox.conditions( {
				conditions: {
					element : '#wpforms-panel-field-settings-form_pages_enable',
					type    : 'checked',
					operator: 'is',
				},
				actions   : {
					if  : {
						element: '#wpforms-form-pages-content-block,#wpforms-form-pages-preview-form-page',
						action : 'show',
					},
					else: {
						element: '#wpforms-form-pages-content-block,#wpforms-form-pages-preview-form-page',
						action : 'hide',
					},
				},
				effect    : 'appear',
			} );
		},

		/**
		 * Register JS events.
		 *
		 * @since 1.0.0
		 */
		events: function() {

			app.generalEvents();
			app.colorPickerEvents();
			app.customLogoEvents();
			app.formSlugEvents();

		},

		/**
		 * Run actions.
		 *
		 * @since 1.0.0
		 */
		actions: function() {

			app.prefillPageTitle();
		},

		/**
		 * Register general events.
		 *
		 * @since 1.0.2
		 */
		generalEvents: function() {

			$el.toggleSettingsCheckbox.click( function( e ) {

				app.toggleSettingsPanel( e );
			} );
		},

		/**
		 * Register colorpicker related events.
		 *
		 * @since 1.0.0
		 */
		colorPickerEvents: function() {

			$.minicolors.defaults.changeDelay = app.settings.minicolorsChangeDelay;
			$( '#wpforms-panel-field-settings-form_pages_color_scheme-7' ).minicolors( {
				show: function() {

					// Once enabled, colorpicker checks a radio button it's attached to.
					$( this ).prop( 'checked', true );
				},
				change: function( value ) {

					if ( value ) {
						$( '#wpforms-panel-field-settings-form_pages_color-input' ).val( value );
					}
				},
			} );

			$( '#wpforms-panel-field-settings-form_pages_color_scheme-wrap .minicolors-panel' ).append( '<input type="text" id="wpforms-panel-field-settings-form_pages_color-input" class="minicolors-input-inner">' );
			$( document ).on(
				'input',
				'#wpforms-panel-field-settings-form_pages_color-input',
				function( event ) {

					if ( event.target.value.length >= app.settings.minicolorsInputEventRunLength ) {
						$( '#wpforms-panel-field-settings-form_pages_color_scheme-7' ).minicolors( 'value', event.target.value );
					}
				}
			);
		},

		/**
		 * Register custom logo related events.
		 *
		 * @since 1.0.0
		 */
		customLogoEvents: function() {

			$el.logo.addBtn.click( function( e ) {

				e.preventDefault();
				app.openMediaFrame();
			} );

			$el.logo.deleleBtn.click( function( e ) {

				e.preventDefault();
				app.deleteCustomLogo();
			} );
		},

		/**
		 * Register form slug related events.
		 *
		 * @since 1.0.0
		 */
		formSlugEvents: function() {

			$el.previewFormBtn.click( function( e ) {

				app.previewForm( e );
			} );

			$el.slug.viewBtn.click( function( e ) {

				app.previewForm( e );
			} );

			$( '#wpforms-builder' ).on( 'wpformsSaved', function( e, data ) {

				app.updateFormSlugUI( data );
			} );
		},

		/**
		 * Conditionally prevent showing the settings panel.
		 *
		 * @since 1.0.2
		 *
		 * @param {object} e Click event.
		 */
		toggleSettingsPanel: function( e ) {

			if ( ! $el.toggleSettingsCheckbox.is( ':checked' ) || ! $( '#wpforms-panel-field-settings-conversational_forms_enable' ).is( ':checked' ) ) {
				return;
			}

			e.preventDefault();

			$.confirm( {
				title    : false,
				content  : wpforms_admin_builder_form_pages.i18n.enable_prevent_modal,
				closeIcon: false,
				icon     : 'fa fa-exclamation-circle',
				type     : 'orange',
				buttons  : {
					cancel: {
						text: wpforms_admin_builder_form_pages.i18n.enable_prevent_modal_ok,
					},
				},
			} );
		},

		/**
		 * Preview the form after saving it.
		 *
		 * @since 1.0.0
		 *
		 * @param {object} e Click event.
		 */
		previewForm: function( e ) {

			e.preventDefault();

			if ( WPFormsBuilder.formIsSaved() ) {
				window.open( e.target.href, '_blank' );
				return;
			}

			var formPage = window.open( '', '_blank' );

			WPFormsBuilder.formSave().done( function() {

				// The location trick is needed to avoid browser popup blocking.
				formPage.location = e.target.href;
			} );
		},

		/**
		 * Init new wp.media frame.
		 *
		 * @since 1.0.0
		 *
		 * @returns {wp.media.view.MediaFrame} Media selection frame.
		 */
		initMediaFrame: function() {

			var mediaFrame;
			var mediaArgs = {
				title   : wpforms_admin_builder_form_pages.i18n.logo_selection_frame_title,
				button  : {
					text: wpforms_admin_builder_form_pages.i18n.logo_selection_frame_button_text,
				},
				library : { type: 'image' },
				multiple: false,
			};

			mediaFrame = wp.media( mediaArgs );

			mediaFrame.on( 'select', function() {
				app.selectCustomLogo( mediaFrame );
			} );

			return mediaFrame;
		},

		/**
		 * Open media selection frame.
		 *
		 * @since 1.0.0
		 */
		openMediaFrame: function() {

			var mediaFrame = ( typeof wp.media.frame === 'undefined' ) ? app.initMediaFrame() : wp.media.frame;

			mediaFrame.open();
		},

		/**
		 * Select an item inside a media frame.
		 *
		 * @since 1.0.0
		 *
		 * @param {wp.media.view.MediaFrame} mediaFrame Media selection frame.
		 */
		selectCustomLogo: function( mediaFrame ) {

			var attachment = mediaFrame.state().get( 'selection' ).first().toJSON();
			var url = attachment.url;

			if ( typeof attachment.sizes.medium !== 'undefined' ) {
				url = attachment.sizes.medium.url;
			}

			$el.logo.deleleBtn.find( 'img' ).remove();
			$el.logo.deleleBtn.append( '<img src="' + url + '" alt="' + wpforms_admin_builder_form_pages.i18n.logo_preview_alt + '"/>' );
			$el.logo.previewContainer.show();
			$el.logo.textField.val( attachment.id );
			$el.logo.deleleBtn.show();
		},

		/**
		 * Delete custom form logo.
		 *
		 * @since 1.0.0
		 */
		deleteCustomLogo: function() {

			$el.logo.previewContainer.find( 'img' ).remove();
			$el.logo.addBtn.show();
			$el.logo.previewContainer.hide();
			$el.logo.textField.val( '' );
		},

		/**
		 * Update form slug field and links.
		 *
		 * @since 1.0.0
		 *
		 * @param {object} data Form save response data.
		 */
		updateFormSlugUI: function( data ) {

			if ( typeof data.form_pages === 'undefined' ) {
				return;
			}

			$el.slug.textField.val( data.form_pages.slug );
			$el.slug.viewBtn.prop( 'href', data.form_pages.url );
			$el.previewFormBtn.prop( 'href', data.form_pages.url );
		},

		/**
		 * Prefill page title before new form is saved.
		 *
		 * @since 1.0.0
		 */
		prefillPageTitle: function() {

			var $formTitle;

			if ( ! wpf.getQueryString( 'newform' ) ) {
				return;
			}

			$formTitle = $( '#wpforms-panel-field-settings-form_pages_title' );

			if ( ! $formTitle.val() ) {
				$formTitle.val( $( '#wpforms-panel-field-settings-form_title' ).val() );
			}
		},
	};

	// Provide access to public functions/properties.
	return app;

}( document, window, jQuery ) );

// Initialize.
WPFormsBuilderFormPages.init();
