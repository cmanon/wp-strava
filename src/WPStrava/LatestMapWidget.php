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
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php // Translator: Widget Title. ?>
				<?php esc_html_e( 'Title:', 'wp-strava' ); ?>
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'client_id' ) ); ?>"><?php esc_html_e( 'Athlete:', 'wp-strava' ); ?></label>
				<select name="<?php echo esc_attr( $this->get_field_name( 'client_id' ) ); ?>">
				<?php foreach ( $all_ids as $id => $nickname ) : ?>
					<option value="<?php echo esc_attr( $id ); ?>"<?php selected( $id, $client_id ); ?>><?php echo esc_html( $nickname ); ?></option>
				<?php endforeach; ?>
				</select>
			</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'distance_min' ) ); ?>">
				<?php // Translators: Label for minimum distance input. ?>
				<?php echo esc_html( sprintf( __( 'Min. Distance (%s):', 'wp-strava' ), $this->som->get_distance_label() ) ); ?>
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'distance_min' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'distance_min' ) ); ?>" type="text" value="<?php echo esc_attr( $distance_min ); ?>" />
		</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'strava_club_id' ) ); ?>"><?php esc_html_e( 'Club ID (leave blank to show Athlete):', 'wp-strava' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'strava_club_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'strava_club_id' ) ); ?>" type="text" value="<?php echo esc_attr( $strava_club_id ); ?>" />
			</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		// Processes widget options to be saved from the admin.
		$instance                   = $old_instance;
		$instance['title']          = wp_strip_all_tags( $new_instance['title'] );
		$instance['client_id']      = wp_strip_all_tags( $new_instance['client_id'] );
		$instance['strava_club_id'] = wp_strip_all_tags( $new_instance['strava_club_id'] );
		$instance['distance_min']   = wp_strip_all_tags( $new_instance['distance_min'] );
		return $instance;
	}

	/**
	 * Method to render the widget on the front end.
	 *
	 * @param array $args     Arguments from the widget settings.
	 * @param array $instance Settings for this particular widget.
	 */
	public function widget( $args, $instance ) {

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Latest Activity Map', 'wp-strava' ) : $instance['title'] );

		$activities_args = array(
			'client_id'      => isset( $instance['client_id'] ) ? $instance['client_id'] : null,
			'strava_club_id' => isset( $instance['strava_club_id'] ) ? $instance['strava_club_id'] : null,
			'distance_min'   => isset( $instance['distance_min'] ) ? absint( $instance['distance_min'] ) : 0,
		);

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Widget OK.
		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		echo WPStrava_LatestMap::get_map_html( $activities_args );
		echo $args['after_widget'];
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
