/* global avadaAdminL10nStrings, ajaxurl, DemoImportNonce, allTags */
/* jshint -W117 */
/* eslint no-unused-vars: off */
this.imagePreview = function() {
	jQuery( '.theme' ).hover( function() {
		jQuery( this ).find( '.screenshot-hover' ).css( 'visibility', 'visible' );
	},
	function() {
		jQuery( this ).find( '.screenshot-hover' ).css( 'visibility', 'visible' );
	} );
};

// Starting the script on page load.
jQuery( document ).ready( function() {
	var copyDebugReport;

	jQuery( '.help_tip' ).tipTip( {
		attribute: 'data-tip'
	} );

	jQuery( 'a.help_tip' ).on( 'click', function() {
		return false;
	} );

	jQuery( '.debug-report-button' ).on( 'click', function() {

		var report = '';

		jQuery( '.avada-db-status h2:not(.avada-status-no-export), .avada-db-status table:not(.avada-status-no-export) tbody' ).each( function() {

			var label,
				theName,
				theValueElement,
				theValue,
				valueArray,
				tempLine;

			if ( jQuery( this ).is( 'h2' ) ) {

				label = jQuery( this ).data( 'export-label' ) || jQuery( this ).text();
				report = report + '\n### ' + jQuery.trim( label ) + ' ###\n\n';

			} else {

				jQuery( 'tr', jQuery( this ) ).each( function() {

					label           = jQuery( this ).find( 'td:eq(0)' ).data( 'export-label' ) || jQuery( this ).find( 'td:eq(0)' ).text();
					theName         = jQuery.trim( label ).replace( /(<([^>]+)>)/ig, '' ); // Remove HTML.
					theValueElement = jQuery( this ).find( 'td:eq(2)' );

					if ( 1 <= jQuery( theValueElement ).find( 'img' ).length ) {
						theValue = jQuery.trim( jQuery( theValueElement ).find( 'img' ).attr( 'alt' ) );
					} else {
						theValue = jQuery.trim( jQuery( this ).find( 'td:eq(2)' ).text() );
					}
					valueArray = theValue.split( ', ' );

					if ( 1 < valueArray.length ) {

						// If value have a list of plugins ','
						// Split to add new line.
						tempLine = '';
						jQuery.each( valueArray, function( key, line ) {
							tempLine = tempLine + line + '\n';
						} );

						theValue = tempLine;
					}

					report = report + '' + theName + ': ' + theValue + '\n';
				} );
			}
		} );

		try {
			jQuery( '.debug-report' ).slideDown();
			jQuery( '.debug-report textarea' ).val( report ).focus().select();
			jQuery( this ).parent().fadeOut();
			return false;
		} catch ( e ) {} // eslint-disable-line no-empty

		return false;
	} );

	jQuery( '#copy-for-support' ).tipTip( {
		attribute: 'data-tip',
		activation: 'click',
		fadeIn: 50,
		fadeOut: 50,
		delay: 100,
		enter: function() {
			copyDebugReport();
		}
	} );

	copyDebugReport = function() {
		jQuery( '.debug-report textarea' ).select();
		document.execCommand( 'copy' );
	};
} );

