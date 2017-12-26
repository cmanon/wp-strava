<?php

require_once WPSTRAVA_PLUGIN_DIR . 'lib/Settings.class.php';
require_once WPSTRAVA_PLUGIN_DIR . 'lib/SOM.class.php';
require_once WPSTRAVA_PLUGIN_DIR . 'lib/ActivityShortcode.class.php';
require_once WPSTRAVA_PLUGIN_DIR . 'lib/RouteShortcode.class.php';
require_once WPSTRAVA_PLUGIN_DIR . 'lib/StaticMap.class.php';

class WPStrava {

	private static $instance = null;
	private $settings        = null;
	private $api             = array(); // Holds an array of APIs.
	private $rides           = null;
	private $routes          = null;

	private function __construct() {
		$this->settings = new WPStrava_Settings();

		if ( is_admin() ) {
			$this->settings->hook();
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		}

		// Register widgets.
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
	}

	public static function get_instance() {
		if ( ! self::$instance ) {
			$class          = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}

	public function __get( $name ) {
		// On-demand classes.
		if ( 'rides' === $name ) {
			return $this->get_rides();
		}

		if ( 'routes' === $name ) {
			return $this->get_routes();
		}

		if ( isset( $this->{$name} ) ) {
			return $this->{$name};
		}

		return null;
	}

	public function get_api( $token = null ) {
		if ( ! $token ) {
			$token = $this->settings->get_default_token();
		}

		if ( empty( $this->api[ $token ] ) ) {
			require_once WPSTRAVA_PLUGIN_DIR . 'lib/API.class.php';
			$this->api[ $token ] = new WPStrava_API( $token );
		}

		return $this->api[ $token ];
	}

	public function get_rides() {
		if ( ! $this->rides ) {
			require_once WPSTRAVA_PLUGIN_DIR . 'lib/Rides.class.php';
			$this->rides = new WPStrava_Rides();
		}

		return $this->rides;
	}

	public function get_routes() {
		if ( ! $this->routes ) {
			require_once WPSTRAVA_PLUGIN_DIR . 'lib/Routes.class.php';
			$this->routes = new WPStrava_Routes();
		}
		return $this->routes;
	}

	public function register_scripts() {
		// Register a personalized stylesheet.
		wp_register_style( 'wp-strava-style', WPSTRAVA_PLUGIN_URL . 'css/wp-strava.css' );
	}

	public function register_widgets() {
		require_once WPSTRAVA_PLUGIN_DIR . 'lib/LatestRidesWidget.class.php';
		require_once WPSTRAVA_PLUGIN_DIR . 'lib/LatestMapWidget.class.php';
		register_widget( 'WPStrava_LatestRidesWidget' );
		register_widget( 'WPStrava_LatestMapWidget' );
	}
}
