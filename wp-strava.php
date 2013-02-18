<?php
/*
Plugin Name: WP Strava
Plugin URI: http://cmanon.com
Description: Plugin to show your strava.com information in your wordpress blog. Some Icons are Copyright © Yusuke Kamiyamane. All rights reserved. Licensed under a Creative Commons Attribution 3.0 license.  
Version: 0.63
Author: Carlos Santa Cruz (cmanon), Justin Foell <justin@foell.org>
Author URI: http://cmanon.com
License: GPL2
*/
/*  Copyright 2011  Carlos Santa Cruz  (email : cmanon at gmail dot com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


define( 'WPSTRAVA_PLUGIN_DIR', trailingslashit( dirname( __FILE__) ) );
define( 'WPSTRAVA_PLUGIN_URL', plugins_url( '/', __FILE__ ) );

// Load the multilingual support.
if( file_exists( WPSTRAVA_PLUGIN_DIR . 'lang/' . get_locale() . '.mo' ) ) {
	load_textdomain( 'wp-strava', WPSTRAVA_PLUGIN_DIR . 'lang/' . get_locale() . '.mo' );
}

require_once WPSTRAVA_PLUGIN_DIR . 'lib/Strava.class.php';
$wpstrava = WPStrava::get_instance();

//@TODO only load these when needed
function load_styles() {
	// Register a personalized stylesheet
	wp_register_style('wp-strava-style', WPSTRAVA_PLUGIN_URL . 'css/wp-strava.css' );
	wp_enqueue_style('wp-strava');
}
add_action('wp_enqueue_script', 'load_styles');

function load_scripts() {
	// Load required javascript libraries
	wp_enqueue_script('jquery');
	//wp_enqueue_script('google-maps', 'http://maps.google.com/maps/api/js?sensor=false');
}
add_action('wp-enqueue_script', 'load_scripts');
