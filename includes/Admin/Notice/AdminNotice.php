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

use IseardMedia\Kudos\Helper\Utils;

/**
 * Class AdminNotice
 */
class AdminNotice implements AdminNoticeInterface {

	/**
	 * {@inheritDoc}
	 */
	public static function is_dismissible(): bool {
		return false;
	}

	/**
	 * Error notice.
	 *
	 * @param string $message Message to be displayed.
	 */
	public static function error( string $message ): void {
		static::notice( self::ERROR, $message );
	}

	/**
	 * Warning notice.
	 *
	 * @param string $message Message to be displayed.
	 */
	public static function warning( string $message ): void {
		static::notice( self::WARNING, $message );
	}

	/**
	 * Success notice.
	 *
	 * @param string $message Message to be displayed.
	 */
	public static function success( string $message ): void {
		static::notice( self::SUCCESS, $message );
	}

	/**
	 * Info notice.
	 *
	 * @param string $message Message to be displayed.
	 */
	public static function info( string $message ): void {
		static::notice( self::INFO, $message );
	}

	/**
	 * Sets up the message and adds the hook.
	 *
	 * @param string $message The message content.
	 * @param string $level The message level.
	 */
	public static function fancy( string $message, string $level = self::INFO ): void {
		static::notice(
			$level,
			"<div class='logo' style='width: 50px; margin-right: 20px'>" . Utils::get_kudos_logo_svg() . "</div><div class='message'>" . $message . '</div>'
		);
	}

	/**
	 * Sets up the message and adds the hook.
	 *
	 * @param string $level The message level.
	 * @param string $message The message content.
	 */
	protected static function notice( string $level, string $message ): void {
		add_action(
			'admin_notices',
			function () use ( $level, $message ) {
				static::render( $level, $message );
			}
		);
	}

	/**
	 * Generates the notice markup.
	 *
	 * @param string $level The alert level.
	 * @param string $message The message to display.
	 */
	protected static function render( string $level, string $message ): void {
		printf(
			'<div class="notice %s %s" style="display: flex; padding: 10px; align-items: center">%s</div>',
			esc_attr( $level ),
			esc_html( static::is_dismissible() ? 'is-dismissible' : '' ),
			wp_kses(
				$message,
				[
					'div'    => [
						'id'    => [],
						'class' => [],
						'style' => [],
					],
					'p'      => [
						'id' => [],
					],
					'strong' => [],
					'form'   => [
						'method' => [],
					],
					'input'  => [
						'id'    => [],
						'type'  => [],
						'name'  => [],
						'value' => [],
					],
					'button' => [
						'id'    => [],
						'class' => [],
						'name'  => [],
						'type'  => [],
						'value' => [],
					],
					'svg'    => [
						'class'   => [],
						'viewbox' => [],
						'xmlns'   => [],
					],
					'path'   => [
						'class' => [],
						'fill'  => [],
						'd'     => [],
					],
				]
			)
		);
	}
}
