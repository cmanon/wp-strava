<?php

class WPStrava_Settings {

	//register admin menus
	public function hook() {
		add_action( 'admin_init', array( $this, 'register_strava_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_strava_menu' ) );
	}
	
	public function add_strava_menu() {
		add_options_page( __( 'Strava Settings', 'wp-strava' ),
						  __( 'Strava', 'wp-strava' ),
						  'manage_options',
						  'wp-strava-options',
						  array( $this, 'print_strava_options' ) );
	}

	public function register_strava_settings() {
		register_setting('wp-strava-settings-group','strava_email',    array( $this, 'sanitize_email' ) );
		register_setting('wp-strava-settings-group','strava_password', NULL );
		register_setting('wp-strava-settings-group','strava_token',    array( $this, 'sanitize_token' ) );

		add_settings_section( 'strava_api', __( 'Strava API', 'wp-strava' ), array( $this, 'print_api_instructions' ), 'wp-strava' ); //NULL / NULL no section label needed

		add_settings_field( 'strava_email',    __( 'Strava Email', 'wp-strava' ),    array( $this, 'print_email_input' ),    'wp-strava', 'strava_api' );
		add_settings_field( 'strava_password', __( 'Strava Password', 'wp-strava' ), array( $this, 'print_password_input' ), 'wp-strava', 'strava_api' );
		add_settings_field( 'strava_token',    __( 'Strava Token', 'wp-strava' ),    array( $this, 'print_token_input' ),    'wp-strava', 'strava_api' );

		register_setting('wp-strava-settings-group','strava_som',    array( $this, 'sanitize_som' ) );

		add_settings_section( 'strava_options', __( 'Options', 'wp-strava' ), NULL, 'wp-strava' );

		add_settings_field( 'strava_som', __( 'System of Measurement', 'wp-strava' ), array( $this, 'print_som_input' ), 'wp-strava', 'strava_options' );
	}

	public function print_api_instructions() {
		?><p><?php _e( 'Please specify the options below in order to obtain an authentication token, this will work with the Strava shortcodes supported by this plugin, the widget options are independant.', 'wp-strava');?> </p><?php
	}
	
	public function print_strava_options() {
		?>
		<div class="wrap">
   			<div id="icon-options-general" class="icon32"><br/></div>
			<h2><?php _e( 'Strava Settings', 'wp-strava' ); ?></h2>
					
			<form method="post" action="<?php echo admin_url( 'options.php' ); ?>">
				<?php settings_fields( 'wp-strava-settings-group' ); ?>
				<?php do_settings_sections( 'wp-strava' ); ?>

				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ); ?>" />
				</p>
			</form>
		</div>
		<?php
	}
			
	public function print_email_input() {
		?><input type="text" id="strava_email" name="strava_email" value="<?php echo get_option('strava_email'); ?>" /><?php
	}

	public function print_password_input() {
		?>
			<input type="password" id="strava_password" name="strava_password" value="" />
			<p class="description"><?php _e( 'Your Password WILL NOT be saved. Only enter your password if you wish to retrieve a new API Token', 'wp-strava' ); ?></p>
		<?php
	}

	public function print_token_input() {
		?><input type="text" id="strava_token" name="strava_token" value="<?php echo get_option('strava_token'); ?>" /><?php
	}

	public function sanitize_email( $email ) {
		if ( is_email( $email ) )
			return $email;
		add_settings_error( 'strava_email', 'strava_email', 'Invalid Email' );
		return NULL;
	}

	public function sanitize_token( $token ) {
		if ( ! empty( $_POST['strava_password'] ) ) {
			require_once WPSTRAVA_PLUGIN_DIR . 'lib/Rides.class.php';
			$ride = new WPStrava_Rides();
			$email = get_option( 'strava_email' );
			$token = $ride->getAuthenticationToken( $email, $_POST['strava_password'] );
			if ( $token ) {
				add_settings_error( 'strava_token', 'strava_token', sprintf( __( 'New Strava Token Retrieved: %s', 'wp-strava' ), $ride->feedback ) , 'updated' );
				return $token;
			} else {
				add_settings_error( 'strava_token', 'strava_token', $ride->feedback );
				return NULL;
			}
		}
						
		return $token;
	}

	public function print_options_label() {
		?><p>Options</p><?php
	}

	public function print_som_input() {
		$strava_som = get_option( 'strava_som' );
		?>
				<select id="strava_som" name="strava_som">
					<option value="metric" <?php selected( $strava_som, 'metric' ); ?>><?php _e( 'Metric', 'wp-strava' )?></option>
					<option value="english" <?php selected( $strava_som, 'english' ); ?>><?php _e( 'English', 'wp-strava' )?></option>
				</select>
		<?php
	}

	public function sanitize_som( $som ) {
		return $som;
	}

	public function __get( $name ) {
		return get_option( "strava_{$name}" );
	}
}