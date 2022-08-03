<?php
if ( class_exists( 'WPForms_Views_License' ) ) {
	class WPF_Views_Single_Entry_License extends WPForms_Views_License{
		public $id = 'single_entry_license';
		public $item_id = 2542;
		public $version = WPF_VIEWS_SINGLE_ENTRY_VERSION;
		public $plugin_file = WPF_VIEWS_SINGLE_ENTRY_PLUGIN_FILE;

	}

	new WPF_Views_Single_Entry_License();
}