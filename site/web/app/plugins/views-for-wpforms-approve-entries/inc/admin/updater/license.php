<?php
if ( class_exists( 'WPForms_Views_License' ) ) {
	class WPForms_Views_Approve_Entries_License extends WPForms_Views_License{
		public $id = 'approve_entries_license';
		public $item_id = 2061;
		public $version = WPF_VIEWS_APPROVE_SUBMISSIONS_VERSION;
		public $plugin_file = WPF_VIEWS_APPROVE_SUBMISSIONS_PLUGIN_FILE;

	}

	new WPForms_Views_Approve_Entries_License();
}