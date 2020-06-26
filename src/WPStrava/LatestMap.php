<?php

class WPStrava_LatestMap {

	public static function get_map_html( $args ) {
		$defaults = array(
			'client_id'      => WPStrava::get_instance()->settings->get_default_id(),
			'strava_club_id' => null,
			'distance_min'   => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$strava_activity = WPStrava::get_instance()->activity;

		$activities = array();

		try {
			$activities = $strava_activity->get_activities( $args );
		} catch ( WPStrava_Exception $e ) {
			// If athlete_token is still set, warn about that first and foremost.
			if ( isset( $args['athlete_token'] ) ) {
				// Translators: Message shown when using deprecated athlete_token parameter.
				echo wp_kses_post( __( 'The <code>athlete_token</code> parameter is deprecated as of WP-Strava version 2 and should be replaced with <code>client_id</code>.', 'wp-strava' ) );
			} else {
				echo $e->to_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Debug only.
			}
		}

		if ( ! empty( $activities ) ) {

			if ( ! empty( $args['distance_min'] ) ) {
				$activities = $strava_activity->get_activities_longer_than( $activities, $args['distance_min'] );
			}

			$activity = current( $activities );

			echo empty( $activity->map ) ?
				// Translators: Text with activity name shown in place of image if not available.
				esc_html( sprintf( __( 'Map not available for activity "%s"', 'wp-strava' ), $activity->name ) ) :
				// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Image OK.
				$strava_activity->get_activity_link(
					$activity->id,
					WPStrava_StaticMap::get_image_tag( $activity, null, null, false, $activity->name ),
					$activity->name
				);
				// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}
