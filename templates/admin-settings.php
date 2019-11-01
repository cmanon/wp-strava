<div class="wrap">
	<div id="icon-options-general" class="icon32"><br/></div>
	<h2><?php esc_html_e( 'Strava Settings', 'wp-strava' ); ?></h2>

	<form method="post" action="<?php echo esc_attr( admin_url( 'options.php' ) ); ?>">
		<?php settings_fields( $this->option_page ); ?>
		<?php do_settings_sections( 'wp-strava' ); ?>

		<p class="submit">
			<input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', 'wp-strava' ); ?>" />
		</p>
	</form>
</div>
