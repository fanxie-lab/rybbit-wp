<?php
/**
 * Admin Page Controller
 *
 * Handles admin menu registration and Vue app initialization.
 *
 * @package RybbitAnalytics
 * @since   1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Page Controller class.
 *
 * Registers the admin menu page and enqueues the Vue.js application.
 *
 * @since 1.0.0
 */
class Rybbit_Admin_Page {

	/**
	 * The ID of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the admin menu.
	 *
	 * @since 1.0.0
	 */
	public function register_admin_menu() {
		add_menu_page(
			__( 'Rybbit Analytics', 'rybbit-analytics' ),
			__( 'Rybbit Analytics', 'rybbit-analytics' ),
			'manage_options',
			'rybbit-analytics',
			array( $this, 'render_admin_page' ),
			'data:image/svg+xml;base64,' . base64_encode( $this->get_menu_icon() ),
			30
		);

		// Add submenu items.
		add_submenu_page(
			'rybbit-analytics',
			__( 'Dashboard', 'rybbit-analytics' ),
			__( 'Dashboard', 'rybbit-analytics' ),
			'manage_options',
			'rybbit-analytics',
			array( $this, 'render_admin_page' )
		);

		add_submenu_page(
			'rybbit-analytics',
			__( 'Settings', 'rybbit-analytics' ),
			__( 'Settings', 'rybbit-analytics' ),
			'manage_options',
			'rybbit-analytics-settings',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Render the admin page.
	 *
	 * @since 1.0.0
	 */
	public function render_admin_page() {
		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'rybbit-analytics' ) );
		}

		// Include the admin container template.
		require_once RYBBIT_PLUGIN_DIR . 'admin/views/admin-container.php';
	}

	/**
	 * Enqueue admin styles.
	 *
	 * @since 1.0.0
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_styles( $hook_suffix ) {
		// Only load on our plugin pages.
		if ( strpos( $hook_suffix, 'rybbit-analytics' ) === false ) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name . '-admin',
			RYBBIT_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			$this->version . '.' . filemtime( RYBBIT_PLUGIN_DIR . 'assets/css/admin.css' ),
			'all'
		);
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @since 1.0.0
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_scripts( $hook_suffix ) {
		// Only load on our plugin pages.
		if ( strpos( $hook_suffix, 'rybbit-analytics' ) === false ) {
			return;
		}

		// Enqueue Vue.js admin app.
		wp_enqueue_script(
			$this->plugin_name . '-admin',
			RYBBIT_PLUGIN_URL . 'assets/js/admin.js',
			array(),
			$this->version . '.' . filemtime( RYBBIT_PLUGIN_DIR . 'assets/js/admin.js' ),
			true
		);

		// Localize script with data for Vue app.
		wp_localize_script(
			$this->plugin_name . '-admin',
			'rybbitAdmin',
			array(
				'restUrl'       => esc_url_raw( rest_url() ),
				'restNonce'     => wp_create_nonce( 'wp_rest' ),
				'pluginUrl'     => RYBBIT_PLUGIN_URL,
				'siteUrl'       => get_site_url(),
				'adminUrl'      => admin_url(),
				'version'       => $this->version,
				'isWooCommerce' => class_exists( 'WooCommerce' ),
				'i18n'          => array(
					'saveSuccess' => __( 'Settings saved successfully.', 'rybbit-analytics' ),
					'saveError'   => __( 'Error saving settings.', 'rybbit-analytics' ),
					'testSuccess' => __( 'Connection test successful!', 'rybbit-analytics' ),
					'testError'   => __( 'Connection test failed.', 'rybbit-analytics' ),
				),
			)
		);
	}

	/**
	 * Get SVG icon for menu.
	 *
	 * @since  1.0.0
	 * @return string SVG icon markup.
	 */
	private function get_menu_icon() {
		return '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M10 2C5.58172 2 2 5.58172 2 10C2 14.4183 5.58172 18 10 18C14.4183 18 18 14.4183 18 10C18 5.58172 14.4183 2 10 2Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			<path d="M10 6V10L12.5 12.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>';
	}
}
