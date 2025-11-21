<?php
/**
 * Script Injector
 *
 * Handles injection of Rybbit tracking script in the frontend.
 *
 * @package RybbitAnalytics
 * @since   1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Script Injector class.
 *
 * Injects the Rybbit tracking script into the page head with proper data attributes.
 *
 * @since 1.0.0
 */
class Rybbit_Script_Injector {

	/**
	 * Inject tracking script into wp_head.
	 *
	 * @since 1.0.0
	 */
	public function inject_tracking_script() {
		// Don't inject in admin or if user is not viewing frontend.
		if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}

		$settings = Rybbit_Settings_Manager::get_settings();

		// Don't inject if site_id is not set.
		if ( empty( $settings['site_id'] ) ) {
			return;
		}

		// Check if current page should be excluded.
		if ( $this->should_exclude_current_page( $settings ) ) {
			return;
		}

		// Build script tag with data attributes.
		$script_url = esc_url( $settings['script_url'] );
		$site_id    = esc_attr( $settings['site_id'] );

		echo "\n<!-- Rybbit Analytics -->\n";
		echo '<script async src="' . $script_url . '" data-site-id="' . $site_id . '"';

		// Add skip patterns.
		if ( ! empty( $settings['skip_patterns'] ) ) {
			echo ' data-skip-patterns="' . esc_attr( wp_json_encode( $settings['skip_patterns'] ) ) . '"';
		}

		// Add mask patterns.
		if ( ! empty( $settings['mask_patterns'] ) ) {
			echo ' data-mask-patterns="' . esc_attr( wp_json_encode( $settings['mask_patterns'] ) ) . '"';
		}

		// Add replay mask selectors.
		if ( ! empty( $settings['replay_mask_selectors'] ) ) {
			echo ' data-replay-mask-text-selectors="' . esc_attr( wp_json_encode( $settings['replay_mask_selectors'] ) ) . '"';
		}

		// Add debounce delay for SPA tracking.
		if ( ! empty( $settings['debounce_delay'] ) && $settings['debounce_delay'] !== 500 ) {
			echo ' data-debounce="' . absint( $settings['debounce_delay'] ) . '"';
		}

		echo '></script>';
		echo "\n<!-- End Rybbit Analytics -->\n";
	}

	/**
	 * Check if current page should be excluded from tracking.
	 *
	 * @since 1.0.0
	 * @param array $settings Plugin settings.
	 * @return bool True if page should be excluded, false otherwise.
	 */
	private function should_exclude_current_page( $settings ) {
		global $post;

		// Check if current user's role should be excluded.
		if ( $this->should_exclude_current_user( $settings ) ) {
			return true;
		}

		$current_url = $this->get_current_url();

		// Check URL pattern exclusions.
		if ( ! empty( $settings['skip_patterns'] ) ) {
			foreach ( $settings['skip_patterns'] as $pattern ) {
				if ( $this->matches_pattern( $current_url, $pattern ) ) {
					return true;
				}
			}
		}

		// Check individual post/page exclusions.
		if ( ! empty( $settings['exclusions'] ) && is_singular() && $post ) {
			foreach ( $settings['exclusions'] as $exclusion ) {
				if ( isset( $exclusion['post_id'] ) && $exclusion['post_id'] === $post->ID ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if current logged-in user should be excluded from tracking.
	 *
	 * @since 1.0.0
	 * @param array $settings Plugin settings.
	 * @return bool True if user should be excluded, false otherwise.
	 */
	private function should_exclude_current_user( $settings ) {
		// Only check if user is logged in.
		if ( ! is_user_logged_in() ) {
			return false;
		}

		// Check if there are roles to exclude.
		if ( empty( $settings['exclude_roles'] ) || ! is_array( $settings['exclude_roles'] ) ) {
			return false;
		}

		// Get current user.
		$user = wp_get_current_user();

		// Check if user has any of the excluded roles.
		foreach ( $settings['exclude_roles'] as $excluded_role ) {
			if ( in_array( $excluded_role, (array) $user->roles, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if URL matches a pattern.
	 *
	 * Supports wildcards:
	 * - * matches within one path segment
	 * - ** matches across multiple segments
	 *
	 * @since 1.0.0
	 * @param string $url     URL to check.
	 * @param string $pattern Pattern to match against.
	 * @return bool True if URL matches pattern, false otherwise.
	 */
	private function matches_pattern( $url, $pattern ) {
		// Parse URL to get path.
		$parsed = wp_parse_url( $url );
		$path   = isset( $parsed['path'] ) ? $parsed['path'] : '/';

		// Convert pattern to regex.
		$regex = $this->pattern_to_regex( $pattern );

		return (bool) preg_match( $regex, $path );
	}

	/**
	 * Convert wildcard pattern to regex.
	 *
	 * @since 1.0.0
	 * @param string $pattern Wildcard pattern.
	 * @return string Regex pattern.
	 */
	private function pattern_to_regex( $pattern ) {
		// Escape special regex characters except * and /.
		// Use # as delimiter to avoid conflicts with forward slashes in paths.
		$pattern = preg_quote( $pattern, '#' );

		// Replace \*\* with regex for multiple segments (matches anything including /).
		$pattern = str_replace( '\*\*', '.*', $pattern );

		// Replace \* with regex for single segment (matches anything except /).
		$pattern = str_replace( '\*', '[^/]*', $pattern );

		return '#^' . $pattern . '$#';
	}

	/**
	 * Get current page URL.
	 *
	 * @since 1.0.0
	 * @return string Current page URL.
	 */
	private function get_current_url() {
		global $wp;
		return home_url( add_query_arg( array(), $wp->request ) );
	}
}
