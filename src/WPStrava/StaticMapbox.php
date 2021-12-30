<?php

class WPStrava_StaticMapbox extends WPStrava_StaticMap {

	/**
	 * Get an image tag to a static mapbox map. Will render with
	 * detailed polyline if not greater than 1865 chars, otherwise
	 * rendering will use summary polyline.
	 *
	 * @param  object $activity Activity object to get image tag for.
	 * @param  int    $height   Height of map in pixels.
	 * @param  int    $width    Width of map in pixels.
	 * @param  bool   $markers  Display start and finish markers.
	 * @param  string $title    Title attribute to accompany image (default empty).
	 * @return string           HTML img tag with static map image.
	 */
	public function get_image_tag( $activity, $height = 320, $width = 480, $markers = false, $title = '' ) {

		$polyline = '';

		if ( ! empty( $activity->map->polyline ) ) {
			$polyline = $activity->map->polyline;
		} elseif ( ! empty( $activity->map->summary_polyline ) ) {
			$polyline = $activity->map->summary_polyline;
		}

		if ( empty( $polyline ) ) {
			// No polyline provided.
			return '';
		}

		if ( ! $height || ! $width ) {
			$height = 320;
			$width  = 480;
		}

		$url = $this->build_url( $polyline, $height, $width, $markers );

		$url_len = strlen( $url );
		if ( $url_len > self::$max_chars ) {
			// Need to reduce the polyline b/c it's too big.
			$polyline = $this->reduce_polyline( $url_len - $this->polyline_length( $polyline ), $polyline );
			$url      = $this->build_url( $polyline, $height, $width, $markers );
		}

		$title_attr = $title ? " title='" . esc_attr( $title ) . "'" : '';
		return "<img class='wp-strava-img' src='{$url}'{$title_attr} />";
	}

	/**
	 * Build a Mapbox Static Map URL.
	 *
	 * @param  string $polyline Polyline string to overlay.
	 * @param  int    $height   Height of map in pixels.
	 * @param  int    $width    Width of map in pixels.
	 * @param  bool   $markers  Display start and finish markers.
	 * @return string           Image URL.
	 * @author Justin Foell <justin@foell.org>
	 * @since 2.11
	 */
	private function build_url( $polyline, $height = 320, $width = 480, $markers = false ) {

		$url   = 'https://api.mapbox.com/styles/v1/mapbox/outdoors-v11/static/';
		$size  = "auto/{$width}x{$height}@2x";
		$token = WPStrava::get_instance()->settings->mapbox_token;

		$path = array();

		if ( $markers ) {
			$points = $this->decode_start_finish( $polyline );
			$path[] = "pin-s+008000({$points['start'][0]},{$points['start'][1]})";
			$path[] = "pin-s+ff0000({$points['finish'][0]},{$points['finish'][1]})";
		}

		// polyline must be URL encoded https://stackoverflow.com/a/65523379/2146022
		$url_polyline = rawurlencode( $polyline );
		$path[]       = "path-2+ff0000({$url_polyline})";
		$url         .= implode( ',', $path ) . "/{$size}?access_token={$token}";

		return $url;
	}

	/**
	 * Get the length of a polyline after encoding.
	 *
	 * @param  mixed $polyline Polyline string.
	 * @return int             Encoded polyline string length.
	 * @author Justin Foell <justin@foell.org>
	 * @since 2.11
	 */
	protected function polyline_length( $polyline ) {
		return strlen( rawurlencode( $polyline ) );
	}
}