jQuery( document ).ready( function() {

	var importedLabel,
		tags,
		importedFilter,
		importStagesLength,
		removeStagesLength,
		demoType,
		disablePreview = jQuery( '.preview-all' ),
		importerDialog = jQuery( '#dialog-demo-confirm' ),
		importNotifications,
		prepareDemoImport,
		importDemo,
		prepareDemoRemove,
		removeDemo,
		importReport;

	if ( jQuery( 'body' ).hasClass( 'avada_page_avada-prebuilt-websites' ) ) {

		// If clicked on import data button.
		jQuery( '.button-install-demo' ).on( 'click', function( e ) {

			importNotifications = {
				classic: avadaAdminL10nStrings.classic,
				caffe: avadaAdminL10nStrings.caffe,
				church: avadaAdminL10nStrings.church,
				modern_shop: avadaAdminL10nStrings.modern_shop,
				classic_shop: avadaAdminL10nStrings.classic_shop,
				landing_product: avadaAdminL10nStrings.landing_product,
				forum: avadaAdminL10nStrings.forum,
				technology: avadaAdminL10nStrings.technology,
				creative: avadaAdminL10nStrings.creative,
				'default': avadaAdminL10nStrings[ 'default' ]
			};

			if ( importNotifications.hasOwnProperty( demoType ) ) {
				importerDialog.html( importNotifications[ demoType ] );
			} else {
				importerDialog.html( importNotifications[ 'default' ] );
			}

			jQuery( '#' + importerDialog.attr( 'id' ) ).dialog( {
				dialogClass: 'avada-demo-dialog',
				resizable: false,
				draggable: false,
				height: 'auto',
				width: 400,
				modal: true,
				buttons: {
					Cancel: function() {
						importerDialog.html( '' );
						jQuery( this ).dialog( 'close' );
					},
					OK: function() {
						prepareDemoImport();
						importerDialog.html( '' );
						jQuery( this ).dialog( 'close' );
					}
				}
			} );

			e.preventDefault();
		} );

		importReport = function( message, progress ) {
			jQuery( '#demo-modal-' + demoType  + ' .demo-update-modal-status-bar-label span' ).html( message );

			jQuery( '#demo-modal-' + demoType  + ' .demo-update-modal-status-bar-progress-bar' ).css( 'width', ( 100 * progress ) + '%' );
		};

		importDemo = function( data ) {

			if ( data.importStages.length === importStagesLength ) {
				importReport( avadaAdminL10nStrings.currently_processing.replace( '%s', avadaAdminL10nStrings.download ), ( importStagesLength - data.importStages.length ) / importStagesLength );
			}

			jQuery.post( ajaxurl, data, function( response ) {
				var importLabel;

				if ( 'content' === data.importStages[ 0 ] ) {

					jQuery.each( jQuery( '#import-' + data.demoType + ' input:checkbox[data-type=content]:checked' ), function( ) {
						jQuery( this ).prop( 'disabled', true );
						jQuery( '#remove-' + data.demoType + ' input:checkbox[value=' + jQuery( this ).val() + ']' ).prop( 'checked', true );
					} );
				} else {
					jQuery( '#import-' + data.demoType + ' input:checkbox[value=' + data.importStages[ 0 ] + ']' ).prop( 'disabled', true );
					jQuery( '#remove-' + data.demoType + ' input:checkbox[value=' + data.importStages[ 0 ] + ']' ).prop( 'checked', true );
				}

				data.importStages.shift();

				if ( ( 0 < response.indexOf( 'partially completed' ) || 0 <= response.indexOf( '{"status"' ) ) && 0 < data.importStages.length ) {

					if ( 'content' === data.importStages[ 0 ] ) {
						if ( 1 === data.contentTypes.length ) {
							importLabel = jQuery( 'label[for=import-' + data.contentTypes[ 0 ] + '-' + demoType + ']' ).html();
						} else {
							importLabel = avadaAdminL10nStrings.content;
						}
					} else if ( 'general_data' === data.importStages[ 0 ] ) {
						importLabel = 'General Data';
					} else if ( -1 !== data.importStages[ 0 ].indexOf( 'convertplug_' ) ) {
						importLabel = jQuery( 'label[for=import-convertplug-' + demoType + ']' ).html();
					} else {
						importLabel = jQuery( 'label[for=import-' + data.importStages[ 0 ] + '-' + demoType + ']' ).html();
					}

					importReport( avadaAdminL10nStrings.currently_processing.replace( '%s', importLabel ), ( importStagesLength - data.importStages.length ) / importStagesLength );

					importDemo( data );

				} else if ( -1 === response && response.indexOf( 'imported' ) ) { // eslint-disable-line no-empty
				} else if ( 1 < response.indexOf( avadaAdminL10nStrings.file_does_not_exist ) ) { // eslint-disable-line no-empty
				} else {
					setTimeout( function() {
						jQuery( '#demo-modal-' + demoType + ' input[type="checkbox"][value="uninstall"]' ).prop( 'disabled', false );
						jQuery( '#demo-modal-' + demoType + ' input[type="checkbox"][value="all"]' ).prop( 'disabled', true );
						jQuery( '#demo-modal-' + demoType ).removeClass( 'demo-import-in-progress' );

						importReport( '', 1 );
						jQuery( '#demo-modal-' + demoType + ' .button-done-demo' ).css( 'display', 'flex' );

						if ( true === data.allImport ) {
							importedLabel.html( avadaAdminL10nStrings.full_import );
						} else {
							importedLabel.html( avadaAdminL10nStrings.partial_import );
						}

						importedLabel.show();
						jQuery( '#theme-demo-' + demoType + ' .button-install-open-modal' ).html( avadaAdminL10nStrings.modify );

						if ( -1 === tags.indexOf( 'imported' ) ) {
							jQuery( '#theme-demo-' + demoType ).parent().data( 'tags', tags + ',imported' );
							importedFilter.data( 'count', importedFilter.data( 'count' ) + 1 );
							importedFilter.children( '.count' ).html( '(' + importedFilter.data( 'count' ) + ')' );
						}
					}, 4000 );
				}
			} ).fail( function( xhr, textStatus, errorThrown ) {
				var message;

				if ( 'object' === typeof xhr.responseJSON && 'string' === typeof xhr.responseJSON.data ) {
					message = xhr.responseJSON.data;
				} else if ( 'Request Timeout' === errorThrown ) {
					message = avadaAdminL10nStrings.error_timeout;
				} else {
					message = avadaAdminL10nStrings.error_php_limits;
				}

				importerDialog.html( message );
				jQuery( '#' + importerDialog.attr( 'id' ) ).dialog( {
					dialogClass: 'avada-demo-dialog',
					resizable: false,
					draggable: false,
					height: 'auto',
					title: 'Import Failed',
					width: 400,
					modal: true,
					buttons: {
						OK: function() {
							importerDialog.html( '' );
							jQuery( this ).dialog( 'close' );
							location.reload();
						}
					}
				} );
			} );

		};

		prepareDemoImport = function() {

			var allImport        = false,
				fetchAttachments = false,
				data,
				importArray,
				importContentArray;

			importedLabel      = jQuery( '#theme-demo-' + demoType + ' .plugin-premium' );
			tags               = jQuery( '#theme-demo-' + demoType ).parent().data( 'tags' );
			importedFilter     = jQuery( '.avada-db-demos-filter-imported' );
			importArray        = [ 'download' ];
			importContentArray = [];

			jQuery( '#import-' + demoType + ' input:checkbox:checked' ).each( function() {

				if ( ! this.disabled ) {

					if ( 'content' === this.getAttribute( 'data-type' ) ) {
						importContentArray.push( this.value );

						if ( -1 === importArray.indexOf( 'content' ) ) {
							importArray.push( 'content' );
						}
					} else {
						importArray.push( this.value );
					}
				}

				if ( 'all' === this.value ) {
					this.disabled = true;
					allImport = true;
				}
			} );

			// If 'all' is selected menus should be imported and home page set (which is done at the end of the process).
			if ( -1 !== importArray.indexOf( 'all' ) ) {
				importArray.splice( importArray.indexOf( 'all' ), 1 );
				importArray.push( 'general_data' );
			}

			if ( 0 < importContentArray.length && ( -1 !== importContentArray.indexOf( 'attachment' ) || -1 !== importContentArray.indexOf( 'fusion_icons' ) ) ) {
				fetchAttachments = true;
			}

			importStagesLength = importArray.length;

			data = {
				action: 'fusion_import_demo_data',
				security: DemoImportNonce,
				demoType: demoType,
				importStages: importArray,
				contentTypes: importContentArray,
				fetchAttachments: fetchAttachments,
				allImport: allImport
			};

			jQuery( '#demo-modal-' + demoType ).addClass( 'demo-import-in-progress' );
			jQuery( '.button-install-demo[data-demo-id=' + demoType + ']' ).css( 'display', 'none' );

			importDemo( data );
		};

		removeDemo = function( data ) {

			var removeLabel;

			if ( 'content' === data.removeStages[ 0 ] ) {
				removeLabel = avadaAdminL10nStrings.content;
			} else {
				removeLabel = jQuery( 'label[for=remove-' + data.removeStages[ 0 ] + '-' + demoType + ']' ).html();
			}

			if ( data.removeStages.length === removeStagesLength ) {
				importReport( avadaAdminL10nStrings.currently_processing.replace( '%s', removeLabel ), ( removeStagesLength - data.removeStages.length ) / removeStagesLength );
			}

			jQuery.post( ajaxurl, data, function( $response ) {

				if ( 'content' === data.removeStages[ 0 ] ) {

					jQuery.each( jQuery( '#remove-' + data.demoType + ' input:checkbox[data-type=content]:checked' ), function( ) {

						jQuery( this ).prop( 'disabled', true );
						jQuery( this ).prop( 'checked', false );

						jQuery( '#import-' + data.demoType + ' input:checkbox[value=' + jQuery( this ).val() + ']' ).prop( 'checked', false );
						jQuery( '#import-' + data.demoType + ' input:checkbox[value=' + jQuery( this ).val() + ']' ).prop( 'disabled', false );
					} );
				} else {
					jQuery( '#remove-' + data.demoType + ' input:checkbox[value=' + data.removeStages[ 0 ] + ']' ).prop( 'disabled', true );
					jQuery( '#remove-' + data.demoType + ' input:checkbox[value=' + data.removeStages[ 0 ] + ']' ).prop( 'checked', false );
					jQuery( '#import-' + data.demoType + ' input:checkbox[value=' + data.removeStages[ 0 ] + ']' ).prop( 'checked', false );
					jQuery( '#import-' + data.demoType + ' input:checkbox[value=' + data.removeStages[ 0 ] + ']' ).prop( 'disabled', false );
				}

				data.removeStages.shift();

				if ( 0 <= $response.indexOf( 'partially removed' ) && 0 < data.removeStages.length  ) {
					importReport( avadaAdminL10nStrings.currently_processing.replace( '%s', removeLabel ), ( removeStagesLength - data.removeStages.length ) / removeStagesLength );

					removeDemo( data );

				} else {
					importReport( '', 1 );
					jQuery( '#demo-modal-' + demoType + ' .button-done-demo' ).css( 'display', 'flex' );
					importedLabel.hide();
					jQuery( '#theme-demo-' + demoType + ' .button-install-open-modal' ).html( avadaAdminL10nStrings[ 'import' ] );

					jQuery( '#theme-demo-' + demoType ).parent().data( 'tags', tags.replace( ',imported', '' ) );
					importedFilter.data( 'count', importedFilter.data( 'count' ) - 1 );
					importedFilter.children( '.count' ).html( '(' + importedFilter.data( 'count' ) + ')' );

					jQuery( '#import-' + demoType + ' input[type="checkbox"][value="all"]' ).prop( 'checked', false );
					jQuery( '#import-' + demoType + ' input[type="checkbox"]:not(:checked)' ).prop( 'disabled', false );
					jQuery( '#demo-modal-' + demoType + ' input[type="checkbox"][value="uninstall"]' ).prop( 'disabled', true );
					jQuery( '#demo-modal-' + demoType + ' input[type="checkbox"][value="uninstall"]' ).prop( 'checked', false );

					jQuery( '#demo-modal-' + demoType ).removeClass( 'demo-import-in-progress' );
				}

			} ).fail( function() {} ); // eslint-disable-line no-empty-function

		};

		prepareDemoRemove = function() {
			var data,
				removeArray = [];

			importedLabel  = jQuery( '#theme-demo-' + demoType + ' .plugin-premium' );
			tags           = jQuery( '#theme-demo-' + demoType ).parent().data( 'tags' );
			importedFilter = jQuery( '.avada-db-demos-filter-imported' );

			jQuery( '#remove-' + demoType + ' input:checkbox:checked' ).each( function() {

				if ( 'content' === this.getAttribute( 'data-type' ) ) {

					if ( -1 === removeArray.indexOf( 'content' ) ) {
						removeArray.push( 'content' );
					}

				} else {
					removeArray.push( this.value );
				}

			} );
			removeStagesLength = removeArray.length;

			data = {
				action: 'fusion_remove_demo_data',
				demoType: demoType,
				security: DemoImportNonce,
				removeStages: removeArray
			};

			jQuery( '#demo-modal-' + demoType ).addClass( 'demo-import-in-progress' );
			jQuery( '.button-uninstall-demo[data-demo-id=' + demoType + ']' ).css( 'display', 'none' );

			removeDemo( data );
		};

		// If clicked on remove demo button.
		jQuery( '.button-uninstall-demo' ).on( 'click', function( e ) {

			importerDialog.html( avadaAdminL10nStrings.remove_demo );

			jQuery( '#' + importerDialog.attr( 'id' ) ).dialog( {
				dialogClass: 'avada-demo-dialog',
				resizable: false,
				draggable: false,
				height: 'auto',
				width: 400,
				modal: true,
				buttons: {
					Cancel: function() {
						importerDialog.html( '' );
						jQuery( this ).dialog( 'close' );
					},
					OK: function() {
						prepareDemoRemove();
						importerDialog.html( '' );
						jQuery( this ).dialog( 'close' );
					}
				}
			} );

			e.preventDefault();

		} );

		jQuery( '.demo-import-form input:checkbox' ).on( 'change', function() {

			var form = jQuery( this ).closest( 'form' );

			if ( 'all' === jQuery( this ).val() ) {

				// 'all' checkbox is checked.

				form.find( 'input:checkbox:not(:disabled)' ).prop( 'checked', jQuery( this ).prop( 'checked' ) );

				if ( jQuery( this ).is( ':checked' ) ) {
					jQuery( '.button-install-demo[data-demo-id="' + demoType + '"]' ).css( 'display', 'flex' );

					jQuery( '#demo-modal-' + demoType  + ' .demo-update-modal-status-bar-label span' ).html( '' );
					jQuery( '#demo-modal-' + demoType + ' .button-done-demo' ).css( 'display', 'none' );
				} else {
					jQuery( '.button-install-demo[data-demo-id="' + demoType + '"]' ).css( 'display', 'none' );
				}

			} else if ( 0 < form.find( 'input[type="checkbox"]:checked' ).not( ':disabled' ).length ) {

				// Checkbox is checked, but there could be disabled (previously imported) checkboxes as well.

				jQuery( '.button-install-demo[data-demo-id="' + demoType + '"]' ).css( 'display', 'flex' );

				// We want to check 'all' if all checkboxes are selected and there are not "disabled" among them.
				if ( ! form.find( 'input[type="checkbox"]:checked' ).is( ':disabled' ) ) {

					// -1 is excluding 'all' checkbox.
					if ( ( form.find( 'input[type="checkbox"]' ).length - 1 ) === form.find( 'input[type="checkbox"]:checked' ).length ) {
						jQuery( '#demo-modal-' + demoType + ' input[type="checkbox"][value="all"]' ).prop( 'checked', true );
					}
				}

				jQuery( '#demo-modal-' + demoType  + ' .demo-update-modal-status-bar-label span' ).html( '' );
				jQuery( '#demo-modal-' + demoType + ' .button-done-demo' ).css( 'display', 'none' );

				jQuery( '#demo-modal-' + demoType + ' input[type="checkbox"][value="uninstall"]' ).prop( 'disabled', true );
			} else {

				// Checkbox is unchecked.
				jQuery( '.button-install-demo[data-demo-id="' + demoType + '"]' ).css( 'display', 'none' );

				if ( form.find( 'input[type="checkbox"]:checked' ).is( ':disabled' ) ) {

					// There is something to uninstall
					jQuery( '#demo-modal-' + demoType + ' input[type="checkbox"][value="uninstall"]' ).prop( 'disabled', false );
				}
			}

			// Uncheck 'all' if checkbox was unchecked.
			if ( false === jQuery( this ).prop( 'checked' ) ) {
				jQuery( '#demo-modal-' + demoType + ' input[type="checkbox"][value="all"]' ).prop( 'checked', false );
			}

		} );

		jQuery( '.demo-remove-form input:checkbox[value="uninstall"]' ).on( 'change', function() {

			if ( jQuery( this ).is( ':checked' ) ) {
				jQuery( '.button-uninstall-demo[data-demo-id="' + demoType + '"]' ).css( 'display', 'flex' );

				jQuery( '#import-' + demoType + ' input[type="checkbox"]' ).prop( 'disabled', true );
				jQuery( '#demo-modal-' + demoType  + ' .demo-update-modal-status-bar-label span' ).html( '' );
				jQuery( '#demo-modal-' + demoType + ' .button-done-demo' ).css( 'display', 'none' );
			} else {
				jQuery( '.button-uninstall-demo[data-demo-id="' + demoType + '"]' ).css( 'display', 'none' );

				jQuery.each( jQuery( '#import-' + demoType + ' input[type="checkbox"]:not(:checked)' ), function() {
					if ( 'all' !== jQuery( this ).val() ) {
						jQuery( this ).prop( 'disabled', false );
					}
				} );
			}
		} );

		jQuery( '.button-install-open-modal' ).on( 'click', function( e ) {
			e.preventDefault();

			demoType = jQuery( this ).data( 'demo-id' );

			if ( 0 === jQuery( '#import-' + demoType ).find( 'input[type="checkbox"]:checked' ).length ) {
				jQuery( '#demo-modal-' + demoType + ' input[type="checkbox"][value="uninstall"]' ).prop( 'disabled', true );
			} else {
				jQuery( '#import-' + demoType + ' input[type="checkbox"][value="all"]' ).prop( 'disabled', true );
			}

			jQuery( 'body' ).addClass( 'fusion_builder_no_scroll' );
			disablePreview.show();

			jQuery( '#demo-modal-' + jQuery( this ).data( 'demo-id' ) ).css( 'display', 'block' );
		} );

		jQuery( '.demo-update-modal-close' ).on( 'click', function( e ) {
			e.preventDefault();
			jQuery( '#demo-modal-' + demoType  + ' .demo-update-modal-status-bar-label span' ).html( '' );

			// Uncheck all checkboxes which aren't disabled (imported).
			jQuery( '#import-' + demoType ).find( 'input[type="checkbox"]:checked' ).not( ':disabled' ).prop( 'checked', false ).trigger( 'change' );

			demoType = null;
			jQuery( 'body' ).removeClass( 'fusion_builder_no_scroll' );
			disablePreview.hide();

			jQuery( this ).closest( '.demo-update-modal-wrap' ).css( 'display', 'none' );

		} );

		jQuery( document ).on( 'keydown', function( e ) {
			if ( 'block' === disablePreview.css( 'display' ) && 27 === e.keyCode ) {
				jQuery( '.demo-update-modal-close' ).trigger( 'click' );
			}
		} );

		jQuery( '.avada-importer-tags-selector button' ).on( 'click', function( e ) {
			var demos = jQuery( '.avada-db-demos-themes' ).find( '.fusion-admin-box' ),
				value = this.getAttribute( 'data-tag' );

			e.preventDefault();

			// Show/hide demos.
			if ( 'all' === value ) {
				demos.show();
			} else {
				demos.hide();
				demos.each( function() {
					if ( -1 !== jQuery( this ).data( 'tags' ).indexOf( value ) ) {
						jQuery( this ).show();
					}
				} );
			}

			// Mark current item as active.
			jQuery( '.avada-importer-tags-selector button' ).removeClass( 'current-filter' );
			this.classList.add( 'current-filter' );

			// Trigger scroll for lazy-loaded images.
			window.dispatchEvent( new Event( 'scroll' ) );
		} );

		jQuery( '#avada-demos-search' ).on( 'change keyup', function( e ) {
			var demos = jQuery( '.avada-db-demos-themes' ).find( '.fusion-admin-box' ),
				value = this.getAttribute( 'data-tag' );

			e.preventDefault();

			// Show/hide demos.
			demos.hide();
			demos.each( function() {
				var demoTitle = jQuery( this ).data( 'title' );
				if ( demoTitle && -1 !== demoTitle.toLowerCase().indexOf( e.target.value.toLowerCase() ) ) {
					jQuery( this ).show();
				}
			} );

			// Move the category filter to "All".
			jQuery( '.avada-importer-tags-selector button' ).removeClass( 'current-filter' );
			document.querySelector( '.avada-importer-tags-selector button[data-tag=all]' ).classList.add( 'current-filter' );

			// Trigger scroll for lazy-loaded images.
			window.dispatchEvent( new Event( 'scroll' ) );
		} );
	}

	if ( jQuery( 'body' ).hasClass( 'avada_page_avada-plugins' ) ) {

		jQuery( '.avada-install-plugins .theme-actions .button-primary.disabled' ).on( 'click', function( e ) {

			var pluginDialog = jQuery( '#dialog-plugin-confirm' );

			e.preventDefault();

			if ( jQuery( this ).hasClass( 'fusion-builder' ) ) {
				pluginDialog.html( avadaAdminL10nStrings.update_fc.replace( '%s', jQuery( this ).data( 'version' ) ) );
			} else {
				pluginDialog.html( avadaAdminL10nStrings.register_first  );
			}

			jQuery( '#' + pluginDialog.attr( 'id' ) ).dialog( {
				dialogClass: 'avada-plugin-dialog',
				resizable: false,
				draggable: false,
				height: 'auto',
				width: 400,
				modal: true,
				buttons: {
					OK: function() {
						pluginDialog.html( '' );
						jQuery( this ).dialog( 'close' );
					}
				}
			} );
		} );

		jQuery( '#manage-plugins' ).on( 'click', function( e ) {

			var href              = jQuery( this ).attr( 'href' ),
				hrefHash          = href.substr( href.indexOf( '#' ) ).slice( 1 ),
				target            = jQuery( '#' + hrefHash ),
				adminbarHeight    = jQuery( '#wpadminbar' ).length ? jQuery( '#wpadminbar' ).height() : 0,
				newScrollPosition = target.offset().top - adminbarHeight;

			e.preventDefault();

			jQuery( 'html, body' ).animate( {
				scrollTop: newScrollPosition
			}, 450 );
		} );
	}

	jQuery( '.demo-required-plugins .activate a' ).on( 'click', function( e ) {

		var $this = jQuery( this ),
			data = {
				action: 'fusion_activate_plugin',
				avada_activate: 'activate-plugin',
				plugin: $this.data( 'plugin' ),
				plugin_name: $this.data( 'plugin_name' ),
				avada_activate_nonce: $this.data( 'nonce' )
			};

		// Disable parallel plugin install
		jQuery( '#demo-modal-' + demoType ).addClass( 'plugin-install-in-progress' );

		$this.addClass( 'installing' );

		jQuery.get( ajaxurl, data, function( response ) {

			if ( true !== response.error ) {

				jQuery.each( jQuery( '.required-plugin-status a[data-plugin=' + data.plugin + ']' ), function( index, element ) {
					jQuery( element ).html( avadaAdminL10nStrings.plugin_active ).css( 'pointer-events', 'none' );
					jQuery( element ).parent().removeClass( 'activate' ).addClass( 'active' );
				} );

			} else {
				jQuery( '#demo-modal-' + demoType  + ' .demo-update-modal-status-bar-label span' ).html( avadaAdminL10nStrings.plugin_install_failed );
			}

			$this.removeClass( 'installing' );
			jQuery( '#demo-modal-' + demoType ).removeClass( 'plugin-install-in-progress' );
		}, 'json' );

		e.preventDefault();
	} );

	jQuery( '.demo-required-plugins .install a' ).on( 'click', function( e ) {

		var $this = jQuery( this ),
			data = {
				action: 'fusion_install_plugin',
				avada_activate: 'activate-plugin',
				plugin: $this.data( 'plugin' ),
				plugin_name: $this.data( 'plugin_name' ),
				avada_activate_nonce: $this.data( 'nonce' ),
				page: 'install-required-plugins'
			};

		// 'page' arg needed so 'avada_get_required_and_recommened_plugins' sets proper plugin URL.

		data[ 'tgmpa-install' ] = 'install-plugin';
		data[ 'tgmpa-nonce' ]   =  $this.data( 'tgmpa_nonce' );

		// Disable parallel plugin install
		jQuery( '#demo-modal-' + demoType ).addClass( 'plugin-install-in-progress' );

		$this.addClass( 'installing' );

		jQuery.get( ajaxurl, data, function( response ) {

			if ( 0 < response.indexOf( 'plugins.php?action=activate' ) ) {

				jQuery.each( jQuery( '.required-plugin-status a[data-plugin=' + data.plugin + ']' ), function( index, element ) {
					jQuery( element ).html( avadaAdminL10nStrings.plugin_active ).css( 'pointer-events', 'none' );
					jQuery( element ).parent().removeClass( 'install' ).addClass( 'active' );
				} );

			} else {
				jQuery( '#demo-modal-' + demoType  + ' .demo-update-modal-status-bar-label span' ).html( avadaAdminL10nStrings.plugin_install_failed );
			}

			$this.removeClass( 'installing' );

			jQuery( '#demo-modal-' + demoType ).removeClass( 'plugin-install-in-progress' );
		}, 'html' );

		e.preventDefault();
	} );

	/**
	 * WIP - Ajax plugin manager.
	 *
	 * A prototype, disabled for now.
	 * To enable the feature just un-comment the line below.
	 */
	// avadaPluginsManager();
} );

