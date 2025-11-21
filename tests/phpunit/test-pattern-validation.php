<?php
/**
 * Pattern Validation Tests
 *
 * Tests for URL pattern validation and matching functionality.
 *
 * @package RybbitAnalytics
 */

/**
 * Test the pattern validation functionality.
 */
class Test_Rybbit_Pattern_Validation extends PHPUnit\Framework\TestCase {

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$GLOBALS['wp_options'] = array();
	}

	/**
	 * Test validate_pattern with valid exact path.
	 */
	public function test_validate_pattern_exact_path() {
		$result = Rybbit_Settings_Manager::validate_pattern( '/checkout' );

		$this->assertTrue( $result['valid'] );
		$this->assertEquals( '/checkout', $result['normalized'] );
		$this->assertEquals( 'exact', $result['type'] );
	}

	/**
	 * Test validate_pattern with valid single wildcard.
	 */
	public function test_validate_pattern_single_wildcard() {
		$result = Rybbit_Settings_Manager::validate_pattern( '/user/*' );

		$this->assertTrue( $result['valid'] );
		$this->assertEquals( '/user/*', $result['normalized'] );
		$this->assertEquals( 'wildcard', $result['type'] );
		$this->assertNotEmpty( $result['examples'] );
	}

	/**
	 * Test validate_pattern with valid double wildcard.
	 */
	public function test_validate_pattern_double_wildcard() {
		$result = Rybbit_Settings_Manager::validate_pattern( '/admin/**' );

		$this->assertTrue( $result['valid'] );
		$this->assertEquals( '/admin/**', $result['normalized'] );
		$this->assertEquals( 'recursive', $result['type'] );
		$this->assertNotEmpty( $result['examples'] );
	}

	/**
	 * Test validate_pattern with wildcard in middle.
	 */
	public function test_validate_pattern_wildcard_middle() {
		$result = Rybbit_Settings_Manager::validate_pattern( '/products/*/reviews' );

		$this->assertTrue( $result['valid'] );
		$this->assertEquals( '/products/*/reviews', $result['normalized'] );
		$this->assertEquals( 'wildcard', $result['type'] );
	}

	/**
	 * Test validate_pattern with empty string.
	 */
	public function test_validate_pattern_empty() {
		$result = Rybbit_Settings_Manager::validate_pattern( '' );

		$this->assertFalse( $result['valid'] );
		$this->assertStringContainsString( 'empty', strtolower( $result['message'] ) );
	}

	/**
	 * Test validate_pattern with null.
	 */
	public function test_validate_pattern_null() {
		$result = Rybbit_Settings_Manager::validate_pattern( null );

		$this->assertFalse( $result['valid'] );
		$this->assertStringContainsString( 'empty', strtolower( $result['message'] ) );
	}

	/**
	 * Test validate_pattern without leading slash.
	 */
	public function test_validate_pattern_no_leading_slash() {
		$result = Rybbit_Settings_Manager::validate_pattern( 'admin/**' );

		$this->assertFalse( $result['valid'] );
		$this->assertStringContainsString( 'forward slash', strtolower( $result['message'] ) );
	}

	/**
	 * Test validate_pattern with invalid characters.
	 */
	public function test_validate_pattern_invalid_chars() {
		$result = Rybbit_Settings_Manager::validate_pattern( '/admin!@#$%' );

		$this->assertFalse( $result['valid'] );
		$this->assertStringContainsString( 'invalid characters', strtolower( $result['message'] ) );
	}

	/**
	 * Test validate_pattern with consecutive slashes.
	 */
	public function test_validate_pattern_consecutive_slashes() {
		$result = Rybbit_Settings_Manager::validate_pattern( '/admin//dashboard' );

		$this->assertFalse( $result['valid'] );
		$this->assertStringContainsString( 'consecutive', strtolower( $result['message'] ) );
	}

	/**
	 * Test validate_pattern with triple asterisk.
	 */
	public function test_validate_pattern_triple_asterisk() {
		$result = Rybbit_Settings_Manager::validate_pattern( '/admin/***' );

		$this->assertFalse( $result['valid'] );
		$this->assertStringContainsString( 'wildcard', strtolower( $result['message'] ) );
	}

	/**
	 * Test validate_pattern with double wildcard not at end.
	 */
	public function test_validate_pattern_double_wildcard_not_at_end() {
		$result = Rybbit_Settings_Manager::validate_pattern( '/admin/**/users' );

		$this->assertFalse( $result['valid'] );
		$this->assertStringContainsString( 'end', strtolower( $result['message'] ) );
	}

	/**
	 * Test validate_pattern normalizes trailing slash.
	 */
	public function test_validate_pattern_normalizes_trailing_slash() {
		$result = Rybbit_Settings_Manager::validate_pattern( '/admin/' );

		$this->assertTrue( $result['valid'] );
		$this->assertEquals( '/admin', $result['normalized'] );
	}

	/**
	 * Test validate_pattern preserves root path.
	 */
	public function test_validate_pattern_root_path() {
		$result = Rybbit_Settings_Manager::validate_pattern( '/' );

		$this->assertTrue( $result['valid'] );
		$this->assertEquals( '/', $result['normalized'] );
	}

	/**
	 * Test validate_pattern with file extension.
	 */
	public function test_validate_pattern_with_extension() {
		$result = Rybbit_Settings_Manager::validate_pattern( '/wp-login.php' );

		$this->assertTrue( $result['valid'] );
		$this->assertEquals( '/wp-login.php', $result['normalized'] );
		$this->assertEquals( 'exact', $result['type'] );
	}

	/**
	 * Test validate_patterns with multiple patterns.
	 */
	public function test_validate_patterns_multiple() {
		$patterns = array(
			'/admin/**',
			'/checkout',
			'invalid',
			'/user/*',
		);

		$results = Rybbit_Settings_Manager::validate_patterns( $patterns );

		$this->assertCount( 4, $results );
		$this->assertTrue( $results['/admin/**']['valid'] );
		$this->assertTrue( $results['/checkout']['valid'] );
		$this->assertFalse( $results['invalid']['valid'] );
		$this->assertTrue( $results['/user/*']['valid'] );
	}

	/**
	 * Test validate_patterns with empty array.
	 */
	public function test_validate_patterns_empty() {
		$results = Rybbit_Settings_Manager::validate_patterns( array() );

		$this->assertIsArray( $results );
		$this->assertEmpty( $results );
	}

	/**
	 * Test validate_patterns with non-array.
	 */
	public function test_validate_patterns_non_array() {
		$results = Rybbit_Settings_Manager::validate_patterns( 'not an array' );

		$this->assertIsArray( $results );
		$this->assertEmpty( $results );
	}

	/**
	 * Test url_matches_pattern with exact match.
	 */
	public function test_url_matches_pattern_exact() {
		$matches = Rybbit_Settings_Manager::url_matches_pattern( '/checkout', '/checkout' );
		$this->assertTrue( $matches );

		$matches = Rybbit_Settings_Manager::url_matches_pattern( '/checkout/confirm', '/checkout' );
		$this->assertFalse( $matches );
	}

	/**
	 * Test url_matches_pattern with single wildcard.
	 */
	public function test_url_matches_pattern_single_wildcard() {
		$pattern = '/user/*';

		$this->assertTrue( Rybbit_Settings_Manager::url_matches_pattern( '/user/123', $pattern ) );
		$this->assertTrue( Rybbit_Settings_Manager::url_matches_pattern( '/user/john', $pattern ) );
		$this->assertFalse( Rybbit_Settings_Manager::url_matches_pattern( '/user/123/profile', $pattern ) );
		$this->assertFalse( Rybbit_Settings_Manager::url_matches_pattern( '/users/123', $pattern ) );
	}

	/**
	 * Test url_matches_pattern with double wildcard.
	 */
	public function test_url_matches_pattern_double_wildcard() {
		$pattern = '/admin/**';

		$this->assertTrue( Rybbit_Settings_Manager::url_matches_pattern( '/admin/dashboard', $pattern ) );
		$this->assertTrue( Rybbit_Settings_Manager::url_matches_pattern( '/admin/users/edit', $pattern ) );
		$this->assertTrue( Rybbit_Settings_Manager::url_matches_pattern( '/admin/a/b/c/d', $pattern ) );
		$this->assertFalse( Rybbit_Settings_Manager::url_matches_pattern( '/administrator', $pattern ) );
	}

	/**
	 * Test url_matches_pattern with full URL.
	 */
	public function test_url_matches_pattern_full_url() {
		$pattern = '/checkout';

		$this->assertTrue( Rybbit_Settings_Manager::url_matches_pattern( 'http://example.com/checkout', $pattern ) );
		$this->assertTrue( Rybbit_Settings_Manager::url_matches_pattern( 'https://example.com/checkout', $pattern ) );
	}

	/**
	 * Test url_matches_pattern with wildcard in middle.
	 */
	public function test_url_matches_pattern_wildcard_middle() {
		$pattern = '/products/*/reviews';

		$this->assertTrue( Rybbit_Settings_Manager::url_matches_pattern( '/products/123/reviews', $pattern ) );
		$this->assertTrue( Rybbit_Settings_Manager::url_matches_pattern( '/products/laptop/reviews', $pattern ) );
		$this->assertFalse( Rybbit_Settings_Manager::url_matches_pattern( '/products/123/456/reviews', $pattern ) );
	}

	/**
	 * Test url_matches_pattern with wp-login.php.
	 */
	public function test_url_matches_pattern_wp_login() {
		$pattern = '/wp-login.php';

		$this->assertTrue( Rybbit_Settings_Manager::url_matches_pattern( '/wp-login.php', $pattern ) );
		$this->assertTrue( Rybbit_Settings_Manager::url_matches_pattern( 'http://example.com/wp-login.php', $pattern ) );
		$this->assertFalse( Rybbit_Settings_Manager::url_matches_pattern( '/wp-admin/index.php', $pattern ) );
	}

	/**
	 * Test url_matches_pattern with wp-admin pattern.
	 */
	public function test_url_matches_pattern_wp_admin() {
		$pattern = '/wp-admin/**';

		$this->assertTrue( Rybbit_Settings_Manager::url_matches_pattern( '/wp-admin/index.php', $pattern ) );
		$this->assertTrue( Rybbit_Settings_Manager::url_matches_pattern( '/wp-admin/plugins.php', $pattern ) );
		$this->assertTrue( Rybbit_Settings_Manager::url_matches_pattern( '/wp-admin/options-general.php', $pattern ) );
		$this->assertFalse( Rybbit_Settings_Manager::url_matches_pattern( '/wp-content/uploads', $pattern ) );
	}

	/**
	 * Test validate_pattern provides useful examples for recursive patterns.
	 */
	public function test_validate_pattern_examples_recursive() {
		$result = Rybbit_Settings_Manager::validate_pattern( '/admin/**' );

		$this->assertTrue( $result['valid'] );
		$this->assertCount( 3, $result['examples'] );
		$this->assertStringContainsString( '/admin/', $result['examples'][0] );
	}

	/**
	 * Test validate_pattern provides useful examples for wildcard patterns.
	 */
	public function test_validate_pattern_examples_wildcard() {
		$result = Rybbit_Settings_Manager::validate_pattern( '/user/*' );

		$this->assertTrue( $result['valid'] );
		$this->assertCount( 3, $result['examples'] );
		$this->assertStringContainsString( '/user/', $result['examples'][0] );
	}

	/**
	 * Test validate_pattern with hyphenated path.
	 */
	public function test_validate_pattern_hyphenated_path() {
		$result = Rybbit_Settings_Manager::validate_pattern( '/my-account/**' );

		$this->assertTrue( $result['valid'] );
		$this->assertEquals( '/my-account/**', $result['normalized'] );
	}

	/**
	 * Test validate_pattern with underscored path.
	 */
	public function test_validate_pattern_underscored_path() {
		$result = Rybbit_Settings_Manager::validate_pattern( '/user_profile/*' );

		$this->assertTrue( $result['valid'] );
		$this->assertEquals( '/user_profile/*', $result['normalized'] );
	}

	/**
	 * Test validate_pattern trims whitespace.
	 */
	public function test_validate_pattern_trims_whitespace() {
		$result = Rybbit_Settings_Manager::validate_pattern( '  /admin/**  ' );

		$this->assertTrue( $result['valid'] );
		$this->assertEquals( '/admin/**', $result['normalized'] );
	}
}
