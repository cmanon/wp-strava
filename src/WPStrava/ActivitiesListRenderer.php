<?php
/**
 * Activities List Renderer.
 * @package WPStrava
 */

/**
 * Activities List class for shortcode and widget.
 *
 * @author Justin Foell <justin@foell.org>
 * @since  2.3.0
 */
class WPStrava_ActivitiesListRenderer {

	/**
	 * Get the HTML for an Activities List.
	 *
	 * @param array $atts
	 * @return string HTML for an route.
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.8.0
	 */
	public function get_html( $atts ) {
		if ( isset( $atts['athlete_token'] ) ) {
			// Translators: Message shown when using deprecated athlete_token parameter.
			return __( 'The <code>athlete_token</code> parameter is deprecated as of WP-Strava version 2 and should be replaced with <code>client_id</code>.', 'wp-strava' );
		}

		$defaults = array(
			'client_id'      => WPStrava::get_instance()->settings->get_default_id(),
			'strava_club_id' => null,
			'quantity'       => 5,
			'som'            => WPStrava::get_instance()->settings->som,
			'date_start'     => '',
			'date_end'       => '',
		);

		$atts = wp_parse_args( $atts, $defaults );

		$som             = WPStrava_SOM::get_som( $atts['som'] );
		$strava_activity = WPStrava::get_instance()->activity;
		$activities      = array();

		try {
			$activities = $strava_activity->get_activities( $atts );
		} catch ( WPStrava_Exception $e ) {
			return $e->to_html();
		}

		$response = "<ul id='activities'>";
		foreach ( $activities as $activity ) {
			if ( ! empty( $activity->id ) ) {
				// Re-get single activity for greater detail (will be cached).
				$activity = $strava_activity->get_activity( $atts['client_id'], $activity->id );
			}
			$response .= "<li class='activity'>";
			$response .= empty( $activity->id ) ?
				$activity->name : $strava_activity->get_activity_link( $activity->id, $activity->name );
			$response .= "<div class='activity-item'>";

			if ( ! empty( $activity->start_date_local ) ) {
				$unixtime  = strtotime( $activity->start_date_local );
				$response .= sprintf(
					// Translators: Shows something like "On <date> <[went 10 miles] [during 2 hours] [climbing 100 feet]>."
					__( 'On %1$s %2$s', 'wp-strava' ),
					date_i18n( get_option( 'date_format' ), $unixtime ),
					$this->get_activity_time( $unixtime )
				);
			}

			if ( is_numeric( $atts['strava_club_id'] ) && ! empty( $activity->athlete ) ) {
				$name      = $activity->athlete->firstname . ' ' . $activity->athlete->lastname;
				$response .= empty( $activity->athlete->id ) ?
					" {$name}" :
					" <a href='" . WPStrava_Activity::ATHLETES_URL . $activity->athlete->id . "'>" . $name . '</a>';
			}

			if ( ! empty( $activity->distance ) ) {
				// Translators: "went 10 miles"
				$response .= sprintf( __( ' went %1$s %2$s', 'wp-strava' ), $som->distance( $activity->distance ), $som->get_distance_label() );
			}

			if ( ! empty( $activity->moving_time ) ) {
				// Translators: "during 2 hours"
				$response .= sprintf( __( ' during %1$s %2$s', 'wp-strava' ), $som->time( $activity->moving_time ), $som->get_time_label() );
			}

			if ( ! WPStrava::get_instance()->settings->hide_elevation && ! empty( $activity->total_elevation_gain ) ) {
				// Translators: "climbing 100 ft."
				$response .= sprintf( __( ' climbing %1$s %2$s', 'wp-strava' ), $som->elevation( $activity->total_elevation_gain ), $som->get_elevation_label() );
			}

			if ( ! empty( $activity->calories ) ) { // LOL - empty calories :^)
				// Translators: "burning 200 calories."
				$response .= sprintf( __( ' burning %1$s calories.', 'wp-strava' ), $som->calories( $activity->calories ) );
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
	private function get_activity_time( $unixtime ) {
		if ( WPStrava::get_instance()->settings->hide_time ) {
			return '';
		}

		return date_i18n( get_option( 'time_format' ), $unixtime );
	}
}
