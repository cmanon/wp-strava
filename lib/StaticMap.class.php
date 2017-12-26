<?php

class WPStrava_StaticMap {

	/**
	 * Get an image tag to a static google map. Will render with
	 * detailed polyline if not greater than 1865 chars, otherwise
	 * rendering will use summary polyline.
	 *
	 * @static
	 * @access public
	 * @param object  $ride    Ride object from strava.
	 * @param int     $height  Height of map in pixels.
	 * @param int     $width   Width of map in pixels.
	 * @param bool    $markers Display start and finish markers.
	 * @return string          HTML img tag with static map image.
	 */
	public static function get_image_tag( $ride, $height = 320, $width = 480, $markers = false ) {
		$key = WPStrava::get_instance()->settings->gmaps_key;

		// Short circuit if missing key or ride object doesn't have the data we need.
		if ( empty( $key ) || empty( $ride->map ) ) {
			return '';
		}

		$url       = "https://maps.googleapis.com/maps/api/staticmap?maptype=terrain&size={$width}x{$height}&sensor=false&key={$key}&path=color:0xFF0000BF|weight:2|enc:";
		$url_len   = strlen( $url );
		$max_chars = 1865;

		$polyline = '';
		if ( ! empty( $ride->map->polyline ) && ( $url_len + strlen( $ride->map->polyline ) < $max_chars ) ) {
			$polyline = $ride->map->polyline;
		} elseif ( ! empty( $ride->map->summary_polyline ) ) {
			$polyline = $ride->map->summary_polyline;
		}
		$url .= $polyline;

		if ( $markers ) {
			$points  = self::decode_start_finish( $polyline );
			$markers = '&markers=color:green|' . $points['start'][0] . ',' . $points['start'][1] .
						'&markers=color:red|' . $points['finish'][0] . ',' . $points['finish'][1];
			$url    .= $markers;
		}

		return "<img class='wp-strava-img' src='{$url}' />";
	}

	/**
	 * From an encoded polyline, get the start and finish points for
	 * the purposes of displaying start and finish markers.
	 *
	 * @static
	 * @see https://developers.google.com/maps/documentation/utilities/polylinealgorithm
	 * @access private
	 * @param string $enc Encoded polyline.
	 * @return array with indexes of start & finish containing lat/lon for each.
	 */
	private static function decode_start_finish( $enc ) {
		require_once WPSTRAVA_PLUGIN_DIR . 'lib/Polyline.php';
		$points = Polyline::decode( $enc );
		$points = Polyline::pair( $points );
		$start  = $points[0];
		$finish = $points[ count( $points ) - 1 ];

		return array(
			'start'  => $start,
			'finish' => $finish,
		);
	}

}
