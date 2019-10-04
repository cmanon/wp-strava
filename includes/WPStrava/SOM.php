<?php

abstract class WPStrava_SOM {

	/**
	 * Factory method to get the correct SOM class based on specified units
	 * or by the options setting.
	 *
	 * @param string $som 'english' or 'metric'
	 * @return WPStrava_SOM Instance of SOM
	 * @author Justin Foell <justin@foell.org>
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

	/**
	 * Create a time string of hours:minutes:seconds from just seconds.
	 *
	 * @return string Time formatted as 'H:i:s'.
	 * @see https://stackoverflow.com/a/20870843/2146022
	 */
	public function time( $seconds ) {
		$zero    = new DateTime( '@0' );
		$offset  = new DateTime( "@{$seconds}" );
		$diff    = $zero->diff( $offset );
		return sprintf( '%02d:%02d:%02d', $diff->days * 24 + $diff->h, $diff->i, $diff->s );
	}

	/**
	 * Label for hours.
	 *
	 * @return string 'hours'
	 */
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
	 * @param float|string $mps Meters per second.
	 * @return string Minutes Per 100 Meters.
	 */
	public function swimpace( $mps ) {

		$kmh = $mps * 3.6;
		$s   = 3600 / $kmh / 10;
		$ss  = $s / 60;
		$ms  = floor( $ss ) * 60;
		$sec = sprintf( '%02d', round( $s - $ms ) );
		$min = floor( $ss );

		return "{$min}:{$sec}";
	}
}
