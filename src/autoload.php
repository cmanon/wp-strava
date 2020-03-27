<?php

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
	$file = WPSTRAVA_PLUGIN_DIR . '/src/' . implode( '/', $parts ) . '.php';
	if ( file_exists( $file ) ) {
		include_once $file;
	}
}
spl_autoload_register( 'wpstrava_autoload_classes' );
