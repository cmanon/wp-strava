<?php
/**
 * Activities List Shortcode [activities].
 * @package WPStrava
 */

/**
 * Activities List Shortcode class (converted from LatestActivitiesShortcode).
 *
 * @author Justin Foell <justin@foell.org>
 * @since  2.3.0
 */
class WPStrava_ActivitiesListShortcode {

	/**
	 * Whether or not to enqueue styles (if shortcode is present).
	 *
	 * @var boolean
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.3.0
	 */
	private $add_script = false;

	/**
	 * Constructor (converted from static init()).
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.3.0
	 */
	public function __construct() {
		add_shortcode( 'activities', array( $this, 'handler' ) );
		add_action( 'wp_footer', array( $this, 'print_scripts' ) );
	}

	/**
	 * Shortcode handler for [activities].
	 *
	 * [activities som=metric quantity=5 client_id=xxx|strava_club_id=yyy]
	 *
	 * @param array $atts Array of attributes (id, som, etc.).
	 * @return string Shortcode output
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.3.0
	 */
	public function handler( $atts ) {
		$this->add_script = true;

		$renderer = new WPStrava_ActivitiesListRenderer();
		return $renderer->get_html( $atts );
	}

	/**
	 * Enqueue style if shortcode is being used.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.3.0
	 */
	public function print_scripts() {
		if ( $this->add_script ) {
			wp_enqueue_style( 'wp-strava-style' );
		}
	}
}
