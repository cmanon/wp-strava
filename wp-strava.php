<?php
namespace WP\Strava;
require_once 'rides.class.php';
require_once 'util.class.php';
/*
Plugin Name: WP Strava
Plugin URI: http://cmanon.com
Description: Plugin to show your strava.com information in your wordpress blog. Some Icons are Copyright © Yusuke Kamiyamane. All rights reserved. Licensed under a Creative Commons Attribution 3.0 license.  
Version: 0.62
Author: Carlos Santa Cruz (cmanon)
Author URI: http://cmanon.com
License: GPL2
*/
?>
<?php
/*  Copyright 2011  Carlos Santa Cruz  (email : cmanon at gmail dot com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php
// Load the multilingual support.
if(file_exists(dirname(__FILE__) . '/lang/' . get_locale() . '.mo' ) ) {
	load_textdomain('wp-strava', dirname(__FILE__) . '/lang/' . get_locale() . '.mo' );
}

// Creating the admin menu options
add_action('admin_menu', '\WP\Strava\wp_strava_plugin_menu');

function wp_strava_plugin_menu() {
	add_options_page('WP Strava Options', 'WP Strava', 'manage_options', '\WP\Strava\wp-strava-options', '\WP\Strava\wp_strava_plugin_options');
	add_action('admin_init', '\WP\Strava\register_strava_settings');
}

function register_strava_settings() {
	register_setting('wp-strava-settings-group','strava_email');
	register_setting('wp-strava-settings-group','strava_token');
	//register_setting('wp-strava-settings-group','strava_som');
}

function load_styles() {
	// Register a personalized stylesheet
	wp_register_style('wp-strava-style', plugins_url('css/wp-strava.css', __FILE__));
	wp_enqueue_style('wp-strava');
}
add_action('wp_enqueue_script', 'load_styles');

function load_scripts() {
	// Load required javascript libraries
	wp_enqueue_script('jquery');
	//wp_enqueue_script('google-maps', 'http://maps.google.com/maps/api/js?sensor=false');
}
add_action('wp-enqueue_script', 'load_scripts');


function wp_strava_plugin_options() {
	if (!current_user_can('administrator'))  {
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}
	
	if(isset($_POST['strava_email'])) {
		$email = is_email($_POST['strava_email']);
		if(!$email OR $_POST['strava_password'] == "") {
			update_option('strava_email', $_POST['strava_email']);
			echo '<div class="error"><p><strong>' . __('Please include your strava email and password the password will not be stored.', 'wp-strava' ) . '</strong></p></div>';
		} else {
			$ride = new Rides();
			$token = $ride->getAuthenticationToken($email, $_POST['strava_password']);
			if($token) {
				update_option('strava_token', $token);
				echo '<div class="updated"><p><strong>' . $ride->feedback . __('Token saved.', 'wp-strava' ) . '</strong></p></div>';
			} else {
				echo '<div class="error"><p><strong>' . $ride->feedback . '</strong></p></div>';
			}
		}
	}
?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br/></div>
		<h2><?php _e('WP Strava Options','wp-strava'); ?></h2>
		<p><?php _e('Please specify the options below in order to obtain an authentication token, this will work with the Strava shortcodes supported by this plugin, the widget options are independant.', 'wp-strava');?> </p>
		
		<form method="post" action="">
			<?php //settings_fields('wp-strava-settings-group'); ?>
			<?php //do_settings_fields('wp-strava-settings-group'); ?>
			
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Email</th>
					<td><input type="text" id="strava_email" name="strava_email" size=50 value="<?php echo get_option('strava_email'); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Password</th>
					<td><input type="password" id="strava_password" name="strava_password" size=50 value="" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Token</th>
					<td><input type="text" id="strava_token" name="strava_token" size=50 value="<?php echo get_option('strava_token'); ?>" disabled /></td>
				</tr>
			</table>
			
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
			</p>
		</form>
	</div>
<?php } // Finished admin menu options


/**
 * WP Strava Latest Rides Widget Class
 */
