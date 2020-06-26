<?php

use \WP_Mock\Tools\TestCase;

class WPStrava_ActivityTest extends TestCase {

	private $activity;
	private $activities;

	public function setUp() : void {
		\WP_Mock::setUp();
		$this->activity = new WPStrava_Activity();

		$activity0 = new stdClass();
		$activity1 = new stdClass();
		$activity2 = new stdClass();

		$activity0->distance = 100;
		$activity1->distance = 1600;
		$activity2->distance = 45000;

		$this->activities = array(
			$activity0,
			$activity1,
			$activity2,
		);
	}

	public function tearDown() : void {
		\WP_Mock::tearDown();
	}

	public function test_object() {
		$this->assertInstanceOf( 'WPStrava_Activity', $this->activity );
	}

	/**
	 * Test that we only get the 45,000 meter activity when filtering greater than 10.00 kilometers.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.0.0
	 */
	public function test_get_activities_longer_than() {
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => 'strava_som',
				'times'  => 1,
				'return' => 'metric',
			)
		);

		$expected = array( $this->activities[2] );
		$actual   = $this->activity->get_activities_longer_than( $this->activities, '10' );
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test that links with a title are rendered correctly.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.3.2
	 */
	public function test_activity_link_with_title() {
		$activity_name = 'Chill Day';
		$activity_id   = 123456778928065;

		$expected = "<a href='" . WPStrava_Activity::ACTIVITIES_URL . $activity_id . "' title='{$activity_name}'>{$activity_name}</a>";
		$actual   = $this->activity->get_activity_link( $activity_id, $activity_name, $activity_name );

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test that links with no title are rendered correctly.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.3.2
	 */
	public function test_activity_link_no_title() {
		$activity_name = 'Chill Day';
		$activity_id   = 123456778928065;

		$expected = "<a href='" . WPStrava_Activity::ACTIVITIES_URL . $activity_id . "'>{$activity_name}</a>";
		$actual   = $this->activity->get_activity_link( $activity_id, $activity_name );

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test that links with no_link set just render the supplied text.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.3.2
	 */
	public function test_activity_no_link() {
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => 'strava_no_link',
				'times'  => 1,
				'return' => true,
			)
		);

		$activity_name = 'Afternoon Ride';
		$actual        = $this->activity->get_activity_link( 123456778928065, 'Afternoon Ride' );

		$this->assertEquals( $activity_name, $actual );
	}
}
