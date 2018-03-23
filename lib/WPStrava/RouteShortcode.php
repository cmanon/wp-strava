<?php

/**
 * Route Shortcode.
 *
 * @author Daniel Lintott
 * @since  1.3.0
 */
class WPStrava_RouteShortcode {
	private static $add_script;

	public static function init() {
		add_action( 'wp_footer', array( __CLASS__, 'print_scripts' ) );
	}

	// Shortcode handler function
	// [route id=id som=metric map_width="100%" map_height="400px" markers=false]
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

		$strava_som    = WPStrava_SOM::get_som( $atts['som'] );
		$route         = WPStrava::get_instance()->routes;
		$route_details = $route->get_route( $atts['id'] );

		if ( is_wp_error( $route_details ) ) {
			if ( WPSTRAVA_DEBUG ) {
				return '<pre>' . print_r( $route_details, true ) . '</pre>'; // @codingStandardsIgnoreLine
			} else {
				return $route_details->get_error_message();
			}
		}

		//sanitize width & height
		$map_width  = str_replace( '%', '', $atts['map_width'] );
		$map_height = str_replace( '%', '', $atts['map_height'] );
		$map_width  = str_replace( 'px', '', $map_width );
		$map_height = str_replace( 'px', '', $map_height );

		if ( $route_details ) {
			return '
				<div id="activity-header-' . $atts['id'] . '" class="wp-strava-activity-container">
					<table id="activity-details-table">
						<thead>
							<tr>
								<th>' . __( 'Est. Moving Time', 'wp-strava' ) . '</th>
								<th>' . __( 'Distance', 'wp-strava' ) . '</th>
								<th>' . __( 'Elevation Gain', 'wp-strava' ) . '</th>
							</tr>
						</thead>
						<tbody>
							<tr class="activity-details-table-info">
								<td>' . $strava_som->time( $route_details->estimated_moving_time ) . '</td>
								<td>' . $strava_som->distance( $route_details->distance ) . '</td>
								<td>' . $strava_som->elevation( $route_details->elevation_gain ) . '</td>
							</tr>
							<tr class="activity-details-table-units">
								<td>' . $strava_som->get_time_label() . '</td>
								<td>' . $strava_som->get_distance_label() . '</td>
								<td>' . $strava_som->get_elevation_label() . '</td>
							</tr>
						</tbody>
					</table>
				<a title="' . $route_details->name . '" href="' . WPStrava_Routes::ROUTES_URL . $route_details->id . '">' .
				WPStrava_StaticMap::get_image_tag( $route_details, $map_height, $map_width, $atts['markers'] ) .
				'</a>
			</div>';
		} // End if( $route_details ).
	}

	public static function print_scripts() {
		if ( self::$add_script ) {
			wp_enqueue_style( 'wp-strava-style' );
		}
	}
}

// Initialize short code
WPStrava_RouteShortcode::init();
