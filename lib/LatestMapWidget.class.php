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
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : __( 'Latest Activity Map', 'wp-strava' );
		$all_tokens = WPStrava::get_instance()->settings->get_all_tokens();
		$athlete_token = isset( $instance['athlete_token'] ) ? esc_attr( $instance['athlete_token'] ) : WPStrava::get_instance()->settings->get_default_token();
		$distance_min = isset( $instance['distance_min'] ) ? esc_attr( $instance['distance_min'] ) : '';
		$strava_club_id = isset( $instance['strava_club_id'] ) ? esc_attr( $instance['strava_club_id'] ) : '';

		//provide some defaults
		//$ride_index_params = $ride_index_params ?: 'athleteId=21';

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wp-strava' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
				<label for="<?php echo $this->get_field_id( 'athlete_token' ); ?>"><?php _e( 'Athlete:', 'wp-strava' ); ?></label>
				<select name="<?php echo $this->get_field_name( 'athlete_token' ); ?>">
				<?php foreach ( $all_tokens as $token => $nickname ): ?>
					<option value="<?php echo $token; ?>"<?php selected( $token, $athlete_token ); ?>><?php esc_attr_e( $nickname ); ?></option>
				<?php endforeach; ?>
				</select>
			</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'distance_min' ); ?>"><?php echo sprintf( __( 'Min. Distance (%s):', 'wp-strava' ), $this->som->get_distance_label() ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'distance_min' ); ?>" name="<?php echo $this->get_field_name( 'distance_min' ); ?>" type="text" value="<?php echo $distance_min; ?>" />
		</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'strava_club_id' ); ?>"><?php _e( 'Club ID (leave blank to show Athlete):', 'wp-strava' ); ?></label> 
				<input class="widefat" id="<?php echo $this->get_field_id( 'strava_club_id' ); ?>" name="<?php echo $this->get_field_name( 'strava_club_id' ); ?>" type="text" value="<?php echo $strava_club_id; ?>" />
			</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved from the admin
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['athlete_token'] = strip_tags( $new_instance['athlete_token'] );
		$instance['strava_club_id'] = strip_tags( $new_instance['strava_club_id'] );
		$instance['distance_min'] = strip_tags( $new_instance['distance_min'] );
		return $instance;
	}

	public function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Latest Activity Map', 'wp-strava' ) : $instance['title'] );
		$athlete_token = isset( $instance['athlete_token'] ) ? $instance['athlete_token'] : WPStrava::get_instance()->settings->get_default_token();
		$distance_min = $instance['distance_min'];
		$strava_club_id = empty( $instance['strava_club_id'] ) ? null : $instance['strava_club_id'];
		$build_new = false;

		// Try our transient first.
		$ride_transient = get_transient( 'strava_latest_map_activity_' . $athlete_token );
		$ride_option = get_option( 'strava_latest_map_activity_' . $athlete_token );

		$ride = $ride_transient ? $ride_transient : null;

		if ( ! $ride ) {
			$strava_rides = WPStrava::get_instance()->rides;
			$rides = $strava_rides->getRides( $athlete_token, $strava_club_id );

			if ( is_wp_error( $rides ) ) {
				echo $before_widget;
				if ( WPSTRAVA_DEBUG ) {
					echo '<pre>';
					print_r($rides);
					echo '</pre>';
				} else {
					echo $rides->get_error_message();
				}
				echo $after_widget;
				return;
			}

			if ( ! empty( $rides ) ) {

				if ( ! empty( $distance_min ) )
					$rides = $strava_rides->getRidesLongerThan( $rides, $distance_min );

				$ride = current( $rides );

				// Compare transient (temporary storage) to option (more permenant).
				// If the option isn't set or the transient is different, update the option.
				if ( empty( $ride_option->id ) || $ride->id != $ride_option->id ) {
					$build_new = true;
					$this->update_activity( $athlete_token, $ride );
				}

				// Update the transient if it needs updating.
				if ( empty( $ride_transient->id ) || $ride->id != $ride_transient->id ) {
					$this->update_activity_transient( $athlete_token, $ride );
				}
			}
		}

		if ( $ride ) {
			echo $before_widget;
			if ( $title ) echo $before_title . $title . $after_title;
			?><a title="<?php echo $ride->name ?>" target="_blank" href="http://app.strava.com/activities/<?php echo $ride->id ?>"><?php
			echo $this->getStaticImage( $athlete_token, $ride, $build_new );
			?></a><?php
			echo $after_widget;
		}
	}

	/**
	 * Get image for specific ride using Static Maps class.
	 *
	 * @author Justin Foell
	 *
	 * @param string  $athlete_token Token for athelete.
	 * @param int     $ride_id Club ID (optional).
	 * @param boolean $build_new Whether to refresh the image from cache.
	 * @return string Image tag.
	 */
	private function getStaticImage( $athlete_token, $ride, $build_new ) {
		$img = get_option( 'strava_latest_map_' . $athlete_token );

		if ( $build_new || ! $img ) {
			$img = WPStrava_StaticMap::get_image_tag( $ride );
			$this->update_map( $athlete_token, $img );
		}

		return $img;
	}

	/**
	 * Update map in option to cache.
	 *
	 * @author Justin Foell
	 * @since  1.2.0
	 *
	 * @param string $athlete_token Token for athelete.
	 * @param string $img           Image tag.
	 */
	private function update_map( $athlete_token, $img ) {
		// Remove old (pre 1.2.0) cached maps.
		if ( get_option( 'strava_latest_map' ) ) {
			delete_option( 'strava_latest_map' );
		}
		update_option( 'strava_latest_map_' . $athlete_token, $img );
	}

	/**
	 * Update activity in option to cache.
	 *
	 * @author Justin Foell
	 * @since  1.2.0
	 *
	 * @param string $athlete_token Token for athelete.
	 * @param object $activity      stdClass Strava activity object.
	 */
	private function update_activity( $athlete_token, $activity ) {
		// Remove old (pre 1.2.0) option.
		if ( get_option( 'strava_latest_map_ride' ) ) {
			delete_option( 'strava_latest_map_ride' );
		}
		update_option( 'strava_latest_map_activity_' . $athlete_token, $activity );
	}

	/**
	 * Update activity in transient to cache.
	 *
	 * @author Justin Foell
	 * @since  1.2.0
	 *
	 * @param string $athlete_token Token for athelete.
	 * @param object $activity      stdClass Strava activity object.
	 */
	private function update_activity_transient( $athlete_token, $activity ) {
		// Remove old (pre 1.2.0) transient.
		if ( get_transient( 'strava_latest_map_ride' ) ) {
			delete_transient( 'strava_latest_map_ride' );
		}
		set_transient( 'strava_latest_map_activity_' . $athlete_token, $activity, HOUR_IN_SECONDS );
	}
}
