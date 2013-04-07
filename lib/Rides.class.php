<?php
/*
 * Rides is a class wrapper for the Strava REST API functions.
 */
class WPStrava_Rides {

	const RIDES_URL = "http://app.strava.com/rides/";
	const ATHLETES_URL = "http://app.strava.com/athletes/";
	
	public function getRideDetails( $rideId ) {
		return WPStrava::get_instance()->api->get( "rides/{$rideId}" );
	} // getRideDetails
		
	public function getRidesDetails( $rides ) {
		$rides_details = array();
		foreach ( $rides as $stravaRide ) {
			$detail = $this->getRideDetails( $stravaRide->id );

			if ( is_wp_error( $detail ) )
				return $detail;
				
			$rides_details[] = $detail;
		}
		return $rides_details;
	} // getRidesDetails
	
	public function getRidesSimple( $searchOption, $searchId ) {
		$api = WPStrava::get_instance()->api;

		$data = NULL;
		//Get the json results using the constructor specified values.
		if ( $searchOption == 'athlete' ) {
			if ( is_numeric( $searchId ) ) {
				$data = $api->get( 'rides', array( 'athleteId' => $searchId ), 1 );
			} else {
				$data = $api->get( 'rides', array( 'athleteName' => $searchId ), 1 );
			}
		} elseif ($searchOption == 'club' && is_numeric($searchId)) {
			$data = $api->get( 'rides', array( 'clubId' => $searchId ), 1 );
		} else {
			return new WP_Error( 'wp-strava_options', __("There's an error in your simple options.", 'wp-strava') );
		}

		if ( is_wp_error( $data ) )
			return $data;
		
		if ( isset( $data->rides ) )
			return $data->rides;
		
		return array();	
		
	} // getRidesSimple

	public function getRidesAdvanced( $params ) {	
		$data = WPStrava::get_instance()->api->get( 'rides', explode( "\n", $params ), 1 ); //version 1

		if ( is_wp_error( $data ) )
			return $data;
		
		if ( isset( $data->rides ) )
			return $data->rides;
		
		return array();	
	}
	    
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
} // class Rides
?>
