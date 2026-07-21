<?php
/**
 * Interface for specifying that the target class requires the encryption service.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Container;

use IseardMedia\Kudos\Notice\Notice;
use IseardMedia\Kudos\Notice\NoticeManager;
use IseardMedia\Kudos\Service\EncryptionService;

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
			throw new \LogicException( 'Encryption service was not set in: ' . static::class );
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
		if ( '1' === $value || $num_asterisks === $count ) {
			return $value;
		}

		$encrypted_key = $this->get_encryption()->encrypt( $value );
		$result        = update_option( $encrypted_option, $encrypted_key );
		if ( $result && \is_callable( $callback ) ) {
			\call_user_func( $callback );
		}

		return '1';
	}

	/**
	 * Decrypt the stored API key.
	 *
	 * @param string $encrypted_option The option to decrypt.
	 * @param string $settings_url     Optional URL to the relevant settings page, included in the error notice.
	 */
	protected function get_decrypted_key( string $encrypted_option, string $settings_url = '' ): string {
		$encrypted_key = get_option( $encrypted_option, '' );
		$decrypted     = $this->get_encryption()->decrypt( $encrypted_key );

		// If salt check failed or decryption failed.
		if ( false === $decrypted ) {
			$message = $settings_url
				? \sprintf(
					/* translators: %1$s: Name of setting being decrypted. %2$s: URL to settings page. */
					__( 'Error decrypting "%1$s". Please <a href="%2$s">reset it</a> and add it again.', 'kudos-donations' ),
					$encrypted_option,
					$settings_url
				)
				: \sprintf(
				/* translators: %s: Name of setting being decrypted. */
					__( 'Error decrypting "%s". Please reset it and add it again.', 'kudos-donations' ),
					$encrypted_option
				);
			NoticeManager::add_notice( new Notice( $encrypted_option, $message, Notice::ERROR, true ) );
			return '';
		}

		return $decrypted;
	}
}
