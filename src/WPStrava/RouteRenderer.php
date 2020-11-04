<?php
/*
 * RouteRenderer has all the markup for the Route Block & Shortcode.
 */
class WPStrava_RouteRenderer {

	/**
	 * Get the HTML for a single route.
	 *
	 * @param array $atts
	 * @return string HTML for an route.
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.5.0
	 */
	public function get_html( $atts ) {
		$defaults = array(
			'id'         => 0,
			'som'        => WPStrava::get_instance()->settings->som,
			'map_width'  => '480',
			'map_height' => '320',
			'client_id'  => WPStrava::get_instance()->settings->get_default_id(),
			'markers'    => false,
			'image_only' => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		/* Make sure boolean values are actually boolean
		 * @see https://wordpress.stackexchange.com/a/119299
		 */
		$atts['markers']    = filter_var( $atts['markers'], FILTER_VALIDATE_BOOLEAN );
		$atts['image_only'] = filter_var( $atts['image_only'], FILTER_VALIDATE_BOOLEAN );

		$route         = WPStrava::get_instance()->routes;
		$route_details = null;

		try {
			$route_details = $route->get_route( $atts['id'] );
		} catch ( WPStrava_Exception $e ) {
			return $e->to_html();
		}

		$route_output = '';
		if ( $route_details ) {
			$route_output = '<div id="activity-header-' . $atts['id'] . '" class="wp-strava-activity-container">';
			if ( ! $atts['image_only'] ) {
				$route_output .= $this->get_table( $route_details, $atts['som'] );
			}

			// Sanitize width & height.
			$map_width  = str_replace( '%', '', $atts['map_width'] );
			$map_height = str_replace( '%', '', $atts['map_height'] );
			$map_width  = str_replace( 'px', '', $map_width );
			$map_height = str_replace( 'px', '', $map_height );

			$route_output .= '<a title="' . $route_details->name . '" href="' . WPStrava_Routes::ROUTES_URL . $route_details->id . '">' .
				WPStrava_StaticMap::get_image_tag( $route_details, $map_height, $map_width, $atts['markers'] ) .
				'</a>
			</div>';
		} // End if( $route_details ).
		return $route_output;

	}

	/**
	 * The the route details in in HTML table.
	 *
	 * @param stdClass $route_details route details from the route class.
	 * @param string $som System of measure (english/metric).
	 * @return string HTML Table of route details.
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.7.0
	 */
	private function get_table( $route_details, $som ) {
		$strava_som = WPStrava_SOM::get_som( $som );

		$elevation_title = '<th>' . __( 'Elevation Gain', 'wp-strava' ) . '</th>';
		$elevation       = '<td data-label="' . __( 'Elevation Gain', 'wp-strava' ) . '">
								<div class="activity-details-table-info">' . $strava_som->elevation( $route_details->elevation_gain ) . '</div>
								<div class="activity-details-table-units">' . $strava_som->get_elevation_label() . '</div>
							</td>';

		if ( WPStrava::get_instance()->settings->hide_elevation ) {
			$elevation_title = '';
			$elevation       = '';
		}

		return '
			<table class="activity-details-table">
				<thead>
					<tr>
						<th>' . __( 'Est. Moving Time', 'wp-strava' ) . '</th>
						<th>' . __( 'Distance', 'wp-strava' ) . '</th>
						' . $elevation_title . '
					</tr>
				</thead>
				<tbody>
					<tr>
						<td data-label="' . __( 'Elapsed Time', 'wp-strava' ) . '">
							<div class="activity-details-table-info">' . $strava_som->time( $route_details->estimated_moving_time ) . '</div>
							<div class="activity-details-table-units">' . $strava_som->get_time_label() . '</div>
						</td>
						<td data-label="' . __( 'Distance', 'wp-strava' ) . '">
							<div class="activity-details-table-info">' . $strava_som->distance( $route_details->distance ) . '</div>
							<div class="activity-details-table-units">' . $strava_som->get_distance_label() . '</div>
						</td>
						' . $elevation . '
					</tr>
				</tbody>
			</table>
		';
	}

}
