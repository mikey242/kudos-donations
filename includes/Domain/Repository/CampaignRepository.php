<?php
/**
 * Campaign repository.
 *
 * @link https://code.iseard.media/michael/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Repository;

use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Table\CampaignsTable;
use IseardMedia\Kudos\Enum\PaymentStatus;
use IseardMedia\Kudos\Service\SettingsService;

/**
 * @extends BaseRepository<CampaignEntity>
 */
class CampaignRepository extends BaseRepository {

	/**
	 * Set once the first campaign has been created (or was found unnecessary), so it is
	 * never recreated after the user deletes it or reactivates the plugin.
	 */
	public const SETTING_FIRST_CAMPAIGN_CREATED = '_kudos_first_campaign_created';

	/**
	 * Creates a ready-to-use campaign on a fresh install, so new users have a working form to
	 * place straight away. Runs at most once per site, and only while onboarding is active
	 * and there are no campaigns yet.
	 *
	 * @return int|null The new campaign's ID, or null when none was created.
	 */
	public function create_first_campaign(): ?int {
		if ( get_option( self::SETTING_FIRST_CAMPAIGN_CREATED ) || ! SettingsService::is_onboarding_active() ) {
			return null;
		}

		update_option( self::SETTING_FIRST_CAMPAIGN_CREATED, true, false );

		if ( $this->count_query() > 0 ) {
			return null;
		}

		$id = $this->insert(
			new CampaignEntity( [ 'title' => __( 'My first campaign', 'kudos-donations' ) ] )
		);

		return $id ? (int) $id : null;
	}

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
		$campaign_id = $campaign->id;
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
		$additional_funds = $campaign->additional_funds ?? 0.0;
		$total            = $this->get_repository( TransactionRepository::class )
			->sum_query(
				'value',
				[
					'campaign_id' => $campaign->id,
					'status'      => PaymentStatus::PAID,
				]
			);
		return $total + $additional_funds;
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
