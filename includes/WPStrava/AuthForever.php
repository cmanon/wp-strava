<?php
/**
 * This functionality is deprecated and will be shut down on October 15, 2019
 *
 * @see https://developers.strava.com/docs/oauth-updates/#migration-instructions
 */

/**
 * AuthForever Class
 *
 * @since 2.0.0
 * @see http://developers.strava.com/docs/authentication/
 */
class WPStrava_AuthForever extends WPStrava_Auth {

	/**
	 * Authorize URL for old style forever tokens.
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
				'approval_prompt' => 'force',
			),
			$this->auth_url
		);
	}
}
