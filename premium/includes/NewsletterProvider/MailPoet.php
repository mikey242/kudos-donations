<?php
/**
 * MailPoet provider.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

namespace IseardMedia\KudosPremium\NewsletterProvider;

use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Vendor\AbstractVendor;
use RuntimeException;

class MailPoet extends AbstractVendor implements NewsletterProviderInterface {

	public const SETTING_MAILPOET_API_KEY           = '_kudos_mailpoet_api_key';
	public const SETTING_MAILPOET_API_KEY_ENCRYPTED = '_kudos_mailpoet_api_key_encrypted';
	public const SETTING_MAILPOET_API_URL           = '_kudos_mailpoet_api_url';
	public const SETTING_MAILPOET_LISTS             = '_kudos_mailpoet_lists';
	public const SETTING_MAILPOET_SELECTED_LIST     = '_kudos_mailpoet_selected_list';
	private ?object $api_client                     = null;

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {}

	/**
	 * Gets the configured MailerLite api client.
	 *
	 * @throws RuntimeException If api key is not set.
	 */
	public function get_api_client(): ?object {
		if ( null === $this->api_client ) {
			if ( class_exists( '\MailPoet\API\API' ) ) {
				$this->api_client = \MailPoet\API\API::MP( 'v1' );
			} else {
				throw new RuntimeException( 'MailPoet does not appear to be installed.' );
			}
		}

		return $this->api_client;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_name(): string {
		return 'MailPoet';
	}

	/**
	 * {@inheritDoc}
	 */
	public function refresh(): bool {
		$lists = $this->get_api_client()->getLists();
		if ( $lists ) {
			update_option(
				self::SETTING_MAILPOET_LISTS,
				array_map(
					fn( $single_list ) =>
					[
						'id'   => $single_list['id'],
						'name' => $single_list['name'],
					],
					$lists
				)
			);
			return true;
		}
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function subscribe_user( string $email, ?string $name = null ): void {
		$api_client = $this->get_api_client();
		$list_id    = get_option( self::SETTING_MAILPOET_SELECTED_LIST );
		$split_name = Utils::split_name( $name );
		$api_client->addSubscriber(
			[
				'email'      => $email,
				'first_name' => $split_name[0] ?? '',
				'last_name'  => $split_name[1] ?? '',
			],
			[ $list_id ]
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_settings(): array {
		return [
			self::SETTING_MAILPOET_API_KEY           => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => '',
			],
			self::SETTING_MAILPOET_API_KEY_ENCRYPTED => [
				'type'         => FieldType::STRING,
				'show_in_rest' => false,
			],
			self::SETTING_MAILPOET_API_URL           => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
			],
			self::SETTING_MAILPOET_SELECTED_LIST     => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => '',
			],
			self::SETTING_MAILPOET_LISTS             => [
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