class LatestRidesWidget extends \WP_Widget {
	function __construct() {
		$widget_ops = array('classname' => 'LatestRidesWidget', 'description' => __( 'Will publish your latest rides activity from strava.com.') );
		parent::__construct('wp-strava', $name = 'Strava Latest Rides', $widget_ops);
		wp_enqueue_style('wp-strava');
	}
	
	/** @see WP_Widget::widget */
	function widget($args, $instance) {
		extract($args);
		
		//$widget_id = $args['widget_id'];
		$title = apply_filters('widget_title', empty($instance['title']) ? _e('Rides', 'wp-strava') : $instance['title']);
		$strava_search_option = empty($instance['strava_search_option']) ? 'athlete' : $instance['strava_search_option'];
		$strava_som_option = empty($instance['strava_som_option']) ? 'metric' : $instance['strava_som_option'];
		$strava_search_id = empty($instance['strava_search_id']) ? '' : $instance['strava_search_id'];
		$quantity = empty($instance['quantity']) ? '5' : $instance['quantity'];
		
		?>
		<?php echo $before_widget; ?>
			<?php if ( $title ) echo $before_title . $title . $after_title; ?>
				<?php echo strava_request_handler($strava_search_option, $strava_search_id, $strava_som_option, $quantity); ?>
			<?php echo $after_widget; ?>
        <?php
	}
	
	/** @see WP_Widget::update */
	function update($new_instance, $old_instance) {
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
	function form($instance) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : _e('Rides', 'wp-strava');
		$strava_search_option = isset($instance['strava_search_option']) ? esc_attr($instance['strava_search_option']) : "athlete";
		$strava_som_option = isset($instance['strava_som_option']) ? esc_attr($instance['strava_som_option']) : "metric";
		$strava_search_id = isset($instance['strava_search_id']) ? esc_attr($instance['strava_search_id']) : "";
		$quantity = isset($instance['quantity']) ? absint($instance['quantity']) : 5;
		
		?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('strava_search_option'); ?>"><?php _e('Search Option:'); ?></label> 
				<select class="widefat" id="<?php echo $this->get_field_id('strava_search_option'); ?>" name="<?php echo $this->get_field_name('strava_search_option'); ?>">
					<option value="athlete" <?php selected($strava_search_option, 'athlete'); ?>><?php _e("Athlete", "wp-strava")?></option>
					<option value="club" <?php selected($strava_search_option, 'club'); ?>><?php _e("Club", "wp-strava")?></option>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('strava_som_option'); ?>"><?php _e('System of Measurement:'); ?></label> 
				<select class="widefat" id="<?php echo $this->get_field_id('strava_som_option'); ?>" name="<?php echo $this->get_field_name('strava_som_option'); ?>">
					<option value="metric" <?php selected($strava_som_option, 'metric'); ?>><?php _e("Metric", "wp-strava")?></option>
					<option value="english" <?php selected($strava_som_option, 'english'); ?>><?php _e("English", "wp-strava")?></option>
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
} // class LatestRidesWidget

// Register StravaLatestRidesWidget widget
add_action('widgets_init', function() {	return register_widget('WP\Strava\LatestRidesWidget'); });

// The handler to the ajax call, we will avoid this if Strava support jsonp request and we can do it
// the parsing directly on the jQuery ajax call, the returned text will be enclosed in the $response variable.
function strava_request_handler($strava_search_option, $strava_search_id, $strava_som_option, $quantity) {
	$response = "";
	
	//Check if the username is empty.
	if (empty($strava_search_id)) {
		$response .= __("Please configure the Strava search id on the widget options.", "wp-strava");
	} else {
		$strava_rides = new Rides(); 
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
				'averageSpeed' => __('miles/h','wp-strava'),
				'maximumSpeed' => __('miles/h','wp-strava'),
				'elevationGain' => __('feet','wp-strava')
			);
		}
		
