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

		$rides = $this->getRides( $ride_index_params );

		if ( ! empty( $rides ) ):
			
			if ( ! empty( $distance_min ) )
				$rides = $this->getRidesLongerThan( $rides, $distance_min );
		
			$ride = current( $rides );

			$map_deets = $this->getMapDetails( $ride->id );
			
			echo $before_widget;

			$max = 50;
			$count = count( $map_deets->latlng );
			$mod = (int) ( $count / $max );
			$points = array();
			for ( $i = 0; $i < $count; $i += $mod ) {
				$point = $map_deets->latlng[$i];
				$points[] = number_format( $point[0],4 ) . ',' . number_format( $point[1],4 );
			}

			$url_points = join( '|', $points );
			echo "<img src='http://maps.google.com/maps/api/staticmap?maptype=terrain&size=390x260&sensor=false&path=color:0xFF0000BF|weight:2|{$url_points}' />";
			/*
			echo '<pre>';
			print_r($map_deets);
			echo '</pre>';
			*/
			echo $after_widget;
		endif;
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