<?php
/**
 * EncryptionService tests.
 */

namespace IseardMedia\Kudos\Tests\Service;

use IseardMedia\Kudos\Service\EncryptionService;
use IseardMedia\Kudos\Tests\BaseTestCase;

/**
 * @covers \IseardMedia\Kudos\Service\EncryptionService
 */
class EncryptionServiceTest extends BaseTestCase {

	private EncryptionService $service;

	public function set_up(): void {
		parent::set_up();
		$this->service = new EncryptionService();
	}

	/**
	 * The key and salt are resolved lazily, so encrypt/decrypt must work on a freshly
	 * constructed instance even when register() has not run yet. This is the case during
	 * container bootstrap, where a provider may be set up before the `init` hook fires.
	 */
	public function test_round_trips_without_register(): void {
		$plaintext = 'sk_test_super_secret_key';

		$encrypted = $this->service->encrypt( $plaintext );

		$this->assertNotFalse( $encrypted );
		$this->assertNotSame( $plaintext, $encrypted );
		$this->assertSame( $plaintext, $this->service->decrypt( $encrypted ) );
	}

	/**
	 * Calling register() after values have already been used must not reset or change them.
	 */
	public function test_register_is_idempotent_after_lazy_init(): void {
		$plaintext = 'rk_live_another_secret';
		$encrypted = $this->service->encrypt( $plaintext );

		$this->service->register();

		$this->assertSame( $plaintext, $this->service->decrypt( $encrypted ) );
	}
}