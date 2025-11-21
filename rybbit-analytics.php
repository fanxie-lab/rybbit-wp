<?php
/**
 * Plugin Name:       Rybbit Analytics for WordPress
 * Plugin URI:        https://github.com/fanxie-lab/rybbit-wp
 * Description:       Privacy-friendly, cookieless analytics for WordPress. Seamless integration with WooCommerce, Gutenberg blocks, and powerful tracking exclusions.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            David Paternina
 * Author URI:        https://dpaternina.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       rybbit-analytics
 * Domain Path:       /languages
 *
 * @package RybbitAnalytics
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define( 'RYBBIT_VERSION', '1.0.0' );

/**
 * Plugin root directory path.
 */
define( 'RYBBIT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin root directory URL.
 */
define( 'RYBBIT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename.
 */
define( 'RYBBIT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Minimum WordPress version required.
 */
define( 'RYBBIT_MIN_WP_VERSION', '6.0' );

/**
 * Minimum PHP version required.
 */
define( 'RYBBIT_MIN_PHP_VERSION', '7.4' );

/**
 * Autoloader for plugin classes.
 */
require_once RYBBIT_PLUGIN_DIR . 'includes/class-autoloader.php';
Rybbit_Autoloader::register();

/**
 * The code that runs during plugin activation.
 */
function rybbit_activate_plugin() {
	require_once RYBBIT_PLUGIN_DIR . 'includes/class-activator.php';
	Rybbit_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function rybbit_deactivate_plugin() {
	require_once RYBBIT_PLUGIN_DIR . 'includes/class-deactivator.php';
	Rybbit_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'rybbit_activate_plugin' );
register_deactivation_hook( __FILE__, 'rybbit_deactivate_plugin' );

/**
 * Check if WordPress and PHP versions are compatible.
 */
function rybbit_check_compatibility() {
	global $wp_version;

	if ( version_compare( $wp_version, RYBBIT_MIN_WP_VERSION, '<' ) ) {
		deactivate_plugins( RYBBIT_PLUGIN_BASENAME );
		wp_die(
			sprintf(
				/* translators: 1: Required WordPress version, 2: Current WordPress version */
				esc_html__( 'Rybbit Analytics requires WordPress %1$s or higher. You are running version %2$s. Please upgrade WordPress and try again.', 'rybbit-analytics' ),
				RYBBIT_MIN_WP_VERSION,
				$wp_version
			),
			esc_html__( 'Plugin Activation Error', 'rybbit-analytics' ),
			array( 'back_link' => true )
		);
	}

	if ( version_compare( PHP_VERSION, RYBBIT_MIN_PHP_VERSION, '<' ) ) {
		deactivate_plugins( RYBBIT_PLUGIN_BASENAME );
		wp_die(
			sprintf(
				/* translators: 1: Required PHP version, 2: Current PHP version */
				esc_html__( 'Rybbit Analytics requires PHP %1$s or higher. You are running version %2$s. Please contact your hosting provider to upgrade PHP.', 'rybbit-analytics' ),
				RYBBIT_MIN_PHP_VERSION,
				PHP_VERSION
			),
			esc_html__( 'Plugin Activation Error', 'rybbit-analytics' ),
			array( 'back_link' => true )
		);
	}
}
add_action( 'admin_init', 'rybbit_check_compatibility' );

/**
 * Begin execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function rybbit_run_plugin() {
	$plugin = new Rybbit_Plugin();
	$plugin->run();
}
rybbit_run_plugin();
