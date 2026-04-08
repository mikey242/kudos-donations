<?php
/**
 * Utils tests.
 */

namespace IseardMedia\Kudos\Tests\Helper;

use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Tests\BaseTestCase;

/**
 * @covers \IseardMedia\Kudos\Helper\Utils
 */
class UtilsTest extends BaseTestCase {

	/**
	 * Test that zero years returns null.
	 */
	public function test_get_times_from_years_returns_null_for_zero(): void {
		$this->assertNull( Utils::get_times_from_years( 0, '1 month' ) );
	}

	/**
	 * Test that negative years returns null.
	 */
	public function test_get_times_from_years_returns_null_for_negative(): void {
		$this->assertNull( Utils::get_times_from_years( -1, '1 month' ) );
	}

	/**
	 * Test 1 year monthly = 11 payments (12 - 1, because the first is the mandate payment).
	 */
	public function test_get_times_from_years_monthly_one_year(): void {
		$this->assertSame( 11, Utils::get_times_from_years( 1, '1 month' ) );
	}

	/**
	 * Test 2 years monthly = 23 payments.
	 */
	public function test_get_times_from_years_monthly_two_years(): void {
		$this->assertSame( 23, Utils::get_times_from_years( 2, '1 month' ) );
	}

	/**
	 * Test 1 year quarterly (every 3 months) = 3 payments.
	 */
	public function test_get_times_from_years_quarterly_one_year(): void {
		$this->assertSame( 3, Utils::get_times_from_years( 1, '3 months' ) );
	}

	/**
	 * Test 1 year yearly (every 12 months) = 0 payments after the first.
	 */
	public function test_get_times_from_years_yearly_one_year(): void {
		$this->assertSame( 0, Utils::get_times_from_years( 1, '12 months' ) );
	}
}