function avadaPluginsManager() {

	// Add listeners for plugin buttons.
	jQuery( '#avada-install-plugins .theme-actions a' ).on( 'click', function( e ) {
		var box, url, action, data;

		// Build the ajax request data.
		data = {
			action: 'avada_ajax_plugin_manager',
			avada_plugins_nonce: jQuery( this ).data( 'nonce' ),
			href: e.target.href
		};

		// Prevent default link action.
		e.preventDefault();

		// If browser doesn't support URL, fallback to redirection.
		// Acts as a fallback for really old browsers.
		if ( 'function' !== typeof URL ) {
			window.location = e.target.href;
			return;
		}

		url = new URL( e.target.href );

		// Get the action we're performing.
		action = url.searchParams.get( 'tgmpa-install' );
		action = action || url.searchParams.get( 'tgmpa-update' );
		action = action || url.searchParams.get( 'avada-activate' );
		action = action || url.searchParams.get( 'avada-deactivate' );

		// If we're activating/deactivating a plugin,
		// do the full refresh because admin menus and functionality changes.
		if ( 'deactivate-plugin' === action || 'activate-plugin' === action ) {
			window.location = e.target.href;
			return;
		}

		// Get the box. We're including extra data there.
		box = jQuery( e.target ).parents( '.fusion-admin-box' );

		// Add extra data for the ajax request.
		data.actionToDo = action;
		data.pluginPath = jQuery( box ).data( 'file_path' );
		data.plugin     = data.pluginPath;
		data.slug       = url.searchParams.get( 'plugin' );

		// Show the overlay.
		jQuery( '#avada-plugins-wrapper-overlay' ).css( 'display', 'flex' );
		jQuery( '#avada-plugins-manager-overlay-message' ).html( avadaAdminL10nStrings.please_wait );

		// Do the ajax call.
		jQuery.post( ajaxurl, data, function( response ) {
			var httpRequest;
			if ( ! response.success ) {

				// log errors to the console.
				console.error( response );
			}

			if ( response.data.install && response.data.activateUrl ) {
				httpRequest = new XMLHttpRequest();
				httpRequest.onreadystatechange = function() {
					if ( 4 === httpRequest.readyState && 200 === httpRequest.status ) {

						// Process was a success. We need to refresh the container.
						setTimeout( function() {
							data.actionToDo = 'refresh-container';
							jQuery.post( ajaxurl, data, function( refreshResponse ) {
								if ( refreshResponse.success ) {
									jQuery( '#avada-plugins-wrapper' ).replaceWith( refreshResponse.data );

									// Retrigger the function to add listeners for new elements.
									avadaPluginsManager();
								}
							} );
						}, 1000 );
					}
				};
				httpRequest.open( 'GET', response.data.activateUrl, true );
				httpRequest.send();
			} else {

				// Process was a success. We need to refresh the container.
				data.actionToDo = 'refresh-container';
				jQuery.post( ajaxurl, data, function( refreshResponse ) {
					if ( refreshResponse.success ) {
						jQuery( '#avada-plugins-wrapper' ).replaceWith( refreshResponse.data );

						// Retrigger the function to add listeners for new elements.
						avadaPluginsManager();
					}
				} );
			}
		} );
	} );
}

