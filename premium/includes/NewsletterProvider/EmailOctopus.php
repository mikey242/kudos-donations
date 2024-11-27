<?php
/**
 * EmailOctopus provider.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

namespace IseardMedia\KudosPremium\NewsletterProvider;

use Exception;
use GoranPopovic\EmailOctopus\Client;
use GoranPopovic\EmailOctopus\EmailOctopus as Octopus;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Vendor\AbstractVendor;
use RuntimeException;

class EmailOctopus extends AbstractVendor implements NewsletterProviderInterface {

	public const SETTING_EMAILOCTOPUS_API_KEY           = '_kudos_emailoctopus_api_key';
	public const SETTING_EMAILOCTOPUS_API_KEY_ENCRYPTED = '_kudos_emailoctopus_api_key_encrypted';
	public const SETTING_EMAILOCTOPUS_LISTS             = '_kudos_emailoctopus_lists';
	public const SETTING_EMAILOCTOPUS_SELECTED_LIST     = '_kudos_emailoctopus_selected_list';
	public const SETTING_EMAILOCTOPUS_TAG               = '_kudos_emailoctopus_tag';
	private ?Client $api_client                         = null;

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		// Handle API key saving.
		add_filter( 'pre_update_option_' . self::SETTING_EMAILOCTOPUS_API_KEY, [ $this, 'handle_key_update' ], 10, 3 );
	}

	/**
	 * Gets the configured EmailOctopus api client.
	 *
	 * @throws RuntimeException | Exception If api key is not set.
	 */
	public function get_api_client(): Client {
		if ( null === $this->api_client ) {
			$api_key = $this->get_decrypted_key( self::SETTING_EMAILOCTOPUS_API_KEY_ENCRYPTED );
			if ( empty( $api_key ) ) {
				throw new RuntimeException( 'EmailOctopus API key is not configured.' );
			}

			$this->api_client = Octopus::client( $api_key );
		}

		return $this->api_client;
	}

	/**
	 * Handles the saving of the api key.
	 *
	 * @param string $value The api key value.
	 */
	public function handle_key_update( string $value ): string {
		return $this->save_encrypted_key( $value, self::SETTING_EMAILOCTOPUS_API_KEY_ENCRYPTED, [ $this, 'refresh' ] );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_name(): string {
		return 'EmailOctopus';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws RuntimeException If there is an error refreshing.
	 */
	public function refresh(): bool {
		try {
			$response = $this->get_api_client()->lists()->getAll();
			$lists    = [];
			foreach ( $response['data'] as $list ) {
				$lists[] = (object) [
					'id'   => $list['id'],
					'name' => $list['name'],
				];
			}
			return update_option( self::SETTING_EMAILOCTOPUS_LISTS, $lists );
		} catch ( Exception $e ) {
			$this->logger->error( __( 'EmailOctopus: Something went wrong refreshing', 'kudos-donations' ), [ 'message' => $e->getMessage() ] );
			throw new RuntimeException( esc_html__( 'EmailOctopus failed to refresh, please check your api key.', 'kudos-donations' ) );
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function subscribe_user( string $email, ?string $name = null ): void {
		try {
			$api_client = $this->get_api_client();
			$group_id   = get_option( self::SETTING_EMAILOCTOPUS_SELECTED_LIST );
			$tag        = get_option( self::SETTING_EMAILOCTOPUS_TAG );
			$split_name = Utils::split_name( $name );
			$api_client->lists()->createContact(
				$group_id,
				[
					'email_address' => $email,
					'fields'        => [
						'FirstName' => $split_name[0] ?? '',
						'LastName'  => $split_name[1] ?? '',
					],
					'tags'          => [
						$tag,
					],
				]
			);
		} catch ( Exception $e ) {
			$this->logger->error( 'Error subscribing user to EmailOctopus', [ 'message' => $e->getMessage() ] );
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_settings(): array {
		return [
			self::SETTING_EMAILOCTOPUS_API_KEY           => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => '',
			],
			self::SETTING_EMAILOCTOPUS_API_KEY_ENCRYPTED => [
				'type'         => FieldType::STRING,
				'show_in_rest' => false,
			],
			self::SETTING_EMAILOCTOPUS_SELECTED_LIST     => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => '',
			],
			self::SETTING_EMAILOCTOPUS_LISTS             => [
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
			self::SETTING_EMAILOCTOPUS_TAG               => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => 'kudos-donations',
			],
		];
	}
}
