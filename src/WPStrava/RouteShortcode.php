<?php
/**
 * Route Shortcode [route].
 * @package WPStrava
 */

/**
 * Route Shortcode class.
 *
 * @author Daniel Lintott
 * @since  1.3.0
 */
class WPStrava_RouteShortcode {

	/**
	 * Whether or not to enqueue styles (if shortcode is present).
	 *
	 * @var boolean
	 * @author Daniel Lintott
	 * @since  1.3.0
	 */
	private $add_script = false;

	/**
	 * Constructor (converted from static init()).
	 *
	 * @author Daniel Lintott
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.6.0
	 */
	public function __construct() {
		add_shortcode( 'route', array( $this, 'handler' ) );
		add_action( 'wp_footer', array( $this, 'print_scripts' ) );
	}

	/**
	 * Shortcode handler for [route].
	 *
	 * [route id=id som=metric map_width="100%" map_height="400px" markers=false]
	 *
	 * @param array $atts Array of attributes (id, map_width, etc.).
	 * @return string Shortcode output
	 * @author Daniel Lintott
	 * @since  1.3.0
	 */
	public function handler( $atts ) {
		if ( isset( $atts['athlete_token'] ) ) {
			// Translators: Message shown when using deprecated athlete_token parameter.
			return __( 'The <code>athlete_token</code> parameter is deprecated as of WP-Strava version 2 and should be replaced with <code>client_id</code>.', 'wp-strava' );
		}

		$this->add_script = true;

		$renderer = new WPStrava_RouteRenderer();
		return $renderer->get_html( $atts );
	}

	/**
	 * Enqueue style if shortcode is being used.
	 *
	 * @author Daniel Lintott
	 * @since  1.3.0
	 */
	public function print_scripts() {
		if ( $this->add_script ) {
			wp_enqueue_style( 'wp-strava-style' );
		}
	}
}
