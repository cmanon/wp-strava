<?php

class WPStrava_LatestMap {

	public static function get_map_html( $args ) {
		$build_new = false;

		$defaults = array(
			'client_id'      => WPStrava::get_instance()->settings->get_default_id(),
			'strava_club_id' => null,
			'distance_min'   => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$id = empty( $args['strava_club_id'] ) ? $args['client_id'] : $args['strava_club_id'];

		// Try our transient first.
		$activity_transient = get_transient( 'strava_latest_map_activity_' . $id );
		$activity_option    = get_option( 'strava_latest_map_activity_' . $id );

		$activity = $activity_transient ? $activity_transient : null;

		if ( ! $activity || empty( $activity->map ) ) {
			$strava_activity = WPStrava::get_instance()->activity;

			$activities = array();

			try {
				$activities = $strava_activity->get_activities( $args['client_id'], $args['strava_club_id'] );
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

				// Compare transient (temporary storage) to option (more permanent).
				// If the option isn't set or the transient is different, update the option.
				if ( empty( $activity_option->id ) || $activity->id != $activity_option->id ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					$build_new = true;
					self::update_activity( $id, $activity );
				}

				// Update the transient if it needs updating.
				if ( empty( $activity_transient->id ) || $activity->id != $activity_transient->id ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					self::update_activity_transient( $id, $activity );
				}
			}
		}

		if ( $activity ) {
			echo empty( $activity->map ) ?
				// Translators: Text with activity name shown in place of image if not available.
				esc_html( sprintf( __( 'Map not available for activity "%s"', 'wp-strava' ), $activity->name ) ) :
				"<a title='" . esc_attr( $activity->name ) . "' href='" . esc_attr( WPStrava_Activity::ACTIVITIES_URL . $activity->id ) . "'>" .
				self::get_static_image( $id, $activity, $build_new ) . // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Image OK.
				'</a>';
		}

	}

	/**
	 * Get image for specific activity using Static Maps class.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @param string  $id        Client ID or Club ID.
	 * @param object  $activity  Activity to get image for.
	 * @param boolean $build_new Whether to refresh the image from cache.
	 * @return string            Image tag.
	 */
	private static function get_static_image( $id, $activity, $build_new ) {
		$img = get_option( 'strava_latest_map_' . $id );

		if ( $build_new || ! $img ) {
			$img = WPStrava_StaticMap::get_image_tag( $activity );
			self::update_map( $id, $img );
		}

		return $img;
	}

	/**
	 * Update map in option to cache.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.2.0
	 * @param string $id  Client ID or Club ID.
	 * @param string $img Image tag.
	 */
	private static function update_map( $id, $img ) {
		update_option( 'strava_latest_map_' . $id, $img );
	}

	/**
	 * Update activity in option to cache.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.2.0
	 * @param string $id       Client ID or Club ID.
	 * @param object $activity stdClass Strava activity object.
	 */
	private static function update_activity( $id, $activity ) {
		update_option( 'strava_latest_map_activity_' . $id, $activity );
	}

	/**
	 * Update activity in transient to cache.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.2.0
	 * @param string $id       CLient ID or Club ID.
	 * @param object $activity stdClass Strava activity object.
	 */
	private static function update_activity_transient( $id, $activity ) {
		set_transient( 'strava_latest_map_activity_' . $id, $activity, HOUR_IN_SECONDS );
	}
}
