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
	
	public function post( $uri, $data = NULL, $version = 2 ) {
		$url = ( $version == 2 ) ? self::STRAVA_V2_API : self::STRAVA_V1_API;

		$args = array(
			'body' => http_build_query( $data ),
		);

		if ( $version == 2 )
			$args['sslverify'] = false;

		$response = wp_remote_post( $url . $uri, $args );

		if ( $response['response']['code'] != 200 ) {
			//see if there's useful info in the body
			$body = json_decode( $response['body'] );
			$error = '';
			if ( ! empty( $body->error ) )
				$error = $body->error;
			else
				$error = print_r( $response, true );

			return new WP_Error( 'wp-strava_post',
								 sprintf( __( 'ERROR %s %s - %s', 'wp-strava'), $response['response']['code'], $response['response']['message'], $error ),
								 $response );
		}

		return json_decode( $response['body'] );
	}

	public function get( $uri, $args = NULL, $version = 2 ) {
		$url = ( $version == 2 ) ? self::STRAVA_V2_API : self::STRAVA_V1_API;

		$url .= $uri;

		if ( ! empty( $args ) )
			$url = add_query_arg( $args, $url );

		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) )
			return $response;
		
		if ( $response['response']['code'] != 200 ) {
			die($url);
			//see if there's useful info in the body
			$body = json_decode( $response['body'] );
			$error = '';
			if ( ! empty( $body->error ) )
				$error = $body->error;
			else
				$error = print_r( $response, true );

			return new WP_Error( 'wp-strava_get',
								 sprintf( __( 'ERROR %s %s - %s', 'wp-strava'), $response['response']['code'], $response['response']['message'], $error ),
								 $response );
		}

		return json_decode( $response['body'] );
	}
	
} // class API