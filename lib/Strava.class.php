<?php

require_once WPSTRAVA_PLUGIN_DIR . 'lib/Settings.class.php';
require_once WPSTRAVA_PLUGIN_DIR . 'lib/SOM.class.php';
require_once WPSTRAVA_PLUGIN_DIR . 'lib/LatestRidesWidget.class.php';
require_once WPSTRAVA_PLUGIN_DIR . 'lib/LatestMapWidget.class.php';

class WPStrava {

	private static $instance = NULL;
	private $settings = NULL;
	private $api = NULL;
	private $rides = NULL;
	
	private function __construct() {
		$this->settings = new WPStrava_Settings();

		if ( is_admin() ) {
			$this->settings->hook();
		}

		// Register StravaLatestRidesWidget widget
		add_action( 'widgets_init', function() { return register_widget( 'WPStrava_LatestRidesWidget' ); } );
		add_action( 'widgets_init', function() { return register_widget( 'WPStrava_LatestMapWidget' ); } );
		
	}

	public static function get_instance() {
		if ( ! self::$instance ) {
			$class = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}

	public function __get( $name ) {
		//on-demand classes
		if ( $name == 'api' )
			return $this->get_api();

		if ( $name == 'rides' )
			return $this->get_rides();

		if ( isset( $this->{$name} ) )
			return $this->{$name};

		return NULL;
	}

	public function get_api() {
		if ( ! $this->api ) {
			require_once WPSTRAVA_PLUGIN_DIR . 'lib/API.class.php';
			$this->api = new WPStrava_API( get_option('strava_token') );
		}

		return $this->api;
	}
	
	public function get_rides() {
		if ( ! $this->rides ) {
			require_once WPSTRAVA_PLUGIN_DIR . 'lib/Rides.class.php';
			$this->rides = new WPStrava_Rides();
		}

		return $this->rides;
	}
}