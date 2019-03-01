<?php
/**
 * Route Shortcode [route].
 * @package WPStrava
 */

/**
 * Route Shortcode class.
 *
 * @author Daniel Lintott
 * @since  1.3.0
 */
class WPStrava_RouteShortcode {

	/**
	 * Whether or not to enqueue styles (if shortcode is present).
	 *
	 * @var boolean
	 * @author Daniel Lintott
	 * @since  1.3.0
	 */
	private $add_script = false;

	/**
	 * Constructor (converted from static init()).
	 *
	 * @author Daniel Lintott
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.6.0
	 */
	public function __construct() {
		add_shortcode( 'route', array( $this, 'handler' ) );
		add_action( 'wp_footer', array( $this, 'print_scripts' ) );
	}

	/**
	 * Shortcode handler for [route].
	 *
	 * [route id=id som=metric map_width="100%" map_height="400px" markers=false]
	 *
	 * @param array $atts Array of attributes (id, map_width, etc.).
	 * @return string Shortcode output
	 * @author Daniel Lintott
	 * @since  1.3.0
	 */
	public function handler( $atts ) {
		$this->add_script = true;

		$defaults = array(
			'id'            => 0,
			'som'           => WPStrava::get_instance()->settings->som,
			'map_width'     => '480',
			'map_height'    => '320',
			'athlete_token' => WPStrava::get_instance()->settings->get_default_token(),
			'markers'       => false,
			'image_only'    => false,
		);

		$atts = shortcode_atts( $defaults, $atts, 'route' );

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
	 * @param string $route_details route details from the route class.
	 * @param string $som System of measure (english/metric).
	 * @return string HTML Table of route details.
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.7.0
	 */
	private function get_table( $route_details, $som ) {
		$strava_som = WPStrava_SOM::get_som( $som );
		return '
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
		';
	}

	/**
	 * Enqueue style if shortcode is being used.
	 *
	 * @author Daniel Lintott
	 * @since  1.3.0
	 */
	public function print_scripts() {
		if ( $this->add_script ) {
			wp_enqueue_style( 'wp-strava-style' );
		}
	}
}