// Avada Dashboard.
jQuery( document ).ready( function() {

	// Welcome Setup Toggle.
	jQuery( '.avada-db-welcome-setup-completed .avada-db-more-info' ).on( 'click', function( e ) {
		e.preventDefault();

		jQuery( this ).parent().removeClass( 'avada-db-welcome-setup-completed' ).addClass( 'avada-db-welcome-setup-completed-toggled' );
	} );

	jQuery( '.avada-db-welcome-setup-completed .notice-dismiss' ).on( 'click', function( e ) {
		e.preventDefault();

		jQuery( this ).parent().removeClass( 'avada-db-welcome-setup-completed-toggled' ).addClass( 'avada-db-welcome-setup-completed' );
	} );


	// Welcome Video expand.
	jQuery( '.avada-db-welcome-video' ).on( 'click', function( e ) {
		e.preventDefault();

		jQuery( '.avada-db-welcome-video-container' ).toggleClass( 'avada-db-active' );
	} );


	// Scroll to the registration form.
	jQuery( '.avada-db-step-one' ).on( 'click', function( e ) {
		var href               = jQuery( this ).attr( 'href' ),
			target             = jQuery( href ),
			animationRoot      = ( jQuery( 'html' ).hasClass( 'ua-edge' ) || jQuery( 'html' ).hasClass( 'ua-safari-12' ) || jQuery( 'html' ).hasClass( 'ua-safari-11' ) || jQuery( 'html' ).hasClass( 'ua-safari-10' ) ) ? 'body' : 'html',
			adminbarHeight     = jQuery( '#wpadminbar' ).length ? jQuery( '#wpadminbar' ).height() : 0,
			stickyHeaderHeight = jQuery( '.avada-db-header-sticky' ).height(),
			newScrollPosition  = target.offset().top - adminbarHeight - stickyHeaderHeight;

		e.preventDefault();

		jQuery( animationRoot ).animate( {
			scrollTop: newScrollPosition
		}, 400 );
	} );

	// Toggle the how to instructions for the registration.
	jQuery( 'body' ).on( 'click', '.avada-db-card-heading-badge-howto', function( e ) {
		e.preventDefault();

		jQuery( '.avada-db-reg-howto' ).slideToggle();
	} );

	jQuery( 'body' ).on( 'submit', '.avada-db-reg-form', function( e ) {
		var form = jQuery( this );

		// No AJAX registration.
		if ( form.find( 'input[name="no_ajax_reg"]' ).length ) {
			return true;
		}

		e.preventDefault();

		form.find( '.avada-db-reg-loader' ).show();
		form.find( '.avada-db-reg-input-icon' ).hide();

		jQuery.post(
			ajaxurl,
			form.serialize(),
			function( response ) {
				form.closest( '#avada-db-registration' ).html( response );

				// Registered.
				if ( jQuery( '#avada-db-registration' ).find( '.avada-db-reg-heading i' ).hasClass( 'fusiona-verified' ) ) {
					jQuery( '.avada-db-menu-sticky-label' ).addClass( 'completed' );
					jQuery( '.avada-db-step-one' ).addClass( 'avada-db-completed' );
					jQuery( '#avada-db-registration' ).addClass( 'avada-db-completed' );

					// Hide grace period warning.
					if ( jQuery( '#fusion-legacy-notice' ).length ) {
						jQuery( '#fusion-legacy-notice' ).remove();
					}
				} else {

					// Unregistered.
					jQuery( '.avada-db-menu-sticky-label' ).removeClass( 'completed' );
					jQuery( '.avada-db-step-one' ).removeClass( 'avada-db-completed' );
					jQuery( '#avada-db-registration' ).removeClass( 'avada-db-completed' );
				}

				// Remove the all completed class from the steps card.
				if ( 3 > jQuery( '.avada-db-setup-step.avada-db-completed' ).length ) {
					jQuery( '.avada-db-welcome-setup' ).removeClass( 'avada-db-welcome-setup-completed' );
				}
			}
		);
		return false;
	} );
} );

