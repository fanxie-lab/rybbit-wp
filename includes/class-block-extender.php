<?php
/**
 * Block Extender
 *
 * Extends Gutenberg blocks with Rybbit event tracking capabilities.
 *
 * @package RybbitAnalytics
 * @since   1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Block Extender class.
 *
 * Adds custom attributes and panels to Gutenberg blocks for event tracking.
 *
 * @since 1.0.0
 */
class Rybbit_Block_Extender {

	/**
	 * Enqueue block editor assets.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_block_editor_assets() {
		// Check if blocks.js exists.
		if ( ! file_exists( RYBBIT_PLUGIN_DIR . 'assets/js/blocks.js' ) ) {
			return;
		}

		// Enqueue block editor JavaScript.
		wp_enqueue_script(
			'rybbit-blocks',
			RYBBIT_PLUGIN_URL . 'assets/js/blocks.js',
			array(
				'wp-blocks',
				'wp-element',
				'wp-components',
				'wp-block-editor',
				'wp-hooks',
				'wp-compose',
			),
			RYBBIT_VERSION,
			true
		);

		// Enqueue block editor styles.
		if ( file_exists( RYBBIT_PLUGIN_DIR . 'assets/css/blocks.css' ) ) {
			wp_enqueue_style(
				'rybbit-blocks',
				RYBBIT_PLUGIN_URL . 'assets/css/blocks.css',
				array(),
				RYBBIT_VERSION
			);
		}

		// Localize script with data.
		wp_localize_script(
			'rybbit-blocks',
			'rybbitBlocks',
			array(
				'pluginUrl' => RYBBIT_PLUGIN_URL,
				'i18n'      => array(
					'eventTracking' => __( 'Rybbit Event Tracking', 'rybbit-analytics' ),
					'enableEvent'   => __( 'Enable Event Tracking', 'rybbit-analytics' ),
					'eventName'     => __( 'Event Name', 'rybbit-analytics' ),
					'eventProps'    => __( 'Event Properties', 'rybbit-analytics' ),
				),
			)
		);
	}

	/**
	 * Add event tracking attributes to button blocks.
	 *
	 * @since 1.0.0
	 * @param string $block_content Block HTML content.
	 * @param array  $block         Block data.
	 * @return string Modified block content.
	 */
	public function add_event_attributes_to_blocks( $block_content, $block ) {
		// Only process button blocks.
		if ( 'core/button' !== $block['blockName'] ) {
			return $block_content;
		}

		// Check if event tracking is enabled for this block.
		$attrs = $block['attrs'];
		if ( empty( $attrs['rybbitEventEnabled'] ) || empty( $attrs['rybbitEventName'] ) ) {
			return $block_content;
		}

		// Build data attributes.
		$event_name = esc_attr( $attrs['rybbitEventName'] );
		$data_attrs = ' data-rybbit-event="' . $event_name . '"';

		// Add event properties as data attributes.
		if ( ! empty( $attrs['rybbitEventProps'] ) && is_array( $attrs['rybbitEventProps'] ) ) {
			foreach ( $attrs['rybbitEventProps'] as $key => $value ) {
				$data_attrs .= ' data-rybbit-prop-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
			}
		}

		// Insert data attributes into the button tag.
		$block_content = preg_replace(
			'/(<a[^>]*class="[^"]*wp-block-button__link[^"]*"[^>]*)>/',
			'$1' . $data_attrs . '>',
			$block_content
		);

		return $block_content;
	}
}
