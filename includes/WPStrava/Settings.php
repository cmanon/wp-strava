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

	//register admin menus
	public function hook() {
		add_action( 'admin_init', array( $this, 'register_strava_settings' ), 20 );
		add_action( 'admin_menu', array( $this, 'add_strava_menu' ) );
		add_filter( 'plugin_action_links_' . WPSTRAVA_PLUGIN_NAME, array( $this, 'settings_link' ) );
	}

	public function add_strava_menu() {
		add_options_page(
			__( 'Strava Settings', 'wp-strava' ),
			__( 'Strava', 'wp-strava' ),
			'manage_options',
			$this->page_name,
			array( $this, 'print_strava_options' )
		);
	}

	public function register_strava_settings() {
		add_settings_section( 'strava_api', __( 'Strava API', 'wp-strava' ), array( $this, 'print_api_instructions' ), 'wp-strava' );

		$this->adding_athlete = $this->is_adding_athlete();
		$this->ids            = $this->get_ids();

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

		// Hide Time Option.
		register_setting( $this->option_page, 'strava_hide_time', array( $this, 'sanitize_hide_time' ) );
		add_settings_field( 'strava_hide_time', __( 'Hide Activity Time', 'wp-strava' ), array( $this, 'print_hide_time_input' ), 'wp-strava', 'strava_options' );

		// Clear cache.
		register_setting( $this->option_page, 'strava_cache_clear', array( $this, 'sanitize_cache_clear' ) );
		add_settings_section( 'strava_cache', __( 'Cache', 'wp-strava' ), null, 'wp-strava' );
		add_settings_field( 'strava_cache_clear', __( 'Clear cache (images & transient data)', 'wp-strava' ), array( $this, 'print_clear_input' ), 'wp-strava', 'strava_cache' );
	}

	public function print_api_instructions() {
		$settings_url = 'https://www.strava.com/settings/api';
		$icon_url     = 'https://plugins.svn.wordpress.org/wp-strava/assets/icon-128x128.png';
		$blog_name    = get_bloginfo( 'name' );

		// Translators: Strava "app" name
		$app_name = sprintf( __( '%s Strava', 'wp-strava' ), $blog_name );
		$site_url = site_url();

		// Translators: Strava "app" description
		$description = sprintf( __( 'WP-Strava for %s', 'wp-strava' ), $blog_name );
		printf( __( "<p>Steps:</p>
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
				<li>To use Google map images, you must create a Static Maps API Key. Create a free key by going here: <a href='%1\$s'>%2\$s</a> and clicking <strong>Get a Key</strong></li>
				<li>Once you've created your Google Static Maps API Key, enter the key below.
			</ol>", 'wp-strava' ), $maps_url, $maps_url );
	}

	public function print_strava_options() {
		include WPSTRAVA_PLUGIN_DIR . 'templates/admin-settings.php';
	}

	public function print_client_input() {
		?>
		<input type="text" id="strava_client_id" name="strava_client_id" value="" />
		<?php
	}

	public function print_secret_input() {
		?>
		<input type="text" id="strava_client_secret" name="strava_client_secret" value="" />
		<?php
	}

	public function print_nickname_input() {
		$nickname = $this->ids_empty( $this->ids ) ? __( 'Default', 'wp-strava' ) : '';
		?>
		<input type="text" name="strava_nickname[]" value="<?php echo $nickname; ?>" />
		<?php
	}

	public function print_id_input() {
		foreach ( $this->get_all_ids() as $id => $nickname ) {
			?>
			<input type="text" name="strava_id[]" value="<?php echo esc_attr( $id ); ?>" />
			<input type="text" name="strava_nickname[]" value="<?php echo esc_attr( $nickname ); ?>" />
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

		if ( '' === trim( $client_secret ) ) {
			add_settings_error( 'strava_client_secret', 'strava_client_secret', __( 'Client Secret is required.', 'wp-strava' ) );
		}
		return $client_secret;
	}

	public function sanitize_nickname( $nicknames ) {
		if ( ! $this->adding_athlete ) {

			// Chop $nicknames to same size as ids.
			$nicknames = array_slice( $nicknames, 0, count( $_POST['strava_id'] ) );

			// Remove indexes from $nicknames that have empty ids.
			foreach ( $_POST['strava_id'] as $index => $id ) {
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

	public function sanitize_id( $id ) {
		return $id;
	}

	public function print_gmaps_key_input() {
		?>
		<input type="text" id="strava_gmaps_key" name="strava_gmaps_key" value="<?php echo $this->gmaps_key; ?>" />
		<?php
	}

	public function sanitize_gmaps_key( $key ) {
		return $key;
	}

	public function print_som_input() {
		?>
		<select id="strava_som" name="strava_som">
			<option value="metric" <?php selected( $this->som, 'metric' ); ?>><?php esc_html_e( 'Metric', 'wp-strava' ); ?></option>
			<option value="english" <?php selected( $this->som, 'english' ); ?>><?php esc_html_e( 'English', 'wp-strava' ); ?></option>
		</select>
		<?php
	}

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
		<input type="checkbox" id="strava_hide_time" name="strava_hide_time" <?php checked( $this->hide_time, 'on' ); ?>/>
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

	public function print_clear_input() {
		?>
		<input type="checkbox" id="strava_cache_clear" name="strava_cache_clear" />
		<?php
	}

	public function sanitize_cache_clear( $checked ) {
		if ( 'on' === $checked ) {
			global $wpdb;

			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE `option_name` LIKE '_transient_timeout_strava_latest_map_%'" );
			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE `option_name` LIKE '_transient_strava_latest_map_%'" );
			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE `option_name` LIKE 'strava_latest_map%'" );

			delete_option( 'strava_token' );
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
	 * @return void
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
	 * Undocumented function
	 *
	 * @param int $id Strava API Client ID
	 * @param string $secret Strava API Client Secret
	 * @param stdClass $info
	 * @author Justin Foell <justin.foell@webdevstudios.com>
	 * @since  2.0.0
	 */
	public function save_info( $id, $secret, $info ) {
		$infos = get_option( 'strava_info', array() );
		$infos = array_filter( $infos, array( $this, 'filter_by_id' ), ARRAY_FILTER_USE_KEY ); // Remove old IDs.
		$info->client_secret = $secret;
		$infos[ $id ] = $info;
		update_option( 'strava_info', $infos );
	}

	/**
	 * array_filter() callback to remove info for IDs we no longer have.
	 *
	 * @param int $key Strava Client ID
	 * @return boolean True if Client ID is in $this->ids, false otherwise.
	 * @author Justin Foell <justin.foell@webdevstudios.com>
	 * @since  2.0.0
	 */
	public function filter_by_id( $key ) {
		if ( in_array( $key, $this->ids ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 * @author Justin Foell <justin.foell@webdevstudios.com>
	 * @since  2.0.0
	 */
	public function delete_id_secret() {
		delete_option( 'strava_client_id' );
		delete_option( 'strava_client_secret' );
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $value
	 * @return boolean
	 * @author Justin Foell <justin.foell@webdevstudios.com>
	 * @since  2.0.0
	 */
	public function is_settings_updated( $value ) {
		return isset( $value[0]['type'] ) && 'updated' === $value[0]['type'];
	}

	/**
	 * Undocumented function
	 *
	 * @return boolean
	 * @author Justin Foell <justin.foell@webdevstudios.com>
	 * @since  2.0.0
	 */
	public function is_option_page() {
		return isset( $_POST['option_page'] ) && $_POST['option_page'] === $this->option_page;
	}

	/**
	 * Undocumented function
	 *
	 * @return boolean
	 * @author Justin Foell <justin.foell@webdevstudios.com>
	 * @since  2.0.0
	 */
	public function is_settings_page() {
		return isset( $_GET['page'] ) && $_GET['page'] === $this->page_name;
	}

	/**
	 * Undocumented function
	 *
	 * @return string
	 * @author Justin Foell <justin.foell@webdevstudios.com>
	 * @since  2.0.0
	 */
	public function get_page_name() {
		return $this->page_name;
	}

	/**
	 * Undocumented function
	 *
	 * @return boolean
	 * @author Justin Foell <justin.foell@webdevstudios.com>
	 * @since  2.0.0
	 */
	private function is_adding_athlete() {
		return ! ( empty( $_POST['strava_client_id'] ) && empty( $_POST['strava_client_secret'] ) );
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
		$links[]       = $settings_link;
		return $links;
	}
}
