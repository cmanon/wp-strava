<?php

/**
 * WP Strava Latest Activities Widget Class
 */
class WPStrava_LatestActivitiesWidget extends WP_Widget {

	public function __construct() {
		$widget_ops = array(
			'classname'   => 'LatestActivitiesWidget',
			'description' => __( 'Will show your latest activities from strava.com.', 'wp-strava' ),
		);
		parent::__construct( 'wp-strava', __( 'Strava Latest Activities List', 'wp-strava' ), $widget_ops );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue' ) );
	}

	public function maybe_enqueue() {
		if ( is_active_widget( false, false, $this->id_base ) ) {
			wp_enqueue_style( 'wp-strava-style' ); // Only load this when widget is loaded.
		}
	}

	/** @see WP_Widget::widget */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Latest Activities', 'wp-strava' ) : $instance['title'] );

		$activities_args = array(
			'client_id'      => isset( $instance['client_id'] ) ? $instance['client_id'] : null,
			'strava_club_id' => isset( $instance['strava_club_id'] ) ? $instance['strava_club_id'] : null,
			'quantity'       => isset( $instance['quantity'] ) ? $instance['quantity'] : null,
		);

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Widget OK.
		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		echo WPStrava_LatestActivities::get_activities_html( $activities_args );
		echo $args['after_widget'];
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/** @see WP_Widget::update */
	public function update( $new_instance, $old_instance ) {
		$instance                   = $old_instance;
		$instance['title']          = wp_strip_all_tags( $new_instance['title'] );
		$instance['client_id']      = wp_strip_all_tags( $new_instance['client_id'] );
		$instance['strava_club_id'] = wp_strip_all_tags( $new_instance['strava_club_id'] );
		$instance['quantity']       = $new_instance['quantity'];

		return $instance;
	}

	/** @see WP_Widget::form */
	public function form( $instance ) {
		$title          = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : __( 'Latest Activities', 'wp-strava' );
		$all_ids        = WPStrava::get_instance()->settings->get_all_ids();
		$client_id      = isset( $instance['client_id'] ) ? esc_attr( $instance['client_id'] ) : WPStrava::get_instance()->settings->get_default_id();
		$strava_club_id = isset( $instance['strava_club_id'] ) ? esc_attr( $instance['strava_club_id'] ) : '';
		$quantity       = isset( $instance['quantity'] ) ? absint( $instance['quantity'] ) : 5;

		?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'wp-strava' ); ?></label>
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
				<label for="<?php echo esc_attr( $this->get_field_id( 'strava_club_id' ) ); ?>"><?php esc_html_e( 'Club ID (leave blank to show single Athlete):', 'wp-strava' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'strava_club_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'strava_club_id' ) ); ?>" type="text" value="<?php echo esc_attr( $strava_club_id ); ?>" />
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'quantity' ) ); ?>"><?php esc_html_e( 'Quantity:', 'wp-strava' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'quantity' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'quantity' ) ); ?>" type="text" value="<?php echo esc_html( $quantity ); ?>" />
			</p>
		<?php
	}

} // class LatestActivitiesWidget
