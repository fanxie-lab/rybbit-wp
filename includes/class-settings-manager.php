<?php
/**
 * Settings Manager
 *
 * Handles CRUD operations for plugin settings.
 *
 * @package RybbitAnalytics
 * @since   1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Settings Manager class.
 *
 * Provides methods for creating, reading, updating, and deleting plugin settings.
 * All settings are stored in the wp_options table as a single JSON object.
 *
 * @since 1.0.0
 */
class Rybbit_Settings_Manager {

	/**
	 * Option name in wp_options table.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	const OPTION_NAME = 'rybbit_settings';

	/**
	 * Get all plugin settings.
	 *
	 * @since 1.0.0
	 * @return array Plugin settings.
	 */
	public static function get_settings() {
		$settings = get_option( self::OPTION_NAME, array() );

		// Ensure all required keys exist with default values.
		return wp_parse_args(
			$settings,
			self::get_default_settings()
		);
	}

	/**
	 * Get a specific setting value.
	 *
	 * @since 1.0.0
	 * @param string $key     Setting key (supports dot notation for nested values).
	 * @param mixed  $default Default value if key doesn't exist.
	 * @return mixed Setting value or default.
	 */
	public static function get_setting( $key, $default = null ) {
		$settings = self::get_settings();

		// Support dot notation for nested keys (e.g., 'woocommerce.enabled').
		if ( strpos( $key, '.' ) !== false ) {
			$keys  = explode( '.', $key );
			$value = $settings;

			foreach ( $keys as $nested_key ) {
				if ( isset( $value[ $nested_key ] ) ) {
					$value = $value[ $nested_key ];
				} else {
					return $default;
				}
			}

			return $value;
		}

		return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
	}

	/**
	 * Update plugin settings.
	 *
	 * @since 1.0.0
	 * @param array $new_settings New settings to save.
	 * @return bool True if updated successfully, false otherwise.
	 */
	public static function update_settings( $new_settings ) {
		$current_settings = self::get_settings();
		$merged_settings  = array_replace_recursive( $current_settings, $new_settings );
		$sanitized        = self::sanitize_settings( $merged_settings );

		return update_option( self::OPTION_NAME, $sanitized );
	}

	/**
	 * Reset settings to defaults.
	 *
	 * @since 1.0.0
	 * @return bool True if reset successfully, false otherwise.
	 */
	public static function reset_settings() {
		return update_option( self::OPTION_NAME, self::get_default_settings() );
	}

	/**
	 * Delete all plugin settings.
	 *
	 * @since 1.0.0
	 * @return bool True if deleted successfully, false otherwise.
	 */
	public static function delete_settings() {
		return delete_option( self::OPTION_NAME );
	}

