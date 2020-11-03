<?php

/**
 * V3 - http://strava.github.io/api/v3/oauth/
 *
 * Set up an "API Application" at Strava
 * Save the Client ID and Client Secret in WordPress - redirect to strava oauth/authorize URL for permission
 * Get redirected back to this settings page with ?code= or ?error=
 * Use code to retrieve auth token
 */
class WPStrava_Settings {

	private $ids            = array();
	private $page_name      = 'wp-strava-options';
	private $option_page    = 'wp-strava-settings-group';
	private $adding_athlete = true;

	/**
	 * Register actions & filters for menus and authentication.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  0.62
	 */
	public function hook() {
		// Load IDs for any subsequent actions.
		$this->ids = $this->get_ids();

		add_action( 'admin_init', array( $this, 'register_strava_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_strava_menu' ) );
		add_filter( 'plugin_action_links_' . WPSTRAVA_PLUGIN_NAME, array( $this, 'settings_link' ) );
		add_action( 'in_plugin_update_message-wp-strava/wp-strava.php', array( $this, 'plugin_update_message' ), 10, 2 );
		add_action( 'after_plugin_row_wp-strava/wp-strava.php', array( $this, 'ms_plugin_update_message' ), 10, 2 );
	}

	/**
	 * Add the strava settings menu.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  0.62
	 */
	public function add_strava_menu() {
		add_options_page(
			__( 'Strava Settings', 'wp-strava' ),
			__( 'Strava', 'wp-strava' ),
			'manage_options',
			$this->page_name,
			array( $this, 'print_strava_options' )
		);
	}

