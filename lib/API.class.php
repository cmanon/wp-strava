<?php
/*
 * Util is a class with all the utility methods.
 */
class WPStrava_API {

	//deactivated
	//const STRAVA_V1_API = 'http://www.strava.com/api/v1/'; //rides?athleteId=134698
	//const STRAVA_V2_API = 'http://www.strava.com/api/v2/'; //rides/:ride_id/map_details
	const STRAVA_V3_API = 'https://www.strava.com/api/v3/';

	public function __construct( $access_token = null ) {
		$this->access_token = $access_token;
	}

	public function post( $uri, $data = null ) {
		$url = self::STRAVA_V3_API;

		$args = array(
			'body' => http_build_query( $data ),
			'sslverify' => false,
			'headers' => array(),
		);

		if ( $this->access_token ) {
			$args['headers']['Authorization'] = 'Bearer ' . $this->access_token;
		}

		$response = wp_remote_post( $url . $uri, $args );

		if ( is_wp_error( $response ) )
			return $response;

		if ( $response['response']['code'] != 200 ) {
			//see if there's useful info in the body
			$body = json_decode( $response['body'] );
			$error = '';
			if ( ! empty( $body->error ) )
				$error = $body->error;
			else
				$error = print_r( $response, true );

			return new WP_Error(
				'wp-strava_post',
				// translators: message shown when there's a problem with ab HTTP POST to the Strava API.
				sprintf( __( 'ERROR %1$s %2$s - See full error by adding <code>define( \'WP_STRAVA_DEBUG\' true );</code> to wp-config.php', 'wp-strava' ), $response['response']['code'], $response['response']['message'] ),
				$error
			);
		}

		return json_decode( $response['body'] );
	}

	public function get( $uri, $args = null ) {
		$url = self::STRAVA_V3_API;

		$url .= $uri;

		if ( ! empty( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		$get_args = array(
			'headers' => array(),
		);
		if ( $this->access_token ) {
			$get_args['headers']['Authorization'] = 'Bearer ' . $this->access_token;
		}

		$response = wp_remote_get( $url, $get_args );

		if ( is_wp_error( $response ) )
			return $response;

		if ( $response['response']['code'] != 200 ) {
			//see if there's useful info in the body
			$body = json_decode( $response['body'] );
			$error = '';
			if ( ! empty( $body->error ) )
				$error = $body->error;
			else if ( $response['response']['code'] == 503 )
				$error = __( 'Strava Temporarily Unavailable', 'wp-strava' );
			else
				$error = print_r( $response, true );

			return new WP_Error(
				'wp-strava_get',
				// translators: message shown when there's a problem with an HTTP GET to the Strava API.
				sprintf( __( 'ERROR %1$s %2$s - See full error by adding <code>define( \'WP_STRAVA_DEBUG\' true );</code> to wp-config.php', 'wp-strava' ), $response['response']['code'], $response['response']['message'] ),
				$error
			);
		}

		return json_decode( $response['body'] );
	}

} // Class API.
