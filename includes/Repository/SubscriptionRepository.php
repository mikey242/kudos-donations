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

use IseardMedia\Kudos\Entity\CampaignEntity;
use IseardMedia\Kudos\Entity\DonorEntity;
use IseardMedia\Kudos\Entity\SubscriptionEntity;
use IseardMedia\Kudos\Entity\TransactionEntity;
use IseardMedia\Kudos\Enum\FieldType;

class SubscriptionRepository extends BaseRepository {

	public const TABLE_NAME = 'kudos_subscriptions';

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
			'value'                  => $this->make_schema_field( FieldType::FLOAT, [ $this, 'sanitize_float' ] ),
			'currency'               => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'frequency'              => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'years'                  => $this->make_schema_field( FieldType::INTEGER, 'absint' ),
			'status'                 => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'transaction_id'         => $this->make_schema_field( FieldType::INTEGER, 'absint' ),
			'donor_id'               => $this->make_schema_field( FieldType::INTEGER, 'absint' ),
			'campaign_id'            => $this->make_schema_field( FieldType::INTEGER, 'absint' ),
			'vendor_customer_id'     => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'vendor_subscription_id' => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
		];
	}

	/**
	 * Returns linked transaction.
	 *
	 * @param SubscriptionEntity $subscription The subscription array.
	 */
	public function get_transaction( SubscriptionEntity $subscription ): ?TransactionEntity {
		$transaction_id = $subscription->transaction_id ?? null;
		if ( ! $transaction_id ) {
			return null;
		}
		return $this->get_repository( TransactionRepository::class )
			->get( $transaction_id );
	}

	/**
	 * Returns linked donor.
	 *
	 * @param SubscriptionEntity $subscription The subscription array.
	 */
	public function get_donor( SubscriptionEntity $subscription ): ?DonorEntity {
		$donor_id = $subscription->donor_id ?? null;
		if ( ! $donor_id ) {
			return null;
		}
		return $this->get_repository( DonorRepository::class )
			->get( $donor_id );
	}

	/**
	 * Returns campaign.
	 *
	 * @param SubscriptionEntity $subscription The subscription array.
	 */
	public function get_campaign( SubscriptionEntity $subscription ): ?CampaignEntity {
		$campaign_id = $subscription->campaign_id;

		// Fallback: get campaign id from linked transaction.
		if ( ! $campaign_id ) {
			$transaction = $this->get_transaction( $subscription );

			if ( ! $transaction ) {
				return null;
			}

			$campaign_id = $transaction->campaign_id;
		}

		if ( ! $campaign_id ) {
			return null;
		}

		return $this->get_repository( CampaignRepository::class )
			->get( $campaign_id );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return class-string<SubscriptionEntity>
	 */
	protected function get_entity_class(): string {
		return SubscriptionEntity::class;
	}
}
