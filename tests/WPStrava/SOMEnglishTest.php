<?php

use \WP_Mock\Tools\TestCase;

class WPStrava_SOMEnglishTest extends TestCase {

	private $som;

	public function setUp() : void { // phpcs:ignore PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound -- PHPUnit requires PHP7
		\WP_Mock::setUp();
		$this->som = new WPStrava_SOMEnglish();
	}

	public function tearDown() : void { // phpcs:ignore PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound -- PHPUnit requires PHP7
		\WP_Mock::tearDown();
	}

	public function test_object() {
		$this->assertInstanceOf( 'WPStrava_SOMEnglish', $this->som );
	}

	/**
	 * Test that 10,000 meters is 6.21 miles using both string and float inputs.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.7.1
	 */
	public function test_distance() {
		$this->assertEquals( '6.21', $this->som->distance( '10000' ) );
		$this->assertEquals( '6.21', $this->som->distance( 10000 ) );
	}

	/**
	 * Test that 6.213712 miles is 10,000.00 meters using float input.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.7.1
	 */
	public function test_distance_inverse() {
		$this->assertEquals( 10000.00, $this->som->distance_inverse( 6.213712 ) );
	}

	/**
	 * Test that 6.705 meters per second is 15.00 mph using both string and float inputs.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.7.1
	 */
	public function test_speed() {
		$this->assertEquals( '15.00', $this->som->speed( '6.705' ) );
		$this->assertEquals( '15.00', $this->som->speed( 6.705 ) );
	}

	/**
	 * Test that 2.68224 meters per second is a 10:00 minute/mile pace using both string and float inputs.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.7.1
	 */
	public function test_pace() {
		$this->assertEquals( '10:00', $this->som->pace( '2.68224' ) );
		$this->assertEquals( '10:00', $this->som->pace( 2.68224 ) );
	}

	/**
	 * Test that 60.96 meters is 200.00 feet using both string and float inputs.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.7.1
	 */
	public function test_elevation() {
		$this->assertEquals( '200.00', $this->som->elevation( '60.96' ) );
		$this->assertEquals( '200.00', $this->som->elevation( 60.96 ) );
	}

	/**
	 * Test that 4805 seconds is 01:20:05 time (H:i:s) using both string and float inputs.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.7.1
	 */
	public function test_time() {
		$this->assertEquals( '01:20:05', $this->som->time( '4805' ) );
		$this->assertEquals( '01:20:05', $this->som->time( 4805 ) );

	}

	/**
	 * Test that 221071 seconds is 61:24:31 time (H:i:s) using both string and float inputs.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.7.2
	 */
	public function test_time_greater24h() {
		$this->assertEquals( '61:24:31', $this->som->time( '221071' ) );
		$this->assertEquals( '61:24:31', $this->som->time( 221071 ) );
	}

	/**
	 * Test that 1304 calories is 1,304 using both string and int inputs.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.9.0
	 */
	public function test_calories() {
		$this->assertEquals( '1,304', $this->som->calories( '1304' ) );
		$this->assertEquals( '1,304', $this->som->calories( 1304 ) );
	}

	/**
	 * Test that 5.7 grade is 5.7% using both string and int inputs.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.9.0
	 */
	public function test_percent() {
		$this->assertEquals( '5.7%', $this->som->percent( '5.7' ) );
		$this->assertEquals( '5.7%', $this->som->percent( 5.7 ) );
	}

}