	/**
	 * Register settings using the WP Settings API.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  0.62
	 */
	public function register_strava_settings() {
		add_settings_section( 'strava_api', __( 'Strava API', 'wp-strava' ), array( $this, 'print_api_instructions' ), 'wp-strava' );

		$this->adding_athlete = $this->is_adding_athlete();

		if ( $this->ids_empty( $this->ids ) ) {
			register_setting( $this->option_page, 'strava_client_id', array( $this, 'sanitize_client_id' ) );
			register_setting( $this->option_page, 'strava_client_secret', array( $this, 'sanitize_client_secret' ) );
			register_setting( $this->option_page, 'strava_nickname', array( $this, 'sanitize_nickname' ) );

			add_settings_field( 'strava_client_id', __( 'Strava Client ID', 'wp-strava' ), array( $this, 'print_client_input' ), 'wp-strava', 'strava_api' );
			add_settings_field( 'strava_client_secret', __( 'Strava Client Secret', 'wp-strava' ), array( $this, 'print_secret_input' ), 'wp-strava', 'strava_api' );
			add_settings_field( 'strava_nickname', __( 'Strava Nickname', 'wp-strava' ), array( $this, 'print_nickname_input' ), 'wp-strava', 'strava_api' );
		} else {
			register_setting( $this->option_page, 'strava_id', array( $this, 'sanitize_id' ) );
			add_settings_field( 'strava_id', __( 'Saved ID', 'wp-strava' ), array( $this, 'print_id_input' ), 'wp-strava', 'strava_api' );

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

		// Hide Options.
		register_setting( $this->option_page, 'strava_hide_time', array( $this, 'sanitize_hide_time' ) );
		add_settings_field( 'strava_hide_time', __( 'Time', 'wp-strava' ), array( $this, 'print_hide_time_input' ), 'wp-strava', 'strava_options' );
		register_setting( $this->option_page, 'strava_hide_elevation', array( $this, 'sanitize_hide_elevation' ) );
		add_settings_field( 'strava_hide_elevation', __( 'Elevation', 'wp-strava' ), array( $this, 'print_hide_elevation_input' ), 'wp-strava', 'strava_options' );

		// No Activity Links.
		register_setting( $this->option_page, 'strava_no_link', array( $this, 'sanitize_no_link' ) );
		add_settings_field( 'strava_no_link', __( 'Links', 'wp-strava' ), array( $this, 'print_no_link_input' ), 'wp-strava', 'strava_options' );

		// Clear cache.
		register_setting( $this->option_page, 'strava_cache_clear', array( $this, 'sanitize_cache_clear' ) );
		add_settings_section( 'strava_cache', __( 'Cache', 'wp-strava' ), null, 'wp-strava' );
		add_settings_field( 'strava_cache_clear', __( 'Clear cache', 'wp-strava' ), array( $this, 'print_clear_input' ), 'wp-strava', 'strava_cache' );
	}

	/**
	 * Print the Strava setup instructions.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  0.62
	 */
	public function print_api_instructions() {
		$settings_url = 'https://www.strava.com/settings/api';
		$icon_url     = 'https://plugins.svn.wordpress.org/wp-strava/assets/icon-128x128.png';
		$blog_name    = get_bloginfo( 'name' );

		// Translators: Strava "app" name
		$app_name = sprintf( __( '%s Strava', 'wp-strava' ), $blog_name );
		$site_url = site_url();

		// Translators: Strava "app" description
		$description = sprintf( __( 'WP-Strava for %s', 'wp-strava' ), $blog_name );
		echo wp_kses_post(
			sprintf(
				__(
					"<p>Steps:</p>
					<ol>
						<li>Create your free API Application/Connection here: <a href='%1\$s'>%2\$s</a> using the following information:</li>
						<ul>
							<li>App Icon: <strong>upload <a href='%3\$s'>this image</a></strong></li>
							<li>Application Name: <strong>%4\$s</strong></li>
							<li>Category: OK to leave at default 'other'</li>
							<li>Club: OK to leave blank</li>
							<li>Website: <strong>%5\$s</strong></li>
							<li>Application Description: <strong>%6\$s</strong></li>
							<li>Authorization Callback Domain: <strong>%7\$s</strong></li>
						</ul>
						<li>Once you've created your API Application at strava.com, enter the <strong>Client ID</strong> and <strong>Client Secret</strong> below, which can now be found on that same strava API Settings page.
						<li>After saving your Client ID and Secret, you'll be redirected to strava to authorize your API Application. If successful, your Strava ID will display in a table, next to your nickname.</li>
						<li>If you need to re-authorize your API Application, erase your Strava ID next to your nickname and click 'Save Changes' to start over.</li>
					</ol>",
					'wp-strava'
				),
				$settings_url,
				$settings_url,
				$icon_url,
				$app_name,
				$site_url,
				$description,
				wp_parse_url( $site_url, PHP_URL_HOST )
			)
		);
	}

	/**
	 * Print the google maps instructions.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.1
	 */
	public function print_gmaps_instructions() {
		$maps_url = 'https://developers.google.com/maps/documentation/static-maps/';
		echo wp_kses_post(
			sprintf(
				__(
					"<p>Steps:</p>
					<ol>
						<li>To use Google map images, you must create a Static Maps API Key. Create a free key by going here: <a href='%1\$s'>%2\$s</a> and clicking <strong>Get a Key</strong></li>
						<li>Once you've created your Google Static Maps API Key, enter the key below.
					</ol>",
					'wp-strava'
				),
				$maps_url,
				$maps_url
			)
		);
	}

	/**
	 * Print the settings page container.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  0.62
	 */
	public function print_strava_options() {
		include WPSTRAVA_PLUGIN_DIR . 'templates/admin-settings.php';
	}

	/**
	 * Print the client ID input
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.2.0
	 */
	public function print_client_input() {
		?>
		<input type="text" id="strava_client_id" name="strava_client_id" value="" />
		<?php
	}

	/**
	 * Print the client secret input
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.2.0
	 */
	public function print_secret_input() {
		?>
		<input type="text" id="strava_client_secret" name="strava_client_secret" value="" />
		<?php
	}

	/**
	 * Print the nickname input
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.2.0
	 */
	public function print_nickname_input() {
		$nickname = $this->ids_empty( $this->ids ) ? __( 'Default', 'wp-strava' ) : '';
		?>
		<input type="text" name="strava_nickname[]" value="<?php echo esc_attr( $nickname ); ?>" />
		<?php
	}

	/**
	 * Print the strava ID(s).
	 *
	 * Renamed from print_token_input().
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.0.0
	 */
	public function print_id_input() {
		$first = true;
		foreach ( $this->get_all_ids() as $id => $nickname ) {
			?>
			<input type="text" name="strava_id[]" value="<?php echo esc_attr( $id ); ?>" />
			<input type="text" name="strava_nickname[]" value="<?php echo esc_attr( $nickname ); ?>" />
			<?php
			if ( $first ) :
				?>
				<span class="default-id"><?php esc_html_e( 'Default', 'wp-strava' ); ?></span>
				<?php
			endif;
			$first = false;
			?>
			<br/>
			<?php
		}
	}

	/**
	 * Sanitize the client ID.
	 *
	 * @param string $client_id
	 * @return string
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.2.0
	 */
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

	/**
	 * Sanitize the client secret.
	 *
	 * @param string $client_secret
	 * @return string
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.2.0
	 */
	public function sanitize_client_secret( $client_secret ) {
		// Return early if not trying to add an additional athlete.
		if ( ! $this->adding_athlete ) {
			return $client_secret;
		}

		if ( '' === trim( $client_secret ) ) {
			add_settings_error( 'strava_client_secret', 'strava_client_secret', __( 'Client Secret is required.', 'wp-strava' ) );
		}
		return $client_secret;
	}

	/**
	 * Sanitize the nicknames - make sure we've got the same number of nicknames and IDs.
	 *
	 * @param array $nicknames Nicknames for the athletes saved.
	 * @return array
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.2.0
	 */
	public function sanitize_nickname( $nicknames ) {
		if ( ! $this->adding_athlete ) {

			$input_args = array(
				'strava_id' => array(
					'filter' => FILTER_SANITIZE_NUMBER_INT,
					'flags'  => FILTER_REQUIRE_ARRAY,
				),
			);

			$input = filter_input_array( INPUT_POST, $input_args );

			// All IDs have been removed.
			if ( empty( $input['strava_id'] ) ) {
				return array();
			}

			// Chop $nicknames to same size as ids.
			$nicknames = array_slice( $nicknames, 0, count( $input['strava_id'] ) );

			// Remove indexes from $nicknames that have empty ids.
			foreach ( $input['strava_id'] as $index => $id ) {
				$id = trim( $id );
				if ( empty( $id ) ) {
					unset( $nicknames[ $index ] );
				}
			}

			// Process $nicknames so indexes start with zero.
			$nicknames = array_merge( $nicknames, array() );
		}

		foreach ( $nicknames as $index => $nickname ) {
			if ( '' === trim( $nickname ) ) {
				add_settings_error( 'strava_nickname', 'strava_nickname', __( 'Nickname is required.', 'wp-strava' ) );
				return $nicknames;
			}
		}
		return $nicknames;
	}

	/**
	 * Sanitize the ID.
	 *
	 * Renamed from sanitize_token().
	 *
	 * @param string $id Client ID.
	 * @return string
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.0
	 */
	public function sanitize_id( $id ) {
		return $id;
	}

	/**
	 * Print the GMaps key input.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.1
	 */
	public function print_gmaps_key_input() {
		?>
		<input type="text" id="strava_gmaps_key" name="strava_gmaps_key" value="<?php echo esc_attr( $this->gmaps_key ); ?>" />
		<?php
	}

	/**
	 * Sanitize GMaps key input.
	 *
	 * @param string $key
	 * @return string
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.1
	 */
	public function sanitize_gmaps_key( $key ) {
		return $key;
	}

	/**
	 * Print System of Measure option.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  0.62
	 */
	public function print_som_input() {
		?>
		<select id="strava_som" name="strava_som">
			<option value="metric" <?php selected( $this->som, 'metric' ); ?>><?php esc_html_e( 'Metric', 'wp-strava' ); ?></option>
			<option value="english" <?php selected( $this->som, 'english' ); ?>><?php esc_html_e( 'English', 'wp-strava' ); ?></option>
		</select>
		<?php
	}

	/**
	 * Sanitize System of Measure input.
	 *
	 * @param string $som Input from System of Measure dropdown.
	 * @return string
	 * @author Justin Foell <justin@foell.org>
	 * @since  0.62
	 */
	public function sanitize_som( $som ) {
		return $som;
	}

	/**
	 * Display the Hide Time Checkbox.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.7.1
	 */
	public function print_hide_time_input() {
		?>
		<label for="strava_hide_time"><input type="checkbox" id="strava_hide_time" name="strava_hide_time" <?php checked( $this->hide_time, 'on' ); ?>/>
		<?php esc_html_e( 'Do not show time on activities', 'wp-strava' ); ?></label>
		<?php
	}

	/**
	 * Sanitize the Hide Time Checkbox.
	 *
	 * @param string $checked 'on' or null.
	 * @return string 'on' if checked.
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.7.1
	 */
	public function sanitize_hide_time( $checked ) {
		if ( 'on' === $checked ) {
			return $checked;
		}
		return null;
	}

	/**
	 * Display the Hide Elevation Checkbox.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.7.2
	 */
	public function print_hide_elevation_input() {
		?>
		<label for="strava_hide_elevation"><input type="checkbox" id="strava_hide_elevation" name="strava_hide_elevation" <?php checked( $this->hide_elevation, 'on' ); ?>/>
		<?php esc_html_e( 'Do not show elevation on activities', 'wp-strava' ); ?></label>
		<?php
	}

	/**
	 * Sanitize the Hide Elevation Checkbox.
	 *
	 * @param string $checked 'on' or null.
	 * @return string 'on' if checked.
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.7.2
	 */
	public function sanitize_hide_elevation( $checked ) {
		if ( 'on' === $checked ) {
			return $checked;
		}
		return null;
	}

	/**
	 * Display the No Links Checkbox.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.3.2
	 */
	public function print_no_link_input() {
		?>
		<label for="strava_no_link"><input type="checkbox" id="strava_no_link" name="strava_no_link" <?php checked( $this->no_link, 'on' ); ?>/>
		<?php esc_html_e( 'Do not link activities to Strava.com', 'wp-strava' ); ?></label>
		<?php
	}

	/**
	 * Sanitize the No Links Checkbox.
	 *
	 * @param string $checked 'on' or null.
	 * @return string 'on' if checked.
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.3.2
	 */
	public function sanitize_no_link( $checked ) {
		if ( 'on' === $checked ) {
			return $checked;
		}
		return null;
	}

	/**
	 * Print checkbox option to clear cache.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.1
	 */
	public function print_clear_input() {
		?>
		<label for="strava_cache_clear"><input type="checkbox" id="strava_cache_clear" name="strava_cache_clear" />
		<?php esc_html_e( 'Clear cached image and transient data', 'wp-strava' ); ?></label>
		<p class="description"><?php esc_html_e( 'To clear cache, check this box and click "Save Changes"' ); ?></p>
		<?php
	}

	/**
	 * Clear Strava cache if checkbox is checked.
	 *
	 * @param string $checked Clear cache checkbox status.
	 * @return void
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.1
	 */
	public function sanitize_cache_clear( $checked ) {
		if ( 'on' === $checked ) {
			global $wpdb;

			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE `option_name` LIKE '_transient_timeout_strava_api_data_%'" );
			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE `option_name` LIKE '_transient_strava_api_data_%'" );
			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE `option_name` LIKE '_transient_timeout_strava_latest_map_%'" );
			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE `option_name` LIKE '_transient_strava_latest_map_%'" );
			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE `option_name` LIKE 'strava_latest_map%'" );

			// Old options.
			delete_option( 'strava_token' );
			delete_option( 'strava_email' );
			delete_option( 'strava_password' );
		}
		return null;
	}

	/**
	 * Gets all saved strava ids as an array.
	 *
	 * @return array
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.0.0
	 */
	public function get_ids() {
		$ids = get_option( 'strava_id' );
		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		foreach ( $ids as $index => $id ) {
			if ( empty( $id ) ) {
				unset( $ids[ $index ] );
				$ids = array_values( $ids ); // Rebase array keys after unset @see https://stackoverflow.com/a/5943165/2146022
			}
		}
		return $ids;
	}

	/**
	 * Returns first (default) ID saved.
	 *
	 * @return string|null
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.2.0
	 */
	public function get_default_id() {
		$ids = $this->get_ids();
		return isset( $ids[0] ) ? $ids[0] : null;
	}

	/**
	 * Get all IDs and their nicknames in one array.
	 *
	 * @return array Array of IDs and nicknames.
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.0.0
	 */
	public function get_all_ids() {
		$ids       = $this->get_ids();
		$nicknames = $this->nickname;
		$all       = array();
		$number    = 1;
		foreach ( $ids as $index => $id ) {
			if ( ! empty( $nicknames[ $index ] ) ) {
				$all[ $id ] = $nicknames[ $index ];
			} else {
				$all[ $id ] = $this->get_default_nickname( $number );
			}
			$number++;
		}
		return $all;
	}

	/**
	 * Returns default nickname 'Default' / 'Athlete n'.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.2.0
	 *
	 * @param integer $number Athlete number (default 1).
	 * @return string
	 */
	private function get_default_nickname( $number = 1 ) {
		// Translators: Athlete number if no nickname present.
		return ( 1 === $number ) ? __( 'Default', 'wp-strava' ) : sprintf( __( 'Athlete %s', 'wp-strava' ), $number );
	}

	/**
	 * Checks for valid IDs.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.2.0
	 *
	 * @param  string|array Single ID or array of IDs.
	 * @return boolean True if empty.
	 */
	public function ids_empty( $ids ) {
		if ( empty( $ids ) ) {
			return true;
		}

		if ( is_array( $ids ) ) {
			foreach ( $ids as $id ) {
				if ( ! empty( $id ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Add an ID if it's not already there, and save to the DB.
	 *
	 * @param string $id
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.0.0
	 */
	public function add_id( $id ) {
		if ( false === array_search( $id, $this->ids, true ) ) {
			$this->ids[] = $id;
			update_option( 'strava_id', $this->ids );
		}
	}

	/**
	 * Update options with new Client ID and Info.
	 *
	 * @param int $id Strava API Client ID
	 * @param string $secret Strava API Client Secret
	 * @param stdClass $info
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.0.0
	 */
	public function save_info( $id, $secret, $info ) {
		$infos = get_option( 'strava_info' );
		$infos = empty( $infos ) ? array() : $infos;
		$infos = array_filter( $infos, array( $this, 'filter_by_id' ), ARRAY_FILTER_USE_KEY ); // Remove old IDs.

		$info->client_secret = $secret;
		$infos[ $id ]        = $info;
		update_option( 'strava_info', $infos );
	}

	/**
	 * array_filter() callback to remove info for IDs we no longer have.
	 *
	 * @param int $key Strava Client ID
	 * @return boolean True if Client ID is in $this->ids, false otherwise.
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.0.0
	 */
	public function filter_by_id( $key ) {
		if ( in_array( $key, $this->ids ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict -- Loose comparison OK.
			return true;
		}
		return false;
	}

	/**
	 * Remove the client ID and Secret (they're saved in the strava_info option).
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.0.0
	 */
	public function delete_id_secret() {
		delete_option( 'strava_client_id' );
		delete_option( 'strava_client_secret' );
	}

	/**
	 * Check to see if settings have been updated.
	 *
	 * @param array $value Data array from pre_set_transient_settings_errors filter.
	 * @return boolean
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.0.0
	 */
	public function is_settings_updated( $value ) {
		return ( isset( $value[0]['type'] ) && ( 'updated' === $value[0]['type'] || 'success' === $value[0]['type'] ) );
	}

	/**
	 * Whether or not we're on the options page.
	 *
	 * @return boolean
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.0.0
	 */
	public function is_option_page() {
		return filter_input( INPUT_POST, 'option_page', FILTER_SANITIZE_STRING ) === $this->option_page;
	}

	/**
	 * Whether or not we're on the WP-Strava settings page.
	 *
	 * @return boolean
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.0.0
	 */
	public function is_settings_page() {
		return filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING ) === $this->page_name;
	}

	/**
	 * Get the WP-Strava settings page name.
	 *
	 * @return string
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.0.0
	 */
	public function get_page_name() {
		return $this->page_name;
	}

	/**
	 * Whether or not we're adding a new athlete.
	 *
	 * @return boolean
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.0.0
	 */
	private function is_adding_athlete() {
		return filter_input( INPUT_POST, 'strava_client_id', FILTER_SANITIZE_NUMBER_INT ) && filter_input( INPUT_POST, 'strava_client_secret', FILTER_SANITIZE_STRING );
	}

	/**
	 * Getter for Strava settings in wp_options.
	 *
	 * @param string $name Option name without the 'strava_' prefix.
	 * @return mixed
	 * @since  0.62
	 */
	public function __get( $name ) {
		if ( ! strpos( 'strava_', $name ) ) {
			$name = "strava_{$name}";
		}
		// Else.
		return get_option( $name );
	}

	/**
	 * Link to the settings on the plugin list page.
	 *
	 * @param array $links Array of plugin links.
	 * @return array Links with settings added.
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.0
	 */
	public function settings_link( $links ) {
		$settings_link = '<a href="' . admin_url( "options-general.php?page={$this->page_name}" ) . '">' . __( 'Settings', 'wp-strava' ) . '</a>';
		$links[]       = $settings_link;
		return $links;
	}

	/**
	 * Plugin Upgrade Notice.
	 *
	 * @param array $data     Plugin data with readme additions.
	 * @param array $response Response from wp.org.
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.7.3
	 */
	public function plugin_update_message( $data, $response ) {
		if ( isset( $data['upgrade_notice'] ) ) {
			echo wp_kses_post( $data['upgrade_notice'] );
		}
	}

	/**
	 * Plugin Upgrade Notice (multisite).
	 *
	 * @param string $file   Relative path to plugin, i.e. wp-strava/wp-strava.php.
	 * @param array  $plugin Plugin data with readme additions.
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.7.3
	 */
	public function ms_plugin_update_message( $file, $plugin ) {
		if ( is_multisite() && ! is_network_admin() && version_compare( $plugin['Version'], $plugin['new_version'], '<' ) ) {
			$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
			echo wp_kses_post(
				sprintf(
					'<tr class="plugin-update-tr"><td colspan="%s" class="plugin-update update-message notice inline notice-warning notice-alt"><div class="update-message"><h4 style="margin: 0; font-size: 14px;">%s</h4>%s</div></td></tr>',
					$wp_list_table->get_column_count(),
					$plugin['Name'],
					$plugin['upgrade_notice']
				)
			);
		}
	}

}
