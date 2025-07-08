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
use IseardMedia\Kudos\Entity\CampaignEntity;
use IseardMedia\Kudos\Entity\DonorEntity;
use IseardMedia\Kudos\Entity\TransactionEntity;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Utils;
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

		/** @var TransactionEntity $transaction */
		$transaction = $this->get_repository( TransactionRepository::class )
							->get( $transaction_id );
		/** @var DonorEntity $donor */
		$donor = $this->get_repository( TransactionRepository::class )
						->get_donor( $transaction );

		// Email address.
		$email = $donor->email;

		// Switch to donor's locale.
		$locale = $donor->locale;
		if ( $locale ) {
			$this->logger->debug( "Switching locale to $locale" );
			Utils::switch_locale( $locale );
		}

		// Bail if no email address.
		if ( ! $email ) {
			$this->logger->debug( 'Cannot send email: donor has no email address', [ 'donor' => $donor ] );
			return false;
		}

		// Assign attachment.
		$attachments = apply_filters( 'kudos_receipt_attachment', [], $transaction->id );

		// Get campaign name if enabled.
		$campaign_name = '';
		if ( get_option( self::SETTING_EMAIL_SHOW_CAMPAIGN ) ) {
			/** @var CampaignEntity $campaign */
			$campaign      = $this->get_repository( TransactionRepository::class )
									->get_campaign( $transaction );
			$campaign_name = $campaign->title;
		}

		// Create array of variables for use in twig template.
		$args = [
			'name'          => $donor->name ?? '',
			'date'          => $transaction->created_at,
			'description'   => $transaction->title,
			'amount'        => ( ! empty( $transaction->currency ) ? html_entity_decode(
				Utils::get_currencies()[ $transaction->currency ]
			) : '' ) . number_format_i18n(
				$transaction->value,
				2
			),
			'receipt_id'    => Utils::get_id( $transaction, TransactionRepository::get_singular_name() ),
			'campaign_name' => $campaign_name,
			'attachments'   => $attachments,
		];

		// Add a cancel button if this is the receipt for a subscription payment.
		try {
			if ( 'oneoff' !== $transaction->sequence_type ) {
				$this->logger->debug( 'Detected recurring payment. Adding cancel button.', [ 'transaction_id' => $transaction->id ] );
				$subscription = $this->get_repository( SubscriptionRepository::class )->find_one_by(
					[
						'transaction_id' => $transaction->id,
					]
				);
				if ( $subscription ) {
					$this->logger->debug( 'Found subscription', [ 'subscription' => $subscription ] );
					$args['cancel_url'] = add_query_arg(
						[
							'kudos_action' => 'cancel_subscription',
							'token'        => EncryptionService::generate_token( $subscription->id ),
							'id'           => $subscription->id,
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

		$result = $this->vendor->send_receipt( $email, $args );
		restore_previous_locale();
		return $result;
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
