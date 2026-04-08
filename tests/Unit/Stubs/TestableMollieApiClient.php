<?php
/**
 * Stub for MollieApiClient that declares the magic properties used in tests.
 *
 * MollieApiClient uses __get() for endpoint properties, which causes PHP 8.2+
 * dynamic property deprecation warnings when mocked. This stub declares the
 * properties explicitly so PHPUnit mocks work without deprecation warnings.
 */

namespace IseardMedia\Kudos\Tests\Stubs;

use IseardMedia\Kudos\ThirdParty\Mollie\Api\MollieApiClient;

class TestableMollieApiClient extends MollieApiClient {

	/** @var object|null */
	public $payments;

	/** @var object|null */
	public $customers;
}
