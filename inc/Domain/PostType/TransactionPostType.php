<?php
/**
 * CampaignPostType Post Type
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

namespace IseardMedia\Kudos\Domain\PostType;

use IseardMedia\Kudos\Infrastructure\Domain\HasMetaFieldsInterface;
use IseardMedia\Kudos\Infrastructure\Domain\PostType\AbstractCustomPostType;

/**
 * Custom PostType
 */
class TransactionPostType extends AbstractCustomPostType implements HasMetaFieldsInterface {

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug(): string {
		return 'kudos_transaction';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_description(): string {
		return 'Kudos Transaction';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_singular_name(): string {
		return _x( 'Transaction', 'Transaction post type singular name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_plural_name(): string {
		return _x( 'Transactions', 'Transaction post type plural name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_args(): array {
		return array_merge(
			parent::get_args(),
			[
				'public'              => false,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'capability_type'     => 'page',
			]
		);
	}

	public function get_meta_fields(): array {
		return [
			'value'                  => [
				'type'              => 'int',
				'sanitize_callback' => 'intval',
			],
			'currency'             => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'status'      => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'method'         => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'mode'   => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'sequence_type'       => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'transaction_id'      => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'order_id'       => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'campaign_id'       => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'refunds'       => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'message'       => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}
}