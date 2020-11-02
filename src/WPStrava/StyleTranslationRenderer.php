<?php

/**
 * Static class to load translations for tables via 'wp_head'
 *
 * @author Justin Foell <justin@foell.org>
 * @since  2.4
 */
abstract class WPStrava_StyleTranslationRenderer {

	/**
	 * Translations indexed by class.
	 * @var array
	 */
	private static $style_translations = array();

	private static $shortcodes = array();

	private static $blocks = array();

	private static $has_shortcodes = array();

	private static $has_blocks = array();

	abstract public static function load_style_translations();

	public static function load_block_style_translations( $block ) {
		$block        = ! is_array( $block ) ? array( $block ) : $block;
		self::$blocks = array_unique( array_merge( self::$blocks, $block ) );
		self::add_filter();
	}

	public static function load_shortcode_style_translations( $shortcode ) {
		$shortcode        = ! is_array( $shortcode ) ? array( $shortcode ) : $shortcode;
		self::$shortcodes = array_unique( array_merge( self::$shortcodes, $shortcode ) );
		self::add_filter();
	}

	/**
	 * Add translation text to column header, identified by class.
	 *
	 * @param [type] $class       CSS class for table data (<td>).
	 * @param [type] $translation Text to add to column.
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.4
	 */
	public static function add_style_translation( $class, $translation ) {
		self::$style_translations[ $class ] = $translation;
	}

	public static function posts_results( $posts ) {
		global $wp_query;

		if ( ! $wp_query->is_main_query() || $wp_query->is_admin() ) {
			return $posts;
		}

		if ( empty( $posts ) ) {
			return $posts;
		}

		if ( empty( self::$shortcodes ) && empty( self::$blocks ) ) {
			return $posts;
		}

		// This should only run once per request.
		remove_filter( 'posts_results', array( __CLASS__, 'posts_results' ) );

		foreach ( $posts as $post ) {
			if ( self::has_shortcodes( $post ) || self::has_blocks( $post ) ) {
				self::add_action();
			}
		}

		return $posts;
	}

	public static function has_shortcode( $shortcode ) {
		$shortcode = ! is_array( $shortcode ) ? array( $shortcode ) : $shortcode;
		return ! empty( array_intersect( self::$has_shortcodes, $shortcode ) );
	}

	public static function has_block( $block ) {
		$block = ! is_array( $block ) ? array( $block ) : $block;
		return ! empty( array_intersect( self::$has_blocks, $block ) );
	}


	private static function add_filter() {
		if ( ! has_filter( 'posts_results', array( __CLASS__, 'posts_results' ) ) ) {
			add_filter( 'posts_results', array( __CLASS__, 'posts_results' ) );
		}
	}

	private static function add_action() {
		if ( ! has_action( 'wp_head', array( __CLASS__, 'print_style_translations' ) ) ) {
			add_action( 'wp_head', array( __CLASS__, 'print_style_translations' ) );
		}
	}

	private static function has_shortcodes( $post ) {
		if ( empty( self::$shortcodes ) ) {
			return false;
		}

		$has_shortcode = false;
		foreach ( self::$shortcodes as $shortcode ) {
			if ( has_shortcode( $post->post_content, $shortcode ) ) {
				if ( ! in_array( $shortcode, self::$has_shortcodes, true ) ) {
					self::$has_shortcodes[] = $shortcode;
				}
				$has_shortcode = true;
			}
		}

		return $has_shortcode;
	}

	private static function has_blocks( $post ) {
		if ( empty( self::$blocks ) ) {
			return false;
		}

		$has_block = false;
		foreach ( self::$blocks as $block ) {
			if ( has_block( $block, $post ) ) {
				if ( ! in_array( $block, self::$has_blocks, true ) ) {
					self::$has_blocks[] = $block;
				}
				return true;
			}
		}

		return $has_block;
	}

	/**
	 * Print translatable strings as <style> in <head>.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.4
	 */
	public static function print_style_translations() {
		if ( empty( self::$style_translations ) ) {
			return;
		}

		$style = '@media
		only screen and (max-width: 760px),
		(min-device-width: 768px) and (max-device-width: 1024px)  {
		';
		foreach ( self::$style_translations as $class => $translation ) {
			$style .= ".{$class}:before { content: \"{$translation}\"; }\n";
		}
		$style .= '}';
		echo "<style id='wp-strava-style-translations'>\n{$style}</style>";
	}
}
