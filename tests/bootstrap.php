<?php

if ( ! defined( 'WPSTRAVA_PLUGIN_DIR' ) ) {
	define( 'WPSTRAVA_PLUGIN_DIR', dirname( __FILE__ ) . '/../' );
}

require_once dirname( __FILE__ ) . '/../includes/autoload.php';
require_once dirname( __FILE__ ) . '/../vendor/autoload.php';

WP_Mock::bootstrap();

// Pseudo mocks for WP functions.
if ( ! function_exists( 'number_format_i18n' ) ) :
	function number_format_i18n( $number, $decimals ) {
		return number_format( $number, $decimals );
	}
endif;
