<?php
/**
 * Latest Map Shortcode [latest_map].
 * @package WPStrava
 */

/**
 * Latest Map Shortcode class.
 *
 * @author Justin Foell <justin@foell.org>
 * @since  2.0.1
 */
class WPStrava_LatestMapShortcode {

	/**
	 * Constructor.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.0.1
	 */
	public function __construct() {
		add_shortcode( 'latest_map', array( $this, 'handler' ) );
	}
	/**
	 * Shortcode handler for [latest_map].
	 *
	 * [latest_map som=metric distance_min=10 client_id=xxx|strava_club_id=yyy]
	 *
	 * @param array $atts Array of attributes (client_id, som, etc.).
	 * @return string Shortcode output
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.0.1
	 */
	public function handler( $atts ) {
		return WPStrava_LatestMap::get_map_html( $atts );
	}

}
