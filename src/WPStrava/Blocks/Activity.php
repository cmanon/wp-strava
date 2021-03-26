<?php
/*
 * Activity block.
 */

class WPStrava_Blocks_Activity implements WPStrava_Blocks_Interface {

	/**
	 * Whether or not to enqueue styles (if shortcode is present).
	 *
	 * @var boolean
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.5.0
	 */
	private $add_script = false;

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
				'attributes'      => array(
					'url'            => array(
						'type'    => 'string',
						'default' => '',
					),
					'imageOnly'      => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'displayMarkers' => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'som'            => array(
						'type'    => 'string',
						'default' => null,
					),
				),
			)
		);
		add_action( 'wp_footer', array( $this, 'print_scripts' ) );
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

		$this->add_script = true;

		$matches = array();
		preg_match( '/\/activities\/([0-9].*)$/', $attributes['url'], $matches );
		if ( $matches[1] ) {
			// Transform from block attributes to shortcode standard.
			$attributes = array(
				'id'         => $matches[1],
				'image_only' => isset( $attributes['imageOnly'] ) ? $attributes['imageOnly'] : false,
				'markers'    => isset( $attributes['displayMarkers'] ) ? $attributes['displayMarkers'] : false,
				'som'        => ! empty( $attributes['som'] ) ? $attributes['som'] : null,
			);

			$renderer = new WPStrava_ActivityRenderer();
			return $renderer->get_html( $attributes );
		}
		return $content;
	}

	/**
	 * Enqueue style if block is being used.
	 *
	 * @author Justin Foell <justin@foell.org>
	 * @since  2.5.0
	 */
	public function print_scripts() {
		if ( $this->add_script ) {
			wp_enqueue_style( 'wp-strava-style' );
		}
	}
}
