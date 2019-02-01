<?php

abstract class WPStrava_SOM {

	/**
	 * Factory method to get the correct SOM class based on specified units
	 * or by the options setting.
	 *
	 * @param string $som 'english' or 'metric'
	 * @return WPStrava_SOM Instance of SOM
	 * @author Justin Foell
	 */
	public static function get_som( $som = null ) {
		$som = $som ? $som : WPStrava::get_instance()->settings->som;
		if ( 'english' === $som ) {
			return new WPStrava_SOMEnglish();
		}
		// Default to metric.
		return new WPStrava_SOMMetric();
	}

	abstract public function distance( $m );
	abstract public function distance_inverse( $dist );
	abstract public function get_distance_label();
	abstract public function speed( $mps );
	abstract public function get_speed_label();
	abstract public function elevation( $m );
	abstract public function get_elevation_label();
	abstract public function pace( $mps );
	abstract public function get_pace_label();

	public function time( $seconds ) {
		return date( 'H:i:s', mktime( 0, 0, $seconds ) );
	}

	public function get_time_label() {
		return __( 'hours', 'wp-strava' );
	}

	/**
	 * Abbreviated label for this system of measure's pace - Minutes Per 100 Meters: min/100m. Same for English/metric.
	 *
	 * @return string 'min/100m'
	 */
	public function get_swimpace_label() {
		return __( 'min/100m', 'wp-strava' );
	}

	/**
	 * Change meters per second to Minutes Per 100 Meters. Same for English/metric.
	 *
	 * @param float $mps Meters per second.
	 * @return float Minutes Per 100 Meters.
	 */
	public function swimpace( $mps ) {

		$kmh     = $mps * 3.6;
		$min100m = 60 / $kmh / 10;

		return number_format( $min100m, 2 );
	}
}
