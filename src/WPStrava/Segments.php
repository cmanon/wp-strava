<?php
/*
 * Segments is a class wrapper for the Strava REST API functions.
 */

class WPStrava_Segments {

	const SEGMENTS_URL = 'https://strava.com/segments/';

	/**
	 * Get single segment by ID.
	 *
	 * @param string  $client_id   Client ID of athlete to retrieve for.
	 * @param int     $segment_id ID of segment to retrieve.
	 * @return object stdClass Representing this segment.
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.9.0
	 */
	public function get_segment( $client_id, $segment_id ) {
		return WPStrava::get_instance()->get_api( $client_id )->get( "segments/{$segment_id}" );
	}

	/**
	 * Get starred segment list from Strava API.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @param array $args {
	 *     Array of arguments.
	 *
	 *     @type string   $client_id      Client ID of athlete to retrieve for.
	 *     @type int|null $quantity       Number of records to retrieve (optional).
	 * }
	 * @return array Array of segments.
	 * @since  2.9.0
	 */
	public function get_starred_segments( $args ) {
		$api = WPStrava::get_instance()->get_api( $args['client_id'] );

		$get_args = array();

		if ( ! empty( $args['quantity'] ) && is_numeric( $args['quantity'] ) ) {
			$get_args['per_page'] = $args['quantity'];
		}

		$data = $api->get( 'segments/starred', $get_args );

		if ( is_array( $data ) ) {
			return $data;
		}

		return array();
	}

	/**
	 * Conditionally display a link based on settings.
	 *
	 * @param int    $segments_id Strava Segments ID
	 * @param string $text        Text (or HTML) that is the content of link.
	 * @param string $title       Title attribute (default empty).
	 * @return string Text with link
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.9.0
	 */
	public function get_segments_link( $segments_id, $text, $title = '' ) {
		if ( WPStrava::is_rest_request() || WPStrava::get_instance()->settings->no_link ) {
			return $text;
		}
		$url        = esc_url( self::SEGMENTS_URL . $segments_id );
		$title_attr = $title ? " title='" . esc_attr( $title ) . "'" : '';
		return "<a href='{$url}'{$title_attr}>{$text}</a>";
	}
}
