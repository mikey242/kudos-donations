<?php
/**
 * Mailerlite provider.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

namespace IseardMedia\KudosPremium\NewsletterProvider;

use Exception;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Vendor\AbstractVendor;
use MailerLite\MailerLite as Client;
use RuntimeException;

class MailerLite extends AbstractVendor implements NewsletterProviderInterface {

	public const SETTING_MAILERLITE_API_KEY           = '_kudos_mailerlite_api_key';
	public const SETTING_MAILERLITE_API_KEY_ENCRYPTED = '_kudos_mailerlite_api_key_encrypted';
	public const SETTING_MAILERLITE_GROUPS            = '_kudos_mailerlite_groups';
	public const SETTING_MAILERLITE_SELECTED_GROUP    = '_kudos_mailerlite_selected_group';
	private ?Client $api_client                       = null;

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		// Handle API key saving.
		add_filter( 'pre_update_option_' . self::SETTING_MAILERLITE_API_KEY, [ $this, 'handle_key_update' ], 10, 3 );
	}

	/**
	 * Gets the configured MailerLite api client.
	 *
	 * @throws RuntimeException If api key is not set.
	 */
	public function get_api_client(): Client {
		if ( null === $this->api_client ) {
			$api_key = $this->get_decrypted_key( self::SETTING_MAILERLITE_API_KEY_ENCRYPTED );
			if ( empty( $api_key ) ) {
				throw new RuntimeException( 'MailerLite API key is not configured.' );
			}

			$this->api_client = new Client( [ 'api_key' => $api_key ] );
		}

		return $this->api_client;
	}

	/**
	 * Handles the saving of the api key.
	 *
	 * @param string $value The api key value.
	 */
	public function handle_key_update( string $value ): string {
		return $this->save_encrypted_key( $value, self::SETTING_MAILERLITE_API_KEY_ENCRYPTED, [ $this, 'refresh' ] );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_name(): string {
		return 'Mailerlite';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws RuntimeException If there is an error refreshing.
	 */
	public function refresh(): bool {
		try {
			$groups = $this->get_groups();
			if ( $groups ) {
				update_option(
					self::SETTING_MAILERLITE_GROUPS,
					array_map(
						fn( $group ) =>
						[
							'id'   => $group['id'],
							'name' => $group['name'],
						],
						$groups
					)
				);
			}
			return true;
		} catch ( Exception $e ) {
			$this->logger->error( __( 'Mailerlite: Something went wrong refreshing', 'kudos-donations' ), [ 'message' => $e->getMessage() ] );
			throw new RuntimeException( esc_html__( 'Mailerlite failed to refresh, please check your api key.', 'kudos-donations' ) );
		}
	}

	/**
	 * Returns the groups stored on the account.
	 *
	 * @throws Exception When there is an error with the api.
	 */
	private function get_groups(): array {
			$response = $this->get_api_client()->groups->get();
			return $response['body']['data'] ?? [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function subscribe_user( string $email, ?string $name = null ): void {
		$api_client = $this->get_api_client();
		$group_id   = get_option( self::SETTING_MAILERLITE_SELECTED_GROUP );
		$api_client->subscribers->create(
			[
				'email'  => $email,
				'groups' => [ $group_id ],
				'status' => 'active',
				'fields' => [
					'name' => $name,
				],
			]
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_settings(): array {
		return [
			self::SETTING_MAILERLITE_API_KEY           => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => '',
			],
			self::SETTING_MAILERLITE_API_KEY_ENCRYPTED => [
				'type'         => FieldType::STRING,
				'show_in_rest' => false,
			],
			self::SETTING_MAILERLITE_SELECTED_GROUP    => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => '',
			],
			self::SETTING_MAILERLITE_GROUPS            => [
				'type'         => FieldType::ARRAY,
				'show_in_rest' => [
					'schema' => [
						'type'  => FieldType::ARRAY,
						'items' => [
							'type'       => FieldType::OBJECT,
							'properties' => [
								'id'   => [
									'type' => FieldType::STRING,
								],
								'name' => [
									'type' => FieldType::STRING,
								],
							],
						],
					],
				],
				'default'      => [],
			],
		];
	}
}
