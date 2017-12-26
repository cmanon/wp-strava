<?php
/*
 * Routes is a class wrapper for the Strava REST API functions.
 */

class WPStrava_Routes {
	const ROUTES_URL = 'http://app.strava.com/routes/';

	/**
	 * Get single route by ID.
	 *
	 * @param int    $route_id ID of activity to retrieve.
	 * @return object  stdClass representing this route.
	 * @author Daniel Lintott
	 */
	public function get_route( $route_id ) {
		return WPStrava::get_instance()->get_api()->get( "routes/{$route_id}" );
	}
}
