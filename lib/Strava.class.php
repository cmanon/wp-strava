<?php

require_once WPSTRAVA_PLUGIN_DIR . 'lib/Settings.class.php';
require_once WPSTRAVA_PLUGIN_DIR . 'lib/SOM.class.php';
require_once WPSTRAVA_PLUGIN_DIR . 'lib/LatestRidesWidget.class.php';
require_once WPSTRAVA_PLUGIN_DIR . 'lib/LatestMapWidget.class.php';

class WPStrava {

	private static $instance = NULL;
	private $settings = NULL;
	private $api = NULL;
	
	private function __construct() {
		$this->settings = new WPStrava_Settings();

		if ( is_admin() ) {
			$this->settings->hook();
		}

		// Register StravaLatestRidesWidget widget
		add_action( 'widgets_init', function() {	return register_widget( 'WPStrava_LatestRidesWidget' ); } );
		add_action( 'widgets_init', function() {	return register_widget( 'WPStrava_LatestMapWidget' ); } );
		
	}

	public static function get_instance() {
		if ( ! self::$instance )
			self::$instance = new WPStrava();
		return self::$instance;
	}

	public function __get( $name ) {
		if ( isset( $this->{$name} ) )
			return $this->{$name};

		if ( $name == 'api' )
			return $this->get_api();

		return NULL;
	}

	public function get_api() {
		if ( ! $this->api ) {
			require_once WPSTRAVA_PLUGIN_DIR . 'lib/API.class.php';
			$this->api = new WPStrava_API();
		}

		return $this->api;
	}
}