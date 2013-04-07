<?php

/**
 * WP Strava Latest Rides Widget Class
 */
class WPStrava_LatestRidesWidget extends WP_Widget {
	
	public function __construct() {
		$widget_ops = array('classname' => 'LatestRidesWidget', 'description' => __( 'Will publish your latest rides activity from strava.com.') );
		parent::__construct('wp-strava', $name = 'Strava Latest Rides', $widget_ops);
		wp_enqueue_style('wp-strava'); //TODO only load this when wigit is loaded
	}
	
	/** @see WP_Widget::widget */
	public function widget( $args, $instance ) {
		extract($args);
		
		//$widget_id = $args['widget_id'];
		$title = apply_filters('widget_title', empty($instance['title']) ? _e('Rides', 'wp-strava') : $instance['title']);
		$strava_search_option = empty($instance['strava_search_option']) ? 'athlete' : $instance['strava_search_option'];
		$strava_search_id = empty($instance['strava_search_id']) ? '' : $instance['strava_search_id'];
		$quantity = empty($instance['quantity']) ? '5' : $instance['quantity'];

	   	$this->som = WPStrava_SOM::get_som();
		?>
		<?php echo $before_widget; ?>
			<?php if ( $title ) echo $before_title . $title . $after_title; ?>
				<?php echo $this->strava_request_handler($strava_search_option, $strava_search_id, $strava_som_option, $quantity); ?>
			<?php echo $after_widget; ?>
        <?php
	}
	
	/** @see WP_Widget::update */
	public function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		if(in_array($new_instance['strava_search_option'], array('athlete', 'club'))) {
			$instance['strava_search_option'] = $new_instance['strava_search_option'];
		} else {
			$instance['strava_search_option'] = 'athlete';
		}
		if(in_array($new_instance['strava_som_option'], array('metric', 'english'))) {
			$instance['strava_som_option'] = $new_instance['strava_som_option'];
		} else {
			$instance['strava_som_option'] = 'metric';
		}
		$instance['strava_search_id'] = strip_tags($new_instance['strava_search_id']);
		$instance['quantity'] = $new_instance['quantity'];
		
		return $instance;
	}
	
	/** @see WP_Widget::form */
	public function form($instance) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : _e('Rides', 'wp-strava');
		$strava_search_option = isset($instance['strava_search_option']) ? esc_attr($instance['strava_search_option']) : "athlete";
		$strava_search_id = isset($instance['strava_search_id']) ? esc_attr($instance['strava_search_id']) : "";
		$quantity = isset($instance['quantity']) ? absint($instance['quantity']) : 5;
		
		?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
			</p>
			<!-- TODO: make an 'advanced' option -->
			<p>
				<label for="<?php echo $this->get_field_id('strava_search_option'); ?>"><?php _e('Search Option:'); ?></label> 
				<select class="widefat" id="<?php echo $this->get_field_id('strava_search_option'); ?>" name="<?php echo $this->get_field_name('strava_search_option'); ?>">
					<option value="athlete" <?php selected($strava_search_option, 'athlete'); ?>><?php _e("Athlete", "wp-strava")?></option>
					<option value="club" <?php selected($strava_search_option, 'club'); ?>><?php _e("Club", "wp-strava")?></option>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('strava_search_id'); ?>"><?php _e('Search Id:'); ?></label> 
				<input class="widefat" id="<?php echo $this->get_field_id('strava_search_id'); ?>" name="<?php echo $this->get_field_name('strava_search_id'); ?>" type="text" value="<?php echo $strava_search_id; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('quantity'); ?>"><?php _e('Quantity:'); ?></label> 
				<input class="widefat" id="<?php echo $this->get_field_id('quantity'); ?>" name="<?php echo $this->get_field_name('quantity'); ?>" type="text" value="<?php echo $quantity; ?>" />
			</p>
		<?php 
    }

	// The handler to the ajax call, we will avoid this if Strava support jsonp request and we can do it
	// the parsing directly on the jQuery ajax call, the returned text will be enclosed in the $response variable.
	private function strava_request_handler( $strava_search_option, $strava_search_id, $strava_som_option, $quantity ) {
	
		//Check if the username is empty.
		if ( empty( $strava_search_id ) )
			return __("Please configure the Strava search id on the widget options.", "wp-strava");		
		//else
		$strava_rides = WPStrava::get_instance()->rides;
		
		$rides = $strava_rides->getRidesSimple( $strava_search_option, $strava_search_id );
		if ( is_wp_error( $rides ) )
			return $rides->get_error_message();

		//adjust quantity
		$rides = array_slice( $rides, 0, $quantity );
		
		$rides_details = $strava_rides->getRidesDetails( $rides );
		if ( is_wp_error( $rides_details ) )
			return $rides_details->get_error_message();

		$response = "<ul id='rides'>";
		foreach($rides_details as $ride_obj) {
			$ride = $ride_obj->ride;
			$response .= "<li class='ride'>";
			$response .= "<a href='" . WPStrava_Rides::RIDES_URL . $ride->id . "' >" . $ride->name . "</a>";
			$response .= "<div class='ride-item'>";
			$unixtime = strtotime( $ride->start_date_local );
			$response .= sprintf( __("On %s %s", "wp-strava"), date_i18n( get_option( 'date_format' ), $unixtime ), date_i18n( get_option( 'time_format' ), $unixtime ) );
			
			if ($strava_search_option == "club") {
				$response .= " <a href='" . WPStrava_Rides::ATHLETES_URL . $ride->athlete_id . "'>" . $ride->athlete_name . "</a>";
			}
			
			$response .= sprintf( __(" rode %s %s", "wp-strava"), $this->som->distance( $ride->distance ), $this->som->get_distance_label() );
			$response .= sprintf( __( " during %s %s", "wp-strava" ), $this->som->time( $ride->elapsed_time ), $this->som->get_time_label() );
			$response .= sprintf( __( " climbing %s %s", "wp-strava" ), $this->som->elevation( $ride->elevation_gain ), $this->som->get_elevation_label() );
			$response .= "</div>";
			$response .= "</li>";
		}
		$response .= "</ul>";
		return $response;
	} // Function strava_request_handler
	
} // class LatestRidesWidget

