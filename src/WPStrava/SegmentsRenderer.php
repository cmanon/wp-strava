<?php
/**
 * Segments Renderer.
 * @package WPStrava
 */

/**
 * Segments class for shortcode and widget.
 *
 * @author Justin Foell <justin@foell.org>
 * @since  2.9.0
 */
class WPStrava_SegmentsRenderer {

	/**
	 * Get the HTML for a single segment.
	 *
	 * @param array $atts
	 * @return string HTML for an segment.
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.9.0
	 */
	public function get_html( $atts ) {
		$defaults = array(
			'id'         => 0,
			'som'        => WPStrava::get_instance()->settings->som,
			'map_width'  => '480',
			'map_height' => '320',
			'client_id'  => WPStrava::get_instance()->settings->get_default_id(),
			'markers'    => true,
			'image_only' => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		/* Make sure boolean values are actually boolean
		 * @see https://wordpress.stackexchange.com/a/119299
		 */
		$atts['markers']    = filter_var( $atts['markers'], FILTER_VALIDATE_BOOLEAN );
		$atts['image_only'] = filter_var( $atts['image_only'], FILTER_VALIDATE_BOOLEAN );

		$segments        = WPStrava::get_instance()->segments;
		$segment_details = null;

		try {
			$segment_details = $segments->get_segment( $atts['client_id'], $atts['id'] );
		} catch ( WPStrava_Exception $e ) {
			return $e->to_html();
		}

		$segments_output = '';
		if ( $segment_details ) {
			$segments_output .= '<div id="segments-header-' . $atts['id'] . '" class="wp-strava-segments-container">';
			if ( ! $atts['image_only'] ) {
				$segments_output .= $this->get_table( $segment_details, $atts['som'] );
			}

			// Sanitize width & height.
			$map_width  = str_replace( '%', '', $atts['map_width'] );
			$map_height = str_replace( '%', '', $atts['map_height'] );
			$map_width  = str_replace( 'px', '', $map_width );
			$map_height = str_replace( 'px', '', $map_height );

			$segments_output .= $segments->get_segments_link(
				$segment_details->id,
				WPStrava_StaticMap::get_image_tag( $segment_details, $map_height, $map_width, $atts['markers'], $segment_details->name ),
				$segment_details->name
			);

			if ( ! empty( $segment_details->description ) ) {
				$segments_output .= '<div class="wp-strava-segments-description">' . esc_html( $segment_details->description ) . '</div>';
			}

			$segments_output .= '</div>';
		} // End if( $segment_details ).
		return $segments_output;
	}

	/**
	 * The the segment details in in HTML table.
	 *
	 * @param stdClass $segment_details Segment details from the segment class.
	 * @param string $som System of measure (english/metric).
	 * @return string HTML Table of segment details.
	 * @author Justin Foell <justin@foell.org>
	 * @author Sebastian Erb <mail@sebastianerb.com>
	 * @since  2.9.0
	 * @TODO FIXME
	 */
	private function get_table( $segment_details, $som ) {
		$strava_som          = WPStrava_SOM::get_som( $som );
		$elevation_title     = '<th>' . __( 'Elevation Gain', 'wp-strava' ) . '</th>';
		$elevation           = '<td data-label="' . __( 'Elevation Gain', 'wp-strava' ) . '">
									<div class="activity-details-table-info">' . $strava_som->elevation( $segment_details->total_elevation_gain ) . '</div>
									<div class="activity-details-table-units">' . $strava_som->get_elevation_label() . '</div>
								</td>';
		$grade_title         = '<th>' . __( 'Avg. Grade', 'wp-strava' ) . '</th>';
		$grade               = '<td data-label="' . __( 'Avg. Grade', 'wp-strava' ) . '">
									<div class="activity-details-table-info">' . $strava_som->percent( $segment_details->average_grade ) . '</div>
									<div class="activity-details-table-units">&nbsp;</div>
								</td>';

		if ( WPStrava::get_instance()->settings->hide_elevation ) {
			$elevation_title = '';
			$elevation       = '';
			$grade_title     = '';
			$grade_title     = '';
		}

		return '
			<table class="activity-details-table">
				<thead>
					<tr>
						<th>' . __( 'Distance', 'wp-strava' ) . '</th>
						' . $grade_title . '
						' . $elevation_title . '
					</tr>
				</thead>
				<tbody>
					<tr>
						<td data-label="' . __( 'Distance', 'wp-strava' ) . '">
							<div class="activity-details-table-info">' . $strava_som->distance( $segment_details->distance ) . '</div>
							<div class="activity-details-table-units">' . $strava_som->get_distance_label() . '</div>
						</td>
						' . $grade . '
						' . $elevation . '
					</tr>
				</tbody>
			</table>
		';
	}
}
