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
class DonorPostType extends AbstractCustomPostType implements HasMetaFieldsInterface {

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug(): string {
		return 'kudos_donor';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_description(): string {
		return 'Kudos DonorPostType';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_singular_name(): string {
		return _x( 'DonorPostType', 'DonorPostType post type singular name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_plural_name(): string {
		return _x( 'Donors', 'DonorPostType post type plural name', 'kudos-donations' );
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
			'email'                  => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_email',
			],
			'mode'             => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'name'      => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'business_name'         => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'street'   => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'postcode'       => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'city'      => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'country'       => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'customer_id'       => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}
}