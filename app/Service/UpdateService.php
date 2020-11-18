<?php

namespace Kudos\Service;

use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Settings;

class UpdateService {

	/**
	 * Adds campaign labels on transactions to the settings array
	 *
	 * @return bool
	 * @since 2.0.4
	 */
	public static function sync_campaign_labels() {

		$mapper = new MapperService( TransactionEntity::class );

		// Get transactions and stop if none found.
		$transactions = $mapper->get_all_by( [ "campaign_label" ] );
		if ( ! $transactions ) {
			return false;
		}

		$transactions = array_filter($transactions, function ($transaction) {
			return ! empty($transaction->campaign_label);
		});

		// Sort transactions by date, ensures oldest dates used.
		usort(
			$transactions,
			function ( $b, $a ) {
				return strtotime( $a->created ) <=> strtotime( $b->created );
			}
		);

		// Create array of labels.
		$transaction_labels = [];
		foreach ( $transactions as $key => $transaction ) {
			$label                        = strtolower( $transaction->campaign_label );
			$transaction_labels[ $label ] = [
				'date'  => $transaction->created,
				'label' => $label,
			];
		}

		// Merge arrays and ensure no duplicates.
		$current_labels = ! empty( Settings::get_setting( 'campaign_labels' ) ) ? Settings::get_setting( 'campaign_labels' ) : [];
		$labels         = array_merge( $transaction_labels, $current_labels );
		$labels         = array_values( array_column( $labels, null, 'label' ) );

		// Update labels in settings with new, merged values.
		return Settings::update_setting( 'campaign_labels', $labels );
	}
}
