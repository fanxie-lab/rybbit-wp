<?php
/**
 * Autoloader for plugin classes
 *
 * @package RybbitAnalytics
 * @since   1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Autoloader class for loading plugin classes automatically.
 *
 * Implements PSR-4 autoloading for plugin classes.
 *
 * @since 1.0.0
 */
class Rybbit_Autoloader {

	/**
	 * Register the autoloader.
	 *
	 * @since 1.0.0
	 */
	public static function register() {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Autoload classes.
	 *
	 * @since 1.0.0
	 * @param string $class The fully-qualified class name.
	 */
	public static function autoload( $class ) {
		// Project-specific namespace prefix.
		$prefix = 'Rybbit_';

		// Base directory for the namespace prefix.
		$base_dir = RYBBIT_PLUGIN_DIR . 'includes/';

		// Does the class use the namespace prefix?
		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			// No, move to the next registered autoloader.
			return;
		}

		// Get the relative class name.
		$relative_class = substr( $class, $len );

		// Convert to lowercase and replace underscores with hyphens.
		$relative_class = strtolower( str_replace( '_', '-', $relative_class ) );

		// Build the file path.
		$file = $base_dir . 'class-' . $relative_class . '.php';

		// If the file exists, require it.
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}
