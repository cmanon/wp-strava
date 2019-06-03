<?php

abstract class WPStrava_Auth {

	protected $auth_url = 'https://www.strava.com/oauth/authorize?response_type=code';
	private $feedback;

	/**
	 * Factory method to get the correct Auth class based on specified string
	 * or by the options setting.
	 *
	 * @param string $auth 'refresh' or 'forever' (default 'refresh').
	 * @return WPStrava_Auth Instance of Auth
	 * @author Justin Foell <justin@foell.org>
	 */
	public static function get_auth( $auth = 'refresh' ) {
		if ( 'forever' === $auth ) {
			return new WPStrava_AuthForever();
		}
		// Default to refresh.
		return new WPStrava_AuthRefresh();
	}

	abstract protected function get_authorize_url( $client_id );

	public function hook() {
		if ( is_admin() ) {
			add_filter( 'pre_set_transient_settings_errors', array( $this, 'maybe_oauth' ) );
			add_action( 'admin_init', array( $this, 'init' ) );
		}
	}

	/**
	 * This runs after options are saved
	 */
	public function maybe_oauth( $value ) {
		$settings = WPStrava::get_instance()->settings;

		// User is clearing to start-over, don't oauth, ignore other errors.
		if ( isset( $_POST['strava_id'] ) && $settings->ids_empty( $_POST['strava_id'] ) ) {
			return array();
		}

		// Redirect only if all the right options are in place.
		if ( $settings->is_settings_updated( $value ) && $settings->is_option_page() ) {
			// Only re-auth if client ID and secret were saved.
			if ( ! empty( $_POST['strava_client_id'] ) && ! empty( $_POST['strava_client_secret'] ) ) {
				wp_redirect( $this->get_authorize_url( $_POST['strava_client_id'] ) );
				exit();
			}
		}
		return $value;
	}

	public function init() {
		$settings = WPStrava::get_instance()->settings;

		//only update when redirected back from strava
		if ( ! isset( $_GET['settings-updated'] ) && $settings->is_settings_page() ) {
			if ( isset( $_GET['code'] ) ) {
				$info = $this->token_exchange_initial( $_GET['code'] );
				if ( isset( $info->access_token ) ) {
					// Translators: New strava token
					add_settings_error( 'strava_token', 'strava_token', sprintf( __( 'New Strava token retrieved. %s', 'wp-strava' ), $this->feedback ), 'updated' );
				} else {
					// throw new WPStrava_Exception( '' );
					add_settings_error( 'strava_token', 'strava_token', $this->feedback );
				}
			} elseif ( isset( $_GET['error'] ) ) {
				// Translators: authentication error mess
				add_settings_error( 'strava_token', 'strava_token', sprintf( __( 'Error authenticating at Strava: %s', 'wp-strava' ), str_replace( '_', ' ', $_GET['error'] ) ) );
			}
		}
	}

	protected function get_redirect_param() {
		$page_name = WPStrava::get_instance()->settings->get_page_name();
		return rawurlencode( admin_url( "options-general.php?page={$page_name}" ) );
	}

	// was fetch_token();
	private function token_exchange_initial( $code ) {
		$settings = WPStrava::get_instance()->settings;
		$client_id     = $settings->client_id;
		$client_secret = $settings->client_secret;

		$settings->delete_id_secret();

		if ( $client_id && $client_secret ) {

			$data = array(
				'client_id'     => $client_id,
				'client_secret' => $client_secret,
				'code'          => $code,
			);

			$data = $this->add_initial_params( $data );

			$strava_info = $this->token_request( $data );

			if ( isset( $strava_info->access_token ) ) {
				$settings->add_id( $client_id );
				$settings->save_info( $client_id, $client_secret, $strava_info );

				$this->feedback .= __( 'Successfully authenticated.', 'wp-strava' );
				return $strava_info;
			}

			// Translators: error message from Strava
			$this->feedback .= sprintf( __( 'There was an error receiving data from Strava: <pre>%s</pre>', 'wp-strava' ), print_r( $strava_info, true ) ); // phpcs:ignore -- Debug output.
			return false;

		}

		$this->feedback .= __( 'Missing Client ID or Client Secret.', 'wp-strava' );
		return false;
	}

	protected function token_request( $data ) {
		$api  = new WPStrava_API();
		return $api->post( 'oauth/token', $data );
	}

	protected function add_initial_params( $data ) {
		return $data;
	}

}
