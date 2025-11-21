<?php
/**
 * Activator Tests
 *
 * @package RybbitAnalytics
 */

/**
 * Test the Activator class.
 */
class Test_Rybbit_Activator extends PHPUnit\Framework\TestCase {

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$GLOBALS['wp_options'] = array();
		$GLOBALS['wp_transients'] = array();
	}

	/**
	 * Test activate creates default settings.
	 */
	public function test_activate_creates_default_settings() {
		Rybbit_Activator::activate();

		$settings = get_option( 'rybbit_settings' );

		$this->assertIsArray( $settings );
		$this->assertArrayHasKey( 'site_id', $settings );
		$this->assertArrayHasKey( 'script_url', $settings );
		$this->assertArrayHasKey( 'connected', $settings );
		$this->assertArrayHasKey( 'woocommerce', $settings );
	}

	/**
	 * Test activate sets correct default values.
	 */
	public function test_activate_default_values() {
		Rybbit_Activator::activate();

		$settings = get_option( 'rybbit_settings' );

		$this->assertEquals( '', $settings['site_id'] );
		$this->assertEquals( 'https://app.rybbit.io/api/script.js', $settings['script_url'] );
		$this->assertFalse( $settings['connected'] );
		$this->assertEquals( 500, $settings['debounce_delay'] );
		$this->assertContains( '/wp-admin/**', $settings['skip_patterns'] );
		$this->assertContains( '/wp-login.php', $settings['skip_patterns'] );
	}

	/**
	 * Test activate doesn't overwrite existing settings.
	 */
	public function test_activate_preserves_existing_settings() {
		// Set existing settings.
		$existing = array(
			'site_id'   => 'existing-site-123',
			'connected' => true,
		);

		add_option( 'rybbit_settings', $existing );

		// Run activation.
		Rybbit_Activator::activate();

		// Settings should not be overwritten.
		$settings = get_option( 'rybbit_settings' );
		$this->assertEquals( 'existing-site-123', $settings['site_id'] );
		$this->assertTrue( $settings['connected'] );
	}

	/**
	 * Test activate stores plugin version.
	 */
	public function test_activate_stores_version() {
		Rybbit_Activator::activate();

		$version = get_option( 'rybbit_version' );

		$this->assertEquals( RYBBIT_VERSION, $version );
	}

	/**
	 * Test activate sets setup wizard transient.
	 */
	public function test_activate_sets_setup_wizard_transient() {
		Rybbit_Activator::activate();

		$transient = $GLOBALS['wp_transients']['rybbit_show_setup_wizard'];

		$this->assertTrue( $transient );
	}

	/**
	 * Test activate includes WooCommerce default settings.
	 */
	public function test_activate_woocommerce_defaults() {
		Rybbit_Activator::activate();

		$settings = get_option( 'rybbit_settings' );

		$this->assertArrayHasKey( 'woocommerce', $settings );
		$this->assertFalse( $settings['woocommerce']['enabled'] );
		$this->assertArrayHasKey( 'events', $settings['woocommerce'] );
		$this->assertTrue( $settings['woocommerce']['events']['view_item'] );
		$this->assertTrue( $settings['woocommerce']['events']['add_to_cart'] );
		$this->assertTrue( $settings['woocommerce']['events']['begin_checkout'] );
		$this->assertTrue( $settings['woocommerce']['events']['purchase'] );
	}

	/**
	 * Test activate includes dashboard features defaults.
	 */
	public function test_activate_dashboard_features_defaults() {
		Rybbit_Activator::activate();

		$settings = get_option( 'rybbit_settings' );

		$this->assertArrayHasKey( 'dashboard_features', $settings );
		$this->assertTrue( $settings['dashboard_features']['spa_tracking'] );
		$this->assertTrue( $settings['dashboard_features']['outbound_links'] );
		$this->assertFalse( $settings['dashboard_features']['error_tracking'] );
		$this->assertFalse( $settings['dashboard_features']['session_replay'] );
		$this->assertFalse( $settings['dashboard_features']['web_vitals'] );
	}

	/**
	 * Test activate includes user identification defaults.
	 */
	public function test_activate_user_identification_defaults() {
		Rybbit_Activator::activate();

		$settings = get_option( 'rybbit_settings' );

		$this->assertArrayHasKey( 'user_identification', $settings );
		$this->assertFalse( $settings['user_identification']['enabled'] );
		$this->assertEquals( 'user_id', $settings['user_identification']['identifier_type'] );
		$this->assertEquals( 'login', $settings['user_identification']['identify_on'] );
		$this->assertTrue( $settings['user_identification']['clear_on_logout'] );
	}

	/**
	 * Test activate initializes empty exclusions array.
	 */
	public function test_activate_exclusions_default() {
		Rybbit_Activator::activate();

		$settings = get_option( 'rybbit_settings' );

		$this->assertArrayHasKey( 'exclusions', $settings );
		$this->assertIsArray( $settings['exclusions'] );
		$this->assertEmpty( $settings['exclusions'] );
	}

	/**
	 * Test activate sets up mask patterns as empty array.
	 */
	public function test_activate_mask_patterns_default() {
		Rybbit_Activator::activate();

		$settings = get_option( 'rybbit_settings' );

		$this->assertArrayHasKey( 'mask_patterns', $settings );
		$this->assertIsArray( $settings['mask_patterns'] );
		$this->assertEmpty( $settings['mask_patterns'] );
	}

	/**
	 * Test activate sets up replay mask selectors as empty array.
	 */
	public function test_activate_replay_mask_selectors_default() {
		Rybbit_Activator::activate();

		$settings = get_option( 'rybbit_settings' );

		$this->assertArrayHasKey( 'replay_mask_selectors', $settings );
		$this->assertIsArray( $settings['replay_mask_selectors'] );
		$this->assertEmpty( $settings['replay_mask_selectors'] );
	}
}
