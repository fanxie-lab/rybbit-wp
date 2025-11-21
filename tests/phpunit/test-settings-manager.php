<?php
/**
 * Settings Manager Tests
 *
 * @package RybbitAnalytics
 */

/**
 * Test the Settings Manager class.
 */
class Test_Rybbit_Settings_Manager extends PHPUnit\Framework\TestCase {

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		// Clear all options before each test.
		$GLOBALS['wp_options'] = array();
	}

	/**
	 * Test get_default_settings returns correct structure.
	 */
	public function test_get_default_settings() {
		$defaults = Rybbit_Settings_Manager::get_default_settings();

		$this->assertIsArray( $defaults );
		$this->assertArrayHasKey( 'site_id', $defaults );
		$this->assertArrayHasKey( 'script_url', $defaults );
		$this->assertArrayHasKey( 'connected', $defaults );
		$this->assertArrayHasKey( 'skip_patterns', $defaults );
		$this->assertArrayHasKey( 'woocommerce', $defaults );
		$this->assertArrayHasKey( 'dashboard_features', $defaults );

		// Verify default values.
		$this->assertEquals( '', $defaults['site_id'] );
		$this->assertEquals( 'https://app.rybbit.io/api/script.js', $defaults['script_url'] );
		$this->assertFalse( $defaults['connected'] );
		$this->assertEquals( 500, $defaults['debounce_delay'] );
		$this->assertContains( '/wp-admin/**', $defaults['skip_patterns'] );
	}

	/**
	 * Test get_settings returns defaults when no settings exist.
	 */
	public function test_get_settings_returns_defaults_when_empty() {
		$settings = Rybbit_Settings_Manager::get_settings();

		$this->assertIsArray( $settings );
		$this->assertEquals( '', $settings['site_id'] );
		$this->assertEquals( 'https://app.rybbit.io/api/script.js', $settings['script_url'] );
	}

	/**
	 * Test get_settings returns saved settings.
	 */
	public function test_get_settings_returns_saved_settings() {
		$test_settings = array(
			'site_id'    => 'test-site-123',
			'connected'  => true,
			'script_url' => 'https://custom.example.com/script.js',
		);

		update_option( 'rybbit_settings', $test_settings );

		$settings = Rybbit_Settings_Manager::get_settings();

		$this->assertEquals( 'test-site-123', $settings['site_id'] );
		$this->assertTrue( $settings['connected'] );
		$this->assertEquals( 'https://custom.example.com/script.js', $settings['script_url'] );
	}

	/**
	 * Test get_setting with simple key.
	 */
	public function test_get_setting_simple_key() {
		$test_settings = array(
			'site_id'   => 'test-site-456',
			'connected' => true,
		);

		update_option( 'rybbit_settings', $test_settings );

		$site_id = Rybbit_Settings_Manager::get_setting( 'site_id' );
		$this->assertEquals( 'test-site-456', $site_id );

		$connected = Rybbit_Settings_Manager::get_setting( 'connected' );
		$this->assertTrue( $connected );
	}

	/**
	 * Test get_setting with dot notation for nested values.
	 */
	public function test_get_setting_with_dot_notation() {
		$test_settings = array(
			'woocommerce' => array(
				'enabled' => true,
				'events'  => array(
					'view_item'   => true,
					'add_to_cart' => false,
				),
			),
		);

		update_option( 'rybbit_settings', $test_settings );

		$wc_enabled = Rybbit_Settings_Manager::get_setting( 'woocommerce.enabled' );
		$this->assertTrue( $wc_enabled );

		$view_item = Rybbit_Settings_Manager::get_setting( 'woocommerce.events.view_item' );
		$this->assertTrue( $view_item );

		$add_to_cart = Rybbit_Settings_Manager::get_setting( 'woocommerce.events.add_to_cart' );
		$this->assertFalse( $add_to_cart );
	}

	/**
	 * Test get_setting returns default when key doesn't exist.
	 */
	public function test_get_setting_returns_default() {
		$result = Rybbit_Settings_Manager::get_setting( 'nonexistent_key', 'default_value' );
		$this->assertEquals( 'default_value', $result );

		$result = Rybbit_Settings_Manager::get_setting( 'nested.nonexistent', 'fallback' );
		$this->assertEquals( 'fallback', $result );
	}

	/**
	 * Test update_settings merges with existing settings.
	 */
	public function test_update_settings_merges() {
		// Set initial settings.
		$initial = array(
			'site_id'   => 'initial-site',
			'connected' => false,
		);
		update_option( 'rybbit_settings', $initial );

		// Update only site_id.
		$update = array( 'site_id' => 'updated-site' );
		Rybbit_Settings_Manager::update_settings( $update );

		$settings = Rybbit_Settings_Manager::get_settings();

		// Site ID should be updated.
		$this->assertEquals( 'updated-site', $settings['site_id'] );
		// Connected should still exist (not overwritten).
		$this->assertFalse( $settings['connected'] );
	}

	/**
	 * Test sanitize_settings sanitizes site_id.
	 */
	public function test_sanitize_settings_site_id() {
		$input = array(
			'site_id' => '<script>alert("xss")</script>test-site',
		);

		$sanitized = Rybbit_Settings_Manager::sanitize_settings( $input );

		$this->assertEquals( 'alert("xss")test-site', $sanitized['site_id'] );
		$this->assertStringNotContainsString( '<script>', $sanitized['site_id'] );
	}

	/**
	 * Test sanitize_settings sanitizes script_url.
	 */
	public function test_sanitize_settings_script_url() {
		$input = array(
			'script_url' => 'https://example.com/valid/script.js',
		);

		$sanitized = Rybbit_Settings_Manager::sanitize_settings( $input );

		// Valid URL should pass through.
		$this->assertStringContainsString( 'example.com', $sanitized['script_url'] );
	}

	/**
	 * Test sanitize_settings converts booleans correctly.
	 */
	public function test_sanitize_settings_booleans() {
		$input = array(
			'connected' => '1',
			'woocommerce' => array(
				'enabled' => 'true',
				'events'  => array(
					'view_item' => 1,
				),
			),
		);

		$sanitized = Rybbit_Settings_Manager::sanitize_settings( $input );

		$this->assertTrue( $sanitized['connected'] );
		$this->assertTrue( $sanitized['woocommerce']['enabled'] );
		$this->assertTrue( $sanitized['woocommerce']['events']['view_item'] );
	}

	/**
	 * Test sanitize_settings sanitizes arrays.
	 */
	public function test_sanitize_settings_arrays() {
		$input = array(
			'skip_patterns' => array(
				'/admin/**',
				'<script>bad</script>/path',
				'/checkout/*',
			),
		);

		$sanitized = Rybbit_Settings_Manager::sanitize_settings( $input );

		$this->assertIsArray( $sanitized['skip_patterns'] );
		$this->assertCount( 3, $sanitized['skip_patterns'] );
		$this->assertEquals( '/admin/**', $sanitized['skip_patterns'][0] );
		$this->assertStringNotContainsString( '<script>', $sanitized['skip_patterns'][1] );
	}

	/**
	 * Test sanitize_settings handles debounce_delay.
	 */
	public function test_sanitize_settings_debounce_delay() {
		$input = array(
			'debounce_delay' => '1000',
		);

		$sanitized = Rybbit_Settings_Manager::sanitize_settings( $input );

		$this->assertIsInt( $sanitized['debounce_delay'] );
		$this->assertEquals( 1000, $sanitized['debounce_delay'] );

		// Test negative value.
		$input = array( 'debounce_delay' => '-500' );
		$sanitized = Rybbit_Settings_Manager::sanitize_settings( $input );
		$this->assertEquals( 500, $sanitized['debounce_delay'] );
	}

	/**
	 * Test sanitize_settings handles exclusions.
	 */
	public function test_sanitize_settings_exclusions() {
		$input = array(
			'exclusions' => array(
				array(
					'type'      => 'post_type',
					'post_type' => 'page',
					'post_id'   => '123',
				),
				array(
					'type'      => 'post_type',
					'post_type' => '<script>page</script>',
					'post_id'   => 'not-a-number',
				),
			),
		);

		$sanitized = Rybbit_Settings_Manager::sanitize_settings( $input );

		$this->assertCount( 2, $sanitized['exclusions'] );
		$this->assertEquals( 123, $sanitized['exclusions'][0]['post_id'] );
		$this->assertStringNotContainsString( '<script>', $sanitized['exclusions'][1]['post_type'] );
		$this->assertEquals( 0, $sanitized['exclusions'][1]['post_id'] );
	}

	/**
	 * Test validate_settings passes with valid data.
	 */
	public function test_validate_settings_valid() {
		$valid_settings = array(
			'site_id'        => 'test-site-123',
			'connected'      => true,
			'script_url'     => 'https://example.com/script.js',
			'debounce_delay' => 500,
		);

		$result = Rybbit_Settings_Manager::validate_settings( $valid_settings );

		$this->assertTrue( $result );
	}

	/**
	 * Test validate_settings fails when site_id missing but connected.
	 */
	public function test_validate_settings_missing_site_id() {
		$invalid_settings = array(
			'site_id'   => '',
			'connected' => true,
		);

		$result = Rybbit_Settings_Manager::validate_settings( $invalid_settings );

		$this->assertInstanceOf( 'WP_Error', $result );
		$errors = $result->get_error_data();
		$this->assertArrayHasKey( 'site_id', $errors );
	}

	/**
	 * Test validate_settings fails with invalid URL.
	 */
	public function test_validate_settings_invalid_url() {
		$invalid_settings = array(
			'script_url' => 'not-a-valid-url',
		);

		$result = Rybbit_Settings_Manager::validate_settings( $invalid_settings );

		$this->assertInstanceOf( 'WP_Error', $result );
		$errors = $result->get_error_data();
		$this->assertArrayHasKey( 'script_url', $errors );
	}

	/**
	 * Test validate_settings fails with invalid debounce_delay.
	 */
	public function test_validate_settings_invalid_debounce() {
		$invalid_settings = array(
			'debounce_delay' => 50, // Too low.
		);

		$result = Rybbit_Settings_Manager::validate_settings( $invalid_settings );

		$this->assertInstanceOf( 'WP_Error', $result );
		$errors = $result->get_error_data();
		$this->assertArrayHasKey( 'debounce_delay', $errors );

		// Test too high.
		$invalid_settings = array(
			'debounce_delay' => 3000, // Too high.
		);

		$result = Rybbit_Settings_Manager::validate_settings( $invalid_settings );
		$this->assertInstanceOf( 'WP_Error', $result );
	}

	/**
	 * Test reset_settings restores defaults.
	 */
	public function test_reset_settings() {
		// Set custom settings.
		$custom = array(
			'site_id'   => 'custom-site',
			'connected' => true,
		);
		update_option( 'rybbit_settings', $custom );

		// Reset to defaults.
		Rybbit_Settings_Manager::reset_settings();

		$settings = Rybbit_Settings_Manager::get_settings();

		// Should be back to defaults.
		$this->assertEquals( '', $settings['site_id'] );
		$this->assertFalse( $settings['connected'] );
		$this->assertEquals( 'https://app.rybbit.io/api/script.js', $settings['script_url'] );
	}

	/**
	 * Test delete_settings removes option.
	 */
	public function test_delete_settings() {
		// Set settings.
		update_option( 'rybbit_settings', array( 'site_id' => 'test' ) );

		// Delete settings.
		Rybbit_Settings_Manager::delete_settings();

		// Should return false (not set).
		$option = get_option( 'rybbit_settings', false );
		$this->assertFalse( $option );
	}

	/**
	 * Test sanitize_settings preserves nested structure.
	 */
	public function test_sanitize_settings_nested_structure() {
		$input = array(
			'woocommerce' => array(
				'enabled' => true,
				'events'  => array(
					'view_item'      => true,
					'add_to_cart'    => false,
					'begin_checkout' => true,
					'purchase'       => true,
				),
			),
			'dashboard_features' => array(
				'spa_tracking'   => true,
				'outbound_links' => false,
			),
		);

		$sanitized = Rybbit_Settings_Manager::sanitize_settings( $input );

		$this->assertArrayHasKey( 'woocommerce', $sanitized );
		$this->assertArrayHasKey( 'events', $sanitized['woocommerce'] );
		$this->assertTrue( $sanitized['woocommerce']['events']['view_item'] );
		$this->assertFalse( $sanitized['woocommerce']['events']['add_to_cart'] );
		$this->assertFalse( $sanitized['dashboard_features']['outbound_links'] );
	}
}
