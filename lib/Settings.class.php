<?php

/**
 * v3 - http://strava.github.io/api/v3/oauth/
 *
 * Set up an "API Application" at Strava
 * Save the Client ID and Client Secret in WordPress - redirect to strava oauth/authorize URL for permission
 * Get redirected back to this settings page with ?code= or ?error=
 * Use code to retrieve auth token
 */

class WPStrava_Settings {

	private $feedback;
	private $tokens = array();
	private $page_name = 'wp-strava-options';
	private $option_page = 'wp-strava-settings-group';
	private $adding_athlete = true;

	//register admin menus
	public function hook() {
		add_action( 'admin_init', array( $this, 'register_strava_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_strava_menu' ) );
		add_filter( 'pre_set_transient_settings_errors', array( $this, 'maybe_oauth' ) );
		add_filter( 'plugin_action_links_' . WPSTRAVA_PLUGIN_NAME, array( $this, 'settings_link' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'settings_scripts' ) );
		//for process debugging
		//add_action( 'all', array( $this, 'hook_debug' ) );
		//add_filter( 'all', array( $this, 'hook_debug' ) );
	}

	public function hook_debug( $name ) {
		echo "<!-- {$name} -->\n";
	}

	/**
	 * This runs after options are saved
	 */
	public function maybe_oauth( $value ) {	
		// User is clearing to start-over, don't oauth, ignore other errors.
		if ( isset( $_POST['strava_token'] ) && $this->tokens_empty( $_POST['strava_token'] ) )
				return array();

		// Redirect only if all the right options are in place.
		if ( isset( $value[0]['type'] ) && 'updated' == $value[0]['type'] ) { // Make sure there were no settings errors.
			if ( isset( $_POST['option_page'] ) && $_POST['option_page'] == $this->option_page ) { // Make sure we're on our settings page.

				// Only re-auth if client ID and secret were saved.
				if ( ! empty( $_POST['strava_client_id'] ) && ! empty( $_POST['strava_client_secret'] ) ) {
					$client_id = $_POST['strava_client_id'];
					$client_secret = $_POST['strava_client_secret'];

					$redirect = admin_url( "options-general.php?page={$this->page_name}" );
					$url = "https://www.strava.com/oauth/authorize?client_id={$client_id}&response_type=code&redirect_uri={$redirect}&approval_prompt=force";
					wp_redirect( $url );
					exit();
				}
			}
		}
		return $value;
	}

	public function add_strava_menu() {
		add_options_page( __( 'Strava Settings', 'wp-strava' ),
						  __( 'Strava', 'wp-strava' ),
						  'manage_options',
						  $this->page_name,
						  array( $this, 'print_strava_options' ) );
	}

	public function init() {
		$this->tokens = $this->get_tokens();

		// Only validate additional athlete information if all fields are present.
		$this->adding_athlete = ! ( empty( $_POST['strava_client_id'] ) && empty( $_POST['strava_client_secret'] ) );

		//only update when redirected back from strava
		if ( ! isset( $_GET['settings-updated'] ) && isset( $_GET['page'] ) && $_GET['page'] == $this->page_name ) {
			if ( isset( $_GET['code'] ) ) {
				$token = $this->fetch_token( $_GET['code'] );
				if ( $token ) {
					add_settings_error( 'strava_token', 'strava_token', sprintf( __( 'New Strava token retrieved. %s', 'wp-strava' ), $this->feedback ) , 'updated' );
					$this->add_token( $token );
					update_option( 'strava_token', $this->tokens );
				} else {
					add_settings_error( 'strava_token', 'strava_token', $this->feedback );
				}
			} elseif ( isset( $_GET['error'] ) ) {
				// Translators: authentication error mess
				add_settings_error( 'strava_token', 'strava_token', sprintf( __( 'Error authenticating at Strava: %s', 'wp-strava' ), str_replace( '_', ' ', $_GET['error'] ) ) );
			}
		}
	}

