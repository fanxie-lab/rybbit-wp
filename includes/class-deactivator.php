<?php
/**
 * Fired during plugin deactivation
 *
 * @package RybbitAnalytics
 * @since   1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since 1.0.0
 */
class Rybbit_Deactivator {

	/**
	 * Actions to perform on plugin deactivation.
	 *
	 * Clean up transients and flush rewrite rules.
	 * Note: Settings are preserved on deactivation and only removed on uninstall.
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
		// Delete transients.
		delete_transient( 'rybbit_show_setup_wizard' );

		// Flush rewrite rules.
		flush_rewrite_rules();
	}
}
