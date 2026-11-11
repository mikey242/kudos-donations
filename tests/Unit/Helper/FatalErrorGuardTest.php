<?php
/**
 * FatalErrorGuard tests.
 */

namespace IseardMedia\Kudos\Tests\Helper;

use IseardMedia\Kudos\Helper\FatalErrorGuard;
use IseardMedia\Kudos\Tests\BaseTestCase;

/**
 * @covers \IseardMedia\Kudos\Helper\FatalErrorGuard
 */
class FatalErrorGuardTest extends BaseTestCase {

	public function tear_down(): void {
		FatalErrorGuard::clear();
		parent::tear_down();
	}

	/**
	 * A fatal error in Kudos during startup switches Kudos off for this version.
	 */
	public function test_records_fatal_error_during_startup(): void {
		FatalErrorGuard::record( $this->error( E_ERROR, KUDOS_PLUGIN_DIR . 'includes/Plugin.php' ), true );

		$this->assertStringContainsString( 'Boom in ' . KUDOS_PLUGIN_DIR . 'includes/Plugin.php:12', (string) FatalErrorGuard::get_blocking_error() );
	}

	/**
	 * After startup, e.g. in a REST call or webhook, a fatal error affects only that request.
	 */
	public function test_ignores_fatal_error_after_startup(): void {
		FatalErrorGuard::record( $this->error( E_ERROR, KUDOS_PLUGIN_DIR . 'includes/Domain/Repository/TransactionRepository.php' ), false );

		$this->assertNull( FatalErrorGuard::get_blocking_error() );
	}

	public function test_ignores_errors_from_other_plugins_and_non_fatal_errors(): void {
		FatalErrorGuard::record( $this->error( E_ERROR, WP_PLUGIN_DIR . '/other/other.php' ), true );
		FatalErrorGuard::record( $this->error( E_WARNING, KUDOS_PLUGIN_DIR . 'includes/Plugin.php' ), true );
		FatalErrorGuard::record( null, true );

		$this->assertNull( FatalErrorGuard::get_blocking_error() );
	}

	/**
	 * An update may have fixed the error: one recorded by another version is cleared.
	 */
	public function test_error_from_another_version_is_cleared(): void {
		update_option(
			FatalErrorGuard::OPTION,
			[
				'message' => 'Boom',
				'version' => '0.0.1',
			]
		);

		$this->assertNull( FatalErrorGuard::get_blocking_error() );
		$this->assertFalse( get_option( FatalErrorGuard::OPTION ) );
	}

	/**
	 * Versions before 4.3.4 stored only the message, as a string.
	 */
	public function test_error_stored_by_older_versions_is_cleared(): void {
		update_option( FatalErrorGuard::OPTION, 'Boom in somewhere.php:1' );

		$this->assertNull( FatalErrorGuard::get_blocking_error() );
		$this->assertFalse( get_option( FatalErrorGuard::OPTION ) );
	}

	/**
	 * An error_get_last() result.
	 *
	 * @param int    $type Error type.
	 * @param string $file File the error happened in.
	 */
	private function error( int $type, string $file ): array {
		return [
			'type'    => $type,
			'message' => 'Boom',
			'file'    => $file,
			'line'    => 12,
		];
	}
}
