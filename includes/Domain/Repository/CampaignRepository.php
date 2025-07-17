<?php
/**
 * Campaign repository.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Repository;

use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Table\CampaignsTable;

/**
 * @extends BaseRepository<CampaignEntity>
 */
class CampaignRepository extends BaseRepository {

	/**
	 * {@inheritDoc}
	 */
	public static function get_table_name(): string {
		return CampaignsTable::get_name();
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_singular_name(): string {
		return _x( 'Campaign', 'Campaign post type singular name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_plural_name(): string {
		return _x( 'Campaigns', 'Campaign post type plural name', 'kudos-donations' );
	}

	/**
	 * Returns linked transactions.
	 *
	 * @param CampaignEntity $campaign The campaign array.
	 * @param array          $columns Columns to return.
	 */
	public function get_transactions( CampaignEntity $campaign, array $columns = [ '*' ] ): ?array {
		$campaign_id = $campaign->id ?? null;
		if ( ! $campaign_id ) {
			return null;
		}
		return $this->get_repository( TransactionRepository::class )
			->find_by( [ 'campaign_id' => $campaign_id ], $columns );
	}

	/**
	 * Returns the total donations for supplied campaign.
	 *
	 * @param CampaignEntity $campaign The campaign array.
	 *
	 * @phpcs:disable Squiz.Commenting.FunctionComment.IncorrectTypeHint
	 */
	public function get_total( CampaignEntity $campaign ): float {
		$transactions = $this->get_transactions( $campaign, [ 'status', 'value' ] );
		return array_reduce(
			$transactions,
			static fn( float $carry, $item ): float =>
			'paid' === $item->status ? $carry + (float) $item->value : $carry,
			0.0
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return class-string<CampaignEntity>
	 */
	protected function get_entity_class(): string {
		return CampaignEntity::class;
	}
}
