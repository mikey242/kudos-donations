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
			'value'           => [
				'type'              => FieldType::INTEGER,
				'sanitize_callback' => 'absint',
			],
			'currency'        => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'frequency'       => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'years'           => [
				'type'              => FieldType::INTEGER,
				'sanitize_callback' => 'absint',
			],
			'status'          => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'customer_id'     => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'transaction_id'  => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'subscription_id' => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}
}
