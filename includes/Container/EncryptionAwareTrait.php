<?php
/**
 * Interface for specifying that the target class requires the encryption service.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Container;

use IseardMedia\Kudos\Service\EncryptionService;
use IseardMedia\Kudos\Service\NoticeService;

trait EncryptionAwareTrait {

	protected EncryptionService $encryption;

	/**
	 * Set the encryption service.
	 *
	 * @param EncryptionService $encryption The encryption service.
	 */
	public function set_encryption( EncryptionService $encryption ): void {
		$this->encryption = $encryption;
	}

	/**
	 * Returns the encryption service.
	 *
	 * @throws \LogicException If encryption service not set.
	 */
	protected function get_encryption(): EncryptionService {
		if ( ! isset( $this->encryption ) ) {
			throw new \LogicException( 'Encryption service was not set' );
		}
		return $this->encryption;
	}

	/**
	 * Encrypt and save an API key.
	 *
	 * @param ?string   $value The value to encrypt.
	 * @param string    $encrypted_option The option to save it under.
	 * @param ?callable $callback Callback to run on successful save.
	 */
	protected function save_encrypted_key( ?string $value, string $encrypted_option, ?callable $callback = null ): string {
		if ( ! $value ) {
			// Clear value.
			update_option( $encrypted_option, '' );
			return '';
		}

		$num_asterisks = substr_count( $value, '*' );
		$count         = \strlen( $value );
		if ( $num_asterisks !== $count ) {
			$encrypted_key = $this->get_encryption()->encrypt_password( $value );
			$stars         = str_repeat( '*', \strlen( $value ) );
			$result        = update_option( $encrypted_option, $encrypted_key );
			if ( $result && \is_callable( $callback ) ) {
				\call_user_func( $callback );
			}
			return $stars;
		}

		return $value;
	}

	/**
	 * Decrypt the stored API key.
	 *
	 * @param string $encrypted_option The option to decrypt.
	 */
	protected function get_decrypted_key( string $encrypted_option ): string {
		$encrypted_key = get_option( $encrypted_option, '' );
		$decrypted     = $this->get_encryption()->decrypt_password( $encrypted_key );

		// If salt check failed or decryption failed.
		if ( false === $decrypted ) {
			NoticeService::add_notice( "Error decrypting key '$encrypted_option'. Please reset the key and add it again.", NoticeService::ERROR, true, $encrypted_option );
			return '';
		}

		return $decrypted;
	}
}
