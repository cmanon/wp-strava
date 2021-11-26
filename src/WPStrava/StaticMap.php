<?php

abstract class WPStrava_StaticMap {

	protected static $max_chars = 1865;

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
	abstract public function get_image_tag( $activity, $height = 320, $width = 480, $markers = false, $title = '' );

	/**
	 * Factory method to get the correct StaticMap class based on specified string
	 * or by the options setting.
	 *
	 * @param string $auth 'google' or 'mapbox' (default 'google').
	 * @return WPStrava_StaticMap Instance of StaticMap
	 * @author Justin Foell <justin@foell.org>
	 * @since NEXT
	 */
	public static function get_map( $type = 'google' ) {
		/*
		if ( 'google' === $type ) {
			return new WPStrava_StaticGMap();
		}
		*/
		// Default to refresh.
		return new WPStrava_StaticMapbox();
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
	protected function decode_start_finish( $enc ) {
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
	 * @param int    $base_url_len Length of map URL.
	 * @param string $enc          Encoded polyline.
	 * @return string Smaller encoded polyline.
	 * @author Justin Foell <justin@foell.org>
	 * @since 2.10.0
	 */
	protected function reduce_polyline( $base_url_len, $enc ) {
		require_once WPSTRAVA_PLUGIN_DIR . 'src/Polyline.php';
		$points = Polyline::decode( $enc );
		$points = Polyline::pair( $points );

		// Reduce by half https://stackoverflow.com/a/6519046/2146022
		$keys   = range( 0, count( $points ), 2 );
		$points = array_values( array_intersect_key( $points, array_combine( $keys, $keys ) ) );

		$points   = Polyline::flatten( $points );
		$polyline = Polyline::encode( $points );

		if ( $base_url_len + $this->polyline_length( $polyline ) >= self::$max_chars ) {
			// Reduce again.
			$polyline = $this->reduce_polyline( $base_url_len, $polyline );
		}

		return $polyline;
	}

	protected function polyline_length( $polyline ) {
		return strlen( $polyline );
	}
}
