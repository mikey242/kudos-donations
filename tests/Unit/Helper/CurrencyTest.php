<?php
/**
 * Currency tests.
 */

namespace IseardMedia\Kudos\Tests\Helper;

use IseardMedia\Kudos\Helper\Currency;
use IseardMedia\Kudos\Tests\BaseTestCase;

/**
 * @covers \IseardMedia\Kudos\Helper\Currency
 */
class CurrencyTest extends BaseTestCase {

	/**
	 * @dataProvider locale_provider
	 */
	public function test_get_default_maps_locale_to_currency( string $locale, string $expected ): void {
		// No timezone, so only the locale counts.
		$this->assertSame( $expected, Currency::get_default( $locale, '' ) );
	}

	public function locale_provider(): array {
		return [
			'eurozone'              => [ 'nl_NL', 'EUR' ],
			'eurozone variant'      => [ 'de_DE_formal', 'EUR' ],
			'United States'         => [ 'en_US', 'USD' ],
			'United Kingdom'        => [ 'en_GB', 'GBP' ],
			'Switzerland variant'   => [ 'de_CH_informal', 'CHF' ],
			'French Canada'         => [ 'fr_CA', 'CAD' ],
			'language-only locale'  => [ 'ja', 'JPY' ],
			'unknown language-only' => [ 'eo', 'EUR' ],
			'unsupported currency'  => [ 'es_AR', 'EUR' ],
		];
	}

	public function test_get_default_falls_back_when_currency_filtered_out(): void {
		$filter = static function ( array $currencies ): array {
			unset( $currencies['GBP'] );
			return $currencies;
		};
		add_filter( 'kudos_get_currencies', $filter );

		$this->assertSame( 'EUR', Currency::get_default( 'en_GB', '' ) );

		remove_filter( 'kudos_get_currencies', $filter );
	}

	public function test_get_default_is_filterable(): void {
		$filter = static fn() => 'SEK';
		add_filter( 'kudos_default_currency', $filter );

		$this->assertSame( 'SEK', Currency::get_default( 'en_US', '' ) );

		remove_filter( 'kudos_default_currency', $filter );
	}

	/**
	 * @dataProvider timezone_provider
	 */
	public function test_get_default_prefers_timezone_over_locale( string $locale, string $timezone, string $expected ): void {
		$this->assertSame( $expected, Currency::get_default( $locale, $timezone ) );
	}

	public function timezone_provider(): array {
		return [
			'English admin in the Netherlands' => [ 'en_US', 'Europe/Amsterdam', 'EUR' ],
			'Dutch admin in the UK'            => [ 'nl_NL', 'Europe/London', 'GBP' ],
			'English admin in New Zealand'     => [ 'en_US', 'Pacific/Auckland', 'NZD' ],
			'UTC has no country'               => [ 'en_GB', 'UTC', 'GBP' ],
			'invalid timezone is ignored'      => [ 'en_GB', 'Not/AZone', 'GBP' ],
		];
	}

	public function test_get_default_reads_site_timezone(): void {
		update_option( 'timezone_string', 'Europe/Amsterdam' );

		$this->assertSame( 'EUR', Currency::get_default( 'en_US' ) );
	}
}
