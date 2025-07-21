<?php
/**
 * Encryption service.
 *
 * @link https://github.com/mikey242/kudos-donations/
 * @see https://permanenttourist.ch/2023/03/storing-credentials-securely-in-wordpress-plugin-settings/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Service;

use Exception;
use IseardMedia\Kudos\Container\AbstractRegistrable;

class EncryptionService extends AbstractRegistrable {

	private const CIPHER  = 'aes-256-ctr';
	private const VERSION = 'k1';
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
	 * {@inheritDoc}
	 */
	public static function get_registration_action_priority(): int {
		return 5;
	}

	/**
	 * Verifies the provided token against the entity id.
	 *
	 * @throws Exception If invalid entity_id supplied.
	 *
	 * @param int    $entity_id The ID of the entity.
	 * @param string $token The token.
	 */
	public static function verify_token( int $entity_id, string $token ): bool {
		return hash_equals(
			self::generate_token( $entity_id ),
			$token
		);
	}

	/**
	 * Generates a unique token based on the entity id.
	 *
	 * @throws Exception If entity ID invalid.
	 *
	 * @param int $entity_id The entity id to be hashed.
	 */
	public static function generate_token( int $entity_id ): ?string {
		if ( $entity_id <= 0 ) {
			throw new Exception( \sprintf( 'Invalid entity ID supplied to generate_token: %s', (int) $entity_id ) );
		}

		return wp_hash( (string) $entity_id, 'auth', 'sha256' );
	}

	/**
	 * Gets the default encryption key, which should ideally be added to wp-config.php.
	 */
	private function get_key(): string {
		if ( \defined( 'AUTH_KEY' ) && '' !== AUTH_KEY ) {
			return AUTH_KEY;
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
		if ( \defined( 'AUTH_SALT' ) && '' !== AUTH_SALT ) {
			return AUTH_SALT;
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
	public function encrypt( string $password ) {
		if ( ! \extension_loaded( 'openssl' ) ) {
			$this->logger->error( 'Tried to encrypt password before openssl was loaded.' );
			return $password;
		}

		$iv_len = openssl_cipher_iv_length( self::CIPHER );
		$iv     = openssl_random_pseudo_bytes( $iv_len );

		$encrypted_value = openssl_encrypt( $password . $this->salt, self::CIPHER, $this->key, 0, $iv );

		if ( ! $encrypted_value ) {
			$this->logger->error( 'Error encrypting password.' );
			return false;
		}

		$mac = hash_hmac( 'sha256', $iv . $encrypted_value, $this->key, true );
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		return base64_encode( self::VERSION . '|' . $iv . $mac . $encrypted_value );
	}

	/**
	 * Decrypt encrypted password.
	 *
	 * @param string|array $raw_value The encrypted password.
	 * @return string|array|false Returns string if parameter is string and array if parameter is array.
	 */
	public function decrypt( $raw_value ) {
		// No raw value.
		if ( ! $raw_value ) {
			return '';
		}

		if ( ! \extension_loaded( 'openssl' ) ) {
			$this->logger->error( 'Tried to decrypt password before openssl was loaded.' );
			return $raw_value;
		}

		// If parameter is array, rerun with string as parameter.
		if ( \is_array( $raw_value ) ) {
			return array_map(
				fn( $value ) => $this->decrypt( $value ),
				$raw_value
			);
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$raw_value = base64_decode( $raw_value, true );

		// Decoding base64 returned false, something went wrong.
		if ( ! $raw_value ) {
			$this->logger->error( 'Error decrypting. Unable to decode base64 string.' );
			return false;
		}

		$iv_len = openssl_cipher_iv_length( self::CIPHER );

		if ( strpos( $raw_value, 'k1|' ) === 0 ) {
			$decoded    = substr( $raw_value, 3 ); // Remove version tag.
			$mac_len    = 32; // SHA256 HMAC, 256 bits = 32 bytes.
			$iv         = substr( $decoded, 0, $iv_len );
			$mac        = substr( $decoded, $iv_len, $mac_len );
			$ciphertext = substr( $decoded, $iv_len + $mac_len );

			$calc_mac = hash_hmac( 'sha256', $iv . $ciphertext, $this->key, true );
			if ( ! hash_equals( $mac, $calc_mac ) ) {
				$this->logger->error( 'HMAC verification failed.' );
				return false;
			}
		} else {
			$decoded    = $raw_value;
			$iv         = substr( $decoded, 0, $iv_len );
			$ciphertext = substr( $decoded, $iv_len );
		}

		$value = openssl_decrypt( $ciphertext, self::CIPHER, $this->key, 0, $iv );
		if ( ! $value || substr( $value, -\strlen( $this->salt ) ) !== $this->salt ) {
			$this->logger->error( 'Error decrypting. Salts do not match.' );
			return false;
		}

		return substr( $value, 0, -\strlen( $this->salt ) );
	}
}
