<?php
/*
 * Rides is a class wrapper for the Strava REST API functions.
 */
class WPStrava_Rides {

	const RIDES_URL = "http://app.strava.com/rides/";
	const ATHLETES_URL = "http://app.strava.com/athletes/";
	
	public function getRide( $rideId ) {
		return WPStrava::get_instance()->api->get( "activities/{$rideId}" );
	} // getRideDetails
	
	public function getRides( $club_id = NULL, $quantity = NULL ) {
		$api = WPStrava::get_instance()->api;

		$data = NULL;

		$args = $quantity ? array( 'per_page' => $quantity ) : NULL;
		
		//Get the json results using the constructor specified values.
		if ( is_numeric( $club_id ) ) {
			$data = $api->get( "clubs/{$club_id}/activities", $args );
		} else {
			$data = $api->get( 'athlete/activities', $args );
		}

		if ( is_wp_error( $data ) )
			return $data;
		
		if ( is_array( $data ) )
			return $data;
		
		return array();	
		
	} // getRides
	    
    public function getRideMap($rideId, $token, $efforts, $threshold) {
    	if($rideId != 0 AND $token != "") {
    		$url = preg_replace('/:id/', $rideId, $this->rideMapDetailsUrlV2);
    		$json = file_get_contents($url . '?token=' . $token . '&threshold=' . $threshold);
    		
    		if($json) {
    			//$map_details = json_decode($json);
    			//return $map_details;
    			return $json;
    		} else {
    			$this->feedback .= _e("There was an error pulling data of strava.com.", "wp-strava");
				return false;
    		}
    	} else {
    		$this->feedback .= _e("You need to provide both parameters to complete the call.", "wp-strava");
			return false;
    	}
    } // getRideDetails

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
?>
