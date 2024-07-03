<?php
/**
 * Creates an admin notice.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin\Notice;

/**
 * Class AdminNotice
 */
interface AdminNoticeInterface {

	public const ERROR   = 'notice-error';
	public const WARNING = 'notice-warning';
	public const SUCCESS = 'notice-success';
	public const INFO    = 'notice-info';

	/**
	 * Whether alert is dismissible or not.
	 */
	public static function is_dismissible(): bool;

	/**
	 * Error notice.
	 *
	 * @param string $message Message to be displayed.
	 */
	public static function error( string $message ): void;

	/**
	 * Warning notice.
	 *
	 * @param string $message Message to be displayed.
	 */
	public static function warning( string $message ): void;

	/**
	 * Success notice.
	 *
	 * @param string $message Message to be displayed.
	 */
	public static function success( string $message ): void;

	/**
	 * Info notice.
	 *
	 * @param string $message Message to be displayed.
	 */
	public static function info( string $message ): void;
}
