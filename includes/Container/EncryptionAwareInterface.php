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

interface EncryptionAwareInterface {

	/**
	 * Set the encryption service.
	 *
	 * @param EncryptionService $encryption The encryption service.
	 */
	public function set_encryption( EncryptionService $encryption ): void;
}
