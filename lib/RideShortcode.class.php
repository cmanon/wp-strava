<?php

class WPStrava_RideShortcode {
	static $add_script;

	static function init() {
		add_shortcode('ride', array(__CLASS__, 'handler'));

		add_action('init', array(__CLASS__, 'registerScripts'));
		add_action('wp_footer', array(__CLASS__, 'printScripts'));
	}

	// Shortcode handler function
	// [ride id=id som=metric efforts=false threshold=5 map-width="100%" map-height="400px"] tag
	function handler($atts) {
		self::$add_script = true;

		$token = get_option('strava_token');
		
		if($token) {
			$pairs = array(
				'id' => 0,
				'som' => "metric",
				'efforts' => false,
				'threshold' => 0,
				'map_width' => "100%",
				'map_height' => "400px"
			);
			
			extract(shortcode_atts($pairs, $atts));

			if (isset($som)) {
				$strava_som = $som;
			} else {
				$strava_som = get_option('strava_som_option', 'metric');
			}
			
			$ride = WPStrava::get_instance()->rides;
			$rideDetails = $ride->getRide($id);
			$rideCoordinates = $ride->getRideMap($id, $token, $efforts, $threshold);
			
			if ($strava_som == "metric") {
				$units = array(
					'elapsedTime' => __('hours','wp-strava'),
					'movingTime' => __('hours','wp-strava'),
					'distance' => __('km','wp-strava'),
					'averageSpeed' => __('km/h','wp-strava'),
					//'maximumSpeed' => __('km/h','wp-strava'),
					'elevationGain' => __('meters','wp-strava')
				);
			} elseif ($strava_som == "english") {
				$units = array(
					'elapsedTime' => __('hours','wp-strava'),
					'movingTime' => __('hours','wp-strava'),
					'distance' => __('miles','wp-strava'),
					'averageSpeed' => __('miles/h','wp-strava'),
					//'maximumSpeed' => __('miles/h','wp-strava'),
					'elevationGain' => __('feet','wp-strava')
				);
			}
			
			if($rideCoordinates) {
				return "
					<div id='ride-header-{$id}' class='table'>
						<table id='ride-details-table'>
							<thead>
								<tr>
									<th>Elapsed Time</th>
									<th>Moving Time</th>
									<th>Distance</th>
									<th>Average Speed</th>
									<th>Elevation Gain</th>
								</tr>
							</thead>
							<tbody>
								<tr class='ride-details-table-info'>
									<td>{$rideDetails['elapsedTime']}</td>
									<td>{$rideDetails['movingTime']}</td>
									<td>{$rideDetails['distance']}</td>
									<td>{$rideDetails['averageSpeed']}</td>
									<td>{$rideDetails['elevationGain']}</td>
								</tr>
								<tr class='ride-details-table-units'>
									<td>{$units['elapsedTime']}</td>
									<td>{$units['movingTime']}</td>
									<td>{$units['distance']}</td>
									<td>{$units['averageSpeed']}</td>
									<td>{$units['elevationGain']}</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div id='{$id}' class='map' style='width: {$map_width}; height: {$map_height}; border: 1px solid lightgrey;'></div>
					<script type='text/javascript'>
						if (window.coordinates === undefined) {
							window.coordinates = [];
						}
						window.coordinates[{$id}] = eval({$rideCoordinates});
					</script>
				";
			}
		} else {
			return _e('Please first get your strava token using the settings wp strava page.', 'wp-strava');
		}
	} // handler

	static function registerScripts() {
		wp_register_style('wp-strava-style', plugins_url('css/wp-strava.css', __FILE__));

		wp_register_script('wp-strava-script', plugins_url('js/wp-strava.js', __FILE__), array('jquery'), '1.0', true);
		wp_register_script('google-maps', 'http://maps.google.com/maps/api/js?sensor=false');
	}

	static function printScripts() {
		if (self::$add_script) {
			wp_enqueue_style('wp-strava-style');
			wp_enqueue_script('jquery');

			wp_print_scripts('google-maps');
			wp_print_scripts('wp-strava-script');
		}
	}
}

// Initialize short code
WPStrava_RideShortcode::init();
