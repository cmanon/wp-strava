<?php
/**
 * ActivityType [activitytype].
 * @package WPStrava
 */

/**
 * ActivityType class.
 *
 * @author Sebastian Erb <mail@sebastianerb.com>
 * @since  1.7.0
 */
class WPStrava_ActivityType {

	const TYPE_ALPINESKI       = 'AlpineSki';
	const TYPE_BACKCOUNTRYSKI  = 'BackcountrySki';
	const TYPE_CANOEING        = 'Canoeing';
	const TYPE_CROSSFIT        = 'Crossfit';
	const TYPE_EBIKERIDE       = 'EBikeRide';
	const TYPE_ELLIPTICAL      = 'Elliptical';
	const TYPE_HANDCYCLE       = 'Hike';
	const TYPE_HIKE            = 'IceSkate';
	const TYPE_ICESKATE        = 'InlineSkate';
	const TYPE_INLINESKATE     = 'AlpineSki';
	const TYPE_KAYAKING        = 'Kayaking';
	const TYPE_KITESURF        = 'Kitesurf';
	const TYPE_NORDICSKI       = 'NordicSki';
	const TYPE_RIDE            = 'Ride';
	const TYPE_ROCKCLIMBING    = 'RockClimbing';
	const TYPE_ROLLERSKI       = 'RollerSki';
	const TYPE_ROWING          = 'Rowing';
	const TYPE_RUN             = 'Run';
	const TYPE_SNOWBOARD       = 'Snowboard';
	const TYPE_SNOWSHOE        = 'Snowshoe';
	const TYPE_STAIRSTEPPER    = 'StairStepper';
	const TYPE_STANDUPPADDLING = 'StandUpPaddling';
	const TYPE_SURFING         = 'Surfing';
	const TYPE_SWIM            = 'Swim';
	const TYPE_VIRTUALRIDE     = 'VirtualRide';
	const TYPE_VIRTUALRUN      = 'VirtualRun';
	const TYPE_WALK            = 'Walk';
	const TYPE_WEIGHTTRAINING  = 'WeightTraining';
	const TYPE_WHEELCHAIR      = 'Wheelchair';
	const TYPE_WINDSURF        = 'Windsurf';
	const TYPE_WORKOUT         = 'Workout';
	const TYPE_YOGA            = 'Yoga';

	private static $pace_types  = array( self::TYPE_CANOEING, self::TYPE_HIKE, self::TYPE_RUN, self::TYPE_SNOWSHOE, self::TYPE_VIRTUALRUN, self::TYPE_WALK );
	private static $water_types = array( self::TYPE_SWIM );

	const TYPE_GROUP_PACE  = 'pace';
	const TYPE_GROUP_WATER = 'water';
	const TYPE_GROUP_SPEED = 'speed';

	/**
	 * Get the type of activity - defaults to 'speed'.
	 *
	 * @param string $type Type provided by Strava.
	 * @return string Type group (pace/water/speed).
	 * @author Sebastian Erb <mail@sebastianerb.com>
	 * @since  1.7.0
	 */
	public static function get_type_group( $type ) {

		if ( in_array( $type, self::$pace_types, true ) ) {
			return self::TYPE_GROUP_PACE;
		}

		if ( in_array( $type, self::$water_types, true ) ) {
			return self::TYPE_GROUP_WATER;
		}

		return self::TYPE_GROUP_SPEED;
	}

}
