<?php

class WPStrava {

	private static $instance = null;
	private $settings        = null;
	private $api             = array(); // Holds an array of APIs.
	private $activity        = null;
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
		if ( 'activity' === $name ) {
			return $this->get_activity();
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
			$this->api[ $token ] = new WPStrava_API( $token );
		}

		return $this->api[ $token ];
	}

	public function get_activity() {
		if ( ! $this->activity ) {
			$this->activity = new WPStrava_Activity();
		}

		return $this->activity;
	}

	public function get_routes() {
		if ( ! $this->routes ) {
			$this->routes = new WPStrava_Routes();
		}
		return $this->routes;
	}

	public function register_scripts() {
		// Register a personalized stylesheet.
		wp_register_style( 'wp-strava-style', WPSTRAVA_PLUGIN_URL . 'css/wp-strava.css' );
	}

	public function register_widgets() {
		register_widget( 'WPStrava_LatestActivitiesWidget' );
		register_widget( 'WPStrava_LatestMapWidget' );
	}
}
