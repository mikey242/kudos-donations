<?php
/**
 * SubscriptionRepository.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Repository;

use IseardMedia\Kudos\Enum\FieldType;

class SubscriptionRepository extends BaseRepository {

	/**
	 * Table name.
	 */
	public const TABLE_NAME = 'kudos_subscriptions';

	/**
	 * Field constants.
	 */
	public const VALUE                  = 'value';
	public const CURRENCY               = 'currency';
	public const FREQUENCY              = 'frequency';
	public const YEARS                  = 'years';
	public const STATUS                 = 'status';
	public const TRANSACTION_ID         = 'transaction_id';
	public const DONOR_ID               = 'donor_id';
	public const CAMPAIGN_ID            = 'campaign_id';
	public const VENDOR_CUSTOMER_ID     = 'vendor_customer_id';
	public const VENDOR_SUBSCRIPTION_ID = 'vendor_subscription_id';

	/**
	 * {@inheritDoc}
	 */
	public static function get_singular_name(): string {
		return _x( 'Subscription', 'Subscription post type singular name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_plural_name(): string {
		return _x( 'Subscriptions', 'Subscription post type plural name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_additional_column_schema(): array {
		return [
			self::VALUE                  => $this->make_schema_field( FieldType::FLOAT, null, [ Utils::class, 'sanitize_float' ] ),
			self::CURRENCY               => $this->make_schema_field( FieldType::STRING, 'EUR', 'sanitize_text_field' ),
			self::FREQUENCY              => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::YEARS                  => $this->make_schema_field( FieldType::INTEGER, null, 'absint' ),
			self::STATUS                 => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::TRANSACTION_ID         => $this->make_schema_field( FieldType::INTEGER, null, 'absint' ),
			self::DONOR_ID               => $this->make_schema_field( FieldType::INTEGER, null, 'absint' ),
			self::CAMPAIGN_ID            => $this->make_schema_field( FieldType::INTEGER, null, 'absint' ),
			self::VENDOR_CUSTOMER_ID     => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::VENDOR_SUBSCRIPTION_ID => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
		];
	}

	/**
	 * Returns linked transaction.
	 *
	 * @param array $subscription The subscription array.
	 */
	public function get_transaction( array $subscription ): ?array {
		$transaction_id = $subscription[ self::TRANSACTION_ID ] ?? null;
		if ( ! $transaction_id ) {
			return null;
		}
		return $this->get_repository( TransactionRepository::class )
			->find( (int) $transaction_id );
	}

	/**
	 * Returns linked donor.
	 *
	 * @param array $subscription The subscription array.
	 */
	public function get_donor( array $subscription ): ?array {
		$donor_id = $subscription[ self::DONOR_ID ] ?? null;
		if ( ! $donor_id ) {
			return null;
		}
		return $this->get_repository( DonorRepository::class )
			->find( (int) $donor_id );
	}

	/**
	 * Returns campaign.
	 *
	 * @param array $subscription The subscription array.
	 */
	public function get_campaign( array $subscription ): ?array {
		$campaign_id = $subscription[ self::CAMPAIGN_ID ];

		// Fallback: get campaign id from linked transaction.
		if ( ! $campaign_id ) {
			$transaction = $this->get_transaction( $subscription );

			if ( ! $transaction ) {
				return null;
			}

			$campaign_id = $transaction[ TransactionRepository::CAMPAIGN_ID ];
		}

		return $this->get_repository( CampaignRepository::class )
			->find( (int) $campaign_id );
	}
}