	public function register_strava_settings() {
		$this->init();

		add_settings_section( 'strava_api', __( 'Strava API', 'wp-strava' ), array( $this, 'print_api_instructions' ), 'wp-strava' );

		if ( $this->tokens_empty( $this->tokens ) ) {
			register_setting( $this->option_page, 'strava_client_id', array( $this, 'sanitize_client_id' ) );
			register_setting( $this->option_page, 'strava_client_secret', array( $this, 'sanitize_client_secret' ) );
			register_setting( $this->option_page, 'strava_nickname', array( $this, 'sanitize_nickname' ) );

			add_settings_field( 'strava_client_id', __( 'Strava Client ID', 'wp-strava' ), array( $this, 'print_client_input' ), 'wp-strava', 'strava_api' );
			add_settings_field( 'strava_client_secret', __( 'Strava Client Secret', 'wp-strava' ), array( $this, 'print_secret_input' ), 'wp-strava', 'strava_api' );
			add_settings_field( 'strava_nickname', __( 'Strava Nickname', 'wp-strava' ), array( $this, 'print_nickname_input' ), 'wp-strava', 'strava_api' );
		} else {
			register_setting( $this->option_page, 'strava_token',    array( $this, 'sanitize_token' ) );
			add_settings_field( 'strava_token', __( 'Strava Token', 'wp-strava' ), array( $this, 'print_token_input' ), 'wp-strava', 'strava_api' );

			// Add additional fields
			register_setting( $this->option_page, 'strava_client_id', array( $this, 'sanitize_client_id' ) );
			register_setting( $this->option_page, 'strava_client_secret', array( $this, 'sanitize_client_secret' ) );
			register_setting( $this->option_page, 'strava_nickname', array( $this, 'sanitize_nickname' ) );

			add_settings_field( 'strava_client_id', __( 'Additional Athlete Client ID', 'wp-strava' ), array( $this, 'print_client_input' ), 'wp-strava', 'strava_api' );
			add_settings_field( 'strava_client_secret', __( 'Additional Athlete Client Secret', 'wp-strava' ), array( $this, 'print_secret_input' ), 'wp-strava', 'strava_api' );
			add_settings_field( 'strava_nickname', __( 'Additional Athlete Nickname', 'wp-strava' ), array( $this, 'print_nickname_input' ), 'wp-strava', 'strava_api' );
		}

		// Google Maps API.
		register_setting( $this->option_page, 'strava_gmaps_key', array( $this, 'sanitize_gmaps_key' ) );
		add_settings_section( 'strava_gmaps', __( 'Google Maps', 'wp-strava' ), array( $this, 'print_gmaps_instructions' ), 'wp-strava' );
		add_settings_field( 'strava_gmaps_key', __( 'Static Maps Key', 'wp-strava' ), array( $this, 'print_gmaps_key_input' ), 'wp-strava', 'strava_gmaps' );

		// System of Measurement.
		register_setting( $this->option_page, 'strava_som', array( $this, 'sanitize_som' ) );
		add_settings_section( 'strava_options', __( 'Options', 'wp-strava' ), null, 'wp-strava' );
		add_settings_field( 'strava_som', __( 'System of Measurement', 'wp-strava' ), array( $this, 'print_som_input' ), 'wp-strava', 'strava_options' );

		// Clear cache.
		register_setting( $this->option_page, 'strava_cache_clear', array( $this, 'sanitize_cache_clear' ) );
		add_settings_section( 'strava_cache', __( 'Cache', 'wp-strava' ), null, 'wp-strava' );
		add_settings_field( 'strava_cache_clear', __( 'Clear cache (images & transient data)', 'wp-strava' ), array( $this, 'print_clear_input' ), 'wp-strava', 'strava_cache' );
	}

