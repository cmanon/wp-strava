<?php

require_once WPSTRAVA_PLUGIN_DIR . 'lib/Settings.class.php';
require_once WPSTRAVA_PLUGIN_DIR . 'lib/SOM.class.php';
require_once WPSTRAVA_PLUGIN_DIR . 'lib/LatestRidesWidget.class.php';
require_once WPSTRAVA_PLUGIN_DIR . 'lib/LatestMapWidget.class.php';
require_once WPSTRAVA_PLUGIN_DIR . 'lib/RideShortcode.class.php';
require_once WPSTRAVA_PLUGIN_DIR . 'lib/StaticMap.class.php';

class WPStrava {

	private static $instance = null;
	private $settings = null;
	private $api = array(); // Holds an array of APIs.
	private $rides = null;

	private function __construct() {
		$this->settings = new WPStrava_Settings();

		if ( is_admin() ) {
			$this->settings->hook();
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		}

		// Register StravaLatestRidesWidget widget
		add_action( 'widgets_init', create_function( '', 'return register_widget( "WPStrava_LatestRidesWidget" );' ) );
		add_action( 'widgets_init', create_function( '', 'return register_widget( "WPStrava_LatestMapWidget" );' ) );
	}

	public static function get_instance() {
		if ( ! self::$instance ) {
			$class = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}

	public function __get( $name ) {
		// On-demand classes.
		if ( $name == 'rides' ) {
			return $this->get_rides();
		}

		if ( isset( $this->{$name} ) ) {
			return $this->{$name};
		}

		return null;
	}

	public function get_api( $id = '0' ) {
		if ( ! $this->api[$id] ) {
			require_once WPSTRAVA_PLUGIN_DIR . 'lib/API.class.php';
			$this->api[$id] = new WPStrava_API( $this->settings->get_setting( 'strava_token', $id ) );
		}

		return $this->api[$id];
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
