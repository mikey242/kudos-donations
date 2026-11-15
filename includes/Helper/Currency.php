<?php
/**
 * Currency helper.
 *
 * @link https://code.iseard.media/michael/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Helper;

class Currency {

	/**
	 * Currency used when the site's locale doesn't point to a supported one. Most Kudos sites
	 * are in the eurozone, so euro-using countries are not listed below.
	 */
	public const FALLBACK = 'EUR';

	/**
	 * Countries with a non-euro currency, keyed by ISO 3166 country code.
	 */
	private const COUNTRY_CURRENCIES = [
		'AE' => 'AED',
		'AU' => 'AUD',
		'BG' => 'BGN',
		'BR' => 'BRL',
		'CA' => 'CAD',
		'CH' => 'CHF',
		'CZ' => 'CZK',
		'DK' => 'DKK',
		'GB' => 'GBP',
		'HK' => 'HKD',
		'HU' => 'HUF',
		'IL' => 'ILS',
		'IS' => 'ISK',
		'JP' => 'JPY',
		'MX' => 'MXN',
		'MY' => 'MYR',
		'NO' => 'NOK',
		'NZ' => 'NZD',
		'PH' => 'PHP',
		'PL' => 'PLN',
		'RO' => 'RON',
		'RU' => 'RUB',
		'SE' => 'SEK',
		'SG' => 'SGD',
		'TH' => 'THB',
		'TW' => 'TWD',
		'US' => 'USD',
		'ZA' => 'ZAR',
	];

	/**
	 * WordPress locales that carry no country code, mapped to the country they imply.
	 */
	private const LANGUAGE_COUNTRIES = [
		'ja' => 'JP',
		'th' => 'TH',
		'en' => 'US',
	];

	/**
	 * Returns the supported currencies and their symbols, keyed by ISO 4217 code.
	 *
	 * @return array<string, string>
	 */
	public static function get_currencies(): array {
		return apply_filters(
			'kudos_get_currencies',
			[
				'AED' => 'د.إ',
				'AUD' => '$',
				'BGN' => 'лв',
				'BRL' => 'R$',
				'CAD' => '$',
				'CHF' => 'CHF',
				'CZK' => 'Kč',
				'DKK' => 'kr',
				'EUR' => '€',
				'GBP' => '£',
				'HKD' => '$',
				'HUF' => 'Ft',
				'ILS' => '₪',
				'ISK' => 'kr',
				'JPY' => '¥',
				'MXN' => '$',
				'MYR' => 'RM',
				'NOK' => 'kr',
				'NZD' => '$',
				'PHP' => '₱',
				'PLN' => 'zł',
				'RON' => 'lei',
				'RUB' => '₽',
				'SEK' => 'kr',
				'SGD' => '$',
				'THB' => '฿',
				'TWD' => 'NT$',
				'USD' => '$',
				'ZAR' => 'R',
			]
		);
	}

	/**
	 * Returns the default currency for new campaigns, derived from where the site is.
	 *
	 * The timezone is the better signal: it says where the site is, while the locale only says
	 * which language the admin is in (plenty of Dutch sites run in English). It's only usable
	 * when set to a city, though; a plain "UTC+1" offset carries no country, so the locale is
	 * the fallback. Only currencies Kudos supports are returned.
	 *
	 * @param string|null $locale   A WordPress locale, or null for the site's locale.
	 * @param string|null $timezone A timezone identifier, or null for the site's setting.
	 */
	public static function get_default( ?string $locale = null, ?string $timezone = null ): string {
		$locale   = $locale ?? get_locale();
		$timezone = $timezone ?? (string) get_option( 'timezone_string', '' );
		$country  = self::country_from_timezone( $timezone ) ?? self::country_from_locale( $locale );
		$currency = self::COUNTRY_CURRENCIES[ $country ] ?? self::FALLBACK;

		if ( ! \array_key_exists( $currency, self::get_currencies() ) ) {
			$currency = self::FALLBACK;
		}

		/**
		 * Filters the default currency for new campaigns.
		 *
		 * @param string $currency The ISO 4217 currency code.
		 * @param string $locale   The site's locale.
		 * @param string $timezone The site's timezone identifier, empty when set as an offset.
		 */
		return (string) apply_filters( 'kudos_default_currency', $currency, $locale, $timezone );
	}

	/**
	 * Returns the country a timezone identifier (e.g. "Europe/Amsterdam") belongs to, or null
	 * when it has none, such as "UTC" or an unrecognised identifier.
	 *
	 * @param string $timezone A timezone identifier.
	 */
	private static function country_from_timezone( string $timezone ): ?string {
		if ( '' === $timezone ) {
			return null;
		}

		try {
			$location = ( new \DateTimeZone( $timezone ) )->getLocation();
		} catch ( \Exception $e ) {
			return null;
		}

		$country = \is_array( $location ) ? $location['country_code'] : '';
		return preg_match( '/^[A-Z]{2}$/', $country ) ? $country : null;
	}

	/**
	 * Returns the country in a WordPress locale. Locales are "language_COUNTRY" with an
	 * optional variant (e.g. "de_CH_informal"), so the country is the second segment.
	 *
	 * @param string $locale A WordPress locale.
	 */
	private static function country_from_locale( string $locale ): string {
		$parts = explode( '_', $locale );
		return isset( $parts[1] )
			? strtoupper( $parts[1] )
			: ( self::LANGUAGE_COUNTRIES[ strtolower( $parts[0] ) ] ?? '' );
	}
}
