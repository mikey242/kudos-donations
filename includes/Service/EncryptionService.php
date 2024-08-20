<?php
/**
 * Authentication based helper.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 * @see https://permanenttourist.ch/2023/03/storing-credentials-securely-in-wordpress-plugin-settings/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Service;

use Exception;
use IseardMedia\Kudos\Container\AbstractRegistrable;

class EncryptionService extends AbstractRegistrable {

	private string $key;
	private string $salt;

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		$this->key  = $this->get_key();
		$this->salt = $this->get_salt();
	}

	/**
	 * Gets the default encryption key, which should ideally be added to wp-config.php.
	 */
	private function get_key(): string {
		if ( \defined( 'KUDOS_AUTH_KEY' ) && '' !== KUDOS_AUTH_KEY ) {
			return KUDOS_AUTH_KEY;
		}

		if ( \defined( 'LOGGED_IN_KEY' ) && '' !== LOGGED_IN_KEY ) {
			return LOGGED_IN_KEY;
		}

		return 'this-is-not-a-secure-key';
	}

	/**
	 * Gets the salt, which should ideally be added to wp-config.php.
	 */
	private function get_salt(): string {
		if ( \defined( 'KUDOS_AUTH_SALT' ) && '' !== KUDOS_AUTH_SALT ) {
			return KUDOS_AUTH_SALT;
		}

		if ( \defined( 'LOGGED_IN_SALT' ) && '' !== LOGGED_IN_SALT ) {
			return LOGGED_IN_SALT;
		}

		return 'this-is-not-a-secure-salt';
	}

	/**
	 * Encrypt provided password.
	 *
	 * @throws Exception Thrown if error getting random_bytes.
	 *
	 * @param string $password Password.
	 * @return string|false
	 */
	public function encrypt_password( string $password ) {
		if ( ! \extension_loaded( 'openssl' ) ) {
			return $password;
		}

		$method = 'aes-256-ctr';
		$iv_len = openssl_cipher_iv_length( $method );
		$iv     = openssl_random_pseudo_bytes( $iv_len );

		$raw_value = openssl_encrypt( $password . $this->salt, $method, $this->key, 0, $iv );

		if ( ! $raw_value ) {
			return false;
		}
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		return base64_encode( $iv . $raw_value );
	}

	/**
	 * Decrypt encrypted password.
	 *
	 * @param string $raw_value The encrypted password.
	 * @return string|false
	 */
	public function decrypt_password( string $raw_value ) {
		if ( ! \extension_loaded( 'openssl' ) ) {
			return $raw_value;
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$raw_value = base64_decode( $raw_value, true );

		$method = 'aes-256-ctr';
		$iv_len = openssl_cipher_iv_length( $method );
		$iv     = substr( $raw_value, 0, $iv_len );

		$raw_value = substr( $raw_value, $iv_len );

		$value = openssl_decrypt( $raw_value, $method, $this->key, 0, $iv );
		if ( ! $value || substr( $value, -\strlen( $this->salt ) ) !== $this->salt ) {
			return false;
		}

		return substr( $value, 0, -\strlen( $this->salt ) );
	}
}
