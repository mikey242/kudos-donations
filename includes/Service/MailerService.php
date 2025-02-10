<?php
/**
 * Mailer service.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Service;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Domain\PostType\DonorPostType;
use IseardMedia\Kudos\Domain\PostType\SubscriptionPostType;
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Vendor\EmailVendor\EmailVendorFactory;
use IseardMedia\Kudos\Vendor\EmailVendor\EmailVendorInterface;

class MailerService extends AbstractRegistrable implements HasSettingsInterface {

	public const SETTING_EMAIL_VENDOR         = '_kudos_email_vendor';
	public const SETTING_EMAIL_RECEIPT_ENABLE = '_kudos_email_receipt_enable';
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
	 * @param int $donor_id Donor id.
	 * @param int $transaction_id Transaction id.
	 */
	public function send_receipt( int $donor_id, int $transaction_id ): bool {
		// Check if setting enabled.
		if ( ! get_option( self::SETTING_EMAIL_RECEIPT_ENABLE ) ) {
			return false;
		}

		// Assign attachment.
		$attachments = apply_filters( 'kudos_receipt_attachment', [], $transaction_id );

		// Get posts.
		$donor       = get_post( $donor_id );
		$transaction = get_post( $transaction_id );

		// Email address.
		$email = $donor->{DonorPostType::META_FIELD_EMAIL};

		// Create array of variables for use in twig template.
		$args = [
			'name'        => $donor->{DonorPostType::META_FIELD_NAME} ?? '',
			'date'        => $transaction->post_date,
			'description' => $transaction->post_title,
			'amount'      => ( ! empty( $transaction->{TransactionPostType::META_FIELD_CURRENCY} ) ? html_entity_decode(
				Utils::get_currencies()[ $transaction->{TransactionPostType::META_FIELD_CURRENCY} ]
			) : '' ) . number_format_i18n(
				$transaction->{TransactionPostType::META_FIELD_VALUE},
				2
			),
			'receipt_id'  => Utils::get_formatted_id( $transaction_id ),
		];

		// Add a cancel button if this is the receipt for a subscription payment.
		try {
			if ( 'oneoff' !== $transaction->{TransactionPostType::META_FIELD_SEQUENCE_TYPE} ) {
				$subscription = SubscriptionPostType::get_post(
					[
						SubscriptionPostType::META_FIELD_VENDOR_SUBSCRIPTION_ID => $transaction->{TransactionPostType::META_FIELD_VENDOR_SUBSCRIPTION_ID},
					]
				);
				$this->logger->debug( 'Detected recurring payment. Adding cancel button.', [ SubscriptionPostType::META_FIELD_TRANSACTION_ID => $transaction_id ] );
				$args['cancel_url'] = add_query_arg(
					[
						'kudos_action' => 'cancel_subscription',
						'token'        => EncryptionService::generate_token( $subscription->ID ),
						'id'           => $subscription->ID,
					],
					apply_filters( 'kudos_cancel_subscription_url', get_home_url() )
				);
			}
		} catch ( \Exception $e ) {
			$this->logger->error( 'Error adding cancel button: ' . $e->getMessage() );
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
		];
	}
}
