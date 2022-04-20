<?php

namespace Kudos\Helpers;

use Exception;

class Campaign {

	/**
	 * Gets the campaign by specified column (e.g. id).
	 *
	 * @param string|null $value
	 *
	 * @return array|null
	 * @throws Exception
	 */
	public static function get_campaign( string $value ): ?array {

		$posts = get_posts( [
			'post_type'      => 'kudos_campaign',
			'posts_per_page' => '1',
			'name'           => $value,
		] );

		if ( ! empty( $posts ) ) {
			$post = get_post_meta( $posts[0]->ID );
			if ( $post ) {
				$post['name'] = $posts[0]->post_title;

				return $post;
			}
		}

		/* translators: %s: Campaign id */
		throw new Exception( sprintf( __( 'Campaign "%s" not found.', 'kudos-donations' ), $value ) );
	}

	/**
	 * Gets transaction stats for campaign.
	 *
	 * @param array $transactions
	 *
	 * @return array
	 */
	public static function get_campaign_stats( array $transactions ): ?array {

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

		// No transactions found.
		return [
			'count'         => 0,
			'total'         => 0,
			'last_donation' => '',
		];
	}

}
