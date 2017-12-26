<?php

require_once WPSTRAVA_PLUGIN_DIR . 'lib/LatestActivities.class.php';

class WPStrava_LatestActivitiesShortcode {
	private static $add_script;

	public static function init() {
		add_shortcode( 'activities', array( __CLASS__, 'handler' ) );
		add_action( 'wp_footer', array( __CLASS__, 'print_scripts' ) );
	}

	// Shortcode handler function
	// [activities som=metric quantity=5 athlete_token=xxx|strava_club_id=yyy]
	public static function handler( $atts ) {
		self::$add_script = true;
		return WPStava_LatestActivities::get_activities_html( $atts );
	} // handler

	public static function print_scripts() {
		if ( self::$add_script ) {
			wp_enqueue_style( 'wp-strava-style' );
		}
	}
}

// Initialize short code
WPStrava_LatestActivitiesShortcode::init();
