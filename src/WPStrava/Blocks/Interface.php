<?php
/**
 * WP-Strava Block Interface.
 */

interface WPStrava_Blocks_Interface {

	public function register_block();

	public function render_block( $attributes, $content );
}
