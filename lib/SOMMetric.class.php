<?php

class WPStrava_SOMMetric extends WPStrava_SOM {
	/**
	 * @param $m meters
	 * @return string km
	 */
	public function distance( $m ) {
		return number_format( $m / 1000, 2 );
	}

	/**
	 * @param string $dist km
	 * @return string meters
	 */
	public function distance_inverse( $dist ) {
		return $dist * 1000;
	}

	public function get_distance_label() {
		return __( 'km', 'wp-strava' );
	}

	/**
	 * @param $mps
	 * @return string km/h
	 */
	public function speed( $mps ) {
		return number_format( $mps * 3.6, 2 );
	}

	public function get_speed_label() {
		return __( 'km/h', 'wp-strava' );
	}

	/**
	 * @param $m meters
	 * @return string meters
	 */
	public function elevation( $m ) {
		return number_format( $m, 2 );
	}

	public function get_elevation_label() {
		return __( 'meters', 'wp-strava' );
	}
}