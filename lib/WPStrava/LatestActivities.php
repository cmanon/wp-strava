<?php

class WPStrava_LatestActivities {
	public static function get_activities_html( $args ) {

		$defaults = array(
			'athlete_token'  => WPStrava::get_instance()->settings->get_default_token(),
			'strava_club_id' => null,
			'quantity'       => 5,
			'som'            => WPStrava::get_instance()->settings->som,
		);

		$args = wp_parse_args( $args, $defaults );

		$som             = WPStrava_SOM::get_som( $args['som'] );
		$strava_activity = WPStrava::get_instance()->activity;
		$activities      = $strava_activity->get_activities( $args['athlete_token'], $args['strava_club_id'], $args['quantity'] );

		if ( is_wp_error( $activities ) ) {
			return $activities->get_error_message();
		}

		$response = "<ul id='activities'>";
		foreach ( $activities as $activity ) {
			$response .= "<li class='activity'>";
			$response .= "<a href='" . WPStrava_Activity::ACTIVITIES_URL . $activity->id . "'>" . $activity->name . '</a>';
			$response .= "<div class='activity-item'>";
			$unixtime  = strtotime( $activity->start_date_local );
			// Translators: Shows something like "On <date> <[went 10 miles] [during 2 hours] [climbing 100 feet]>."
			$response .= sprintf( __( 'On %1$s %2$s', 'wp-strava' ), date_i18n( get_option( 'date_format' ), $unixtime ), date_i18n( get_option( 'time_format' ), $unixtime ) );

			if ( is_numeric( $args['strava_club_id'] ) ) {
				$response .= " <a href='" . WPStrava_Activity::ATHLETES_URL . $activity->athlete->id . "'>" . $activity->athlete->firstname . ' ' . $activity->athlete->lastname . '</a>';
			}

			// Translators: "went 10 miles"
			$response .= sprintf( __( ' went %1$s %2$s', 'wp-strava' ), $som->distance( $activity->distance ), $som->get_distance_label() );
			// Translators: "during 2 hours"
			$response .= sprintf( __( ' during %1$s %2$s', 'wp-strava' ), $som->time( $activity->elapsed_time ), $som->get_time_label() );
			// Translators: "climbing 100 ft."
			$response .= sprintf( __( ' climbing %1$s %2$s', 'wp-strava' ), $som->elevation( $activity->total_elevation_gain ), $som->get_elevation_label() );
			$response .= '</div></li>';
		}
		$response .= '</ul>';
		return $response;
	}
}
