<?php
/**
 * Notice service.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Notice;

use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Utils;

class NoticeManager implements HasSettingsInterface {
	public const SETTING_ADMIN_NOTICES = '_kudos_admin_notices';

	/**
	 * Filter applied to the notice list whenever notices are consumed (an admin render or the REST
	 * endpoint).
	 */
	public const FILTER_NOTICES = 'kudos_notices';

	/**
	 * Unified in-memory notice store for this request.
	 *
	 * @var Notice[]
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
			foreach ( $stored as $data ) {
				if ( $data instanceof Notice ) {
					self::$notices[ $data->id ] = $data;
				}
			}
		}

		add_action( 'admin_notices', [ self::class, 'render_admin_notices' ] );
	}

	/**
	 * Add a runtime notice (current request only, not persisted).
	 *
	 * @param Notice $notice The notice object.
	 */
	public static function notice( Notice $notice ): void {
		if ( ! $notice->id ) {
			$notice->id = wp_generate_uuid4();
		}
		self::$notices[ $notice->id ] = $notice;
	}

	/**
	 * Add a persisted notice (stored in DB, survives page reloads until dismissed).
	 *
	 * Use when the notice is triggered by a one-off event — e.g. a webhook registration
	 * failure, a decryption error — where the condition won't recur on every request.
	 *
	 * @param Notice $notice The notice object.
	 */
	public static function add_notice( Notice $notice ): void {
		if ( ! $notice->id ) {
			$notice->id = wp_generate_uuid4();
		}

		self::$notices[ $notice->id ] = $notice;

		$stored                = get_option( self::SETTING_ADMIN_NOTICES, [] );
		$stored[ $notice->id ] = $notice;
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
	 * Returns the in-memory notices addressable to the given channel — those whose context is
	 * Notice::BOTH, plus those matching the channel exactly.
	 *
	 * @param string $context The target channel: Notice::APP (REST) or Notice::ADMIN (native).
	 * @return Notice[]
	 */
	private static function get_notices( string $context ): array {
		$notices = apply_filters( self::FILTER_NOTICES, self::$notices );
		return array_filter(
			$notices,
			static fn( Notice $notice ) => Notice::BOTH === $notice->context || $context === $notice->context
		);
	}

	/**
	 * Returns all current notices formatted for the REST API / frontend.
	 *
	 * @return list<array{id: string, status: string, content: string, isDismissible: bool, type: string}>
	 */
	public static function get_notices_for_rest(): array {
		$formatted = [];
		foreach ( self::get_notices( Notice::APP ) as $notice ) {
			$formatted[] = [
				'id'            => $notice->id,
				'status'        => $notice->level,
				'content'       => $notice->message,
				'isDismissible' => $notice->dismissible,
				'type'          => 'default',
			];
		}
		return $formatted;
	}

	/**
	 * Renders all non-kudos-only notices via the admin_notices hook.
	 */
	public static function render_admin_notices(): void {
		$renderable = self::get_notices( Notice::ADMIN );

		if ( ! $renderable ) {
			return;
		}

		self::enqueue_dismiss_script();

		foreach ( $renderable as $notice ) {
			$message = $notice->logo
				? "<div class='logo' style='width: 40px; margin-right: 20px'>" . Utils::get_kudos_logo_svg() . "</div><div class='message'>" . $notice->message . '</div>'
				: $notice->message;

			self::render( $notice->id, $notice->level, $message, $notice->dismissible );
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
