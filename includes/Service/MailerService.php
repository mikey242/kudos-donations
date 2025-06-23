<?php
/**
 * Mailer service.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Service;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Repository\BaseRepository;
use IseardMedia\Kudos\Repository\DonorRepository;
use IseardMedia\Kudos\Repository\RepositoryAwareInterface;
use IseardMedia\Kudos\Repository\RepositoryAwareTrait;
use IseardMedia\Kudos\Repository\SubscriptionRepository;
use IseardMedia\Kudos\Repository\TransactionRepository;
use IseardMedia\Kudos\Vendor\EmailVendor\EmailVendorFactory;
use IseardMedia\Kudos\Vendor\EmailVendor\EmailVendorInterface;

class MailerService extends AbstractRegistrable implements HasSettingsInterface, RepositoryAwareInterface {

	use RepositoryAwareTrait;

	public const SETTING_EMAIL_VENDOR         = '_kudos_email_vendor';
	public const SETTING_EMAIL_RECEIPT_ENABLE = '_kudos_email_receipt_enable';
	public const SETTING_EMAIL_SHOW_CAMPAIGN  = '_kudos_email_show_campaign_name';
	private EmailVendorInterface $vendor;

	/**
	 * MailerService constructor.
	 *
	 * @param EmailVendorFactory $vendor The currently configured email vendor.
	 */
	public function __construct( EmailVendorFactory $vendor ) {
		$this->vendor = $vendor->get_vendor();
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {}

	/**
	 * Sends a message.
	 *
	 * @param string $email Email address.
	 * @param string $header Message header.
	 * @param string $message Message body.
	 */
	public function send_message( string $email, string $header, string $message ): bool {
		return $this->vendor->send_message( $email, $header, $message );
	}

	/**
	 * Sends receipt to the donor.
	 *
	 * @param int $transaction_id Transaction id.
	 */
	public function send_receipt( int $transaction_id ): bool {
		// Check if setting enabled.
		if ( ! get_option( self::SETTING_EMAIL_RECEIPT_ENABLE ) ) {
			return false;
		}

		$this->logger->debug( 'Receipt emails enabled, setting up email.', [ 'transaction_id' => $transaction_id ] );

		$transaction = $this->get_repository( TransactionRepository::class )
			->find( $transaction_id );
		$donor       = $this->get_repository( TransactionRepository::class )
			->get_donor( $transaction );

		// Email address.
		$email = $donor[ DonorRepository::EMAIL ];

		// Bail if no email address.
		if ( ! $email ) {
			$this->logger->debug( 'Cannot send email: donor has no email address', [ 'donor' => $donor ] );
			return false;
		}

		// Assign attachment.
		$attachments = apply_filters( 'kudos_receipt_attachment', [], $transaction[ BaseRepository::ID ] );

		// Get campaign name if enabled.
		$campaign_name = '';
		if ( get_option( self::SETTING_EMAIL_SHOW_CAMPAIGN ) ) {
			$campaign      = $this->get_repository( TransactionRepository::class )
									->get_campaign( $transaction );
			$campaign_name = $campaign[ BaseRepository::TITLE ];
		}

		// Create array of variables for use in twig template.
		$args = [
			'name'          => $donor[ DonorRepository::NAME ] ?? '',
			'date'          => $transaction[ BaseRepository::CREATED_AT ],
			'description'   => $transaction[ BaseRepository::TITLE ],
			'amount'        => ( ! empty( $transaction[ TransactionRepository::CURRENCY ] ) ? html_entity_decode(
				Utils::get_currencies()[ $transaction[ TransactionRepository::CURRENCY ] ]
			) : '' ) . number_format_i18n(
				$transaction[ TransactionRepository::VALUE ],
				2
			),
			'receipt_id'    => Utils::get_id( $transaction, TransactionRepository::get_singular_name() ),
			'campaign_name' => $campaign_name,
			'attachments'   => $attachments,
		];

		// Add a cancel button if this is the receipt for a subscription payment.
		try {
			if ( 'oneoff' !== $transaction[ TransactionRepository::SEQUENCE_TYPE ] ) {
				$this->logger->debug( 'Detected recurring payment. Adding cancel button.', [ SubscriptionRepository::TRANSACTION_ID => $transaction[ BaseRepository::ID ] ] );
				$subscription = $this->get_repository( SubscriptionRepository::class )->find_one_by(
					[
						SubscriptionRepository::VENDOR_SUBSCRIPTION_ID => $transaction[ TransactionRepository::VENDOR_SUBSCRIPTION_ID ],
					]
				);
				if ( $subscription ) {
					$this->logger->debug( 'Found subscription', [ 'subscription' => $subscription ] );
					$args['cancel_url'] = add_query_arg(
						[
							'kudos_action' => 'cancel_subscription',
							'token'        => EncryptionService::generate_token( (int) $subscription[ BaseRepository::ID ] ),
							'id'           => $subscription[ BaseRepository::ID ],
						],
						apply_filters( 'kudos_cancel_subscription_url', get_home_url() )
					);
				}
			}
		} catch ( \Exception $e ) {
			$this->logger->error( 'Error adding cancel button', [ 'message' => $e->getMessage() ] );
		}

		$this->logger->debug(
			'Creating receipt email.',
			array_merge(
				[
					'email' => $email,
					$args,
				]
			)
		);

		return $this->vendor->send_receipt( $email, $args );
	}


	/**
	 * {@inheritDoc}
	 */
	public static function get_settings(): array {
		return [
			self::SETTING_EMAIL_VENDOR         => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => 'smtp',
			],
			self::SETTING_EMAIL_RECEIPT_ENABLE => [
				'type'              => FieldType::BOOLEAN,
				'show_in_rest'      => true,
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			self::SETTING_EMAIL_SHOW_CAMPAIGN  => [
				'type'              => FieldType::BOOLEAN,
				'default'           => false,
				'show_in_rest'      => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
		];
	}
}
