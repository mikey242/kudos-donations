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
class CampaignPostType extends AbstractCustomPostType implements HasMetaFieldsInterface {

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug(): string {
		return 'kudos_campaign';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_description(): string {
		return 'Kudos Campaign';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_singular_name(): string {
		return _x( 'Campaign', 'Campaign post type singular name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_plural_name(): string {
		return _x( 'Campaigns', 'Campaign post type plural name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_args(): array {
		return array_merge(
			parent::get_args(),
			[
				'has_archive'         => true,
				'exclude_from_search' => false,
				'capability_type'     => 'page',
			]
		);
	}

	public function get_meta_fields(): array {
		return [
			'goal'                  => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'show_goal'             => [
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'additional_funds'      => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'initial_title'         => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'initial_description'   => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'address_enabled'       => [
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'address_required'      => [
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'message_enabled'       => [
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'amount_type'           => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'fixed_amounts'         => [
				'type'              => 'string',
				'single'            => false,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'minimum_donation'      => [
				'type'              => 'integer',
				'sanitize_callback' => 'sanitize_float',
			],
			'donation_type'         => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'theme_color'           => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'terms_link'            => [
				'type'              => 'string',
				'sanitize_callback' => 'esc_url_raw',
			],
			'privacy_link'          => [
				'type'              => 'string',
				'sanitize_callback' => 'esc_url_raw',
			],
			'show_return_message'   => [
				'type' => 'boolean',
			],
			'use_custom_return_url' => [
				'type' => 'boolean',
			],
			'custom_return_url'     => [
				'type' => 'string',
			],
			'return_message_title'  => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'return_message_text'   => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}
}