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
	public const CUSTOMER_ID            = 'customer_id';
	public const TRANSACTION_ID         = 'transaction_id';
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
			self::CUSTOMER_ID            => $this->make_schema_field( FieldType::STRING, null, 'absint' ),
			self::TRANSACTION_ID         => $this->make_schema_field( FieldType::INTEGER, null, 'absint' ),
			self::VENDOR_SUBSCRIPTION_ID => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
		];
	}
}
