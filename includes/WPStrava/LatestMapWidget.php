<?php

class WPStrava_LatestMapWidget extends WP_Widget {

	private $som;

	public function __construct() {
		$this->som = WPStrava_SOM::get_som();

		parent::__construct(
			false,
			__( 'Strava Latest Map', 'wp-strava' ), // Name
			array( 'description' => __( 'Strava latest activity using static google map image', 'wp-strava' ) ) // Args.
		);
	}

	public function form( $instance ) {
		// outputs the options form on admin
		$title          = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : __( 'Latest Activity Map', 'wp-strava' );
		$all_ids        = WPStrava::get_instance()->settings->get_all_ids();
		$client_id      = isset( $instance['client_id'] ) ? esc_attr( $instance['client_id'] ) : WPStrava::get_instance()->settings->get_default_id();
		$distance_min   = isset( $instance['distance_min'] ) ? esc_attr( $instance['distance_min'] ) : '';
		$strava_club_id = isset( $instance['strava_club_id'] ) ? esc_attr( $instance['strava_club_id'] ) : '';

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">
				<?php // Translator: Widget Title. ?>
				<?php esc_html_e( 'Title:', 'wp-strava' ); ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
				<label for="<?php echo $this->get_field_id( 'client_id' ); ?>"><?php _e( 'Athlete:', 'wp-strava' ); ?></label>
				<select name="<?php echo $this->get_field_name( 'client_id' ); ?>">
				<?php foreach ( $all_ids as $id => $nickname ) : ?>
					<option value="<?php echo $id; ?>"<?php selected( $id, $client_id ); ?>><?php echo $nickname; ?></option>
				<?php endforeach; ?>
				</select>
			</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'distance_min' ); ?>">
				<?php // Translators: Label for minimum distance input. ?>
				<?php echo sprintf( __( 'Min. Distance (%s):', 'wp-strava' ), $this->som->get_distance_label() ); ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'distance_min' ); ?>" name="<?php echo $this->get_field_name( 'distance_min' ); ?>" type="text" value="<?php echo $distance_min; ?>" />
		</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'strava_club_id' ); ?>"><?php esc_html_e( 'Club ID (leave blank to show Athlete):', 'wp-strava' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'strava_club_id' ); ?>" name="<?php echo $this->get_field_name( 'strava_club_id' ); ?>" type="text" value="<?php echo $strava_club_id; ?>" />
			</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		// Processes widget options to be saved from the admin.
		$instance                   = $old_instance;
		$instance['title']          = strip_tags( $new_instance['title'] );
		$instance['client_id']      = strip_tags( $new_instance['client_id'] );
		$instance['strava_club_id'] = strip_tags( $new_instance['strava_club_id'] );
		$instance['distance_min']   = strip_tags( $new_instance['distance_min'] );
		return $instance;
	}

	/**
	 * Method to render the widget on the front end.
	 *
	 * @param array $args     Arguments from the widget settings.
	 * @param array $instance Settings for this particular widget.
	 */
	public function widget( $args, $instance ) {

		$title          = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Latest Activity Map', 'wp-strava' ) : $instance['title'] );
		$client_id      = isset( $instance['client_id'] ) ? $instance['client_id'] : WPStrava::get_instance()->settings->get_default_id();
		$distance_min   = empty( $instance['distance_min'] ) ? 0 : absint( $instance['distance_min'] );
		$strava_club_id = empty( $instance['strava_club_id'] ) ? null : $instance['strava_club_id'];
		$build_new      = false;

		$id = empty( $strava_club_id ) ? $client_id : $strava_club_id;

		// Try our transient first.
		$activity_transient = get_transient( 'strava_latest_map_activity_' . $id );
		$activity_option    = get_option( 'strava_latest_map_activity_' . $id );

		$activity = $activity_transient ? $activity_transient : null;

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		if ( ! $activity || empty( $activity->map ) ) {
			$strava_activity = WPStrava::get_instance()->activity;

			$activities = array();

			try {
				$activities = $strava_activity->get_activities( $client_id, $strava_club_id );
			} catch ( WPStrava_Exception $e ) {
				if ( isset( $instance['athlete_token'] ) ) {
					// Translators: Message shown when using deprecated athlete_token parameter.
					echo wp_kses_post( __( 'The <code>athlete_token</code> parameter is deprecated as of WP-Strava version 2 and should be replaced with <code>client_id</code>.', 'wp-strava' ) );
				} else {
					echo $e->to_html();
				}
			}

			if ( ! empty( $activities ) ) {

				if ( ! empty( $distance_min ) ) {
					$activities = $strava_activity->get_activities_longer_than( $activities, $distance_min );
				}

				$activity = current( $activities );

				// Compare transient (temporary storage) to option (more permanent).
				// If the option isn't set or the transient is different, update the option.
				if ( empty( $activity_option->id ) || $activity->id != $activity_option->id ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					$build_new = true;
					$this->update_activity( $id, $activity );
				}

				// Update the transient if it needs updating.
				if ( empty( $activity_transient->id ) || $activity->id != $activity_transient->id ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					$this->update_activity_transient( $id, $activity );
				}
			}
		}

		if ( $activity ) {
			echo empty( $activity->map ) ?
				// Translators: Text with activity name shown in place of image if not available.
				sprintf( __( 'Map not available for activity "%s"', 'wp-strava' ), $activity->name ) :
				"<a title='{$activity->name}' href='" . WPStrava_Activity::ACTIVITIES_URL . "{$activity->id}'>" .
				$this->get_static_image( $id, $activity, $build_new ) .
				'</a>';
		}
		echo $args['after_widget'];
	}

	/**
	 * Get image for specific activity using Static Maps class.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @param string  $id        Client ID or Club ID.
	 * @param object  $activity  Activity to get image for.
	 * @param boolean $build_new Whether to refresh the image from cache.
	 * @return string            Image tag.
	 */
	private function get_static_image( $id, $activity, $build_new ) {
		$img = get_option( 'strava_latest_map_' . $id );

		if ( $build_new || ! $img ) {
			$img = WPStrava_StaticMap::get_image_tag( $activity );
			$this->update_map( $id, $img );
		}

		return $img;
	}

	/**
	 * Update map in option to cache.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.2.0
	 * @param string $id  Client ID or Club ID.
	 * @param string $img Image tag.
	 */
	private function update_map( $id, $img ) {
		update_option( 'strava_latest_map_' . $id, $img );
	}

	/**
	 * Update activity in option to cache.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.2.0
	 * @param string $id       Client ID or Club ID.
	 * @param object $activity stdClass Strava activity object.
	 */
	private function update_activity( $id, $activity ) {
		update_option( 'strava_latest_map_activity_' . $id, $activity );
	}

	/**
	 * Update activity in transient to cache.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.2.0
	 * @param string $id       CLient ID or Club ID.
	 * @param object $activity stdClass Strava activity object.
	 */
	private function update_activity_transient( $id, $activity ) {
		set_transient( 'strava_latest_map_activity_' . $id, $activity, HOUR_IN_SECONDS );
	}
}
