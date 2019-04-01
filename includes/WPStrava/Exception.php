<?php
/**
 * WPStrava Exception(s).
 */

// phpcs:disable Generic.Files.OneClassPerFile.MultipleFound, Generic.Classes.DuplicateClassName.Found

/*
 * PHP 5.2 Nonsense
 * @see http://php.net/manual/en/exception.getprevious.php#106020
 */
if ( version_compare( PHP_VERSION, '5.3.0', '>=' ) ) {
	abstract class WPStrava_Abstract_Exception extends Exception {}
} else {
	abstract class WPStrava_52_Exception extends Exception {
		protected $previous;

		public function __construct( $message, $code = 0, Exception $previous = null ) {
			$this->previous = $previous;
			parent::__construct( $message, $code );
		}

		public function getPrevious() {
			return $this->previous;
		}
	}

	abstract class WPStrava_Abstract_Exception extends WPStrava_52_Exception {}
}

/*
 * Exception class for error handling/display.
 */
class WPStrava_Exception extends WPStrava_Abstract_Exception {

	/**
	 * Create a WPStrava_Exception from a WP_Error.
	 *
	 * @param WP_Error $error
	 * @return WPStrava_Exception
	 * @author Justin Foell <justin@foell.org>
	 * @since 1.6.0
	 */
	public static function from_wp_error( WP_Error $error ) {
		$class = __CLASS__;
		return new $class( $error->get_error_message( $error->get_error_code() ) );
	}

	/**
	 * HTML version of this exception.
	 *
	 * @return string The exception string wrapped in <pre> tags.
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.6.0
	 */
	public function to_html() {
		return '<pre>' . $this . '</pre>';
	}

	/**
	 * Magic method to convert this exception to a string.
	 *
	 * @return string
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.6.0
	 */
	public function __toString() {
		if ( WPSTRAVA_DEBUG && $this->getPrevious() ) {
			return $this->get_formatted_message( $this->getPrevious() );
		}

		return $this->get_formatted_message( $this );
	}

	/**
	 * Exception message with extra formatting.
	 *
	 * @param Exception $exception
	 * @return string Formatted exception message.
	 * @author Justin Foell <justin@foell.org>
	 * @since  1.6.0
	 */
	public function get_formatted_message( $exception ) {
		$code = $exception->getCode();

		if ( $exception->getPrevious() ) {
			if ( $code ) {
				// Translators: Message shown when there's an exception thrown with a code and there's more details available.
				return sprintf( __( 'WP Strava ERROR %1$s %2$s - See full error by adding<br/><code>define( \'WPSTRAVA_DEBUG\', true );</code><br/>to wp-config.php', 'wp-strava' ), $code, $this->getMessage() );
			}
			// Translators: Message shown when there's an exception thrown (no code) and there's more details available.
			return sprintf( __( 'WP Strava ERROR %1$s - See full error by adding<br/><code>define( \'WPSTRAVA_DEBUG\', true );</code><br/>to wp-config.php', 'wp-strava' ), $this->getMessage() );
		}

		if ( $code ) {
			// Translators: Message shown when there's an exception thrown with a code.
			return sprintf( __( 'WP Strava ERROR %1$s %2$s', 'wp-strava' ), $code, $exception->getMessage() );
		}
		// Translators: Message shown when there's an exception thrown without a code.
		return sprintf( __( 'WP Strava ERROR %1$s', 'wp-strava' ), $exception->getMessage() );
	}
}
// phpcs:enable
