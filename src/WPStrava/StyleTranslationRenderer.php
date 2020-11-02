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

	/**
	 * Shortcodes registered with translatable style strings.
	 * @var array
	 */
	private static $shortcodes = array();

	/**
	 * Blocks registered with translatable style strings.
	 * @var array
	 */
	private static $blocks = array();

	/**
	 * Shortcodes found in content of current page.
	 * @var array
	 */
	private static $has_shortcodes = array();

	/**
	 * Blocks found in content of current page.
	 * @var array
	 */
	private static $has_blocks = array();

	/**
	 * Implemented by concrete renderers to load column headers by calling self::add_style_translation().
	 *
	 * @author Justin Foell <justin.foell.org>
	 * @since  2.4
	 */
	abstract public static function load_style_translations();

	/**
	 * Register a shortcode that has translatable CSS strings.
	 *
	 * @param string|array $shortcode Shortcode (or array of shortcodes).
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.4
	 */
	public static function register_shortcode_style_translations( $shortcode ) {
		$shortcode        = ! is_array( $shortcode ) ? array( $shortcode ) : $shortcode;
		self::$shortcodes = array_unique( array_merge( self::$shortcodes, $shortcode ) );
		self::add_content_filter();
	}

	/**
	 * Register a block that has translatable CSS strings.
	 *
	 * @param string|array $block Block name (or array of block names).
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.4
	 */
	public static function register_block_style_translations( $block ) {
		$block        = ! is_array( $block ) ? array( $block ) : $block;
		self::$blocks = array_unique( array_merge( self::$blocks, $block ) );
		self::add_content_filter();
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

	/**
	 * Go through posts from The Loop looking for our shortcodes and blocks.
	 *
	 * @param array $posts Posts from the current page loop.
	 * @return array Posts array, unharmed.
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.4
	 */
	public static function search_content_for_shortcodes_and_blocks( $posts ) {
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
		remove_filter( 'posts_results', array( __CLASS__, 'search_content_for_shortcodes_and_blocks' ) );

		foreach ( $posts as $post ) {
			if ( self::has_shortcodes( $post ) || self::has_blocks( $post ) ) {
				self::add_print_style_action();
			}
		}

		return $posts;
	}

	/**
	 * Current page has the shortcode specified.
	 *
	 * @param [type] $shortcode Shortcode.
	 * @return boolean True if shortcode was found in the_content.
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.4
	 */
	public static function has_shortcode( $shortcode ) {
		$shortcode = ! is_array( $shortcode ) ? array( $shortcode ) : $shortcode;
		return ! empty( array_intersect( self::$has_shortcodes, $shortcode ) );
	}

	/**
	 * Current page has the block specified.
	 *
	 * @param [type] $block Block name.
	 * @return boolean True if block was found in the_content.
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.4
	 */
	public static function has_block( $block ) {
		$block = ! is_array( $block ) ? array( $block ) : $block;
		return ! empty( array_intersect( self::$has_blocks, $block ) );
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
		echo wp_kses( "<style id='wp-strava-style-translations'>\n{$style}</style>", array( 'style' => array( 'id' => array() ) ) );
	}

	/**
	 * Add filter to post_results content.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.4
	 */
	private static function add_content_filter() {
		if ( ! has_filter( 'posts_results', array( __CLASS__, 'search_content_for_shortcodes_and_blocks' ) ) ) {
			add_filter( 'posts_results', array( __CLASS__, 'search_content_for_shortcodes_and_blocks' ) );
		}
	}

	/**
	 * Undocumented function
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.4
	 */
	private static function add_print_style_action() {
		if ( ! has_action( 'wp_head', array( __CLASS__, 'print_style_translations' ) ) ) {
			add_action( 'wp_head', array( __CLASS__, 'print_style_translations' ) );
		}
	}

	/**
	 * Post contains one of the registered shortcodes.
	 *
	 * @param \WP_Post $post
	 * @return boolean
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.4
	 */
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

	/**
	 * Post contains one of the registered blocks.
	 *
	 * @param \WP_Post $post
	 * @return boolean
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.4
	 */
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
				$has_block = true;
			}
		}

		return $has_block;
	}

}
