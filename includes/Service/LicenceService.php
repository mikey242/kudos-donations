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
	private const CACHE_KEY                = 'kudos_update_info';
	private const CACHE_TTL                = 12 * HOUR_IN_SECONDS;
	private string $base_domain;
	private string $base_path;
	private string $base_url;
	private string $plugin_slug;
	private string $plugin_basename;

	/**
	 * Plugin licence constructor. Sets licence server URL based on presence of ENV_VAR.
	 */
	public function __construct() {
		$this->base_domain     = $_ENV['API_SERVER_URL'] ?? 'https://kudosdonations.com';
		$this->base_path       = '/wp-json/kudos-licence/v1';
		$this->base_url        = $this->base_domain . $this->base_path;
		$this->plugin_basename = plugin_basename( KUDOS_PLUGIN_FILE );
		$this->plugin_slug     = \dirname( $this->plugin_basename );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		add_filter( 'plugins_api', [ $this, 'plugin_info' ], 20, 3 );
		add_filter( 'site_transient_update_plugins', [ $this, 'update_check' ] );
		add_action( 'upgrader_process_complete', [ $this, 'purge_cache' ], 10, 2 );
		add_filter( 'pre_update_option_' . self::SETTING_KUDOS_LICENCE_KEY, [ $this, 'handle_key_update' ], 10, 2 );
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

		return $new_value;
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
	 * Fetch remote plugin data, using a transient as a cache.
	 *
	 * @return stdClass|false Remote data object on success, false on failure.
	 */
	private function fetch_remote() {
		$remote = get_transient( self::CACHE_KEY );

		if ( false !== $remote ) {
			return $remote;
		}

		$url = add_query_arg(
			[
				'licence_key' => get_option( self::SETTING_KUDOS_LICENCE_KEY, '' ),
				'domain'      => $_SERVER['SERVER_NAME'] ?? '',
			],
			rtrim( $this->base_url, '/' ) . '/info'
		);

		$response = wp_remote_get(
			$url,
			[
				'timeout' => 10,
				'headers' => [
					'Accept' => 'application/json',
				],
			]
		);

		if (
			is_wp_error( $response )
			|| 200 !== wp_remote_retrieve_response_code( $response )
			|| empty( wp_remote_retrieve_body( $response ) )
		) {
			return false;
		}

		$remote = json_decode( wp_remote_retrieve_body( $response ) );
		set_transient( self::CACHE_KEY, $remote, self::CACHE_TTL );

		return $remote;
	}

	/**
	 * Supply plugin information for the "View details" modal.
	 *
	 * @param mixed  $result The result object or array. Default false.
	 * @param string $action The type of information being requested.
	 * @param object $args   Plugin API arguments.
	 * @return mixed
	 */
	public function plugin_info( $result, string $action, object $args ) {
		if ( 'plugin_information' !== $action ) {
			return $result;
		}

		if ( $this->plugin_slug !== $args->slug ) {
			return $result;
		}

		$remote = $this->fetch_remote();

		if ( false === $remote ) {
			return $result;
		}

		$result                 = new stdClass();
		$result->name           = $remote->name;
		$result->slug           = $remote->slug;
		$result->author         = $remote->author;
		$result->author_profile = $remote->author_profile;
		$result->version        = $remote->version;
		$result->tested         = $remote->tested;
		$result->requires       = $remote->requires;
		$result->requires_php   = $remote->requires_php;
		$result->download_link  = $remote->download_url;
		$result->trunk          = $remote->download_url;
		$result->last_updated   = $remote->last_updated;
		$result->sections       = [
			'description'  => $remote->sections->description,
			'installation' => $remote->sections->installation,
			'changelog'    => $remote->sections->changelog,
		];
		if ( ! empty( $remote->sections->screenshots ) ) {
			$result->sections['screenshots'] = $remote->sections->screenshots;
		}
		$result->banners = [
			'low'  => $remote->banners->low,
			'high' => $remote->banners->high,
		];

		return $result;
	}

	/**
	 * Inject an update into the update transient when a newer version is available.
	 *
	 * @param mixed $transient The update_plugins transient value.
	 * @return mixed
	 */
	public function update_check( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$remote = $this->fetch_remote();

		if ( false === $remote ) {
			return $transient;
		}

		if (
			version_compare( KUDOS_VERSION, $remote->version, '<' )
			&& version_compare( $remote->requires, get_bloginfo( 'version' ), '<=' )
			&& version_compare( $remote->requires_php, PHP_VERSION, '<' )
		) {
			$update               = new stdClass();
			$update->id           = $this->plugin_slug;
			$update->slug         = $this->plugin_slug;
			$update->plugin       = $this->plugin_basename;
			$update->new_version  = $remote->version;
			$update->tested       = $remote->tested;
			$update->package      = $remote->download_url;
			$update->requires_php = $remote->requires_php;
			$update->url          = '';

			$transient->response[ $this->plugin_basename ] = $update;
		}

		return $transient;
	}

	/**
	 * Clear the cached remote data after a successful plugin update.
	 *
	 * @param mixed $upgrader   WP_Upgrader instance.
	 * @param array $hook_extra Extra data about the upgrade.
	 */
	public function purge_cache( $upgrader, array $hook_extra ): void {
		if ( 'plugin' !== ( $hook_extra['type'] ?? '' ) ) {
			return;
		}

		$plugins = (array) ( $hook_extra['plugins'] ?? [] );

		if ( \in_array( $this->plugin_basename, $plugins, true ) ) {
			delete_transient( self::CACHE_KEY );
		}
	}

	/**
	 * Returns true if the licence is valid and not expired.
	 */
	public static function is_active(): bool {
		$status = get_option( self::SETTING_LICENCE_STATUS, [] );
		if ( empty( $status['valid'] ) ) {
			return false;
		}
		if ( ! empty( $status['expires_at'] ) ) {
			return strtotime( $status['expires_at'] ) > time();
		}
		return true;
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
