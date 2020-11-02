<?php
/*
 * Activity block.
 */

class WPStrava_Blocks_Activity implements WPStrava_Blocks_Interface {

	public function __construct() {
		WPStrava_ActivityRenderer::load_style_translations( array( 'wp-strava/activity' ) );
	}

	/**
	 * Register the wp-strava/activity block.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.2.0
	 */
	public function register_block() {
		register_block_type(
			'wp-strava/activity',
			array(
				'style'           => 'wp-strava-block',
				'editor_style'    => 'wp-strava-block-editor',
				'editor_script'   => 'wp-strava-block',
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Render for this block.
	 *
	 * @param array $attributes JSON attributes saved in the HTML comment for this block.
	 * @param string $content The content from JS save() for this block.
	 * @return string HTML for this block.
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.2.0
	 */
	public function render_block( $attributes, $content ) {
		if ( empty( $attributes['url'] ) ) {
			return $content;
		}

		$matches = [];
		preg_match( "/\/activities\/([0-9].*)$/", $attributes['url'], $matches );
		if ( $matches[1] ) {
			// Transform from block attributes to shortcode standard.
			$attributes = array(
				'image_only' => isset( $attributes['imageOnly'] ) ? $attributes['imageOnly'] : false,
				'markers'    => isset( $attributes['displayMarkers'] ) ? $attributes['displayMarkers'] : false,
				'id'         => $matches[1],
			);

			$renderer = new WPStrava_ActivityRenderer();
			return $renderer->get_html( $attributes );
		}
		return $content;
	}
}
