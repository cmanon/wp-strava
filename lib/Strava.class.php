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

	public static function getInstance() {
		if ( ! self::$instance )
			self::$instance = new WPStrava();
		return self::$instance;
	}
	
	// The handler to the ajax call, we will avoid this if Strava support jsonp request and we can do it
	// the parsing directly on the jQuery ajax call, the returned text will be enclosed in the $response variable.
	function strava_request_handler($strava_search_option, $strava_search_id, $strava_som_option, $quantity) {
		$response = "";
	
		//Check if the username is empty.
		if (empty($strava_search_id)) {
			$response .= __("Please configure the Strava search id on the widget options.", "wp-strava");
		} else {
			$strava_rides = new Rides(); 
			$strava_rides->getLatestRides($strava_search_option, $strava_search_id, $quantity);
			$rides_details = $strava_rides->getRidesDetails($strava_som_option);
		
			if ($strava_som_option == "metric") {
				$units = array(
					'elapsedTime' => __('hours','wp-strava'),
					'movingTime' => __('hours','wp-strava'),
					'distance' => __('km','wp-strava'),
					'averageSpeed' => __('km/h','wp-strava'),
					'maximumSpeed' => __('km/h','wp-strava'),
					'elevationGain' => __('meters','wp-strava')
							   );
			} elseif ($strava_som_option == "english") {
				$units = array(
					'elapsedTime' => __('hours','wp-strava'),
					'movingTime' => __('hours','wp-strava'),
					'distance' => __('miles','wp-strava'),
					'averageSpeed' => __('miles/h','wp-strava'),
					'maximumSpeed' => __('miles/h','wp-strava'),
					'elevationGain' => __('feet','wp-strava')
							   );
			}
		
			$response .= "<ul id='rides'>";
			foreach($rides_details as $ride) {
				$response .= "<li class='ride'>";
				$response .= "<a href='" . $strava_rides->ridesLinkUrl . $ride['id'] . "' >" . $ride['name'] . "</a>";
				$response .= "<div class='ride-item'>";
				$response .= __("On ", "wp-strava") . $ride['startDate'];
				if ($strava_search_option == "club") {
					$response .= " <a href='" . $strava_rides->athletesLinkUrl . $ride['athleteId'] . "'>" . $ride['athleteName'] . "</a>";
				}
				$response .= __(" rode ", "wp-strava") . $ride['distance'] . " " . $units['distance'];
				$response .= __(" during ", "wp-strava") . $ride['elapsedTime'] . " " . $units['elapsedTime'];
				$response .= __(" climbing ", "wp-strava") . $ride['elevationGain'] . " " . $units['elevationGain'] . ".";			
				$response .= "</div>";
				$response .= "</li>";
			}
			$response .= "</ul>";
		}
		return $response;
	} // Function strava_request_handler

}