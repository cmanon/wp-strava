<?php

/**
 * AuthRefresh class
 *
 * @since 2.0.0
 * @see http://developers.strava.com/docs/authentication/
 */
class WPStrava_AuthRefresh extends WPStrava_Auth {

	/**
	 * Hooks.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.0.0
	 */
	public function hook() {
		parent::hook();

		// Refresh tokens via cronjob.
		add_action( 'init', array( $this, 'setup_auth_refresh_cron' ) );
		add_action( 'wp_strava_auth_refresh_cron', array( $this, 'auth_refresh' ) );
	}

	/**
	 * Set up cron to refresh the auth token hourly (expires after 6-hours).
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.0.0
	 */
	public function setup_auth_refresh_cron() {

		// Schedule the cron job to purge all expired transients.
		if ( ! wp_next_scheduled( 'wp_strava_auth_refresh_cron' ) ) {
			wp_schedule_event( time(), 'hourly', 'wp_strava_auth_refresh_cron' );
		}
	}

	/**
	 * Cron method to refresh auth tokens from Strava.
	 *
	 * @author Justin Foell
	 * @since  2.0.0
	 */
	public function auth_refresh() {
		$settings = WPStrava::get_instance()->settings;
		foreach ( $settings->info as $client_id => $info ) {
			if ( ! empty( $info->refresh_token ) ) {
				$this->token_exchange_refresh( $client_id, $info );
			}
		}
	}

	/**
	 * Authorize URL for new style refresh token auth.
	 *
	 * @param int $client_id Strava API Client ID.
	 * @return string URL to authorize against.
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.0.0
	 */
	protected function get_authorize_url( $client_id ) {
		return add_query_arg(
			array(
				'client_id'       => $client_id,
				'redirect_uri'    => $this->get_redirect_param(),
				'approval_prompt' => 'auto',
				'scope'           => 'read,activity:read',
			),
			$this->auth_url
		);
	}

	/**
	 * Add 'authorization_code' grand type to first API request (when authenticating a new user).
	 *
	 * @param array $data Request array for the Strava API.
	 * @return array Data array with 'grant_type' => 'authorization_code' added.
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.0.0
	 */
	protected function add_initial_params( $data ) {
		$data['grant_type'] = 'authorization_code';
		return $data;
	}

	/**
	 * Extend access by contacting strava with a refresh token.
	 *
	 * @param int $client_id
	 * @param stdClass $info
	 * @return boolean True if refreshed successfully, false otherwise.
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.0.0
	 */
	protected function token_exchange_refresh( $client_id, $info ) {
		$data = array(
			'client_id'     => $client_id,
			'client_secret' => $info->client_secret,
			'refresh_token' => $info->refresh_token,
			'grant_type'    => 'refresh_token',
		);

		$strava_info = $this->token_request( $data );

		if ( isset( $strava_info->access_token ) ) {
			// Translators: Token refresh success message.
			$this->feedback .= __( 'ID %s successfully re-authenticated.', 'wp-strava' );

			if ( $strava_info->access_token !== $info->access_token ) {
				// Translators: New token created message.
				$this->feedback .= __( 'ID %s access extended.', 'wp-strava' );

				$settings = WPStrava::get_instance()->settings;
				$settings->save_info( $client_id, $info->client_secret, $strava_info );
			}

			return true;
		}

		// @TODO how to determine if refresh wasn't successful?
		$this->feedback .= sprintf( __( 'There was an error receiving data from Strava: <pre>%s</pre>', 'wp-strava' ), print_r( $strava_info, true ) ); // phpcs:ignore -- Debug output.
		return false;
	}

}

