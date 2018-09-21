<?php

use \WP_Mock\Tools\TestCase;

class WPStrava_SOMEnglishTest extends TestCase {

	public function test_true() {
		$som = new WPStrava_SOMEnglish();
		$this->assertInstanceOf( 'WPStrava_SOMEnglish', $som );
	}
}