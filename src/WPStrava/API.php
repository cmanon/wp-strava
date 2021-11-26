<?php

/*
 * API class for all remote calls.
 */
class WPStrava_API {

	const STRAVA_V3_API = 'https://www.strava.com/api/v3/';

	private $client_id = null;

	/**
	 * Constructor.
	 *
	 * @param int $client_id Strava API Client ID representing an athlete.
	 * @author Justin Foell <justin@foell.org>
	 */
	public function __construct( $client_id = null ) {
		$this->client_id = $client_id;
	}

	/**
	 * POST something to the Strava API.
	 *
	 * @param string $uri Path within the Strava API.
	 * @param array $data Data to POST.
	 * @return stdClass Strava API response.
	 * @throws WPStrava_Exception
	 * @author Justin Foell <justin@foell.org>
	 */
	public function post( $uri, $data = null ) {
		$url = self::STRAVA_V3_API;

		$args = array(
			'body'      => http_build_query( $data ),
			'sslverify' => false,
			'headers'   => array(),
			'timeout'   => 30,
		);

		$access_token = $this->get_access_token();
		if ( $access_token ) {
			$args['headers']['Authorization'] = 'Bearer ' . $access_token;
		}

		$response = wp_remote_post( $url . $uri, $args );

		if ( WPSTRAVA_DEBUG ) {
			// phpcs:disable WordPress.PHP.DevelopmentFunctions -- Debug output.
			error_log(
				print_r(
					array(
						'url'      => $url . $uri,
						'args'     => $args,
						'response' => $response,
					),
					true
				)
			);
			// phpcs:enable
		}

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

	/**
	 * GET something from the Strava API - checking the cache first.
	 *
	 * @param string $uri Path within the Strava API.
	 * @param array $args Request arguments.
	 * @return stdClass Strava API response.
	 * @throws WPStrava_Exception
	 * @author Justin Foell <justin@foell.org>
	 */
	public function get( $uri, $args = null ) {

		// @see https://stackoverflow.com/a/3764390/2146022
		$arg_suffix = is_array( $args ) && ! empty( $args ) ? '_' . substr( md5( wp_json_encode( $args ) ), 0, 12 ) : '';

		$transient_key = 'strava_api_data_' . $this->client_id . '_' . $uri . $arg_suffix;

		$data = get_transient( $transient_key );

		if ( $data ) {
			return $data;
		}

		$data = $this->remote_get( $uri, $args );

		// Default to 1 hour of transient cache unless set otherwise.
		$settings   = WPStrava::get_instance()->settings;
		$cache_time = $settings->cache_time ? absint( $settings->cache_time ) : HOUR_IN_SECONDS;

		set_transient( $transient_key, $data, $cache_time );

		return $data;
	}

	/**
	 * GET something from the Strava API.
	 *
	 * @param string $uri Path within the Strava API.
	 * @param array $args Request arguments.
	 * @return stdClass Strava API response.
	 * @throws WPStrava_Exception
	 * @author Justin Foell <justin@foell.org>
	 * @since 2.0.1
	 */
	private function remote_get( $uri, $args = null ) {
		static $retry = true;

		$url  = self::STRAVA_V3_API;
		$url .= $uri;

		if ( ! empty( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		$get_args = array(
			'headers'   => array(),
			'sslverify' => false,
			'timeout'   => 30,
		);

		$access_token = $this->get_access_token();
		if ( $access_token ) {
			$get_args['headers']['Authorization'] = 'Bearer ' . $access_token;
		}

		$response = wp_remote_get( $url, $get_args );
		if ( is_wp_error( $response ) ) {
			throw WPStrava_Exception::from_wp_error( $response );
		}

		// Try *one* real-time token refresh if Unauthorized.
		if ( $retry && 401 == $response['response']['code'] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			$retry = false;
			$auth  = WPStrava::get_instance()->auth;
			if ( $auth instanceof WPStrava_AuthRefresh ) {
				$auth->auth_refresh();
				$access_token = $this->get_access_token();
				if ( $access_token ) {
					$get_args['headers']['Authorization'] = 'Bearer ' . $access_token;
				}
				return $this->remote_get( $uri, $get_args );
			}
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

	/**
	 * Get the (ever changing) access token based on current Client ID.
	 *
	 * @return string|null String for access token, null if not found.
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.0.0
	 */
	private function get_access_token() {
		// If client_id not set (OAuth set-up), don't even look.
		if ( ! $this->client_id ) {
			return null;
		}

		$settings = WPStrava::get_instance()->settings;
		$info     = $settings->info;

		if ( ! empty( $info[ $this->client_id ]->access_token ) ) {
			return $info[ $this->client_id ]->access_token;
		}
		return null;
	}
}
