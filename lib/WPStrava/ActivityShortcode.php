<?php
/**
 * Activity Shortcode [activity].
 * @package WPStrava
 */

/**
 * Activity Shortcode class (converted from Ride).
 *
 * @author Justin Foell <justin@foell.org>
 * @since  1.0
 */
class WPStrava_ActivityShortcode {

	/**
	 * Whether or not to enqueue styles (if shortcode is present).
	 *
	 * @var boolean
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.0
	 */
	private $add_script = false;

	/**
	 * Constructor (converted from static init()).
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.0
	 */
	public function __construct() {
		add_shortcode( 'ride', array( $this, 'handler' ) ); // @deprecated 1.1
		add_shortcode( 'activity', array( $this, 'handler' ) );
		add_action( 'wp_footer', array( $this, 'print_scripts' ) );
	}

	/**
	 * Shortcode handler for [activity].
	 *
	 * [activity id=id som=metric map_width="100%" map_height="400px" markers=false]
	 *
	 * @param array $atts Array of attributes (id, map_width, etc.).
	 * @return string Shortcode output
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.0
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

		$atts = shortcode_atts( $defaults, $atts, 'activity' );

		/* Make sure boolean values are actually boolean
		 * @see https://wordpress.stackexchange.com/a/119299
		 */
		$atts['markers']    = filter_var( $atts['markers'], FILTER_VALIDATE_BOOLEAN );
		$atts['image_only'] = filter_var( $atts['image_only'], FILTER_VALIDATE_BOOLEAN );

		$strava_som       = WPStrava_SOM::get_som( $atts['som'] );
		$activity         = WPStrava::get_instance()->activity;
		$activity_details = null;

		try {
			$activity_details = $activity->get_activity( $atts['athlete_token'], $atts['id'] );
		} catch ( WPStrava_Exception $e ) {
			return $e->to_html();
		}

		//sanitize width & height
		$map_width  = str_replace( '%', '', $atts['map_width'] );
		$map_height = str_replace( '%', '', $atts['map_height'] );
		$map_width  = str_replace( 'px', '', $map_width );
		$map_height = str_replace( 'px', '', $map_height );

		if ( $activity_details ) {
			$activity_output = '<div id="activity-header-' . $atts['id'] . '" class="wp-strava-activity-container">';
			if ( ! $atts['image_only'] ) {

				$strava_activitytype = WPStrava_ActivityType::get_type_group( $activity_details->type );
				$avg_speed           = '';
				$max_speed           = '';
				$speed_label         = '';
				$avg_title           = '<th>' . __( 'Average Speed', 'wp-strava' ) . '</th>';
				$max_title           = '<th>' . __( 'Max Speed', 'wp-strava' ) . '</th>';

				switch ( $strava_activitytype ) {
					case WPStrava_ActivityType::TYPE_GROUP_PACE:
						$avg_speed   = '<td>' . $strava_som->pace( $activity_details->average_speed ) . '</td>';
						$max_speed   = '<td>' . $strava_som->pace( $activity_details->max_speed ) . '</td>';
						$speed_label = '<td>' . $strava_som->get_pace_label() . '</td>';
						break;
					case WPStrava_ActivityType::TYPE_GROUP_SPEED:
						$avg_speed   = '<td>' . $strava_som->speed( $activity_details->average_speed ) . '</td>';
						$max_speed   = '<td>' . $strava_som->speed( $activity_details->max_speed ) . '</td>';
						$speed_label = '<td>' . $strava_som->get_speed_label() . '</td>';
						break;
					case WPStrava_ActivityType::TYPE_GROUP_PACE:
						$avg_speed   = '<td>' . $strava_som->swimpace( $activity_details->average_speed ) . '</td>';
						$max_speed   = '<td>' . $strava_som->swimpace( $activity_details->max_speed ) . '</td>';
						$speed_label = '<td>' . $strava_som->get_swimpace_label() . '</td>';
						break;
					default:
						$avg_title = '';
						$max_title = '';
						break;
				}

				$activity_output .= '
				<table id="activity-details-table">
					<thead>
						<tr>
							<th>' . __( 'Elapsed Time', 'wp-strava' ) . '</th>
							<th>' . __( 'Moving Time', 'wp-strava' ) . '</th>
							<th>' . __( 'Distance', 'wp-strava' ) . '</th>
							' . $avg_title . '
							' . $max_title . '
							<th>' . __( 'Elevation Gain', 'wp-strava' ) . '</th>
						</tr>
					</thead>
					<tbody>
						<tr class="activity-details-table-info">
							<td>' . $strava_som->time( $activity_details->elapsed_time ) . '</td>
							<td>' . $strava_som->time( $activity_details->moving_time ) . '</td>
							<td>' . $strava_som->distance( $activity_details->distance ) . '</td>
							' . $avg_speed . '
							' . $max_speed . '
							<td>' . $strava_som->elevation( $activity_details->total_elevation_gain ) . '</td>
						</tr>
						<tr class="activity-details-table-units">
							<td>' . $strava_som->get_time_label() . '</td>
							<td>' . $strava_som->get_time_label() . '</td>
							<td>' . $strava_som->get_distance_label() . '</td>
							' . $speed_label . '
							' . $speed_label . '
							<td>' . $strava_som->get_elevation_label() . '</td>
						</tr>
					</tbody>
				</table>
				';
			}
			$activity_output .= '<a title="' . $activity_details->name . '" href="' . WPStrava_Activity::ACTIVITIES_URL . $activity_details->id . '">' .
				WPStrava_StaticMap::get_image_tag( $activity_details, $map_height, $map_width, $atts['markers'] ) .
				'</a>
			</div>';
			return $activity_output;
		} // End if( $activity_details ).
	}

	/**
	 * Enqueue style if shortcode is being used.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.0
	 */
	public function print_scripts() {
		if ( $this->add_script ) {
			wp_enqueue_style( 'wp-strava-style' );
		}
	}
}
