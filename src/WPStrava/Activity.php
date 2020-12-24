<?php
/*
 * Activity is a class wrapper for the Strava REST API functions.
 */
class WPStrava_Activity {

	const ACTIVITIES_URL = 'https://strava.com/activities/';
	const ATHLETES_URL   = 'https://strava.com/athletes/';

	/**
	 * Get single activity by ID.
	 *
	 * @param string  $client_id   Client ID of athlete to retrieve for
	 * @param int     $activity_id ID of activity to retrieve.
	 * @return object stdClass Representing this activity.
	 * @author Justin Foell <justin@foell.org>
	 */
	public function get_activity( $client_id, $activity_id ) {
		return WPStrava::get_instance()->get_api( $client_id )->get( "activities/{$activity_id}" );
	}

	/**
	 * Get activity list from Strava API.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @param array $args {
	 *     Array of arguments.
	 *
	 *     @type string   $client_id      Client ID of athlete to retrieve for
	 *     @type int      $strava_club_id Club ID of all club riders (optional).
	 *     @type int|null $quantity       Number of records to retrieve (optional).
	 *     @type int|null $date_start     Timestamp for filtering activities after a certain time (optional, negates $quantity).
	 *     @type int|null $date_end       Timestamp for filtering activities before a certain time (optional, negates $quantity).
	 * }
	 * @return array Array of activities.
	 */
	public function get_activities( $args ) {
		$api = WPStrava::get_instance()->get_api( $args['client_id'] );

		$get_args = array();

		if ( ! empty( $args['quantity'] ) && is_numeric( $args['quantity'] ) ) {
			$get_args['per_page'] = $args['quantity'];
		}

		// Add start/end date (not supported for clubs).
		if ( empty( $args['strava_club_id'] ) && ! empty( $args['date_start'] ) && ! empty( $args['date_end'] ) ) {

			// Check for valid dates.
			if ( strtotime( $args['date_start'] ) && strtotime( $args['date_end'] ) ) {
				unset( $get_args['per_page'] );

				$localtime = new DateTimeZone( get_option( 'timezone_string' ) );
				$before_dt = new DateTime( $args['date_end'], $localtime );
				$after_dt  = new DateTime( $args['date_start'], $localtime );

				$get_args['before'] = $before_dt->format( 'U' );
				$get_args['after']  = $after_dt->format( 'U' );
			}
		}

		$data = null;

		//Get the json results using the constructor specified values.
		if ( ! empty( $args['strava_club_id'] ) && is_numeric( $args['strava_club_id'] ) ) {
			$data = $api->get( "clubs/{$args['strava_club_id']}/activities", $get_args );
		} else {
			$data = $api->get( 'athlete/activities', $get_args );
		}

		if ( is_array( $data ) ) {
			return $data;
		}

		return array();

	}

	/**
	 * Get activities with a distance longer than specified length.
	 *
	 * @param array $activities
	 * @param float $dist Distance in default system of measure (km/mi).
	 * @return void
	 * @author Justin Foell <justin@foell.org>
	 */
	public function get_activities_longer_than( $activities, $dist ) {
		$som    = WPStrava_SOM::get_som();
		$meters = $som->distance_inverse( $dist );

		$long_activities = array();
		foreach ( $activities as $activity_info ) {
			if ( $activity_info->distance > $meters ) {
				$long_activities[] = $activity_info;
			}
		}

		return $long_activities;
	}

	/**
	 * Conditionally display a link based on settings.
	 *
	 * @param int    $activity_id Strava Activity ID
	 * @param string $text        Text (or HTML) that is the content of link.
	 * @param string $title       Title attribute (default empty).
	 * @return void
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.3.2
	 */
	public function get_activity_link( $activity_id, $text, $title = '' ) {
		if ( $this->is_rest_request() || WPStrava::get_instance()->settings->no_link ) {
			return $text;
		}
		$url        = esc_url( self::ACTIVITIES_URL . $activity_id );
		$title_attr = $title ? " title='" . esc_attr( $title ) . "'" : '';
		return "<a href='{$url}'{$title_attr}>{$text}</a>";
	}

	/**
	 * Check if rest request to skip link rendering in block editor.
	 *
	 * @return boolean
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.5.1
	 */
	private function is_rest_request() {
		return defined( 'REST_REQUEST' ) && REST_REQUEST;
	}
}
