<?php

class WPStrava_LatestActivities {
	public static function get_activities_html( $args ) {
		if ( isset( $args['athlete_token'] ) ) {
			// Translators: Message shown when using deprecated athlete_token parameter.
			return __( 'The <code>athlete_token</code> parameter is deprecated as of WP-Strava version 2 and should be replaced with <code>client_id</code>.', 'wp-strava' );
		}

		$defaults = array(
			'client_id'      => WPStrava::get_instance()->settings->get_default_id(),
			'strava_club_id' => null,
			'quantity'       => 5,
			'som'            => WPStrava::get_instance()->settings->som,
		);

		$args = wp_parse_args( $args, $defaults );

		$som             = WPStrava_SOM::get_som( $args['som'] );
		$strava_activity = WPStrava::get_instance()->activity;
		$activities      = array();

		try {
			$activities = $strava_activity->get_activities( $args['client_id'], $args['strava_club_id'], $args['quantity'] );
		} catch ( WPStrava_Exception $e ) {
			return $e->to_html();
		}

		$response = "<ul id='activities'>";
		foreach ( $activities as $activity ) {
			$response .= "<li class='activity'>";
			$response .= empty( $activity->id ) ?
				$activity->name :
				"<a href='" . WPStrava_Activity::ACTIVITIES_URL . $activity->id . "'>" . $activity->name . '</a>';
			$response .= "<div class='activity-item'>";

			if ( ! empty( $activity->start_date_local ) ) {
				$unixtime = strtotime( $activity->start_date_local );
				// Translators: Shows something like "On <date> <[went 10 miles] [during 2 hours] [climbing 100 feet]>."
				$response .= sprintf( __( 'On %1$s %2$s', 'wp-strava' ),
					date_i18n( get_option( 'date_format' ), $unixtime ),
					self::get_activity_time( $unixtime )
				);
			}

			if ( is_numeric( $args['strava_club_id'] ) ) {
				$name      = $activity->athlete->firstname . ' ' . $activity->athlete->lastname;
				$response .= empty( $activity->athlete->id ) ?
					" {$name}" :
					" <a href='" . WPStrava_Activity::ATHLETES_URL . $activity->athlete->id . "'>" . $name . '</a>';
			}

			// Translators: "went 10 miles"
			$response .= sprintf( __( ' went %1$s %2$s', 'wp-strava' ), $som->distance( $activity->distance ), $som->get_distance_label() );
			// Translators: "during 2 hours"
			$response .= sprintf( __( ' during %1$s %2$s', 'wp-strava' ), $som->time( $activity->elapsed_time ), $som->get_time_label() );

			if ( ! WPStrava::get_instance()->settings->hide_elevation ) {
				// Translators: "climbing 100 ft."
				$response .= sprintf( __( ' climbing %1$s %2$s', 'wp-strava' ), $som->elevation( $activity->total_elevation_gain ), $som->get_elevation_label() );
			}

			$response .= '</div></li>';
		}
		$response .= '</ul>';
		return $response;
	}

	/**
	 * Get the activity time, possibly hiding it.
	 *
	 * @param int $unixtime
	 * @return string Formatted time, or empty string depending on hide_time option.
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.7.1
	 */
	public static function get_activity_time( $unixtime ) {
		if ( WPStrava::get_instance()->settings->hide_time ) {
			return '';
		}

		return date_i18n( get_option( 'time_format' ), $unixtime );
	}
}
