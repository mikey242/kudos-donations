<?php
/**
 * SubscriptionRepository.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Repository;

use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Entity\DonorEntity;
use IseardMedia\Kudos\Domain\Entity\SubscriptionEntity;
use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Domain\Table\SubscriptionsTable;

/**
 * @extends BaseRepository<SubscriptionEntity>
 */
class SubscriptionRepository extends BaseRepository {

	/**
	 * {@inheritDoc}
	 */
	public static function get_table_name(): string {
		return SubscriptionsTable::get_name();
	}

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
	 * Returns linked transaction.
	 *
	 * @param SubscriptionEntity $subscription The subscription array.
	 */
	public function get_transaction( SubscriptionEntity $subscription ): ?TransactionEntity {
		/** @var TransactionEntity $transaction */
		$transaction = $this->get_repository( TransactionRepository::class )
			->find_one_by( [ 'subscription_id' => $subscription->id ] );
		return $transaction;
	}

	/**
	 * Returns linked donor.
	 *
	 * @param SubscriptionEntity $subscription The subscription array.
	 */
	public function get_donor( SubscriptionEntity $subscription ): ?DonorEntity {
		$donor_id = $subscription->donor_id ?? null;
		if ( null === $donor_id ) {
			return null;
		}
		/** @var DonorEntity $donor */
		$donor = $this->get_repository( DonorRepository::class )
			->get( $donor_id );
		return $donor;
	}

	/**
	 * Returns campaign.
	 *
	 * @param SubscriptionEntity $subscription The subscription array.
	 */
	public function get_campaign( SubscriptionEntity $subscription ): ?CampaignEntity {
		$campaign_id = $subscription->campaign_id;

		// Fallback: get campaign id from linked transaction.
		if ( null === $campaign_id ) {
			$transaction = $this->get_transaction( $subscription );

			if ( ! $transaction ) {
				return null;
			}

			$campaign_id = $transaction->campaign_id;
		}

		if ( null === $campaign_id ) {
			return null;
		}

		/** @var CampaignEntity $campaign */
		$campaign = $this->get_repository( CampaignRepository::class )
			->get( $campaign_id );
		return $campaign;
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
