<?php
/**
 * Authentication based helper.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 * @see https://permanenttourist.ch/2023/03/storing-credentials-securely-in-wordpress-plugin-settings/
 *
 * @copyright 2024 Iseard Media
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
	 * Verifies the provided token against the post id.
	 *
	 * @throws Exception If invalid post_id supplied.
	 *
	 * @param int    $post_id The ID of the post.
	 * @param string $token The token.
	 */
	public static function verify_token( int $post_id, string $token ): bool {
		return hash_equals(
			self::generate_token( $post_id ),
			$token
		);
	}

	/**
	 * Generates a unique token based on the post id.
	 *
	 * @throws Exception If post ID invalid.
	 *
	 * @param int $post_id The post id to be hashed.
	 */
	public static function generate_token( int $post_id ): ?string {
		if ( ! is_numeric( $post_id ) || $post_id <= 0 ) {
			throw new Exception( wp_sprintf( 'Invalid post ID supplied to generate_token: %s', (int) $post_id ) );
		}

		return hash_hmac( 'sha256', (string) $post_id, KUDOS_SALT );
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
	 * @param string $password Password.
	 * @return string|false
	 */
	public function encrypt_password( string $password ) {
		if ( ! \extension_loaded( 'openssl' ) ) {
			$this->logger->error( 'Tried to encrypt password before openssl was loaded.' );
			return $password;
		}

		$method = 'aes-256-ctr';
		$iv_len = openssl_cipher_iv_length( $method );
		$iv     = openssl_random_pseudo_bytes( $iv_len );

		$encrypted_value = openssl_encrypt( $password . $this->salt, $method, $this->key, 0, $iv );

		if ( ! $encrypted_value ) {
			$this->logger->error( 'Error encrypting password.' );
			return false;
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		return base64_encode( $iv . $encrypted_value );
	}

	/**
	 * Decrypt encrypted password.
	 *
	 * @param string|array $raw_value The encrypted password.
	 * @return string|array|false Returns string if parameter is string and array if parameter is array.
	 */
	public function decrypt_password( $raw_value ) {

		// No raw value.
		if ( ! $raw_value ) {
			return false;
		}

		if ( ! \extension_loaded( 'openssl' ) ) {
			$this->logger->error( 'Tried to decrypt password before openssl was loaded.' );
			return $raw_value;
		}

		// If parameter is array, rerun with string as parameter.
		if ( \is_array( $raw_value ) ) {
			$result = [];
			foreach ( $raw_value as $key => $value ) {
				$result[ $key ] = $this->decrypt_password( $value );
			}
			return $result;
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$raw_value = base64_decode( $raw_value, true );

		// Decoding base64 returned false, something went wrong.
		if ( ! $raw_value ) {
			return false;
		}

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
