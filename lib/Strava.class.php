<?php

require_once WPSTRAVA_PLUGIN_DIR . 'lib/Settings.class.php';
require_once WPSTRAVA_PLUGIN_DIR . 'lib/SOM.class.php';
require_once WPSTRAVA_PLUGIN_DIR . 'lib/LatestRidesWidget.class.php';
require_once WPSTRAVA_PLUGIN_DIR . 'lib/LatestMapWidget.class.php';
require_once WPSTRAVA_PLUGIN_DIR . 'lib/RideShortcode.class.php';
require_once WPSTRAVA_PLUGIN_DIR . 'lib/StaticMap.class.php';

class WPStrava {

	private static $instance = NULL;
	private $settings = NULL;
	private $api = NULL;
	private $rides = NULL;
	
	private function __construct() {
		$this->settings = new WPStrava_Settings();

		if ( is_admin() ) {
			$this->settings->hook();
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
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

	public function register_scripts() {
		// Register a personalized stylesheet
		wp_register_style( 'wp-strava-style', WPSTRAVA_PLUGIN_URL . 'css/wp-strava.css' );
	}
}