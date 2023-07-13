<?php
/**
 * CampaignPostType Post Type
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\PostType;

use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Infrastructure\Domain\AbstractCustomPostType;
use IseardMedia\Kudos\Infrastructure\Domain\HasMetaFieldsInterface;

class CampaignPostType extends AbstractCustomPostType implements HasMetaFieldsInterface {

	protected const SUPPORTS     = [ 'title', 'custom-fields' ];
	protected const SHOW_IN_REST = true;
	protected const CAPABILITIES = [ 'create_posts' => true ];

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
	public static function get_meta_config(): array {
		return [
			'goal'                  => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'show_goal'             => [
				'type'              => FieldType::BOOLEAN,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'additional_funds'      => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'initial_title'         => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'initial_description'   => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'address_enabled'       => [
				'type'              => FieldType::BOOLEAN,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'address_required'      => [
				'type'              => FieldType::BOOLEAN,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'message_enabled'       => [
				'type'              => FieldType::BOOLEAN,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'amount_type'           => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'fixed_amounts'         => [
				'type'              => FieldType::STRING,
				'single'            => false,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'minimum_donation'      => [
				'type'              => FieldType::INTEGER,
				'sanitize_callback' => 'sanitize_float',
			],
			'donation_type'         => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'theme_color'           => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'terms_link'            => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'esc_url_raw',
			],
			'privacy_link'          => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'esc_url_raw',
			],
			'show_return_message'   => [
				'type'              => FieldType::BOOLEAN,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'use_custom_return_url' => [
				'type'              => FieldType::BOOLEAN,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'custom_return_url'     => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'return_message_title'  => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'return_message_text'   => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}
}
