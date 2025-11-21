<?php
/**
 * Admin page container template
 *
 * This file provides the HTML container where the Vue.js app will mount.
 *
 * @package RybbitAnalytics
 * @since   1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>

<div class="wrap">
	<div id="rybbit-admin-app">
		<!-- Vue.js app will mount here -->
		<div class="rybbit-loading">
			<div class="rybbit-loading-spinner"></div>
			<p><?php esc_html_e( 'Loading Rybbit Analytics...', 'rybbit-analytics' ); ?></p>
		</div>
	</div>
</div>
