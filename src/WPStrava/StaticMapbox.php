<?php

class WPStrava_StaticMapbox extends WPStrava_StaticMap {

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

	private function build_url( $polyline, $height = 320, $width = 480, $markers = false ) {

		$url   = 'https://api.mapbox.com/styles/v1/mapbox/outdoors-v11/static/';
		$size  = "auto/{$width}x{$height}@2x";
		$token = 'access_token=pk.eyJ1IjoianJmb2VsbCIsImEiOiJ4NkNwU2RjIn0.MHjY7k0Okawa3bdV9HtSXg';

		$path = array();

		if ( $markers ) {
			$points = $this->decode_start_finish( $polyline );
			$path[] = "pin-s+008000({$points['start'][0]},{$points['start'][1]})";
			$path[] = "pin-s+ff0000({$points['finish'][0]},{$points['finish'][1]})";
		}

		// polyline must be URL encoded https://stackoverflow.com/a/65523379/2146022
		$url_polyline = rawurlencode( $polyline );
		$path[]       = "path-2+ff0000({$url_polyline})";
		$url         .= implode( ',', $path ) . "/{$size}?{$token}";

		return $url;
	}

	protected function polyline_length( $polyline ) {
		return strlen( rawurlencode( $polyline ) );
	}
}
