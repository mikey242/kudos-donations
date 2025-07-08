<?php
/**
 * Transaction repository.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Repository;

use IseardMedia\Kudos\Entity\CampaignEntity;
use IseardMedia\Kudos\Entity\DonorEntity;
use IseardMedia\Kudos\Entity\SubscriptionEntity;
use IseardMedia\Kudos\Entity\TransactionEntity;
use IseardMedia\Kudos\Enum\FieldType;

class TransactionRepository extends BaseRepository {

	use SanitizeTrait;

	public const TABLE_NAME = 'kudos_transactions';

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
	 * {@inheritDoc}
	 */
	public function get_additional_column_schema(): array {
		return [
			'value'             => $this->make_schema_field( FieldType::FLOAT, [ $this, 'sanitize_float' ] ),
			'currency'          => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'status'            => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'method'            => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'mode'              => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'sequence_type'     => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'donor_id'          => $this->make_schema_field( FieldType::INTEGER, 'absint' ),
			'campaign_id'       => $this->make_schema_field( FieldType::INTEGER, 'absint' ),
			'subscription_id'   => $this->make_schema_field( FieldType::INTEGER, 'absint' ),
			'refunds'           => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'message'           => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'vendor'            => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'invoice_number'    => $this->make_schema_field( FieldType::INTEGER, 'absint' ),
			'checkout_url'      => $this->make_schema_field( FieldType::STRING, 'sanitize_url' ),
			'vendor_payment_id' => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
		];
	}

	/**
	 * Returns linked donor.
	 *
	 * @param TransactionEntity $transaction The subscription array.
	 * @param array             $columns The list of columns to return.
	 */
	public function get_donor( TransactionEntity $transaction, array $columns = [ '*' ] ): ?DonorEntity {
		$donor_id = $transaction->donor_id ?? null;
		if ( ! $donor_id ) {
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
		if ( ! $campaign_id ) {
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
		if ( ! $subscription_id ) {
			return null;
		}

		return $this->get_repository( SubscriptionRepository::class )
					->get( $subscription_id, $columns );
	}

	/**
	 * Get total paid transaction value for a given campaign.
	 *
	 * @param int $campaign_id The campaign entity id.
	 */
	public function get_total_by_campaign_id( int $campaign_id ): float {
		return $this->get_total_by( 'campaign_id', $campaign_id );
	}

	/**
	 * Get total by donor id.
	 *
	 * @param int $donor_id The donor entity id.
	 */
	public function get_total_by_donor_id( int $donor_id ): float {
		return $this->get_total_by( 'donor_id', $donor_id );
	}

	/**
	 * Base method for returning total value of donations.
	 *
	 * @param string $column The column to filter by.
	 * @param mixed  $value The value of the column.
	 *
	 *  phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
	 *  phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	 */
	protected function get_total_by( string $column, $value ): float {
		if ( ! preg_match( '/^[a-zA-Z0-9_]+$/', $column ) ) {
			return 0.0;
		}

		$column_esc = esc_sql( $column );

		$sql = $this->wpdb->prepare(
			"SELECT SUM(value) FROM $this->table WHERE $column_esc = %s AND status = %s",
			$value,
			'paid'
		);

		return (float) ( $this->wpdb->get_var( $sql ) ?? 0 );
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
