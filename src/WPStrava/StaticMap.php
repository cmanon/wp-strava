<?php

class WPStrava_StaticMap {

	private static $max_chars = 1865;

	/**
	 * Get an image tag to a static google map. Will render with
	 * detailed polyline if not greater than 1865 chars, otherwise
	 * rendering will use summary polyline.
	 *
	 * @static
	 * @access public
	 * @param object  $activity Activity object to get image tag for.
	 * @param int     $height   Height of map in pixels.
	 * @param int     $width    Width of map in pixels.
	 * @param bool    $markers  Display start and finish markers.
	 * @param string  $title    Title attribute to accompany image (default empty).
	 * @return string           HTML img tag with static map image.
	 */
	public static function get_image_tag( $activity, $height = 320, $width = 480, $markers = false, $title = '' ) {
		$key = WPStrava::get_instance()->settings->gmaps_key;

		// Short circuit if missing key or activity object doesn't have the data we need.
		if ( empty( $key ) || empty( $activity->map ) ) {
			return '';
		}

		if ( ! $height || ! $width ) {
			$height = 320;
			$width  = 480;
		}

		$url     = "https://maps.googleapis.com/maps/api/staticmap?maptype=terrain&size={$width}x{$height}&scale=2&sensor=false&key={$key}&path=color:0xFF0000BF|weight:2|enc:";
		$url_len = strlen( $url );

		$polyline = '';
		if ( ! empty( $activity->map->polyline ) && ( $url_len + strlen( $activity->map->polyline ) < self::$max_chars ) ) {
			$polyline = $activity->map->polyline;
		} elseif ( ! empty( $activity->map->summary_polyline ) ) {
			$polyline = $activity->map->summary_polyline;
		} elseif ( ! empty( $activity->map->polyline ) ) {
			// Need to reduce the polyline b/c it's too big and no summary was provided.
			$polyline = self::reduce_polyline( $url_len, $activity->map->polyline );
		}
		$url .= $polyline;

		if ( $markers ) {
			$points  = self::decode_start_finish( $polyline );
			$markers = '&markers=color:green|' . $points['start'][0] . ',' . $points['start'][1] .
						'&markers=color:red|' . $points['finish'][0] . ',' . $points['finish'][1];
			$url    .= $markers;
		}

		$title_attr = $title ? " title='" . esc_attr( $title ) . "'" : '';
		return "<img class='wp-strava-img' src='{$url}'{$title_attr} />";
	}

	/**
	 * From an encoded polyline, get the start and finish points for
	 * the purposes of displaying start and finish markers.
	 *
	 * @static
	 * @see https://developers.google.com/maps/documentation/utilities/polylinealgorithm
	 * @access private
	 * @param string $enc Encoded polyline.
	 * @return array {
	 *     Indexes of start & finish containing lat/lon for each.
	 *     @type array $start {
	 *         @type float $0 Latitude
	 *         @type float $1 Longitude
	 *     }
	 *     @type array $finish {
	 *         @type float $0 Latitude
	 *         @type float $1 Longitude
	 *     }
	 * }
	 */
	private static function decode_start_finish( $enc ) {
		require_once WPSTRAVA_PLUGIN_DIR . 'src/Polyline.php';
		$points = Polyline::decode( $enc );
		$points = Polyline::pair( $points );

		return array(
			'start'  => $points[0],
			'finish' => end( $points ),
		);
	}

	/**
	 * From a (large) encoded polyline, reduce the number of points
	 * until it is small enough for a GET URL.
	 *
	 * @param int    $url_len Length of map URL.
	 * @param string $enc     Encoded polyline.
	 * @return string Smaller encoded polyline.
	 * @author Justin Foell <justin@foell.org>
	 * @since 2.10.0
	 */
	private static function reduce_polyline( $url_len, $enc ) {
		require_once WPSTRAVA_PLUGIN_DIR . 'src/Polyline.php';
		$points = Polyline::decode( $enc );
		$points = Polyline::pair( $points );

		// Reduce by half https://stackoverflow.com/a/6519046/2146022
		$keys   = range( 0, count( $points ), 2 );
		$points = array_values( array_intersect_key( $points, array_combine( $keys, $keys ) ) );

		$points   = Polyline::flatten( $points );
		$polyline = Polyline::encode( $points );

		if ( $url_len + strlen( $polyline ) >= self::$max_chars ) {
			// Reduce again.
			$polyline = self::reduce_polyline( $url_len, $polyline );
		}

		return $polyline;
	}
}
