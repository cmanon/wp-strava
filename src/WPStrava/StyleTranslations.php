<?php

class WPStrava_StyleTranslations {

	/**
	 * Translations indexed by class.
	 * @var array
	 */
	private $style_translations = array();

	/**
	 * Hook into wp_head.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.4
	 */
	public function hook() {
		add_action( 'wp_head', array( $this, 'print_style_translations' ) );
	}

	/**
	 * Add translation text to column header, identified by class.
	 *
	 * @param [type] $class       CSS class for table data (<td>).
	 * @param [type] $translation Text to add to column.
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.4
	 */
	public function add_style_translation( $class, $translation ) {
		$this->style_translations[ $class ] = $translation;
	}
	/**
	 * Print translatable strings as <style> in <head>.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.4
	 */
	public function print_style_translations() {
		if ( empty( $this->style_translations ) ) {
			return;
		}

		$style = '';
		foreach ( $this->style_translations as $class => $translation ) {
			$style .= ".{$class}:before { content: \"{$translation}\"; }\n";
		}
		echo "<style>\n{$style}</style>";
	}

}
