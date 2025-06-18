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
	public const VENDOR_CUSTOMER_ID     = 'vendor_customer_id';
	public const VENDOR_SUBSCRIPTION_ID = 'vendor_subscription_id';

	/**
	 * {@inheritDoc}
	 */
	public function get_column_schema(): array {
		return [
			self::VALUE                  => $this->make_schema_field( FieldType::NUMBER, null, 'floatval' ),
			self::CURRENCY               => $this->make_schema_field( FieldType::STRING, 'EUR', 'sanitize_text_field' ),
			self::FREQUENCY              => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::YEARS                  => $this->make_schema_field( FieldType::INTEGER, null, 'absint' ),
			self::STATUS                 => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::TRANSACTION_ID         => $this->make_schema_field( FieldType::INTEGER, null, 'absint' ),
			self::DONOR_ID               => $this->make_schema_field( FieldType::INTEGER, null, 'absint' ),
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
		$transaction_repository = $this->get_repository_manager()->get( TransactionRepository::class );
		return $transaction_repository->find( (int) $transaction_id );
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

		$donor_repository = $this->get_repository_manager()->get( DonorRepository::class );
		return $donor_repository->find( (int) $donor_id );
	}

	/**
	 * Returns campaign.
	 *
	 * @param array $subscription The subscription array.
	 */
	public function get_campaign( array $subscription ): ?array {
		$transaction = $this->get_transaction( $subscription );
		if ( ! $transaction ) {
			return null;
		}

		$campaign_repository = $this->get_repository_manager()->get( CampaignRepository::class );
		return $campaign_repository->find( (int) $transaction[ TransactionRepository::CAMPAIGN_ID ] );
	}
}
