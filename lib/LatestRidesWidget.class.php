<?php

/**
 * WP Strava Latest Rides Widget Class
 */
class WPStrava_LatestRidesWidget extends WP_Widget {

	public function __construct() {
		$widget_ops = array('classname' => 'LatestRidesWidget', 'description' => __( 'Will publish your latest rides activity from strava.com.') );
		parent::__construct('wp-strava', $name = 'Strava Latest Rides', $widget_ops);
		wp_enqueue_style('wp-strava');
	}
	
	/** @see WP_Widget::widget */
	public function widget($args, $instance) {
		extract($args);
		
		//$widget_id = $args['widget_id'];
		$title = apply_filters('widget_title', empty($instance['title']) ? _e('Rides', 'wp-strava') : $instance['title']);
		$strava_search_option = empty($instance['strava_search_option']) ? 'athlete' : $instance['strava_search_option'];
		$strava_search_id = empty($instance['strava_search_id']) ? '' : $instance['strava_search_id'];
		$quantity = empty($instance['quantity']) ? '5' : $instance['quantity'];

		$wpstrava = WPStrava::get_instance();
		$strava_som_option = $wpstrava->settings->som;
	   
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
		$response = "";
	
		//Check if the username is empty.
		if (empty($strava_search_id)) {
			$response .= __("Please configure the Strava search id on the widget options.", "wp-strava");
		} else {
			require_once WPSTRAVA_PLUGIN_DIR . 'lib/Rides.class.php';
			$strava_rides = new WPStrava_Rides(); 
			$strava_rides->getLatestRides($strava_search_option, $strava_search_id, $quantity);
			$rides_details = $strava_rides->getRidesDetails($strava_som_option);
		
			if ($strava_som_option == "metric") {
				$units = array(
					'elapsedTime' => __('hours','wp-strava'),
					'movingTime' => __('hours','wp-strava'),
					'distance' => __('km','wp-strava'),
					'averageSpeed' => __('km/h','wp-strava'),
					'maximumSpeed' => __('km/h','wp-strava'),
					'elevationGain' => __('meters','wp-strava')
							   );
			} elseif ($strava_som_option == "english") {
				$units = array(
					'elapsedTime' => __('hours','wp-strava'),
					'movingTime' => __('hours','wp-strava'),
					'distance' => __('miles','wp-strava'),
					'averageSpeed' => __('mph','wp-strava'),
					'maximumSpeed' => __('mph','wp-strava'),
					'elevationGain' => __('feet','wp-strava')
							   );
			}
		
			$response .= "<ul id='rides'>";
			foreach($rides_details as $ride) {
				$response .= "<li class='ride'>";
				$response .= "<a href='" . $strava_rides->ridesLinkUrl . $ride['id'] . "' >" . $ride['name'] . "</a>";
				$response .= "<div class='ride-item'>";
				$response .= __("On ", "wp-strava") . $ride['startDate'];
				if ($strava_search_option == "club") {
					$response .= " <a href='" . $strava_rides->athletesLinkUrl . $ride['athleteId'] . "'>" . $ride['athleteName'] . "</a>";
				}
				$response .= __(" rode ", "wp-strava") . $ride['distance'] . " " . $units['distance'];
				$response .= __(" during ", "wp-strava") . $ride['elapsedTime'] . " " . $units['elapsedTime'];
				$response .= __(" climbing ", "wp-strava") . $ride['elevationGain'] . " " . $units['elevationGain'] . ".";			
				$response .= "</div>";
				$response .= "</li>";
			}
			$response .= "</ul>";
		}
		return $response;
	} // Function strava_request_handler
	
} // class LatestRidesWidget

