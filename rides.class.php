<?php
namespace WP\Strava;
/*
 * WP\Strava\Rides is a class wrapper for the Strava REST API functions.
 */
class Rides {
	private $rideUrl = "http://www.strava.com/api/v1/rides/:id";
	private $ridesUrl = "http://www.strava.com/api/v1/rides";
	private $authenticationUrl = "https://www.strava.com/api/v1/authentication/login";
	private $rideMapDetailsUrl = "http://www.strava.com/api/v1/rides/:id/map_details";
	private $rideMapDetailsUrlv2 = "http://www.strava.com/api/v2/rides/:id/map_details";
	
	public $ridesLinkUrl = "http://app.strava.com/rides/";
	public $stravaRides;
	public $feedback;
	
	public function __construct() {
		// Empty constructor
	} // __construct
	
	public function getRideDetails($rideId, $systemOfMeasurement) {
		$url = preg_replace('/:id/', $rideId, $this->rideUrl);
		//$url = $this->ridesUrl . $rideId;
		$json = file_get_contents($url);
		if($json) {
			$strava_ride = json_decode($json);
			
			//Transform data to a ready to be displayed format
			$startDate = date("F j, Y - H:i a", strtotime($strava_ride->ride->startDateLocal));
			$elapsedTime = date("H:i:s", mktime(0, 0, $strava_ride->ride->elapsedTime));
			$movingTime = date("H:i:s", mktime(0, 0, $strava_ride->ride->movingTime));
			
			if ($systemOfMeasurement == "metric") {
				//To km
				$distance = number_format($strava_ride->ride->distance/1000, 2);
				//To km/h
				$averageSpeed = number_format($strava_ride->ride->averageSpeed * 3.6, 2);
				//To km/h
				$maximumSpeed = number_format($strava_ride->ride->maximumSpeed/1000, 2);
				//It is already in meters
				$elevationGain = number_format($strava_ride->ride->elevationGain, 2);
			} elseif ($systemOfMeasurement == "english") {
				//To miles
				$distance = number_format($strava_ride->ride->distance/1609.34, 2);
				//To miles/h
				$averageSpeed = number_format($strava_ride->ride->averageSpeed * 2.2369, 2);
				//To miles/h
				$maximumSpeed = number_format($strava_ride->ride->maximumSpeed/1609.34, 2);
				//To foot
				$elevationGain = number_format($strava_ride->ride->elevationGain/0.3048, 2);
			}
			
			$ride_details = array(
				'id' => $rideId,
				'name' => $strava_ride->ride->name,
				//'description' => $stravaRide->rides->description,
				'startDate' => $startDate,
				'elapsedTime' => $elapsedTime,
				'movingTime' => $movingTime,
				'distance' => $distance,
				'averageSpeed' => $averageSpeed,
				'maximumSpeed' => $maximumSpeed,
				'elevationGain' => $elevationGain
			);
			return $ride_details;
		} else {
			$this->feedback .= _e("Could not get information from strava.com for the ride id: ") . $stravaRide->id;
			return false;
		}
	} // getRideDetails
	
	public function getRidesDetails($systemOfMeasurement) {
		if($this->stravaRides) {
			$rides_details = array();
			foreach($this->stravaRides as $stravaRide) {
				$rides_details[] = $this->getRideDetails($stravaRide->id, $systemOfMeasurement);
			}
			return $rides_details;
		} else {
			$this->feedback .= _e("Please provide the rides array to be processed.", "wp-strava");
			return false;
		}
	} // getRidesDetails
	
	public function getLatestRides($searchOption, $searchId, $quantity) {
		$url = $this->ridesUrl;
		//Get the json results using the constructor specified values.
		if($searchOption == "athlete") {
			if(is_numeric($searchId)) {
				$json = file_get_contents($url . '?athleteId=' . urlencode($searchId));
			} else {
				$json = file_get_contents($url . '?athleteName=' . urlencode($searchId));
			}
		} elseif ($this->searchOption == "club" AND is_numeric($searchId)) {
			$json = file_get_contents($url . '?clubId=' . urlencode($searchId));
		} else {
			$this->feedback .= _e("There's an error on the widget options combination.", "wp-strava");
		}
		if($json) {
			$strava_rides = json_decode($json);
			$this->stravaRides = array_slice($strava_rides->rides, 0, $quantity);
		} else {
			$this->feedback .= _e("There was an error pulling data of strava.com.", "wp-strava");
			return false;
		}
	} // getLatestRides
	
	public function getAuthenticationToken($email, $password) {
		$util = new Util;
		$data = array('email' => $email, 'password' => $password);
		$json = $util->makePostRequest($this->authenticationUrl, $data);

		if($json) {
			$strava_login = json_decode($json);
			if($strava_login->success == "true") {
				$this->feedback .= __('Successfully authenticated.', 'wp-strava');
				return $strava_login->token;
			} else {
				$this->feedback .= __('Authentication failed, please check your credentials.', 'wp-strava');
				return false;
			}
		} else {
			$this->feedback .= __('There was an error pulling data of strava.com.', 'wp-strava');
			return false;
		}
    } // getAuthenticationToken
    
    public function getRideMap($rideId, $token, $efforts, $threshold) {
    	if($rideId != 0 AND $token != "") {
    		$url = preg_replace('/:id/', $rideId, $this->rideMapDetailsUrlv2);
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