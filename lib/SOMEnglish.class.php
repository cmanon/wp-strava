<?php

class WPStrava_SOMEnglish extends WPStrava_SOM {

	/**
	 * @param string $m meters
	 * @return string mi
	 */
	public function distance( $m ) {
		return number_format( $m / 1609.344, 2 );
	}

	/**
	 * @param string $dist miles
	 * @return float meters
	 */
	public function distance_inverse( $dist ) {
		return $dist * 1609.344;
	}

	public function get_distance_label() {
		return __( 'mi.', 'wp-strava' );
	}

	/**
	 * @param string $mps 
	 * @return string mph
	 */
	public function speed( $mps ) {
		return number_format( $mps * 2.2369, 2 );
	}

	public function get_speed_label() {
		return __( 'mph', 'wp-strava' );
	}

	/**
	 * @param string $m meters
	 * @return string feet
	 */
	public function elevation( $m ) {
		return number_format( $m / 0.3048, 2 );
	}

	public function get_elevation_label() {
		return __( 'ft.', 'wp-strava' );
	}
}	