		$response .= "<ul id='rides'>";
		foreach($rides_details as $ride) {
			$response .= "<li class='ride'>";
			$response .= "<a href='" . $strava_rides->ridesLinkUrl . $ride['id'] . "' >" . $ride['name'] . "</a>";
			$response .= "<div class='ride-item'>";
				$response .= __("On ", "wp-strava") . $ride['startDate'];
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


class RideShortcode {
	static $add_script;

	static function init() {
		add_shortcode('ride', array(__CLASS__, 'handler'));

		add_action('init', array(__CLASS__, 'registerScripts'));
		add_action('wp_footer', array(__CLASS__, 'printScripts'));
	}

	// Shortcode handler function
	// [ride id=id som=metric efforts=false threshold=5 map-width="100%" map-height="400px"] tag
	function handler($atts) {
		self::$add_script = true;

		$token = get_option('strava_token');
		
		if($token) {
			$pairs = array(
				'id' => 0,
				'som' => "metric",
				'efforts' => false,
				'threshold' => 0,
				'map_width' => "100%",
				'map_height' => "400px"
			);
			
			extract(shortcode_atts($pairs, $atts));

			if (isset($som)) {
				$strava_som = $som;
			} else {
				$strava_som = get_option('strava_som_option', 'metric');
			}
			
			$ride = new Rides();
			$rideDetails = $ride->getRideDetails($id, $strava_som);
			$rideCoordinates = $ride->getRideMap($id, $token, $efforts, $threshold);
			
			if ($strava_som == "metric") {
				$units = array(
					'elapsedTime' => __('hours','wp-strava'),
					'movingTime' => __('hours','wp-strava'),
					'distance' => __('km','wp-strava'),
					'averageSpeed' => __('km/h','wp-strava'),
					//'maximumSpeed' => __('km/h','wp-strava'),
					'elevationGain' => __('meters','wp-strava')
				);
			} elseif ($strava_som == "english") {
				$units = array(
					'elapsedTime' => __('hours','wp-strava'),
					'movingTime' => __('hours','wp-strava'),
					'distance' => __('miles','wp-strava'),
					'averageSpeed' => __('miles/h','wp-strava'),
					//'maximumSpeed' => __('miles/h','wp-strava'),
					'elevationGain' => __('feet','wp-strava')
				);
			}
			
			if($rideCoordinates) {
				return "
					<div id='ride-header-{$id}' class='table'>
						<table id='ride-details-table'>
							<thead>
								<tr>
									<th>Elapsed Time</th>
									<th>Moving Time</th>
									<th>Distance</th>
									<th>Average Speed</th>
									<th>Elevation Gain</th>
								</tr>
							</thead>
							<tbody>
								<tr class='ride-details-table-info'>
									<td>{$rideDetails['elapsedTime']}</td>
									<td>{$rideDetails['movingTime']}</td>
									<td>{$rideDetails['distance']}</td>
									<td>{$rideDetails['averageSpeed']}</td>
									<td>{$rideDetails['elevationGain']}</td>
								</tr>
								<tr class='ride-details-table-units'>
									<td>{$units['elapsedTime']}</td>
									<td>{$units['movingTime']}</td>
									<td>{$units['distance']}</td>
									<td>{$units['averageSpeed']}</td>
									<td>{$units['elevationGain']}</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div id='{$id}' class='map' style='width: {$map_width}; height: {$map_height}; border: 1px solid lightgrey;'></div>
					<script type='text/javascript'>
						if (window.coordinates === undefined) {
							window.coordinates = [];
						}
						window.coordinates[{$id}] = eval({$rideCoordinates});
					</script>
				";
			}
		} else {
			return _e('Please first get your strava token using the settings wp strava page.', 'wp-strava');
		}
	} // handler

	static function registerScripts() {
		wp_register_style('wp-strava-style', plugins_url('css/wp-strava.css', __FILE__));

		wp_register_script('wp-strava-script', plugins_url('js/wp-strava.js', __FILE__), array('jquery'), '1.0', true);
		wp_register_script('google-maps', 'http://maps.google.com/maps/api/js?sensor=false');
	}

	static function printScripts() {
		if (self::$add_script) {
			wp_enqueue_style('wp-strava-style');
			wp_enqueue_script('jquery');

			wp_print_scripts('google-maps');
			wp_print_scripts('wp-strava-script');
		}
	}
}

// Initialize short code
RideShortcode::init();

?>
