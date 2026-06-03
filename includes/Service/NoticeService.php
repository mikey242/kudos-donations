<?php
/**
 * Notice service.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
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
	 * Unified in-memory notice store for this request.
	 *
	 * @var array<string, array{message: string, level: string, dismissible: bool, logo: bool, kudos_only: bool}>
	 */
	private static array $notices = [];

	/**
	 * Load persisted notices from the DB and register the admin_notices render hook.
	 * Must be called early (e.g. on plugins_loaded) so notices are available to both
	 * REST endpoints and admin pages.
	 */
	public static function init(): void {
		$stored = get_option( self::SETTING_ADMIN_NOTICES, [] );
		if ( \is_array( $stored ) ) {
			foreach ( $stored as $key => $data ) {
				self::$notices[ $key ] = $data;
			}
		}
		add_action( 'admin_notices', [ self::class, 'render_all' ] );
	}

	/**
	 * Add a runtime notice (current request only, not persisted).
	 *
	 * Use when the condition is always true while it exists — e.g. test mode active,
	 * API keys missing, migration needed. The notice is regenerated every request.
	 *
	 * @param string  $message     The notice message.
	 * @param string  $level       The level (info, success, warning, error).
	 * @param bool    $dismissible Whether the notice can be dismissed.
	 * @param ?string $key         Optional key to identify the notice (allows overwriting).
	 * @param bool    $logo        Whether to include the Kudos logo.
	 * @param bool    $kudos_only  Whether to only show on Kudos Donations pages.
	 */
	public static function notice( string $message, string $level = self::INFO, bool $dismissible = false, ?string $key = null, bool $logo = true, bool $kudos_only = false ): void {
		if ( \is_null( $key ) ) {
			$key = wp_generate_uuid4();
		}
		self::$notices[ $key ] = compact( 'message', 'level', 'dismissible', 'logo', 'kudos_only' );
	}

	/**
	 * Add a persisted notice (stored in DB, survives page reloads until dismissed).
	 *
	 * Use when the notice is triggered by a one-off event — e.g. a webhook registration
	 * failure, a decryption error — where the condition won't recur on every request.
	 *
	 * @param string $message     The notice message.
	 * @param string $level       The level (info, success, warning, error).
	 * @param bool   $dismissible Whether the notice can be dismissed.
	 * @param string $key         Optional key to identify the notice (allows overwriting).
	 * @param bool   $logo        Whether to include the Kudos logo.
	 * @param bool   $kudos_only  Whether to only show on Kudos Donations pages.
	 */
	public static function add_notice( string $message, string $level = self::INFO, bool $dismissible = true, string $key = '', bool $logo = true, bool $kudos_only = false ): void {
		if ( ! $key ) {
			$key = wp_generate_uuid4();
		}
		$data                  = compact( 'message', 'level', 'dismissible', 'logo', 'kudos_only' );
		self::$notices[ $key ] = $data;

		$stored         = get_option( self::SETTING_ADMIN_NOTICES, [] );
		$stored[ $key ] = $data;
		update_option( self::SETTING_ADMIN_NOTICES, $stored );
	}

	/**
	 * Dismiss a notice by key. Removes from memory and DB (idempotent).
	 *
	 * @param string $key The notice key.
	 */
	public static function dismiss_notice( string $key ): void {
		unset( self::$notices[ $key ] );

		$stored = get_option( self::SETTING_ADMIN_NOTICES, [] );
		if ( isset( $stored[ $key ] ) ) {
			unset( $stored[ $key ] );
			update_option( self::SETTING_ADMIN_NOTICES, $stored );
		}
	}

	/**
	 * Returns all current notices formatted for the REST API / frontend.
	 *
	 * @return list<array{id: string, status: string, content: string, isDismissible: bool, type: string}>
	 */
	public static function get_formatted_notices(): array {
		$formatted = [];
		foreach ( self::$notices as $key => $notice ) {
			$formatted[] = [
				'id'            => $key,
				'status'        => substr( $notice['level'], strpos( $notice['level'], '-' ) + 1 ),
				'content'       => $notice['message'],
				'isDismissible' => $notice['dismissible'],
				'type'          => 'default',
			];
		}
		return $formatted;
	}

	/**
	 * Renders all non-kudos-only notices via the admin_notices hook.
	 */
	public static function render_all(): void {
		$renderable = array_filter(
			self::$notices,
			static fn( $n ) => ! $n['kudos_only']
		);

		if ( ! $renderable ) {
			return;
		}

		self::enqueue_dismiss_script();

		foreach ( $renderable as $key => $notice ) {
			$message = $notice['logo']
				? "<div class='logo' style='width: 40px; margin-right: 20px'>" . Utils::get_kudos_logo_svg() . "</div><div class='message'>" . $notice['message'] . '</div>'
				: $notice['message'];

			self::render( $key, $notice['level'], $message, $notice['dismissible'] );
		}
	}

	/**
	 * Outputs the inline script that wires up dismiss clicks on non-React admin pages.
	 */
	private static function enqueue_dismiss_script(): void {
		add_action(
			'admin_print_footer_scripts',
			static function () {
				echo '<script type="text/javascript">
					document.querySelectorAll(".kudos-notice").forEach(function(notice) {
						notice.addEventListener("click", function() {
							fetch("' . esc_url( rest_url( '/kudos/v1/notice/dismiss' ) ) . '", {
								method: "POST",
								headers: {
									"Content-Type": "application/json",
									"X-WP-Nonce": "' . esc_attr( wp_create_nonce( 'wp_rest' ) ) . '"
								},
								body: JSON.stringify({ id: notice.dataset.noticeKey })
							});
						});
					});
				</script>';
			}
		);
	}

	/**
	 * Generates the notice markup.
	 *
	 * @param string $key         Unique key for each notice.
	 * @param string $level       The alert level.
	 * @param string $message     The message to display.
	 * @param bool   $dismissible Whether the notice can be dismissed.
	 */
	private static function render( string $key, string $level, string $message, bool $dismissible = true ): void {
		printf(
			'<div class="kudos-notice notice %s %s" data-notice-key="%s" style="display: flex; padding: 10px; align-items: center">%s</div>',
			esc_attr( $level ),
			esc_attr( $dismissible ? 'is-dismissible' : '' ),
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
			'button' => [
				'id'       => [],
				'class'    => [],
				'name'     => [],
				'type'     => [],
				'value'    => [],
				'disabled' => [],
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
			'a'      => [
				'href'  => [],
				'class' => [],
			],
		];
	}

	/**
	 * Resets all in-memory notices. Intended for use in tests.
	 */
	public static function reset(): void {
		self::$notices = [];
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
