<?php
/*
 * Util is a class with all the utility methods.
 */
class WPStrava_API {

	const STRAVA_V1_API = 'http://www.strava.com/api/v1/'; //rides?athleteId=134698
	const STRAVA_V2_API = 'http://www.strava.com/api/v2/'; //rides/:ride_id/map_details

	/*
	private $rideUrl = "http://www.strava.com/api/v1/rides/:id";
	private $rideUrlV2 = "http://www.strava.com/api/v2/rides/:id";
	private $ridesUrl = "http://www.strava.com/api/v1/rides";
	private $authenticationUrl = "https://www.strava.com/api/v1/authentication/login";
	private $authenticationUrlV2 = "https://www.strava.com/api/v2/authentication/login";
	private $rideMapDetailsUrl = "http://www.strava.com/api/v1/rides/:id/map_details";
	private $rideMapDetailsUrlV2 = "http://www.strava.com/api/v2/rides/:id/map_details";
	*/
	
	public $ridesLinkUrl = "http://app.strava.com/rides/";
	public $athletesLinkUrl = "http://app.strava.com/athletes/";
	public $feedback = '';
	
	public function post( $uri, $data = NULL, $version = 2 ) {
		$url = ( $version == 2 ) ? self::STRAVA_V2_API : self::STRAVA_V1_API;

		$args = array(
			'body' => http_build_query( $data ),
		);

		if ( $version == 2 )
			$args['sslverify'] = false;

		$response = wp_remote_post( $url . $uri, $args );

		if ( empty( $response['response']['code'] ) || $response['response']['code'] != 200 ) {
    		$this->feedback .= sprintf( __( 'ERROR - %s', 'wp-strava'), print_r( $response, true ) );
			return false;
		}

		return json_decode( $response['body'] );
	}

	public function get( $uri, $version = 2 ) {
		$url = ( $version == 2 ) ? self::STRAVA_V2_API : self::STRAVA_V1_API;

		$response = wp_remote_get( $url . $uri );

		if ( empty( $response['response']['code'] ) || $response['response']['code'] != 200 ) {
    		$this->feedback .= sprintf( __( 'ERROR - %s', 'wp-strava'), print_r( $response, true ) );
			return false;
		}

		return json_decode( $response['body'] );
	}
	
} // class API