// System status page.
jQuery( document ).ready( function() {

	// API Check.
	jQuery( '.fusion-check-api-status' ).on( 'click', function( event ) {
		var $this = jQuery( this ),
			statusCell = $this.closest( 'tr' ).find( 'td:nth-child(3)' ),
			data = {
			action: 'fusion_check_api_status',
			nonce: jQuery( '#fusion-system-status-nonce' ).val()
		};

		event.preventDefault();

		if ( 'undefined' === typeof jQuery( this ).data( 'api_type' ) ) {
			return;
		}

		statusCell.html( '' );
		$this.closest( 'tr' ).find( '.fusion-system-status-spinner' ).css( 'display', 'inline-block' );

		data.api_type = jQuery( this ).data( 'api_type' );

		jQuery.get( ajaxurl, data, function( response ) {

			if ( 200 === response.code ) {
				statusCell.removeClass( 'fusion-api-status-error' );
				statusCell.addClass( 'fusion-api-status-ok' );
				jQuery( '#fusion-check-api-textarea' ).css( 'display', 'none' );
			} else {
				statusCell.removeClass( 'fusion-api-status-ok' );
				statusCell.addClass( 'fusion-api-status-error' );
				jQuery( '#fusion-check-api-textarea' ).css( 'display', 'block' );
			}

			$this.closest( 'tr' ).find( '.fusion-system-status-spinner' ).css( 'display', 'none' );
			statusCell.html( response.message );

			jQuery( '#fusion-check-api-textarea' ).html( response.api_response );
		}, 'json' );

	} );

	// Form tables.
	jQuery( '.fusion-create-forms-tables' ).on( 'click', function( event ) {
		var $this = jQuery( this ),
			statusCell = $this.closest( 'tr' ).find( 'td:nth-child(3)' ),
			data = {
			action: 'fusion_create_forms_tables',
			nonce: jQuery( '#fusion-system-status-nonce' ).val()
		};

		event.preventDefault();

		statusCell.html( '' );
		$this.closest( 'tr' ).find( '.fusion-system-status-spinner' ).css( 'display', 'inline-block' );

		jQuery.get( ajaxurl, data, function( response ) {

			$this.closest( 'tr' ).find( '.fusion-system-status-spinner' ).css( 'display', 'none' );
			statusCell.html( response.message );
		}, 'json' );

	} );

	// Registration scroll to.
	function scrollToRegistration() {
		var target            = jQuery( '#avada-db-registration' ),
			adminbarHeight    = jQuery( '#wpadminbar' ).length ? jQuery( '#wpadminbar' ).height() : 0,
			scrollSticky      = jQuery( '.avada-db-menu-sticky' ).length ? jQuery( '.avada-db-menu-sticky' ).height() : 0,
			newScrollPosition = target.offset().top - adminbarHeight - scrollSticky - 20;

		jQuery( 'html, body' ).animate( {
			scrollTop: newScrollPosition
		}, 450 );
	}

	if ( -1 !== window.location.href.indexOf( '#_avada-db-registration' ) ) {
		scrollToRegistration();
	}

	jQuery( 'a[href="#avada-db-registration"]' ).on( 'click', function( event ) {
		event.preventDefault();
		scrollToRegistration();
	} );
} );
