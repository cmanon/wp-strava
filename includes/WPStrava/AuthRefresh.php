<?php

/**
 * AuthRefresh class
 *
 * @since 2.0.0
 */
class WPStrava_AuthRefresh extends WPStrava_Auth {

	public function hook() {
		parent::hook();
		// @TODO Need cronjob.
	}

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

	protected function token_exchange_refresh() {
		$data = array(
			'client_id'     => $client_id,
			'client_secret' => $client_secret,
			'refresh_token' => $refresh_token,
			'grant_type'    => 'refresh_token',
		);

		$strava_info = $this->token_request( $data );

		if ( isset( $strava_info->access_token ) ) {
			$this->feedback .= __( 'Successfully re-authenticated.', 'wp-strava' );
			return $strava_info;
		}

		$this->feedback .= sprintf( __( 'There was an error receiving data from Strava: <pre>%s</pre>', 'wp-strava' ), print_r( $strava_info, true ) ); // phpcs:ignore -- Debug output.
		return false;
	}

}

