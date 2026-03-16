<?php
/**
 * Plugin Update Service.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Service;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Enum\FieldType;
use stdClass;

class LicenceService extends AbstractRegistrable implements HasSettingsInterface {

	public const SETTING_KUDOS_LICENCE_KEY = '_kudos_licence_key';
	public const SETTING_LICENCE_STATUS    = '_kudos_licence_status';
	public const STATUS_ACTIVE             = 'active';
	public const STATUS_EXPIRED            = 'expired';
	public const STATUS_NOT_SET            = 'not-set';
	private string $base_domain;
	private string $base_path;
	private string $base_url;

	/**
	 * Plugin licence constructor. Sets licence server URL based on presence of ENV_VAR.
	 */
	public function __construct() {
		$this->base_domain = $_ENV['API_SERVER_URL'] ?? 'https://kudosdonations.com';
		$this->base_path   = '/wp-json/kudos-licence/v1';
		$this->base_url    = $this->base_domain . $this->base_path;
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		add_filter( 'kudos_global_localization', [ $this, 'add_licence_status' ] );
		add_filter( 'pre_update_option_' . self::SETTING_KUDOS_LICENCE_KEY, [ $this, 'handle_key_update' ], 10, 2 );
	}

	/**
	 * Returns true if licence active.
	 *
	 * @param array $localisation The localisation array.
	 */
	public function add_licence_status( array $localisation ): array {
		$localisation['isLicenceActive'] = self::is_active();
		return $localisation;
	}

	/**
	 * Validates the licence key before saving. Rejects the new value if invalid.
	 *
	 * Hooked into pre_update_option so an invalid key is never persisted.
	 *
	 * @param string $new_value New licence key value.
	 * @param string $old_value Previous licence key value.
	 * @return string The value to save.
	 */
	public function handle_key_update( string $new_value, string $old_value ): string {
		if ( empty( $new_value ) ) {
			if ( ! empty( $old_value ) ) {
				$this->deactivate_licence( $old_value );
			}
			update_option( self::SETTING_LICENCE_STATUS, [] );
			return $new_value;
		}

		$response = $this->activate_licence( $new_value );
		$valid    = $response && isset( $response->success ) && (bool) $response->success;

		if ( ! $valid ) {
			return $old_value;
		}

		update_option(
			self::SETTING_LICENCE_STATUS,
			[
				'valid'      => true,
				'expires_at' => $response->expires_at ?? '',
			]
		);

		/**
		 * Fires after a licence key has been successfully activated.
		 *
		 * @param string $licence_key The activated licence key.
		 */
		do_action( 'kudos_licence_activated', $new_value );

		$this->maybe_install_addon( $new_value );

		return $new_value;
	}

	/**
	 * Attempts to install the premium add-on if it is not already present.
	 *
	 * Fetches plugin info from the licence server and, if a download URL is
	 * returned and the add-on is not yet installed, uses WP_Upgrader to install
	 * and activate it.
	 *
	 * @param string $licence_key The active licence key.
	 */
	private function maybe_install_addon( string $licence_key ): void {
		$url = add_query_arg(
			[
				'licence_key' => $licence_key,
				'domain'      => $_SERVER['SERVER_NAME'] ?? '',
			],
			rtrim( $this->base_url, '/' ) . '/info'
		);

		$response = wp_remote_get(
			$url,
			[
				'timeout' => 10,
				'headers' => [ 'Accept' => 'application/json' ],
			]
		);

		if (
			is_wp_error( $response )
			|| 200 !== wp_remote_retrieve_response_code( $response )
			|| empty( wp_remote_retrieve_body( $response ) )
		) {
			return;
		}

		$remote = json_decode( wp_remote_retrieve_body( $response ) );

		if ( empty( $remote->download_url ) || empty( $remote->slug ) ) {
			return;
		}

		$plugin_file = $remote->slug . '/' . $remote->slug . '.php';

		if ( is_plugin_active( $plugin_file ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		$upgrader = new \Plugin_Upgrader( new \WP_Ajax_Upgrader_Skin() );
		$result   = $upgrader->install( $remote->download_url );

		if ( true === $result ) {
			activate_plugin( $plugin_file );
		}
	}

	/**
	 * Activates the licence key against the update server.
	 *
	 * @param string $licence_key The licence key to activate.
	 * @return stdClass|false Decoded response object on success, false on failure.
	 */
	private function activate_licence( string $licence_key ) {
		$url = rtrim( $this->base_url, '/' ) . '/licences/activate';

		$response = wp_remote_post(
			$url,
			[
				'timeout' => 10,
				'headers' => [
					'Accept'       => 'application/json',
					'Content-Type' => 'application/json',
				],
				'body'    => wp_json_encode(
					[
						'licence_key' => $licence_key,
						'domain'      => $_SERVER['SERVER_NAME'] ?? '',
					]
				),
			]
		);

		if ( is_wp_error( $response ) || empty( wp_remote_retrieve_body( $response ) ) ) {
			return false;
		}

		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Deactivates the licence key against the update server.
	 *
	 * @param string $licence_key The licence key to deactivate.
	 * @return void True if the server accepted the deactivation, false otherwise.
	 */
	private function deactivate_licence( string $licence_key ): void {
		$url = rtrim( $this->base_url, '/' ) . '/licences/deactivate';

		$response = wp_remote_post(
			$url,
			[
				'timeout' => 10,
				'headers' => [
					'Accept'       => 'application/json',
					'Content-Type' => 'application/json',
				],
				'body'    => wp_json_encode(
					[
						'licence_key' => $licence_key,
						'domain'      => $_SERVER['SERVER_NAME'] ?? '',
					]
				),
			]
		);

		! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response );
	}

	/**
	 * Returns the licence status: active, expired, or not-set.
	 */
	public static function get_status(): string {
		$status = get_option( self::SETTING_LICENCE_STATUS, [] );
		if ( empty( $status['valid'] ) ) {
			return self::STATUS_NOT_SET;
		}
		if ( ! empty( $status['expires_at'] ) && strtotime( $status['expires_at'] ) <= time() ) {
			return self::STATUS_EXPIRED;
		}
		return self::STATUS_ACTIVE;
	}

	/**
	 * Returns true if the licence is active.
	 */
	public static function is_active(): bool {
		return self::STATUS_ACTIVE === self::get_status();
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_settings(): array {
		return [
			self::SETTING_KUDOS_LICENCE_KEY => [
				'type'              => FieldType::STRING,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::SETTING_LICENCE_STATUS    => [
				'type'         => FieldType::OBJECT,
				'show_in_rest' => [
					'schema' => [
						'properties' => [
							'valid'      => [ 'type' => FieldType::BOOLEAN ],
							'expires_at' => [ 'type' => FieldType::STRING ],
						],
					],
				],
				'default'      => [],
			],
		];
	}
}
