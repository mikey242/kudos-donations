<?php
/**
 * Subscription Post Type.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\PostType;

use IseardMedia\Kudos\Domain\HasMetaFieldsInterface;
use IseardMedia\Kudos\Enum\FieldType;

class SubscriptionPostType extends AbstractCustomPostType implements HasMetaFieldsInterface {

	/**
	 * Meta field constants.
	 */
	public const META_FIELD_VALUE           = 'value';
	public const META_FIELD_CURRENCY        = 'currency';
	public const META_FIELD_FREQUENCY       = 'frequency';
	public const META_FIELD_YEARS           = 'years';
	public const META_FIELD_STATUS          = 'status';
	public const META_FIELD_CUSTOMER_ID     = 'customer_id';
	public const META_FIELD_TRANSACTION_ID  = 'transaction_id';
	public const META_FIELD_SUBSCRIPTION_ID = 'subscription_id';

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug(): string {
		return 'kudos_subscription';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_description(): string {
		return 'Kudos Subscription';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_singular_name(): string {
		return _x( 'Subscription', 'Subscription post type singular name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_plural_name(): string {
		return _x( 'Subscriptions', 'Subscription post type plural name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_meta_config(): array {
		return [
			self::META_FIELD_VALUE           => [
				'type'              => FieldType::INTEGER,
				'sanitize_callback' => 'absint',
			],
			self::META_FIELD_CURRENCY        => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_FREQUENCY       => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_YEARS           => [
				'type'              => FieldType::INTEGER,
				'sanitize_callback' => 'absint',
			],
			self::META_FIELD_STATUS          => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_CUSTOMER_ID     => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_TRANSACTION_ID  => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_SUBSCRIPTION_ID => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}
}
