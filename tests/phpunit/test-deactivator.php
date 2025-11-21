<?php
/**
 * Deactivator Tests
 *
 * @package RybbitAnalytics
 */

if ( ! function_exists( 'delete_transient' ) ) {
	function delete_transient( $transient ) {
		unset( $GLOBALS['wp_transients'][ $transient ] );
		return true;
	}
}

/**
 * Test the Deactivator class.
 */
class Test_Rybbit_Deactivator extends PHPUnit\Framework\TestCase {

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$GLOBALS['wp_options'] = array();
		$GLOBALS['wp_transients'] = array();
	}

	/**
	 * Test deactivate removes transients.
	 */
	public function test_deactivate_removes_transients() {
		// Set the setup wizard transient.
		$GLOBALS['wp_transients']['rybbit_show_setup_wizard'] = true;

		// Run deactivation.
		Rybbit_Deactivator::deactivate();

		// Transient should be removed.
		$this->assertArrayNotHasKey( 'rybbit_show_setup_wizard', $GLOBALS['wp_transients'] );
	}

	/**
	 * Test deactivate doesn't remove settings.
	 */
	public function test_deactivate_preserves_settings() {
		// Set settings.
		$settings = array(
			'site_id'   => 'test-site',
			'connected' => true,
		);

		update_option( 'rybbit_settings', $settings );

		// Run deactivation.
		Rybbit_Deactivator::deactivate();

		// Settings should still exist.
		$saved_settings = get_option( 'rybbit_settings' );
		$this->assertEquals( 'test-site', $saved_settings['site_id'] );
		$this->assertTrue( $saved_settings['connected'] );
	}

	/**
	 * Test deactivate handles missing transients gracefully.
	 */
	public function test_deactivate_handles_missing_transients() {
		// Ensure no transients are set.
		$GLOBALS['wp_transients'] = array();

		// Should not throw an error.
		Rybbit_Deactivator::deactivate();

		$this->assertTrue( true );
	}
}
