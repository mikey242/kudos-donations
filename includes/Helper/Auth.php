<?php
/**
 * Authentication based helper.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Helper;

use Exception;
use SodiumException;

class Auth {

	/**
	 * Encrypt provided password.
	 *
	 * @throws Exception Thrown if error getting random_bytes.
	 *
	 * @param string $password Password.
	 */
	public static function encrypt_password( string $password ): string {
		$key        = self::get_sodium_key();
		$nonce      = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
		$ciphertext = sodium_crypto_secretbox( $password, $nonce, $key );
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		return base64_encode( $nonce . $ciphertext );
	}

	/**
	 * Decrypt encrypted password.
	 *
	 * @param string $encrypted_password The encrypted password.
	 * @return string|false
	 */
	public static function decrypt_password( string $encrypted_password ) {
		$key = self::get_sodium_key();
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$decoded    = base64_decode( $encrypted_password );
		$nonce      = substr( $decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
		$ciphertext = substr( $decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );

		if ( $ciphertext ) {
			try {
				return sodium_crypto_secretbox_open( $ciphertext, $nonce, $key );
			} catch ( SodiumException $e ) {
				return false;
			}
		}
		return false;
	}

	/**
	 * Returns a sodium compatible key.
	 *
	 * @return false|string
	 */
	private static function get_sodium_key() {
		$key = \defined( 'KUDOS_AUTH_KEY' ) ? KUDOS_AUTH_KEY : '';
		return substr( hash( 'sha256', $key, true ), 0, SODIUM_CRYPTO_SECRETBOX_KEYBYTES );
	}
}
