<?php

class WPStrava_ActivityShortcode {
	private static $add_script;

	public static function init() {
		add_shortcode( 'ride', array( __CLASS__, 'handler' ) ); // @deprecated 1.1
		add_shortcode( 'activity', array( __CLASS__, 'handler' ) );
		add_action( 'wp_footer', array( __CLASS__, 'print_scripts' ) );
	}

	// Shortcode handler function
	// [activity id=id som=metric map_width="100%" map_height="400px" markers=false]
	public static function handler( $atts ) {
		self::$add_script = true;

		$defaults = array(
			'id'            => 0,
			'som'           => WPStrava::get_instance()->settings->som,
			'map_width'     => '480',
			'map_height'    => '320',
			'athlete_token' => WPStrava::get_instance()->settings->get_default_token(),
			'markers'       => false,
		);

		$atts = shortcode_atts( $defaults, $atts );

		$strava_som       = WPStrava_SOM::get_som( $atts['som'] );
		$activity         = WPStrava::get_instance()->activity;
		$activity_details = $activity->get_activity( $atts['athlete_token'], $atts['id'] );

		if ( is_wp_error( $activity_details ) ) {
			if ( WPSTRAVA_DEBUG ) {
				return '<pre>' . print_r( $activity_details, true ) . '</pre>'; // @codingStandardsIgnoreLine
			} else {
				return $activity_details->get_error_message();
			}
		}

		//sanitize width & height
		$map_width  = str_replace( '%', '', $atts['map_width'] );
		$map_height = str_replace( '%', '', $atts['map_height'] );
		$map_width  = str_replace( 'px', '', $map_width );
		$map_height = str_replace( 'px', '', $map_height );

		if ( $activity_details ) {
			return '
				<div id="activity-header-' . $atts['id'] . '" class="wp-strava-activity-container">
					<table id="activity-details-table">
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
							<tr class="activity-details-table-info">
								<td>' . $strava_som->time( $activity_details->elapsed_time ) . '</td>
								<td>' . $strava_som->time( $activity_details->moving_time ) . '</td>
								<td>' . $strava_som->distance( $activity_details->distance ) . '</td>
								<td>' . $strava_som->speed( $activity_details->average_speed ) . '</td>
								<td>' . $strava_som->speed( $activity_details->max_speed ) . '</td>
								<td>' . $strava_som->elevation( $activity_details->total_elevation_gain ) . '</td>
							</tr>
							<tr class="activity-details-table-units">
								<td>' . $strava_som->get_time_label() . '</td>
								<td>' . $strava_som->get_time_label() . '</td>
								<td>' . $strava_som->get_distance_label() . '</td>
								<td>' . $strava_som->get_speed_label() . '</td>
								<td>' . $strava_som->get_speed_label() . '</td>
								<td>' . $strava_som->get_elevation_label() . '</td>
							</tr>
						</tbody>
					</table>
					<a title="' . $activity_details->name . '" href="' . WPStrava_Activity::ACTIVITIES_URL . $activity_details->id . '">' .
					WPStrava_StaticMap::get_image_tag( $activity_details, $map_height, $map_width, $atts['markers'] ) .
					'</a>
				</div>';
		} // End if( $activity_details ).
	}

	public static function print_scripts() {
		if ( self::$add_script ) {
			wp_enqueue_style( 'wp-strava-style' );
		}
	}
}

// Initialize short code
WPStrava_ActivityShortcode::init();
