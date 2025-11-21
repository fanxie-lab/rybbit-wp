<?php
/**
 * Fired during plugin activation
 *
 * @package RybbitAnalytics
 * @since   1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since 1.0.0
 */
class Rybbit_Activator {

	/**
	 * Actions to perform on plugin activation.
	 *
	 * Set up default settings and perform any necessary database operations.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		// Initialize default settings if they don't exist.
		if ( false === get_option( 'rybbit_settings' ) ) {
			$default_settings = array(
				'site_id'               => '',
				'script_url'            => 'https://app.rybbit.io/api/script.js',
				'connected'             => false,
				'skip_patterns'         => array( '/wp-admin/**', '/wp-login.php' ),
				'mask_patterns'         => array(),
				'replay_mask_selectors' => array(),
				'debounce_delay'        => 500,
				'woocommerce'           => array(
					'enabled' => false,
					'events'  => array(
						'view_item'      => true,
						'add_to_cart'    => true,
						'begin_checkout' => true,
						'purchase'       => true,
					),
				),
				'dashboard_features'    => array(
					'spa_tracking'   => true,
					'outbound_links' => true,
					'error_tracking' => false,
					'session_replay' => false,
					'web_vitals'     => false,
				),
				'user_identification'   => array(
					'enabled'          => false,
					'identifier_type'  => 'user_id',
					'identify_on'      => 'login',
					'clear_on_logout'  => true,
				),
				'exclusions'            => array(),
			);

			add_option( 'rybbit_settings', $default_settings );
		}

		// Store plugin version.
		add_option( 'rybbit_version', RYBBIT_VERSION );

		// Set a transient to trigger setup wizard on first activation.
		set_transient( 'rybbit_show_setup_wizard', true, 60 );

		// Flush rewrite rules.
		flush_rewrite_rules();
	}
}
