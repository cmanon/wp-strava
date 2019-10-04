<?php
/*
 * Main Strava plugin class.
 */
class WPStrava {

	/**
	 * Instance of this class (singleton).
	 * @var WPStrava
	 */
	private static $instance = null;

	/**
	 * Settings object to access settings.
	 * @var WPStrava_Settings
	 */
	private $settings = null;

	/**
	 * Authorization object.
	 * @var WPStrava_Auth
	 */
	private $auth = null;

	/**
	 * Array of WPStrava_API objects (one for each athlete).
	 *
	 * @var array
	 */
	private $api = array();

	/**
	 * Activity object to get activities.
	 * @var WPStrava_Activity
	 */
	private $activity = null;

	/**
	 * Route object to get routes.
	 * @var WPStrava_Routes
	 */
	private $routes = null;

	/**
	 * Private constructor (singleton).
	 */
	private function __construct() {
		$this->settings = new WPStrava_Settings();
		$this->auth     = WPStrava_Auth::get_auth( 'refresh' );
	}

	/**
	 * Get a singleton instance.
	 *
	 * @return WPStrava
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			$class          = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}

	/**
	 * Function to install hooks at WP runtime.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.0.0
	 */
	public function hook() {
		$this->auth->hook();

		if ( is_admin() ) {
			$this->settings->hook();
		} else {
			add_action( 'init', array( $this, 'register_shortcodes' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		}

		// Register widgets.
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );

	}

	/**
	 * Magic method to access activity, routes, settings, etc.
	 *
	 * @param string $name One of activity, routes, settings.
	 * @return mixed|null
	 */
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

	/**
	 * Get an API object for the given athlete token.
	 *
	 * @param string $token Athlete token.
	 * @return WPStrava_API
	 */
	public function get_api( $id = null ) {
		if ( ! $id ) {
			$id = $this->settings->get_default_id();
		}

		if ( empty( $this->api[ $id ] ) ) {
			$this->api[ $id ] = new WPStrava_API( $id );
		}

		return $this->api[ $id ];
	}

	/**
	 * Get the activity object.
	 *
	 * @return WPStrava_Activity
	 */
	public function get_activity() {
		if ( ! $this->activity ) {
			$this->activity = new WPStrava_Activity();
		}

		return $this->activity;
	}

	/**
	 * Get the routes object.
	 *
	 * @return WPStrava_Routes
	 */
	public function get_routes() {
		if ( ! $this->routes ) {
			$this->routes = new WPStrava_Routes();
		}
		return $this->routes;
	}

	/**
	 * Register the wp-strava stylesheet.
	 */
	public function register_scripts() {
		// Register a personalized stylesheet.
		wp_register_style( 'wp-strava-style', WPSTRAVA_PLUGIN_URL . 'css/wp-strava.css' );
	}

	/**
	 * Register the widgets.
	 */
	public function register_widgets() {
		register_widget( 'WPStrava_LatestActivitiesWidget' );
		register_widget( 'WPStrava_LatestMapWidget' );
	}

	/**
	 * Register the shortcodes.
	 */
	public function register_shortcodes() {
		// Initialize short code classes.
		new WPStrava_ActivityShortcode();
		new WPStrava_LatestActivitiesShortcode();
		new WPStrava_RouteShortcode();
	}
}
