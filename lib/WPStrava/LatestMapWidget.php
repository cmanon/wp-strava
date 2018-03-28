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
		$all_tokens     = WPStrava::get_instance()->settings->get_all_tokens();
		$athlete_token  = isset( $instance['athlete_token'] ) ? esc_attr( $instance['athlete_token'] ) : WPStrava::get_instance()->settings->get_default_token();
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
				<label for="<?php echo $this->get_field_id( 'athlete_token' ); ?>"><?php _e( 'Athlete:', 'wp-strava' ); ?></label>
				<select name="<?php echo $this->get_field_name( 'athlete_token' ); ?>">
				<?php foreach ( $all_tokens as $token => $nickname ) : ?>
					<option value="<?php echo $token; ?>"<?php selected( $token, $athlete_token ); ?>><?php echo $nickname; ?></option>
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
		$instance['athlete_token']  = strip_tags( $new_instance['athlete_token'] );
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
		$athlete_token  = isset( $instance['athlete_token'] ) ? $instance['athlete_token'] : WPStrava::get_instance()->settings->get_default_token();
		$distance_min   = empty( $instance['distance_min'] ) ? 0 : absint( $instance['distance_min'] );
		$strava_club_id = empty( $instance['strava_club_id'] ) ? null : $instance['strava_club_id'];
		$build_new      = false;

		$id = empty( $strava_club_id ) ? $athlete_token : $strava_club_id;

		// Try our transient first.
		$activity_transient = get_transient( 'strava_latest_map_activity_' . $id );
		$activity_option    = get_option( 'strava_latest_map_activity_' . $id );

		$activity = $activity_transient ? $activity_transient : null;

		if ( ! $activity || empty( $activity->map ) ) {
			$strava_activity = WPStrava::get_instance()->activity;
			$activities      = $strava_activity->get_activities( $athlete_token, $strava_club_id );

			if ( is_wp_error( $activities ) ) {
				echo $args['before_widget'];
				if ( $title ) {
					echo $args['$before_title'] . $title . $args['$after_title'];
				}

				if ( WPSTRAVA_DEBUG ) {
					echo '<pre>';
					print_r( $activities ); // phpcs:ignore -- Debug output.
					echo '</pre>';
				} else {
					echo $activities->get_error_message();
				}
				echo $args['$after_widget'];
				return;
			}

			if ( ! empty( $activities ) ) {

				if ( ! empty( $distance_min ) ) {
					$activities = $strava_activity->get_activities_longer_than( $activities, $distance_min );
				}

				$activity = current( $activities );

				// Compare transient (temporary storage) to option (more permenant).
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
			echo $args['before_widget'];
			if ( $title ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}

			echo empty( $activity->map ) ?
				// Translators: Text with activity name shown in place of image if not available.
				sprintf( __( 'Map not available for activity "%s"', 'wp-strava' ), $activity->name ) :
				"<a title='{$activity->name}' href='" . WPStrava_Activity::ACTIVITIES_URL . "{$activity->id}'>" .
				$this->get_static_image( $id, $activity, $build_new ) .
				'</a>';
			echo $args['after_widget'];
		}
	}

	/**
	 * Get image for specific activity using Static Maps class.
	 *
	 * @author Justin Foell
	 *
	 * @param string  $id        Athlete Token or Club ID.
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
	 * @author Justin Foell
	 * @since  1.2.0
	 *
	 * @param string $id  Athlete Token or Club ID.
	 * @param string $img Image tag.
	 */
	private function update_map( $id, $img ) {
		update_option( 'strava_latest_map_' . $id, $img );
	}

	/**
	 * Update activity in option to cache.
	 *
	 * @author Justin Foell
	 * @since  1.2.0
	 *
	 * @param string $id       Athlete Token or Club ID.
	 * @param object $activity stdClass Strava activity object.
	 */
	private function update_activity( $id, $activity ) {
		update_option( 'strava_latest_map_activity_' . $id, $activity );
	}

	/**
	 * Update activity in transient to cache.
	 *
	 * @author Justin Foell
	 * @since  1.2.0
	 *
	 * @param string $id       Athlete Token or Club ID.
	 * @param object $activity stdClass Strava activity object.
	 */
	private function update_activity_transient( $id, $activity ) {
		set_transient( 'strava_latest_map_activity_' . $id, $activity, HOUR_IN_SECONDS );
	}
}
