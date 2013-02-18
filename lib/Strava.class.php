<?php

require_once WPSTRAVA_PLUGIN_DIR . 'lib/Settings.class.php';
require_once WPSTRAVA_PLUGIN_DIR . 'lib/LatestRidesWidget.class.php';

class WPStrava {

	private static $instance = NULL;
	public $settings = NULL;
	
	private function __construct() {
		$this->settings = new WPStrava_Settings();

		if ( is_admin() ) {
			$this->settings->hook();
		}

		// Register StravaLatestRidesWidget widget
		add_action('widgets_init', function() {	return register_widget('WPStrava_LatestRidesWidget'); });
		
	}

	public static function get_instance() {
		if ( ! self::$instance )
			self::$instance = new WPStrava();
		return self::$instance;
	}
}