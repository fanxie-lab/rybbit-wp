<?php
/**
 * REST Controller Tests
 *
 * @package RybbitAnalytics
 */

/**
 * Test the REST Controller class.
 */
class Test_Rybbit_Rest_Controller extends PHPUnit\Framework\TestCase {

	/**
	 * REST Controller instance.
	 *
	 * @var Rybbit_Rest_Controller
	 */
	private $controller;

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$GLOBALS['wp_options'] = array();
		$GLOBALS['current_user_can'] = true;
		$this->controller = new Rybbit_Rest_Controller();
	}

	/**
	 * Test check_permission returns true for admin users.
	 */
	public function test_check_permission_for_admin() {
		$GLOBALS['current_user_can'] = true;

		$result = $this->controller->check_permission();

		$this->assertTrue( $result );
	}

	/**
	 * Test check_permission returns false for non-admin users.
	 */
	public function test_check_permission_for_non_admin() {
		$GLOBALS['current_user_can'] = false;

		$result = $this->controller->check_permission();

		$this->assertFalse( $result );
	}

	/**
	 * Test get_settings returns current settings.
	 */
	public function test_get_settings() {
		$test_settings = array(
			'site_id'   => 'test-site-123',
			'connected' => true,
		);

		update_option( 'rybbit_settings', $test_settings );

		$request = new WP_REST_Request();
		$response = $this->controller->get_settings( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertEquals( 'test-site-123', $data['site_id'] );
		$this->assertTrue( $data['connected'] );
	}

	/**
	 * Test update_settings with valid data.
	 */
	public function test_update_settings_valid() {
		$new_settings = array(
			'site_id'   => 'new-site-456',
			'connected' => true,
		);

		$request = new WP_REST_Request();
		$request->set_json_params( $new_settings );

		$response = $this->controller->update_settings( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertTrue( $data['success'] );
		$this->assertEquals( 'new-site-456', $data['settings']['site_id'] );
	}

	/**
	 * Test update_settings with invalid data.
	 */
	public function test_update_settings_invalid() {
		$invalid_settings = array(
			'site_id'   => '', // Empty site ID.
			'connected' => true, // But connected is true.
		);

		$request = new WP_REST_Request();
		$request->set_json_params( $invalid_settings );

		$response = $this->controller->update_settings( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 400, $response->get_status() );

		$data = $response->get_data();
		$this->assertFalse( $data['success'] );
		$this->assertArrayHasKey( 'errors', $data );
	}

	/**
	 * Test test_connection with valid site_id.
	 */
	public function test_test_connection_valid() {
		$request = new WP_REST_Request();
		$request->set_param( 'site_id', 'valid-site-123' );

		$response = $this->controller->test_connection( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertTrue( $data['success'] );
	}

	/**
	 * Test test_connection with invalid site_id format.
	 */
	public function test_test_connection_invalid_format() {
		$request = new WP_REST_Request();
		$request->set_param( 'site_id', 'invalid site!@#' );

		$response = $this->controller->test_connection( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 400, $response->get_status() );

		$data = $response->get_data();
		$this->assertFalse( $data['success'] );
	}

	/**
	 * Test test_connection with empty site_id.
	 */
	public function test_test_connection_empty() {
		$request = new WP_REST_Request();
		$request->set_param( 'site_id', '' );

		$response = $this->controller->test_connection( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 400, $response->get_status() );

		$data = $response->get_data();
		$this->assertFalse( $data['success'] );
	}

	/**
	 * Test test_connection accepts alphanumeric and dashes/underscores.
	 */
	public function test_test_connection_valid_chars() {
		$valid_ids = array(
			'site123',
			'site-123',
			'site_123',
			'Site-ABC_123',
		);

		foreach ( $valid_ids as $site_id ) {
			$request = new WP_REST_Request();
			$request->set_param( 'site_id', $site_id );

			$response = $this->controller->test_connection( $request );

			$this->assertEquals( 200, $response->get_status(), "Failed for site_id: $site_id" );
		}
	}

	/**
	 * Test get_sample_woocommerce_events returns sample data.
	 */
	public function test_get_sample_woocommerce_events() {
		// This test requires WooCommerce class to exist.
		// In our mock environment, it returns 400 because class_exists fails.
		$request = new WP_REST_Request();
		$response = $this->controller->get_sample_woocommerce_events( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		// Without WooCommerce, returns 400.
		$this->assertEquals( 400, $response->get_status() );
	}

	/**
	 * Test get_sample_woocommerce_events includes currency.
	 */
	public function test_get_sample_woocommerce_events_currency() {
		// This test requires WooCommerce class to exist.
		// Skip in mock environment.
		$this->markTestSkipped( 'Requires WooCommerce class' );
	}

	/**
	 * Test update_settings sanitizes input.
	 */
	public function test_update_settings_sanitizes_input() {
		$dirty_settings = array(
			'site_id' => '<script>alert("xss")</script>test',
			'skip_patterns' => array(
				'<b>/admin/**</b>',
			),
		);

		$request = new WP_REST_Request();
		$request->set_json_params( $dirty_settings );

		$response = $this->controller->update_settings( $request );

		$data = $response->get_data();
		$saved_settings = $data['settings'];

		// Should not contain script tags.
		$this->assertStringNotContainsString( '<script>', $saved_settings['site_id'] );
		$this->assertStringNotContainsString( '<b>', $saved_settings['skip_patterns'][0] );
	}

	/**
	 * Test update_settings merges with existing settings.
	 */
	public function test_update_settings_merges() {
		// Set initial settings.
		update_option( 'rybbit_settings', array(
			'site_id'   => 'original-site',
			'connected' => false,
		) );

		// Update only site_id.
		$partial_update = array(
			'site_id' => 'updated-site',
		);

		$request = new WP_REST_Request();
		$request->set_json_params( $partial_update );

		$response = $this->controller->update_settings( $request );

		$data = $response->get_data();
		$settings = $data['settings'];

		// Site ID should be updated.
		$this->assertEquals( 'updated-site', $settings['site_id'] );
		// Connected should still exist.
		$this->assertArrayHasKey( 'connected', $settings );
	}

	/**
	 * Test get_settings returns defaults for new installation.
	 */
	public function test_get_settings_defaults() {
		// No settings saved yet.
		$request = new WP_REST_Request();
		$response = $this->controller->get_settings( $request );

		$data = $response->get_data();

		// Should have default values.
		$this->assertEquals( '', $data['site_id'] );
		$this->assertEquals( 'https://app.rybbit.io/api/script.js', $data['script_url'] );
		$this->assertFalse( $data['connected'] );
		$this->assertEquals( 500, $data['debounce_delay'] );
	}

	/**
	 * Test update_settings validates debounce_delay range.
	 */
	public function test_update_settings_validates_debounce_range() {
		$invalid_settings = array(
			'debounce_delay' => 50, // Too low.
		);

		$request = new WP_REST_Request();
		$request->set_json_params( $invalid_settings );

		$response = $this->controller->update_settings( $request );

		$this->assertEquals( 400, $response->get_status() );

		$data = $response->get_data();
		$this->assertFalse( $data['success'] );
	}

	/**
	 * Test update_settings validates script_url format.
	 */
	public function test_update_settings_validates_script_url() {
		$invalid_settings = array(
			'script_url' => 'not-a-valid-url',
		);

		$request = new WP_REST_Request();
		$request->set_json_params( $invalid_settings );

		$response = $this->controller->update_settings( $request );

		$this->assertEquals( 400, $response->get_status() );

		$data = $response->get_data();
		$this->assertFalse( $data['success'] );
		$this->assertArrayHasKey( 'errors', $data );
	}

	/**
	 * Test get_posts returns empty array when no posts.
	 */
	public function test_get_posts_empty() {
		$request = new WP_REST_Request();
		$request->set_param( 'post_type', 'page' );
		$request->set_param( 'search', '' );
		$request->set_param( 'per_page', 20 );

		$response = $this->controller->get_posts( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $response->get_data() );
	}
}
