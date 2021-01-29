<?php

/**
 * Routes is a class wrapper for the Strava REST API functions.
 *
 * @author Daniel Lintott
 * @since 1.3.0
 */
class WPStrava_Routes {
	const ROUTES_URL = 'https://strava.com/routes/';

	/**
	 * Get single route by ID.
	 *
	 * @param string  $client_id Client ID of athlete to retrieve for
	 * @param int     $route_id  ID of route to retrieve.
	 * @return object stdClass representing this route.
	 * @author Daniel Lintott
	 *
	 * @since 1.3.0
	 */
	public function get_route( $client_id, $route_id ) {
		return WPStrava::get_instance()->get_api( $client_id )->get( "routes/{$route_id}" );
	}

	/**
	 * Conditionally display a link based on settings.
	 *
	 * @param int    $route_id Strava Route ID
	 * @param string $text     Text (or HTML) that is the content of link.
	 * @param string $title    Title attribute (default empty).
	 * @return void
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.6.0
	 */
	public function get_route_link( $route_id, $text, $title = '' ) {
		if ( WPStrava::is_rest_request() || WPStrava::get_instance()->settings->no_link ) {
			return $text;
		}
		$url        = esc_url( self::ROUTES_URL . $route_id );
		$title_attr = $title ? " title='" . esc_attr( $title ) . "'" : '';
		return "<a href='{$url}'{$title_attr}>{$text}</a>";
	}

}
