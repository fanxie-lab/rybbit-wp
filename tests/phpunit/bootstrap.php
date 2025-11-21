<?php
/**
 * PHPUnit Bootstrap File
 *
 * Sets up WordPress testing environment.
 *
 * @package RybbitAnalytics
 */

// Define plugin constants.
define( 'RYBBIT_PLUGIN_DIR', dirname( dirname( __DIR__ ) ) . '/' );
define( 'RYBBIT_PLUGIN_URL', 'http://example.com/wp-content/plugins/rybbit-analytics/' );
define( 'RYBBIT_VERSION', '1.0.0' );
define( 'WPINC', 'wp-includes' );

// Load Composer autoloader if available.
if ( file_exists( RYBBIT_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once RYBBIT_PLUGIN_DIR . 'vendor/autoload.php';
}

// Check if WordPress test library is available.
$wp_tests_available = false;

if ( file_exists( '/tmp/wordpress-tests-lib/includes/functions.php' ) ) {
	$wp_tests_available = true;
	require_once '/tmp/wordpress-tests-lib/includes/functions.php';
} elseif ( getenv( 'WP_TESTS_DIR' ) && file_exists( getenv( 'WP_TESTS_DIR' ) . '/includes/functions.php' ) ) {
	$wp_tests_available = true;
	require_once getenv( 'WP_TESTS_DIR' ) . '/includes/functions.php';
}

/**
 * Manually load the plugin for testing.
 */
function _manually_load_plugin() {
	// Load plugin classes.
	require_once RYBBIT_PLUGIN_DIR . 'includes/class-settings-manager.php';
	require_once RYBBIT_PLUGIN_DIR . 'includes/class-script-injector.php';
	require_once RYBBIT_PLUGIN_DIR . 'includes/class-rest-controller.php';
	require_once RYBBIT_PLUGIN_DIR . 'includes/class-woocommerce-handler.php';
	require_once RYBBIT_PLUGIN_DIR . 'includes/class-admin-page.php';
	require_once RYBBIT_PLUGIN_DIR . 'includes/class-activator.php';
	require_once RYBBIT_PLUGIN_DIR . 'includes/class-deactivator.php';
}

// Load WordPress testing environment if available.
if ( $wp_tests_available && file_exists( '/tmp/wordpress-tests-lib/includes/bootstrap.php' ) ) {
	tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
	require '/tmp/wordpress-tests-lib/includes/bootstrap.php';
} elseif ( $wp_tests_available && getenv( 'WP_TESTS_DIR' ) && file_exists( getenv( 'WP_TESTS_DIR' ) . '/includes/bootstrap.php' ) ) {
	tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
	require getenv( 'WP_TESTS_DIR' ) . '/includes/bootstrap.php';
} else {
	// If WordPress test library is not available, load mocks FIRST, then plugin classes.
	require_once __DIR__ . '/mocks/wordpress-functions.php';
	_manually_load_plugin();
}
