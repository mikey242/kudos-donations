<?php
/**
 * Brevo provider.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

namespace IseardMedia\KudosPremium\NewsletterProvider;

use Brevo\Client\Api\ContactsApi as Client;
use Brevo\Client\Configuration;
use Brevo\Client\Model\CreateContact;
use Exception;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Vendor\AbstractVendor;
use RuntimeException;

class Brevo extends AbstractVendor implements NewsletterProviderInterface {

	public const SETTING_BREVO_API_KEY           = '_kudos_brevo_api_key';
	public const SETTING_BREVO_API_KEY_ENCRYPTED = '_kudos_brevo_api_key_encrypted';
	public const SETTING_BREVO_LISTS             = '_kudos_brevo_lists';
	public const SETTING_BREVO_SELECTED_LIST     = '_kudos_brevo_selected_list';
	public const SETTING_BREVO_TAG               = '_kudos_brevo_tag';
	private ?Client $api_client                  = null;

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		// Handle API key saving.
		add_filter( 'pre_update_option_' . self::SETTING_BREVO_API_KEY, [ $this, 'handle_key_update' ], 10, 3 );
	}

	/**
	 * Gets the configured Brevo api client.
	 *
	 * @throws RuntimeException | Exception If api key is not set.
	 */
	public function get_api_client(): Client {
		if ( null === $this->api_client ) {
			$api_key = $this->get_decrypted_key( self::SETTING_BREVO_API_KEY_ENCRYPTED );
			if ( empty( $api_key ) ) {
				throw new RuntimeException( 'Brevo API key is not configured.' );
			}

			$config           = Configuration::getDefaultConfiguration()->setApiKey( 'api-key', $api_key );
			$this->api_client = new Client( new \GuzzleHttp\Client(), $config );
		}

		return $this->api_client;
	}

	/**
	 * Handles the saving of the api key.
	 *
	 * @param string $value The api key value.
	 */
	public function handle_key_update( string $value ): string {
		return $this->save_encrypted_key( $value, self::SETTING_BREVO_API_KEY_ENCRYPTED, [ $this, 'refresh' ] );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_name(): string {
		return 'Brevo';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws RuntimeException If there is an error refreshing.
	 */
	public function refresh(): bool {
		try {
			$response = $this->get_api_client()->getLists();
			return update_option(
				self::SETTING_BREVO_LISTS,
				array_map(
					fn( $email_list ) => [
						'id'   => $email_list['id'],
						'name' => $email_list['name'],
					],
					$response->getLists()
				)
			);
		} catch ( Exception $e ) {
			$this->logger->error( __( 'Brevo: Something went wrong refreshing', 'kudos-donations' ), [ 'message' => $e->getMessage() ] );
			throw new RuntimeException( esc_html__( 'Brevo failed to refresh, please check your api key.', 'kudos-donations' ) );
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function subscribe_user( string $email, ?string $name = null ): void {
		try {
			$api_client     = $this->get_api_client();
			$list_id        = get_option( self::SETTING_BREVO_SELECTED_LIST );
			$split_name     = Utils::split_name( $name );
			$create_contact = new CreateContact(
				[
					'email'      => $email,
					'attributes' => [
						'FIRSTNAME' => $split_name[0] ?? '',
						'LASTNAME'  => $split_name[1] ?? '',
					],
					'listIds'    => [ (int) $list_id ],
				]
			);
			$api_client->createContact( $create_contact );
		} catch ( Exception $e ) {
			$this->logger->error( 'Error subscribing user to Brevo', [ 'message' => $e->getMessage() ] );
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_settings(): array {
		return [
			self::SETTING_BREVO_API_KEY           => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => '',
			],
			self::SETTING_BREVO_API_KEY_ENCRYPTED => [
				'type'         => FieldType::STRING,
				'show_in_rest' => false,
			],
			self::SETTING_BREVO_SELECTED_LIST     => [
				'type'         => FieldType::INTEGER,
				'show_in_rest' => true,
				'default'      => '',
			],
			self::SETTING_BREVO_LISTS             => [
				'type'         => FieldType::ARRAY,
				'show_in_rest' => [
					'schema' => [
						'type'  => FieldType::ARRAY,
						'items' => [
							'type'       => FieldType::OBJECT,
							'properties' => [
								'id'   => [
									'type' => FieldType::INTEGER,
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
			self::SETTING_BREVO_TAG               => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => 'kudos-donations',
			],
		];
	}
}
