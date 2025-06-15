<?php
/**
 * Campaign repository.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Repository;

use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Lifecycle\SchemaInstaller;

class CampaignRepository extends BaseRepository {

	/**
	 * {@inheritDoc}
	 */
	protected function get_table_name(): string {
		return SchemaInstaller::TABLE_CAMPAIGNS;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_column_schema(): array {
		return [
			'id'                         => [
				'type'              => FieldType::INTEGER,
				'default'           => null,
				'sanitize_callback' => 'absint',
			],
			'wp_post_id'                 => [
				'type'              => FieldType::INTEGER,
				'default'           => null,
				'sanitize_callback' => 'absint',
			],
			'title'                      => [
				'type'              => FieldType::STRING,
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'currency'                   => [
				'type'              => FieldType::STRING,
				'default'           => 'EUR',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'goal'                       => [
				'type'              => FieldType::FLOAT,
				'default'           => null,
				'sanitize_callback' => 'floatval',
			],
			'show_goal'                  => [
				'type'              => FieldType::BOOLEAN,
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'additional_funds'           => [
				'type'              => FieldType::FLOAT,
				'default'           => null,
				'sanitize_callback' => 'floatval',
			],
			'amount_type'                => [
				'type'              => FieldType::STRING,
				'default'           => 'fixed',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'fixed_amounts'              => [
				'type'              => FieldType::OBJECT,
				'default'           => [],
				'sanitize_callback' => [ $this, 'sanitize_json_field' ],
			],
			'minimum_donation'           => [
				'type'              => FieldType::FLOAT,
				'default'           => null,
				'sanitize_callback' => 'floatval',
			],
			'maximum_donation'           => [
				'type'              => FieldType::FLOAT,
				'default'           => null,
				'sanitize_callback' => 'floatval',
			],
			'donation_type'              => [
				'type'              => FieldType::STRING,
				'default'           => 'oneoff',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'frequency_options'          => [
				'type'              => FieldType::OBJECT,
				'default'           => [],
				'sanitize_callback' => [ $this, 'sanitize_json_field' ],
			],
			'email_enabled'              => [
				'type'              => FieldType::BOOLEAN,
				'default'           => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'email_required'             => [
				'type'              => FieldType::BOOLEAN,
				'default'           => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'name_enabled'               => [
				'type'              => FieldType::BOOLEAN,
				'default'           => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'name_required'              => [
				'type'              => FieldType::BOOLEAN,
				'default'           => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'address_enabled'            => [
				'type'              => FieldType::BOOLEAN,
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'address_required'           => [
				'type'              => FieldType::BOOLEAN,
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'message_enabled'            => [
				'type'              => FieldType::BOOLEAN,
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'message_required'           => [
				'type'              => FieldType::BOOLEAN,
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'theme_color'                => [
				'type'              => FieldType::STRING,
				'default'           => '#ff9f1c',
				'sanitize_callback' => 'sanitize_hex_color',
			],
			'terms_link'                 => [
				'type'              => FieldType::STRING,
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			],
			'privacy_link'               => [
				'type'              => FieldType::STRING,
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			],
			'show_return_message'        => [
				'type'              => FieldType::BOOLEAN,
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'use_custom_return_url'      => [
				'type'              => FieldType::BOOLEAN,
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'custom_return_url'          => [
				'type'              => FieldType::STRING,
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			],
			'payment_description_format' => [
				'type'              => FieldType::STRING,
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'custom_styles'              => [
				'type'              => FieldType::STRING,
				'default'           => '',
				'sanitize_callback' => 'sanitize_textarea_field',
			],
			'initial_title'              => [
				'type'              => FieldType::STRING,
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'initial_description'        => [
				'type'              => FieldType::STRING,
				'default'           => '',
				'sanitize_callback' => 'sanitize_textarea_field',
			],
			'subscription_title'         => [
				'type'              => FieldType::STRING,
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'subscription_description'   => [
				'type'              => FieldType::STRING,
				'default'           => '',
				'sanitize_callback' => 'sanitize_textarea_field',
			],
			'address_title'              => [
				'type'              => FieldType::STRING,
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'address_description'        => [
				'type'              => FieldType::STRING,
				'default'           => '',
				'sanitize_callback' => 'sanitize_textarea_field',
			],
			'message_title'              => [
				'type'              => FieldType::STRING,
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'message_description'        => [
				'type'              => FieldType::STRING,
				'default'           => '',
				'sanitize_callback' => 'sanitize_textarea_field',
			],
			'payment_title'              => [
				'type'              => FieldType::STRING,
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'payment_description'        => [
				'type'              => FieldType::STRING,
				'default'           => '',
				'sanitize_callback' => 'sanitize_textarea_field',
			],
			'return_message_title'       => [
				'type'              => FieldType::STRING,
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'return_message_text'        => [
				'type'              => FieldType::STRING,
				'default'           => '',
				'sanitize_callback' => 'sanitize_textarea_field',
			],
			'allow_anonymous'            => [
				'type'              => FieldType::BOOLEAN,
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			'created_at'                 => [
				'type'              => FieldType::STRING,
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'updated_at'                 => [
				'type'              => FieldType::STRING,
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Get all transactions linked to a specific campaign.
	 *
	 * @param int   $campaign_id The ID of the campaign in the custom table.
	 * @param array $filters     Optional filters (e.g., ['status' => 'paid']).
	 * @return array List of matching transactions.
	 */
	public function get_transactions( int $campaign_id, array $filters = [] ): array {
		$transaction_table = $this->wpdb->table( 'kudos_transactions' );

		$where_clauses = [ '`campaign_id` = %d' ];
		$params        = [ $campaign_id ];

		foreach ( $filters as $column => $value ) {
			// Safeguard against unsafe column names.
			if ( ! preg_match( '/^[a-zA-Z0-9_]+$/', $column ) ) {
				continue;
			}

			$placeholder     = \is_int( $value ) ? '%d' : '%s';
			$where_clauses[] = "`$column` = $placeholder";
			$params[]        = $value;
		}

		$where_sql = implode( ' AND ', $where_clauses );
		$sql       = "SELECT * FROM {$transaction_table} WHERE {$where_sql} ORDER BY created_at DESC";

		return $this->wpdb->get_results(
			$this->wpdb->prepare( $sql, ...$params ),
			ARRAY_A
		);
	}
}
