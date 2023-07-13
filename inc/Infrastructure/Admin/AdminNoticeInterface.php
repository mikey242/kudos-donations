<?php
/**
 * Creates an admin notice.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Infrastructure\Admin;

/**
 * Class AdminNotice
 */
interface AdminNoticeInterface {

	public const ERROR   = 'notice-error';
	public const WARNING = 'notice-warning';
	public const SUCCESS = 'notice-success';
	public const INFO    = 'notice-info';

	/**
	 * Error notice.
	 *
	 * @param string $message Message to be displayed.
	 */
	public function error( string $message ): void;

	/**
	 * Warning notice.
	 *
	 * @param string $message Message to be displayed.
	 */
	public function warning( string $message ): void;

	/**
	 * Success notice.
	 *
	 * @param string $message Message to be displayed.
	 */
	public function success( string $message ): void;

	/**
	 * Info notice.
	 *
	 * @param string $message Message to be displayed.
	 */
	public function info( string $message ): void;

	/**
	 * Sets up the message and adds the hook.
	 *
	 * @param string $level The message level.
	 * @param string $message The message content.
	 */
	public function message( string $level, string $message ): void;

	/**
	 * Add the required hook to display the notice.
	 */
	public function hook(): void;

	/**
	 * Generates the notice markup.
	 */
	public function render(): void;
}
