<?php
/**
 * Fired when the plugin is uninstalled
 *
 * @package RybbitAnalytics
 * @since   1.0.0
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Clean up plugin data on uninstall.
 *
 * This removes all plugin settings and options from the database.
 * Note: This only runs when the plugin is deleted, not deactivated.
 */

// Delete plugin settings.
delete_option( 'rybbit_settings' );
delete_option( 'rybbit_version' );

// Delete any transients.
delete_transient( 'rybbit_show_setup_wizard' );

// For multisite installations, delete site options if needed.
if ( is_multisite() ) {
	delete_site_option( 'rybbit_settings' );
	delete_site_option( 'rybbit_version' );
}

// Clean up any custom database tables (if we add any in the future).
// global $wpdb;
// $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}rybbit_data" );
