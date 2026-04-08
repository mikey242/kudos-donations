<?php
/**
 * CompatibilityService tests.
 */

namespace IseardMedia\Kudos\Tests\Service;

use IseardMedia\Kudos\Service\CompatibilityService;
use IseardMedia\Kudos\Tests\BaseTestCase;

/**
 * @covers \IseardMedia\Kudos\Service\CompatibilityService
 */
class CompatibilityServiceTest extends BaseTestCase {

	private CompatibilityService $service;

	public function set_up(): void {
		parent::set_up();
		$this->service = new CompatibilityService();
	}

	/**
	 * Test that check_compatibility returns true in a compatible environment.
	 */
	public function test_check_compatibility_returns_true_in_compatible_environment(): void {
		global $wp_version;
		$original    = $wp_version;
		$wp_version  = '6.9';

		$result = $this->service->check_compatibility();

		$wp_version = $original;

		$this->assertTrue( $result );
	}

	/**
	 * Test that check_compatibility returns WP_Error when WordPress is too old.
	 */
	public function test_check_compatibility_fails_on_old_wordpress(): void {
		global $wp_version;
		$original   = $wp_version;
		$wp_version = '6.8';

		$result = $this->service->check_compatibility();

		$wp_version = $original;

		$this->assertWPError( $result );
		$this->assertSame( 'wp_version_error', $result->get_error_code() );
	}

	/**
	 * Test that the WP version error message contains both versions.
	 */
	public function test_wp_version_error_message_contains_versions(): void {
		global $wp_version;
		$original   = $wp_version;
		$wp_version = '5.0';

		$result = $this->service->check_compatibility();

		$wp_version = $original;

		$this->assertWPError( $result );
		$this->assertStringContainsString( '5.0', $result->get_error_message() );
		$this->assertStringContainsString( '6.9', $result->get_error_message() );
	}
}
