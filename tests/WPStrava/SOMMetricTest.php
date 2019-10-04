<?php

use \WP_Mock\Tools\TestCase;

class WPStrava_SOMMetricTest extends TestCase {

	private $som;

	public function setUp() : void {
		$this->som = new WPStrava_SOMMetric();
	}

	public function test_object() {
		$this->assertInstanceOf( 'WPStrava_SOMMetric', $this->som );
	}

	/**
	 * Test that 10,000 meters is 10.00 kilometers using both string and float inputs.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.7.1
	 */
	public function test_distance() {
		$this->assertEquals( '10.00', $this->som->distance( '10000' ) );
		$this->assertEquals( '10.00', $this->som->distance( 10000 ) );
	}

	/**
	 * Test that 42.195 km is 42,195.00 meters using float input.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.7.1
	 */
	public function test_distance_inverse() {
		$this->assertEquals( 42195.00, $this->som->distance_inverse( 42.195 ) );
	}

	/**
	 * Test that 4.47 meters per second is 16.09 kmh using both string and float inputs.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.7.1
	 */
	public function test_speed() {
		$this->assertEquals( '16.09', $this->som->speed( '4.47' ) );
		$this->assertEquals( '16.09', $this->som->speed( 4.47 ) );
	}

	/**
	 * Test that 2.2352 meters per second is a 7:27 minute/kilometer pace using both string and float inputs.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.7.1
	 */
	public function test_pace() {
		$this->assertEquals( '7:27', $this->som->pace( '2.2352' ) );
		$this->assertEquals( '7:27', $this->som->pace( 2.2352 ) );
	}

	/**
	 * Test that 70 meters is 70.00 meters using both string and float inputs.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.7.1
	 */
	public function test_elevation() {
		$this->assertEquals( '70.00', $this->som->elevation( '70' ) );
		$this->assertEquals( '70.00', $this->som->elevation( 70 ) );
	}

	/**
	 * Test that 1.66 meters per second is a 1:00 minute/100m pace using both string and float inputs.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.7.1
	 */
	public function test_swimpace() {
		$this->assertEquals( '1:00', $this->som->swimpace( '1.66' ) );
		$this->assertEquals( '1:00', $this->som->swimpace( 1.66 ) );
	}
}
