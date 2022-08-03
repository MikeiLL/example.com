<?php
class WPF_Views_Widget {

	public $widget_type ;

	public function __construct() {
		$this->add_hooks();
	}


	/**
	 * Add the filter to display values
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	protected function add_hooks() {

		add_filter( "wpf-views/{$this->widget_type}-html", array(
				$this,
				'get_widget_html',
			), 10, 4 );


	}

	public function get_widget_html( $html, $_view_field, $_view_settings,$view_Obj ) {
		return $html;
	}



}

new WPF_Views_Widget();
