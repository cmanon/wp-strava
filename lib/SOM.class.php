<?php

abstract class WPStrava_SOM {

	public static function get_som( $som = NULL ) {
		$som = $som ? $som : WPStrava::get_instance()->settings->som;
		if ( $som == 'english' ) {
			require_once WPSTRAVA_PLUGIN_DIR . 'lib/SOMEnglish.class.php';
			return new WPStrava_SOMEnglish();
		} else { //default to metric
			require_once WPSTRAVA_PLUGIN_DIR . 'lib/SOMMetric.class.php';
			return new WPStrava_SOMMetric();
		}
			
	}
	
	abstract public function distance( $m );
	abstract public function distance_inverse( $dist );
	abstract public function get_distance_label();
	abstract public function speed( $mps );
	abstract public function get_speed_label();
	abstract public function elevation( $m );
	abstract public function get_elevation_label();

	public function time( $seconds ) {
		return date( 'H:i:s', mktime( 0, 0, $seconds ) );
	}

	public function get_time_label() {
		return __( 'hours', 'wp-strava' );
	}
}