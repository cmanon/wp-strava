<?php

class WPStrava_StaticMap {
	
	public static function get_image_tag( $ride, $height = 320, $width = 480 ) {
		$url = "http://maps.google.com/maps/api/staticmap?maptype=terrain&size={$width}x{$height}&sensor=false&path=color:0xFF0000BF|weight:2|enc:";
		$url_len = strlen( $url );
		$max_chars = 1865;

		if ( $url_len + strlen( $ride->map->polyline ) < $max_chars )
			$url .= $ride->map->polyline;
		else
			$url .= $ride->map->summary_polyline;
		
		return "<img src='{$url}' />";
	}

}