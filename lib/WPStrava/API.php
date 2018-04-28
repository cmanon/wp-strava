<?php

/*
 * API class for all remote calls.
 */
class WPStrava_API {

	const STRAVA_V3_API = 'https://www.strava.com/api/v3/';

	public function __construct( $access_token = null ) {
		$this->access_token = $access_token;
	}

	public function post( $uri, $data = null ) {
		$url = self::STRAVA_V3_API;

		$args = array(
			'body'      => http_build_query( $data ),
			'sslverify' => false,
			'headers'   => array(),
			'timeout'   => 30,
		);

		if ( $this->access_token ) {
			$args['headers']['Authorization'] = 'Bearer ' . $this->access_token;
		}

		$response = wp_remote_post( $url . $uri, $args );

		if ( is_wp_error( $response ) ) {
			throw WPStrava_Exception::from_wp_error( $response );
		}

		if ( 200 != $response['response']['code'] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison

			// See if there's useful info in the body.
			$body  = json_decode( $response['body'] );
			$error = '';
			if ( ! empty( $body->error ) ) {
				$error = $body->error;
			} else {
				$error = print_r( $response, true ); // phpcs:ignore -- Debug output.
			}

			// Throw an informational exception with a detailed debug exception.
			throw new WPStrava_Exception(
				$response['response']['message'],
				$response['response']['code'],
				new WPStrava_Exception( $error )
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
			'headers'   => array(),
			'sslverify' => false,
			'timeout'   => 30,
		);

		if ( $this->access_token ) {
			$get_args['headers']['Authorization'] = 'Bearer ' . $this->access_token;
		}

		$response = wp_remote_get( $url, $get_args );

		if ( is_wp_error( $response ) ) {
			throw WPStrava_Exception::from_wp_error( $response );
		}

		if ( 200 != $response['response']['code'] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison

			// See if there's useful info in the body.
			$body  = json_decode( $response['body'] );
			$error = '';
			if ( ! empty( $body->error ) ) {
				$error = $body->error;
			} elseif ( 503 == $response['response']['code'] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				$error = __( 'Strava Temporarily Unavailable', 'wp-strava' );
			} else {
				$error = print_r( $response, true ); // phpcs:ignore -- Debug output.
			}

			// Throw an informational exception with a detailed debug exception.
			throw new WPStrava_Exception(
				$response['response']['message'],
				$response['response']['code'],
				new WPStrava_Exception( $error )
			);
		}

		return json_decode( $response['body'] );
	}

} // Class API.
