<?php

class WPStrava_LatestMapWidget extends WP_Widget {

	private $som;
	
	public function __construct() {
		$this->som = WPStrava_SOM::get_som();
		
		parent::__construct(
	 		false,
			'Strava Latest Map', // Name
			array( 'description' => __( 'Strava latest ride using static google map image', 'wp-strava' ), ) // Args
		);
	}
	
	public function form( $instance ) {
		// outputs the options form on admin
        $distance_min = isset( $instance['distance_min'] ) ? esc_attr( $instance['distance_min'] ) : '';
        $ride_index_params = isset( $instance['ride_index_params'] ) ? esc_attr( $instance['ride_index_params'] ) : '';

		//provide some defaults
        //$ride_index_params = $ride_index_params ? $ride_index_params : 'athleteId=21';

		?>
        <p>
			<label for="<?php echo $this->get_field_id( 'distance_min' ); ?>"><?php echo sprintf( __( 'Min. Distance (%s):', 'wp-strava' ), $this->som->get_distance_label() ); ?></label> 
        	<input class="widefat" id="<?php echo $this->get_field_id( 'distance_min' ); ?>" name="<?php echo $this->get_field_name( 'distance_min' ); ?>" type="text" value="<?php echo $distance_min; ?>" />
        </p>
        <p>
			<label for="<?php echo $this->get_field_id( 'ride_index_params' ); ?>"><?php _e( 'Ride Search Parameters (one per line): ' ); ?>
			<a href="https://stravasite-main.pbworks.com/w/page/51754146/Strava%20REST%20API%20Method%3A%20rides%20index" target="_blank"><?php _e( 'help' ); ?></a></label>
			<textarea name="<?php echo $this->get_field_name( 'ride_index_params' ); ?>" id="<?php echo $this->get_field_id( 'ride_index_params' ); ?>" cols="10" rows="5" class="widefat"><?php echo $ride_index_params; ?></textarea>
        </p>
        <?php		
	}
	
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved from the admin
		$instance = $old_instance;
		$instance['ride_index_params'] = strip_tags( $new_instance['ride_index_params'] );
		$instance['distance_min'] = strip_tags( $new_instance['distance_min'] );

		/*
		if ( empty( $instance['ride_index_params'] ) ) {
			$instance['ride_index_params'] = "athleteId={$auth->athlete->id}";
		}
		*/

		//$instance['athlete_hash'] = strip_tags( $new_instance['athlete_hash'] );

        return $instance;
	}

	public function widget( $args, $instance ) {
		extract( $args );
		$ride_index_params = $instance['ride_index_params'];
		$distance_min = $instance['distance_min'];
		$build_new = false;
		
		//try our transient first
		$ride_transient = get_transient( 'strava_latest_map_ride' );
		$ride_option = get_option( 'strava_latest_map_ride' );

		if ( $ride_transient )
			$ride = $ride_transient;
		
		if ( ! $ride ) {
			$rides = $this->getRides( $ride_index_params );
			if ( ! empty( $rides ) ) {
			
				if ( ! empty( $distance_min ) )
					$rides = $this->getRidesLongerThan( $rides, $distance_min );

				$ride = current( $rides );

				//update transients & options
				if ( $ride->id != $ride_option->id ) {
					$build_new = true;
					update_option( 'strava_latest_map_ride', $ride );
				}

				if ( $ride->id != $ride_transient->id )
					set_transient( 'strava_latest_map_ride', $ride, 60 * 60 ); //one hour
			}
		}

		if ( $ride ):			
			echo $before_widget;
			//die('<pre>' . print_r($ride, true));
			?><h3 class="widget-title"><?php echo $ride->ride->name ?></h3>
			<a href="http://app.strava.com/activities/<?php echo $ride->id ?>"><?php
			echo $this->getStaticImage( $ride->id, $build_new );
			?></a><?php
			echo $after_widget;
		endif;
	}

	private function buildImage( $map_details ) {
		$url = 'http://maps.google.com/maps/api/staticmap?maptype=terrain&size=390x260&sensor=false&path=color:0xFF0000BF|weight:2|';
		$url_len = strlen( $url );
		$point_len = 0;
		$num = 50;
		$count = count( $map_details->latlng );
		$full_url = '';
		$max_chars = 1865;
		
		//get the longest usable URL
		while ( $url_len + $point_len < $max_chars ) {
			$mod = (int) ( $count / $num );
			$points = array();
			for ( $i = 0; $i < $count; $i += $mod ) {
				$point = $map_details->latlng[$i];
				$points[] = number_format( $point[0], 4 ) . ',' . number_format( $point[1], 4 );
			}

			$url_points = join( '|', $points );
			$point_len = strlen( $url_points );
			if ( $url_len + $point_len < $max_chars )
				$full_url = $url . $url_points;
			$num++;
		}
		
		return "<img src='{$full_url}' />";
	}

	private function getStaticImage( $ride_id, $build_new ) {

		$img = get_option( 'strava_latest_map' );
		
		if ( $build_new || ! $img ) {
			$map_details = $this->getMapDetails( $ride_id );
			$img = $this->buildImage( $map_details );
			update_option( 'strava_latest_map', $img );
		}

		return $img;
	}
	
	private function getRides( $params ) {
		$data = WPStrava::get_instance()->api->get( 'rides?' . implode( '&', explode( "\n", $params ) ), 1 ); //version 1
		
		if ( isset( $data->rides ) )
			return $data->rides;
		return array();
	}

	private function getRideInfo( $ride_id ) {
		return WPStrava::get_instance()->api->get( "rides/{$ride_id}" );
	}

	private function getRidesLongerThan( $rides, $dist ) {
		$meters = $this->som->distance_inverse( $dist );

		$long_rides = array();
		foreach ( $rides as $ride ) {
			$ride_info = $this->getRideInfo( $ride->id );
			if ( $ride_info->ride->distance > $meters ) {
				$long_rides[] = $ride_info;
			}
		}
		
		return $long_rides;
	}
		
	private function getMapDetails( $ride_id ) {
		$token = WPStrava::get_instance()->settings->token;
		return WPStrava::get_instance()->api->get( "rides/{$ride_id}/map_details?token={$token}" );
	}
}