<?php

/**
 * WP Strava Latest Rides Widget Class
 */
class WPStrava_LatestRidesWidget extends WP_Widget {

	public function __construct() {
		$widget_ops = array(
			'classname'   => 'LatestRidesWidget',
			'description' => __( 'Will publish your latest rides activity from strava.com.', 'wp-strava' ),
		);
		parent::__construct( 'wp-strava', __( 'Strava Latest Activity List', 'wp-strava' ), $widget_ops );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue' ) );
	}

	public function maybe_enqueue() {
		if ( is_active_widget( false, false, $this->id_base ) ) {
			wp_enqueue_style( 'wp-strava-style' ); //only load this when wigit is loaded
		}
	}

	/** @see WP_Widget::widget */
	public function widget( $args, $instance ) {
		extract( $args );

		$title          = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Activity', 'wp-strava' ) : $instance['title'] );
		$athlete_token  = isset( $instance['athlete_token'] ) ? $instance['athlete_token'] : WPStrava::get_instance()->settings->get_default_token();
		$strava_club_id = empty( $instance['strava_club_id'] ) ? '' : $instance['strava_club_id'];
		$quantity       = empty( $instance['quantity'] ) ? '5' : $instance['quantity'];

		echo $before_widget;
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		echo $this->strava_request_handler( $athlete_token, $strava_club_id, $quantity );
		echo $after_widget;
	}

	/** @see WP_Widget::update */
	public function update( $new_instance, $old_instance ) {
		$instance                   = $old_instance;
		$instance['title']          = strip_tags( $new_instance['title'] );
		$instance['athlete_token']  = strip_tags( $new_instance['athlete_token'] );
		$instance['strava_club_id'] = strip_tags( $new_instance['strava_club_id'] );
		$instance['quantity']       = $new_instance['quantity'];

		return $instance;
	}

	/** @see WP_Widget::form */
	public function form( $instance ) {
		$title          = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : __( 'Activity', 'wp-strava' );
		$all_tokens     = WPStrava::get_instance()->settings->get_all_tokens();
		$athlete_token  = isset( $instance['athlete_token'] ) ? esc_attr( $instance['athlete_token'] ) : WPStrava::get_instance()->settings->get_default_token();
		$strava_club_id = isset( $instance['strava_club_id'] ) ? esc_attr( $instance['strava_club_id'] ) : '';
		$quantity       = isset( $instance['quantity'] ) ? absint( $instance['quantity'] ) : 5;

		?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wp-strava' ); ?></label> 
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
				<label for="<?php echo $this->get_field_id( 'strava_club_id' ); ?>"><?php esc_html_e( 'Club ID (leave blank to show single Athlete):', 'wp-strava' ); ?></label> 
				<input class="widefat" id="<?php echo $this->get_field_id( 'strava_club_id' ); ?>" name="<?php echo $this->get_field_name( 'strava_club_id' ); ?>" type="text" value="<?php echo $strava_club_id; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'quantity' ); ?>"><?php esc_html_e( 'Quantity:', 'wp-strava' ); ?></label> 
				<input class="widefat" id="<?php echo $this->get_field_id( 'quantity' ); ?>" name="<?php echo $this->get_field_name( 'quantity' ); ?>" type="text" value="<?php echo $quantity; ?>" />
			</p>
		<?php
	}

	// The handler to the ajax call, we will avoid this if Strava support jsonp request and we can do it
	// the parsing directly on the jQuery ajax call, the returned text will be enclosed in the $response variable.
	private function strava_request_handler( $athlete_token, $strava_club_id, $quantity ) {

		$som          = WPStrava_SOM::get_som();
		$strava_rides = WPStrava::get_instance()->rides;

		$rides = $strava_rides->getRides( $athlete_token, $strava_club_id, $quantity );
		if ( is_wp_error( $rides ) ) {
			return $rides->get_error_message();
		}

		$response = "<ul id='rides'>";
		foreach ( $rides as $ride ) {
			$response .= "<li class='ride'>";
			$response .= "<a href='" . WPStrava_Rides::ACTIVITIES_URL . $ride->id . "' target='_blank'>" . $ride->name . '</a>';
			$response .= "<div class='ride-item'>";
			$unixtime  = strtotime( $ride->start_date_local );
			// Translators: Shows something like "On <date> <[went 10 miles] [during 2 hours] [climbing 100 feet]>."
			$response .= sprintf( __( 'On %1$s %2$s', 'wp-strava' ), date_i18n( get_option( 'date_format' ), $unixtime ), date_i18n( get_option( 'time_format' ), $unixtime ) );

			if ( is_numeric( $strava_club_id ) ) {
				$response .= " <a href='" . WPStrava_Rides::ATHLETES_URL . $ride->athlete_id . "'>" . $ride->athlete_name . '</a>';
			}

			// Translators: "went 10 miles"
			$response .= sprintf( __( ' went %1$s %2$s', 'wp-strava' ), $som->distance( $ride->distance ), $som->get_distance_label() );
			// Translators: "during 2 hours"
			$response .= sprintf( __( ' during %1$s %2$s', 'wp-strava' ), $som->time( $ride->elapsed_time ), $som->get_time_label() );
			// Translators: "climbing 100 ft."
			$response .= sprintf( __( ' climbing %1$s %2$s', 'wp-strava' ), $som->elevation( $ride->total_elevation_gain ), $som->get_elevation_label() );
			$response .= '</div></li>';
		}
		$response .= '</ul>';
		return $response;
	} // Function strava_request_handler

} // class LatestRidesWidget
