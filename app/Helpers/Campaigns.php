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
	 * Sanitize the various setting fields in the donation form array
	 *
	 * @param $campaigns
	 *
	 * @return array
	 * @since 2.3.0
	 */
	public function sanitize_campaigns( $campaigns ): array {

		//Define the array for the updated options
		$output = [];

		// Loop through each of the options sanitizing the data
		foreach ( $campaigns as $key => $form ) {

			if ( ! array_search( 'id', $form ) ) {
				$output[ $key ]['id'] = $this->generate_id( $form['name'] );
			}

			foreach ( $form as $option => $value ) {

				switch ( $option ) {
					case 'modal_title':
					case 'welcome_text':
						$output[ $key ][ $option ] = sanitize_text_field( $value );
						break;
					case 'amount_type':
					case 'donation_type':
						$output[ $key ][ $option ] = sanitize_key( $value );
						break;
					default:
						$output[ $key ][ $option ] = $value;
				}
			}
		}

		return $output;
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

		// If current id exists in array, iterate $n until it it unique
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
	 * Adds default campaign is no campaigns found
	 *
	 * @since 2.3.0
	 */
	public function add_default() {

		$default_campaign[0] = [
			'id'               => 'default',
			'name'             => 'Default',
			'modal_title'      => __( 'Support us!', 'kudos-donations' ),
			'welcome_text'     => __( 'Your support is greatly appreciated and will help to keep us going.',
				'kudos-donations' ),
			'address_required' => true,
			'amount_type'      => 'both',
			'fixed_amounts'    => '1,5,20,50',
			'donation_type'    => 'both',
			'protected'        => true,
		];

		if ( empty( $this->campaigns ) ) {
			update_option( Settings::PREFIX . 'campaigns', $default_campaign );
		}

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
	 * Gets total value paid for campaign
	 *
	 * @param string $campaign_id
	 *
	 * @return float|int
	 */
	public function get_campaign_total( string $campaign_id ) {

		$mapper       = new MapperService( TransactionEntity::class );
		$transactions = $mapper->get_all_by( [
			'campaign_id' => $campaign_id,
		] );

		return array_sum( array_map( function ( $transaction ) {
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
			(array) $transactions ) );

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
