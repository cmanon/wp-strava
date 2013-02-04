<?php

class WPStrava_Settings {

	//register admin menus
	public function hook() {
		add_action( 'admin_init', array( $this, 'registerStravaSettings' ) );
		add_action( 'admin_menu', array( $this, 'addStravaMenu' ) );
	}
	
	public function addStravaMenu() {
		add_options_page( __( 'Strava Settings', 'wp-strava' ),
						  __( 'Strava', 'wp-strava' ),
						  'manage_options',
						  'wp-strava-options',
						  array( $this, 'printStravaOptions' ) );

	}

	public function registerStravaSettings() {
		register_setting('wp-strava-settings-group','strava_email', array( $this, 'sanitizeEmail' ) );
		register_setting('wp-strava-settings-group','strava_password', '__return_null' );
		register_setting('wp-strava-settings-group','strava_token', array( $this, 'sanitizeToken' ) );

		add_settings_section( 'strava_api', NULL, '__return_null', 'wp-strava' ); //NULL / __return_null no section label needed
		add_settings_field( 'strava_email', 'Strava Email', array( $this, 'printEmailInput' ), 'wp-strava', 'strava_api' );
		add_settings_field( 'strava_password', 'Strava Password', array( $this, 'printPasswordInput' ), 'wp-strava', 'strava_api' );
		add_settings_field( 'strava_token', 'Strava Token', array( $this, 'printTokenInput' ), 'wp-strava', 'strava_api' );
		
	}

	public function printStravaOptions() {
		?>
		<div class="wrap">
   			<div id="icon-options-general" class="icon32"><br/></div>
			<h2><?php _e( 'Strava Options', 'wp-strava' ); ?></h2>
			<p><?php _e( 'Please specify the options below in order to obtain an authentication token, this will work with the Strava shortcodes supported by this plugin, the widget options are independant.', 'wp-strava');?> </p>
		
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
			
	public function printEmailInput() {
		?><input type="text" id="strava_email" name="strava_email" value="<?php echo get_option('strava_email'); ?>" /><?php
	}

	public function printPasswordInput() {
		?>
			<input type="password" id="strava_password" name="strava_password" value="" />
			<p class="description"><?php _e( 'Your Password WILL NOT be saved. Only enter your password if you wish to retrieve a new API Token', 'wp-strava' ); ?></p>
		<?php
	}

	public function printTokenInput() {
		?><input type="text" id="strava_token" name="strava_token" value="<?php echo get_option('strava_token'); ?>" /><?php
	}

	public function sanitizeEmail( $email ) {
		if ( is_email( $email ) )
			return $email;
		add_settings_error( 'strava_email', 'strava_email', 'Invalid Email' );
		return NULL;
	}

	public function sanitizeToken( $token ) {
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

}

