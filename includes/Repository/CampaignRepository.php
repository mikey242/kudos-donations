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
	public static function get_column_schema(): array {
		return [
			'id'                         => FieldType::INTEGER,
			'wp_post_id'                 => FieldType::INTEGER,
			'title'                      => FieldType::STRING,
			'currency'                   => FieldType::STRING,
			'goal'                       => FieldType::FLOAT,
			'show_goal'                  => FieldType::BOOLEAN,
			'additional_funds'           => FieldType::FLOAT,
			'amount_type'                => FieldType::STRING,
			'fixed_amounts'              => FieldType::OBJECT,
			'minimum_donation'           => FieldType::FLOAT,
			'maximum_donation'           => FieldType::FLOAT,
			'donation_type'              => FieldType::STRING,
			'frequency_options'          => FieldType::OBJECT,
			'email_enabled'              => FieldType::BOOLEAN,
			'email_required'             => FieldType::BOOLEAN,
			'name_enabled'               => FieldType::BOOLEAN,
			'name_required'              => FieldType::BOOLEAN,
			'address_enabled'            => FieldType::BOOLEAN,
			'address_required'           => FieldType::BOOLEAN,
			'message_enabled'            => FieldType::BOOLEAN,
			'message_required'           => FieldType::BOOLEAN,
			'theme_color'                => FieldType::STRING,
			'terms_link'                 => FieldType::STRING,
			'privacy_link'               => FieldType::STRING,
			'show_return_message'        => FieldType::BOOLEAN,
			'use_custom_return_url'      => FieldType::BOOLEAN,
			'custom_return_url'          => FieldType::STRING,
			'payment_description_format' => FieldType::STRING,
			'custom_styles'              => FieldType::STRING,
			'initial_title'              => FieldType::STRING,
			'initial_description'        => FieldType::STRING,
			'subscription_title'         => FieldType::STRING,
			'subscription_description'   => FieldType::STRING,
			'address_title'              => FieldType::STRING,
			'address_description'        => FieldType::STRING,
			'message_title'              => FieldType::STRING,
			'message_description'        => FieldType::STRING,
			'payment_title'              => FieldType::STRING,
			'payment_description'        => FieldType::STRING,
			'return_message_title'       => FieldType::STRING,
			'return_message_text'        => FieldType::STRING,
			'allow_anonymous'            => FieldType::BOOLEAN,
			'created_at'                 => FieldType::STRING,
			'updated_at'                 => FieldType::STRING,
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
