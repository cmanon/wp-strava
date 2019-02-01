<?php
/**
 * Plugin Name: WP Strava
 * Plugin URI: https://wordpress.org/plugins/wp-strava/
 * Description: Show your strava.com activity on your WordPress site. Some Icons are Copyright © Yusuke Kamiyamane. All rights reserved. Licensed under a Creative Commons Attribution 3.0 license.
 * Version: 1.6.0
 * Author: Carlos Santa Cruz, Justin Foell, Lance Willett, Daniel Lintott, Sebastian Erb
 * License: GPL2
 * Text Domain: wp-strava
 * Domain Path: /lang
 */

/*
Copyright 2018  Carlos Santa Cruz  (email : cmanon at gmail dot com)

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


define( 'WPSTRAVA_PLUGIN_DIR', trailingslashit( dirname( __FILE__ ) ) );
define( 'WPSTRAVA_PLUGIN_URL', plugins_url( '/', __FILE__ ) );
define( 'WPSTRAVA_PLUGIN_NAME', plugin_basename( __FILE__ ) );
if ( ! defined( 'WPSTRAVA_DEBUG' ) ) {
	define( 'WPSTRAVA_DEBUG', false );
}

// Load the multilingual support.
function wpstrava_load_plugin_textdomain() {
	load_plugin_textdomain( 'wp-strava', false, WPSTRAVA_PLUGIN_DIR . 'lang/' );
}
add_action( 'plugins_loaded', 'wpstrava_load_plugin_textdomain' );

/**
 * Autoloads files with classes when needed.
 *
 * @since  1.6.0
 * @param  string $class_name Name of the class being requested.
 * @return void
 */
function wpstrava_autoload_classes( $class_name ) {
	$parts = explode( '_', $class_name );

	// If our class doesn't have our namespace, don't load it.
	if ( empty( $parts[0] ) || 'WPStrava' !== $parts[0] ) {
		return;
	}

	// @TODO Add directory searching if they get created.
	$file = dirname( __FILE__ ) . '/lib/' . implode( '/', $parts ) . '.php';
	if ( file_exists( $file ) ) {
		include_once $file;
	}
}
spl_autoload_register( 'wpstrava_autoload_classes' );

$wpstrava = WPStrava::get_instance();
