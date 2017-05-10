<?php

class WPStrava_RideShortcode {
	private static $add_script;

	public static function init() {
		add_shortcode( 'ride', array( __CLASS__, 'handler' ) );
		add_shortcode( 'activity', array( __CLASS__, 'handler' ) );
		add_action( 'wp_footer', array( __CLASS__, 'printScripts' ) );
	}

	// Shortcode handler function
	// [ride id=id som=metric map_width="100%" map_height="400px"]
	public static function handler($atts) {
		self::$add_script = true;

		$defaults = array(
			'id' => 0,
			'som' => WPStrava::get_instance()->settings->som,
			'map_width' => '480',
			'map_height' => '320',
		);
			
		extract(shortcode_atts($defaults, $atts));
		
		$strava_som = WPStrava_SOM::get_som( $som );
		$strava_ride = WPStrava::get_instance()->rides;
		$rideDetails = $strava_ride->getRide( $id );

		//sanitize width & height
		$map_width = str_replace( '%', '', $map_width );
		$map_height = str_replace( '%', '', $map_height );
		$map_width = str_replace( 'px', '', $map_width );
		$map_height = str_replace( 'px', '', $map_height );

		if( $rideDetails ) {
			return '
				<div id="ride-header-' . $id . '" class="wp-strava-ride-container">
					<table id="ride-details-table">
						<thead>
							<tr>
								<th>' . __( 'Elapsed Time', 'wp-strava' ) . '</th>
								<th>' . __( 'Moving Time', 'wp-strava' ) . '</th>
								<th>' . __( 'Distance', 'wp-strava' ) . '</th>
								<th>' . __( 'Average Speed', 'wp-strava' ) . '</th>
								<th>' . __( 'Max Speed', 'wp-strava' ) . '</th>
								<th>' . __( 'Elevation Gain', 'wp-strava' ) . '</th>
							</tr>
						</thead>
						<tbody>
							<tr class="ride-details-table-info">
								<td>' . $strava_som->time( $rideDetails->elapsed_time ) . '</td>
								<td>' . $strava_som->time( $rideDetails->moving_time ) . '</td>
								<td>' . $strava_som->distance( $rideDetails->distance ) . '</td>
								<td>' . $strava_som->speed( $rideDetails->average_speed ) . '</td>
								<td>' . $strava_som->speed( $rideDetails->max_speed ) . '</td>
								<td>' . $strava_som->elevation( $rideDetails->total_elevation_gain ) . '</td>
							</tr>
							<tr class="ride-details-table-units">
								<td>' . $strava_som->get_time_label() . '</td>
								<td>' . $strava_som->get_time_label() . '</td>
								<td>' . $strava_som->get_distance_label() . '</td>
								<td>' . $strava_som->get_speed_label() . '</td>
								<td>' . $strava_som->get_speed_label() . '</td>
								<td>' . $strava_som->get_elevation_label() . '</td>
							</tr>
						</tbody>
					</table>' .
				WPStrava_StaticMap::get_image_tag( $rideDetails, $map_height, $map_width ) . 
				'</div>';				
		}
	} // handler

	public static function printScripts() {
		if (self::$add_script) {
			wp_enqueue_style('wp-strava-style');

			//wp_print_scripts('google-maps');
			//wp_print_scripts('wp-strava-script');
		}
	}
}

// Initialize short code
WPStrava_RideShortcode::init();