	public function print_api_instructions() {
		$signup_url = 'http://www.strava.com/developers';
		$settings_url = 'https://www.strava.com/settings/api';
		$icon_url = 'https://plugins.svn.wordpress.org/wp-strava/assets/icon-128x128.png';
		$blog_name = get_bloginfo( 'name' );
		$app_name = sprintf( esc_html( '%s Strava', 'wp-strava' ), $blog_name );
		$site_url = site_url();
		$description = 'WP-Strava for ' . $blog_name;
	   	printf( __( "<p>Steps:</p>
			<ol>
				<li>Create your free API Application/Connection here: <a href='%1\$s' target='_blank'>%2\$s</a> using the following information:</li>
				<ul>
					<li>App Icon: <strong>upload <a href='%3\$s' target='_blank'>this image</a></strong></li>
					<li>Application Name: <strong>%4\$s</strong></li>
					<li>Category: OK to leave at default 'other'</li>
					<li>Club: OK to leave blank</li>
					<li>Website: <strong>%5\$s</strong></li>
					<li>Application Description: <strong>%6\$s</strong></li>
					<li>Authorization Callback Domain: <strong>%7\$s</strong></li>
				</ul>
				<li>Once you've created your API Application at strava.com, enter the <strong>Client ID</strong> and <strong>Client Secret</strong> below, which can now be found on that same strava API Settings page.
				<li>After saving your Client ID and Secret, you'll be redirected to strava to authorize your API Application. If successful, your Strava Token will display instead of Client ID and Client Secret.</li>
				<li>If you need to re-authorize your API Application, erase your Strava Token here and click 'Save Changes' to start over.</li>
			</ol>", 'wp-strava' ),
			$settings_url,
			$settings_url,
			$icon_url,
			$app_name,
			$site_url,
			$description,
			wp_parse_url( $site_url, PHP_URL_HOST )
		);
	}

	public function print_gmaps_instructions() {
		$maps_url = 'https://developers.google.com/maps/documentation/static-maps/';
	   	printf( __( "<p>Steps:</p>
			<ol>
				<li>To use Google map images, you must create a Static Maps API Key. Create a free key by going here: <a href='%1\$s' target='_blank'>%2\$s</a> and clicking <strong>Get a Key</strong></li>
				<li>Once you've created your Google Static Maps API Key, enter the key below.
			</ol>", 'wp-strava' ), $maps_url, $maps_url );

	}

	public function print_strava_options() {
		?>
		<div class="wrap">
			<div id="icon-options-general" class="icon32"><br/></div>
			<h2><?php esc_html_e( 'Strava Settings', 'wp-strava' ); ?></h2>
					
			<form method="post" action="<?php echo admin_url( 'options.php' ); ?>">
				<?php settings_fields( $this->option_page ); ?>
				<?php do_settings_sections( 'wp-strava' ); ?>

				<p class="submit">
					<input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', 'wp-strava' ); ?>" />
				</p>
			</form>
		</div>
		<?php
	}

	public function print_client_input() {
		?><input type="text" id="strava_client_id" name="strava_client_id" value="" /><?php
	}

	public function print_secret_input() {
		?><input type="text" id="strava_client_secret" name="strava_client_secret" value="" /><?php
	}

	public function print_nickname_input() {
		$nickname = $this->tokens_empty( $this->tokens ) ? __( 'Default', 'wp-strava' ) : '';
		?><input type="text" name="strava_nickname[]" value="<?php echo $nickname; ?>" /><?php
	}

	public function print_token_input() {
		foreach ( $this->get_all_tokens() as $token => $nickname ) {
			?>
			<input type="text" name="strava_token[]" value="<?php echo $token; ?>" />
			<input type="text" name="strava_nickname[]" value="<?php echo $nickname; ?>" />
			<br/>
			<?php
		}
	}

	public function sanitize_client_id( $client_id ) {
		// Return early if not trying to add an additional athlete.
		if ( ! $this->adding_athlete ) {
			return $client_id;
		}

		if ( ! is_numeric( $client_id ) ) {
			add_settings_error( 'strava_client_id', 'strava_client_id', __( 'Client ID must be a number.', 'wp-strava' ) );
		}
		return $client_id;
	}

	public function sanitize_client_secret( $client_secret ) {
		// Return early if not trying to add an additional athlete.
		if ( ! $this->adding_athlete ) {
			return $client_secret;
		}

		if ( '' == trim( $client_secret ) ) {
			add_settings_error( 'strava_client_secret', 'strava_client_secret', __( 'Client Secret is required.', 'wp-strava' ) );
		}
		return $client_secret;
	}

	public function sanitize_nickname( $nicknames ) {
		$second2last = $last = false;
		if ( ! $this->adding_athlete ) {

			$last = end( $nicknames );
			$second2last = prev( $nicknames );

			$token_index = count( $nicknames ) - 1;
			if ( $last === '' && $second2last !== '' &&
			     empty( $_POST['strava_token'][ $token_index ] ) ) {
				// Remove last (blank) entry if not trying to add an additional athlete.
				array_pop( $nicknames );
			}
		}

		foreach ( $nicknames as $index => $nickname ) {
			if ( '' == trim( $nickname ) ) {
				add_settings_error( 'strava_nickname', 'strava_nickname', __( 'Nickname is required.', 'wp-strava' ) );
				return $nicknames;
			}
		}
		return $nicknames;
	}

	public function sanitize_token( $token ) {
		return $token;
	}

	private function fetch_token( $code ) {
		$client_id = $this->client_id;
		$client_secret = $this->client_secret;

		delete_option( 'strava_client_id' );
		delete_option( 'strava_client_secret' );

		if ( $client_id && $client_secret ) {
			require_once WPSTRAVA_PLUGIN_DIR . 'lib/API.class.php';
			$api = new WPStrava_API();
			$data = array( 'client_id' => $client_id, 'client_secret' => $client_secret, 'code' => $code );

			$strava_info = $api->post( 'oauth/token', $data );

			if ( $strava_info ) {
				if ( isset( $strava_info->access_token ) ) {
					$this->feedback .= __( 'Successfully authenticated.', 'wp-strava' );
					return $strava_info->access_token;
				} else {
					$this->feedback .= __( 'Authentication failed, please check your credentials.', 'wp-strava' );
					return false;
				}
			} else {
				$this->feedback .= __( sprintf( 'There was an error receiving data from Strava: %s', print_r( $strava_info, true ) ), 'wp-strava' );
				return false;
			}
		} else {
			$this->feedback .= __( 'Missing Client ID or Client Secret.', 'wp-strava' );
			return false;
		} // End if.
	}

	public function print_gmaps_key_input() {
		?><input type="text" id="strava_gmaps_key" name="strava_gmaps_key" value="<?php echo $this->gmaps_key; ?>" /><?php
	}

	public function sanitize_gmaps_key( $key ) {
		return $key;
	}

	public function print_som_input() {
		?>
		<select id="strava_som" name="strava_som">
			<option value="metric" <?php selected( $this->som, 'metric' ); ?>><?php esc_html_e( 'Metric', 'wp-strava' )?></option>
			<option value="english" <?php selected( $this->som, 'english' ); ?>><?php esc_html_e( 'English', 'wp-strava' )?></option>
		</select>
		<?php
	}

	public function sanitize_som( $som ) {
		return $som;
	}

	public function print_clear_input() {
		?><input type="checkbox" id="strava_cache_clear" name="strava_cache_clear" /><?php
	}

	public function sanitize_cache_clear( $checked ) {
		if ( 'on' === $checked ) {
			// Clear these (pre 1.2.0) values:
			delete_transient( 'strava_latest_map_ride' );
			delete_option( 'strava_latest_map_ride' );
			delete_option( 'strava_latest_map' );

			// Remove activity transients and options.
			foreach ( $this->get_tokens() as $token ) {
				delete_transient( 'strava_latest_map_activity_' . $token );
				delete_option( 'strava_latest_map_activity_' . $token );
				delete_option( 'strava_latest_map_' . $token );
			}
		}
		return null;
	}

	/**
	 * Gets all saved strava tokens as an array.
	 *
	 * @return array
	 * @author Justin Foell
	 * @since  1.2.0
	 */
	public function get_tokens() {
		$tokens = get_option( 'strava_token' );
		if ( ! is_array( $tokens ) ) {
			$tokens = array( $tokens );
		}

		foreach ( $tokens as $index => $token ) {
			if ( empty( $token ) ) {
				unset( $tokens[ $index ] );
				$tokens = array_values( $tokens ); // Rebase array keys after unset @see https://stackoverflow.com/a/5943165/2146022
			}
		}
		return $tokens;
	}

	/**
	 * Returns first (default) token saved.
	 *
	 * @return string|null
	 * @author Justin Foell
	 * @since  1.2.0
	 */
	public function get_default_token() {
		$tokens = $this->get_tokens();
		return isset( $tokens[0] ) ? $tokens[0] : null;
	}

	/**
	 * Get all tokens and their nicknames in one array.
	 *
	 * @return void
	 * @author Justin Foell
	 * @since  1.2.0
	 */
	public function get_all_tokens() {
		$tokens = $this->get_tokens();
		$nicknames = $this->nickname;
		$all = array();
		$number = 1;
		foreach ( $tokens as $index => $token ) {
			if ( ! empty( $nicknames[ $index ] ) ) {
				$all[ $token ] = $nicknames[ $index ];
			} else {
				$all[ $token ] = $this->get_default_nickname( $number );
			}
			$number++;
		}
		return $all;
	}

	/**
	 * Returns default nickname 'Default' / 'Athlete n'.
	 *
	 * @author Justin Foell
	 * @since  1.2.0
	 *
	 * @param integer $number
	 * @return string
	 */
	private function get_default_nickname( $number = 1 ) {
		// Translators: Athelete number if no nickname present.
		return ( 1 == $number ) ? __( 'Default', 'wp-strava' ) : sprintf( __( 'Athlete %s', 'wp-strava' ), $number );
	}

	/**
	 * Checks for valid tokens.
	 *
	 * @author Justin Foell
	 * @since  1.2.0
	 *
	 * @param  string|array Single token or array of tokens.
	 * @return boolean True if empty.
	 */
	public function tokens_empty( $tokens ) {
		if ( empty( $tokens ) ) {
			return true;
		}

		if ( is_array( $tokens ) ) {
			foreach( $tokens as $token ) {
				if ( ! empty( $token ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Only add a token if it's not already there.
	 *
	 * @param string $token
	 *
	 * @author Justin Foell
	 * @since  1.2.0
	 */
	public function add_token( $token ) {
		if ( false === array_search( $token, $this->tokens ) ) {
			$this->tokens[] = $token;
		}
	}

	public function __get( $name ) {
		if ( ! strpos( 'strava_', $name ) ) {
			$name = "strava_{$name}";
		}
		// Else.
		return get_option( $name );
	}

	public function settings_link( $links ) {
		$settings_link = '<a href="' . admin_url( "options-general.php?page={$this->page_name}" ) . '">' . __( 'Settings', 'wp-strava' ) . '</a>';
		$links[] = $settings_link;
		return $links;
	}

	public function settings_scripts() {
		$screen = get_current_screen();
		if ( "settings_page_{$this->page_name}" === $screen->id ) {
			wp_enqueue_script( 'wp-strava-settings', WPSTRAVA_PLUGIN_URL . 'js/wp-strava-settings.js', array( 'jquery' ) );
		}
	}
}
