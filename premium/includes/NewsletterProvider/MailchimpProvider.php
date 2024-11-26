<?php
/**
 * Mailchimp provider.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

namespace IseardMedia\KudosPremium\NewsletterProvider;

use GuzzleHttp\Exception\BadResponseException;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Vendor\AbstractVendor;
use MailchimpMarketing\ApiClient;
use RuntimeException;

class MailchimpProvider extends AbstractVendor implements NewsletterProviderInterface {

	public const SETTING_MAILCHIMP_API_KEY           = '_kudos_mailchimp_api_key';
	public const SETTING_MAILCHIMP_API_KEY_ENCRYPTED = '_kudos_mailchimp_api_key_encrypted';
	public const SETTING_MAILCHIMP_CONTACT_TAG       = '_kudos_mailchimp_contact_tag';
	public const SETTING_MAILCHIMP_AUDIENCES         = '_kudos_mailchimp_audiences';
	public const SETTING_MAILCHIMP_SELECTED_AUDIENCE = '_kudos_mailchimp_selected_audience';
	private ?ApiClient $api_client                   = null;

	/**
	 * Mailchimp service constructor.
	 */
	public function __construct() {
		// Handle API key saving.
		add_filter( 'pre_update_option_' . self::SETTING_MAILCHIMP_API_KEY, [ $this, 'handle_key_update' ], 10, 3 );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {}

	/**
	 * Returns the api client.
	 *
	 * @throws RuntimeException If api key is not set.
	 */
	public function get_api_client(): ApiClient {
		if ( null === $this->api_client ) {
			$api_key = $this->get_decrypted_key( self::SETTING_MAILCHIMP_API_KEY_ENCRYPTED );

			if ( empty( $api_key ) ) {
				throw new RuntimeException( 'Mailchimp API key is not configured.' );
			}

			$server           = substr( $api_key, strpos( $api_key, '-' ) + 1 );
			$this->api_client = new ApiClient();
			$this->api_client->setConfig(
				[
					'apiKey' => $api_key,
					'server' => $server,
				]
			);
		}

		return $this->api_client;
	}


	/**
	 * Handles the saving of the api key.
	 *
	 * @param string $value The value to encrypt.
	 */
	public function handle_key_update( string $value ): string {
		return $this->save_encrypted_key( $value, self::SETTING_MAILCHIMP_API_KEY_ENCRYPTED, [ $this, 'refresh' ] );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_name(): string {
		return 'Mailchimp';
	}

	/**
	 * Perform a test ping.
	 */
	public function ping(): bool {
		$response = $this->get_api_client()->ping->get();
		return "Everything's Chimpy!" === $response->health_status;
	}

	/**
	 * Get lists/audiences.
	 */
	private function get_audiences(): ?object {
		if ( $this->ping() ) {
			return $this->get_api_client()->lists->getAllLists();
		}
		return null;
	}

	/**
	 * Refresh the cached audiences.
	 */
	public function refresh(): bool {
		$audiences = $this->get_audiences();
		if ( $audiences ) {
			update_option(
				self::SETTING_MAILCHIMP_AUDIENCES,
				array_map(
					fn( $audience ) => [
						'id'   => $audience->id,
						'name' => $audience->name,
					],
					$audiences->lists
				)
			);
			return true;
		}
		return false;
	}

	/**
	 * Add the user to the mailing list.
	 *
	 * @see https://stackoverflow.com/questions/63722005/mailchimp-addlistmember-returns-client-error-400-bad-request-when-already-existi
	 *
	 * @param string  $email Email address.
	 * @param ?string $name The user's name.
	 */
	public function subscribe_user( string $email, ?string $name = null ): void {
		$api_client = $this->get_api_client();
		$list_id    = get_option( self::SETTING_MAILCHIMP_SELECTED_AUDIENCE );
		try {
			$subscriber_hash = md5( strtolower( $email ) );
			$response        = $api_client->lists->setListMember(
				$list_id,
				$subscriber_hash,
				[
					'email_address' => $email,
					'status'        => 'subscribed',
					'merge_fields'  => [
						'NAME' => $name,
					],
				]
			);
			$api_client->lists->updateListMember(
				$list_id,
				$subscriber_hash,
				[
					'tags' => [
						'name' => get_option( self::SETTING_MAILCHIMP_CONTACT_TAG ),
					],
				]
			);
		} catch ( BadResponseException $e ) {
			$response        = $e->getResponse();
			$response_string = $response->getBody()->getContents();
			$decoded         = json_decode( $response_string );
			$this->logger->error( 'Mailchimp error:', [ $decoded ] );
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_settings(): array {
		return [
			self::SETTING_MAILCHIMP_API_KEY           => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => '',
			],
			self::SETTING_MAILCHIMP_API_KEY_ENCRYPTED => [
				'type'         => FieldType::STRING,
				'show_in_rest' => false,
			],
			self::SETTING_MAILCHIMP_CONTACT_TAG       => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => 'kudos-donations',
			],
			self::SETTING_MAILCHIMP_SELECTED_AUDIENCE => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => '',
			],
			self::SETTING_MAILCHIMP_AUDIENCES         => [
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
