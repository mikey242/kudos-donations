<?php
/**
 * Remote notice service.
 *
 * Periodically fetches a JSON notice feed from the Kudos server and injects matching,
 * undismissed items into the NoticeManager.
 *
 * @link https://code.iseard.media/michael/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Service;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Container\PluginUninstallAware;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Notice\Notice;
use IseardMedia\Kudos\Notice\NoticeManager;

class RemoteNoticeService extends AbstractRegistrable implements HasSettingsInterface, PluginUninstallAware {

	public const SETTING_SHOW_REMOTE_NOTICES = '_kudos_show_remote_notices';
	public const SETTING_REMOTE_NOTICES      = '_kudos_remote_notices';
	public const SETTING_DISMISSED_NOTICES   = '_kudos_dismissed_remote_notices';

	public const CRON_HOOK = 'kudos_fetch_remote_notices';

	/**
	 * Prefix applied to remote notice IDs so they can never collide with local notices
	 * and so dismissals can be routed back to this service.
	 */
	private const ID_PREFIX = 'remote-';

	/**
	 * Highest feed schema version this code understands. Bump on breaking feed changes;
	 * older plugin versions will then ignore the newer feed instead of misreading it.
	 */
	private const FEED_SCHEMA_VERSION = 1;

	private const ALLOWED_LEVELS = [ Notice::INFO, Notice::SUCCESS, Notice::WARNING, Notice::ERROR ];

	private string $feed_url;

	/**
	 * Sets the feed URL, overridable via env for local development (mirrors LicenceService).
	 */
	public function __construct() {
		$base_domain    = $_ENV['API_SERVER_URL'] ?? 'https://kudosdonations.com';
		$this->feed_url = $base_domain . '/wp-json/kudos-licence/v1/notices';
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		add_action( self::CRON_HOOK, [ $this, 'fetch' ] );
		add_filter( NoticeManager::FILTER_NOTICES, [ $this, 'inject_notices' ] );
		add_action( NoticeManager::ACTION_NOTICE_DISMISSED, [ $this, 'handle_dismiss' ] );

		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time(), 'twicedaily', self::CRON_HOOK );
		}
	}

	/**
	 * Fetches the remote feed and caches the validated result.
	 *
	 * Runs from cron only — never during a page request. On any failure the previous
	 * cached copy is kept, so a broken server never blanks or corrupts notices.
	 */
	public function fetch(): void {
		if ( ! get_option( self::SETTING_SHOW_REMOTE_NOTICES, true ) ) {
			return;
		}

		$response = wp_safe_remote_get(
			$this->feed_url,
			[
				'timeout'             => 5,
				'redirection'         => 2,
				'limit_response_size' => 64 * KB_IN_BYTES,
				// Default WP user agent includes the site URL; send only the plugin version.
				'user-agent'          => 'KudosDonations/' . KUDOS_VERSION,
			]
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$this->get_logger()->warning(
				'Remote notice fetch failed.',
				[ 'error' => is_wp_error( $response ) ? $response->get_error_message() : wp_remote_retrieve_response_code( $response ) ]
			);
			return;
		}

		$feed = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! \is_array( $feed ) || (int) ( $feed['version'] ?? 0 ) > self::FEED_SCHEMA_VERSION || ! \is_array( $feed['notices'] ?? null ) ) {
			$this->get_logger()->warning( 'Remote notice feed invalid or unsupported version.' );
			return;
		}

		$notices = array_values( array_filter( array_map( [ $this, 'sanitize_item' ], $feed['notices'] ) ) );

		update_option( self::SETTING_REMOTE_NOTICES, $notices, false );
		$this->prune_dismissed( $notices );
	}

	/**
	 * Adds applicable cached remote notices to the notice list. Hooked to the NoticeManager
	 * filter, so the work only happens when notices are actually being displayed.
	 *
	 * @param Notice[] $notices Current notices keyed by ID.
	 * @return Notice[]
	 */
	public function inject_notices( array $notices ): array {
		if ( ! get_option( self::SETTING_SHOW_REMOTE_NOTICES, true ) || ! current_user_can( 'manage_options' ) ) {
			return $notices;
		}

		$cached    = get_option( self::SETTING_REMOTE_NOTICES, [] );
		$dismissed = (array) get_option( self::SETTING_DISMISSED_NOTICES, [] );

		foreach ( (array) $cached as $item ) {
			$id = self::ID_PREFIX . $item['id'];
			if ( isset( $notices[ $id ] ) || \in_array( $item['id'], $dismissed, true ) || ! $this->matches_conditions( $item ) ) {
				continue;
			}

			$notices[ $id ] = new Notice(
				$id,
				$this->build_message( $item ),
				$item['level'],
				$item['dismissible'],
				$item['context']
			);
		}

		return $notices;
	}

	/**
	 * Records a dismissal of a remote notice. Without this the notice would be re-injected
	 * on the next request, since remote notices are not stored in the NoticeManager option.
	 *
	 * @param string $id The dismissed notice ID.
	 */
	public function handle_dismiss( string $id ): void {
		if ( ! str_starts_with( $id, self::ID_PREFIX ) ) {
			return;
		}

		$dismissed   = (array) get_option( self::SETTING_DISMISSED_NOTICES, [] );
		$dismissed[] = substr( $id, \strlen( self::ID_PREFIX ) );
		update_option( self::SETTING_DISMISSED_NOTICES, array_values( array_unique( $dismissed ) ), false );
	}

	/**
	 * Validates and normalises a single feed item. Anything unexpected is dropped rather
	 * than trusted — the feed is treated as untrusted input.
	 *
	 * @param mixed $item Raw decoded feed item.
	 * @return array|null Normalised item, or null if invalid.
	 */
	private function sanitize_item( $item ): ?array {
		if ( ! \is_array( $item ) || empty( $item['id'] ) || empty( $item['message'] ) ) {
			return null;
		}

		$id = sanitize_key( (string) $item['id'] );
		if ( '' === $id ) {
			return null;
		}

		$url = isset( $item['url'] ) ? esc_url_raw( (string) $item['url'], [ 'https' ] ) : '';

		$conditions = \is_array( $item['conditions'] ?? null ) ? $item['conditions'] : [];

		return [
			'id'          => $id,
			'title'       => sanitize_text_field( (string) ( $item['title'] ?? '' ) ),
			'message'     => sanitize_textarea_field( (string) $item['message'] ),
			'url'         => $url,
			'url_label'   => sanitize_text_field( (string) ( $item['url_label'] ?? __( 'Read more', 'kudos-donations' ) ) ),
			'level'       => \in_array( $item['level'] ?? '', self::ALLOWED_LEVELS, true ) ? $item['level'] : Notice::INFO,
			'context'     => \in_array( $item['context'] ?? '', [ Notice::BOTH, Notice::APP, Notice::ADMIN ], true ) ? $item['context'] : Notice::APP,
			'dismissible' => (bool) ( $item['dismissible'] ?? true ),
			'conditions'  => [
				'min_version' => sanitize_text_field( (string) ( $conditions['min_version'] ?? '' ) ),
				'max_version' => sanitize_text_field( (string) ( $conditions['max_version'] ?? '' ) ),
				'locales'     => array_map( 'sanitize_text_field', (array) ( $conditions['locales'] ?? [] ) ),
				'licence'     => \in_array( $conditions['licence'] ?? '', [ 'active', 'inactive' ], true ) ? $conditions['licence'] : '',
				'starts'      => (int) strtotime( (string) ( $conditions['starts'] ?? '' ) ),
				'expires'     => (int) strtotime( (string) ( $conditions['expires'] ?? '' ) ),
			],
		];
	}

	/**
	 * Evaluates targeting conditions locally. Nothing about the site is sent to the server.
	 *
	 * @param array $item Sanitised feed item.
	 */
	private function matches_conditions( array $item ): bool {
		$c   = $item['conditions'];
		$now = time();

		if ( $c['starts'] && $now < $c['starts'] ) {
			return false;
		}
		if ( $c['expires'] && $now > $c['expires'] ) {
			return false;
		}
		if ( $c['min_version'] && version_compare( KUDOS_VERSION, $c['min_version'], '<' ) ) {
			return false;
		}
		if ( $c['max_version'] && version_compare( KUDOS_VERSION, $c['max_version'], '>' ) ) {
			return false;
		}
		if ( $c['locales'] && ! \in_array( get_user_locale(), $c['locales'], true ) ) {
			return false;
		}
		if ( $c['licence'] && ( 'active' === $c['licence'] ) !== LicenceService::is_active() ) {
			return false;
		}

		return true;
	}

	/**
	 * Builds the notice HTML from plain-text fields. The feed never supplies HTML.
	 *
	 * @param array $item Sanitised feed item.
	 */
	private function build_message( array $item ): string {
		$html = '<p>';
		if ( $item['title'] ) {
			$html .= '<strong>' . esc_html( $item['title'] ) . '</strong><br>';
		}
		$html .= nl2br( esc_html( $item['message'] ) );
		if ( $item['url'] ) {
			$html .= ' <a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['url_label'] ) . '</a>';
		}
		return $html . '</p>';
	}

	/**
	 * Drops dismissal records for notices no longer in the feed, so the option doesn't grow forever.
	 *
	 * @param array $notices Current sanitised feed items.
	 */
	private function prune_dismissed( array $notices ): void {
		$live      = array_column( $notices, 'id' );
		$dismissed = (array) get_option( self::SETTING_DISMISSED_NOTICES, [] );
		$pruned    = array_values( array_intersect( $dismissed, $live ) );

		if ( $pruned !== $dismissed ) {
			update_option( self::SETTING_DISMISSED_NOTICES, $pruned, false );
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function on_plugin_uninstall(): void {
		wp_clear_scheduled_hook( self::CRON_HOOK );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_settings(): array {
		return [
			self::SETTING_SHOW_REMOTE_NOTICES => [
				'type'         => FieldType::BOOLEAN,
				'show_in_rest' => true,
				'default'      => true,
			],
			self::SETTING_REMOTE_NOTICES      => [
				'type'         => FieldType::ARRAY,
				'show_in_rest' => false,
				'default'      => [],
			],
			self::SETTING_DISMISSED_NOTICES   => [
				'type'         => FieldType::ARRAY,
				'show_in_rest' => false,
				'default'      => [],
			],
		];
	}
}