	/**
	 * Get default settings.
	 *
	 * @since 1.0.0
	 * @return array Default settings.
	 */
	public static function get_default_settings() {
		return array(
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
				'enabled'         => false,
				'identifier_type' => 'user_id',
				'identify_on'     => 'login',
				'clear_on_logout' => true,
			),
			'exclude_roles'         => array( 'administrator', 'editor' ),
			'exclusions'            => array(),
		);
	}

	/**
	 * Sanitize settings before saving.
	 *
	 * @since 1.0.0
	 * @param array $settings Settings to sanitize.
	 * @return array Sanitized settings.
	 */
	public static function sanitize_settings( $settings ) {
		$sanitized = array();

		// Sanitize site_id.
		$sanitized['site_id'] = isset( $settings['site_id'] ) ? sanitize_text_field( $settings['site_id'] ) : '';

		// Sanitize script_url.
		$sanitized['script_url'] = isset( $settings['script_url'] ) ? esc_url_raw( $settings['script_url'] ) : 'https://app.rybbit.io/api/script.js';

		// Sanitize connected (boolean).
		$sanitized['connected'] = isset( $settings['connected'] ) ? (bool) $settings['connected'] : false;

		// Sanitize skip_patterns (array of strings).
		$sanitized['skip_patterns'] = isset( $settings['skip_patterns'] ) && is_array( $settings['skip_patterns'] ) ?
			array_map( 'sanitize_text_field', $settings['skip_patterns'] ) : array();

		// Sanitize mask_patterns (array of strings).
		$sanitized['mask_patterns'] = isset( $settings['mask_patterns'] ) && is_array( $settings['mask_patterns'] ) ?
			array_map( 'sanitize_text_field', $settings['mask_patterns'] ) : array();

		// Sanitize replay_mask_selectors (array of strings).
		$sanitized['replay_mask_selectors'] = isset( $settings['replay_mask_selectors'] ) && is_array( $settings['replay_mask_selectors'] ) ?
			array_map( 'sanitize_text_field', $settings['replay_mask_selectors'] ) : array();

		// Sanitize debounce_delay (integer).
		$sanitized['debounce_delay'] = isset( $settings['debounce_delay'] ) ? absint( $settings['debounce_delay'] ) : 500;

		// Sanitize WooCommerce settings.
		$sanitized['woocommerce'] = array(
			'enabled' => isset( $settings['woocommerce']['enabled'] ) ? (bool) $settings['woocommerce']['enabled'] : false,
			'events'  => array(
				'view_item'      => isset( $settings['woocommerce']['events']['view_item'] ) ? (bool) $settings['woocommerce']['events']['view_item'] : true,
				'add_to_cart'    => isset( $settings['woocommerce']['events']['add_to_cart'] ) ? (bool) $settings['woocommerce']['events']['add_to_cart'] : true,
				'begin_checkout' => isset( $settings['woocommerce']['events']['begin_checkout'] ) ? (bool) $settings['woocommerce']['events']['begin_checkout'] : true,
				'purchase'       => isset( $settings['woocommerce']['events']['purchase'] ) ? (bool) $settings['woocommerce']['events']['purchase'] : true,
			),
		);

		// Sanitize dashboard features.
		$sanitized['dashboard_features'] = array(
			'spa_tracking'   => isset( $settings['dashboard_features']['spa_tracking'] ) ? (bool) $settings['dashboard_features']['spa_tracking'] : true,
			'outbound_links' => isset( $settings['dashboard_features']['outbound_links'] ) ? (bool) $settings['dashboard_features']['outbound_links'] : true,
			'error_tracking' => isset( $settings['dashboard_features']['error_tracking'] ) ? (bool) $settings['dashboard_features']['error_tracking'] : false,
			'session_replay' => isset( $settings['dashboard_features']['session_replay'] ) ? (bool) $settings['dashboard_features']['session_replay'] : false,
			'web_vitals'     => isset( $settings['dashboard_features']['web_vitals'] ) ? (bool) $settings['dashboard_features']['web_vitals'] : false,
		);

		// Sanitize user identification.
		$sanitized['user_identification'] = array(
			'enabled'         => isset( $settings['user_identification']['enabled'] ) ? (bool) $settings['user_identification']['enabled'] : false,
			'identifier_type' => isset( $settings['user_identification']['identifier_type'] ) ? sanitize_text_field( $settings['user_identification']['identifier_type'] ) : 'user_id',
			'identify_on'     => isset( $settings['user_identification']['identify_on'] ) ? sanitize_text_field( $settings['user_identification']['identify_on'] ) : 'login',
			'clear_on_logout' => isset( $settings['user_identification']['clear_on_logout'] ) ? (bool) $settings['user_identification']['clear_on_logout'] : true,
		);

		// Sanitize exclude_roles (array of valid WordPress roles).
		$valid_roles              = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );
		$sanitized['exclude_roles'] = array();
		if ( isset( $settings['exclude_roles'] ) && is_array( $settings['exclude_roles'] ) ) {
			foreach ( $settings['exclude_roles'] as $role ) {
				$role = sanitize_text_field( $role );
				if ( in_array( $role, $valid_roles, true ) ) {
					$sanitized['exclude_roles'][] = $role;
				}
			}
		}

		// Sanitize exclusions.
		$sanitized['exclusions'] = isset( $settings['exclusions'] ) && is_array( $settings['exclusions'] ) ?
			array_map( array( __CLASS__, 'sanitize_exclusion' ), $settings['exclusions'] ) : array();

		return $sanitized;
	}

	/**
	 * Sanitize a single exclusion rule.
	 *
	 * @since 1.0.0
	 * @param array $exclusion Exclusion rule to sanitize.
	 * @return array Sanitized exclusion rule.
	 */
	private static function sanitize_exclusion( $exclusion ) {
		return array(
			'type'      => isset( $exclusion['type'] ) ? sanitize_text_field( $exclusion['type'] ) : '',
			'post_type' => isset( $exclusion['post_type'] ) ? sanitize_text_field( $exclusion['post_type'] ) : '',
			'post_id'   => isset( $exclusion['post_id'] ) ? absint( $exclusion['post_id'] ) : 0,
		);
	}

	/**
	 * Validate settings.
	 *
	 * @since 1.0.0
	 * @param array $settings Settings to validate.
	 * @return array|WP_Error Array of validation errors or true if valid.
	 */
	public static function validate_settings( $settings ) {
		$errors = array();

		// Validate site_id (required if connected is true).
		if ( isset( $settings['connected'] ) && $settings['connected'] && empty( $settings['site_id'] ) ) {
			$errors['site_id'] = __( 'Site ID is required when connection is enabled.', 'rybbit-analytics' );
		}

		// Validate script_url.
		if ( isset( $settings['script_url'] ) && ! filter_var( $settings['script_url'], FILTER_VALIDATE_URL ) ) {
			$errors['script_url'] = __( 'Script URL must be a valid URL.', 'rybbit-analytics' );
		}

		// Validate debounce_delay (must be between 100 and 2000).
		if ( isset( $settings['debounce_delay'] ) ) {
			$delay = absint( $settings['debounce_delay'] );
			if ( $delay < 100 || $delay > 2000 ) {
				$errors['debounce_delay'] = __( 'Debounce delay must be between 100 and 2000 milliseconds.', 'rybbit-analytics' );
			}
		}

		return empty( $errors ) ? true : new WP_Error( 'validation_failed', __( 'Settings validation failed.', 'rybbit-analytics' ), $errors );
	}

	/**
	 * Validate a URL pattern for exclusions or masking.
	 *
	 * Validates patterns like `/admin/**` (double wildcard for multiple segments),
	 * `/user/*` (single wildcard for one segment), `/checkout` (exact path),
	 * and `/products/star/reviews` (wildcard in middle where star = asterisk).
	 *
	 * @since 1.0.0
	 * @param string $pattern Pattern to validate.
	 * @return array Validation result with 'valid' (bool), 'message' (string), and 'normalized' (string) keys.
	 */
	public static function validate_pattern( $pattern ) {
		$result = array(
			'valid'      => false,
			'message'    => '',
			'normalized' => '',
			'type'       => '',
			'examples'   => array(),
		);

		// Check if pattern is empty.
		if ( empty( $pattern ) || ! is_string( $pattern ) ) {
			$result['message'] = __( 'Pattern cannot be empty.', 'rybbit-analytics' );
			return $result;
		}

		// Trim and sanitize the pattern.
		$pattern = trim( $pattern );

		// Pattern must start with a forward slash.
		if ( strpos( $pattern, '/' ) !== 0 ) {
			$result['message'] = __( 'Pattern must start with a forward slash (/).', 'rybbit-analytics' );
			return $result;
		}

		// Check for invalid characters (only allow alphanumeric, -, _, ., *, /).
		if ( preg_match( '/[^a-zA-Z0-9\-_\.\*\/]/', $pattern ) ) {
			$result['message'] = __( 'Pattern contains invalid characters. Only alphanumeric characters, hyphens, underscores, dots, asterisks, and forward slashes are allowed.', 'rybbit-analytics' );
			return $result;
		}

		// Check for consecutive slashes (except after protocol, but we don't allow protocols).
		if ( preg_match( '/\/\//', $pattern ) ) {
			$result['message'] = __( 'Pattern cannot contain consecutive forward slashes.', 'rybbit-analytics' );
			return $result;
		}

		// Check for invalid wildcard usage (*** or more).
		if ( preg_match( '/\*{3,}/', $pattern ) ) {
			$result['message'] = __( 'Invalid wildcard usage. Use * for single segment or ** for multiple segments.', 'rybbit-analytics' );
			return $result;
		}

		// Check for ** not at the end (** should only be used at the end of a pattern).
		if ( preg_match( '/\*\*(?!\s*$)/', $pattern ) && ! preg_match( '/\*\*$/', $pattern ) ) {
			$result['message'] = __( 'Double wildcard (**) should only be used at the end of a pattern.', 'rybbit-analytics' );
			return $result;
		}

		// Normalize the pattern (remove trailing slash unless it's just /).
		$normalized = $pattern;
		if ( strlen( $normalized ) > 1 && substr( $normalized, -1 ) === '/' && substr( $normalized, -2 ) !== '**' ) {
			$normalized = rtrim( $normalized, '/' );
		}

		// Determine pattern type and generate examples.
		$type = 'exact';
		$examples = array();

		if ( strpos( $normalized, '**' ) !== false ) {
			$type = 'recursive';
			$base_path = str_replace( '**', '', $normalized );
			$examples = array(
				rtrim( $base_path, '/' ) . '/page',
				rtrim( $base_path, '/' ) . '/page/subpage',
				rtrim( $base_path, '/' ) . '/a/b/c',
			);
		} elseif ( strpos( $normalized, '*' ) !== false ) {
			$type = 'wildcard';
			$parts = explode( '*', $normalized );
			if ( count( $parts ) === 2 ) {
				$examples = array(
					$parts[0] . 'example' . $parts[1],
					$parts[0] . '123' . $parts[1],
					$parts[0] . 'test-item' . $parts[1],
				);
			}
		} else {
			$examples = array( $normalized );
		}

		$result['valid']      = true;
		$result['message']    = __( 'Pattern is valid.', 'rybbit-analytics' );
		$result['normalized'] = $normalized;
		$result['type']       = $type;
		$result['examples']   = $examples;

		return $result;
	}

	/**
	 * Validate multiple patterns at once.
	 *
	 * @since 1.0.0
	 * @param array $patterns Array of patterns to validate.
	 * @return array Array of validation results keyed by pattern.
	 */
	public static function validate_patterns( $patterns ) {
		$results = array();

		if ( ! is_array( $patterns ) ) {
			return $results;
		}

		foreach ( $patterns as $pattern ) {
			$results[ $pattern ] = self::validate_pattern( $pattern );
		}

		return $results;
	}

	/**
	 * Check if a URL matches a given pattern.
	 *
	 * This is a public wrapper for pattern matching logic that can be used
	 * for testing patterns in the admin interface.
	 *
	 * @since 1.0.0
	 * @param string $url     URL to check (can be full URL or just path).
	 * @param string $pattern Pattern to match against.
	 * @return bool True if URL matches pattern, false otherwise.
	 */
	public static function url_matches_pattern( $url, $pattern ) {
		// Extract path from URL if full URL is provided.
		$parsed = wp_parse_url( $url );
		$path   = isset( $parsed['path'] ) ? $parsed['path'] : '/';

		// Convert pattern to regex.
		$regex = self::pattern_to_regex( $pattern );

		return (bool) preg_match( $regex, $path );
	}

	/**
	 * Convert wildcard pattern to regex.
	 *
	 * @since 1.0.0
	 * @param string $pattern Wildcard pattern.
	 * @return string Regex pattern.
	 */
	private static function pattern_to_regex( $pattern ) {
		// Escape special regex characters except * and /.
		// Use # as delimiter to avoid conflicts with forward slashes in paths.
		$pattern = preg_quote( $pattern, '#' );

		// Replace \*\* with regex for multiple segments (matches anything including /).
		$pattern = str_replace( '\*\*', '.*', $pattern );

		// Replace \* with regex for single segment (matches anything except /).
		$pattern = str_replace( '\*', '[^/]*', $pattern );

		return '#^' . $pattern . '$#';
	}
}
