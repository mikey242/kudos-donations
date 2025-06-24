<?php
/**
 * Transaction repository.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Repository;

use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Utils;

class TransactionRepository extends BaseRepository {

	/**
	 * Field constants.
	 */
	public const TABLE_NAME = 'kudos_transactions';

	/**
	 * Field constants.
	 */
	public const VALUE                  = 'value';
	public const CURRENCY               = 'currency';
	public const STATUS                 = 'status';
	public const METHOD                 = 'method';
	public const MODE                   = 'mode';
	public const SEQUENCE_TYPE          = 'sequence_type';
	public const DONOR_ID               = 'donor_id';
	public const VENDOR_PAYMENT_ID      = 'vendor_payment_id';
	public const CAMPAIGN_ID            = 'campaign_id';
	public const REFUNDS                = 'refunds';
	public const MESSAGE                = 'message';
	public const VENDOR                 = 'vendor';
	public const VENDOR_CUSTOMER_ID     = 'vendor_customer_id';
	public const VENDOR_SUBSCRIPTION_ID = 'vendor_subscription_id';
	public const INVOICE_NUMBER         = 'invoice_number';
	public const CHECKOUT_URL           = 'checkout_url';

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
	public function get_column_schema(): array {
		return [
			self::VALUE                  => $this->make_schema_field( FieldType::NUMBER, null, [ Utils::class, 'sanitize_float' ] ),
			self::CURRENCY               => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::STATUS                 => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::METHOD                 => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::MODE                   => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::SEQUENCE_TYPE          => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::DONOR_ID               => $this->make_schema_field( FieldType::INTEGER, null, 'absint' ),
			self::VENDOR_PAYMENT_ID      => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::CAMPAIGN_ID            => $this->make_schema_field( FieldType::INTEGER, null, 'absint' ),
			self::REFUNDS                => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::MESSAGE                => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::VENDOR                 => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::VENDOR_CUSTOMER_ID     => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::VENDOR_SUBSCRIPTION_ID => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::INVOICE_NUMBER         => $this->make_schema_field( FieldType::INTEGER, null, 'absint' ),
			self::CHECKOUT_URL           => $this->make_schema_field( FieldType::STRING, null, 'sanitize_url' ),
		];
	}

	/**
	 * Returns linked donor.
	 *
	 * @param array $transaction The subscription array.
	 * @param array $columns The list of columns to return.
	 */
	public function get_donor( array $transaction, array $columns = [ '*' ] ): ?array {
		$donor_id = $transaction[ self::DONOR_ID ] ?? null;
		if ( ! $donor_id ) {
			return null;
		}
		return $this->repository_manager->get( DonorRepository::class )->find( (int) $donor_id, $columns );
	}

	/**
	 * Returns linked campaign.
	 *
	 * @param array $transaction The subscription array.
	 * @param array $columns The list of columns to return.
	 */
	public function get_campaign( array $transaction, array $columns = [ '*' ] ): ?array {
		$campaign_id = $transaction[ self::CAMPAIGN_ID ] ?? null;
		if ( ! $campaign_id ) {
			return null;
		}
		return $this->repository_manager->get( CampaignRepository::class )->find( (int) $campaign_id, $columns );
	}

	/**
	 * Returns linked campaign.
	 *
	 * @param array $transaction The subscription array.
	 * @param array $columns The list of columns to return.
	 */
	public function get_subscription( array $transaction, array $columns = [ '*' ] ): ?array {
		$vendor_subscription_id = $transaction[ self::VENDOR_SUBSCRIPTION_ID ] ?? null;
		if ( ! $vendor_subscription_id ) {
			return null;
		}
		return $this->repository_manager->get( SubscriptionRepository::class )->find_one_by( [ SubscriptionRepository::VENDOR_SUBSCRIPTION_ID => $vendor_subscription_id ], $columns );
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
}
