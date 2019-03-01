<?php

if ( ! defined( 'WPSTRAVA_PLUGIN_DIR' ) ) define( 'WPSTRAVA_PLUGIN_DIR', dirname( __FILE__ ) . '/../' );
require_once dirname( __FILE__ ) . '/../lib/autoload.php';
require_once dirname( __FILE__ ) . '/../vendor/autoload.php';

WP_Mock::bootstrap();
