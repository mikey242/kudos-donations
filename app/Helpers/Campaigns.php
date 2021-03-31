<?php

namespace Kudos\Helpers;

use Kudos\Entity\TransactionEntity;
use Kudos\Service\MapperService;

class Campaigns {

	/**
	 * @var mixed
	 */
	private $campaigns;

	public function __construct() {

		$this->campaigns = Settings::get_setting( 'campaigns' );

	}

	/**
	 * Sanitize the various setting fields in the donation form array.
	 *
	 * @param $campaigns
	 *
	 * @return array
	 * @since 2.3.0
	 */
	public function sanitize_campaigns( $campaigns ): array {

		// Loop through each of the campaigns sanitizing the data.
		foreach ( $campaigns as $key => &$form ) {

			if ( ! isset( $form['id'] ) ) {
				$form['id'] = $this->generate_id( $form['name'] );
			}

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
						$value = rest_sanitize_boolean( $value );
						break;
				}
			}
		}

		return $campaigns;
	}

	/**
	 * Generates a unique ID in the form of a slug for the campaign
	 *
	 * @param $name string User provided name for the campaign
	 *
	 * @return string
	 */
	public function generate_id( string $name ): string {

		$id        = sanitize_title( $name );
		$campaigns = $this->campaigns;
		$ids       = array_map( function ( $campaign ) {
			return $campaign['id'];
		},
			$campaigns );

		// If current id exists in array, iterate $n until it is unique
		$n      = 1;
		$new_id = $id;
		while ( in_array( $new_id, $ids ) ) {
			$new_id = $id . '-' . $n;
			$n ++;
		}

		// Return new id
		return $new_id;
	}

	/**
	 * Gets the campaign by specified column (e.g slug)
	 *
	 * @param string|null $value
	 *
	 * @return array|null
	 * @since 2.3.0
	 */
	public function get_campaign( ?string $value ): ?array {

		$campaigns = $this->campaigns;
		$key       = array_search( $value, array_column( (array) $campaigns, 'id' ) );

		// Check if key is an index and if so return index from forms
		if ( is_int( $key ) ) {
			return $campaigns[ $key ];
		}

		return null;

	}

	/**
	 * Gets transaction stats for campaign
	 *
	 * @param string $campaign_id
	 *
	 * @return array
	 */
	public static function get_campaign_stats( string $campaign_id ): ?array {

		$mapper       = new MapperService( TransactionEntity::class );
		$transactions = $mapper->get_all_by( [
			'campaign_id' => $campaign_id,
		] );

		if ( $transactions ) {
			$values = array_map( function ( $transaction ) {
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
				$transactions );

			return [
				'count'         => count( $values ),
				'total'         => array_sum( $values ),
				'last_donation' => end( $transactions )->created,
			];
		}

		return [
			'count'         => 0,
			'total'         => 0,
			'last_donation' => '',
		];


	}

	/**
	 * Returns all campaigns
	 *
	 * @return null|array
	 * @since 2.3.0
	 */
	public function get_all(): ?array {

		return (array) $this->campaigns;

	}

}
