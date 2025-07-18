<?php
/**
 * Notice service.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Service;

use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Utils;

class NoticeService implements HasSettingsInterface {
	public const SETTING_ADMIN_NOTICES = '_kudos_admin_notices';
	public const SUCCESS               = 'notice-success';
	public const ERROR                 = 'notice-error';
	public const INFO                  = 'notice-info';
	public const WARNING               = 'notice-warning';

	/**
	 * Add a new notice.
	 *
	 * @param string $message The notice message.
	 * @param string $level The level (info, success, warning, error).
	 * @param bool   $dismissible Whether the notice can be dismissed.
	 * @param string $key Optional key to uniquely identify the notice.
	 * @param bool   $logo Whether to include the logo or not.
	 */
	public static function add_notice( string $message, string $level = self::INFO, bool $dismissible = true, string $key = '', bool $logo = true ): void {
		$notices = get_option( self::SETTING_ADMIN_NOTICES, [] );

		// Use a key if provided, otherwise generate a unique key.
		if ( ! $key ) {
			$key = wp_generate_uuid4();
		}

		// Add the notice.
		$notices[ $key ] = [
			'message'     => $message,
			'level'       => $level,
			'dismissible' => $dismissible,
			'logo'        => $logo,
		];

		update_option( self::SETTING_ADMIN_NOTICES, $notices );
	}

	/**
	 * Retrieve all notices.
	 *
	 * @return array The list of notices.
	 */
	public static function get_notices(): array {
		$notices = get_option( self::SETTING_ADMIN_NOTICES, [] );
		return \is_array( $notices ) ? $notices : [];
	}

	/**
	 * Dismiss a notice by key.
	 *
	 * @param string $key The key of the notice to dismiss.
	 */
	public static function dismiss_notice( string $key ): bool {
		$notices = get_option( self::SETTING_ADMIN_NOTICES, [] );

		if ( isset( $notices[ $key ] ) ) {
			unset( $notices[ $key ] );
			return update_option( self::SETTING_ADMIN_NOTICES, $notices );
		}
		return false;
	}

	/**
	 * Sets up the message and adds the hook.
	 *
	 * @param string  $message The message content.
	 * @param string  $level The message level.
	 * @param bool    $dismissible Whether the notice can be dismissed by the user or not.
	 * @param ?string $key Unique key for each notice.
	 * @param bool    $logo Whether to include the logo or not.
	 */
	public static function notice( string $message, string $level = self::INFO, bool $dismissible = false, ?string $key = null, bool $logo = true ): void {
		if ( $logo ) {
			$message = "<div class='logo' style='width: 50px; margin-right: 20px'>" . Utils::get_kudos_logo_svg() . "</div><div class='message'>" . $message . '</div>';
		}
		if ( \is_null( $key ) ) {
			$key = wp_generate_uuid4();
		}
		add_action(
			'admin_notices',
			function () use ( $key, $level, $message, $dismissible ) {
				NoticeService::render( $key, $level, $message, $dismissible );
			}
		);
	}

	/**
	 * Generates the notice markup.
	 *
	 * @param string $key Unique key for each notice.
	 * @param string $level The alert level.
	 * @param string $message The message to display.
	 * @param bool   $dismissible Whether the notice can be dismissed by the user or not.
	 */
	private static function render( string $key, string $level, string $message, bool $dismissible = true ): void {
		printf(
			'<div class="kudos-notice notice %s %s" data-notice-key="%s" style="display: flex; padding: 10px; align-items: center">%s</div>',
			esc_attr( $level ),
			esc_html( $dismissible ? 'is-dismissible' : '' ),
			esc_attr( $key ),
			wp_kses(
				$message,
				self::get_allowed_tags()
			)
		);
	}

	/**
	 * Returns the tags allowed within the notice message.
	 */
	private static function get_allowed_tags(): array {
		return [
			'div'    => [
				'id'    => [],
				'class' => [],
				'style' => [],
			],
			'p'      => [
				'id'    => [],
				'style' => [],
			],
			'i'      => [
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
			'br'     => [],
		];
	}

	/**
	 * Returns all settings in array.
	 */
	public static function get_settings(): array {
		return [
			self::SETTING_ADMIN_NOTICES => [
				'type'         => FieldType::ARRAY,
				'show_in_rest' => false,
				'default'      => [],
			],
		];
	}
}
