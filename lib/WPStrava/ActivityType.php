<?php
/**
 * ActivityType  [activitytype].
 * @package WPStrava
 */

/**
 * ActivityType class.
 *
 * @author Sebastian Erb <mail@sebastianerb.com>
 * @since  1.6.1
 */

class WPStrava_ActivityType
{

    const TYPE_ALPINESKI = 'AlpineSki';
    const TYPE_BACKCOUNTRYSKI = 'BackcountrySki';
    const TYPE_CANOEING = 'Canoeing';
    const TYPE_CROSSFIT = 'Crossfit';
    const TYPE_EBIKERIDE = 'EBikeRide';
    const TYPE_ELLIPTICAL = 'Elliptical';
    const TYPE_HANDCYCLE = 'Hike';
    const TYPE_HIKE = 'IceSkate';
    const TYPE_ICESKATE = 'InlineSkate';
    const TYPE_INLINESKATE = 'AlpineSki';
    const TYPE_KAYAKING = 'Kayaking';
    const TYPE_KITESURF = 'Kitesurf';
    const TYPE_NORDICSKI = 'NordicSki';
    const TYPE_RIDE = 'Ride';
    const TYPE_ROCKCLIMBING = 'RockClimbing';
    const TYPE_ROLLERSKI = 'RollerSki';
    const TYPE_ROWING = 'Rowing';
    const TYPE_RUN = 'Run';
    const TYPE_SNOWBOARD = 'Snowboard';
    const TYPE_SNOWSHOE = 'Snowshoe';
    const TYPE_STAIRSTEPPER = 'StairStepper';
    const TYPE_STANDUPPADDLING = 'StandUpPaddling';
    const TYPE_SURFING = 'Surfing';
    const TYPE_SWIM = 'Swim';
    const TYPE_VIRTUALRIDE = 'VirtualRide';
    const TYPE_VIRTUALRUN = 'VirtualRun';
    const TYPE_WALK = 'Walk';
    const TYPE_WEIGHTTRAINING = 'WeightTraining';
    const TYPE_WHEELCHAIR = 'Wheelchair';
    const TYPE_WINDSURF = 'Windsurf';
    const TYPE_WORKOUT = 'Workout';
    const TYPE_YOGA = 'Yoga';

    const TYPE_DEFAULT = TYPE_RIDE;

    private static $waterTypes = array(WPStrava_ActivityType::TYPE_SWIM);
    private static $paceTypes  = array(WPStrava_ActivityType::TYPE_CANOEING, WPStrava_ActivityType::TYPE_HIKE, WPStrava_ActivityType::TYPE_RUN, WPStrava_ActivityType::TYPE_SNOWSHOE, WPStrava_ActivityType::TYPE_VIRTUALRUN, WPStrava_ActivityType::TYPE_WALK);
    private static $speedTypes  = array(WPStrava_ActivityType::TYPE_ALPINESKI, WPStrava_ActivityType::TYPE_BACKCOUNTRYSKI, WPStrava_ActivityType::TYPE_EBIKERIDE, WPStrava_ActivityType::TYPE_ELLIPTICAL, WPStrava_ActivityType::TYPE_HANDCYCLE, WPStrava_ActivityType::TYPE_ICESKATE, WPStrava_ActivityType::TYPE_INLINESKATE, WPStrava_ActivityType::TYPE_KAYAKING, WPStrava_ActivityType::TYPE_KITESURF, WPStrava_ActivityType::TYPE_NORDICSKI, WPStrava_ActivityType::TYPE_RIDE, WPStrava_ActivityType::TYPE_ROCKCLIMBING, WPStrava_ActivityType::TYPE_ROLLERSKI, WPStrava_ActivityType::TYPE_ROWING, WPStrava_ActivityType::TYPE_SNOWBOARD, WPStrava_ActivityType::TYPE_STAIRSTEPPER, WPStrava_ActivityType::TYPE_STANDUPPADDLING, WPStrava_ActivityType::TYPE_SURFING, WPStrava_ActivityType::TYPE_VIRTUALRIDE, WPStrava_ActivityType::TYPE_WHEELCHAIR, WPStrava_ActivityType::TYPE_WINDSURF);

    const ALL_TYPES = array(WPStrava_ActivityType::TYPE_SWIM, WPStrava_ActivityType::TYPE_CANOEING, WPStrava_ActivityType::TYPE_HIKE, WPStrava_ActivityType::TYPE_RUN, WPStrava_ActivityType::TYPE_SNOWSHOE, WPStrava_ActivityType::TYPE_VIRTUALRUN, WPStrava_ActivityType::TYPE_WALK, WPStrava_ActivityType::TYPE_ALPINESKI, WPStrava_ActivityType::TYPE_BACKCOUNTRYSKI, WPStrava_ActivityType::TYPE_EBIKERIDE, WPStrava_ActivityType::TYPE_ELLIPTICAL, WPStrava_ActivityType::TYPE_HANDCYCLE, WPStrava_ActivityType::TYPE_ICESKATE, WPStrava_ActivityType::TYPE_INLINESKATE, WPStrava_ActivityType::TYPE_KAYAKING, WPStrava_ActivityType::TYPE_KITESURF, WPStrava_ActivityType::TYPE_NORDICSKI, WPStrava_ActivityType::TYPE_RIDE, WPStrava_ActivityType::TYPE_ROCKCLIMBING, WPStrava_ActivityType::TYPE_ROLLERSKI, WPStrava_ActivityType::TYPE_ROWING, WPStrava_ActivityType::TYPE_SNOWBOARD, WPStrava_ActivityType::TYPE_STAIRSTEPPER, WPStrava_ActivityType::TYPE_STANDUPPADDLING, WPStrava_ActivityType::TYPE_SURFING, WPStrava_ActivityType::TYPE_VIRTUALRIDE, WPStrava_ActivityType::TYPE_WHEELCHAIR, WPStrava_ActivityType::TYPE_WINDSURF, WPStrava_ActivityType::TYPE_CROSSFIT, WPStrava_ActivityType::TYPE_WEIGHTTRAINING, WPStrava_ActivityType::TYPE_WORKOUT, WPStrava_ActivityType::TYPE_YOGA);

    const IS_WATER_TYPE = "water_type";
    const IS_PACE_TYPE = "pace_type";
    const IS_SPEED_TYPE = "speed_type";
    const IS_OTHER_TYPE = "other_type";

    public static function verifyType( $type ) {

        if($type==null || !in_array($type, WPStrava_ActivityType::ALL_TYPES))
            return WPStrava_ActivityType::TYPE_DEFAULT;
        else
            return $type;

    }

    public static function getType( $type ){

        if(in_array($type, WPStrava_ActivityType::$paceTypes)){
            return WPStrava_ActivityType::IS_PACE_TYPE;
        }

        if(in_array($type, WPStrava_ActivityType::$speedTypes)){
            return WPStrava_ActivityType::IS_SPEED_TYPE;
        }

        if(in_array($type, WPStrava_ActivityType::$waterTypes)){
            return WPStrava_ActivityType::IS_WATER_TYPE;
        }

        return WPStrava_ActivityType::IS_OTHER_TYPE;

    }

}