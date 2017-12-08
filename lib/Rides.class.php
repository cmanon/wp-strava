<?php
/*
 * Rides is a class wrapper for the Strava REST API functions.
 */
class WPStrava_Rides {

	const RIDES_URL = 'http://app.strava.com/rides/';
	const ATHLETES_URL = 'http://app.strava.com/athletes/';

	/**
	 * Get single activity by ID.
	 *
	 * @param string $athlete_token Token of athlete to retrieve for
	 * @param int    $activity_id ID of activity to retrieve.
	 * @return object  stdClass representing this activty.
	 * @author Justin Foell
	 */
	public function getRide( $athlete_token, $activity_id ) {
		return WPStrava::get_instance()->get_api( $athlete_token )->get( "activities/{$activity_id}" );
	} // getRideDetails

	/**
	 * Get activity list from Strava API.
	 *
	 * @author Justin Foell
	 *
	 * @param string   $athlete_token Token of athlete to retrieve for
	 * @param int      $club_id       Club ID of all club riders (optional).
	 * @param int|null $quantity      Number of records to retrieve (optional).
	 * @return array|WP_Error Array of rides or WP_Error.
	 */
	public function getRides( $athlete_token, $club_id = null, $quantity = null ) {
		$api = WPStrava::get_instance()->get_api( $athlete_token );

		$data = null;

		$args = $quantity ? array( 'per_page' => $quantity ) : null;

		//Get the json results using the constructor specified values.
		if ( is_numeric( $club_id ) ) {
			$data = $api->get( "clubs/{$club_id}/activities", $args );
		} else {
			$data = $api->get( 'athlete/activities', $args );
		}

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		if ( is_array( $data ) ) {
			return $data;
		}

		return array();

	} // getRides

	public function getRidesLongerThan( $rides, $dist ) {
		$som = WPStrava_SOM::get_som();
		$meters = $som->distance_inverse( $dist );

		$long_rides = array();
		foreach ( $rides as $ride_info ) {
			if ( $ride_info->distance > $meters ) {
				$long_rides[] = $ride_info;
			}
		}

		return $long_rides;
	}

} // class Rides
