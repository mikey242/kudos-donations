<?php
/**
 * Transaction repository.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Domain\Repository;

use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Entity\DonorEntity;
use IseardMedia\Kudos\Domain\Entity\SubscriptionEntity;
use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Domain\Table\TransactionsTable;

/**
 * @extends BaseRepository<TransactionEntity>
 */
class TransactionRepository extends BaseRepository {

	/**
	 * {@inheritDoc}
	 */
	public static function get_table_name(): string {
		return TransactionsTable::get_name();
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_singular_name(): string {
		return _x( 'Transaction', 'Transaction post type singular name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_plural_name(): string {
		return _x( 'Transactions', 'Transaction post type plural name', 'kudos-donations' );
	}

	/**
	 * Returns linked donor.
	 *
	 * @param TransactionEntity $transaction The subscription array.
	 * @param array             $columns The list of columns to return.
	 */
	public function get_donor( TransactionEntity $transaction, array $columns = [ '*' ] ): ?DonorEntity {
		$donor_id = $transaction->donor_id ?? null;
		if ( null === $donor_id ) {
			return null;
		}

		return $this->repository_manager->get( DonorRepository::class )
										->get( $donor_id, $columns );
	}

	/**
	 * Returns linked campaign.
	 *
	 * @param TransactionEntity $transaction The subscription array.
	 * @param array             $columns The list of columns to return.
	 */
	public function get_campaign( TransactionEntity $transaction, array $columns = [ '*' ] ): ?CampaignEntity {
		$campaign_id = $transaction->campaign_id ?? null;
		if ( null === $campaign_id ) {
			return null;
		}

		return $this->get_repository( CampaignRepository::class )
					->get( $campaign_id, $columns );
	}

	/**
	 * Returns linked campaign.
	 *
	 * @param TransactionEntity $transaction The transaction array.
	 * @param array             $columns The list of columns to return.
	 */
	public function get_subscription( TransactionEntity $transaction, array $columns = [ '*' ] ): ?SubscriptionEntity {
		$subscription_id = $transaction->subscription_id;
		if ( null === $subscription_id ) {
			return null;
		}

		return $this->get_repository( SubscriptionRepository::class )->get( $subscription_id, $columns );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return class-string<TransactionEntity>
	 */
	protected function get_entity_class(): string {
		return TransactionEntity::class;
	}
}
