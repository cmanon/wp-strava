<?php
/*
 * ActivityRenderer has all the markup for the Activity Block & Shortcode.
 */
class WPStrava_ActivityRenderer {

	/**
	 * Get the HTML for a single activity.
	 *
	 * @param array $atts
	 * @return string HTML for an activity.
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.2.0
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

		$activity         = WPStrava::get_instance()->activity;
		$activity_details = null;

		try {
			$activity_details = $activity->get_activity( $atts['client_id'], $atts['id'] );
		} catch ( WPStrava_Exception $e ) {
			return $e->to_html();
		}

		$activity_output = '';
		if ( $activity_details ) {
			$activity_output .= '<div id="activity-header-' . $atts['id'] . '" class="wp-strava-activity-container">';
			if ( ! $atts['image_only'] ) {
				$activity_output .= $this->get_table( $activity_details, $atts['som'] );
			}

			// Sanitize width & height.
			$map_width  = str_replace( '%', '', $atts['map_width'] );
			$map_height = str_replace( '%', '', $atts['map_height'] );
			$map_width  = str_replace( 'px', '', $map_width );
			$map_height = str_replace( 'px', '', $map_height );

			$activity_output .= $activity->get_activity_link(
				$activity_details->id,
				WPStrava_StaticMap::get_map()->get_image_tag( $activity_details, $map_height, $map_width, $atts['markers'], $activity_details->name ),
				$activity_details->name
			);

			if ( ! empty( $activity_details->description ) ) {
				$activity_output .= '<div class="wp-strava-activity-description">' . esc_html( $activity_details->description ) . '</div>';
			}

			$activity_output .= '</div>';
		} // End if( $activity_details ).
		return $activity_output;
	}

	/**
	 * The the activity details in in HTML table.
	 *
	 * @param stdClass $activity_details Activity details from the activity class.
	 * @param string $som System of measure (english/metric).
	 * @return string HTML Table of activity details.
	 * @author Justin Foell <justin@foell.org>
	 * @author Sebastian Erb <mail@sebastianerb.com>
	 * @since  1.7.0
	 */
	private function get_table( $activity_details, $som ) {
		$strava_som          = WPStrava_SOM::get_som( $som );
		$strava_activitytype = WPStrava_ActivityType::get_type_group( $activity_details->type );
		$avg_title           = '<th>' . __( 'Average Speed', 'wp-strava' ) . '</th>';
		$max_title           = '<th>' . __( 'Max Speed', 'wp-strava' ) . '</th>';
		$elevation_title     = '<th>' . __( 'Elevation Gain', 'wp-strava' ) . '</th>';
		$elevation           = '<td data-label="' . __( 'Elevation Gain', 'wp-strava' ) . '">
									<div class="activity-details-table-info">' . $strava_som->elevation( $activity_details->total_elevation_gain ) . '</div>
									<div class="activity-details-table-units">' . $strava_som->get_elevation_label() . '</div>
								</td>';
		$avg_speed           = '';
		$max_speed           = '';
		$calories_title      = '';
		$calories            = '';

		switch ( $strava_activitytype ) {
			case WPStrava_ActivityType::TYPE_GROUP_PACE:
				$avg_speed = '<td data-label="' . __( 'Average Speed', 'wp-strava' ) . '">
								<div class="activity-details-table-info">' . $strava_som->pace( $activity_details->average_speed ) . '</div>
								<div class="activity-details-table-units">' . $strava_som->get_pace_label() . '</div>
							</td>';
				$max_speed = '<td data-label="' . __( 'Max Speed', 'wp-strava' ) . '">
								<div class="activity-details-table-info">' . $strava_som->pace( $activity_details->max_speed ) . '</div>
								<div class="activity-details-table-units">' . $strava_som->get_pace_label() . '</div>
							</td>';
				break;
			case WPStrava_ActivityType::TYPE_GROUP_WATER:
				$avg_speed = '<td data-label="' . __( 'Average Speed', 'wp-strava' ) . '">
								<div class="activity-details-table-info">' . $strava_som->swimpace( $activity_details->average_speed ) . '</div>
								<div class="activity-details-table-units">' . $strava_som->get_swimpace_label() . '</div>
							</td>';
				$max_speed = '<td data-label="' . __( 'Max Speed', 'wp-strava' ) . '">
								<div class="activity-details-table-info">' . $strava_som->swimpace( $activity_details->max_speed ) . '</div>
								<div class="activity-details-table-units">' . $strava_som->get_swimpace_label() . '</div>
							</td>';
				break;
			case WPStrava_ActivityType::TYPE_GROUP_SPEED:
			default:
				$avg_speed = '<td data-label="' . __( 'Average Speed', 'wp-strava' ) . '">
								<div class="activity-details-table-info">' . $strava_som->speed( $activity_details->average_speed ) . '</div>
								<div class="activity-details-table-units">' . $strava_som->get_speed_label() . '</div>
							</td>';
				$max_speed = '<td data-label="' . __( 'Max Speed', 'wp-strava' ) . '">
								<div class="activity-details-table-info">' . $strava_som->speed( $activity_details->max_speed ) . '</div>
								<div class="activity-details-table-units">' . $strava_som->get_speed_label() . '</div>
							</td>';
				break;
		}

		if ( WPStrava::get_instance()->settings->hide_elevation ) {
			$elevation_title = '';
			$elevation       = '';
		}

		if ( $activity_details->calories ) {
			$calories_title  = '<th>' . __( 'Calories Burned', 'wp-strava' ) . '</th>';
			$calories        = '<td data-label="' . __( 'Calories Burned', 'wp-strava' ) . '">
									<div class="activity-details-table-info">' . $strava_som->calories( $activity_details->calories ) . '</div>
									<div class="activity-details-table-units">' . $strava_som->get_calories_label() . '</div>
								</td>';
		}

		return '
			<table class="activity-details-table">
				<thead>
					<tr>
						<th>' . __( 'Elapsed Time', 'wp-strava' ) . '</th>
						<th>' . __( 'Moving Time', 'wp-strava' ) . '</th>
						<th>' . __( 'Distance', 'wp-strava' ) . '</th>
						' . $avg_title . '
						' . $max_title . '
						' . $elevation_title . '
						' . $calories_title . '
					</tr>
				</thead>
				<tbody>
					<tr>
						<td data-label="' . __( 'Elapsed Time', 'wp-strava' ) . '">
							<div class="activity-details-table-info">' . $strava_som->time( $activity_details->elapsed_time ) . '</div>
							<div class="activity-details-table-units">' . $strava_som->get_time_label() . '</div>
						</td>
						<td data-label="' . __( 'Moving Time', 'wp-strava' ) . '">
							<div class="activity-details-table-info">' . $strava_som->time( $activity_details->moving_time ) . '</div>
							<div class="activity-details-table-units">' . $strava_som->get_time_label() . '</div>
						</td>
						<td data-label="' . __( 'Distance', 'wp-strava' ) . '">
							<div class="activity-details-table-info">' . $strava_som->distance( $activity_details->distance ) . '</div>
							<div class="activity-details-table-units">' . $strava_som->get_distance_label() . '</div>
						</td>
						' . $avg_speed . '
						' . $max_speed . '
						' . $elevation . '
						' . $calories . '
					</tr>
				</tbody>
			</table>
		';
	}
}
