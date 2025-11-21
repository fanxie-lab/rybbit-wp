<?php
/**
 * The main plugin class
 *
 * This is used to define core functionality, admin-specific hooks,
 * and public-facing site hooks.
 *
 * @package RybbitAnalytics
 * @since   1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The main plugin class.
 *
 * This class is the core of the plugin and coordinates all hooks
 * between the admin area and the public-facing parts of the site.
 *
 * @since 1.0.0
 */
class Rybbit_Plugin {

	/**
	 * The loader that's responsible for maintaining and registering all hooks.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    Rybbit_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->version     = RYBBIT_VERSION;
		$this->plugin_name = 'rybbit-analytics';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function load_dependencies() {
		$this->loader = new Rybbit_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function set_locale() {
		$plugin_i18n = new Rybbit_I18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_admin_hooks() {
		// Admin page controller.
		$admin_page = new Rybbit_Admin_Page( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_menu', $admin_page, 'register_admin_menu' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin_page, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin_page, 'enqueue_scripts' );

		// REST API controller.
		$rest_controller = new Rybbit_Rest_Controller();
		$this->loader->add_action( 'rest_api_init', $rest_controller, 'register_routes' );

		// Block extender for Gutenberg.
		$block_extender = new Rybbit_Block_Extender();
		$this->loader->add_action( 'enqueue_block_editor_assets', $block_extender, 'enqueue_block_editor_assets' );
		$this->loader->add_filter( 'render_block', $block_extender, 'add_event_attributes_to_blocks', 10, 2 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_public_hooks() {
		// Script injector for frontend tracking.
		$script_injector = new Rybbit_Script_Injector();
		$this->loader->add_action( 'wp_head', $script_injector, 'inject_tracking_script', 1 );

		// WooCommerce handler - defer initialization until WooCommerce is loaded.
		// Priority 20 ensures WooCommerce is fully initialized first.
		$this->loader->add_action( 'woocommerce_init', $this, 'init_woocommerce_handler', 20 );

		// Fallback: Also check on plugins_loaded in case woocommerce_init doesn't fire.
		$this->loader->add_action( 'plugins_loaded', $this, 'maybe_init_woocommerce_handler', 20 );
	}

	/**
	 * Initialize the WooCommerce handler.
	 *
	 * Called on woocommerce_init to ensure WooCommerce is fully loaded.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_woocommerce_handler() {
		static $initialized = false;

		// Prevent double initialization.
		if ( $initialized ) {
			return;
		}

		if ( class_exists( 'WooCommerce' ) ) {
			$woocommerce_handler = new Rybbit_Woocommerce_Handler();
			$woocommerce_handler->init();
			$initialized = true;
		}
	}

	/**
	 * Fallback WooCommerce initialization.
	 *
	 * Called on plugins_loaded as a fallback in case woocommerce_init doesn't fire.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function maybe_init_woocommerce_handler() {
		// Only initialize if woocommerce_init hasn't fired yet.
		if ( class_exists( 'WooCommerce' ) && did_action( 'woocommerce_init' ) === 0 ) {
			$this->init_woocommerce_handler();
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since  1.0.0
	 * @return string The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since  1.0.0
	 * @return Rybbit_Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since  1.0.0
	 * @return string The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
