<?php

namespace Kudos\Helpers;

use Exception;

class Campaign {

	/**
	 * Gets the campaign by specified column (e.g. id).
	 *
	 * @throws Exception
	 *
	 * @param string|null $value
	 * @return array|null
	 */
	public static function get_campaign( string $value ): ?array {

		$campaigns = Settings::get_setting( 'campaigns' );
		$key       = array_search( $value, array_column( (array) $campaigns, 'id' ) );

		// Check if key is an index and if so return index from forms.
		if ( \is_int( $key ) ) {
			return $campaigns[ $key ];
		}

		/* translators: %s: Campaign id */
		throw new Exception( sprintf( __( 'Campaign "%s" not found.', 'kudos-donations' ), $value ) );
	}

	/**
	 * Gets transaction stats for campaign.
	 *
	 * @param array $transactions
	 */
	public static function get_campaign_stats( array $transactions ): ?array {

		if ( $transactions ) {
			$values = array_map(
				function ( $transaction ) {
					if ( 'paid' === $transaction->status ) {
							$refunds = $transaction->get_refund();
						if ( $refunds ) {
							return $refunds->remaining;
						} else {
							return $transaction->value;
						}
					}

					return 0;
				},
				$transactions
			);

			return [
				'count'         => \count( $values ),
				'total'         => array_sum( $values ),
				'last_donation' => end( $transactions )->created,
			];
		}

		// No transactions found.
		return [
			'count'         => 0,
			'total'         => 0,
			'last_donation' => '',
		];
	}

	/**
	 * Sanitize the various setting fields in the donation form array.
	 *
	 * @param $campaigns
	 */
	public static function sanitize_campaigns( $campaigns ): array {

		// Loop through each of the campaigns.
		foreach ( $campaigns as &$form ) {

			// Generate a unique campaign ID if none yet.
			if ( ! isset( $form['id'] ) ) {
				$form['id'] = self::generate_campaign_id( $form['name'] );
			}

			// Loop through fields and sanitize.
			foreach ( $form as $option => &$value ) {

				switch ( $option ) {
					case 'name':
					case 'modal_title':
					case 'welcome_text':
					case 'fixed_amounts':
						$value = sanitize_text_field( $value );
						break;
					case 'amount_type':
					case 'donation_type':
						$value = sanitize_key( $value );
						break;
					case 'address_enabled':
					case 'address_required':
					case 'show_progress':
					case 'message_enabled':
						$value = rest_sanitize_boolean( $value );
						break;
				}
			}
		}

		return $campaigns;
	}

	/**
	 * Generates a unique ID in the form of a slug for the campaign.
	 *
	 * @param $name string User provided name for the campaign.
	 */
	private static function generate_campaign_id( string $name ): string {

		$id        = sanitize_title( $name );
		$campaigns = Settings::get_setting( 'campaigns' );
		$ids       = array_map(
			function ( $campaign ) {
				return $campaign['id'];
			},
			$campaigns
		);

		// If current id exists in array, iterate $n until it is unique.
		$n      = 1;
		$new_id = $id;
		while ( \in_array( $new_id, $ids ) ) {
			$new_id = $id . '-' . $n;
			++$n;
		}

		// Return new id.
		return $new_id;
	}
}
